<?php

class pMenuEntry {
	private $menuId;
	private $data;
	private $static_menu = false;
	private $imageSize = array('width'=>32, 'height'=>32);

	/**
	 * Get a MenuEntry for Menu with ID $id in Language $lang
	 * If $lang is not given, the menu in the current Language is loaded
	 *
	 * @param integer $id MenuID to get
	 * @param pLanguage $lang Language to get the MenuEntry from
	 * @param boolean $static Load the static/published menu or not
	 */
	function __construct($id, pLanguage $lang=null, $static=false) {
		$this->static_menu = $static;
		$this->menuId = (int)$id;
		$this->data = array();
		$this->loadMenu($lang);
	}

	/**
	 * Set the Size for the Image - if there is one
	 * @param int $w Width for the Image
	 * @param int $h Height for the Image
	 * @return void
	 */
	public function setImageSize($w, $h) {
		$this->imageSize = array('width'=>(int)$w, 'height'=>(int)$h);
	}

	/**
	 * Load the menu in given Language
	 *
	 * @param pLanguage $lang
	 */
	public function loadMenu(pLanguage $lang=null) {
		if (!$lang instanceof pLanguage) {
			$lang = pMessages::getLanguageInstance()->getLanguage();
		}

		// Needed to load values from the static menu-table
		if ($this->static_menu) {
			$tbl = 'staticmenu';
			$where = ' AND [staticmenu.lang]='.$lang->id;
		} else {
			$tbl = 'men';
			$where = '';
		}

		// Load
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = 'SELECT * FROM [table.'.$tbl.'] WHERE ['.$tbl.'.id]='.$this->menuId.$where.';';
		$db->run($sql, $res);

		if ($res->getFirst()) {
			$this->data['short'] = $res->{$db->getFieldName($tbl.'.short')};
			$this->data['parent'] = (int)$res->{$db->getFieldName($tbl.'.parent')};
			$this->data['trans_short'] = '';

			// The Link is either a URI or a ShortLink
			if (!empty($this->data['short']) && (substr_count($this->data['short'], '://') > 0)) {
				$this->data['link'] = $this->data['short'];
			} else {
				$this->data['link'] = '/'.$lang->short.'/'.((empty($this->data['short']) ? $this->menuId : $this->data['short']));
			}
		} else {
			$this->data['short'] = '';
			$this->data['trans_short'] = '';
			$this->data['parent'] = 0;
			$this->data['link'] = '/'.$lang->short.'/home';
		}
		$res = null;

		// Don't load more when we load the static menu
		if ($this->static_menu) {
			return;
		}

		$sql = 'SELECT * FROM [table.mtx] WHERE [mtx.menu]='.$this->menuId.' AND [mtx.lang]='.$lang->id.';';
		$db->run($sql, $res);
		if ($res->getFirst()) {
			$this->data['translated'] = true;
			$this->data['active'] = (int)$res->{$db->getFieldName('mtx.active')} > 0;
			$this->data['textId'] = $res->{$db->getFieldName('mtx.id')};
			$this->data['lang'] = (int)$res->{$db->getFieldName('mtx.lang')};
			$this->data['text'] = $res->{$db->getFieldName('mtx.text')};
			$this->data['real_title'] = preg_replace("/(\r|\r\n|\n)/smi", " ", $res->{$db->getFieldName('mtx.title')});
			$this->data['description'] = preg_replace("/(\r|\r\n|\n)/smi", " ", $res->{$db->getFieldName('mtx.description')});
			$this->data['keywords'] = preg_replace("/(\r|\r\n|\n)/smi", " ", $res->{$db->getFieldName('mtx.keywords')});
			$this->data['groups'] = $this->getAccessGroups(true);
			$this->data['image_id'] = (int)$res->{$db->getFieldName('mtx.image')};
			$this->data['trans_short'] = $res->{$db->getFieldName('mtx.transshort')};
		} else {
			$masterLang = new pLanguage(MASTER_LANGUAGE);
			$sql = 'SELECT * FROM [table.mtx] WHERE [mtx.menu]='.$this->menuId.' AND [mtx.lang]='.$masterLang->id.';';
			$db->run($sql, $res);
			if ($res->getFirst()) {
				$this->data['translated'] = false;
				$this->data['active'] = false;
				$this->data['textId'] = $res->{$db->getFieldName('mtx.id')};
				$this->data['lang'] = (int)$res->{$db->getFieldName('mtx.lang')};
				$this->data['text'] = $res->{$db->getFieldName('mtx.text')};
				$this->data['real_title'] = preg_replace("/(\r|\r\n|\n)/smi", " ", $res->{$db->getFieldName('mtx.title')});
				$this->data['description'] = preg_replace("/(\r|\r\n|\n)/smi", " ", $res->{$db->getFieldName('mtx.description')});
				$this->data['keywords'] = preg_replace("/(\r|\r\n|\n)/smi", " ", $res->{$db->getFieldName('mtx.keywords')});
				$this->data['groups'] = $this->getAccessGroups(true);
				$this->data['image_id'] = (int)$res->{$db->getFieldName('mtx.image')};
				$this->data['trans_short'] = '';
			}
		}
		$res = null;

		// Redo the link if we have a translated ShortLink
		if (array_key_exists('trans_short', $this->data) && (strlen($this->data['trans_short']) > 0)) {
			if (!empty($this->data['trans_short']) && (substr_count($this->data['trans_short'], '://') > 0)) {
				$this->data['link'] = $this->data['trans_short'];
			} else {
				$this->data['link'] = '/'.$lang->short.'/'.((empty($this->data['trans_short']) ? $this->menuId : $this->data['trans_short']));
			}
		}
	}

	/**
	 * Load the Menu by a ShortMenu Text
	 *
	 * @param string $short ShortMenu Text
	 * @param boolean $static Load the static Menu
	 * @access public
	 */
	public function loadByShortMenu($short, $static=false) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$this->static_menu = $static;

		// Needed to load values from the static menu-table
		if ($this->static_menu) {
			$tbl = 'staticmenu';
		} else {
			$tbl = 'men';
		}

		// remove trailing slash if one is there and check also against this shortmenu
		$short = trim($short);
		$add = '';
		if (substr($short, -1, 1) == '/') {
			$add = ' OR ['.$tbl.'.short]=\''.substr($short, 0, strlen($short)-1).'\'';
			if ($this->static_menu) {
				$add = ' OR ['.$tbl.'.translated]=\''.substr($short, 0, strlen($short)-1).'\'';
			} else {
				$add = ' OR [mtx.transshort]=\''.substr($short, 0, strlen($short)-1).'\'';
			}
		}

		// Check if the called ShortMenu is an ID and not a text
		$parts = explode('/', $short);
		do {
			$part = array_pop($parts);
		} while(empty($part) && !empty($parts));

		if (is_numeric($part)) {
			$add = ' OR ['.$tbl.'.id]='.(int)$part.'';
		}

		// remove the Language-Part if one is given in front of $short
		if ((substr($short, 0, 1) == '/') && (substr($short, 3, 1) == '/')) {
			$short = substr($short, 4);
		}

		// Check also the translated Shortlink if the above was not found
		if ($this->static_menu) {
			$add = ' OR ['.$tbl.'.translated]=\''.$short.'\'';
		}

		$sql  = 'SELECT ['.$tbl.'.id] FROM [table.'.$tbl.'] WHERE ['.$tbl.'.short]=\''.$short.'\''.$add.';';
		$db->run($sql, $res);
		if ($res->getFirst()) {
			$this->menuId = (int)$res->{$db->getFieldName($tbl.'.id')};
			$this->loadMenu(null);
			return;
		}

		// Check also the translated Shortlink if the above was not found
		if (!$this->static_menu) {
			$add = str_replace('[men.', '[mtx.', $add);
			$add = str_replace('.short]', '.transshort]', $add);
			$sql  = 'SELECT [men.id] FROM [table.men],[table.mtx] WHERE [men.id]=[mtx.menu] AND [mtx.transshort]=\''.$short.'\''.$add.';';
			$db->run($sql, $res);
			if ($res->getFirst()) {
				$this->menuId = (int)$res->{$db->getFieldName($tbl.'.id')};
				$this->loadMenu(null);
			}
		}
	}

	/**
	 * If the current User has access to this page
	 *
	 * @return boolean
	 * @access public
	 */
	public function checkLogin() {
		// TODO: Fix this - In case Menu with ID=61 is Protected, Image with ID=61 is not shown
		$template = pURIParameters::get('tpl', '', pURIParameters::$STRING);
		if (!empty($template)) {
			return true;
		}

		$groups = $this->getAccessGroups(true);
		$user = pCheckUserData::getInstance();
		$access = $user->checkAccess('fulladmin');
		$hasAuth = false;

		// Check each Group against the User-Groups to see if the user can access this page
		if (!$access && !empty($groups)) {
			$userGroups = $user->getUserGroups();
			foreach ($groups as $grp) {
				if ($grp->selected) {
					$hasAuth = true;
				}
				if (!$access && in_array($grp->id, $userGroups)) {
					$access = true;
				}
			}
		} else if (empty($groups)) {
			// If no Groups are defined, each user has access to the page
			$access = true;
			$hasAuth = false;
		}
		return ( (!$hasAuth) || ($hasAuth && $access) );
	}

	/**
	 * Get all Access-Groups for the requested Menu
	 *
	 * @param boolean $onlyRequired set to true if only required groups should be returned
	 * @param int $menu MenuID to get all Access-Groups from
	 * @return array [ stdClass:{ string:name, int:id, string:description }, ... ]
	 * @access public
	 */
	public function getAccessGroups($onlyRequired=false, $menu=null) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$list = array();
		if (is_null($menu)) {
			$menu = $this->menuId;
		}
		$sql = 'SELECT [menugrp.group] FROM [table.menugrp] WHERE [menugrp.menu]='.$menu.';';
		$db->run($sql, $res);
		if ($res->getFirst()) {
			while ($res->getNext()) {
				$list[] = $res->{$db->getFieldName('menugrp.group')};
			}
		}

		$sql = 'SELECT [grp.id],[grp.name],[grp.descr] FROM [table.grp];';
		$db->run($sql, $res);
		$back = array();
		$loaded = array();
		if ($res->getFirst()) {
			while ($res->getNext()) {
				$id = $res->{$db->getFieldName('grp.id')};
				if ( (in_array($id, $list) && $onlyRequired) || !$onlyRequired) {
					$obj = new stdClass();
					$obj->id = $id;
					$obj->name = $res->{$db->getFieldName('grp.name')};
					$obj->description = $res->{$db->getFieldName('grp.descr')};
					$obj->selected = in_array($obj->id, $list);
					$back[] = $obj;
					$loaded[] = $id;
				}
			}
		}

		// Check for ParentMenu-Groups
		$sql = 'SELECT [men.parent] FROM [table.men] WHERE [men.id]='.$menu.';';
		$db->run($sql, $res);
		if ($res->getFirst()) {
			$parent = (int)$res->{$db->getFieldName('men.parent')};
			$res = null;
			if ($parent > 0) {
				$arr = $this->getAccessGroups($onlyRequired, $parent);
				foreach ($arr as $a) {
					if (!in_array($a->id, $loaded)) {
						$back[] = $a;
						$loaded[] = $a->id;
					} else {
						// Added 2011-03-18: If there is a Menu secured, we need to secure each Submenu also
						if ($a->selected) {
							foreach ($back as &$b) {
								if ($b->id == $a->id) {
									$b->selected = true;
									break;
								}
							}
						}
					}
				}
				$arr = null;
			}
		}
		$loaded = null;
		return $back;
	}

	/**
	 * Save this MenuEntry
	 *
	 */
	public function save() {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;

		// First check if the Menu exists
		if ($this->menuId > 0) {
			$sql = 'UPDATE [table.men] SET [field.men.short]=\''.mysql_real_escape_string($this->short).'\' WHERE [field.men.id]='.$this->menuId.';';
			$db->run($sql, $res);

		} else {
			$lastEntry = -1;
			$sql = 'SELECT MAX([men.pos]) AS LastEntry FROM [table.men] WHERE [men.parent]='.$this->parent.';';
			$db->run($sql, $res);
			if ($res->getFirst()) {
				$lastEntry = $res->LastEntry;
			}
			$res = null;

			$sql = 'INSERT INTO [table.men] ([field.men.short],[field.men.pos],[field.men.parent]) VALUES (\''.mysql_real_escape_string($this->short).'\','.($lastEntry+1).','.$this->parent.');';
			$db->run($sql, $res);
			$this->menuId = $res->getInsertId();
		}
		$res = null;

		// Check if there exists a MenuText entry in the Database
		$sql = 'SELECT [mtx.id] FROM [table.mtx] WHERE [mtx.menu]='.$this->menuId.' AND [mtx.lang]='.$this->lang->id.';';
		$db->run($sql, $res);
		if ($res->getFirst()) {
			$sql = 'UPDATE [table.mtx] SET [field.mtx.text]=\''.mysql_real_escape_string($this->text).'\'';
			$sql .= ',[field.mtx.title]=\''.mysql_real_escape_string($this->real_title).'\',[field.mtx.description]=\''.mysql_real_escape_string($this->description).'\',[field.mtx.keywords]=\''.mysql_real_escape_string($this->keywords).'\',[field.mtx.image]='.$this->image_id;
			$sql .= ',[field.mtx.transshort]=\''.mysql_real_escape_string($this->trans_short).'\' WHERE [field.mtx.id]='.$this->textId.';';
		} else {
			$sql = 'INSERT INTO [table.mtx] ([field.mtx.text],[field.mtx.menu],[field.mtx.lang],[field.mtx.active]';
			$sql .= ',[field.mtx.title],[field.mtx.description],[field.mtx.keywords],[field.mtx.image],[field.mtx.transshort]';
			$sql .= ') VALUES (\''.mysql_real_escape_string($this->text).'\','.$this->menuId.','.$this->lang->id.',0';
			$sql .= ',\''.mysql_real_escape_string($this->real_title).'\',\''.mysql_real_escape_string($this->description).'\'';
			$sql .= ',\''.mysql_real_escape_string($this->keywords).'\','.$this->image_id.',\''.mysql_real_escape_string($this->trans_short).'\');';
		}
		$res = null;
		$db->run($sql, $res);
		$res = null;

		// Insert all Groups
		$sql = 'DELETE FROM [table.menugrp] WHERE [field.menugrp.menu]='.$this->menuId.';';
		$db->run($sql, $res);
		foreach ($this->groups as $grp) {
			$sql = 'INSERT INTO [table.menugrp] ([field.menugrp.menu],[field.menugrp.group]) VALUES ('.$this->menuId.','.(int)$grp->id.');';
			$db->run($sql, $res);
			$res = null;
		}
	}

	/**
	 * Get the Parent Menu from this Menu
	 *
	 * @return pMenuEntry
	 */
	public function getParentMenuEntry() {
		return new pMenuEntry($this->parent);
	}

	/**
	 * Check if the given ShortMenu does exist
	 * @param string $name ShortMenu to check if it exists or not
	 * @return boolean
	 * @access public
	 */
	public function shortMenuExists($name) {
		//$lang = pMessages::getLanguageInstance()->getLanguageId();
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = 'SELECT [men.id] FROM [table.men],[table.mtx] WHERE [men.id]<>'.$this->menuId.' AND [mtx.menu]=[men.id] AND ([men.short]=\''.mysql_real_escape_string($name).'\' OR [mtx.transshort]=\''.mysql_real_escape_string($name).'\');';
		$db->run($sql, $res);
		return $res->numRows() > 0;
	}

	/**
	 * Get a Menu property
	 *
	 * @param string $name Property
	 * @return mixed
	 * @access public
	 */
	public function __get($name) {
		if ($name == 'id') {
			return $this->menuId;

		} else if ($name == 'lang') {
			return new pLanguage($this->lang_id);

		} else if (($name == 'title') && (!array_key_exists('real_title', $this->data) || empty($this->data['real_title']))) {
			return $this->text;

		} else if ($name == 'title') {
			return $this->real_title;

		} else if ($name == 'trans_short') {
			return (strlen($this->data['trans_short']) > 0) ? $this->data['trans_short'] : $this->short;

		} else if (($name == 'short') && empty($this->data['short'])) {
			return $this->menuId;

		} else if (($name == 'image') || ($name == 'image_url')) {
			$img = new pImageEntry(array_key_exists('image_id', $this->data) ? $this->data['image_id'] : 0);
			$img->setRenderOptions($this->imageSize);
			if ($name == 'image_url') {
				return $img->url;
			} else {
				return $img->content;
			}

		} else if (array_key_exists($name, $this->data)) {
			return $this->data[$name];

		} else if ($name == 'groups') {      // Only if there are no groups loaded currently
			return array();
		} else if ($name == 'translated') {  // Only if no Menutexts could be found
			return false;
		} else if ($name == 'image_id') {  // Only if no image_id could be found
			return 0;
		}
		return '';
	}

	/**
	 * Set a Menu property
	 *
	 * @param string $name PropertyName
	 * @param string $value Value
	 * @access public
	 */
	public function __set($name, $value) {
		if ($name == 'id') {
			$this->menuId = (int)$value;

		} else if (($name == 'lang') && ($value instanceof pLanguage)) {
			$this->data['lang'] = $value->id;

		} else if ($name == 'title') {
			$this->data['real_title'] = $value;

		} else if ($name == 'name') {
			$this->data['text'] = $value;

		} else if ($name == 'groups') {
			$groups = $this->getAccessGroups(false);
			$list = array();
			foreach ($groups as $g) {
				if (in_array($g->id, $value)) {
					$obj = $g;
					$obj->selected = true;
					$list[] = $obj;
				}
			}
			$this->data['groups'] = $list;

		} else {
			$this->data[$name] = $value;
		}
	}

}

?>