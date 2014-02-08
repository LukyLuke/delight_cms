<?php

/**
 * GlobalTexts Administration
 */

class admin_1800_Settings extends admin_MAIN_Settings {

	/**
	 * initialization
	 *
	 * @access public
	 */
	public function __construct() {
		parent::__construct();
		new pGlobalText(0);
	}

	/**
	 * Call a function, based on parameter adm
	 * This is the main function, which will be called for getting some content
	 *
	 * @access public
	 */
	public function createActionBasedContent() {
		$userCheck = pCheckUserData::getInstance();

		// Check for Access
		if ($userCheck->checkAccess($this->_adminAccess)) {
			// Check which Action is called and return the appropriate content or JSON
			switch (pURIParameters::get('action', '', pURIParameters::$STRING)) {
				case 'template':
					$tpl = pURIParameters::get('template', 'globaltext', pURIParameters::$STRING);
					$section = pURIParameters::get('section', 0, pURIParameters::$INT);
					$entry = pURIParameters::get('entry', 0, pURIParameters::$INT);
					$isContent = pURIParameters::get('iscontent', false, pURIParameters::$BOOL);

					$tpl = $this->getAdminContent($tpl, 1800);
					$tpl = str_replace('[SECTION_ID]', $section, $tpl);
					$tpl = str_replace('[ENTRY_ID]', $entry, $tpl);

					// If this is a Content-Plugin Request, replace some additional variables
					if ($isContent) {
						$text = $this->getTextEntryObject($entry);
						$tpl = str_replace('[ENTRY_TEMPLATE]', $text->layout, $tpl);
						$tpl = str_replace('[ENTRY_OPTIONS]', json_encode($text->optionsArray), $tpl);
						unset($text);
					}

					echo $this->ReplaceAjaxLanguageParameters($tpl);
					exit();
					break;

				case 'editor':
					$entry = pURIParameters::get('entry', 0, pURIParameters::$INT);
					$section = pURIParameters::get('section', 0, pURIParameters::$INT);

					if ($entry == 0) {
						$obj = new pGlobalText(null);
						$obj->plugin = 'TEXT';
						$obj->lang = pMessages::getLanguageInstance()->getLanguageId();
						$obj->section = $section;
						$obj->title = "";
						$obj->text = "";
						$obj->save();
						$entry = $obj->id;
					}
					$obj = new pGlobalText($entry);

					$tpl = $this->getAdminContent('editor_full', 1800);
					$tpl = str_replace('[SECTION_ID]', $section, $tpl);
					$tpl = str_replace('[ENTRY_ID]', $obj->id, $tpl);
					$tpl = str_replace('[TITLE]', $obj->title, $tpl);
					$tpl = str_replace('[TEXT]', str_replace('<textarea', '<_textarea', str_replace('</textarea', '</_textarea', $obj->text)), $tpl);
					$tpl = str_replace('[OPTIONS]', $obj->options, $tpl);
					$tpl = str_replace('[LAYOUT]', $obj->layout, $tpl);
					echo $this->ReplaceAjaxLanguageParameters( $tpl );
					exit();
					break;
				case 'editortext':
					$entry = pURIParameters::get('entry', 0, pURIParameters::$INT);
					$section = pURIParameters::get('section', 0, pURIParameters::$INT);
					$title = pURIParameters::get('title', '', pURIParameters::$STRING);
					$content = pURIParameters::get('content', '', pURIParameters::$STRING);

					$obj = new pGlobalText($entry);
					$obj->plugin = 'TEXT';
					$obj->lang = pMessages::getLanguageInstance()->getLanguageId();
					$obj->section = $section;
					$obj->title = $title;
					$obj->text = $content;
					$obj->save();

					$tpl = pURIParameters::get('template', 'success_close', pURIParameters::$STRING);
					echo $this->ReplaceAjaxLanguageParameters( $this->getAdminContent($tpl, 1800, false, 'GlobaltextManagement') );
					exit();
					break;
				case 'get_formular_config':
					header('Content-Type: application/json');
					$text = pURIParameters::get('text', 0, pURIParameters::$INT);
					$config = $this->_getJSONFormularConfig($text);
					echo $this->ReplaceAjaxLanguageParameters($config);
					exit();
					break;
				case 'set_formular_config':
					header('Content-Type: application/json');
					$text = pURIParameters::get('text', 0, pURIParameters::$INT);
					$config = pURIParameters::get('config', new stdClass(), pURIParameters::$OBJECT);

					if ($this->_setJSONFormularConfig($text, $config)) {
						echo '{"success":true}';
					} else {
						echo '{"success":false}';
					}
					exit();
					break;

				case 'sections':
				case 'sectiontree':
					echo $this->_getJSONSectionList(0, 'gtsec', 'gtsec');
					exit();
					break;

				case 'content':
					$entity = pURIParameters::get('entity', 0, pURIParameters::$INT);
					$section = pURIParameters::get('section', 0, pURIParameters::$INT);
					$pageContent = pURIParameters::get('page_content', false, pURIParameters::$BOOL);
					$settingsContent = pURIParameters::get('page_settings', 0, pURIParameters::$INT);
					$template = pURIParameters::get('template', 'default', pURIParameters::$STRING);
					$options = pURIParameters::get('options', '', pURIParameters::$STRING);
					$selected = pURIParameters::get('selected', -1, pURIParameters::$INT);

					echo '{"section":'.$section.',"page_content":'.($pageContent||$settingsContent ? 'true' : 'false').',';
					if ($selected >= 0) {
						echo $this->_getJSONPageContent($selected, $entity, $options, $template);
					} else if ($settingsContent > 0) {
						echo $this->_getJSONPageSettings($settingsContent);
					} else {
						echo $this->_getJSONTextList($section);
					}
					echo '}';
					exit();
					break;

				case 'section':
					$id = pURIParameters::get('id', 0, pURIParameters::$INT);
					$parent = pURIParameters::get('parent', 0, pURIParameters::$INT);
					$name = pURIParameters::get('name', 'Neuer Ordner', pURIParameters::$STRING);
					$newid = $this->_changeSectionParameters($id, $name, $parent, 'gtsec');
					if ($id != $newid) {
						echo '{"adm":1800,"name":"'.$this->_escapeJSONString($name).'", "oldid":'.$id.', "id":'.$newid.', "parent":'.$parent.',"call":"sectionCreateFinal","click":"GlobaltextManagement.sectionClick","success":true}';
					} else {
						echo '{"adm":1800,"success":true}';
					}
					exit();
					break;

				case 'section_remove':
					$id = pURIParameters::get('id', 0, pURIParameters::$INT);
					$this->_deleteSection($id);
					echo '{"id":'.$id.',"call":"sectionDeleteFinal","success":true}';
					exit();
					break;

				case 'remove':
					$id = pURIParameters::get('id', 0, pURIParameters::$INT);
					$obj = new pGlobalText($id);
					$section = $obj->section;
					$obj->delete();
					echo '{"section":'.$section.',"success":true,"call":"reloadContent","scope":"GlobaltextManagement"}';
					exit();
					break;

				case 'save_page':
					$entity = pURIParameters::get('entity', 0, pURIParameters::$INT);
					$section = pURIParameters::get('section', 0, pURIParameters::$INT);
					$template = pURIParameters::get('template', 'default', pURIParameters::$STRING);
					$title = pURIParameters::get('title', 'default', pURIParameters::$STRING);
					$globalId = pURIParameters::get('text', '', pURIParameters::$STRING);
					$options = pURIParameters::get('options', new stdClass(), pURIParameters::$OBJECT);
					$lang = new pLanguage(pURIParameters::get('lang', MASTER_LANGUAGE, pURIParameters::$STRING));

					$opt = '';
					$opt .= '#title='.((isset($options->title)) ? $options->title : 'default').'#';

					$text = $this->getTextEntryObject($entity);
					$text->text = (int)$globalId;
					$text->layout = $template;
					$text->options = $opt;
					$text->title = $title;
					$text->lang = $lang->languageId;

					$success = $text->save();

					echo '{"success":'.($success ? 'true' : 'false').',"call":"contentSaved"}';
					exit();
					break;
			}
		}
	}

	private function _getJSONTextList($section) {
		$lang = pMessages::getLanguageInstance();
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$back = '[';
		$sql = 'SELECT [gtext.id] FROM [table.gtext] WHERE [gtext.section]='.(int)$section.' AND [gtext.lang]='.$lang->getLanguageId().';';
		$db->run($sql, $res);
		if ($res->getFirst()) {
			while ($res->getNext()) {
				$text = new pGlobalText($res->{$db->getFieldName('gtext.id')});
				if (strlen($back) > 1) {
					$back .= ',';
				}

				$back .= '{';
				$back .= '"id":'.$text->id.',';
				$back .= '"title":'.json_encode($text->title).',';
				$back .= '"text":'.json_encode($text->text).',';
				$back .= '"section":'.$text->section.',';
				$back .= '"plugin":"'.$text->plugin.'",';
				$back .= '"date":"'.date('Y-m-d H:i:s', $text->date).'"';
				$back .= '}';
			}
		}
		$res = null;
		$back .= ']';
		return '"list":'.$back;
	}

	private function _deleteSection($section) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;

		$_parent = $this->_getParentSectionData($section, 'gtsec');
		$_parent = $_parent['par'];
		$_childs = $this->_getChildSections($section, 'gtsec', true, array($section));
		$_childs = array_reverse($_childs);

		foreach ($_childs as $s) {
			$this->_deleteSectionTexts($s);
			$sql = 'DELETE FROM [table.gtsec] WHERE [field.gtsec.id]='.$s.';';
			$db->run($sql, $res);
			$res = null;
		}
	}

	private function _deleteSectionTexts($section) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = 'SELECT [gtext.id] FROM [table.gtext] WHERE [gtext.section]='.(int)$section.';';
		$db->run($sql, $res);
		if ($res->getFirst()) {
			while ($res->getNext()) {
				$text = new pGlobalText($res->{$db->getFieldName('gtext.id')});
				$text->delete();
			}
		}
		$res = null;
	}

/**
	 * Get Textlist as JSON-Compatible HTML-Content to show in the Page directly
	 *
	 * @param int $selected Selected GlobalText
	 * @param int $entity The EntityID
	 * @param boolean $options
	 * @param string $template
	 * @return string
	 */
	private function _getJSONPageContent($selected, $entity, $options=null, $template=null) {
		$data = array();
		$data['layout'] = is_null($template) ? 'default' : $template;
		$data['sort'] = 0;
		$data['id'] = $entity;
		$data['text'] = $selected;
		$data['title'] = '';
		$data['menu'] = 0;
		$data['lang'] = pMessages::getLanguageInstance()->getLanguageId();
		$data['plugin'] = 'GLOBALTEXT';
		$data['options'] = is_null($options) ? '#title=default#' : json_decode($options);

		$global = new GLOBALTEXT();
		$back = $global->getSource('', $data);
		$back = $this->ReplaceAjaxLanguageParameters($back);
		$back = $this->_escapeJSONString($back, false);
		$back = preg_replace('/\s+/smi', ' ', $back);

		return '"content":"'.$back.'"';
	}

	/**
	 * Get Settings and Layouts with preselected values from a given Textentry
	 *
	 * @param int $id TextID to get all Settings and Layouts from
	 * @return string
	 */
	private function _getJSONPageSettings($id) {
		$text = new pTextEntry($id);

		$options = $this->getPluginOptions($text->plugin);
		$layouts = $this->getLayoutList();

		foreach ($layouts as $layout) {
			foreach ($layout->options as $name => $option) {
				$options[$name] = $option;
			}
		}

		return '"settings":'.json_encode($options).',"layouts":'.json_encode($layouts).',"section":'.preg_replace('/[^\d]+/smi', '', $text->text).',"title":'.json_encode($text->title);
	}

	/**
	 * Set the Formularconfiguration from requested TextID
	 * @param int $id
	 * @param stdClass $config
	 * @return boolean
	 */
	protected function _setJSONFormularConfig($id, $config) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = 'DELETE FROM [table.formular] WHERE [formular.textid]='.(int)$id.' AND [formular.plugin]="GLOBALTEXT";';
		$db->run($sql, $res);
		$res = null;

		$sql = '';
		foreach ($config as $k => $v) {
			if (empty($sql)) {
				$sql = 'INSERT INTO [table.formular] ([field.formular.textid],[field.formular.field],[field.formular.value],[field.formular.plugin]) VALUES ';
			} else {
				$sql .= ',';
			}
			$sql .= '('.(int)$id.',\''.mysql_real_escape_string($k).'\',\''.mysql_real_escape_string($v).'\',\'GLOBALTEXT\')';
		}
		$sql .= ';';
		$db->run($sql, $res);

		return $res->numAffected() > 0;
	}

	/**
	 * Get the Formularconfiguration from requested TextID
	 * @param int $id
	 * @return JSON-String
	 */
	protected function _getJSONFormularConfig($id) {
		$config = new stdClass();
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = 'SELECT * FROM [table.formular] WHERE [formular.textid]='.(int)$id.' AND [formular.plugin]=\'GLOBALTEXT\';';
		$db->run($sql, $res);
		if ($res->getFirst()) {
			while ($res->getNext()) {
				$config->{$res->{$db->getFieldName('formular.field')}} = trim($res->{$db->getFieldName('formular.value')});
			}
		}
		return json_encode($config);
	}

}
?>