<?php
class GROUP extends MainPlugin {
	const VERSION = 2010052000;

	private $contentFile = 'cont_grouped.tpl';
	private $mainContent = '<div id="grouped_main_[TEXTID]" class="grouped_main"><div id="grouped_titles_[TEXTID]" class="grouped_titles">[TITLES]</div><div id="grouped_contents_[TEXTID]" class="grouped_contents">[CONTENTS]</div></div>';
	private $titleEntry = '<span id="grouped_title_[TEXTID]" class="grouped_title [FIRST]" onclick="GroupedTexts.show(\'grouped_title_[TEXTID]\',\'grouped_content_[TEXTID]\',\'[SELECTED_CLASS]\');">[TITLE]</span>';
	private $contentEntry = '<div id="grouped_content_[TEXTID]" class="grouped_content [FIRST]">[CONTENT]</div>';
	private $selectedClass = 'grouped_selected';

	private $cssFiles = array();
	private $scriptFiles = array();

	/**
	 * Initialization
	 *
	 */
	public function __construct() {
		parent::__construct();
		$this->_isTextPlugin = true;
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
		return "grouped";
		return "javascript:openAdmin(2100,'group_content',".$id.");";
	}

	/**
	 * Return the Link to hide the Editor in UI
	 *
	 * @param int $id Textblock-ID
	 * @return string
	 * @access public
	 */
	public function getCloseFunction($id) {
		return "javascript:closeDelightEdit();";
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
		$opt .= '[show_title]choose:true,false[/show_title]';
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
		$userCheck = pCheckUserData::getInstance();
		$lang = pMessages::getLanguageInstance();
		$menu = pMenu::getMenuInstance();

		if (count($adminData) > 5) {
			$text = $this->getTextEntryObject();
			foreach ($adminData as $k => $v) {
				$text->{$k} = $v;
			}
		} else {
			$text = $this->getTextEntryObject();
		}

		// Read the Layout and Content templates
		$_template = $this->_readTemplateFile($text->layout);
		$this->_readContentFile('default');

		if (true) { // (count($adminData) > 5) { // AdminFunctions in Templates are deprecated since JS-AdminMenuClass functions
			$_template = str_replace('[ADMIN_FUNCTIONS]', '', $_template);
			$_template = preg_replace('/(\[TEXT_ADMIN_FUNCTIONS\])(.*?)(\[\/TEXT_ADMIN_FUNCTIONS\])/smi', '', $_template);
			$_template = preg_replace('/(\[ADMIN_REMOVE\])(.*?)(\[\/ADMIN_REMOVE\])/smi', '', $_template);
		}

		// Check for CSS-File, CSS-Content and SCRIPT-File
		if ( !empty($this->_cssFile) || !empty($this->_specialCssFile)) {
			$this->_hasCssImportFile = true;
		}

		if ( !empty($this->_scriptFile) || !empty($this->_specialScriptFile)) {
			$this->_hasScriptImportFile = true;
		}

		if ( !empty($this->_cssContent) || !empty($this->_specialCssContent)) {
			$this->_hasCssContent = true;
		}

		// Replace [TITLE] or strip out the CAT_TITLE...
		if ($text->title == 'default') {
			$text->title = '';
		}
		if (!empty($text->title)) {
			$html = str_replace('[TITLE]', $this->_appendTitleAnchor($text->title, $text->id), $_template);
			$html = str_replace('[CAT_TITLE]', '', str_replace('[/CAT_TITLE]', '', $html));
		} else {
			$html = str_replace('[TITLE]', '', $_template);
			$html = preg_replace('/(\[CAT_TITLE\])(.*?)(\[\/CAT_TITLE\])/smi', '', $html);
		}

		// Create the Content
		$idList = array();
		$texts = $this->getTextEntryList();
		$first = true;
		foreach ($texts as $t) {
			if ($t->id <= 0) {
				continue;
			}

			$idList[] = $t->id;

			$title = str_replace('[TITLE]', $t->getPluginTitle(), $this->titleEntry);
			$title = str_replace('[TEXTID]', $t->id, $title);
			$title = str_replace('[FIRST]', $first ? $this->selectedClass : '', $title);

			$content = $t->getPluginContent();
			$this->appendTextAdminAddons($content, $t, $t->id, $t->getEditFunction());
			$content = str_replace('[CONTENT]', $content, $this->contentEntry);
			$content = str_replace('[TEXTID]', $t->id, $content);
			$content = str_replace('[FIRST]', $first ? $this->selectedClass : '', $content);

			$this->mainContent = str_replace('[TITLES]', $title.'[TITLES]', $this->mainContent);
			$this->mainContent = str_replace('[CONTENTS]', $content.'[CONTENTS]', $this->mainContent);
			$first = false;

			$metaContent = $t->getMetaContent();
			if ($metaContent->cssFiles !== false) {
				$this->cssFiles[] = $metaContent->cssFiles;
				$this->_hasCssImportFile = true;
			}
			if ($metaContent->cssContent !== false) {
				$this->_cssContent .= $metaContent->cssContent;
				$this->_hasCssContent = true;
			}
			if ($metaContent->jsFiles !== false) {
				$this->scriptFiles[] = $metaContent->jsFiles;
				$this->_hasScriptImportFile = true;
			}
		}

		$hideScript = '<script type="text/javascript">GroupedTexts.hide(['.implode(',',$idList).']);</script>';
		$this->appendTextAdminAddons($hideScript, $text, $text->id, $text->getEditFunction());

		$this->mainContent = str_replace('[TITLES]', '', $this->mainContent);
		$this->mainContent = str_replace('[CONTENTS]', $hideScript, $this->mainContent);
		$this->mainContent = str_replace('[SELECTED_CLASS]', $this->selectedClass, $this->mainContent);
		$this->mainContent = str_replace('[TEXTID]', $text->id, $this->mainContent);

		$html = str_replace('[TEXT]', $this->mainContent,  $html);
		$html = str_replace('[CAT_CONTENT]', '', str_replace('[/CAT_CONTENT]', '', $html));

		// Replace Text-Options
		$html = $this->ReplaceLayoutOptions($html, $text->options);

		return $html;
	}

	/**
	 * (non-PHPdoc)
	 * @see delight_hp/php/class/plugins/MainPlugin#getScriptImportFile()
	 */
	public function getScriptImportFile() {
		$files = parent::getScriptImportFile();
		if ($files === false) {
			$files = '';
		}
		foreach ($this->scriptFiles as $f) {
			if (!empty($files)) {
				$files .= '::';
			}
			$files .= $f;
		}
		if (empty($files)) {
			return false;
		}
		return $files;
	}

	/**
	 * (non-PHPdoc)
	 * @see delight_hp/php/class/plugins/MainPlugin#getCssImportFile()
	 */
	public function getCssImportFile() {
		$files = parent::getCssImportFile();
		if ($files === false) {
			$files = '';
		}
		foreach ($this->cssFiles as $f) {
			if (!empty($files)) {
				$files .= '::';
			}
			$files .= TEMPLATE_DIR.$f;
		}
		if (empty($files)) {
			return false;
		}
		return $files;
	}

	/**
	 * Get all Texts from current group
	 * @return array[pTextEntry]
	 */
	private function getTextEntryList() {
		$back = array();
		$text = new pTextEntry($this->_textId);

		foreach (explode(',', $text->text) as $id) {
			$back[] = new pTextEntry($id);
		}

		return $back;
	}

	/**
	 * Read the LayoutFile cont_grouped.tpl
	 *
	 * @param string $layout optional layout-section
	 * @access protected
	 */
	protected function _readContentFile($type='default') {
		$layout = ABS_TEMPLATE_DIR."/".$this->contentFile;
		if (is_file($layout) && is_readable($layout)) {
			$cont = file_get_contents($layout);

			// Check for additional Scripts, Styles and CSS-Content
			if (preg_match('/(\[SCRIPT_INCLUDE\])(.*?)(\[\/SCRIPT_INCLUDE\])/smi', $cont, $match)) {
				$this->_specialScriptFile = $match[2];
			}
			if (preg_match('/(\[STYLE_INCLUDE\])(.*?)(\[\/STYLE_INCLUDE\])/smi', $cont, $match)) {
				$this->_specialCssFile = $match[2];
			}
			if (preg_match('/(\[STYLE_CONTENT\])(.*?)(\[\/STYLE_CONTENT\])/smi', $cont, $match)) {
				$this->_specialCssContent = $match[2];
			}

			// Check for a [LAYOUT]...[/LAYOUT]
			if (preg_match_all('/(\[LAYOUT)((:'.$type.')?)(\])(.*?)(\[\/LAYOUT\])/smi', $cont, $match)) {
				for ($i = 0; $i < count($match[0]); $i++) {
					if ( ($match[3][$i] == ":".$type) || ((trim($type) == "default") && (strlen(trim($match[3][$i])) <= 0)) ) {
						$this->mainContent = $match[5][$i];
					}
				}
			}

			// Check for Title- and Textentry
			if (preg_match('/(\[TITLE_ENTRY\])(.*?)(\[\/TITLE_ENTRY\])/smi', $this->mainContent, $match)) {
				$this->titleEntry = $match[2];
				$this->mainContent = str_replace($match[0], '', $this->mainContent);
			}
			if (preg_match('/(\[TEXT_ENTRY\])(.*?)(\[\/TEXT_ENTRY\])/smi', $this->mainContent, $match)) {
				$this->contentEntry = $match[2];
				$this->mainContent = str_replace($match[0], '', $this->mainContent);
			}

			// Check for a Class to be used when an Entry is selected
			if (preg_match('/(\[SELECTED_CLASS\])(.*?)(\[\/SELECTED_CLASS\])/smi', $this->mainContent, $match)) {
				$this->selectedClass = trim($match[2]);
				$this->mainContent = str_replace($match[0], '', $this->mainContent);
			}
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

		// For changes on "txt" Table see MENU.cls.php

		// Updates to the Database
		if ($version < 2010052000) {
		}

		// Update the version
		$this->_updateVersionTable($version, $versionid, self::VERSION);
	}

}

?>