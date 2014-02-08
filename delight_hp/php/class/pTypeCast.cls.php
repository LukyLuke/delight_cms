<?php

class pTypeCast {
	
	/**
	 * Cast by a Formating-Array
	 * 
	 * $castBy = array( 0 => "Formating if needed", 1 => "Cast to" )
	 *   "Cast to" can be one of "int", "float", "string", "set"
	 * 
	 * @param mixed $value Value to cast by the given Format
	 * @param array $castBy Cast-Array which defines the Format
	 * @return mixed Value casted by the given Format
	 * @static true
	 * @access public
	 */
	public static function castByFormatingArray($value, array $castBy) {
		if (!is_array($castBy) || !isset($castBy[1])) {
			return null;
		}
		switch ((string)$castBy[1]) {
			case 'int':
				return pTypeCast::asInteger($value);
				break;
				
			case 'float':
				return pTypeCast::asFloat($value, $castBy[0]);
				break;
				
			case 'string':
				return pTypeCast::asString($value, $castBy[0]);
				break;
				
			case 'set':
				return pTypeCast::asSet($value, $castBy[0]);
				break;
			
			case 'bool':
			case 'boolean':
				return pTypeCast::asBoolean($value, $castBy[0]);
				break;
		}
		return null;
	}
	
	/**
	 * Get the given Value as a String
	 *
	 * @param mixes $value Value to convert to a String
	 * @param mixed $format The max Length for the resulting String or a RegExp
	 * @return string The value as a String if the conversion is possible
	 * @static true
	 * @access public
	 */
	public static function asString($value, $format=null) {
		$length = preg_match('/[^0-9]+/', (string)$format) ? null : (int)$format;
		if ( ($length !== null) && ($length > 0)) {
			return substr((string)$value, 0, ($length <= strlen((string)$value)-1) ? $length : strlen($value)-1);
		} else if ($back = preg_replace('/'.$format.'/smi', '', (string)$value)) {
			return $back;
		}
		return (string)$value;
	}
	
	/**
	 * Typecast $value to a FLOAT based on the given Format
	 *
	 * @param mixed $value Value to cast as float
	 * @param string $format The Format for the value (.2 for two decimal, 5.2 for five digits and two decimals, ...)
	 * @return float The value as a FLOAT if the conversion is possible
	 * @static true
	 * @access public
	 */
	public static function asFloat($value, $format=null) {
		$value = floatval($value);
		if ($format !== null) {
			$positive = true;
			if ($value < 0) {
				$positive = false;
				$value = -$value;
			}
			$v = explode('.', (string)$value);
			if (!array_key_exists(1, $v)) {
				$v[1] = 0;
			}
			$f = explode('.', (string)$format);
			if ( ($f[0] != '') && ((int)$f[0] >= 0) && (strlen($v[0]) > (int)$f[0])) {
				$v[0] = substr($v[0], strlen($v[0])-(int)$f[0]);
			}
			if ( ($f[1] != '') && ((int)$f[1] >= 0) && (strlen($v[1]) > (int)$f[1])) {
				$v[1] = substr($v[1], 0, (int)$f[1]);
			}
			$value = floatval(implode('.', $v));
			if (!$positive) {
				$value = -$value;
			}
		}
		return $value;
	}
	
	/**
	 * Get teh given Value as an Integer
	 *
	 * @param mixed $value Value to cast as an Integer
	 * @return integer The Value as an Integer if the conversion is possible
	 * @static true
	 * @access public
	 */
	public static function asInteger($value) {
		return intval($value);
	}
	
	/**
	 * The given Value can only be one of the given $format's
	 * $format is splitted out by ',' to get the different values
	 * the value can be
	 *
	 * @param mixed $value The value to Check for
	 * @param String $format All possible values for $value seperated by semicolon (,)
	 * @return String The value as a String if it's in $format or null otherwise
	 * @static true
	 * @access public
	 */
	public static function asSet($value, $format) {
		$format = explode(',', $format);
		$value = pTypeCast::asString($value);
		if (in_array($value, $format)) {
			return $value;
		}
		return null;
	}
	
	/**
	 * The given Value can only be true or false. The default is in $format
	 *
	 * @param mixed $value The value to Check for
	 * @param String $format The default if not true|false
	 * @return Boolean
	 * @static true
	 * @access public
	 */
	public static function asBoolean($value, $format) {
		return (strtolower($value) == strtolower($format));
	}
	
}

?>
