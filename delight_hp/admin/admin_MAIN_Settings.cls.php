<?php
abstract class admin_MAIN_Settings extends MainPlugin {
	var $LANG;
	var $DB;
	var $_langId;
	var $_langShort;
	var $_menuId;
	var $_mainAction;
	var $_changeId;
	var $_subChangeId;
	var $_content;
	var $_cssFiles;
	var $_cssContent;
	var $_scriptFiles;
	var $_adminAccess;
	var $_sectionContent;
	var $_sectionDelimiter;
	var $_sectionDelimiterImages;
	var $_userId;
	public $VERSION = 2007122100;

	/**
	 * Initialisation
	 *
	 */
	function __construct() {
		global $DB, $LANG;
		$this->LANG = $LANG;
		$this->DB   = $DB;

		$userCheck = pCheckUserData::getInstance();
		$lang = pMessages::getLanguageInstance();
		$this->_langId = $lang->getLanguageId();
		$this->_langShort = $lang->getShortLanguageName();
		$this->_mainAction = 0;
		$this->_changeId = 0;
		$this->_subChangeId = 0;
		$this->_content = '&nbsp;';
		$this->_cssFiles = array();
		$this->_scriptFiles = array();
		$this->_cssContent = '';
		$this->_adminAccess = (integer)preg_replace("/[^\d]+/", "", get_class($this));
		$this->_sectionContent = '';
		$this->_sectionDelimiter = array('clean','down','entry','last');
		$this->_sectionDelimiterImages = array('empty','entry','expanded');
		$this->_userId = $userCheck->getPerson()->get('userId');
	}

	/**
	 * Return Additional options for TextEditor
	 *
	 * @return string Options like defined in a Template
	 * @access public
	 */
	public function getAdditionalOptions() {

	}

	/**
	 * Create the HTML-Source and return it
	 *
	 * @param string $method A special method-name - here we use it to switch between the complete List and just a list of news
	 * @param array $adm If shown under Admin-Editor, this Array must be a complete DB-likeness Textentry
	 * @param array $glob If included in a Template, this array has keys 'layout','template','num' with equivalent values
	 * @return string
	 * @access public
	 */
	public function getSource($method="", $adminData=array(), $templateTag=array()) {
		$this->createActionBasedContent();
		return $this->_content;
	}

	/**
	 * Each Plugin has to implement this function to set _content
	 * or run any other Ajax-Code...
	 *
	 * @access public
	 * @abstract
	 */
	public abstract function createActionBasedContent();

	protected function getAdminContent($name, $admin='', $uploaded=false, $scope='') {
		$back = '<html><body>Content-File '.$name.' for Administration does not exist...';
		$file = ABS_MAIN_DIR.'editor'.DIRECTORY_SEPARATOR.'admin_editor'.DIRECTORY_SEPARATOR.'contents'.DIRECTORY_SEPARATOR.$name.'.html';
		if (file_exists($file)) {
			$back = file_get_contents($file);
		}

		$dirList = scandir(ABS_FTP_UPLOAD);
		$list = array();
		foreach ($dirList as $file) {
			if (is_file(ABS_FTP_UPLOAD.$file)) {
				array_push($list, utf8_encode($file));
			}
		}
		unset($dirList);
		$back = str_replace("[JS_FTP_FILE_ARRAY]", '["'.implode('","', $list).'"]', $back);

		$back = str_replace('[ADMIN_ACTION]', $admin, $back);
		$back = str_replace('[SCOPE]', $scope, $back);
		$back = str_replace('[UPLOADED]', $uploaded ? 'true' : 'false', $back);

		return $back;
	}


	function _setLanguage(&$lang, $short="de", $id=0) {
		$this->LANG = &$lang;
		$this->_langShort = $short;
		$this->_langId = (integer)$id;
	}
	function _setDatabase(&$db) {
		$this->DB = &$db;
		$this->_checkDatabase();
	}
	function _setAction($act) {
		$this->_mainAction = (integer)($act - (floor($act / 100) * 100));
	}
	function _setSelected($id) {
		if (substr_count($id, ":") <= 0) {
			$this->_changeId = (integer)$id;
		} else {
			$tmp = explode(":", $id);
			$this->_changeId = (integer)$tmp[0];
			$this->_subChangeId = (integer)$tmp[1];
		}
	}
	function _setMenuId($id) {
		$this->_menuId = (integer)$id;
	}

	function _getContent($file='', $subSection="") {
		$match = null;
		$cont = '';
		if (strlen(trim($file)) > 0) {
			$file = ABS_ADMIN_TEMPLATE_DIR.DIRECTORY_SEPARATOR."adm_".$file.".tpl";
			if (is_file($file)) {
				$cont = file_get_contents($file);
			}
		}

		// Check for any optional CSS-Includes
		if (preg_match_all("/(\[STYLE_INCLUDE\])(.*?)(\[\/STYLE_INCLUDE\])/smi", $cont, $match)) {
			for ($x = 0; $x < count($match[0]); $x++) {
				$this->_cssFiles[] = $match[2][$x];
				//$cont = str_replace($match[0][$x], "", $cont);
			}
		}
		$cont = preg_replace("/(\[STYLE_INCLUDE\])(.*?)(\[\/STYLE_INCLUDE\])/smi", "", $cont);

		// Check for any optional CSS-Content
		if (preg_match_all("/(\[STYLE_CONTENT\])(.*?)(\[\/STYLE_CONTENT\])/smi", $cont, $match)) {
			for ($x = 0; $x < count($match); $x++) {
				$this->_cssContent .= "\n".$match[2][$x];
				//$cont = str_replace($match[0][$x], "", $cont);
			}
		}
		$cont = preg_replace("/(\[STYLE_CONTENT\])(.*?)(\[\/STYLE_CONTENT\])/smi", "", $cont);

		// Check for any optional SCRIPT-Includes
		if (preg_match_all("/(\[SCRIPT_INCLUDE\])(.*?)(\[\/SCRIPT_INCLUDE\])/smi", $cont, $match)) {
			for ($x = 0; $x < count($match); $x++) {
				$this->_scriptFiles[] = $match[2][$x];
				//$cont = str_replace($match[0][$x], "", $cont);
			}
		}
		$cont = preg_replace("/(\[SCRIPT_INCLUDE\])(.*?)(\[\/SCRIPT_INCLUDE\])/smi", "", $cont);

		// Strip out the SubSection if available
		if (strlen(trim($subSection)) > 0) {
			if (preg_match("/(\[".strtoupper($subSection)."\])(.*?)(\[\/".strtoupper($subSection)."\])/smi", $cont, $match)) {
				$cont = $match[2];
			} else {
				$cont = "";
			}
		}

		$cont = str_replace("[LAYOUT]", "", $cont);
		$cont = str_replace("[/LAYOUT]", "", $cont);

		// Check for an INCLUDE-Tag
		if (preg_match_all("/(\[INCLUDE:)(.*?)(((:)(.*?))?)(\])/smi", $cont, $match)) {
			for ($i = 0; $i < count($match[0]); $i++) {
				$template = trim($match[2][$i]);
				$section  = trim($match[6][$i]);
				$includeContent = $this->_getContent(str_replace("admin/adm_", "", str_replace(".tpl", "", $template)), $section);
				$cont = str_replace($match[0][$i], trim($includeContent), $cont);
			}
		}

		return $cont;
	}

	function _replaceTextEditOptions($_html) {
		$match = null;
		// Insert the InternalLinkChooser
		$_intMenuChoose = "";
		if (substr_count($_html, "[SUBMENU_ADMIN_CHOOSE]") > 0) {
			if ( !(class_exists("MENU")) ) {
				if (file_exists("./php/class/MENU.cls.php")) {
					require_once("./php/class/MENU.cls.php");
				}
			}
			if (class_exists("MENU")) {
				$MEN = new MENU($this->DB, $this->LANG);
				$MEN->_selected = 0;
				$MEN->_template = 'adm_SubMenu_choose.tpl';
				$MEN->_minParent = 0;
				$MEN->_maxParent = 65535;
				$_intMenuChoose = $MEN->GetSource(true);
				unset($MEN);
			}
		}
		$_html = str_replace("[SUBMENU_ADMIN_CHOOSE]", $_intMenuChoose, $_html);

		// Insert the Layout-Colortags if available
		if (preg_match("/(\[FONT_COLOR\])(.*?)(\[\/FONT_COLOR\])/smi", $_html, $match)) {
			global $TextLayout;
			$_insColor = '';
			$lay = $this->_readTemplateFile($TextLayout);
			unset($lay);

			$_keys = array_keys($this->LayoutColorTags);
			for ($i = 0; $i < count($_keys); $i++) {
				$_tmp = $match[2];
				$_tmp = str_replace("[COLOR_NUMBER]", $_keys[$i], $_tmp);
				$_tmp = str_replace("[COLOR_VALUE]", $this->LayoutColorTags[$_keys[$i]], $_tmp);
				$_insColor .= $_tmp;
			}
			$_html = str_replace($match[0], $_insColor, $_html);
		}

		// Replace the ImageInsertLink if available
		if (preg_match("/(\[EDIT_IMAGELIST_LINK:)(.*?)(\])/smi", $_html, $match)) {
			$_html = str_replace($match[0], "/".$this->_langShort."/".$match[2]."/0/", $_html);
		}
		return $_html;
	}

	// Replace the "text-function" by a Text-Field, defined by $options
	// $options = array('size', 'color', 'paragraph', 'external', 'internal', 'image', 'list', 'hint')
	function _replaceAdminTextOptions($_adminHtml, $options=array()) {
		// Create lowercase Array_entries
		$_opt = array();
		foreach ($options as $k => $v) {
			$_opt[] = strtolower($v);
		}

		// Replace some (perhaps) available xxx_TAG's
		if (!in_array("size", $_opt)) {
			$_adminHtml = preg_replace("/(\[SIZE_TAG\])(.*?)(\[\/SIZE_TAG\])/smi", "", $_adminHtml);
		} else {
			$_adminHtml = str_replace("[SIZE_TAG]", "", str_replace("[/SIZE_TAG]", "", $_adminHtml));
		}

		if (!in_array("color", $_opt)) {
			$_adminHtml = preg_replace("/(\[COLOR_TAG\])(.*?)(\[\/COLOR_TAG\])/smi", "", $_adminHtml);
		} else {
			$_adminHtml = str_replace("[COLOR_TAG]", "", str_replace("[/COLOR_TAG]", "", $_adminHtml));
		}

		if (!in_array("paragraph", $_opt)) {
			$_adminHtml = preg_replace("/(\[PARAGRAPH_TAG\])(.*?)(\[\/PARAGRAPH_TAG\])/smi", "", $_adminHtml);
		} else {
			$_adminHtml = str_replace("[PARAGRAPH_TAG]", "", str_replace("[/PARAGRAPH_TAG]", "", $_adminHtml));
		}

		if (!in_array("external", $_opt)) {
			$_adminHtml = preg_replace("/(\[EXTERNAL_LINK_TAG\])(.*?)(\[\/EXTERNAL_LINK_TAG\])/smi", "", $_adminHtml);
		} else {
			$_adminHtml = str_replace("[EXTERNAL_LINK_TAG]", "", str_replace("[/EXTERNAL_LINK_TAG]", "", $_adminHtml));
		}

		if (!in_array("internal", $_opt)) {
			$_adminHtml = preg_replace("/(\[INTERNAL_LINK_TAG\])(.*?)(\[\/INTERNAL_LINK_TAG\])/smi", "", $_adminHtml);
		} else {
			$_adminHtml = str_replace("[INTERNAL_LINK_TAG]", "", str_replace("[/INTERNAL_LINK_TAG]", "", $_adminHtml));
		}

		if (!in_array("image", $_opt)) {
			$_adminHtml = preg_replace("/(\[IMAGE_TAG\])(.*?)(\[\/IMAGE_TAG\])/smi", "", $_adminHtml);
		} else {
			$_adminHtml = str_replace("[IMAGE_TAG]", "", str_replace("[/IMAGE_TAG]", "", $_adminHtml));
		}

		if (!in_array("list", $_opt)) {
			$_adminHtml = preg_replace("/(\[LIST_TAG\])(.*?)(\[\/LIST_TAG\])/smi", "", $_adminHtml);
		} else {
			$_adminHtml = str_replace("[LIST_TAG]", "", str_replace("[/LIST_TAG]", "", $_adminHtml));
		}

		if (!in_array("hint", $_opt)) {
			$_adminHtml = preg_replace("/(\[HINT_TAG\])(.*?)(\[\/HINT_TAG\])/smi", "", $_adminHtml);
		} else {
			$_adminHtml = str_replace("[HINT_TAG]", "", str_replace("[/HINT_TAG]", "", $_adminHtml));
		}

		// Replace the ImageInsertLink if available
		$_adminHtml = preg_replace("/(\[EDIT_IMAGELIST_LINK:)(.*?)(\])/smi", "/".$this->_langShort."/\\2/0/", $_adminHtml);
		//if (preg_match("/(\[EDIT_IMAGELIST_LINK:)(.*?)(\])/smi", $_adminHtml, $match)) {
		//	$_adminHtml = str_replace($match[0], "/".$langshort."/".$match[2]."/0/", $_adminHtml);
		//}

		// Insert the InternalLinkChooser
		$_intMenuChoose = "";
		if (substr_count($_adminHtml, "[SUBMENU_ADMIN_CHOOSE]") > 0) {
			if ( !(class_exists("MENU")) ) {
				if (file_exists("./php/class/MENU.cls.php")) {
					require_once("./php/class/MENU.cls.php");
				}
			}
			if (class_exists("MENU")) {
				$MEN = new MENU($this->DB, $this->LANG);
				$MEN->_selected = 0;
				$MEN->_template = 'adm_SubMenu_choose.tpl';
				$MEN->_minParent = 0;
				$MEN->_maxParent = 65535;
				$_intMenuChoose = $MEN->GetSource(true);
				unset($MEN);
			}
		}
		$_adminHtml = str_replace("[SUBMENU_ADMIN_CHOOSE]", $_intMenuChoose, $_adminHtml);

		return $_adminHtml;
	}

	/**
	 * If a User has no access to called Adin-Section, call this function to reset the Content
	 *
	 * @access private
	 */
	function showNoAccess() {
		$this->_content = '<div class="error">[LANG_VALUE:error_002]</div><div class="errorMessage">[LANG_VALUE:error_003]</div>';
	}

	/* SECTION-VIEW */
	/**
	 * Parse the sectionAdminTemplate and fill _sectionDelimiter* lists
	 */
	function parseSectionAdminTemplate() {
		// Replace the Section-List
		if (substr_count($this->_content, "[SECTION_LIST]") > 0) {
			// get the SectionContent
			$this->_sectionContent = $this->_getContent('sectionAdmin', 'MAIN_SECTION');

			// Get all Section-Delimiter
			$_tmp = $this->_getContent('sectionAdmin', 'SECTION_DELIMITER_LIST');
			if (preg_match_all("/(\[)(CLEAN|DOWN|ENTRY|LAST)(\])(.*?)(\[\/\\2\])/smi", $_tmp, $_match)) {
				$this->_sectionDelimiter = array();
				for ($i = 0; $i < count($_match[0]); $i++) {
					$this->_sectionDelimiter[strToLower($_match[2][$i])] = $_match[4][$i];
				}
			}

			// get all Section-Delimiter-Images
			$_tmp = $this->_getContent('sectionAdmin', 'SECTION_DELIMITER_IMAGE');
			if (preg_match_all("/(\[)(EMPTY|ENTRY|EXPANDED)(\])(.*?)(\[\/\\2\])/smi", $_tmp, $_match)) {
				$this->_sectionDelimiterImages = array();
				for ($i = 0; $i < count($_match[0]); $i++) {
					$this->_sectionDelimiterImages[strToLower($_match[2][$i])] = $_match[4][$i];
				}
			}
		}
	}

	/**
	 * Create and return a SectionList based on loaded template
	 *
	 * @param int $id ID of first section
	 * @param string $tbl_s Tablename from sections
	 * @param string $tbl_e Table where the entries are stored for section
	 * @param int $admAct Admin-Parameter for links
	 * @param boolean $addTxt if true, then $TextContent wil be added to links
	 * @param string $tbl_txt Tablename from Section-Translations
	 * @param string $fld_name Fieldname in Translation-Table where the Sectionid is in
	 * @param string $fld_lang Fieldname in Translatin-Table where the Language-ID is in
	 * @param string $fld_icon Fieldname where the Icon is in
	 * @return string
	 */
	function createSectionContentList($id, $tbl_s, $tbl_e, $admAct=0, $addTxt=false, $tbl_txt="", $fld_name="", $fld_lang="", $fld_icon="") {
		global $SectionId;
		$match = null;
		$selected = $SectionId;
		if ((integer)$selected <= 0) {
			$selected = $id;
		}

		if (preg_match("/(\[SECTION\])(.*?)(\[\/SECTION\])/smi", $this->_sectionContent, $match)) {
			$_section = $match[2];

			if (substr_count($this->_sectionContent, "[SECTION_CONTENT]") > 0) {
				$html = str_replace($match[0], "", $this->_sectionContent);
			} else {
				$html = str_replace($match[0], "[SECTION_CONTENT]", $this->_sectionContent);
			}

			if (strlen($tbl_txt) > 0) {
				$html = str_replace("[SELECTED_SECTION_NAME]", $this->_getSectionName($selected, $tbl_txt, $fld_name, $fld_lang), $html);
			} else {
				$html = str_replace("[SELECTED_SECTION_NAME]", $this->_getSectionName($selected, $tbl_s, $fld_name, $fld_lang), $html);
			}
			$html = str_replace("[SECTION_CONTENT]", $this->_sub_createSectionContentList($id, '', $selected, $_section, $tbl_s, $tbl_e, $admAct, $addTxt, $tbl_txt, $fld_name, $fld_lang, $fld_icon), $html);
		} else {
			$html = '<span style="color:rgb(250,100,100);font-weight:bold;">Template-failure: <strong>SECTION</strong> tags not found...</span>';
		}
		return $html;
	}

	/**
	 * PRIVATE: Creates section-HTML for all Sections (recursive-call)
	 *
	 * @param integer $id Current Section-ID
	 * @param string $before Content to append the current Section
	 * @param integer $selected ID of selected Section
	 * @param string $template HTML-Template to use for Sections
	 * @param string $tbl_s The Sectiontable
	 * @param string $tbl_e Tablename of Entries for current Section
	 * @param integer $admAct AdminAction to append to Links
	 * @param boolean $addTxt Append TextContent to links or not
	 * @param string $tbl_txt Tablename from Section-Translations
	 * @param string $fld_name SectionID-Fieldname in Translation-Table
	 * @param string $fld_lang LanguageID-Fieldname in Translation-Table
	 * @param string $fld_icon Iconfieldname in Sectiontable
	 * @return unknown
	 */
	function _sub_createSectionContentList($id, $before, $selected, $template, $tbl_s, $tbl_e, $admAct=0, $addTxt=false, $tbl_txt="", $fld_name="", $fld_lang="", $fld_icon="") {
		if ($addTxt) {
			global $TextContent;
		}
		$selectedList = $this->_getSelectedSectionIdList($selected, $tbl_s);
		$html = '';

		// Get the first Section
		if ( (strlen(trim($before)) <= 0) && ($id == 0) ) {
			$sql = "SELECT * FROM ".$this->DB->Table($tbl_s)." WHERE ".$this->DB->Field($tbl_s,'id')." = '".$id."'";
			$res = $this->DB->ReturnQueryResult($sql);
			if ($res) {
				$row = mysql_fetch_assoc($res);
				$html = $template;

				// Get the Number of available Programs in this Section
				$_count = $this->_getNumOfEntriesInSection($row[$this->DB->FieldOnly($tbl_s,'id')], $tbl_e);
				$html = str_replace("[NUMBER_OF_ENTRIES]", $_count, $html);

				// Check if this section is selected
				if ((integer)$selected == (integer)$row[$this->DB->FieldOnly($tbl_s,'id')]) {
					$html = preg_replace("/(\[IF_SELECTED:')(.*?)(':')(.*?)('\])/smi", "\\2", $html);
				} else {
					$html = preg_replace("/(\[IF_SELECTED:')(.*?)(':')(.*?)('\])/smi", "\\4", $html);
				}
				$html = str_replace("[SECTION_DELIMITER]", "", $html);
				//$html = str_replace("[SECTION_NAME]", $row[$this->DB->FieldOnly($tbl_s,'text')], $html);
				if (strlen($tbl_txt) > 0) {
					$html = str_replace("[SECTION_NAME]", $this->_getSectionName($row[$this->DB->FieldOnly($tbl_s,'id')], $tbl_txt, $fld_name, $fld_lang), $html);
				} else {
					$html = str_replace("[SECTION_NAME]", $this->_getSectionName($row[$this->DB->FieldOnly($tbl_s,'id')], $tbl_s, $fld_name, $fld_lang), $html);
				}
				$html = str_replace("[SECTION_NUMBER]", $row[$this->DB->FieldOnly($tbl_s,'id')], $html);
				$html = str_replace("[SECTION_ID]", $row[$this->DB->FieldOnly($tbl_s,'id')], $html);

				// Add the AdminAction
				if ((integer)$admAct != 0) {
					$admLnk = "/".$this->_langShort."/".$this->_menuId."/adm=".$admAct."&sec=".$row[$this->DB->FieldOnly($tbl_s,'id')];
				} else {
					$admLnk = "/".$this->_langShort."/".$this->_menuId."/adm=".$this->_adminAccess."&sec=".$row[$this->DB->FieldOnly($tbl_s,'id')];
				}

				// Add textContent
				if ($addTxt) {
					$admLnk .= "&textContent=".$TextContent;
				}
				$html = str_replace("[SECTION_LINK]", $admLnk, $html);

				// Add the SectionImage
				/*if ( (strlen($fld_icon) > 0) && $this->_checkSectionIconExists($row[$this->DB->FieldOnly($tbl_s,'id')], $tbl_txt, $fld_icon)) {
					$html = str_replace("[SECTION_IMAGE]", $this->_getCategoryImage($row[$this->DB->FieldOnly($tbl_s,'id')], $tbl_txt, $fld_icon), $html);
				} else {
					$html = preg_replace("/(\<img[^\>]+?\[SECTION_IMAGE\].*?\/>)/smi", "", $html);
				}*/
			}
			$this->DB->FreeDatabaseResult($res);
		}

		// Get Parent-Sections all Child-Sections
		$sql = "SELECT * FROM ".$this->DB->Table($tbl_s)." WHERE ".$this->DB->Field($tbl_s,'parent')." = '".$id."'";
		$res = $this->DB->ReturnQueryResult($sql);
		if ($res) {
			$cnt = 1;
			while ($row = mysql_fetch_assoc($res)) {
				$tmp = $template;
				// Create current delimiter
				if ($cnt < mysql_num_rows($res)) {
					$_delim = $before.$this->_sectionDelimiter['entry'];
					$_delim_child = $before.$this->_sectionDelimiter['down'];
				} else {
					$_delim = $before.$this->_sectionDelimiter['last'];
					$_delim_child = $before.$this->_sectionDelimiter['clean'];
				}
				$tmp = str_replace("[SECTION_DELIMITER]", $_delim, $tmp);

				// Get the Number of available Programs in this Section
				$_count = $this->_getNumOfEntriesInSection($row[$this->DB->FieldOnly($tbl_s,'id')], $tbl_e);
				$tmp = str_replace("[NUMBER_OF_ENTRIES]", $_count, $tmp);

				// Check if this Section is selected
				if ((integer)$selected == (integer)$row[$this->DB->FieldOnly($tbl_s,'id')]) {
					$tmp = preg_replace("/(\[IF_SELECTED:')(.*?)(':')(.*?)('\])/smi", "\\2", $tmp);
				} else {
					$tmp = preg_replace("/(\[IF_SELECTED:')(.*?)(':')(.*?)('\])/smi", "\\4", $tmp);
				}

				// Add the Admin-link
				if ((integer)$admAct != 0) {
					$admLnk = "/".$this->_langShort."/".$this->_menuId."/adm=".$admAct."&sec=".$row[$this->DB->FieldOnly($tbl_s,'id')];
				} else {
					$admLnk = "/".$this->_langShort."/".$this->_menuId."/adm=".$this->_adminAccess."&sec=".$row[$this->DB->FieldOnly($tbl_s,'id')];
				}

				// Add TextContent
				if ($addTxt) {
					$admLnk .= "&textContent=".$TextContent;
				}
				$tmp = str_replace("[SECTION_LINK]", $admLnk, $tmp);

				// Append Section-Childs
				$sub = $this->_sub_createSectionContentList($row[$this->DB->FieldOnly($tbl_s,'id')], $_delim_child, $selected, $template, $tbl_s, $tbl_e, $admAct, $addTxt, $tbl_txt, $fld_name, $fld_lang, $fld_icon);

				// Check for a Template-SectionImage
				if (in_array($row[$this->DB->FieldOnly($tbl_s,'id')], $selectedList) && (strlen($sub) > 0)) {
					$_secImage = $this->_sectionDelimiterImages['expanded'];
					$tmp = preg_replace("/(\[IF_SELECTED_ID:')(.*?)(':')(.*?)('\])/smi", "\\2", $tmp);
				} else if (strlen($sub) > 0) {
					$_secImage = $this->_sectionDelimiterImages['entry'];
					$tmp = preg_replace("/(\[IF_SELECTED_ID:')(.*?)(':')(.*?)('\])/smi", "\\4", $tmp);
				} else {
					$_secImage = $this->_sectionDelimiterImages['empty'];
					$tmp = preg_replace("/(\[IF_SELECTED_ID:')(.*?)(':')(.*?)('\])/smi", "\\4", $tmp);
				}

				// Add the SectionImage if available
				/*if ( (strlen($fld_icon) > 0) && $this->_checkSectionIconExists($row[$this->DB->FieldOnly($tbl_s,'id')], $tbl_txt, $fld_icon)) {
					$tmp = str_replace("[SECTION_IMAGE]", $_secImage.$this->_getCategoryImage($row[$this->DB->FieldOnly($tbl_s,'id')], $tbl_txt, $fld_icon), $tmp);
				} else {
					$tmp = str_replace("[SECTION_IMAGE]", $_secImage, $tmp);
				}*/

				// name, replace id and number
				if (strlen($tbl_txt) > 0) {
					$tmp = str_replace("[SECTION_NAME]", $this->_getSectionName($row[$this->DB->FieldOnly($tbl_s,'id')], $tbl_txt, $fld_name, $fld_lang), $tmp);
				} else {
					$tmp = str_replace("[SECTION_NAME]", $this->_getSectionName($row[$this->DB->FieldOnly($tbl_s,'id')], $tbl_s, $fld_name, $fld_lang), $tmp);
				}
				$tmp = str_replace("[SECTION_NUMBER]", $row[$this->DB->FieldOnly($tbl_s,'id')], $tmp);
				$tmp = str_replace("[SECTION_ID]", $row[$this->DB->FieldOnly($tbl_s,'id')], $tmp);

				// Append the Section
				if ( (strlen($sub) > 0) && (preg_match("/(\[SUBSECTION\])(.*?)(\[\/SUBSECTION\])/smi", $tmp, $match))) {
					$match[2] = str_replace("[SECTION_CONTENT]", $sub, $match[2]);
					$tmp = str_replace($match[0], $match[2], $tmp);
				} else {
					$tmp = preg_replace("/(\[SUBSECTION\])(.*?)(\[\/SUBSECTION\])/smi", "", $tmp);
				}
				$html .= $tmp;

				unset($sub);
				unset($tmp);

				// Count up the counter ;-)
				$cnt++;
			}
		}
		return $html;
	}

	/**
	 * Get a List with all SectionID's from beginning to the requested
	 * Needed while checking the visibility of a Section in a tree
	 *
	 * @param integer $id SectionID to get all parents from
	 * @param string $tbl Name of SectionTable
	 * @param array $list [optional] add ParentID's to this List
	 * @return array All Parent SectionID's (added to $list)
	 */
	function _getSelectedSectionIdList($id, $tbl, $list=array()) {
		if (count($list) <= 0) {
			array_push($list, $id);
		}
		$sql = "SELECT ".$this->DB->Field($tbl,'parent')." FROM ".$this->DB->Table($tbl)." WHERE ".$this->DB->Field($tbl, 'id')." = ".$id;
		$res = $this->DB->ReturnQueryResult($sql);
		if ($res) {
			$row = mysql_fetch_assoc($res);
			$parent = (int)$row[$this->DB->FieldOnly($tbl, 'parent')];
			if ($parent > 0) {
				array_push($list, $parent);
				$list = $this->_getSelectedSectionIdList($parent, $tbl, $list);
			}
		}
		return $list;
	}

	/**
	 * Returns a list with all Child-SectionID's
	 *
	 * @param array $list Curent Section-ID's
	 * @param integer $section ID of section to get childs from
	 * @param string $tbl Tablename where sections are stored
	 * @param string $idfld Name of Id-Field
	 * @param string $parentfld Name of Field which holds the Paren-Section
	 * @param boolean $getall get all ChildSections or only one level
	 * @return array
	 */
	protected function _getSectionIdList($list, $section, $tbl, $idfld='id', $parentfld='parent', $getall=true) {
		$list = (array)$list;
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = 'SELECT ['.$tbl.'.'.$idfld.'] FROM [table.'.$tbl.'] WHERE ['.$tbl.'.'.$parentfld.']='.(int)$section.';';
		$db->run($sql, $res);
		if ($res->getFirst()) {
			while ($res->getNext()) {
				$id = $res->{$db->getFieldName($tbl.'.'.$idfld)};
				if (!in_array($id, $list)) {
					$list[] = $id;
					if ($getall) {
						$list = $this->_getSectionIdList($list, $id, $tbl, $idfld, $parentfld, $getall);
					}
				}
			}
		}
		return $list;
	}

	/**
	 * Returns the name of a Section
	 *
	 * @param int $id ID of section to get the name
	 * @param string $tbl Tablename of section-translations
	 * @param string $fld Databasetable-Fieldname in which the SectionID is
	 * @param string $fld_lang Databasetable-Fieldname which includes the Language-ID
	 * @return string
	 */
	function _getSectionName($id, $tbl, $fld='id', $fld_lang='') {
		if (strlen($fld) <= 0) {
			$fld = 'id';
		}
		$sql = "SELECT * FROM ".$this->DB->Table($tbl)." WHERE ".$this->DB->Field($tbl, $fld)." = '".$id."'";
		if (strlen($fld_lang) > 0) {
			$sql .= " AND ".$this->DB->Field($tbl, $fld_lang)." = '".$this->_langId."'";
		}
		$res = $this->DB->ReturnQueryResult($sql);
		if ($res) {
			$row = mysql_fetch_assoc($res);
			$this->DB->FreeDatabaseResult($res);
			return $row[$this->DB->FieldOnly($tbl,'text')];
		} else {
			return "";
		}
	}

	/**
	 * Check if the Icon of a section exists -
	 *
	 * @param integer $id ID from section to check
	 * @param string $tbl Database-Tablename of Sectionimages
	 * @param string $fld Fieldname where the Icon should be in
	 * @param string $fld_sec Fieldname where the SectionID is in
	 * @param string $fld_lang Fieldname where the LanguageID is in
	 * @return boolean
	 */
	/*function _checkSectionIconExists($id, $tbl, $fld, $fld_sec='section', $fld_lang='lang') {
		// Check with a SQL-CASE if the Image is NULL then return '0', if not, return '1'
		$sql  = "SELECT IF(".$this->DB->FieldOnly($tbl, $fld)." IS NULL, 0, 1) AS chk1,";
		$sql .= " IF(LENGTH(".$this->DB->FieldOnly($tbl, $fld).") <= 0, 0, 1) AS chk2";
		$sql .= " FROM ".$this->DB->Table($tbl)." WHERE ".$this->DB->Field($tbl, $fld_sec)." = '".$id."'";
		$sql .= " AND ".$this->DB->Field($tbl, $fld_lang)."='".$this->_langId."'";
		$res = $this->DB->ReturnQueryResult($sql);
		if ($res) {
			$row = mysql_fetch_assoc($res);
			$this->DB->FreeDatabaseResult($res);
			if ( ((int)$row['chk1'] == 0) || ((int)$row['chk2'] == 0) ) {
				$back = false;
			} else {
				$back = true;
			}
		} else {
			$back = false;
		}
		return $back;
	}*/

	/**
	 * Return a Li nkt to an Image from a Seciton
	 *
	 * @param integer $id ID from Section to get the Image from
	 * @param string $tbl Tablename where the Sectionimages are
	 * @param string $fld Fieldname where the Image is in
	 * @param string $fld_sec Fieldname for Section-ID-Field
	 * @param string $fld_lang Fieldname for Language-ID
	 * @return unknown
	 */
	/*function _getCategoryImage($id, $tbl, $fld, $fld_sec='section', $fld_lang='lang') {
		$sql  = "SELECT ".$this->DB->FieldOnly($tbl,'id')." FROM ".$this->DB->Table($tbl)."";
		$sql .= " WHERE ".$this->DB->FieldOnly($tbl, $fld_sec)."='".$id."'";
		$sql .= " AND ".$this->DB->FieldOnly($tbl, $fld_lang)."='".$this->_langId."'";
		$res = $this->DB->ReturnQueryResult($sql);
		if ($res) {
			$row = mysql_fetch_assoc($res);
		} else {
			$row = array($this->DB->FieldOnly($tbl,'id') => "0");
		}
		return "../..".constant('MAIN_DIRECTORY')."/showImage.php?a=".$tbl."&b=".$fld."&c=".$row[$this->DB->FieldOnly($tbl,'id')];
	}*/

	/**
	 * Get all Entries (Id's) from section $id
	 *
	 * @param integer $id SectionID to get the Content from
	 * @param string $tbl Table with Content (field 'section' contains the SectionID)
	 * @return array List with all ContentID's
	 */
	function _getEntriesFromSection($id, $tbl) {
		$sql = "SELECT ".$this->DB->Field($tbl,'id')." FROM ".$this->DB->Table($tbl)." WHERE ".$this->DB->Field($tbl,'section')." = '".$id."'";
		$res = $this->DB->ReturnQueryResult($sql);
		$back = array();
		if ($res) {
			while ($row = mysql_fetch_assoc($res)) {
				$back[] = $row[$this->DB->FieldOnly($tbl,'id')];
			}
		}
		return $back;
	}

	/**
	 * Get the Content from Parent-Section
	 *
	 * @param integer $id SectionID
	 * @param string $tbl Table with Content (field 'section' and 'parent' contains the SectionID)
	 * @return array List with all Content-ID's
	 */
	protected function _getParentSectionData($id, $tbl) {
		$back = array('par'=>null);
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = "SELECT [".$tbl.".parent] FROM [table.".$tbl."] WHERE [".$tbl.".id]=".(int)$id.";";
		$db->run($sql, $res);
		if ($res->getFirst()) {
			$back['par'] = (int)$res->{$db->getFieldName($tbl.'.parent')};
			$sql = "SELECT * FROM [table.".$tbl."] WHERE [".$tbl.".id]=".$back['par'].";";
			$res = null;
			$db->run($sql, $res);
			if ($res->getFirst()) {
				$back['id'] = (int)$res->{$db->getFieldName($tbl.'.id')};
				$back['text'] = (int)$res->{$db->getFieldName($tbl.'.text')};
			}
		}
		$res = null;
		return $back;
	}

	/**
	 * Get a List with all Child-Sections (recursive)
	 *
	 * @param integer $id SectionID to get the Childs from
	 * @param string $tbl SectionTable name
	 * @param boolean $flat true returns a flat list, false returns a multidimensional-Array (structured list)
	 * @param array $data Append all Sectins to this Array - needed in calse of recursive call
	 * @return array All Sections, flat or structured (appended to $data)
	 */
	protected function _getChildSections($id, $tbl, $flat=true, $data=array()) {
		settype($data, "array");
		$list = $this->_getSectionIdList(array(), $id, $tbl, 'id', 'parent', false);
		for ($i = 0; $i < count($list); $i++) {
			if ($flat) {
				array_push($data, $list[$i]);
				$data = $this->_getChildSections($list[$i], $tbl, $flat, $data);
			} else {
				$_tmp = $this->_getChildSections($list[$i], $tbl, $flat, array());
				$data[$list[$i]] = $_tmp;
			}
		}
		return $data;
	}

	/**
	 * Get the number of ContentEntries from a Section
	 *
	 * @param integer $id The SectionID
	 * @param string $tbl Name of Content-Table (field 'section' contains the sectionid)
	 * @return integer Number of Entries
	 */
	function _getNumOfEntriesInSection($id, $tbl) {
		$sql = "SELECT COUNT(".$this->DB->Field($tbl,'id').") AS num FROM ".$this->DB->Table($tbl)." WHERE ".$this->DB->Field($tbl,'section')." = '".$id."'";
		$res = $this->DB->ReturnQueryResult($sql);
		$row = array('num'=>0);
		if ($res) {
			$row = mysql_fetch_assoc($res);
		}
		return (integer)$row['num'];
	}

	/**
	 * Change the Name of a Section
	 *
	 * @param integer $id ID of Section to change
	 * @param string $text New Section-Name
	 * @param string $tbl Section-Table
	 * @return boolean true if succes, false if failure
	 */
	function _changeSectionName($id, $text, $tbl) {
		$sql = "UPDATE ".$this->DB->Table($tbl)."";
		$sql .= " SET ".$this->DB->FieldOnly($tbl,'text')." = '".trim(urldecode($text))."'";
		$sql .= " WHERE ".$this->DB->Field($tbl,'id')." = '".$id."'";
		$res = $this->DB->ReturnQueryResult($sql);
		if (mysql_affected_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Insert a new Section
	 *
	 * @param integer $id Parent Section ID
	 * @param string $text Name of new Section
	 * @param string $tbl Name of SectionTable
	 * @return boolean true if success, false if failure
	 */
	function _insertChildSection($id, $text, $tbl) {
		$sql = "INSERT INTO ".$this->DB->Table($tbl)."";
		$sql .= " SET ".$this->DB->FieldOnly($tbl,'text')." = '".trim(urldecode($text))."'";
		$sql .= ", ".$this->DB->FieldOnly($tbl,'parent')." = '".(integer)$id."'";
		$res = $this->DB->ReturnQueryResult($sql);
		if (mysql_affected_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Create HTML-Code for an extendable Section-List
	 * mostly used in AJAX-Content
	 *
	 * @param string $content content before this section
	 * @param int $current Id of current Section
	 * @param int $level level of this section
	 * @param array $list List with open sections
	 * @param string $tbl short name of the section table (ims, nes, ...)
	 * @return string the Extendable SectionList
	 */
	protected function _createAjaxSectionList($content='', $current=0, $level=0, $list=array(), $tbl='') {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$res1 = null;
		$back = '';

		$sql = 'SELECT * FROM [table.'.$tbl.'] WHERE ['.$tbl.'.parent]='.(int)$current.';';
		$db->run($sql, $res);
		if ($res->getFirst()) {
			$_idField = $db->getFieldName($tbl.'.id');
			$_textField = $db->getFieldName($tbl.'.text');
			$back .= '<ul class="sectionlist">';
			while ($res->getNext()) {
				// add the base
				$js = 'onclick="'.$tbl.'DelightChangeSection(this);" onmouseover="'.$tbl.'DelightCursor(this,true);" onmouseout="'.$tbl.'DelightCursor(this,false);"';
				$back .= '<li>';

				// Check for Subsections
				$sql1 = 'SELECT ['.$tbl.'.id] FROM [table.'.$tbl.'] WHERE ['.$tbl.'.parent]='.(int)$res->{$_idField}.' LIMIT 0,1;';
				$db->run($sql1, $res1);
				if ($res1->getFirst()) {
					// Add plus-image
					$back .= '<img src="/delight_hp/editor/admin_editor/css/section_';
					if (in_array($res->{$_idField}, $list)) {
						$back .= 'collapse';
					} else {
						$back .= 'expand';
					}
					$back .= '.gif" alt="+" onclick="'.$tbl.'DelightExpandSection(this.parentNode);" class="expand" />';
					$back .= '<img class="folder" alt="Folder" src="/delight_hp/editor/admin_editor/css/folder_close.png" />';
					$back .= '<span class="sectionlist" id="'.$tbl.(int)$res->{$_idField}.'" '.$js.'>'.htmlentities($res->{$_textField}).'</span>';
					$level++;
					$back .= $this->_createAjaxSectionList('', $res->{$_idField}, $level, $list, $tbl);
					$level--;
				} else {
					// Add none-image
					//$back .= '<img src="images/section_none.gif" alt=" " />&nbsp;';
					$back .= '<img src="/delight_hp/editor/admin_editor/css/section_none.gif" alt="." class="expand" />';
					$back .= '<img class="folder" alt="Folder" src="/delight_hp/editor/admin_editor/css/folder_close.png" />';
					$back .= '<span class="sectionlist" id="'.$tbl.(int)$res->{$_idField}.'" '.$js.'>'.htmlentities($res->{$_textField}).'</span>';
				}
				$res1 = null;
				$back .= '</li>';
			}
			$res = null;
			$back .= '</ul>';
		}

		return $content.$back;
	}

	/**
	 * Returns a Sectionlist for a JSON-Request
	 *
	 * @param integer $parent The first parent from which all sections should be returned
	 * @param string $sectionTable Tablename where sections are stored (3 chars)
	 * @param string $textTable Optional, Tablename where names from sections are stored (3 chars)
	 * @return string JSON-String to return
	 */
	protected function _getJSONSectionList($parent, $sectionTable, $textTable='') {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$secure = $db->getFieldName($sectionTable.'.secure');
		$secureField = '';
		if (!empty($secure)) {
			$secureField = ','.$secure;
		}
		if (empty($textTable)) {
			$textTable = $sectionTable;
		}
		if ($sectionTable != $textTable) {
			$sql = 'SELECT ['.$sectionTable.'.id],['.$textTable.'.name]'.$secureField.' FROM [table.'.$sectionTable.'],[table.'.$textTable.'] WHERE ['.$sectionTable.'.parent]='.$parent.' AND ['.$sectionTable.'.id]=['.$textTable.'.section];';
		} else {
			$sql = 'SELECT ['.$sectionTable.'.id],['.$sectionTable.'.text]'.$secureField.' FROM [table.'.$sectionTable.'] WHERE ['.$sectionTable.'.parent]='.$parent.';';
		}
		$db->run($sql, $res);

		$back = '[';
		if ($res->getFirst()) {
			while ($res->getNext()) {
				$name = str_replace('\'', '\\\'', $res->{$db->getFieldName('['.$textTable.'.text]')});
				$id = $res->{$db->getFieldName('['.$sectionTable.'.id]')};
				if (strlen($back) > 2) {
					$back .= ',';
				}
				$back .= '{';
				$back .= '"name":"'.$name.'",';
				$back .= '"id":'.$id.',';
				if (!empty($secure)) {
					$s = $res->{$secure};
					$back .= '"secure":'.((int)$s > 0 ? 'true' : 'false').',';
				}
				$back .= '"sub":'.$this->_getJSONSectionList($id, $sectionTable, $textTable);
				$back .= '}';
			}
		}
		$res = null;
		$back .= ']';
		return $back;
	}

	/**
	 * Return a SectionList as an Object
	 * @param int $parent ID of the Parent-Section, initially "0"
	 * @param string $sectionTable Tablename from Configuration where the SecitonStructure is stored
	 * @param string $textTable Tablename from Configuration where names from Section are in
	 * @param int $sel Selected Section
	 * @return array
	 */
	protected function _getSectionList($parent, $sectionTable, $textTable='', $sel=0) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		if (empty($textTable)) {
			$textTable = $sectionTable;
		}
		if ($sectionTable != $textTable) {
			$sql = "SELECT [".$sectionTable.".id],[".$textTable.".name] FROM [table.".$sectionTable."],[table.".$textTable."] WHERE [".$sectionTable.".parent]=".$parent." AND [".$sectionTable.".id]=[".$textTable.".section];";
		} else {
			$sql = "SELECT [".$sectionTable.".id],[".$sectionTable.".text] FROM [table.".$sectionTable."] WHERE [".$sectionTable.".parent]=".$parent.";";
		}
		$db->run($sql, $res);
		$back = array();
		if ($res->getFirst()) {
			while ($res->getNext()) {
				$name = str_replace('\'', '\\\'', $res->{$db->getFieldName('['.$textTable.'.text]')});
				$id = $res->{$db->getFieldName('['.$sectionTable.'.id]')};
				$obj = new stdClass();
				$obj->name = $name;
				$obj->id = $id;
				$obj->selected = ($id == $sel);
				$obj->childSelected = false;
				$obj->childs = $this->_getSectionList($id, $sectionTable, $textTable, $sel);
				foreach ($obj->childs as $c) {
					if ($c->selected || $c->childSelected) {
						$obj->childSelected = true;
						break;
					}
				}
				$back[] = $obj;
			}
		}
		$res = null;
		return $back;
	}

	/**
	 * Return all Languages as a JSON-List
	 * @param boolean $asSectionList if true, an empty Property "sub" is added
	 * @param boolean $enabled Only get enabled languages
	 * @return JSON-String
	 */
	protected function _getJSONLanguageList($asSectionList=false, $enabled=false) {
		$back = '[]';
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;

		$sql = "SELECT * FROM [table.lan]";
		if ($enabled) {
			$sql .= " WHERE [lan.active]=1";
		}
		$sql .= " ORDER BY [lan.text] ASC;";
		$db->run($sql, $res);
		if ($res->getFirst()) {
			$back = '[';
			while ($res->getNext()) {
				$name = str_replace('\'', '\\\'', $res->{$db->getFieldName('lan.text')});
				if (strlen($back) > 2) {
					$back .= ',';
				}
				$back .= '{"name":"'.$name.'",';
				$back .= '"id":'.$res->{$db->getFieldName('lan.id')}.',';
				$back .= '"short":"'.$res->{$db->getFieldName('lan.short')}.'",';
				$back .= '"icon":"'.$res->{$db->getFieldName('lan.icon')}.'",';
				$back .= '"charset":"'.$res->{$db->getFieldName('lan.char')}.'",';
				if ($asSectionList) {
					$back .= '"sub":[]';
				}
				$back .= '}';
			}
			$back .= ']';
		}
		$res = null;
		return $back;
	}

	/**
	 * Change a Section
	 *
	 * @param integer $id SectionID to Change - For a new section a value lower than '0'
	 * @param string $name The Name for the section
	 * @param integer $parent ID from Parent Section
	 * @return integer The SectionID
	 */
	protected function _changeSectionParameters($id, $name, $parent, $sectiontable) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		if ($id <= 0) {
			$db->run("INSERT INTO [table.".$sectiontable."] ([field.".$sectiontable.".parent],[field.".$sectiontable.".text]) VALUES (".$parent.",'".$name."')", $res);
			$newid = $res->getInsertId();
		} else {
			$db->run("UPDATE [table.".$sectiontable."] SET ".($parent!=0 ? "[field.".$sectiontable.".parent]=".$parent.",":"")." [field.".$sectiontable.".text]='".$name."' WHERE [field.".$sectiontable.".id]=".$id.";", $res);
			$newid = $id;
		}
		unset($res);
		return $newid;
	}

	protected function getJSONfromObject($obj) {
		$back = '';
		$isobject = (gettype($obj) == 'object');
		$isarray  = (gettype($obj) == 'array');
		if ($isobject) {
			$vars = get_object_vars($obj);
		} else if (!$isarray) {
			$vars = array();
		}
		foreach ($vars as $k => $v) {
			$back .= (strlen($back) > 0) ? ',' : '';

			$type = gettype($v);
			switch ($type) {
				case 'object':
					$val = $this->getJSONfromObject($v);
					break;
				case 'array':
					break;
				case 'string':
					$val = '"'.$this->_escapeJSONString($v).'"';
					break;
				case 'boolean':
					if ($val) {
						$val = 'true';
					} else {
						$val = 'true';
					}
					break;
				case 'integer':
					$val = $v;
					break;
				case 'double':
				case 'float':
					$val = round($v, 4);
					break;
				default:
					continue;
					break;
			}
			$back .= '"'.$k.'":'.$val;
		}
		return '{'.$back.'}';
	}

	protected function _escapeJSONString($str, $utf8_encode=false) {
		$str = str_replace('\'', '\\\'', $str);
		$str = str_replace('"', '\\"', $str);
		$str = preg_replace("/[\r|\n]+/smi", "\\n", $str);
		if ($utf8_encode) {
			return utf8_encode($str);
		}
		return $str;
	}

	protected function storeObjectTextData($id, $title, $content, $table, $field) {
		$messages = pMessages::getLanguageInstance();
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = "SELECT [".$table.".id] FROM [table.".$table."] WHERE [".$table.".".$field."]=".$id." AND [".$table.".lang]=".$messages->getLanguageId().";";
		$db->run($sql, $res);
		if ($res->getFirst()) {
			$sql = "UPDATE [table.".$table."] SET [field.".$table.".title]='".mysql_real_escape_string(trim($title))."', [field.".$table.".text]='".mysql_real_escape_string($content)."' WHERE [field.".$table.".id]=".$res->{$db->getFieldName($table.'.id')}.";";
		} else {
			$sql = "INSERT INTO [table.".$table."] ([field.".$table.".".$field."],[field.".$table.".title],[field.".$table.".text],[field.".$table.".lang]) VALUES(".$id.",'".mysql_real_escape_string(trim($title))."','".mysql_real_escape_string($content)."',".$messages->getLanguageId().");";
		}
		$res = null;
		$db->run($sql, $res);
		$res = null;
	}

	/**
	 * Get all Options from a given Object which has a Method "getOptionalOptions()"
	 * @param string $name Name of the Object to get Options from
	 * @return array[stdClass]
	 */
	protected function getPluginOptions($name) {
		$options = '';
		try {
			$obj = new $name();
			if ($obj instanceof MainPlugin) {
				$options = $obj->getAdditionalOptions();
			}
			$options = str_replace('[OPTIONS]', '', str_replace('[/OPTIONS]', '', $options));
		} catch (Exception $e) {
			$options = '';
		}
		return $this->parseOptions($options);
	}

	/**
	 * Parse Options-String
	 * [option_name]''|(edit|choose):values/options[/option_name]
	 * @param string $name Name of the Object to get Options from
	 * @return array[stdClass]
	 */
	protected function parseOptions($options) {
		$opt = array();
		$match = array();
		if (preg_match_all('/\[([a-z]+)_([a-z_]+)\](.*?)\[\/\\1_\\2\]/smi', $options, $match, PREG_SET_ORDER)) {
			foreach ($match as $m) {
				$o = new stdClass();
				$o->name = $m[1].'_'.$m[2];
				$o->value = $m[3];

				// Check for Value-Type
				$tmp = explode(':', $m[3]);
				$o->type = array_shift($tmp);
				if ($o->type == 'choose') {
					$o->value = explode(',', implode(':', $tmp));

				} else if ($o->type == 'edit') {
					$o->type = array_shift($tmp);
					$o->value = implode(':', $tmp);

				} else {
					$o->type = 'choose';
					$o->name = $m[1];
					$o->value = array($m[2].':'.$m[3]);
				}

				if (!array_key_exists($o->name, $opt)) {
					$opt[$o->name] = $o;
				} else {
					$opt[$o->name]->value[] = $o->value[0];
				}
			}
		}

		return $opt;
	}

	/**
	 * Get all Layouts as an ObjectList
	 * @return array[stdClass]
	 */
	protected function getLayoutList() {
		$lang = pMessages::getLanguageInstance();
		$list = array();

		foreach (scandir(ABS_TEMPLATE_DIR) as $file) {
			if (substr($file, 0, 4) == 'lay_') {
				$content = file_get_contents(ABS_TEMPLATE_DIR.$file);
				$obj = new stdClass();
				$obj->name = trim(str_replace('lay_', '', str_replace('.tpl', '', $file)));

				// Get the Description
				$match = array();
				$obj->description = $obj->name;
				if (preg_match('/\[DESCR:'.$lang->getLanguageName().'\](.*?)\[\/DESCR\]/smi', $content, $match)) {
					$obj->description = trim($match[1]);
				}

				// Get Style-Includes
				$match = array();
				$obj->style = '';
				if (preg_match('/\[STYLE_INCLUDE\](.*?)\[\/STYLE_INCLUDE\]/smi', $content, $match)) {
					$obj->style = TEMPLATE_DIR.trim($match[1]);
				}

				// Get Style-Content
				$match = array();
				$obj->style_content = '';
				if (preg_match('/\[STYLE_CONTENT\](.*?)\[\/STYLE_CONTENT\]/smi', $content, $match)) {
					$obj->style_content = trim($match[1]);
				}

				// Get Content
				$match = array();
				$obj->content = '';
				if (preg_match('/\[LAYOUT\](.*?)\[\/LAYOUT\]/smi', $content, $match)) {
					$obj->content = trim($match[1]);
				}

				// Get Options
				$match = array();
				$obj->options = array();
				if (preg_match('/\[OPTIONS\](.*?)\[\/OPTIONS\]/smi', $content, $match)) {
					$obj->options = $this->parseOptions($match[1]);
				}

				// Create a Preview
				$match = array();
				$obj->preview = $obj->content;
				$obj->preview = str_replace('[ADMINID]', '', $obj->preview);
				$obj->preview = str_replace('[CAT_TITLE]', '', $obj->preview);
				$obj->preview = str_replace('[/CAT_TITLE]', '', $obj->preview);
				$obj->preview = str_replace('[CAT_CONTENT]', '', $obj->preview);
				$obj->preview = str_replace('[/CAT_CONTENT]', '', $obj->preview);
				$obj->preview = preg_replace('/\[ADMIN_REMOVE\](.*?)\[\/ADMIN_REMOVE\]/smi', '', $obj->preview);
				$obj->preview = str_replace('[TITLE]', $obj->name, $obj->preview);
				if (is_file(ABS_TEMPLATE_DIR.'txtPreview.txt')) {
					$obj->preview = str_replace('[TEXT]', '<p>'.str_replace(chr(10), '</p><p>', trim(file_get_contents(ABS_TEMPLATE_DIR.'txtPreview.txt'))).'</p>', $obj->preview);
				} else {
					$obj->preview = str_replace('[TEXT]', str_repeat( '<p>'.str_repeat('Sample Text ', rand(10,20)).'</p>', 2), $obj->preview);
				}
				if (preg_match_all('/(\[OPTION:)(.*?)(,)(.*?)(\])/smi', $obj->preview, $match, PREG_SET_ORDER)) {
					foreach ($match as $m) {
						$replaced = false;
						if (array_key_exists($m[2], $obj->options)) {
							foreach ($obj->options[$m[2]]->value as $o) {
								$o = explode(':', $o);
								if ($o[0] == $m[4]) {
									$obj->preview = str_replace($m[0], $o[1], $obj->preview);
									$replaced = true;
									break;
								}
							}
						}
						if (!$replaced) {
							$obj->preview = str_replace($m[0], '-', $obj->preview);
						}
					}
				}
				$obj->preview = preg_replace('/(\t|\n|\r)+/smi', '', $obj->preview);

				$list[$obj->name] = $obj;
			}
		}

		return $list;
	}

	/**
	 * Get the SectionID from specified Table where $_img is in spesified field
	 *
	 * @param String $search The String to search in $fld
	 * @param String $tbl Table to search in
	 * @param String $fld field to search on
	 * @return int the SectionID or 0 if none was found
	 */
	function _getSectionFromContent($search, $tbl, $fld) {
		$section = 0;
		// Get the section from current image
		if (substr_count($search, "/") > 0) {
			$search = substr($search, strrpos($search, "/")+1);
			$sql = "SELECT ".$this->DB->Field($tbl,'section')." FROM ".$this->DB->Table($tbl)." WHERE ".$this->DB->Field($tbl,$fld)."='".$search."';";
			$res = $this->DB->ReturnQueryResult($sql);
			if ($res) {
				$row = mysql_fetch_assoc($res);
				$section = (int)$row[$this->DB->FieldOnly($tbl,'section')];
			}
			$this->DB->FreeDatabaseResult($res);
		}
		return $section;
	}

	/**
	 * this function checks the Database for tables, entries and other things needed by current class
	 *
	 */
	protected function _checkDatabase($classVersion) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;

		// Get the current Version
		$v = $this->_checkMainDatabase();
		$version = $v[0];
		$versionid = $v[1];

		// Check current Plugin for updates
		if (method_exists($this, '_checkDatabaseUpdates')) {
			$this->_checkDatabaseUpdates($version);
		}

		$this->_updateVersionTable($version, $versionid, $classVersion);
	}

}
?>