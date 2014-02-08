<?php

class DOWNLOADS extends MainPlugin {
	const VERSION = 2011101800;
	const LAYOUT = 'default';
	const FULLSCREEN_LAYOUT = 'fullscreen';
	const REGISTER_LAYOUT = 'registration';

	var $contentFile;
	var $_mainContent;
	var $_programContent;
	var $_programLine;
	var $_toolContent;
	var $_toolsPerLine;
	var $__toolCleanContent;
	var $_sectionContent;
	var $_sectionDelimiter;
	var $_sectionDelimiterImages;
	var $_registerLink;
	var $_titleText;

	public function __construct() {
		parent::__construct();
		$this->_isTextPlugin = true;

		$this->contentFile = "cont_downloads.tpl";
		$this->_mainContent = "";
		$this->_toolContent = "";
		$this->_toolCleanContent = "";
		$this->_toolsPerLine = 3;

		$this->_programContent = "";
		$this->_programLine = "";
		$this->_registerLink = "";;

		$this->_sectionContent = '';
		$this->_sectionDelimiter = array('','','','');
		$this->_sectionDelimiterImages = array('','','');
		$this->_titleText = array('', '');

		$this->_checkDatabase();
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
		$opt .= '[show_recursive]choose:true,false[/show_recursive]';
		$opt .= '[show_number]edit:integer:0[/show_number]';
		$opt .= '[show_section]choose:true,false[/show_section]';
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
		if ($method == PLG_PROGRAM_METHOD) {
			return $this->getProgramDetail();
		} else if ($method == PLG_DLOADREG_METHOD) {
			return $this->getProgramRegistration();
		} else {
			return $this->getProgramList($adminData);
		}
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
		return $this->getProgramList(null, true);
	}

	/**
	 * Return the OpenEditor function for ContentAdministration
	 *
	 * @param integer $id The TextID
	 * @return string
	 * @access public
	 */
	public function getEditFunction($id) {
		return 'files';
		return "javascript:openAdmin(1100,'download_content',".$id.");";
	}

	/**
	 * Return the CloseEditor function for ContentAdministration
	 *
	 * @param integer $id The TextID
	 * @return string
	 * @access public
	 */
	public function getCloseFunction($id) {
		return "javascript:closeDelightEdit();";
	}

	function checkTemplateFile($reg=false) {
		$match = null;
		$back = '';
		if (preg_match("/(\[TEMPLATE_FILE\])(.*?)(\[\/TEMPLATE_FILE\])/smi", $this->_toolContent, $match)) {
			$back = $match[2];
			$this->_toolContent = str_replace($match[0], '', $this->_toolContent);
			$match = null;
		}
		return $back;
	}

	/**
	 * Return the complete HTML based on the template for a List of all Images in a Section
	 *
	 * @param array $adminData
	 * @return string
	 */
	private function getProgramList($adminData=array(), $onlyContent=false) {
		$lang = pMessages::getLanguageInstance();
		$menu = pMenu::getMenuInstance();
		$SectionId = pURIParameters::get('sec', 0, pURIParameters::$INT);
		$adminAction = pURIParameters::get('adm', 0, pURIParameters::$INT);
		$userCheck = pCheckUserData::getInstance();

		if (count($adminData) > 5) {
			$text = $this->getTextEntryObject();
			foreach ($adminData as $k => $v) {
				$text->{$k} = $v;
			}
		} else {
			$text = $this->getTextEntryObject();
		}

		$_template = trim($this->_readTemplateFile($text->layout));
		$_template = str_replace('[ADMIN_FUNCTIONS]', "", $_template);
		$_template = preg_replace('/(\[TEXT_ADMIN_FUNCTIONS\])(.*?)(\[\/TEXT_ADMIN_FUNCTIONS\])/smi', "", $_template);
		$_template = preg_replace('/(\[ADMIN_REMOVE\])(.*?)(\[\/ADMIN_REMOVE\])/smi', '', $_template);
		$_template = str_replace('[ADMIN_REMOVE]', '', $_template);
		$_template = str_replace('[/ADMIN_REMOVE]', '', $_template);

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

		// Read the ContentFile (cont_downloads.tpl)
		if ( empty($adminData) || !empty($_template) ) {
			$this->_readContentFile(self::LAYOUT);

			// Check for CSS-File, CSS-Content and SCRIPT-File
			if ( (strlen(trim($this->_cssFile)) > 0) || (strlen(trim($this->_specialCssFile)) > 0) ) {
				$this->_hasCssImportFile = true;
			}

			if ( (strlen(trim($this->_scriptFile)) > 0) || (strlen(trim($this->_specialScriptFile)) > 0) ) {
				$this->_hasScriptImportFile = true;
			}

			if ( (strlen(trim($this->_cssContent)) > 0) || (strlen(trim($this->_specialCssContent)) > 0) ) {
				$this->_hasCssContent = true;
			}
		}

		// Check if the programms should be readed recursively or not
		if (substr_count($text->options, '#show_recursive=true#') >= 1) {
			$_recursive = true;
		} else {
			$_recursive = false;
		}

		// Get all Programs from Section
		if ( ($SectionId <= 0) && ((int)$text->text > 0) ) {
			$SectionId = (int)$text->text;
		}
		$_programs = $this->_getProgramsFromSection($SectionId, false, $_recursive);

		// Insert an ID-Field into the Title-Field
		$_titleBefore = substr($_template, 0, strpos($_template, '[TITLE]'));
		$_titleAfter  = substr($_template, strpos($_template, '[TITLE]'));
		$_template = $_titleBefore.'<span id="title_'.$text->id.'">'.$_titleAfter.'</span>';

		// Create the Content
		$match = array();
		$_cnt = 0;
		$_text = '';
		$_cont = $this->_programLine;
		foreach ($_programs as $program) {
			$_cnt++;
			$_tmp = $this->_toolContent;

			// Replace Variables
			$_tmp = str_replace('[PROGRAM_SRC]',         $program->src,    $_tmp);
			$_tmp = str_replace('[PROGRAM_NUMBER]',      $_cnt,                    $_tmp);
			$_tmp = str_replace('[PROGRAM_TYPE]',        $program->type,   $_tmp);
			$_tmp = str_replace('[PROGRAM_TYPE_EXT]',    $program->type_e, $_tmp);
			$_tmp = str_replace('[PROGRAM_FILE]',        $program->name,   $_tmp);
			$_tmp = str_replace('[PROGRAM_NAME]',        $program->name,   $_tmp);
			$_tmp = str_replace('[PROGRAM_SIZE]',        $this->humanReadableFileSize($program->size), $_tmp);
			$_tmp = str_replace('[PROGRAM_DOWNLOADED]',  number_format($program->loaded, 0, '.', '\''), $_tmp);
			$_tmp = str_replace('[PROGRAM_ICON]',        $program->icon->src, $_tmp);
			$_tmp = str_replace('[PROGRAM_ICON_HEIGHT]', $program->icon->height, $_tmp);
			$_tmp = str_replace('[PROGRAM_ICON_WIDTH]',  $program->icon->width, $_tmp);
			$_tmp = str_replace('[PROGRAM_DOWNLOADED]',  number_format($program->loaded, 0, '.', '\''), $_tmp);
			$_tmp = str_replace('[PROGRAM_DATE]',        $this->formatDate('d. M Y, H:i:s', $program->date), $_tmp);
			$_tmp = str_replace('[PROGRAM_DATE_SMALL]',  $this->formatDate('d. M Y', $program->date), $_tmp);
			$_tmp = str_replace('[PROGRAM_LAST_ACCESS]', $this->formatDate('d. M Y, H:i:s', $program->last), $_tmp);

			// Show the Detail-Link
			if ((integer)$adminAction > 0) {
				$_tmp = str_replace('[PROGRAM_DETAIL_LINK]', '/'.$lang->getShortLanguageName().'/program/'.$program->id.'/sec='.$program->section.'&adm='.$adminAction, $_tmp);
			} else if (((integer)$userCheck->getUserAccess() > 0)) {
				$_tmp = str_replace('[PROGRAM_DETAIL_LINK]', '/'.$lang->getShortLanguageName().'/program/'.$program->id.'/sec='.$program->section.'&adm=1', $_tmp);
			} else {
				$_tmp = str_replace("[PROGRAM_DETAIL_LINK]", '/'.$lang->getShortLanguageName().'/program/'.$program->id.'/sec='.$program->section, $_tmp);
			}

			// Check for Secure download
			if (!$program->secure && !($userCheck->checkAccess(ADM_DOWNLOAD) || ($userCheck->checkAccess("DOWNLOAD") && ($userCheck->CheckUserInfo('dl:'.$program->id))) ) ) {
				$_tmp = str_replace('[DOWNLOAD_LINK]', '/'.$lang->getShortLanguageName().'/'.$menu->getShortMenuName().'/adm=1&sec='.$program->section, $_tmp);
			} else {
				if (!$program->public) {
					$_tmp = str_replace('[DOWNLOAD_LINK]', $this->_registerLink, $_tmp);
					if ((integer)$adminAction > 0) {
						$_tmp = str_replace('[DOWNLOAD_LINK]', '/'.$lang->getShortLanguageName().'/dloadreg/'.$program->id.'/sec='.$program->section.'&adm='.$adminAction, $_tmp);
					} else if (((integer)$userCheck->getUserAccess() > 0)) {
						$_tmp = str_replace('[DOWNLOAD_LINK]', '/'.$lang->getShortLanguageName().'/dloadreg/'.$program->id.'/sec='.$program->section.'&adm=1', $_tmp);
					} else {
						$_tmp = str_replace('[DOWNLOAD_LINK]', '/'.$lang->getShortLanguageName().'/dloadreg/'.$program->id.'/sec='.$program->section, $_tmp);
					}
				} else {
					$_tmp = str_replace('[DOWNLOAD_LINK]', '/download/'.$program->id.'/'.$program->name, $_tmp);
				}
			}

			// Check for a Title
			$descr = strip_tags($program->title);
			if (!empty($descr) && preg_match_all('/(\[PROGRAM_TITLE)(((:)([\d]+?))?)(\])/smi', $_tmp, $match)) {
				for ($x = 0; $x < count($match[0]); $x++) {
					if ( ((integer)$match[5][$x] > 0) && (strlen($descr) > (integer)$match[5][$x])) {
						$_tmp = str_replace($match[0][$x], substr($descr, 0, (integer)$match[5][$x]).'...', $_tmp);
					} else {
						$_tmp = str_replace($match[0][$x], $descr, $_tmp);
					}
				}
				$_tmp = str_replace('[CAT_TITLE]', '', $_tmp);
				$_tmp = str_replace('[/CAT_TITLE]', '', $_tmp);
			}
			$_tmp = preg_replace('/\[CAT_TITLE\].*?\[\/CAT_TITLE\]/smi', '', $_tmp);
			$_tmp = preg_replace('/(\[PROGRAM_TITLE.*?\])/smi', '', $_tmp);

			// Check for a Description
			$descr = strip_tags($program->text);
			if (empty($descr)) {
				$_tmp = preg_replace('/(\[CUT_DESCRIPTION:false\])(.*?)(\[\/CUT_DESCRIPTION\])/smi', '', $_tmp);
			}
			if ((substr_count($text->options, '#show_description=false#') < 1) && preg_match_all('/\[PROGRAM_DESCRIPTION(:(\d+)?)?\]/smi', $_tmp, $match)) {
				for ($x = 0; $x < count($match[0]); $x++) {
					$len = isset($match[2][$x]) ? (int)$match[2][$x] : 0;
					if ( ($len > 0) && (strlen($descr) > $len)) {
						$_tmp = str_replace($match[0][$x], substr($descr, 0, $len).'...', $_tmp);
					} else {
						$_tmp = str_replace($match[0][$x], $descr, $_tmp);
					}
				}
				$_tmp = preg_replace('/(\[CUT_DESCRIPTION.*?\])(.*?)(\[\/CUT_DESCRIPTION\])/smi', '\\2', $_tmp);
			}
			$_tmp = preg_replace('/(\[CUT_DESCRIPTION.*?\])(.*?)(\[\/CUT_DESCRIPTION\])/smi', '', $_tmp);
			$_tmp = preg_replace('/(\[PROGRAM_DESCRIPTION.*?\])/smi', '', $_tmp);

			if (substr_count($text->options, '#show_data=false#') < 1) {
				$_tmp = str_replace('[CUT_DATA]', '', $_tmp);
				$_tmp = str_replace('[/CUT_DATA]', '', $_tmp);
			} else {
				$_tmp = preg_replace('/(\[CUT_DATA\])(.*?)(\[\/CUT_DATA\])/smi', '', $_tmp);
			}

			if (substr_count($text->options, '#show_title=false#') < 1) {
				$_tmp = str_replace('[CUT_TITLE]', '', $_tmp);
				$_tmp = str_replace('[/CUT_TITLE]', '', $_tmp);
			} else {
				$_tmp = preg_replace('/(\[CUT_TITLE\])(.*?)(\[\/CUT_TITLE\])/smi', '', $_tmp);
			}

			// Add the Image to a _programLine
			$_cont = str_replace('[TEXT]', $_tmp.'[TEXT]', $_cont);

			// Add a new thumbnailLine
			if ($_cnt%$this->_toolsPerLine == 0) {
				$_text .= str_replace('[TEXT]', '', $_cont);
				$_cont  = $this->_programLine;
			}
		}

		// Add some toolCleanContent until the line is full
		$_addClean = false;
		while ($_cnt%$this->_toolsPerLine != 0) {
			$_addClean = true;
			$_cont = str_replace('[TEXT]', $this->_toolCleanContent.'[TEXT]', $_cont);
			$_cnt++;
		}
		if ($_addClean) {
			$_text .= str_replace('[TEXT]', '', $_cont);
		}

		if ( empty($adminData) || !empty($_template) ) {
			// Check for a required SectionList
			if (substr_count($text->options, '#show_section=true#') >= 1) {
				$_sectionContent = $this->createSectionContentList($SectionId);
			} else {
				$_sectionContent = '';
			}

			// Append the thumbnails
			$_text = str_replace('[TEXT]', $_text, $this->_programContent);

			// Append the Section-List, or cut them out
			if (strlen(trim($_sectionContent)) > 0) {
				$_text = str_replace('[CUT_SECTION]', '', $_text);
				$_text = str_replace('[/CUT_SECTION]', '', $_text);
				$_text = str_replace('[SECTION_LIST]', $_sectionContent, $_text);
			} else {
				$_text = preg_replace('/(\[CUT_SECTION\])(.*?)(\[\/CUT_SECTION\])/smi', '', $_text);
			}

			$html = $_template;
			if (!$onlyContent) {
				// Replace [TITLE] or strip out the CAT_TITLE...
				$_title = $text->title;
				if ($_title == 'default') $_title = $this->_getSectionName($SectionId, 'prs');
				if ( !empty($_title) && (substr_count($text->options, '#show_title=false#') <= 0) ) {
					$_title = $this->_titleText[0].$_title.$this->_titleText[1];
					$html = str_replace('[TITLE]', $this->_appendTitleAnchor($_title, $text->id), $html);
				} else {
					$html = str_replace('[TITLE]', '', $html);
					if (substr_count($text->options, '#show_title=false#') > 0) {
						$html = preg_replace('/(\[CAT_TITLE\])(.*?)(\[\/CAT_TITLE\])/smi', '', $html);
						$html = preg_replace('/(\[CUT_TITLE\])(.*?)(\[\/CUT_TITLE\])/smi', '', $html);
					}
				}
			} else {
				$html = str_replace('[TITLE]', '', $html);
				$html = preg_replace('/(\[CAT_TITLE\])(.*?)(\[\/CAT_TITLE\])/smi', '', $html);
				$html = preg_replace('/(\[CUT_TITLE\])(.*?)(\[\/CUT_TITLE\])/smi', '', $html);
			}
			$html = str_replace('[CAT_TITLE]', '', str_replace('[/CAT_TITLE]', '', $html));
			$html = str_replace('[CUT_TITLE]', '', str_replace('[/CUT_TITLE]', '', $html));

			// Replace [TEXT] or strip out the CAT_CONTENT...
			if ( (strlen($_text) > 0) || ($userCheck->checkAccess('content')) ) {
				if (!$onlyContent) {
					$this->appendTextAdminAddons($_text, $text, $text->id);
				}

				$html = str_replace('[TEXT]', $_text,  $html);
				$html = str_replace('[CAT_CONTENT]', '', str_replace('[/CAT_CONTENT]', '', $html));
			} else {
				$html = str_replace('[TEXT]', '',  $html);
				$html = preg_replace('/(\[CAT_CONTENT\])(.*?)(\[\/CAT_CONTENT\])/smi', '', $html);
			}

			// Replace Text-Options
			$html = $this->ReplaceLayoutOptions($html, $text->options);
		} else {
			$html = $_text;
		}

		return $html;
	}

	// Return the complete HTML based on the template for ProgramDetails
	private function getProgramDetail() {
		$menu = pMenu::getMenuInstance();
		$lang = pMessages::getLanguageInstance();
		$userCheck = pCheckUserData::getInstance();
		$adminAction = pURIParameters::get('adm', 0, pURIParameters::$INT);

		// Read the ContentFile (cont_screenshots.tpl)
		$this->_readContentFile(self::FULLSCREEN_LAYOUT);
		$_tplFile = $this->checkTemplateFile();
		$_template = '[TEXT]';
		if (!empty($_tplFile)) {
			$_template = $this->_readTemplateFile($_tplFile);
		}

		// Check for CSS-File, CSS-Content and SCRIPT-File
		if ( (strlen(trim($this->_cssFile)) > 0) || (strlen(trim($this->_specialCssFile)) > 0) ) {
			$this->_hasCssImportFile = true;
		}

		if ( (strlen(trim($this->_scriptFile)) > 0) || (strlen(trim($this->_specialScriptFile)) > 0) ) {
			$this->_hasScriptImportFile = true;
		}

		if ( (strlen(trim($this->_cssContent)) > 0) || (strlen(trim($this->_specialCssContent)) > 0) ) {
			$this->_hasCssContent = true;
		}

		// Get title
		$_title = '';
		$match = array();
		if (preg_match('/(\[TITLE_TEXT\])(.*?)(\[\/TITLE_TEXT\])/smi', $this->_toolContent, $match)) {
			$_title = $match[2];
			$this->_toolContent = str_replace($match[0], '', $this->_toolContent);
		}
		$match = null;

		// Get all Images from Section
		$program = new pFileEntry($menu->getMenuId());

		// Create the Content
		$_tmp = $this->_toolContent;

		// Replace Variables
		$_tmp = str_replace("[PROGRAM_SRC]",         $program->direct,  $_tmp);
		$_tmp = str_replace("[PROGRAM_NUMBER]",      1, $_tmp);
		$_tmp = str_replace("[PROGRAM_TYPE]",        $program->mime, $_tmp);
		$_tmp = str_replace("[PROGRAM_TYPE_EXT]",    $program->comment, $_tmp);
		$_tmp = str_replace("[PROGRAM_FILE]",        $program->name, $_tmp);
		$_tmp = str_replace("[PROGRAM_DOWNLOADED]",  number_format($program->viewed, 0, ".", "'"), $_tmp);
		$_tmp = str_replace("[PROGRAM_ICON]",        $program->icon, $_tmp);
		$_tmp = str_replace("[PROGRAM_ICON_HEIGHT]", $program->icon_height, $_tmp);
		$_tmp = str_replace("[PROGRAM_ICON_WIDTH]",  $program->icon_width, $_tmp);
		$_tmp = str_replace("[PROGRAM_DATE]",        $this->formatDate("d. M Y, H:i:s", $program->date), $_tmp);
		$_tmp = str_replace("[PROGRAM_DATE_SMALL]",  $this->formatDate("d. M Y", $program->date), $_tmp);
		$_tmp = str_replace("[PROGRAM_LAST_ACCESS]", $this->formatDate("d. M Y, H:i:s", $program->last), $_tmp);
		$_tmp = str_replace("[PROGRAM_SIZE]",        $this->humanReadableFileSize($program->size), $_tmp);

		// Show the Detail-Link
		if ((integer)$adminAction > 0) {
			$_tmp = str_replace('[PROGRAM_DETAIL_LINK]', '/'.$lang->getShortLanguageName().'/program/'.$program->id.'/sec='.$program->section.'&adm='.$adminAction, $_tmp);
		} else if (((integer)$userCheck->getUserAccess() > 0)) {
			$_tmp = str_replace('[PROGRAM_DETAIL_LINK]', '/'.$lang->getShortLanguageName().'/program/'.$program->id.'/sec='.$program->section.'&adm=1', $_tmp);
		} else {
			$_tmp = str_replace("[PROGRAM_DETAIL_LINK]", '/'.$lang->getShortLanguageName().'/program/'.$program->id.'/sec='.$program->section, $_tmp);
		}

		// Check for Secure download
		if ($program->secure && !($userCheck->checkAccess(ADM_DOWNLOAD) || ($userCheck->checkAccess("DOWNLOAD") && ($userCheck->CheckUserInfo('dl:'.$program->id))) ) ) {
			$_tmp = str_replace('[DOWNLOAD_LINK]', '/'.$lang->getShortLanguageName().'/'.$menu->getShortMenuName().'/adm=1&sec='.$program->section, $_tmp);
		} else {
			if (!$program->public) {
				$_tmp = str_replace('[DOWNLOAD_LINK]', $this->_registerLink, $_tmp);
				if ((integer)$adminAction > 0) {
					$_tmp = str_replace('[DOWNLOAD_LINK]', '/'.$lang->getShortLanguageName().'/dloadreg/'.$program->id.'/sec='.$program->section.'&adm='.$adminAction, $_tmp);
				} else if (((integer)$userCheck->getUserAccess() > 0)) {
					$_tmp = str_replace('[DOWNLOAD_LINK]', '/'.$lang->getShortLanguageName().'/dloadreg/'.$program->id.'/sec='.$program->section.'&adm=1', $_tmp);
				} else {
					$_tmp = str_replace('[DOWNLOAD_LINK]', '/'.$lang->getShortLanguageName().'/dloadreg/'.$program->id.'/sec='.$program->section, $_tmp);
				}
			} else {
				$_tmp = str_replace('[DOWNLOAD_LINK]', '/download/'.$program->id.'/'.$program->name, $_tmp);
			}
		}

		// Check for a Title
		$descr = strip_tags($program->title);
		if (preg_match_all("/(\[PROGRAM_TITLE)(((:)([\d]+?))?)(\])/smi", $_tmp, $match)) {
			for ($x = 0; $x < count($match[0]); $x++) {
				if ( ((integer)$match[5][$x] > 0) && (strlen($descr) > (integer)$match[5][$x])) {
					$_tmp = str_replace($match[0][$x], substr($descr, 0, (integer)$match[5][$x]).'...', $_tmp);
				} else {
					$_tmp = str_replace($match[0][$x], $descr, $_tmp);
				}
			}
		}

		// Check for a Description
		if (preg_match("/(\[PROGRAM_DESCRIPTION?)(((:)([\d]+?))?)(\])/smi", $_tmp, $match)) {
			if ((integer)$match[5] > 0) {
				$_tmp = str_replace($match[0], substr($program->text, 0, (integer)$match[5]).'...', $_tmp);
			} else {
				$_tmp = str_replace($match[0], $program->text, $_tmp);
			}
		}

		// Add the File to a _programLine
		$_cont = str_replace("[TEXT]", $_tmp, $this->_programLine);
		$_text = str_replace("[TEXT]", "", $_cont);

		// Append the thumbnails
		$_text = str_replace("[TEXT]", $_text, $this->_programContent);
		$html = $_template;

		// Replace [TEXT] or strip out the CAT_CONTENT...
		if (!empty($_text)) {
			$html = str_replace("[TEXT]",  $_text,  $html);
			$html = str_replace("[CAT_CONTENT]", "", str_replace("[/CAT_CONTENT]", "", $html));
		} else {
			$html = str_replace("[TEXT]",  "",  $html);
			$html = preg_replace("/(\[CAT_CONTENT\])(.*?)(\[\/CAT_CONTENT\])/smi", "", $html);
		}

		// Replace [TITLE] or strip out the CAT_TITLE...
		if (empty($_title)) {
			$_title = $program->title;
		}
		if (empty($_title)) {
			$_title = $program->name;
		}
		if (!empty($_title)) {
			$html = str_replace("[TITLE]", $_title, $html);
			$html = str_replace("[CAT_TITLE]", "", str_replace("[/CAT_TITLE]", "", $html));
		} else {
			$html = str_replace("[TITLE]", "", $html);
			$html = preg_replace("/(\[CAT_TITLE\])(.*?)(\[\/CAT_TITLE\])/smi", "", $html);
		}

		// Replace Text-Options
		$html = $this->ReplaceLayoutOptions($html, "#title=default#");

		return $html;
	}

	// Return a Pergistration-Form for a Download
	private function getProgramRegistration() {
		global $langshort, $MainMenu, $SectionId, $PostId;
		$match = null;

		// Get Program-details
		$_program = $this->getProgramData($MainMenu);

		// Read the ContentFile (cont_screenshots.tpl)
		$this->_readContentFile(self::REGISTER_LAYOUT);
		$_tplFile = $this->checkTemplateFile();
		$_template = $this->_readTemplateFile($_tplFile);

		// Check for CSS-File, CSS-Content and SCRIPT-File
		if ( (strlen(trim($this->_cssFile)) > 0) || (strlen(trim($this->_specialCssFile)) > 0) ) {
			$this->_hasCssImportFile = true;
		}

		if ( (strlen(trim($this->_scriptFile)) > 0) || (strlen(trim($this->_specialScriptFile)) > 0) ) {
			$this->_hasScriptImportFile = true;
		}

		if ( (strlen(trim($this->_cssContent)) > 0) || (strlen(trim($this->_specialCssContent)) > 0) ) {
			$this->_hasCssContent = true;
		}

		// Get title
		$_title = "";
		if (preg_match("/(\[TITLE_TEXT\])(.*?)(\[\/TITLE_TEXT\])/smi", $this->_toolContent, $match)) {
			$_title = $match[2];
			$this->_toolContent = str_replace($match[0], "", $this->_toolContent);
		}
		$match = null;

		// Create the Content
		$_text = $this->_toolContent;

		// Replace Variables
		$_text = str_replace("[DIRECT_DOWNLOAD_LINK]", "/download/".$_program['id']."/".$_program['name'], $_text);
		$_text = str_replace("[PROGRAM_FILE]", $_program['name'], $_text);

		// Append the thumbnails
		$_text = str_replace("[TEXT]", $_text, $this->_programContent);

		// Replace [TITLE] or strip out the CAT_TITLE...
		if (strlen(trim($_title)) > 0) {
			$html = str_replace("[TITLE]", $_title, $_template);
			$html = str_replace("[CAT_TITLE]", "", str_replace("[/CAT_TITLE]", "", $html));
		} else {
			$html = str_replace("[TITLE]", "", $_template);
			$html = preg_replace("/(\[CAT_TITLE\])(.*?)(\[\/CAT_TITLE\])/smi", "", $html);
		}

		// Replace [TEXT] or strip out the CAT_CONTENT...
		if (strlen(trim($_text)) > 0) {
			$html = str_replace("[TEXT]",  $_text,  $html);
			$html = str_replace("[CAT_CONTENT]", "", str_replace("[/CAT_CONTENT]", "", $html));
		} else {
			$html = str_replace("[TEXT]",  "",  $html);
			$html = preg_replace("/(\[CAT_CONTENT\])(.*?)(\[\/CAT_CONTENT\])/smi", "", $html);
		}

		// Replace Text-Options
		$html = $this->ReplaceLayoutOptions($html, "#title=big#");

		return $html;
	}

	// Create and Return the Section-List based on the template (only if it is required by this entry)
	function createSectionContentList($id) {
		global $SectionId;
		if ((integer)$SectionId <= 0) {
			$selected =  $this->getSelectedSectionStructure($id, 'prs', 'parent', 'id');
		} else {
			$selected =  $this->getSelectedSectionStructure($SectionId, 'prs', 'parent', 'id');
		}

		if (preg_match("/(\[SECTION\])(.*?)(\[\/SECTION\])/smi", $this->_sectionContent, $match)) {
			$_section = $match[2];

			// in newer versions, you can use [SECTION_CONTENT] to build more complex sectionlists
			if (substr_count($match[0], "[SECTION_CONTENT]") > 0) {
				$html = str_replace($match[0], "", $this->_sectionContent);
				$html = str_replace("[SELECTED_SECTION_NAME]", $this->_getProgramSectionName($selected[0]), $html);
				$html = str_replace("[SECTION_CONTENT]", $this->_sub_createSectionContentList($id, "", $selected, $_section), $html);
			} else {
				// used for Old-Style-Sections
				$html = str_replace($match[0], "[SECTION]", $this->_sectionContent);
				$html = str_replace("[SELECTED_SECTION_NAME]", $this->_getProgramSectionName($selected[0]), $html);
				$html = str_replace("[SECTION]", $this->_sub_createSectionContentList($id, "", $selected, $_section), $html);
			}
		} else {
			$html = '<span style="color:rgb(250,100,100);font-weight:bold;">Template-failure in <strong>PROGRAM_SECTION</strong></span>';
		}
		return $html;
	}

	/**
		 * Create a SectionList with Template-Design
		 *
		 * this function calls itselfs recursively until lasts section is arrived
		 *
		 * @param int $id DB-id of root-section
		 * @param string $before Content to insert before the section [*_SECTION_DESIMITER]
		 * @param int $selected DB-id of selected Section
		 * @param string $template Section-Template
		 * @return String Content of current Section
		 */
	function _sub_createSectionContentList($id, $before, $selected, $template) {
		global $langshort, $MainMenu;
		$html = '';
		$match = null;

		// Check for SUBSECTION tags in $template - for more complex section-design
		$templateOrig = $template;
		if (preg_match("/(\[SUBSECTION\])(.*?)(\[\/SUBSECTION\])/smi", $template, $match)) {
			$subsection = $match[2];
			$template = str_replace($match[0], $match[2], $template);
		} else {
			$subsection = $template;
		}

		// if $before is empty, this indicates that this is the root-node
		if (strlen(trim($before)) <= 0) {
			$sql = "SELECT * FROM ".$this->DB->Table('prs')." WHERE ".$this->DB->Field('prs','id')."='".$id."'";
			$res = $this->DB->ReturnQueryResult($sql);
			if ($res) {
				$row = mysql_fetch_assoc($res);

				// Get the Number of available Programs in this Section
				$_count = $this->_getNumProgramsFromSection($id);

				// Check if this section is selected
				if (in_array((integer)$id, $selected)) {
					$html = preg_replace("/(\[IF_SELECTED:')(.*?)(':')(.*?)('\])/smi", "\\2", $template);
				} else {
					$html = preg_replace("/(\[IF_SELECTED:')(.*?)(':')(.*?)('\])/smi", "\\4", $template);
				}
				// Replace real selected
				if ((int)$id == (int)$selected[0]) {
					$html = preg_replace("/(\[IF_SELECTED_ID:')(.*?)(':')(.*?)('\])/smi", "\\2", $html);
				} else {
					$html = preg_replace("/(\[IF_SELECTED_ID:')(.*?)(':')(.*?)('\])/smi", "\\4", $html);
				}

				// replace othe tags
				$html = str_replace("[NUMBER_OF_PROGRAMS]", $_count, $html);
				$html = str_replace("[SECTION_DELIMITER]",  "", $html);
				$html = str_replace("[SECTION_NAME]",       $row[$this->DB->FieldOnly('prs','text')], $html);
				$html = str_replace("[SECTION_NUMBER]",     $id, $html);
				$html = str_replace("[SECTION_ID]",         $id, $html);
				$html = str_replace("[PARENT_SECTION_ID]",  0, $html);
				$html = str_replace("[SECTION_LINK]",       "/".$langshort."/".$MainMenu."/sec=".$row[$this->DB->FieldOnly('prs','id')], $html);
			}
			$this->DB->FreeDatabaseResult($res);
		}

		// get all sections where the current section is the parent (subsections from current)
		$sql = "SELECT * FROM ".$this->DB->Table('prs')." WHERE ".$this->DB->Field('prs','parent')." = '".$id."'";
		$res = $this->DB->ReturnQueryResult($sql);
		if ($res) {
			$cnt = 1;
			while ($row = mysql_fetch_assoc($res)) {

				// Create current and next delimiter
				if ($cnt < mysql_num_rows($res)) {
					$_delim = $before.$this->_sectionDelimiter['entry'];
					$_delim_child = $before.$this->_sectionDelimiter['down'];
				} else {
					$_delim = $before.$this->_sectionDelimiter['last'];
					$_delim_child = $before.$this->_sectionDelimiter['clean'];
				}

				// Get the Number of available Programs in this Section
				$_count = $this->_getNumProgramsFromSection($row[$this->DB->FieldOnly('prs','id')]);

				// if we can locate SECTION_CONTENT in $subsection, we replace this with $template
				// and insert the tag SECTION_SECTION after so we know where wo insert the next section on this level
				$tmp = $template;
				if (substr_count($tmp, "[SECTION_CONTENT]") <= 0) {
					$tmp .= "[SECTION_CONTENT]";
				}

				// Check if this Section is selected
				if (in_array((int)$row[$this->DB->FieldOnly('prs','id')], $selected)) {
					$tmp = preg_replace("/(\[IF_SELECTED:')(.*?)(':')(.*?)('\])/smi", "\\2", $tmp);
				} else {
					$tmp = preg_replace("/(\[IF_SELECTED:')(.*?)(':')(.*?)('\])/smi", "\\4", $tmp);
				}
				// Replace real selected
				if ((int)$row[$this->DB->FieldOnly('prs','id')] == (int)$selected[0]) {
					$tmp = preg_replace("/(\[IF_SELECTED_ID:')(.*?)(':')(.*?)('\])/smi", "\\2", $tmp);
				} else {
					$tmp = preg_replace("/(\[IF_SELECTED_ID:')(.*?)(':')(.*?)('\])/smi", "\\4", $tmp);
				}

				// replace all other tags
				$tmp = str_replace("[NUMBER_OF_PROGRAMS]", $_count, $tmp);
				$tmp = str_replace("[SECTION_DELIMITER]",  $_delim, $tmp);
				$tmp = str_replace("[SECTION_NAME]",       $row[$this->DB->FieldOnly('prs','text')], $tmp);
				$tmp = str_replace("[SECTION_NUMBER]",     $row[$this->DB->FieldOnly('prs','id')], $tmp);
				$tmp = str_replace("[SECTION_ID]",         $row[$this->DB->FieldOnly('prs','id')], $tmp);
				$tmp = str_replace("[PARENT_SECTION_ID]",  $id, $tmp);
				$tmp = str_replace("[SECTION_LINK]",       "/".$langshort."/".$MainMenu."/sec=".$row[$this->DB->FieldOnly('prs','id')], $tmp);

				// Get the child-Sections
				$sub = $this->_sub_createSectionContentList($row[$this->DB->FieldOnly('prs','id')], $_delim_child, $selected, $templateOrig);

				// if subsections are available, replace the image with ENTRY, otherwise with EMPTY
				if (strlen($sub) > 0) {
					if (in_array((integer)$row[$this->DB->FieldOnly('prs','id')], $selected)) {
						$tmp = str_replace("[SECTION_IMAGE]", $this->_sectionDelimiterImages['expanded'], $tmp);
					} else {
						$tmp = str_replace("[SECTION_IMAGE]", $this->_sectionDelimiterImages['entry'], $tmp);
					}
				} else {
					$tmp = str_replace("[SECTION_IMAGE]", $this->_sectionDelimiterImages['empty'], $tmp);
				}

				// replace the SECTION_SECTION tag with the ChildSections
				$tmp = str_replace("[SECTION_CONTENT]", $sub, $tmp);
				if (substr_count($html, "[SECTION_CONTENT]") > 0) {
					$html = str_replace("[SECTION_CONTENT]", $tmp."[SECTION_CONTENT]", $html);
				} else {
					$html .= $tmp;
				}

				// increase the counter ;-)
				$cnt++;
			}
			$html = str_replace("[SECTION_CONTENT]", "", $html);

			// replace the SECTION_IMAGE in html (replace it with ENTRY, because we have some entries if we are here)
			if (in_array((integer)$id, $selected)) {
				$html = str_replace("[SECTION_IMAGE]", $this->_sectionDelimiterImages['expanded'], $html);
			} else {
				$html = str_replace("[SECTION_IMAGE]", $this->_sectionDelimiterImages['entry'], $html);
			}
		} else {
			// replace the SECTION_IMAGE in html (replace it with EMPTY, because we don't have any here
			$html = str_replace("[SECTION_IMAGE]", $this->_sectionDelimiterImages['empty'], $html);
			$html = str_replace("[SECTION_CONTENT]", "", $html);
		}
		return $html;
	}

	// Return the Name of an Program-Section (if $isProg is true, the ID is an ProgramId and not an SectionID)
	function _getProgramSectionName($id, $isProg=false)
	{
		if ($isProg)
		$sql = "SELECT ".$this->DB->Table('prs').".* FROM ".$this->DB->Table('prg').",".$this->DB->Table('prs')." WHERE ".$this->DB->Field('prg','id')." = '".$id."' AND ".$this->DB->Field('prg','section')." = ".$this->DB->Field('prs','id')."";
		else
		$sql = "SELECT * FROM ".$this->DB->Table('prs')." WHERE ".$this->DB->Field('prs','id')." = '".$id."'";
		$res = $this->DB->ReturnQueryResult($sql);
		if ($res)
		{
			$row = mysql_fetch_assoc($res);
			$this->DB->FreeDatabaseResult($res);
			return $row[$this->DB->FieldOnly('prs','text')];
		}
		else
		return "unknown";
	}

	/**
		 * Return a List with all Programs from a ProgramSection
		 *
		 * @param integer $id ID from Section or Program
		 * @param boolean $isProg true if $id is a Program, otherwise $id is a Section
		 * @param boolean $recursive Get Programms recursive
		 * @return array List with all programs
		 */
	protected function _getProgramsFromSection($id, $isProg=false, $recursive=false) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		if ($isProg) {
			$sql = "SELECT [prs.id] FROM [table.prg],[table.prs] WHERE [prg.id]=".(int)$id." AND [prg.section]=[prs.id] ORDER BY [prg.order] ASC;";
			$db->run($sql, $res);
			if ($res->getFirst()) {
				$id = $res->{$db->getFieldName('prs.id')};
			} else {
				$id = 0;
			}
			$res = null;
		}

		// get the SectionID's from Subsections if
		if ($recursive) {
			$idList = $this->getChildSectionList($id, 'prs', 'id', 'parent');
		} else {
			$idList = array($id);
		}

		// go trough each section and get the ProgramID
		$back = array();
		foreach ($idList as $sid) {
			$sql = "SELECT [prg.id] FROM [table.prg] WHERE [prg.section]=".(int)$sid." ORDER BY [prg.order] ASC;";
			$db->run($sql, $res);
			if ($res->getFirst()) {
				while ($res->getNext()) {
					$_tmp = $this->getProgramObject($res->{$db->getFieldName('prg.id')});
					if ($_tmp->id > 0) {
						$back[] = $_tmp;
					}
					unset($_tmp);
				}
			}
			$res = null;
		}
		return $back;
	}

	// Return the Number of programs in a Section
	function _getNumProgramsFromSection($id) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = "SELECT COUNT([prg.id]) AS num FROM [table.prg] WHERE [prg.section]=".(int)$id.";";
		$db->run($sql, $res);
		$num = 0;
		if ($res->getFirst()) {
			$num = (int)$res->num;
		}
		$res = null;
		return $num;
	}

	// Reads the Contents-File for FileDetails
	protected function _readContentFile($type="") {
		// Create some default styles
		$this->_programContent = '<table>[TEXT]</table>';
		$this->_programLine = '<tr>[TEXT]</tr>';
		$this->_toolsPerLine = 2;
		$this->_toolContent = '<td style="text-align:center;"><a target="_blank" href="[DOWNLOAD_LINK]"><img src="[PROGRAM_ICON]" style="border-width:0px;width:[PROGRAM_ICON_WIDTH]px;height:[PROGRAM_ICON_HEIGHT]px;margin:5px;" alt="[PROGRAM_TITLE]" /><br />[PROGRAM_FILE]</a><br /><br /><strong>Description:</strong> [PROGRAM_DESCRIPTION]<br /><strong>Type:</strong> [PROGRAM_TYPE]<br /><strong>size:</strong> [PROGRAM_SIZE]</td>'.chr(10);
		$this->_toolCleanContent = '<td>&nbsp;</td>'.chr(10);

		// Set the ScreenshotLayoutFile and read them
		$layout = ABS_TEMPLATE_DIR."/".$this->contentFile;
		if (is_file($layout) && is_readable($layout)) {
			$fp = fopen($layout, "r");
			$cont = fread($fp, filesize($layout));
			fclose($fp);

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

			// Check for [PROGRAM_REGISTER_LINK]
			if (preg_match("/(\[PROGRAM_REGISTER_LINK\])(.*?)(\[\/PROGRAM_REGISTER_LINK\])/smi", $cont, $match)) {
				$this->_registerLink = $match[2];
			}

			// Check for [OPTIONS]
			$this->_contentOptions = $this->parseOptionsTags($cont, $this->_contentOptions);

			// Check for a [LAYOUT]...[/LAYOUT] as _mainContent
			if (preg_match_all("/(\[LAYOUT)((:".$type.")?)(\])(.*?)(\[\/LAYOUT\])/smi", $cont, $match)) {
				for ($i = 0; $i < count($match[0]); $i++) {
					if ( ($match[3][$i] == ":".$type) || ((trim($type) == "default") && (strlen(trim($match[3][$i])) <= 0)) ) {
						$this->_programContent = $match[5][$i];
					}
				}
			}

			// Check for [THUMBNAIL_LINE]...[/THUMBNAIL_LINE]
			if (preg_match("/(\[PROGRAM_LINE\])(.*?)(\[\/PROGRAM_LINE\])/smi", $cont, $match)) {
				$this->_programLine = $match[2];
			}

			// Check for [THUMBNAIL:type:NumOfImgPerEntry]...[/THUMBNAIL]
			if (preg_match_all("/(\[PROGRAM:)(.*?)(:)([\d]+?)(\])(.*?)(\[\/PROGRAM\])/smi", $cont, $match)) {
				for ($i = 0; $i < count($match[0]); $i++) {
					if (strToLower($match[2][$i]) == strToLower($type)) {
						$this->_toolContent  = $match[6][$i];
						$this->_toolsPerLine = (integer)$match[4][$i];
						break;
					}
				}
			}

			// Check for [THUMBNAIL:type:NumOfImgPerEntry]...[/THUMBNAIL]
			if (preg_match_all("/(\[PROGRAM_CLEAN:)(.*?)(\])(.*?)(\[\/PROGRAM_CLEAN\])/smi", $cont, $match)) {
				for ($i = 0; $i < count($match[0]); $i++) {
					if (strToLower($match[2][$i]) == strToLower($type)) {
						$this->_toolCleanContent  = $match[4][$i];
						break;
					}
				}
			}

			// Check for a Section-Content
			if (preg_match("/(\[PROGRAM_SECTION\])(.*?)(\[\/PROGRAM_SECTION\])/smi", $cont, $match)) {
				$this->_sectionContent = $match[2];
			}

			// Check for a Section-Delimiter
			if (preg_match("/(\[PROGRAM_SECTION_DELIMITER\])(.*?)(\[\/PROGRAM_SECTION_DELIMITER\])/smi", $cont, $match)) {
				// Read all Section-Delimiter, found in the PROGRAM_SECTION_DELIMITER
				if (preg_match_all("/(\[)(CLEAN|DOWN|ENTRY|LAST)(\])(.*?)(\[\/\\2\])/smi", $match[2], $_match)) {
					$this->_sectionDelimiter = array();
					for ($i = 0; $i < count($_match[0]); $i++) {
						$this->_sectionDelimiter[strToLower($_match[2][$i])] = $_match[4][$i];
					}
				}
			}

			// Check for a Section-Images
			if (preg_match("/(\[SECTION_DELIMITER_IMAGE\])(.*?)(\[\/SECTION_DELIMITER_IMAGE\])/smi", $cont, $match)) {
				// Read all Section-Delimiter, found in the SECTION_DELIMITER_IMAGE
				if (preg_match_all("/(\[)(ENTRY|EMPTY|EXPANDED)(\])(.*?)(\[\/\\2\])/smi", $match[2], $_match)) {
					$this->_sectionDelimiterImages = array();
					for ($i = 0; $i < count($_match[0]); $i++) {
						$this->_sectionDelimiterImages[strToLower($_match[2][$i])] = $_match[4][$i];
					}
				}
			}

			// Check for [TITLE_TEXT:before]
			if (preg_match("/(\[TITLE_TEXT:before\])(.*?)(\[\/TITLE_TEXT\])/smi", $cont, $match)) {
				$this->_titleText[0] = $match[2];
			}
			// Check for [TITLE_TEXT:before]
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
	 */
	function _checkDatabase() {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;

		// Get the current Version
		$v = $this->_checkMainDatabase();
		$version = $v[0];
		$versionid = $v[1];

		// Updates to the Database
		if ($version < 2006010600) {
			// Create the Programs-Table
			$sql  = "CREATE TABLE IF NOT EXISTS [table.prg] (".
			" [field.prg.id] INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,".
			" [field.prg.program] VARCHAR(150) NOT NULL DEFAULT '',".
			" [field.prg.name] VARCHAR(200) NOT NULL DEFAULT '',".
			" [field.prg.section] INT(10) UNSIGNED NOT NULL DEFAULT 0,".
			" [field.prg.local] INT(1) UNSIGNED NOT NULL DEFAULT 0,".
			" [field.prg.register] INT(1) UNSIGNED NOT NULL DEFAULT 0,".
			" [field.prg.secure] INT(1) UNSIGNED NOT NULL DEFAULT 0,".
			" PRIMARY KEY (id),".
			" UNIQUE KEY id (id)".
			" ) TYPE=MyISAM;";
			$db->run($sql, $res);
			$res = null;

			// Create the ProgramText-Table
			$sql  = "CREATE TABLE IF NOT EXISTS [table.prt] (".
			" [field.prt.id] INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,".
			" [field.prt.program] INT(10) UNSIGNED NOT NULL DEFAULT 0,".
			" [field.prt.title] VARCHAR(200) NOT NULL DEFAULT '',".
			" [field.prt.text] TEXT NOT NULL DEFAULT '',".
			" [field.prt.html] INT(1) UNSIGNED NOT NULL DEFAULT 0,".
			" [field.prt.lang] INT(10) UNSIGNED NOT NULL DEFAULT 0,".
			" PRIMARY KEY (id),".
			" UNIQUE KEY id (id)".
			" ) TYPE=MyISAM;";
			$db->run($sql, $res);
			$res = null;

			// Create the DownloadLog-Table
			$sql  = "CREATE TABLE IF NOT EXISTS [table.dll] (".
			" [field.dll.id] INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,".
			" [field.dll.file] VARCHAR(200) NOT NULL DEFAULT '',".
			" [field.dll.real] VARCHAR(100) NOT NULL DEFAULT '',".
			" [field.dll.file_id] INT(10) UNSIGNED NOT NULL DEFAULT 0,".
			" [field.dll.file_size] BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,".
			" [field.dll.section] INT(10) UNSIGNED NOT NULL DEFAULT 0,".
			" [field.dll.ip] VARCHAR(50) NOT NULL DEFAULT '',".
			" [field.dll.domain] VARCHAR(150) NOT NULL DEFAULT '',".
			" [field.dll.browser] VARCHAR(100) NOT NULL DEFAULT '',".
			" [field.dll.time] DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',".
			" [field.dll.lang] VARCHAR(50) NOT NULL DEFAULT '',".
			" [field.dll.user] INT(10) UNSIGNED NOT NULL DEFAULT 0,".
			" PRIMARY KEY (id),".
			" UNIQUE KEY id (id)".
			" ) TYPE=MyISAM;";
			$db->run($sql, $res);
			$res = null;

			// Create the ProgramMirror-Table
			$sql  = "CREATE TABLE IF NOT EXISTS [table.mir] (".
			" [field.mir.id] INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,".
			" [field.mir.program] INT(10) UNSIGNED NOT NULL DEFAULT 0,".
			" [field.mir.url] VARCHAR(250) NOT NULL DEFAULT '',".
			" [field.mir.user] VARCHAR(50) NOT NULL DEFAULT '',".
			" [field.mir.passwd] VARCHAR(100) NOT NULL DEFAULT '',".
			" [field.mir.update] DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',".
			" [field.mir.active] INT(1) UNSIGNED NOT NULL DEFAULT 0,".
			" PRIMARY KEY (id),".
			" UNIQUE KEY id (id)".
			" ) TYPE=MyISAM;";
			$db->run($sql, $res);
			$res = null;

			// Create the ProgramSection-Table
			$sql  = "CREATE TABLE IF NOT EXISTS [table.prs] (".
			" [field.prs.id] INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,".
			" [field.prs.parent] INT(10) UNSIGNED NOT NULL DEFAULT 0,".
			" [field.prs.text] VARCHAR(100) NOT NULL DEFAULT '',".
			" PRIMARY KEY (id),".
			" UNIQUE KEY id (id)".
			" ) TYPE=MyISAM;";
			$db->run($sql, $res);
			$res = null;

			// Insert base-Section if not already exists
			$sql = "SELECT [field.prs.text] FROM [table.prs] WHERE [field.prs.text]='default'";
			$db->run($sql, $res);
			if (!$res->getFirst()) {
				$sql = "INSERT INTO ".$this->DB->Table('prs')."".
				" ([field.prs.parent],[field.prs.text])".
				" VALUES (0,'default');";
				$db->run($sql, $res);
				$res = null;
			}
		}

		if ($version < 2006111700) {
			// extend the Database with mimetype-row
			$sql = "ALTER TABLE [table.prg] ADD COLUMN [field.prg.mime] VARCHAR(50) NOT NULL DEFAULT 'unknown';";
			$db->run($sql, $res);
			$res = null;
		}

		if ($version < 2008121602) {
			// extend the Database with mimetype-row
			$sql = "ALTER TABLE [table.prg] ADD COLUMN [field.prg.order] INT(11) UNSIGNED DEFAULT 0;";
			$db->run($sql, $res);
			$res = null;
		}

		if ($version < 2010031800) {
			// extend the Database with mimetype-row
			$sql = "ALTER TABLE [table.prs] ADD COLUMN [field.prs.secure] TINYINT(1) UNSIGNED DEFAULT 0;";
			$db->run($sql, $res);
			$res = null;
		}

		if ($version < 2011101800) {
			// Nothing than the VersionNumber which I had forgotten to increase on last update
		}

		// Update the version
		$this->_updateVersionTable($version, $versionid, self::VERSION);
	}

}

?>