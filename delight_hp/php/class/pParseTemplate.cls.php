<?php
require_once("config/html_headers.inc.php");

class pParseTemplate {
	private $type;
	private $file;
	private $fileData;
	private $templateHtml;
	private $ERROR;

	public $CreateStaticFiles;
	public $StaticOffset;

	public function __construct($static=false) {
		$this->CreateStaticFiles = $static;
		$this->StaticOffset = 0;
	}

	public function setTemplate($file="",$type="") {
		if (empty($file)) {
			$file = "base";
		}
		$this->checkType($type);
		$this->readTemplateFile($file);
	}

	public function getTemplateHtml() {
		return $this->templateHtml;
	}

	private function checkType($type="") {
		switch (strtolower($type)) {
			case 'text':  $this->type = "TEXT";     break;
			case 'subm':  $this->type = "SUBMENU";  break;
			case 'mainm': $this->type = "MAINMENU"; break;
			case 'page':  $this->type = "SITE";     break;
			case 'frame': $this->type = "FRAMESET"; break;
			default:      $this->type = "SITE";     break;
		}
	}

	private function readTemplateFile($file="") {
		$this->fileData = "";
		$this->ERROR    = "";
		$file = str_replace("//", "/", ABS_TEMPLATE_DIR."/".$file.".html");
		if (file_exists($file)) {
			$this->fileData = file_get_contents($file);
		} else {
			$lang = pMessages::getLanguageInstance();
			$this->ERROR = $lang->getValue('', 'text', 'error_001');
		}
	}

	public function parseTemplate() {
		$userCheck = pCheckUserData::getInstance();
		$lang = pMessages::getLanguageInstance()->getLanguage();
		$menu = pMenu::getMenuInstance();
		$entry = $menu->getMenuEntry();

		$pageTitle       = $entry->title;
		$pageDescription = $entry->description;
		$pageKeywords    = $entry->keywords;

		if (empty($pageTitle)) {
			$pageTitle = defined('DHP_NAME') ? DHP_NAME : 'delight cms';
		}
		if (empty($pageDescription)) {
			$pageDescription = defined('DHP_DESCRIPTION') ? DHP_DESCRIPTION : 'delight cms';
		}
		if (empty($pageKeywords)) {
			$pageKeywords = defined('DHP_KEYWORDS') ? DHP_KEYWORDS : 'delight cms';
		}

		$this->templateHtml = "";
		if (strlen($this->ERROR) <= 0) {
			$OBJ = new $this->type();
			$OBJ->CreateStaticFiles = $this->CreateStaticFiles;
			$HTML = $OBJ->doParseData($this->fileData);
			unset($OBJ);

			if ($userCheck->checkLogin() && (!$this->CreateStaticFiles)) {
				$editorLang = $lang->short;
				if (!in_array($editorLang, explode(',', WORKING_EDITOR_LANGUAGE))) {
					$editorLang = DEFAULT_EDITOR_LANGUAGE;
				}

				$scripts = '';
				$scripts .= '<script type="text/javascript">'.chr(10);
				$scripts .= 'var ADMIN_LINK_MENU_CREATE = \'/'.$lang->short.'/'.$menu->getShortMenuName().'/adm='.ADM_MENU_CREATE.'&menu=\';'.chr(10);
				$scripts .= 'var ADMIN_LINK_MENU_EDIT = \'/'.$lang->short.'/'.$menu->getShortMenuName().'/adm='.ADM_MENU_EDIT.'&menu=\';'.chr(10);
				$scripts .= 'var ADMIN_LINK_MENU_DELETE = \'/'.$lang->short.'/'.$menu->getShortMenuName().'/adm='.ADM_MENU_DELETE.'&menu=\';'.chr(10);
				$scripts .= 'var ADMIN_LINK_MENU_MOVEUP = \'/'.$lang->short.'/'.$menu->getShortMenuName().'/adm='.ADM_MENU_MVUP.'&menu=\';'.chr(10);
				$scripts .= 'var ADMIN_LINK_MENU_MOVEDOWN = \'/'.$lang->short.'/'.$menu->getShortMenuName().'/adm='.ADM_MENU_MVDOWN.'&menu=\';'.chr(10);
				$scripts .= 'var ADMIN_LINK_MENU_VISIBILITY = \'/'.$lang->short.'/'.$menu->getShortMenuName().'/adm='.ADM_MENU_VISIBILITY.'&menu=\';'.chr(10);
				$scripts .= 'var ADMIN_LINK_MENU_LINK = \'/'.$lang->short.'/'.$menu->getShortMenuName().'/adm='.ADM_MENU_LINK.'&menu=\';'.chr(10);
				$scripts .= 'var ADMIN_LINK_DELETE = \'/'.$lang->short.'/'.$menu->getShortMenuName().'/adm='.ADM_DELETE.'&i=\';'.chr(10);
				$scripts .= '</script>'.chr(10);

				$scripts .= '<script type="text/javascript">var dedtEditorLanguage_Short="'.$lang->short.'",dedtEditorLanguage="'.$editorLang.'",dedtEditorLanguage_Extended="'.$lang->name.'",editorContentCSS="'.TEMPLATE_DIR.'css_editor.css";</script>'.chr(10);
				$scripts .= '<script type="text/javascript" src="[DATA_DIR]../editor/prototype/prototype-release.js" charset="utf-8"></script>'.chr(10);
				//$scripts .= '<script type="text/javascript" src="[DATA_DIR]../editor/prototype/prototype_update_helper.js" charset="utf-8"></script>'.chr(10);
				$scripts .= '<script type="text/javascript" src="[DATA_DIR]../editor/prototype/fastinit.js" charset="utf-8"></script>'.chr(10);
				$scripts .= '<script type="text/javascript" src="[DATA_DIR]../editor/scriptaculous/builder.js" charset="utf-8"></script>'.chr(10);
				$scripts .= '<script type="text/javascript" src="[DATA_DIR]../editor/scriptaculous/effects.js" charset="utf-8"></script>'.chr(10);
				$scripts .= '<script type="text/javascript" src="[DATA_DIR]../editor/scriptaculous/dragdrop.js" charset="utf-8"></script>'.chr(10);
				$scripts .= '<script type="text/javascript" src="[DATA_DIR]../editor/tiny_mce/tiny_mce'.((!defined('JS_SRC_MODE') || JS_SRC_MODE) ? '_src' : '').'.js"></script>'.chr(10);
				//$scripts .= '<script type="text/javascript" src="[DATA_DIR]../editor/tiny_mce/tiny_mce_prototype'.((!defined('JS_SRC_MODE') || JS_SRC_MODE) ? '_src' : '').'.js"></script>'.chr(10);
				//$scripts .= '<script type="text/javascript" src="[DATA_DIR]../editor/tiny_mce/tiny_mce_gzip.php"></script>'.chr(10);
				$scripts .= '<script type="text/javascript" src="[DATA_DIR]../data/admin_functions'.((!defined('JS_SRC_MODE') || JS_SRC_MODE) ? '_src' : '').'.js" charset="utf-8"></script>'.chr(10);

				// AdminEditor
				$scripts .= '<div style="visibility:hidden;height:1px;overflow:hidden;position:absolute;top:50px;">'.chr(10);
				$scripts .= '<form action="#" method="post" onsubmit="return false;" id="form_dedt_MainContent">'.chr(10);
				$scripts .= '<fieldset style="display:none;">'.chr(10);
				$scripts .= '<input type="hidden" name="title_dedt_MainContent" id="title_dedt_MainContent" value="" />'.chr(10);
				$scripts .= '<input type="hidden" name="layout_dedt_MainContent" id="layout_dedt_MainContent" value="" />'.chr(10);
				$scripts .= '<input type="hidden" name="options_dedt_MainContent" id="options_dedt_MainContent" value="" />'.chr(10);
				$scripts .= '<input type="hidden" name="id_dedt_MainContent" id="id_dedt_MainContent" value="" />'.chr(10);
				$scripts .= '<input type="hidden" name="dedtid_dedt_MainContent" id="dedtid_dedt_MainContent" value="0" />'.chr(10);
				$scripts .= '</fieldset>'.chr(10);
				$scripts .= '<div id="dedt_MainContent">&nbsp;</div>'.chr(10);
				$scripts .= '</form>'.chr(10);
				$scripts .= '</div>'.chr(10);

				$HTML = preg_replace("/(\[ADMIN_REMOVE\])(.*?)(\[\/ADMIN_REMOVE\])/smi", "\\2", $HTML);
				$HTML = str_replace("[ADMIN_EDITOR_SCRIPTS]", $scripts, $HTML);
			} else {
				$HTML = preg_replace("/(\[ADMIN_REMOVE\])(.*?)(\[\/ADMIN_REMOVE\])/smi", "", $HTML);
				$HTML = str_replace("[ADMIN_EDITOR_SCRIPTS]", "", $HTML);
			}
		} else {
			$HTML = '';
			if (!($this->CreateStaticFiles)) {
				$HTML = '[HEADER][/HEADER]';
			}
			$HTML .= '
				<body>
					<span class="error">'.$this->ERROR.'</span>
				</body>';
		}
		$this->templateHtml = preg_replace("/(\[GLOBAL_ADMIN_FUNCTIONS\])/i", "", $HTML);

		// Replace the Header and the Footer
		if (!($this->CreateStaticFiles)) { // TODO: Is this needed anymore?
			$this->replaceHeader();
		}

		// replace PHPIDS if available
		if (defined('PHPIDS_MESSAGE')) {
			$this->templateHtml = str_replace('[PHPIDS]', PHPIDS_MESSAGE, $this->templateHtml);
		} else {
			$this->templateHtml = str_replace('[PHPIDS]', '', $this->templateHtml);
		}

		// Replace all "&xxx;=" encoded special-chars with "&amp;xxx;="
		$this->replaceAmpersand();

		// Replace Language-Parameters and Date-Parameters
		$this->templateHtml = str_replace('[PAGE_BASE]', MAIN_DIRECTORY, $this->templateHtml);
		$this->templateHtml = str_replace('[SITE_BASE]', MAIN_DIRECTORY, $this->templateHtml);

		$this->templateHtml = str_replace('[SHORT]', $lang->short, $this->templateHtml);
		$this->templateHtml = str_replace('[CHARSET]', $lang->charset,  $this->templateHtml);
		$this->templateHtml = str_replace('[LANGUAGE]', $lang->extendedLanguage,  $this->templateHtml);
		$this->templateHtml = str_replace('[CHANGE_DATE]', date("Y-m-d"), $this->templateHtml);

		$this->templateHtml = str_replace('[LANG_SHORT]', $lang->short, $this->templateHtml);
		$this->templateHtml = str_replace('[YEAR_NOW]', date('Y'), $this->templateHtml);
		$this->templateHtml = str_replace('[MONTH_NOW]', date('m'), $this->templateHtml);
		$this->templateHtml = str_replace('[DAY_NOW]', date('d'), $this->templateHtml);
		$this->templateHtml = str_replace('[HOSTNAME]', $_SERVER['HTTP_HOST'], $this->templateHtml);
		$this->templateHtml = str_replace('[PAGE_LINK]', 'http://'.$_SERVER['HTTP_HOST'].'/'.$lang->short.'/'.$menu->getShortMenuName(), $this->templateHtml);
		$this->templateHtml = str_replace('[SITE_LINK]', 'http://'.$_SERVER['HTTP_HOST'].'/'.$lang->short.'/'.$menu->getShortMenuName(), $this->templateHtml);
		$this->templateHtml = str_replace('[RELATIVE_LINK]', '/'.$lang->short.'/'.$menu->getShortMenuName(), $this->templateHtml);

		$this->templateHtml = str_replace('[PAGE_TITLE]', $pageTitle, $this->templateHtml);
		$this->templateHtml = str_replace('[SITE_TITLE]', $pageTitle, $this->templateHtml);
		$this->templateHtml = str_replace('[PAGE_DESCRIPTION]', $pageDescription, $this->templateHtml);
		$this->templateHtml = str_replace('[SITE_DESCRIPTION]', $pageDescription, $this->templateHtml);
		$this->templateHtml = str_replace('[PAGE_KEYWORDS]', $pageKeywords, $this->templateHtml);
		$this->templateHtml = str_replace('[SITE_KEYWORDS]', $pageKeywords, $this->templateHtml);
	}

	private function replaceHeader() {
		$this->replaceHeaderBegin();
		$this->replaceHeaderEnd();
	}

	private function replaceHeaderBegin() {
		global $HtmlBody;
		$this->templateHtml = str_replace("[HEADER]", $HtmlBody, $this->templateHtml);
	}

	private function replaceHeaderEnd() {
		global $HtmlBodyEND;
		$this->templateHtml = str_replace("[/HEADER]", $HtmlBodyEND, $this->templateHtml);
	}

	private function replaceAmpersand() {
		$this->templateHtml = preg_replace("/(&)([a-zA-Z0-9_]+)(=)/smi", "\\1amp;\\2\\3", $this->templateHtml);
	}

}

?>
