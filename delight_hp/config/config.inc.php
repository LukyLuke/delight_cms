<?php
/*
	The Configuration-File
*/

// we set the right TZ after we have the language - only for logfiles and things like this.
if (function_exists('date_default_timezone_exists')) {
	date_default_timezone_set('Europe/Zurich');
} else {
	ini_set('date.timezone', 'Europe/Zurich');
}

// Include the PHPIDS (Intrusion-Detection) if available
//@require_once('PHPIDS.php');

// The Table-Prefix for DatabaseTables
$tablePrefix = 'dhp';

define('WEB_ROOT', '/');
define('MAIN_DIR', str_replace(DIRECTORY_SEPARATOR, '/', str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, dirname(DIRECTORY_SEPARATOR . str_replace($_SERVER['DOCUMENT_ROOT'], '', (substr(dirname($_SERVER['SCRIPT_FILENAME']), -3) == 'php') ? dirname(dirname($_SERVER['SCRIPT_FILENAME'])) : dirname($_SERVER['SCRIPT_FILENAME'])) . DIRECTORY_SEPARATOR. 'dirname') . DIRECTORY_SEPARATOR)));
define('ABS_MAIN_DIR', realpath((substr(dirname($_SERVER['SCRIPT_FILENAME']), -3)=='php') ? dirname(dirname($_SERVER['SCRIPT_FILENAME'])) : dirname($_SERVER['SCRIPT_FILENAME'])).DIRECTORY_SEPARATOR );
define('ABS_PHP_DIR', ABS_MAIN_DIR.'php'.DIRECTORY_SEPARATOR);
define('ABS_CLASSES_DIR', ABS_MAIN_DIR.'php'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR);
define('ABS_ADMIN_DIR', ABS_MAIN_DIR.'admin'.DIRECTORY_SEPARATOR);
define('IMAGE_DIR', MAIN_DIR.'images/page/');
define('ABS_IMAGE_DIR', ABS_MAIN_DIR.'images'.DIRECTORY_SEPARATOR.'page'.DIRECTORY_SEPARATOR);

// Editor Languages needs more checking. currently only de and en are working really
define('WORKING_EDITOR_LANGUAGE', 'de,en');
define('DEFAULT_EDITOR_LANGUAGE', 'en');

// Set this Variable in a Plugin, which should have no output (HTML will not be printed by index.php)
$_DoNotShowAnyContent = false;

// Define all 'HowToDisplayText' Plugins
// The Plugin-Files must be stored under php/class/PLUGINNAME.cls.php
// The Classnames are Case-Sensitive. UPPERCASE Names are preffered...
define('_textPlugins', 'MENU,LANG,TEXT,SCREENSHOT,DOWNLOADS,NEWS,SIMPLESHOP,SEARCH,IFRAME,LOGIN,GROUP,GLOBALTEXT');
//define('NEW_ADMIN_EDITOR', '1000,1100,1200,1300,1400,1700,2000,2100');

// OBSOLETE Define all Plugins which sends JSON-Objects instead normal Text while saveing an TextBlock
//define('JSON_SAVE_BLOCKS', 'FORMULAR');

// Anything
define('DHP_VERSION', 2011112503);
define('DHP_PRODUCT',     'delight cms');
define('SESSION_TIMEOUT', 3600*2);

// DownloadLog and link to MIME-Types Database
define('LOG_DOWNLOAD', true);
define('MIMELNK_PATH', dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'mimelnk'.DIRECTORY_SEPARATOR);

// Default AdminUser
define('DEFAULT_ADMIN_NUMBER', 0);
define('DEFAULT_ADMIN_USER', 'admin');
define('DEFAULT_ADMIN_PASSWORD', 'cmsadmin');
define('DEFAULT_ADMIN_ACCESS', (pow(2,32) - 1)); // RGT_FULLADMIN
define('MAX_AUTOLOGIN_DAYS', 30);

// define Rights
define('RGT_DOWNLOAD',   pow(2,0));
define('RGT_NEWS',       pow(2,1));
define('RGT_STATICSITE', pow(2,2));
define('RGT_MENU',       pow(2,3));
define('RGT_CONTENT',    pow(2,4));
define('RGT_USERS',      pow(2,5));
define('RGT_IMAGES',     pow(2,6));
define('RGT_PROGRAMS',   pow(2,7));
define('RGT_CONFIG',     pow(2,8));
define('RGT_BACKUP',     pow(2,9));
define('RGT_STATISTIC',  pow(2,10));
define('RGT_LANGUAGES',  pow(2,11));
define('RGT_SIMPLESHOP', pow(2,12));
define('RGT_GLOBALTEXT', pow(2,13));
//define('RGT_IFRAME',     pow(2,14));

define('RGT_FULLADMIN',  (PHP_INT_MAX > (pow(2,32)-1) ? pow(2,32)-1 : PHP_INT_MAX) );


// User must not have a language-Access-Entry for this sections
$CheckRightLang = array(
	RGT_STATICSITE,
	RGT_PROGRAMS,
	RGT_IMAGES,
	RGT_USERS,
	RGT_BACKUP,
	RGT_DOWNLOAD,
	RGT_LANGUAGES,
	RGT_STATISTIC,
	RGT_SIMPLESHOP
);

/* ATTENTION: The Section from 0 until 10000 is reserved for official plugins and actions */
// Define the Administration-Actions
	define('ADM_CREATE',      '100');
	define('ADM_CONTENT',     '100');
	define('ADM_EDIT',        '101');
	define('ADM_DELETE',      '102');
	define('ADM_MVUP',        '103');
	define('ADM_MVDOWN',      '104');
	define('ADM_MENU',        '200');
	define('ADM_MENU_CREATE', '200');
	define('ADM_MENU_EDIT',   '201');
	define('ADM_MENU_DELETE', '202');
	define('ADM_MENU_MVUP',   '203');
	define('ADM_MENU_MVDOWN', '204');
	define('ADM_MENU_LINK',   '205');
	define('ADM_MENU_VISIBILITY', '206');

	define('ADM_IMAGES',     '1000');
	define('ADM_DOWNLOAD',   '1100');
	define('ADM_PROGRAMS',   '1100');
	define('ADM_USERS',      '1200');
 	define('ADM_LANGUAGES',  '1300');
	define('ADM_NEWS',       '1400');
//	define('ADM_STATISTIC',  '1500');
 	define('ADM_CONFIG',     '1600');
	define('ADM_STATICSITE', '1700');
 	define('ADM_GLOBALTEXT', '1800');
//	define('ADM_CONFIG',     '1900');
	define('ADM_IFRAME',     '2000');
//	define('ADM_REGDATA',   '10000');

// Aditional Plugins
$_ADITIONAL_PLUGINS = array(
	'SIMPLESHOP' => 100000
);

// Define an Array with all ImageFile-Extensions (Which Files should be accepted as Images)
$ImageExtensions = array('jpg', 'jpeg', 'jpe', 'gif', 'png', 'swf');

require_once(dirname(__FILE__).'/userconf.inc.php');
require_once(dirname(__FILE__).'/globals.inc.php');
require_once(dirname(__FILE__).'/database.conf.php');

// For debuging with Firebug Extension and FirePHP
if ($_SERVER['SERVER_NAME'] == 'cms.localhost') {
	define('RELEASE', false);
}
if (!defined('RELEASE')) {
	define('RELEASE', true);
}

/// Plugin-Constants
// TEXT
define('PLG_TEXT_TPL',     'TEXT');
define('PLG_TEXT_METHOD',  'SHOW_TEXT_DETAILS');

// SCREENSHOTS
define('PLG_IMAGE_TPL',    'SCREENSHOT');
define('PLG_IMAGE_METHOD', 'BIG_IMAGE');
define('PLG_IMAGEBLANKLIST_TPL',    'SCREENSHOT');
define('PLG_IMAGEBLANKLIST_METHOD', 'INSERT_IMAGE_LIST');

// NEWS
define('PLG_NEWS_TPL',     'NEWS');
define('PLG_NEWS_METHOD',  'SHOW_NEWS_DETAILS');
define('PLG_NEWS_FEED',    'SHOW_NEWS_FEED');
define('PLG_NEWS_FEEDCONT','SHOW_NEWS_FEEDCONTAINER');

// DOWNLOADS
define('PLG_PROGRAM_TPL',     'DOWNLOADS');
define('PLG_PROGRAM_METHOD',  'SHOW_PROGRAM_DETAILS');
define('PLG_DLOADREG_TPL',    'DOWNLOADS');
define('PLG_DLOADREG_METHOD', 'SHOW_DLOADREG_DETAILS');


/**
 * Autloloading Classes and Interfaces
 *
 * @param String $name Name of the Class or Interface to load
 */
function __autoload($name) {
	if (substr($name, 0, 3) == 'adm') {
		$loaded = tryLoadClass($name, ABS_ADMIN_DIR);
	} else {
		$loaded = tryLoadClass($name, ABS_PHP_DIR);
	}

	if (!$loaded) {
		throw new Exception('Class not Found: '.$name, 0);
	}
}

/**
 * Try to load a Class which can be in any Subdirectory of the given one
 *
 * @param $class String Name of the Class to load
 * @param $directory String The Root-Directory to look for the Class
 * @return boolean If the class was found and loaded
 */
function tryLoadClass($class, $directory) {
	global $tablePrefix,$DBTables,$DBFields;
	if (@is_dir($directory)) {
		if (file_exists($directory.$class.'.cls.php')) {
			require_once($directory.$class.'.cls.php');
			return true;
		} else if (file_exists($directory.$class.'.iface.php')) {
			require_once($directory.$class.'.iface.php');
			return true;
		} else {
			foreach (scandir($directory) as $dir) {
				if (($dir{0} != '.') && tryLoadClass($class, $directory.$dir.DIRECTORY_SEPARATOR)) {
					return true;
				}
			}
		}
	}
	return false;
}


function fireLog($arg, $label='') {
	if (!RELEASE) {
		if (!class_exists('FirePHP', false)) {
			if (file_exists('../FirePHPCore/FirePHP.class.php')) {
				require_once('../FirePHPCore/FirePHP.class.php');
			} else {
				require_once('../../FirePHPCore/FirePHP.class.php');
			}
		}
		$firephp = FirePHP::getInstance(true);
		$firephp->log($arg, $label);
	}
}
