<?php
ignore_user_abort(true);

require_once("./config/config.inc.php");
require_once("./config/userconf.inc.php");
require_once("./php/class/pURIParameters.cls.php");

// Check for a defined TEMPLATE_DIR
if (!defined('TEMPLATE_DIR')) {
	define('TEMPLATE_DIR', MAIN_DIR.'/template/');
	define('ABS_TEMPLATE_DIR', realpath(dirname($_SERVER['SCRIPT_FILENAME'])).DIRECTORY_SEPARATOR.TEMPLATE_DIR);
}

if (!defined('FONT_OTF_REGULAR'))        define('FONT_OTF_REGULAR', 'regular.otf');
if (!defined('FONT_TTF_REGULAR'))        define('FONT_TTF_REGULAR', 'regular.ttf');
if (!defined('FONT_OTF_BOLD'))           define('FONT_OTF_BOLD', 'bold.otf');
if (!defined('FONT_TTF_BOLD'))           define('FONT_TTF_BOLD', 'bold.ttf');
if (!defined('FONT_OTF_ITALIC'))         define('FONT_OTF_ITALIC', 'italic.otf');
if (!defined('FONT_TTF_ITALIC'))         define('FONT_TTF_ITALIC', 'italic.ttf');
if (!defined('FONT_OTF_BOLDITALIC'))     define('FONT_OTF_BOLDITALIC', 'bold_italic.otf');
if (!defined('FONT_TTF_BOLDITALIC'))     define('FONT_TTF_BOLDITALIC', 'bold_italic.ttf');
if (!defined('FONT_OTF_SEMIBOLD'))       define('FONT_OTF_SEMIBOLD', 'semibold.otf');
if (!defined('FONT_TTF_SEMIBOLD'))       define('FONT_TTF_SEMIBOLD', 'semibold.ttf');
if (!defined('FONT_OTF_SEMIBOLDITALIC')) define('FONT_OTF_SEMIBOLDITALIC', 'semibold_italic.otf');
if (!defined('FONT_TTF_SEMIBOLDITALIC')) define('FONT_TTF_SEMIBOLDITALIC', 'semibold_italic.ttf');

// Style and Fontname
$style = strtolower(pURIParameters::get('style', 'regular', pURIParameters::$STRING));
$font  = strtolower(pURIParameters::get('font',  null,      pURIParameters::$STRING));
$absolute = ABS_TEMPLATE_DIR.DIRECTORY_SEPARATOR.'fonts'.DIRECTORY_SEPARATOR;
$relative = TEMPLATE_DIR.'/fonts/';
$file = null;

// Check the Fontname and use it if it exists. If not, check for the default one
if (!empty($font) && file_exists($absolute.$font.'_'.$style.'.otf')) {
	$file = $absolute.$font.'_'.$style.'.otf';
	header('Content-Type: application/x-font-otf');

} else if (!empty($font) && file_exists($absolute.$font.'_'.$style.'.ttf')) {
	$file = $absolute.$font.'_'.$style.'.ttf';
	header('Content-Type: application/x-font-ttf');

} else {
	switch ($style) {
		case 'regular':
		case 'bold':
		case 'italic':
		case 'bolditalic':
		case 'semibold':
		case 'semibolditalic':
			break;
		default:
			$style = 'regular';
	}
	$style = strtoupper($style);
	if (file_exists($absolute.constant('FONT_OTF_'.$style))) {
		$file = $absolute.constant('FONT_OTF_'.$style);
		header('Content-Type: application/x-font-otf');

	} else if (file_exists($absolute.constant('FONT_TTF_'.$style))) {
		$file = $absolute.constant('FONT_TTF_'.$style);
		header('Content-Type: application/x-font-ttf');
	}
}

// Exit if no Fontfile exists
if (empty($file)) {
	exit();
}

// Show the File
header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($file)).' GMT' );
@readfile($file);

?>
