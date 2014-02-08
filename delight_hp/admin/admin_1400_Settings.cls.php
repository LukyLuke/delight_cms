<?php

/**
 * Admin-Class for NEWS
 *
 */
class admin_1400_Settings extends admin_MAIN_Settings {

	/**
	 * constructor
	 *
	 * @return admin_1400_Settings
	 */
	public function __construct() {
		parent::__construct();
		$obj = new NEWS($this->DB, $this->LANG);
		unset($obj);
	}

	/**
	 * Call a function, based on parameter adm
	 * This is the main function, which will be called for getting some content
	 */
	public function createActionBasedContent() {
		$userCheck = pCheckUserData::getInstance();

		// Check Access
		if ($userCheck->checkAccess($this->_adminAccess)) {
			// Check which Action is called and return the appropriate content or JSON
			switch (pURIParameters::get('action', '', pURIParameters::$STRING)) {
				case 'template':
					$tpl = pURIParameters::get('template', 'news', pURIParameters::$STRING);
					$section = pURIParameters::get('section', 0, pURIParameters::$INT);
					$entry = pURIParameters::get('entry', 0, pURIParameters::$INT);
					$isContent = pURIParameters::get('iscontent', false, pURIParameters::$BOOL);

					$tpl = $this->getAdminContent($tpl, 1400);
					$tpl = str_replace('[SECTION_ID]', $section, $tpl);
					$tpl = str_replace('[ENTRY_ID]', $entry, $tpl);

					// If this is a Content-Plugin Request, replace some additional variables
					if ($isContent) {
						$text = $this->getTextEntryObject($entry);
						$tpl = str_replace('[ENTRY_TEMPLATE]', $text->layout, $tpl);
						$tpl = str_replace('[ENTRY_OPTIONS]', json_encode($text->optionsArray), $tpl);
						unset($text);
					}

					echo $this->ReplaceAjaxLanguageParameters( $tpl );
					exit();
					break;

				case 'editor':
					$entry = pURIParameters::get('entry', 0, pURIParameters::$INT);
					$section = pURIParameters::get('section', 0, pURIParameters::$INT);
					$obj = $this->getNewsObject($entry);

					$tpl = $this->getAdminContent('editor_news', 1400);
					$tpl = str_replace('[SECTION_ID]', $section, $tpl);
					$tpl = str_replace('[ENTRY_ID]', $entry, $tpl);
					$tpl = str_replace('[TITLE]', $obj->title, $tpl);
					$tpl = str_replace('[TEXT]', $obj->text, $tpl);
					$tpl = str_replace('[SHORT]', $obj->short, $tpl);
					$tpl = str_replace('[DATE]', $obj->date, $tpl);
					$tpl = str_replace('[DATE_EXTENDED]', $obj->fulldate, $tpl);
					$tpl = str_replace('[TIMESTAMP]', $obj->timestamp, $tpl);

					echo $this->ReplaceAjaxLanguageParameters( $tpl );
					exit();
					break;
				case 'editortext':
					$entry = pURIParameters::get('entry', 0, pURIParameters::$INT);
					$section = pURIParameters::get('section', 0, pURIParameters::$INT);
					$title = pURIParameters::get('title', '', pURIParameters::$STRING);
					$content = pURIParameters::get('content', '', pURIParameters::$STRING);
					$short = pURIParameters::get('short', '', pURIParameters::$STRING);

					$id = $this->saveNews($entry, $section, $title, $content, $short, false);
					$tpl = pURIParameters::get('template', 'success_close', pURIParameters::$STRING);

					echo $this->ReplaceAjaxLanguageParameters( $this->getAdminContent($tpl, 1400, false, 'NewsManagement') );
					exit();
					break;

				case 'date':
					$entry = pURIParameters::get('entry', 0, pURIParameters::$INT);
					$section = pURIParameters::get('section', 0, pURIParameters::$INT);
					$date = urldecode(pURIParameters::get('date', '', pURIParameters::$STRING));
					if (empty($date)) {
						$obj = $this->getNewsObject($entry);
						$tpl = $this->getAdminContent('date', 1400, true);
						$tpl = str_replace('[CURRENT_DATE]', date('Y-m-d H:i:s', $obj->timestamp), $tpl);
						$tpl = str_replace('[ENTRY_ID]', $entry, $tpl);
						$tpl = str_replace('[SECTION_ID]', $section, $tpl);
						echo $this->ReplaceAjaxLanguageParameters($tpl);
						exit();
					}

					$this->setNewsDate($entry, $date);
					echo $this->ReplaceAjaxLanguageParameters( $this->getAdminContent('success_close', 1400, false, 'NewsManagement') );
					exit();
					break;

				case 'addrss':
					$entry = pURIParameters::get('entry', 0, pURIParameters::$INT);
					$section = pURIParameters::get('section', 0, pURIParameters::$INT);
					$feed_title = pURIParameters::get('feed_title', '', pURIParameters::$STRING);
					$feed_summarize = pURIParameters::get('feed_summarize', 3600, pURIParameters::$INT);
					$feed_refresh = pURIParameters::get('feed_refresh', 86400, pURIParameters::$INT);
					$feed_uri = pURIParameters::get('feed_uri', '', pURIParameters::$STRING);
					$feed_uri = preg_replace('/[\r\n]+/smi', chr(10), $feed_uri);

					if (empty($feed_uri)) {
						$obj = $this->getNewsObject($entry);
						$tpl = $this->getAdminContent('rss_editor', 1400, true);
						$tpl = str_replace('[ENTRY_ID]', $entry, $tpl);
						$tpl = str_replace('[SECTION_ID]', $section, $tpl);
						$tpl = str_replace('[RSS_FEED_TITLE]', $obj->feed_list->title, $tpl);
						$tpl = str_replace('[RSS_FEED_SUMMARIZE]', $obj->feed_list->summarize, $tpl);
						$tpl = str_replace('[RSS_FEED_CACHEAGE]', $obj->feed_list->max_cache_age, $tpl);
						$tpl = str_replace('[RSS_FEED_URI]', implode(chr(10), $obj->feed_list->feeds), $tpl);
						echo $this->ReplaceAjaxLanguageParameters($tpl);
						exit();
					}

					$content  = '[feed]'.chr(10);
					$content .= 'title='.$feed_title.chr(10);
					$content .= 'summarize='.$feed_summarize.chr(10);
					$content .= 'cacheage='.$feed_refresh.chr(10);
					$content .= 'feed[]='.str_replace(chr(10), chr(10).'feed[]=', $feed_uri).chr(10);

					$id = $this->saveNews($entry, $section, $feed_title, $content, '', true);
					echo $this->ReplaceAjaxLanguageParameters( $this->getAdminContent('success_close', 1400, false, 'NewsManagement') );
					exit();
					break;

				case 'sections':
					echo $this->_getJSONSectionList(0, 'nes', 'nes');
					exit();
					break;

				case 'content':
					$entity = pURIParameters::get('entity', 0, pURIParameters::$INT);
					$section = pURIParameters::get('section', 0, pURIParameters::$INT);
					$pageContent = pURIParameters::get('page_content', false, pURIParameters::$BOOL);
					$settingsContent = pURIParameters::get('page_settings', 0, pURIParameters::$INT);
					$template = pURIParameters::get('template', 'default', pURIParameters::$STRING);
					$options = pURIParameters::get('options', '', pURIParameters::$STRING);

					echo '{"section":'.$section.',"page_content":'.($pageContent||$settingsContent ? 'true' : 'false').',';
					if ($pageContent) {
						echo $this->_getJSONPageContent($entity, $section, $options, $template);

					} else if ($settingsContent > 0) {
						echo $this->_getJSONPageSettings($settingsContent);

					} else {
						echo $this->_getJSONNewsList($section);
					}
					echo '}';
					exit();
					break;

				case 'reorder':
					$section = pURIParameters::get('section', 0, pURIParameters::$INT);
					$order = explode(',', pURIParameters::get('order', '', pURIParameters::$STRING));
					//$success = $this->changeImageOrder($section, $order); //TODO
					$success = false;
					echo '{"section":'.$section.',"success":'.($success ? 'true' : 'false').'}';
					exit();
					break;

				case 'section':
					$id = pURIParameters::get('id', 0, pURIParameters::$INT);
					$parent = pURIParameters::get('parent', 0, pURIParameters::$INT);
					$name = pURIParameters::get('name', 'Neuer Ordner', pURIParameters::$STRING);
					$newid = $this->_changeSectionParameters($id, $name, $parent, 'nes');
					if ($id != $newid) {
						echo '{"adm":1000,"name":"'.$this->_escapeJSONString($name).'", "oldid":'.$id.', "id":'.$newid.', "parent":'.$parent.',"call":"sectionCreateFinal","click":"NewsManagement.sectionClick","success":true}';
					} else {
						echo '{"adm":1000,"success":true}';
					}
					exit();
					break;

				case 'section_remove':
					$id = pURIParameters::get('id', 0, pURIParameters::$INT);
					$this->deleteSection($id);
					echo '{"id":'.$id.',"call":"sectionDeleteFinal","success":true}';
					exit();
					break;

				case 'remove':
					$id = pURIParameters::get('id', 0, pURIParameters::$INT);
					$section = $this->_getSectionIdFromObject($id, 'new');
					$this->deleteNews($id);
					echo '{"section":'.$section.', "success":true,"call":"reloadContent","scope":"NewsManagement"}';
					exit();
					break;

				case 'save_page':
					$entity = pURIParameters::get('entity', 0, pURIParameters::$INT);
					$section = pURIParameters::get('section', 0, pURIParameters::$INT);
					$template = pURIParameters::get('template', 'default', pURIParameters::$STRING);
					$title = pURIParameters::get('title', 'default', pURIParameters::$STRING);
					$options = pURIParameters::get('options', new stdClass(), pURIParameters::$OBJECT);
					$lang = new pLanguage(pURIParameters::get('lang', MASTER_LANGUAGE, pURIParameters::$STRING));

					$opt = '';
					$opt .= '#show_recursive='.((isset($options->show_recursive) && ($options->show_recursive == 'true')) ? 'true' : 'false').'#';
					$opt .= '#show_section='.((isset($options->show_section) && ($options->show_section == 'true')) ? 'true' : 'false').'#';
					$opt .= '#show_title='.((isset($options->show_title) && ($options->show_title == 'true')) ? 'true' : 'false').'#';
					$opt .= '#title='.((isset($options->title)) ? $options->title : 'default').'#';
					$opt .= '#show_number='.((isset($options->show_number)) ? $options->show_number : '0').'#';

					$text = $this->getTextEntryObject($entity);
					$text->text = $section;
					$text->layout = $template;
					$text->options = $opt;
					$text->title = $title;
					$text->lang = $lang->languageId;

					$success = $text->save();

					echo '{"success":'.($success ? 'true' : 'false').',"call":"contentSaved"}';
					exit();
					break;
			}

		} else {
			$this->showNoAccess();
		}
	}

	/**
	 * Get the NewsList from a Section as a JSON-String
	 *
	 * @param integer $section The SectionID to get all News from
	 * @return string JSON compatible String with all News
	 */
	private function _getJSONNewsList($section) {
		$message = pMessages::getLanguageInstance();
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = 'SELECT [new.id] FROM [table.new] WHERE [new.section]='.(int)$section.' AND [new.lang]='.$message->getLanguageId().' ORDER BY [new.date] DESC;';
		$db->run($sql, $res);
		$back = '[';
		if ($res->getFirst()) {
			while ($res->getNext()) {
				if (strlen($back) > 1) {
					$back .= ',';
				}
				$news = $this->getNewsObject($res->{$db->getFieldName('new.id')});
				$back .= '{';
				$back .= '"id":"'.$news->id.'",';
				$back .= '"title":"'.$this->_escapeJSONString($news->title, false).'",';
				$back .= '"text":"'.$this->_escapeJSONString($news->text, false).'",';
				$back .= '"date":"'.$this->_escapeJSONString($news->date).'",';
				$back .= '"fulldate":"'.$this->_escapeJSONString($news->fulldate).'",';
				$back .= '"section":"'.$news->section.'",';
				$back .= '"rss":'.($news->rss ? 'true' : 'false');
				$back .= '}';
			}
		}
		$res = null;
		$back .= ']';
		return '"list":'.$back;
	}

	/**
	 * Get FileList as JSON-Compatible HTML-Content to show in the Page directly
	 *
	 * @param int $entity Text ID
	 * @param int $section Section to get files from
	 * @param boolean $options
	 * @param string $template
	 * @return string
	 */
	private function _getJSONPageContent($entity, $section, $options=null, $template=null) {
		$data = array();
		$data['layout'] = is_null($template) ? 'default' : $template;
		$data['id'] = $entity;
		$data['sort'] = 0;
		$data['text'] = $section;
		$data['title'] = '';
		$data['menu'] = 0;
		$data['lang'] = 1;
		$data['plugin'] = 'NEWS';
		$data['options'] = is_null($options) ? '#show_recursive=false##show_title=true##show_section=false##title=default#' : json_decode($options);

		$down = new NEWS();
		$back = $down->getSource('', $data);
		$back = $this->ReplaceAjaxLanguageParameters($back);
		$back = $this->_escapeJSONString($back, false);
		$back = preg_replace('/\s+/smi', ' ', $back);

		return '"list":"'.$back.'"';
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
	 * Store the changed news in the database and redirect the user back to the NewsList
	 * Use $this->setNewsDate($id, $date) to set a new Date for an existent News
	 *
	 * @param integer $id The NewsID or 0 if this is a new entry
	 * @param integer $section The SectionID to store the News in
	 * @param string $title News-Title
	 * @param string $text The News-Text
	 * @param boolean $rss Set this to TRUE if this NewsEntry defines an RSS-Newsfeed and not a normal News-Entry
	 * @access private
	 * @return integer the NewsID
	 */
	private function saveNews($id, $section, $title, $text, $short, $rss=false) {
		$messages = pMessages::getLanguageInstance();
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		if ((int)$id > 0) {
			$sql = 'UPDATE [table.new] SET [field.new.title]=\''.mysql_real_escape_string($title).'\',[field.new.text]=\''.mysql_real_escape_string($text).'\',[field.new.short]=\''.mysql_real_escape_string($short).'\' WHERE [field.new.id]='.(int)$id.';';
			$db->run($sql, $res);
		} else {
			$sql  = 'INSERT INTO [table.new] ([field.new.title],[field.new.text],[field.new.short],[field.new.date],[field.new.section],[field.new.rss],[field.new.lang])';
			$sql .= ' VALUES (\''.mysql_real_escape_string($title).'\',\''.mysql_real_escape_string($text).'\',\''.mysql_real_escape_string($short).'\',\''.date('Y-m-d H:i:s', time()).'\','.$section.','.($rss ? '1' : '0').',\''.$messages->getLanguageId().'\')';
			$db->run($sql, $res);
			$id = $res->getInsertId();
		}
		return $id;
	}

	/**
	 * Set a new Date for a News
	 *
	 * @param integer $id The NewsID
	 * @param string $date The Date as a String
	 * @access private
	 */
	private function setNewsDate($id, $date) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$time = strtotime($date);
		if ( ((int)$id > 0) && ($time !== false)) {
			$sql = 'UPDATE [table.new] SET [field.new.date]=\''.date('Y-m-d H:i:s', $time).'\' WHERE [field.new.id]='.(int)$id.';';
			$db->run($sql, $res);
		}
		$res = null;
	}

	/**
	 * Delete a News
	 *
	 * @param integer $id News-ID to delete
	 */
	private function deleteNews($id) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = 'DELETE FROM [table.new] WHERE [new.id]='.$id.';';
		$db->run($sql, $res);
		$res = null;
	}

	/**
	 * Delete the given Section and all Content and SubSections
	 *
	 * @param integer $id SectionID to delete
	 * @access private
	 */
	private function deleteSection($id) {
		// First get the Parent-Section to switch to after a successfull delete
		$_parent = $this->_getParentSectionData($id, 'nes');
		$_parent = $_parent['par'];
		$_childs = $this->_getChildSections($id, 'nes', true, array($id));
		$_childs = array_reverse($_childs);

		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;

		// Delete each Section
		foreach ($_childs as $v) {
			$sql = "SELECT [new.id] FROM [table.new] WHERE [new.section]=".(int)$v.";";
			$db->run($sql, $res);
			if ($res->getFirst()) {
				while ($res->getNext()) {
					$this->deleteNews($res->{$db->getFieldName('new.id')});
				}
			}
			// finally delete the Section
			$sql = "DELETE FROM [table.nes] WHERE [nes.id]=".(int)$v.";";
			$db->run($sql, $res);
			$res = null;
		}
	}

}
?>