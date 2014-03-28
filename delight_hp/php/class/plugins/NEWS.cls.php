<?php

class NEWS extends MainPlugin {
	const VERSION = 2012061500;
	const LAYOUT = 'default';
	const FULLSCREEN_LAYOUT = 'show_complete';

	private $contentFile;
	private $_newsContent;
	private $_newsLine;
	private $_sectionContent;
	private $_sectionDelimiter;
	private $_sectionDelimiterImages;
	private $_readMoreContent;
	private $_newsContentLine;
	private $templateTag;

	// TODO: Find out for what this var should be
	private $_specialLinkDirection;

	/**
	 * Initialization
	 *
	 * @access public
	 */
	public function __construct() {
		parent::__construct();
		$this->_isTextPlugin = true;

		$this->contentFile = ABS_TEMPLATE_DIR.DIRECTORY_SEPARATOR.'cont_news.tpl';
		$this->_newsContent = "";
		$this->_newsLine = "";
		$this->_readMoreContent = array();
		$this->_newsContentLine = "";
		$this->templateTag = array();
		$this->_specialLinkDirection = '';

		$this->_sectionContent = '';
		$this->_sectionDelimiter = array('','','','');
		$this->_sectionDelimiterImages = array('','','');

		$this->_checkDatabase();
	}

	/**
	 * Additional Options for the TextEditor
	 *
	 * @param string $options Options from Template
	 * @return string Options like defined in a Template
	 * @access public
	 */
	public function getAdditionalOptions($options='') {
		$opt  = '[OPTIONS]';
		$opt .= '[show_recursive]choose:true,false[/show_recursive]';
		$opt .= '[show_number]edit:integer:0[/show_number]';
		$opt .= '[show_section]choose:true,false[/show_section]';
		$opt .= '[show_title]choose:true,false[/show_title]';
		$opt .= '[/OPTIONS]';
		return $opt;
	}

	/**
	 * Create the HTML-Source and return it
	 *
	 * @param string $method A special method-name - here we use it to switch between the complete List and just a list of news
	 * @param array $adminData If shown under Admin-Editor, this Array must be a complete DB-likeness Textentry
	 * @param array $templateTag If included in a Template, this array has keys 'layout','template','num' with equivalent values
	 * @return string
	 * @access public
	 */
	public function getSource($method="", $adminData=array(), $templateTag=array()) {
		$this->templateTag = $templateTag;
		if ($method == PLG_NEWS_METHOD) {
			return $this->getCompleteNewsSource();
		} else if ($method == PLG_NEWS_FEEDCONT) {
			return $this->getNewsContainer();
		} else {
			return $this->getNewsList($adminData);
		}
	}

	/**
	 * Just get the title
	 * @return string
	 */
	public function getTitle() {
		return $this->getTextEntryObject()->title;
	}

	/**
	 * Just get the Content
	 * @return string
	 */
	public function getContent() {
		return $this->getNewsList(null, true);
	}

	/**
	 * Return the OpenEditor function for ContentAdministration
	 *
	 * @param integer $id The TextID
	 * @return string
	 * @access public
	 */
	public function getEditFunction($id) {
		return 'news';
		return "javascript:openAdmin(1400,'news_content',".$id.");";
	}

	/**
	 * Return the CloseEditor function for ContentAdministration
	 *
	 * @param integer $id The TextID
	 * @return string
	 * @access public
	 */
	public function getCloseFunction($id) {
		return "javascript:closeDelightEdit();";
	}

	/**
	 * Return just the NEWS-Container with a JavaScript to fetch the right RSS and parse this
	 */
	private function getNewsContainer() {
		// Get a Database-Instance
		$db = pDatabaseConnection::getDatabaseInstance();
		$lang = pMessages::getLanguageInstance();
		$SectionId = pURIParameters::get('sec', 0 ,pURIParameters::$INT);
		$showNewsNum = -1;

		// News are included from a Template - not as a Standalone-TextEntry-Block
		// [INCLUDE_NEWS:"tpl_file":"tpl":"Title":"show_num"]
		if (count($this->templateTag) > 1) {
			$sql = "SELECT [nes.id] FROM [table.nes] WHERE [nes.parent]='0' ORDER BY [nes.id] ASC;";
			$res = null;
			$db->run($sql, $res);
			if ($res->getFirst()) {
				$_sec = $res->{$db->getFieldName('nes.id')};
			} else {
				$_sec = 0;
			}
			$_textData = new pTextEntry(null);
			$_textData->layout = $this->templateTag['layout'];
			$_textData->title = $this->templateTag['template'];
			$_textData->id = 0;
			$_textData->sort = 0;
			$_textData->text = $_sec;
			$_textData->lang = $lang->getLanguageName();
			$_textData->options = '#show_recursive=true##show_number='.(int)$this->templateTag['num'].'##show_title=true#';
			$_template = $this->_readTemplateFile($this->templateTag['layout']);
			if (intval($this->templateTag['num']) > 0) {
				$showNewsNum = intval($this->templateTag['num']);
			}
		} else {
			$_textData = $this->getTextEntryObject();
			$_template = $this->_readTemplateFile($_textData->layout);
		}

		// Replace unused TemplateTags
		$_template = str_replace("[ADMIN_FUNCTIONS]", "", $_template);
		$_template = preg_replace("/(\[TEXT_ADMIN_FUNCTIONS\])(.*?)(\[\/TEXT_ADMIN_FUNCTIONS\])/smi", "", $_template);

		// Parse the Layout to get all needed contents
		$_layout = empty($this->templateTag['template']) ? self::LAYOUT : $this->templateTag['template'];
		$this->parseNewsContentTemplateFile($_layout);

		// Get content for "read more"
		$readmore = null;
		foreach ($this->_readMoreContent as $v) {
			if ($v->layout == $_layout) {
				$readmore = $v->content;
				break;
			}
		}
		if ($readmore == null) {
			$this->_readMoreContent[0]->content;
		}

		// Check for empty template
		if (strlen($this->_newsContent) <= 0) {
			$this->_newsContent = '[TEXT]';
		}
		if (strlen($this->_newsLine) <= 0) {
			$this->_newsLine = '[TEXT]';
		}
		if (strlen($this->_newsContentLine) <= 0) {
			$this->_newsContentLine = '[NEWS_NUMBER].) [NEWS_DATE_EXTENDED]<br /><h3>[NEWS_TITLE]</h3>[NEWS_TEXT]';
		}

		// Get all News from Section
		if ((int)$_textData->text > 0) {
			$SectionId = (int)$_textData->text;
		}

		// Get Category-Title
		if (count($this->templateTag) > 0) {
			$_title = $this->templateTag['title'];
		} else {
			$_title = $_textData->title;
		}

		// Check for Recursive-Newsview  (show_recursive=Yes)
		if (substr_count($_textData->options, "#show_recursive=true#") > 0) {
			$_recursive = true;
		} else {
			$_recursive = false;
		}

		// Create the Javascript for showing the RSS-FEED-News here
		$_uid = 'atom'.sha1(uniqid(rand(), true));
		$_text  = '<div id="'.$_uid.'"></div>';
		$_text .= '<script type="text/javascript" src="/delight_hp/data/atomParser.js"></script>'."\r\n";
		$_text .= '<script type="text/javascript">//<![CDATA['."\r\n";
		$_text .= 'function _a() {';
		$_text .= 'if (typeof(atomReader) == \'undefined\') {';
		$_text .= 'setTimeout("_a()",100);';
		$_text .= '} else {';
		$_text .= 'a = new atomReader();';
		$_text .= 'a.feed("/delight_hp/feed.php?s='.$SectionId.'&r='.($_recursive?1:0).'&lang='.$lang->getShortLanguageName().'&n='.(($showNewsNum>0)?$showNewsNum:10).'");';
		$_text .= 'a.line("'.$this->escapeHtmlForJavaScript($this->_newsLine).'");';
		$_text .= 'a.content("'.$this->escapeHtmlForJavaScript($this->_newsContentLine).'");';
		$_text .= 'a.moreinfo("[LANG_VALUE:input_006]");';
		$_text .= 'a.morelink("'.$this->escapeHtmlForJavaScript(str_replace('[NEWS_COMPLETE_LINK]', '[NEWS_LINK]', $readmore)).'");';
		if ($showNewsNum > 0) {
			$_text .= 'a.maxnews("'.$showNewsNum.'");';
		} else {
			$_text .= 'a.maxnews("5");';
		}
		//$_text .= 'a.parseAndWrite();';
		$_text .= 'a.parseAndReplace(document.getElementById(\''.$_uid.'\'));';
		$_text .= '};';
		$_text .= '};';
		$_text .= 'setTimeout("_a()",100);';
		$_text .= "\r\n".'//]]></script>'."\r\n";
		$_text = str_replace("[TEXT]", $_text, $this->_newsContent);

		$_cont = $_template;
		$_cont = str_replace('[TEXT]', $_text, $_cont);
		$_cont = str_replace('[CAT_CONTENT]', '', $_cont);
		$_cont = str_replace('[/CAT_CONTENT]', '', $_cont);

		if (strlen($_title) > 0) {
			$_cont = str_replace('[TITLE]', $_title, $_cont);
			$_cont = str_replace('[CAT_TITLE]', '', $_cont);
			$_cont = str_replace('[/CAT_TITLE]', '', $_cont);
		} else {
			$_cont = str_replace('[TITLE]', '', $_cont);
			$_cont = preg_replace('/(\[CAT_TITLE\])(.*?)(\[\/CAT_TITLE\])/', '', $_cont);
		}
		$_cont = $this->ReplaceLayoutOptions($_cont, $_textData->options);

		return $_cont;
	}

	/**
	 * Return the HTML-Source, based on the template for a List of all News in a Section
	 *
	 * @param array $adminData If shown under Admin-Editor, this Array must be a complete DB-likeness Textentry
	 * @return string HTML-Source from News-Entries
	 * @access public
	 */
	public function getNewsList($adminData=array(), $onlyContent=false) {
		// Get a Database-Instance
		$db = pDatabaseConnection::getDatabaseInstance();
		$lang = pMessages::getLanguageInstance();
		$menu = pMenu::getMenuInstance();
		$SectionId = pURIParameters::get('sec', 0 ,pURIParameters::$INT);
		$adminAction = pURIParameters::get('adm', 0 ,pURIParameters::$INT);
		$userCheck = pCheckUserData::getInstance();

		// News are included from a Template - not as a Standalone-TextEntry-Block
		// [INCLUDE_NEWS:"tpl_file":"tpl":"Title":"show_num"]
		if (count($this->templateTag) > 1) {
			$sql = "SELECT [nes.id] FROM [table.nes] WHERE [nes.parent]='0' ORDER BY [nes.id] ASC;";
			$res = null;
			$db->run($sql, $res);
			if ($res->getFirst()) {
				$_sec = $res->{$db->getFieldName('nes.id')};
			} else {
				$_sec = 0;
			}
			$text = new pTextEntry(null);
			$text->layout = $this->templateTag['layout'];
			$text->title = $this->templateTag['template'];
			$text->id = 0;
			$text->sort = 0;
			$text->text = $_sec;
			$text->lang = $lang->getLanguageName();
			$text->options = '#show_recursive=true##show_number='.(int)$this->templateTag['num'].'##show_title=true#';
			$_template = $this->_readTemplateFile($this->templateTag['layout']);

		// normal news-entry
		} else if (count($adminData) <= 0) {
			$text = $this->getTextEntryObject();
			$_template = $this->_readTemplateFile($text->layout);

		// Admin-Called news-entry (for the Editor for example)
		} else {
			$text = new pTextEntry(null);
			foreach ($adminData as $k => $v) {
				$text->{$k} = $v;
			}
			if (strlen(trim($text->layout)) <= 0) {
				$_template = '';
			} else {
				$_template = $this->_readTemplateFile($text->layout);
			}
		}

		$_template = str_replace('[ADMIN_FUNCTIONS]', '', $_template);
		$_template = preg_replace('/(\[TEXT_ADMIN_FUNCTIONS\])(.*?)(\[\/TEXT_ADMIN_FUNCTIONS\])/smi', '', $_template);
		$_template = str_replace('[ADMIN_REMOVE]', '', $_template);
		$_template = str_replace('[/ADMIN_REMOVE]', '', $_template);

		// Check for CSS-File, CSS-Content and SCRIPT-File
		if (strlen(trim($this->_cssFile)) > 0) {
			$this->_hasCssImportFile = true;
		}

		if (strlen(trim($this->_scriptFile)) > 0) {
			$this->_hasScriptImportFile = true;
		}

		if (strlen(trim($this->_cssContent)) > 0) {
			$this->_hasCssContent = true;
		}

		// Get the Layout
		if ( (count($adminData) <= 0) || !empty($_template) ) {
			$this->parseNewsContentTemplateFile(self::LAYOUT);
		}

		// Get content for "read more"
		$readmore = null;
		foreach ($this->_readMoreContent as $v) {
			if ($v->layout == self::LAYOUT) {
				$readmore = $v->content;
				break;
			}
		}
		if ($readmore === null) {
			$readmore = $this->_readMoreContent[0]->content;
		}

		// Check for empty template
		if (strlen($this->_newsContent) <= 0) {
			$this->_newsContent = '[TEXT]';
		}
		if (strlen($this->_newsLine) <= 0) {
			$this->_newsLine = '[TEXT]';
		}
		if (strlen($this->_newsContentLine) <= 0) {
			$this->_newsContentLine = '[NEWS_NUMBER].) [NEWS_DATE_EXTENDED]<br /><h3>[NEWS_TITLE]</h3>[NEWS_TEXT]';
		}

		// Check for a valid section
		if ( ($SectionId <= 0) && ((int)$text->text > 0) ) {
			$SectionId = (int)$text->text;
		}

		// Get Category-Title
		if (is_array($this->templateTag) && array_key_exists('title', $this->templateTag)) {
			$_title = $this->templateTag['title'];
		} else {
			$_title = $text->title;
		}

		// Check for Recursive-Newsview  (show_recursive=Yes)
		$_opt = $text->options;
		if (substr_count($_opt, "#show_recursive=true#") > 0) {
			$_recursive = true;
		} else {
			$_recursive = false;
		}

		// Check for "number of news"
		$_maxNews = 0;
		if (substr_count($_opt, "#show_number=") > 0) {
			$_maxNews = (int)preg_replace("/^(.*?)(#show_number=)(.*?)(#)(.*)$/smi", "\\3",  $_opt);
		}

		// Get all News from Section
		//$_news = array();
		//$this->_getNewsDetails($SectionId, $_news, true, $_recursive);

		// Get all News
		$sectionList = array($SectionId);
		if ($_recursive) {
			$sectionList = array_merge($sectionList, $this->getChildSectionList($SectionId, 'nes', 'id', 'parent'));
			$sectionList = array_unique($sectionList);
		}
		$_news = $this->getFromSections($sectionList, $lang->getShortLanguageName(), $_maxNews > 0 ? $_maxNews : null);

		// Show each news
		if (count($_news) > 0) {
			// Create the Content
			$_text = $this->_newsLine;
			$_countNews = 0;
			foreach ($_news as &$news) {
				if (!empty($_maxNews) && ($_countNews > $_maxNews)) {
					break;
				}
				$_countNews += 1;
				$_tmp = $this->_newsContentLine;

				// Replace Variables
				$_tmp = str_replace("[NEWS_NUMBER]",        $_countNews, $_tmp);
				$_tmp = str_replace("[NEWS_TITLE]",         $news->title, $_tmp);
				$_tmp = str_replace("[NEWS_DATE]",          $news->date,  $_tmp);
				$_tmp = str_replace("[NEWS_DATE_SHORT]",    $news->date_short, $_tmp);
				$_tmp = str_replace("[NEWS_DATE_EXTENDED]", $news->date_extended,   $_tmp);
				if (strlen(trim(strip_tags($news->short))) <= 0) {
					$_tmp = str_replace("[NEWS_SHORT]",         $news->text,  $_tmp);
				}
				$_tmp = str_replace("[NEWS_SHORT]",         $news->short,  $_tmp);
				if (preg_match("/(\[NEWS_TEXT:)(.*?)(\])/smi", $_tmp, $match)) {
					$news->text = strip_tags($news->text);
					$newstxt = $this->ReplaceTextVariables(substr($news->text, 0, (integer)$match[2]) );
					$newstxt = $newstxt.$readmore;
					$_tmp = str_replace($match[0], $newstxt, $_tmp);
				} else {
					$_tmp = str_replace("[NEWS_TEXT]", $this->ReplaceTextVariables($news->text),  $_tmp);
				}

				// Check (and replace) for a Complete-News link
				if (substr_count($_tmp, "[NEWS_COMPLETE_LINK]") > 0) {
					$_tmp = str_replace("[NEWS_COMPLETE_LINK]", "/".$lang->getShortLanguageName()."/news/".$news->id."/sec=".$news->section, $_tmp);
				}

				if (substr_count($_text, "[TEXT]") <= 0) {
					$_text .= $this->_newsLine;
				}
				$_text = preg_replace("/(\[TEXT\])/smi", $_tmp, $_text, 1);
			}
		} else { // Create an empty News-Entry if there is no News available in this Section...
			$_text = $this->_newsLine;
		}
		$_text = str_replace("[TEXT]", "&nbsp;", $_text);
		$_text = str_replace("[TEXT]", $_text, $this->_newsContent);

		if ( (count($adminData) <= 0) || (strlen(trim($_template)) > 0) ) {
			// Check for a required SectionList
			if (substr_count($text->options, "#show_section=true#") >= 1) {
				$_sectionContent = $this->createSectionContentList((integer)$text->text);
			} else {
				$_sectionContent = '';
			}

			// Check if the Title should be printed or not
			if (substr_count($text->options, "#show_title=true#") <= 0) {
				$_title = "";
			}

			$html = $_template;
			if (!$onlyContent) {
				if ($_title == 'default') $_title = $this->_getSectionName($SectionId, 'nes');
				// Replace [TITLE] or strip out the CAT_TITLE...
				if ( !empty($_title) || ( $userCheck->checkAccess('content') && (count($this->templateTag) <= 0)) ) {
					$html = str_replace('[TITLE]', $_title, $html);

				} else {
					$html = str_replace('[TITLE]', '', $html);
					if (substr_count($text->options, '#show_title=false#') > 0) {
						$html = preg_replace('/(\[CAT_TITLE\])(.*?)(\[\/CAT_TITLE\])/smi', '', $html);
						$html = preg_replace('/(\[CUT_TITLE\])(.*?)(\[\/CUT_TITLE\])/smi', '', $html);
					}
				}
			} else {
				$html = str_replace('[TITLE]', '', $html);
				$html = preg_replace('/(\[CAT_TITLE\])(.*?)(\[\/CAT_TITLE\])/smi', '', $html);
				$html = preg_replace('/(\[CUT_TITLE\])(.*?)(\[\/CUT_TITLE\])/smi', '', $html);
			}
			$html = str_replace('[CAT_TITLE]', '', str_replace('[/CAT_TITLE]', '', $html));
			$html = str_replace('[CUT_TITLE]', '', str_replace('[/CUT_TITLE]', '', $html));

			// Replace [TEXT] or strip out the CAT_CONTENT...
			if ( (strlen(trim($_text)) > 0) || ($userCheck->checkAccess('content')) ) {
				if (!$onlyContent) {
					$this->appendTextAdminAddons($_text, $text, $text->id);
				}

				$html = str_replace("[TEXT]",  $_text,  $html);
				$html = str_replace("[CAT_CONTENT]", "", str_replace("[/CAT_CONTENT]", "", $html));
			} else {
				$html = str_replace("[TEXT]",  "",  $html);
				$html = preg_replace("/(\[CAT_CONTENT\])(.*?)(\[\/CAT_CONTENT\])/smi", "", $html);
			}

			// Append the Section-List, or cut them out
			if (strlen(trim($_sectionContent)) > 0) {
				$html = preg_replace("/\[CUT_SECTION\]/smi", "", $html);
				$html = preg_replace("/\[\/CUT_SECTION\]/smi", "", $html);
				$html = preg_replace("/\[SECTION_LIST\]/smi", $_sectionContent, $html);
			} else {
				$html = preg_replace("/(\[CUT_SECTION\])(.*?)(\[\/CUT_SECTION\])/smi", "", $html);
			}

			// Replace Text-Options
			$html = $this->ReplaceLayoutOptions($html, $text->options);
		} else {
			$html = $_text;
		}

		return $html;
	}

	/**
	 * Get all Child-Sections from $section
	 *
	 * @param integer $section SectionID to get all Childs from
	 */
	/*public function getChildSectionList($section) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$sql = 'SELECT [nes.id] FROM [table.nes] WHERE [nes.parent]='.(int)$section;
		$res = null;
		$db->run($sql, $res);
		$back = array();
		while ($res->getNext()) {
			$sid = $res->{$db->getFieldName('nes.id')};
			$back[] = $sid;
			$back = array_merge($back, $this->getChildSectionList($sid));
		}
		return $back;
	}*/

	/**
	 * Get an Array with all or $num News from given Sections ordered ASC by Date
	 *
	 * @param array $sectionList Integer-List with all Sections
	 * @param string $shortLanguage Language-Name (2-CharName)
	 * @param integer $num optional number of nes to return
	 * @return array List of stdClass
	 * @access public
	 */
	public function getFromSections(array $sectionList, $shortLanguage='de', $num=null) {
		$lang = pMessages::getLanguageInstance($shortLanguage);
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$back = array();
		$hasAtomNews = false;

		$where = 'AND ([new.section]='.implode(' OR [new.section]=', $sectionList).')';
		$limit = (is_null($num) ? '' : ' LIMIT '.(int)$num);
		$sql = 'SELECT [new.id] FROM [table.new] WHERE [new.lang]='.$lang->getLanguageId().' '.$where.' ORDER BY [new.date] DESC'.$limit.';';
		$db->run($sql, $res);
		if ($res->getFirst()) {
			while ($res->getNext()) {
				$n = new pNewsEntry($res->{$db->getFieldName('new.id')});
				if (!$n->rss) {
					$back[] = $n;
				} else {
					$back = array_merge($back, $n->feed_array);
					$hasAtomNews = true;
				}
			}
		}

		if ($hasAtomNews) {
			$fn = create_function('$a,$b', 'if ($a->timestamp == $b->timestamp) { return 0; } return ($a->timestamp < $b->timestamp) ? 1 : -1;');
			usort($back, $fn);
			if (!is_null($num)) {
				$list = array();
				$cnt = 0;
				foreach ($back as $news) {
					if ($cnt++ >= $num) {
						break;
					}
					$list[] = $news;
					$back = $list;
				}
			}
		}

		return $back;
	}

	private function getRSSNews(pNewsEntry  $news) {
		die('DEPRECATED: Use a pNewsEntry Object');
		if (property_exists($news, 'rss') && $news->rss) {
			$db = pDatabaseConnection::getDatabaseInstance();
			$res = null;

			foreach ($news->feed_list->feeds as $feed) {
				$sql = 'SELECT * FROM [table.rssnews] WHERE [rssnews.uri]=\''.mysql_real_escape_string($feed).'\';';
				$db->run($sql, $res);
				$fid = 0;
				$last = 0;
				if ($res->getFirst()) {
					$fid = (int)$res->{$db->getFieldName('rssnews.id')};
					$last = (int)$res->{$db->getFieldName('rssnews.last')};
				}
				$res = null;
				if ($last <= ( time() - $news->feed_list->max_cache_age) ) {
					$feedReader = new pFeedReader($feed);
					if ($feedReader->parse()) {
						// Upgrade the rssNews-Table
						if ($fid > 0) {
							$sql = 'UPDATE [table.rssnews] SET [field.rssnews.last]='.time().' WHERE [field.rssnews.id]='.$fid.';';
							$db->run($sql, $res);
						} else {
							$last = time();
							$sql = 'INSERT INTO [table.rssnews] ([field.rssnews.uri],[field.rssnews.last]) VALUES (\''.mysql_real_escape_string($feed).'\','.$last.');';
							$db->run($sql, $res);
							$fid = $res->getInsertId();
						}
						$res = null;

						// Add all news received from the feed
						foreach ($feedReader as $f) {
							// Check if this News is not already grabbed
							$sql = 'SELECT [rsscache.date] FROM [table.rsscache] WHERE [rsscache.uid]=\''.mysql_real_escape_string($f->id).'\' AND [rsscache.rssnews]='.$fid.';';
							$db->run($sql, $res);
							$feeddate = $f->updated > 0 ? $f->updated : $f->published;
							if ($res->getFirst()) {
								$date = (int)$res->{$db->getFieldName('rsscache.date')};
								if ($feeddate > $date) {
									$sql = 'UPDATE [table.rsscache] SET [field.rsscache.date]='.$feeddate.', [field.rsscache.title]=\''.mysql_real_escape_string($f->title).'\', [field.rsscache.text]=\''.mysql_real_escape_string($f->content).'\' WHERE [field.rsscache.uid]=\''.mysql_real_escape_string($f->id).'\' AND [field.rsscache.rssnews]='.$fid.';';
									$db->run($sql, $res);
								}

							} else {
								$sql = 'INSERT INTO [table.rsscache] ([field.rsscache.rssnews],[field.rsscache.date],[field.rsscache.title],[field.rsscache.text],[field.rsscache.uid]) VALUES ('.$fid.','.$feeddate.',\''.mysql_real_escape_string($f->title).'\',\''.mysql_real_escape_string($f->content).'\',\''.mysql_real_escape_string($f->id).'\');';
								$db->run($sql, $res);
							}
							$res = null;

						}
					}
				}

				// Get the RSS-News
				$sql = 'SELECT [rsscache.date] FROM [table.rsscache] WHERE [rsscache.uid]=\''.mysql_real_escape_string($f->id).'\' AND [rsscache.rssnews]='.$fid.';';
			}
		}
	}

	/**
	 * Check for a given [TEMPLATE_FILE]...[/TEMPLATE_FILE] Tag inside the News-Content
	 *
	 * @return string The Template-File to user or false if there is none defined
	 */
	private function checkTemplateFile() {
		$match = null;
		if (preg_match("/(\[TEMPLATE_FILE\])(.*?)(\[\/TEMPLATE_FILE\])/smi", $this->_newsContent, $match)) {
			$back = $match[2];
			$this->_newsContent = str_replace($match[0], '', $this->_newsContent);
			$match = null;
			return $back;
		}
		return false;
	}

	/**
	 * Show a site with news
	 *
	 * @return string HTML-Source
	 * @access private
	 */
	private function getCompleteNewsSource() {
		$menu = pMenu::getMenuInstance();
		$lang = pMessages::getLanguageInstance();
		$userCheck = pCheckUserData::getInstance();
		$adminAction = pURIParameters::get('adm', 0, pURIParameters::$INT);

		$this->parseNewsContentTemplateFile(self::FULLSCREEN_LAYOUT);
		$_tplFile = $this->checkTemplateFile();
		$_template = '[TEXT]';
		if (!empty($_tplFile)) {
			$_template = $this->_readTemplateFile($_tplFile);
		}

		// Check for CSS-File, CSS-Content and SCRIPT-File
		if ( (strlen(trim($this->_cssFile)) > 0) || (strlen(trim($this->_specialCssFile)) > 0) ) {
			$this->_hasCssImportFile = true;
		}

		if ( (strlen(trim($this->_scriptFile)) > 0) || (strlen(trim($this->_specialScriptFile)) > 0) ) {
			$this->_hasScriptImportFile = true;
		}

		if ( (strlen(trim($this->_cssContent)) > 0) || (strlen(trim($this->_specialCssContent)) > 0) ) {
			$this->_hasCssContent = true;
		}

		// Get content for "read more"
		$readmore = null;
		foreach ($this->_readMoreContent as $v) {
			if ($v->layout == 'show_complete') {
				$readmore = $v->content;
				break;
			}
		}
		if ($readmore == null) {
			$this->_readMoreContent[0]->content;
		}

		// Get all News from Section
		$_news = new pNewsEntry($menu->getMenuId(), $menu->getMenuIdPart());
		//$this->_getNewsDetails($menu->getMenuId(), $_news, false, false);

		// Create the Content
		$_text = '';
		$_tmp  = $this->_newsContentLine;

		// Check if we should replace NEWS_TITLE or NEWS_DATE_* in News-Template or on the Text-Template
		$_title = '';
		if (substr_count($_tmp, '[NEWS_TITLE]') <= 0) {
			$_title = $_news->title;
		}
		if (substr_count($_tmp, '[NEWS_DATE') <= 0) {
			if (strlen($_title) > 0) {
				$_title .= ' - ';
			}
			$_title .= $_news->date_short;
		}

		// Replace Variables
		$match = array();
		$_tmp = str_replace("[NEWS_NUMBER]",        1, $_tmp);
		$_tmp = str_replace("[NEWS_TITLE]",         $_news->title, $_tmp);
		$_tmp = str_replace("[NEWS_DATE]",          $_news->date,  $_tmp);
		$_tmp = str_replace("[NEWS_DATE_SHORT]",    $_news->date_short, $_tmp);
		$_tmp = str_replace("[NEWS_DATE_EXTENDED]", $_news->date_extended,   $_tmp);
		$_tmp = str_replace("[NEWS_SHORT]",         $_news->short,  $_tmp);
		if (preg_match('/(\[NEWS_TEXT:)(.*?)(\])/smi', $_tmp, $match)) {
			$newstxt = substr(strip_tags($_news->text), 0, (int)$match[2]).$readmore;
			$_tmp = str_replace($match[0], $newstxt, $_tmp);
		} else {
			$_tmp = str_replace("[NEWS_TEXT]", $_news->text,  $_tmp);
		}

		// Check (and replace) for a Complete-News link
		if (substr_count($_tmp, "[NEWS_COMPLETE_LINK]") > 0) {
			$_tmp = str_replace('[NEWS_COMPLETE_LINK]', '/'.$lang->short.'/news/'.$_news->id.'/sec='.$menu->getMenuId(), $_tmp);
		}

		// Add the File to a _newsLine
		$_cont = str_replace("[TEXT]", $_tmp, $this->_newsLine);
		$_text = str_replace("[TEXT]", "", $_cont);
		$_text = str_replace("[TEXT]", $_text, $this->_newsContent);
		$html = $_template;

		// Replace [TEXT] or strip out the CAT_CONTENT...
		if (!empty($_text)) {
			$html = str_replace("[TEXT]",  $_text,  $html);
			$html = str_replace("[CAT_CONTENT]", "", str_replace("[/CAT_CONTENT]", "", $html));
		} else {
			$html = str_replace("[TEXT]",  "",  $html);
			$html = preg_replace("/(\[CAT_CONTENT\])(.*?)(\[\/CAT_CONTENT\])/smi", "", $html);
		}

		// Replace [TITLE] or strip out the CAT_TITLE...
		if (empty($_title)) {
			$_title = $_news->title;
		}
		if (empty($_title)) {
			$_title = $_news->name;
		}
		if (!empty($_title)) {
			$html = str_replace("[TITLE]", $_title, $html);
			$html = str_replace("[CAT_TITLE]", "", str_replace("[/CAT_TITLE]", "", $html));
		} else {
			$html = str_replace("[TITLE]", "", $html);
			$html = preg_replace("/(\[CAT_TITLE\])(.*?)(\[\/CAT_TITLE\])/smi", "", $html);
		}

		// Replace Text-Options
		$html = $this->ReplaceLayoutOptions($html, "#title=default#");

		return $html;
	}

	/**
	 * Create and Return the Section-List based on the template (only if it is required by this entry)
	 *
	 * @param integer $id SectionID
	 * @return unknown
	 */
	function createSectionContentList($id) {
		$SectionId = pURIParameters::get('sec', 0, pURIParameters::$INT);
		$selected = $SectionId;

		if ($SectionId <= 0) {
			$selected =  $this->getSelectedSectionStructure($id, 'ims', 'parent', 'id');
		} else {
			$selected =  $this->getSelectedSectionStructure($SectionId, 'ims', 'parent', 'id');
		}

		if (preg_match("/(\[SECTION\])(.*?)(\[\/SECTION\])/smi", $this->_sectionContent, $match)) {
			$_section = $match[2];

			// in newer versions, you can use [SECTION_CONTENT] to build more complex sectionlists
			if (substr_count($match[0], "[SECTION_CONTENT]") > 0) {
				$html = str_replace($match[0], "", $this->_sectionContent);
				$html = str_replace("[SELECTED_SECTION_NAME]", $this->_getNewsSectionName($selected[0]), $html);
				$html = str_replace("[SECTION_CONTENT]", $this->_sub_createSectionContentList($id, "", $selected, $_section), $html);
			} else {
				// used for Old-Style-Sections
				$html = str_replace($match[0], "[SECTION]", $this->_sectionContent);
				$html = str_replace("[SELECTED_SECTION_NAME]", $this->_getNewsSectionName($selected[0]), $html);
				$html = str_replace("[SECTION]", $this->_sub_createSectionContentList($id, "", $selected, $_section), $html);
			}
		} else {
			$html = '<span style="color:rgb(250,100,100);font-weight:bold;">Template-failure in <strong>IMAGE_SECTION</strong></span>';
		}
		return $html;
	}

	/**
	 * Create a SectionList with Template-Design
	 *
	 * this function calls itselfs recursively until lasts section is arrived
	 *
	 * @param int $id DB-id of root-section
	 * @param string $before Content to insert before the section [*_SECTION_DESIMITER]
	 * @param int $selected DB-id of selected Section
	 * @param string $template Section-Template
	 * @return String Content of current Section
	 */
	private function _sub_createSectionContentList($id, $before, $selected, $template) {
		global $langshort, $MainMenu;
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$html = '';
		$match = null;

		// Check for SUBSECTION tags in $template - for more complex section-design
		$templateOrig = $template;
		if (preg_match("/(\[SUBSECTION\])(.*?)(\[\/SUBSECTION\])/smi", $template, $match)) {
			$subsection = $match[2];
			$template = str_replace($match[0], $match[2], $template);
		} else {
			$subsection = $template;
		}

		// if $before is empty, this indicates that this is the root-node
		if (strlen(trim($before)) <= 0) {
			$sql = "SELECT * FROM [table.nes] WHERE [nes.id]=".(int)$id.";";
			$db->run($sql, $res);
			if ($res->getFirst()) {

				// Get the Number of available Programs in this Section
				$_count = $this->_getNumNewsFromSection($res->{$db->getFieldName('nes.id')});

				// Check if this section is selected
				if (in_array((integer)$id, $selected)) {
					$html = preg_replace("/(\[IF_SELECTED:')(.*?)(':')(.*?)('\])/smi", "\\2", $template);
				} else {
					$html = preg_replace("/(\[IF_SELECTED:')(.*?)(':')(.*?)('\])/smi", "\\4", $template);
				}
				// Replace real selected
				if ((int)$id == (int)$selected[0]) {
					$html = preg_replace("/(\[IF_SELECTED_ID:')(.*?)(':')(.*?)('\])/smi", "\\2", $html);
				} else {
					$html = preg_replace("/(\[IF_SELECTED_ID:')(.*?)(':')(.*?)('\])/smi", "\\4", $html);
				}

				// replace other tags
				$html = str_replace("[NUMBER_OF_NEWS]",     $_count, $html);
				$html = str_replace("[SECTION_DELIMITER]",  "", $html);
				$html = str_replace("[SECTION_NAME]",       $res->{$db->getFieldName('nes.text')}, $html);
				$html = str_replace("[SECTION_NUMBER]",     $id, $html);
				$html = str_replace("[SECTION_ID]",         $id, $html);
				$html = str_replace("[PARENT_SECTION_ID]",  0, $html);
				if (strlen(trim($this->_specialLinkDirection)) <= 0) {
					$html = str_replace("[SECTION_LINK]", "/".$langshort."/".$MainMenu."/sec=".$res->{$db->getFieldName('nes.id')}, $html);
				} else {
					$html = str_replace("[SECTION_LINK]", "/".$langshort."/".$this->_specialLinkDirection."/0/sec=".$res->{$db->getFieldName('nes.id')}, $html);
				}
				$html = str_replace("[SECTION_LINK]", "/".$langshort."/".$MainMenu."/sec=".$res->{$db->getFieldName('nes.id')}, $html);
			}
			$res = null;
		}

		// Get Parent-Sections all Child-Sections
		$sql = "SELECT * FROM [table.nes] WHERE [nes.parent]=".(int)$id.";";
		$db->run($sql, $res);
		if ($res->getFirst()) {
			$cnt = 1;
			while ($res->getNext()) {
				// Create current delimiter
				if ($res->hasNext()) {
					$_delim = $before.$this->_sectionDelimiter['entry'];
					$_delim_child = $before.$this->_sectionDelimiter['down'];
				} else {
					$_delim = $before.$this->_sectionDelimiter['last'];
					$_delim_child = $before.$this->_sectionDelimiter['clean'];
				}

				// Get the Number of available Programs in this Section
				$_count = $this->_getNumNewsFromSection($res->{$db->getFieldName('nes.id')});

				// if we can locate SECTION_CONTENT in $subsection, we replace this with $template
				// and insert the tag SECTION_SECTION after so we know where wo insert the next section on this level
				$tmp = $template;
				if (substr_count($tmp, "[SECTION_CONTENT]") <= 0) {
					$tmp .= "[SECTION_CONTENT]";
				}

				// Check if this Section is selected
				if (in_array((int)$res->{$db->getFieldName('nes.id')}, $selected)) {
					$tmp = preg_replace("/(\[IF_SELECTED:')(.*?)(':')(.*?)('\])/smi", "\\2", $template);
				} else {
					$tmp = preg_replace("/(\[IF_SELECTED:')(.*?)(':')(.*?)('\])/smi", "\\4", $template);
				}
				// Replace real selected
				if ((int)$res->{$db->getFieldName('nes.id')} == (int)$selected[0]) {
					$tmp = preg_replace("/(\[IF_SELECTED_ID:')(.*?)(':')(.*?)('\])/smi", "\\2", $tmp);
				} else {
					$tmp = preg_replace("/(\[IF_SELECTED_ID:')(.*?)(':')(.*?)('\])/smi", "\\4", $tmp);
				}

				// replace all other tags
				$tmp = str_replace("[NUMBER_OF_NEWS]",     $_count, $tmp);
				$tmp = str_replace("[SECTION_DELIMITER]",  $_delim, $tmp);
				$tmp = str_replace("[SECTION_NAME]",       $res->{$db->getFieldName('nes.text')}, $tmp);
				$tmp = str_replace("[SECTION_NUMBER]",     $res->{$db->getFieldName('nes.id')}, $tmp);
				$tmp = str_replace("[SECTION_ID]",         $res->{$db->getFieldName('nes.id')}, $tmp);
				$tmp = str_replace("[PARENT_SECTION_ID]",  $id, $tmp);
				if (strlen(trim($this->_specialLinkDirection)) <= 0) {
					$tmp = str_replace("[SECTION_LINK]", "/".$langshort."/".$MainMenu."/sec=".$res->{$db->getFieldName('nes.id')}, $tmp);
				} else {
					$tmp = str_replace("[SECTION_LINK]", "/".$langshort."/".$this->_specialLinkDirection."/0/sec=".$res->{$db->getFieldName('nes.id')}, $tmp);
				}

				// get all subsections
				$sub = $this->_sub_createSectionContentList($res->{$db->getFieldName('nes.id')}, $_delim_child, $selected, $template);

				// if subsections are available, replace the image with ENTRY, otherwise with EMPTY
				if (strlen($sub) > 0) {
					if (in_array((int)$res->{$db->getFieldName('nes.id')}, $selected)) {
						$tmp = str_replace("[SECTION_IMAGE]", (array_key_exists('expanded', $this->_sectionDelimiterImages) ? $this->_sectionDelimiterImages['expanded'] : ''), $tmp);
					} else {
						$tmp = str_replace("[SECTION_IMAGE]", (array_key_exists('entry', $this->_sectionDelimiterImages) ? $this->_sectionDelimiterImages['entry'] : ''), $tmp);
					}
				} else {
					$tmp = str_replace("[SECTION_IMAGE]", (array_key_exists('empty', $this->_sectionDelimiterImages) ? $this->_sectionDelimiterImages['empty'] : ''), $tmp);
				}

				// replace the SECTION_SECTION tag with the ChildSections
				$tmp = str_replace("[SECTION_CONTENT]", $sub, $tmp);
				if (substr_count($html, "[SECTION_CONTENT]") > 0) {
					$html = str_replace("[SECTION_CONTENT]", $tmp."[SECTION_CONTENT]", $html);
				} else {
					$html .= $tmp;
				}

				// increase the counter ;-)
				$cnt++;
			}
			$html = str_replace("[SECTION_CONTENT]", "", $html);

			// replace the SECTION_IMAGE in html (replace it with ENTRY, because we have some entries if we are here)
			if (in_array((int)$id, $selected) && array_key_exists('expanded', $this->_sectionDelimiterImages)) {
				$html = str_replace("[SECTION_IMAGE]", $this->_sectionDelimiterImages['expanded'], $html);
			} else if (array_key_exists('entry', $this->_sectionDelimiterImages)) {
				$html = str_replace("[SECTION_IMAGE]", $this->_sectionDelimiterImages['entry'], $html);
			} else {
				$html = str_replace("[SECTION_IMAGE]", '', $html);
			}
		} else {
			// replace the SECTION_IMAGE in html (replace it with EMPTY, because we don't have any here
			if (array_key_exists('empty', $this->_sectionDelimiterImages)) {
				$html = str_replace("[SECTION_IMAGE]", $this->_sectionDelimiterImages['empty'], $html);
			} else {
				$html = str_replace("[SECTION_IMAGE]", '', $html);
			}
			$html = str_replace("[SECTION_CONTENT]", "", $html);
		}
		return $html;
	}

	/**
	 * Return the name of a Section
	 * if $isEntry is set and is TRUE, the Parameter $id identifies an NEWS-Entry and not a Sectin-Entry
	 *
	 * @param integer $id SectionId (or NewsId if $isEntry is TRUE)
	 * @param boolean $isEntry if TRUE, $id is a NewsId and not a SectionId
	 * @return string The SectionName
	 */
	private function _getNewsSectionName($id, $isEntry=false) {
		$db = pDatabaseConnection::getDatabaseInstance();
		if ($isEntry) {
			$sql = "SELECT [nes.text] FROM [table.new],[table.nes] WHERE [new.section]=[nes.id] AND [new.id]='".(int)$id."'";
		} else {
			$sql = "SELECT [nes.text] FROM [table.nes] WHERE [nes.id]=".(int)$id;
		}
		$res = null;
		$db->run($sql, $res);
		if ($res->getFirst()) {
			return $res->{$db->getFieldName('nes.text')};
		} else {
			return "Unbenannter Bereich";
		}
	}

	/**
	 * Get all Details from one (ore more) news-entries
	 *
	 * @param integer $id NewsID - or SectionId if $isSection is TRUE
	 * @param boolean $isSection Set to TRUE if $id is a SectionID and not a NewsID
	 * @param boolean $recursive Set to TRUE to get all News also from SubSections and not only from the current
	 * @param array &$newsList Pointer to the News-List to fill
	 */
	private function _getNewsDetails($id, &$newsList, $isSection=false, $recursive=false) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$lang = pMessages::getLanguageInstance();

		if ($isSection) {
			$_secList = "[new.section]=".(int)$id;
			// Get all Sections which are SubSections from submitted SectionID
			if ($recursive) {
				$_secList .= $this->_getRecursiveNewsIDList((int)$id, "[new.section]=[REC_SEC_ID]", " OR ");
				$_secList = '('.$_secList.')';
			}

			// Get all news from SectionList
			$sql = "SELECT [new.id] FROM [table.new] WHERE ".$_secList." AND [new.lang]= '".$lang->getLanguageId()."' ORDER BY [new.date] DESC";

		} else {
			// Get the News with submitted ID
			$sql = "SELECT [new.id] FROM [table.new] WHERE [new.id]=".(int)$id."";
		}
		$res = null;
		$db->run($sql, $res);
		$newsList = array();
		if ($res->getFirst()) {
			while ($res->getNext()) {
				/*$tmp = new pTextEntry(null);
				$tmp->id         = $res->{$db->getFieldName('new.id')};
				$tmp->section    = $res->{$db->getFieldName('new.section')};
				$tmp->title      = $res->{$db->getFieldName('new.title')};
				$tmp->text       = $res->{$db->getFieldName('new.text')};
				$tmp->date       = strtotime($res->{$db->getFieldName('new.date')});
				$tmp->date_short = $this->formatDate("Y-m-d H:i",       $tmp->date);
				$tmp->date_ext   = $this->formatDate("l, j. M. Y, H:i", $tmp->date);
				$newsList[] = $tmp;*/
				$newsList[] = new pNewsEntry($res->{$db->getFieldName('new.id')});
			}
		}
		if (!$isSection) {
			$newsList = array_pop($newsList);
		}
	}

	/**
	 * Create a List with all SectionID's where the Section with $id is in
	 * finally there is a List like "$separator $idValuePart" which is repeated
	 * until there are no parent-Sections
	 *
	 * @param integer $id SectionID to get the ParentSections from
	 * @param string $idValuePart A String where [REC_SEC_ID] will be replaced with the SectionID
	 * @param string $separator Separator to seperate all SectionID's
	 * @return string full string with all SectionID's
	 */
	private function _getRecursiveNewsIDList($id, $idValuePart, $separator) {
		$back = '';
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = "SELECT [nes.id] AS sectionid FROM [table.nes] WHERE [nes.parent]=".(int)$id;
		$db->run($sql, $res);
		if ($res->getFirst()) {
			while ($res->getNext()) {
				$back .= $separator.str_replace("[REC_SEC_ID]", (int)$res->sectionid, $idValuePart);
				$back .= $this->_getRecursiveNewsIDList((int)$res->sectionid, $idValuePart, $separator);
			}
		}
		return $back;
	}

	/**
	 * Return the Number of news in a Section
	 *
	 * @param integer $sectionId Section to get the num of news from
	 * @return integer Number of News in this Section
	 */
	function _getNumNewsFromSection($sectionId) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = "SELECT COUNT([new.id]) AS num FROM [table.new] WHERE [new.section]=".(int)$sectionId;
		$db->run($sql, $res);
		if ($res->getFirst()) {
			$back = $res->num;
		} else {
			$back = 0;
		}
		return $back;
	}

	// Reads the Contents-File for Screenshots
	private function parseNewsContentTemplateFile($layout) {
		// Create the default News-Content "look"
		$this->_newsContent = '[NEWS_TITLE]<br />[NEWS_TEXT]<br />[NEWS_DATE]<br />[NEWS_DATE_SHORT]<br />[NEWS_DATE_EXTENDED]';
		$match = null;
		$templateContent = '';

		// Read the global News-Template
		if (is_file($this->contentFile) && is_readable($this->contentFile)) {
			$templateContent = file_get_contents($this->contentFile);

			// get [SCRIPT_INCLUDE]...[/SCRIPT_INCLUDE]
			$this->_specialScriptFile = $this->getSimpleTagFromContent('SCRIPT_INCLUDE', $templateContent);

			// get [STYLE_INCLUDE]...[/STYLE_INCLUDE]
			$this->_specialCssFile = $this->getSimpleTagFromContent('STYLE_INCLUDE', $templateContent);

			// get [STYLE_CONTENT]...[/STYLE_CONTENT]
			$this->_specialCssContent = $this->getSimpleTagFromContent('STYLE_CONTENT', $templateContent);

			// get [NEWS_READ_MORE]...[/NEWS_READ_MORE]
			$this->_readMoreContent[0] = new stdClass();
			$this->_readMoreContent[0]->layout = "default";
			$this->_readMoreContent[0]->content = $this->getSimpleTagFromContent('NEWS_READ_MORE', $templateContent);
			if (preg_match_all('/(\[NEWS_READ_MORE:)([^\]]+)(\])(.*?)(\[\/NEWS_READ_MORE\])/smi', $templateContent, $match, PREG_SET_ORDER)) {
				for ($i = 0; $i < count($match); $i++) {
					$this->_readMoreContent[$i+1] = new stdClass();
					$this->_readMoreContent[$i+1]->layout = $match[$i][2];
					$this->_readMoreContent[$i+1]->content = $match[$i][4];
				}
			}

			// get [NEWS_SECTION]...[/NEWS_SECTION]
			$this->_sectionContent = $this->getSimpleTagFromContent('NEWS_SECTION', $templateContent);

			// get [NEWS_ENTRY]...[/NEWS_ENTRY]
			$this->_newsContentLine = $this->getSimpleTagFromContent('NEWS_ENTRY', $templateContent, $layout);

			// get [NEWS_LINE]...[/NEWS_LINE]
			$this->_newsLine = $this->getSimpleTagFromContent('NEWS_LINE', $templateContent, $layout);

			// Check for [OPTIONS]...[/OPTIONS]
			$this->_contentOptions = $this->_parseOptionsTags($templateContent, $this->_contentOptions);

			// get content from [LAYOUT:$layout]...[/LAYOUT]
			if (preg_match_all('/(\[LAYOUT)((:'.$layout.')?)(\])(.*?)(\[\/LAYOUT\])/smi', $templateContent, $match)) {
				for ($i = 0; $i < count($match[0]); $i++) {
					if ( ($match[3][$i] == ":".$layout) || ((trim($layout) == "default") && (strlen(trim($match[3][$i])) <= 0)) ) {
						$this->_newsContent = $match[5][$i];
					}
				}
			}

			// Check for a Section-Delimiter and read out all (CLEAN,DOWN,ENTRY,LAST)
			$sdelim = $this->getSimpleTagFromContent('NEWS_SECTION_DELIMITER', $templateContent);
			if (preg_match_all('/(\[)(CLEAN|DOWN|ENTRY|LAST)(\])(.*?)(\[\/\\2\])/smi', $sdelim, $match)) {
				$this->_sectionDelimiter = array();
				for ($i = 0; $i < count($match[0]); $i++) {
					$this->_sectionDelimiter[strToLower($match[2][$i])] = $match[4][$i];
				}
			}

			// Check for Section-Delimiter-Images and read out all (CLEAN,DOWN,ENTRY,LAST)
			$sdelim = $this->getSimpleTagFromContent('SECTION_DELIMITER_IMAGE', $templateContent);
			if (preg_match_all('/(\[)(CLEAN|DOWN|ENTRY|LAST)(\])(.*?)(\[\/\\2\])/smi', $sdelim, $match)) {
				$this->_sectionDelimiterImages = array();
				for ($i = 0; $i < count($match[0]); $i++) {
					$this->_sectionDelimiterImages[strToLower($match[2][$i])] = $match[4][$i];
				}
			}

			unset($templateContent);
		}
	}

	/**
	 * Check Database integrity
	 *
	 * This function creates all required Tables, Updates, Inserts and Deletes.
	 */
	function _checkDatabase() {
		$db = pDatabaseConnection::getDatabaseInstance();
		// Get the current Version
		$v = $this->_checkMainDatabase();
		$version = $v[0];
		$versionid = $v[1];

		// Updates to the Database
		if ($version < 2006010600) {
			// Create the News-Table
			$sql  = "CREATE TABLE IF NOT EXISTS [table.new] (".
			" [field.new.id] INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,".
			" [field.new.section] INT(10) UNSIGNED NOT NULL DEFAULT 0,".
			" [field.new.title] VARCHAR(100) NOT NULL DEFAULT '',".
			" [field.new.text] TEXT NOT NULL DEFAULT '',".
			" [field.new.html] INT(1) UNSIGNED NOT NULL DEFAULT 0,".
			" [field.new.date] DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',".
			" [field.new.lang] INT(10) UNSIGNED NOT NULL DEFAULT 0,".
			" PRIMARY KEY ([field.new.id]),".
			" UNIQUE KEY [field.new.id] ([field.new.id])".
			" );";
			$res = null;
			$db->run($sql, $res);
		}

		if ($version < 2006010601) {
			// Create the NewsSection-Table
			$sql  = "CREATE TABLE IF NOT EXISTS [table.nes] (".
			" [field.nes.id] INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,".
			" [field.nes.parent] INT(10) UNSIGNED NOT NULL DEFAULT 0,".
			" [field.nes.text] VARCHAR(100) NOT NULL DEFAULT '',".
			" PRIMARY KEY ([field.nes.id]),".
			" UNIQUE KEY [field.nes.id] ([field.nes.id])".
			" );";
			$res = null;
			$db->run($sql, $res);

			// Insert base-Section if not already exists
			$sql = "SELECT [nes.text] FROM [table.nes] WHERE [nes.text]='default'";
			$res = null;
			$db->run($sql, $res);
			if (!$res->getFirst()) {
				$sql = "INSERT INTO [table.nes]".
				" ([field.nes.parent],[field.nes.text])".
				" VALUES (0,'default');";
				$res = null;
				$db->run($sql, $res);
			}
		}

		if ($version < 2009112000) {
			$sql = 'ALTER TABLE [table.new] ADD COLUMN ([field.new.rss] TINYINT(1) UNSIGNED NOT NULL DEFAULT 0);';
			$res = null;
			$db->run($sql, $res);
		}

		if ($version < 2009112300) {
			$sql = "CREATE TABLE IF NOT EXISTS [table.rssnews] (".
			" [field.rssnews.id] INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,".
			" [field.rssnews.last] INT(10) UNSIGNED NOT NULL DEFAULT 0,".
			" [field.rssnews.uri] VARCHAR(250) NOT NULL DEFAULT '',".
			" KEY [field.rssnews.uri] ([field.rssnews.uri]),".
			" PRIMARY KEY ([field.rssnews.id]),".
			" UNIQUE KEY [field.rssnews.id] ([field.rssnews.id])".
			" );";
			$res = null;
			$db->run($sql, $res);
		}

		if ($version < 2009112303) {
			$sql = "CREATE TABLE IF NOT EXISTS [table.rsscache] (".
			" [field.rsscache.rssnews] INT(10) UNSIGNED NOT NULL DEFAULT 0,".
			" [field.rsscache.date] INT(10) UNSIGNED NOT NULL DEFAULT 0,".
			" [field.rsscache.title] VARCHAR(250) NOT NULL DEFAULT '',".
			" [field.rsscache.text] TEXT NOT NULL DEFAULT '',".
			" KEY [field.rsscache.rssnews] ([field.rsscache.rssnews]),".
			" KEY [field.rsscache.date] ([field.rsscache.date])".
			" );";
			$res = null;
			$db->run($sql, $res);
		}

		if ($version < 2009112703) {
			$sql = 'ALTER TABLE [table.rsscache] ADD COLUMN ([field.rsscache.uid] VARCHAR(200) NOT NULL DEFAULT \'\');';
			$res = null;
			$db->run($sql, $res);
		}

		if ($version < 2012061500) {
			$sql = 'ALTER TABLE [table.new] ADD COLUMN ([field.new.short] TEXT NOT NULL DEFAULT \'\');';
			$res = null;
			$db->run($sql, $res);
		}

		// Update the version
		$this->_updateVersionTable($version, $versionid, self::VERSION);
	}

}

?>
