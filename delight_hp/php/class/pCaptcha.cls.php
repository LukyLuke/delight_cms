<?php
/**
 * Captcha-Class
 *
 * @author Lukas Zurschmiede <l.zurschmiede@delightsoftware.com>
 * @copyright (c) 2009 by delight software gmbh, switzerland
 *
 * @package delightWebProduct
 * @version 1.0
 */

$DBTables['captcha'] = $tablePrefix.'_captcha_store';
$DBFields['captcha'] = array(
	'uid' => 'uid',
	'string' => 'captcha_string'
);

class pCaptcha implements iUpdateIface {
	const MODULE_VERSION = 2009040800;
	
	private $font;
	private $numChars;
	private $validChars;
	private $captchaUid;
	private $captchaString;
	private $backgroundColor;
	private $noiseColor;
	private $fontColor;
	private $fontAngle;
	
	public function __construct($numChars=5, $fontFile=null) {
		$this->updateModule();
		if (is_numeric($numChars)) {
			$this->numChars = (int)$numChars;
		} else {
			$this->numChars = 5;
		}
		if (file_exists($fontFile)) {
			$this->font = $fontFile;
		} else {
			$this->font = '';
		}
		$this->validChars = implode('', array_merge(range(0,9), range('a', 'z')));
		$this->backgroundColor = array(255, 255, 255);
		$this->fontColor = array(190, 90, 140);
		$this->noiseColor = array(200, 120, 180);
		$this->fontAngle = 0;
	}
	
	/**
	 * Set the Background-Color
	 * Use a HEX-Style like in HTML -> '#RRGGBB'
	 * 
	 * @param string $backgroundColor The Background-Color as HTML-HEX
	 */
	public function setBackgroundColor($backgroundColor) {
		if ( ($backgroundColor{0} == '#') && (strlen($backgroundColor) == 7) ) {
			$this->backgroundColor = sscanf($backgroundColor, '#%2x%2x%2x');
		} else if (strlen($backgroundColor) == 6) {
			$this->backgroundColor = sscanf($backgroundColor, '%2x%2x%2x');
		}
	}

	/**
	 * Set the Font-Color
	 * Use a HEX-Style like in HTML -> '#RRGGBB'
	 * 
	 * @param string $fontColor The Font-Color as HTML-HEX
	 */
	public function setFontColor($fontColor) {
		if ( ($fontColor{0} == '#') && (strlen($fontColor) == 7) ) {
			$this->fontColor = sscanf($fontColor, '#%2x%2x%2x');
		} else if (strlen($fontColor) == 6) {
			$this->fontColor = sscanf($fontColor, '%2x%2x%2x');
		}
	}

	/**
	 * Set the Noise-Color
	 * Use a HEX-Style like in HTML -> '#RRGGBB'
	 * 
	 * @param string $noiseColor The Noise-Color as HTML-HEX
	 */
	public function setNoiseColor($noiseColor) {
		if ( ($noiseColor{0} == '#') && (strlen($noiseColor) == 7) ) {
			$this->noiseColor = sscanf($noiseColor, '#%2x%2x%2x');
		} else if (strlen($noiseColor) == 6) {
			$this->noiseColor = sscanf($noiseColor, '%2x%2x%2x');
		}
	}
	
	public function setFontAngle($angle) {
		if (is_numeric($angle)) {
			$this->fontAngle = $angle;
		}
	}
	
	/**
	 * Set the TrueType-FontFile
	 *
	 * @param string $fontFile Absolute Path to the FontFile
	 */
	public function setFontFile($fontFile) {
		if (is_file($fontFile)) {
			$this->font = $fontFile;
		}
	}
	
	/**
	 * String of Chars to use for the Captcha
	 *
	 * @param string $chars String of Chars
	 */
	public function setValidChars($chars) {
		$this->validChars = $chars;
	}
	
	/**
	 * Number of Chars to show in Captcha
	 *
	 * @param int $numChars Number of Chars to show
	 */
	public function setNumChars($numChars) {
		if (is_numeric($numChars)) {
			$this->numChars = (int)$numChars;
			$this->generateCode();
		}
	}
	
	/**
	 * Check for valid String by the given Captcha-UID
	 *
	 * @param string $uid The Captcha UID
	 * @param string $string The User-Entered String
	 * @return boolean
	 */
	public function validateCaptcha($uid, $string) {
		$this->captchaString = '';
		if (!empty($uid)) {
			$this->loadStringByUID($uid);
			if ( !empty($string) && ($this->captchaString == $string) ) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Return the current Captcha-UID
	 * Use this for the hidden InputField
	 *
	 * @return string The Captcha UID
	 */
	public function getCaptchaUID() {
		return $this->captchaUid;
	}
	
	/**
	 * Create and Return the Captcha-Image as HTML Image-Tag
	 * The ImageTag uses src="data:..." to show the Image
	 *
	 * @param int $width Captcha-Width
	 * @param int $height Captcha-Height
	 * @return string IMG-HTML-Tag
	 */
	public function createCaptcha($width=120, $height=40) {
		$this->captchaUid = '';
		$this->generateCode();
		if (empty($this->captchaUid)) {
			$this->captchaUid = pGUID::getGUID();
			$db = pDatabaseConnection::getDatabaseInstance();
			$res = null;
			$sql = 'INSERT INTO [table.captcha] ([field.captcha.uid],[field.captcha.string]) VALUES (\''.$this->captchaUid.'\',\''.$this->captchaString.'\');';
			$db->run($sql, $res);
		}
		if (empty($this->font)) {
			throw new DWPException('Captcha: No Fontfile defined', 80);
		}
		$imageData = $this->createImage($width, $height);
		
		// IE doesn't support Inline-Images
		if (ereg('MSIE', $_SERVER['HTTP_USER_AGENT']) && !ereg('Opera', $_SERVER['HTTP_USER_AGENT'])) {
			if (!defined('CAPTCHA_IMAGE_PATH_REL') || !defined('CAPTCHA_IMAGE_PATH_ABS')) {
				throw new DWPException('Captcha: Configuration not setup correctly. Need Constantes: CAPTCHA_IMAGE_PATH_REL, CAPTCHA_IMATE_PATH_ABS');
			}
			if (!is_dir(CAPTCHA_IMAGE_PATH_ABS)) {
				throw new DWPException('Captcha: Installation not setup correctly. Path for storing Captcha-Images (CAPTCHA_IMAGE_PATH_ABS) does not exist.');
			}
			$written = file_put_contents(CAPTCHA_IMAGE_PATH_ABS.$this->captchaUid.'.jpeg', base64_decode($imageData));
			if ($written <= 0) {
				throw new DWPException('Captcha: Installation not setup correctly. Path for storing Captcha-Images (CAPTCHA_IMAGE_PATH_ABS) seams not to be writable.');
			}
			chmod(CAPTCHA_IMAGE_PATH_ABS.$this->captchaUid.'.jpeg', 0666);
			return '<img src="'.CAPTCHA_IMAGE_PATH_REL.$this->captchaUid.'.jpeg" style="width:'.$width.'px;height:'.$height.'px;" alt="Captcha" />';
		} else {
			return '<img src="data:image/jpeg;base64,'.$imageData.'" style="width:'.$width.'px;height:'.$height.'px;" alt="Captcha" />';
		}
	}
	
	/**
	 * Create the Captcha as base64-Encoded Data
	 *
	 * @param int $width Captcha-Width
	 * @param int $height Captcha-Height
	 * @return string base64-Encoded Image-Data
	 */
	private function createImage($width, $height) {
		
		$img = imagecreate($width, $height);
		$bg = imagecolorallocate($img, $this->backgroundColor[0], $this->backgroundColor[1], $this->backgroundColor[2]);
		$fg = imagecolorallocate($img, $this->fontColor[0], $this->fontColor[1], $this->fontColor[2]);
		$noise = imagecolorallocate($img, $this->noiseColor[0], $this->noiseColor[1], $this->noiseColor[2]);
		imagefill($img, 0, 0, $bg);
		
		// Create noise
		for ($i = 0; $i < ($width*$height)/3; $i++) {
			imagefilledellipse($img, mt_rand(0, $width), mt_rand(0, $height), 1, 1, $noise);
		}
		for ($i = 0; $i < ($width*$height)/150; $i++) {
			imageline($img, mt_rand(0, $width), mt_rand(0, $height), mt_rand(0, $width), mt_rand(0, $height), $noise);
		}
		
		// Create the Text
		$fact = 1;
		do {
			$fontSize = $height * $fact;
			$box = $this->calculateTextBox($fontSize);
			$x = $box['left'] + ($width  / 2) - ($box['width']  / 2);
			$y = $box['top']  + ($height / 2) - ($box['height'] / 2);
			$fact -= 0.05;
			if ($fact <= 0.1) {
				break;
			}
		} while (($box['width'] >= ($width-5)) || ($box['height'] >= ($height-5)));
		imagettftext($img, $fontSize, $this->fontAngle, $x, $y, $fg, $this->font , $this->captchaString);
		
		// Get the ImageCode
		ob_start();
		imagejpeg($img);
		$image = ob_get_clean();
		imagedestroy($img);
		return base64_encode($image);
	}
	
	/**
	 * Calculate the TextBox position with imagettfbbox
	 *
	 * @param double $fontSize FontSize
	 * @return array
	 */
	private function calculateTextBox($fontSize) {
		$bbox = imagettfbbox($fontSize, $this->fontAngle, $this->font, $this->captchaString);
		
		$minX = min(array($bbox[0], $bbox[2], $bbox[4], $bbox[6]));
		$maxX = max(array($bbox[0], $bbox[2], $bbox[4], $bbox[6]));
		$minY = min(array($bbox[1], $bbox[3], $bbox[5], $bbox[7]));
		$maxY = max(array($bbox[1], $bbox[3], $bbox[5], $bbox[7]));
		
		return array(
			'left'   => abs($minX),
			'top'    => abs($minY),
			'width'  => $maxX - $minX,
			'height' => $maxY - $minY,
			'box'    => $bbox
		);
	}
	
	/**
	 * Calculate the Font-Box by given Font-Size
	 * See http://ch2.php.net/manual/en/function.imagettfbbox.php
	 * for the ReturnValue
	 * 
	 * @param double $fontSize The FontSize
	 * @return array
	 */
	private function calcFontPosition($fontSize) {
		
	}
	
	/**
	 * Create a new Captcha-Code and load the UID if one is already in the Database
	 *
	 */
	private function generateCode() {
		$code = '';
		for ($i = 0; $i < $this->numChars; $i++) {
			$code .= substr($this->validChars, mt_rand(0, strlen($this->validChars)-1), 1);
		}
		$this->captchaString = $code;
		$this->loadUIDByString($this->captchaString);
		unset($code);
	}
	
	/**
	 * Load the CaptchaUID from given Captcha-String
	 *
	 * @param string $string The Captcha-String to load the UID from
	 */
	private function loadUIDByString($string) {
		$string = preg_replace('/[^'.$this->validChars.']/smi', '', $string);
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = 'SELECT [captcha.uid] FROM [table.captcha] WHERE [captcha.string]=\''.$string.'\';';
		$db->run($sql, $res);
		if ($res->getFirst()) {
			$this->captchaUid = $res->{$db->getFieldName('captcha.uid')};
		}
		unset($res);
	}
	
	/**
	 * Load the Captcha-String by the given UID
	 *
	 * @param string $uid The UID to load the String from
	 */
	private function loadStringByUID($uid) {
		$uid = preg_replace('/[^a-z0-9-]/smi', '', $uid);
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = 'SELECT [captcha.string] FROM [table.captcha] WHERE [captcha.uid]=\''.$uid.'\';';
		$db->run($sql, $res);
		if ($res->getFirst()) {
			$this->captchaString = $res->{$db->getFieldName('captcha.string')};
		}
		unset($res);
	}
	
	/**
	 * Update the Module-DB
	 *
	 */
	public function updateModule() {
		$db = pDatabaseConnection::getDatabaseInstance();
		if ($db->connectSuccessfully()) {
			$version = $db->getModuleVersion('pCaptcha');
			$res = null;

			// Check if we need an Update
			if (self::MODULE_VERSION > $version) {
				// initial
				if ($version <= 0) {
					$sql = 'CREATE TABLE [table.captcha] ('.
					' [field.captcha.uid] VARCHAR(80) NOT NULL default \'\','.
					' [field.captcha.string] VARCHAR(10) NOT NULL default \'\','.
					' UNIQUE KEY [field.captcha.uid] ([field.captcha.uid])'.
					');';
					$db->run($sql, $res);
				}

				// Update the version in database for this module
				$db->updateModuleVersion('pCaptcha', self::MODULE_VERSION);
			}
		}
		
	}
	
}

?>