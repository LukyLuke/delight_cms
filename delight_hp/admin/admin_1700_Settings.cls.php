<?php
/********************************************************/
/* Static-Files will represent the whole page.          */
/*                                                      */
/* The Filename has the following format:               */
/*    Language-ID-SectionID-PageOffset-Template.html    */
/*                                                      */
/* Exapmle:                                             */
/*    german-23-0-0-.html                               */
/*    german-24-0-0-dloadreg.html                       */
/*    german-24-0-1-image.html                          */
/********************************************************/

class admin_1700_Settings extends admin_MAIN_Settings {
	private $currentProcessStateData;

	/**
	 * Initialization
	 *
	 * @access public
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Calls from Administration-Interface
	 *
	 * @access public
	 */
	public function createActionBasedContent() {
		$userCheck = pCheckUserData::getInstance();

		if (pURIParameters::get('callDoCreateStaticSites', false, pURIParameters::$BOOLEAN) && (pURIParameters::get('action', '', pURIParameters::$STRING) == 'start_background')) {
			$lang = new pLanguage(pURIParameters::get('lang', MASTER_LANGUAGE, pURIParameters::$STRING));
			$this->doCreateStaticSites($lang);
			exit();
		}

		if ($userCheck->checkAccess($this->_adminAccess)) {

			switch (pURIParameters::get('action', '', pURIParameters::$STRING)) {
				case 'template':
					$tpl = pURIParameters::get('template', 'staticsites', pURIParameters::$STRING);
					$section = pURIParameters::get('section', 0, pURIParameters::$INT);
					$entry = pURIParameters::get('entry', 0, pURIParameters::$INT);
					$tpl = $this->getAdminContent($tpl, 1100);
					$tpl = str_replace('[SECTION_ID]', $section, $tpl);
					$tpl = str_replace('[ENTRY_ID]', $entry, $tpl);
					echo $this->ReplaceAjaxLanguageParameters( $tpl );
					exit();

				case 'content':
					$langId = pURIParameters::get('section', 0, pURIParameters::$INTEGER);
					$lang = new pLanguage($langId);
					$o = $lang->getSimpleObject();
					$o->sites_count_old = $this->getNumPublishedPages($lang);
					$o->sites_count = 0;
					$o->sites_created = 0;
					$o->sites_pending = 0;
					$o->time_elapsed = 0;
					$o->time_pending = 0;
					echo json_encode($o);
					exit();
					break;

				case 'sections':
					$list = new pLanguageList(true);
					$back = '[';
					foreach ($list as $lang) {
						if ($lang->active) {
							if (strlen($back) > 1) {
								$back .= ',';
							}
							$back .= '{"name":"'.utf8_encode($lang->extendedLanguage).'","id":'.$lang->languageId.',"icon":"'.utf8_encode($lang->icon).'","sub":[]}';
						} else {
							break;
						}
					}
					$back .= ']';
					echo $back;
					exit();
					break;

				case 'state':
					$lang = new pLanguage(pURIParameters::get('lang', MASTER_LANGUAGE, pURIParameters::$STRING));
					$obj = $this->getStaticProcessList($lang);
					$obj->call = 'showState';
					$obj->success = true;
					$obj->lang = $lang->shortLanguage;
					echo json_encode($obj);
					exit();
					break;

				case 'start':
					$lang = new pLanguage(pURIParameters::get('lang', MASTER_LANGUAGE, pURIParameters::$STRING));
					$success = $this->initializeBackgroundProcess($lang);
					echo '{"success":true,"started":'.($success ? 'true' : 'false').',"lang":"'.$lang->shortLanguage.'","call":"updateState"}';
					exit();
					break;

				case 'stop':
					$lang = new pLanguage(pURIParameters::get('lang', MASTER_LANGUAGE, pURIParameters::$STRING));
					$success = $this->cancelBackgroundProcess($lang);
					echo '{"success":true,"stopped":'.($success ? 'true' : 'false').',"lang":"'.$lang->shortLanguage.'","call":"updateState"}';
					exit();
					break;

			}
		} else {
			$this->showNoAccess();
		}
	}

	/**
	 * Get the number of currently existent static pages
	 *
	 * @param string $short Short language name
	 * @return integer
	 */
	private function getNumPublishedPages(pLanguage $lang) {
		$back = 0;
		$language = $lang->extendedLanguage;
		foreach (scandir(ABS_STATIC_DIR) as $file) {
			if (is_file(ABS_STATIC_DIR.$file) && (substr($file, 0, strlen($language)) == $language) && (substr_count($file, '-adm') <= 0)) {
				$back++;
			}
		}
		return $back;
	}

	/**
	 * Get the state of the current Process in pulishing
	 *
	 * @return stdClass
	 * @access private
	 */
	private function getStaticProcessList(pLanguage $lang) {
		$back = new stdClass();
		$back->language = new stdClass();
		$back->process = new stdClass();
		$back->language->name = $lang->extendedLanguage;
		$back->language->short = $lang->shortLanguage;
		$back->language->charset = $lang->charset;

		$back->process->num_pages = $this->getCurrentNumPages($lang);
		$back->process->total_published = $this->getCurrentPublishedPages($lang);
		$back->process->pending = $back->process->num_pages - $back->process->total_published;
		$back->process->time_now = time();
		$back->process->time_start = $this->getCurrentStartTime($lang);
		if ($back->process->time_start > 0) {
			$back->process->time_elapsed = $back->process->time_now - $back->process->time_start;
		} else {
			$back->process->time_elapsed = 0;
		}
		if ($back->process->total_published > 0) {
			$back->process->time_pending = ($back->process->time_elapsed / $back->process->total_published) * $back->process->pending;
		} else {
			$back->process->time_pending = 0;
		}
		$back->process->current_size = $this->getCurrentStaticSize($lang);
		$back->process->sites_deleted = $this->getCurrentDeletedFiles($lang);
		$back->process->finished = $this->getCurrentProcessState($lang);
		return $back;
	}

	/**
	 * Return the Process-State for the given Language
	 * If the File was once readen, it will not readen once again,
	 * the last data will be returned - see $this->currentProcessStateData
	 *
	 * @param pLanguage $lang
	 * @return array
	 */
	private function getProcessStateData(pLanguage $lang) {
		if (!is_array($this->currentProcessStateData)) {
			$this->currentProcessStateData = array();
		}
		if (!array_key_exists($lang->extendedLanguage, $this->currentProcessStateData)) {
			$this->currentProcessStateData[$lang->extendedLanguage] = array();
		}

		if (count($this->currentProcessStateData[$lang->extendedLanguage]) <= 0) {
			$file = ABS_STATIC_DIR.'progress_'.$lang->extendedLanguage.'.lock';
			if (is_file($file)) {
				$data = file($file);
				foreach ($data as $line) {
					$this->currentProcessStateData[$lang->extendedLanguage][] = explode(';', trim($line));
				}
			}
		}
		return $this->currentProcessStateData[$lang->extendedLanguage];
	}

	/**
	 * Get the start-time from creation-process in given language
	 *
	 * @param pLanguage $lang Language to get the StartTime from
	 * @return integer Timestamp
	 * @access private
	 */
	private function getCurrentStartTime(pLanguage $lang) {
		$data = $this->getProcessStateData($lang);
		foreach ($data as $line) {
			if ($line[0] == 'start') {
				return intval($line[1]);
			}
		}
		return 0;
	}

	/**
	 * Return the current size of all created static pages
	 *
	 * @param pLanguage $lang Language to get the Size from
	 * @return float
	 * @access private
	 */
	private function getCurrentStaticSize(pLanguage $lang) {
		$data = $this->getProcessStateData($lang);
		$size = 0;
		foreach ($data as $line) {
			if ($line[0] == 'size') {
				$size += floatval($line[1]);
			}
		}
		return $size;
	}

	/**
	 * Return the number of pages where deleted
	 *
	 * @param pLanguage $lang Language to get the deleted pages number from
	 * @return int
	 * @access private
	 */
	private function getCurrentDeletedFiles(pLanguage $lang) {
		$data = $this->getProcessStateData($lang);
		$deleted = 0;
		foreach ($data as $line) {
			if ($line[0] == 'delete') {
				$deleted += intval($line[1]);
			}
		}
		return $deleted;
	}

	/**
	 * Return the number of pages they were created during the current process
	 *
	 * @param pLanguage $lang Language to get the num of pages from
	 * @return int
	 * @access private
	 */
	private function getCurrentPublishedPages(pLanguage $lang) {
		$data = $this->getProcessStateData($lang);
		$created = 0;
		foreach ($data as $line) {
			if ($line[0] == 'created') {
				$created += 1;
			}
		}
		return $created;
	}

	/**
	 * Return the number of pages they should be created
	 *
	 * @param pLanguage $lang Language to get the num of pages from
	 * @return int
	 * @access private
	 */
	private function getCurrentNumPages(pLanguage $lang) {
		$data = $this->getProcessStateData($lang);
		$create = 0;
		foreach ($data as $line) {
			if ($line[0] == 'num_sites') {
				$create += intval($line[1]);
			}
		}
		return $create;
	}

	/**
	 * Check wether the creation process is finished or not
	 *
	 * @param pLanguage $lang Language to get the process state from
	 * @return boolean
	 * @access private
	 */
	private function getCurrentProcessState(pLanguage $lang) {
		$data = $this->getProcessStateData($lang);
		foreach ($data as $line) {
			if ($line[0] == 'end') {
				return true;
			} else if ($line[0] == 'abort') {
				$this->writeStateFile($lang, 'end', '');
				return true;
			}
		}
		return (count($data) <= 0);
	}

	/**
	 * Start the background-process which creates all static sites
	 *
	 * @param pLanguage $lang Language to start the Create-Process
	 * @return boolean
	 * @access private
	 */
	private function initializeBackgroundProcess(pLanguage $lang) {
		// If there is already a process running, we dont start over again
		if (!$this->getCurrentProcessState($lang)) {
			return false;
		}

		$_getLink = $_SERVER['SCRIPT_NAME'].'?adm=1700&lang='.$lang->shortLanguage.'&action=start_background&callDoCreateStaticSites=true';
		$crlf = "\r\n";
		$errno = 0;
		$errstr = '';
		$fp = pfsockopen($_SERVER['SERVER_NAME'], $_SERVER['SERVER_PORT'], $errno, $errstr);
		stream_set_blocking($fp, 0);
		fwrite($fp, 'GET '.$_getLink.' HTTP/1.1'.$crlf.'Host: '.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].$crlf.'Connection: Close'.$crlf.$crlf);
		fgets($fp, 1024);
		fclose($fp);
		return !$this->getCurrentProcessState($lang);
	}

	/**
	 * Write the progress-file to cancel the current creation-process
	 *
	 * @param pLanguage $lang Language to cancel the creation-process
	 * @return boolean
	 * @access private
	 */
	private function cancelBackgroundProcess(pLanguage $lang) {
		return $this->writeStateFile($lang, 'abort', 'abort');
	}

	/**
	 * Write state data into the datafile
	 *
	 * @param pLanguage $lang Language to write the State for
	 * @param string $key the key
	 * @param string $value value for the key
	 * @access private
	 */
	private function writeStateFile(pLanguage $lang, $key, $value) {
		$file = ABS_STATIC_DIR.'progress_'.$lang->extendedLanguage.'.lock';
		$data = str_replace(";", "&#44;", $key).';'.str_replace(";", "&#44;", trim($value)).PHP_EOL;
		$written = file_put_contents($file, $data, FILE_APPEND);
		return ($written > 0);
	}

	/**
	 * Initialize the statefile
	 *
	 * @param pLanguage $lang Language to initialize the statefile
	 * @access private
	 */
	private function initializeStateFile(pLanguage $lang) {
		$file = ABS_STATIC_DIR.'progress_'.$lang->extendedLanguage.'.lock';
		if (is_file($file)) {
			unlink($file);
		}
		$this->writeStateFile($lang, 'begin', 'begin');
		if (is_file($file)) {
			chmod($file, 0777);
		}
	}

	/** Socket initialized by PHP so the Client can check the Access trough Ajax **/
	/** On some debian systems there is a bug within the DNS-Resolve **/
	/** just add the Domain to the hosts-file and these functions are faster **/
	/**
	 * make all sites in selected language ($TextContent - param textContent) static
	 * this includes all pages, main-page and also subpages (and so on)
	 *
	 */
	private function doCreateStaticSites(pLanguage $lang) {
		if (pURIParameters::get('callDoCreateStaticSites', false, pURIParameters::$BOOLEAN)) {
			ignore_user_abort(true);
			set_time_limit(0);

			// print out some dots so we can break the socket in section above
			echo 'started';
			echo str_repeat(".", 1024);
			flush();

			$db = pDatabaseConnection::getDatabaseInstance();
			$res = null;
			$match = null;
			$_getPagesList = array();
			$_realSites = array();    // All pages which exists in curent language and are not listen here will be deleted
			$_OBJECTS = array();
			$_pluginsContents = array();
			$_searchEnabled = false;
			$_filesDeleted = 0;

			// Check for a valid language
			if ($lang instanceof pLanguage) {
				$this->initializeStateFile($lang);

				// Remove all Text-Images (they will be created by the first request)
				$tmpdir = ABS_IMAGE_DIR.'tmp'.DIRECTORY_SEPARATOR;
				foreach (scandir($tmpdir) as $file) {
					if (is_file($tmpdir.$file) && (substr($file, 0, strlen($lang->short)) == $lang->short)) {
						unlink($tmpdir.$file);
					}
				}

				// Initialize the sitemap-XML
				$this->createSitemapIndexFile();
				$this->sitemapXmlInit($lang);

				// include all Text-Plugins...
				foreach (explode(",", constant("_textPlugins")) as $v) {
					$_OBJECTS[strtolower($v)] = new $v($this->DB);
				}
				$_searchEnabled = (class_exists('SEARCH') && array_key_exists('search', $_OBJECTS) && ($_OBJECTS['search'] instanceof iPlugin));

				// Get the base template to check for includes and others
				$_base = file_get_contents(ABS_TEMPLATE_DIR.'base.html');

				// Check for Plugin-Contents
				$match = null;
				//[PLUGIN:shop:SIMPLESHOP:ShowCategories]params:template=menu_main.tpl;level=1[/PLUGIN:shop:SIMPLESHOP:ShowCategories]
				if (preg_match_all("/(\[PLUGIN:)([^:]*):([^:]*):([^\]]+)(\])(.*?)(\[\/PLUGIN:\\2:\\3:\\4\])/smi", $_base, $match, PREG_SET_ORDER)) {
					foreach ($match as $k => $v) {
						// List with: 0=>ShortMenu, 1=>PluginName, 2=>PluginFunction, 3=>PluginParameters
						$_pluginsContents[] = array($v[2], $v[3], $v[4], $v[6]);
					}
				}
				unset($match);
				unset($_base);

				// Delete all Search-Pages if enabled
				if ($_searchEnabled) {
					$_OBJECTS['search']->getStaticPagesList($lang->languageId, null);
				}

				// Get all pages to create
				$sql  = 'SELECT [men.id],[men.short],[men.parent] FROM [table.men],[table.mtx] WHERE [men.id]=[mtx.menu] AND [mtx.lang]='.$lang->languageId.' AND [mtx.active]=1;';
				$db->run($sql, $res);
				if ($res->getFirst()) {
					$_menu = new pMenuEntry();

					while ($res->getNext()) {
						$_menu->load($res->{$db->getFieldName('men.id')}, $lang);
						$_staticFile = trim(strtolower($lang->extendedLanguage)).'-'.$_menu->id.'-0-0-.html';

						// Append the URL to the Sitemap-XML only if there are no restricted access
						if (count($_menu->getAccessGroups(true)) > 0) {
							// The Mainmenu has Priority 1, subpages have 0.8
							if ($res->{$db->getFieldName('men.parent')} == 0) {
								$this->sitemapXmlAppend($lang, $_menu->link, '1.0');
							} else {
								$this->sitemapXmlAppend($lang, $_menu->link, '0.8');
							}
						}

						// Only add the site if it's not already registered
						if (!in_array($_staticFile, $_realSites)) {
							array_push($_realSites, $_staticFile);
							array_push($_realSites, str_replace('.html', '-adm.html', $_staticFile));

							array_push($_getPagesList, array($_menu->getStaticPagesLink($lang->shortLanguage), $_staticFile));
							array_push($_getPagesList, array($_menu->getStaticPagesLink($lang->shortLanguage) . '&adm=1', str_replace('.html', '-adm.html', $_staticFile)));
						}

						// Check for PluginContent
						// $p is a list with: 0=>ShortMenu, 1=>PluginName, 2=>PluginFunction, 3=>PluginParameters
						foreach ($_pluginsContents as $p) {
							if ( (empty($p[0]) || ($p[0] == $_menu->short)) && (array_key_exists(strtolower($p[1]), $_OBJECTS)) ) {
								$plg = $_OBJECTS[strtolower($p[1])];
								if ($plg instanceof iPlugin) {
									$plg->setContentParameters($p[3]);
									$_pluginFileList = $plg->getStaticPagesList($lang->id, $_menu->id, $_menu->short, true);

									foreach ($_pluginFileList as $_f) {
										// We just add this file if it's not already in list
										// double-sites can be occure, because we can have images, downloads, etc. on multiple pages
										$_plgFile = trim(strtolower($lang->extendedLanguage)).'-'.$_menu->id.'-0-0-'.str_replace('/', '_DS_', $_f['file']).'.html';
										$_plgLink = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].'/'.$lang->shortLanguage.'/'.$_menu->id.$_f['link'].'/doGetStaticPages=true';
										if (!in_array($_plgFile, $_realSites)) {
											array_push($_realSites, $_plgFile);
											array_push($_getPagesList, array($_plgLink, $_plgFile));
											$this->sitemapXmlAppend($lang, $_getLink, '0.4');
										}
									}
									unset($_pluginFileList);
								}
							}
						}

					}
					$res = null;

					// Append all TEXTBLOCKS as single page to open them in a Popup or on an othe page
					$sql = 'SELECT [txt.id] FROM [table.txt] WHERE [txt.lang]='.$lang->id.';';
					$db->run($sql, $res);
					if ($res->getFirst()) {
						$idField = $db->getFieldName('txt.id');
						while ($res->getNext()) {
							$_getLink = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].'/'.$lang->shortLanguage.'/text/'.$res->{$idField}.'/doGetStaticPages=true';
							$_staticFile = trim(strtolower($lang->extendedLanguage)).'-'.$res->{$idField}.'-0-0-text.html';

							// Only add the site if it's not already registered
							if (!in_array($_staticFile, $_realSites)) {
								array_push($_realSites, $_staticFile);
								array_push($_getPagesList, array($_getLink, $_staticFile));
								$this->sitemapXmlAppend($lang, $_getLink, '0.4');
							}
						}
					}

					// Append all IMAGES from SCREENSHOTS Plugin
					$sql = 'SELECT [img.id],[img.section] FROM [table.img];';
					$db->run($sql, $res);
					if ($res->getFirst()) {
						$idField = $db->getFieldName('img.id');
						$secField = $db->getFieldName('img.section');
						while ($res->getNext()) {
							$_getLink = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].'/'.$lang->shortLanguage.'/image/'.$res->{$idField}.'/doGetStaticPages=true';
							//$_staticFile = trim(strtolower($lang->extendedLanguage)).'-'.$res->{$idField}.'-'.$res->{$secField}.'-0-image.html';
							$_staticFile = trim(strtolower($lang->extendedLanguage)).'-'.$res->{$idField}.'-0-0-image.html';

							// Only add the site if it's not already registered
							if (!in_array($_staticFile, $_realSites)) {
								array_push($_realSites, $_staticFile);
								array_push($_getPagesList, array($_getLink, $_staticFile));
								$this->sitemapXmlAppend($lang, $_getLink, '0.4');
							}
						}
					}
					// TODO: Add Sections if needed

					// Append all NEWS from NEWS Plugin
					$sql = 'SELECT [new.id],[new.section] FROM [table.new] WHERE [new.lang]='.$lang->languageId.';';
					$db->run($sql, $res);
					if ($res->getFirst()) {
						$idField = $db->getFieldName('new.id');
						$secField = $db->getFieldName('new.section');
						while ($res->getNext()) {
							$_getLink = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].'/'.$lang->shortLanguage.'/news/'.$res->{$idField}.'/sec='.$res->{$secField}.'&doGetStaticPages=true';
							$_staticFile = trim(strtolower($lang->extendedLanguage)).'-'.$res->{$idField}.'-'.$res->{$secField}.'-0-news.html';

							// Only add the site if it's not already registered
							if (!in_array($_staticFile, $_realSites)) {
								array_push($_realSites, $_staticFile);
								array_push($_getPagesList, array($_getLink, $_staticFile));
								$this->sitemapXmlAppend($lang, $_getLink, '0.4');
							}
						}
					}
					// TODO: Add Sections if needed

					// Append all PROGRAMS from DOWNLOADS Plugin
					$sql = 'SELECT [prg.id],[prg.section] FROM [table.prg];';
					$db->run($sql, $res);
					if ($res->getFirst()) {
						$idField = $db->getFieldName('prg.id');
						$secField = $db->getFieldName('prg.section');
						while ($res->getNext()) {
							$_getLink = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].'/'.$lang->shortLanguage.'/program/'.$res->{$idField}.'/sec='.$res->{$secField}.'&doGetStaticPages=true';
							$_staticFile = trim(strtolower($lang->extendedLanguage)).'-'.$res->{$idField}.'-'.$res->{$secField}.'-0-program.html';

							// Only add the site if it's not already registered
							if (!in_array($_staticFile, $_realSites)) {
								array_push($_realSites, $_staticFile);
								array_push($_getPagesList, array($_getLink, $_staticFile));
								$this->sitemapXmlAppend($lang, $_getLink, '0.4');
							}
						}
					}
					// TODO: Add Sections if needed

					if ($this->getCurrentProcessState($lang)) {
						exit();
					}

					// write number of pages to the state-file
					$this->writeStateFile($lang, "num_sites", count($_realSites));
					echo 'Sites: '.count($_realSites).PHP_EOL;

					// finalize the sitemap-XML
					$this->sitemapXmlFinalize($lang);

					// Create the Static Menu-Table
					$res = null;
					$sql = 'DELETE FROM [table.staticmenu] WHERE [staticmenu.lang]='.$lang->languageId.';';
					$db->run($sql, $res);
					$res = null;
					$sql = 'SELECT [men.id],[men.short],[mtx.transshort],[mtx.lang] FROM [table.men],[table.mtx] WHERE [men.id]=[mtx.menu] AND [mtx.lang]='.$lang->languageId.' AND [mtx.active]=1;';
					$db->run($sql, $res);
					if ($res->getFirst()) {
						while ($res->getNext()) {
							$ires = null;
							$isql = 'INSERT INTO [table.staticmenu] ([field.staticmenu.menu],[field.staticmenu.short],[field.staticmenu.translated],[field.staticmenu.lang])'.
							' VALUES ('.$res->{$db->getFieldName('men.id')}.',\''.$res->{$db->getFieldName('men.short')}.'\',\''.$res->{$db->getFieldName('mtx.transshort')}.'\','.$lang->languageId.');';
							$db->run($isql, $ires);
						}
						$ires = null;
					}
					$res = null;

					// get unused files (deleted pages)
					$langLength = strlen($lang->extendedLanguage);
					foreach (scandir(ABS_STATIC_DIR) as $file) {
						if ( (substr($file, 0, $langLength) == $lang->extendedLanguage) && (!in_array($file, $_realSites))) {
							unlink(ABS_STATIC_DIR.$file);
							$_filesDeleted += 1;
						}
					}
					$this->writeStateFile($lang, "delete", $_filesDeleted);
					echo 'Remove: '.$_filesDeleted.PHP_EOL;

					if ($this->getCurrentProcessState($lang)) {
						exit();
					}

					// grab and save the pages
					$this->grabAndSaveUrlToFile($_getPagesList, $lang);

				} else {
					$this->writeStateFile($lang, "error", "no pages found");
					echo 'no pages found'.PHP_EOL;
				}

				$this->writeStateFile($lang, "end", "");
				echo 'end'.PHP_EOL;
			}
		} else {
			echo 'invalid request'.PHP_EOL;
		}
		exit();
	}

	/**
	 * Grab an URL and save this to a file on the server
	 * $fileList[][0] represents the URL
	 * $fileList[][1] represents the local file
	 *
	 * @param array $fileList List of files and URL's t grab and save
	 * @param boolean $echoMessages true if messages should be showed, false for silent
	 * @param pLanguage $lang Language to grab and save pages for
	 * @access private
	 */
	private function grabAndSaveUrlToFile($fileList, pLanguage $lang) {
		$this->writeStateFile($lang, "begin", "grab");

		foreach ($fileList as $v) {
			// Don't continue if someone has aborted the process
			unset($this->currentProcessStateData[$lang->extendedLanguage]);
			if ($this->getCurrentProcessState($lang)) {
				exit();
			}

			$_getLink = $v[0];
			$_staticFile = $v[1];
			//echo $_getLink.chr(10);

			// Get the microtime for "time-to-create"
			$_c_begin = microtime(true);

			$this->writeStateFile($lang, "url", $_getLink);
			$url = parse_url($_getLink);
			$crlf = chr(13).chr(10);
			$headers = 'Host: '.$url['host'].':'.$url['port'].$crlf.
			           'Connection: close'.$crlf.
			           'User-Agent: delight cms static site generation';

			// Get the Content - preffered with file_get_contents and allow_url_fopen enabled, otherwise traditionally
			if (false && ini_get('allow_url_fopen')) {
				$context = stream_context_create(array('http' => array(
					'header' => $headers,
					'method' => 'GET'
				)));
				$data = file_get_contents($_getLink, false, $context);

			} else {
				$errno = 0;
				$errstr = '';
				$fp = pfsockopen($_SERVER['SERVER_NAME'], $_SERVER['SERVER_PORT'], $errno, $errstr);
				stream_set_blocking($fp, 0);
				fwrite($fp, 'GET '.$url['path'].' HTTP/1.1'.$crlf.$headers.$crlf.$crlf);
				$data = '';
				while (is_resource($fp) && !feof($fp)) {
					$data .= fgets($fp, 1024);
				}
				if (is_resource($fp)) {
					fclose($fp);
				} else {
					$this->writeStateFile($lang, "read_error", "Socket died while getting: " . $url['path']);
					$this->writeStateFile($lang, "read_error", "Error(" . $errno . "): " . $errstr);
				}

				$pos = strpos($data, $crlf.$crlf);
				$header = explode($crlf, trim(substr($data, 0, $pos)) );
				$data = trim(substr($data, $pos));
				$odata = $data;

				// Chunked response, just remove the sizes...
				$cnt = 0;
				foreach ($header as $h) {
					if (trim($h) == "Transfer-Encoding: chunked") {
						$end_data = '';
						$hsize = trim(substr($data, 0, strpos($data, "\r\n", 0)));
						$size = hexdec($hsize);
						$start = strlen($hsize) + 2;

						while ($size > 0) {
							$end_data .= substr($data, $start, $size);
							$start += $size + 2;

							$hsize = trim(substr($data, $start, strpos($data, "\r\n", $start) - $start));
							$size = hexdec($hsize);
							$start += strlen($hsize) + 2;

							if ($cnt > 20) break;
							$cnt++;
						}
						$data = $end_data;
						break;
					}
				}
			}

			if (strlen($data) > 0) {
				// Add the link from the page as last: Just for "debuging"
				$data = str_replace('</html>', '<!--'.chr(10).$_getLink.chr(10).'-->'.chr(10).'</html>', $data);

				$written = file_put_contents(ABS_STATIC_DIR.$_staticFile, $data);
				if (is_file(ABS_STATIC_DIR.$_staticFile)) {
					chmod(ABS_STATIC_DIR.$_staticFile, 0777);
				}

				// Show the Creation-time
				$_c_end = microtime(true);

				// Check if the file was written or not
				if ($written <= 0) {
					$this->writeStateFile($lang, "write_error", $_staticFile);
				} else {
					$this->writeStateFile($lang, "created", $_staticFile);
					$this->writeStateFile($lang, "size", filesize(ABS_STATIC_DIR.$_staticFile));
				}
				$this->writeStateFile($lang, "time", ($_c_end - $_c_begin));

			} else {
				$this->writeStateFile($lang, "fetch_error", $_getLink);
			}
			unset($data);
		}
	}

	/**
	 * Create the Sitemap-Indexfile and sitmap-files the don't exists
	 * @access private
	 */
	private function createSitemapIndexFile() {
		// sitemapIndex file
		$indexFile = ABS_STATIC_DIR.'sitemap.xml';
		$index = '<?xml version="1.0" encoding="UTF-8"?>'.chr(10);
		$index .= '<sitemapindex '.
		          'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '.
		          'xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 '.
		          'http://www.sitemaps.org/schemas/sitemap/0.9/siteindex.xsd" '.
		          'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.chr(10);

		$list = new pLanguageList(true);
		foreach ($list as $lang) {
			// Selected languages are loaded first, so we can break on the first inactive
			if (!$lang->active) {
				break;
			}
			$index .= '  <sitemap>'.chr(10);
			$index .= '    <loc>http://'.$_SERVER['HTTP_HOST'].'/delight_hp/template/static/sitemap.'.$lang->name.'.xml</loc>'.chr(10);
			$index .= '    <lastmod>'.$this->getW3CTime().'</lastmod>'.chr(10);
			$index .= '  </sitemap>'.chr(10);

			// Check if this file exists
			$sitemapFile = ABS_STATIC_DIR.'sitemap.'.$lang->name.'.xml';
			if (!file_exists($sitemapFile)) {
				$this->sitemapXmlInit($lang);
				$this->sitemapXmlFinalize($lang);
			}
		}
		$index .= '</sitemapindex>'.chr(10);
		file_put_contents($indexFile, $index);
		if (is_file($indexFile)) {
			chmod($indexFile, 0777);
		}
		unset($list);
		unset($index);
		unset($indexFile);
	}

	/**
	 * initialize a Sitemap-XML file
	 * write the headers and some general informations if needed
	 *
	 * @param string $lang The Language, it's a Part of the filename linked in the sitemapindex
	 */
	private function sitemapXmlInit(pLanguage $lang) {
		$sitemapFile = ABS_STATIC_DIR.'sitemap.'.$lang->extendedLanguage.'.xml';
		$sitemap  = '<?xml version="1.0" encoding="UTF-8"?>'.chr(10);
		$sitemap .= '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '.
		            'xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 '.
		            'http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" '.
		            'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.chr(10);
		file_put_contents($sitemapFile, $sitemap);
		if (is_file($sitemapFile)) {
			chmod($sitemapFile, 0777);
		}
		//$this->sitemapXmlAppend($lang, 'http://'.$_SERVER['HTTP_HOST'].'/', '1.0'); // not allowed according to google
	}

	/**
	 * finalize a Sitemap-XML file
	 * write all closeing tags and add some general informations if needed
	 *
	 * @param string $lang The Language, it's a Part of the filename linked in the sitemapindex
	 */
	private function sitemapXmlFinalize(pLanguage $lang) {
		$sitemapFile = ABS_STATIC_DIR.'sitemap.'.$lang->extendedLanguage.'.xml';
		$sitemap = '</urlset>'.chr(10);
		file_put_contents($sitemapFile, $sitemap, FILE_APPEND);
	}

	/**
	 * Create a URL-Tag in a sitemap-XML
	 *
	 * @param string $lang The Language, it's a Part of the filename linked in the sitemapindex
	 * @param string $link URL to link to
	 * @param string $prio Priority - highest=1.0 - lowest 0.0 --
	 */
	private function sitemapXmlAppend(pLanguage $lang, $link, $prio='0.5') {
		$link = str_replace("&doGetStaticPages=true", "", $link);
		$link = str_replace("doGetStaticPages=true", "", $link);
		$link = str_replace('&', '&amp;', $link);

		$sitemapFile = ABS_STATIC_DIR.'sitemap.'.$lang->extendedLanguage.'.xml';
		$sitemap  = '  <url>'.chr(10);
		$sitemap .= '    <loc>'.$link.'</loc>'.chr(10);
		$sitemap .= '    <lastmod>'.$this->getW3CTime().'</lastmod>'.chr(10);
		$sitemap .= '    <priority>'.$prio.'</priority>'.chr(10);
		$sitemap .= '    <changefreq>daily</changefreq>'.chr(10);
		$sitemap .= '  </url>'.chr(10);
		file_put_contents($sitemapFile, $sitemap, FILE_APPEND);
	}

	/**
	 * Return a W3C conform date for Sitemap-XML - ISO-8601 format
	 *
	 * @return string Date in ISO8601 form
	 */
	private function getW3CTime() {
		//return date("Y-m-d\TH:i:s"); // No Time for tag "lastmod"
		return date("Y-m-d");
	}

}
?>