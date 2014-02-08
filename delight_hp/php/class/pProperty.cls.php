<?php
/** $id$
 * Class to store Values which can be accessed liek Properties
 *
 * @author Lukas Zurschmiede <l.zurschmiede@delightsoftware.com>
 * @copyright 2008 (c) by delight software gmbh
 * @package delightWebProduct
 */

class pProperty {
	private $_prop = array();

	/**
	 * Create a Property
	 *
	 * @param string $name Name of the Property
	 * @param string $type Type of the Property
	 * @param mixed $default [optional] default value for the Property
	 * @access public
	 */
	public function define($name, $type, $default=null) {
		if (array_key_exists($name, $this->_prop)) {
			unset($this->_prop[$name]);
		}

		$default = $this->setType($default, $type);

		$this->_prop[$name] = array();
		$this->_prop[$name]['type'] = $type;
		$this->_prop[$name]['value'] = $default;
		$this->_prop[$name]['default'] = $default;
	}

	/**
	 * Clean up all Properties and reset them to the initial value
	 *
	 * @access public
	 */
	public function cleanValues() {
		foreach ($this->_prop as $k => $v) {
			$this->_prop[$k]['value'] = $this->_prop[$k]['default'];
		}
	}

	/**
	 * Check if a Property is already defined
	 *
	 * @param String $name Check for this Property
	 * @return boolean true if the Property exists
	 * @access public
	 */
	public function isDefined($name) {
		return array_key_exists($name, $this->_prop);
	}

	/**
	 * Create a Property if it does not exists already
	 *
	 * @param string $name Name of the Property
	 * @param string $type Type of the Property
	 * @param mixed $default [optional] default value for the Property
	 * @access public
	 */
	public function defineIfNotDefined($name, $type, $default=null) {
		if (!$this->isDefined($name)) {
			$this->define($name, $type, $default);
		}
	}

	/**
	 * Undefine a Property which was defined first
	 * this is specially needed by Session-Unregister
	 *
	 * @param String $name Name of Property to unset
	 * @access public
	 */
	public function undefine($name) {
		if (array_key_exists($name, $this->_prop)) {
			unset($this->_prop[$name]);
		}
	}

	/**
	 * Set new type to a variable
	 * if it's an Object we will make a TypeCast
	 *
	 * @param mixed $value Value to change the type
	 * @param string $type Type to change $value to
	 * @return mixed typecasted $value
	 * @access protected
	 */
	protected function setType($value, $type) {
		if ($value != null) {
			switch (strtolower($type)) {
				case 'int':
				case 'integer':
					$value = (int)$value;
					break;
				case 'bool':
				case 'boolean':
					$value = (bool)$value;
					break;
				case 'float':
				case 'double':
				case 'real':
					$value = (float)$value;
					break;
				case 'string':
					$value = (string)$value;
					break;
				case 'array':
					$value = (array)$value;
					break;
				default:
					$value = $this->typecast($value, $type);
					break;
			}
		}
		return $value;
	}

	/**
	 * Typecast an Object to a specific ObjectType
	 *
	 * @param mixed $object Object to cast to $class
	 * @param string $class Classname - Cast $object to this type
	 * @return mixed $object as type $class
	 * @access protected
	 */
	protected function typecast($object, $class) {
		// The header of a serialized class looks like "O:8:MyObject:"
		// So we can replace this string with a new one and unserialize it to get the new type
		//
		// O -> this is an Object
		// 8 -> Length of the Class-Name
		// MyObject -> Type of the Object

		$obj = serialize($object);
		$length = strlen($class);
		$obj = preg_replace("/^O:[0-9]+:\"[^\"]+\":/i", 'O:'.$length.':"'.$class.'":', $obj);

		return unserialize($obj);
	}

	/**
	 * Get all KeyNames wich begins with $fraction
	 *
	 * @param String $fraction The Key should begin with this string
	 * @return array All Keys which begins by the given fraction
	 * @access public
	 */
	public function getKeysByFraction($fraction='') {
		$back = array();
		$len = strlen($fraction);
		foreach (array_keys($this->_prop) as $key) {
			if (substr($key, 0, $len) == $fraction) {
				$back[] = $key;
			}
		}
		return $back;
	}

	/**
	 * Get Formations from the Configuration and return the prepared ones
	 *
	 * @param String $tag TagName to get the Formations for (Config: type_attrib_{integer,float,set} )
	 * @return array Array with all formatings
	 * @access public
	 */
	public function getAttributeFormats($tag) {
		$format = array();
		$int = explode(';', $this->type_attrib_integer);
		$float = explode(';', $this->type_attrib_float);
		$set = explode(';', $this->type_attrib_set);
		$string = explode(';', $this->type_attrib_string);
		$boolean = explode(';', $this->type_attrib_boolean);

		for ($i = 0; $i < count($set); $i++) {
			$tmp = explode(':', $set[$i]);
			if ( (count($tmp) > 1) && (strlen($this->{$tmp[0]}) > 0) ) {
				$format[$this->{$tmp[0]}][0] = $tmp[1];
				$format[$this->{$tmp[0]}][1] = 'set';
			}
		}
		for ($i = 0; $i < count($int); $i++) {
			if (strlen($this->{$int[$i]}) > 0) {
				$format[$this->{$int[$i]}][0] = null;
				$format[$this->{$int[$i]}][1] = 'int';
			}
		}
		for ($i = 0; $i < count($float); $i++) {
			$tmp = explode(':', $float[$i]);
			if ( (count($tmp) > 1) && (strlen($this->{$tmp[0]}) > 0) ) {
				$format[$this->{$tmp[0]}][0] = $tmp[1];
				$format[$this->{$tmp[0]}][1] = 'float';
			}
		}
		for ($i = 0; $i < count($string); $i++) {
			$tmp = explode(':', $string[$i]);
			if ( (count($tmp) > 1) && (strlen($this->{$tmp[0]}) > 0) ) {
				$format[$this->{$tmp[0]}][0] = (empty($tmp[1]) ? -1 : (int)$tmp[1]);
				$format[$this->{$tmp[0]}][1] = 'string';
			}
		}
		for ($i = 0; $i < count($boolean); $i++) {
			$tmp = explode(':', $boolean[$i]);
			if ( (count($tmp) > 1) && (strlen($this->{$tmp[0]}) > 0) ) {
				$format[$this->{$tmp[0]}][0] = (empty($tmp[1]) ? -1 : (int)$tmp[1]);
				$format[$this->{$tmp[0]}][1] = 'boolean';
			}
		}
		return $format;
	}

	/**
	 * Set a Value for a Property
	 *
	 * @param string $name name of the Property
	 * @param mixed $value Value for the Property
	 * @access public
	 */
	public function __set($name, $value) {
		if (array_key_exists($name, $this->_prop)) {
			$value = $this->setType($value, $this->_prop[$name]['type']);
			$this->_prop[$name]['value'] = $value;
		}
	}

	/**
	 * Get a Property and return the value.
	 * If it does not exists, return null
	 *
	 * @param string $name Name of the Property to get
	 * @return mixed Type of the Property
	 * @access public
	 */
	public function __get($name) {
		if (array_key_exists($name, $this->_prop)) {
			$value = $this->setType($this->_prop[$name]['value'], $this->_prop[$name]['type']);
		} else {
			$value = null;
		}
		return $value;
	}

	/**
	 * Get the Type of a Property
	 * @param string $name PropertyName
	 * @return string or null if not found
	 * @access public
	 */
	public function getType($name) {
		if (array_key_exists($name, $this->_prop)) {
			$value = $this->_prop[$name]['type'];
		} else {
			$value = null;
		}
		return $value;
	}

}

?>