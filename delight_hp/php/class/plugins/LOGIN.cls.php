<?php
class LOGIN extends MainPlugin {

	const VERSION = 2010012900;

	public function __construct() {
		parent::__construct();
		$this->_isTextPlugin = true;
		$this->_checkDatabase();
	}

	/**
	 * Get the JS-Editor-Function to open the Editor
	 *
	 * @param int $id Text-ID to show Editor for
	 * @return string JS-Function
	 * @access public
	 */
	public function getEditFunction($id) {
		return 'javascript:;';
		//return "javascript:setTextareaToDelightEdit('txt_".$id."','iframe');";
	}

	/**
	 * Get the JS-Editor-Function to close the Editor
	 *
	 * @param int $id Text-ID to hide Editor for
	 * @return string JS-Function
	 * @access public
	 */
	public function getCloseFunction($id) {
		return 'javascript:;';
		//return "javascript:closeDelightEdit();";
	}

	/**
	 * Set the Parameters/Content from the TemplateTag
	 *
	 * @override iPlugin
	 * @param String $params The Parameters which are defined in the Template
	 * @access public
	 */
	public function setContentParameters($params) {
		$this->parsePluginContentParameters($params);
	}

	/**
	 * Additional Options for the TextEditor
	 *
	 * @param string $options Options from Template
	 * @return string Options like defined in a Template
	 * @access public
	 */
	public function getAdditionalOptions($options='') {
		$opt  = '[OPTIONS]';
		$opt .= '[/OPTIONS]';
		return $opt;
	}

	/**
	 * Create the HTML-Source and return it
	 *
	 * @param string $method A special method-name - here we use it to switch between the complete List and just a list of news
	 * @param array $adminData If shown under Admin-Editor, this Array must be a complete DB-likeness Textentry
	 * @param array $templateTag If included in a Template, this array has keys 'layout','template','num' with equivalent values
	 * @return string
	 * @access public
	 */
	public function getSource($method="", $adminData=array(), $templateTag=array()) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$userCheck = pCheckUserData::getInstance();
		if (count($adminData) > 5) {
			$_textData = $adminData;
		} else {
			$_textData = $this->_getTextEntryData();
		}
		$_template = $this->_readTemplateFile($_textData[$db->getFieldName('txt.layout')]);

		// Get the Content
		$_txtId = $_textData[$db->getFieldName('txt.id')];
		//$_title = $_textData[$db->getFieldName('txt.title')];
		//$_text  = $_textData[$db->getFieldName('txt.text')];
		$_title = '';
		$_text = '';
		$_text = empty($_text) ? $this->_readTemplateFile('adm_loginForm.tpl') : $_text;

		if (count($adminData) > 5) {
			$_template = str_replace('[ADMIN_FUNCTIONS]', '', $_template);
			$_template = preg_replace('/(\[TEXT_ADMIN_FUNCTIONS\])(.*?)(\[\/TEXT_ADMIN_FUNCTIONS\])/smi', '', $_template);
			$_template = preg_replace('/(\[ADMIN_REMOVE\])(.*?)(\[\/ADMIN_REMOVE\])/smi', '', $_template);
		}

		// Check for CSS-File, CSS-Content and SCRIPT-File
		if (strlen(trim($this->_cssFile)) > 0) {
			$this->_hasCssImportFile = true;
		}

		if (strlen(trim($this->_scriptFile)) > 0) {
			$this->_hasScriptImportFile = true;
		}

		if (strlen(trim($this->_cssContent)) > 0) {
			$this->_hasCssContent = true;
		}

		// Indert an ID-Field into the Title-Field
		if (substr_count($_template, '[TITLE]') > 0) {
			$_titleBefore = substr($_template, 0, strpos($_template, '[TITLE]'));
			$_titleAfter = substr($_template, strpos($_template, '[TITLE]'));
			$_template = $_titleBefore.'<span id="title_'.$_txtId.'">'.$_titleAfter.'</span>';
		}

		// Replace [TITLE] or strip out the CAT_TITLE...
		if (!empty($_title) || ($userCheck->checkAccess('content'))) {
			if (empty($_title) && ($userCheck->checkAccess('content'))) {
				$_title = "";
			}
			$_title = $this->_appendTitleAnchor($_title, $_textData[$db->getFieldName('txt.id')]);
			$html = str_replace('[TITLE]', $_title, $_template);
			$html = str_replace('[CAT_TITLE]', '', str_replace('[/CAT_TITLE]', '', $html));
		} else {
			$html = str_replace('[TITLE]', '', $_template);
			$html = preg_replace('/(\[CAT_TITLE\])(.*?)(\[\/CAT_TITLE\])/smi', '', $html);
		}

		// Replace [TEXT] or strip out the CAT_CONTENT...
		if ((strlen(trim($_text)) > 0) || ($userCheck->checkAccess('content'))) {
			$_text = $this->appendAdminTextEditAddon($_text, $_txtId, $_textData);

			$html = str_replace('[TEXT]', $_text, $html);
			$html = str_replace('[CAT_CONTENT]', '', str_replace('[/CAT_CONTENT]', '', $html));
		} else {
			$html = str_replace('[TEXT]', '', $html);
			$html = preg_replace('/(\[CAT_CONTENT\])(.*?)(\[\/CAT_CONTENT\])/smi', '', $html);
		}

		// Replace Text-Options
		$html = $this->ReplaceLayoutOptions($html, $_textData[$db->getFieldName('txt.options')]);

		return $html;
	}

	/**
	 * Check Database integrity
	 *
	 * This function creates all required Tables, Updates, Inserts and Deletes.
	 */
	function _checkDatabase() {
		// Get the current Version
		$v = $this->_checkMainDatabase();
		$version = $v[0];
		$versionid = $v[1];

		// Updates to the Database
		if ($version <= 0) {
		}

		// Update the version
		$this->_updateVersionTable($version, $versionid, self::VERSION);
	}
}

?>