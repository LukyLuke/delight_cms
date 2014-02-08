<?php
/**
 * This Class is for URI-Parameters
 * All Language-Specific textx are defined in an XML with tags <SECTION> and child-Tags <TEXT id="name" value="value" />
 * 
 * @author Lukas Zurschmiede <l.zurschmiede@delightsoftware.com>
 * @copyright (c) 2007 by delight software gmbh, switzerland
 * 
 * @package delightcms
 * @version 2.0
 * @uses singelton, pLanguage
 */


class pURIParameters {
	public static $INTEGER = 0;
	public static $INT = 0;
	public static $STRING = 1;
	public static $BOOLEAN = 2;
	public static $BOOL = 2;
	public static $FLOAT = 3;
	public static $DOUBLE = 3;
	public static $ARRAY = 4;
	public static $OBJECT = 5;
	
	/**
	 * Get a Variable from the Request
	 *
	 * @static yes
	 * @access public
	 * 
	 * @param string $name The name to get
	 * @param mixed $default The default value
	 * @param int/string $type pURIParameters::-Type
	 * @return mixed
	 */
	public static function get($name, $default=null, $type=1) {
		$value = null;
		if (array_key_exists($name, $_POST)) {
			$value = $_POST[$name];
		} else if (array_key_exists($name, $_GET)) {
			$value = $_GET[$name];
		} else {
			$value = $default;
		}
		$value = self::unescape($value);
		pURIParameters::setType($value, $type);
		return $value;
	}
	
	/**
	 * Remove backslashes from variables
	 *
	 * @param string/array $value
	 * @return string/array
	 * @access private
	 */
	private static function unescape($value) {
		if (get_magic_quotes_gpc() == 1) {
			 if (!is_array($value)) {
				$value = stripslashes($value);
			} else {
				foreach ($value as $k => $val) {
					$value[$k] = self::unescape($val);
				}
			}
		}
		return $value;
	}
	
	/**
	 * Set a variable to its type
	 *
	 * @param mixed $value Pointer to the Value to convert
	 * @param int/string $type pURIParameters::-Type or String of Object to convert to
	 */
	public static function setType(&$value, $type) {
		switch ($type) {
			case pURIParameters::$INT:
				$value = (int)$value;
				break;
				
			case pURIParameters::$BOOL:
				if ( ((string)$value != 'true') && ((string)$value != 'yes')) {
					$value = false;
				} else {
					$value = true;
				}
				break;
				
			case pURIParameters::$DOUBLE:
				$value = (float)$value;
				break;
				
			case pURIParameters::$ARRAY:
				if (is_string($value)) {
					$value = json_decode($value);
				} else {
					$value = (array)$value;
				}
				break;
				
			case pURIParameters::$OBJECT:
				try {
					$value = json_decode($value);
				} catch(Exception $e) {
					$value = (object)$value;
				}
				break;
				
			case pURIParameters::$STRING:
				$value = (string)$value;
				break;
				
			default:
				settype($value, $type);
				break;
		}
	}
	
}


?>