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

require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'pLanguage.cls.php');

class pMessages {
	private $lang;
	private $languageFile;
	private $xmlCount;
	private $currentLanguage;
	private $currentShortLanguage;
	private $currentSection;
	private $languageContent;

	private $validXmlSections = array('TEXT', 'DATABASE', 'MENU');
	private static $instance = null;

	/**
	 * Initialization
	 *
	 * @param string $val The Language-File
	 * @param string $lang Long language-Name
	 * @param string $short Short Language-Name
	 * @return Messages
	 */
	private function __construct($shortLanguage=null) {
		$this->lang = new pLanguage($shortLanguage);
		$this->currentLanguage  = '';
		$this->currentShortLanguage = '';
		$this->currentSection   = '';
		$this->currentTag       = '';
		$this->languageFile     = ABS_DATA_DIR.'messages.xml';

		$this->languageContent = null;
		$this->parseLanguageFile();
		$this->setDefaultLanguageParameters();

		$this->setLocale();
	}

	/**
	 * Singelton-Initialization
	 *
	 * @param string $shortLanguage Short language name
	 * @return Messages instance
	 */
	public static function getLanguageInstance($shortLanguage=null) {
		if (pMessages::$instance == null) {
			pMessages::$instance = new pMessages($shortLanguage);
		}
		return pMessages::$instance;
	}

	private function setLocale() {
		$lang = strtolower($this->getShortLanguageName());
		switch ($lang) {
			case 'de':
				$this->setTimezone('Europe/Zurich');
				setlocale(LC_TIME, array('de_CH.UTF8', 'de_DE@euro', 'de_DE.UTF8', 'de', 'ge'));
				break;
			default:
				setlocale(LC_TIME, array($lang.'_'.strtoupper($lang).'.UTF8', $lang));
				break;
		}
	}

	private function setTimezone($zone) {
		if (function_exists('date_default_timezone_exists')) {
			date_default_timezone_set($zone);
		} else {
			ini_set('date.timezone', $zone);
		}
	}

	/**
	 * Return the full LanguageName
	 *
	 * @return string Long LanguageName
	 */
	public function getLanguageName() {
		$lang = $this->getLanguage();
		if (!is_null($lang)) {
			return $lang->extendedLanguage;
		}
		return $this->lang->extendedLanguage;
	}

	/**
	 * Return the short LanguageName (2 chars)
	 *
	 * @return string short language name
	 */
	public function getShortLanguageName() {
		$lang = $this->getLanguage();
		if (!is_null($lang)) {
			return $lang->short;
		}
		return $this->lang->short;
	}

	/**
	 * Return the current LanguageID
	 *
	 * @return integer language id
	 */
	public function getLanguageId() {
		$lang = $this->getLanguage();
		if (!is_null($lang)) {
			return $lang->id;
		}
		return $this->lang->id;
	}

	/**
	 * Set some ObjectVars to the default selected Language
	 */
	private function setDefaultLanguageParameters() {
		$obj = null;
		if ($this->getLanguageObject($this->lang->short, $obj) === false) {
			$this->currentShortLanguage = $this->lang->short;
			$this->currentSection = 'parser';
			$this->setLanguageValue('parsed', 'false');
		}
	}

	/**
	 * Parse the LanguageFile for the current selected Language
	 */
	private function parseLanguageFile() {
		$this->xmlCount  = 0;
		$parser = xml_parser_create();
		xml_set_object($parser, $this);
		xml_set_element_handler($parser, "xmlParseStartTag", "xmlParseEndTag");

		if (is_file($this->languageFile)) {
			if (!(xml_parse($parser, file_get_contents($this->languageFile)))) {
				die(sprintf("XML error: %s at line %d", xml_error_string(xml_get_error_code($this->XmlParser)), xml_get_current_line_number($this->XmlParser)));
			}
		}
		xml_parser_free($parser);
	}

	/**
	 * Get a value from Language for coresponding field
	 *
	 * @param string $languageName Long Language-Name
	 * @param string $section Section in which the value shoud be
	 * @param string $name Field/Parameter to get
	 * @return string translated Language-Value
	 */
	public function getValue($languageName="", $section="", $name="") {
		$lang = $this->getLanguage($languageName);
		if ($lang->getValue('parser', 'parsed') == 'false') {
			$lang = $this->getLanguage(MASTER_LANGUAGE);
		}
		if (!is_null($lang)) {
			return $lang->getValue($section, $name);
		}
		return $section.'.'.$name;
	}

	/**
	 * Return the requested Language-Object
	 *
	 * @param string $languageName The Language-Name - none, extended or short
	 * @return pLanguage
	 * @access public
	 */
	public function getLanguage($languageName='') {
		$lang = null;
		if (empty($languageName)) {
			$languageName = $this->lang->short;
		}
		if ($this->getLanguageObject($languageName, $lang) !== false) {
			return $lang;
		}
		return null;
	}

	/**
	 * Return a LanguageObject
	 *
	 * @param string $languageName Short Language name
	 * @param object Pointer to set the language-object to
	 * @return boolean
	 */
	private function getLanguageObject($languageName, &$obj) {
		if (!is_array($this->languageContent)) {
			$this->languageContent = array();
		}
		foreach ($this->languageContent as $lang) {
			if (($lang->short == $languageName) || ($lang->extendedLanguage == $languageName)) {
				$obj = $lang;
				return true;
			}
		}
		return false;
	}

	/**
	 * Set a value for a LangauageParameter on given Language-Section
	 *
	 * @param string $name Parameter-Name
	 * @param string $value value to set on given parameter
	 */
	private function setLanguageValue($name, $value) {
		$lang = null;
		if ($this->getLanguageObject($this->currentShortLanguage, $lang) === false) {
			$l = new pLanguage($this->currentShortLanguage);
			if ($l->short == $this->currentShortLanguage) {
				$this->languageContent[] = $l;
				$this->getLanguageObject($this->currentShortLanguage, $lang);
				$lang->setValue('parser', 'parsed','true');
			}
		}
		if ($lang) {
			$lang->setValue($this->currentSection, $name, $value);
		}
	}

	/**
	 * XML-Parser Start-Tag-Function
	 *
	 * @param xmlParser $parser The XML-Parser
	 * @param string $name TagName
	 * @param array $attrs Attributes
	 */
	private function xmlParseStartTag($parser, $name, $attrs) {
		if ($this->xmlCount >= 1) {
			// handle the 'LANG'-Tag
			if ($name == "LANG") {
				$this->currentLanguage = $attrs['NAME'];
				$this->currentShortLanguage = strtolower($attrs['SHORT']);

			// Handle all Section-Tags
			} else if (in_array($name, $this->validXmlSections)) {
				$this->currentSection = strtolower($name);
			}

			// if the attribute has keys "ID" and "VALUE" we have a LanguageEntry
			if (array_key_exists('ID', $attrs) && array_key_exists('VALUE', $attrs)) {
				$this->setLanguageValue($attrs['ID'], $attrs['VALUE']);
			}
		}
		$this->xmlCount++;
	}

	/**
	 * XML-Parser-Function END-Tag-Function
	 *
	 * @param xmlParser $parser The XML-Parser
	 * @param string $name TagName
	 */
	private function xmlParseEndTag($parser, $name) {
		$this->XmlCount--;
	}

}

?>