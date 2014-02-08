<?php
ignore_user_abort(true);

require_once("./config/config.inc.php");
require_once("./config/userconf.inc.php");
require_once("./php/class/pURIParameters.cls.php");
require_once("./php/class/pDatabaseConnection.cls.php");

// Check for a defined TEMPLATE_DIR
if (!defined('TEMPLATE_DIR')) {
	define('TEMPLATE_DIR', MAIN_DIR.'/template/');
	define('ABS_TEMPLATE_DIR', realpath(dirname($_SERVER['SCRIPT_FILENAME'])).DIRECTORY_SEPARATOR.TEMPLATE_DIR);
}

if (!defined('IMAGE_BORDER_WIDTH')) {
	define('IMAGE_BORDER_WIDTH', 0);
}
if (!defined('IMAGE_BORDER_SPACE')) {
	define('IMAGE_BORDER_SPACE', 0);
}
if (!defined('IMAGE_BORDER_COLOR')) {
	define('IMAGE_BORDER_COLOR', '000000');
}
if (!defined('IMAGE_BACKGROUND_COLOR')) {
	define('IMAGE_BACKGROUND_COLOR', 'FFFFFF');
}
if (!defined('IMAGE_TEXT_COLOR')) {
	define('IMAGE_TEXT_COLOR', '000000');
}
if (!defined('IMAGE_OPACITY_USEPIXEL')) {
	define('IMAGE_OPACITY_USEPIXEL', false);
}

// Main Variables
$width  = pURIParameters::get('width', 0, pURIParameters::$INT);
$height = pURIParameters::get('height', 0, pURIParameters::$INT);
$lang   = new pLanguage(pURIParameters::get('lang', 'de', pURIParameters::$STRING));
$showTitle = pURIParameters::get('title', 0, pURIParameters::$INT) == 1;
$font = realpath(dirname($_SERVER['SCRIPT_FILENAME'])).DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'Vera.ttf';
$fontsize = 9;

// Check for a Configuration
$conf = pURIParameters::get('conf', '{}', pURIParameters::$STRING);
try {
	$conf_hash = sha1($conf);
	$conf = json_decode($conf);
} catch (Exception $e) {
	$conf = new stdClass();
	$conf_hash = '';
}

// Check for Parameters in the configuration
$width = property_exists($conf, 'width') ? $conf->width : $width;
$height = property_exists($conf, 'height') ? $conf->height : $height;
$showTitle = property_exists($conf, 'title') ? $conf->title : $showTitle;
$stretch = property_exists($conf, 'stretch') ? $conf->stretch : true;
$opacity = property_exists($conf, 'opacity') ? $conf->opacity : 0;
$mask = property_exists($conf, 'mask') ? $conf->mask : '';
$blur = property_exists($conf, 'blur') ? $conf->blur : 0;
$maskFade = property_exists($conf, 'mask-fade') ? $conf->{'mask-fade'} : 0;
$maskColor = property_exists($conf, 'mask-background') ? $conf->{'mask-background'} : '';

// Prepare the MaskColor
if (strlen($maskColor) >= 6) {
	$maskColor = array(hexdec(substr($maskColor, 0, 2)), hexdec(substr($maskColor, 2, 2)), hexdec(substr($maskColor, 4, 2)));
} else if (strlen($maskColor) >= 3) {
	$maskColor = array(hexdec($maskColor[0].$maskColor[0]), hexdec($maskColor[1].$maskColor[1]), hexdec($maskColor[2].$maskColor[2]));
}

// Create the FileName
$imgParam = pURIParameters::get('img', 'none', pURIParameters::$STRING);
$tmpdir = ABS_IMAGE_DIR.'tmp'.DIRECTORY_SEPARATOR;
if (!file_exists(ABS_IMAGE_DIR.$imgParam)) {
	$imgParam = str_replace('.jpg', '.jpeg', $imgParam);
}
$real = ABS_IMAGE_DIR.$imgParam;

// Show the original image if width oand height is not set
// If only width or height is set calculate the other one
if ( ($width <= 0) && ($height <= 0)) {
	$size = getimagesize($real);
	header('Content-Type: '.$size['mime']);
	echo(file_get_contents($real));
	exit();

} else if ( ($width <= 0) || ($height <= 0)) {
	$size = getimagesize($real);
	if ($width <= 0) {
		$width = ($size[0] / $size[1]) * $height;
	} else if ($height <= 0) {
		$height = ($size[1] / $size[0]) * $width;
	}
}

// Calculate the Non-Streched Size
if (!$stretch) {
	$size = getimagesize($real);
	$h = ($size[1] / $size[0]) * $width;
	if ($h > $height) {
		$width = ($size[0] / $size[1]) * $height;
	} else {
		$height = $h;
	}
}

// The created Image
$image  = $tmpdir.$lang->short.'_'.$width.'x'.$height.'_'.($showTitle ? '1' : '0').(empty($conf_hash) ? '' : '_'.$conf_hash).'_'.$imgParam;

// Create Folders
if (!is_dir($tmpdir)) {
	mkdir($tmpdir, 0777, true);
	chmod($tmpdir, 0777);
}

// If the real image does not exists, show the Nonexistent-Image
if (!file_exists($real) || ($width <= 0) || ($width > 2000) || ($height <= 0) || ($height > 2000)) {
	showUnknown(($width > 2000), ($height > 2000));
}

// Check for a Recreate-Request
if (pURIParameters::get('rc', false, pURIParameters::$BOOLEAN) && file_exists($image)) {
	unlink($image);
}

if (file_exists($image)) {
	// Last-Change header for Cache
	header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($image)).' GMT' );

	// Show the Image
	$size = getimagesize($image);
	header('Content-Type: '.$size['mime']);
	//header('Content-Type: image/jpeg'); // JPEG has no transparency, but we need that...
	@readfile($image);
	exit();
}

// If the Image does not exists, set the LastModified header to the current Date
header('Last-Modified: '.gmdate('D, d M Y H:i:s', time()+3600).' GMT' );

// Get the Title if needed
$title = '';
if ($showTitle) {
	$db = pDatabaseConnection::getDatabaseInstance();
	$res = null;
	$sql = 'SELECT [imt.title] FROM [table.img],[table.imt] WHERE [imt.lang]='.$lang->id.' AND [imt.image]=[img.id] AND [img.image]=\''.mysql_real_escape_string(pURIParameters::get('img', 'none', pURIParameters::$STRING)).'\';';
	$db->run($sql, $res);
	if ($res->getFirst()) {
		$title = $res->{$db->getFieldName('imt.title')};
	}
	unset($res);
	unset($sql);
	if (empty($title)) {
		$showTitle = false;
	}
}

// Create the Image
$_preserveAlpha = false;
$usePng = false;
$size = getimagesize($real);
$src = null;
switch ($size[2]) {
	case IMAGETYPE_JPEG:
	case IMAGETYPE_JP2:
	case IMAGETYPE_JPEG2000:
	case IMAGETYPE_JPX:
		$src = imagecreatefromjpeg($real);
		break;
	case IMAGETYPE_GIF:
		$src = imagecreatefromgif($real);
		$_preserveAlpha = true;
		$usePng = true;
		break;
	case IMAGETYPE_PNG:
		$src = imagecreatefrompng($real);
		$_preserveAlpha = true;
		$usePng = true;
		break;
}

// If the Image is not based on a valid format, show an Unknown Image
if (empty($src)) {
	showUnknown();
}

// Prepare variables
$imgwidth = $width;
$imgheight = $height;
$textWidth = 0;
$textHeight = 0;
if ($showTitle && !empty($title)) {
	while (true) {
		$bbox = imagettfbbox($fontsize, 0, $font, $title);
		$textWidth  = abs($bbox[0]) + abs($bbox[4]);
		$textHeight = abs($bbox[1]) + abs($bbox[7]);
		if (($textWidth + 2) < $width) {
			$imgheight = $height - $textHeight - 2;
			$imgwidth = $width;
			break;
		}
		$fontsize--;
	}
}
$imgheight = $imgheight - (IMAGE_BORDER_WIDTH*2) - (IMAGE_BORDER_WIDTH>0 ? (IMAGE_BORDER_SPACE*2) : 0);
$imgwidth = $imgwidth - (IMAGE_BORDER_WIDTH*2) - (IMAGE_BORDER_WIDTH>0 ? (IMAGE_BORDER_SPACE*2) : 0);

// Create the small Image
$img = imagecreatetruecolor($width, $height);
if (function_exists('imageantialias')) {
	imageantialias($img, true);
	imageantialias($src, true);
}
if ($_preserveAlpha) {
	imagealphablending($img, false);
	imagesavealpha($img, true);
}

// Check for Image-Colors
$colorText = str_split(IMAGE_TEXT_COLOR, 2);
$colorBorder = str_split(IMAGE_BORDER_COLOR, 2);
$colorBackground = str_split(IMAGE_BACKGROUND_COLOR, 2);

$colBackground = imagecolorallocatealpha($img, hexdec($colorBackground[0]), hexdec($colorBackground[1]), hexdec($colorBackground[2]), $_preserveAlpha ? 127 : 0);
$colBorder = imagecolorallocatealpha($img, hexdec($colorBorder[0]), hexdec($colorBorder[1]), hexdec($colorBorder[2]), $_preserveAlpha ? 50 : 0);
$colText = imagecolorallocatealpha($img, hexdec($colorText[0]), hexdec($colorText[1]), hexdec($colorText[2]), 0);

// Imagefill ends in a "500 Internal Server-Error" on Apache-1.3 with PHP-5.2 on http://hoststar.ch/
if (IMAGE_BORDER_WIDTH > 0) {
	//imagefill($img, 0, 0, $colBorder);
	imagefilledrectangle($img, 0, 0, $width, $height, $colBorder);
	imagefilledrectangle($img, IMAGE_BORDER_WIDTH, IMAGE_BORDER_WIDTH, $width-IMAGE_BORDER_WIDTH-1, $height-IMAGE_BORDER_WIDTH-1, $colBackground);
} else {
	//imagefill($img, 0, 0, $colBackground);
	imagefilledrectangle($img, 0, 0, $width, $height, $colBackground);
}

// Blur the original image if so wanted
for ($i = 0; $i < $blur; $i++) {
	imagefilter($src, IMG_FILTER_GAUSSIAN_BLUR);
}

// Copy the original image to the small one
$x = 0;
$y = 0;
if (IMAGE_BORDER_WIDTH) {
	$x += IMAGE_BORDER_WIDTH;
	$y += IMAGE_BORDER_WIDTH;
	if (IMAGE_BORDER_SPACE) {
		$x += IMAGE_BORDER_SPACE;
		$y += IMAGE_BORDER_SPACE;
	}
}
imagecopyresampled($img, $src, $x, $y, 0, 0, $imgwidth, $imgheight, $size[0], $size[1]);

// Mask the Image
if (!empty($mask)) {
	imagealphablending($img, false);
	imagesavealpha($img, true);
	$mask_img = imagecreatetruecolor($imgwidth, $imgheight);
	$img_alpha = imagecreatetruecolor($imgwidth, $imgheight);
	imagealphablending($img_alpha, false);
	imagesavealpha($img_alpha, true);

	if (IMAGE_OPACITY_USEPIXEL) {
		$mask_color = imagecolorallocate($img, 255, 0, 255);
	} else {
		$mask_black = imagecolorallocate($img, 255, 0, 255);
		$mask_color = imagecolorallocate($img, 0, 255, 0);
		imagecolortransparent($mask_img, $mask_color);
		imagefill($mask_img, 0, 0, $mask_black);
		imagefill($img_alpha, 0, 0, imagecolorallocatealpha($img_alpha, $maskColor[0], $maskColor[1], $maskColor[2], 127));
	}

	switch (strtolower($mask)) {
		case 'ellipse':
			imagefilledellipse($mask_img, $imgwidth/2, $imgheight/2, $imgwidth, $imgheight, $mask_color);
			break;
		case 'rect':
		default:
			// make a Rounded-Rect here
			imagefilledellipse($mask_img, $imgwidth/2, $imgheight/2, $imgwidth, $imgheight, $mask_color);
	}

	if (IMAGE_OPACITY_USEPIXEL) {
		for ($x = 0; $x < $imgwidth; $x++) {
			for ($y = 0; $y < $imgheight; $y++) {
				$rgb_mask = @imagecolorat($mask_img, $x, $y);
				$rgb = @imagecolorat($img, $x, $y);
				$r = ($rgb>>16)&0xFF;
				$g = ($rgb>>8)&0xFF;
				$b = ($rgb)&0xFF;
				if ($rgb_mask == $mask_color) {
					imagesetpixel($img_alpha, $x, $y, imagecolorallocatealpha($img_alpha, $r, $g, $b, 0));
				} else {
					imagesetpixel($img_alpha, $x, $y, imagecolorallocatealpha($img_alpha, 255, 0, 255, 127));
				}
			}
		}
	} else {
		imagecolortransparent($img, $mask_color);
		imagecopymerge($img, $mask_img, 0, 0, 0, 0, $imgwidth, $imgheight, 100);
		imagecolortransparent($img, $mask_black);
		imagecopymerge($img_alpha, $img, 0, 0, 0, 0, $imgwidth, $imgheight, 100);
	}

	imagedestroy($mask_img);
	imagedestroy($img);
	$img = $img_alpha;
	$usePng = true;

	// fade the mask
	settype($maskFade, 'integer');
	if ($maskFade > $imgwidth/2) $maskFade = $imgwidth/2;
	if ($maskFade > $imgheight/2) $maskFade = $imgheight/2;
	if ($maskFade > 0) {
		imagealphablending($img, false);
		$mask_black = imagecolorallocate($img, 255, 0, 255);
		$mask_color = imagecolorallocate($img, 0, 255, 0);
		$alpha = $maskFade/100;

		$img_alpha = imagecreatetruecolor($imgwidth, $imgheight);
		imagealphablending($img_alpha, false);
		imagesavealpha($img_alpha, true);
		if (is_array($maskColor)) {
			imagefill($img_alpha, 0, 0, imagecolorallocatealpha($img_alpha, $maskColor[0], $maskColor[1], $maskColor[2], 127));
		} else {
			imagefill($img_alpha, 0, 0, imagecolorallocatealpha($img_alpha, 255, 255, 255, 127));
		}

		for ($i = 0; $i < 100; $i++) {
			$mask_img = imagecreatetruecolor($imgwidth, $imgheight);
			imagecolortransparent($mask_img, $mask_color);
			imagefill($mask_img, 0, 0, $mask_black);

			switch (strtolower($mask)) {
				case 'ellipse':
					imagefilledellipse($mask_img, $imgwidth/2, $imgheight/2, $imgwidth-($i*$alpha), $imgheight-($i*$alpha), $mask_color);
					break;
				case 'rect':
				default:
					// make a Rounded-Rect here
					imagefilledellipse($mask_img, $imgwidth/2, $imgheight/2, $imgwidth-($i*$alpha), $imgheight-($i*$alpha), $mask_color);
			}
			imagecolortransparent($img, $mask_color);
			imagecopymerge($img, $mask_img, 0, 0, 0, 0, $imgwidth, $imgheight, 100);
			imagecolortransparent($img, $mask_black);
			imagecopymerge($img_alpha, $img, 0, 0, 0, 0, $imgwidth, $imgheight, ($i+1));
		}
		imagedestroy($mask_img);
		imagedestroy($img);
		$img = $img_alpha;
	}
}

// Make the image transparent
settype($opacity, 'integer');
if (IMAGE_OPACITY_USEPIXEL) {
	$opacity = $opacity%127;
} else {
	$opacity = $opacity%100;
}
if ($opacity > 0) {
	$img_alpha = imagecreatetruecolor($imgwidth, $imgheight);
	if (IMAGE_OPACITY_USEPIXEL && !empty($maskColor)) {
		imagealphablending($img_alpha, false);
		imagesavealpha($img_alpha, true);

		for ($x = 0; $x < $imgwidth; $x++) {
			for ($y = 0; $y < $imgheight; $y++) {
				$rgb = @imagecolorat($img, $x, $y);
				$r = ($rgb>>16)&0xFF;
				$g = ($rgb>>8)&0xFF;
				$b = ($rgb)&0xFF;
				if (($r==255 && $g==0 && $b==255) || ($r==$maskColor[0] && $g==$maskColor[1] && $b==$maskColor[2])) {
					imagesetpixel($img_alpha, $x, $y, imagecolorallocatealpha($img_alpha, 255, 0, 255, 127));
				} else {
					imagesetpixel($img_alpha, $x, $y, imagecolorallocatealpha($img_alpha, $r, $g, $b, $opacity));
				}
			}
		}
	} else {
		imagealphablending($img_alpha, false);
		imagesavealpha($img_alpha, true);
		if (is_array($maskColor)) {
			imagefill($img_alpha, 0, 0, imagecolorallocatealpha($img_alpha, $maskColor[0], $maskColor[1], $maskColor[2], 127));
		} else {
			imagefill($img_alpha, 0, 0, imagecolorallocatealpha($img_alpha, 255, 255, 255, 127));
		}
		imagecopymerge($img_alpha, $img, 0, 0, 0, 0, $imgwidth, $imgheight, $opacity);
	}
	imagedestroy($img);
	$img = $img_alpha;
}

// Print the Title
if ($showTitle) {
	$x = ($width/2) - ($textWidth/2);
	$y = $textHeight + $imgheight + IMAGE_BORDER_WIDTH + (IMAGE_BORDER_WIDTH>0 ? IMAGE_BORDER_SPACE : 0);
	imagettftext($img, $fontsize, 0, $x, $y, $colText, $font, encodeTextForGD($title));
}

if ($usePng) {
	header('Content-Type: image/png'); // we need Transparency on masked images
	imagepng($img, $image);
	imagepng($img);

} else {
	header('Content-Type: '.$size['mime']);
	switch ($size[2]) {
		case IMAGETYPE_JPEG:
		case IMAGETYPE_JP2:
		case IMAGETYPE_JPEG2000:
		case IMAGETYPE_JPX:
			imagejpeg($img, $image);
			imagejpeg($img);
			break;
		case IMAGETYPE_GIF:
		case IMAGETYPE_PNG:
			imagepng($img, $image);
			imagepng($img);
			break;
	}
}

chmod($image, 0777);
imagedestroy($img);
exit();

/**
 * Show the UnknownImage Image
 * and die...
 *
 * @param boolean $w true if the Width-Factor is to big
 * @param boolean $h true if the height-factor is to big
 */
function showUnknown($w, $h) {
	header('Content-Type: image/jpeg');
	$img = imagecreatefrompng(ABS_IMAGE_DIR.'..'.DIRECTORY_SEPARATOR.'unknownImage.png');

	if ($w || $h) {
		$text = imagecolorallocate($img, 0, 0, 0);
		$bg = imagecolorallocatealpha($img, 255, 0, 0, 100);
		$points = array(
			9, 16,
			imagesx($img)-8, 16,
			imagesx($img)-8, imagesy($img)-28,
			imagesx($img)-32, imagesy($img)-28,
			imagesx($img)-32, imagesy($img)-4,
			9, imagesy($img)-4
		);
		imagefilledpolygon($img, $points, 6, $bg);
	}
	if ($w) {
		imagestring($img, 2, 12, 17, 'Image cannot', $text);
		imagestring($img, 2, 12, 27, 'be wider than', $text);
		imagestring($img, 2, 12, 37, '2000 pixel', $text);
	}
	if ($h) {
		imagestring($img, 2, 12, 52, 'Image cannot', $text);
		imagestring($img, 2, 12, 62, 'be higher than', $text);
		imagestring($img, 2, 12, 72, '2000 pixel', $text);
	}
	imagejpeg($img);
	imagedestroy($img);
	die();
}

function encodeTextForGD($string) {
	$back = '';
	$string = utf8_decode($string);
	for ($i = 0; $i < strlen($string); $i++) {
		$char = $string{$i};
		if (ord($char) > 127) {
			$back .= '&#x'.bin2hex($char).';';
		} else {
			$back .= $char;
		}
	}
	return utf8_encode($back);
}
