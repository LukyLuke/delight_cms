<?php

// Include the Admin-Plugins if user has Access
$userCheck = pCheckUserData::getInstance();
if ( ($userCheck->checkLogin() && (pURIParameters::get('adm', 0, pURIParameters::$INT) > 99)) || pURIParameters::get('callDoCreateStaticSites', false, pURIParameters::$BOOLEAN) ) {
	require_once("./admin/admin_MAIN_Settings.cls.php");
	if (($od = opendir(dirname($_SERVER['SCRIPT_FILENAME'])."/admin/")) !== false) {
		while (($file = readdir($od)) !== false) {
			if (substr($file, 0, strlen("admin_")) == "admin_") {
				require_once("./admin/".$file);
			}
		}
	}
	@closedir($od);
}

class SITE extends MainPlugin {
	const VERSION = 2006060700;

	protected $parseData;
	protected $textEntryTemplate;
	protected $adminHtmlTemplate;
	protected $ADM;
	protected $_scriptFiles;
	protected $_cssFiles;
	protected $_cssContent;
	protected $_textHtml;

	public $CreateStaticFiles;

	protected $mainMenu; // TODO: This has to be set from outside not as "global" under __construct

	public function __construct() {
		global $mainMenu;
		$this->mainMenu = $mainMenu;
		$this->parseData = "";
		$this->ADM = null;
		$this->CreateStaticFiles = false;

		$this->_scriptFiles = array();
		$this->_cssFiles    = array();
		$this->_cssContent  = '';
		$this->_textHtml    = '';

		$this->_checkDatabase();
	}

	/**
	 * Return Additional options for TextEditor
	 *
	 * @return string Options like defined in a Template
	 * @access public
	 * @abstract overriden from MainPlugin
	 */
	public function getAdditionalOptions() {}
	public function getSource($method="", $adminData=array(), $templateTag=array()) {}

	/**
	 * Parse the Template, create the Content and return it
	 *
	 * @param string $data Content to parse instead the template
	 * @return string Created Content
	 */
	public function doParseData($data="") {
		$this->parseData = $data;
		$this->checkForParameters();
		return $this->parseData;
	}

	/**
	 * Read the Templatefile if needed, replace all Administration-, Language and other variables
	 * @access private
	 */
	private function checkForParameters() {
		$userCheck = pCheckUserData::getInstance();
		$lang = pMessages::getLanguageInstance()->getLanguage();
		$adminAction = pURIParameters::get('adm', 0, pURIParameters::$INT);
		$sectionId = pURIParameters::get('sec', 0, pURIParameters::$INT);

		// Get Admin-Functions
		$this->getAdminFunctions();

		// Get Text-Entry
		if ( (!$userCheck->checkLogin() && ($adminAction > 0)) && (!($this->CreateStaticFiles)) ) {
			$this->parseTextEntryTemplate('loginForm');
		} else if ($userCheck->checkLogin() && ($adminAction > 99)) {
			$this->parseTextEntryTemplate('mainAdminContent');
		} else {
			$this->parseTextEntryTemplate();
		}

		// Check for an [LOGOUT_LINK] in whole Template
		if ((integer)$sectionId > 0) {
			$_sec = "&sec=".$sectionId;
		} else {
			$_sec = '';
		}
		$this->parseData = str_replace("[LOGOUT_LINK]", "/".$lang->short."/".pMenu::getMenuInstance()->getShortMenuName()."/adm=-1".$_sec, $this->parseData);
		$this->parseData = str_replace("[LOGIN_LINK]", "/".$lang->short."/".pMenu::getMenuInstance()->getShortMenuName()."/adm=1".$_sec, $this->parseData);

		$this->parseData = $this->replaceGlobalMenuVariables($this->parseData);
		$this->parseData = $this->replaceLanguageValues($this->parseData);
		$this->parseData = $this->replaceGlobalVariables($this->parseData);
	}

	/**
	 * Extract all TEXT_ENTRY sections from Template and replace them with [TEXT_ENTRY]
	 * @access private
	 */
	private function extractTextEntyFromTemplate() {
		$match = array();
		if (preg_match("/(\[TEXT_ENTRY\])(.*)(\[\/TEXT_ENTRY\])/smi", $this->parseData, $match)) {
			// Replace the found entry with [TEXT_ENTRY]
			$this->parseData = str_replace($match[0], '[TEXT_ENTRY]', $this->parseData);
			$this->textEntryTemplate = $match[2];
		}
	}

	/**
	 * Extract all CSS_IMPORT Tags andreplace them with [CSS_IMPORT]
	 *
	 * @return string Content to include other CSS-Files and Content
	 * @access private
	 */
	private function extractCSSReplacementFromTemplate() {
		$_cssImportReplacement = '';
		$match = array();
		if (preg_match("/(\[CSS_IMPORT:\")(.*?)(\"\])/smi", $this->parseData, $match)) {
			$_cssImportReplacement = $match[2];
			$this->parseData = str_replace($match[0], "[CSS_IMPORT]", $this->parseData);
		}
		return $_cssImportReplacement;
	}

	/**
	 * Extract all SCRIPT_IMPORT Tags and replace them with [SCRIPT_IMPORT]
	 *
	 * @return string Content to include a Script in the main Template
	 * @access private
	 */
	private function extractSCRIPTReplacementFromTemplate() {
		$_scriptImportReplacement = '';
		$match = array();
		if (preg_match("/(\[SCRIPT_IMPORT:\")(.*?)(\"\])/smi", $this->parseData, $match)) {
			$_scriptImportReplacement = $match[2];
			$this->parseData = str_replace($match[0], "[SCRIPT_IMPORT]", $this->parseData);
		}
		return $_scriptImportReplacement;
	}

	/**
	 * Get all TextBlocks and replace them in global template
	 *
	 * @param string $showContentFile Show this Template instead of the Textblocks
	 * @access private
	 */
	private function parseTextEntryTemplate($showContentFile="") {
		$lang = new pLanguage();
		$menu = pMenu::getMenuInstance();
		$template = pURIParameters::get('tpl', '', pURIParameters::$STRING);
		$adminAction = pURIParameters::get('adm', 0, pURIParameters::$INT);
		$postId = pURIParameters::get('i', 0, pURIParameters::$INT);

		$match = null;
		$userCheck = pCheckUserData::getInstance();
		if (!empty($this->parseData)) {
			// get [TEXT_ENTRY] section
			$this->extractTextEntyFromTemplate();

			// Check for a [CSS_IMPORT:"cssfile"] in ParseData
			$_cssImportReplacement = $this->extractCSSReplacementFromTemplate();

			// Check for a [SCRIPT_IMPORT:"scriptfile"] in ParseData
			$_scriptImportReplacement = $this->extractSCRIPTReplacementFromTemplate();

			// Replace PAGE-Specific Content
			// we need this here before all others, because we can insert CSS, SCRIPTS and all other things
			// on any page
			$this->replacePageSpecificContent();

			// Show the Text-Entries
			if (empty($showContentFile)) {
				// Check if the Current $Template is not a Text-Entry
				$object = '';
				$method = '';
				$_isTextEntry = true;
				$constName = 'PLG_'.strtoupper($template).'_TPL';
				$constMethod = 'PLG_'.strtoupper($template).'_METHOD';
				if (defined($constName) && defined($constMethod)) {
					$_isTextEntry = false;
					$object = constant($constName);
					$method = constant($constMethod);
				}

				// Get a Text-Entry if the current $Template is a Text-Entry
				if ($_isTextEntry) {
					$_textIdList = $this->getAllTextEntries($menu->getMenuId());
				} else {
					$_textIdList = array($menu->getMenuId());
				}

				if ($userCheck->checkLogin()) {
					$this->_cssFiles[] = DATA_DIR.'admin.css';
				}

				// Get all Textentries from $_textIdList
				foreach ($_textIdList as $textEntryId) {
					// get the classname from current text-entry
					if ($_isTextEntry) {
						$_currentClassName = $this->getTextClassName($textEntryId);
					} else {
						$_currentClassName = $object;
					}

					// Check if the Class exists (if not, take the TEXT-Class)
					if ( class_exists($_currentClassName) ) {
						$_txt = new $_currentClassName();
						$_txt->setTextId($textEntryId);
						$html_source = $_txt->GetSource($method);

						// Added 2011-03-18: If there is a Menu secured, we need to secure each Textentry on each
						//                   Sub-Page and on each Popup-Page so SearchEngines cannot index them
						$textEntry = new pTextEntry($textEntryId);
						$accessGroups = $textEntry->getMenuAccessGroups();
						if (!empty($accessGroups) && !empty($html_source) && !$userCheck->checkAccess('content')) {
							$accessString = '';
							foreach ($accessGroups as $a) {
								$accessString .= (empty($accessString) ? '' : ',').$a->id;
							}
							$html_source = '[MENU_ACCESS_GROUPS_'.$textEntry->menu.':'.$accessString.']'.$html_source.'[/MENU_ACCESS_GROUPS_'.$textEntry->menu.']';
						}

						$this->_textHtml .= str_replace("[CONTENT_ENTRY]", $html_source, $this->textEntryTemplate);
						$this->_textHtml  = str_replace("[ADMINID]",      ' id="admcont_'.$textEntryId.'"', $this->_textHtml); // this ID is needed now for GroupedTexts to hide the grouped ones

						// Check for Admin-TEXT-Link's
						if ($userCheck->checkAccess('content')) {
							$this->_textHtml = str_replace("[ADMIN_FUNCTIONS]",       $this->adminHtmlTemplate, $this->_textHtml);
							$this->_textHtml = str_replace("[TEXT_ADMIN_FUNCTIONS]",  "", $this->_textHtml);
							$this->_textHtml = str_replace("[/TEXT_ADMIN_FUNCTIONS]", "", $this->_textHtml);
							$this->_textHtml = str_replace("[ADMIN_MENU_INDEX]",      md5(uniqid(rand(), true)), $this->_textHtml); // just an random ID for the AdminMenu on each TextBlock

							if (defined("ADM_CREATE") && defined("ADM_EDIT") && defined("ADM_DELETE") && defined("ADM_MVUP") && defined("ADM_MVDOWN")) {
								//$this->_textHtml = str_replace("[ADMIN_LINK_CREATE]",   "/".$lang->short."/".$menu->getShortMenuName()."/adm=".ADM_CREATE, $this->_textHtml);
								//$this->_textHtml = str_replace("[ADMIN_LINK_EDIT]",     "/".$lang->short."/".$menu->getShortMenuName()."/adm=".ADM_EDIT."&i=".$_textIdList[$i], $this->_textHtml);
								$this->_textHtml = str_replace("[ADMIN_LINK_MOVEUP]",   "/".$lang->short."/".$menu->getShortMenuName()."/adm=".ADM_MVUP."&i=".$textEntryId, $this->_textHtml);
								$this->_textHtml = str_replace("[ADMIN_LINK_MOVEDOWN]", "/".$lang->short."/".$menu->getShortMenuName()."/adm=".ADM_MVDOWN."&i=".$textEntryId, $this->_textHtml);
								$this->_textHtml = str_replace("[ADMIN_LINK_CREATE]",   "javascript:showCreateText();", $this->_textHtml);
								$this->_textHtml = str_replace("[ADMIN_LINK_EDIT]",     $_txt->getEditFunction($textEntryId), $this->_textHtml);
								$this->_textHtml = str_replace("[ADMIN_LINK_CLOSE]",    $_txt->getCloseFunction($textEntryId), $this->_textHtml);
								$this->_textHtml = str_replace("[ADMIN_TEXT_ID]",       $textEntryId, $this->_textHtml);
							}

						}

						// Remove ADMIN_REMVOE
						if ($userCheck->checkAccess("content") || $userCheck->checkAccess("menu")) {
							$this->_textHtml = str_replace('[ADMIN_REMOVE]', '', $this->_textHtml);
							$this->_textHtml = str_replace('[/ADMIN_REMOVE]', '', $this->_textHtml);
						} else {
							$this->_textHtml =  preg_replace('/(\[ADMIN_REMOVE\])(.*?)(\[\/ADMIN_REMOVE\])/smi', '', $this->_textHtml);
						}

						// Insert the CSS_IMPORT if available
						if (($cssImport = $_txt->getCssImportFile()) !== false) {
							$this->_cssFiles = array_merge($this->_cssFiles, explode("::", $cssImport));
						}

						// Add CSS-Content if available
						if (($cssContent = $_txt->getCssContent()) !== false) {
							$this->_cssContent .= $cssContent.chr(13).chr(10);
						}

						// Insert the SCRIPT_IMPORT if available
						if (($scriptImport = $_txt->getScriptImportFile()) !== false) {
							$this->_scriptFiles = array_merge($this->_scriptFiles, explode("::", $scriptImport));
						}
					}
				}

			// Show the ContentFile and not the Text-Entries - this is (currently) only for Admin-Contents
			} else {

				// Load the Content-File or set it to an Error if the Contentfile does not exist
				//$file = str_replace("//", "/", ABS_TEMPLATE_DIR."/adm_".$showContentFile.".tpl");
				$this->_textHtml = $this->_readTemplateFile('adm_'.$showContentFile.'.tpl');
				if (empty($this->_textHtml)) {
				//if (file_exists($file)) {
				//	$this->_textHtml = file_get_contents($file);
				//} else {
					$this->_textHtml  = '<div style="padding:20px;">';
					$this->_textHtml .= '<div class="error">[LANG_VALUE:error_004]</div>';
					$this->_textHtml .= '<div class="errorMessage">[LANG_VALUE:error_005]<br /><br />Template: <em>adm_'.$showContentFile.'.tpl</em><br />Path: <em>'.TEMPLATE_DIR.'</em></div>';
					$this->_textHtml .= '</div>';
				}

				// Check for CSS-File, CSS-Content and SCRIPT-File
				if (!empty($this->_cssFile)) {
					$this->_cssFiles[] = $this->_cssFile;
				}
				if (!empty($this->_scriptFile)) {
					$this->_scriptFiles[] = $this->_scriptFile;
				}

				// Check for CSS-Files defined in ContentFile to import
				/*if (preg_match_all("/(\[STYLE_INCLUDE\])(.*?)(\[\/STYLE_INCLUDE\])/smi", $this->_textHtml, $match)) {
					for ($x = 0; $x < count($match[0]); $x++) {
						$this->_cssFiles[] = $match[2][$x];
						$this->_textHtml   = str_replace($match[0][$x], "", $this->_textHtml);
					}
				}*/

				// An Admin-Action higher than 10 is a Special-Plugin
				if ($adminAction > 10) {
					// Calculate the _action, which identifies the Admin-Content-Plugin
					$_action = floor($adminAction / 100) * 100;

					// Load the AdminClass if it exists
					if (class_exists("admin_".(string)$_action."_Settings")) {
						$_adminClass = "admin_".(string)$_action."_Settings";
						$ADM = new $_adminClass;
						//$ADM->_setLanguage($this->LANG, $lang->short, $langid);
						//$ADM->_setDatabase($this->DB);
						$ADM->_setAction($adminAction);
						$ADM->_setSelected($postId);
						$ADM->_setMenuId($menu->getMenuId());
						$_adminHtml = $ADM->getSource();

						// Get the CSS-Includes
						foreach ($ADM->_cssFiles as $__k => $css) {
							$tmp = array_push($this->_cssFiles, $css);
						}

						// Get the CSS-Source
						$this->_cssContent .= $ADM->_cssContent.chr(10);

						// Get the SCRIPT-Includes
						foreach ($ADM->_scriptFiles as $__k => $script) {
							$tmp = array_push($this->_scriptFiles, $script);
						}
					} else {
						// If the AdminClass does not exists, show an Error
						$_adminHtml = '<div class="error">[LANG_VALUE:error_006]'.(string)$_action.'-'.(string)($_action + 99).'</div>';
						$_adminHtml .= '<div class="errorMessage">[LANG_VALUE:error_007]</div>';
					}
					$this->_textHtml = str_replace("[ADMIN_CONTENT]", $_adminHtml, $this->_textHtml);
				}

				// Replace the Layout
				$this->_textHtml = str_replace("[LAYOUT]",  "", $this->_textHtml);
				$this->_textHtml = str_replace("[/LAYOUT]", "", $this->_textHtml);
			}

			// Replace [TEXT_ENTRY] with parsed Plugin-Content
			$this->parseData = str_replace("[TEXT_ENTRY]", $this->_textHtml, $this->parseData);

			// Check for [INCLUDE_MENU:"abc":x:y:visible:hidden_level] and Replace it if found
			if (preg_match_all('/(\[INCLUDE_MENU:")([^"]+)(":)(([\d]+)(:([\d]+)(:(true|false)(:(\d+)?)?)?)?)(\])/smi', $this->parseData, $match)) {
				// Replace ADMIN-LINK links on base template
				if ($userCheck->checkAccess("menu")) {
					$this->parseData = str_replace("[ADMIN_LINK_MENU_CREATE]", "/".$lang->short."/".$menu->getShortMenuName()."/adm=".ADM_MENU_CREATE."&menu=", $this->parseData);
					$this->parseData = str_replace("[ADMIN_LINK_MENU_EDIT]",   "/".$lang->short."/".$menu->getShortMenuName()."/adm=".ADM_MENU_EDIT."&menu=", $this->parseData);
					$this->parseData = str_replace("[ADMIN_LINK_MENU_DELETE]", "/".$lang->short."/".$menu->getShortMenuName()."/adm=".ADM_MENU_DELETE."&menu=", $this->parseData);
				}
				if ($userCheck->checkAccess("content")) {
					$this->parseData = str_replace("[ADMIN_LINK_DELETE]",   "/".$lang->short."/".$menu->getShortMenuName()."/adm=".ADM_DELETE."&i=", $this->parseData);
				}

				// Replace all Menu-Includes
				for ($i = 0; $i < count($match[0]); $i++) {
					$_menuFile = $match[2][$i];
					$_menuParent = $match[5][$i];
					$_menuMaxParent = $match[7][$i];
					$_showCompleteMenu = $match[9][$i];
					$_menuReplacement = $match[0][$i];
					if ((count($match) >= 11) && ((int)$match[11][$i] > 0)) {
						$_hideMenuAfterLevel = (int)$match[11][$i];
					} else {
						$_hideMenuAfterLevel = 65535;
					}

					// Create a MENU-Object and set the Parameters
					if (class_exists("MENU")) {
						$_menu = new MENU();
						$_menu->_minParent = $_menuParent;
						$_menu->_maxParent = $_menuMaxParent;
						$_menu->_showAll = ($_showCompleteMenu == 'true');
						$_menu->_template  = $_menuFile;
						//$_menu->_selected  = $this->mainMenu;
						$_menu->_hideLowerLevels = $_hideMenuAfterLevel;

						// Get the Menu-HTML and replace it
						$this->_textHtml = $_menu->GetSource();
						unset($_menu);

						// Check for MENU-Link's
						if ($userCheck->checkAccess("menu")) {
							if (defined("ADM_MENU_CREATE") && defined("ADM_MENU_EDIT") && defined("ADM_MENU_DELETE") && defined("ADM_MENU_MVUP") && defined("ADM_MENU_MVDOWN")) {
								// Made with delightEditor-Popup
								$this->_textHtml = str_replace("[ADMIN_LINK_MENU_CREATE]", "/".$lang->short."/".$menu->getShortMenuName()."/adm=".ADM_MENU_CREATE."&menu=", $this->_textHtml);
								$this->_textHtml = str_replace("[ADMIN_LINK_MENU_EDIT]",   "/".$lang->short."/".$menu->getShortMenuName()."/adm=".ADM_MENU_EDIT."&menu=", $this->_textHtml);
								$this->_textHtml = str_replace("[ADMIN_LINK_MENU_DELETE]", "/".$lang->short."/".$menu->getShortMenuName()."/adm=".ADM_MENU_DELETE."&menu=", $this->_textHtml);

								// TODO: Works currently, but there are some problems while creating static sites
								$this->_textHtml = str_replace("[ADMIN_LINK_MENU_LINK]", "/".$lang->short."/".$menu->getShortMenuName()."/adm=".ADM_MENU_LINK."&menu=", $this->_textHtml);

								// Direct link, there is only a Site-Refresh with some parameters
								$this->_textHtml = str_replace("[ADMIN_LINK_MENU_MOVEUP]",     "/".$lang->short."/".$menu->getShortMenuName()."/adm=".ADM_MENU_MVUP."&menu=", $this->_textHtml);
								$this->_textHtml = str_replace("[ADMIN_LINK_MENU_MOVEDOWN]",   "/".$lang->short."/".$menu->getShortMenuName()."/adm=".ADM_MENU_MVDOWN."&menu=", $this->_textHtml);
								$this->_textHtml = str_replace("[ADMIN_LINK_MENU_VISIBILITY]", "/".$lang->short."/".$menu->getShortMenuName()."/adm=".ADM_MENU_VISIBILITY."&menu=", $this->_textHtml);
							}
						}
						$this->parseData = str_replace($_menuReplacement, $this->_textHtml, $this->parseData);
					} else {
						$this->parseData = str_replace($_menuReplacement, '', $this->parseData);
					}
				}
			}

			// Replace the current Location [LOCATION_LINK]
			$locationLink = '/'.$lang->short.'/'.$menu->getShortMenuName();
			$this->parseData = str_replace('[LOCATION_LINK]', $locationLink, $this->parseData);

			// Check for [INCLUDE_NEWS..] Tag and Replace it if there is one
			$this->replaceNewsTag();

			// Check for [BREADCRUMBS:"sep":"end":"css-class"]
			$this->replaceBreadcrumb();

			// include any language-specific things
			$this->insertLanguageTemplates();

			// Insert the SCRIPT-Files and CSS-Files
			$this->insertCSS($this->_cssFiles, $_cssImportReplacement, $this->_cssContent);
			$this->insertScripts($this->_scriptFiles, $_scriptImportReplacement);

			$this->replacePluginSpecificContent();
			$this->replaceShortLinkMenu();
			$this->replaceMenuImages();

			// Replace Login and Logout-Links if some existst in current page
			if ($userCheck->checkLogin()) {
				$this->parseData = preg_replace('/(\[LOGOUT\])(.*)(\[\/LOGOUT\])/smi', '\\2', $this->parseData);
				$this->parseData = preg_replace('/(\[LOGIN\])(.*)(\[\/LOGIN\])/smi', '', $this->parseData);
			} else {
				$this->parseData = preg_replace('/(\[LOGOUT\])(.*)(\[\/LOGOUT\])/smi', '', $this->parseData);
				$this->parseData = preg_replace('/(\[LOGIN\])(.*)(\[\/LOGIN\])/smi', '\\2', $this->parseData);
			}

			// replace all unwanted ADMIN-Template-Tags
			$this->parseData = str_replace('[ADMIN_FUNCTIONS]', '', $this->parseData);
			$this->parseData = preg_replace('/(\[TEXT_ADMIN_FUNCTIONS\])(.*?)(\[\/TEXT_ADMIN_FUNCTIONS\])/smi', '', $this->parseData);
			$this->parseData = str_replace('[ADMINID]', '', $this->parseData);
		}

		// finally we encode all %u0xxxx to real chars
		$this->parseData = preg_replace('/%u0([[:alnum:]]{3})/smi', '&#x${1};', $this->parseData);
	}

	/**
	 * Check for [INCLUDE_NEWS:"cont_news.tpl":"news_plain":"News":"5"] and replace it if found
	 *
	 * @access private
	 */
	private function replaceNewsTag() {
		$match = array();
		if (preg_match_all("/(\[INCLUDE_NEWS\:\")([^\"]+)(\":\"([^\"]+)(\":\"([^\"]+)?(\":\"([^\"]+)?)?)?)(\"\])/smi", $this->parseData, $match, PREG_SET_ORDER)) {
			for ($i = 0; $i < count($match); $i++) {
				$_newsFile = $match[$i][2];
				$_newsTitle = $match[$i][6];
				$_newsNum = (integer)$match[$i][8];
				$_newsTemplate = $match[$i][4];
				$_newsReplacement = $match[$i][0];

				if (class_exists("NEWS")) {
					// Create a LANG-Object and set the Parameters
					$_news = new NEWS();
					$data = array();
					$data['layout'] = $_newsFile;
					$data['title'] = $_newsTitle;
					$data['num'] = $_newsNum;
					$data['template'] = $_newsTemplate;

					// Get the Menu-HTML and replace it
					$this->parseData = str_replace($_newsReplacement, $_news->getSource(PLG_NEWS_FEEDCONT, array(), $data), $this->parseData);

					// Insert the CSS_IMPORT if available
					if (($cssImport = $_news->getCssImportFile()) !== false) {
						$this->_cssFiles = array_merge($this->_cssFiles, explode("::", $cssImport));
					}

					// Add CSS-Content if available
					if (($cssContent = $_news->getCssContent()) !== false) {
						$this->_cssContent .= $cssContent.chr(13).chr(10);
					}

					// Insert the SCRIPT_IMPORT if available
					if (($scriptImport = $_news->getScriptImportFile()) !== false) {
						$this->_scriptFiles = array_merge($this->_scriptFiles, explode("::", $scriptImport));
					}

				} else {
					$this->parseData = str_replace($_newsReplacement, '', $this->parseData);
				}
			}
		}
	}

	/**
	 * Replace all [BREADCRUMBS:"sep":"end":"css-class"] Tags
	 *
	 * @access private
	 * @return void
	 */
	private function replaceBreadcrumb() {
		if (preg_match_all('/\[BREADCRUMBS(:([^:\]]+)(:([^\]]+)?)?)?\](.*?)\[\/BREADCRUMBS\]/smi', $this->parseData, $match, PREG_SET_ORDER)) {
			$menu = pMenu::getMenuInstance();
			foreach ($match as $m) {
				$sep = strlen($m[2])>0 ? $m[2] : ' / ';
				$end = strlen($m[4])>0 ? $m[4] : ' &gt;';
				$cont = $m[5];
				$repl = $menu->getBreadcrumb($cont, $sep, $end);
				$this->parseData = str_replace($m[0], $repl, $this->parseData);
			}
		}
	}

	/**
	 * include all Language-Templates
	 *
	 * @access private
	 */
	private function insertLanguageTemplates() {
		$match = array();
		if (preg_match("/(\[INCLUDE_LANG\:\")([^\"]+)(\"\])/smi", $this->parseData, $match)) {
			$_langFile = $match[2];
			$_langReplacement = $match[0];

			// Create a LANG-Object and set the Parameters
			if (class_exists("LANG")) {
				$_lang = new LANG();
				$_lang->setTemplateFile($_langFile);

				// Get the Menu-HTML and replace it
				$this->parseData = str_replace($_langReplacement, $_lang->GetSource(), $this->parseData);
			} else {
				$this->parseData = str_replace($_langReplacement, '', $this->parseData);
			}
		}
	}

	/**
	 * Insert Scriptfiles which we found in templates
	 *
	 * @param array $scriptFiles Array with all files to be inserted as a script
	 * @param string $replacement Script-Content trought which a script should be inserted
	 * @access private
	 */
	private function insertScripts($scriptFiles, $replacement) {
		if (substr_count($this->parseData, "[SCRIPT_IMPORT]") > 0) {
			$scriptFiles = array_unique($scriptFiles);
			foreach ($scriptFiles as $_file) {
				if (strlen(trim($_file)) > 0) {
					$this->parseData = str_replace("[SCRIPT_IMPORT]", str_replace("[SCRIPT_FILE]", $_file, $replacement)."[SCRIPT_IMPORT]", $this->parseData);
				}
			}
			$this->parseData = str_replace("[SCRIPT_IMPORT]", "", $this->parseData);
		}
	}

	/**
	 * Insert CSS-Fiels and CSS-Content which we found in out templates
	 *
	 * @param array $cssFiles List with all CSS-Files to be inserted
	 * @param string $replacement CSS-Content trough which the CSS should be inserted
	 * @param string $cssContent CSS-Content which should be inserted
	 */
	private function insertCSS($cssFiles, $replacement, $cssContent) {
		if (substr_count($this->parseData, "[CSS_CONTENT]") > 0) {
			$this->parseData = str_replace("[CSS_CONTENT]", $cssContent, $this->parseData);
		}

		// Insert the CSS-Files
		if (substr_count($this->parseData, "[CSS_IMPORT]") > 0) {
			$cssFiles = array_unique($cssFiles);
			foreach ($cssFiles as $_file) {
				if (strlen(trim($_file)) > 0) {
					$this->parseData = str_replace("[CSS_IMPORT]", str_replace("[CSS_FILE]", $_file, $replacement)."[CSS_IMPORT]", $this->parseData);
				}
			}
			$this->parseData = str_replace("[CSS_IMPORT]", "", $this->parseData);
		}
	}

	/**
	 * Replace Content which should be showed only on the current Page or which should be NOT displayd on this page
	 *
	 * @access private
	 */
	private function replacePageSpecificContent(&$content="") {
		$parseData = empty($content);
		if ($parseData) {
			$content = $this->parseData;
		}

		$lang = pMessages::getLanguageInstance()->getLanguage();
		$match = array();
		if (preg_match_all('/(\[(|NOT_)PAGE_CONTENT:)([^:\]]+)(:([^]]+))?(\])(.*?)(\[\/(\\2)PAGE_CONTENT(:\\3)?\])/smi', $content, $match, PREG_SET_ORDER) > 0) {
			foreach ($match as $m) {
				$sc = new pSpecialContent();
				try {
					$options = @json_decode($m[5], true);
				} catch(Exception $e) {
					$options = array();
				}
				if (!is_array($options)) {
					$options = array();
				}
				if (array_key_exists('empty', $options)) {
					$options['empty'] = str_replace('&#91;', '[', $options['empty']);
					$options['empty'] = str_replace('&#93;', ']', $options['empty']);
				}
				$this->replacePageSpecificContent($m[7]);
				$options['content'] = $m[7];
				$content = str_replace($m[0], $sc->getContent($m[3], $options), $content);
				/*if ( (($currentMenu['short'] == $m[3]) && ($m[2] == '')) || (($currentMenu['short'] != $m[3]) && ($m[2] == 'NOT_')) ) {
					$content = str_replace($m[0], $m[5], $content);
				}*/
			}
		}
		$content = preg_replace('/(\[(|NOT_)PAGE_CONTENT:)([^:\]]+)(:([^]]+))?(\])(.*?)(\[\/(\\2)PAGE_CONTENT(:\\3)?\])/smi', '', $content);
		if ($parseData) {
			$this->parseData = $content;
		}
	}

	/**
	 * Replace all MenuImages Tags which are not given by layouts or other
	 *
	 * @access private
	 */
	private function replaceMenuImages() {
		$menuEntry = pMenu::getMenuInstance()->getMenuEntry();
		$this->parseData = str_replace('[MENU_IMAGE_ID]', $menuEntry->image_id, $this->parseData);
		$match = array();
		if (preg_match_all('/\[MENU_IMAGE:(\d+)x(\d+)(:(true|false))?\]/smi', $this->parseData, $match, PREG_SET_ORDER)) {
			foreach ($match as $img) {
				$menuEntry->setImageSize((int)$img[1], (int)$img[2]);
				$img_src = $menuEntry->image;
				if ( ($img[4] == 'false') && (substr_count($img_src, 'about:blank') > 0) ) $img_src = '';
				$this->parseData = str_replace($img[0], $img_src, $this->parseData);
			}
		}
		if (preg_match_all('/\[MENU_IMAGE_URL:(\d+)x(\d+)(:(true|false))?\]/smi', $this->parseData, $match, PREG_SET_ORDER)) {
			foreach ($match as $img) {
				$menuEntry->setImageSize((int)$img[1], (int)$img[2]);
				$img_src = $menuEntry->image_url;
				if ( ($img[4] == 'false') && (substr_count($img_src, 'about:blank') > 0) ) $img_src = '';
				$this->parseData = str_replace($img[0], $img_src, $$this->parseData);
			}
		}
	}

	/**
	 * Relpace PluginContent
	 * Format of TemplateTag: [PLUGIN:ShortMenu:PluginName:PluginFunction] ... [/PLUGIN:ShortMenu:PluginName:PluginFunction]
	 * Format for the TemplateTagContent: "Normal String" or "params:key=value;key=value;..."
	 *
	 * @access private
	 */
	private function replacePluginSpecificContent() {
		$lang = pMessages::getLanguageInstance()->getLanguage();
		$menu = pMenu::getMenuInstance();
		$match = array();
		$currentMenu = $this->getMenuData($lang->short, $menu->getMenuId());
		if (array_key_exists('short', $currentMenu)) {
			if (preg_match_all("/(\[PLUGIN:)([^:]*):([^:]*):([^\]]+)(\])(.*?)(\[\/PLUGIN:\\2:\\3:\\4\])/smi", $this->parseData, $match, PREG_SET_ORDER) && (count($match) > 0)) {
				foreach ($match as $m) {
					if ( ((empty($m[2])) || ($currentMenu['short'] == $m[2])) && class_exists($m[3]) ) {
						$obj = new $m[3]();
						if ($obj instanceof iPlugin) {
							$obj->setContentParameters($m[6]);
							$this->parseData = str_replace($m[0], $obj->callFunction($m[4]), $this->parseData);
						}
					}
				}
			}
		}
		$this->parseData = preg_replace("/(\[PLUGIN:)([^\]]+)(\])(.*?)(\[\/PLUGIN:)([^\]]+)(\])/smi", "", $this->parseData);
	}

	/**
	 * Replace tag SHORT_LINK which is a link to a specific Menu-Shortlink
	 *
	 * @access private
	 */
	private function replaceShortLinkMenu() {
		$lang = pMessages::getLanguageInstance()->getLanguage();
		$userCheck = pCheckUserData::getInstance();
		$match = array();
		if (preg_match_all('/(\[SHORT_LINK:")(.*?)("\])(.*?)(\[\/SHORT_LINK\])/smi',$this->parseData, $match) && (count($match) > 0)) {
			for ($i = 0; $i < count($match[0]); $i++) {
				$_lnk  = $this->getShortLinkData($lang->short, $match[2][$i]);
				if (count($_lnk) <= 0) {
					if ($userCheck->checkLogin()) {
						$match[4][$i] = str_replace('[LINK]', '#', $match[4][$i]);
						$match[4][$i] = str_replace('[LINK_TEXT]', $match[2][$i], $match[4][$i]);
						$match[4][$i] = preg_replace('/(\[NOT_EXISTS:")(.*?)("\])/smi', '\\2', $match[4][$i]);
					} else {
						$match[4][$i] = "";
					}
				} else {
					$match[4][$i] = str_replace('[LINK]', $_lnk[0], $match[4][$i]);
					$match[4][$i] = str_replace('[LINK_TEXT]', $_lnk[1], $match[4][$i]);
					$match[4][$i] = preg_replace('/(\[NOT_EXISTS:")(.*?)("\])/smi', '', $match[4][$i]);
				}
				$this->parseData = str_replace($match[0][$i], $match[4][$i], $this->parseData);
			}
		}
	}

	/**
	 * Return a List with all Textblock-ID's which are on the cutrrent page
	 *
	 * @param integer $mid MenuID to get all Textblocks from
	 * @return array Simple array with all TextblockID's
	 * @access private
	 */
	private function getAllTextEntries($mid) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$lang = new pLanguage();

		// Check if this is a LINKed Menu and not a real one
		$sql  = "SELECT [men.link] FROM [table.men] WHERE [men.id]=".(int)$mid." AND [men.link]>0;";
		$db->run($sql, $res);
		if ($res->getFirst()) {
			$mid = (int)$res->{$db->getFieldName('men.link')};
		}
		$res = null;

		$sql  = "SELECT [txt.id] FROM [table.txt] WHERE [txt.menu]=".(int)$mid." AND [txt.lang]=".(int)$lang->id." AND [txt.grouped]=0 ORDER BY [txt.sort];";
		$db->run($sql, $res);
		$back = array();
		if ($res->getFirst()) {
			while ($res->getNext()) {
				array_push($back, $res->{$db->getFieldName('txt.id')});
			}
		}
		$res = null;
		return $back;
	}

	/**
	 * Get a Text-Entry by id and return the corresponding Class from which this textentry is
	 *
	 * @param integer $id Textblock-ID
	 * @return string Plugin-Name (used to parse the data)
	 */
	private function getTextClassName($id) {
		$textEntry = new pTextEntry($id);
		return $textEntry->plugin;

		/*$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;

		$sql = 'SELECT [txt.plugin] FROM [table.txt] WHERE [txt.id]='.(int)$id.';';
		$db->run($sql, $res);
		if ($res->getFirst()) {
			$class = $res->{$db->getFieldName('txt.plugin')};
		} else {
			$class = 'TEXT';
		}
		$res = null;

		if (!(class_exists($class))) {
			$class = 'TEXT';
		}
		return $class;*/
	}

	/**
	 * Replace all Admin-Functions and Tags in current content
	 *
	 * @access private
	 */
	private function getAdminFunctions() {
		$lang = pMessages::getLanguageInstance();
		$adminAction = pURIParameters::get('adm', 0, pURIParameters::$INT);
		$menu = pMenu::getMenuInstance();

		$match = null;
		$this->adminHtmlTemplate = "";
		$userCheck = pCheckUserData::getInstance();
		if ($userCheck->checkAccess('content') && ($adminAction <= 10)) {
			// get Text-Entry-Template-Section
			if (preg_match('/(\[ADMIN_MAIN_FUNCTIONS\])(.*?)(\[\/ADMIN_MAIN_FUNCTIONS\])/smi', $this->parseData, $match)) {
				$this->parseData = str_replace($match[0], $match[2], $this->parseData);
				$this->parseData = str_replace('[ADMIN_LINK_CREATE]', 'javascript:showCreateText();" id="adm_newtext', $this->parseData);
			}

			// TODO: DEPRECATED since we use MouseOver Menus
			if (preg_match('/(\[ADMIN_FUNCTIONS\])(.*?)(\[\/ADMIN_FUNCTIONS\])/smi', $this->parseData, $match)) {
				$this->adminHtmlTemplate = $match[2];
				$this->parseData = str_replace($match[0], '', $this->parseData);
			}

			if (preg_match('/(\[TEXT_TYPE_ENTRY\])(.*?)(\[\/TEXT_TYPE_ENTRY\])/smi', $this->parseData, $match)) {
				$entries = '';
				$lnk = "/".$lang->getShortLanguageName()."/".$menu->getShortMenuName()."/adm=".ADM_CREATE;

				foreach (explode(',', TEXT_PLUGINS) as $_insPlg) {
					if (class_exists($_insPlg)) {
						$OBJ = new $_insPlg();
						if ($OBJ->isTextPlugin()) {
							if (file_exists(ABS_TEMPLATE_DIR.'images/admin/texttype_'.strtolower($_insPlg).'.gif')) {
								$_insPlgImg = TEMPLATE_DIR.'images/admin/texttype_'.strtolower($_insPlg).'.gif';
							} else if (file_exists(ABS_TEMPLATE_DIR.'images/admin/texttype_'.strtolower($_insPlg).'.png')) {
								$_insPlgImg = TEMPLATE_DIR.'images/admin/texttype_'.strtolower($_insPlg).'.png';
							} else {
								$_insPlgImg = MAIN_DIR.'/images/blank.gif';
							}

							$_tmp = str_replace('[TEXT_TYPE_NAME]', ucfirst(strtolower($_insPlg)), $match[2]);
							$_tmp = str_replace('[TEXT_TYPE_IMAGE]', $_insPlgImg, $_tmp);
							$_tmp = str_replace('[TEXT_TYPE_CREATE]', $lnk.'&textParser='.strtoupper($_insPlg), $_tmp);
							$entries .= $_tmp;
						}
						unset($OBJ);
					}
				}

				$this->parseData = str_replace($match[0], $entries, $this->parseData);
			}
		} else {
			$this->parseData = preg_replace('/(\[ADMIN_MAIN_FUNCTIONS\])(.*?)(\[\/ADMIN_MAIN_FUNCTIONS\])/smi', '', $this->parseData, -1);
			$this->parseData = preg_replace('/(\[ADMIN_FUNCTIONS\])(.*?)(\[\/ADMIN_FUNCTIONS\])/smi', '', $this->parseData, -1);
		}
	}

	/**
	 * Replace all LANG_VALUE Tags in $rep
	 *
	 * @param string $rep Content to replace language-tags
	 * @return string $rep with all lang_values replaced
	 */
	private function replaceLanguageValues($rep='') {
		// Replace Lang_Value messages
		$lang = pMessages::getLanguageInstance();
		$rep = preg_replace('/(\[LANG_VALUE\:)([\w\d]+)(\])/ie', '$lang->getValue("","txt","$2")', $rep);
		return $rep;
	}

	private function replaceGlobalMenuVariables($rep='') {
		// Replace "[CURRENT_MENU_SHORT]"
		$_menu = pMenu::getMenuInstance()->getMenuEntry();
		if ($_menu instanceof pMenuEntry) {
			$rep = str_replace('[CURRENT_MENU_SHORT]', $_menu->short, $rep);
			$rep = str_replace('[SITE_TITLE]', $_menu->title, $rep);
			$rep = str_replace('[SITE_DESCRIPTION]', $_menu->description, $rep);
			$rep = str_replace('[SITE_KEYWORDS]', $_menu->keywords, $rep);
		}

		// Replace "[PARENT_MENU_SHORT]"
		$_menu = pMenu::getMenuInstance()->getMenuEntry()->getParentMenuEntry();
		if ($_menu instanceof pMenuEntry) {
			$rep = str_replace('[PARENT_MENU_SHORT]', $_menu->short, $rep);
		}

		// Replace [MAIN_MENU_SHORT]
		$backtrace = pMenu::getMenuInstance()->getBacktrace();
		if (count($backtrace) > 0) {
			$_menu = new pMenuEntry($backtrace[0]);
			$rep = str_replace('[MAIN_MENU_SHORT]', $_menu->short, $rep);
		}

		// Replace [MENU_SHORT:NUMBER:SLASH-REPLACEMENT]
		for ($i = 0; $i < count($backtrace); $i++) {
			$_menu = new pMenuEntry($backtrace[$i]);
			$rep = preg_replace('/\[MENU_SHORT:'.$i.'(:([^\]]+))?\]/smie', 'str_replace("/", "\\2", $_menu->short);', $rep);
		}
		return $rep;
	}

	/**
	 * Replace some global variables
	 *
	 * @param string $rep Content to replace global variables and things
	 * @return string $rep with all replaced things
	 * @access protected
	 */
	private function replaceGlobalVariables($rep='') {
		$match = null;
		// Replace Privacy-Policy-Link
		if (preg_match("/(\[PRIVACY_POLICY_LINK\])(((.*?)(\[\/PRIVACY_POLICY_LINK\]))?)/smi", $rep, $match) && defined("PRIVACY_POLICY_LINK")) {
			if ( (strlen(trim($match[5])) > 0) && (substr_count($match[2], "[PRIVACY_POLICY_LINK]") <= 0) ) {
				$replacement = str_replace("[LINK_TEXT]", $match[4], PRIVACY_POLICY_LINK);
			} else {
				$replacement = str_replace("[LINK_TEXT]", "privacy policy", PRIVACY_POLICY_LINK);
			}
			$rep = str_replace($match[0], $replacement, $rep);
		}

		// Replace the CopyRight
		$copy = '<a class="anchor" href="http://www.delight.ch/" target="_blank" style="font-size:8pt;"><strong>delight cms</strong> &copy; delight software gmbh, 2001-'.date('Y').'</a>';
		$rep = str_replace('[COPYRIGHT]', $copy, $rep);

		$rep = str_replace('\"', '"', $rep);
		$rep = str_replace('\'', '\'', $rep);
		return $rep;
	}

	/**
	 * Return the Name from a Menu identified by the ShortLink
	 *
	 * @param string $lang Language to get the Menu from
	 * @param string $short ShortLink from Menu to get
	 * @return array 0=>Link, 1=>Menuname
	 * @access private
	 */
	private function getShortLinkData($lang, $short) {
		$back = array();
		/*$sql  = "SELECT ".$this->DB->Field('mtx','text');
		$sql .= " FROM ".$this->DB->Table('men').",".$this->DB->Table('mtx').",".$this->DB->Table('lan');
		$sql .= " WHERE ".$this->DB->Field('men','short')."='".$short."'";
		$sql .= " AND ".$this->DB->Field('men','id')."=".$this->DB->Field('mtx','menu');
		$sql .= " AND ".$this->DB->Field('mtx','lang')."=".$this->DB->Field('lan','id');
		$sql .= " AND ".$this->DB->Field('lan','short')."='".$lang."'";
		$sql .= " AND ".$this->DB->Field('mtx','active').">=1;";
		$res = $this->DB->ReturnQueryResult($sql);

		if ($res) {
		$row = mysql_fetch_assoc($res);
		$this->DB->FreeDatabaseResult($res);
		$back[0] = "/".$lang."/".$short;
		$back[1] = $row[$this->DB->FieldOnly('mtx','text')];
		}*/

		$data = $this->getMenuData($lang, 0, $short);
		if ((count($data) > 0) && ($data['active'] > 0)) {
			$back[0] = "/".$lang."/".$short;
			$back[1] = $data['text'];
		}
		return $back;
	}

	/**
	 * Return an Array with all informations about a menu
	 *
	 * @param string $lang Language to get the menu from
	 * @param integer $id MenuID to get informations from
	 * @param string $short optional ShortMenuName
	 * @return array List with all Menuinformations
	 * @access private
	 */
	private function getMenuData($lang, $id, $short=null) {
		$lang = new pLanguage($lang);
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$back = array();

		// If there is a Short-Link we first get the MenuID
		if (($short != null) && (strlen($short) > 0)) {
			$sql  = 'SELECT [men.id] FROM [table.men],[table.mtx]';
			$sql .= ' WHERE [men.short]=\''.mysql_real_escape_string($short).'\' AND [men.id]=[mtx.menu] AND [mtx.lang]='.$lang->id.';';
			$db->run($sql, $res);

			if ($res->getFirst()) {
				$id = $res->{$db->getFieldName('men.id')};
			}
			$res = null;
		}

		// Get all MenuValues if the MenuID is higher than zero
		if ($id > 0) {
			$sql  = 'SELECT * FROM [table.men],[table.mtx] WHERE [men.id]='.(int)$id.' AND [men.id]=[mtx.menu] AND [mtx.lang]='.$lang->id.';';
			$db->run($sql, $res);
			if ($res->getFirst()) {
				$back['id']     = (int)$id;
				$back['parent'] = (int)$res->{$db->getFieldName('men.parent')};
				$back['pos']    = (int)$res->{$db->getFieldName('men.pos')};
				$back['link']   = $res->{$db->getFieldName('men.link')};
				$back['short']  = $res->{$db->getFieldName('men.short')};
				$back['text']   = $res->{$db->getFieldName('mtx.text')};
				$back['active'] = (int)$res->{$db->getFieldName('mtx.active')};
				$back['lang']   = (int)$res->{$db->getFieldName('mtx.lang')};
				$back['langtext'] = $lang;
			}
		}
		$res = null;
		return $back;
	}

	/**
	 * Check Database integrity
	 *
	 * This function creates all required Tables, Updates, Inserts and Deletes.
	 */
	public function _checkDatabase() {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;

		pCheckUserData::getInstance();
		new pLanguage();
		new pCountry();

		// Get the current Version
		$v = $this->_checkMainDatabase();
		$version = $v[0];
		$versionid = $v[1];

		// Updates to the Database
		if ($version < 2006010500) {
			
		}

		// Update the version
		$this->_updateVersionTable($version, $versionid, self::VERSION);
	}

}

?>
