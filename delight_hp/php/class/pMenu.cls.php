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

class pMenu {
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
}


?>