<?php
/**
 * This Class is for Localization
 * All Language-Specific textx are defined in an XML with tags <SECTION> and child-Tags <TEXT id="name" value="value" />
 *
 * @author Lukas Zurschmiede <l.zurschmiede@delightsoftware.com>
 * @copyright (c) 2007 by delight software gmbh, switzerland
 *
 * @package delightcms
 * @version 2.0
 * @uses singelton, pLanguage
 */

$DBTables['lan'] = $tablePrefix.'_languages';
$DBFields['lan'] = array(
	'id' => 'id',
	'text' => 'text',
	'short' => 'short',
	'char' => 'charset',
	'icon' => 'icon_path',
	'active' => 'lang_is_active'
);

class pLanguage {
	const ICON_DIR = '/images/language/';
	const MODULE_VERSION = 2014042800;
	
	private $shortLanguage;
	private $extendedLanguage;
	private $languageId;
	private $languageIcon;
	private $languageCharset;
	private $active;
	private $languageData;

	/**
	 * Initialization
	 *
	 * @param unknown_type $short
	 */
	public function __construct($short=null) {
		$this->updateModule();
		$this->languageData = array();
		if (is_numeric($short)) {
			$this->loadById($short);
		} else {
			$this->getLanguageParameters($short);
		}
	}

	/**
	 * Load the Language by it's DB-ID and not by it's name
	 *
	 * @param integer $langId The LanguageID to load
	 */
	private function loadById($langId) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = "SELECT [lan.short] FROM [table.lan] WHERE [lan.id]=".(int)$langId.";";
		$db->run($sql, $res);
		if ($res->getFirst()) {
			$this->getLanguageParameters($res->{$db->getFieldName('lan.short')});
		}
		unset($res);
	}

	/**
	 * Load all default values from the Database for this Language
	 *
	 * @param string $short Short language code (2-char ISO-Code)
	 */
	private function getLanguageParameters($short=null) {
		if (empty($short)) {
			$short = pURIParameters::get('lan', '', pURIParameters::$STRING);
		}
		if (empty($short)) {
			$short = pURIParameters::get('lang', MASTER_LANGUAGE, pURIParameters::$STRING);
		}

		$db = pDatabaseConnection::getDatabaseInstance();
		$sql = "SELECT * FROM [table.lan] WHERE [lan.short]='".$short."'";
		$res = null;
		$db->run($sql, $res);
		if ($res->getFirst()) {
			$this->extendedLanguage = $res->{$db->getFieldName('lan.text')};
			$this->languageId       = $res->{$db->getFieldName('lan.id')};
			$this->shortLanguage    = $res->{$db->getFieldName('lan.short')};
			$this->languageIcon     = $res->{$db->getFieldName('lan.icon')};
			$this->languageCharset  = $res->{$db->getFieldName('lan.char')};
			$this->active           = (int)$res->{$db->getFieldName('lan.active')} > 0;
		} else {
			$this->extendedLanguage = 'german';
			$this->languageId       = 1;
			$this->shortLanguage    = 'de';
			$this->languageIcon     = 'german.gif';
			$this->languageCharset  = 'iso-8859-1';
			$this->active           = 1;
		}
	}

	/**
	 * Change the active-flag from loaded language
	 *
	 * @access public
	 */
	public function changeLanguageState() {
		if ($this->languageId <= 0) {
			return;
		}
		$this->active = !$this->active;
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = 'UPDATE [table.lan] SET [lan.active]='.($this->active ? '1' : '0').' WHERE [lan.id]='.$this->languageId.';';
		$db->run($sql, $res);
	}

	/**
	 * Set a Property
	 *
	 * @param string $name Name pf Property to set
	 * @param mixed $value Value for the Property
	 */
	public function __set($name, $value) {
		switch ($name) {
			case 'shortLanguage':
				$this->shortLanguage = $value;
				break;
			case 'extendedLanguage':
				$this->extendedLanguage = $value;
				break;
			default:
				trigger_error('Tried to set an undefined Property: '.$name, E_USER_ERROR);
				break;
		}
	}

	/**
	 * Return a Property-value
	 *
	 * @param string $name Name of Property to get
	 * @return mixed Value from Property
	 */
	public function __get($name) {
		switch ($name) {
			case 'short':
			case 'shortLanguage':
				return $this->shortLanguage;
				break;
			case 'name':
			case 'extendedLanguage':
				return $this->extendedLanguage;
				break;
			case 'id':
			case 'languageId':
				return $this->languageId;
				break;
			case 'charset':
				return $this->languageCharset;
				break;
			case 'iconName':
				return $this->languageIcon;
				break;
			case 'icon':
				$dir = str_replace('/', DIRECTORY_SEPARATOR, self::ICON_DIR);
				if (file_exists(ABS_TEMPLATE_DIR.$dir.$this->languageIcon)) {
					return TEMPLATE_DIR.self::ICON_DIR.$this->languageIcon;

				} else if (file_exists(ABS_TEMPLATE_DIR.$dir.$this->shortLanguage.'.gif')) {
					return TEMPLATE_DIR.self::ICON_DIR.$this->shortLanguage.'.gif';

				} else if (file_exists(ABS_TEMPLATE_DIR.$dir.$this->shortLanguage.'.png')) {
					return TEMPLATE_DIR.self::ICON_DIR.$this->shortLanguage.'.png';

				} else if (file_exists(ABS_MAIN_DIR.$dir.$this->languageIcon)) {
					return MAIN_DIR.self::ICON_DIR.$this->languageIcon;

				} else if (file_exists(ABS_MAIN_DIR.$dir.$this->shortLanguage.'.gif')) {
					return MAIN_DIR.self::ICON_DIR.$this->shortLanguage.'.gif';

				} else if (file_exists(ABS_MAIN_DIR.$dir.$this->shortLanguage.'.png')) {
					return MAIN_DIR.self::ICON_DIR.$this->shortLanguage.'.png';

				}
				return MAIN_DIR.'/images/blank.gif';
				break;
			case 'active':
				return $this->active;
				break;
			default:
				trigger_error('Tried to get an undefined Property: '.$name, E_USER_ERROR);
				break;
		}
	}

	/**
	 * Return a Simple Object which identifies this Language
	 *
	 * @return stdClass
	 * @access public
	 */
	public function getSimpleObject() {
		$back = new stdClass();
		$back->name = $this->extendedLanguage;
		$back->short = $this->shortLanguage;
		$back->charset = $this->languageCharset;
		$back->icon = $this->iconName;
		$back->icon_full = $this->icon;
		$back->active = $this->active;
		$back->id = $this->languageId;
		return $back;
	}

	/**
	 * Get a value defined by it's name
	 *
	 * @param string $type Type of Language-Entry
	 * @param string $name Parametername to get the value from
	 * @return string/false value or false on failure
	 */
	public function getValue($type, $name) {
		if ($type == 'txt') {
			$type = 'text';
		}
		if (array_key_exists($type, $this->languageData) && array_key_exists($name, $this->languageData[$type])) {
			return $this->languageData[$type][$name];
		}
		return $type.'.'.$name;
	}

	/**
	 * Set a Language-Value for a Parameter
	 *
	 * @param string $type Type of Language-Entry
	 * @param string $name Parameter-Name to set a value to
	 * @param string $value value for the Parameter
	 */
	public function setValue($type, $name, $value) {
		if (!array_key_exists($type, $this->languageData)) {
			$this->languageData[$type] = array();
		}
		$this->languageData[$type][$name] = $value;
	}

	/**
	 * Update the module if needed - this was first in the administration
	 * class but is moved here to be more general
	 * @access protected
	 */
	protected function updateModule() {
		// first get the version stored in the Database
		$db = pDatabaseConnection::getDatabaseInstance();
		$version = $db->getModuleVersion(get_class($this));
		$res = null;

		// Check if we need an Update
		if (self::MODULE_VERSION > $version) {
			// Initial create the Languages-Table
			if ($version <= 0) {
				$sql  = 'CREATE TABLE IF NOT EXISTS [table.lan] ('.
				' [field.lan.id] INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,'.
				' [field.lan.text] VARCHAR(100) NOT NULL DEFAULT \'\','.
				' [field.lan.short] VARCHAR(5) NOT NULL DEFAULT \'\','.
				' [field.lan.char] VARCHAR(50) NOT NULL DEFAULT \'\','.
				' [field.lan.icon] VARCHAR(50) NOT NULL DEFAULT \'\','.
				' [field.lan.active] INT(1) UNSIGNED NOT NULL DEFAULT 0,'.
				' PRIMARY KEY ([field.lan.id]),'.
				' UNIQUE KEY [field.lan.id] ([field.lan.id])'.
				' );';
				$db->run($sql, $res);
				$res = null;

				// Insert base-language if not already exists
				$sql = 'SELECT [lan.text] FROM [table.lan] WHERE [lan.text]=\'german\';';
				$db->run($sql, $res);
				if (!$res->getFirst()) {
					$res = null;
					$sql = 'INSERT INTO [table.lan]'.
					' ([field.lan.text],[field.lan.short],[field.lan.char],[field.lan.icon],[field.lan.active])'.
					' VALUES (\'german\',\'de\',\'iso-8859-1\',\'german.gif\',1);';
					$db->run($sql, $res);
					$res = null;
				}
			}

			// Update the version in database for this module
			$db->updateModuleVersion(get_class($this), self::MODULE_VERSION);
		}
	}

}

?>