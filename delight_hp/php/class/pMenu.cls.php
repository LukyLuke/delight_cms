<?php
/**
 * This Class represents the complete Menu
 *
 * @author Lukas Zurschmiede <l.zurschmiede@delightsoftware.com>
 * @copyright (c) 2007 by delight software gmbh, switzerland
 *
 * @package delightcms
 * @version 2.0
 * @uses singelton, MainPlugin
 */

$DBTables['men'] = $tablePrefix.'_menu';
$DBTables['mtx'] = $tablePrefix.'_menutexts';
$DBTables['txt'] = $tablePrefix.'_texts';
$DBTables['staticmenu'] = 'dhp_menu_static';
$DBFields['men'] = array(
	'id' => 'id',
	'parent' => 'parent',
	'pos' => 'show_order',
	'link' => 'link',
	'short' => 'short_link',
	'isform' => 'is_formular_page'
);
$DBFields['mtx'] = array(
	'id' => 'id',
	'text' => 'text',
	'menu' => 'menu_id',
	'lang' => 'lang_id',
	'active' => 'is_active',
	'title' => 'site_title',
	'description' => 'site_description',
	'keywords' => 'site_keywords',
	'image' => 'image_id',
	'transshort' => 'translated_shortlink'
);
$DBFields['staticmenu'] = array(
	'menu' => 'menu_id',
	'id' => 'menu_id',
	'short' => 'short_link',
	'translated' => 'translated_short_link',
	'lang' => 'lang_id',
);
$DBFields['txt'] = array(
	'id' => 'id',
	'layout' => 'layout_file',
	'sort' => 'textorder',
	'text' => 'text',
	'title' => 'short',
	'menu' => 'menu_id',
	'lang' => 'lang_id',
	'plugin' => 'text_parser',
	'options' => 'layout_options',
	'grouped' => 'grouped_text'
);


class pMenu {
	const MODULE_VERSION = 2014042800;
	
	private $currentMenuId;
	private $currentMenuIdPart;
	private $currentMenuShort;
	private $loginRequested;
	private $menuTree;
	private $menuParentsList;
	private $menu;
	private $backtrace;
	private $static_menu = false;
	private static $instance = null;
	private static $static_instance = null;

	/**
	 * Initialization
	 * @param boolean $static Load the static/published menu or the live one
	 */
	private function __construct($static=false) {
		$this->updateModule();
		$this->static_menu = $static;
		$this->loginRequested = (pURIParameters::get('adm', 0, pURIParameters::$INT) == 1);
		$this->currentMenuId = pURIParameters::get('m', 0, pURIParameters::$INT);
		$this->currentMenuShort = pURIParameters::get('sm');
		$this->loadMenuData();
		$this->createBacktrace();

		$this->currentMenuIdPart = '';
		$tmp = explode('/', $_SERVER['REQUEST_URI']);
		if ((count($tmp) > 3) && (substr_count($tmp[3], '_') > 0)) {
			$tmp = explode('_', $tmp[3], 2);
			$this->currentMenuIdPart = $tmp[1];
		}
	}

	/**
	 * Get a Menu-Instance as a Singenlton
	 *
	 * @param boolean $static Load the static menu (true) or the not yet published
	 * @return pMenu
	 */
	public static function getMenuInstance($static=false) {
		if ($static) {
			if (pMenu::$static_instance == null) {
				pMenu::$static_instance = new pMenu($static);
			}
			return pMenu::$static_instance;
		}
		if (pMenu::$instance == null) {
			pMenu::$instance = new pMenu();
		}
		return pMenu::$instance;
	}

	/**
	 * Return the current MenuID
	 *
	 * @return integer The MenuID
	 */
	public function getMenuId() {
		return $this->menu->id;
	}

	/**
	 * Get the second ID-Part if a Menu is submitted as ID_XX
	 *
	 * @return string
	 */
	public function getMenuIdPart() {
		return $this->currentMenuIdPart;
	}

	/**
	 * Return the short-name of current selected Menu
	 *
	 * @return string current Short-Menu
	 */
	public function getShortMenuName() {
		$short = $this->menu->short;
		if (empty($short)) {
			$short = $this->menu->id;
		}
		if (substr($short, -1, 1) == '/') {
			$short = substr($short, 0, strlen($short)-1);
		}
		return $short;
	}

	/**
	 * Get the Backtrace as an ArrayIterator
	 * @return Array
	 */
	public function getBacktrace() {
		return $this->backtrace;
	}

	/**
	 * Get a Breadcrum-String which shows the current Menu inside the Menustructure
	 * This string can be placed directly on the page
	 *
	 * @param string $cont The Content to use for an Entry
	 * @param string $sep Seperator between the Menuentries
	 * @param string $end The final string to append to the Breadcrumb
	 * @return string HTML
	 */
	public function getBreadcrumb($cont, $sep=' / ', $end=' &gt;') {
		$back = '';
		$last = count($this->backtrace)-1;
		foreach ($this->backtrace as $k => $id) {
			if (!empty($back)) $back .= $sep;
			$menu = new pMenuEntry($id);
			$tmp = str_ireplace('[NAME]', $menu->text, $cont);
			$tmp = str_ireplace('[LINK]', $menu->link, $tmp);
			if ($k == 0) {
				$tmp = preg_replace('/\[FIRST:([^\]]+)\]/smi', '${1}', $tmp);
			} else {
				$tmp = preg_replace('/\[FIRST:([^\]]+)\]/smi', '', $tmp);
			}
			if ($k == $last) {
				$tmp = preg_replace('/\[LAST:([^\]]+)\]/smi', '${1}', $tmp);
			} else {
				$tmp = preg_replace('/\[LAST:([^\]]+)\]/smi', '', $tmp);
			}
			$back .= $tmp;
		}

		// Only add the EndString if there is a valid Breadcrumb-String
		if (!empty($back)) $back .= $end;

		return $back;
	}

	/**
	 * Create the whole menu as a simple Object and return it with optionally selected MenuID
	 *
	 * @param int $selected optional MenuID which has a flag to be selected
	 * @return array
	 * @access public
	 */
	public function getStructureObject($selected=0) {
		$this->createMenuStructure();
		return $this->_getStructureObject($selected, $this->menuTree);
	}

	public function _getStructureObject($selected, &$childs) {
		$back = array();
		foreach ($childs as $entry) {
			$o = new stdClass();
			$o->id = $entry->entry->id;
			$o->short = $entry->entry->short;
			$o->text = $entry->entry->text;
			$o->name = $o->text;
			$o->selected = ($o->id == $selected);
			$o->childs = $this->_getStructureObject($selected, $entry->childs);
			$o->childSelected = false;
			foreach ($o->childs as &$c) {
				if ($c->selected || $c->childSelected) {
					$o->childSelected = true;
					break;
				}
			}
			$back[] = $o;
		}
		return $back;
	}

	/**
	 * Get the complete Menu as a flat Array with only the IDs
	 * @return array
	 */
	public function getFlatMenuIdList() {
		return $this->_getFlatMenuIdList(array(), $this->menuTree);
	}
	public function _getFlatMenuIdList($list, &$childs) {
		$list = (array)$list;
		foreach($childs as $entry) {
			if (!in_array($entry->entry->id, $list)) {
				$list[] = $entry->entry->id;
				$list = $this->_getFlatMenuIdList($list, $entry->childs);
			}
		}
		return $list;
	}

	/**
	 * Check if Login is requested or not by URL-Parameter "adm"
	 *
	 * @return boolean
	 * @access public
	 */
	public function isLoginRequested() {
		return $this->loginRequested;
	}

	/**
	 * Get the pMenuEntry from current Menu
	 *
	 * @return pMenuentry
	 * @access public
	 */
	public function getMenuEntry() {
		return $this->menu;
	}

	/**
	 * Load all MenuData from current Menu
	 *
	 */
	private function loadMenuData() {
		$this->menu = new pMenuEntry($this->currentMenuId, null, $this->static_menu);
		if ($this->menu->id == 0) {
			$this->menu->loadByShortMenu($this->currentMenuShort, $this->static_menu);
		}
		$this->currentMenuId = $this->menu->id;
		$this->currentMenuShort = $this->menu->short;
	}

	/**
	 * Create the complete Menu-Structure as a Tree
	 *
	 * @access private
	 */
	private function createMenuStructure() {
		$this->menuTree = array();
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = 'SELECT [men.id] FROM [table.men] WHERE [men.parent]=0;';
		$db->run($sql, $res);
		if ($res->getFirst()) {
			while ($res->getNext()) {
				$id = (int)$res->{$db->getFieldName('men.id')};
				$o = new stdClass();
				$o->entry = new pMenuEntry($id);
				$o->id = $id;
				$o->childs = array();
				$this->_createMenuStructure($o);
				$this->menuTree[] = $o;
			}
		}
	}

	/**
	 * Create the MenuStructure from Submenus - only call this from createMenuStructure()
	 *
	 * @param stdClass $parent Parent MenuEntry
	 * @return null
	 * @access private
	 */
	private function _createMenuStructure(&$parent) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = 'SELECT [men.id] FROM [table.men] WHERE [men.parent]='.$parent->id.';';
		$db->run($sql, $res);
		if ($res->getFirst()) {
			while ($res->getNext()) {
				$id = (int)$res->{$db->getFieldName('men.id')};
				$o = new stdClass();
				$o->entry = new pMenuEntry($id);
				$o->id = $id;
				$o->childs = array();
				$this->_createMenuStructure($o);
				$parent->childs[] = $o;
			}
		}
	}

	/**
	 * Create the Backtrace MenuID List
	 *
	 */
	private function createBacktrace($id = null) {
		$this->backtrace = array();
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;

		if (is_null($id)) {
			$id = $this->currentMenuId;
		}

		// Get the Parent-Menu
		$sql  = "SELECT [men.id],[men.parent] FROM [table.men] WHERE [men.id]=".(int)$id.";";
		$db->run($sql, $res);
		if (!$res->getFirst()) {
			return;
		}

		$parent = (int)$res->{$db->getFieldName('men.parent')};
		if ($parent > 0) {
			$this->createBacktrace($parent);
		}
		array_push($this->backtrace, $id);
	}
	
	/**
	 * Update the module if needed - this was first in the administration
	 * class but is moved here to be more general
	 * @access protected
	 */
	protected function updateModule() {
		// first get the version stored in the Database
		$db = pDatabaseConnection::getDatabaseInstance();
		$version = $db->getModuleVersion(get_class($this));
		$res = null;

		// Check if we need an Update
		if (self::MODULE_VERSION > $version) {
			// Initial create the menu, menutext and text tables
			if ($version <= 0) {
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
				' [field.mtx.active] INT(1) UNSIGNED NOT NULL DEFAULT 1,'.
				' [field.mtx.title] VARCHAR(200) NOT NULL DEFAULT \'\','.
				' [field.mtx.description] VARCHAR(200) NOT NULL DEFAULT \'\','.
				' [field.mtx.keywords] TEXT NOT NULL DEFAULT \'\','.
				' [field.mtx.image] INT(11) UNSIGNED NOT NULL DEFAULT 0,'.
				' [field.mtx.transshort] VARCHAR(100) NOT NULL DEFAULT \'\','.
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
				' [field.txt.grouped] TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,'.
				' PRIMARY KEY ([field.txt.id]),'.
				' UNIQUE KEY [field.txt.id] ([field.txt.id])'.
				' );';
				$db->run($sql, $res);
				$res = null;
				
				$sql  = 'CREATE TABLE IF NOT EXISTS [table.staticmenu] ('.
				' [field.staticmenu.menu] INT(10) UNSIGNED NOT NULL DEFAULT 0,'.
				' [field.staticmenu.short] VARCHAR(100) NULL DEFAULT \'\','.
				' [field.staticmenu.lang] INT(10) UNSIGNED NOT NULL DEFAULT 0,'.
				' [field.staticmenu.translated] VARCHAR(100) NOT NULL DEFAULT \'\','.
				' KEY staticmenu_uid ([field.staticmenu.menu],[field.staticmenu.lang])'.
				' );';
				$db->run($sql, $res);
				$res = null;
				
				// Insert the main menu if not already exists
				$sql = 'SELECT COUNT([men.id]) AS cnt FROM [table.men];';
				$db->run($sql, $res);
				if (!$res->getFirst() || ($res->cnt <= 0)) {
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
			}

			// Update the version in database for this module
			$db->updateModuleVersion(get_class($this), self::MODULE_VERSION);
		}
	}

}
