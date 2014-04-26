<?php

/**
 * Images and Screenshots
 *
 */

class admin_1000_Settings extends admin_MAIN_Settings {
	private $imageRealPath;

	/**
	 * Initialization
	 *
	 * @access public
	 */
	public function __construct() {
		//$this->admin_MAIN_Settings();
		parent::__construct();
		$this->imageRealPath = realpath('./images/page/');

		// Insert and create the Class - this action will create all needed tables and Entries
		$obj = new SCREENSHOT($this->DB, $this->LANG);
		unset($obj);
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
					$tpl = pURIParameters::get('template', 'image', pURIParameters::$STRING);
					$section = pURIParameters::get('section', 0, pURIParameters::$INT);
					$entry = pURIParameters::get('entry', 0, pURIParameters::$INT);
					$isContent = pURIParameters::get('iscontent', false, pURIParameters::$BOOL);
					$selected = pURIParameters::get('selected', 0, pURIParameters::$INT);
					$window = pURIParameters::get('window', '', pURIParameters::$STRING);
					$function = pURIParameters::get('function', '', pURIParameters::$STRING);

					$tpl = $this->getAdminContent($tpl, 1000);
					$tpl = str_replace('[SECTION_ID]', $section, $tpl);
					$tpl = str_replace('[ENTRY_ID]', $entry, $tpl);
					$tpl = str_replace('[SELECTED_ID]', $selected, $tpl);
					$tpl = str_replace('[WINDOW_ID]', $window, $tpl);
					$tpl = str_replace('[WINDOW_CALL_FUNCTION]', $function, $tpl);

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
					$obj = $this->getImageObject($entry);
					$tpl = $this->getAdminContent('editor', 1000);
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
					$this->storeObjectTextData($entry, $title, $content, 'imt', 'image');

					$tpl = pURIParameters::get('template', 'success_close', pURIParameters::$STRING);
					echo $this->ReplaceAjaxLanguageParameters( $this->getAdminContent($tpl, 1000, false, 'ImageManagement') );
					exit();
					break;

				case 'sections':
				case 'sectiontree':
					echo $this->_getJSONSectionList(0, 'ims', 'ims');
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
						echo $this->_getJSONImageList($section);
					}
					echo '}';
					exit();
					break;

				case 'reorder':
					$section = pURIParameters::get('section', 0, pURIParameters::$INT);
					$order = explode(',', pURIParameters::get('order', '', pURIParameters::$STRING));
					$success = $this->changeImageOrder($section, $order);
					echo '{"section":'.$section.',"success":'.($success ? 'true' : 'false').'}';
					exit();
					break;

				case 'section':
					$id = pURIParameters::get('id', 0, pURIParameters::$INT);
					$parent = pURIParameters::get('parent', 0, pURIParameters::$INT);
					$name = pURIParameters::get('name', 'Neuer Ordner', pURIParameters::$STRING);
					$newid = $this->_changeSectionParameters($id, $name, $parent, 'ims');
					if ($id != $newid) {
						echo '{"adm":1000,"name":"'.$this->_escapeJSONString($name).'", "oldid":'.$id.', "id":'.$newid.', "parent":'.$parent.',"call":"sectionCreateFinal","click":"ImageManagement.sectionClick","success":true}';
					} else {
						echo '{"adm":1000,"success":true}';
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
					$idlist = explode(',', pURIParameters::get('idlist', array(), pURIParameters::$STRING));
					$id = count($idlist) > 0 ? $idlist[0] : pURIParameters::get('id', 0, pURIParameters::$INT);
					$section = $this->_getSectionIdFromObject($id, 'img');
					if (empty($idlist)) {
						$this->_deleteImage($id);
					} else {
						foreach ($idlist as $id) {
							$this->_deleteImage($id);
						}
					}
					echo '{"section":'.$section.',"success":true,"call":"reloadContent","scope":"ImageManagement"}';
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
							$error = $this->_storeUploadedImage($section, null, $entry, $file);
							$cnt =+ 1;
							if (!empty($entry)) {
								break;
							}
							if (!empty($error)) {
								break;
							}
						}

						// Uploaded Files
					} else {
						$error = $this->_storeUploadedImage($section, $_FILES['upload'], $entry);
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
						$tpl = $this->getAdminContent('upload_form', 1000, true, 'ImageManagement');
						$tpl = str_replace('[UPLOADED_MESSAGE]', empty($error) ? '[LANG_VALUE:file_uploaded]' : htmlentities($error), $tpl);
						$tpl = str_replace('[UPLOADED_ERROR]', empty($error)?'false':'true', $tpl);
						echo $this->ReplaceAjaxLanguageParameters($tpl);
					}
					exit();
					break;
				case 'html5_upload':
					$section = pURIParameters::get('section', 0, pURIParameters::$INT);
					$entry = pURIParameters::get('entry', 0, pURIParameters::$INT);
					$error = $this->_storeUploadedImage($section, array(), $entry, null, true);
					echo '{"error":'.($error ? 'true' : 'false').',"message":"'.$error.'",scope:"ImageManagement"}';
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
	 * Get the ImageList from a Section as a JSON-String
	 *
	 * @param integer $section The SectionID to get all Images from
	 * @return string JSON compatible String with all images
	 */
	private function _getJSONImageList($section, $maxWidth=80) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = "SELECT [img.id] FROM [table.img] WHERE [img.section]=".(int)$section." ORDER BY [img.order];";
		$db->run($sql, $res);
		$back = '[';
		if ($res->getFirst()) {
			while ($res->getNext()) {
				$img = $this->getImageObject($res->{$db->getFieldName('img.id')}, 'w', $maxWidth, 'px');
				if (strlen($back) > 1) {
					$back .= ',';
				}
				$back .= '{';
				$back .= '"id":'.$img->id.',';
				$back .= '"src":"'.$img->src.'",';
				$back .= '"file":"'.$img->file.'",';
				$back .= '"name":"'.$img->name.'",';
				$back .= '"dimension":['.$img->real_width.','.$img->real_height.'],';
				$back .= '"size":'.$img->size.',';
				$back .= '"mime":"'.$img->mime.'",';
				$back .= '"mimecomment":"'.$img->mimecomment.'",';
				$back .= '"title":"'.$this->_escapeJSONString($img->title).'",';
				$back .= '"text":"'.$this->_escapeJSONString($img->text).'",';
				$back .= '"date":"'.$this->_escapeJSONString(strftime('%d. %b. %Y', $img->timestamp)).'",';
				$back .= '"fulldate":"'.$this->_escapeJSONString(strftime('%d. %B %Y %R', $img->timestamp)).'",';
				$back .= '"section":'.$img->section.'';
				$back .= '}';
			}
		}
		$res = null;
		$back .= ']';
		return '"list":'.$back;
	}

	/**
	 * Get ImageList as JSON-Compatible HTML-Content to show in the Page directly
	 *
	 * @param int $entity The EntityID
	 * @param int $section Section to get files from
	 * @param boolean $options
	 * @param string $template
	 * @return string
	 */
	private function _getJSONPageContent($entity, $section, $options=null, $template=null) {
		$data = array();
		$data['layout'] = is_null($template) ? 'default' : $template;
		$data['sort'] = 0;
		$data['id'] = $entity;
		$data['text'] = $section;
		$data['title'] = '';
		$data['menu'] = 0;
		$data['lang'] = pMessages::getLanguageInstance()->getLanguageId();
		$data['plugin'] = 'SCREENSHOT';
		$data['options'] = is_null($options) ? '#show_recursive=false##show_title=true##show_section=false##title=default#' : json_decode($options);

		$down = new SCREENSHOT();
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
	 * Delete the given Section and all Content and SubSections
	 *
	 * @param integer $id SectionID to delete
	 * @access private
	 */
	private function _deleteSection($id) {
		// First get the Parent-Section to switch to after a successfull delete
		$_parent = $this->_getParentSectionData($id, 'ims');
		$_parent = $_parent['par'];
		$_childs = $this->_getChildSections($id, 'ims', true, array($id));
		$_childs = array_reverse($_childs);

		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;

		// Delete each Section
		foreach ($_childs as $v) {
			$sql = "SELECT [img.id] FROM [table.img] WHERE [img.section]=".(int)$v.";";
			$db->run($sql, $res);
			if ($res->getFirst()) {
				while ($res->getNext()) {
					$this->_deleteImage($res->{$db->getFieldName('img.id')});
				}
			}
			// finally delete the Section
			$sql = "DELETE FROM [table.ims] WHERE [ims.id]=".(int)$v.";";
			$db->run($sql, $res);
			$res = null;
		}
	}

	/**
	 * Check if the Filename has a valid Extension for an Image
	 *
	 * @param string $file The Filename
	 * @return boolean
	 * @access private
	 */
	private function _validImageType($file) {
		$size = @getimagesize($file);
		return in_array($size[2], array(IMAGETYPE_GIF, IMAGETYPE_JP2, IMAGETYPE_JPEG, IMAGETYPE_JPEG2000, IMAGETYPE_PNG, IMAGETYPE_SWF));
	}

	/**
	 * Store a File uploaded or selected from FTP-Upload to the Database
	 *
	 * @param int $section SectionID to insert the Image
	 * @param array $file $_FILES Array with the Uploaded File
	 * @param int $replace ImageID to replace
	 * @param string $ftpfile filename from FTP-Uploade directory
	 * @return string Error-String
	 * @access private
	 */
	private function _storeUploadedImage($section, $file, $replace, $ftpfile='', $html5=false) {
		if (!empty($ftpfile) || $html5 || (is_uploaded_file($file['tmp_name']) && ($file['size'] > 0) && ($file['error'] <= 0)) ) {
			$db = pDatabaseConnection::getDatabaseInstance();
			$res = null;

			if ($html5) {
				$data_5 = pHttpRequest::getRequestData(true);
				$name_5 = $_SERVER['HTTP_X_FILE_NAME'];
				$size_5 = (int)$_SERVER['HTTP_X_FILE_SIZE'];
				$type_5 = $_SERVER['HTTP_X_FILE_TYPE'];

				$tmpImageFile = tempnam(sys_get_temp_dir(), 'delightcms_html5');
				file_put_contents($tmpImageFile, $data_5);
			} else {
				$data_5 = null;
				$name_5 = '';
				$size_5 = 0;
				$type_5 = '';
				$tmpImageFile = empty($ftpfile) ? $file['tmp_name'] : ABS_FTP_UPLOAD.$ftpfile;
			}
			$_realFileName = !empty($ftpfile) ? $ftpfile : empty($name_5) ? $file['name'] : $name_5;

			// Get the Image-Type
			$size = @getimagesize($tmpImageFile);
			$error = '';

			// Check for a valid Image-Type
			if ($this->_validImageType($tmpImageFile)) {
				// Get data from image if it exists already
				$_exists = false;
				$_fileName = '';

				// If no File was selected, replace the one with the same name
				if (empty($_fileName)) {
					$sql = 'SELECT * FROM [table.img] WHERE [img.section]='.(int)$section.' AND [img.name]=\''.$_realFileName.'\';';
					$db->run($sql, $res);
					if ($res->getFirst()) {
						$_exists = true;
						$_fileName = $res->{$db->getFieldName('img.image')};
					}
				}
				if (empty($_fileName)) {
					$_fileName = md5(uniqid(rand(), true)).'.'.strtolower(preg_replace("/(.*?)(\/)/smi", "", $size['mime']));
				}

				// Copy the image to its destination
				if (empty($error)) {
					if ( (!empty($ftpfile) && copy($tmpImageFile, $this->imageRealPath.DIRECTORY_SEPARATOR.$_fileName)) || (!$html5 && move_uploaded_file($tmpImageFile, $this->imageRealPath.DIRECTORY_SEPARATOR.$_fileName)) || (file_put_contents($this->imageRealPath.DIRECTORY_SEPARATOR.$_fileName, $data_5) > 0) ) {
						// Chage the File-Permissions
						chmod($this->imageRealPath.DIRECTORY_SEPARATOR.$_fileName, 0777);

						// Insert/Update the Database
						if (!$_exists) {
							// Get the next position
							$pos = 0;
							$sql = 'SELECT MAX([img.order]) FROM [table.img] WHERE [img.section]='.$section.';';
							$db->run($sql, $res);
							if ($res->getFirst()) {
								$pos = (int)$res->{$db->getFieldName('img.order')};
								$pos++;
							}
							$res = null;

							// Insert the Image
							$sql = "INSERT INTO [table.img] ([field.img.image],[field.img.section],[field.img.date],[field.img.name],[field.img.order]) VALUES ('".$_fileName."',".$section.",".time().",'".$_realFileName."',".$pos.");";
							$db->run($sql, $res);
						} else {
							$sql = "UPDATE [table.img] SET [field.img.date]=".time().",[field.img.name]='".$_realFileName."' WHERE [field.img.id]=".(int)$replace.";";
							$db->run($sql, $res);
							// delete the small-sized image
							// the thumbnail will be created by the first request

							// We dont use thumbnails any longer, now we use individual images
							@unlink($this->imageRealPath.DIRECTORY_SEPARATOR.'small'.DIRECTORY_SEPARATOR.$_fileName);
						}
					} else {
						$error = "Datei konnte nicht importiert werden. Zugriffs-Fehler auf Speicherort.";
					}
				}

				// Unlink all small images
				$dir = ABS_IMAGE_DIR.'tmp'.DIRECTORY_SEPARATOR;
				if (!is_dir($dir)) {
					mkdir($dir, 0777);
					chmod($dir, 0777);
				}
				$fileNamePart = substr($_fileName, 0, strpos($_fileName, '.'));
				if (($od = opendir($dir)) !== false) {
					while (($f = readdir($od)) !== false) {
						if (is_file($dir.$f) && substr_count($f, $fileNamePart)) {
							unlink($dir.$f);
						}
					}
				}

			} else {
				$error = "Die zu importierende Datei ist kein gültiges Bild.";
			}
		} else {
			if (is_array($file) && ($file['error'] > 0)) {
				switch ($file['error']) {
					case UPLOAD_ERR_INI_SIZE: $error = 'Datei zu gross, Maximal erlaubte Bytes: '.ini_get('upload_max_filesize'); break;
					case UPLOAD_ERR_FORM_SIZE: $error = 'Datei zu gross, Maximal erlaubte Bytes:  '.$_POST['MAX_FILE_SIZE']; break;
					case UPLOAD_ERR_PARTIAL: $error = 'Importvorgang unterbochen. Datei wurde nur teilweise hochgeladen.'; break;
					case UPLOAD_ERR_NO_FILE: $error = 'Es wurde keine Datei hochgeladen.'; break;
					case UPLOAD_ERR_CANT_WRITE: $error = 'Server-Fehler: Datei konnte nicht geschrieben werden.'; break;
					case UPLOAD_ERR_NO_TMP_DIR: $error = 'Server-Fehler: Es existiert kein Temp-Verzeichniss.'; break;
					default: $error = 'Unbekannter Fehler: '.$file['error']; break;
				}
			} else {
				$error = 'Unbekannter Fehler. Entweder wurde keine Datei hochgeladen oder keine Datei aus den FTP-Uploadverzeichniss ausgew�hlt.';
			}
		}
		return $error;
	}

	private function _deleteImage($id) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = "SELECT [img.image] FROM [table.img] WHERE [img.id]=".$id.";";
		$db->run($sql, $res);
		if ($res->getFirst()) {
			$image = $res->{$db->getFieldName('img.image')};
			@unlink($this->imageRealPath.'/'.$image);

			// We don't use the Small images any longer. Now we use individual Images
			// but we do create it, so we need to delete them
			@unlink($this->imageRealPath.'/small/'.$image);

			// Unlink all small images
			$dir = ABS_IMAGE_DIR.'tmp'.DIRECTORY_SEPARATOR;
			$fileNamePart = substr($image, 0, strpos($image, '.'));
			if (($od = opendir($dir)) !== false) {
				while (($f = readdir($od)) !== false) {
					if (is_file($dir.$f) && substr_count($f, $fileNamePart)) {
						unlink($dir.$f);
					}
				}
			}
		}
		$res = null;
		$sql = "DELETE FROM [table.img] WHERE [img.id]=".$id.";";
		$db->run($sql, $res);
		$res = null;
		$sql = "DELETE FROM [table.imt] WHERE [imt.image]=".$id.";";
		$db->run($sql, $res);
		$res = null;
	}

	/**
	 * Change the Order from all Images in given $order Array to the Order they are inside this Array
	 *
	 * @param int $section The Section ahere the Images lies in
	 * @param array $order All ID's in new Order
	 * @return boolean
	 */
	private function changeImageOrder($section, array $order) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$cnt = 0;
		foreach ($order as $img) {
			$sql = 'UPDATE [table.img] SET [field.img.order]='.$cnt.' WHERE [field.img.id]='.(int)$img.';';
			$db->run($sql, $res);
			$res = null;
			$cnt++;
		}
		return true;
	}

}
?>