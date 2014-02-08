<?php

/**
 * Menu-Administration
 *
 */

class admin_200_Settings extends admin_MAIN_Settings {
	const MENU_EDIT = 201;
	const MENU_CREATE = 200;
	public $VERSION = 2009072100;

	public function __construct() {
		parent::__construct();
		$this->menuId = pURIParameters::get('menu',0,pURIParameters::$INT);
		$obj = new MENU();
		unset($obj);
	}

	public function createActionBasedContent() {
		$userCheck = pCheckUserData::getInstance();
		if ($userCheck->checkAccess('menu')) {
			switch (200 + $this->_mainAction) {
				case 200: $this->showCreateEntry(self::MENU_CREATE); break;
				case 201: $this->showEditEntry();   break;
				case 205: $this->showLinkEntry();   break;
				case 203: $this->moveEntryUp();     break;
				case 204: $this->moveEntryDown();   break;
				case 202: $this->showDeleteConfirmation(); break;
				case 206: $this->changeMenuVisibility(); break;

				case 252: $this->doDeleteEntry(); break;
				case 250: $this->doChangeAndInsertEntry(); break;
				case 251: $this->doChangeAndInsertEntry(true); break;
				case 255: $this->doChangeAndInsertEntry(); break;

				case 256: $this->checkShortmenuExists(pURIParameters::get('menu', 0, pURIParameters::$INT), pURIParameters::get('name', '', pURIParameters::$STRING)); break;
			}
		}
	}

	/**
	 * Show the Create-Formular
	 *
	 * @param int $actionId ID which action was called (see createActionBasedContent)
	 * @param pMenuEntry $options Class with predefined values
	 * @access private
	 */
	private function showCreateEntry($actionId=0, pMenuEntry $menuEntry=null) {
		$lang = new pLanguage();
		$userCheck = pCheckUserData::getInstance();
		if ($userCheck->checkAccess("menu")) {

			// Get a MenuEntry Object if none is given
			if (is_null($menuEntry)) {
				$menuEntry = new pMenuEntry(0, $lang);
				$menuEntry->parent = $this->menuId;
				$menuEntry->short = '';
			}
			$menuEntry->setImageSize(68, 58);

			$accessGroups = 'null';
			foreach ($menuEntry->getAccessGroups(false) as $group) {
				$accessGroups .= ',{name:\''.$group->name.'\',value:'.$group->id.',selected:'.($group->selected?'true':'false').'}';
			}

			// get the SectionHTML
			//$_adminHtml = $this->_getContent('menuEntryEdit', 'EDIT');
			$_adminHtml = $this->getAdminContent('menu_edit', 200);

			// define hidden fields
			$_hidden_fields = '<input type="hidden" name="lan" value="'.$lang->short.'" />
				<input type="hidden" name="menu" value="'.$menuEntry->id.'" />
				<input type="hidden" name="parent" value="'.$menuEntry->parent.'" />
				<input type="hidden" name="adm" value="'.($actionId + 50).'" />
				<input type="hidden" name="m" value="'.$menuEntry->id.'" />
				<input type="hidden" name="lang" value="'.$lang->short.'" />
				<input type="hidden" name="advanced" id="advancedEdit" value="0" />';

			// Replace Tags
			$_adminHtml = str_replace('[UPLOAD_HIDDEN_FIELDS]', $_hidden_fields, $_adminHtml);
			$_adminHtml = str_replace('[UPLOAD_ACTION]', '/delight_hp/index.php', $_adminHtml);
			$_adminHtml = str_replace('[FORM_MENU_TEXT]', $menuEntry->text, $_adminHtml);
			$_adminHtml = str_replace('[FORM_MENU_SHORT]', is_numeric($menuEntry->short) ? '' : $menuEntry->short, $_adminHtml);
			$_adminHtml = str_replace('[FORM_MENU_SHORT_TRANSLATED]', $menuEntry->trans_short, $_adminHtml);
			$_adminHtml = str_replace('[FORM_MENU_TITLE]', $menuEntry->title, $_adminHtml);
			$_adminHtml = str_replace('[FORM_MENU_DESC]', $menuEntry->description, $_adminHtml);
			$_adminHtml = str_replace('[FORM_MENU_KEYWORDS]', $menuEntry->keywords, $_adminHtml);
			$_adminHtml = str_replace('[FORM_MENU_IMAGE]', $menuEntry->image_id, $_adminHtml);
			$_adminHtml = str_replace('[FORM_MENU_IMAGE_URL]', $menuEntry->image_url, $_adminHtml);
			$_adminHtml = str_replace('[FORM_MENU_IMAGE_SRC]', $menuEntry->image, $_adminHtml);
			$_adminHtml = str_replace('[FORM_MENU_ACCESS_GROUPS]', $accessGroups, $_adminHtml);

			if ((substr($menuEntry->link, 0, 4) == 'http') || (substr($menuEntry->link, 0, 3) == 'ftp')) {
				$_adminHtml = str_replace('[FORM_MENU_SHORT_IS_LINK]', "checked='checked'", $_adminHtml);
			} else {
				$_adminHtml = str_replace('[FORM_MENU_SHORT_IS_LINK]', "", $_adminHtml);
			}

			$this->_content = $_adminHtml;
		} else {
			$this->showNoAccess();
		}
		$this->_content = $this->ReplaceAjaxLanguageParameters($this->_content);
		echo $this->_content;
		exit();
	}

	/**
	 * Show the Change-Form
	 *
	 * @access private
	 */
	private function showEditEntry() {
		$lang = new pLanguage();
		$userCheck = pCheckUserData::getInstance();
		if ($userCheck->checkAccess("menu")) {
			$this->showCreateEntry(self::MENU_EDIT, new pMenuEntry($this->menuId, $lang));
		} else {
			$this->showNoAccess();
		}
	}

	// Show the complete Menu and let the user choose a Menu to link in selected Menu
	private function showLinkEntry() {
		//$_idList = $this->getRecursiveMenuList(0, array());
		$userCheck = pCheckUserData::getInstance();
		$lang = new pLanguage();
		if ($userCheck->checkAccess("menu")) {
			$_lnk = "/".$lang->short."/".$this->menuId."/adm=".((integer)constant("ADM_MENU_LINK") + 50)."&i=";

			// Create a Menu-Object and get the source
			$_menu = new MENU($this->DB, $this->LANG);
			$_menu->_template = 'adm_completeMenu.tpl';
			$_menu->_showAll = true;
			$_menu->_showNoAdmin = true;
			$_menu->_insertAdminLink = $_lnk;
			$menuSource = $_menu->GetSource();
			unset($_menu);

			// Create the content
			$this->_content = $this->_getContent('menuEntryEdit', 'LINK');
			$this->_content = str_replace("[TEXT]", $menuSource, $this->_content);
		} else {
			$this->showNoAccess();
		}
		$this->_content = $this->ReplaceAjaxLanguageParameters($this->_content);
		echo $this->_content;
		exit();
	}

	/**
	 * Show the final Change- and Insert-Redirect-Page and exit
	 *
	 * @param boolean $isUpdate If it is an Update-Request
	 * @access private
	 */
	private function doChangeAndInsertEntry($isUpdate=false) {
		$userCheck = pCheckUserData::getInstance();
		$lang = new pLanguage();
		if ($userCheck->checkAccess('menu')) {
			$this->doInsertMenuEntry($isUpdate);

			$_adminHtml = $this->getAdminContent('success_forward', 200);
			$_adminHtml = str_replace('[REDIRECT_LINK]', '/'.$lang->short.'/'.$this->menuId.'/', $_adminHtml);
			$this->_content = $_adminHtml;
		} else {
			$this->showNoAccess();
		}
		$this->_content = $this->ReplaceAjaxLanguageParameters($this->_content);
		echo $this->_content;
		exit();
	}

	/**
	 * Show a Delete-Confirmation and exit
	 *
	 * @access private
	 */
	private function showDeleteConfirmation() {
		$userCheck = pCheckUserData::getInstance();
		$lang = new pLanguage();
		if ($userCheck->checkAccess('menu')) {
			$db = pDatabaseConnection::getDatabaseInstance();
			$res = null;

			// Get the Menu-Entry which should be deleted
			$shortLink = '';
			$sql = 'SELECT [men.short] FROM [table.men] WHERE [men.id]='.(int)$this->menuId.';';
			$db->run($sql, $res);
			if ($res->getFirst()) {
				$shortLink = $res->{$db->getFieldName('men.short')};
			}
			$res = null;

			// Get all texts from menu which should be deleted
			$menuText = '';
			$sql  = 'SELECT [mtx.text],[mtx.title],[mtx.description],[mtx.keywords] FROM [table.mtx] WHERE [mtx.menu]='.(int)$this->menuId.' AND [mtx.lang]='.$lang->id.';';
			$db->run($sql, $res);
			if ($res->getFirst()) {
				$menuText = $res->{$db->getFieldName('mtx.text')};
			}
			$res = null;

			// We need the parent-ID from this Menu if the user deletes the menu in which he is
			$menId = $this->_menuId;
			/*if ((int)$this->_menuId == (int)$this->menuId) {
				$sql = 'SELECT [men.parent] FROM [table.men] WHERE [men.id]='.(int)$this->menuId.';';
				$db->run($sql, $res);
				if ($res->getFirst()) {
					$menId = $res->{$db->getFieldName('men.parent')};
				}
			}
			$res = null;*/

			// get the SectionHTML
			$_adminHtml = $this->getAdminContent('menu_delete', 200);

			// define hidden fields
			$_hidden_fields = '<input type="hidden" name="lan" value="'.$lang->short.'" />
				<input type="hidden" name="menu" value="'.$this->menuId.'" />
				<input type="hidden" name="adm" value="252" />
				<input type="hidden" name="m" value="'.$menId.'" />
				<input type="hidden" name="lang" value="'.$lang->short.'" />';

			// Replace Tags
			$_adminHtml = str_replace('[UPLOAD_HIDDEN_FIELDS]', $_hidden_fields, $_adminHtml);
			$_adminHtml = str_replace('[UPLOAD_ACTION]', '/delight_hp/index.php', $_adminHtml);
			$_adminHtml = str_replace('[FORM_MENU_TEXT]', $menuText, $_adminHtml);
			$_adminHtml = str_replace('[FORM_MENU_SHORT]', $shortLink, $_adminHtml);

			$this->_content = $_adminHtml;
		} else {
			$this->showNoAccess();
		}
		$this->_content = $this->ReplaceAjaxLanguageParameters($this->_content);
		echo $this->_content;
		exit();
	}

	/**
	 * Delete a Menu and all its descendants and texts. exit after
	 *
	 * @access private
	 */
	private function doDeleteEntry() {
		$userCheck = pCheckUserData::getInstance();
		$lang = new pLanguage();
		if ($userCheck->checkAccess("menu") && $userCheck->checkAccess("content")) {
			$db = pDatabaseConnection::getDatabaseInstance();
			$res = null;
			$menuRealParent = 0;
			$curPos = 0;

			// get the parent menu and the menu-position
			$sql = 'SELECT [men.parent],[men.pos] FROM [table.men] WHERE [men.id]='.(int)$this->menuId.';';
			$db->run($sql, $res);
			if ($res->getFirst()) {
				$parentMenu = (int)$res->{$db->getFieldName('men.parent')};
				$menuRealParent = $parentMenu;
				$curPos = (int)$res->{$db->getFieldName('men.pos')};;

				// We get the first MainMenu if the user deletes one of them
				if ($parentMenu <= 0) {
					$res = null;
					$sql = 'SELECT [men.id] FROM [table.men] WHERE [men.parent]=0 ORDER BY [men.id] ASC;';
					$db->run($sql, $res);
					if ($res->getFirst()) {
						$parentMenu = (int)$res->{$db->getFieldName('men.id')};
					}
				}
			}
			$res = null;

			$_menuList  = $this->getRecursiveMenuList($this->menuId, array($this->menuId));
			if (count($_menuList) > 0) {
				$sql = 'UPDATE [table.men] SET [field.men.pos]=[field.men.pos]-1 WHERE [men.parent]='.(int)$menuRealParent.' AND [men.pos]>'.(int)$curPos.';';
				$db->run($sql, $res);
				$res = null;

				// Delete the Menu and the Text-Entries
				for ($i = 0; $i < count($_menuList); $i++) {
					// Delete all text-Entries based on this Menu
					$sql = 'DELETE FROM [table.txt] WHERE [field.txt.menu]='.(int)$_menuList[$i].';';
					$db->run($sql, $res);
					$res = null;

					// Delete all Access-Group assignments
					$sql = 'DELETE FROM [table.menugrp] WHERE [menugrp.menu]='.(int)$_menuList[$i].';';
					$db->run($sql, $res);
					$res = null;

					// Delete the MenuText-Entries
					$sql = 'DELETE FROM [table.mtx] WHERE [mtx.menu]='.(int)$_menuList[$i].';';
					$db->run($sql, $res);
					$res = null;

					// Finally delete the Menu
					$sql = 'DELETE FROM [table.men] WHERE [men.id]='.(int)$_menuList[$i].';';
					$db->run($sql, $res);
					$res = null;
				}
			}

			$_adminHtml = $this->getAdminContent('success_forward', 200);
			$_adminHtml = str_replace('[REDIRECT_LINK]', '/'.$lang->short.'/'.$parentMenu.'/', $_adminHtml);
			$this->_content = $_adminHtml;
		} else {
			$this->showNoAccess();
		}
		$this->_content = $this->ReplaceAjaxLanguageParameters($this->_content);
		echo $this->_content;
		exit();
	}

	/**
	 * Get all descendants from a Menu as a flat list/array
	 *
	 * @param int $id MenuID to get it's descendants
	 * @param array $idList the MenuList
	 * @return array List with all MenuIDs
	 * @access private
	 */
	private function getRecursiveMenuList($id, $idList) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = 'SELECT [men.id] FROM [table.men] WHERE [men.parent]='.(int)$id.';';
		$db->run($sql, $res);
		$fname = $db->getFieldName('men.id');
		if ($res->getFirst()) {
			while ($res->getNext()) {
				$mid = $res->{$fname};
				array_push($idList, $mid);
				$idList = $this->getRecursiveMenuList($mid, $idList);
			}
			$res = null;
		}
		return $idList;
	}

	/**
	 * Move the current Menu one position up
	 *
	 * @access private
	 */
	private function moveEntryUp() {
		$userCheck = pCheckUserData::getInstance();
		$lang = new pLanguage();
		if ($userCheck->checkAccess("menu")) {
			$db = pDatabaseConnection::getDatabaseInstance();
			$res = null;

			$sql  = 'SELECT [men.id],[men.pos],[men.parent] FROM [table.men] WHERE [men.id]='.(int)$this->menuId.';';
			$db->run($sql, $res);
			if ($res->getFirst()) {
				$id = (int)$res->{$db->getFieldName('men.id')};
				$parent = (int)$res->{$db->getFieldName('men.parent')};
				$pos = (int)$res->{$db->getFieldName('men.pos')};
				$res = null;

				// Get the lower Entry (lower means a lower Order-ID)
				$sql = 'SELECT [men.id],[men.pos],[men.parent] FROM [table.men] WHERE [men.pos]<'.$pos.' AND [men.parent]='.$parent.' ORDER BY [men.pos] DESC;';
				$db->run($sql, $res);
				if ($res->getFirst()) {
					$newPos = $res->{$db->getFieldName('men.pos')};
					$updateId = $res->{$db->getFieldName('men.id')};
					$res = null;

					// Update the lower-Entry
					$sql = 'UPDATE [table.men] SET [men.pos]='.$pos.' WHERE [men.id]='.$updateId.';';
					$db->run($sql, $res);
					$res = null;

					// Update the moveing-Entry
					$sql = 'UPDATE [table.men] SET [men.pos]='.$newPos.' WHERE [men.id]='.$id.';';
					$db->run($sql, $res);
					$res = null;
				}
			}
			$lnk = '/'.$lang->short.'/'.$this->_menuId.'/';
			//$this->_content = $this->_getContent('forwardOnly');
			//$this->_content = str_replace("[FORWARD_LINK]", $lnk, $this->_content);
			header('Location: '.$lnk);
			exit();
		} else {
			$this->showNoAccess();
		}
	}

	/**
	 * Move the current Menu one Position down
	 *
	 * @access private
	 */
	private function moveEntryDown() {
		$userCheck = pCheckUserData::getInstance();
		$lang = new pLanguage();

		if ($userCheck->checkAccess("menu")) {
			$db = pDatabaseConnection::getDatabaseInstance();
			$res = null;

			$sql  = 'SELECT [men.id],[men.pos],[men.parent] FROM [table.men] WHERE [men.id]='.(int)$this->menuId.';';
			$db->run($sql, $res);
			if ($res->getFirst()) {
				$id = (int)$res->{$db->getFieldName('men.id')};
				$parent = (int)$res->{$db->getFieldName('men.parent')};
				$pos = (int)$res->{$db->getFieldName('men.pos')};
				$res = null;

				// Get the lower Entry (lower means a lower Order-ID)
				$sql = 'SELECT [men.id],[men.pos],[men.parent] FROM [table.men] WHERE [men.pos]>'.$pos.' AND [men.parent]='.$parent.' ORDER BY [men.pos] ASC;';
				$db->run($sql, $res);
				if ($res->getFirst()) {
					$newPos = $res->{$db->getFieldName('men.pos')};
					$updateId = $res->{$db->getFieldName('men.id')};
					$res = null;

					// Update the lower-Entry
					$sql = 'UPDATE [table.men] SET [men.pos]='.$pos.' WHERE [men.id]='.$updateId.';';
					$db->run($sql, $res);
					$res = null;

					// Update the moveing-Entry
					$sql = 'UPDATE [table.men] SET [men.pos]='.$newPos.' WHERE [men.id]='.$id.';';
					$db->run($sql, $res);
					$res = null;
				}
			}
			$lnk = '/'.$lang->short.'/'.$this->_menuId.'/';
			//$this->_content = $this->_getContent('forwardOnly');
			//$this->_content = str_replace("[FORWARD_LINK]", $lnk, $this->_content);
			header('Location: '.$lnk);
			exit();
		} else {
			$this->showNoAccess();
		}
	}

	/**
	 * Change Menu-Visibility
	 *
	 * @access private
	 */
	private function changeMenuVisibility() {
		$lang = new pLanguage();
		$userCheck = pCheckUserData::getInstance();
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		if ($userCheck->checkAccess("menu")) {
			$sql = 'SELECT [mtx.id],[mtx.active] FROM [table.mtx] WHERE [mtx.menu]='.$this->menuId.' AND [mtx.lang]='.$lang->id.';';
			$db->run($sql, $res);
			if ($res->getFirst()) {
				$active = (int)$res->{$db->getFieldName('mtx.active')};
				$active = $active > 0 ? 0 : 1;
				$id = (int)$res->{$db->getFieldName('mtx.id')};
				$sql = 'UPDATE [table.mtx] SET [field.mtx.active]='.$active.' WHERE [field.mtx.id]='.$id.';';
				$db->run($sql, $res);
			}
			$lnk = '/'.$lang->short.'/'.$this->_menuId.'/';
			//$this->_content = $this->_getContent('forwardOnly');
			//$this->_content = str_replace("[FORWARD_LINK]", $lnk, $this->_content);
			header('Location: '.$lnk);
			exit();
		} else {
			$this->showNoAccess();
		}
	}

	/**
	 * Insert/Update a MenuEntry
	 *
	 * @param boolean $update if this is an Update Request not an insert
	 * @access private
	 */
	private function doInsertMenuEntry($update=true) {
		$lang = new pLanguage();
		$advancedEdit = pURIParameters::get('advanced', 0, pURIParameters::$INT) == 1;

		if ($update) {
			$menu = new pMenuEntry($this->menuId, $lang);
		} else {
			$menu = new pMenuEntry(0, $lang);
			$menu->parent = pURIParameters::get('parent', 1, pURIParameters::$INT);
		}
		$menu->name = pURIParameters::get('name', '', pURIParameters::$STRING);
		$menu->short = pURIParameters::get('short', '', pURIParameters::$STRING);
		if ($advancedEdit) {
			$menu->title = pURIParameters::get('title', '', pURIParameters::$STRING);
			$menu->description = pURIParameters::get('descr', '', pURIParameters::$STRING);
			$menu->keywords = pURIParameters::get('keywords', '', pURIParameters::$STRING);
			$menu->groups = pURIParameters::get('login', array(), pURIParameters::$ARRAY);
			$menu->image_id = pURIParameters::get('image', 0, pURIParameters::$INT);
			$menu->trans_short = pURIParameters::get('transshort', '', pURIParameters::$STRING);
		}

		$menu->save();
		$this->menuId = $menu->id;
	}

	/**
	 * Check if a ShortMenu does already exists or not
	 * @param int $menuId MenuID not to check
	 * @param string $shortMenu ShortMenu to check if it exists
	 * @return void
	 * @echo JSON
	 * @access private
	 */
	private function checkShortmenuExists($menuId, $shortMenu) {
		$lang = pMessages::getLanguageInstance();
		$menu = new pMenuEntry($menuId, $lang->getLanguage());

		header('Content-Type: application/json');
		$back = new stdClass();
		$back->name = $shortMenu;
		$back->ignored = $menu->id;
		$back->exists = $menu->shortMenuExists($shortMenu);
		echo json_encode($back);
		exit();
	}

	// Insert a Menu-Link
	private function doInsertMenuLink() {
		// Get the Last Order-Sort from current entry
		die('This function is curently not usable: admin_200_Settings::doInsertMenuLink()');

		/*$sql = "SELECT MAX(".$this->DB->Field('men','pos').") AS LastEntry FROM ".$this->DB->Table('men')." WHERE ".$this->DB->Field('men','parent')." = '".$this->_menuId."'";
		$res = $this->DB->ReturnQueryResult($sql);
		if ($res) {
			$row = mysql_fetch_assoc($res);
		} else {
			$row['LastEntry'] = '-1';
		}
		$this->DB->FreeDatabaseResult($res);

		// Create Insert-SQL
		$sql  = "INSERT INTO ".$this->DB->Table('men')."";
		$sql .= " SET ".$this->DB->FieldOnly('men','pos')." = '".((integer)$row['LastEntry'] + 1)."'";
		$sql .= ",".$this->DB->FieldOnly('men','parent')." = '".$this->_menuId."'";
		$sql .= ",".$this->DB->FieldOnly('men','link')." = '".$this->menuId."'";
		$res = $this->DB->ReturnQueryResult($sql);
		$this->DB->FreeDatabaseResult($res);*/
	}

}
?>

