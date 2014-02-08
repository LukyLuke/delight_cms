<?php

$DBTables['content'] = "dhp_page_content";  // All Page related Content
$DBTables['csettings'] = "dhp_page_content_settings";  // All Page related Content Settings
$DBFields['content'] = array(
	'id' => 'id',
	'var' => 'variable',
	'menu' => 'menu_id',
	'recursive' => 'on_submenus',
	'type' => 'content_type',
	'value' => 'value'
);
$DBFields['csettings'] = array(
	'content' => 'content_id',
	'name' => 'name',
	'value' => 'value',
	'type' => 'type'
);

class pSpecialContent implements iUpdateIface,Iterator {
	const MODULE_VERSION = 2012032701;
	const MAX_RECURSION_VARS = 10;
	const TYPE_INT = 0;
	const TYPE_STRING = 1;
	const TYPE_FLOAT = 2;
	const TYPE_IMAGE = 3;
	const TYPE_FILE = 4;
	const TYPE_NEWS = 5;
	const TYPE_TEXT = 6;
	const TYPE_COLOR = 7;
	const TYPE_HTML = 8;

	private $_contents = array();
	private $iteratorPosition = 0; // Iterator-Interface
	private $recursion = 0;

	/**
	 * Initialization
	 * @param string $var Optional Variable-Name
	 * @return none
	 * @access public
	 */
	public function __construct($var=null) {
		$this->updateModule();
		$this->loadContent($var);
		$this->iteratorPosition = 0;
	}

	/**
	 * Load the Content
	 * @param string $var Optional Variable to load all Content for
	 * @return none
	 * @access public
	 */
	public function loadContent($var=null) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;

		if (is_string($var)) {
			$sql = 'SELECT * FROM [table.content] WHERE [content.var]=\''.mysql_real_escape_string($var).'\' ORDER BY [content.id] ASC;';
			$db->run($sql, $res);
			if ($res->getFirst()) {
				while ($res->getNext()) {
					$prop = new pProperty();
					$prop->defineIfNotDefined('id', 'int', $res->{$db->getFieldName('content.id')});
					$prop->defineIfNotDefined('var', 'string', $res->{$db->getFieldName('content.var')});
					$prop->defineIfNotDefined('menu', 'int', $res->{$db->getFieldName('content.menu')});
					$prop->defineIfNotDefined('recursive', 'int', $res->{$db->getFieldName('content.recursive')});
					$prop->defineIfNotDefined('type', 'int', $res->{$db->getFieldName('content.type')});
					$prop->defineIfNotDefined('value', $this->_getPropertyType($res->{$db->getFieldName('content.type')}), $this->_getPropertyValue($res->{$db->getFieldName('content.value')}, $res->{$db->getFieldName('content.type')}));
					$prop->defineIfNotDefined('settings', 'pProperty', $this->_loadSettings($prop->id));
					$this->_contents[] = $prop;
				}
			}
		}
	}

	/**
	 * Return the Content for the given selected Menu
	 * @param string $var Optional Variable Name
	 * @param array $options Special Options defined in the TemplateTag
	 * @param int $recursion used to count the Var-In-Var recursion and break in prepareContent()
	 * @return string Empty if no Content was found
	 * @access public
	 */
	public function getContent($var=null, array $options=array(), $recursion=0) {
		$this->recursion = $recursion;
		$this->loadContent($var);
		$menu = pMenu::getMenuInstance();
		$menuId = $menu->getMenuId();
		$menuBacktrace = $menu->getBacktrace();

		// First try to get the Content for the exact Menu
		foreach ($this->_contents as $content) {
			if ($content->menu == $menuId) {
				return $this->prepareContent($content, $options);
			}
		}

		// Try to get the Content from a Submenu and a Recursiv-Configured Content
		foreach ($this->_contents as $content) {
			if (($content->recursive == 1) && (in_array($content->menu, $menuBacktrace))) {
				return $this->prepareContent($content, $options);
			}
		}

		// The third Option is a globally defined Content (MenuID = 0)
		foreach ($this->_contents as $content) {
			if ($content->menu == 0) {
				return $this->prepareContent($content, $options);
			}
		}

		// Last chance is an "emtpy"-Option
		if (array_key_exists('empty', $options)) {
			$v = $options['empty'];
			$this->replaceVariables($v);
			return $v;
		}
		return '';
	}

	/**
	 * Get the Value to display on AdminPage
	 *
	 * @param pProperty $prop Property to get the Value from
	 * @param boolean $source Optional: Get "source_value" instead of "value"
	 * @param array $options Optional: Array with Options
	 * @return mixed
	 * @access public
	 */
	public function getValue(pProperty $prop, $source=true, array $options=array()) {
		$value = $prop->value;
		if ($value instanceof pTextEntry) {
			$value->setRenderOptions($options);
		}

		if ($source && $value instanceof pTextEntry) {
			return $value->source_value;

		} else if (!$source && $value instanceof pTextEntry) {
			return $value->content;
		}
		return (string)$value;
	}

	/**
	 * Prepare and return the Content from a Property
	 * @param pProperty $content
	 * @param array $options Special options defined in the Template
	 * @return string
	 * @access private
	 */
	private function prepareContent(pProperty $content, array $options=array()) {
		if ($this->recursion <= self::MAX_RECURSION_VARS) {
			foreach ($options as $k => &$v) {
				$this->replaceVariables($v);
			}
		}

		$back = $this->getValue($content, false, $options);
		$back = $back == 'about:blank' ? '' : $back;
		if (empty($back) && array_key_exists('empty', $options)) {
			$back = $options['empty'];
			//$back = html_entity_decode($back);
			$this->replaceVariables($back);
		}
		if (array_key_exists('content', $options)) {
			$back = str_replace('[CONTENT]', $back, $options['content']);
		}
		return $back;
	}

	private function replaceVariables(&$v) {
		$match = array();
		if (preg_match_all('/\[?VAR:([a-z0-9_-]+)\]?/smi', $v, $match, PREG_PATTERN_ORDER)) {
			for ($i = 0; $i < count($match[0]); $i++) {
				$cont = new pSpecialContent($match[1][$i]);
				$var = $cont->getContent($match[1][$i], array(), ++$this->recursion);
				$this->replaceVariables($var);
				$v = str_replace($match[0][$i], $var, $v);
			}
		}
		if (preg_match_all('/\[?CALC\(([0-9()+\/*%-]+)\)\]?/smi', $v, $match, PREG_PATTERN_ORDER)) {
			for ($i = 0; $i < count($match[0]); $i++) {
				$calc = null;
				$match[1][$i] = str_replace('()', 0, $match[1][$i]);
				@eval('$calc='.$match[1][$i].';');
				if ($calc != null) {
					$v = str_replace($match[0][$i], $calc, $v);
				}
			}
		}
	}

	/**
	 * Load all Settings from a Content Configuration
	 * @param int $id Content ID
	 * @return pProperty
	 * @access private
	 */
	private function _loadSettings($id) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$prop = new pProperty();
		$sql = 'SELECT * FROM [table.csettings] WHERE [csettings.content]='.(int)$id.' ORDER BY [csettings.name] ASC;';
		$db->run($sql, $res);
		if ($res->getFirst()) {
			while ($res->getNext()) {
				$prop->defineIfNotDefined($res->{$db->getFieldName('csettings.name')}, $this->_getPropertyType($res->{$db->getFieldName('csettings.type')}), $this->_getPropertyValue($res->{$db->getFieldName('csettings.value')}, $res->{$db->getFieldName('csettings.type')}));
				$prop->defineIfNotDefined($res->{$db->getFieldName('csettings.name')}.'_type', 'int', $res->{$db->getFieldName('csettings.type')});
				$prop->defineIfNotDefined($res->{$db->getFieldName('csettings.name')}.'_name', 'string', $res->{$db->getFieldName('csettings.name')});
			}
		}
		return $prop;
	}

	/**
	 * Get the Type to use for a pProperty
	 * @param int $type pSecialContent::TYPE_XX Constant
	 * @return string
	 * @access private
	 */
	private function _getPropertyType($type) {
		switch ((int)$type) {
			case self::TYPE_IMAGE:
				return 'pImageEntry';
				break;
			case self::TYPE_TEXT:
				return 'pTextEntry';
				break;
			case self::TYPE_FILE:
				return 'pFileEntry';
				break;
			case self::TYPE_NEWS:
				return 'pNewsEntry';
				break;
			case self::TYPE_INT:
				return 'int';
				break;
			case self::TYPE_FLOAT:
				return 'float';
				break;
			case self::TYPE_STRING:
			case self::TYPE_HTML:
			case self::TYPE_COLOR:
			default:
				return 'string';
				break;
		}
	}

	/**
	 * Get the Value for a Property
	 * @param mixed $value Value to cast for the Property
	 * @param int $type pSecialContent::TYPE_XX Constant
	 * @return mixed
	 * @access private
	 */
	private function _getPropertyValue($value, $type) {
		switch ((int)$type) {
			case self::TYPE_IMAGE:
				$value = new pImageEntry($value);
				break;
			case self::TYPE_TEXT:
				$value = new pTextEntry($value);
				break;
			case self::TYPE_FILE:
				$value = new pFileEntry($value);
				break;
			case self::TYPE_NEWS:
				$value = new pNewsEntry($value);
				break;
			case self::TYPE_INT:
				settype($value, 'int');
				break;
			case self::TYPE_FLOAT:
				settype($value, 'float');
				break;
			case self::TYPE_STRING:
			case self::TYPE_HTML:
			case self::TYPE_COLOR:
			default:
				settype($value, 'string');
				break;
		}
		return $value;
	}

	/**
	 * Delete the COnfiguration with given ID
	 * @param int $id COnfiguration-ID to remove
	 * @return boolean
	 * @access public
	 */
	public function delete($id) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		foreach ($this->_contents as &$c) {
			if ($c->id == $id) {
				$sql = 'DELETE FROM [table.csettings] WHERE [field.csettings.content]='.(int)$id.';';
				$db->run($sql, $res);
				$sql = 'DELETE FROM [table.content] WHERE [field.content.id]='.(int)$id.';';
				$db->run($sql, $res);
				unset($c);
				return true;
			}
		}
		return false;
	}

	/**
	 * Create a new, empty Configuration
	 * @param int $type
	 * @return int int
	 * @access public
	 */
	public function create($variable, $type) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = 'INSERT INTO [table.content] ([field.content.var],[field.content.menu],[field.content.recursive],[field.content.type],[field.content.value]) VALUES (\''.$variable.'\',0,0,'.$type.',\'\');';
		$db->run($sql, $res);
		return $res->getInsertId();
	}

	/**
	 * Change a Variable-Configuration
	 * @param string $variable
	 * @param int $id
	 * @param string $value
	 * @param int $menu
	 * @param boolean $recursive
	 * @param string $settings
	 * @return boolean
	 */
	public function change($variable, $id, $value, $menu, $recursive, $settings) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		foreach ($this->_contents as $c) {
			if ($c->id == $id) {
				$c->menu = $menu;
				$c->recursive = $recursive;
				if ($c->value instanceof pTextEntry) {
					$v = $c->value;
					$v->id = (int)$value;
					$c->value = $v;
				} else {
					$c->value = $value;
				}

				$val = ($c->value instanceof pTextEntry) ? $c->value->id : $c->value;
				$sql = 'UPDATE [table.content] SET [field.content.value]=\''.mysql_real_escape_string($val).'\'';
				$sql .= ',[field.content.menu]='.$c->menu.',[field.content.recursive]='.($c->recursive ? '1' : '0');
				$sql .= ' WHERE [field.content.id]='.$c->id.' AND [field.content.var]=\''.$c->var.'\';';
				$db->run($sql, $res);

				return true;
			}
		}
		return false;
	}

	/**
	 * Interface-Function to update the Module
	 * @see delight_hp/php/class/iUpdateIface#updateModule()
	 */
	public function updateModule() {
		$db = pDatabaseConnection::getDatabaseInstance();
		$version = $db->getModuleVersion(get_class($this));
		$res = null;

		// Check if we need an Update
		if (self::MODULE_VERSION > $version) {
			// initial
			if ($version < 2010072900) {
				$sql = 'CREATE TABLE [table.content] ('.
				' [field.content.id] INT(11) UNSIGNED NOT NULL auto_increment,'.
				' [field.content.var] VARCHAR(100) NOT NULL default \'\','.
				' [field.content.menu] INT(11) UNSIGNED NOT NULL default 0,'.
				' [field.content.recursive] TINYINT(1) NOT NULL default 0,'.
				' [field.content.type] TINYINT(2) UNSIGNED NOT NULL default 0,'.
				' [field.content.value] TEXT NOT NULL default \'\','.
				' UNIQUE KEY [field.content.id] ([field.content.id])'.
				');';
				$db->run($sql, $res);

				$sql = 'CREATE TABLE [table.csettings] ('.
				' [field.csettings.content] INT(11) UNSIGNED NOT NULL default 0,'.
				' [field.csettings.name] VARCHAR(100) NOT NULL default \'\','.
				' [field.csettings.value] TEXT NOT NULL default \'\','.
				' [field.csettings.type] TINYINT(2) NOT NULL default 0,'.
				' KEY [field.csettings.content] ([field.csettings.content])'.
				');';
				$db->run($sql, $res);
			}
			// Change some Variable-Names we wrongly defined in some templates
			if ($version < 2013032701) {
				$sql= 'UPDATE [table.content] SET [field.content.var]=\'Menu_Width\' WHERE [field.content.var]=\'MenuWidth\';';
				$db->run($sql, $res);
				$sql= 'UPDATE [table.content] SET [field.content.var]=\'Page_Width\' WHERE [field.content.var]=\'PageWidth\';';
				$db->run($sql, $res);
				$sql= 'UPDATE [table.content] SET [field.content.var]=\'Content_Width\' WHERE [field.content.var]=\'ContentWidth\';';
				$db->run($sql, $res);
				$sql= 'UPDATE [table.content] SET [field.content.var]=\'Background_Image\' WHERE [field.content.var]=\'BackgroundImage\';';
				$db->run($sql, $res);
			}

			// Update the version in database for this module
			$db->updateModuleVersion(get_class($this), self::MODULE_VERSION);
		}
	}

	/**
	 * Iterator Interface
	 * @link http://ch.php.net/manual/en/class.iterator.php
	 */
	public function current() {
		return $this->_contents[$this->iteratorPosition];
	}

	/**
	 * Iterator Interface
	 * @link http://ch.php.net/manual/en/class.iterator.php
	 */
	public function key() {
		return $this->_contents[$this->iteratorPosition]->var;
	}

	/**
	 * Iterator Interface
	 * @link http://ch.php.net/manual/en/class.iterator.php
	 */
	public function next() {
		++$this->iteratorPosition;
	}

	/**
	 * Iterator Interface
	 * @link http://ch.php.net/manual/en/class.iterator.php
	 */
	public function rewind() {
		$this->iteratorPosition = 0;
	}

	/**
	 * Iterator Interface
	 * @link http://ch.php.net/manual/en/class.iterator.php
	 */
	public function valid() {
		return isset($this->_contents[$this->iteratorPosition]);
	}
}