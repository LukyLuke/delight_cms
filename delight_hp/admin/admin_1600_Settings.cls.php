<?php

/**
 * Special Page-Content Administration
 */

class admin_1600_Settings extends admin_MAIN_Settings {

	/**
	 * initialization
	 *
	 * @access public
	 */
	public function __construct() {
		parent::__construct();
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
					$tpl = pURIParameters::get('template', 'specialcontent', pURIParameters::$STRING);
					$tpl = $this->getAdminContent($tpl, 1600);
					echo $this->ReplaceAjaxLanguageParameters($tpl);
					exit();
					break;
				case 'create':
					$tpl = pURIParameters::get('template', 'specialcontent_create', pURIParameters::$STRING);
					$tpl = $this->getAdminContent($tpl, 1600);
					$var = pURIParameters::get('variable', '', pURIParameters::$STRING);
					$tpl = str_replace('[VARIABLE]', $var, $tpl);
					echo $this->ReplaceAjaxLanguageParameters($tpl);
					exit();
					break;

				case 'docreate':
					$var = pURIParameters::get('variable', '', pURIParameters::$STRING);
					$call = pURIParameters::get('call', 'close', pURIParameters::$STRING);
					$type = pURIParameters::get('type', pSpecialContent::TYPE_STRING, pURIParameters::$INT);
					$success = $this->createVariable($var, $type);
					echo '{"section":"'.$var.'","success":'.($success ? 'true' : 'false').',"call":"'.$call.'","scope":"'.(($call=='close')?'AdminDialog':'SpecialcontentManagement').'"}';
					exit();
					break;
					break;

				case 'save':
					$var   = pURIParameters::get('variable', '', pURIParameters::$STRING);
					$id    = pURIParameters::get('selected', '', pURIParameters::$STRING);
					$value = pURIParameters::get('value', '', pURIParameters::$STRING);
					$menu  = pURIParameters::get('menu', 0, pURIParameters::$INT);
					$recursive = pURIParameters::get('recursive', false, pURIParameters::$BOOL);
					$settings  = pURIParameters::get('settings', '', pURIParameters::$STRING);

					$success = $this->saveVariable($var, $id, $value, $menu, $recursive, $settings);
					echo '{"section":"'.$var.'","success":'.($success ? 'true' : 'false').'}';
					exit();
					break;
				case 'remove':
					$id = pURIParameters::get('id', '', pURIParameters::$STRING);
					$id = explode('_', $id);
					$realId = array_pop($id);
					$id = implode('_', $id);
					$this->deleteVariable($realId, $id);
					echo '{"section":"'.$id.'_'.$realId.'","success":true,"scope":"SpecialcontentManagement","call":"_removeElements","list":["'.$id.'_'.$realId.'_tab","'.$id.'_'.$realId.'_panel"]}';
					exit();
					break;

				case 'sections':
					echo $this->_getJSONVariablesList();
					exit();
					break;

				case 'content':
					$var = pURIParameters::get('section', null, pURIParameters::$STRING);
					echo '{"section":"'.$var.'","list":'.json_encode($this->getVariables($var)).'}';
					exit();
					break;
				case 'getvalue':
					$id = pURIParameters::get('id', 0, pURIParameters::$INT);
					$variable = pURIParameters::get('variable', '', pURIParameters::$STRING);
					$element = pURIParameters::get('element', '', pURIParameters::$STRING);
					$value = pURIParameters::get('value', '', pURIParameters::$STRING);
					$o = $this->getVariableValue($variable, $id, $value);
					echo '{"section":"'.$variable.'","success":true,"element":"'.$element.'","call":"'.$o->call.'","data":'.json_encode($o->data).'}';
					exit();
					break;

				case 'list':
					$type = pURIParameters::get('type', null, pURIParameters::$STRING);
					$container = pURIParameters::get('container', '', pURIParameters::$STRING);
					echo '{"success":true,"call":"_setContainerHtml","type":"'.$type.'","container":"'.$container.'",';
					echo '"sections":'.$this->getTypeSectionList($type).',';
					echo '"content":'.json_encode($this->getTypeListHTML($type)).'}';
					exit();
					break;
				case 'list_content':
					$section = pURIParameters::get('section', 0, pURIParameters::$INT);
					$type = pURIParameters::get('type', null, pURIParameters::$STRING);
					$container = pURIParameters::get('container', '', pURIParameters::$STRING);
					echo '{"success":true,"call":"_showListContent","type":"'.$type.'","container":"'.$container.'",';
					echo '"content":'.json_encode($this->getTypeListContent($type, $section)).'';
					echo '}';
					exit();
					break;

				case 'menuchooser':
					$entry = pURIParameters::get('entry', 0, pURIParameters::$INT);
					$element = pURIParameters::get('element', '', pURIParameters::$STRING);
					$recursive = pURIParameters::get('recursive', 0, pURIParameters::$INT);
					$menu = pMenu::getMenuInstance();

					echo '{"success":true,"element":"'.$element.'","recursive":'.$recursive.',"call":"_createMenuChooserCall","menu":';
					echo json_encode($menu->getStructureObject($entry));
					echo '}';
					exit();
					break;
			}
		}
	}

	/**
	 * Return the SectionList for the given Type (images, files, news, texts)
	 * @param string $type Type for which to get the SectionList
	 * @return JSON SectionList
	 */
	protected function getTypeSectionList($type) {
		switch (strtolower($type)) {
			case 'images': return $this->_getJSONSectionList(0, 'ims', 'ims'); break;
			case 'files':  return $this->_getJSONSectionList(0, 'prs', 'prs'); break;
			case 'news':   return $this->_getJSONSectionList(0, 'nes', 'nes'); break;
			case 'texts':  return json_encode(pMenu::getMenuInstance()->getStructureObject()); break;
		}
		return '[]';
	}

	/**
	 * Get HTML Content to show Sections and the Content
	 *
	 * @param string $type
	 * @return string
	 */
	protected function getTypeListHTML($type) {
		return '<div class="section noToolbar"><div class="content" id="subsection"></div></div><div class="container noToolbar sectionContent"><div class="content" id="subcontent"></div></div>';
	}

	/**
	 * Return Content from given Section and Type
	 * @param string $type
	 * @param int $section
	 * @return HTML
	 */
	protected function getTypeListContent($type, $section) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$html = '';
		switch (strtolower($type)) {
			case 'images':
				$sql = 'SELECT [img.id] FROM [table.img] WHERE [img.section]='.(int)$section.' ORDER BY [img.order];';
				$db->run($sql, $res);
				if ($res->getFirst()) {
					while ($res->getNext()) {
						$image = new pImageEntry($res->{$db->getFieldName('img.id')});
						$image->setSize(120, 80);
						$html .= '<div style="float:left;padding:3px;margin:3px;border:1px solid black;position:relative;" id="'.$type.'_'.$image->id.'">';
						if ($image->title != '') {
							$html .= '<strong style="font-size:7pt;position:absolute;top:0;left:0;right:0;padding:2px;background:white;opacity:0.7;">'.$image->title.'</strong>';
						}
						$html .= '<img src="'.$image->url.'" alt="'.$image->title.'" title="'.$image->title.'" />';
						$html .= '</div>';
					}
				}
				break;

			case 'files':
				$sql = 'SELECT [prg.id] FROM [table.prg] WHERE [prg.section]='.(int)$section.' ORDER BY [prg.order];';
				$db->run($sql, $res);
				if ($res->getFirst()) {
					while ($res->getNext()) {
						$file = new pFileEntry($res->{$db->getFieldName('prg.id')});
						$html .= '<div style="clear:both;padding:3px;margin:3px;border:1px solid black;" id="'.$type.'_'.$file->id.'">';
						$html .= '<img src="'.$file->icon.'" alt="'.$file->mime.'" title="'.$file->comment.'" style="vertical-align:middle;" />';
						$html .= ' '.$file->name.' <em>('.$file->getExtendedDate($file->date, false, true).')</em>';
						$html .= '</div>';
					}
				}
				break;

			case 'news':
				$lang = pMessages::getLanguageInstance();
				$sql = 'SELECT [new.id] FROM [table.new] WHERE [new.section]='.(int)$section.' AND [new.lang]='.$lang->getLanguageId().' ORDER BY [new.date] DESC;';
				$db->run($sql, $res);
				if ($res->getFirst()) {
					while ($res->getNext()) {
						$news = new pNewsEntry($res->{$db->getFieldName('new.id')});
						$html .= '<div style="clear:both;padding:3px;margin:3px;border:1px solid black;" id="'.$type.'_'.$news->id.'">';
						$html .= $news->title.' <em>('.$news->getExtendedDate($news->date, false, true).')</em>';
						$html .= '</div>';
					}
				}
				break;

			case 'texts':
				$lang = pMessages::getLanguageInstance();
				$sql = 'SELECT [txt.id] FROM [table.txt] WHERE [txt.menu]='.(int)$section.' AND [txt.lang]='.$lang->getLanguageId().' ORDER BY [txt.sort];';
				$db->run($sql, $res);
				if ($res->getFirst()) {
					while ($res->getNext()) {
						$text = new pTextEntry($res->{$db->getFieldName('txt.id')});
						$html .= '<div style="clear:both;padding:3px;margin:3px;border:1px solid black;" id="'.$type.'_'.$text->id.'">';
						$html .= $text->title;
						$html .= '</div>';
					}
				}
				break;
		}
		return $html;
	}

	/**
	 * Get all Configurations from given Variable
	 *
	 * @param string $variable
	 * @return array
	 */
	private function getVariables($variable) {
		$back = array();

		$contents = new pSpecialContent($variable);
		$count = 0;
		foreach ($contents as $content) {
			$o = new stdClass();
			$o->count = $count++;
			$o->name = $content->var;
			$o->id = $content->id;
			$o->menu = $content->menu;
			$o->recursive = $content->recursive;
			$o->type = $content->type;
			$o->value = $contents->getValue($content);
			$o->settings = array();
			foreach ($content->settings as $setting) {
				$o->settings[] = '';
			}

			$back[] = $o;
		}

		return $back;
	}

	/**
	 * Create the Variables-List to show as JSON-Sectionlist
	 *
	 * @access protected
	 */
	protected function _getJSONVariablesList() {
		$back = array();
		$seen = array();
		foreach ($this->getContentVariables() as $var) {
			if (in_array($var->name, $seen)) {
				continue;
			}
			$seen[] = $var->name;

			$_name = explode('_', $var->name);
			$_sectionName = array_shift($_name);
			$_entry = -1;
			for ($i = 0; $i < count($back); $i++) {
				if ($back[$i]->name == $_sectionName) {
					$_entry = $i;
					break;
				}
			}
			if ($_entry < 0) {
				$o = new stdClass();
				$o->name = $_sectionName;
				$o->sub = array();
				$o->id = $_sectionName;
				$o->options = 'null';
				$back[] = $o;
				$_entry = count($back) - 1;
			}
			if (count($_name) > 0) {
				$obj = new stdClass();
				$obj->name = implode('_', $_name);
				$obj->sub = array();
				$obj->id = $_sectionName.'_'.$obj->name;
				$obj->options = json_decode($var->options);
				$back[$_entry]->sub[] = $obj;
			} else {
				$back[$_entry]->options = json_decode($var->options);
			}
		}
		return json_encode($back);
	}

	/**
	 * Get all defined Variables from Content-Files
	 *
	 * @return array[stdClass{ name, content, options }]
	 * @access private
	 */
	private function getContentVariables($name=null) {
		$back = array();
		foreach (scandir(ABS_TEMPLATE_DIR) as $file) {
			if (is_file(ABS_TEMPLATE_DIR.$file)) {
				$cont = file_get_contents(ABS_TEMPLATE_DIR.$file);
				$back = array_merge($back, $this->parseContentVariables($cont));
			}
		}
		return $back;
	}


	private function parseContentVariables($cont) {
		$back = array();
		$match = array();
		if (preg_match_all('/(\[PAGE_CONTENT:)([^:\]]+)(:([^]]+))?(\])(.*?)(\[\/PAGE_CONTENT(:\\2)?\])/smi', $cont, $match, PREG_SET_ORDER) > 0) {
			foreach ($match as $m) {
				$o = new stdClass();
				$o->name = $m[2];
				$o->content = $m[6];
				$o->options = str_replace('(', '[', str_replace(')', ']', $m[4]));

				if (empty($o->options)) {
					$o->options = '[]';
				}
				$back[] = $o;
				$back = array_merge($back, $this->parseContentVariables($m[6]));
			}
		}
		return $back;
	}

	/**
	 * Delete a Configuration
	 *
	 * @param int $id ID to delete
	 * @param string $variable Variable to load
	 * @return boolean
	 * @access protected
	 */
	protected function deleteVariable($id, $variable) {
		$contents = new pSpecialContent($variable);
		return $contents->delete($id);
	}

	/**
	 * Create a new Variable-Configuration
	 *
	 * @param string $variable Variable to create a Configuration for
	 * @param int $type Content type
	 * @return boolean
	 * @access protected
	 */
	protected function createVariable($variable, $type) {
		$contents = new pSpecialContent($variable);
		return $contents->create($variable, $type) > 0;
	}

	/**
	 * Change a Variable Configuration
	 *
	 * @param string $variable
	 * @param int $id
	 * @param string $value
	 * @param int $menu
	 * @param boolean $recursive
	 * @param string $settings
	 * @return boolean
	 */
	protected function saveVariable($variable, $id, $value, $menu, $recursive, $settings) {
		$contents = new pSpecialContent($variable);
		return $contents->change($variable, $id, $value, $menu, $recursive, $settings);
	}


	protected function getVariableValue($variable, $id, $val=null) {
		$contents = new pSpecialContent($variable, $id);
		$back = new stdClass();
		$back->call = '_setElementValue';
		$back->data = '';

		foreach ($contents as $content) {
			if (($content->id == $id) && ($content->var == $variable)) {
				switch ($content->type) {
					case pSpecialContent::TYPE_INT:
					case pSpecialContent::TYPE_STRING:
					case pSpecialContent::TYPE_FLOAT:
						$back->data = empty($val) ? $content->value : $val;
						break;
					case pSpecialContent::TYPE_IMAGE:
						$value = empty($val) ? $content->value : new pImageEntry($val);
						$value->setSize(80, 50);
						$back->data = '<img src="'.$value->url.'" style="width:'.$value->width.'px;height:'.$value->height.'px;" alt="image" title="choose" />';
					break;
					case pSpecialContent::TYPE_FILE:
						$value = empty($val) ? $content->value : new pFileEntry($val);
						$back->data = $value->icon_tag.' '.$value->name.' <em>('.$value->getExtendedDate($value->date, false, true).')</em>';
						break;
					case pSpecialContent::TYPE_NEWS:
						$value = empty($val) ? $content->value : new pNewsEntry($val);
						$back->data = $value->title;
						break;
					case pSpecialContent::TYPE_TEXT:
						$value = empty($val) ? $content->value : new pTextEntry($val);
						$back->data = $value->title;
						break;
				}
				break;
			}
		}

		return $back;
	}

}
?>