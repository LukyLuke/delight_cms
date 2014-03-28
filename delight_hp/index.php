<?php

ob_start();
require_once("./config/config.inc.php");

if ($_SERVER['APPLICATION_ENV'] == 'development') {
	require_once('../FirePHPCore/FirePHP.class.php');
	error_reporting(E_ALL);
	ini_set("display_errors", 1);
	$firephp = FirePHP::getInstance(true);
	$firephp->setEnabled(true);
}

// Old things to be removed and cleand
require_once("./php/class/Database.cls.php");
require_once("./php/class/pParseTemplate.cls.php");
require_once("./php/pParseVariables.cls.php");
require_once("./php/pCheckUserData.cls.php");
require_once("./php/pAdminHTTP.cls.php");
require_once("./php/RefererLog.php");

// Check for a defined TEMPLATE_DIR
if (!defined('TEMPLATE_DIR')) {
	define('TEMPLATE_DIR', MAIN_DIR.'template/');
	define('ABS_TEMPLATE_DIR', str_replace('/', DIRECTORY_SEPARATOR, realpath(dirname($_SERVER['SCRIPT_FILENAME'])).'/../'.TEMPLATE_DIR));
}
// delight cms small
if (!defined('CMS_SMALL')) {
	define('CMS_SMALL', false);
}

// Send all Headers
header('Content-Type: text/html; charset=UTF-8');
header('Pragma: public');
//header('Cache-Control: no-cache, maxage=1, must-revalidate, post-check=0, pre-check=0');
//header('Expires: 0');

// Firefox AntiClickJacking Header: Allow only IFRAMS from same Domain
if (defined('ANTI_CLICKJACKING') && ANTI_CLICKJACKING) {
	header('X-FRAME-OPTIONS: SAMEORIGIN');
}

// PIWIK-Statistics
if (!defined('PIWIK_SITE_ID')) {
	define('PIWIK_SITE_ID', '');
}

// New Session-Handler
$session = pSession::getInstance();
$session->set('hijackprevention', $_SERVER['HTTP_HOST']);

// We always need to save the Session which is called by __destruct on pSession
register_shutdown_function('globalSaveSession', $session);

// Create Messages-Object
$l = pURIParameters::get('lan', MASTER_LANGUAGE, pURIParameters::$STRING);
if ($l == MASTER_LANGUAGE) {
	$l = pURIParameters::get('lang', MASTER_LANGUAGE, pURIParameters::$STRING);
}
$i18n = pMessages::getLanguageInstance($l);

// TODO:DEBRECATED - Remove after cleaned all Plugins and Classes from this variables
$DB = new Database();
$DB->ConnectToDatabase();
$LANG = pMessages::getLanguageInstance();
$langshort = $i18n->getShortLanguageName();
$langid = $i18n->getLanguageId();


// Log the referer
//log_referer();

// Check UserAccess
$userCheck = pCheckUserData::getInstance();
$userCheck->setLoginHashData($session->get('loginhash', ''));

$userName = pURIParameters::get('us', '', pURIParameters::$STRING);
$userPass = pURIParameters::get('ps', '', pURIParameters::$STRING);

// Logout...
if ( (pURIParameters::get('adm', 0, pURIParameters::$INT) < 0) || ((strlen($userName) > 0) || (strlen($userPass) > 0)) ) {
	$userCheck->doLogout();
}

if (!$userCheck->checkLogin()) {
	// Administration-Login
	if (!$userCheck->checkLogin()) {
		$userCheck->setUserData($userName, $userPass, true);
		$session->set('loginhash', $userCheck->getHashString());
	}

	// User-Login in Menu
	// TODO: Is this needed anymore?
	if (!$userCheck->checkLogin()) {
		$userCheck->setUserData(pURIParameters::get('user', '', pURIParameters::$STRING), pURIParameters::get('pass', '', pURIParameters::$STRING), true);
		$session->set('loginhash', $userCheck->getHashString());
	}

	// Cookie-Base Autologin
	if (isset($_COOKIE['dhpautologin'])) {
		$session->set('loginhash', $_COOKIE['dhpautologin']);
		$userCheck->setLoginHashData($session->get('loginhash', ''));
		$userCheck->checkLogin();
	}
}

// Check for AutoLogin
$autoLogin = pURIParameters::get('al', '', pURIParameters::$STRING);
if (!empty($autoLogin) || isset($_COOKIE['dhpautologin'])) {
	if ($userCheck->checkLogin()) {
		$cookieData = $userCheck->getHashString();
	} else if (isset($_COOKIE['dhpautologin'])) {
		$cookieData = $_COOKIE['dhpautologin'];
	} else {
		$cookieData = '';
	}
	if (version_compare(phpversion(), '5.2.0') >= 0) {
		setcookie('dhpautologin', $cookieData, time()+(3600*24*MAX_AUTOLOGIN_DAYS), WEB_ROOT, $_SERVER['HTTP_HOST'], false, false);
	} else {
		setcookie('dhpautologin', $cookieData, time()+(3600*24*MAX_AUTOLOGIN_DAYS), WEB_ROOT, $_SERVER['HTTP_HOST'], false);
	}
}

// Get all needed Variables to get a static page
$sectionId = pURIParameters::get('sec', 0, pURIParameters::$INT);
$pageOffset = pURIParameters::get('off', 0, pURIParameters::$INT);
$postId = pURIParameters::get('i', 0, pURIParameters::$INT);
$template = pURIParameters::get('tpl', '', pURIParameters::$STRING);

// Check for a ShortMenu and get the MainMenu
// Also check if the current User has access to the page
$menu = pMenu::getMenuInstance(!$userCheck->checkAccess('menu'));
$hasAccess = true;
$hasAccess = $menu->getMenuEntry()->checkLogin();

// Check if a StaticFile existst based on the ShortLink from Plugins-Content
$shortMenuFilePart = str_replace('/', '_DS_', substr($menu->getShortMenuName(), strpos($menu->getShortMenuName(), '/')));
if (!empty($shortMenuFilePart)) {
	$filePart = DIRECTORY_SEPARATOR.$i18n->getLanguageName().'-'.$menu->getMenuId().'-'.$sectionId.'-'.$pageOffset.'-'.$shortMenuFilePart.'.html';
	if (defined('ABS_STATIC_DIR')) {
		$_staticFile = ABS_STATIC_DIR.$filePart;
	} else {
		$_staticFile = ABS_TEMPLATE_DIR.'static'.$filePart;
	}
} else {
	$_staticFile = 'nonexistent';
}

// Check for standard static files
if (!file_exists($_staticFile)) {
	if ((integer)$postId > 0) {
		$filePart = DIRECTORY_SEPARATOR.$i18n->getLanguageName().'-'.$postId.'-'.$sectionId.'-'.$pageOffset.'-'.$template.'.html';
		if (defined('ABS_STATIC_DIR')) {
			$_staticFile = ABS_STATIC_DIR.$filePart;
		} else {
			$_staticFile = ABS_TEMPLATE_DIR.'static'.$filePart;
		}
	} else {
		$filePart = DIRECTORY_SEPARATOR.$i18n->getLanguageName().'-'.$menu->getMenuId().'-'.$sectionId.'-'.$pageOffset.'-'.$template.'.html';
		if (defined('ABS_STATIC_DIR')) {
			$_staticFile = ABS_STATIC_DIR.$filePart;
		} else {
			$_staticFile = ABS_TEMPLATE_DIR.'static'.$filePart;
		}
	}
}
if (!$hasAccess || $menu->isLoginRequested()) {
	$_staticFile = str_replace('.html', '-adm.html', $_staticFile);
}
$showStaticFile = true;

// Loged-In users should not have static sites (expect the Downloaders)
if (!$userCheck->showStaticFiles()) {
	$showStaticFile = false;
} else if ( $menu->isLoginRequested() && (@filesize($_staticFile) <= 0) ) {
	$showStaticFile = false;
} else if ( !file_exists($_staticFile) && (!SHOW_ONLY_STATIC || $menu->isLoginRequested()) ) {
	// if the file does not exists and it's configured to show also the dynamic page, show it...
	$showStaticFile = false;
}

if (pURIParameters::get('doGetStaticPages', false, pURIParameters::$BOOL)) {
	$showStaticFile = false;
}

if (pURIParameters::get('callDoCreateStaticSites', false, pURIParameters::$BOOL)) {
	$showStaticFile = false;
}

if (array_key_exists('HTTP_X_APPLICATION', $_SERVER) && ($_SERVER['HTTP_X_APPLICATION'] == 'delight cms plugin')) {
	$showStaticFile = false;
}

// Show the static-site
if ($showStaticFile) {
	if (file_exists($_staticFile)) {
		$last_mod = filemtime($_staticFile);
		header("Last-Modified: ".gmdate('D, d M Y H:i:s', $last_mod)." GMT");
		$html = file_get_contents($_staticFile);
		$html = $userCheck->replaceMenuAccessGroups($html);
		print($html);
	} else {
		header("HTTP/1.0 404 Not Found");
		if (($fp = @fopen(TEMPLATE_DIR.'404.html', 'rb')) !== false) {
			$error = fread($fp, filesize(TEMPLATE_DIR.'404.html'));
			$error = str_replace("[REQUESTED_URL]", "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'], $error);
			$error = str_replace("[BACK_LINK]", "<a href='javascript:history.back();'>".@$_SERVER['HTTP_REFERER']."</a>", $error);
			print(str_replace("[MAIN_DIR]", TEMPLATE_DIR, $error));
		} else {
			print("<h1>404 Not found</h1><p>The requested URL http://".$_SERVER['SERVER_NAME']."".$_SERVER['REQUEST_URI']." was not found on this server.</p><p><a href='javascript:history.back();'>back to the previous site</a></p><hr /><address>This error was occured while calling an unexistent page to the <b><i>delight software gmbh</i> CMS</b></address>");
		}
		@fclose($fp);
	}
} else { // Show the dynamic site
	header("Last-Modified: ".gmdate('D, d M Y H:i:s', time())." GMT");
	// Parse Template
	$tpl = new pParseTemplate();
	$tpl->setTemplate($template);
	$tpl->parseTemplate();

	// TODO: Check if this is really needed anylonger
	if (!$_DoNotShowAnyContent) { // defined in config.inc.php for letting a plugin define that no content will be shown
		$statistics = '';
		if (($_SERVER['SERVER_NAME'] != 'cms.localhost') && (PIWIK_SITE_ID != '')) {
			$statistics .= '<!-- Piwik -->';
			$statistics .= '<script type="text/javascript">';
			$statistics .= 'var pkBaseURL = "/pageanasta/";';
			$statistics .= 'document.write(unescape("%3Cscript src=\'" + pkBaseURL + "piwik.js\' type=\'text/javascript\'%3E%3C/script%3E"));';
			$statistics .= '</script><script type="text/javascript">';
			$statistics .= 'try {';
			$statistics .= 'var piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", '.PIWIK_SITE_ID.');';
			$statistics .= 'piwikTracker.trackPageView();';
			$statistics .= 'piwikTracker.enableLinkTracking();';
			$statistics .= '} catch( err ) {}';
			$statistics .= '</script><noscript><p><img src="/pageanasta/piwik.php?idsite='.PIWIK_SITE_ID.'" style="border:0" alt="" /></p></noscript>';
			$statistics .= '<!-- End Piwik Tag -->';
		}

		//$statistics = '<script type="text/javascript" src="/awstats_js/awstats_misc_tracker.js"></script><noscript><p><img src="/awstats_js/awstats_misc_tracker.js?nojs=y" height="0" width="0" border="0" style="display:none" alt="awstats" /></p></noscript>';

		$html = $tpl->getTemplateHtml();
		$html = str_replace('</body>', $statistics.'</body>', $html);

		if (!pURIParameters::get('doGetStaticPages', false, pURIParameters::$BOOL)) {
			$html = $userCheck->replaceMenuAccessGroups($html);
		}

		print(str_replace("[MAIN_DIR]", TEMPLATE_DIR, str_replace('[DATA_DIR]', '/v_'.DHP_VERSION.DATA_DIR, $html)));
	}
}

if ($session->get('loginhash', null) == null) {
	$session->cleanSessionData();
}

ob_end_flush();

