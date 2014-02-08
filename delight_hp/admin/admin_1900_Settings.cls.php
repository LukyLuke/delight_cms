<?php

/**
 * The User-Configuration
 *
 */

class admin_1900_Settings extends admin_MAIN_Settings {

	var $userConfigFile;

	/**
		 * Class constructor
		 *
		 * @return admin_1900_Settings
		 */
	function __construct() {
		parent::__construct();
		$this->userConfigFile = $config = dirname($_SERVER['SCRIPT_FILENAME']).'/config/userconf.inc.php';
	}

	/**
		 * Call the correct function, based on _mainAction (param adm=)
		 */
	function createActionBasedContent() {
		global $SectionId;
		$userCheck = pCheckUserData::getInstance();
		if ($userCheck->checkAccess($this->_adminAccess)) {
			switch (1000 + $this->_mainAction) {
				case  1000:  $this->showMainScreen(); break;
				case  1001:  $this->showConfigScreen(); break;

				case (1000 + 50): $this->doXYZ();   break;
			}
		} else {
			$this->showNoAccess();
		}
	}

	/**
		 * Create the amin content for settings
		 *
		 */
	function showMainScreen() {
		$userCheck = pCheckUserData::getInstance();
		if ($userCheck->checkAccess($this->_adminAccess)) {
			global $SectionId;
			$match = null;
			$_baseLnk = "/".$this->_langShort."/".$this->_menuId."/adm=";
			$_adminHtml = $this->_getContent('globalSettings', 'SETTING_LIST');

			// Check for a SETTING-Tag in _adminHml
			if (preg_match("/(\[SETTINGS\])(.*?)(\[\/SETTINGS\])/smi", $_adminHtml, $match)) {
				$_settingHtml = $match[2];
				$_adminHtml = str_replace($match[0], "[SETTINGS]", $_adminHtml);
				$_adminHtml = str_replace("[CHANGE_SETTINGS_LINK]", $_baseLnk.'1901&textContent=', $_adminHtml);

				// get all settings
				$list = "";
				$settings = $this->getSettings();
				foreach ($settings as $k => $v) {
					$tmp = $_settingHtml;
					$tmp = str_replace("[SETTING_NAME]",  $v[0], $tmp);
					$tmp = str_replace("[SETTING_VALUE]", $this->getSettingVisual($v), $tmp);
					$tmp = str_replace("[SETTING_TYPE]",  $v[2], $tmp);
					$list .= $tmp;
				}
				$_adminHtml = str_replace("[SETTINGS]", $list, $_adminHtml);
			}

			$this->_content = $_adminHtml;
		} else {
			$this->showNoAccess();
		}
	}

	/**
		 * Shows content for a popup in which the user can change a value from a config-parameter
		 * 
		 * ATTENTION: Function exit at the end of the script because we don't need the page content
		 *            which is generated later
		 */
	function showConfigScreen() {
		$userCheck = pCheckUserData::getInstance();
		if ($userCheck->checkAccess($this->_adminAccess)) {
			global $SectionId, $TextContent;
			$error = '';
			$param = 'unknown';
			$_baseLnk = "/".$this->_langShort."/".$this->_menuId."/adm=";

			// Get the selected config-parameter
			if (strlen(trim($TextContent)) <= 0) {
				$error = 'unknown config parameter';
				$param = 'unknown';
			} else {
				$param = preg_replace("/[^a-zA-Z_-]/smi", "", $TextContent);
			}

			// get settings
			$setting = $this->getSettings($param);
				
			// only continue if the parameter was valid and exists in userconfig
			if (count($setting) == 1) {
				// get the SectionHTML
				$_adminHtml = $this->_getContent('globalSettings', 'CHANGE_PARAM');
				$_adminHtml = str_replace("[LANGUAGE_SELECTED]", $selLang, $_adminHtml);
				$_adminHtml = str_replace("[CREATE_STATIC_FILES_LINK]", $_baseLnk."1751&nm=true&textContent=".$selLang, $_adminHtml);
				$_adminHtml = str_replace("[STATE_STATIC_FILES_LINK]", $_baseLnk."1752&nm=true&textContent=".$selLang, $_adminHtml);

				$this->_content = $_adminHtml;
			}
		} else {
			$this->showNoAccess();
			$this->_content = '<html><head><title>no acceess</title></head><body>'.$this->_content.'</body></html>';
		}
		$this->_content = $this->ReplaceAjaxLanguageParameters($this->_content);
		echo $this->_content;
		exit();
	}

	/**
		 * Read the user_config_file and retunr all settings from there back as an Array
		 * key   is an index
		 * value is a array:  [0] the name, [1] the value, [2] the type
		 *
		 * @param string $param optional - only get this parameter
		 * @return array List with all settings from userconfiguration
		 */
	function getSettings($param='') {
		$list = array();
		$file = file($this->userConfigFile);
		$match = null;
		$color = null;

		foreach ($file as $k => $line) {
			// Check if the line begins with "define(" - so we know there is a valid setting-line
			if (preg_match("/^(define\(.)([a-zA-Z_]+)(.,)(.*?)(\);)/smi", $line, $match)) {
				$name = trim($match[2]);
				$value = trim($match[4]);
				$type = 'string';

				// Check if we only get parameter $param or if we get all
				if ( (strlen($param) <= 0) || ($name == $param)) {
					// Check if the value is a string
					if (preg_match("/^(.*?)([\d]{1,3})(,)([\d]{1,3})(,)([\d]{1,3})/smi", $value, $color)) {
						$type = 'color';
						$value = $color[2].",".$color[4].",".$color[6];
					} else if ( (substr($value, 0, 1) == "'") || (substr($value,0,1) == '"')) {
						$value = substr($value, 1, strlen($value) - 2);
						$type = 'string';
					} else if (($value == 'true') || ($value == 'false')) {
						$type = 'boolean';
					} else if (substr($value, 0, 1) == '$') {
						$type = 'variable';
					} else if (in_array(substr($value, 0, 1), range("a","z"))) {
						$type = 'command';
					} else if (in_array(substr($value, 0, 1), range(0,9))) {
						$type = 'number';
						if (substr_count($value, '.') > 0) {
							$value = doubleval($value);
						} else {
							$value = intval($value);
						}
					} else {
						$type = 'string';
					}

					// fill the list
					$list[] = array($name, $value, $type);
				}
			}
		}
		//			print_object($list);
		return $list;
	}

	/**
		 * Create html code to display a Setting-Value
		 *
		 * @param array $val the Setting to gte the visual from
		 * @return string the HTML-Code for current setting
		 */
	function getSettingVisual($val) {
		$html = "";
		if (strlen($val[1]) > 50) {
			$val[1] = substr($val[1], 0, 50).'...';
		}
		$val[1] = htmlentities($val[1]);

		switch (strtolower($val[2])) {
			case 'command':
				$html = '<span class="setting_command">'.$val[1].'</span>';
				break;

			case 'variable':
				$html = '<span class="setting_variable">'.$val[1].'</span>';
				break;

			case 'number':
				$html = '<span class="setting_number">'.$val[1].'</span>';
				break;

			case 'color':
				$html = '<div class="setting_color" style="background-color:rgb('.$val[1].');">'.$val[1].'</div>';
				break;

			case 'boolean':
				$html = '<span class="setting_boolean">[LANG_VALUE:set_'.$val[1].']</span>';
				break;

			case 'string':
			default:
				$html = '<span class="setting_string">'.$val[1].'</span>';
				break;
		}

		return $html;
	}

	function doXYZ() {
		$userCheck = pCheckUserData::getInstance();
		if ($userCheck->checkAccess($this->_adminAccess)) {
			global $SectionId, $_POST;

			$_defData = array();
			$_defData['FieldName...'] = " ";

			foreach ($_POST  as $k => $v) {
				if (array_key_exists($k, $_defData)) {
					$_defData[$k] = trim($v);
				}
			}
			$res = $this->DB->ReturnQueryResult($sql);

			$this->_content = $this->_getContent("forwardOnly");
			$this->_content = str_replace("[FORWARD_LINK]", "/".$this->_langShort."/".$this->_menuId."/adm=10000", $this->_content);
		} else {
			$this->showNoAccess();
		}
	}
}
?>