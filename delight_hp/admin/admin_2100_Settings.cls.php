<?php

/**
 * Admin-Class for GROUP
 *
 */
class admin_2100_Settings extends admin_MAIN_Settings {

	/**
	 * constructor
	 *
	 * @return admin_2100_Settings
	 */
	public function __construct() {
		parent::__construct();
		$obj = new GROUP();
		unset($obj);
	}

	/**
	 * Call a function, based on parameter adm
	 * This is the main function, which will be called for getting some content
	 */
	public function createActionBasedContent() {
		$userCheck = pCheckUserData::getInstance();

		// Check for Access
		if ($userCheck->checkAccess($this->_adminAccess)) {
			// Check which Action is called and return the appropriate content or JSON
			switch (pURIParameters::get('action', '', pURIParameters::$STRING)) {
				case 'template':
					$tpl = pURIParameters::get('template', 'group_content', pURIParameters::$STRING);
					$section = pURIParameters::get('section', 0, pURIParameters::$INT);
					$entry = pURIParameters::get('entry', 0, pURIParameters::$INT);

					$tpl = $this->getAdminContent($tpl, 2100);
					$tpl = str_replace('[SECTION_ID]', $section, $tpl);
					$tpl = str_replace('[ENTRY_ID]', $entry, $tpl);

					// If this is a Content-Plugin Request, replace some additional variables
					//$text = $this->getTextEntryObject($entry);
					//$tpl = str_replace('[ENTRY_TEMPLATE]', $text->layout, $tpl);
					//$tpl = str_replace('[ENTRY_OPTIONS]', json_encode($text->optionsArray), $tpl);
					//unset($text);

					echo $this->ReplaceAjaxLanguageParameters($tpl);
					exit();
					break;

				case 'textlist':
					$entry = pURIParameters::get('entry', 0, pURIParameters::$INT);
					$back = $this->getTextList($entry);
					echo '{"entry":'.$entry.',"action":"textlist","title":"'.$back->title.'","selected":'.json_encode($back->selected).',"list":'.json_encode($back->list).'}';
					exit();
					break;

				case 'update':
					$entry = pURIParameters::get('entry', 0, pURIParameters::$INT);
					$list = pURIParameters::get('list', '', pURIParameters::$STRING);
					$this->updateContent($entry, $list);

					echo '{"entry":'.$entry.',"success":true,"call":"close"}';
					exit();
					break;
			}
		} else {
			$this->showNoAccess();
		}
		exit();
	}

	/**
	 * Get all texts from current menu in which $id is as an array of objects
	 * or an ErrorString
	 *
	 * @param int $id The TextID to get Data from
	 * @access private
	 * @return stdClass
	 */
	private function getTextList($textId) {
		$userCheck = pCheckUserData::getInstance();

		$back = new stdClass();
		$back->list = array();
		$back->selected = array();
		$back->title = '';
		$back->error = '';

		if ($userCheck->checkAccess($this->_adminAccess)) {
			$text = new pTextEntry($textId);
			$back->title = $text->title;
			$back->selected = explode(',', $text->text);
			foreach ($back->selected as $k=>$v) {
				$back->selected[$k] = (int)$v;
			}

			foreach ($this->getAllTexts($text->menu, $text->lang) as $id) {
				$text = new pTextEntry($id);
				$back->list[] = $text->object;
			}

		} else {
			$back->error = 'Access denied';
		}
		return $back;
	}

	/**
	 * Save all textentries in $list in $entry TextGroup
	 *
	 * @param int $entry
	 * @param array $list
	 * @access private
	 */
	private function updateContent($entry, $list) {
		$idList = explode(',', $list);
		$text = new pTextEntry($entry);

		// Ungroup no longer grouped texts
		foreach (explode(',', $text->text) as $id) {
			if (!in_array($id, $idList)) {
				$t = new pTextEntry($id);
				$t->grouped = false;
				$t->save();
			}
		}

		// Update the Grouped textblock
		$text->text = $list;
		$text->save();

		// Group all assigned texts
		foreach ($idList as $id) {
			$text = new pTextEntry($id);
			if (!$text->grouped) {
				$text->grouped = true;
				$text->save();
			}
		}
	}

	/**
	 * Get all Text IDs from Menu $menuId
	 *
	 * @param int $menuId
	 * @return array[int]
	 * @access private
	 */
	private function getAllTexts($menuId, $language=0) {
		$back = array();
		$lang = pMessages::getLanguageInstance();
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;

		if ($language <= 0) {
			$language = $lang->getLanguageId();
		}

		$sql = 'SELECT [txt.id] FROM [table.txt] WHERE [txt.menu]='.(int)$menuId.' AND [txt.lang]='.$language.' AND [txt.plugin]!=\'GROUP\';';
		$db->run($sql, $res);
		if ($res->getFirst()) {
			while ($res->getNext()) {
				$back[] = (int)$res->{$db->getFieldName('txt.id')};
			}
		}
		return $back;
	}

}
?>