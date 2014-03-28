<?php

class SCREENSHOT extends MainPlugin {
	const VERSION = 2008121603;
	const LAYOUT = 'default';
	const PLAIN_LAYOUT = 'plainLayout';
	const FULLSCREEN_LAYOUT = 'fullscreen';

	protected $contentFile;
	protected $_mainContent;
	protected $_imageContent;
	protected $_imageCleanContent;
	protected $_imagesPerLine;
	protected $_thumbnailContent;
	protected $_thumbnailLine;
	protected $_sectionContent;
	protected $_specialLinkDirection;
	protected $_sectionDelimiterImages;
	protected $_titleText;
	protected $_thumbMaxWidth;
	protected $_thumbMaxHeight;

	public function __construct() {
		parent::__construct();
		$this->_isTextPlugin = true;

		$this->contentFile = "cont_screenshots.tpl";
		$this->_mainContent = "";
		$this->_imageContent = "";
		$this->_imageCleanContent = "";
		$this->_imagesPerLine = 3;

		$this->_specialLinkDirection = '';

		$this->_thumbnailContent = "";
		$this->_thumbnailLine = "";
		$this->_sectionContent = '';
		$this->_sectionDelimiterImages = array('','','');
		$this->_titleText = array('', '');
		$this->_thumbMaxHeight = 0;
		$this->_thumbMaxWidth = 0;

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
		$opt .= '[show_description]choose:true,false[/show_description]';
		$opt .= '[show_data]choose:true,false[/show_data]';
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
		if ($method == PLG_IMAGE_METHOD) {
			return $this->getBigImage();
		} else if ($method == PLG_IMAGEBLANKLIST_METHOD) {
			$this->_specialLinkDirection = "imageBlankList";
			return $this->getPlainThumbnailList();
		} else {
			return $this->getThumbnailList($adminData);
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
		return $this->getThumbnailList(null, true);
	}

	/**
	 * Return the OpenEditor function for ContentAdministration
	 *
	 * @param integer $id The TextID
	 * @return string
	 * @access public
	 */
	public function getEditFunction($id) {
		return 'gallery';
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

	function checkTemplateFile($reg=false) {
		$match = null;
		$back = '';
		if (preg_match("/(\[TEMPLATE_FILE\])(.*?)(\[\/TEMPLATE_FILE\])/smi", $this->_thumbnailContent, $match)) {
			$back = $match[2];
			$this->_thumbnailContent = str_replace($match[0], '', $this->_thumbnailContent);
			$match = null;
		}
		return $back;
	}

	// Return a complete Thumbnail-List (but only if the User has access to edit Text-Sites)
	function getPlainThumbnailList() {
		global $MenuId, $langid;
		$userCheck = pCheckUserData::getInstance();
		if ($userCheck->checkAccess('CONTENT')) {
			// Create a dummy-array
			$adm = array();
			$adm[$this->DB->FieldOnly('txt','text')] = 0;
			$adm[$this->DB->FieldOnly('txt','title')] = '';
			$adm[$this->DB->FieldOnly('txt','options')] = '#show_section=true##show_title=false#';
			$adm[$this->DB->FieldOnly('txt','sort')] = '1';
			$adm[$this->DB->FieldOnly('txt','menu')] = $MenuId;
			$adm[$this->DB->FieldOnly('txt','lang')] = $langid;
			$adm[$this->DB->FieldOnly('txt','plugin')] = 'SCREENSHOT';
			$adm[$this->DB->FieldOnly('txt','layout')] = 'screenshots';

			return $this->getThumbnailList($adm, false, true);
		} else {
			return "no access";
		}
	}

	/**
	 * Return the complete HTML based on the template for a List of all Images in a Section
	 * @param $adminData
	 * @return unknown_type
	 */
	private function getThumbnailList($adminData=array(), $onlyContent=false, $plainLayout=false) {
		$lang = pMessages::getLanguageInstance();
		$menu = pMenu::getMenuInstance();
		$SectionId = pURIParameters::get('sec', 0 ,pURIParameters::$INT);
		$adminAction = pURIParameters::get('adm', 0 ,pURIParameters::$INT);
		$userCheck = pCheckUserData::getInstance();

		if (count($adminData) > 5) {
			$text = $this->getTextEntryObject();
			foreach ($adminData as $k => $v) {
				$text->{$k} = $v;
			}
		} else {
			$text = $this->getTextEntryObject();
		}

		$_template = trim($this->_readTemplateFile($text->layout));
		$_template = str_replace("[ADMIN_FUNCTIONS]", "", $_template);
		$_template = preg_replace("/(\[TEXT_ADMIN_FUNCTIONS\])(.*?)(\[\/TEXT_ADMIN_FUNCTIONS\])/smi", "", $_template);
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

		// Read the ContentFile (cont_downloads.tpl)
		if ( empty($adminData) || !empty($_template) ) {
			if ($plainLayout) {
				$this->_readContentFile(self::PLAIN_LAYOUT);
			} else {
				$this->_readContentFile(self::LAYOUT);
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
		}

		// Check if the images should be readed recursively or not
		if (substr_count($text->options, '#show_recursive=true#') >= 1) {
			$_recursive = true;
		} else {
			$_recursive = false;
		}

		// Get all Images from Section
		if ( ($SectionId <= 0) && ((int)$text->text > 0) ) {
			$SectionId = (int)$text->text;
		}

		// Get all Images from Section and the title
		$_images = $this->_getImagesFromSection($SectionId, false, $_recursive);

		// Insert an ID-Field into the Title-Field
		$_titleBefore = substr($_template, 0, strpos($_template, '[TITLE]'));
		$_titleAfter  = substr($_template, strpos($_template, '[TITLE]'));
		$_template = $_titleBefore.'<span id="title_'.$text->id.'">'.$_titleAfter.'</span>';

		// Create the Content
		$match = array();
		$_cnt = 0;
		$_text = '';
		$_cont = $this->_thumbnailLine;
		foreach ($_images as $image) {
			$_cnt++;
			$_tmp = $this->_imageContent;

			// Get width, height and ShowTitle from [IMAGE_SRC:WxH:BoolShowTitle]
			$match = array();
			if (preg_match('/\[IMAGE_SRC:(\d+)x(\d+)(:(true|false))?\]/smi', $_tmp, $match)) {
				$image->setSize($match[1], $match[2]);
				$image->setShowTitle($match[4]=='true' ? true : false);
				$_tmp = str_replace($match[0], '[IMAGE_SRC]', $_tmp);
			}

			// Show the SWF-Image if it's an Flash-File
			if ( $image->mime == 'application/x-shockwave-flash' ) {
				$_tmp = str_replace('[IMAGE_SRC]', SCREENSHOT_FLASH,  $_tmp);
			} else {
				$_tmp = str_replace('[IMAGE_SRC]', $image->url,  $_tmp);
			}

			// Replace other variables
			$_tmp = str_replace('[IMAGE_NUMBER]',   $_cnt,          $_tmp);
			$_tmp = str_replace('[IMAGE_ID]',       $image->id,     $_tmp);
			$_tmp = str_replace('[IMAGE_NAME]',     $image->name,   $_tmp);
			$_tmp = str_replace('[REAL_IMAGE_SRC]', $image->realurl,    $_tmp);
			$_tmp = str_replace('[REAL_SIZE]',      $this->humanReadableFileSize($image->size), $_tmp);
			$_tmp = str_replace('[BIG_IMAGE_LINK]', '/'.$lang->getShortLanguageName().'/image/'.$image->id, $_tmp);

			// Check for Size-Tags with additional +/- size
			if (preg_match_all('/(\[)(MAX_|REAL_)?(WIDTH|HEIGHT)(:)?((\+|\-)\d+)?(\])/smi', $_tmp, $match)) {
				for ($j = 0; $j < count($match[0]); $j++) {
					$tag = $match[2][$j].$match[3][$j];
					$op  = $match[6][$j];
					$ndif = (int)str_replace($op, "", $match[5][$j]);

					switch ($tag) {
						case 'WIDTH':
							$num = (int)$image->width;
							break;
						case 'HEIGHT':
							$num = (int)$image->height;
							break;
						case 'MAX_WIDTH':
							$num = (int)$image->real_width;
							break;
						case 'MAX_HEIGHT':
							$num = (int)$image->real_height;
							break;
						case 'REAL_WIDTH':
							$num = (int)$image->real_width;
							break;
						case 'REAL_HEIGHT':
							$num = (int)$image->real_height;
							break;
					}

					if ($op == "-") {
						$num -= $ndif;
					} else {
						$num += $ndif;
					}

					$_tmp = str_replace($match[0][$j], $num, $_tmp);
				}
			}

			// Check for a Title
			$descr = strip_tags($image->title);
			if (!empty($descr) && preg_match_all('/(\[IMAGE_TITLE)(((:)([\d]+?))?)(\])/smi', $_tmp, $match)) {
				// Most customer don't want the Filename as the Imagetitle if no title is given, so we take out that
				//if (empty($descr)) {
				//	$descr = $image->name;
				//}
				for ($x = 0; $x < count($match[0]); $x++) {
					if ( ((integer)$match[5][$x] > 0) && (strlen($descr) > (integer)$match[5][$x])) {
						$_tmp = str_replace($match[0][$x], substr($descr, 0, (integer)$match[5][$x]).'...', $_tmp);
					} else {
						$_tmp = str_replace($match[0][$x], $descr, $_tmp);
					}
				}
				$_tmp = str_replace('[CAT_TITLE]', '', $_tmp);
				$_tmp = str_replace('[/CAT_TITLE]', '', $_tmp);
			}
			$_tmp = preg_replace('/\[CAT_TITLE\].*?\[\/CAT_TITLE\]/smi', '', $_tmp);
			$_tmp = preg_replace('/(\[IMAGE_TITLE.*?\])/smi', '', $_tmp);

			// Show/hide the Image-Description
			$descr = strip_tags($image->text);
			if (empty($descr)) {
				$_tmp = preg_replace('/(\[CUT_DESCRIPTION:false\])(.*?)(\[\/CUT_DESCRIPTION\])/smi', '', $_tmp);
			}
			if ((substr_count($text->options, '#show_description=false#') < 1) && preg_match_all('/\[IMAGE_DESCRIPTION(:(\d+)?)?\]/smi', $_tmp, $match)) {
				for ($x = 0; $x < count($match[0]); $x++) {
					$len = isset($match[2][$x]) ? (int)$match[2][$x] : 0;
					if ( ($len > 0) && (strlen($descr) > $len)) {
						$_tmp = str_replace($match[0][$x], substr($descr, 0, $len).'...', $_tmp);
					} else {
						$_tmp = str_replace($match[0][$x], $descr, $_tmp);
					}
				}
				$_tmp = preg_replace('/(\[CUT_DESCRIPTION.*?\])(.*?)(\[\/CUT_DESCRIPTION\])/smi', '\\2', $_tmp);
			}
			$_tmp = preg_replace('/(\[CUT_DESCRIPTION.*?\])(.*?)(\[\/CUT_DESCRIPTION\])/smi', '', $_tmp);
			$_tmp = preg_replace('/(\[IMAGE_DESCRIPTION.*?\])/smi', '', $_tmp);

			if (substr_count($text->options, '#show_data=false#') < 1) {
				$_tmp = str_replace('[CUT_DATA]', '', $_tmp);
				$_tmp = str_replace('[/CUT_DATA]', '', $_tmp);
			} else {
				$_tmp = preg_replace('/(\[CUT_DATA\])(.*?)(\[\/CUT_DATA\])/smi', '', $_tmp);
			}

			if (substr_count($text->options, '#show_title=false#') < 1) {
				$_tmp = str_replace('[CUT_TITLE]', '', $_tmp);
				$_tmp = str_replace('[/CUT_TITLE]', '', $_tmp);
			} else {
				$_tmp = preg_replace('/(\[CUT_TITLE\])(.*?)(\[\/CUT_TITLE\])/smi', '', $_tmp);
			}

			// Add the Image to a _thumbnailLine
			$_cont = str_replace('[TEXT]', $_tmp.'[TEXT]', $_cont);

			// Add a new thumbnailLine
			if ($_cnt%$this->_imagesPerLine == 0) {
				$_text .= str_replace('[TEXT]', '', $_cont);
				$_cont  = $this->_thumbnailLine;
			}
		}

		// Add some imageCleanContent until the line is full
		$_addClean = false;
		while ($_cnt%$this->_imagesPerLine != 0) {
			$_addClean = true;
			$_cont = str_replace('[TEXT]', $this->_imageCleanContent.'[TEXT]', $_cont);
			$_cnt++;
		}
		if ($_addClean) {
			$_text .= str_replace('[TEXT]', '', $_cont);
		}

		if ( empty($adminData) || !empty($_template) ) {
			// Check for a required SectionList
			if (substr_count($text->options, '#show_section=true#') >= 1) {
				$_sectionContent = $this->createSectionContentList($SectionId);
			} else {
				$_sectionContent = '';
			}

			// Append the thumbnails
			$_text = str_replace('[TEXT]', $_text, $this->_thumbnailContent);

			// Append the Section-List, or cut them out
			if (strlen(trim($_sectionContent)) > 0) {
				$_text = str_replace('[CUT_SECTION]', '', $_text);
				$_text = str_replace('[/CUT_SECTION]', '', $_text);
				$_text = str_replace('[SECTION_LIST]', $_sectionContent, $_text);
			} else {
				$_text = preg_replace('/(\[CUT_SECTION\])(.*?)(\[\/CUT_SECTION\])/smi', '', $_text);
			}

			$html = $_template;
			if (!$onlyContent) {
				// Replace [TITLE] or strip out the CAT_TITLE...
				$_title = $text->title;
				if ($_title == 'default') $_title = $this->_getSectionName($SectionId, 'ims');
				if ( !empty($_title) && (substr_count($text->options, '#show_title=false#') <= 0) ) {
					$_title = $this->_titleText[0].$_title.$this->_titleText[1];
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
			if ( (strlen($_text) > 0) || ($userCheck->checkAccess('content')) ) {
				if (!$onlyContent) {
					$this->appendTextAdminAddons($_text, $text, $text->id);
				}

				$html = str_replace('[TEXT]',  $_text,  $html);
				$html = str_replace('[CAT_CONTENT]', '', str_replace('[/CAT_CONTENT]', '', $html));
			} else {
				$html = str_replace('[TEXT]', '',  $html);
				$html = preg_replace('/(\[CAT_CONTENT\])(.*?)(\[\/CAT_CONTENT\])/smi', '', $html);
			}

			// Replace Text-Options
			$html = $this->ReplaceLayoutOptions($html, $text->options);
		} else {
			$html = $_text;
		}

		return $html;
	}

	/**
	 * Return a BigImage, based on the template
	 * @return unknown_type
	 */
	protected function getBigImage() {
		global $MainMenu, $langshort;

		// Read the ContentFile (cont_screenshots.tpl)
		$this->_readContentFile(self::FULLSCREEN_LAYOUT);
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

		// Get title
		$_title = '';
		$match = array();
		if (preg_match('/(\[TITLE_TEXT\])(.*?)(\[\/TITLE_TEXT\])/smi', $this->_imageContent, $match)) {
			$_title = $match[2];
			$this->_imageContent = str_replace($match[0], '', $this->_imageContent);
		}
		$match = null;

		// Get all Images from Section
		$image = $this->getImageObject($MainMenu);

		// Create the Content
		$_text = '';

		// Get the Content
		$_tmp = $this->_imageContent;

		// Check for a Flas-Replacement
		$isFlash = (($image->type == 4) || ($image->type == 13));
		if ($isFlash) {
			$_tmp = preg_replace("/(\[IF_IMAGE\])(.*?)(\[\/IF_IMAGE\])/smi", "", $_tmp);
			$_tmp = str_replace("[IF_FLASH]", "", $_tmp);
			$_tmp = str_replace("[/IF_FLASH]", "", $_tmp);
		} else {
			$_tmp = preg_replace("/(\[IF_FLASH\])(.*?)(\[\/IF_FLASH\])/smi", "", $_tmp);
			$_tmp = str_replace("[IF_IMAGE]", "", $_tmp);
			$_tmp = str_replace("[/IF_IMAGE]", "", $_tmp);
		}

		// Show the SWF-Image if it's an Flash-File
		if ($image->type == 4) {
			$_tmp = str_replace("[IMAGE_SRC]", SCREENSHOT_FLASH,  $_tmp);
		} else {
			$_tmp = str_replace("[IMAGE_SRC]", $image->thumb->src,  $_tmp);
		}

		// Replace other variables
		$_tmp = str_replace("[WIDTH]",          $image->thumb->width,  $_tmp);
		$_tmp = str_replace("[HEIGHT]",         $image->thumb->height, $_tmp);
		$_tmp = str_replace("[IMAGE_ID]",       $image->id,     $_tmp);
		$_tmp = str_replace("[IMAGE_NAME]",     htmlentities($image->name), $_tmp);
		$_tmp = str_replace("[REAL_IMAGE_SRC]", $image->src,    $_tmp);
		$_tmp = str_replace("[MAX_HEIGHT]",     $image->height, $_tmp);
		$_tmp = str_replace("[MAX_WIDTH]",      $image->width,  $_tmp);
		$_tmp = str_replace("[REAL_HEIGHT]",    $image->real_height, $_tmp);
		$_tmp = str_replace("[REAL_WIDTH]",     $image->real_width,  $_tmp);
		$_tmp = str_replace("[REAL_SIZE]",      $this->humanReadableFileSize($image->size), $_tmp);
		$_tmp = str_replace("[IMAGE_NUMBER]",   1,  $_tmp);

		if (!$isFlash) {
			$next = $this->getNextImageId($image->id);
			$prev = $this->getPreviousImageId($image->id);
			if (!empty($next)) {
				$_tmp = str_replace('[NEXT_IMAGE_LINK]', '/'.$langshort.'/image/'.$next, $_tmp);
				$_tmp = str_replace('[NEXT_IMAGE]', '', $_tmp);
				$_tmp = str_replace('[/NEXT_IMAGE]', '', $_tmp);
			} else {
				$_tmp = preg_replace('/\[NEXT_IMAGE\](.*?)\[\/NEXT_IMAGE\]/smi', '', $_tmp);
			}
			if (!empty($prev)) {
				$_tmp = str_replace('[PREVIOUS_IMAGE_LINK]', '/'.$langshort.'/image/'.$prev, $_tmp);
				$_tmp = str_replace('[PREVIOUS_IMAGE]', '', $_tmp);
				$_tmp = str_replace('[/PREVIOUS_IMAGE]', '', $_tmp);
			} else {
				$_tmp = preg_replace('/\[PREVIOUS_IMAGE\](.*?)\[\/PREVIOUS_IMAGE\]/smi', '', $_tmp);
			}
		}

		if (preg_match_all("/(\[IMAGE_TITLE)(((:)([\d]+?))?)(\])/smi", $_tmp, $match)) {
			$descr = preg_replace("/(\<)(.*?)(\>)/smi", "", $image->title);
			for ($x = 0; $x < count($match[0]); $x++) {
				if ( ((integer)$match[5][$x] > 0) && (strlen($descr) > (integer)$match[5][$x])) {
					$_tmp = str_replace($match[0][$x], substr($descr, 0, (integer)$match[5][$x]).'...', $_tmp);
				} else {
					$_tmp = str_replace($match[0][$x], $descr, $_tmp);
				}
			}
		}

		if (preg_match("/(\[IMAGE_DESCRIPTION?)(((:)([\d]+?))?)(\])/smi", $_tmp, $match)) {
			if ((integer)$match[5] > 0) {
				$_tmp = str_replace($match[0], substr($image->text, 0, (integer)$match[5]).'...', $_tmp);
			} else {
				$_tmp = str_replace($match[0], $image->text, $_tmp);
			}
		}

		// Add the Image to a _thumbnailLine
		$_cont = str_replace("[TEXT]", $_tmp, $this->_thumbnailLine);
		$_text = str_replace("[TEXT]", "", $_cont);

		// Append the thumbnails
		$_text = str_replace("[TEXT]", $_text, $this->_thumbnailContent);
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
			$_title = $image->title;
		}
		if (!empty($_title)) {
			$html = str_replace("[TITLE]", $_title, $html);
			$html = str_replace("[CAT_TITLE]", "", str_replace("[/CAT_TITLE]", "", $html));
		} else {
			$html = str_replace("[TITLE]", "", $html);
			$html = preg_replace("/(\[CAT_TITLE\])(.*?)(\[\/CAT_TITLE\])/smi", "", $html);
		}

		// Replace Text-Options
		$html = $this->ReplaceLayoutOptions($html, '#title=default#');

		return $html;
	}

	/**
	 * Return the Name of an Image-Section (if $isImage is true, the ID is an ImageId and not an SectionID)
	 * @param $id
	 * @param $isImage
	 * @return unknown_type
	 */
	protected function _getImageSectionName($id, $isImage=false) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		if ($isImage) {
			$sql = "SELECT [ims.text] FROM [table.img],[table.img] WHERE [img.id]=".(int)$id." AND [img.section]=[ims.id];";
		} else {
			$sql = "SELECT [ims.text] FROM [table.ims] WHERE [ims.id]=".(int)$id.";";
		}
		$db->run($sql, $res);
		$back = 'unbekannt';
		if ($res->getFirst()) {
			$back = $res->{$db->getFieldName('ims.text')};
		}
		$res = null;
		return $back;
	}

	/**
	 * Return al List with all Images from a ImageSection (if $isImage is true, the ID is an ImageId and not an SectionID)
	 * @param unknown_type $id
	 * @param unknown_type $isImage
	 * @param unknown_type $recursive
	 * @return unknown_type
	 */
	protected function _getImagesFromSection($id, $isImage=false, $recursive=false) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		if ($isImage) {
			$sql = "SELECT [ims.id] FROM [table.img],[table.ims] WHERE [img.id]=".(int)$id." AND [img.section]=[ims.id];";
			$db->run($sql, $res);
			if ($res->getFirst()) {
				$id = (int)$res->{$db->getFieldName('ims.id')};
			} else {
				$id = 0;
			}
			$res = null;
		}

		if ($recursive) {
			$idList = $this->getChildSectionList($id, 'ims', 'id', 'parent');
		} else {
			$idList = array($id);
		}

		// go trough each section and get the ImageID
		$back = array();
		foreach ($idList as $sid) {
			$sql = "SELECT [img.id] FROM [table.img] WHERE [img.section]=".(int)$sid." order by [img.order] ASC;";
			$db->run($sql, $res);
			if ($res->getFirst()) {
				while ($res->getNext()) {
					$_tmp = new pImageEntry($res->{$db->getFieldName('img.id')});
					//$_tmp = $this->getImageObject($res->{$db->getFieldName('img.id')}, "", 0, 'px', (int)$this->_thumbMaxWidth, (int)$this->_thumbMaxHeight);
					if ($_tmp->id > 0) {
						$back[] = $_tmp;
					}
					unset($_tmp);
				}
			}
			$res = null;
		}
		return $back;
	}

	// Reads the Contents-File for Screenshots
	protected function _readContentFile($type="") {
		// Create some default styles
		$this->_thumbnailContent = '<table>[TEXT]</table>';
		$this->_thumbnailLine = '<tr>[TEXT]</tr>';
		$this->_imagesPerLine = 2;
		$this->_imageContent = '<td style="text-align:center;"><a target="_blank" href="[BIG_IMAGE_LINK]"><img src="[IMAGE_SRC]" style="border-width:0px;width:[WIDTH]px;height:[HEIGHT]px;margin:5px;" alt="[IMAGE_TITLE]" /></a><br /><strong>Description:</strong> [IMAGE_DESCRIPTION:100]<br /><strong>dimension:</strong> [REAL_WIDTH]x[REAL_HEIGHT]<br /><strong>size:</strong> [REAL_SIZE]</td>'.chr(10);
		$this->_imageCleanContent = '<td>&nbsp;</td>'.chr(10);

		// Set the ScreenshotLayoutFile and read them
		$layout = ABS_TEMPLATE_DIR."/".$this->contentFile;
		if (is_file($layout) && is_readable($layout)) {
			$cont = file_get_contents($layout);

			// Check for [SCRIPT_INCLUDE]
			if (preg_match("/(\[SCRIPT_INCLUDE\])(.*?)(\[\/SCRIPT_INCLUDE\])/smi", $cont, $match)) {
				$this->_specialScriptFile = $match[2];
			}

			// Check for [STYLE_INCLUDE]
			if (preg_match("/(\[STYLE_INCLUDE\])(.*?)(\[\/STYLE_INCLUDE\])/smi", $cont, $match)) {
				$this->_specialCssFile = $match[2];
			}

			// Check for [STYLE_CONTENT]
			if (preg_match("/(\[STYLE_CONTENT\])(.*?)(\[\/STYLE_CONTENT\])/smi", $cont, $match)) {
				$this->_specialCssContent = $match[2];
			}

			// Check for [OPTIONS]
			$this->_contentOptions = $this->_parseOptionsTags($cont, $this->_contentOptions);

			// Check for a [LAYOUT]...[/LAYOUT] as _mainContent
			if (preg_match_all("/(\[LAYOUT)((:".$type.")?)(\])(.*?)(\[\/LAYOUT\])/smi", $cont, $match)) {
				for ($i = 0; $i < count($match[0]); $i++) {
					if ( ($match[3][$i] == ":".$type) || ((trim($type) == "default") && (strlen(trim($match[3][$i])) <= 0)) ) {
						$this->_thumbnailContent = $match[5][$i];
					}
				}
			}

			// Check for [THUMBNAIL_LINE]...[/THUMBNAIL_LINE]
			if (preg_match("/(\[THUMBNAIL_LINE\])(.*?)(\[\/THUMBNAIL_LINE\])/smi", $cont, $match)) {
				$this->_thumbnailLine = $match[2];
			}

			// Check for [THUMBNAIL:type:NumOfImgPerEntry]...[/THUMBNAIL]
			if (preg_match_all("/(\[THUMBNAIL:)(.*?)(:)([\d]+?)(\])(.*?)(\[\/THUMBNAIL\])/smi", $cont, $match)) {
				for ($i = 0; $i < count($match[0]); $i++) {
					if (strToLower($match[2][$i]) == strToLower($type)) {
						$this->_imageContent  = $match[6][$i];
						$this->_imagesPerLine = (integer)$match[4][$i];
						break;
					}
				}
			}

			// Check for [THUMBNAIL:type:NumOfImgPerEntry]...[/THUMBNAIL]
			if (preg_match_all("/(\[THUMBNAIL_CLEAN:)(.*?)(\])(.*?)(\[\/THUMBNAIL_CLEAN\])/smi", $cont, $match)) {
				for ($i = 0; $i < count($match[0]); $i++) {
					if (strToLower($match[2][$i]) == strToLower($type)) {
						$this->_imageCleanContent  = $match[4][$i];
						break;
					}
				}
			}

			// Check for a Section-Content
			if (preg_match("/(\[IMAGE_SECTION\])(.*?)(\[\/IMAGE_SECTION\])/smi", $cont, $match)) {
				$this->_sectionContent = $match[2];
			} else if (preg_match("/(\[THUMBNAIL_SECTION\])(.*?)(\[\/THUMBNAIL_SECTION\])/smi", $cont, $match)) {
				$this->_sectionContent = $match[2];
			}

			// Check for a Section-Delimiter
			if (preg_match("/(\[IMAGE_SECTION_DELIMITER\])(.*?)(\[\/IMAGE_SECTION_DELIMITER\])/smi", $cont, $match)) {
				// Read all Section-Delimiter, found in the PROGRAM_SECTION_DELIMITER
				if (preg_match_all("/(\[)(CLEAN|DOWN|ENTRY|LAST)(\])(.*?)(\[\/\\2\])/smi", $match[2], $_match)) {
					$this->_sectionDelimiter = array();
					for ($i = 0; $i < count($_match[0]); $i++)
					$this->_sectionDelimiter[strToLower($_match[2][$i])] = $_match[4][$i];
				}
			} else if (preg_match("/(\[THUMBNAIL_SECTION_DELIMITER\])(.*?)(\[\/THUMBNAIL_SECTION_DELIMITER\])/smi", $cont, $match)) {
				// Read all Section-Delimiter, found in the PROGRAM_SECTION_DELIMITER
				if (preg_match_all("/(\[)(CLEAN|DOWN|ENTRY|LAST)(\])(.*?)(\[\/\\2\])/smi", $match[2], $_match)) {
					$this->_sectionDelimiter = array();
					for ($i = 0; $i < count($_match[0]); $i++) {
						$this->_sectionDelimiter[strToLower($_match[2][$i])] = $_match[4][$i];
					}
				}
			}

			// Check for a Section-Images
			if (preg_match("/(\[SECTION_DELIMITER_IMAGE\])(.*?)(\[\/SECTION_DELIMITER_IMAGE\])/smi", $cont, $match)) {
				// Read all Section-Delimiter, found in the SECTION_DELIMITER_IMAGE
				if (preg_match_all("/(\[)(ENTRY|EMPTY|EXPANDED)(\])(.*?)(\[\/\\2\])/smi", $match[2], $_match)) {
					$this->_sectionDelimiterImages = array();
					for ($i = 0; $i < count($_match[0]); $i++) {
						$this->_sectionDelimiterImages[strToLower($_match[2][$i])] = $_match[4][$i];
					}
				}
			}

			// Check for [TITLE_TEXT:before]
			if (preg_match("/(\[TITLE_TEXT:before\])(.*?)(\[\/TITLE_TEXT\])/smi", $cont, $match)) {
				$this->_titleText[0] = $match[2];
			}
			// Check for [TITLE_TEXT:before]
			if (preg_match("/(\[TITLE_TEXT:after\])(.*?)(\[\/TITLE_TEXT\])/smi", $cont, $match)) {
				$this->_titleText[1] = $match[2];
			}

			// Check for [THUMB_MAX_WIDTH:xx]
			if (preg_match("/(\[THUMB_MAX_WIDTH:)(.*?)(\])/smi", $cont, $match)) {
				$this->_thumbMaxWidth = $match[2];
			}
			// Check for [THUMB_MAX_HEIGHT:xx]
			if (preg_match("/(\[THUMB_MAX_HEIGHT:)(.*?)(\])/smi", $cont, $match)) {
				$this->_thumbMaxHeight = $match[2];
			}

			unset($cont);
		}
	}

	// Create and Return the Section-List based on the template (only if it is required by this entry)
	function createSectionContentList($id) {
		global $SectionId;
		$selected = $SectionId;

		if ((integer)$SectionId <= 0) {
			$selected =  $this->getSelectedSectionStructure($id, 'ims', 'parent', 'id');
		} else {
			$selected =  $this->getSelectedSectionStructure($SectionId, 'ims', 'parent', 'id');
		}

		if (preg_match("/(\[SECTION\])(.*?)(\[\/SECTION\])/smi", $this->_sectionContent, $match)) {
			$_section = $match[2];

			// in newer versions, you can use [SECTION_CONTENT] to build more complex sectionlists
			if (substr_count($match[0], "[SECTION_CONTENT]") > 0) {
				$html = str_replace($match[0], "", $this->_sectionContent);
				$html = str_replace("[SELECTED_SECTION_NAME]", $this->_getImageSectionName($selected[0]), $html);
				$html = str_replace("[SECTION_CONTENT]", $this->_sub_createSectionContentList($id, "", $selected, $_section), $html);
			} else {
				// used for Old-Style-Sections
				$html = str_replace($match[0], "[SECTION]", $this->_sectionContent);
				$html = str_replace("[SELECTED_SECTION_NAME]", $this->_getImageSectionName($selected[0]), $html);
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
	function _sub_createSectionContentList($id, $before, $selected, $template) {
		global $langshort, $MainMenu;
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
			$sql = "SELECT * FROM ".$this->DB->Table('ims')." WHERE ".$this->DB->Field('ims','id')."='".$id."'";
			$res = $this->DB->ReturnQueryResult($sql);
			if ($res) {
				$row = mysql_fetch_assoc($res);

				// Get the Number of available Programs in this Section
				$_count = $this->_getNumImagesFromSection($row[$this->DB->FieldOnly('ims','id')]);

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
				$html = str_replace("[NUMBER_OF_PROGRAMS]", $_count, $html);
				$html = str_replace("[NUMBER_OF_IMAGES]",   $_count, $html);
				$html = str_replace("[SECTION_DELIMITER]",  "", $html);
				$html = str_replace("[SECTION_NAME]",       $row[$this->DB->FieldOnly('ims','text')], $html);
				$html = str_replace("[SECTION_NUMBER]",     $id, $html);
				$html = str_replace("[SECTION_ID]",         $id, $html);
				$html = str_replace("[PARENT_SECTION_ID]",  0, $html);
				if (strlen(trim($this->_specialLinkDirection)) <= 0) {
					$html = str_replace("[SECTION_LINK]", "/".$langshort."/".$MainMenu."/sec=".$row[$this->DB->FieldOnly('ims','id')], $html);
				} else {
					$html = str_replace("[SECTION_LINK]", "/".$langshort."/".$this->_specialLinkDirection."/0/sec=".$row[$this->DB->FieldOnly('ims','id')], $html);
				}
				$html = str_replace("[SECTION_LINK]", "/".$langshort."/".$MainMenu."/sec=".$row[$this->DB->FieldOnly('ims','id')], $html);
			}
			$this->DB->FreeDatabaseResult($res);
		}

		// Get Parent-Sections all Child-Sections
		$sql = "SELECT * FROM ".$this->DB->Table('ims')." WHERE ".$this->DB->Field('ims','parent')." = '".$id."'";
		$res = $this->DB->ReturnQueryResult($sql);
		if ($res) {
			$cnt = 1;
			while ($row = mysql_fetch_assoc($res)) {
				// Create current delimiter
				if ($cnt < mysql_num_rows($res)) {
					$_delim = $before.$this->_sectionDelimiter['entry'];
					$_delim_child = $before.$this->_sectionDelimiter['down'];
				} else {
					$_delim = $before.$this->_sectionDelimiter['last'];
					$_delim_child = $before.$this->_sectionDelimiter['clean'];
				}

				// Get the Number of available Programs in this Section
				$_count = $this->_getNumImagesFromSection($row[$this->DB->FieldOnly('ims','id')]);

				// if we can locate SECTION_CONTENT in $subsection, we replace this with $template
				// and insert the tag SECTION_SECTION after so we know where wo insert the next section on this level
				$tmp = $template;
				if (substr_count($tmp, "[SECTION_CONTENT]") <= 0) {
					$tmp .= "[SECTION_CONTENT]";
				}

				// Check if this Section is selected
				if (in_array((int)$row[$this->DB->FieldOnly('ims','id')], $selected)) {
					$tmp = preg_replace("/(\[IF_SELECTED:')(.*?)(':')(.*?)('\])/smi", "\\2", $template);
				} else {
					$tmp = preg_replace("/(\[IF_SELECTED:')(.*?)(':')(.*?)('\])/smi", "\\4", $template);
				}
				// Replace real selected
				if ((int)$row[$this->DB->FieldOnly('ims','id')] == (int)$selected[0]) {
					$tmp = preg_replace("/(\[IF_SELECTED_ID:')(.*?)(':')(.*?)('\])/smi", "\\2", $tmp);
				} else {
					$tmp = preg_replace("/(\[IF_SELECTED_ID:')(.*?)(':')(.*?)('\])/smi", "\\4", $tmp);
				}

				// replace all other tags
				$tmp = str_replace("[NUMBER_OF_PROGRAMS]", $_count, $tmp);
				$tmp = str_replace("[NUMBER_OF_IMAGES]",   $_count, $tmp);
				$tmp = str_replace("[SECTION_DELIMITER]",  $_delim, $tmp);
				$tmp = str_replace("[SECTION_NAME]",       $row[$this->DB->FieldOnly('ims','text')], $tmp);
				$tmp = str_replace("[SECTION_NUMBER]",     $row[$this->DB->FieldOnly('ims','id')], $tmp);
				$tmp = str_replace("[SECTION_ID]",         $row[$this->DB->FieldOnly('ims','id')], $tmp);
				$tmp = str_replace("[PARENT_SECTION_ID]",  $id, $tmp);
				if (strlen(trim($this->_specialLinkDirection)) <= 0) {
					$tmp = str_replace("[SECTION_LINK]", "/".$langshort."/".$MainMenu."/sec=".$row[$this->DB->FieldOnly('ims','id')], $tmp);
				} else {
					$tmp = str_replace("[SECTION_LINK]", "/".$langshort."/".$this->_specialLinkDirection."/0/sec=".$row[$this->DB->FieldOnly('ims','id')], $tmp);
				}

				// get all subsections
				$sub = $this->_sub_createSectionContentList($row[$this->DB->FieldOnly('ims','id')], $_delim_child, $selected, $template);

				// if subsections are available, replace the image with ENTRY, otherwise with EMPTY
				if (strlen($sub) > 0) {
					if (in_array((integer)$row[$this->DB->FieldOnly('ims','id')], $selected) && (array_key_exists('expanded',$this->_sectionDelimiterImages))) {
						$tmp = str_replace("[SECTION_IMAGE]", $this->_sectionDelimiterImages['expanded'], $tmp);
					} else if (array_key_exists('entry',$this->_sectionDelimiterImages)) {
						$tmp = str_replace("[SECTION_IMAGE]", $this->_sectionDelimiterImages['entry'], $tmp);
					} else {
						$tmp = str_replace("[SECTION_IMAGE]", '', $tmp);
					}
				} else if (array_key_exists('empty',$this->_sectionDelimiterImages)) {
					$tmp = str_replace("[SECTION_IMAGE]", $this->_sectionDelimiterImages['empty'], $tmp);
				} else {
					$tmp = str_replace("[SECTION_IMAGE]", '', $tmp);
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
			if (in_array((integer)$id, $selected) && (array_key_exists('expanded',$this->_sectionDelimiterImages))) {
				$html = str_replace("[SECTION_IMAGE]", $this->_sectionDelimiterImages['expanded'], $html);
			} else if (array_key_exists('entry',$this->_sectionDelimiterImages)) {
				$html = str_replace("[SECTION_IMAGE]", $this->_sectionDelimiterImages['entry'], $html);
			} else {
				$html = str_replace("[SECTION_IMAGE]", '', $html);
			}
		} else {
			// replace the SECTION_IMAGE in html (replace it with EMPTY, because we don't have any here
			if (array_key_exists('empty',$this->_sectionDelimiterImages)) {
				$html = str_replace("[SECTION_IMAGE]", $this->_sectionDelimiterImages['empty'], $html);
			} else {
				$html = str_replace("[SECTION_IMAGE]", '', $html);
			}
			$html = str_replace("[SECTION_CONTENT]", "", $html);
		}
		return $html;
	}

	// Return the Number of Images in a Section
	function _getNumImagesFromSection($id) {
		$sql = "SELECT COUNT(".$this->DB->Field('img','id').") AS num FROM ".$this->DB->Table('img')." WHERE ".$this->DB->Field('img','section')." = '".$id."'";
		$res = $this->DB->ReturnQueryResult($sql);
		$row = array('num'=>0);
		if ($res) {
			$row = mysql_fetch_assoc($res);
		}
		return (integer)$row['num'];
	}

	/**
	 * Check Database integrity
	 *
	 * This function creates all required Tables, Updates, Inserts and Deletes.
	 */
	function _checkDatabase() {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;

		// Get the current Version
		$v = $this->_checkMainDatabase();
		$version = $v[0];
		$versionid = $v[1];

		// Updates to the Database
		if ($version < 2006010600) {
			// Create the Images-Table
			$sql  = "CREATE TABLE IF NOT EXISTS [table.img] (".
			" [field.img.id] INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,".
			" [field.img.image] VARCHAR(100) NOT NULL DEFAULT '',".
			" [field.img.section] INT(10) UNSIGNED NOT NULL DEFAULT 0,".
			" PRIMARY KEY ([field.img.id]),".
			" UNIQUE KEY [field.img.id] ([field.img.id])".
			" );";
			$db->run($sql, $res);
			$res = null;

			// Create the ImageSection-Table
			$sql  = "CREATE TABLE IF NOT EXISTS [table.ims] (".
			" [field.ims.id] INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,".
			" [field.ims.parent] INT(10) UNSIGNED NOT NULL DEFAULT 0,".
			" [field.ims.text] VARCHAR(100) NOT NULL DEFAULT '',".
			" PRIMARY KEY ([field.ims.id]),".
			" UNIQUE KEY [field.ims.id] ([field.ims.id])".
			" );";
			$db->run($sql, $res);
			$res = null;

			// Create the ImageTexts-Table
			$sql  = "CREATE TABLE IF NOT EXISTS [table.imt] (".
			" [field.imt.id] INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,".
			" [field.imt.image] INT(10) UNSIGNED NOT NULL DEFAULT 0,".
			" [field.imt.title] VARCHAR(100) NOT NULL DEFAULT '',".
			" [field.imt.text] TEXT NOT NULL DEFAULT '',".
			" [field.imt.html] INT(10) UNSIGNED NOT NULL DEFAULT 0,".
			" [field.imt.lang] INT(10) UNSIGNED NOT NULL DEFAULT 0,".
			" PRIMARY KEY ([field.imt.id]),".
			" UNIQUE KEY [field.imt.id] ([field.imt.id])".
			" );";
			$db->run($sql, $res);
			$res = null;

			// Insert base-Section if not already exists
			$sql = "SELECT [ims.text] FROM [table.ims] WHERE [ims.text]='default'";
			$db->run($sql, $res);
			if (!$res->getFirst()) {
				$sql = "INSERT INTO [table.ims]".
				" ([field.ims.parent],[field.ims.text])".
				" VALUES (0,'default');";
				$db->run($sql, $res);
				$res = null;
			}
		}

		if ($version < 2007013000) {
			$sql = "ALTER TABLE [table.img] ADD [field.img.date] INT(11) UNSIGNED NOT NULL DEFAULT 0;";
			$db->run($sql, $res);
			$res = null;
		}
		if ($version < 2007013001) {
			$sql = "ALTER TABLE [table.img] ADD [field.img.name] VARCHAR(50) NOT NULL DEFAULT '';";
			$db->run($sql, $res);
			$res = null;
		}

		if ($version < 2008121603) {
			// extend the Database with mimetype-row
			$sql = "ALTER TABLE [table.img] ADD COLUMN [field.img.order] INT(11) UNSIGNED DEFAULT 0;";
			$db->run($sql, $res);
			$res = null;
		}

		// Update the version
		$this->_updateVersionTable($version, $versionid, self::VERSION);
	}

}

?>