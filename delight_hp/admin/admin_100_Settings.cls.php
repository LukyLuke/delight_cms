<?php

/**
 * Text-Administration
 *
 */

class admin_100_Settings extends admin_MAIN_Settings {

	/**
	 * Initialization
	 */
	public function __construct() {
		parent::__construct();
		$obj = new TEXT();
		unset($obj);
	}

	/**
	 * FunctionCall for Administration
	 * @see delight_hp/admin/admin_MAIN_Settings#createActionBasedContent()
	 */
	public function createActionBasedContent() {
		$userCheck = pCheckUserData::getInstance();

		if ($userCheck->checkAccess($this->_adminAccess)) {
			// Check which Action is called and return the appropriate content or JSON
			switch (pURIParameters::get('action', '', pURIParameters::$STRING)) {
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
				case 'get_link_list':
					header('Content-Type: application/json');
					$type = strtolower(pURIParameters::get('type', 'm', pURIParameters::$STRING));
					$sel = pURIParameters::get('selected', '', pURIParameters::$STRING);
					$lang = pMessages::getLanguageInstance();

					$back = new stdClass();
					$back->success = true;
					$back->error = '';
					$back->type = $type;
					$back->lang = $lang->getShortLanguageName();

					switch($type) {
						case 'm':
							$back->entries = $this->_getMenuStructure(null, $sel);
							break;
						case 'p':
							$back->entries = $this->_getImageStructure(null, $sel);
							break;
						case 'f':
							$back->entries = $this->_getFileStructure(null, $sel);
							break;
						case 'n':
							$back->entries = $this->_getNewsStructure(null, $sel);
							break;
						case 't':
							$back->entries = $this->_getTextStructure(null, $sel);
							break;
						default:
							$back->success = false;
							$back->error = 'Unknown List-Type requested';
					}
					echo json_encode($back);
					exit();
					break;

				case 'save':
					header('Content-Type: application/json');
					$entry = pURIParameters::get('text', 0, pURIParameters::$INT);

					$text = new pTextEntry($entry);
					$text->title = pURIParameters::get('title', '', pURIParameters::$STRING);
					$text->layout = pURIParameters::get('layout', '', pURIParameters::$STRING);
					$text->options = pURIParameters::get('options', '', pURIParameters::$STRING);
					$text->text = pURIParameters::get('content', '', pURIParameters::$STRING);

					if ($text->save()) {
						$cont = $this->getTextentryContent($entry);
						echo '{"success":true,"content":'.json_encode($cont).'}';
					} else {
						echo '{"success":true}';
					}
					exit();
					break;
			}
		}

		// Insert and create the Class - this action will create all needed tables and Entries
		switch (100 + $this->_mainAction) {
			case 100: $this->showCreateEntry(); break;
			case 101: $this->showEditEntry();   break;
			case 103: $this->moveEntryUp();     break;
			case 104: $this->moveEntryDown();   break;
			case 102: $this->showDeleteConfirmation(); break;

			case 152: $this->doDeleteEntry(); break;
			case 150: $this->doCreateEntry(); break;
			case 151: $this->doUpdateEntry(); break;

			case 155: $this->sendAjaxTemplates(); break;
			case 156: $this->reloadTextEntry(); break;

			case 157: // Change the Textblock Order
				$order = explode(',', pURIParameters::get('order', '', pURIParameters::$STRING));
				$success = $this->changeTextOrder($order);
				echo '{"success":' + ($success ? 'true' : 'false') + '}';
				exit();
				break;
		}
	}

	// Show the "ENTRY-CREATE" form
	private function showCreateEntry(iDatabaseResult &$edit=null) {
		$userCheck = pCheckUserData::getInstance();
		$lang = pMessages::getLanguageInstance();
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;

		if (!is_null($edit)) {
			$textContent = $edit->{$db->getFieldName('txt.text')};
			$textLayout = $edit->{$db->getFieldName('txt.layout')};
			$textOptions = $edit->{$db->getFieldName('txt.options')};
			$textParser = $edit->{$db->getFieldName('txt.plugin')};
			$textTitle = $edit->{$db->getFieldName('txt.title')};
		} else {
			$textContent = pURIParameters::get('textContent', '', pURIParameters::$STRING);
			$textLayout = pURIParameters::get('textLayout', '', pURIParameters::$STRING);
			$textOptions = pURIParameters::get('textOptions', '', pURIParameters::$STRING);
			$textParser = pURIParameters::get('textParser', '', pURIParameters::$STRING);
			$textTitle = pURIParameters::get('textTitle', '', pURIParameters::$STRING);
		}

		if ($userCheck->checkAccess("content")) {
			if ($textParser == 'TEXT') {
				$textTitle = $lang->getValue($this->_langShort, 'text', 'new_text_title');
				$textContent = '<p>'.$lang->getValue($this->_langShort, 'text', 'new_text_content').'</p>';
			} else {
				$textTitle = 'default';
				$textContent = '0';
			}

			$textLayout = 'onlytext';
			$textOptions = '';

			// Get the Last Order-Sort from current entry
			$sql = 'SELECT MAX([txt.sort]) AS LastEntry FROM [table.txt] WHERE [txt.menu]='.pMenu::getMenuInstance()->getMenuId().';';
			$db->run($sql, $res);
			if ($res->getFirst()) {
				$last = (int)$res->LastEntry;
			} else {
				$last = -1;
			}

			$sql = 'INSERT INTO [table.txt] ([field.txt.text],[field.txt.title],[field.txt.layout],[field.txt.menu],[field.txt.lang],[field.txt.plugin],[field.txt.options],[field.txt.sort])'.
			' VALUES (\''.mysql_real_escape_string($textContent).'\',\''.mysql_real_escape_string($textTitle).'\','.
			'\''.mysql_real_escape_string($textLayout).'\','.pMenu::getMenuInstance()->getMenuId().','.$lang->getLanguageId().','.
			'\''.mysql_real_escape_string($textParser).'\',\''.mysql_real_escape_string($textOptions).'\','.($last+1).');';
			$db->run($sql, $res);
			$_insId = $res->getInsertId();
			$res = null;

			echo '<script type="text/javascript" language="Javascript">location.href="/'.$lang->getShortLanguageName().'/'.pMenu::getMenuInstance()->getMenuId().'/textParser='.$_insId.'";</script>';
		} else {
			$this->showNoAccess();
		}
	}

	// Show the "CHANGE-ENTRY" form
	private function showEditEntry() {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$userCheck = pCheckUserData::getInstance();
		if ($userCheck->checkAccess("content")) {
			$sql = 'SELECT * FROM [table.txt] WHERE [txt.id]='.$this->_changeId.';';
			$db->run($sql, $res);
			$this->showCreateEntry($res);
			$res = null;
		} else {
			$this->showNoAccess();
		}
	}

	// Do create the new Text-Entry
	private function doCreateEntry() {
		$userCheck = pCheckUserData::getInstance();
		$lang = new pLanguage();
		if ($userCheck->checkAccess("content")) {
			$this->doInsertTextEntry();
			$lnk = "/".$lang->short."/".pMenu::getMenuInstance()->getShortMenuName()."/adm=1";
			$this->_content = $this->_getContent('forwardOnly');
			$this->_content = str_replace("[FORWARD_LINK]", $lnk, $this->_content);
		} else {
			$this->showNoAccess();
		}
	}

	// Update the Selected Entry with the new Values
	private function doUpdateEntry() {
		$userCheck = pCheckUserData::getInstance();
		$lang = pMessages::getLanguageInstance()->getLanguage();
		if ($userCheck->checkAccess("content")) {
			$this->doInsertTextEntry();
			$lnk = "/".$lang->short."/".pMenu::getMenuInstance()->getShortMenuName()."/adm=1";
			$this->_content = $this->_getContent('forwardOnly');
			$this->_content = str_replace("[FORWARD_LINK]", $lnk, $this->_content);
		} else {
			$this->showNoAccess();
		}
	}

	// Show a Delete-Confirmation
	private function showDeleteConfirmation() {
		$userCheck = pCheckUserData::getInstance();
		$lang = pMessages::getLanguageInstance()->getLanguage();
		if ($userCheck->checkAccess("content")) {
			// get the SectionHTML
			//$_adminHtml = $this->_getContent('textEntryEdit', 'DELETE');
			$_adminHtml = $this->getAdminContent('text_delete');

			// define hidden fields
			$_hidden_fields = '
				<input type="hidden" name="i" value="'.$this->_changeId.'" />
				<input type="hidden" name="adm" value="'.((int)ADM_DELETE + 50).'" />
				<input type="hidden" name="m" value="'.pMenu::getMenuInstance()->getMenuId().'" />
				<input type="hidden" name="lang" value="'.$lang->short.'" />
				<input type="hidden" name="advanced" id="advancedEdit" value="0" />';

			// Replace Tags
			$_adminHtml = str_replace('[UPLOAD_HIDDEN_FIELDS]', $_hidden_fields, $_adminHtml);
			$_adminHtml = str_replace('[UPLOAD_ACTION]', '/delight_hp/index.php', $_adminHtml);
			$_adminHtml = str_replace('[FORM_TEXT]', "[LANG_VALUE:entry_delete]", $_adminHtml);

			$this->_content = $_adminHtml;
		} else {
			$this->showNoAccess();
		}
		$this->_content = $this->ReplaceAjaxLanguageParameters($this->_content);
		echo $this->_content;
		exit();
	}

	// Do Delete an Entry
	private function doDeleteEntry() {
		$userCheck = pCheckUserData::getInstance();
		$lang = pMessages::getLanguageInstance()->getLanguage();
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$menu = 0;

		if ($userCheck->checkAccess('content')) {
			$text = new pTextEntry($this->_changeId);

			// Update the Sort-Order
			$sql = 'UPDATE [table.txt] SET [field.txt.sort]=[field.txt.sort]-1 WHERE [field.txt.menu]='.$text->menu.' AND [field.txt.sort]>'.$text->sort.';';
			$db->run($sql, $res);
			$res = null;

			// If this is a GROUPED TextBlock, ungroup all Texts
			if ($text->plugin == 'GROUP') {
				foreach (explode(',', $text->text) as $id) {
					$sql = 'UPDATE [table.txt] SET [field.txt.grouped]=0 WHERE [field.txt.id]='.(int)$id.';';
					$db->run($sql, $res);
					$res = null;
				}
			}

			// Delete the TextEntry
			$sql = 'DELETE FROM [table.txt] WHERE [field.txt.id]='.$text->id.';';
			$db->run($sql, $res);

			// Show the Content
			$_adminHtml = $this->getAdminContent('text_delete_success');
			$_adminHtml = str_replace('[REDIRECT_LINK]', '/'.$lang->short.'/'.pMenu::getMenuInstance()->getShortMenuName().'/', $_adminHtml);
			$_adminHtml = str_replace('[TEXTBLOCK_ID]', 'admcont_'.$this->_changeId, $_adminHtml);
			$this->_content = $_adminHtml;
		} else {
			$this->showNoAccess();
		}
		$this->_content = $this->ReplaceAjaxLanguageParameters($this->_content);
		echo $this->_content;
		exit();
	}

	// Move the Entry one Poeition UP
	private function moveEntryUp() {
		$userCheck = pCheckUserData::getInstance();
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;

		if ($userCheck->checkAccess("content")) {
			// Get the current Menu to get the lowerOne
			$sql = 'SELECT [txt.sort],[txt.menu] FROM [table.txt] WHERE [txt.id]='.$this->_changeId.';';
			$db->run($sql, $res);
			if ($res->getFirst()) {
				// Get the upper Entry (upper means a higher OrderSort-ID)
				$sql = 'SELECT [txt.id] FROM [table.txt] WHERE [txt.menu]='.$res->{$db->getFieldName('txt.menu')}.' AND [txt.sort]<'.$res->{$db->getFieldName('txt.sort')}.' ORDER BY [txt.sort] ASC LIMIT 0,1;';
				$res = null;
				$db->run($sql, $res);
				if ($res->getFirst()) {
					// Update the upper-Entry
					$sql = 'UPDATE [table.txt] SET [field.txt.sort]=[field.txt.sort]-1 WHERE [field.txt.id]='.$res->{$db->getFieldName('txt.id')}.';';
					$res = null;
					$db->run($sql, $res);

					// Update the moveing-Entry
					$sql = 'UPDATE [table.txt] SET [field.txt.sort]=[field.txt.sort]+1 WHERE [field.txt.id]='.$this->_changeId.';';
					$res = null;
					$db->run($sql, $res);
				}
			}
			$lnk = "/".$this->_langShort."/".$this->_menuId."/adm=1";
			$this->_content = $this->_getContent('forwardOnly');
			$this->_content = str_replace("[FORWARD_LINK]", $lnk, $this->_content);;
		} else {
			$this->showNoAccess();
		}
	}

	// Move the Entry one Position DOWN
	private function moveEntryDown() {
		$userCheck = pCheckUserData::getInstance();
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;

		if ($userCheck->checkAccess("content")) {
			// Get the current Menu to get the lowerOne
			$sql = 'SELECT [txt.sort],[txt.menu] FROM [table.txt] WHERE [txt.id]='.$this->_changeId.';';
			$db->run($sql, $res);
			if ($res->getFirst()) {
				// Get the lower Entry (lower means a lower Order-ID)
				$sql = 'SELECT [txt.id] FROM [table.txt] WHERE [txt.menu]='.$res->{$db->getFieldName('txt.menu')}.' AND [txt.sort]>'.$res->{$db->getFieldName('txt.sort')}.' ORDER BY [txt.sort] ASC LIMIT 0,1;';
				$res = null;
				$db->run($sql, $res);
				if ($res->getFirst()) {
					// Update the lower-Entry
					$sql = 'UPDATE [table.txt] SET [field.txt.sort]=[field.txt.sort]+1 WHERE [field.txt.id]='.$res->{$db->getFieldName('txt.id')}.';';
					$res = null;
					$db->run($sql, $res);

					// Update the moveing-Entry
					$sql = 'UPDATE [table.txt] SET [field.txt.sort]=[field.txt.sort]-1 WHERE [field.txt.id]='.$this->_changeId.';';
					$res = null;
					$db->run($sql, $res);
				}
			}
			$lnk = "/".$this->_langShort."/".$this->_menuId."/adm=1";
			$this->_content = $this->_getContent('forwardOnly');
			$this->_content = str_replace("[FORWARD_LINK]", $lnk, $this->_content);;
		} else {
			$this->showNoAccess();
		}
	}

	// Reads the Template and return the CHANGE-Form, specified by the EntryType
	private function getTextTypeChangeEntry() {
		// Check for the Class, insert it if not exists
		$layoutParser = pURIParameters::get('textParser', '', pURIParameters::$STRING);
		if (class_exists($layoutParser)) {
			// Create the Parser and get the Admin-Content, based on TextParser, TextTitle and TextContent
			$OBJ = new $layoutParser();
			$_html = $OBJ->getAdminContent('entry'.$layoutParser.'edit', pURIParameters::get('textTitle',''), pURIParameters::get('textContent', ''));
			$_html = $this->_replaceAdminTextOptions($_html, array('size', 'paragraph', 'external', 'internal', 'image', 'list', 'hint'));

			// get additional Script-Files
			foreach ($OBJ->_admScript as $v) {
				$this->_scriptFiles[] = $v;
			}
			// get additional Css-Files
			foreach ($OBJ->_admCssInclude as $v) {
				$this->_cssFiles[] = $v;
			}
			// get additional CSS-Content
			$this->_cssContent .= $OBJ->_admCssContent;

			// Destroy the Parser-Class
			unset($OBJ);
		}

		return $_html;
	}

	// Reads the Template and return a List with all Available Options for the current Entry
	private function getTextOptionsEntry() {
		$_html = $this->_getContent('textEntryOptions', 'MAIN');
		$_lay  = $this->_getContent('textEntryOptions', 'OPTION_ENTRY');
		$_opt  = $this->_getContent('textEntryOptions', 'OPTION');
		$_txt  = $this->_getContent('textEntryOptions', 'TEXT_ENTRY');

		// Get the Layout-Content
		$layoutFile = pURIParameters::get('textLayout', '', pURIParameters::$STRING);
		$layoutParser = pURIParameters::get('textParser', '', pURIParameters::$STRING);
		$layoutOptions = pURIParameters::get('textOptions', '', pURIParameters::$STRING);
		$_optFile = ABS_TEMPLATE_DIR.'/lay_'.$layoutFile.'.tpl';
		if (file_exists($_optFile)) {
			$_optFile = implode('', file($_optFile));
		} else {
			$_optFile = '';
		}

		// Get all Options from LayoutFile and store them under $_layOptions
		$_layOptions = $this->_parseOptionsTags($_optFile);

		// get all Options from TextParser
		$OBJ = new $layoutParser();
		$tmp = $OBJ->_readContentFile("default");
		$tmpOpt = $OBJ->getContentOptions();
		unset($OBJ);

		// append the Plugin-Options to the _layOptions
		$keys = array_keys($tmpOpt);
		for ($i = 0; $i < count($keys); $i++) {
			$_keys = array_keys($tmpOpt[$keys[$i]]);
			for ($x = 0; $x < count($_keys); $x++) {
				$_layOptions[$keys[$i]][$_keys[$x]] = $tmpOpt[$keys[$i]][$_keys[$x]];
			}
		}

		// Go through each category
		$keys = array_keys($_layOptions);
		for ($i = 0; $i < count($keys); $i++) {
			$_tmpOpt = '';
			$_keys = array_keys($_layOptions[$keys[$i]]);
			switch ($_keys[0]) {
				case 'edit_field':
					if (substr_count($layoutOptions, "#".$keys[$i]."=") > 0) {
						$_value = preg_replace("/^(.*?)(#".$keys[$i]."=)(.*?)(#)(.*)$/smi", "\\3", $layoutOptions);
					} else {
						$_value = $_layOptions[$keys[$i]]['edit_field']['value'];
					}

					if (strtolower($_layOptions[$keys[$i]]['edit_field']['type']) == 'integer') {
						$_value = (integer)$_value;
					}
					$_tmp .= str_replace("[OPTION_DESCRIPTION]", $_value, $_txt);
					break;
				case 'choose_field':
					$_selected = $_layOptions[$keys[$i]]['choose_field']['selected'];
					for ($x = 0; $x < count($_layOptions[$keys[$i]]['choose_field']['values']); $x++) {
						// create the Option
						$_tmpOpt .= $_opt;
						$_tmpOpt = str_replace("[OPTION_VALUE]", $_layOptions[$keys[$i]]['choose_field']['values'][$x], $_tmpOpt);
						$_tmpOpt = str_replace("[OPTION_DESCRIPTION]", $_layOptions[$keys[$i]]['choose_field']['values'][$x], $_tmpOpt);

						// Check for selected
						if (substr_count($layoutOptions, "#".$keys[$i]."=".$_layOptions[$keys[$i]]['choose_field']['values'][$x]."#") > 0) {
							$_tmpOpt = preg_replace("/(\[SELECTED\:')(.*?)('\:')(.*?)('\])/smi", "\\2", $_tmpOpt);
						} else if ( (substr_count($layoutOptions, "#".$keys[$i]."=") <= 0) && ($_selected == $x) ) {
							$_tmpOpt = preg_replace("/(\[SELECTED\:')(.*?)('\:')(.*?)('\])/smi", "\\2", $_tmpOpt);
						} else {
							$_tmpOpt = preg_replace("/(\[SELECTED\:')(.*?)('\:')(.*?)('\])/smi", "\\4", $_tmpOpt);
						}
					}
					$_tmp .= str_replace("[OPTIONS_LIST]", $_tmpOpt, $_lay);
					break;
				default:
					for ($x = 0; $x < count($_keys); $x++) {
						// create the Option
						$_tmpOpt .= $_opt;
						$_tmpOpt = str_replace("[OPTION_VALUE]", $_keys[$x], $_tmpOpt);
						$_tmpOpt = str_replace("[OPTION_DESCRIPTION]", "[LANG_VALUE:lay_".$keys[$i]."_".$_keys[$x]."]", $_tmpOpt);

						// Check for selected
						if (substr_count($layoutOptions, "#".$keys[$i]."=".$_keys[$x]."#") > 0) {
							$_tmpOpt = preg_replace("/(\[SELECTED\:')(.*?)('\:')(.*?)('\])/smi", "\\2", $_tmpOpt);
						} else {
							$_tmpOpt = preg_replace("/(\[SELECTED\:')(.*?)('\:')(.*?)('\])/smi", "\\4", $_tmpOpt);
						}
					}
					$_tmp .= str_replace("[OPTIONS_LIST]", $_tmpOpt, $_lay);
					break;
			}

			// Add the Options-List
			$_tmp = str_replace("[OPTION_FIELD]", $keys[$i], $_tmp);
			$_tmp = str_replace("[OPTION_TITLE]", "[LANG_VALUE:lay_".$keys[$i]."]", $_tmp);
		}
		$_html = str_replace("[OPTIONS_LIST]", $_tmp, $_html);
		return $_html;
	}

	// Reads the Template and return a PREVIEW
	private function getTextPreview() {
		$_html = $this->_getContent('textEntryPreview', 'MAIN');

		$layout = array();
		$layout['layout_file'] = pURIParameters::get('textLayout', '', pURIParameters::$STRING);
		$layout['id'] = '0';
		$layout['textorder'] = '0';
		$layout['text'] = pURIParameters::get('textContent', '', pURIParameters::$STRING);;
		$layout['short'] = pURIParameters::get('textTitle', '', pURIParameters::$STRING);;
		$layout['text_parser'] = pURIParameters::get('textParser', '', pURIParameters::$STRING);;
		$layout['layout_options'] = pURIParameters::get('textOptions', '', pURIParameters::$STRING);;

		// Create the Parser and get the Source
		$OBJ = new $layout['text_parser']();
		$_html = str_replace("[TEXT_PREVIEW]", $OBJ->GetSource("", $layout), $_html);

		// Append the CSS-Includes
		$this->_cssFiles[]  = $OBJ->_cssFile;
		$this->_cssContent .= $OBJ->_cssContent;

		// Destroy the parser
		unset($OBJ);

		return $_html;
	}

	// Reads the Template and return a List with all available Layouts
	private function getTextLayoutTypes() {
		$_html = $this->_getContent('textEditLayouts', 'MAIN');
		$_lay  = $this->_getContent('textEditLayouts', 'ENTRY');
		$_plg  = $this->_getContent('textEditLayouts', 'PLUGIN');
		$_layoutList = '';
		$_pluginList = '';

		// get all Layouts
		if (($od = opendir(ABS_TEMPLATE_DIR)) !== false) {
			while (($file = readdir($od)) !== false) {
				$match = array();
				if (preg_match("/(lay_)(.*?)(\.tpl)/smi", $file, $match)) {
					$layout = array();
					$layout['layout_file'] = $match[2];
					$layout['id'] = '0';
					$layout['textorder'] = '0';
					$layout['text'] = implode(file(LAYOUT_PREVIEW_TEXT));
					$layout['short'] = 'A sample title';
					$layout['text_parser'] = 'TEXT';
					$layout['layout_options'] = '';

					// Create a TEXT object
					$OBJ = new TEXT();

					// Get and Append the Layout
					$_tmp = $OBJ->GetSource("", $layout);
					$_tmp = str_replace("[LAYOUT_SAMPLE]", $_tmp, $_lay);
					$_tmp = str_replace("[DESCRIPTION]", $OBJ->getTemplateDescription(), $_tmp);
					$_tmp = str_replace("[LAYOUT_NAME]", $match[2], $_tmp);
					if (strToLower($TextLayout) == strtolower($match[2])) {
						$_tmp = preg_replace("/(\[SELECTED\:)(.*?)(\:)(.*?)(\])/smi", "\\2", $_tmp, -1);
					} else {
						$_tmp = preg_replace("/(\[SELECTED\:)(.*?)(\:)(.*?)(\])/smi", "\\4", $_tmp, -1);
					}
					$_layoutList .= $_tmp;

					// Append the CSS-Includes
					$this->_cssFiles[]  = $OBJ->_cssFile;
					$this->_cssContent .= $OBJ->_cssContent;

					// Destroy the TEXT-Object
					unset($OBJ);
				}
			}
		}

		$requestedPlugin = strtoupper(pURIParameters::get('textParser', '', pURIParameters::$STRING));
		foreach (explode(',', TEXT_PLUGINS) as $_insPlg) {
			if (class_exists($_insPlg)) {
				$OBJ = new $_insPlg($this->DB, $this->LANG);
				if ($OBJ->_isTextPlugin) {
					$_tmp = str_replace("[PLUGIN_NAME]", $_insPlg, $_plg);
					if ($requestedPlugin == strtoupper($_insPlg)) {
						$_tmp = preg_replace("/(\[SELECTED\:)(.*?)(\:)(.*?)(\])/smi", "\\2", $_tmp, -1);
					} else {
						$_tmp = preg_replace("/(\[SELECTED\:)(.*?)(\:)(.*?)(\])/smi", "\\4", $_tmp, -1);
					}
					$_pluginList .= $_tmp;
				}
			}
		}

		// Return the Code
		$_html = str_replace("[ADMIN_CONTENT]",  $_layoutList, $_html);
		$_html = str_replace("[PLUGIN_CONTENT]", $_pluginList, $_html);
		return $_html;
	}

	// Insert or Update the entry
	function doInsertTextEntry() {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;

		$menu = pMenu::getMenuInstance()->getMenuId();
		$text = urldecode(pURIParameters::get('textContent', pURIParameters::$STRING));
		$title = urldecode(pURIParameters::get('textTitle', pURIParameters::$STRING));
		$layout = urldecode(pURIParameters::get('textLayout', pURIParameters::$STRING));
		$options = urldecode(pURIParameters::get('textOptions', pURIParameters::$STRING));

		// If we dont't have any kind of Text submitted, we show an Error...
		if ($text == '0') {
			die("failed\nWe encountered a problem while saving the TextEntry. There was no Content submitted by the Browser, so we do not save this TextEntry.\nWe are sorry about that, please reload and try it again.");
		}

		$parsePlugin = 'TEXT';
		if ((int)$this->_changeId > 0) {
			$sql = "SELECT [txt.plugin] FROM [table.txt] WHERE [txt.id]=".(int)$this->_changeId.";";
			$db->run($sql, $res);
			if ($res->getFirst()) {
				$parsePlugin = $res->{$db->getFieldName('txt.plugin')};
			}
			$res = null;

		}
		if ((int)$this->_changeId == 0) {
			// Get the Last Order-Sort from current entry
			$sql = "SELECT MAX([txt.sort]) AS LastEntry FROM [table.txt] WHERE [txt.menu]=".$menu.";";
			$db->run($sql, $res);
			if ($res->getFirst()) {
				$lastSort = $res->LastEntry;
			} else {
				$lastSort = -1;
			}
			$res = null;

			$sql = "INSERT INTO [table.txt] ";
		} else {
			$sql = "UPDATE [table.txt] ";
		}
		$sql .= "SET [field.txt.text]='".mysql_real_escape_string($text)."',[field.txt.title]='".mysql_real_escape_string($title)."',[field.txt.layout]='".mysql_real_escape_string($layout)."',[field.txt.options]='".mysql_real_escape_string($options)."'";

		if ((int)$this->_changeId == 0) {
			$sql .= ",[field.txt.sort]=".($lastSort + 1).";";
		} else {
			$sql .= " WHERE [field.txt.id]=".(int)$this->_changeId.";";
		}
		$db->run($sql, $res);
		if ((int)$this->_changeId == 0) {
			$this->_changeId = $res->getInsertId();
		}

		if ($res->getError() > 0) {
			echo "failed \n".$res->errorString();
		} else {
			echo "success replace".$this->_changeId;
		}
		$res = null;
		echo "\n";
		echo "this.__replaceContent('admcont_', 'txt_', ".$this->_changeId.", '".$this->getTextentryContent()."')";
		exit();
	}

	/**
	 * print a String with all Templates and it's options for an AJAX-Request
	 *
	 */
	private function sendAjaxTemplates() {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$userCheck = pCheckUserData::getInstance();
		if ( (pURIParameters::get('textContent', '', pURIParameters::$STRING) != 'ajaxtemplates') || (!$userCheck->checkAccess("content")) ) {
			echo "failed";
			exit();
		}

		// get the PARSER-Type to check which additionall options there are available
		$sql = 'SELECT [txt.plugin] FROM [table.txt] WHERE [txt.id]='.pURIParameters::get('textOptions', 0, pURIParameters::$INT).';';
		$db->run($sql, $res);
		if ($res->getFirst()) {
			$textParser = $res->{$db->getFieldName('txt.plugin')};
		} else {
			$textParser = 'TEXT';
		}
		$res = null;

		// Create a Parser-Objetc and get all additionall options
		$additionalOptions = '';
		if (class_exists($textParser)) {
			$tmp = new $textParser();
			if (method_exists($tmp, "getAdditionalOptions")) {
				$additionalOptions = $tmp->getAdditionalOptions();
			}
			unset($tmp);
		}

		// initialize variables - JavaScript-Objects
		$_layoutList = 'layoutList : {';
		$_optionsList = 'optionsList : {';
		$_cssList = 'loadCssFiles : {';

		// get all Layouts
		if (($od = opendir(ABS_TEMPLATE_DIR)) !== false) {
			while (($file = readdir($od)) !== false) {
				$match = array();
				if (preg_match("/(lay_)(.*?)(\.tpl)/smi", $file, $match)) {
					$layout = array();
					$layout['layout_file'] = $match[2];
					$layout['id'] = '0';
					$layout['textorder'] = '0';
					$layout['text'] = file_get_contents(LAYOUT_PREVIEW_TEXT);
					$layout['short'] = $match[2];
					$layout['text_parser'] = 'TEXT';
					$layout['layout_options'] = '';

					// Create a TEXT object
					$OBJ = new TEXT();
					$OBJ->appendAdditionalTextOptions($additionalOptions);

					// Get and Append the Layout
					$_tmp = $OBJ->GetSource("", $layout);
					$_tmp = str_replace("\r", "\n", $_tmp);
					$_tmp = str_replace("\n", "", $_tmp);

					$_layoutList .= strtolower($match[2]).':"'.str_replace("\"", "\\\"", $_tmp).'",';
					$_cssList .= strtolower($match[2]).':"'.$OBJ->getCssImportFile().'",';
					$_optionsList .= strtolower($match[2]).':"'.$OBJ->getAjaxTextOptions($match[2]).'",';

					// Destroy the TEXT-Object
					unset($OBJ);
					unset($_tmp);
				}
			}
		}
		$_layoutList = str_replace("[MAIN_DIR]", TEMPLATE_DIR, $_layoutList);
		$_layoutList = str_replace("[DATA_DIR]", DATA_DIR, $_layoutList);
		$_layoutList = preg_replace("/(\[)(.*?)(\])/smi", "", $_layoutList);
		$_layoutList = $_layoutList.'none:""},';
		$_cssList = $_cssList.'none:""},';
		$_optionsList = $_optionsList.'none:""},';

		echo "while(1);{".$_cssList.$_optionsList.$_layoutList.'none:null}';
		exit();
	}

	private function reloadTextEntry() {
		echo "success replace".$this->_changeId;
		echo "\n";
		echo "this.__replaceContent('admcont_', 'txt_', ".$this->_changeId.", '".$this->getTextentryContent()."')";
		exit();
	}

	private function getTextentryContent($textId=0) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$lang = pMessages::getLanguageInstance()->getLanguage();
		$userCheck = pCheckUserData::getInstance();
		$html = '[CONTENT_ENTRY]';
		$menu = pMenu::getMenuInstance();

		if ($textId <= 0) {
			$textId = $this->_changeId;
		}

		if ($userCheck->checkAccess('content')) {
			$obj = new pTextEntry($textId);

			if ($obj->grouped) {
				$html = $obj->getPluginContent();
			} else {
				$html = str_replace("[CONTENT_ENTRY]", $obj->getPluginsource(), $html);
				$html = str_replace("[ADMINID]",       ' id="admcont_'.$textId.'"', $html);
				$html = str_replace('[ADMIN_FUNCTIONS]', '', $html);
				$html = preg_replace('/(\[TEXT_ADMIN_FUNCTIONS\])(.*?)(\[\/TEXT_ADMIN_FUNCTIONS\])/smi', '', $html);
				$html = preg_replace('/(\[ADMIN_REMOVE\])(.*?)(\[\/ADMIN_REMOVE\])/smi', '', $html);
			}
		} else {
			$html = '<div style="padding:20px;"><div class="error">no access</div></div>';
		}
		$html = $this->ReplaceAjaxLanguageParameters($html);

		return str_replace("'", "&#34;", preg_replace('/[\n\r\t]+/smi', '', $html));
	}

	/**
	 * Creates an Object based on a JSON-String
	 *
	 * @param string $json JSON-String to convert to a PHP-Object
	 * @return stdClass/Array JSON as a stdClass or an Array
	 */
	function convertJSONtoObject($json) {
		if ($json[0] == '[') {
			$json = substr($json, 1);
			$back = $this->parseJSONArray($json);
			$object = $back[0];
			$json = $back[1];

		} else if ($json[0] == '{') {
			$json = substr($json, 1);
			$back = $this->parseJSONObject($json);
			$object = $back[0];
			$json = $back[1];

		} else {
			$object = new stdClass();
		}
		return $object;
	}

	/**
	 * Parse a JSON-Object and return it
	 *
	 * @param string $json JSON-String-Fragment
	 * @return stdClass 0=>Parsed Object, 1=>JSON-String-Fragment
	 */
	function parseJSONObject($json) {
		$object = new stdClass();
		while (strlen($json) > 0) {
			$name = $this->getNextJSONParamName($json);
			if (strlen($name) > 0) {
				$json = substr($json, strpos($json, '"'.$name.'":') + strlen('"'.$name.'":'));
				$next = $json[0];

				if ($next == '"') {
					$back = $this->parseJSONString($json);
					$value = $back[0];
					$json = $back[1];
				} else if ($next == '[') {
					$json = substr($json, 1);
					$back = $this->parseJSONArray($json);
					$value = $back[0];
					$json = $back[1];
				} else if ($next == '{') {
					$json = substr($json, 1);
					$back = $this->parseJSONObject($json);
					$value = $back[0];
					$json = $back[1];
				} else if ($next == 't') {
					$value = true;
					$json = substr($json, 4);
				} else if ($next == 'f') {
					$value = false;
					$json = substr($json, 5);
				} else if ($next == 'n') {
					$value = null;
					$json = substr($json, 4);
				} else {
					$back = $this->parseJSONNumber($json);
					$value = $back[0];
					$json = $back[1];
				}
				$object->$name = $value;
				if ($json[0] == ',') {
					$json = substr($json, 1);
				}
			}
			if ($json[0] == '}') {
				$json = substr($json, 1);
				break;
			}
		}
		return array($object, $json);
	}

	/**
	 * Parse out an Array from a JSON-String
	 *
	 * @param string $json JSON-String fragment
	 * @return array 0=>Parsed Array, 1=>JSON-String-Fragment
	 */
	function parseJSONArray($json) {
		$object = array();
		while (strlen($json) > 0) {
			$next = $json[0];

			if ($next == '"') {
				$back = $this->parseJSONString($json);
				$value = $back[0];
				$json = $back[1];
			} else if ($next == '[') {
				$json = substr($json, 1);
				$back = $this->parseJSONArray($json);
				$value = $back[0];
				$json = $back[1];
			} else if ($next == '{') {
				$json = substr($json, 1);
				$back = $this->parseJSONObject($json);
				$value = $back[0];
				$json = $back[1];
			} else if ($next == 't') {
				$value = true;
				$json = substr($json, 4);
			} else if ($next == 'f') {
				$value = false;
				$json = substr($json, 5);
			} else if ($next == 'n') {
				$value = null;
				$json = substr($json, 4);
			} else {
				$back = $this->parseJSONNumber($json);
				$value = $back[0];
				$json = $back[1];
			}
			$object[] = $value;
			if ($json[0] == ',') {
				$json = substr($json, 1);
			}
			if ($json[0] == ']') {
				$json = substr($json, 1);
				break;
			}
		}
		return array($object, $json);
	}

	/**
	 * Get the next Parameter-Name from a JSON-String and return it
	 *
	 * @param string $json current JSON-String - Just the fragment where the first parameter is searched
	 * @return string ParameterName
	 */
	function getNextJSONParamName($json) {
		$name = '';
		$record = false;
		for ($i = 0; $i < strlen($json); $i++) {
			$char = $json[$i];
			$next = $json[$i+1];
			if ( ($char == '"') && !$record ) {
				$record = true;
			} else if (($char == '"') && ($next == ':') && $record) {
				break;
			} else if ($record) {
				$name .= $char;
			}
		}
		return $name;
	}

	/**
	 * Get a String as Parameter-Value
	 *
	 * @param string $json JSON-String fragment
	 * @return array 0=>PHP-String, 1=>json-fragment
	 */
	function parseJSONString($json) {
		$string = '';
		$record = false;
		$i = 0;
		for ($i = 0; $i < strlen($json); $i++) {
			$char = $json[$i];
			$prev = '';
			if ($i > 0) {
				$prev = $json[$i-1];
			}
			if ( ($char == '"') && !$record) {
				$record = true;
			} else if (($char == '"') && ($prev != '\\') && $record) {
				break;
			} else if ($record) {
				$string .= $char;
			}
		}
		return array($this->escapeStringFromJSON($string), substr($json, $i+1));
	}

	/**
	 * Get a Number as Parameter-Value
	 *
	 * @param string $json JSON-String fragment
	 * @return array 0=>PHP-Integer or Float, 1=>json-fragment
	 */
	function parseJSONNumber($json) {
		$string = '';
		$record = false;
		$range = range(0, 9, 1);
		$range[] = '.';
		foreach ($range as $k => $v) {
			$range[$k] = ord($v);
		}

		for ($i = 0; $i < strlen($json); $i++) {
			$char = ord($json[$i]);
			if (in_array($char, $range)) {
				$string .= chr($char);
			} else {
				break;
			}
		}
		$length = strlen($string);
		if (strpos($string, '.')) {
			return array(doubleval($string), substr($json, $length));
		} else {
			return array(intval($string), substr($json, $length));
		}
	}

	/**
	 * Unescape a JSON-String to a normal string
	 *
	 * @param string $str JSON-String to mak a normal string from
	 * @return string normal string
	 */
	function escapeStringFromJSON($str) {
		// UnEscape certain ASCII characters:
		// \b => 0x08
		// \f => 0x0c
		$str = str_replace(array('\b', '\f'), array(chr(0x08), chr(0x0C)), $str);

		// UnEscape these characters with a backslash:
		// " \ / \n \r \t \b \f
		$search  = array('\\\\', "\\n", "\\t", "\\r", "\\b", "\\f", '\\"');
		$replace = array('\\', '\n', '\t', '\r', '\b', '\f', '"');
		$str     = str_replace($search, $replace, $str);

		return $str;
	}

	private function changeTextOrder($order) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$cnt = 0;
		foreach ($order as $txt) {
			$sql = 'UPDATE [table.txt] SET [field.txt.sort]='.$cnt.' WHERE [field.txt.id]='.(int)$txt.';';
			$db->run($sql, $res);
			$res = null;
			$cnt++;
		}
		return true;
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
		$sql = 'DELETE FROM [table.formular] WHERE [formular.textid]='.(int)$id.' AND [formular.plugin]="TEXT";';
		$db->run($sql, $res);
		$res = null;

		$sql = '';
		foreach ($config as $k => $v) {
			if (empty($sql)) {
				$sql = 'INSERT INTO [table.formular] ([field.formular.textid],[field.formular.field],[field.formular.value],[field.formular.plugin]) VALUES ';
			} else {
				$sql .= ',';
			}
			$sql .= '('.(int)$id.',\''.mysql_real_escape_string($k).'\',\''.mysql_real_escape_string($v).'\',\'TEXT\')';
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
		$sql = 'SELECT * FROM [table.formular] WHERE [formular.textid]='.(int)$id.' AND [formular.plugin]=\'TEXT\';';
		$db->run($sql, $res);
		if ($res->getFirst()) {
			while ($res->getNext()) {
				$config->{$res->{$db->getFieldName('formular.field')}} = trim($res->{$db->getFieldName('formular.value')});
			}
		}
		return json_encode($config);
	}

	/**
	 * Get the complete Menu as an Object to send back as JSON
	 * @param integer $sel optional selected Menu-Entry
	 * @param string $short optional ShortMenu which is selected
	 * @return stdClass
	 */
	protected function _getMenuStructure($sel=null, $short='') {
		$entry = new pMenuEntry(is_numeric($sel) ? $sel : null);
		if (!empty($short)) {
			$entry->loadByShortMenu($short);
		}
		$menu = pMenu::getMenuInstance();
		return $menu->getStructureObject($entry->id);
	}

	/**
	 * get the complete ImageSections and the containing Images as an Object to send back as JSON
	 * @param int $sel Selected ImageID
	 * @param string $short Link to the selected Image
	 * @return stdClass
	 */
	protected function _getImageStructure($sel=null, $short='') {
		$back = new stdClass();
		$back->sections = array();
		$back->data = array();

		// Check for the selected Image
		if (empty($sel) && !empty($short)) {
			$sel = preg_replace('/^[^\d]+/smi', '', $short);
			$sel = preg_replace('/^(\d+).*$/smi', '\\1', $sel);
		}

		// Get the Section from the Selected Image
		$section = 0;
		if (!empty($sel)) {
			$img = new pImageEntry($sel);
			$section = $img->section;
			unset($img);
		}

		// Get all Sections
		$back->sections = $this->_getSectionList(0, 'ims', 'ims', $section);

		// Get all Images from all Sections
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		foreach ($this->_getSectionIdList(array(), 0, 'ims', 'id', 'parent', true) as $section) {
			$s = new stdClass();
			$s->section = $section;
			$s->data = array();

			$sql = 'SELECT [img.id] FROM [table.img] WHERE [img.section]='.$section.';';
			$db->run($sql, $res);
			if ($res->getFirst()) {
				while ($res->getNext()) {
					$image = new pImageEntry($res->{$db->getFieldName('img.id')});
					$image->setSize(100, 100);

					$img = new stdClass();
					$img->html = $image->value;
					$img->name = $image->title;
					$img->id = $image->id;
					$img->selected = ($sel == $image->id);
					$img->size = array($image->real_width, $image->real_height);

					$s->data[] = $img;
				}
			}
			$back->data[] = $s;
		}
		return $back;
	}

	/**
	 * get the complete FileSections and the containing Files as an Object to send back as JSON
	 * @param int $sel Selected FileID
	 * @param string $short Link to the selected File
	 * @return stdClass
	 */
	protected function _getFileStructure($sel=null, $short='') {
		$back = new stdClass();
		$back->sections = array();
		$back->data = array();

		// Check for the selected File
		if (empty($sel) && !empty($short)) {
			$sel = preg_replace('/^[^\d]+/smi', '', $short);
			$sel = preg_replace('/^(\d+).*$/smi', '\\1', $sel);
		}

		// Get the Section from the Selected File
		$section = 0;
		if (!empty($sel)) {
			$file = new pFileEntry($sel);
			$section = $file->section;
			unset($file);
		}

		// Get all Sections
		$back->sections = $this->_getSectionList(0, 'prs', 'prs', $section);

		// Get all Images from all Sections
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		foreach ($this->_getSectionIdList(array(), 0, 'prs', 'id', 'parent', true) as $section) {
			$s = new stdClass();
			$s->section = $section;
			$s->data = array();

			$sql = 'SELECT [prg.id] FROM [table.prg] WHERE [prg.section]='.$section.';';
			$db->run($sql, $res);
			if ($res->getFirst()) {
				while ($res->getNext()) {
					$filentry = new pFileEntry($res->{$db->getFieldName('prg.id')});

					$file = new stdClass();
					$file->html = $filentry->icon_tag.' '.$filentry->title.'<br/> ('.$filentry->name.')';
					$file->name = $filentry->title;
					$file->id = $filentry->id;
					$file->selected = ($sel == $filentry->id);
					$file->size = array(600, 300); // this is just a dummy value, but 600px should a page be in minimum

					$s->data[] = $file;
				}
			}
			$back->data[] = $s;
		}
		return $back;
	}

	/**
	 * get the complete NewsSections and the containing News as an Object to send back as JSON
	 * @param int $sel Selected NewsID
	 * @param string $short Link to the selected News
	 * @return stdClass
	 */
	protected function _getNewsStructure($sel=null, $short='') {
		$back = new stdClass();
		$back->sections = array();
		$back->data = array();

		// Check for the selected News
		if (empty($sel) && !empty($short)) {
			$sel = preg_replace('/^[^\d]+/smi', '', $short);
			$sel = preg_replace('/^(\d+).*$/smi', '\\1', $sel);
		}

		// Get the Section from the Selected News
		$section = 0;
		if (!empty($sel)) {
			$news = new pNewsEntry($sel);
			$section = $news->section;
			unset($news);
		}

		// Get all Sections
		$back->sections = $this->_getSectionList(0, 'nes', 'nes', $section);

		// Get all Images from all Sections
		$lang = pMessages::getLanguageInstance();
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		foreach ($this->_getSectionIdList(array(), 0, 'nes', 'id', 'parent', true) as $section) {
			$s = new stdClass();
			$s->section = $section;
			$s->data = array();

			$sql = 'SELECT [new.id] FROM [table.new] WHERE [new.section]='.$section.' AND [new.lang]='.$lang->getLanguageId().';';
			$db->run($sql, $res);
			if ($res->getFirst()) {
				while ($res->getNext()) {
					$newsentry = new pNewsEntry($res->{$db->getFieldName('new.id')});

					$news = new stdClass();
					$news->html = '<strong>'.$newsentry->title.'</strong><br/><em>'.$newsentry->date_extended.'</em><br/>'.$newsentry->text;
					$news->name = $newsentry->title;
					$news->id = $newsentry->id;
					$news->selected = ($sel == $newsentry->id);
					$news->size = array(420, 450); // this is just a dummy value

					$s->data[] = $news;
				}
			}
			$back->data[] = $s;
		}
		return $back;
	}

	/**
	 * get the complete Menu-Structure and the containing Texts as an Object to send back as JSON
	 * @param int $sel Selected TextID
	 * @param string $short Link to the selected Textblock
	 * @return stdClass
	 */
	protected function _getTextStructure($sel=null, $short='') {
		$back = new stdClass();
		$back->sections = array();
		$back->data = array();

		// Check for the selected Image
		if (empty($sel) && !empty($short)) {
			$sel = preg_replace('/^[^\d]+/smi', '', $short);
			$sel = preg_replace('/^(\d+).*$/smi', '\\1', $sel);
		}

		// Get the Section from the Selected Image
		$section = 0;
		if (!empty($sel)) {
			$txt = new pTextEntry($sel);
			$section = $txt->menu;
			unset($txt);
		}

		// Get all Sections
		$menu = pMenu::getMenuInstance();
		$back->sections = $menu->getStructureObject($section);

		// Get all Images from all Sections
		$lang = pMessages::getLanguageInstance();
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		foreach ($menu->getFlatMenuIdList() as $section) {
			$s = new stdClass();
			$s->section = $section;
			$s->data = array();

			$sql = 'SELECT [txt.id] FROM [table.txt] WHERE [txt.menu]='.$section.' AND [txt.lang]='.$lang->getLanguageId().';';
			$db->run($sql, $res);
			if ($res->getFirst()) {
				while ($res->getNext()) {
					$textentry = new pTextEntry($res->{$db->getFieldName('txt.id')});

					$txt = new stdClass();
					$txt->html = '<strong>'.$textentry->title.'</strong><br/>'.strip_tags($textentry->text);
					$txt->name = $textentry->title;
					$txt->id = $textentry->id;
					$txt->selected = ($sel == $textentry->id);
					$txt->size = array(600, 300); // Just dummy-values

					$s->data[] = $txt;
				}
			}
			$back->data[] = $s;
		}
		return $back;
	}

}
?>