<?php
class TEXT extends MainPlugin {
	const VERSION = 2006010600;
	const FULLSCREEN_LAYOUT = 'fullscreen';

	private $contentFile;
	private $_templateContent = '';
	private $_titleText = array('', '');

	/**
	 * Initialization
	 *
	 */
	public function __construct() {
		parent::__construct();
		$this->_isTextPlugin = true;
		$this->contentFile = 'cont_text.tpl';
		$this->_checkDatabase();
	}

	/**
	 * Return the Link to show the Editor in UI
	 *
	 * @param int $id Textblock-ID
	 * @return string
	 * @access public
	 */
	public function getEditFunction($id) {
		return 'tinymce';
		return "javascript:setTextareaToTinyMCE('txt_".$id."');";
	}

	/**
	 * Return the Link to hide the Editor in UI
	 *
	 * @param int $id Textblock-ID
	 * @return string
	 * @access public
	 */
	public function getCloseFunction($id) {
		return "javascript:closeEditor();";
	}

	/**
	 * Additional Options for the TextEditor
	 *
	 * @param string $options Options from Template
	 * @return string Options like defined in a Template
	 * @access public
	 */
	public function getAdditionalOptions($options='') {
		if (strlen($options) > 0) {
			$this->_contentOptions = $this->_parseOptionsTags($options, $this->_contentOptions);
		}
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
		if ($method == PLG_TEXT_METHOD) {
			return $this->getSimpleTextEntry();
		}
		return $this->getTextEntry($adminData);
	}

	/**
	 * Highlight an XML string as HTML with same colors as kwrite.
	 * 
	 * @param string $s The XML String to highlight
	 * @return HTML Markup
	 */
	protected function highlight_xml($s) {
		$s = htmlspecialchars($s);
		
		// The whole opening and closing tag without the content: <name foo="bar"> ; </name> ; <name foo="bar" />
		$s = preg_replace('#&lt;([/]*?)(.*)([\s]*?)&gt;#sU', '<span style="color:#00000;font-weight:bold;">&lt;\\1\\2\\3&gt;</span>', $s);
		
		// Peamble
		$s = preg_replace('#&lt;([\?a-z]*?)(.*)([\?])&gt;#sU', '<span style="color:#000000;font-weight:bold;">&lt;\\1</span><span style="color:#000000;font-weight:normal;">\\2</span><span style="color:#000000;font-weight:bold;">\\3&gt;</span>', $s);
		
		// Tag nameswithout attributes
		$s = preg_replace('#&lt;([^\s\?/=])(.*)([\[\s/]|&gt;)#iU', '&lt;<span style="color:#000000;font-weight:bold;">\\1\\2</span>\\3', $s);
		$s = preg_replace('#&lt;([/])([^\s]*?)([\s\]]*?)&gt;#iU', '&lt;\\1<span style="color:#000000;font-weight:bold;">\\2</span>\\3&gt;', $s);
		
		// Attributes
		$s = preg_replace('#([^\s]*?)\=(&quot;|\')(.*)(&quot;|\')#isU', '<span style="color:#40805E;font-weight:normal;">\\1=</span><span style="color:#BF2040;font-weight:normal;">\\2\\3\\4</span>', $s);
		
		// CData content
		$s = preg_replace('#&lt;(\[CDATA\[)(.*)(\]])&gt;#isU', '<span style="color:#B08840;font-weight:bold;">&lt;\\1</span><span style="color:#000000;font-weight:normal;">\\2</span><span style="color:#B08840;font-weight:bold;">\\3&gt;</span>', $s);
		return '<code>' . nl2br($s) . '</code>';
	}

	private function getSimpleTextEntry() {
		// Read the ContentFile (cont_screenshots.tpl)
		$this->_readContentFile(self::FULLSCREEN_LAYOUT);

		// Check for CSS-File, CSS-Content and SCRIPT-File
		if ( !empty($this->_cssFile) || !empty($this->_specialCssFile) ) {
			$this->_hasCssImportFile = true;
		}

		if ( !empty($this->_scriptFile) || !empty($this->_specialScriptFile) ) {
			$this->_hasScriptImportFile = true;
		}

		if ( !empty($this->_cssContent) || !empty($this->_specialCssContent) ) {
			$this->_hasCssContent = true;
		}

		// Get the Text and replace Content Tags
		$text = $this->getTextEntryObject();

		if ($text->plugin != 'TEXT') {
			$obj = new $text->plugin();
			$obj->setTextId($text->id);
			$txt = $obj->getContent();
			unset($obj);
		} else {
			$txt = $text->text;
		}

		$html = str_replace('[TEXT]', $txt, $this->_templateContent);
		$html = str_replace('[SOURCE]', $this->highlight_xml($txt), $this->_templateContent);
		$html = str_replace('[TEXT_ID]', $text->id, $html);

		if (strlen($text->title) > 0) {
			$html = str_replace('[TITLE]', $this->_titleText[0].$text->title.$this->_titleText[1], $html);
			$html = str_replace('[CAT_TITLE]', '', str_replace('[/CAT_TITLE]', '', $html));
		} else {
			$html = str_replace('[TITLE]', '', $html);
			$html = preg_replace('/(\[CAT_TITLE\])(.*?)(\[\/CAT_TITLE\])/smi', '', $html);
		}
		$html = $this->ReplaceLayoutOptions($html, '#title=default#');
		return $html;
	}

	private function getTextEntry($adminData=array()) {
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
		$_titleAfter  = substr($_template, strpos($_template, '[TITLE]') + 7);
		$_template = $_titleBefore.'<span id="title_'.$text->id.'">[TITLE]</span>'.$_titleAfter;

		// Replace [TITLE] or strip out the CAT_TITLE...
		if ( (strlen($text->title) > 0) || ($userCheck->checkAccess('content')) ) {
			if ( (strlen($text->title) <= 0) && ($userCheck->checkAccess('content'))) {
				$text->title = '';
			}
			$html = str_replace('[TITLE]', $this->_appendTitleAnchor($text->title, $text->id), $_template);
			$html = str_replace('[CAT_TITLE]', '', str_replace('[/CAT_TITLE]', '', $html));
		} else {
			$html = str_replace('[TITLE]', '', $_template);
			$html = preg_replace('/(\[CAT_TITLE\])(.*?)(\[\/CAT_TITLE\])/smi', '', $html);
		}

		// Replace [TEXT] or strip out the CAT_CONTENT...
		$text->text = $this->ReplaceTextVariables($text->text);
		if ( (strlen($text->text) > 0) || ($userCheck->checkAccess('content')) ) {
			$_text = $text->text;
			$this->appendTextAdminAddons($_text, $text, $text->id);

			$html = str_replace('[TEXT]', $_text,  $html);
			$html = str_replace('[SOURCE]', $this->highlight_xml($_text),  $html);
			$html = str_replace('[CAT_CONTENT]', '', str_replace('[/CAT_CONTENT]', '', $html));
		} else {
			$html = str_replace('[TEXT]', '', $html);
			$html = preg_replace('/(\[CAT_CONTENT\])(.*?)(\[\/CAT_CONTENT\])/smi', '', $html);
		}

		// Replace Text-Options
		$html = $this->ReplaceLayoutOptions($html, $text->options);
		return $html;
	}

	/**
	 * Just get the title
	 * @return string
	 */
	public function getTitle() {
		return $this->getTextEntryObject()->title;
	}

	/**
	 * Just get the Content
	 * @return string
	 */
	public function getContent() {
		return $this->getTextEntryObject()->text;
	}

	protected function _readContentFile($type="") {
		// Create some default styles
		$this->_templateContent = '[CAT_TITLE]<div class="title">[TITLE]</div>[/CAT_TITLE]<div class="text">[TEXT]</div>';
		$this->_titleText = array('', '');

		// Set the ScreenshotLayoutFile and read them
		$layout = ABS_TEMPLATE_DIR."/".$this->contentFile;
		if (is_file($layout) && is_readable($layout)) {
			$cont = file_get_contents($layout);

			// Check for [SCRIPT_INCLUDE]
			if (preg_match("/(\[SCRIPT_INCLUDE\])(.*?)(\[\/SCRIPT_INCLUDE\])/smi", $cont, $match)) {
				$this->_specialScriptFile = $match[2];
			}

			// Check for [STYLE_INCLUDE]
			if (preg_match("/(\[STYLE_INCLUDE\])(.*?)(\[\/STYLE_INCLUDE\])/smi", $cont, $match)) {
				$this->_specialCssFile = $match[2];
			}

			// Check for [STYLE_CONTENT]
			if (preg_match("/(\[STYLE_CONTENT\])(.*?)(\[\/STYLE_CONTENT\])/smi", $cont, $match)) {
				$this->_specialCssContent = $match[2];
			}

			// Check for a [LAYOUT]...[/LAYOUT] as _mainContent
			if (preg_match_all("/(\[LAYOUT)((:".$type.")?)(\])(.*?)(\[\/LAYOUT\])/smi", $cont, $match)) {
				for ($i = 0; $i < count($match[0]); $i++) {
					if ( ($match[3][$i] == ":".$type) || ((trim($type) == "default") && (strlen(trim($match[3][$i])) <= 0)) ) {
						$this->_templateContent = $match[5][$i];
					}
				}
			}

			// Check for [TITLE_TEXT:before]
			if (preg_match("/(\[TITLE_TEXT:before\])(.*?)(\[\/TITLE_TEXT\])/smi", $cont, $match)) {
				$this->_titleText[0] = $match[2];
			}
			// Check for [TITLE_TEXT:after]
			if (preg_match("/(\[TITLE_TEXT:after\])(.*?)(\[\/TITLE_TEXT\])/smi", $cont, $match)) {
				$this->_titleText[1] = $match[2];
			}

			unset($cont);
		}
	}

	/**
	 * Check Database integrity
	 *
	 * This function creates all required Tables, Updates, Inserts and Deletes.
	 * @access private
	 */
	private function _checkDatabase() {
		// Get the current Version
		$v = $this->_checkMainDatabase();
		$version = $v[0];
		$versionid = $v[1];

		// Updates to the Database
		if ($version < 2006010600) {
		}

		// Update the version
		$this->_updateVersionTable($version, $versionid, self::VERSION);
	}

}

?>