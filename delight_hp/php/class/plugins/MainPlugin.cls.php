<?php
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'pKdeMimeType.cls.php');
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'pTextEntry.cls.php');

abstract class MainPlugin {
	protected $LANG;
	protected $DB;

	protected $_selected;
	protected $_shortMenu;
	protected $_textId;
	protected $_hasCssContent;
	protected $_hasCssImportFile;
	protected $_hasScriptFile;
	protected $_cssFile;
	protected $_cssContent;
	protected $_scriptFile;
	protected $_specialCssFile;
	protected $_specialCssContent;
	protected $_specialScriptFile;
	protected $_contentOptions;
	protected $_tplDescription;
	protected $_admCssInclude;
	protected $_admCssContent;
	protected $_admScript;
	protected $_isTextPlugin;
	protected $LayoutSizeTags;
	protected $LayoutColorTags;
	protected $mainMenu;
	protected $textEntry;

	// Plain PHP5
	protected $kdeMime;

	// Parameters from TemplateTags "PLUGIN..."
	protected $tagContent;
	protected $tagParams;

	/**
	 * The new Initialization
	 */
	public function __construct() {
		$this->mainMenu = pMenu::getMenuInstance()->getMenuId();

		// TODO: DEPRECATED: Remove this two variables from all Plugins
		global $DB, $LANG;
		$this->DB = $DB;
		$this->LANG = $this->LANG;

		$this->_isTextPlugin = false;
		$this->_textId = 0;
		$this->_hasCssContent = false;
		$this->_hasCssImportFile = false;
		$this->_hasScriptFile = false;
		$this->_cssFile = '';
		$this->_cssContent = '';
		$this->_scriptFile = '';
		$this->_contentOptions = array();
		$this->_tplDescription = array();

		$this->_specialCssFile = '';
		$this->_specialCssContent = '';
		$this->_specialScriptFile = '';

		$this->_admCssInclude = array();
		$this->_admCssContent = '';
		$this->_admScript = array();
		// big     bigger  small  smaller
		$this->LayoutSizeTags = array('12pt', '14pt', '7pt', '5pt');
		$this->LayoutColorTags = array();
		$this->kdeMime = null;

		$this->_selected = pURIParameters::get('m', 0, pURIParameters::$INT);
		$this->_shortMenu = pURIParameters::get('sm', 0, pURIParameters::$STRING);
		if (!empty($this->_shortMenu)) {
			$this->_selected = $this->getShortMenuID();
		}

		$this->_contentOptions = $this->_parseOptionsTags($this->getAdditionalOptions(), $this->_contentOptions);
	}

	/**
	 * Check for a submitted ShortMenu and return the MainMenu-ID
	 *
	 * @param String $sm A ShortMenu String
	 * @return integer The MenuID which this ShohrtMenu identifies
	 */
	public function getShortMenuID($shortMenu=null) {
		$menu = pMenu::getMenuInstance();
		return $menu->getMenuId();
	}

	/**
	 * Get all Access-Groups for the requested Menu
	 *
	 * @param int $menu MenuID to get all Access-Groups from
	 * @param boolean $onlyRequired set to true if only required groups should be returned
	 * @return array [ stdClass:{ string:name, int:id, string:description }, ... ]
	 * @access public
	 */
	public function getAccessGroups($menu=null, $onlyRequired=false) {
		if (empty($menu)) {
			$menu = pMenu::getMenuInstance()->getMenuId();
		}
		$obj = new pMenuEntry($menu);
		return $obj->getAccessGroups($onlyRequired);
	}

	/**
	 * Get all UserGroup IDs from given User
	 *
	 * @param int $userId UserID to get Groups from
	 * @return array
	 * @access public
	 */
	public function getUserGroups($userId) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = 'SELECT [usrgrp.group] FROM [table.usrgrp] WHERE [usrgrp.user]='.(int)$userId.';';
		$db->run($sql, $res);
		$back = array();
		if ($res->getFirst()) {
			while ($res->getNext()) {
				$back[] = (int)$res->{$db->getFieldName('usrgrp.group')};
			}
		}
		return $back;
	}

	/**
	 * Get all Right-Names from given User
	 *
	 * @param int $userId userID to get rights from
	 * @return array
	 * @access public
	 */
	public function getUserRights($userId) {
		$back = array();
		$person = new pUserAccount($userId);
		$list = $this->getAllUserRightsAsObjectlist();
		foreach ($list as $right) {
			if ($person->hasRight($right->right)) {
				$back[] = strtolower($right->name);
			}
		}
		return $back;
	}

	/**
	 * Get all available UserRigths as a list of stdClass Objects
	 * valid properties are: right, name, description
	 *
	 * @return array[stdClass]
	 * @access protected
	 */
	protected function getAllUserRightsAsObjectlist() {
		$list = array();
		$lang = pMessages::getLanguageInstance();
		$constList = get_defined_constants(true);
		foreach ($constList['user'] as $const => $v) {
			if (substr($const, 0, 4) != 'RGT_') {
				continue;
			}
			$access = new stdClass();
			$access->right = $v;
			$access->name = $const;
			$access->description = $lang->getValue('', 'text', strtolower($const));
			array_push($list, $access);
		}
		return $list;
	}

	/**
	 * If the current User has access to this page
	 *
	 * @return boolean
	 * @access public
	 */
	public function checkLogin() {
		$groups = $this->getAccessGroups(null, true);
		$user = pCheckUserData::getInstance();
		$access = $user->checkAccess('fulladmin');
		$hasAuth = false;

		// Check each Group against the User-Groups to see if the user can access this page
		if (!$access && !empty($groups)) {
			$userGroups = $this->getUserGroups($user->getPerson()->get('userId'));
			foreach ($groups as $grp) {
				if ($grp->selected) {
					$hasAuth = true;
				}
				if (!$access && in_array($grp->id, $userGroups)) {
					$access = true;
				}
			}
		} else if (empty($groups)) {
			// If no Groups are defined, each user has access to the page
			$access = true;
			$hasAuth = false;
		}
		return ( (!$hasAuth) || ($hasAuth && $access) );
	}

	/**
	 * Return the EDIT-Function for use in HTML/Javascript
	 *
	 * @param integer $id The TextID
	 * @return string
	 * @access public
	 */
	public function getEditFunction($id) {
		return "javascript:alert('Plugin-Failure: missing function getEditFunction(\$id) in ".get_class($this)."');";
	}

	/**
	 * Return the CloseEDIT-Function for use in HTML/Javascript
	 *
	 * @param integer $id The TextID
	 * @return string
	 * @access public
	 */
	public function getCloseFunction($id) {
		return "javascript:alert('Plugin-Failure: missing function getCloseFunction(\$id) in ".get_class($this)."');";
	}

	/**
	 * Return Additional options for TextEditor
	 *
	 * @return string Options like defined in a Template
	 * @access public
	 * @abstract
	 */
	public abstract function getAdditionalOptions();

	/**
	 * Append additional textOptions to this Plugin
	 *
	 * @param string $opt Options to append
	 */
	function appendAdditionalTextOptions($opt='') {
		if (strlen($opt) > 0) {
			$this->_contentOptions = $this->parseOptionsTags($opt, $this->_contentOptions);
		}
	}

	/**
	 * Create the HTML-Source and return it
	 *
	 * @param string $method A special method-name - here we use it to switch between the complete List and just a list of news
	 * @param array $adm If shown under Admin-Editor, this Array must be a complete DB-likeness Textentry
	 * @param array $glob If included in a Template, this array has keys 'layout','template','num' with equivalent values
	 * @return string
	 * @access public
	 * @abstract
	 */
	public abstract function getSource($method="", $adminData=array(), $templateTag=array());

	/**
	 * Set the currently selected TextID
	 * ATTENTION: Use only if it's not the same as URI-Variable 'm'
	 *
	 * @param integer $selected Current TextID
	 */
	public function setSelected($selected) {
		$this->_selected = (int)$selected;
	}

	/**
	 * Check if this Plugin is a TextPlugin or not.
	 *
	 * @return boolean true if it's a TEXT-Plugin, otherwise false
	 */
	public function isTextPlugin() {
		return $this->_isTextPlugin;
	}

	/**
	 * Set the ID for the current Text
	 *
	 * @param integer $textId The TextID for the current ContentBlock
	 */
	public function setTextId($textId) {
		$this->_textId = $textId;
	}

	/**
	 * OBSOLETE: Use the new appendTextAdminAddons()
	 * Append all Administration-tags and scripts to a TextEntry
	 *
	 * @param unknown_type $_text
	 * @param unknown_type $_txtId
	 * @param unknown_type $_textData
	 * @param unknown_type $_dedtId
	 * @return unknown
	 */
	protected function appendAdminTextEditAddon($_text, $_txtId, $_textData, $_dedtId=0) {
		$userCheck = pCheckUserData::getInstance();
		$lang = pMessages::getLanguageInstance();

		$data = $_textData;
		if (is_array($_textData)) {
			$data = new pTextEntry(0);
			foreach ($_textData as $k => $v) {
				$data->{$k} = $v;
			}
		}

		if ($userCheck->checkAccess('content')) {
			$_titleField  = '<fieldset style="display:none;">';
			$_titleField .= '<input type="hidden" name="title_txt_'.$_txtId.'" id="title_txt_'.$_txtId.'" value="'.$data->title.'" />';
			$_titleField .= '<input type="hidden" name="layout_txt_'.$_txtId.'" id="layout_txt_'.$_txtId.'" value="'.$data->layout.'" />';
			$_titleField .= '<input type="hidden" name="options_txt_'.$_txtId.'" id="options_txt_'.$_txtId.'" value="'.$data->options.'" />';
			$_titleField .= '<input type="hidden" name="id_txt_'.$_txtId.'" id="id_txt_'.$_txtId.'" value="'.$_txtId.'" />';
			$_titleField .= '<input type="hidden" name="dedtid_txt_'.$_txtId.'" id="dedtid_txt_'.$_txtId.'" value="'.$_dedtId.'" />';
			$_titleField .= '</fieldset>';

			$functions  = '<div id="admin_text_'.$_txtId.'" class="admin_text" style="display:none;">';
			$functions .= '<div class="admin_entry admin_edit">'.$lang->getValue('', 'text', 'input_008').'</div>';
			$functions .= '<div class="admin_entry admin_delete">'.$lang->getValue('', 'text', 'input_005').'</div>';
			$functions .= '</div>';

			$_text  = '<div id="txt_'.$_txtId.'">'.$_text.'</div>'.$functions;
			$_text .= '<script type="text/javascript">if (!document.adminMenu'.$_txtId.') document.adminMenu'.$_txtId.'=new AdminMenuClass('.$_txtId.',\''.$this->getEditFunction($_txtId).'\');</script>';
			if (pURIParameters::get('textParser', 0, pURIParameters::$INT) == $_txtId) {
				$_text .= '<script type="text/javascript" language="Javascript">(function(){document.adminMenu'.$_txtId.'.editText();});</script>';
			}
			$_text = '<form action="#" method="post" onsubmit="return false;" id="form_txt_'.$_txtId.'">'.$_titleField.$_text.'</form>';
		}
		return $_text;
	}

	/**
	 * Append all Administration-tags and scripts to a TextContent
	 *
	 * @param string &$content Content to append administration-tags around
	 * @param TextEntry &$data TextEntry Object with the current Textentry
	 * @param integer $_dedtId Special ID to append as 'dedtid_txt_...' for the Editor
	 * @return string
	 */
	protected function appendTextAdminAddons(&$content, &$data, $_dedtId=0, $function=null) {
		$lang = pMessages::getLanguageInstance();
		$textId = $data->getTextId();
		$userCheck = pCheckUserData::getInstance();
		if ($userCheck->checkAccess('content')) {
			$_cont  = '<fieldset style="display:none;">';
			$_cont .= '<input type="hidden" name="title_txt_'.$textId.'" id="title_txt_'.$textId.'" value="'.$data->title.'" />';
			$_cont .= '<input type="hidden" name="layout_txt_'.$textId.'" id="layout_txt_'.$textId.'" value="'.$data->layout.'" />';
			$_cont .= '<input type="hidden" name="options_txt_'.$textId.'" id="options_txt_'.$textId.'" value="'.$data->options.'" />';
			$_cont .= '<input type="hidden" name="id_txt_'.$textId.'" id="id_txt_'.$textId.'" value="'.$textId.'" />';
			$_cont .= '<input type="hidden" name="dedtid_txt_'.$textId.'" id="dedtid_txt_'.$textId.'" value="'.$_dedtId.'" />';
			$_cont .= '</fieldset>';

			$content = '<div id="txt_'.$textId.'">'.$content.'</div>';
			$content .= '<div id="admin_text_'.$textId.'" class="admin_text" style="display:none;">';
			if (strtolower($data->plugin) == 'group') {
				$content .= '<div class="admin_entry admin_grouped">'.$lang->getValue('', 'text', 'input_009').'</div>';
				$content .= '<div class="admin_entry admin_grouprm">'.$lang->getValue('', 'text', 'input_010').'</div>';
			}
			$content .= '<div class="admin_entry admin_edit">'.$lang->getValue('', 'text', 'input_008').'</div>';
			$content .= '<div class="admin_entry admin_delete">'.$lang->getValue('', 'text', 'input_005').'</div>';
			$content .= '</div>';

			if (is_null($function)) {
				$function = $this->getEditFunction($textId);
			}

			$content .= '<script type="text/javascript">if (!document.adminMenu'.$textId.') document.adminMenu'.$textId.'=new AdminMenuClass('.$textId.',\''.$function.'\');</script>';
			if (pURIParameters::get('textParser', 0, pURIParameters::$INT) == $textId) {
				$content .= '<script type="text/javascript">window.setTimeout(function(){document.adminMenu'.$textId.'.editText();},1000);</script>';
			}
			$content = '<form action="#" method="post" onsubmit="return false;" id="form_txt_'.$textId.'">'.$_cont.$content.'</form>';
		}
	}

	/**
	 * Get additional Script-Files from this TextBlock
	 *
	 * @return string "::" seperated list with all ScriptFiles or FALSE if there are none
	 * @access public
	 */
	public function getScriptImportFile() {
		$this->_scriptFile = trim($this->_scriptFile);
		$this->_specialScriptFile = trim($this->_specialScriptFile);
		$back = '';
		if (strlen($this->_scriptFile) > 0) {
			$back .= $this->_scriptFile;
		}
		if (strlen($this->_specialScriptFile) > 0) {
			if (!empty($back)) {
				$back .= '::';
			}
			$back .= $this->_specialScriptFile;
		}
		if (!empty($back)) {
			return $back;
		} else {
			return false;
		}
		return false;
	}

	/**
	 * Get additional CSS-Files from this TextBlock
	 *
	 * @return string "::" seperated list with all CSSFiles or FALSE if there are none
	 * @access public
	 */
	public function getCssImportFile() {
		$this->_cssFile = trim($this->_cssFile);
		$this->_specialCssFile = trim($this->_specialCssFile);
		$back = '';
		if (strlen($this->_cssFile) > 0) {
			$back .= TEMPLATE_DIR.$this->_cssFile;
		}
		if (strlen($this->_specialCssFile) > 0) {
			if (!empty($back)) {
				$back .= '::';
			}
			$back .= TEMPLATE_DIR.$this->_specialCssFile;
		}
		if (!empty($back)) {
			return $back;
		} else {
			return false;
		}
	}

	/**
	 * Get additional CSS-Content from this TextBlock
	 *
	 * @return string Complete additional CSS-Content or FALSE if there is none
	 * @access public
	 */
	public function getCssContent() {
		$this->_cssContent = trim($this->_cssContent);
		$this->_specialCssContent = trim($this->_specialCssContent);
		if ( (strlen($this->_cssContent) > 0) || (strlen($this->_specialCssContent) > 0)) {
			return $this->_cssContent.chr(13).chr(10).$this->_specialCssContent;
		}
		return false;
	}

	/**
	 * Relace Chars to make a HTML-String JavaScript compatible
	 *   Replace \r, \n, \t with en empty string
	 *   Replace double-Quotes with the &#34;
	 *   Replace single-Quotes with the &#39;
	 *   Replace & with the &amp;
	 *   Replace < with the &lt;
	 *   Replace > with the &gt;
	 *
	 * @param string $html The HTML-Source which should be prepared
	 */
	protected function escapeHtmlForJavaScript($html) {
		$html = str_replace("\r", '', $html);
		$html = str_replace("\n", '', $html);
		$html = str_replace("\t", '', $html);
		$html = str_replace('"', '&#34;', $html);
		$html = str_replace("&", '&amp;', $html);
		$html = str_replace("<", '&lt;', $html);
		$html = str_replace(">", '&gt;', $html);
		return $html;
	}


	function getTemplateDescription($lang="") {
		if (strlen(trim($lang)) <= 0) {
			$l = pMessages::getLanguageInstance();
			$lang = $l->getShortLanguageName();
		}
		$lang = strToLower($lang);
		if (array_key_exists($lang, $this->_tplDescription)) {
			return $this->_tplDescription[$lang];
		} else {
			$_keys = array_keys($this->_tplDescription);
			return $this->_tplDescription[$_keys[0]];
		}
	}

	/* OVERRIDE ONLY*/
	protected function _readContentFile($lay="") {
	}

	function getContentOptions()
	{
		return $this->_contentOptions;
	}

	/**
	 * OBSOLETE: Use getTextEntryObject() for better usability
	 * Get and return the current TextEntry - identified by the ID ($this->_textId)
	 *
	 * @return array Whole TextEntry as an Array
	 */
	protected function _getTextEntryData() {
		$db = pDatabaseConnection::getDatabaseInstance();
		$sql  = "SELECT * FROM [table.txt] WHERE [txt.id]=".(int)$this->_textId;
		$res = null;
		$row = array();
		$db->run($sql, $res);
		if ($res->getFirst()) {
			$row[$db->getFieldName('txt.id')] = $res->{$db->getFieldName('txt.id')};
			$row[$db->getFieldName('txt.layout')] = $res->{$db->getFieldName('txt.layout')};
			$row[$db->getFieldName('txt.sort')] = $res->{$db->getFieldName('txt.sort')};
			$row[$db->getFieldName('txt.text')] = $res->{$db->getFieldName('txt.text')};
			$row[$db->getFieldName('txt.title')] = $res->{$db->getFieldName('txt.title')};
			$row[$db->getFieldName('txt.menu')] = $res->{$db->getFieldName('txt.menu')};
			$row[$db->getFieldName('txt.lang')] = $res->{$db->getFieldName('txt.lang')};
			$row[$db->getFieldName('txt.plugin')] = $res->{$db->getFieldName('txt.plugin')};
			$row[$db->getFieldName('txt.options')] = $res->{$db->getFieldName('txt.options')};
		}
		return $row;
	}

	/**
	 * Get the current TextEntry from Database and return it as a TextEntry Object
	 *
	 * @param integer $id TextID - if not given, _textId is used instead
	 * @return TextEntry The current TextEntry
	 * @access protected
	 */
	protected function getTextEntryObject($id=null) {
		$id = is_null($id) ? $this->_textId : $id;
		if ((!$this->textEntry instanceof pTextEntry) || ($this->textEntry->id != $id)) {
			$this->textEntry = new pTextEntry($id);
		}
		return $this->textEntry;
	}

	// Append an Anchor to the Text-Title
	function _appendTitleAnchor($title, $id)
	{
		$title = $title.'&nbsp;<a name="txt'.$id.'" class="anchor"></a>';
		return $title;
	}

	/**
		 * Replace all global Variables inside a Textentry
		 * this includes also the <dedtform ... /> Tag which will be created by TinyMCE-Plugin delightform
		 *
		 * @param string $txt The Text to replace all global variables
		 * @return string the $txt with all replaced variables
		 */
	function ReplaceTextVariables($txt) {
		if (!empty($txt)) {
			// Search for a <dedtform Tag and create based in this a Formular around the Textblock
			$txt = $this->SearchAndReplaceFormTag($txt);

			// Return the Text (but replace first all NewLines with <br />'s)
			//return nl2br($txt);
			return $txt;
		}
		return '';
	}

	/**
		 * Search and replace a <dedtform-Tag and create based on it's attributes a Form-Tag around the TextBlock
		 * The tag will not be replaced if this is an AdminRequest.
		 * Replace it only by static-site-creation
		 *
		 * @param String $txt Text to check for a dedtForm-Tag
		 * @return String Text with Form around if dedt-tag found and no admin request
		 */
	function SearchAndReplaceFormTag($txt) {
		$menu = pMenu::getMenuInstance();
		$lang = new pLanguage();
		$userCheck = pCheckUserData::getInstance();
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$dbg = false;

		if (!$userCheck->checkAccess('content') || $dbg) {
			// We use the Plugin-ID only for loading the Formular-Data, for the Config-File we use the TextId then
			$formTextId = $this->textEntry->id;
			if ($this->textEntry->plugin != 'TEXT') {
				$formTextId = $this->textEntry->text;
			}
			$sql = 'SELECT [formular.field],[formular.value] FROM [table.formular] WHERE [formular.textid]='.$formTextId.' AND [formular.plugin]=\''.$this->textEntry->plugin.'\';';
			$db->run($sql, $res);

			if ($res->getFirst()) {
				$uid = sha1(php_uname('n').$_SERVER['HTTP_HOST'].$formTextId);
				$config = array();
				$formValidate = 0;
				$formName = 'textform'.$this->_textId;
				$formEnc = 'application/x-www-form-urlencoded';
				if (defined('ABS_STATIC_DIR')) {
					$formConfigFile = ABS_STATIC_DIR.DIRECTORY_SEPARATOR.'formConfig-'.$this->_textId.'.php';
				} else {
					$formConfigFile = ABS_TEMPLATE_DIR.'static'.DIRECTORY_SEPARATOR.'formConfig-'.$this->_textId.'.php';
				}
				$formConfigFile = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $formConfigFile);

				$formTag  = '<fieldset style="display:none;">';
				$formTag .= '<input type="hidden" name="failure" value="/'.$lang->short.'/'.$menu->getShortMenuName().'" />';
				$formTag .= '<input type="hidden" name="referer" value="'.$uid.'" />';
				$formTag .= '<input type="hidden" name="tid" value="'.$this->_textId.'" />';
				$formTag .= '<input type="hidden" name="tpl" value="'.$this->textEntry->plugin.'" />';
				$formTag .= '</fieldset>';
				$formConfig  = '<?php'.chr(10);
				$formConfig .= '$_formConfig = array();'.chr(10);
				$formConfig .= '$_formConfig[\'referer\']="'.$uid.'";'.chr(10);

				// Go trough each attibute from dedt-tag and create the ConfigFile and set variables for later use
				while ($res->getNext()) {
					$field = $res->{$db->getFieldName('formular.field')};
					$value = $res->{$db->getFieldName('formular.value')};
					$config[$field] = $value;
					if (!empty($value)) {
						if ($field == 'encoding') {
							$formEnc = preg_replace('/[^a-z0-9_\/-]/smi', '', $value);
						} else if ($field == 'name') {
							$tmpName = preg_replace('/[^a-z0-9_]/smi', '', $value);
							$formName = preg_replace('/[^a-z0-9_-]/smi', '', (strlen($tmpName) > 0) ? $tmpName : $formName);
						} else if ($field == 'validate') {
							$formValidate = (int)$value;
						} else {
							$formConfig .= '$_formConfig[\''.$field.'\']="'.$value.'";'.chr(10);
						}
					}
				}
				$res = null;

				// extract all fieldnames from $txt which will be used in sendform.php
				$formConfig .= '$_formConfig[\'mail_fieldnames\']=array();'.chr(10);
				$fields = '';
				$match = array();
				if (preg_match_all('/\<(input|select|textarea)(.*?)(name=")([^"]+)(")(.*?)((title=")([^"]+)(")(.*?))?(\>)/smi', str_replace('\\"', '"', $txt), $match, PREG_SET_ORDER)) {
					foreach ($match as $fld) {
						if (strlen($fields) > 0) {
							$fields .= ',';
						}
						$fields .= $fld[4];
						$formConfig .= '$_formConfig[\'mail_fieldnames\'][\''.$fld[4].'\']="'.((strlen($fld[9])>0) ? $fld[9] : $fld[4]).'";'.chr(10);
					}
				}
				$formConfig .= '$_formConfig[\'mail_fields\']="'.$fields.'";'.chr(10);

				// Write the Form-ConfigFile
				$formConfig .= chr(10).'?>';
				if (is_file($formConfigFile)) {
					unlink($formConfigFile);
				}
				file_put_contents($formConfigFile, $formConfig);
				chmod($formConfigFile, 0777);

				// Create the Send-Script
				$sendFormScript  = "<script type=\"text/javascript\">/*<![CDATA[*/\n";

				// Function for sending the Form - without validation or called after successful validation
				//$sendFormScript .= "function s".$formName."() {";
				//$sendFormScript .= "document.getElementById('".$formName."').submit(); return true;";
				//$sendFormScript .= "};";

				// Create the Form-Validation part
				if ($formValidate > 0) {
					$config['mandatory_text_string']   = str_replace('\'', '\\\'', array_key_exists('mandatory_text_string',   $config) ? $config['mandatory_text_string']   : ' [LANG_VALUE:formular_004]');
					$config['mandatory_text_color']    = str_replace('\'', '\\\'', array_key_exists('mandatory_text_color',    $config) ? $config['mandatory_text_color']    : '#FDB987');
					$config['mandatory_number_string'] = str_replace('\'', '\\\'', array_key_exists('mandatory_number_string', $config) ? $config['mandatory_number_string'] : ' [LANG_VALUE:formular_002]');
					$config['mandatory_number_color']  = str_replace('\'', '\\\'', array_key_exists('mandatory_number_color',  $config) ? $config['mandatory_number_color']  : '#FDB987');
					$config['mandatory_email_string']  = str_replace('\'', '\\\'', array_key_exists('mandatory_email_string',  $config) ? $config['mandatory_email_string']  : ' [LANG_VALUE:formular_001]');
					$config['mandatory_email_color']   = str_replace('\'', '\\\'', array_key_exists('mandatory_email_color',   $config) ? $config['mandatory_email_color']   : '#FDB987');

					// mark a FormField as mandatory
					$sendFormScript .= "function m".$formName."(e,t,c) {";
					$sendFormScript .= "if (typeof(t)!='undefined' && t.length > 0){";
					$sendFormScript .= "var n = document.createElement('span');";
					$sendFormScript .= "n.appendChild(document.createTextNode(t));";
					$sendFormScript .= "n.setAttribute('title','formerror');";
					$sendFormScript .= "n.style.fontWeight='bold';";
					$sendFormScript .= "n.style.color=typeof(c)!='undefined'?c:'#cb0909';";
					$sendFormScript .= "if (e.nextSibling != null) {";
					$sendFormScript .= "e.parentNode.insertBefore(n, e.nextSibling);";
					$sendFormScript .= "} else {";
					$sendFormScript .= "e.parentNode.appendChild(n);";
					$sendFormScript .= "};";
					$sendFormScript .= "};";
					$sendFormScript .= "if (typeof(c)!='undefined' && c.length > 0){";
					$sendFormScript .= "e.style.backgroundColor=c;";
					$sendFormScript .= "};";
					$sendFormScript .= "};";

					// Check each field for class mandatory and check if it is empty if it holds the class
					$sendFormScript .= "function v".$formName."(b) {";
					$sendFormScript .= "var o=true,c,n,i,e,el;";
					$sendFormScript .= "el = document.getElementById('".$formName."').getElementsByTagName('span');";
					$sendFormScript .= "for (i = el.length; i > 0; i--) {";
					$sendFormScript .= "e = el[i-1];";
					$sendFormScript .= "if (e.title.indexOf('formerror') > -1) {";
					$sendFormScript .= "e.parentNode.removeChild(e);";
					$sendFormScript .= "}";
					$sendFormScript .= "}";
					$sendFormScript .= "el = document.getElementById('".$formName."').elements;";
					$sendFormScript .= "for (i = 0; i < el.length; i++) {";
					$sendFormScript .= "e = el[i];";
					$sendFormScript .= "n = e.nodeName.toLowerCase();";
					$sendFormScript .= "c = e.className.indexOf('mandatory');";
					$sendFormScript .= "if ((n == 'input') && (e.type == 'text')) {";
					$sendFormScript .= "if ((c > -1) && (e.value == '')) {";
					$sendFormScript .= "m".$formName."(e,'".$config['mandatory_text_string']."','".$config['mandatory_text_color']."');";
					$sendFormScript .= "o=false;";
					$sendFormScript .= "} else if ((e.value != '') && (e.className.indexOf('email') > -1) && (e.value.match(/^[a-z0-9][a-z0-9\._-]+\@[a-z0-9][a-z0-9\._-]+\.[a-z]+$/) == null)) {";
					$sendFormScript .= "m".$formName."(e,'".$config['mandatory_email_string']."','".$config['mandatory_email_color']."');";
					$sendFormScript .= "o=false;";
					$sendFormScript .= "} else if ((e.value != '') && (e.className.indexOf('number') > -1) && (e.value.match(/^[0-9]+$/) == null)) {";
					$sendFormScript .= "m".$formName."(e,'".$config['mandatory_number_string']."','".$config['mandatory_number_color']."');";
					$sendFormScript .= "o=false;";
					$sendFormScript .= "}";
					$sendFormScript .= "} else if ((c > -1) && (n == 'textarea') && (e.innerText == '')) {";
					$sendFormScript .= "m".$formName."(e,'".$config['mandatory_text_string']."','".$config['mandatory_text_color']."');";
					$sendFormScript .= "o=false;";
					$sendFormScript .= "} else if ((c > -1) && (n == 'input') && (e.type == 'checkbox') && !e.checked) {";
					$sendFormScript .= "m".$formName."(e,'".$config['mandatory_text_string']."','".$config['mandatory_text_color']."');";
					$sendFormScript .= "o=false;";
					$sendFormScript .= "} else if ((c > -1) && (n == 'input') && (e.type == 'radio') && !e.checked) {";
					$sendFormScript .= "m".$formName."(e,'".$config['mandatory_text_string']."','".$config['mandatory_text_color']."');";
					$sendFormScript .= "o=false;";
					$sendFormScript .= "};";
					$sendFormScript .= "};";
					$sendFormScript .= "if (o) {return s".$formName."();} else {m".$formName."(b,'[LANG_VALUE:formular_003]'); return false;};";
					$sendFormScript .= "};";
				}
				$sendFormScript .= "\n/*]]>*/</script>";

				// Search for a Button in the current Formular and insert an Attribute for sending the Formular
				$click = $formValidate ? 'v'.$formName.'(this);' : 's'.$formName.'();';
				//$txt = str_ireplace('class="button_submit"', 'class="button_submit" onclick="return '.$click.'"', $txt);
				//$txt = str_ireplace('class=\\"button_submit\\"', 'class="button_submit" onclick="return '.$click.'"', $txt);
				$txt = str_ireplace('class="button_submit"', 'class="button_submit"', $txt);
				$txt = str_ireplace('class=\\"button_submit\\"', 'class="button_submit"', $txt);
				$txt = str_ireplace('class="button_reset"', 'class="button_reset" onclick="document.getElementById(\''.$formName.'\').reset();return false;"', $txt);
				$txt = str_ireplace('class=\\"button_reset\\"', 'class="button_reset" onclick="document.getElementById(\''.$formName.'\').reset();return false;"', $txt);

				// Check for Preselected Values
				$selectionScript  = '<script type="text/javascript">/*<![CDATA[*/'.chr(10);
				$selectionScript .= 'function f'.$formName.'(){';
				$selectionScript .= 'var loc = window.location.search.replace(/\?/, "").replace(/&amp;/, "&").split("&");';
				$selectionScript .= 'for (var i = 0; i < loc.length; i++) {';
				$selectionScript .= 'var f = loc[i].split("=");';
				$selectionScript .= 'if (f.length > 1) {';
				$selectionScript .= 'var fields = document.getElementsByName(f.reverse().pop());';
				$selectionScript .= 'if (fields.length >= 1){';
				$selectionScript .= 'f = f.reverse().join("=");';
				$selectionScript .= 'fields[0].value = f;';
				$selectionScript .= '}';
				$selectionScript .= '}';
				$selectionScript .= '}';
				$selectionScript .= '};f'.$formName.'();'.chr(10);
				$selectionScript .= '/*]]>*/</script>';

				// append the Form-Tag and the Preselection-Script
				$txt = $sendFormScript.'<form id="'.$formName.'" enctype="'.$formEnc.'" method="post" action="'.MAIN_DIR.'/sendform.php"'.($formValidate ? ' onsubmit="return v'.$formName.'(this);"' : 'return true;').'>'.$formTag.$txt.'</form>'.$selectionScript;
			}
		}
		return $txt;
	}

	/**
	 * Replace all Language-Variables for AJAX or POPUP-Requests
	 *
	 * @param string $rep the html content to replace the language variables
	 * @return string the html content with replaced variables
	 */
	function ReplaceAjaxLanguageParameters($rep='') {
		$lang = pMessages::getLanguageInstance();
		$rep = preg_replace('/(\[LANG_VALUE\:)([\w\d]+)(\])/ie', '$lang->getValue("","txt","$2")', $rep);
		$rep = str_replace('[DATA_DIR]', '/v_'.DHP_VERSION.DATA_DIR, $rep);
		$rep = str_replace('[MAIN_DIR]', MAIN_DIR, $rep);
		$rep = str_replace('[LANG_SHORT]', $lang->getShortLanguageName(), $rep);
		return $rep;
	}

	// Replace special options in a Text
	// ($opt has format; #key1=value1##key2=value2##key3=value3#...)
	function ReplaceLayoutOptions($txt, $opt) {
		if (preg_match_all("/(\[OPTION:)(.*?)(,)(.*?)(\])/smi", $txt, $match)) {
			for ($i = 0; $i < count($match[0]); $i++) {
				// Check if there is a valid Replacement defined
				if (array_key_exists($match[2][$i], $this->_contentOptions)) {
					// Get the Value from the Text-Entry
					if (preg_match("/(\#".$match[2][$i]."=)(.*?)(\#)/smi", $opt, $optmatch)) {
						$optmatch[2] = explode(':', $optmatch[2]);
						$optmatch[2] = $optmatch[2][0];
						if (array_key_exists($optmatch[2], $this->_contentOptions[$match[2][$i]])) {
							$txt = str_replace($match[0][$i], $this->_contentOptions[$match[2][$i]][$optmatch[2]], $txt);
						} else {
							$keys = array_keys($this->_contentOptions[$match[2][$i]]);
							if ($keys[0] == 'edit_field') {
								$txt = str_replace($match[0][$i], $optmatch[2], $txt);
							} else if ($keys[0] == 'choose_field') {
								$txt = str_replace($match[0][$i], $optmatch[2], $txt);
							}
						}
					}

					// Replace it with the Default-Value or if it does not exists, replace it with an empty-String
					if (array_key_exists($match[4][$i], $this->_contentOptions[$match[2][$i]])) {
						$txt = str_replace($match[0][$i], $this->_contentOptions[$match[2][$i]][$match[4][$i]], $txt);
					} else {
						$txt = str_replace($match[0][$i], "", $txt);
					}
				} else {
					$txt = str_replace($match[0][$i], "", $txt);
				}
			}
		}

		// Replace openWindow-Link - Not again choosen images have a wrong link to open the Image through a click
		$match = array();
		if (preg_match_all('/openWindow\(([\'"])\/\/delight_hp\/images\/page\/([a-z0-9\.]+)\\1/smi', $txt, $match, PREG_SET_ORDER)) {
			$lang = pMessages::getLanguageInstance();
			$db = pDatabaseConnection::getDatabaseInstance();
			$res = null;
			foreach ($match as $m) {
				$sql = 'SELECT [img.id] FROM [table.img] WHERE [img.image]=\''.$m[2].'\';';
				$db->run($sql, $res);
				if ($res->getFirst()) {
					$txt = str_replace($m[0], 'openWindow('.$m[1].'/'.$lang->getShortLanguageName().'/image/'.$res->{$db->getFieldName('img.id')}.'/noslide'.$m[1], $txt);
				}
			}
		}

		return $txt;
	}

	// create HTML-Code with options from all templates available for use in an AJAX-Response
	function getAjaxTextOptions($tpl='none') {
		$back = "";
		$cnt = 0;
		$onChange = '';//'applyChange(this);';
		$name = '';

		// loop trough all options
		foreach ($this->_contentOptions as $k => $v) {
			$values = "";
			$name = $k;

			// Check for Edit-Field
			if (array_key_exists('edit_field',$v) && is_array($v['edit_field']) && array_key_exists('type',$v['edit_field'])) {
				$type = '';
				$func = '';

				switch (strtolower($v['edit_field']['type'])) {
					case 'password':
						$type = 'password';
						$func = '';
						break;

					case 'number':
					case 'integer':
						$type = 'text';
						$func = '';
						break;

					case 'string':
					default:
						$type = 'text';
						$func = '';
						break;
				}
				$values .= '<input type="'.$type.'" name="'.$name.'" onchange="TemplateDialog.'.$func.$onChange.'" style="width:100%;" value="'.(array_key_exists('value',$v['edit_field'])?$v['edit_field']['value']:'').'" />';

			} else if (array_key_exists('choose_field',$v) && is_array($v['choose_field']) && array_key_exists('values',$v['choose_field']) && is_array($v['choose_field']['values'])) {

				$_selected = ( (array_key_exists('selected',$v['choose_field']) && ((int)$v['choose_field']['selected'])>=0 ) ? (int)$v['choose_field']['selected'] : 0);
				$list = $v['choose_field']['values'];
				for ($i = 0; $i < count($list); $i++) {
					$values .= '<input type="radio" value="'.$list[$i].'" name="'.$name.'" onchange="'.$onChange.'"'.($i==$_selected ? ' checked="checked"' : '').' />'.$list[$i].'<br />';
				}

			} else {
				foreach ($v as $_k => $_v) {
					$values .= '<option value="'.$_k.'">'.$_k.'</option>';
				}
				$values = '<select style="width:100%;" name="'.$name.'" onchange="'.$onChange.'">'.$values.'</select>';
			}
			if ($cnt%2 == 0) {
				$bgcol = 'rgb(245,245,245)';
			} else {
				$bgcol = 'transparent';
			}
			$back .= '<tr><td style="border:1px inset #eee;background-color:'.$bgcol.';">'.ucfirst(str_replace("_", " ", $name)).'</td>';
			$back .= '<td style="border:1px inset #eee;background-color:'.$bgcol.';">';
			$back .= $values;
			$back .= '</td></tr>';
			$cnt++;
		}

		if (strlen($back) > 0) {
			$back = '<table cellpadding="2" cellpadding="0" style="width:100%;">'.$back.'</table>';
		}
		return str_replace("\"", "\\\"", $back);
	}

	/**
	 * Return all needed data from an Image
	 *
	 * @param integer $id Image-ID
	 * @param string $scale [w] for scale in width, [h] for scale in height
	 * @param integer $size max size for given $scale
	 * @param string $measure Get image-size in '%' or 'px'
	 * @param integer $maxWidth max width for small image
	 * @param integer $maxHeight max height for small image
	 * @return array All data from an Image
	 */
	public function GetImageData($id, $scale="", $size=0, $measure='px', $maxWidth=0, $maxHeight=0) {
		$obj =  $this->getImageObject($id, $scale, $size, $measure, $maxWidth, $maxHeight);
		$back = array();
		$back['thumb'] = array();
		$back['id'] = $obj->id;
		$back['src'] = $obj->src;
		$back['type'] = $obj->type;
		$back['name'] = $obj->name;
		$back['title'] = $obj->title;
		$back['descr'] = $obj->text;
		$back['width'] = $obj->width;
		$back['height'] = $obj->height;
		$back['real_width'] = $obj->real_width;
		$back['real_height'] = $obj->real_height;
		$back['size'] = $obj->size;
		$back['sectionid'] = $obj->section;
		$back['thumb']['src'] = $obj->thumb->src;
		$back['thumb']['width'] = $obj->thumb->width;
		$back['thumb']['height'] = $obj->thumb->height;
		$back['thumb']['type'] = $obj->thumb->type;
		return $back;
	}

	/**
	 * Return all needed data from an Image as an Object
	 *
	 * @param integer $id Image-ID
	 * @param string $scale [w] for scale in width, [h] for scale in height
	 * @param integer $size max size for given $scale
	 * @param string $measure Get image-size in '%' or 'px'
	 * @param integer $maxWidth max width for small image
	 * @param integer $maxHeight max height for small image
	 * @return stdClass All data from an Image
	 */
	public function getImageObject($id, $scale="", $size=0, $measure='px', $maxWidth=0, $maxHeight=0) {
		$lang = pMessages::getLanguageInstance();
		$back = new stdClass();
		$back->id     = 0;
		$back->src    = '';
		$back->name   = '';
		$back->type   = 0;
		$back->title  = '';
		$back->text   = '';
		$back->mime   = '';
		$back->mimecomment = '';
		$back->width  = 0;
		$back->height = 0;
		$back->real_width  = 0;
		$back->real_height = 0;
		$back->size = 0;
		$back->timestamp = 0;
		$back->date = '';
		$back->fulldate = '';
		$back->section = 0;
		$back->position = 0;
		$back->thumb = new stdClass();
		$back->thumb->src    = '';
		$back->thumb->width  = 0;
		$back->thumb->height = 0;
		$back->thumb->type   = 0;

		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$_res = null;

		$sql = "SELECT * FROM [table.img] WHERE [img.id]=".(int)$id.";";
		$db->run($sql, $res);
		if ($res->getFirst()) {
			// Get Image-title and description
			$_lang = array('title'=>'', 'text'=>'');
			$_sql  = "SELECT * FROM [table.imt] WHERE [imt.lang]=".$lang->getLanguageId()." AND [imt.image]=".(int)$id.";";
			$db->run($_sql, $_res);
			if ($_res->getFirst()) {
				$_lang['title'] = $_res->{$db->getFieldName('imt.title')};
				$_lang['text'] = $_res->{$db->getFieldName('imt.text')};
			}
			$_res = null;

			// temporary image variables
			$rel_path = "/images/page/";
			$rel_path_small = "/images/page/small/";
			$abs_path = realpath(dirname($_SERVER['SCRIPT_FILENAME'])).$rel_path;
			$abs_path_small = realpath(dirname($_SERVER['SCRIPT_FILENAME'])).$rel_path_small;
			$image_src = $res->{$db->getFieldName('img.image')};
			$image_id = $res->{$db->getFieldName('img.id')};
			$image_section = $res->{$db->getFieldName('img.section')};
			$image_date = $res->{$db->getFieldName('img.date')};
			$image_name = $res->{$db->getFieldName('img.name')};
			$position = $res->{$db->getFieldName('img.order')};
			if (empty($image_name)) {
				$image_name = $res->{$db->getFieldName('img.image')};
			}

			$mime = $this->_getMimeInfoObject($res->{$db->getFieldName('img.name')});

			if (file_exists($abs_path.$image_src)) {
				// Get image dimension and calculate lower values if the image is bigger than defined
				$image_dimension = getimagesize($abs_path.$image_src);
				if ( ($image_dimension[0] > SCREENSHOT_WIDTH_MAX) || ($image_dimension[1] > SCREENSHOT_HEIGHT_MAX) ) {
					$imgDimension = $this->_calcSquareSize($image_dimension[0], $image_dimension[1], SCREENSHOT_WIDTH_MAX, SCREENSHOT_HEIGHT_MAX);
				} else {
					$imgDimension = array($image_dimension[0], $image_dimension[1], 0, 0);
				}

				// Check if the small image exists
				if (!file_exists($abs_path_small.$image_src)) {
					$this->createSmallSizeImage($image_src);
				}

				// Get small-image dimension and calculate lower values if the image is bigger than defined
				$small_dimension = @getimagesize($abs_path_small.$image_src);
				$tmp = $this->_calcSquareSize($small_dimension[0], $small_dimension[1], $maxWidth, $maxHeight);
				$small_dimension[0] = $tmp[0];
				$small_dimension[1] = $tmp[1];
				if (!array_key_exists(2, $small_dimension)) {
					$small_dimension[2] = 'unknown';
				}

				// Create Back-Array for BIG-image
				$back->id     = $image_id;
				$back->src    = MAIN_DIR.$rel_path.$image_src;
				$back->file   = $image_src;
				$back->name   = $image_name;
				$back->type   = $image_dimension[2];
				$back->title  = $_lang['title'];
				$back->text   = $_lang['text'];
				$back->width  = $imgDimension[0];
				$back->height = $imgDimension[1];
				$back->real_width  = $image_dimension[0];
				$back->real_height = $image_dimension[1];
				$back->size        = filesize($abs_path.$image_src);
				$back->timestamp = $image_date;
				$back->date      = strftime('%d. %b. %Y', $image_date);
				$back->fulldate  = strftime('%d. %B %Y %R', $image_date);
				$back->section   = $image_section;
				$back->mime      = $mime['MimeType'];
				$back->mimecomment = $mime['Comment'];
				$back->position    = $position;
				$back->thumb = new stdClass();

				// Check if this is a Flash/SWF and not really an image
				if ( ($image_dimension[2] == 4) || ($image_dimension[2] == 13) ) {
					$_tmpSize = getimagesize($_SERVER['DOCUMENT_ROOT'].SCREENSHOT_FLASH);
					$back->thumb->src    = SCREENSHOT_FLASH;
					$back->thumb->width  = $_tmpSize[0];
					$back->thumb->height = $_tmpSize[1];
					$back->thumb->type   = $small_dimension[2];
				} else {
					$back->thumb->src    = MAIN_DIR.$rel_path_small.$image_src;
					$back->thumb->width  = $small_dimension[0];
					$back->thumb->height = $small_dimension[1];
					$back->thumb->type   = $small_dimension[2];
				}

				if ((int)$size > 0) {
					switch (trim(strtolower($scale))) {
						case 'w':
							if ($measure == '%') {
								$back->width  = round( $image_dimension[0] * ($size / 100) );
								$back->height = round( $image_dimension[1] * ($size / 100) );
							} else {
								$back->width  = $size;
								$back->height = round( $image_dimension[1] / ( $image_dimension[0] / $size ) );
							}
							break;

						case 'h':
						default:
							if ($measure == '%') {
								$back->width  = round( $image_dimension[0] * ($size / 100) );
								$back->height = round( $image_dimension[1] * ($size / 100) );
							} else {
								$back->width  = round( $image_dimension[0] / ( $image_dimension[1] / $size ) );
								$back->height = $size;
							}
							break;

					}
				}
			}
		}
		return $back;
	}

	/**
	 * Get the ImageID from the Next Image
	 *
	 * @param integer $id The current ImageID
	 * @return integer The ImageID or null
	 */
	public function getNextImageId($id) {
		$back = null;
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = 'SELECT [img.section],[img.order] FROM [table.img] WHERE [img.id]='.(int)$id.';';
		$db->run($sql, $res);
		if ($res->getFirst()) {
			$section = (int)$res->{$db->getFieldName('img.section')};
			$currentPos = (int)$res->{$db->getFieldName('img.order')};
			$res = null;
			$sql = 'SELECT [img.id],[img.order] FROM [table.img] WHERE [img.section]='.(int)$section.' ORDER BY [img.order] ASC;';
			$db->run($sql, $res);
			if ($res->getFirst()) {
				$next = false;
				while ($res->getNext()) {
					if ($next) {
						$back = $res->{$db->getFieldName('img.id')};
						break;
					} else if ((int)$res->{$db->getFieldName('img.id')} == $id) {
						$next = true;
					}
				}
			}
		}
		return $back;
	}

	/**
	 * Get the ID from the Previous Image
	 *
	 * @param integer $id The current ImageID
	 * @return integer The ImageID or null
	 */
	public function getPreviousImageId($id) {
		$back = null;
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = 'SELECT [img.section],[img.order] FROM [table.img] WHERE [img.id]='.(int)$id.';';
		$db->run($sql, $res);
		if ($res->getFirst()) {
			$section = (int)$res->{$db->getFieldName('img.section')};
			$currentPos = (int)$res->{$db->getFieldName('img.order')};
			$res = null;
			$sql = 'SELECT [img.id],[img.order] FROM [table.img] WHERE [img.section]='.(int)$section.' ORDER BY [img.order] ASC;';
			$db->run($sql, $res);
			if ($res->getFirst()) {
				$next = false;
				while ($res->getNext()) {
					if ((int)$res->{$db->getFieldName('img.id')} == $id) {
						$back = $next;
						break;
					}
					$next = (int)$res->{$db->getFieldName('img.id')};
				}
			}
		}
		return $back;
	}

	/**
	 * Return all needed data from a News as an Object
	 * If $id is null, there must be set $res and vice versa
	 *
	 * @param integer $id News-ID
	 * @param iDatabaseResult A Database-Result to build the News-Object from
	 * @return stdClass All data from a News
	 */
	public function getNewsObject($id=null, iDatabaseResult $res=null) {
		$back = new stdClass();
		$back->id = 0;
		$back->title = '';
		$back->text = '<p>&nbsp;</p>';
		$back->short = '<p>&nbsp;</p>';
		$back->plaintext = '';
		$back->timestamp = time();
		$back->date = strftime('%d. %b. %Y', time());
		$back->fulldate = strftime('%d. %B %Y %R', time());
		$back->section = 0;
		$back->feed_list = new stdClass();
		$back->feed_list->title = '';
		$back->feed_list->summarize = 3600;
		$back->feed_list->max_cache_age = 86400;
		$back->feed_list->feeds = array();

		$db = pDatabaseConnection::getDatabaseInstance();
		if (!is_null($id) && !($res instanceof iDatabaseResult)) {
			$res = null;
			$sql = "SELECT * FROM [table.new] WHERE [new.id]=".(int)$id.";";
			$db->run($sql, $res);
		}
		if (!$res->getFirst()) {
			return $back;
		}
		$back->id = $res->{$db->getFieldName('new.id')};
		$back->title = $res->{$db->getFieldName('new.title')};
		$back->text = $res->{$db->getFieldName('new.text')};
		$back->short = $res->{$db->getFieldName('new.short')};
		$back->plaintext = strip_tags($res->{$db->getFieldName('new.text')});
		$back->timestamp = strtotime($res->{$db->getFieldName('new.date')});
		$back->date = strftime('%d. %b. %Y', strtotime($res->{$db->getFieldName('new.date')}));
		$back->fulldate = strftime('%d. %B %Y %R', strtotime($res->{$db->getFieldName('new.date')}));
		$back->section = $res->{$db->getFieldName('new.section')};
		$back->rss = $res->{$db->getFieldName('new.rss')} > 0 ? true : false;
		$back->feed_list = new stdClass();

		// If this is an RSS-Feed-Definition we need to parse the Content as a INI-File
		if ($back->rss) {
			$ini = parse_ini_string($back->text, true);
			if (array_key_exists('feed', $ini)) {
				$back->feed_list->title = array_key_exists('title', $ini['feed']) ? $ini['feed']['title'] : '';
				$back->feed_list->summarize = array_key_exists('summarize', $ini['feed']) ? $ini['feed']['summarize'] : 3600;
				$back->feed_list->max_cache_age = array_key_exists('cacheage', $ini['feed']) ? $ini['feed']['cacheage'] : 86400;
				$back->feed_list->feeds = array_key_exists('feed', $ini['feed']) ? $ini['feed']['feed'] : array();
				// Reformat the News-Content to get a better look in AdminEditor
				$back->text = nl2br($back->text);
			} else {
				$back->text = '[feed]';
			}
		}
		if (is_null($id) && !($res instanceof iDatabaseResult)) {
			$res = null;
		}
		return $back;
	}

	/**
	 * Get Datas from an Image
	 *
	 * @param unknown_type $img
	 * @param unknown_type $scale
	 * @param unknown_type $size
	 * @param unknown_type $measure
	 * @return unknown
	 */
	function GetExternalImageData($img, $scale="", $size=0, $measure='px') {
		$back = array();
		if ($img) {
			// Reads some ImageData like SIZE and TYPE
			$img_size = @getimagesize($img);

			// Create Back-Array for BIG-image
			$back['src']    = $img;
			$back['type']   = $img_size[2];
			$back['title']  = $img;
			$back['descr']  = $img;
			$back['width']  = (integer)$img_size[0];
			$back['height'] = (integer)$img_size[1];
			$back['size']   = $this->getRemoteFileSize($img);

			// Append the SMALL-image to the BIG-image
			$back['thumb']['src']    = $img;
			$back['thumb']['width']  = (integer)$img_size[0];
			$back['thumb']['height'] = (integer)$img_size[1];
			$back['thumb']['type']   = $img_size[2];

			if ((integer)$size > 0) {
				switch (trim(strToLower($scale))) {
					case 'w':
						if ($measure == '%') {
							$back['width']  = round( (integer)$img_size[0] * ((integer)$size / 100) );
							$back['height'] = round( (integer)$img_size[1] * ((integer)$size / 100) );
						} else {
							$back['width']  = (integer)$size;
							$back['height'] = round( (integer)$img_size[1] / ( (integer)$img_size[0] / (integer)$size ) );
						}
						break;
					case 'h':
						if ($measure == '%') {
							$back['width']  = round( (integer)$img_size[0] * ((integer)$size / 100) );
							$back['height'] = round( (integer)$img_size[1] * ((integer)$size / 100) );
						} else {
							$back['width']  = round( (integer)$img_size[0] / ( (integer)$img_size[1] / (integer)$size ) );
							$back['height'] = (integer)$size;
						}
						break;
				}
			}
		}
		return $back;
	}

	/**
	 * Calculate a new Square based on same ratio
	 *
	 * @param int $origW The Original width
	 * @param int $origH The Original Height
	 * @param int $smallW Max new width
	 * @param int $smallH Max new Height
	 * @return array [width, height, X-offset, Y-offset]
	 * @access protected
	 */
	protected function _calcSquareSize($origW, $origH, $smallW, $smallH) {
		if ( ($origW <= $smallW) && ($origH <= $smallH) ) {
			// do not resize because the Image is smaller than the requested size
			$_w = $origW;
			$_h = $origH;
			if ((int)($smallH > 0) && ((int)$smallW > 0)) {
				$_x = ceil( ( (int)$smallW / 2 ) - ( $_w / 2 ) );
				$_y = ceil( ( (int)$smallH / 2 ) - ( $_h / 2 ) );
			} else {
				$_x = 0;
				$_y = 0;
			}
		} else if ((int)($smallH > 0) && ((int)$smallW > 0)) {
			// Calculate the small Image-Size and Position on white Background (The Background is optional)
			if ((int)$smallW > 0) {
				$_w = (int)$smallW;
				$_h = ceil( ((int)$origH * (int)$smallW) / (int)$origW );
				$_x = 0;
				$_y = ceil( ( (int)$smallH / 2 ) - ( $_h / 2 ) );
			}

			// Recalculate if the new height is to big (take the SCREENSHOT_HEIGHT as default-Value)
			if ( ((int)$smallH > 0) && ($_h > (int)$smallH) ) {
				$_w = ceil( ((int)$origW * (int)$smallH) / (int)$origH );
				$_h = (int)$smallH;
				$_x = ceil( ( (int)$smallW / 2 ) - ( $_w / 2 ) );
				$_y = 0;
			}
		} else {
			$_w = $origW;
			$_h = $origH;
			$_x = 0;
			$_y = 0;
		}

		// Width, Height, x-Offset, y-Offset
		return array($_w, $_h, $_x, $_y);
	}

	/**
	 * Get all Datas from a Programm, based on the Database-ID
	 *
	 * @param integer $id DatabaseID from Programm
	 * @return array Array with all datas from a programm
	 */
	function GetProgramData($id) {
		$obj = $this->getProgramObject($id);
		$array = array();
		foreach ($obj as $k => $v) {
			if (is_object($v)) {
				$x = $v;
				$v = array();
				foreach ($x as $y => $z) {
					$v[$y] = $z;
				}
			}
			$array[$k] = $v;
		}
		$array['sectionid'] = $obj->section;
		$array['descr'] = $obj->text;
		return $array;
	}

	/**
	 * get all datas from a program, based on its ID
	 *
	 * @param integer $id ProgramID
	 * @return stdClass All data from the program as an Object
	 */
	protected function getProgramObject($id) {
		$messages = pMessages::getLanguageInstance();
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$_res = null;

		$back = new stdClass();
		$back->id      = 0;
		$back->src     = '';
		$back->name    = '';
		$back->local   = 0;
		$back->public  = false;
		$back->secure  = false;
		$back->section = 0;
		$back->type    = '';
		$back->type_e  = '';
		$back->title   = '';
		$back->text    = '';
		$back->size    = 0;
		$back->date    = 0;
		$back->last    = 0;
		$back->loaded  = 0;
		$back->icon    = new stdClass();
		$back->icon->src      = '';
		$back->icon->comment  = '';
		$back->icon->width    = 0;
		$back->icon->height   = 0;

		$sql  = "SELECT * FROM [table.prg] WHERE [prg.id]=".(int)$id.";";
		$db->run($sql, $res);
		if ($res->getFirst()) {
			$_sql  = "SELECT * FROM [table.prt] WHERE [prt.lang]=".$messages->getLanguageId()." AND [prt.program]=".(int)$id.";";
			$db->run($_sql, $_res);
			if ($_res->getFirst()) {
				$_lang = array('title'=>$_res->{$db->getFieldName('prt.title')}, 'text'=>$_res->{$db->getFieldName('prt.text')});
			} else {
				$_lang = array('title'=>'', 'text'=>'');
			}
			$_res = null;

			$absoluteFile = ABS_DATA_DIR.'downloadfiles'.DIRECTORY_SEPARATOR.$res->{$db->getFieldName('prg.program')};
			$relativeFile = DATA_DIR.'downloadfiles/'.$res->{$db->getFieldName('prg.program')};
			if (file_exists($absoluteFile)) {
				// Get file-informations
				$fstat = stat($absoluteFile);

				// NO MORE DOWLOAD LOGGING
				/*// Get number of downloads
				$_sql  = "SELECT COUNT([dll.file]) AS num FROM [table.dll] WHERE [dll.file]=".$res->{$db->getFieldName('prg.id')}."';";
				$db->run($_sql, $_res);
				if ($_res->getFirst()) {
					$numDownloaded = $_res->num;
				} else {
					$numDownloaded = 0;
				}
				$_res = null;*/

				// Get last download
				/*$_sql  = "SELECT [dll.time] as last FROM [table.dll] WHERE [dll.file]=".$res->{$db->getFieldName('prg.id')}."' LIMIT 0,1";
				$db->run($_sql, $_res);
				if ($_res->getFirst()) {
					$fstat[8] = (int)strtotime($_res->last);
				} else {
					$fstat[8] = $fstat[9];
				}
				$_res = null;*/

				// Get the MimeInformations
				$mime = $this->_getMimeInfoObject($res->{$db->getFieldName('prg.name')});

				// Create Object
				$back->id      = $res->{$db->getFieldName('prg.id')};
				$back->src     = $relativeFile;
				$back->name    = $res->{$db->getFieldName('prg.name')};
				$back->local   = $res->{$db->getFieldName('prg.local')};
				$back->public  = (!(boolean)$res->{$db->getFieldName('prg.register')});
				$back->secure  = (!(boolean)$res->{$db->getFieldName('prg.secure')});
				$back->section = $res->{$db->getFieldName('prg.section')};
				$back->type    = $mime['MimeType'];
				$back->type_e  = $mime['Comment'];
				$back->title   = $_lang['title'];
				$back->text    = $_lang['text'];
				$back->size    = $fstat[7];
				$back->date    = $fstat[9];
				$back->last    = $fstat[9];//$fstat[8];
				$back->loaded  = 0;//$numDownloaded;

				$back->icon->src      = $mime['IconRelative'];
				$back->icon->comment  = $mime['Comment'];
				$back->icon->width = 32;
				$back->icon->height = 32;
			}
		}
		return $back;
	}

	/**
		 * Return a List with all needed Icons for known Filetypes
		 *
		 * @return array Keys identifies the IconName, the value is a List with mimeTypes
		 */
	function _getNeededFiletypeIcons() {
		if ($this->kdeMime == null) {
			$this->kdeMime = new pKdeMimeType(MIMELNK_PATH);
		}
		return $this->kdeMime->checkIconAviability(realpath(dirname($_SERVER['SCRIPT_FILENAME'])).'/template/images/mimetypes/');
	}

	/**
		 * Return a localized Filetype / Mimetype name
		 *
		 * @param string $file Filename to check
		 * @return string Localized Mime-/Filetype-Name
		 */
	function _getLocalizedFileType($file) {
		global $langshort;
		if ($this->kdeMime == null) {
			$this->kdeMime = new pKdeMimeType(MIMELNK_PATH);
		}
		$mime = $this->kdeMime->getMimeInfo($file, $langshort);
		return $mime['Comment'];
	}

	/**
		 * Get the Iconname, based on a Filename-Extension
		 * Returns just the Iconname, no extension, no path, etc.
		 *
		 * @param string $file Filename to get an Icon for
		 * @return String Name of the Icon to use for this File
		 */
	function _getFileTypeIcon($file) {
		global $langshort;
		if ($this->kdeMime == null) {
			$this->kdeMime = new pKdeMimeType(MIMELNK_PATH);
		}
		$mime = $this->kdeMime->getMimeInfo($file, $langshort);
		return $mime['Icon'];
	}

	/**
	 * Get Mime.Informations, based on a Filename-Extension
	 * Returns just the Iconname, no extension, no path, etc.
	 *
	 * @param string $file Filename to get an Icon for
	 * @return String Name of the Icon to use for this File
	 */
	public function _getMimeInfoObject($file) {
		if ($this->kdeMime == null) {
			$this->kdeMime = new pKdeMimeType(MIMELNK_PATH);
		}
		$messages = pMessages::getLanguageInstance();
		$mime = $this->kdeMime->getMimeInfo($file, $messages->getShortLanguageName());
		$mime['IconAbsolute'] = realpath(dirname($_SERVER['SCRIPT_FILENAME'])).'/template/images/mimetypes/'.$mime['Icon'].'.png';
		if (file_exists($mime['IconAbsolute'])) {
			$mime['IconRelative'] = MAIN_DIR.'/template/images/mimetypes/'.$mime['Icon'].'.png';

		} else if (file_exists(realpath(dirname($_SERVER['SCRIPT_FILENAME'])).'/images/mimetypes/'.$mime['Icon'].'.png')) {
			$mime['IconAbsolute'] = realpath(dirname($_SERVER['SCRIPT_FILENAME'])).'/images/mimetypes/'.$mime['Icon'].'.png';
			$mime['IconRelative'] = MAIN_DIR.'/images/mimetypes/'.$mime['Icon'].'.png';

		} else if (file_exists(realpath(dirname($_SERVER['SCRIPT_FILENAME'])).'/template/images/mimetypes/unknown.png')) {
			$mime['IconAbsolute'] = realpath(dirname($_SERVER['SCRIPT_FILENAME'])).'/template/images/mimetypes/unknown.png';
			$mime['IconRelative'] = MAIN_DIR.'/template/images/mimetypes/unknown.png';

		} else {
			$mime['IconAbsolute'] = realpath(dirname($_SERVER['SCRIPT_FILENAME'])).'/images/mimetypes/unknown.png';
			$mime['IconRelative'] = MAIN_DIR.'/images/mimetypes/unknown.png';
		}
		return $mime;
	}

	/**
	 * Return the real MimeType for a File
	 *
	 * @param string $file the Filename to get the Mimetype from
	 * @return string MimeType from File
	 */
	function _getRealFileType($file) {
		global $langshort;
		if ($this->kdeMime == null) {
			$this->kdeMime = new pKdeMimeType(MIMELNK_PATH);
		}
		$mime = $this->kdeMime->getMimeInfo($file, $langshort);
		return $mime['MimeType'];
	}

	/**
	 * Get the default Layout/Template
	 *
	 * @return string
	 * @access protected
	 */
	protected function getDefaultLayoutfile() {
		$back = 'none';
		$dir = ABS_TEMPLATE_DIR;
		foreach (scandir($dir) as $k => $v) {
			if ( (strlen($v) > 4) && (strrpos($v, '.') > 4) && (substr($v, 0, 4) == 'lay_') && (substr($v, strrpos($v, '.')+1, 3) == 'tpl') ) {
				$back = $v;
				break;
			}
		}
		return $dir.'/'.$back;
	}

	/**
	 * Read a LayoutFile with all available Content-Sections
	 *
	 * @param string $layout Template to read
	 * @param string $type Layout-Type from Template to get
	 * @return string
	 * @access protected
	 */
	protected function _readTemplateFile($layout, $type=null) {
		$layout_special = ABS_TEMPLATE_DIR."/".$layout;
		$layout = ABS_TEMPLATE_DIR."/lay_".$layout.".tpl";
		// If the TemplateFile does not exists, get the first we find in the Template-directory
		if (is_file($layout_special) && is_readable($layout_special)) {
			$layout = $layout_special;
		}
		if (!is_file($layout) || !is_readable($layout)) {
			$layout = $this->getDefaultLayoutfile();
		}

		// Check for the File again, just to be sure if the method to get the default-layout cannot find one
		if (is_file($layout) && is_readable($layout)) {
			$cont = file_get_contents($layout);

			// Check for [SCRIPT_INCLUDE]
			if (preg_match("/(\[SCRIPT_INCLUDE\])(.*?)(\[\/SCRIPT_INCLUDE\])/smi", $cont, $match)) {
				$this->_scriptFile = $match[2];
			}

			// Check for [STYLE_INCLUDE]
			if (preg_match("/(\[STYLE_INCLUDE\])(.*?)(\[\/STYLE_INCLUDE\])/smi", $cont, $match)) {
				$this->_cssFile = $match[2];
			}

			// Check for [STYLE_CONTENT]
			if (preg_match("/(\[STYLE_CONTENT\])(.*?)(\[\/STYLE_CONTENT\])/smi", $cont, $match)) {
				if (!empty($this->_cssContent)) {
					$this->_cssContent .= chr(13).chr(10);
				}
				$this->_cssContent .= $match[2];
			}

			// Check for [OPTIONS]
			$this->_contentOptions = $this->_parseOptionsTags($cont, $this->_contentOptions);

			// Check for [TEXT_SIZE]
			if (preg_match_all("/(\[TEXT_SIZE:)(big|bigger|small|smaller)(\])([\d]+?)(pt|pc|in|mm|cm|px|em|ex|\%)(\[\/TEXT_SIZE\])/smi", $cont, $match)) {
				for ($i = 0; $i < count($match[0]); $i++) {
					switch (strToLower($match[2][$i])) {
						case 'big':     $this->LayoutSizeTags[0] = $match[4][$i].$match[5][$i]; break;
						case 'bigger':  $this->LayoutSizeTags[1] = $match[4][$i].$match[5][$i]; break;
						case 'small':   $this->LayoutSizeTags[2] = $match[4][$i].$match[5][$i]; break;
						case 'smaller': $this->LayoutSizeTags[3] = $match[4][$i].$match[5][$i]; break;
					}
				}
			}

			// Check for [TEXT_COLOR]
			if (preg_match_all("/(\[TEXT_COLOR:)([\d]+?)(\])(.*?)(\[\/TEXT_COLOR\])/smi", $cont, $match)) {
				for ($i = 0; $i < count($match[0]); $i++) {
					$this->LayoutColorTags[(integer)$match[2][$i]] = $match[4][$i];
				}
			}

			// Check fo a Description in current language
			if (preg_match("/(\[DESCR:)(.*?)(\])(.*?)(\[\/DESCR\])/smi", $cont, $match)) {
				$this->_tplDescription[$match[2]] = $match[4];
			}

			// Check for a [LAYOUT]...[/LAYOUT] and reset the content
			if ($type != null) {
				$layAdd = ':'.$type;
			} else {
				$layAdd = '';
			}
			if (preg_match("/(\[LAYOUT".$layAdd."\])(.*?)(\[\/LAYOUT\])/smi", $cont, $match)) {
				$back = $match[2];
			} else if (empty($layAdd) && preg_match("/(\[LAYOUT:default\])(.*?)(\[\/LAYOUT\])/smi", $cont, $match)) {
				$back = $match[2];
			}
			unset($cont);
		} else {
			// If no Layout-File exists, just create a simple-plain-layout...
			$back = '[CAT_TITLE]<h1 style="padding:0px;margin:0px;padding-bottom:10px;">[TITLE]</h1>[/CAT_TITLE][ADMIN_FUNCTIONS][CAT_CONTENT]<br />'.chr(10).'[TEXT]<br />'.chr(10).'[/CAT_CONTENT]<br />'.chr(10);
		}

		return $back;
	}

	/**
	 * DEPRECATED - use parseOptionsTags($content, $opt) instead
	 */
	protected function _parseOptionsTags($content, $opt=array()) {
		return $this->parseOptionsTags($content, $opt);
	}

	/**
	 * Parse the Option-Tags and return them as an Array
	 *
	 * @param string $content Template-Content to read the options from
	 * @param array $opt Additional options defined inside the Source of the PHP-plugin
	 * @return unknown
	 */
	protected function parseOptionsTags($content, $opt=array()) {
		$match = null;
		$options = null;
		if (!is_array($opt)) {
			$opt = array();
		}

		if (preg_match("/(\[OPTIONS\])(.*?)(\[\/OPTIONS\])/smi", $content, $match)) {
			if (preg_match_all("/(\[)(.*?)(_)((.*?)(((_)(.*?)))?)(\])(.*?)(\[\/\\2\\3\\4\])/smi", $match[2], $options)) {
				for ($x = 0; $x < count($options[0]); $x++) {
					if (strlen(trim($options[9][$x])) > 0) {
						$opt[trim($options[2][$x]).'_'.trim($options[5][$x])][trim($options[9][$x])] = $options[11][$x];
					} else {
						// an Edit-Field
						if (strtolower(substr($options[11][$x], 0, 5)) == 'edit:') {
							if (preg_match("/^(edit:)(.*?)(((:)(.*))?)$/smi", $options[11][$x], $__match)) {
								$opt[trim($options[2][$x]).'_'.trim($options[5][$x])]['edit_field']['type']  = strtolower($__match[2]);
								$opt[trim($options[2][$x]).'_'.trim($options[5][$x])]['edit_field']['value'] = strtolower($__match[6]);
							}
						} else if (strtolower(substr($options[11][$x], 0, 7)) == 'choose:') {
							// strip out 'choose:' and the possible ':selectedIndex' on the end
							if (preg_match("/^(choose:)(.*?)(((:)(.*))?)$/smi", $options[11][$x], $__match)) {
								$_sel  = count($__match) > 4 ? $__match[6] : 1;
								$_list = explode(',', $__match[2]);
							}
							$opt[trim($options[2][$x]).'_'.trim($options[5][$x])]['choose_field']['selected'] = (integer)$_sel - 1;
							foreach ($_list as $k => $v) {
								$opt[trim($options[2][$x]).'_'.trim($options[5][$x])]['choose_field']['values'][] = $v;
							}
						} else {
							// Any single-options
							$opt[trim($options[2][$x])][trim($options[5][$x])] = $options[11][$x];
						}
					}
				}
			}
		}
		return $opt;
	}

	/**
	 * Get the Content from a simple tag from a string
	 * The Tag in the String should look like [TAG:section]...[/TAG]
	 * but it can also be a simple tag [TAG]...[/TAG]
	 *
	 * @param string $tagName Tag to ectract teh content from
	 * @param string &$content The Content to get the Tag-Content from
	 * @param string $section if defined, check for a tag with defined section
	 * @return string The TagContent (an empty string if there was nothing)
	 */
	protected function getSimpleTagFromContent($tagName, &$content, $section=null) {
		$back = '';
		$match = null;
		if (!empty($section) && preg_match("/(\[".$tagName.")((:".$section.")?)(\])(.*?)(\[\/".$tagName."\])/smi", $content, $match)) {
			$back = $match[5];
		}
		if (empty($back) && preg_match("/(\[".$tagName."\])(.*?)(\[\/".$tagName."\])/smi", $content, $match)) {
			$back = $match[2];
		}
		unset($match);
		return $back;
	}

	/**
	 * reads an Admin-Templatefile
	 *
	 * @param string $file Filename/Template to read
	 * @param string $subSection Section from Template to get
	 * @return string
	 * @access protected
	 */
	protected function _getAdminContent($file='', $subSection="") {
		$cont = '';
		if (strlen(trim($file)) > 0) {
			$file = "./template/adm_".$file.".tpl";
			if (file_exists($file)) {
				if ($fp = fopen($file, "r")) {
					$cont = fread($fp, filesize($file));
				}
				@fclose($fp);
			}
		}

		// Check for [INCLUDE:"..."] Tag
		$cont = preg_replace("/(\[INCLUDE:)(.*?)(\])/smie", '@file_get_contents("./template/$2")', $cont);

		// Check for any optional CSS-Includes
		if (preg_match_all("/(\[STYLE_INCLUDE\])(.*?)(\[\/STYLE_INCLUDE\])/smi", $cont, $match)) {
			for ($x = 0; $x < count($match); $x++) {
				$this->_admCssInclude[] = $match[2][$x];
				$cont = str_replace($match[0][$x], "", $cont);
			}
		}

		// Check for any optional CSS-Content
		if (preg_match_all("/(\[STYLE_CONTENT\])(.*?)(\[\/STYLE_CONTENT\])/smi", $cont, $match)) {
			for ($x = 0; $x < count($match); $x++) {
				$this->_admCssContent .= "\n".$match[2][$x];
				$cont = str_replace($match[0][$x], "", $cont);
			}
		}

		// Check for any optional SCRIPT-Includes
		if (preg_match_all("/(\[SCRIPT_INCLUDE\])(.*?)(\[\/SCRIPT_INCLUDE\])/smi", $cont, $match)) {
			for ($x = 0; $x < count($match); $x++) {
				$this->_admScript[] = $match[2][$x];
				$cont = str_replace($match[0][$x], "", $cont);
			}
		}

		$cont = str_replace("[LAYOUT]", "", $cont);
		$cont = str_replace("[/LAYOUT]", "", $cont);

		if (strlen(trim($subSection)) > 0) {
			if (preg_match("/(\[".strtoupper($subSection)."\])(.*?)(\[\/".strtoupper($subSection)."\])/smi", $cont, $match))
			$cont = $match[2];
		}

		return $cont;
	}

	/**
	 * Create a small Image for a Thumbnail-View or other things
	 *
	 * @param string $real_image Imagename to create the small one
	 * @access protected
	 */
	protected function createSmallSizeImage($real_image) {
		// Needed base-Variables
		$small_image = realpath(dirname($_SERVER['SCRIPT_FILENAME']))."/images/page/small/".$real_image;
		$real_image  = realpath(dirname($_SERVER['SCRIPT_FILENAME']))."/images/page/".$real_image;
		$image_info  = @getImageSize($real_image);

		// Check for a valid Image-Format (if not, break...)
		if ( !( in_array((integer)$image_info[2], array(1,2,3,9,10,11,12)) ) ) {
			return;
		}

		$imgDim = $this->_calcSquareSize($image_info[0], $image_info[1], SCREENSHOT_WIDTH, SCREENSHOT_HEIGHT);
		$_w = $imgDim[0];
		$_h = $imgDim[1];
		$_x = $imgDim[2];
		$_y = $imgDim[3];

		// Create array from Background-Color
		$backgroundColor = explode(",", SCREENSHOT_BACKGROUND);

		if (SCREENSHOTS_USE_GD) {
			// Create a GD-Image from Orriginal
			if ( ($image_info[2] == "2") || ($image_info[2] == "9") || ($image_info[2] == "10") || ($image_info[2] == "11") || ($image_info[2] == "12")) {
				// if it's a JPEG
				$imageReal = imageCreateFromJpeg($real_image);
			} else if ($image_info[2] == "3") {
				// if it's a PNG
				$imageReal = imageCreateFromPng($real_image);
			} else if ($image_info[2] == "1") {
				// if it's a GIF
				$imageReal = imageCreateFromGif($real_image);
			} else {
				// Other images are not suportet by this way, create an empty one
				$imageReal = ImageCreate($image_info[0], $image_info[1]);
			}

			// Create an empty new Image with THUMBNAILED Size
			if (SCREENSHOTS_USE_BG) {
				// Create the defined Thumbnail
				if (function_exists("imagecreatetruecolor") && ($image_info[2] != "1"))
				$smallImg = ImageCreateTrueColor(SCREENSHOT_WIDTH, SCREENSHOT_HEIGHT);
				else
				$smallImg = ImageCreate(SCREENSHOT_WIDTH, SCREENSHOT_HEIGHT);
				$bgColor  = ImageColorAllocate($smallImg, $backgroundColor[0], $backgroundColor[1], $backgroundColor[2]);
				ImageFill($smallImg, 0, 0, $bgColor);
			} else {
				// Create the calculated Thumbnail
				if (function_exists("imagecreatetruecolor") && ($image_info[2] != "1"))
				$smallImg = ImageCreateTruecolor($_w, $_h);
				else
				$smallImg = ImageCreate($_w, $_h);
				$bgColor  = ImageColorAllocate($smallImg, $backgroundColor[0], $backgroundColor[1], $backgroundColor[2]);
				ImageFill($smallImg, 0, 0, $bgColor);

				// Destroy the calculated _x and _y because the Image should be inserted at 0,0
				$_x = 0;
				$_y = 0;
			}

			// Copy the Original Image to the small one (copy it resampled if this function is available)
			if (function_exists("ImageCopyResampled"))
			imageCopyResampled($smallImg, $imageReal, $_x, $_y, 0, 0, $_w, $_h, (integer)$image_info[0], (integer)$image_info[1]);
			else
			imageCopyResized($smallImg, $imageReal, $_x, $_y, 0, 0, $_w, $_h, (integer)$image_info[0], (integer)$image_info[1]);

			// Destroy the Original-Image
			imageDestroy($imageReal);

			// Write out the Image to the thumbnail-File
			ImageColorDeallocate($smallImg, $bgColor);
			imageJpeg($smallImg, $small_image);
			imageDestroy($smallImg);

		} else {
			/*  USE IMAGEMAGICK - convert */

			$conv = exec("whereis convert | awk '{print \$2}'");
			if (trim($conv) != "") {
				$cmd1 = '[ ! -d "'.dirname($small_image).'" ] && mkdir -p '.dirname($small_image).' && chmod 0777 '.dirname($small_image);

				if (SCREENSHOTS_USE_BG) {
					// convert -size 160x180 xc:white -draw "image over 20,0 121,180 'base.jpg'" base_convert.jpg
					$cmd2  = $conv.' -size '.SCREENSHOT_WIDTH.'x'.SCREENSHOT_HEIGHT.' xc:white';
					$cmd2 .= ' -draw "image over '.$_x.','.$_y.' '.$_w.','.$_h.' \''.$real_image.'\'" '.$small_image.'';
				} else {
					// convert -size 160x180 $real_image $small_image
					$cmd2  = $conv.' -size '.$_w.'x'.$_h.' '.$real_image.' '.$small_image;
				}

				$cmd4 = 'chmod 0777 '.$small_image;
				system($cmd1);
				system($cmd2);
				system($cmd4);
			}
		}

		// reset the filepermissions
		if (file_exists($small_image)) {
			chmod($small_image, 0777);
		}
	}

	/**
	 * Reads a Remote-File and return the FileSize from it
	 *
	 * @param string $url File to read from remote
	 * @return int
	 * @access protected
	 */
	protected function getRemoteFileSize($url) {
		$size = 0;
		$timeout = 2;
		$url = parse_url($url);
		if ($sock = @fsockopen($url['host'], ($url['port'] ? $url['port'] : 80), $errno, $errstr, $timeout)) {
			// Call the HEAD only (there should no content returned)
			fwrite($sock, 'HEAD '.$url['path'].$url['query'].' HTTP/1.0'.chr(13).chr(10).'Host: '.$url['host'].chr(13).chr(10).chr(13).chr(10));
			stream_set_timeout($sock, $timeout);

			// get datas form the socket
			while (!(feof($sock))) {
				$content = fgets($sock, 4096);
				if (preg_match("/(Content-Length)(.*?)([\d]+)/smi", $content, $match)) {
					$size = (int)$match[3];
					break;
				}
			}
		}
		@fclose($sock);

		// Return the Size as an Integer, if its a Numeric-Value
		return $size;
	}

	/**
	 * Calculate HumanReadable filesize values
	 *
	 * @param int $size Filesize to get as Human-Readable String
	 * @return String
	 * @access protected
	 */
	protected function humanReadableFileSize($size) {
		$size = intval($size);
		if ($size >= pow(1024, 4))
		return number_format(($size / pow(1024, 4)), 2, ",", "'").' Tb';
		else if ($size >= pow(1024, 3))
		return number_format(($size / pow(1024, 3)), 2, ",", "'").' Gb';
		else if ($size >= pow(1024, 2))
		return number_format(($size / pow(1024, 2)), 2, ",", "'").' Mb';
		else if ($size >= pow(1024, 1))
		return number_format(($size / pow(1024, 1)), 2, ",", "'").' Kb';
		else
		return number_format($size, 2, ",", "'").' bytes';
	}

	/**
	 * Format a Date with localized names
	 *
	 * @param string $format like PHP-Fuction date()
	 * @param unknown_type $time timevalue to format
	 * @return string formated date
	 */
	protected function formatDate($format, $time) {
		$back = date($format, $time);

		// Check if the given $time is a Date (string) or an Integer (what we need)
		if ((int)$time != $time) {
			$time = strtotime($time);
		}

		$conv = array("d"=>"%d", "D"=>"%a", "j"=>"%e", "l"=>"%A",
		"N"=>"%u", "S"=>date('S',$time), "w"=>"%w", "z"=>"%j", "W"=>"%W",
		"F"=>"%B", "m"=>"%m", "M"=>"%b", "n"=>date('n',$time), "t"=>date('t',$time),
		"L"=>date('L',$time), "o"=>"%Y", "Y"=>"%Y", "y"=>"%y",
		"a"=>"%p", "A"=>date('A',$time), "B"=>date('B',$time), "g"=>date('g',$time), "G"=>date('G',$time),
		"h"=>"%I", "H"=>"%H", "i"=>"%M", "s"=>"%S",
		"e"=>"%Z", "I"=>date('I',$time), "O"=>date('O',$time),"T"=>"%z", "Z"=>date('Z',$time),
		"r"=>date('r',$time), "U"=>$time);

		if (version_compare(phpversion(), '5.1.3') <= 0) {
			$conv["P"] = date('P',$time);
		}
		if (version_compare(phpversion(), '5') <= 0) {
			$conv["c"] = date('c',$time);
		}

		$new_format = '';
		$skip = false;
		$keys = array_keys($conv);
		for ($i = 0; $i < strlen($format); $i++) {
			// get next char
			$v = $format[$i];
			// check current char
			if (!$skip) {
				if (in_array($v, $keys)) {
					$new_format .= $conv[$v];
				} else {
					// Check if we should skip the next char
					if ($v == '\\') {
						$skip = true;
					} else {
						$new_format .= $v;
					}
				}
			} else {
				// Backslash must be escaped
				if ($v == '\\') {
					$new_format .= '\\';
				} else {
					$new_format .= $v;
				}
				$skip = false;
			}
		}

		// convert format-string
		$back = strftime($new_format, $time);

		return $back;
	}

	/**
	 * Return all SectionIDs where $selected is a child from
	 *
	 * @param int $selected SectionId from which we would know where it is in
	 * @param String $table SectionTable (short form)
	 * @param String $parentField Field where parentId is stored in (short  form)
	 * @param String $idField Field where the SectionId is stored in (short form)
	 * @return array List with all SectionIDs in which $selected is in
	 */
	protected function getSelectedSectionStructure($selected, $table, $parentField, $idField) {
		$back = array();
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		array_push($back, (int)$selected);
		while (true) {
			$sql = "SELECT [".$table.".".$parentField."] FROM [table.".$table."] WHERE [".$table.".".$idField."]=".$selected.";";
			$db->run($sql, $res);
			if ($res->getFirst()) {
				array_push($back, (int)$res->{$db->getFieldName($table.'.'.$parentField)});
				$selected = (int)$res->{$db->getFieldName($table.'.'.$parentField)};
				$res = null;
			} else {
				break;
			}
		}
		return $back;
	}

	/**
	 * Get a List with all Section-ID's, which are childs from $sid
	 *
	 * @param integer $sid SectionID to get the childs from
	 * @param String $table SectionTable (short form)
	 * @param String $parentField Field where parentId is stored in (short  form)
	 * @param String $idField Field where the SectionId is stored in (short form)
	 * @return array List with all SectionID's
	 */
	protected function getChildSectionList($sid, $table, $idField, $parentField) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$back = array((int)$sid);

		$sql = 'SELECT ['.$table.'.'.$idField.'] FROM [table.'.$table.'] WHERE ['.$table.'.'.$parentField.']='.$sid.';';
		$db->run($sql, $res);
		if ($res->getFirst()) {
			while ($res->getNext()) {
				$clist = $this->getChildSectionList((int)$res->{$db->getFieldName($table.'.'.$idField)}, $table, $idField, $parentField);
				foreach ($clist as $id) {
					array_push($back, $id);
				}
			}
		}
		return array_unique($back);
	}

	/**
	 * Get the sectionID from an Object stored in the DB
	 *
	 * @param integer $id ID from Object to get the SectionID from
	 * @param string $table Table where the Object is stored
	 * @return integer The SectionID or 0 if there is none found
	 */
	protected function _getSectionIdFromObject($id, $table) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = "SELECT [".$table.".section] FROM [table.".$table."] WHERE [".$table.".id]=".$id.";";
		$db->run($sql, $res);
		if ($res->getFirst()) {
			$section = $res->{$db->getFieldName($table.'.section')};
			$res = null;
			return $section;
		}
		return 0;
	}

	/**
	 * Get the sectionName from an Object stored in the DB
	 *
	 * @param integer $id Section ID
	 * @param string $table Table where the Object is stored
	 * @return integer The SectionName or 0 if there is none found
	 */
	protected function _getSectionName($id, $table) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = 'SELECT ['.$table.'.text] FROM [table.'.$table.'] WHERE ['.$table.'.id]='.$id.';';
		$db->run($sql, $res);
		if ($res->getFirst()) {
			$section = $res->{$db->getFieldName($table.'.text')};
			$res = null;
			return $section;
		}
		return '';
	}

	/**
	 * Parse a String and save the Content as a String in tagContent or as an Array in tagParams
	 *
	 * If the String begins with "params:" the this are Parameters and are saved as an Array in tagParams
	 * The Format of the Params must be like "key=value;key=value;..." Each "=" and ";" must be encoded
	 *
	 * If the String does not begin with "params:", the it's normal content and is saved in tagContent
	 *
	 * @param String $params String to parse
	 * @access protected
	 */
	protected function parsePluginContentParameters($params) {
		$this->tagContent = '';
		$this->tagParams = array();

		if (substr($params, 0, 7) == 'params:') {
			$params = substr($params, 7);
			$tmp = explode(";", $params);
			foreach ($tmp as $v) {
				$v = explode("=", $v);
				if (count($v) >= 2) {
					$name = trim($v[0]);
					unset($v[0]);
					$value = trim(implode('=', $v));
					$this->tagParams[$name] = str_replace("&#".ord("=").";", "=", str_replace("&#".ord(";").";", ";", $value));
				}
			}

		} else {
			$this->tagContent = $params;
		}
	}

	/**
	 * Get the current version-number and ID from this Plugin and return it as an array
	 * with the versionnumber as the version and the second one as the version-ID
	 *
	 * @return array 0=>version, 1=>versionID
	 * @access protected
	 * @deprecated use pDatabaseConnection::getDatabaseInstance()->getModuleVersion($module)
	 */
	protected function _checkMainDatabase() {
		$db = pDatabaseConnection::getDatabaseInstance();
		$version = $db->getModuleVersion(get_class($this));
		return array($version, 0);

		/*$version = 0;
		$versionid = 0;
		$sql  = "SELECT * FROM [table.opt] WHERE [opt.name]='".get_class($this)."'";
		$res = null;
		$db->run($sql, $res);
		if ($res->getFirst()) {
			$version = $res->{$db->getFieldName('opt.version')};
			$versionid = $res->{$db->getFieldName('opt.id')};
			$back = array($version, $versionid);
		}

		// If the $version is '0' or lower, check if the table 'opt' exists and create it
		if ($version <= 0) {
			$sql  = "CREATE TABLE IF NOT EXISTS [table.opt] (".
			" [field.opt.id] INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,".
			" [field.opt.version] INT(10) UNSIGNED NOT NULL DEFAULT 0,".
			" [field.opt.name] VARCHAR(50) NOT NULL DEFAULT '',".
			" [field.opt.lastmod] INT(10) UNSIGNED NOT NULL DEFAULT 0,".
			" PRIMARY KEY ([field.opt.id]),".
			" UNIQUE KEY [field.opt.id] ([field.opt.id])".
			" );";
			$res = null;
			$db->run($sql, $res);
		}

		return $back;*/
	}

	/**
	 * Update the versiontable for the current plugin with the new version
	 *
	 * @param int $version the new version
	 * @param int $versionId id from current version/plugin
	 * @param int $moduleVersion The current Module-Version
	 * @access protected
	 * @deprecated use pDatabaseConnection::getDatabaseInstance()->updateModuleVersion($module, $version)
	 */
	protected function _updateVersionTable($version, $versionid, $moduleVersion=null) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$db->updateModuleVersion(get_class($this), $moduleVersion);

		/*if ($version < $moduleVersion) {
			$db = pDatabaseConnection::getDatabaseInstance();
			$res = null;
			if ($versionid <= 0) {
				$sql  = "INSERT INTO [table.opt]";
				$sql .= " ([opt.version],[opt.name],[opt.lastmod])";
				$sql .= " VALUES ('".$moduleVersion."','".get_class($this)."','".time()."');";
				$db->run($sql, $res);
			} else {
				$sql  = "UPDATE [table.opt]";
				$sql .= " SET [opt.version]='".$moduleVersion."',";
				$sql .= " [opt.lastmod]='".time()."'";
				$sql .= " WHERE [opt.id]=".$versionid.";";
				$db->run($sql, $res);
			}
		}*/
	}

}
?>