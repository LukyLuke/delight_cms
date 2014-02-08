<?php
define("OPEN_DOWNLOAD_DATABASE_CONFIG", true);

// Include required Files
require_once ("../config/config.inc.php");
require_once ("../config/userconf.inc.php");
require_once("class/pURIParameters.cls.php");
require_once("class/pDatabaseConnection.cls.php");
require_once("class/pMessages.cls.php");

$session = pSession::getInstance();
$session->set('hijackprevention', $_SERVER['HTTP_HOST']);

// Create DatabaseConnection
$db = pDatabaseConnection::getDatabaseInstance();
$res = null;

// Get the Program
$_prgId = pURIParameters::get('prg','',pURIParameters::$STRING);

/* BEGIN: OLD LINKS WILL POINT PERHAPS TO mir_XX AND NOT DIRECTLY TO THE REAL PROGRAM-ID */
// Check if the $_prgId is a mirror
if (substr($_prgId, 0, 4) == "mir_") {
	$_mirId = substr($_prgId, 4);
	$sql = "SELECT [mir.program] FROM [table.mir] WHERE [mir.id]=".(int)$_mirId.";";
	$db->run($sql, $res);
	if ($res->getFirst()) {
		$_prgId = $res->{$db->getFieldName('mir.program')};
	} else {
		$_prgId = 'NoFile';
	}
	unset($_mirId);
}
$res = null;
/* END: OLD LINKS WILL POINT PERHAPS TO mir_XX AND NOT DIRECTLY TO THE REAL PROGRAM-ID */

// Get the real program-id if we get a guid instead
$sql = "SELECT [prg.id] FROM [table.prg] WHERE [prg.program] LIKE '".$_prgId.".%';";
$db->run($sql, $res);
if ($res->getFirst()) {
	$_prgId = $res->{$db->getFieldName('prg.id')};
} else {
	$sql = "SELECT [prg.id] FROM [table.prg] WHERE [prg.id]=".(int)$_prgId.";";
	$db->run($sql, $res);
	if (!$res->getFirst()) {
		die("This program does not exist");
	}
}
$res = null;

// Check for active mirrors
$_mirUrl = "";
$sql = "SELECT [mir.url] FROM [table.mir] WHERE [mir.active]=1 AND [mir.program]=".(int)$_prgId.";";
$db->run($sql, $res);
if ($res->getFirst()) {
	$_mirList = array();
	while ($res->getNext()) {
		array_push($_mirList, $res->{$db->getFieldName('mir.url')});
	}

	// Get a random mirror
	$_mirUrl = $_mirList[rand(0, (count($_mirList) - 1))];
}
$res = null;

// Get the File-Data and check Useraccess
$data = getFileData($_prgId);
$userCheck = pCheckUserData::getInstance();
$userCheck->setLoginHashData($session->get('loginhash', ''));
if ($data->secure && (!$userCheck->checkLogin() || !$userCheck->checkAccess('download')) ) {
	die('You need to Login to read this File...');
}

// Log the Download ad redirect if the User has access
// 2011-10-18 Dowloads are no longer logged
//doLogDownload($data);

// Redirect to the File
if (strlen(trim($_mirUrl)) > 0) {
	if (strtolower(substr($_mirUrl, 0, 3)) == "ftp") {
		header('Location: /downmir/ftp/' . preg_replace('/(.*?)(\:\/\/)(.*)/i', '\\3', $_mirUrl));
	} else {
		header('Location: /downmir/http/' . preg_replace('/(.*?)(\:\/\/)(.*)/i', '\\3', $_mirUrl));
	}
} else {
	if (!is_file($data->binary) || !is_readable($data->binary)) {
		header('HTTP/1.0 404 Not Found');
		echo '<html><head></head><body><h2>File not found</h2><br/>Go back to <a href="http://'.$_SERVER['HTTP_HOST'].'/">http://'.$_SERVER['HTTP_HOST'].'/<a></body></html>';
		exit;
	}

	// For the Etag-Header
	$etag = sprintf('%x-%x-%x', $data->stat['ino'], $data->stat['size'], $data->stat['mtime'] * 1000000);

	// First disable Cache
	header('Expires: ');
	header('Cache-Control: ');
	header('Pragma: ');

	// Check for "not changed until last time"
	if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && ($_SERVER['HTTP_IF_NONE_MATCH'] == $etag)) {
		header('Etag: "'.$etag.'"');
		header('X-System-Url: http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], true, 304);

	} else if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $data->stat['mtime'])) {
		header('Last-Modified: ' . date('r', $data->stat['mtime']));
		header('X-System-Url: http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], true, 304);

	} else {
		header('Content-Type: '.$data->mime);
		header('Content-Disposition: attachment; filename="'.$data->file.'"');
		header('Content-Length: '.$data->stat['size']);
		header('Etag: '.$etag);
		header('Last-Modified: ' . date('r', $data->stat['mtime']));
		header('Accept-Ranges: bytes');
		if (!file_exists($data->binary) || !is_readable($data->binary)) {
			header('X-System-Url: http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], true, 500);
			echo 'File not found';
		} else {
			header('X-System-Url: http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], true, 200);
			@readfile($data->binary);
		}
	}
}

/**
 * Getall needed Filedata from current download
 *
 * @param integer $id Programm-ID to get all data from
 * @return stdClass all needed filedata
 */
function getFileData($id=0) {
	$db = pDatabaseConnection::getDatabaseInstance();
	$res = null;
	$data = new stdClass();
	$sql = 'SELECT * FROM [table.prg] WHERE [prg.id]='.(int)$id.';';
	$db->run($sql, $res);
	if ($res->getFirst()) {
		if ($res->{$db->getFieldName('prg.name')} != "") {
			$data->id = $res->{$db->getFieldName('prg.id')};
			$data->filename = $res->{$db->getFieldName('prg.program')};
			$data->binary = null;
			$data->file = $res->{$db->getFieldName('prg.name')};
			$data->mime = $res->{$db->getFieldName('prg.mime')};
			$data->filesize = 0;
			$data->stat = array('ino'=>0,'size'=>0,'mtime'=>0);
			$data->section = $res->{$db->getFieldName('prg.section')};
			$data->name = substr($data->filename, 0, strpos($data->file, '.'));
			$data->extension = substr($data->filename, strpos($data->file, '.')+1);
			$data->secure = false;

			$absoluteBinary = dirname($_SERVER["SCRIPT_FILENAME"])."/../data/downloadfiles/".$res->{$db->getFieldName('prg.program')};
			if (file_exists($absoluteBinary)) {
				$data->binary = $absoluteBinary;
				$data->stat = stat($data->binary);
				$data->filesize = $data->stat['size'];
			}
		}
	}
	$res = null;

	// Check if the Secion is secured or not
	if (property_exists($data, 'section')) {
		$sql = 'SELECT [prs.secure],[prs.parent] FROM [table.prs] WHERE [prs.id]='.$data->section.';';
		$db->run($sql, $res);
		$res->getFirst();
		$secure = $res->{$db->getFieldName('prs.secure')};
		$data->secure = (int)$secure > 0;
		$parent = (int)$res->{$db->getFieldName('prs.parent')};
		$res = null;

		// Check if there is a Parent-Section which is secure
		while (($parent > 0) && !$data->secure) {
			$sql = 'SELECT [prs.parent],[prs.secure] FROM [table.prs] WHERE [prs.id]='.$parent.';';
			$db->run($sql, $res);
			$res->getFirst();
			$secure = $res->{$db->getFieldName('prs.secure')};
			$data->secure = (int)$secure > 0;
			$parent = (int)$res->{$db->getFieldName('prs.parent')};
			$res = null;
		}
	}

	return $data;
}

/**
 * make a Logentry
 *
 * @param stdClass $data filedata from function GetFileData
 */
function doLogDownload(stdClass $data) {
	if (!defined('LOG_DOWNLOAD') || !LOG_DOWNLOAD) {
		return;
	}

	$db = pDatabaseConnection::getDatabaseInstance();
	$res = null;

	$sql = 'SELECT * FROM [table.dll] WHERE [dll.file_id]='.$data->id.' AND [dll.ip]=\''.$_SERVER['REMOTE_ADDR'].'\' AND ([dll.time] LIKE \''.date("Y-m-d H:i").'%\' OR [dll.time] LIKE \''.date("Y-m-d H:i", mktime(date("H"), date("i") - 60, date("s"), date("m"), date("d"), date("Y"))).'%\');';
	$db->run($sql, $res);
	if (!$res->getFirst()) {
		$sql = 'INSERT INTO [table.dll] ([field.dll.file],[field.dll.real],[field.dll.fileid],[field.dll.size],[field.dll.section],[field.dll.ip],[field.dll.domain],[field.dll.browser],[field.dll.time])';
		$sql .= ' VALUES (\''.$data->name.'\',\''.$data->filename.'\',\''.$data->id.'\',\''.$data->filesize.'\',\''.$data->section.'\',\''.$_SERVER['REMOTE_ADDR'].'\',\''.gethostbyaddr($_SERVER['REMOTE_ADDR']).'\',\''.$_SERVER['HTTP_USER_AGENT'].'\',\''.date('Y-m-d H:i:s').'\');';
		$res = null;
		$db->run($sql, $res);
	}
	$res = null;
}
?>