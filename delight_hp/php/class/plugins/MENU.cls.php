<?php
/**
 * This Class is for the Menu-Section in Templates
 * All Language-Specific textx are defined in an XML with tags <SECTION> and child-Tags <TEXT id="name" value="value" />
 *
 * @author Lukas Zurschmiede <l.zurschmiede@delightsoftware.com>
 * @copyright (c) 2007 by delight software gmbh, switzerland
 *
 * @package delightcms
 * @version 2.0
 * @uses singelton, MainPlugin
 */

class MENU extends MainPlugin {
	const VERSION = 2012052603;
	const MAX_SMALL_MENU_ENTRIES = 10;

	protected $_content;
	protected $_btId;
	protected $_btArray;
	protected $_currentLevel;
	protected $_tplOffset;
	protected $_closedTemplate;
	protected $_opendTemplate;
	protected $_adminTemplate;
	protected $_adminMenuTemplate;
	protected $_colorValues;
	protected $_showNoAdmin;
	protected $_insertAdminLink;
	protected $_currentLinkLevel;
	protected $_currentLink;
	protected $_selectedLinks;
	protected $_isTextPlugin = false;
	private $maxMenuEntries;
	private $numMenuEntries;

	public $_template;
	public $_minParent;
	public $_maxParent;
	public $_showAll;
	public $_hideLowerLevels;

	public function __construct() {
		$this->_checkDatabase();
		parent::__construct();

		$this->_content   = '';
		$this->_template  = 'nonexistent';
		$this->_minParent = 0;
		$this->_maxParent = 65534;
		$this->_btId = 0;
		$this->_btArray = array();
		$this->_currentLevel = 0;
		$this->_tplOffset = 0;
		$this->_showAll = false;
		$this->_hideLowerLevels = 65535;
		$this->_showNoAdmin = false;
		$this->_insertAdminLink = '';
		$this->_currentLinkLevel = 0;
		$this->_currentLink = '';
		$this->_selectedLinks = array();
		$this->maxMenuEntries = 0;
		$this->numMenuEntries = 0;
		$this->_adminMenuTemplate = array();
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
		// Create the Backtrace IdList
		$this->getBacktraceMenu();
		$this->parseLinkMenus();
		if (CMS_SMALL) {
			$this->maxMenuEntries = defined('CMS_SMALL_NUM_MENU') ? (int)CMS_SMALL_NUM_MENU : self::MAX_SMALL_MENU_ENTRIES;
		}

		// Check for the TemplateFile
		if ($this->readTemplateFile()) {
			if ((int)$this->_minParent == 0) {
				if (substr_count($this->_content, "[0_MENU_") > 0) {
					//for ($i = 0; $i < constant("MAX_MAIN_MENU_ENTRIES"); $i++)
					//$this->replaceMainMenuEntry($i, $adminData);
					$this->replaceMainMenuEntry();
				} else {
					if (count($adminData) <= 0) {
						//$this->_maxParent = 0;
						//$this->_minParent = 0;
						$this->_currentLevel = 0;
						$this->createSubMenu(false);
					}
				}
			} else if ( ((int)$this->_maxParent > 0) || (strlen($this->_maxParent) == 0) ) {
				if (strlen($this->_maxParent) == 0) {
					$this->_maxParent = 65534;
				}
				$this->_currentLevel = $this->_minParent;
				$this->createSubMenu($adminData);
			}
		}
		return $this->_content;
	}

	/**
	 * Additional Options for the TextEditor
	 *
	 * @param string $options Options from Template
	 * @return string Options like defined in a Template
	 * @access public
	 */
	public function getAdditionalOptions($options='') {

	}

	/**
	 * Read the Template-File
	 *
	 * @return boolean If the Templatefile have to read
	 */
	protected function readTemplateFile() {
		$file = ABS_TEMPLATE_DIR.$this->_template;
		if (file_exists($file) && is_readable($file)) {
			$this->_content = file_get_contents($file);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Create a list with all MenuID's where the current one is in
	 *
	 */
	private function getBacktraceMenu() {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;

		$this->_btArray = array();
		$_btId = $this->_selected;

		while (true) {
			// Add the SelectedId to the Backtrace
			array_push($this->_btArray, $_btId);

			// Get the Parent-Menu
			$sql  = "SELECT [men.id],[men.parent] FROM [table.men] WHERE [men.id]=".(int)$_btId.";";
			$db->run($sql, $res);
			if (!$res->getFirst()) {
				break;
			}

			// Get the Database-Entry
			$_btId = (int)$res->{$db->getFieldName('men.parent')};

			// Check for 'parent' is '0'
			if ($_btId == 0) {
				array_push($this->_btArray, $_btId);
				break;
			}
		}
		sort($this->_btArray);
	}

	//function replaceMainMenuEntry($num=0, $adm=false)
	/**
	 * Replace the MainMenu links
	 *
	 */
	private function replaceMainMenuEntry() {
		$db = pDatabaseConnection::getDatabaseInstance();
		$lang = pMessages::getLanguageInstance();
		$userCheck = pCheckUserData::getInstance();
		$_idList = $this->getMenuParentId(0);
		for ($i = 0; $i < count($_idList); $i++) {
			$_menuValues = $this->getMenuValues($_idList[$i]);

			// Get Values
			$MenuText  = $_menuValues['text'];
			$MenuWidth = $lang->getValue('','menu','mainmenu_width_'.$i);
			if (strlen($_menuValues['short_link']) > 0) {
				$MenuLink  = '/'.$lang->getShortLanguageName().'/'.$_menuValues['short_link'];
			} else {
				$MenuLink  = '/'.$lang->getShortLanguageName().'/'.$_idList[$i];
			}

			// Replace Menu-Settings
			$this->_content = str_replace('[0_MENU_'.($i+1).']',       $MenuText,  $this->_content);
			$this->_content = str_replace('[0_MENU_'.($i+1).'_WIDTH]', $MenuWidth, $this->_content);
			$this->_content = str_replace('[0_MENU_'.($i+1).'_LINK]',  $MenuLink,  $this->_content);
			$this->_content = str_replace('[0_MENU_'.($i+1).'_LANG]',  $lang->getShortLanguageName(),  $this->_content);
			$this->_content = str_replace('[0_ADMIN_MENU_'.($i+1).'_ID]',  $_idList[$i], $this->_content);

			// only admin
			if ($userCheck->checkAccess('menu') && (!$this->_showNoAdmin)) {
				// replace visible and translated
				if (!$_menuValues['translated']) {
					$this->_content = preg_replace('/(\[0_MENU_'.($i+1).'_NOT_TRANSLATED:")(.*?)("\])/smi', '\\2', $this->_content);
				}

				// Check if the Menu should be sowed as NOT-VISIBLE
				if ((int)$_menuValues[$db->getFieldName('mtx.active')] <= 0) {
					$this->_content = preg_replace('/(\[0_MENU_'.($i+1).'_NOT_VISIBLE:")(.*?)((":"(.*?))?)("\])/smi', '\\2', $this->_content);
				} else {
					$this->_content = preg_replace('/(\[0_MENU_'.($i+1).'_NOT_VISIBLE:")(.*?)((":"(.*?))?)("\])/smi', '\\5', $this->_content);
				}

				// Check if the Menu is translated or not
				if ((int)$_menuValues['translated'] <= 0) {
					$this->_content = preg_replace('/(\[0_MENU_'.($i+1).'_NOT_TRANSLATED:")(.*?)((":"(.*?))?)("\])/smi', '\\2', $this->_content);
				} else {
					$this->_content = preg_replace('/(\[0_MENU_'.($i+1).'_NOT_TRANSLATED:")(.*?)((":"(.*?))?)("\])/smi', '\\5', $this->_content);
				}

				// admin-functions
				$this->_content = str_replace('[0_ADMIN_FUNCTIONS_'.($i+1).']', '', $this->_content);
				$this->_content = str_replace('[/0_ADMIN_FUNCTIONS_'.($i+1).']', '', $this->_content);
			} else {
				$this->_content = preg_replace('/(\[0_(.*?)\])(.*?)(\[\/\\1\])/smi', '', $this->_content);
				$this->_content = preg_replace('/(\[0_(.*?)\])/smi', '', $this->_content);
			}
		}
	}


	private function parseTemplate() {
		// initialize variables
		if (count($this->_adminMenuTemplate) <= 0) {
			$this->_adminMenuTemplate = array();
		}
		if (count($this->_closedTemplate) <= 0) {
			$this->_closedTemplate = array();
		}
		if (count($this->_opendTemplate) <= 0) {
			$this->_opendTemplate = array();
		}
		if (count($this->_colorValues) <= 0) {
			$this->_colorValues = array();
		}
		$_content = $this->_content;

		// Get the Main-Template
		if (preg_match("/(\[SUBMENU_MAIN\])(.*)(\[\/SUBMENU_MAIN\])/smi", $this->_content, $match)) {
			$this->_content = $match[2];
		} else {
			$this->_content = "[SUBMENU_ENTRIES]";
		}

		// Get some Color-Definitions
		if (count($this->_colorValues) <= 0) {
			if (preg_match_all("/(\[color:)([^:]+)(:)([\d]+)(:)([\d]+)(\])/smi", $_content, $match)) {
				for ($i = 0; $i < count($match[0]); $i++) {
					$this->_colorValues[$match[2][$i]][0] = $match[4][$i];
					$this->_colorValues[$match[2][$i]][1] = $match[6][$i];
				}
			}
		}

		// Get ClosedMenu templates
		if (count($this->_closedTemplate) <= 0) {
			if (preg_match_all("/(\[MENU_)(\d+)(_closed\])(.*)(\[\/MENU_\\2_closed\])/smi", $_content, $match)) {
				for ($i = 0; $i < count($match[0]); $i++) {
					$this->_closedTemplate[(integer)$match[2][$i]] = $match[4][$i];
				}
			}
		}

		// Get OpendMenu templates
		if (count($this->_opendTemplate) <= 0) {
			if (preg_match_all("/(\[MENU_)(\d+)(_open\])(.*)(\[\/MENU_\\2_open\])/smi", $_content, $match)) {
				for ($i = 0; $i < count($match[0]); $i++) {
					$_curId = (integer)$match[2][$i];
					$this->_opendTemplate[$_curId]['main'] = $match[4][$i];
					if (preg_match_all("/(\[SUBMENU:)([\d]+)(\])(.*?)(\[\/SUBMENU(:\\2)?\])/smi", $match[4][$i], $_match)) {
						// Fill the _openTemplate-Array
						for ($x = 0; $x < count($_match[0]); $x++) {
							if (substr_count($this->_opendTemplate[$_curId]['main'], "[SUBMENU_ENTRY]") <= 0) {
								$this->_opendTemplate[$_curId]['main'] = str_replace($_match[0][$x], "[SUBMENU_ENTRY]", $this->_opendTemplate[$_curId]['main']);
							} else {
								$this->_opendTemplate[$_curId]['main'] = str_replace($_match[0][$x], "", $this->_opendTemplate[$_curId]['main']);
							}
							if (substr_count($_match[4][$x], '[SUBMENU_ENTRY]') <= 0) {
								$this->_opendTemplate[$_curId]['sub_'.(integer)$_match[2][$x]] = $_match[4][$x]."[SUBMENU_ENTRY]";
							} else {
								$this->_opendTemplate[$_curId]['sub_'.(integer)$_match[2][$x]] = $_match[4][$x];
							}
						}

						// Get all numbers
						$_tmp = array();
						for ($x = 0; $x < count($_match[0]); $x++) {
							array_push($_tmp, (integer)$_match[2][$x]);
						}
						sort($_tmp);

						// Fill out blank entries
						for ($x = 0; $x < $_tmp[count($_tmp)-1]; $x++) {
							if (!array_key_exists('sub_'.$x, $this->_opendTemplate[$_curId])) {
								if ($x == 0) {
									$_entry = '';
									$_keys = array_keys($this->_opendTemplate[$_curId]);
									foreach ($_keys as $_k => $_v) {
										if (substr($_v, 0, 4) == 'sub_') {
											$_entry = $this->_opendTemplate[$_curId][$_v];
											break;
										}
									}
								} else {
									$_entry = $this->_opendTemplate[$_curId]['sub_'.($x-1)];
								}
								$this->_opendTemplate[$_curId]['sub_'.$x] = $_entry;
							}
						}
						ksort($this->_opendTemplate[$_curId]);
					}
				}
			}
		}

	}

	/**
	 * Parse out the AdminMenu Tag from the given Content
	 *
	 * @param string $_content
	 * @param integer $mainMenu
	 * @param boolean $adm
	 */
	private function parseAdminTemplate($_content, $mainMenu, $adm=false) {
		//if (count($this->_adminMenuTemplate) <= 0) {
			$match = array();
			$_match = array();
			if (preg_match('/(\[ADMIN_MENU\])(.*?)(\[\/ADMIN_MENU\])/smi', $_content, $match)) {
				$db = pDatabaseConnection::getDatabaseInstance();
				$res = null;
				$baseMenu = 0;

				if ( (preg_match('/(\[SUBMENU\])(.*?)(\[\/SUBMENU\])/smi', $match[2], $_match)) && (!$this->_showNoAdmin) ) {
					$_checkId = $mainMenu;
					do {
						$sql = 'SELECT [men.parent] FROM [table.men] WHERE [men.id]='.(int)$_checkId.";";
						$db->run($sql, $res);
						if ($res->getFirst()) {
							$baseMenu = $_checkId;
							$_checkId = (int)$res->{$db->getFieldName('men.parent')};
							$_parent  = $_checkId;
						} else {
							$_parent  = 0;
							$baseMenu = $_checkId;
						}
					} while ((integer)$_parent > 0);

					if ($this->_minParent == 0) {
						$baseMenu = 0;
					}

					$this->_adminMenuTemplate[0] = str_replace($_match[0], '[ADMIN_MENU]', $match[2]);
					$this->_adminMenuTemplate[0] = str_replace('[ADMIN_MENU_ID]', $mainMenu, $this->_adminMenuTemplate[0]);
					$this->_adminMenuTemplate[0] = str_replace('[ADMIN_MENU_BASEID]', $baseMenu, $this->_adminMenuTemplate[0]);
					$this->_adminMenuTemplate[1] = $_match[2];
				} else {
					$this->_adminMenuTemplate = '';
				}
			}
		//}

		if ($adm) {
			// Get AdminStyleMenu templates
			if ( (preg_match_all('/(\[MENU_)(\d+)(_admin\])(.*)(\[\/MENU_\\2_admin\])/smi', $_content, $match)) && (!$this->_showNoAdmin) ) {
				for ($i = 0; $i < count($match[0]); $i++) {
					$this->_adminTemplate[(integer)$match[2][$i]]['main'] = $match[4][$i];
					if (preg_match_all('/(\[SUBMENU\:)([\d]+)(\])(.*)(\[\/SUBMENU\:\\2\])/smi', $match[4][$i], $_match)) {
						for ($x = 0; $x < count($_match[0]); $x++) {
							if (substr_count($this->_adminTemplate[(integer)$match[2][$i]]['main'], '[SUBMENU_ENTRY]') <= 0) {
								$this->_adminTemplate[(integer)$match[2][$i]]['main'] = str_replace($_match[0][$x], '[SUBMENU_ENTRY]', $this->_adminTemplate[(integer)$match[2][$i]]['main']);
							} else {
								$this->_adminTemplate[(integer)$match[2][$i]]['main'] = str_replace($_match[0][$x], '', $this->_adminTemplate[(integer)$match[2][$i]]['main']);
							}
							$this->_adminTemplate[(integer)$match[2][$i]]['sub_'.(integer)$_match[2][$x]] = str_replace('[SUBMENU_ENTRIES]', '[SUBMENU_ENTRY]', $_match[4][$x]);
						}
					}
				}
			}
		}

	}

	/**
	 * Create the Submenu from _minParent until _maxParent
	 *
	 * @param boolean $adm If this is an Administration-Menu or not
	 */
	protected function createSubMenu($adm=false) {
		$lang = pMessages::getLanguageInstance()->getLanguage();
		$userCheck = pCheckUserData::getInstance();

		// Get AdminMenu templates (Administration-Functions)
		$this->parseAdminTemplate($this->_content, $this->mainMenu, $adm);

		// Parse the Menu-Template
		$this->parseTemplate();

		// Create the Menu
		if (array_key_exists($this->_currentLevel, $this->_btArray)) {
			$_curId = $this->_btArray[$this->_currentLevel];
			$menu = $this->_sub_createSubMenuStructure($_curId, $adm);
		} else {
			$menu = "";
		}

		// Add the Admin-Menu if the User has access
		if ( $userCheck->checkAccess('menu') && (!$this->_showNoAdmin) ) {
			$admMenu = $this->_createAdminMenu();
			$menu = $menu.$admMenu;
		}

		// Replace the Menu in content
		$this->_content = str_replace("[SUBMENU_ENTRIES]", $menu, $this->_content);

		// Cut out some Administration-tags
		if ($userCheck->checkAccess('menu') && (!$this->_showNoAdmin)) {
			$this->_content = preg_replace('/(\[ADMIN_FUNCTIONS_CUT\])(.*?)(\[\/ADMIN_FUNCTIONS_CUT\])/smi', '\\2', $this->_content);
			$this->_content =  preg_replace('/(\[ADMIN_REMOVE\])(.*?)(\[\/ADMIN_REMOVE\])/smi', '\\2', $this->_content);
		} else {
			$this->_content = preg_replace('/(\[ADMIN_FUNCTIONS_CUT\])(.*?)(\[\/ADMIN_FUNCTIONS_CUT\])/smi', '', $this->_content);
			$this->_content =  preg_replace('/(\[ADMIN_REMOVE\])(.*?)(\[\/ADMIN_REMOVE\])/smi', '', $this->_content);
		}
		$this->_content = str_replace('[LANG_SHORT]', $lang->short, $this->_content);
	}

	/**
	 * Create all Submenu entries recursive
	 *
	 * @param int $_curId the current MenuID to get all Submenus from
	 * @param boolean $adm TRUE for the AdminMenu
	 * @return string
	 */
	protected function _sub_createSubMenuStructure($_curId, $adm=false) {
		$lang = pMessages::getLanguageInstance();
		if ($this->_currentLinkLevel <= (int)MAX_LINK_LEVEL) {
			$db = pDatabaseConnection::getDatabaseInstance();
			$userCheck = pCheckUserData::getInstance();
			$_idList = $this->getMenuParentId($_curId);
			$_tplLevel = 0;
			$_selectedLinksString = implode('/_', $this->_selectedLinks);

			$menu = '';
			for ($i = 0; $i < count($_idList); $i++) {
				$menuId = $_idList[$i];

				// Deprecated, use $menuEntry instead of $_menuValues
				$_isLink = $this->checkLinkMenu($menuId);
				/*if ($_isLink > 0) {
					$_menuValues = $this->getMenuValues($_isLink);
				} else {
					$_menuValues = $this->getMenuValues($menuId);
				}*/

				// Use this instead of $_menuValues
				$menuEntry = new pMenuEntry($menuId);

				// $userCheck->checkAccess('menu') && (!$this->_showNoAdmin)
				if ( ($menuEntry->translated && $menuEntry->active) || $adm || ($userCheck->checkAccess('menu') && !$this->_showNoAdmin) ) {
					// Check if the Admin-Menu should be showed
					if ($adm && (!$this->_showNoAdmin)) {

						// Get the ID for AdminTemplate
						$keys = array_keys($this->_adminTemplate);
						if (in_array(($i + (int)$this->_currentLevel), $keys)) {
							$tplId = $keys[array_search(($i + (int)$this->_currentLevel), $keys)];

						} else {
							$tplId = $keys[count($keys)-1];
						}

						// get the MAIN OpenTemplate if it is the first level
						if ($this->_currentLevel <= $this->_minParent) {
							$menu_tmp = $this->_adminTemplate[$tplId]['main'];
						} else {
							// Get the Submenu from OpenTemplate
							if ($adm) {
								$__keys = array_keys((array)$this->_adminTemplate[$tplId]);
							}
							if (in_array('sub_'.($this->_currentLevel - 1), $__keys)) {
								$menu_tmp = $this->_adminTemplate[$tplId]['sub_'.($this->_currentLevel - 1)];
							} else {
								$menu_tmp = $this->_adminTemplate[$tplId][$__keys[count($__keys)-1]];
							}
							unset($__keys);
						}

					} else {
						// Check for an open menu
						if (in_array($menuId, $this->_btArray) || ($this->_currentLevel > $this->_minParent) || $this->_showAll) {
							// Check if this Menu should really be showed or not
							if ($this->_showAll && ($this->_currentLevel >= $this->_hideLowerLevels) && !in_array($_curId, $this->_btArray)) {
								continue;
							}

							// Get the ID for OpenTemplate
							$keys = array_keys($this->_opendTemplate);
							if (in_array(($i + (int)$this->_currentLevel + (int)$this->_tplOffset), $keys)) {
								$tplId = $keys[array_search(($i + (int)$this->_currentLevel + (int)$this->_tplOffset), $keys)];

							} else if (in_array(($i + (int)$this->_currentLevel), $keys)) {
								$tplId = $keys[array_search(($i + (int)$this->_currentLevel), $keys)];

							} else {
								if (array_key_exists(count($keys)-1, $keys)) {
									$tplId = $keys[count($keys)-1];
								} else if (array_key_exists(0, $keys)) {
									$tplId = $keys[0];
								} else {
									$tplId = '0';
								}
							}

							// get the MAIN OpenTemplate if it is the first level
							if (array_key_exists($tplId, $this->_opendTemplate)) {
								if ($this->_currentLevel <= $this->_minParent) {
									$menu_tmp = $this->_opendTemplate[$tplId]['main'];
								} else {
									// Get the Submenu from OpenTemplate
									$__keys = array_keys((array)$this->_opendTemplate[$tplId]);
									if (in_array('sub_'.($this->_currentLevel - 1), $__keys)) {
										$_tplLevel = (int)($this->_currentLevel - 1);
									} else {
										$_tplLevel = (int)str_replace("sub_", "", $__keys[count($__keys)-1]);
									}
									$menu_tmp = $this->_opendTemplate[$tplId]['sub_'.$_tplLevel];
									unset($__keys);
								}
							} else {
								$menu_tmp = '';
							}
						} else {
							// Get the ClosedTemplate
							$keys = array_keys($this->_closedTemplate);
							$kidx = (($i%count($keys))+1);
							if (in_array($kidx, $keys)) {
								$menu_tmp = $this->_closedTemplate[$keys[array_search($kidx, $keys)]];
							} else {
								$menu_tmp = $this->_closedTemplate[$keys[0]];
							}
						}
					}

					// Replace MenuValues in current Menu
					$menu_tmp = str_replace('[MENU_TITLE]',    $menuEntry->text, $menu_tmp);
					$menu_tmp = str_replace('[SUBMENU_TITLE]', $menuEntry->text, $menu_tmp);
					$menu_tmp = str_replace('[MENU_SHORT]',    $menuEntry->trans_short, $menu_tmp);

					// Replace extended Attributes
					$menu_tmp = str_replace('[SITE_TITLE]',       $menuEntry->title, $menu_tmp);
					$menu_tmp = str_replace('[SITE_DESCRIPTION]', $menuEntry->description, $menu_tmp);
					$menu_tmp = str_replace('[SITE_KEYWORDS]',    $menuEntry->keywords, $menu_tmp);

					// Menu Images
					$menu_tmp = str_replace('[MENU_IMAGE_ID]', $menuEntry->image_id, $menu_tmp);
					$match = array();
					if (preg_match_all('/\[MENU_IMAGE:(\d+)x(\d+)(:(true|false))?\]/smi', $menu_tmp, $match, PREG_SET_ORDER)) {
						foreach ($match as $img) {
							$menuEntry->setImageSize((int)$img[1], (int)$img[2]);
							$img_src = $menuEntry->image;
							if ( ($img[4] == 'false') && (substr_count($img_src, 'about:blank') > 0) ) $img_src = '';
							$menu_tmp = str_replace($img[0], $img_src, $menu_tmp);
						}
					}
					if (preg_match_all('/\[MENU_IMAGE_URL:(\d+)x(\d+)(:(true|false))?\]/smi', $menu_tmp, $match, PREG_SET_ORDER)) {
						foreach ($match as $img) {
							$menuEntry->setImageSize((int)$img[1], (int)$img[2]);
							$img_src = $menuEntry->image_url;
							if ( ($img[4] == 'false') && (substr_count($img_src, 'about:blank') > 0) ) $img_src = '';
							$menu_tmp = str_replace($img[0], $img_src, $menu_tmp);
						}
					}

					// Check if there should be inserted an AdminLink-Parameter
					if (!empty($this->_insertAdminLink)) {
						$menu_tmp = str_replace('[SUBMENU_LINK]', $this->_insertAdminLink.$menuId, $menu_tmp);
					} else {
						$menu_tmp = str_replace('[SUBMENU_LINK]', $menuEntry->link, $menu_tmp);
						/*
						// Create the MenuLink
						if (!empty($_menuValues[$db->getFieldName('men.short')]) && ($_menuValues[$db->getFieldName('men.short')] != '0')) {
							$_menuLink = $this->urlEncode($_menuValues[$db->getFieldName('men.short')]);
						} else {
							$_menuLink = $menuId;
						}

						// If the ShortLink begins with "http" or "ftp", we use this as Link
						if ((substr($_menuLink, 0, 4) == 'http') || (substr($_menuLink, 0, 3) == 'ftp')) {
							// Replace the MenuLink
							$menu_tmp = str_replace('[SUBMENU_LINK]', $_menuValues[$db->getFieldName('men.short')], $menu_tmp);
						} else {
							// Check vor a LINK-Menu
							if ($_isLink > 0) {
								$this->_currentLink .= '_';
							}
							//if ($this->_currentLinkLevel > 0) {
								//if ($_isLink > 0) {
								//	$_menuLink = $this->_currentLink.$menuId;
								//} else {
									$_menuLink = $this->_currentLink.$_menuLink;
								//}
							//} else if ( ($this->_currentLinkLevel <= 0) && ($_isLink > 0) ) {
							//	$_menuLink = $this->_currentLink.$menuId;
							//}

							// Replace the MenuLink
							$menu_tmp = str_replace('[SUBMENU_LINK]', '/'.$lang->getShortLanguageName().'/'.$_menuLink, $menu_tmp);
						}*/

					}

					// Check for Admin-Functions
					if ($adm && (!$this->_showNoAdmin)) {
						$menu_tmp = str_replace('[SUBMENU_ID_ADMIN]',        $menuId, $menu_tmp);
						$menu_tmp = str_replace('[SUBMENU_PARENT_ID_ADMIN]', $_curId, $menu_tmp);
					}

					// Replace [color:NAME:X:Y]-Values
					$_colKeys = array_keys($this->_colorValues);
					for ($y = 0; $y < count($_colKeys); $y++) {
						// Get initial Values
						$_colName = $_colKeys[$y];
						$_colFrom = $this->_colorValues[$_colName][0];
						$_colTo   = $this->_colorValues[$_colName][1];

						// Check for values in $menu_tmp
						if (substr_count($menu_tmp, $_colName) > 0) {
							// Calculate the new Value
							$_div = abs( (integer)$_colFrom - (integer)$_colTo );
							if (count($_idList) > 1) {
								$_new = ( ( $_div / (count($_idList)-1) ) * $i );
							} else {
								$_new = ($_div * $i);
							}
							$_new = $_colTo > $_colFrom ? $_colFrom + $_new : $_colFrom - $_new;
							$menu_tmp = str_replace("[".$_colName."]", round($_new), $menu_tmp);
						}
					}

					// Replace [LEVEL_INSERT:"level":"char":"method"] --> method can be "r"epeate, "m"ultiplicate
					if (substr_count($menu_tmp, '[LEVEL_INSERT:') > 0) {
						$match = array();
						if (preg_match_all('/(\[LEVEL_INSERT:")([^"]+)(":")([^"]+)(":")(r|R|m|M)("\])/smi', $menu_tmp, $match)) {
							for ($y = 0; $y < count($match[0]); $y++) {
								// 2 -> level / 4 -> char / 6 -> methode
								if (($this->_currentLevel + $this->_tplOffset) >= (integer)$match[2][$y]) {
									switch (strToUpper($match[6][$y])) {
										case 'R':
											$rep = '';
											for ($r = (integer)$match[2][$y]; $r <= ($this->_currentLevel + $this->_tplOffset); $r++) {
												$rep .= $match[4][$y];
											}
											$menu_tmp = str_replace($match[0][$y], $rep, $menu_tmp);
											break;
										case 'M':
											$rep = ( ( ($this->_currentLevel + $this->_tplOffset) - (integer)$match[2][$y] + 1 ) * (integer)$match[4][$y] );
											$menu_tmp = str_replace($match[0][$y], $rep, $menu_tmp);
											break;
										default:
											$menu_tmp = str_replace($match[0][$y], "", $menu_tmp);
											break;
									}
								} else {
									$menu_tmp = str_replace($match[0][$y], "", $menu_tmp);
								}
							}
						}
					}

					// Replace [SELECTED:"if_selected":"if_not_selected"]
					if (substr_count($menu_tmp, '[SELECTED:') > 0) {
						if (preg_match_all('/(\[SELECTED:")([^"]+)?(((":")([^"]+))?)("\])/smi', $menu_tmp, $match)) {
							for ($y = 0; $y < count($match[0]); $y++) {
								// echo $_menuLink." -- _".$_selectedLinksString."<br>";
								// 2 -> selected -- 6 -> not_selected
								if ( ((int)$menuId == (int)$this->_selected) ) { // && ( (($this->_currentLinkLevel <= 0) && (strlen($_selectedLinksString) <= 0)) || ($_menuLink == '_'.$_selectedLinksString) ) ) {
									$menu_tmp = str_replace($match[0][$y], $match[2][$y], $menu_tmp);
								} else {
									$menu_tmp = str_replace($match[0][$y], $match[6][$y], $menu_tmp);
								}
							}
						}
					}

					// Replace [NOT_SELECTED:"if_not_selected"]
					if (substr_count($menu_tmp, '[NOT_SELECTED:') > 0) {
						if (preg_match_all('/(\[NOT_SELECTED:")(.*?)("\])/smi', $menu_tmp, $match, PREG_SET_ORDER)) {
							for ($y = 0; $y < count($match); $y++) {
								if ( ((int)$menuId == (int)$this->_selected) ) { // && ( (($this->_currentLinkLevel <= 0) && (strlen($_selectedLinksString) <= 0)) || ($_menuLink == '_'.$_selectedLinksString) ) ) {
									$menu_tmp = str_replace($match[$y][0], "", $menu_tmp);
								} else {
									$menu_tmp = str_replace($match[$y][0], $match[$y][2], $menu_tmp);
								}
							}
						}
					}

					// Replace [IN_MENU_BACKTRACE:"if_in":"if_not_in"]
					if (substr_count($menu_tmp, '[IN_MENU_BACKTRACE:') > 0) {
						if (preg_match_all('/(\[IN_MENU_BACKTRACE:")([^"]+)?(((":")([^"]+))?)("\])/smi', $menu_tmp, $match)) {
							for ($y = 0; $y < count($match[0]); $y++) {
								//echo $_menuLink." -- _".$_selectedLinksString."<br>";
								// 2 -> selected -- 6 -> not_selected
								if ( in_array((int)$menuId, $this->_btArray) ) { // && ( (($this->_currentLinkLevel <= 0) && (strlen($_selectedLinksString) <= 0)) || ($_menuLink == '_'.$_selectedLinksString) ) ) {
									$menu_tmp = str_replace($match[0][$y], $match[2][$y], $menu_tmp);
								} else {
									$menu_tmp = str_replace($match[0][$y], $match[6][$y], $menu_tmp);
								}
							}
						}
					}

					// Replace [NOT_IN_SELECTED_MENU:"if_not_selected"]
					if (substr_count($menu_tmp, '[NOT_IN_MENU_BACKTRACE:') > 0) {
						if (preg_match_all('/(\[NOT_IN_MENU_BACKTRACE:")(.*?)("\])/smi', $menu_tmp, $match, PREG_SET_ORDER)) {
							for ($y = 0; $y < count($match); $y++) {
								if ( in_array((int)$menuId, $this->_btArray) ) { // && ( (($this->_currentLinkLevel <= 0) && (strlen($_selectedLinksString) <= 0)) || ($_menuLink == '_'.$_selectedLinksString) ) ) {
									$menu_tmp = str_replace($match[$y][0], "", $menu_tmp);
								} else {
									$menu_tmp = str_replace($match[$y][0], $match[$y][2], $menu_tmp);
								}
							}
						}
					}

					// Check for inserting if this is the first entry
					if (preg_match_all('/(\[IF_FIRST:")(.*?)("(:"(.*?)")?\])/smi', $menu_tmp, $match)) {
						for ($y = 0; $y < count($match[0]); $y++) {
							if ( ($i == 0)) {// && (($this->_currentLevel - 0) == $_tplLevel) ) {
								$menu_tmp = str_replace($match[0][$y], $match[2][$y], $menu_tmp);
							} else {
								$menu_tmp = str_replace($match[0][$y], $match[5][$y], $menu_tmp);
							}
						}
					}

					// Check for inserting if this is the last entry
					if (preg_match_all('/(\[IF_LAST:")(.*?)("(:"(.*?)")?\])/smi', $menu_tmp, $match)) {
						for ($y = 0; $y < count($match[0]); $y++) {
							if ( ($i == (count($_idList) - 1)) ) {//&& (($this->_currentLevel - $this->_minParent) == $_tplLevel) ) {
								$menu_tmp = str_replace($match[0][$y], $match[2][$y], $menu_tmp);
							} else{
								$menu_tmp = str_replace($match[0][$y], $match[5][$y], $menu_tmp);
							}
						}
					}

					if ($_isLink > 0) {
						$menu_tmp = preg_replace('/(\[LINK_REMOVE\])(.*?)(\[\/LINK_REMOVE\])/smi', '', $menu_tmp);
					} else {
						$menu_tmp = preg_replace('/(\[LINK_REMOVE\])(.*?)(\[\/LINK_REMOVE\])/smi', '\\2', $menu_tmp);
					}

					// Get the Submenu-Entries
					if ( ($this->_showAll || (in_array($menuId, $this->_btArray) || $adm) ) && ($this->_currentLevel < $this->_maxParent) ) {
						// Get the LINK Submenus
						if ($_isLink > 0) {
							$this->_currentLinkLevel++;
							$this->_currentLevel++;
							//$this->_currentLink .= $menuId.'/';
							$this->_currentLink .= $menuEntry->short.'/';
							$tmpSub = $this->_sub_createSubMenuStructure($_isLink, $adm);
							if (strlen($tmpSub) > 0) {
								$menu_tmp = str_replace('[SUBMENU_ENTRY]', $tmpSub, $menu_tmp);
								$menu_tmp = str_replace('[NO_SUBMENU]', '', $menu_tmp);
								$menu_tmp = str_replace('[/NO_SUBMENU]', '', $menu_tmp);
							} else {
								$menu_tmp = preg_replace('/(\[NO_SUBMENU\])(.*?)(\[\/NO_SUBMENU\])/smi', '', $menu_tmp);
								$menu_tmp = str_replace("[SUBMENU_ENTRY]", "", $menu_tmp);
							}
							$this->_currentLink = substr_replace($this->_currentLink, '', strrpos($this->_currentLink, '/_')+1);
							//$this->_currentLink = substr_replace($this->_currentLink, '', (strlen($this->_currentLink) - strlen('_'.$menuId.'/')), strlen('_'.$menuId.'/'));
							$this->_currentLevel--;
							$this->_currentLinkLevel--;

						} else {
							// Get the real Submenus
							$this->_currentLevel++;
							$tmpSub = $this->_sub_createSubMenuStructure($menuId, $adm);
							$this->_currentLevel--;

							if (strlen($tmpSub) > 0) {
								$menu_tmp = str_replace('[SUBMENU_ENTRY]', $tmpSub, $menu_tmp);
								$menu_tmp = str_replace('[NO_SUBMENU]', '', $menu_tmp);
								$menu_tmp = str_replace('[/NO_SUBMENU]', '', $menu_tmp);
							} else {
								$menu_tmp = preg_replace('/(\[NO_SUBMENU\])(.*?)(\[\/NO_SUBMENU\])/smi', '', $menu_tmp);
								$menu_tmp = str_replace('[SUBMENU_ENTRY]', '', $menu_tmp);
							}
						}
					} else {
						// needed to get only the first XX Menus
						if (CMS_SMALL) {
							$this->_currentLevel++;
							$tmpSub = $this->_sub_createSubMenuStructure($menuId, $adm);
							$tmpSub = '';
							$this->_currentLevel--;
						}
						$menu_tmp = str_replace("[SUBMENU_ENTRY]", "", $menu_tmp);
						$menu_tmp = preg_replace('/(\[NO_SUBMENU\])(.*?)(\[\/NO_SUBMENU\])/smi', '', $menu_tmp);
					}

					// Append MENU_ACCESS_GROUPS if the current Menu is Protected
					$accessCheck = new pMenuEntry($menuId);
					$accessGroups = $accessCheck->getAccessGroups(true);
					if (!empty($accessGroups) && !empty($menu_tmp) && !$userCheck->checkAccess('menu')) {
						$accessString = '';
						foreach ($accessGroups as $a) {
							$accessString .= (empty($accessString) ? '' : ',').$a->id;
						}
						$menu_tmp = '[MENU_ACCESS_GROUPS_'.$menuId.':'.$accessString.']'.$menu_tmp.'[/MENU_ACCESS_GROUPS_'.$menuId.']';
					}

					// Administrational content
					$menu_tmp = str_replace('[ADMINID]', ' id="admmenu_'.$accessCheck->id.'"', $menu_tmp);
					if ($userCheck->checkAccess('menu')) {
						$admin  = '<div id="admin_menu_'.$menuId.'" class="admin_text" style="display:none;">';
						$admin .= '<div class="admin_entry admin_menuhide">'.$lang->getValue('', 'text', 'input_014').'</div>';
						$admin .= '<div class="admin_entry admin_menuedit">'.$lang->getValue('', 'text', 'input_008').'</div>';
						$admin .= '<div class="admin_entry admin_menudelete">'.$lang->getValue('', 'text', 'input_005').'</div>';
						$admin .= '<div class="admin_entry admin_moveup">'.$lang->getValue('', 'text', 'input_011').'</div>';
						$admin .= '<div class="admin_entry admin_movedown">'.$lang->getValue('', 'text', 'input_012').'</div>';
						$admin .= '<div class="admin_entry admin_menucreate">'.$lang->getValue('', 'text', 'input_013').'</div>';
						$admin .= '</div>';
						$admin .= '<script type="text/javascript">if (!document.menuAdminMenu'.$menuId.') document.menuAdminMenu'.$menuId.'=new AdminMenuClass('.$menuId.',\'menu\');</script>';
						$menu_tmp = str_replace('[ADMIN_MENU]', $admin, $menu_tmp);
					} else {
						$menu_tmp = str_replace('[ADMIN_MENU]', '', $menu_tmp);
					}

					// DEPRECATED - Use [ADMINID] and [ADMIN_MENU] - don't use [ADMIN_FUNCTIONS] any longer
					if ($userCheck->checkAccess('menu') && (!$this->_showNoAdmin) && ($this->_currentLinkLevel < 1)) { //&& ((integer)$menuId == (integer)$this->_selected))
						$menu_tmp = preg_replace('/(\[ADMIN_REMOVE\])(.*?)(\[\/ADMIN_REMOVE\])/smi', '\\2', $menu_tmp);
						$menu_tmp = preg_replace('/(\[ADMIN_FUNCTIONS\])(.*?)(\[\/ADMIN_FUNCTIONS\])/smi', '\\2', $menu_tmp);
						// Check if the Menu should be sowed as Untranslated
						if (!$menuEntry->translated) {
							$menu_tmp = preg_replace('/(\[MENU_NOT_TRANSLATED:\")(.*?)(\"\])/smi', '\\2', $menu_tmp);
						}
						// Check if the Menu should be sowed as NOT-VISIBLE
						if (!$menuEntry->active) {
							$menu_tmp = preg_replace('/(\[MENU_NOT_VISIBLE:")(.*?)((":"(.*?))?)("\])/smi', '\\2', $menu_tmp);
						} else {
							$menu_tmp = preg_replace('/(\[MENU_NOT_VISIBLE:")(.*?)((":"(.*?))?)("\])/smi', '\\5', $menu_tmp);
						}
					} else {
						$menu_tmp = preg_replace('/(\[ADMIN_REMOVE\])(.*?)(\[\/ADMIN_REMOVE\])/smi', '', $menu_tmp);
						$menu_tmp = preg_replace('/(\[ADMIN_FUNCTIONS\])(.*?)(\[\/ADMIN_FUNCTIONS\])/smi', '', $menu_tmp);
						$menu_tmp = preg_replace('/(\[MENU_NOT_TRANSLATED:)(.*?)(\])/smi', '', $menu_tmp);
						$menu_tmp = preg_replace('/(\[MENU_NOT_VISIBLE:")(.*?)((":"(.*?))?)("\])/smi', '', $menu_tmp);
						//$menu_tmp = preg_replace('/(\[MENU_NOT_TRANSLATED:)(.*?)(\])/smi', '', $menu_tmp);
					}

					$menu_tmp = preg_replace('/(\[MENU_NOT_TRANSLATED:")(.*?)("\])/smi', '', $menu_tmp);
					$menu_tmp = preg_replace('/(\[MENU_NOT_VISIBLE:")(.*?)((":"(.*?))?)(\"\])/smi', '', $menu_tmp);
					$menu_tmp = str_replace('[ADMIN_MENU_ID]', $accessCheck->id, $menu_tmp);
					$menu_tmp = str_replace('[MENU_ID]', $accessCheck->id, $menu_tmp);

					// Apply the temporary MenuEnrty to the final-Menu
					$menu .= $menu_tmp;
				}
			}
		} else {
			$menu = '';
		}
		return $menu;
	}

	/**
	 * Return a List with all Child-MenuID's this menu has
	 *
	 * @param integer $id All Childs from this MenuID
	 * @return array A list with all IDs
	 */
	protected function getMenuParentId($id) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$back = array();

		$sql  = "SELECT [men.id] FROM [table.men] WHERE [men.parent]=".(int)$id." ORDER BY [men.pos];";
		$db->run($sql, $res);
		if ($res->getFirst()) {
			while ($res->getNext()) {
				$this->numMenuEntries++;
				if (!CMS_SMALL || (CMS_SMALL && ($this->numMenuEntries <= $this->maxMenuEntries))) {
					array_push($back, $res->{$db->getFieldName('men.id')});
				} else {
					break;
				}
			}
		}
		$res = null;
		return $back;
	}

	/**
	 * Check if the current MenuID is a LINK
	 *
	 * @param integer $id The MenuID to check if it's a LINK or a normal menu
	 * @return integer The MenuID this LinkMenu points to
	 */
	protected function checkLinkMenu($id) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$back = 0;

		$sql  = "SELECT [men.link] FROM [table.men] WHERE [men.id]=".(int)$id.";";
		$db->run($sql, $res);

		if ($res->getFirst()) {
			$back = (int)$res->{$db->getFieldName('men.link')};
		}
		return $back;
	}

	/**
	 * Check if the current Menu is based on links or if it's a real Submenu
	 *
	 * @param string $linkName
	 */
	protected function parseLinkMenus($linkName="") {
		if (strlen($linkName) <= 0) {
			$linkName = pURIParameters::get('sm', $linkName, pURIParameters::$STRING);
		}
		if (strlen($linkName) <= 0) {
			$linkName = pURIParameters::get('m', $linkName, pURIParameters::$STRING);
		}
		$tmp = explode('/_', $linkName);
		//$tmp[0] = substr($tmp[0], 1, (strlen($tmp[0]) - 1) );
		//if (count($this->_selectedLinks) <= 0)
		$this->_selectedLinks = $tmp;
	}

	/**
	 * Encodes a Section into a valid URL-Part
	 *
	 * @param string $str Section to encode into a valid URL-Part
	 * @return The URL-Encoded String $str
	 * @access private
	 */
	protected function urlEncode($str) {
		$replace = array('�', '�', '�', '�', '�', '�', ' ');
		$values = array('ae','oe','ue','Ae','Oe','Ue', '%20');
		$str = str_replace($replace, $values, $str);
		$str = preg_replace('/[^a-zA-Z0-9_\/-]/smi', '_', $str);
		$str = preg_replace('/[_]+/smi', '_', $str);
		return $str;
	}

	// Check for a ShortMenu through an MenuID
	function getShortMenuValue($sm) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$back = 0;

		$sql  = "SELECT [men.short] FROM [table.men] WHERE [men.id]=".(int)$sm.";";
		$db->run($sql, $res);
		if ($res->getFirst()) {
			$back = $res->{$db->getFieldName('men.short')};
		}
		return $back;
	}

	/**
	 * Return all Values from a Menu
	 *
	 * @param integer $id The MenuID to get the values from
	 * @return array An Array with all needed values
	 */
	protected function getMenuValues($id) {
		$lang = pMessages::getLanguageInstance();
		$db = pDatabaseConnection::getDatabaseInstance();
		$res1 = null;
		$res2 = null;
		$return = '';

		$sql  = "SELECT [mtx.text],[mtx.active],[men.short] FROM [table.mtx],[table.men],[table.lan]";
		$sql .= " WHERE (([mtx.menu]=[men.id] AND [men.id]=".(int)$id.")";
		$sql .= " OR ([mtx.menu]=[men.link] AND [men.link]=".(int)$id.")) AND [mtx.lang]=[lan.id]";
		$sql1 = $sql." AND [lan.short]='".$lang->getShortLanguageName()."';";
		$sql2 = $sql." AND [lan.short]='".MASTER_LANGUAGE."';";
		$db->run($sql1, $res1);
		$db->run($sql2, $res2);

		if ($res1->getFirst()) {
			$return = array();
			$return[$db->getFieldName('mtx.text')]   = $res1->{$db->getFieldName('mtx.text')};
			$return[$db->getFieldName('mtx.active')] = $res1->{$db->getFieldName('mtx.active')};
			$return[$db->getFieldName('men.short')]  = $res1->{$db->getFieldName('men.short')};
			$return['translated'] = true;

		} else if ($res2->getFirst()) {
			$return = array();
			$return[$db->getFieldName('mtx.text')]   = $res2->{$db->getFieldName('mtx.text')};
			$return[$db->getFieldName('mtx.active')] = $res2->{$db->getFieldName('mtx.active')};
			$return[$db->getFieldName('men.short')]  = $res2->{$db->getFieldName('men.short')};
			$return['translated'] = false;

		} else {
			$_txt = $lang->getValue('', 'menu', 'mainmenu_text_'.$id);
			$return = array($db->getFieldName('mtx.text') => '', $db->getFieldName('mtx.active') => 1, $db->getFieldName('men.short') => '');
			if ($_txt != "mainmenu_text_".$id) {
				$return[$db->getFieldName('mtx.text')] = $_txt;
				$return['translated'] = true;
			} else {
				$return['translated'] = false;
			}
		}
		return $return;
	}

	// Return all available Administration-Menus
	private function _createAdminMenu() {
		global $_ADITIONAL_PLUGINS;
		$lang = pMessages::getLanguageInstance();
		$userCheck = pCheckUserData::getInstance();

		$_admIndex = array();
		$_html = '';

		$consts = get_defined_constants(true);
		foreach ($consts['user'] as $k => $v) {
			if (substr(strtoupper($k), 0, 4) == "ADM_") {
				$_admIndex[$v] = $k;
			}
		}

		// Add all default plugins
		for ($i = 1000; $i <= 10000; ($i+=100)) {
			if ( (array_key_exists($i, $_admIndex)) && $userCheck->checkAccess($i) ) {
				$menuText = $lang->getValue('', 'menu', $i);

				// Add the Image if there is one
				if (file_exists(ABS_DATA_DIR.'admin_images'.DIRECTORY_SEPARATOR.'menu_'.$i.'.png')) {
					$menuText = '<img src="'.DATA_DIR.'admin_images/menu_'.$i.'.png" style="vertical-align:middle;width:22px;height:22px;margin:3px 10px 3px 3px;" />'.$menuText;
				}

				if (strlen($menuText) > 0) {
					if (is_array($this->_adminMenuTemplate) && array_key_exists(1, $this->_adminMenuTemplate)) {
						$_tmp = $this->_adminMenuTemplate[1];
					} else {
						$_tmp = '<a href="[SUBMENU_LINK]">[SUBMENU_TITLE]</a>';
					}
					$_tmp = str_replace('[SUBMENU_LINK]', 'javascript:openAdmin('.$i.');', $_tmp);
					$_tmp = str_replace('[SUBMENU_TITLE]', $menuText, $_tmp);
					$_tmp = preg_replace('/(\[)(.*?)(\])/smi', '', $_tmp);
					$_html .= $_tmp;
				}
			}
		}

		// Add all Aditional Plugins
		foreach ($_ADITIONAL_PLUGINS as $k => $v) {
			if ( $userCheck->checkAccess($k) ) {
				$menuText = $lang->getValue('', 'menu', $k);

				// Add the Image if there is one
				if (file_exists(ABS_DATA_DIR.'admin_images'.DIRECTORY_SEPARATOR.'menu_'.$v.'.png')) {
					$menuText = '<img src="'.DATA_DIR.'admin_images/menu_'.$v.'.png" style="vertical-align:middle;width:22px;height:22px;margin:3px 10px 3px 3px;" />'.$menuText;
				}

				if (strlen($menuText) > 0) {
					if (is_array($this->_adminMenuTemplate) && array_key_exists(1, $this->_adminMenuTemplate)) {
						$_tmp = $this->_adminMenuTemplate[1];
					} else {
						$_tmp = '<a href="[SUBMENU_LINK]">[SUBMENU_TITLE]</a>';
					}
					$_tmp = str_replace('[SUBMENU_LINK]', 'javascript:openAdmin('.$v.');', $_tmp);
					$_tmp = str_replace('[SUBMENU_TITLE]', $menuText, $_tmp);
					$_tmp = preg_replace('/(\[)(.*?)(\])/smi', '', $_tmp);
					$_html .= $_tmp;
				}
			}
		}

		if ($userCheck->checkAccess('menu')) {
			$this->_adminMenuTemplate[0] = @preg_replace('/(\[CAT_NOMENU\])(.*?)(\[\/CAT_NOMENU\])/smi', '\\2', trim($this->_adminMenuTemplate[0]));
		} else {
			$this->_adminMenuTemplate[0] = @preg_replace('/(\[CAT_NOMENU\])(.*?)(\[\/CAT_NOMENU\])/smi', '', trim($this->_adminMenuTemplate[0]));
		}
		return str_replace('[ADMIN_MENU]', $_html, $this->_adminMenuTemplate[0]);
	}

	/**
	 * This function creates all required Tables, Updates, Inserts and Deletes.
	 *
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
			// Create the Menu-Table
			$sql  = 'CREATE TABLE IF NOT EXISTS [table.men] ('.
			' [field.men.id] INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,'.
			' [field.men.parent] INT(10) UNSIGNED NOT NULL DEFAULT 0,'.
			' [field.men.pos] INT(10) UNSIGNED NOT NULL DEFAULT 0,'.
			' [field.men.link] INT(10) UNSIGNED NOT NULL DEFAULT 0,'.
			' [field.men.short] VARCHAR(100) NOT NULL DEFAULT \'\','.
			' PRIMARY KEY ([field.men.id]),'.
			' UNIQUE KEY id ([field.men.id])'.
			' );';
			$db->run($sql, $res);
			$res = null;

			// Create the MenuText-Table
			$sql  = 'CREATE TABLE IF NOT EXISTS [table.mtx] ('.
			' [field.mtx.id] INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,'.
			' [field.mtx.text] VARCHAR(100) NOT NULL DEFAULT \'\','.
			' [field.mtx.menu] INT(10) UNSIGNED NOT NULL DEFAULT 0,'.
			' [field.mtx.lang] INT(10) UNSIGNED NOT NULL DEFAULT 0,'.
			' PRIMARY KEY ([field.mtx.id]),'.
			' UNIQUE KEY id ([field.mtx.id])'.
			' );';
			$db->run($sql, $res);
			$res = null;

			// Create the Text-Table
			$sql  = 'CREATE TABLE IF NOT EXISTS [table.txt] ('.
			' [field.txt.id] INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,'.
			' [field.txt.layout] VARCHAR(150) NOT NULL DEFAULT \'\','.
			' [field.txt.sort] INT(10) UNSIGNED NOT NULL DEFAULT 0,'.
			' [field.txt.text] TEXT NOT NULL DEFAULT \'\','.
			' [field.txt.title] VARCHAR(250) NOT NULL DEFAULT \'\','.
			' [field.txt.menu] INT(10) UNSIGNED NOT NULL DEFAULT 0,'.
			' [field.txt.lang] INT(10) UNSIGNED NOT NULL DEFAULT 0,'.
			' [field.txt.plugin] VARCHAR(50) NOT NULL DEFAULT \'TEXT\','.
			' [field.txt.options] VARCHAR(255) NOT NULL DEFAULT \'\','.
			' PRIMARY KEY ([field.txt.id]),'.
			' UNIQUE KEY [field.txt.id] ([field.txt.id])'.
			' );';
			$db->run($sql, $res);
			$res = null;
		}

		// Insert BaseMenu if not already exists
		$sql = 'SELECT COUNT([men.id]) AS cnt FROM [table.men];';
		$db->run($sql, $res);
		if (!$res->getFirst() || ((int)$res->cnt <= 0)) {
			$sql = 'INSERT INTO [table.men] ([field.men.parent],[field.men.pos],[field.men.link],[field.men.short]) VALUES (0,1,0,\'home\');';
			$db->run($sql, $res);
			$last = $res->getInsertId();
			$res = null;

			// Insert BaseMenutexts
			$sql = 'INSERT INTO [table.mtx] ([field.mtx.text],[field.mtx.menu],[field.mtx.lang]) VALUES (\'Change me\',\''.$last.'\',1);';
			$db->run($sql, $res);
			$res = null;

			// Insert base-languages
			$sql = 'INSERT INTO [table.txt]'.
			' ([field.txt.layout],[field.txt.sort],[field.txt.text],[field.txt.title],[field.txt.menu],[field.txt.lang],[field.txt.plugin],[field.txt.options])'.
			' VALUES (\'plain_text\',1,\'Sample text\',\'Sample title\',\''.$last.'\',1,\'TEXT\',\'#title=default#\');';
			$db->run($sql, $res);
			$res = null;
		}

		// Add a field to enable/disable the whole Menu
		if ($version < 2006042802) {
			$sql = 'ALTER TABLE [table.mtx] ADD COLUMN [field.mtx.active] INT(1) UNSIGNED NOT NULL DEFAULT 1;';
			$db->run($sql, $res);
			$res = null;
		}

		// Add aditional fields to the Menu for META-tags in the HTML-Header (title, keywords and description)
		if ($version < 2007120301) {
			$sql = 'ALTER TABLE [table.mtx] ADD COLUMN [field.mtx.title] VARCHAR(200) NOT NULL DEFAULT \'\';';
			$db->run($sql, $res);
			$res = null;

			$sql = 'ALTER TABLE [table.mtx] ADD COLUMN [field.mtx.description] VARCHAR(200) NOT NULL DEFAULT \'\';';
			$db->run($sql, $res);
			$res = null;

			$sql = 'ALTER TABLE [table.mtx] ADD COLUMN [field.mtx.keywords] TEXT NOT NULL DEFAULT \'\';';
			$db->run($sql, $res);
			$res = null;
		}

		if ($version < 2009071702) {
			// Create the User-Groups-Table
			$sql = 'CREATE TABLE IF NOT EXISTS [table.grp] ('.
			' [field.grp.id] INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,'.
			' [field.grp.name] VARCHAR(50) NOT NULL DEFAULT \'\','.
			' [field.grp.descr] VARCHAR(250) NOT NULL DEFAULT \'\','.
			' PRIMARY KEY ([field.grp.id]),'.
			' UNIQUE KEY id ([field.grp.id])'.
			' );';
			$db->run($sql, $res);
			$res = null;

			$sql = 'CREATE TABLE IF NOT EXISTS [table.usrgrp] ('.
			' [field.usrgrp.user] INT(10) UNSIGNED NOT NULL DEFAULT 0,'.
			' [field.usrgrp.group] INT(10) UNSIGNED NOT NULL DEFAULT 0,'.
			' KEY userid ([field.usrgrp.user])'.
			' );';
			$db->run($sql, $res);
			$res = null;
		}

		if ($version < 2009071703) {
			$sql = 'CREATE TABLE IF NOT EXISTS [table.menugrp] ('.
			' [field.menugrp.menu] INT(10) UNSIGNED NOT NULL DEFAULT 0,'.
			' [field.menugrp.group] INT(10) UNSIGNED NOT NULL DEFAULT 0,'.
			' KEY menuid ([field.menugrp.menu])'.
			' );';
			$db->run($sql, $res);
			$res = null;
		}

		// We had a Bug by an Update so we must recreate all "menu positions" on new Menues
		if ($version < 2009101300) {
			$sql = 'SELECT [men.parent], COUNT([men.parent]) AS cnt FROM [table.men] GROUP BY [men.parent];';
			$db->run($sql, $res);
			if ($res->getFirst()) {
				while ($res->getNext()) {
					$res2 = null;
					if ($res->cnt <= 1) {
						continue;
					}
					$sql2 = 'SELECT [men.id],[men.pos] FROM [table.men] WHERE [men.parent]='.$res->parent.' ORDER BY [men.pos],[men.id];';
					$db->run($sql2, $res2);
					if ($res2->getFirst()) {
						$cnt = 0;
						while ($res2->getNext()) {
							$res3 = null;
							$sql3 = 'UPDATE [table.men] SET [field.men.pos]='.$cnt.' WHERE [field.men.id]='.$res2->id.';';
							$db->run($sql3, $res3);
							$cnt++;
						}
					}
					$res3 = null;
				}
				$res2 = null;
			}
			$res = null;
		}

		if ($version < 2010043001) {
			// Create the Text-Table
			$sql  = 'CREATE TABLE IF NOT EXISTS [table.formular] ('.
			' [field.formular.textid] INT(10) UNSIGNED NOT NULL DEFAULT 0,'.
			' [field.formular.field] VARCHAR(150) NOT NULL DEFAULT \'\','.
			' [field.formular.value] TEXT NOT NULL DEFAULT \'\','.
			' KEY ([field.formular.textid])'.
			' );';
			$db->run($sql, $res);
			$res = null;

			// Go through each Textentry, parse for a "<dedtform ...>" and insert all Attributes into
			// the table above
			$match = array();
			$id = 0;
			$text = '';
			$sql = 'SELECT [txt.id],[txt.text] FROM [table.txt] WHERE [txt.plugin]=\'TEXT\';';
			$db->run($sql, $res);
			if ($res->getFirst()) {
				while ($res->getNext()) {
					$res_t = null;
					$id = $res->{$db->getFieldName('txt.id')};
					$text = $res->{$db->getFieldName('txt.text')};

					$sql_t = 'DELETE FROM [table.formular] WHERE [formular.textid]='.$id.';';
					$db->run($sql_t, $res_t);
					$res_t = null;

					if (preg_match('/(\<dedtform)(.*?)(>)/smi', $text, $match)) {
						// replace the dedtform-Tag
						$text = str_replace($match[0], '', $text);
						$text = str_replace('</dedtform>', '', $text);
						$sql_t = 'UPDATE [table.txt] SET [txt.text]=\''.mysql_real_escape_string($text).'\' WHERE [txt.id]='.$id.';';
						$db->run($sql_t, $res_t);
						$res_t = null;
						$sql_t = '';

						// Parse Attributes
						$attr = preg_split('/[\s,]*\\\"([^\\"]+)\\\"[\s,]*|[\s,]*\\\'([^\']+)\\\'[\s,]*|[\s,]+/', $match[2], -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
						for ($i = 0; $i < count($attr); $i+=2) {
							$name = substr($attr[$i], 0, -1);
							$value = $attr[$i+1];
							$value = str_replace('&#43;', '+', $value);
							$value = str_replace('+',     ' ', $value);
							$value = str_replace('##43;', '+', $value);
							$value = str_replace('##34;', '"', $value);
							$value = str_replace('##39;', '\'', $value);
							$value = str_replace('##10;', '&#10;', $value);
							$value = str_replace('&#10;', chr(10), $value);
							$value = html_entity_decode($value, ENT_NOQUOTES, 'utf-8');

							if (empty($sql_t)) {
								$sql_t = 'INSERT INTO [table.formular] ([field.formular.textid],[field.formular.field],[field.formular.value]) VALUES ';
							} else {
								$sql_t .= ',';
							}
							$sql_t .= '('.$id.',\''.mysql_real_escape_string($name).'\',\''.mysql_real_escape_string($value).'\')';
						}
						$sql_t .= ';';
						if ($sql_t != ';') {
							$db->run($sql_t, $res_t);
						}
					}
				}
			}
			$res = null;
			unset($id);
			unset($text);
			unset($match);
		}

		// Grouped Text
		if ($version < 2010052000) {
			$sql = 'ALTER TABLE [table.txt] ADD COLUMN [field.txt.grouped] TINYINT(1) UNSIGNED NOT NULL DEFAULT 0;';
			$db->run($sql, $res);
			$res = null;
		}

		// Icons for Menus
		if ($version < 2011011400) {
			$sql = 'ALTER TABLE [table.mtx] ADD COLUMN [field.mtx.image] INT(11) UNSIGNED NOT NULL DEFAULT 0;';
			$db->run($sql, $res);
			$res = null;
		}

		// Staticmenu
		if ($version < 2011102602) {
			$sql  = 'CREATE TABLE IF NOT EXISTS [table.staticmenu] ('.
			' [field.staticmenu.menu] INT(10) UNSIGNED NOT NULL DEFAULT 0,'.
			' [field.staticmenu.short] VARCHAR(100) NULL DEFAULT \'\','.
			' [field.staticmenu.lang] INT(10) UNSIGNED NOT NULL DEFAULT 0,'.
			' KEY staticmenu_uid ([field.staticmenu.menu],[field.staticmenu.lang])'.
			' );';
			$db->run($sql, $res);
			$res = null;
		}

		// Add a translated ShortLink (future release) and fill the table so we don't have to create static sites after the Update
		if ($version < 2011102603) {
			$sql = 'ALTER TABLE [table.staticmenu] ADD COLUMN [field.staticmenu.translated] VARCHAR(100) NOT NULL DEFAULT \'\';';
			$db->run($sql, $res);
			$res = null;

			$sql = 'SELECT [men.id],[men.short],[mtx.lang] FROM [table.men],[table.mtx] WHERE [men.id]=[mtx.menu] AND [mtx.active]=1;';
			$db->run($sql, $res);
			if ($res->getFirst()) {
				while ($res->getNext()) {
					$ires = null;
					$isql = 'INSERT INTO [table.staticmenu] ([field.staticmenu.menu],[field.staticmenu.short],[field.staticmenu.translated],[field.staticmenu.lang])'.
					' VALUES ('.$res->{$db->getFieldName('men.id')}.',\''.$res->{$db->getFieldName('men.short')}.'\',\''.$res->{$db->getFieldName('men.short')}.'\','.$res->{$db->getFieldName('mtx.lang')}.');';
					$db->run($isql, $ires);
				}
				$ires = null;
			}
			$res = null;
		}

		if ($version < 2011102604) {
			$sql = 'ALTER TABLE [table.mtx] ADD COLUMN [field.mtx.transshort] VARCHAR(100) NOT NULL DEFAULT \'\';';
			$db->run($sql, $res);
			$res = null;
		}

		// We need the Formulars also on GLOBALTEXT and not only on TEXT-Blocks
		if ($version < 2012052603) {
			$sql = 'ALTER TABLE [table.formular] ADD COLUMN [field.formular.plugin] VARCHAR(10) NOT NULL DEFAULT \'TEXT\';';
			$db->run($sql, $res);
			$res = null;
			$sql = 'UPDATE [table.formular] SET [field.formular.plugin]=\'TEXT\';';
			$db->run($sql, $res);
			$res = null;
		}

		// Update the version
		$this->_updateVersionTable($version, $versionid, self::VERSION);
	}

}
?>
