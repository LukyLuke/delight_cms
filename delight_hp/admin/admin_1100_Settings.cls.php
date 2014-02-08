<?php

/**
 * Programms and Downloads
 *
 */

class admin_1100_Settings extends admin_MAIN_Settings {

	private $fileRealPath;

	/**
	 * Initialization
	 * @access public
	 */
	public function __construct() {
		parent::__construct();
		$this->fileRealPath = realpath('./data/downloadfiles/');

		$obj = new DOWNLOADS($this->DB, $this->LANG);
		$obj = null;
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
					$tpl = pURIParameters::get('template', 'download', pURIParameters::$STRING);
					$section = pURIParameters::get('section', 0, pURIParameters::$INT);
					$entry = pURIParameters::get('entry', 0, pURIParameters::$INT);
					$isContent = pURIParameters::get('iscontent', false, pURIParameters::$BOOL);

					$tpl = $this->getAdminContent($tpl, 1100);
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
					$obj = $this->getProgramObject($entry);
					$tpl = $this->getAdminContent('editor', 1100);
					$tpl = str_replace('[SECTION_ID]', $section, $tpl);
					$tpl = str_replace('[ENTRY_ID]', $entry, $tpl);
					$tpl = str_replace('[TITLE]', $obj->title, $tpl);
					$tpl = str_replace('[TEXT]', $obj->text, $tpl);
					echo $this->ReplaceAjaxLanguageParameters( $tpl );
					exit();
					break;
				case 'editortext':
					$entry = pURIParameters::get('entry', 0, pURIParameters::$INT);
					$section = pURIParameters::get('section', 0, pURIParameters::$INT);
					$title = pURIParameters::get('title', '', pURIParameters::$STRING);
					$content = pURIParameters::get('content', '', pURIParameters::$STRING);
					$this->storeObjectTextData($entry, $title, $content, 'prt', 'program');

					$tpl = pURIParameters::get('template', 'success_close', pURIParameters::$STRING);
					echo $this->ReplaceAjaxLanguageParameters( $this->getAdminContent($tpl, 1100, false, 'FileManagement') );
					exit();
					break;

				case 'sections':
					echo $this->_getJSONSectionList(0, 'prs', 'prs');
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
						echo $this->_getJSONFileList($section);
					}
					echo '}';
					exit();
					break;

				case 'reorder':
					$section = pURIParameters::get('section', 0, pURIParameters::$INT);
					$order = explode(',', pURIParameters::get('order', '', pURIParameters::$STRING));
					$success = $this->_changeFileOrder($section, $order);
					echo '{"section":'.$section.',"success":'.($success ? 'true' : 'false').'}';
					exit();
					break;

				case 'section':
					$id = pURIParameters::get('id', 0, pURIParameters::$INT);
					$parent = pURIParameters::get('parent', 0, pURIParameters::$INT);
					$name = pURIParameters::get('name', 'Neuer Ordner', pURIParameters::$STRING);
					$newid = $this->_changeSectionParameters($id, $name, $parent, 'prs');
					if ($id != $newid) {
						echo '{"adm":1100,"name":"'.$this->_escapeJSONString($name).'", "oldid":'.$id.', "id":'.$newid.', "parent":'.$parent.',"call":"sectionCreateFinal","click":"FileManagement.sectionClick","success":true}';
					} else {
						echo '{"adm":1100,"success":true}';
					}
					exit();
					break;

				case 'section_remove':
					$id = pURIParameters::get('id', 0, pURIParameters::$INT);
					$secure = $this->_deleteSection($id);
					echo '{"id":'.$id.',"call":"sectionDeleteFinal","success":true}';
					exit();
					break;

				case 'secure_section':
					$id = pURIParameters::get('section', 0, pURIParameters::$INT);
					$secure = $this->_secureSection($id);
					echo '{"id":'.$id.',"call":"sectionSecureFinal","success":true,"secure":'.($secure ? 'true' : 'false').'}';
					exit();
					break;

				case 'remove':
					$idlist = explode(',', pURIParameters::get('idlist', array(), pURIParameters::$STRING));
					$id = count($idlist) > 0 ? $idlist[0] : pURIParameters::get('id', 0, pURIParameters::$INT);
					$section = $this->_getSectionIdFromObject($id, 'prg');
					if (empty($idlist)) {
						$this->_deleteFile($id);
					} else {
						foreach ($idlist as $id) {
							$this->_deleteFile($id);
						}
					}
					echo '{"section":'.$section.', "success":true,"call":"reloadContent","scope":"FileManagement"}';
					exit();
					break;

				case 'upload':
					$section = pURIParameters::get('section', 0, pURIParameters::$INT);
					$entry = pURIParameters::get('entry', array(0), pURIParameters::$ARRAY);
					$ftp = pURIParameters::get('ftp', '', pURIParameters::$STRING);

					// FTP-Files
					$cnt = 0;
					if (!empty($ftp)) {
						$ftp = explode(';', $ftp);
						foreach ($ftp as $file) {
							if (empty($file) || ($file == 'lastentry')) {
								continue;
							}
							$error = $this->_storeUploadedFile($section, null, $entry, $file);
							$cnt =+ 1;
							if (!empty($entry)) {
								break;
							}
							if (!empty($error)) {
								break;
							}
						}
					} else {
						// Uploaded Files
						$error = $this->_storeUploadedFile($section, $_FILES['upload'], $entry);
					}

					if (!empty($ftp)) {
						echo '{"section":'.$section.',"call":"uploadNext","ftpfiles":true,"loaded":'.$cnt.',"success":';
						if (empty($error)) {
							echo 'true';
						} else {
							echo 'false,"error":"'.htmlentities($error).'"';
						}
						echo '}';

					} else {
						$tpl = $this->getAdminContent('upload_form', 1100, true, 'FileManagement');
						$tpl = str_replace('[UPLOADED_MESSAGE]', empty($error) ? '[LANG_VALUE:file_uploaded]' : htmlentities($error), $tpl);
						$tpl = str_replace('[UPLOADED_ERROR]', empty($error)?'false':'true', $tpl);
						echo $this->ReplaceAjaxLanguageParameters($tpl);
					}
					exit();
					break;
				case 'html5_upload':
					$section = pURIParameters::get('section', 0, pURIParameters::$INT);
					$entry = pURIParameters::get('entry', 0, pURIParameters::$INT);
					$error = $this->_storeUploadedFile($section, array(), $entry, null, true);
					echo '{"error":'.($error ? 'true' : 'false').',"message":"'.$error.'",scope:"FileManagement"}';
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
	 * Get the FileList from a Section as a JSON-String
	 *
	 * @param integer $section The SectionID to get all files from
	 * @return string JSON compatible String with all files
	 */
	private function _getJSONFileList($section) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = "SELECT * FROM [table.prg] WHERE [prg.section]=".(int)$section." ORDER BY [prg.order];";
		$db->run($sql, $res);
		$back = '"list":[';
		if ($res->getFirst()) {
			while ($res->getNext()) {
				$file = $this->getProgramObject($res->{$db->getFieldName('prg.id')});
				if ($file->id <= 0) {
					continue;
				}
				if (strlen($back) > 1) {
					$back .= ',';
				}
				$back .= '{';
				$back .= '"id":"'.$file->id.'",';
				$back .= '"src":"'.$this->_escapeJSONString($file->src, false).'",';
				$back .= '"name":"'.$this->_escapeJSONString($file->name, false).'",';
				$back .= '"size":"'.$file->size.'",';
				$back .= '"downloaded":"'.$file->loaded.'",';
				$back .= '"icon":{';
				$back .= '"src":"'.$file->icon->src.'",';
				$back .= '"text":"'.$this->_escapeJSONString($file->icon->comment).'"';
				$back .= '},';
				$back .= '"mimetype":"'.$this->_escapeJSONString($file->type).'",';
				$back .= '"download_link":"'.$this->_escapeJSONString('/download/'.$file->id.'/'.$file->name, false).'",';
				$back .= '"mimecomment":"'.$this->_escapeJSONString($file->type_e).'",';
				$back .= '"title":"'.$this->_escapeJSONString($file->title, false).'",';
				$back .= '"text":"'.$this->_escapeJSONString($file->text, false).'",';
				$back .= '"date":"'.$this->_escapeJSONString(strftime('%d. %b. %Y', $file->date)).'",';
				$back .= '"fulldate":"'.$this->_escapeJSONString(strftime('%d. %B %Y %R', $file->date)).'",';
				$back .= '"downloaded_last":"'.$this->_escapeJSONString(strftime('%d. %b. %Y', $file->last)).'",';
				$back .= '"downloaded_lastfull":"'.$this->_escapeJSONString(strftime('%d. %B %Y %R', $file->last)).'",';
				$back .= '"section":"'.$file->section.'"';
				$back .= '}';
			}
		}
		$res = null;
		$back .= ']';
		return $back;
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
		$data['plugin'] = 'DOWNLOADS';
		$data['options'] = is_null($options) ? '#show_recursive=false##show_title=true##show_section=false##title=default#' : json_decode($options);

		$down = new DOWNLOADS();
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
	 * Delete a Section and all its contents and Subsections
	 *
	 * @param int $id SectionID to remove
	 */
	private function _deleteSection($id) {
		// First get the Parent-Section to switch to after a successfull delete
		$_parent = $this->_getParentSectionData($id, 'prs');
		$_parent = $_parent['par'];
		$_childs = $this->_getChildSections($id, 'prs', true, array($id));
		$_childs = array_reverse($_childs);
		$_success = true;

		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;

		// Delete each Section
		foreach ($_childs as $k => $v) {
			$sql = "SELECT [prg.id] FROM [table.prg] WHERE [prg.section]=".(int)$v.";";
			$db->run($sql, $res);
			if ($res->getFirst()) {
				while ($res->getNext()) {
					$this->_deleteFile($res->{$db->getFieldName('prg.id')});
				}
			}
			// finally delete the Section
			$sql = "DELETE FROM [table.prs] WHERE [prs.id]=".(int)$v.";";
			$db->run($sql, $res);
			$res = null;
		}
	}

	/**
	 * Switch the Secure-Flag of a Section
	 * @param integer $id
	 * @access private
	 * @return boolean
	 */
	private function _secureSection($id) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;

		$sql = 'SELECT [prs.secure] FROM [table.prs] WHERE [field.prs.id]='.(int)$id.';';
		$db->run($sql, $res);
		$res->getFirst();
		$secure = $res->{$db->getFieldName('prs.secure')};
		$res = null;
		$secure = abs($secure) > 0 ? 0 : 1;

		$sql = 'UPDATE [table.prs] SET [field.prs.secure]='.$secure.' WHERE [field.prs.id]='.(int)$id.';';
		$db->run($sql, $res);
		$res = null;
		return $secure>0;
	}

	/**
	 * Add all uploaded Files or selected FTP-Files in the given Section
	 *
	 * @param int $section Section to add files
	 * @param array $file $_FILES part
	 * @param int $replace ID of File to replace
	 * @param string $ftpfile FTP-File to use instead $files-array
	 * @return string Error-String
	 */
	private function _storeUploadedFile($section, $file, $replace, $ftpfile='', $html5=false) {
		$error = '';
		$msg = pMessages::getLanguageInstance();
		if (!empty($ftpfile) || $html5 || (is_uploaded_file($file['tmp_name']) && ($file['size'] > 0) && ($file['error'] <= 0)) ) {
			$db = pDatabaseConnection::getDatabaseInstance();
			$res = null;

			if ($html5) {
				$data_5 = pHttpRequest::getRequestData(true);
				$size_5 = (int)$_SERVER['HTTP_X_FILE_SIZE'];

				$_fileMime = $this->_getMimeInfoObject($_SERVER['HTTP_X_FILE_NAME']);
				$_realName = $_SERVER['HTTP_X_FILE_NAME'];

			} else if (empty($ftpfile)) {
				$_fileMime = $this->_getMimeInfoObject($file['name']);
				$_realName = $file['name'];

			} else {
				$_fileMime = $this->_getMimeInfoObject($ftpfile);
				$_realName = $ftpfile;
			}

			// Get dta from image if it exists already
			$_exists = false;
			$_fileName = '';
			// 2012-08-17: Replace files by name and not by selection
			/*if ($replace > 0) {
				$sql = 'SELECT * FROM [table.prg] WHERE [prg.id]='.(int)$replace.';';
				$db->run($sql, $res);
				if ($res->getFirst()) {
					$_exists = true;
					$_fileName = $res->{$db->getFieldName('prg.program')};
					$firephp->info(array($replace, $_fileName), "ID based replacement");
				}
			}
			$res = null;*/

			// If no File was selected, replace the one with the same name
			if (empty($_fileName)) {
				$sql = 'SELECT * FROM [table.prg] WHERE [prg.section]='.(int)$section.' AND [prg.name]=\''.$_realName.'\';';
				$db->run($sql, $res);
				if ($res->getFirst()) {
					$_exists = true;
					$_fileName = $res->{$db->getFieldName('prg.program')};
				}
			}
			if (empty($_fileName)) {
				$_fileName = md5(uniqid(rand(), true)).'.'.$_fileMime['Extension'];
			}

			// Copy the file to its final destination
			if (empty($error)) {
				if ($html5) {
					$written = file_put_contents($this->fileRealPath.DIRECTORY_SEPARATOR.$_fileName, $data_5);
					chmod($this->fileRealPath.DIRECTORY_SEPARATOR.$_fileName, 0777);
					if (($written == 0) || ($written != $size_5)) {
						unlink($this->fileRealPath.DIRECTORY_SEPARATOR.$_fileName);
						$error .= $msg->getValue('', 'text', 'upload_different_sizes');
					}

				} else if (!empty($ftpfile)) {
					copy(ABS_FTP_UPLOAD.utf8_decode($_realName), $this->fileRealPath.DIRECTORY_SEPARATOR.$_fileName);
					chmod($this->fileRealPath.DIRECTORY_SEPARATOR.$_fileName, 0777);

				} else if (move_uploaded_file($file['tmp_name'], $this->fileRealPath.DIRECTORY_SEPARATOR.$_fileName)) {
					chmod($this->fileRealPath.DIRECTORY_SEPARATOR.$_fileName, 0777);

				} else {
					$error .= $msg->getValue('', 'text', 'upload_write_error');
				}

				// Insert/Update the Database
				if (empty($error)) {
					if (!$_exists) {
						$pos = 0;
						$sql = 'SELECT MAX([prg.order]) FROM [table.prg] WHERE [prg.section]='.$section.';';
						$db->run($sql, $res);
						if ($res->getFirst()) {
							$pos = (int)$res->{$db->getFieldName('prg.order')};
							$pos++;
						}
						$res = null;

						$sql = "INSERT INTO [table.prg] ([field.prg.program],[field.prg.section],[field.prg.name],[field.prg.mime],[field.prg.order]) VALUES('".$_fileName."',".$section.",'".$_realName."','".$_fileMime['MimeType']."',".$pos.");";
						$db->run($sql, $res);
					} else {
						$sql = "UPDATE [table.prg] SET [field.prg.name]='".$_realName."' WHERE [field.prg.id]=".(int)$replace.";";
						$db->run($sql, $res);
					}
				}

			}

		} else {
			if ($file['error'] > 0) {
				switch ($file['error']) {
					case UPLOAD_ERR_INI_SIZE: $error = $msg->getValue('', 'text', 'upload_file_to_big').ini_get('upload_max_filesize'); break;
					case UPLOAD_ERR_FORM_SIZE: $error = $msg->getValue('', 'text', 'upload_file_to_big').$_POST['MAX_FILE_SIZE']; break;
					case UPLOAD_ERR_PARTIAL: $error = $msg->getValue('', 'text', 'upload_partial'); break;
					case UPLOAD_ERR_NO_FILE: $error = $msg->getValue('', 'text', 'upload_no_file'); break;
					case UPLOAD_ERR_CANT_WRITE: $error = $msg->getValue('', 'text', 'upload_cant_write'); break;
					case UPLOAD_ERR_NO_TMP_DIR: $error = $msg->getValue('', 'text', 'upload_no_temp'); break;
					default: $error = $msg->getValue('', 'text', 'upload_unknown_error').$file['error']; break;
				}
			}
		}
		return $error;
	}

	/**
	 * Delete the given File
	 *
	 * @param int $id FileID
	 */
	private function _deleteFile($id) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = "SELECT [prg.program] FROM [table.prg] WHERE [prg.id]=".$id.";";
		$db->run($sql, $res);
		if ($res->getFirst()) {
			$file = $res->{$db->getFieldName('prg.program')};
			if (@unlink($this->fileRealPath.'/'.$file)) {
				$res = null;
				$sql = "DELETE FROM [table.prg] WHERE [prg.id]=".$id.";";
				$db->run($sql, $res);
				$res = null;
				$sql = "DELETE FROM [table.prt] WHERE [prt.program]=".$id.";";
				$db->run($sql, $res);
				$res = null;
			}
		}
	}

	/**
	 * Change the Order from all Files in given $order Array to the Order they are inside this Array
	 *
	 * @param int $section The Section ahere the Files lies in
	 * @param array $order All ID's in new Order
	 * @return boolean
	 */
	private function _changeFileOrder($section, array $order) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$cnt = 0;
		foreach ($order as $prg) {
			$sql = 'UPDATE [table.prg] SET [field.prg.order]='.$cnt.' WHERE [field.prg.id]='.(int)$prg.';';
			$db->run($sql, $res);
			$res = null;
			$cnt++;
		}
		return true;
	}

}
?>