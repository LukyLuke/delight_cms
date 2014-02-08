<?php
class GLOBALTEXT extends MainPlugin {
	const FULLSCREEN_LAYOUT = 'fullscreen';

	public function __construct() {
		parent::__construct();
		$this->_isTextPlugin = true;
	}

	/**
	 * Get the JS-Editor-Function to open the Editor
	 *
	 * @param int $id Text-ID to show Editor for
	 * @return string JS-Function
	 * @access public
	 */
	public function getEditFunction($id) {
		return "globaltext";
	}

	/**
	 * Get the JS-Editor-Function to close the Editor
	 *
	 * @param int $id Text-ID to hide Editor for
	 * @return string JS-Function
	 * @access public
	 */
	public function getCloseFunction($id) {
		return "javascript:closeDelightEdit();";
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
		return $this->getPageSource($adminData);
	}

	public function getPageSource($adminData=array(), $onlyContent=false) {
		$userCheck = pCheckUserData::getInstance();
		if (count($adminData) > 5) {
			$text = $this->getTextEntryObject();
			foreach ($adminData as $k => $v) {
				$text->{$k} = $v;
			}
		} else {
			$text = $this->getTextEntryObject();
		}
		$_template = $this->_readTemplateFile($text->layout);

		$_template = str_replace('[ADMIN_FUNCTIONS]', '', $_template);
		$_template = preg_replace('/(\[TEXT_ADMIN_FUNCTIONS\])(.*?)(\[\/TEXT_ADMIN_FUNCTIONS\])/smi', '', $_template);
		$_template = preg_replace('/(\[ADMIN_REMOVE\])(.*?)(\[\/ADMIN_REMOVE\])/smi', '', $_template);

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

		// Insert an ID-Field into the Title-Field
		$_titleBefore = substr($_template, 0, strpos($_template, '[TITLE]'));
		$_titleAfter  = substr($_template, strpos($_template, '[TITLE]'));
		$_template = $_titleBefore.'<span id="title_'.$text->id.'">'.$_titleAfter.'</span>';

		// Get the Global Textentry
		$global = new pGlobalText((int)$text->text);

		// Replace [TITLE] or strip out the CAT_TITLE...
		$_title = $global->title;
		if ( !empty($_title) || $userCheck->checkAccess('content') ) {
			if ( empty($_title) && $userCheck->checkAccess('content')) {
				$_title = '';
			}
			$html = str_replace('[TITLE]', $this->_appendTitleAnchor($_title, $text->id), $_template);
			$html = str_replace('[CAT_TITLE]', '', str_replace('[/CAT_TITLE]', '', $html));
		} else {
			$html = str_replace('[TITLE]', '', $_template);
			$html = preg_replace('/(\[CAT_TITLE\])(.*?)(\[\/CAT_TITLE\])/smi', '', $html);
		}

		// Replace [TEXT] or strip out the CAT_CONTENT...
		$_text = $this->ReplaceTextVariables($global->text);
		if ( !empty($_text) || $userCheck->checkAccess('content') ) {
			$this->appendTextAdminAddons($_text, $text, $text->id);
			$html = str_replace('[TEXT]', $_text,  $html);
			$html = str_replace('[CAT_CONTENT]', '', str_replace('[/CAT_CONTENT]', '', $html));
		} else {
			$html = str_replace('[TEXT]', '', $html);
			$html = preg_replace('/(\[CAT_CONTENT\])(.*?)(\[\/CAT_CONTENT\])/smi', '', $html);
		}

		// Replace Text-Options
		$html = $this->ReplaceLayoutOptions($html, $text->options);

		return trim($html);
	}

	/**
	 * Get the title
	 * @return string
	 */
	public function getTitle() {
		return $this->getTextEntryObject()->title;
	}

	/**
	 * Get the Content
	 * @return string
	 */
	public function getContent() {
		return $this->getSource(true);
	}

}

?>