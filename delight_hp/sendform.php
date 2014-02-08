<?php

// Include the Configuration
require_once("./config/config.inc.php");
require_once("./config/userconf.inc.php");

// Get the referer and the TextID
$formFailure = preg_replace('/[^a-z0-9\/_-]+/smi', '', $_POST['failure']);
$formCheck = preg_replace('/[^a-z0-9]+/smi', '', $_POST['referer']);
$formTextId = preg_replace('/[^0-9]+/smi', '', $_POST['tid']);

$formConfigFile = ABS_STATIC_DIR.'formConfig-'.$formTextId.'.php';

// Include the Formular-Configuration which should define the $_formConfig array
$_formConfig = array();
if (file_exists($formConfigFile)) {
	require_once($formConfigFile);
}

if (!array_key_exists('tracking_form', $_formConfig)) {
	$_formConfig['tracking_form'] = '';
}

if (false) {
	print("<pre>");
	print_r($_POST);
	print_r($_formConfig);
	print("</pre>");
	exit();
}

// we need to Check if $formFailure is really just a relative path and not a URI on a remote-Host
if (empty($formFailure)) {
	$formFailure = $_formConfig['onfailure'];
}
if ($formFailure[0] != '/') {
	$formFailure = '/de/home';
}

// first we Check for a Hacking-Attempt or a not-existent ConfigFile
if ( (count($_formConfig) > 0) && (array_key_exists('referer', $_formConfig)) && ($_formConfig['referer'] == $formCheck)) {
	validateFormConfiguration($_formConfig);

	switch (strtolower($_formConfig['method'])) {

		// Send an EMail
		case 'mail':
			sendFormEmail($_formConfig);
			break;

		// Send the FormData to the TrackingServer
		case 'tracking':
			sendToTrackingServer($_formConfig);
			break;

		// as default action we just redirect to the Failure-Site
		default:
			redirectToUrl($formFailure);
			break;
	}

} else {
	redirectToUrl($formFailure);
}

/**
 * Check for missing configuration-parameters
 *
 * @param array &$config A Pointer to the Configuration
 */
function validateFormConfiguration(&$config) {
	// First we need to check if we know the method to proceed this FormPosting
	if (!array_key_exists('method', $config)) {
		$config['method'] = 'mail';
	}

	// Also global parameters are 'onsuccess' and 'onfailure'
	if (!array_key_exists('onsuccess', $config)) {
		$config['onsuccess'] = '/de/home';
	}
	if (!array_key_exists('onfailure', $config)) {
		$config['onfailure'] = '/de/home';
	}

	// different methods need different fields
	if ($config['method'] == 'mail') {
		if (!array_key_exists('mailrcpt', $config)) {
			$config['mailrcpt'] = '';
		}
		if (!array_key_exists('mailrcptname', $config)) {
			$config['mailrcptname'] = '';
		}
		if (!array_key_exists('mailrcptfield', $config)) {
			$config['mailrcptfield'] = 'null';
		}
		if (!array_key_exists('mailsubject', $config)) {
			$config['mailsubject'] = '';
		}
		if (!array_key_exists('mailinform', $config)) {
			$config['mailinform'] = 0;
		}
		if (!array_key_exists('mailsenderfield', $config)) {
			$config['mailsenderfield'] = 'null';
		}
		if (!array_key_exists('mailpretext', $config)) {
			$config['mailpretext'] = '';
		}
		if (!array_key_exists('mailposttext', $config)) {
			$config['mailposttext'] = '';
		}
		if (!array_key_exists('mail_fields', $config)) {
			$config['mail_fields'] = '';
		}
		if (!array_key_exists('mail_fieldnames', $config)) {
			$fields = explode(',', $config['mail_fields']);
			$config['mail_fieldnames'] = array();
			foreach ($fields as $field) {
				$config['mail_fieldnames'][$field] = $field;
			}
		}

		// replace some chars
		$keys = array_keys($config);
		foreach ($keys as $key) {
			$config[$key] = str_replace('&#43;', '+',  $config[$key]);
			$config[$key] = str_replace('+',     ' ',  $config[$key]);
			$config[$key] = str_replace('##43;', '+',  $config[$key]);
			$config[$key] = str_replace('##34;', '"',  $config[$key]);
			$config[$key] = str_replace('##39;', '\'', $config[$key]);
			$config[$key] = str_replace('##10;', "\n", $config[$key]);
			$config[$key] = str_replace('&#10;', "\n", $config[$key]);
		}

		// Check for a defined SenderList
		if (($config['mailrcptfield'] != 'null') && array_key_exists($config['mailrcptfield'], $_POST)) {
			$val = filter_input(INPUT_POST, $config['mailrcptfield'], FILTER_SANITIZE_NUMBER_INT);
			$tmp = explode(',', $config['mailrcpt']);
			foreach ($tmp as $addr) {
				$addr = explode(':', trim($addr));
				if ((int)trim($addr[0]) == $val) {
					$config['mailrcpt'] = filter_var(trim($addr[1]), FILTER_SANITIZE_EMAIL);
					break;
				}
			}
		}
	}
}


/**
 * Send an Email with all FORM-Datas
 *
 * @param array &$config A Pointer to the Configuration
 */
function sendFormEmail(&$config) {
	$maxChar = 70;
	$fields = array_unique(explode(',', $config['mail_fields']));
	$fieldNames = $config['mail_fieldnames'];
	$contents = array();
	$content = '';
	$label = '';
	$nl = chr(10);
	$boundary = '=_bound'.md5(uniqid(time()));
	$boundary_content = '=_cont'.md5(uniqid(time()));
	$attach_type = array_key_exists('mailattachment', $config) ? $config['mailattachment'] : 'none';
	$attach_fields = array();

	// Initialize the Contents (text/plain and text/html)
	$contents[0] = '';
	$contents[1] = '';

	if (strlen($config['mailpretext']) > 0) {
		$formPreText = strip_tags($config['mailpretext']);
	} else {
		$formPreText = '';
	}
	if (strlen($config['mailposttext']) > 0) {
		$formPostText = strip_tags($config['mailposttext']);
	} else {
		$formPostText = '';
	}

	// Get the longest Field
	$fieldMaxLength = 0;
	foreach ($_POST as $k => $v) {
		if (array_key_exists($k, $fieldNames)) {
			$label = $fieldNames[$k];
		} else {
			$label = $k;
		}
		$fieldMaxLength = (strlen($label) > $fieldMaxLength) ? strlen($label) : $fieldMaxLength;
	}

	// Append each field
	$cnt = 0;
	$postField = '';
	foreach ($fields as $field) {
		$postField = str_replace("]", "", str_replace("[", "", $field));
		if (array_key_exists($postField, $_POST)) {
			$cnt++;
			if (array_key_exists($field, $fieldNames)) {
				$label = $fieldNames[$field];
			} else {
				$label = $field;
			}
			if (!is_array($_POST[$postField])) {
				$value = strip_tags($_POST[$postField]);
			} else {
				$value = '';
				foreach ($_POST[$postField] as $v) {
					$value .= $v.', ';
				}
			}

			$contents[0] .= html_entity_decode($label).':'.str_repeat(' ', $fieldMaxLength - strlen($label) + 3).html_entity_decode($value).$nl;
			$contents[1] .= '<tr style="background-color:#'.($cnt%2 ? 'fcfcfc' : 'f0f0f0').';"><td>'.$label.'</td><td>'.nl2br($value).'</td></tr>'.$nl;

			// Replace the fieldvalue in pretext and posttext
			$formPreText  = str_replace('['.substr($field, strpos($field, '_')+1).']', $value, $formPreText);
			$formPostText = str_replace('['.substr($field, strpos($field, '_')+1).']', $value, $formPostText);

			// Append all fields for the Attachment
			if ($attach_type != 'none') {
				$attach_fields[substr($field, strpos($field, '_')+1)] = array($label, $value);
			}
		}
	}

	// Append lines before and after the fields
	$contents[0] = str_repeat('*', $maxChar).$nl.$contents[0].str_repeat('*', $maxChar);
	$contents[1] = '<table cellpadding="4" cellspacing="0" border="0">'.$nl.'<tr style="font-weight:bold;background-color:#c1c1c1;"><td>Name:</td><td>Beschreibung:</td>'.$nl.$contents[1].'</table>';

	// Append Pretext and Posttext
	if (!empty($formPreText)) {
		$contents[0] = strip_tags($formPreText).$nl.$nl.$contents[0];
		$contents[1] = '<p>'.nl2br($formPreText).'<br /></p>'.$nl.$nl.$contents[1];
	}
	if (!empty($formPostText)) {
		$contents[0] .= $nl.$nl.strip_tags($formPostText);
		$contents[1] .= $nl.'<p><br /><br />'.nl2br($formPostText).'</p>';
	}

	// Create the content to send
	$content = '';
	$content .= '--'.$boundary.$nl;
	$content .= 'Content-Type: multipart/alternative;'.$nl;
	$content .= chr(9).'boundary="'.$boundary_content.'"'.$nl.$nl;

	$content .= '--'.$boundary_content.$nl;
	$content .= 'Content-Type: text/plain; charset="utf-8"'.$nl;
	$content .= 'Content-Transfer-Encoding: quoted-printable'.$nl.$nl;
	$content .= iso88591_encode(html_entity_decode($contents[0]), false).$nl.$nl;

	$content .= '--'.$boundary_content.$nl;
	$content .= 'Content-Type: text/html; charset="utf-8"'.$nl;
	$content .= 'Content-Transfer-Encoding: 8bit'.$nl.$nl;
	$content .= $contents[1].$nl.$nl;
	$content .= '--'.$boundary_content.'--'.$nl.$nl;

	// Attach Data if requested
	if ($attach_type != 'none') {
		$filename = preg_replace('/[^a-z0-9_-]+/smi', '_', $config['mailsubject']);
		$filename = $filename.'_'.date('Y-m-d_H-i-s');

		$content .= '--'.$boundary.$nl;
		switch ($attach_type) {
			case 'csv':
				$content .= 'Content-Type: text/csv;'.$nl;
				$content .= chr(9).'charset="utf-8";'.$nl;
				$content .= chr(9).'name="'.$filename.'.csv"'.$nl;
				$content .= 'Content-Transfer-Encoding: base64'.$nl;
				$content .= 'Content-Description: CSV Data from a delight cms Formular-Request'.$nl;
				$content .= 'Content-Disposition: attachment;'.$nl;
				$content .= chr(9).'filename="'.$filename.'.csv"'.$nl.$nl;
				$content .= createCSV($attach_fields);
				break;

			case 'vcard':
				$content .= 'Content-Type: text/directory;'.$nl;
				$content .= chr(9).'charset="utf-8";'.$nl;
				$content .= chr(9).'name="'.$filename.'.vcf"'.$nl;
				$content .= 'Content-Transfer-Encoding: base64'.$nl;
				$content .= 'Content-Description: VCard Data from a delight cms Formular-Request'.$nl;
				$content .= 'Content-Disposition: attachment;'.$nl;
				$content .= chr(9).'filename="'.$filename.'.vcf"'.$nl.$nl;
				$content .= createVCF($attach_fields);
				break;
		}
	}

	$content .= '--'.$boundary.'--'.$nl;

	// shorten lines from content to maximum $maxChar
	$content = wordwrap($content, $maxChar, $nl);

	// Create Headers
	$headers = '';
	$headers .= 'Mime-Version: 1.0'.$nl;
	$headers .= 'Content-Type: multipart/related;'.$nl;
	$headers .= chr(9).'boundary="'.$boundary.'"'.$nl;
	$headers .= 'X-Mailer: delight cms formular mailer'.$nl;
	//$headers .= 'Content-Transfer-Encoding: 8bit'.$nl;
	//$headers .= 'To: '.str_replace(',', ' ', $_formConfig['mailrcptname']).' <'.$_formConfig['mailrcpt'].'>'.$nl;

	// Get Customers name
	$customer_name = '';
	if (isset($_POST['ed_title']))      { $customer_name .= preg_replace('/[^a-zA-Z0-9]/smi', '', $_POST['ed_title']).' '; }
	if (isset($_POST['ed_prefix']))     { $customer_name .= preg_replace('/[^a-zA-Z0-9]/smi', '', $_POST['ed_prefix']).' '; }
	if (isset($_POST['ed_firstname']))  { $customer_name .= preg_replace('/[^a-zA-Z0-9]/smi', '', $_POST['ed_firstname']).' '; }
	if (isset($_POST['ed_middlename'])) { $customer_name .= preg_replace('/[^a-zA-Z0-9]/smi', '', $_POST['ed_middlename']).' '; }
	if (isset($_POST['ed_lastname']))   { $customer_name .= preg_replace('/[^a-zA-Z0-9]/smi', '', $_POST['ed_lastname']).' '; }
	if (isset($_POST['ed_surname']))    { $customer_name .= preg_replace('/[^a-zA-Z0-9]/smi', '', $_POST['ed_surname']).' '; }

	// Send the Mail to the Customer
	if ( ((int)$config['mailinform'] > 0) && ($config['mailsenderfield'] != 'null') && (array_key_exists('ed_'.$config['mailsenderfield'], $_POST))) {
		$customer = $headers;
		$customer .= 'From: =?iso-8859-1?Q?\''.iso88591_encode($config['mailrcptname'], false).'\'?= <'.$config['mailrcpt'].'>'.$nl;
		$customer .= 'Return-Path: '.$config['mailrcpt'].''.$nl;
		$customer .= 'Reply-To: '.$config['mailrcpt'].''.$nl;
		// Empfangsbestaetigung, aber nicht wirklich Standard...
		//$customer .= 'Return-Receipt-To: '.$config['mailrcpt'].''.$nl;
		$customer_email = preg_replace('/[^0-9a-z\.\@_-]/smi', '', $_POST['ed_'.$config['mailsenderfield']]);
		if (empty($customer_name)) {
			$customer_name = $customer_email;
		}

		// does not work with QMail...
		//, '-r '.$config['mailrcpt']
		mail('=?iso-8859-1?Q?\''.iso88591_encode($customer_name, false).'\'?= <'.$customer_email.'>', iso88591_encode($config['mailsubject'], true), $content, $customer);

	} else {
		$customer_email = $config['mailrcpt'];
		if (empty($customer_name)) {
			$customer_name = $customer_email;
		}
	}

	// Send the Email to the Company
	if (isset($_POST['ed_organization'])) {
		$headers .= 'Organization: =?iso-8859-1?Q?\''.iso88591_encode($_POST['ed_organization'], false).'\'?='.$nl;
	}
	$headers .= 'From: =?iso-8859-1?Q?\''.iso88591_encode($customer_name, false).'\'?= <'.$customer_email.'>'.$nl;
	$headers .= 'Return-Path: '.$customer_email.''.$nl;
	$headers .= 'Reply-To: '.$customer_email.''.$nl;
	// Empfangsbestaetigung, aber nicht wirklich Standard...
	//$headers .= 'Return-Receipt-To: '.$customer_email.''.$nl;

	// does not work with QMail...
	//, '-r '.$customer_email
	if (mail('=?iso-8859-1?Q?\''.iso88591_encode($config['mailrcptname'], false).'\'?= <'.$config['mailrcpt'].'>', iso88591_encode($config['mailsubject'], true), $content, $headers)) {
		redirectToUrl($config['onsuccess']);
	} else {
		redirectToUrl($config['onfailure']);
	}
}

/**
 * Encode a String to ISO-8859-1 Conform MIME-Text
 * @param string $str String to encode
 * @param boolean $showISO Show the iso-8859-1 pretext
 * @return string
 */
function iso88591_encode($str, $showISO=true) {
	global $qpKeys, $qpReplaceValues;

	$str = html_entity_decode($str);
	$str = str_replace('=', '=3D', $str);
	$str = str_replace($qpKeys, $qpReplaceValues, $str);
	$str = rtrim($str);
	//$str = str_replace(array('?', ' ', '_'), array('=3F', '=20', '=5F'), $str);
	$str = str_replace(array('=0A', '=0D'), array(chr(10), chr(13)), $str);

	if ($showISO) {
		return '=?iso-8859-1?Q?'.$str.'?=';
	}
	return $str;
}

/**
 * Return a Base64 Encoded CSV from given Fields
 * @param array $fields array(field=>array(label, value), ...)
 * @return string
 */
function createCSV($fields) {
	$back = '';
	$values = '';
	foreach ($fields as $field=>$value) {
		if (!empty($back)) {
			$back .= ',';
		}
		if (!empty($values)) {
			$values .= ',';
		}
		$back .= '"'.$value[0].'"';
		$values .= '"'.$value[1].'"';
	}
	return base64_encode($back.chr(13).chr(10).$values);
}

/**
 * Return a Base64 Encoded VCF from given Fields
 * @param array $fields array(field=>array(label, value), ...)
 * @return string
 */
function createVCF($fields) {
	$nl = chr(13).chr(10);
	$keys = array_keys($fields);
	$back  = 'BEGIN:VCARD'.$nl;
	$back .= 'VERSION:2.1'.$nl;

	// Work-Address
	if (in_array('street_work', $keys) && in_array('city_work', $keys) && in_array('plz_work', $keys)) {
		$back .= 'ADR;TYPE=WORK;ENCODING=QUOTED-PRINTABLE:;;'.vcfStringEncode($fields['street_work'][1]).';';
		$back .= vcfStringEncode($fields['city_work'][1]).';';
		$back .= (in_array('region_work', $keys) ? vcfStringEncode($fields['region_work'][1]) : '').';';
		$back .= vcfStringEncode($fields['plz_work'][1]).';';
		$back .= (in_array('country_work', $keys) ? vcfStringEncode($fields['country_work'][1]) : '');
		$back .= $nl;
		unset($fields['street_work']);
		unset($fields['city_work']);
		unset($fields['plz_work']);
		unset($fields['region_work']);
		unset($fields['country_work']);
	}

	// Home-Address
	if (in_array('street', $keys) && in_array('city', $keys) && in_array('plz', $keys)) {
		$back .= 'ADR;TYPE=HOME;ENCODING=QUOTED-PRINTABLE:;;'.vcfStringEncode($fields['street'][1]).';';
		$back .= vcfStringEncode($fields['city'][1]).';';
		$back .= (in_array('region', $keys) ? vcfStringEncode($fields['region'][1]) : '').';';
		$back .= vcfStringEncode($fields['plz'][1]).';';
		$back .= (in_array('country', $keys) ? vcfStringEncode($fields['country'][1]) : '');
		$back .= $nl;
		unset($fields['street']);
		unset($fields['city']);
		unset($fields['plz']);
		unset($fields['region']);
		unset($fields['country']);
	}

	// other known values
	if (in_array('firstname', $keys)) {
		$back .= 'FN;ENCODING=QUOTED-PRINTABLE:'.vcfStringEncode($fields['firstname'][1]);
		$back .= (in_array('middlename', $keys) ? ' '.vcfStringEncode($fields['middlename'][1]) : '');
		$back .= (in_array('lastname', $keys) ? ' '.vcfStringEncode($fields['lastname'][1]) : '').$nl;

		$back .= 'N;ENCODING=QUOTED-PRINTABLE:'.(in_array('lastname', $keys) ? vcfStringEncode($fields['lastname'][1]) : '');
		$back .= ';'.vcfStringEncode($fields['firstname'][1]).';';
		$back .= (in_array('middlename', $keys) ? vcfStringEncode($fields['middlename'][1]) : '').';';
		$back .= (in_array('title', $keys) ? vcfStringEncode($fields['title'][1]) : '').';';
		$back .= (in_array('prefix', $keys) ? vcfStringEncode($fields['prefix'][1]) : '').';'.$nl;
		unset($fields['title']);
		unset($fields['prefix']);
		unset($fields['firstname']);
		unset($fields['midlename']);
		unset($fields['lastname']);
	}
	if (in_array('birthday', $keys)) {
		$back .= 'BDAY:'.strftime('%Y-%m-%dT00:00:00Z', strtotime($fields['birthday'][1])).$nl;
		unset($fields['birthday']);
	}
	if (in_array('organization', $keys)) {
		$back .= 'ORG;ENCODING=QUOTED-PRINTABLE:'.vcfStringEncode($fields['organization'][1]).$nl;
		unset($fields['organization']);
	}
	if (in_array('role', $keys)) {
		$back .= 'ROLE;ENCODING=QUOTED-PRINTABLE:'.vcfStringEncode($fields['role'][1]).$nl;
		unset($fields['role']);
	}
	if (in_array('website', $keys)) {
		$back .= 'URL;ENCODING=QUOTED-PRINTABLE:'.vcfStringEncode($fields['website'][1]).$nl;
		unset($fields['website']);
	}
	if (in_array('email', $keys)) {
		$back .= 'EMAIL;TYPE=PREF;ENCODING=QUOTED-PRINTABLE:'.vcfStringEncode($fields['email'][1]).$nl;
		unset($fields['email']);
	}
	if (in_array('telephone', $keys)) {
		$back .= 'TEL;TYPE=HOME;ENCODING=QUOTED-PRINTABLE:'.vcfStringEncode($fields['telephone'][1]).$nl;
		unset($fields['telephone']);
	}
	if (in_array('telefax', $keys)) {
		$back .= 'TEL;TYPE=FAX;ENCODING=QUOTED-PRINTABLE:'.vcfStringEncode($fields['telefax'][1]).$nl;
		unset($fields['telefax']);
	}

	$notes = '';
	$keys = array_keys($fields);
	foreach ($keys as $field) {
		$value = $fields[$field];

		if (substr($field, 0, 6) == 'email_') {
			$back .= 'EMAIL;TYPE='.strtoupper(substr($field, 6)).';ENCODING=QUOTED-PRINTABLE:'.vcfStringEncode($value[1]).$nl;

		} else if (substr($field, 0, 4) == 'tel_') {
			$back .= 'TEL;TYPE='.strtoupper(substr($field, 4)).';ENCODING=QUOTED-PRINTABLE:'.vcfStringEncode($value[1]).$nl;

		} else if (!empty($value[0])) {
			$notes .= $value[0].': '.$value[1].$nl;
		}
	}
	$back .= 'NOTE;ENCODING=QUOTED-PRINTABLE:'.vcfStringEncode($notes).$nl;

	$back .= 'END:VCARD'.$nl;
	return base64_encode($back);
}

/**
 * Encoding and Escaping VCard-Values
 * @param string $str String to Encode and Escape
 * @return string
 */
function vcfStringEncode($str) {
	global $qpKeys, $qpReplaceValues;
	$str = trim($str);

	$str = str_replace('=', '=3D', $str);
	$str = str_replace($qpKeys, $qpReplaceValues, $str);

	/* Not needed in "Quoted-Printable" Encoding
	$str = str_replace('\\' ,'\\\\', $str);
	$str = str_replace(',' ,'\\,', $str);
	$str = str_replace(';' ,'\\;', $str);
	$str = str_replace(':' ,'\\:', $str);*/
	return $str;
}


/**
 * Send all FormData to the TrackingServer defined in the $_formConfig
 *
 * @param array &$config A Pointer to the Configuration
 */
function sendToTrackingServer(&$config) {
	$crlf = chr(13).chr(10);
	$success = false;

	// Prepare Data to send within a POST-Request to the Tracking-Server
	$data = '';
	$nosend = array('tid', 'referer', 'failure', 'success');
	foreach ($_POST as $k=>$v) {
		if (in_array($k, $nosend)) continue;
		if (strlen($k) > 3 && ($k[2] == '_')) {
			$k = substr($k, 3, strlen($k)-1);
		} else if (strlen($k) > 4 && ($k[3] == '_')) {
			$k = substr($k, 4, strlen($k)-1);
		}
		if (is_array($v)) {
			foreach ($v as $_k=>$_v) {
				$data .= urlencode($k.'['.$_k.']').'='.urlencode($_v).'&';
			}
		} else {
			$data .= urlencode($k).'='.urlencode($v).'&';
		}
	}
	if (!empty($data)) {
		$data = substr($data, 0, -1);
	}

	// Connect and send
	$errno = 0;
	$errstr = '';
	$host = explode(':', $config['traserver']);
	$port = count($host) > 1 ? intval($host[1]) : 80;
	$sock = fsockopen($host[0], $port, $errno, $errstr, 5);

	if (get_resource_type($sock) == 'stream') {
		// Build the URL: /show/postdata/UID/post,method/GROUP,group/success,printSuccess/failed,printFailure/[EMAILFIELD,sendConfirmation/]
		// we can also use "redirectSuccess/redirectFailure"
		// The "sendConfirmation" should better be configured on the tracking server than being submitted here
		$url  = '/show/postdata/'.$config['traaccount'].'/post,method/';
		if ($config['tramodule'] == 'newsletter') {
			$config['tracking_form'] = 'registerservice';
		}
		$url .= str_replace(',', '_', $config['tracking_form']).',group/';
		$url .= 'success,printSuccess/failed,printFailure/';
		if (array_key_exists('trasenderemail', $config) && !empty($config['trasenderemail'])) {
			$url .= urlencode(preg_replace('/[,\/]+/smi', '', $config['trasenderemail'])).',sendConfirmation/';
		}

		fputs($sock, 'POST '.$url.' HTTP/1.1'.$crlf);
		fputs($sock, 'Host: '.$host[0].''.$crlf);
		fputs($sock, 'Accept-Charset: utf-8'.$crlf);
		fputs($sock, 'User-Agent: delight cms sendform.php'.$crlf);
		fputs($sock, 'Content-Type: application/x-www-form-urlencoded; charset=utf-8'.$crlf);
		fputs($sock, 'Content-Length: '.strlen($data).$crlf);
		fputs($sock, 'Connection: close'.$crlf.$crlf);
		fputs($sock, $data.$crlf.$crlf);

		$response = '';
		while (!feof($sock)) {
			$response .= fgets($sock, 4096);
		}
		fclose($sock);

		if (!empty($response)) {
			// Split into Header and Content
			$hunks = explode($crlf.$crlf, $response);
			if (count($hunks) >= 2) {
				$headers = explode($crlf, $hunks[0]);
				$content = $hunks[1];
				unset($hunks);

				print("<pre>");
				print_r($data);
				print("</pre>");
				print("<pre>");
				print_r($content);
				//print_r($_SERVER);
				//print_r($_POST);
				print("</pre>");
				exit();

				$success = validateTrackingResponseHeader($headers) && validateTrackingResponseContent($headers, $content);
				unset($headers);
				unset($content);
			}
		}
	}
	if ($success) {
		redirectToUrl($config['onsuccess']);
	} else {
		redirectToUrl($config['onfailure']);
	}
}

/**
 * Check for a valid HTTP-Response
 * @param array $headers
 * @return boolean
 */
function validateTrackingResponseHeader(array $headers) {
	$valid = false;
	$states = array('http/1.0 100 ok', 'http/1.0 200 ok', 'http/1.1 100 ok', 'http/1.1 200 ok');

	if (in_array(strtolower($headers[0]), $states)) {
		$valid = true;
	}
	return $valid;
}

/**
 * Check for a valid Response
 * @param array $headers Response-Headers - needed to chech for chunked content
 * @param string $content ReponseContent
 * @return boolean
 */
function validateTrackingResponseContent(array $headers, &$content) {
	$valid = false;
	$unpack = false;
	foreach ($headers as $v) {
		$k = trim(substr($v, 0, strpos($v, ':')));
		$v = trim(substr($v, strlen($k)+1));
		switch (strtolower($k)) {
			case 'content-encoding':
				$unpack = ($v == 'gzip');
				break;
			case 'transfer-encoding':
			case 'content-coding':
				if ($v == 'chunked') {
					$content = unchunkTrackingResponseContent($content);
				}
				break;
		}
	}
	if ($content !== false) {
		$valid = ($content == 'success');
	}

	return $valid;
}

/**
 * Unchunck HTTP-Response
 * @param string $content
 * @return string
 */
function unchunkTrackingResponseContent($content) {
	if (empty($content)) return false;

	$tmp = $content;
	$content = '';
	$crlf = chr(13).chr(10);
	$add = strlen($crlf);

	do {
		// strip out blanks on left and get the position of next CRLF
		$tmp = ltrim($tmp);
		$pos = strpos($tmp, $crlf);
		if ($pos === false) return false;

		// The current Line is a Hexvalue which stands for the length of the next chunck
		$len = hexdec(substr($tmp, 0, $pos));
		if (!is_numeric($len) || ($len < 0)) return false;

		// Append the Chunk to $content and strip it out from $tmp
		$content .= substr($tmp, ($pos + $add), $len);
		$tmp = substr($tmp, ($len + $pos + $add));

		// If $tmp is empty, we have finished unchunck
		$check = trim($tmp);
	} while (!empty($check));

	return trim($content);
}

/**
 * Redirect to an URL
 * $url can be absolute or relative
 *
 * @param string $url URL to redirect to
 */
function redirectToUrl($url) {
	if ( (substr($url, 0, 7) != 'http://') && (substr($url, 0, 6) != 'ftp://')) {
		if ($url{0} != '/') {
			$url = '/'.$url;
		}
		$port = '';
		if ($_SERVER['SERVER_PORT'] != 80) {
			$port = ':'.$_SERVER['SERVER_PORT'];
		}
		$url = 'http://'.$_SERVER['SERVER_NAME'].$port.$url;
	}
	header('Location: '.$url);
}

?>