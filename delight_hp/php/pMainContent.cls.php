<?php
die("pMainContent");

class pMainContent {
	var $DB;
	var $LANG;
	var $Template;
	var $Content;
	var $PARSER;
	var $LayoutPath;
	var $LayoutStyles;
	var $ShowTextEntry;
	var $LayoutFileList;
	var $LayoutComments;
	var $AdminHtmlTemplate;
	var $AdminFunctionHtml;
	var $CreateStaticFiles;
	var $HasDifferentSections;

	function pMainContent(&$db="", &$lang="") {
		// Set main Objects
		$this->DB   = &$db;
		$this->LANG = &$lang;

		// default it's not the CreateStaticFiles-Script
		$this->CreateStaticFiles    = false;
		$this->HasDifferentSections = false;

		// Set Main variables
		global $_SERVER;
		$this->LayoutPath = dirname($_SERVER["SCRIPT_FILENAME"])."/php/layout/";

		// Create Template-File parser
		require_once(dirname(__FILE__)."/php/class/pParseTemplate.cls.php");
		$this->PARSER = new pParseTemplate($this->DB, $this->LANG);
	}

	function SetTemplateContent($data="",$admin="") {
		$this->Template = $data;
		$this->AdminFunctionHtml = $admin;
	}

	function GetNumberOfStaticPages() {
		return 1;
	}

	function CreateContent($MenuId="0") {
		global $ChangeId,$ChangeAction;
		$userCheck = pCheckUserData::getInstance();
		if (($userCheck->checkAccess("CONTENT")) && (!($this->CreateStaticFiles)) && (($ChangeId != "0") || (in_array($ChangeAction, array("new", "layout", "image", "link", "correct", "preview"))) )) {
			switch ($ChangeAction) {
				case 'moveup':   $this->ShowTextEntry = true;  $this->MoveText($ChangeId,'up');    break;
				case 'movedown': $this->ShowTextEntry = true;  $this->MoveText($ChangeId,'down');  break;
				case 'edit':     $this->ShowTextEntry = false; $this->EditTextEntry($ChangeId);    break;
				case 'delete':   $this->ShowTextEntry = false; $this->DeleteTextEntry($ChangeId);  break;
				case 'new':      $this->ShowTextEntry = false; $this->EditTextEntry();             break;
				case 'layout':   $this->ShowTextEntry = false; $this->ShowLayoutWindow($ChangeId); break;
				case 'link':     $this->ShowTextEntry = false; $this->ShowLinkWindow();            break;
				case 'correct':  $this->ShowTextEntry = false; $this->ShowTextCorrectWindow();     break;
				case 'preview':  $this->ShowTextEntry = false; $this->ShowLayoutPreview();         break;
				case 'image':    $this->ShowTextEntry = false; $this->ShowImageChooser($ChangeId); break;
				default:         $this->ShowTextEntry = true;  break;
			}
		} else {
			$this->ShowTextEntry = true;
		}
	}

	function GetContent() {
		$this->Content = preg_replace("/(\[ADMIN_FUNCTIONS\])/i","",$this->Content);
		if ($this->Content == "403") {
			$this->Content = $this->CreateAccessDeniedContent();
		} else if ($this->Content == "") {
			$this->Content = $this->CreateNoContentContent();
		}
		$this->Content = str_replace('\"', '"', $this->Content);
		$this->Content = str_replace('\'', '\'', $this->Content);
		return $this->Content;
	}

	function ReplaceGlobalVariables($rep="") {
		global $MainMenu,$SubMenu,$lang,$StaticMenuId;
		if ($StaticMenuId != "0") {
			$SubMenuId = $StaticMenuId;
		} else {
			$SubMenuId = $SubMenu;
		}
		$rep = str_replace("[MENU_ID]",$MainMenu,$rep);
		$rep = str_replace("[SUBMENU_ID]",$SubMenu,$rep);
		$rep = str_replace("[LINK_LANG]",$lang,$rep);
		$rep = str_replace("[MENU_LINK_ID]","m=".$MainMenu."&s=".$SubMenuId,$rep);
		$rep = str_replace("[MENU_LINK_LANG]","lan=".$this->LANG->getLanguageName(),$rep); //."&log=".$Login
		// Replace Lang_Value messages
		$match = null;
		if (preg_match_all("/(\[LANG_VALUE\:)([\w\d]+)(\])/i", $rep, $match)) {
			for ($i = 0; $i < count($match[0]); $i++) {
				$rep = preg_replace("/(".preg_quote($match[0][$i],"/").")/i",$this->LANG->getValue('','txt',$match[2][$i]),$rep);
			}
		}
		return $rep;
	}

	function GetAllLayoutFiles() {
		$this->LayoutFileList = array();
		$sql = "SELECT ".$this->DB->Table('lay').".* FROM ".$this->DB->Table('lay')." ORDER BY ".$this->DB->Field('lay','text')."";
		$res = $this->DB->ReturnQueryResult($sql);
		if ($res) {
			while (($row = mysql_fetch_assoc($res)) !== false) {
				$file = $this->LayoutPath."/".$row[$this->DB->FieldOnly('lay','file')].".html";
				if (file_exists($file)) {
					$this->LayoutFileList[$row[$this->DB->FieldOnly('lay','file')]] = $row[$this->DB->FieldOnly('lay','text')];
				}
			}
		}
		$this->DB->FreeDatabaseResult($res);
	}

	function ParseTextTemplateFile($file="") {
		$match = null;
		if (!(is_array($this->LayoutStyles)) || (!(array_key_exists($file,$this->LayoutStyles)))) {
			if (@file_exists($file)) {
				if (($fp = @fopen($file,"r")) !== false) {
					// Read the Layout-File and reset Layouts
					$this->LayoutStyles[$file]   = array();
					$this->LayoutComments[$file] = array();
					$tpl = @fread($fp,@filesize($file));

					// Parse Layout File into Array
					// Get GLOBAL Layouts
					if (preg_match("/(\[-->GLOBAL\])(.*)(\[--GLOBAL\])/smi", $tpl,$match)) {
						$data['global'] = $match[2];
					}
					// Get TEXT Layouts
					if (preg_match("/(\[-->TEXT\])(.*)(\[--TEXT\])/smi", $tpl, $match)) {
						$data['text'] = $match[2];
					}
					// Get TITLE Layouts
					if (preg_match("/(\[-->TITLE\])(.*)(\[--TITLE\])/smi", $tpl, $match)) {
						$data['title'] = $match[2];
					}
					// Get IMAGE Layouts
					if (preg_match("/(\[-->IMAGE\])(.*)(\[--IMAGE\])/smi", $tpl, $match)) {
						$data['image'] = $match[2];
					}
					// Get LINK Layouts
					if (preg_match("/(\[-->LINK\])(.*)(\[--LINK\])/smi", $tpl, $match)) {
						$data['link'] = $match[2];
					}

					// Parse GLOBAL Template-Styles
					if (preg_match_all("/(\[descr\:)(\w+)(\:)([\w\W]+)(\])([\w\W]+)(\[-->)(\\2)(\])(.*)(\[--\\2\])/smi", $data['global'], $match)) {
						for ($i = 0; $i < count($match[2]); $i++) {
							$this->LayoutStyles[$file]['GlobalLayout'][$match[2][$i]]   = $match[10][$i];
							$this->LayoutComments[$file]['GlobalLayout'][$match[2][$i]] = $match[4][$i];
						}
					}
					// Parse TEXT Template-Styles
					if (preg_match_all("/(\[descr\:)(\w+)(\:)([\w\W]+)(\])([\w\W]+)(\[-->)(\w+)(\])(.*)(\[--\\2\])/smi", $data['text'], $match)) {
						for ($i = 0; $i < count($match[2]); $i++) {
							$this->LayoutStyles[$file]['TextLayout'][$match[2][$i]]   = $match[10][$i];
							$this->LayoutComments[$file]['TextLayout'][$match[2][$i]] = $match[4][$i];
						}
					}
					// Parse TITLE Template-Styles
					if (preg_match_all("/(\[descr\:)(\w+)(\:)([\w\W]+)(\])([\w\W]+)(\[-->)(\w+)(\])(.*)(\[--\\2\])/smi", $data['title'], $match)) {
						for ($i = 0; $i < count($match[2]); $i++) {
							$this->LayoutStyles[$file]['TitleLayout'][$match[2][$i]]   = $match[10][$i];
							$this->LayoutComments[$file]['TitleLayout'][$match[2][$i]] = $match[4][$i];
						}
					}
					// Parse IMAGE Template-Styles
					if (preg_match_all("/(\[descr\:)(\w+)(\:)([\w\W]+)(\])([\w\W]+)(\[-->)(\w+)(\])(.*)(\[--\\2\])/smi", $data['image'], $match)) {
						for ($i = 0; $i < count($match[2]); $i++) {
							$this->LayoutStyles[$file]['ImagePosition'][$match[2][$i]]   = $match[10][$i];
							$this->LayoutComments[$file]['ImagePosition'][$match[2][$i]] = $match[4][$i];
						}
					}
					// Parse LINK Template-Styles
					if (preg_match_all("/(\[-->)(\w+)(\])(.*)(\[--\\2\])/smi", $data['link'], $match)) {
						for ($i = 0; $i < count($match[2]); $i++) {
							$this->LayoutStyles[$file]['LinkLayout'][$match[2][$i]]   = $match[4][$i];
						}
					}

					// Get Admin-Functions
					$this->GetAdminFunctionHtml($tpl);
				}
				@fclose($fp);
			}
		}
	}

	function ParseFAQTemplateFile($file="") {
		$match = null;
		if (!(is_array($this->LayoutStyles)) || (!(array_key_exists($file,$this->LayoutStyles)))) {
			if (@file_exists($file)) {
				if (($fp = @fopen($file,"r")) !== false) {
					// Read the Layout-File and reset Layouts
					$this->LayoutStyles[$file] = array();
					$tpl = @fread($fp,@filesize($file));

					// Parse Layout File into Array
					// Get GLOBAL Layouts
					if (preg_match("/(\[-->GLOBAL\])(.*)(\[--GLOBAL\])/smi", $tpl, $match)) {
						$data['global'] = $match[2];
					}
					// Get IMAGE Layouts
					if (preg_match("/(\[-->IMAGE\])(.*)(\[--IMAGE\])/smi", $tpl, $match)) {
						$data['image'] = $match[2];
					}
					// Get LINK Layouts
					if (preg_match("/(\[-->LINK\])(.*)(\[--LINK\])/smi", $tpl, $match)) {
						$data['link'] = $match[2];
					}

					// Parse GLOBAL Template-Styles
					if (preg_match_all("/(\[-->)(\w+)(\])(.*)(\[--\\2\])/smi", $data['global'], $match)) {
						for ($i = 0; $i < count($match[2]); $i++) {
							$this->LayoutStyles[$file]['GlobalLayout'][$match[2][$i]] = $match[4][$i];
						}
					}
					// Parse IMAGE Template-Styles
					if (preg_match_all("/(\[-->)(\w+)(\])(.*)(\[--\\2\])/smi", $data['image'], $match)) {
						for ($i = 0; $i < count($match[2]); $i++) {
							$this->LayoutStyles[$file]['ImagePosition'][$match[2][$i]] = $match[4][$i];
						}
					}
					// Parse LINK Template-Styles
					if (preg_match_all("/(\[-->)(\w+)(\])(.*)(\[--\\2\])/smi", $data['link'], $match)) {
						for ($i = 0; $i < count($match[2]); $i++) {
							$this->LayoutStyles[$file]['LinkLayout'][$match[2][$i]] = $match[4][$i];
						}
					}

					// Get Admin-Functions
					$this->GetAdminFunctionHtml($tpl);
				}
				@fclose($fp);
			}
		}
	}

	function ParseScreenshotTemplateFile($file="") {
		$match = null;
		if (!(is_array($this->LayoutStyles)) || (!(array_key_exists($file,$this->LayoutStyles)))) {
			if (@file_exists($file)) {
				if (($fp = @fopen($file,"r")) !== false) {
					// Read the Layout-File and reset Layouts
					$this->LayoutStyles[$file] = array();
					$tpl = @fread($fp,@filesize($file));

					// Parse Layout File into Array
					// Get GLOBAL Layouts
					if (preg_match("/(\[-->GLOBAL\])(.*)(\[--GLOBAL\])/smi", $tpl, $match)) {
						$data['global'] = $match[2];
					}
					// Get IMAGE Layouts
					if (preg_match("/(\[-->IMAGE\])(.*)(\[--IMAGE\])/smi", $tpl, $match)) {
						$data['image'] = $match[2];
					}
					// Get LINK Layouts
					if (preg_match("/(\[-->LINK\])(.*)(\[--LINK\])/smi", $tpl, $match)) {
						$data['link'] = $match[2];
					}

					// Parse GLOBAL Template-Styles
					if (preg_match_all("/(\[-->)(\w+)(\])(.*)(\[--\\2\])/smi", $data['global'], $match)) {
						for ($i = 0; $i < count($match[2]); $i++) {
							$this->LayoutStyles[$file]['GlobalLayout'][$match[2][$i]] = $match[4][$i];
						}
					}
					// Parse IMAGE Template-Styles
					if (preg_match_all("/(\[-->)(\w+)(\])(.*)(\[--\\2\])/smi", $data['image'], $match)) {
						for ($i = 0; $i < count($match[2]); $i++) {
							$this->LayoutStyles[$file]['ImagePosition'][$match[2][$i]] = $match[4][$i];
						}
					}
					// Parse LINK Template-Styles
					if (preg_match_all("/(\[-->)(\w+)(\])(.*)(\[--\\2\])/smi", $data['link'], $match)) {
						for ($i = 0; $i < count($match[2]); $i++) {
							$this->LayoutStyles[$file]['LinkLayout'][$match[2][$i]] = $match[4][$i];
						}
					}

					// Get Admin-Functions
					$this->GetAdminFunctionHtml($tpl);
				}
				@fclose($fp);
			}
		}
	}

	function ParseNewsTemplateFile($file="") {
		$match = null;
		if (!(is_array($this->LayoutStyles)) || (!(array_key_exists($file,$this->LayoutStyles)))) {
			if (@file_exists($file)) {
				if (($fp = @fopen($file,"r")) !== false) {
					// Read the Layout-File and reset Layouts
					$this->LayoutStyles[$file] = array();
					$tpl = @fread($fp,@filesize($file));

					// Parse Layout File into Array
					// Get GLOBAL Layouts
					if (preg_match("/(\[-->GLOBAL\])(.*)(\[--GLOBAL\])/smi", $tpl, $match)) {
						$data['global'] = $match[2];
					}
					// Get IMAGE Layouts
					if (preg_match("/(\[-->NEWS\])(.*)(\[--NEWS\])/smi", $tpl, $match)) {
						$data['entry'] = $match[2];
					}
					// Get LINK Layouts
					if (preg_match("/(\[-->LINK\])(.*)(\[--LINK\])/smi", $tpl, $match)) {
						$data['link'] = $match[2];
					}

					// Parse GLOBAL Template-Styles
					if (preg_match_all("/(\[-->)(\w+)(\])(.*)(\[--\\2\])/smi", $data['global'], $match)) {
						for ($i = 0; $i < count($match[2]); $i++) {
							$this->LayoutStyles[$file]['GlobalLayout'][$match[2][$i]] = $match[4][$i];
						}
					}
					// Parse NEWS Template-Styles
					if (preg_match_all("/(\[-->)(\w+)(\:)(\d+)(\])(.*)(\[--\\2\\3\\4\])/smi", $data['entry'], $match)) {
						for ($i = 0; $i < count($match[2]); $i++) {
							$this->LayoutStyles[$file]['NewsText'][$match[2][$i]][$match[4][$i]] = $match[6][$i];
						}
					}
					rsort($this->LayoutStyles[$file]['NewsText']['NEWS_ENTRY']);
					// Parse LINK Template-Styles
					if (preg_match_all("/(\[-->)(\w+)(\])(.*)(\[--\\2\])/smi", $data['link'], $match)) {
						for ($i = 0; $i < count($match[2]); $i++) {
							$this->LayoutStyles[$file]['LinkLayout'][$match[2][$i]] = $match[4][$i];
						}
					}

					// Check for NumOfNews per page
					if (preg_match("/(\[NEWS_LIST\:)(\d+)(\])/smi", $this->LayoutStyles[$file]['GlobalLayout']['NEWS_LIST'], $match)) {
						$this->LayoutStyles[$file]['GlobalLayout']['NEWS_COUNT'] = $match[2];
						$this->LayoutStyles[$file]['GlobalLayout']['NEWS_LIST']  = preg_replace("/(\[NEWS_LIST\:)(\d+)(\])/smi", "[NEWS_LIST]", $this->LayoutStyles[$file]['GlobalLayout']['NEWS_LIST']);
					} else {
						$this->LayoutStyles[$file]['GlobalLayout']['NEWS_COUNT'] = "0";
					}

					// Get Admin-Functions
					$this->GetAdminFunctionHtml($tpl);
				}
				@fclose($fp);
			}
		}
	}

	function ParseProgramTemplateFile($file="") {
		$match = null;
		if (!(is_array($this->LayoutStyles)) || (!(array_key_exists($file,$this->LayoutStyles)))) {
			if ((@file_exists($file)) !== false) {
				if (($fp = @fopen($file,"r")) !== false) {
					// Read the Layout-File and reset Layouts
					$this->LayoutStyles[$file] = array();
					$tpl = @fread($fp,@filesize($file));

					// Parse Layout File into Array
					// Get GLOBAL Layouts
					if (preg_match("/(\[-->GLOBAL\])(.*)(\[--GLOBAL\])/smi", $tpl, $match)) {
						$data['global'] = $match[2];
					}
					// Get IMAGE Layouts
					if (preg_match("/(\[-->DETAIL\])(.*)(\[--DETAIL\])/smi", $tpl, $match)) {
						$data['detail'] = $match[2];
					}
					// Get LINK Layouts
					if (preg_match("/(\[-->LINK\])(.*)(\[--LINK\])/smi", $tpl, $match)) {
						$data['link'] = $match[2];
					}

					// Parse GLOBAL Template-Styles
					if (preg_match_all("/(\[-->)(\w+)(\])(.*)(\[--\\2\])/smi", $data['global'], $match)) {
						for ($i = 0; $i < count($match[2]); $i++) {
							$this->LayoutStyles[$file]['GlobalLayout'][$match[2][$i]] = $match[4][$i];
						}
					}
					// Parse IMAGE Template-Styles
					if (preg_match_all("/(\[-->)(\w+)(\])(.*)(\[--\\2\])/smi", $data['detail'], $match)) {
						for ($i = 0; $i < count($match[2]); $i++) {
							$this->LayoutStyles[$file]['DetailPosition'][$match[2][$i]] = $match[4][$i];
						}
					}
					// Parse LINK Template-Styles
					if (preg_match_all("/(\[-->)(\w+)(\])(.*)(\[--\\2\])/smi", $data['link'], $match)) {
						for ($i = 0; $i < count($match[2]); $i++) {
							$this->LayoutStyles[$file]['LinkLayout'][$match[2][$i]] = $match[4][$i];
						}
					}

					// Get Admin-Functions
					$this->GetAdminFunctionHtml($tpl);
				}
				@fclose($fp);
			}
		}
	}

	function GetTextTemplateStyles($id="0",$LayoutFile="none") {
		$tpl  = "";
		$sql = "SELECT ".$this->DB->Table('sty').".* FROM ".$this->DB->Table('sty')." WHERE ".$this->DB->Field('sty','text')." = '".$id."'";
		$res = $this->DB->ReturnQueryResult($sql);
		if ($res) {
			while (($row = mysql_fetch_assoc($res)) !== false) {
				$para = $row[$this->DB->FieldOnly('sty','para')];
				if (is_array($this->LayoutStyles[$LayoutFile][$para])) {
					$keys = array_keys($this->LayoutStyles[$LayoutFile][$para]);
					if (in_array($row[$this->DB->FieldOnly('sty','class')],$keys)) {
						$tpl[$para][0] = $this->LayoutStyles[$LayoutFile][$para][$row[$this->DB->FieldOnly('sty','class')]];
						$tpl[$para][1] = $row[$this->DB->FieldOnly('sty','class')];
						$tpl[$para][2] = $this->LayoutComments[$LayoutFile][$para][$row[$this->DB->FieldOnly('sty','class')]];
					} else {
						$tpl[$para][0] = $this->LayoutStyles[$LayoutFile][$para][$keys[0]];
						$tpl[$para][1] = $keys[0];
						$tpl[$para][2] = $this->LayoutComments[$LayoutFile][$para][$keys[0]];
					}
				}
			}
		}
		return $tpl;
	}

	function GetImageData($id="0",$type="database") {
		global $_SERVER;
		$sql = "SELECT ".$this->DB->Table('img').".* FROM ".$this->DB->Table('img')." WHERE ".$this->DB->Field('img','id')." = '".$id."'";
		$res = $this->DB->ReturnQueryResult($sql);
		if (($res) || ($type == "file")) {
			$Tsql = "SELECT ".$this->DB->Table('imt').".* FROM ".$this->DB->Table('imt').",".$this->DB->Table('lan')." WHERE ".$this->DB->Field('imt','image')." = '".$id."' AND ".$this->DB->Field('lan','text')." = '".$this->LANG->getLanguageName()."' AND ".$this->DB->Field('imt','lang')." = ".$this->DB->Field('lan','id')."";
			$Tres = $this->DB->ReturnQueryResult($Tsql);
			if ($Tres) {
				$Trow = mysql_fetch_assoc($Tres);
			} else {
				$Trow = array();
			}
			$this->DB->FreeDatabaseResult($Tres);
			if ($type == "database") {
				$row = mysql_fetch_assoc($res);
			}

			if (($type == "file") || (($row[$this->DB->FieldOnly('img','image')] != "") && ($row[$this->DB->FieldOnly('img','id')] != ""))) {
				if ($type == "database") {
					$data['id']    = $row[$this->DB->FieldOnly('img','id')];
					$data['src']   = "images/page/".$row[$this->DB->FieldOnly('img','image')];
				} else {
					$data['id']  = "0";
					$data['src'] = $id;
				}

				if ($type == "database") {
					$ImgAbs1 = dirname($_SERVER["SCRIPT_FILENAME"])."/images/page/".$row[$this->DB->FieldOnly('img','image')];
					$ImgAbs2 = dirname($_SERVER["SCRIPT_FILENAME"])."/images/page/small/".$row[$this->DB->FieldOnly('img','image')];
				} else {
					$ImgAbs1 = dirname($_SERVER["SCRIPT_FILENAME"]).$id;
					$ImgAbs2 = dirname($_SERVER["SCRIPT_FILENAME"]).$id;
				}

				if (file_exists($ImgAbs1)) {
					$size_b = @getimagesize($ImgAbs1);
					$size_s = @getimagesize($ImgAbs2);
					$data['small'] = $this->CheckSmallImageType($row[$this->DB->FieldOnly('img','image')]);
					$data['width'] = $size_b[0];
					$data['height']   = $size_b[1];
					$data['width_s']  = $size_s[0];
					$data['height_s'] = $size_s[1];
					if ($Trow[$this->DB->FieldOnly('imt','text')] != "") {
						$data['text'] = $Trow[$this->DB->FieldOnly('imt','text')];
					} else {
						$data['text'] = "n/a";
					}
					if ($Trow[$this->DB->FieldOnly('imt','title')] != "") {
						$data['title'] = $Trow[$this->DB->FieldOnly('imt','title')];
					} else {
						$data['title'] = "n/a";
					}

					if (strlen($data['text']) > 100) {
						$data['text_short'] = substr($data['text'],0,100)."...";
					} else {
						$data['text_short'] = $data['text'];
					}

					if (strlen($data['title']) > 20) {
						$data['title_short'] = substr($data['title'],0,20)."...";
					} else {
						$data['title_short'] = $data['title'];
					}
				}
			} else {
				$data = array();
			}
		} else {
			$data = array();
		}
		$this->DB->FreeDatabaseResult($res);
		return $data;
	}

	// Check for ImageType (FlashImages-Extension must be replaced)
	function CheckSmallImageType($file) {
		global $_SERVER;
		$img_size = @getimagesize(dirname($_SERVER['SCRIPT_FILENAME'])."/images/page/".$file);
		if ($img_size[2] == "4") {
			return constant("SCREENSHOT_FLASH");
		} else {
			if (!(file_exists(dirname($_SERVER['SCRIPT_FILENAME'])."/images/page/small/".$file))) {
				$bigFile   = dirname($_SERVER['SCRIPT_FILENAME'])."/images/page/".$file;
				$smallFile = dirname($_SERVER['SCRIPT_FILENAME'])."/images/page/small/".$file;
				$size = @getimagesize($bigFile);

				// Calculate new width, height and Position
				if ($size[0] > $size[1]) {
					$_w = (integer)constant("SCREENSHOT_WIDTH");
					$_h = ceil( ((integer)$size[1] * (integer)constant("SCREENSHOT_WIDTH")) / (integer)$size[0] );
					$_x = 0;
					$_y = ceil( ( (integer)constant("SCREENSHOT_HEIGHT") / 2 ) - ( $_h / 2 ) );
				} else if ($size[0] < $size[1]) {
					$_w = ceil( ((integer)$size[0] * (integer)constant("SCREENSHOT_HEIGHT")) / (integer)$size[1]);
					$_h = (integer)constant("SCREENSHOT_HEIGHT");
					$_x = ceil( ( (integer)constant("SCREENSHOT_WIDTH") / 2 ) - ( $_w / 2 ) );
					$_y = 0;
				} else {
					$_w = (integer)constant("SCREENSHOT_WIDTH");
					$_h = (integer)constant("SCREENSHOT_HEIGHT");
					$_x = 0;
					$_y = 0;
				}

				// Check if GD supports PNG-Files
				if ( (constant("SCREENSHOTS_USE_GD"))
				&& (function_exists("ImageCreateTrueColor"))
				&& (function_exists("ImageCreateFromJpeg"))
				&& (function_exists("ImageCreateFromPng"))
				&& (function_exists("ImageColorAllocate"))
				&& (function_exists("ImageFilledRectangle"))
				&& ( (function_exists("ImageCopyResampled")) || (function_exists("ImageCopyResized")) )
				) {
					$smallFile = preg_replace("/^(.*)\.([a-zA-Z0-9]{3,}$)/i", "\${1}.jpg", $smallFile);
					$bcol = explode(",", constant("SCREENSHOT_BACKGROUND"));

					// get the Original-Image
					if ( ($size[2] == "2") || ($size[2] == "9") || ($size[2] == "10") || ($size[2] == "11") ) {
						$img = ImageCreateFromJpeg($bigFile);
					} else if ($size[2] == "3") {
						$img = ImageCreateFromPng($bigFile);
					} else {
						$img = ImageCreate($size[0],$size[1]);
					}

					// create the new Image
					$sm = ImageCreate((integer)constant("SCREENSHOT_WIDTH"), (integer)constant("SCREENSHOT_HEIGHT"));
					$back = ImageColorAllocate($sm, (integer)$bcol[0], (integer)$bcol[1], (integer)$bcol[2]);
					ImageFilledRectangle($sm, 0, 0, (integer)constant("SCREENSHOT_WIDTH"), (integer)constant("SCREENSHOT_HEIGHT"), $back);
					ImageColorDeallocate($sm, $back);
					/*if (function_exists("ImageCopyResampled"))
					ImageCopyResampled($sm, $img, $_x, $_y, 0, 0, $_w, $_h, (integer)$size[0], (integer)$size[1]);
					else*/
					ImageCopyResized($sm, $img, $_x, $_y, 0, 0, $_w, $_h, (integer)$size[0], (integer)$size[1]);

					// Destroy the Big-Image
					ImageDestroy($img);

					// Write out the Image to the Thumbnail-File
					ImageJpeg($sm, $smallFile);
					ImageDestroy($sm);
					$backFile = "images/page/small/".preg_replace("/^(.*)\.([a-zA-Z0-9]{3,})$/i", "\\1.jpg", $file);
				} else {
					$DirName = dirname($_SERVER['SCRIPT_FILENAME'])."/images/page/small/";
					$size = @getimagesize($bigFile);
					$sx   = 180;
					$sy   = ceil($size[1]/($size[0]/180));
					$conv = exec("whereis convert | awk '{print \$2}'");
					if (trim($conv) != "") {
						$cmd1 = '[ ! -d "'.$DirName.'" ] && mkdir -p '.$DirName.'';
						$cmd3 = 'chmod a+rwx '.$DirName.'';
						$cmd2  = $conv." -size ".constant("SCREENSHOT_WIDTH")."x".constant("SCREENSHOT_HEIGHT")." xc:white";
						$cmd2 .= " -draw \"image over ".$_x.",".$_y." ".$_w.",".$_h." '".$bigFile."'\" ".$smallFile."";
						//$cmd2 = $conv.' -scale '.$sx.' '.$sy.' '.$bigFile.' '.$smallFile.'';
						$cmd4= 'chmod a+rw '.$smallFile;
						system($cmd1);
						system($cmd3);
						system($cmd2);
						system($cmd4);
					}
					$backFile = "images/page/small/".$file;
				}
				if (file_exists($smallFile)) {
					chmod($smallFile, 0777);
				}
			} else {
				$backFile = "images/page/small/".$file;
			}
			return $backFile;
		}
	}

	function GetFileData($id="0") {
		global $_SERVER;
		$match = null;
		$sql = "SELECT ".$this->DB->Table('prg').".* FROM ".$this->DB->Table('prg')." WHERE ".$this->DB->Field('prg','id')." = '".$id."'";
		$res = $this->DB->ReturnQueryResult($sql);
		if ($res) {
			$Tsql = "SELECT ".$this->DB->Table('prt').".* FROM ".$this->DB->Table('prt').",".$this->DB->Table('lan')." WHERE ".$this->DB->Field('prt','program')." = '".$id."' AND ".$this->DB->Field('lan','text')." = '".$this->LANG->getLanguageName()."' AND ".$this->DB->Field('prt','lang')." = ".$this->DB->Field('lan','id')."";
			$Tres = $this->DB->ReturnQueryResult($Tsql);
			if ($Tres) {
				$Trow = mysql_fetch_assoc($Tres);
			} else {
				$Trow = array();
			}
			$this->DB->FreeDatabaseResult($Tres);
			$row = mysql_fetch_assoc($res);

			if (($row[$this->DB->FieldOnly('prg','name')] != "") && ($row[$this->DB->FieldOnly('prg','id')] != "")) {
				$data['id']    = $row[$this->DB->FieldOnly('prg','id')];
				$data['src']   = "data/downloadfiles/".$row[$this->DB->FieldOnly('prg','name')];
				$data['fname'] = $row[$this->DB->FieldOnly('prg','program')];
				$data['file']  = $row[$this->DB->FieldOnly('prg','name')];
				$data['section']  = $row[$this->DB->FieldOnly('prg','section')];
				$data['local']    = $row[$this->DB->FieldOnly('prg','local')];
				$data['register'] = $row[$this->DB->FieldOnly('prg','register')];
				$PrgAbs = dirname($_SERVER["SCRIPT_FILENAME"])."/data/downloadfiles/".$row[$this->DB->FieldOnly('prg','program')];
				if (file_exists($PrgAbs)) {
					$data['file_abs'] = $PrgAbs;
					if ( (preg_match("/(\.)([\w]+\$)/i",$row[$this->DB->FieldOnly('prg','name')],$match)) && ($match[2] != "") ) {
						$data['type'] = $match[2];
					} else {
						$data['type'] = "unknown";
					}
					if (strlen($data['file']) > 15) {
						$data['file_short'] = substr($data['file'],0,11)."...".$data['type'];
					} else {
						$data['file_short'] = $data['file'];
					}
					$data['icotxt'] = "filetype: '".$data['type']."'";
					$data['icon']   = "data/filetypes/".$data['type'].".gif";
					$data['ico_w']  = "32";
					$data['ico_h']  = "32";
					$data['size']   = @filesize($PrgAbs);
					$data['fsize']  = $this->FormatFileSize($data['size']);
					$data['tstep']  = @filectime($PrgAbs);
					$data['date_s'] = date('Y-m-d',$data['tstep']);
					$data['date_n'] = date('j M Y',$data['tstep']);
					$data['date_h'] = date('l, j M Y',$data['tstep']);
					$data['time']   = date('H:i:s',$data['tstep']);
					if ($Trow[$this->DB->FieldOnly('prt','text')] != "") {
						$data['text'] = $Trow[$this->DB->FieldOnly('prt','text')];
					} else {
						$data['text'] = "n/a";
					}

					if (strlen($data['text']) > 90) {
						$data['text_short'] = substr($data['text'],0,90)."...";
					} else {
						$data['text_short'] = $data['text'];
					}

					if ($Trow[$this->DB->FieldOnly('prt','title')] != "") {
						$data['title'] = $Trow[$this->DB->FieldOnly('prt','title')];
					} else {
						$data['title'] = "n/a";
					}
					if (strlen($data['title']) > 20) {
						$data['title_short'] = substr($data['title'],0,20)."...";
					} else {
						$data['title_short'] = $data['title'];
					}
				}
			} else {
				$data = array();
			}
		} else {
			$data = array();
		}
		$this->DB->FreeDatabaseResult($res);
		return $data;
	}

	function ReplaceImageVariables($rep="",$img=array()) {
		$rep = preg_replace("/(\[IMAGE_ID\])/i",$img['id'],$rep);
		$rep = preg_replace("/(\[IMAGE_SRC\])/i",$img['src'],$rep);
		$rep = preg_replace("/(\[IMAGE_TEXT\])/i",$img['text'],$rep);
		$rep = preg_replace("/(\[IMAGE_TEXT_SHORT\])/i",$img['text_short'],$rep);
		$rep = preg_replace("/(\[IMAGE_TITLE\])/i",$img['title'],$rep);
		$rep = preg_replace("/(\[IMAGE_TITLE_SHORT\])/i",$img['title_short'],$rep);
		$rep = preg_replace("/(\[IMAGE_WIDTH\])/i",$img['width'],$rep);
		$rep = preg_replace("/(\[IMAGE_HEIGHT\])/i",$img['height'],$rep);
		$rep = preg_replace("/(\[IMAGE_SRC_SMALL\])/i",$img['small'],$rep);
		$rep = preg_replace("/(\[IMAGE_WIDTH_SMALL\])/i",$img['width_s'],$rep);
		$rep = preg_replace("/(\[IMAGE_HEIGHT_SMALL\])/i",$img['height_s'],$rep);
		return $rep;
	}

	function ReplaceFileVariables($rep="",$prg=array()) {
		$rep = preg_replace("/(\[PROGRAM_ID\])/i",$prg['id'],$rep);
		$rep = preg_replace("/(\[PROGRAM_LINK_ID\])/i","i=".$prg['id'],$rep);
		$rep = preg_replace("/(\[PROGRAM_DOWNLOAD_LINK\])/i","dlprg=".$prg['id'],$rep);
		$rep = preg_replace("/(\[PROGRAM_DIRECTLINK\])/i",$prg['src'],$rep);
		$rep = preg_replace("/(\[PROGRAM_FILETYPE\])/i",$prg['type'],$rep);
		$rep = preg_replace("/(\[PROGRAM_TITLE\])/i",$prg['title'],$rep);
		$rep = preg_replace("/(\[PROGRAM_TITLE_SHORT\])/i",$prg['title_short'],$rep);
		$rep = preg_replace("/(\[PROGRAM_DESCR\])/i",$prg['text'],$rep);
		$rep = preg_replace("/(\[PROGRAM_DESCR_SHORT\])/i",$prg['text_short'],$rep);
		$rep = preg_replace("/(\[PROGRAM_ICON\])/i",$prg['icon'],$rep);
		$rep = preg_replace("/(\[PROGRAM_ICON_TEXT\])/i",$prg['icotxt'],$rep);
		$rep = preg_replace("/(\[PROGRAM_ICON_WIDTH\])/i",$prg['icon_w'],$rep);
		$rep = preg_replace("/(\[PROGRAM_ICON_HEIGHT\])/i",$prg['icon_h'],$rep);
		$rep = preg_replace("/(\[PROGRAM_FILE_NAME\])/i",$prg['file'],$rep);
		$rep = preg_replace("/(\[PROGRAM_FILE_NAME_SHORT\])/i",$prg['file_short'],$rep);
		$rep = preg_replace("/(\[PROGRAM_FILE_TIME\])/i",$prg['time'],$rep);
		$rep = preg_replace("/(\[PROGRAM_FILE_SIMPLEDATE\])/i",$prg['date_s'],$rep);
		$rep = preg_replace("/(\[PROGRAM_FILE_NORMALDATE\])/i",$prg['date_n'],$rep);
		$rep = preg_replace("/(\[PROGRAM_FILE_EXTENDEDDATE\])/i",$prg['date_h'],$rep);
		$rep = preg_replace("/(\[PROGRAM_FILE_TIMESTEP\])/i",$prg['tstep'],$rep);
		$rep = preg_replace("/(\[PROGRAM_FILE_SIZE\])/i",$prg['size'],$rep);
		$rep = preg_replace("/(\[PROGRAM_FILE_SIZE_FORMATED\])/i",$prg['fsize'],$rep);
		//$rep = preg_replace("/(\[\])/i",$prg[''],$rep);
		return $rep;
	}

	function ReplaceTitleVariables($rep="",$title="") {
		$match = null;
		$rep = preg_replace("/(\[TITLE_TEXT\])/i",$title,$rep);
		// Replace TITLE_TEXT_WIDTH
		if ( (preg_match_all("/(\[TITLE_TEXT_WIDTH:)([0-9\.]+)(\])/i",$rep,$match)) && (count($match[2]) >= 1) ) {
			for ($i = 0; $i < count($match[2]); $i++) {
				$char_width   = (float)$match[2][$i];
				if (strlen($title) > 18) {
					$char_width = $char_width + 0.6;
				}
				$title_width  = (integer)(strlen($title) * $char_width);
				$rep = preg_replace("/".preg_quote($match[0][$i])."/i",$title_width,$rep);
			}
		}
		return $rep;
	}

	function ReplaceTextVariables($rep="",$txt="",$lay_type="",$lay_file="") {
		$match = null;
		// Replace some HTML-Tags in text
		$txt = $this->ReplaceSpecialTags($txt, $lay_file);

		// Check for "Line-per-Line" Text
		if (preg_match_all("/(\[TEXT_TYPE:)([\w!\]]+)?(\])(.*)(\[TEXT_TYPE:end\])/i",str_replace("\n","--LF--",$rep),$match) !== false) {
			if ((substr_count($txt,"\r") >= 1) && (substr_count($txt,"\n") <= 0)) {
				$exp = explode("\r",$txt);
			} else if ((substr_count($txt,"\n") >= 1) && (substr_count($txt,"\r") <= 0)) {
				$exp = explode("\n",$txt);
			} else {
				$exp = explode("\r\n",$txt);
			}
			$txt_new = "";
			$keys = array_keys($exp);
			for ($i = 0; $i < count($keys); $i++) {
				if (trim($exp[$keys[$i]]) != "") {
					$txt_new .= preg_replace("/(\[TEXT_TEXT\])/i",$exp[$keys[$i]],str_replace("--LF--","\n",$match[4][0]));
				}
			}
			$rep = preg_replace("/(".preg_quote(str_replace("--LF--","\n",$match[0][0]),"/").")/i",$txt_new,$rep);
		} else { // Standard-text
			//$rep = preg_replace("/(\[TEXT_TEXT\])/i",nl2br($txt),$rep);
			$rep = preg_replace("/(\[TEXT_TEXT\])/i", $txt, $rep);
		}
		// Check for Code-Formated text
		if (preg_match("/(\[TEXT_TEXT:)(.*)(\])/i",$rep,$match) !== false) {
			if ($match[2] != "") {
				// include Code-Highlightning-Definitins
				if (file_exists(dirname(__FILE__)."/php/CodeHiglightning.inc.php")) {
					require_once(dirname(__FILE__)."/php/CodeHiglightning.inc.php");
					// replace Code...
					$txt = str_replace(chr(92),chr(92).chr(92),$txt);
					$txt = str_replace("[","&#91;",str_replace("]","&#93;",$txt));
					if (is_array($Code[$lay_type])) {
						$keys = array_keys($Code[$lay_type]);
						for ($i = 0; $i < count($keys); $i++) {
							if (($Code[$lay_type][$keys[$i]] != "") && ($Replace[$lay_type][$keys[$i]] != "")) {
								$txt = preg_replace($Code[$lay_type][$keys[$i]],$Replace[$lay_type][$keys[$i]],$txt);
							}
						}
					}
					$rep = preg_replace("/(".preg_quote($match[0]).")/i",str_replace(chr(92).chr(92),chr(92),$txt),$rep);
				}
			} else {
				//$rep = preg_replace("/(".preg_quote($match[0]).")/i",nl2br($txt),$rep);
				$rep = preg_replace("/(".preg_quote($match[0]).")/i", $txt, $rep);
			}
		}
		return $rep;
	}

	function ReplaceCriticalChar($txt="") {
		$txt = str_replace("\n","--LF--",$txt);
		$txt = preg_replace("/(ä)/i","&auml;",$txt);
		$txt = preg_replace("/(Ä)/i","&Auml;",$txt);
		$txt = preg_replace("/(ö)/i","&ouml;",$txt);
		$txt = preg_replace("/(Ö)/i","&Ouml;",$txt);
		$txt = preg_replace("/(ü)/i","&uuml;",$txt);
		$txt = preg_replace("/(Ü)/i","&Uuml;",$txt);
		$txt = preg_replace("/(<)/i","&lt;",$txt);
		$txt = preg_replace("/(>)/i","&gt;",$txt);
		$txt = preg_replace("/(\")/i","&quot;",$txt);
		$txt = preg_replace("/(\(c\)|\(C\))/i","&copy;",$txt);
		$txt = preg_replace("/(\(r\)|\(R\))/i","&reg;",$txt);
		//$txt = preg_replace("/()/i","",$txt);
		$txt = str_replace("--LF--","\n",$txt);
		return $txt;
	}

	function ReplaceSpecialTags($txt="", $LayoutFile="") {
		return $txt;
		
		$tagMatch = null;
		// Replace CrLf's (because RegularExpression)
		//     $txt = str_replace("--", "\-\-", $txt);
		//     if ((substr_count($txt,"\r") >= 1) && (substr_count($txt,"\n") <= 0))
		//       $txt = str_replace("\r","--LF--",$txt);
		//     else if ((substr_count($txt,"\n") >= 1) && (substr_count($txt,"\r") <= 0))
		//       $txt = str_replace("\n","--LF--",$txt);
		//     else
		//       $txt = str_replace("\r\n","--LF--",$txt);

		// Check for b/B Tag (bold)
		/*$tmp = preg_match_all("/(\[[b|B]\])(.*?)(\[\/[b|B]\])/si", $txt, $tagMatch);
		if (count($tagMatch[0]) >= 1) {
		$tagOpen  = "<strong>";
		$tagClose = "</strong>";
		$keys = array_keys($tagMatch[0]);
		for ($i = 0; $i < count($keys); $i++) {
		$txt = preg_replace("/".preg_quote($tagMatch[0][$keys[$i]],"/")."/",$tagOpen.$tagMatch[2][$keys[$i]].$tagClose,$txt,1);
		}
		}*/
		$txt = preg_replace("/(\[b\])(.*?)(\[\/b\])/smi", "<strong>\\2</strong>", $txt);

		// Check for u/U Tag (underline)
		/*$tmp = preg_match_all("/(\[[u|U]\])(.*?)(\[\/[u|U]\])/si", $txt, $tagMatch);
		if (count($tagMatch[0]) >= 1) {
		$tagOpen  = "<span style=\"text-decoration:underline;\">";
		$tagClose = "</span>";
		$keys = array_keys($tagMatch[0]);
		for ($i = 0; $i < count($keys); $i++) {
		$txt = preg_replace("/".preg_quote($tagMatch[0][$keys[$i]],"/")."/",$tagOpen.$tagMatch[2][$keys[$i]].$tagClose,$txt,1);
		}
		}*/
		$txt = preg_replace("/(\[u\])(.*?)(\[\/u\])/smi", "<span style=\"text-decoration:underline;\">\\2</span>", $txt);

		// Check for i/I Tag (italic)
		/*$tmp = preg_match_all("/(\[[i|I]\])(.*?)(\[\/[i|I]\])/si", $txt, $tagMatch);
		if (count($tagMatch[0]) >= 1) {
		$tagOpen  = "<em>";
		$tagClose = "</em>";
		$keys = array_keys($tagMatch[0]);
		for ($i = 0; $i < count($keys); $i++) {
		$txt = preg_replace("/".preg_quote($tagMatch[0][$keys[$i]],"/")."/",$tagOpen.$tagMatch[2][$keys[$i]].$tagClose,$txt,1);
		}
		}*/
		$txt = preg_replace("/(\[i\])(.*?)(\[\/i\])/smi", "<em>\\2</em>", $txt);

		// Check for p/P Tag (paragraph)
		if (preg_match_all("/(\[[p|P])(\=)([\d]+)(.*?)(\,)([\d]+)(.*?)(\])(.*?)(\[\/[p|P]\])/smi", $txt, $tagMatch)) {
			$tagClose = "</p>";
			$keys = array_keys($tagMatch[0]);
			for ($i = 0; $i < count($keys); $i++) {
				$tagOpen  = "<p style=\"padding:0px;padding-left:".$tagMatch[3][$keys[$i]]."px;padding-right:".$tagMatch[6][$keys[$i]]."px;\">";
				$txt = preg_replace("/\\n".preg_quote($tagMatch[0][$keys[$i]],"/")."/",$tagMatch[0][$keys[$i]],$txt,1);
				$txt = preg_replace("/".preg_quote($tagMatch[0][$keys[$i]],"/")."\\n/",$tagMatch[0][$keys[$i]],$txt,1);
				$txt = preg_replace("/".preg_quote($tagMatch[0][$keys[$i]],"/")."/",$tagOpen.$tagMatch[9][$keys[$i]].$tagClose,$txt,1);
			}
		}
		// Check for list/LIST Tag (List)
		if (preg_match_all("/(\[[L|l][I|i][S|s][T|t])(\=)(.*?)(\])(.*?)(\[\/[L|l][I|i][S|s][T|t]\])/si", $txt, $tagMatch)) {
			$keys = array_keys($tagMatch[0]);
			for ($i = 0; $i < count($keys); $i++) {
				if (strlen($tagMatch[3][$keys[$i]]) > 1) {
					$tagOpen  = "<ul style=\"list-style-type:".$tagMatch[3][$keys[$i]].";margin-top:5px;\">";
					$tagClose = "</ul>";
				} else {
					switch ($tagMatch[3][$keys[$i]]) {
						case '1': $type = "decimal"; break;
						case 'a': $type = "lower-latin"; break;
						case 'A': $type = "upper-latin"; break;
						case 'i': $type = "lower-roman"; break;
						case 'I': $type = "upper-roman"; break;
					}
					$tagOpen  = "<ol style=\"list-style-type:".$type.";margin-top:5px;\">";
					$tagClose = "</ol>";
				}

				//         $tagMatch[5][$keys[$i]] = preg_replace("/\-\-LF\-\-\[\-\]/","[-]", $tagMatch[5][$keys[$i]]);
				//         $tagMatch[5][$keys[$i]] = preg_replace("/\[\-\]\-\-LF\-\-/","[-]", $tagMatch[5][$keys[$i]]);

				$stagOpen  = "<li style=\"padding-top:5px;\">";
				$stagClose = "</li>";

				$subMatch = preg_split("/(\[-\])+/si", $tagMatch[5][$keys[$i]], -1, PREG_SPLIT_NO_EMPTY);
				$subtxt = "";
				for ($si = 0; $si < count($subMatch); $si++) {
					if (trim($subMatch[$si]) != "") {
						$subtxt .= $stagOpen.trim($subMatch[$si]).$stagClose;
					}
				}
				$subtxt = $tagOpen.$subtxt.$tagClose;
				$txt = str_replace($tagMatch[0][$keys[$i]], $subtxt, $txt);

				//         for ($si = 0; $si < substr_count($tagMatch[5][$keys[$i]], "[-]"); $si++)
				//         {
				//           if ($i > 0)
				//             $tagMatch[5][$keys[$i]] = preg_replace("/(\[\-\])/",$stagClose.$stagOpen, $tagMatch[5][$keys[$i]]);
				//           else
				//             $tagMatch[5][$keys[$i]] = preg_replace("/(\[\-\])/",$stagOpen, $tagMatch[5][$keys[$i]]);
				//         }
				//
				//         $txt = preg_replace("/\-\-LF\-\-".preg_quote($tagMatch[0][$keys[$i]],"/")."/",$tagMatch[0][$keys[$i]],$txt,1);
				//         $txt = preg_replace("/".preg_quote($tagMatch[0][$keys[$i]],"/")."\-\-LF\-\-/",$tagMatch[0][$keys[$i]],$txt,1);
				//         $txt = preg_replace("/".preg_quote($tagMatch[0][$keys[$i]],"/")."/",$tagOpen.$tagMatch[5][$keys[$i]].$stagClose.$tagClose,$txt,1);
			}
		}
		// Check for Link-Tags (link=e/i,lnk)
		if (preg_match_all("/(\[[L|l][I|i][N|n][K|k])(\=)(.*?)(\,)(.*?)(\])(.*?)(\[\/[L|l][I|i][N|n][K|k]\])/si", $txt, $tagMatch)) {
			$keys = array_keys($tagMatch[0]);
			for ($i = 0; $i < count($keys); $i++) {
				switch (strtolower($tagMatch[3][$keys[$i]])) {
					case 'i':
						$tmp = explode("-", $tagMatch[5][$keys[$i]]);
						$chk_id = $tmp[0];
						while (true) {
							$sql = "SELECT ".$this->DB->Field('men','parent')." FROM ".$this->DB->Table('men')." WHERE ".$this->DB->Field('men','id')." = '".$chk_id."'";
							$res = $this->DB->ReturnQueryResult($sql);
							if ($res) {
								$row = mysql_fetch_assoc($res);
								if ($row[$this->DB->FieldOnly('men','parent')] == "0") {
									break;
								} else {
									$chk_id = $row[$this->DB->FieldOnly('men','parent')];
								}
							} else {
								$chk_id = "n/a";
								break;
							}
						}

						// Create the Link
						if ($chk_id != "n/a") {
							$tagLink = "m=".$chk_id."&s=".$tmp[0]."&mck=".$tmp[0]."&lan=".$tmp[1];
						} else {
							$tagLink = "n/a";
						}
						$link_tag = $this->LayoutStyles[$LayoutFile]['LinkLayout']['INTERNAL'];
						break;
					case 'e':
						$tagLink = $tagMatch[5][$keys[$i]];
						$link_tag = $this->LayoutStyles[$LayoutFile]['LinkLayout']['EXTERNAL'];
						break;
					default:
						$tagLink = $tagMatch[5][$keys[$i]];
						$link_tag = $this->LayoutStyles[$LayoutFile]['LinkLayout']['EXTERNAL'];
						break;
				}
				// Only replace when tagLink is available
				if ($tagLink != "n/a") {
					$link_tag = preg_replace("/\[LINK\]/",      $tagLink, $link_tag);
					$link_tag = preg_replace("/\[LINK_TEXT\]/", $tagMatch[7][$keys[$i]], $link_tag);
					$txt = preg_replace("/".preg_quote($tagMatch[0][$keys[$i]],"/")."/", $link_tag, $txt, 1);
				} else {
					$txt = preg_replace("/".preg_quote($tagMatch[0][$keys[$i]],"/")."/", $tagMatch[7][$keys[$i]], $txt, 1);
				}
			}
		}

		// ReReplace CrLf's
		//     $txt = str_replace("--LF--","\r\n",$txt);
		//     $txt = str_replace("\-\-", "--", $txt);

		return $txt;
	}

	function GetFileInformations($file="") {
		global $_SERVER;
		$match = null;
		$data = array();
		if (file_exists($file) && preg_match("/(.*)(\/)(.*)(\.)(.*)/i",$file,$match)) {
			$data['full'] = $match[0];
			$data['path'] = $match[1];
			$data['name'] = $match[3];
			$data['type'] = $match[5];
			$data['icon'] = $this->GetFileTypeIcon($match[5]);
			$data['size'] = @filesize($match[0]);
			$data['date'] = date("Y-m-d H:i:s",@filectime($match[0]));
			$data['hrsz'] = $this->FormatFileSize(@filesize($match[0]));
		}
		return $data;
	}

	function GetFileTypeIcon($ext="") {
		global $_SERVER;
		if (file_exists(dirname($_SERVER['SCRIPT_FILENAME'])."/data/filetypes/".$ext.".gif")) {
			return "data/filetypes/".$ext.".gif";
		} else {
			return "data/filetypes/unknown.gif";
		}
	}

	function FormatFileSize($size=0) {
		if ($size < (1024)) {
			$size = $size." b";
		} else if ($size < (1024*1024)) {
			$size = round(($size/(1024)),2)." kb";
		} else if ($size < (1024*1024*1024)) {
			$size = round(($size/(1024*1024)),2)." Mb";
		} else if ($size < (1024*1024*1024*1024)) {
			$size = round(($size/(1024*1024*1024)),2)." Gb";
		} else {
			$size = round(($size/(1024*1024*1024*1024)),2)." Tb";
		}
		return $size;
	}

	function ReadFTPUploadDir($type='download') {
		global $ImageExtensions;
		$match = null;
		$filelist = array();
		$od = opendir(constant('FTP_UPLOAD_DIR'));
		if ($od) {
			while (($file = readdir($od)) !== false) {
				if (($file != ".") && ($file != "..") && (!(is_dir(constant('FTP_UPLOAD_DIR').$file)))) {
					if ($type == "image") {
						if (preg_match("/\.([\w]{1,4})$/i",$file,$match) && in_array($match[1],$ImageExtensions)) {
							$filelist[$file] = constant('FTP_UPLOAD_DIR').$file;
						}
					} else {
						$filelist[$file] = constant('FTP_UPLOAD_DIR').$file;
					}
				}
			}
		}
		asort($filelist);
		return $filelist;
	}

	function GetAdminFunctionHtml($tpl="") {
		global $Template,$TemplateType,$MainMenu,$SubMenu;
		$match = null;
		$this->AdminHtmlTemplate = array();

		// get Text-Entry-Template-Section
		if (preg_match("/(\[\-\->ADMIN_FUNCTIONS\])(.*)(\[\-\-ADMIN_FUNCTIONS\])/smi", $tpl, $match)) {
			$adm_ent = $match[2];
		}

		// get SubSection 'Global'
		if (preg_match("/(\[\-\->Global\])(.*)(\[\-\-Global\])/smi", $adm_ent, $match)) {
			$this->AdminHtmlTemplate[0] = $match[2];
			$this->AdminHtmlTemplate[0] = str_replace("[MENU_LINK]",     "m=".$MainMenu."&s=".$SubMenu, $this->AdminHtmlTemplate[0]);
			$this->AdminHtmlTemplate[0] = str_replace("[TEMPLATE_TYPE]", "tpt=".$TemplateType, $this->AdminHtmlTemplate[0]);
			$this->AdminHtmlTemplate[0] = str_replace("[TEMPLATE_NAME]", "tpl=".$Template, $this->AdminHtmlTemplate[0]);
			$this->AdminHtmlTemplate[0] = str_replace("[LANGUAGE]",      "lan=".$this->LANG->getLanguageName(), $this->AdminHtmlTemplate[0]);
		}

		// get SubSection 'Functions'
		if (preg_match("/(\[\-\->Functions\])(.*)(\[\-\-Functions\])/smi", $adm_ent, $match)) {
			$this->AdminHtmlTemplate[1] = $match[2];
		}
	}

	function GetTextAdminFunctions($id) {
		global $Template,$TemplateType,$MainMenu,$SubMenu;
		$userCheck = pCheckUserData::getInstance();
		if (($userCheck->checkAccess("CONTENT")) && (!($this->CreateStaticFiles))) {
			$AdminMenu = $this->AdminHtmlTemplate[1];
			$AdminMenu = str_replace("[MENU_LINK]",     "m=".$MainMenu."&s=".$SubMenu, $AdminMenu);
			$AdminMenu = str_replace("[TEMPLATE_TYPE]", "tpt=".$TemplateType, $AdminMenu);
			$AdminMenu = str_replace("[TEMPLATE_NAME]", "tpl=".$Template, $AdminMenu);
			$AdminMenu = str_replace("[LANGUAGE]",      "lan=".$this->LANG->getLanguageName(), $AdminMenu);
			$AdminMenu = str_replace("[TEXT_ID]",       "chi=".$id, $AdminMenu);

			$AdminMenu = '<div id="adm_mdiv_'.$id.'" style="padding:0px;border-top:1px solid rgb(0,111,153);">'.$AdminMenu.'</div>';
			$AdminMenu = str_replace("\"", "&#34;", $AdminMenu);
			$AdminMenu = str_replace("'", "&#39;", $AdminMenu);

			$AdminMenu = '<table id="tbl_adm_'.$id.'" cellpadding="0" cellspacing="0" style="margin-left:5px;width:40px;min-height:30px;text-align:center;vertical-align:center;border:1px solid rgb(0,111,153);background-color:rgb(240,250,255);">
				<colgroup>
					<col style="min-width:40px;width:40px;max-width:40px;">
				</colgroup>
				<tr>
					<td style="text-align:center;padding:0px;">
						<img src="images/design/admin/showAdminMenu.gif" onClick="showMenu_'.$id.'(this);" onMouseOver="this.style.cursor=\'pointer\';" onMouseOut="this.style.cursor=\'normal\';" title="show Admin-Menu" alt="admin" style="width:24px;height:24px;border:0px solid black;" />
						<input type="hidden" name="adm_men_'.$id.'" id="adm_men_'.$id.'" value="'.$AdminMenu.'" />
						<div id="adm_mdiv_'.$id.'" style="padding:0px;border-top:0px solid rgb(0,111,153);font-size:1px;"></div>
						<script type="text/javascript">
							<!--
								var show'.$id.' = false;
								var data'.$id.' = \'\';

								function showMenu_'.$id.'(obj)
								{
									if (show'.$id.' == false)
									{
										show'.$id.' = true;
										obj.src = \'images/design/admin/hideAdminMenu.gif\';
										data'.$id.' = document.getElementById(\'adm_mdiv_'.$id.'\').innerHTML;
										document.getElementById(\'adm_mdiv_'.$id.'\').innerHTML = document.getElementById(\'adm_men_'.$id.'\').value;
										document.getElementById(\'adm_mdiv_'.$id.'\').style.borderTop = \'1px solid rgb(0,111,153)\';
									}
									else
									{
										show'.$id.' = false;
										obj.src = \'images/design/admin/showAdminMenu.gif\';
										document.getElementById(\'adm_mdiv_'.$id.'\').innerHTML = data'.$id.';
										document.getElementById(\'adm_mdiv_'.$id.'\').style.borderTop = \'0px solid rgb(0,111,153)\';
										data'.$id.' = \'\';
									}
								}
							//-->
						</script>
					</td>
				</tr>
			</table>';

			return $AdminMenu;
		} else {
			return "";
		}
	}

	function MoveText($id, $direction) {
		$sql = "SELECT ".$this->DB->Field('txt','id').",".$this->DB->Field('txt','sort').",".$this->DB->Field('txt','menu')." FROM ".$this->DB->Table('txt')." WHERE ".$this->DB->Field('txt','id')." = '".$id."'";
		$res = $this->DB->ReturnQueryResult($sql);
		if ($res) {
			$row = mysql_fetch_assoc($res);
			$this->DB->FreeDatabaseResult($res);
			// Get lower text
			$LOsql = "SELECT ".$this->DB->Field('txt','id').",".$this->DB->Field('txt','sort')." FROM ".$this->DB->Table('txt')." WHERE ".$this->DB->Field('txt','sort')." < '".$row[$this->DB->FieldOnly('txt','sort')]."' AND ".$this->DB->Field('txt','menu')." = '".$row[$this->DB->FieldOnly('txt','menu')]."'  ORDER BY ".$this->DB->Field('txt','sort')." DESC";
			$LOres = $this->DB->ReturnQueryResult($LOsql);
			if ($LOres) {
				$LOrow = mysql_fetch_assoc($LOres);
			} else {
				$LOrow = array();
			}
			$this->DB->FreeDatabaseResult($LOres);
			// Get Upper text
			$UPsql = "SELECT ".$this->DB->Field('txt','id').",".$this->DB->Field('txt','sort')." FROM ".$this->DB->Table('txt')." WHERE ".$this->DB->Field('txt','sort')." > '".$row[$this->DB->FieldOnly('txt','sort')]."' AND ".$this->DB->Field('txt','menu')." = '".$row[$this->DB->FieldOnly('txt','menu')]."' ORDER BY ".$this->DB->Field('txt','sort')." ASC";
			$UPres = $this->DB->ReturnQueryResult($UPsql);
			if ($UPres) {
				$UProw = mysql_fetch_assoc($UPres);
			} else {
				$UProw = array();
			}
			$this->DB->FreeDatabaseResult($UPres);
			// Move text up
			if ($direction == 'up') {
				if (count($LOrow) > 0) {
					$sql = "UPDATE ".$this->DB->Table('txt')." SET ".$this->DB->FieldOnly('txt','sort')." = '".$LOrow[$this->DB->FieldOnly('txt','sort')]."' WHERE ".$this->DB->FieldOnly('txt','id')." = '".$row[$this->DB->FieldOnly('txt','id')]."'";
					$this->DB->ReturnQueryResult($sql);
					$sql = "UPDATE ".$this->DB->Table('txt')." SET ".$this->DB->FieldOnly('txt','sort')." = '".$row[$this->DB->FieldOnly('txt','sort')]."'   WHERE ".$this->DB->FieldOnly('txt','id')." = '".$LOrow[$this->DB->FieldOnly('txt','id')]."'";
					$this->DB->ReturnQueryResult($sql);
				}
			}
			// Move text down
			if ($direction == 'down') {
				if (count($UProw) > 0) {
					$sql = "UPDATE ".$this->DB->Table('txt')." SET ".$this->DB->FieldOnly('txt','sort')." = '".$UProw[$this->DB->FieldOnly('txt','sort')]."' WHERE ".$this->DB->FieldOnly('txt','id')." = '".$row[$this->DB->FieldOnly('txt','id')]."'";
					$this->DB->ReturnQueryResult($sql);
					$sql = "UPDATE ".$this->DB->Table('txt')." SET ".$this->DB->FieldOnly('txt','sort')." = '".$row[$this->DB->FieldOnly('txt','sort')]."'   WHERE ".$this->DB->FieldOnly('txt','id')." = '".$UProw[$this->DB->FieldOnly('txt','id')]."'";
					$this->DB->ReturnQueryResult($sql);
				}
			}
		}
	}

	function ShowLayoutWindow($Id="0") {
		global $_GET,$MainMenu,$SubMenu;
		$match = null;
		// Get Templates and Layouts
		$this->GetAllLayoutFiles();
		$LayoutFile = $this->LayoutPath.$_GET['lay_file'].".html";
		// Get First file from Database if called file does not exist
		if (!(@file_exists($LayoutFile))) {
			$sql = "SELECT ".$this->DB->Field('lay','file')." FROM ".$this->DB->Table('lay')." ORDER BY ".$this->DB->Field('lay','text')."";
			$res = $this->DB->ReturnQueryResult($sql);
			if ($res) {
				$row = mysql_fetch_assoc($res);
			}
			$this->DB->FreeDatabaseResult($res);
			$LayoutFile = $this->LayoutPath.$row[$this->DB->FieldOnly('lay','file')].".html";
		}
		// Get Layout styles
		$this->ParseTextTemplateFile($LayoutFile);
		$tpl = $this->GetTextTemplateStyles($Id,$LayoutFile);
		// Set lay_* Variables for Selecting
		$lay_file    = $_GET['lay_file'];
		$lay_global  = $tpl['GlobalLayout'][1];
		$lay_title   = $tpl['TitleLayout'][1];
		$lay_text    = $tpl['TextLayout'][1];
		$lay_picture = $tpl['ImagePosition'][1];
		// Get main HTML-Form and set Replacement-Variables to zero-length
		$main  = $SESS['admin']->GetHtml('ChangeText','LayoutScreen');
		throw new DWPException('FIX: $SESS["admin"]',99);
		$lay_file_list    = "";
		$lay_global_list  = "";
		$lay_title_list   = "";
		$lay_text_list    = "";
		$lay_picture_list = "";
		// Parse TemplateFileChooser
		if (preg_match("/(\[-->FILE_LIST\])([\w\W]+)(\[--FILE_LIST\])/smi", $main, $match)) {
			if (count($this->LayoutFileList) > 0) {
				$keys = array_keys($this->LayoutFileList);
				for ($i = 0; $i < count($keys); $i++) {
					$tmp_list = $match[2];
					$tmp_list = str_replace("[FILE_NAME]",        $keys[$i], $tmp_list);
					$tmp_list = str_replace("[FILE_DESCRIPTION]", $this->LayoutFileList[$keys[$i]], $tmp_list);
					if ($keys[$i] == $lay_file) {
						$tmp_list = preg_replace("/(<option)/smi", "\\1 selected=\"selected\"", $tmp_list);
					}
					$lay_file_list .= $tmp_list;
				}
			}
			$main = str_replace($match[0], $lay_file_list, $main);
		}

		// Parse global-Layout
		if (preg_match("/(\[-->GLOBAL_LAYOUT_LIST\])([\w\W]+)(\[--GLOBAL_LAYOUT_LIST\])/smi", $main, $match)) {
			if (count($this->LayoutComments[$LayoutFile]['GlobalLayout']) > 0) {
				$keys = array_keys($this->LayoutComments[$LayoutFile]['GlobalLayout']);
				for ($i = 0; $i < count($keys); $i++) {
					$tmp_list = $match[2];
					$tmp_list = str_replace("[NAME]",        $keys[$i], $tmp_list);
					$tmp_list = str_replace("[DESCRIPTION]", $this->LayoutComments[$LayoutFile]['GlobalLayout'][$keys[$i]], $tmp_list);
					if ($keys[$i] == $lay_global) {
						$tmp_list = preg_replace("/(<option)/smi", "\\1 selected=\"selected\"", $tmp_list);
					}
					$lay_global_list .= $tmp_list;
				}
			}
			$main = str_replace(str_replace("--LF--","\n",$match[0]),$lay_global_list,$main);
		}

		// Parse Title-Layout
		if (preg_match("/(\[\-\->TITLE_LAYOUT_LIST\])([\w\W]+)(\[\-\-TITLE_LAYOUT_LIST\])/smi", $main, $match)) {
			if (count($this->LayoutComments[$LayoutFile]['TitleLayout']) > 0) {
				$keys = array_keys($this->LayoutComments[$LayoutFile]['TitleLayout']);
				for ($i = 0; $i < count($keys); $i++) {
					$tmp_list = $match[2];
					$tmp_list = str_replace("[NAME]",        $keys[$i], $tmp_list);
					$tmp_list = str_replace("[DESCRIPTION]", $this->LayoutComments[$LayoutFile]['TitleLayout'][$keys[$i]], $tmp_list);
					if ($keys[$i] == $lay_title) {
						$tmp_list = preg_replace("/(<option)/smi", "\\1 selected=\"selected\"", $tmp_list);
					}
					$lay_title_list .= $tmp_list;
				}
			}
			$main = str_replace($match[0], $lay_title_list, $main);
		}

		// Parse Text-Layout
		if (preg_match("/(\[\-\->TEXT_LAYOUT_LIST\])([\w\W]+)(\[\-\-TEXT_LAYOUT_LIST\])/smi", $main, $match)) {
			if (count($this->LayoutComments[$LayoutFile]['TextLayout']) > 0) {
				$keys = array_keys($this->LayoutComments[$LayoutFile]['TextLayout']);
				for ($i = 0; $i < count($keys); $i++) {
					$tmp_list = $match[2];
					$tmp_list = str_replace("[NAME]",        $keys[$i], $tmp_list);
					$tmp_list = str_replace("[DESCRIPTION]", $this->LayoutComments[$LayoutFile]['TextLayout'][$keys[$i]], $tmp_list);
					if ($keys[$i] == $lay_text) {
						$tmp_list = preg_replace("/(<option)/smi", "\\1 selected=\"selected\"", $tmp_list);
					}
					$lay_text_list .= $tmp_list;
				}
			}
			$main = str_replace($match[0], $lay_text_list, $main);
		}

		// Parse Picture-Layout
		if (preg_match("/(\[\-\->PICTURE_LAYOUT_LIST\])([\w\W]+)(\[\-\-PICTURE_LAYOUT_LIST\])/smi", $main, $match)) {
			if (count($this->LayoutComments[$LayoutFile]['ImagePosition']) > 0) {
				$keys = array_keys($this->LayoutComments[$LayoutFile]['ImagePosition']);
				for ($i = 0; $i < count($keys); $i++) {
					$tmp_list = $match[2];
					$tmp_list = str_replace("[NAME]",        $keys[$i], $tmp_list);
					$tmp_list = str_replace("[DESCRIPTION]", $this->LayoutComments[$LayoutFile]['ImagePosition'][$keys[$i]], $tmp_list);
					if ($keys[$i] == $lay_picture) {
						$tmp_list = preg_replace("/(<option)/smi", "\\1 selected=\"selected\"", $tmp_list);
					}
					$lay_picture_list .= $tmp_list;
				}
			}
			$main = str_replace(str_replace("--LF--","\n",$match[0]),$lay_picture_list,$main);
		}

		// Replace some global Variables
		$main = str_replace("[CHANGE_TEXT_ID]", $Id, $main);
		$main = str_replace("\[MAIN_MENU_ID]", $MainMenu, $main);
		$main = str_replace("[SUB_MENU_ID]", $SubMenu, $main);
		$main = str_replace("[LANGUAGE]", $this->LANG->getLanguageName(), $main);
		// Replace Preview-Variables
		$main = str_replace("[LAYOUT_FILE]", $lay_file, $main);
		$main = str_replace("[LAYOUT_GLOBAL]", $lay_global, $main);
		$main = str_replace("[LAYOUT_TITLE]", $lay_title, $main);
		$main = str_replace("[LAYOUT_TEXT]", $lay_text, $main);
		$main = str_replace("[LAYOUT_PICTURE]", $lay_picture, $main);
		$this->Content = $main;
	}

	function ShowLinkWindow() {
		global $_GET,$MainMenu,$SubMenu;
		$match = null;
		$match_1 = null;
		$match_2 = null;
		$match_3 = null;
		$MenuEntry_1 = '';
		$MenuEntry_2 = '';
		$MenuEntry_3 = '';
		$main  = $SESS['admin']->GetHtml('ChangeText','LinkChooser');
		throw new DWPException('FIX: $SESS["admin"]', 99);
		$menu = "";

		// Getting the MenuEntries
		if (preg_match("/(\[\-\-\>MENU\])([\w\W]+)(\[\-\-MENU\])/smi", $main, $match) !== false) {
			// getting the Menu-Entries
			if (preg_match("/(\[\-\-\>MENU_ENTRY_1\])([\w\W]+)(\[\-\-MENU_ENTRY_1\])/smi", $match[2], $match_1)) {
				$MenuEntry_1 = $match_1[2];
			}
			if (preg_match("/(\[\-\-\>MENU_ENTRY_2\])([\w\W]+)(\[\-\-MENU_ENTRY_2\])/smi", $match[2], $match_2)) {
				$MenuEntry_2 = $match_2[2];
			}
			if (preg_match("/(\[\-\-\>MENU_ENTRY_ACTIVE\])([\w\W]+)(\[\-\-MENU_ENTRY_ACTIVE\])/smi", $match[2], $match_3)) {
				$MenuEntry_3 = $match_3[2];
			}

			// Replace the menuentries with a parameter (in $main and $match[0])
			$main = preg_replace("/".preg_quote($match_1[0], "/")."/", "[MENU]", $main);
			$match[0] = preg_replace("/".preg_quote($match_1[0], "/")."/", "[MENU]", $match[0]);
			$match[2] = preg_replace("/".preg_quote($match_1[0], "/")."/", "[MENU]", $match[2]);

			$main = preg_replace("/".preg_quote($match_2[0], "/")."/", "", $main);
			$match[0] = preg_replace("/".preg_quote($match_2[0], "/")."/", "", $match[0]);
			$match[2] = preg_replace("/".preg_quote($match_2[0], "/")."/", "", $match[2]);

			$main = preg_replace("/".preg_quote($match_3[0], "/")."/", "", $main);
			$match[0] = preg_replace("/".preg_quote($match_3[0], "/")."/", "", $match[0]);
			$match[2] = preg_replace("/".preg_quote($match_3[0], "/")."/", "", $match[2]);

			// Checking for a Menu- and SubMenu-Id
			if (isset($_GET['l_mid'])) {
				$mid = $_GET['l_mid'];
			} else {
				$mid = "0";
			}

			// Getting all Menus recursiv
			if ($mid != "0") {
				$parent_list = $mid;
				$cur_menu = $mid;
				while ($cur_menu != "0") {
					$tmp_list = explode(",", $parent_list);
					$sql  = "SELECT ".$this->DB->Table('men').".*";
					$sql .= " FROM ".$this->DB->Table('men')."";
					$sql .= " WHERE ".$this->DB->Field('men','id')." = '".$tmp_list[0]."'";
					$res = $this->DB->ReturnQueryResult($sql);
					if ($res) {
						$row = mysql_fetch_assoc($res);
						$parent_list = $row[$this->DB->FieldOnly('men','parent')].",".$parent_list;
						$cur_menu = $row[$this->DB->FieldOnly('men','parent')];
					} else {
						$cur_menu = "0";
					}
				}
			} else {
				$parent_list = "0";
			}
			$menu_entry = $this->GetLinkWindowMenus(explode(",",$parent_list), $mid, $MenuEntry_1, $MenuEntry_2, $MenuEntry_3, 0, 0);

			// Replace the Menu-Section with the parsed Menu
			$menu = str_replace("[MENU]", $menu_entry[1], $match[2]);
		}

		// Append the menu to the template
		$main = str_replace($match[0], $menu, $main);

		// Replace MenuLink which should be inserted in textfield
		$int_link = $mid."-".$this->LANG->getLanguageName();
		$main = str_replace("[INTERNAL_LINK_STRING]", $int_link, $main);

		$this->Content = $main;
	}

	function ShowTextCorrectWindow() {
		global $_GET, $MainMenu, $SubMenu;
		$match = null;
		$main  = $SESS['admin']->GetHtml('ChangeText','TextCorrection');
		throw new DWPException('FIX: $SESS["admin"]', 99);

		// Read out the CORR_ values (HTML for the correction-Selection)
		if (preg_match("/(\[--\>CORR_WORD\])(.*)(\[--CORR_WORD\])/smi", $main, $match)) {
		$_corrInputField = $match[2];
		}
		$main = preg_replace("/(\[--\>CORR_WORD\].*\[--CORR_WORD\])/smi", "[CORRECTION_INPUT_LIST]", $main);

		if (preg_match("/(\[--\>CORR_WORD_VALUE\])(.*)(\[--CORR_WORD_VALUE\])/smi", $main, $match)) {
		$_corrInputValue = $match[2];
		}
		$main = preg_replace("/(\[--\>CORR_WORD_VALUE\].*\[--CORR_WORD_VALUE\])/smi", "", $main);

		if (preg_match("/(\[--\>CORR_WORD_LIST\])(.*)(\[--CORR_WORD_LIST\])/smi", $main, $match)) {
		$_corrInputList = $match[2];
		}
		$main = preg_replace("/(\[--\>CORR_WORD_LIST\].*\[--CORR_WORD_LIST\])/smi", "", $main);

		// replace main variables
		$main = str_replace("[MAIN_MENU_ID]", $MainMenu, $main);
		$main = str_replace("[SUB_MENU_ID]",  $SubMenu,  $main);
		$main = str_replace("[LANGUAGE]",     $this->LANG->getLanguageName(), $main);

		// Set the text to correct (textarea)
		$main = str_replace("[TXT_CORRECTED_VALUE]", urldecode($_GET['ctext']), $main);

		// get all Suported Languages
		$_pspellConfig = shell_exec("which pspell-config");
		if (trim($_pspellConfig) != '') {
			$_pspellConfigDir  = shell_exec(trim($_pspellConfig)." pkgdatadir");
			$_pspellConfigList = shell_exec('ls '.trim($_pspellConfigDir).'');
			$_pspellConfigList = explode("\n", $_pspellConfigList);
			$_availableLanguages = array();
			foreach($_pspellConfigList as $v) {
				if (preg_match("/^(\w\w)\.(.*)$/i", trim($v), $match)) {
					$_availableLanguages[] = $match[1];
				}
			}
		}

		// Get the Language-Short from Database
		$sql = "SELECT * FROM ".$this->DB->Table('ln')." ORDER BY ".$this->DB->Field('ln','short')."";
		$res = $this->DB->ReturnQueryResult($sql);
		$_tmp = $_availableLanguages;
		$_availableLanguages = array();
		if ($res) {
			while ($row = mysql_fetch_assoc($res)) {
				$_lnShort = $row[$this->DB->FieldOnly('lan','short')];
				$_lnLong  = $row[$this->DB->FieldOnly('lan','text')];
				if (in_array($_lnShort, $_tmp)) {
					if (($this->SelectedLanguage == $_lnLong) || (trim($_GET['chkLan']) == $_lnLong)) {
						$_checkLanguage = $_lnShort;
					}
					$_availableLanguages[$_lnShort] = $_lnLong;
				}
			}
		}

		// Check if the Spellchecking-Languages are avaliable in Database
		if (count($_availableLanguages) <= 0) {
			foreach ($_tmp as $v) {
				$_availableLanguages[$v] = $v;
			}
			$k = array_keys($_availableLanguages);
			$_checkLanguage = $_availableLanguages[$k[0]];
		}
		unset($_tmp);

		// Create the Language-Chooser

		// Initialize the SpellChecken (aspell/pspell)
		$pspell_config = pspell_config_create($_checkLanguage);
		pspell_config_personal($pspell_config, constant("PERSONAL_DICTIONARY"));
		$pspell_link = pspell_new_config($pspell_config);

		// Correct the text word-by-word
		$_correctionJavascriptlist = '';
		$_correctionList = '';
		$_failed_words = array();
		$_GET['ctext'] = str_replace("\n", " ", $_GET['ctext']);
		$_tmp_txt = explode(" ", trim($_GET['ctext']));
		for ($i = 0; $i < count($_tmp_txt); $i++) {
			$_tmp_chk = str_replace(".", " ", trim($_tmp_txt[$i]) );
			$_tmp_chk = str_replace(":", " ", $_tmp_chk);
			$_tmp_chk = str_replace(";", " ", $_tmp_chk);
			$_tmp_chk = str_replace(",", " ", $_tmp_chk);
			//$_tmp_chk = str_replace("-", " ", $_tmp_chk);
			//$_tmp_chk = str_replace("_", " ", $_tmp_chk);
			$_tmp_chk = str_replace("!", " ", $_tmp_chk);
			$_tmp_chk = str_replace("?", " ", $_tmp_chk);
			$_tmp_chk = str_replace("(", " ", $_tmp_chk);
			$_tmp_chk = str_replace(")", " ", $_tmp_chk);
			$_tmp_chk = str_replace("\""," ", $_tmp_chk);
			$_tmp_chk = str_replace("'", " ", $_tmp_chk);
			$_tmp_chk = preg_replace("/(&#[\d]+;)/i", " ", $_tmp_chk);
			$_tmp_chk = preg_replace("/(\[[^\[\]]+\])/i", "", $_tmp_chk);

			if (!(pspell_check($pspell_link, trim($_tmp_chk)))) {
				$_failed_words[][0] = $_tmp_chk;
				$_failed_words[count($_failed_words)-1][1] = $_tmp_txt[$i];
				$tmp_corr = '';
				$_suggest = pspell_suggest($pspell_link, trim($_tmp_chk));
				foreach ($_suggest as $sug) {
					$tmp_corr .= str_replace("[CORR_VALUE]",   $sug, $_corrInputList);
				}
				$tmp_corr = str_replace("[CORR_WORD_LIST]",  $tmp_corr, $_corrInputValue);
				$tmp_corr = str_replace("\"", "&#34;",       $tmp_corr);
				$tmp_corr = str_replace("'",  "&#39;",       $tmp_corr);

				$_correctionList .= str_replace("[CORR_WORD_VALUE]", $tmp_corr,       $_corrInputField);
				$_correctionList  = str_replace("[CORR_ORIG_VALUE]", trim($_tmp_chk), $_correctionList);
				$_correctionList  = str_replace("[CORR_ID]",         $i,              $_correctionList);
				$_correctionJavascriptlist .= ','.$i;
			}
		}
		// strip first comma from $_correctionJavascriptlist
		$_correctionJavascriptlist = substr($_correctionJavascriptlist, 1);

		// insert the Correction-Inputs into the HTML
		$main = str_replace("[CORRECTION_INPUT_LIST]", $_correctionList, $main);

		// Replace [CORR_ID_LIST] with $_correctionJavascriptlist
		$main = str_replace("[CORR_ID_LIST]", $_correctionJavascriptlist, $main);

		/*  Ersetzen durch eine JS-Funktion welche immer die aktuelle Position des Wortes speichert
		und so weiss wo an replaced werden soll
		$jsFinalReplace = 'var finalReplace = new Array();
		finalReplace["cor_1_Lizenz"] = "License";';
		$main = str_replace("[DO_FINAL_REPLACE]", $jsFinalReplace, $main);
		*/

		$this->Content = $main;
	}

	function GetLinkWindowMenus($parent_list=array("0"), $sel_men=0, $men1="", $men2="", $men3="", $cnt=0, $blank=0)
	{
		// Getting all Menus recursiv
		if ((!(is_array($parent_list))) || (count($parent_list) <= 0)) {
			$parent_list = array("0");
		}

		// getting the Menu-entries
		$menu = array(0 => $cnt, 1 => "");
		$i = $blank;
		//for ($i = 0; $i < count($parent_list); $i++)
		{
			$mid  = $parent_list[$i];
			$sql  = "SELECT DISTINCT ".$this->DB->Table('men').".*, ".$this->DB->Field('mtx','text')."";
			$sql .= " FROM ".$this->DB->Table('men').", ".$this->DB->Table('mtx').", ".$this->DB->Table('lan')."";
			$sql .= " WHERE ".$this->DB->Field('men','id')." = ".$this->DB->Field('mtx','menu')."";
			$sql .= " AND ".$this->DB->Field('men','parent')." = '".$mid."'";
			if ($mid != "0") {
				$sql .= " AND ".$this->DB->Field('mtx','lang')." = ".$this->DB->Field('lan','id')."";
				$sql .= " AND ".$this->DB->Field('lan','text')." = '".$this->LANG->getLanguageName()."'";
			}
			$sql .= " ORDER BY ".$this->DB->Field('men','parent')."";
			$res = $this->DB->ReturnQueryResult($sql);

			if ($res) {
				while (($row = mysql_fetch_assoc($res)) !== false) {
					// Geting the HTML-Line for curent Menu
					if ($cnt%2 == 0) {
						$menu_entry = $men1;
					} else {
						$menu_entry = $men2;
					}
					if ($row[$this->DB->FieldOnly('men','id')] == $sel_men) {
						$menu_entry = $men3;
					}

					// Count up the Line-Counter variable
					$cnt++;
					// getting current Menu-ID
//					$sid = $row[$this->DB->FieldOnly('men','parent')];
					$id  = $row[$this->DB->FieldOnly('men','id')];

					// Replace any Variables in current Menu-Line
					$menu_text  = $row[$this->DB->FieldOnly('mtx','text')];
					$menu_link  = "index.php?tpt=page&tpl=blank&lang=".$this->SelectedLanguage."&act=link&l_mid=".$id."";
					$menu_blank = "";
					for ($i1 = 0; $i1 < $blank; $i1++) {
						$menu_blank .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
					}
					$menu_entry = str_replace("[MENU_TEXT]",   $menu_text, $menu_entry);
					$menu_entry = str_replace("[MENU_LINK]",   $menu_link, $menu_entry);
					$menu_entry = str_replace("[MENU_SPACER]", $menu_blank, $menu_entry);
					$menu[1] .= $menu_entry;

					// Getting DubMenus from current menu (if required)
					if (in_array($id, $parent_list)) {
						$tmp = $this->GetLinkWindowMenus($parent_list, $sel_men, $men1, $men2, $men3, $cnt, ($blank+1));
						$menu[1] .= $tmp[1];
						$menu[0] = $tmp[0];
						$cnt = $cnt + $menu[0];
					}
				}
			}
			$this->DB->FreeDatabaseResult($res);
		}
		return $menu;
	}

	function EditTextEntry($id="0") {
		global $_POST,$ChangeAction,$MainMenu,$SubMenu;
		if ((isset($_POST['txt_title'])) || (isset($_POST['txt_text']))) {
			// Chech for MenuId
			if ($SubMenu == "0") {
				$MenuId = $MainMenu;
			} else {
				$MenuId = $SubMenu;
			}
			// Check for LayoutFile
			$sql = "SELECT ".$this->DB->Field('lay','id')." FROM ".$this->DB->Table('lay')." WHERE ".$this->DB->Field('lay','file')." = '".$_POST['lay_file']."'";
			$res = $this->DB->ReturnQueryResult($sql);
			if ($res) {
				$lay = mysql_fetch_assoc($res);
			} else {
				$lay = array($this->DB->FieldOnly('lay','id') => "0");
			}
			$lay = $lay[$this->DB->FieldOnly('lay','id')];
			$this->DB->FreeDatabaseResult($res);
			// Get some Parameters by a new text
			if ($ChangeAction != "edit") {
				// Check for next text-order if it's a new text
				$sql = "SELECT MAX(".$this->DB->Field('txt','sort').") AS ".$this->DB->FieldOnly('txt','sort')." FROM ".$this->DB->Table('txt')." WHERE ".$this->DB->Field('txt','menu')." = '".$MenuId."'";
				$res = $this->DB->ReturnQueryResult($sql);
				if ($res) {
					$sort = mysql_fetch_assoc($res);
				} else {
					$sort = array($this->DB->FieldOnly('txt','sort') => "0");
				}
				$sort = ((integer)$sort[$this->DB->FieldOnly('txt','sort')] + 1);
			}
			// Check for Language
			$sql = "SELECT ".$this->DB->Field('lan','id')." FROM ".$this->DB->Table('lan')." WHERE ".$this->DB->Field('lan','text')." = '".$this->LANG->getLanguageName()."'";
			$res = $this->DB->ReturnQueryResult($sql);
			if ($res) {
				$lang = mysql_fetch_assoc($res);
			} else {
				$lang = array($this->DB->FieldOnly('lan','id') => "0");
			}
			$lang = $lang[$this->DB->FieldOnly('lan','id')];
			$this->DB->FreeDatabaseResult($res);
			// Create Insert-Query
			if ($ChangeAction == "edit") {
				$sql = "UPDATE ";
			} else {
				$sql = "INSERT INTO ";
			}
			$sql .= $this->DB->Table('txt')." SET ";
			$sql .= $this->DB->FieldOnly('txt','title')." = '".$_POST['txt_title']."', ";
			$sql .= $this->DB->FieldOnly('txt','text')." = '".$_POST['txt_text']."', ";
			$sql .= $this->DB->FieldOnly('txt','image')." = '".$_POST['txt_picfile']."', ";
			$sql .= $this->DB->FieldOnly('txt','layout')." = '".$lay."'";
			if ($ChangeAction != "edit") {
				$sql .= ", ".$this->DB->FieldOnly('txt','menu')." = '".$MenuId."'";
				$sql .= ", ".$this->DB->FieldOnly('txt','lang')." = '".$lang."'";
				$sql .= ", ".$this->DB->FieldOnly('txt','sort')." = '".$sort."'";
			} else {
				$sql .= " WHERE ".$this->DB->FieldOnly('txt','id')." = '".$id."'";
			}
			$res = $this->DB->ReturnQueryResult($sql);
			// Get last Insert-ID for insert/update new Template-Styles
			if ($ChangeAction != "edit") {
				$id   = mysql_insert_id();
			}
			// Insert / Update Text-Styles
			// GlobalLayout
			$sql = "SELECT ".$this->DB->Field('sty','id')." FROM ".$this->DB->Table('sty')." WHERE ".$this->DB->Field('sty','para')." = 'GlobalLayout' AND ".$this->DB->Field('sty','text')." = '".$id."'";
			$res = $this->DB->ReturnQueryResult($sql);
			if ($res) {
				$row  = mysql_fetch_assoc($res);
				$sql  = "UPDATE ".$this->DB->Table('sty')." SET ";
				$sql .= $this->DB->FieldOnly('sty','class')." = '".$_POST['lay_global']."'";
				$sql .= " WHERE ".$this->DB->FieldOnly('sty','id')." = '".$row[$this->DB->FieldOnly('sty','id')]."'";
			} else {
				$sql  = "INSERT INTO ".$this->DB->Table('sty')." SET ";
				$sql .= $this->DB->FieldOnly('sty','text')." = '".$id."', ";
				$sql .= $this->DB->FieldOnly('sty','para')." = 'GlobalLayout', ";
				$sql .= $this->DB->FieldOnly('sty','class')." = '".$_POST['lay_global']."'";
			}
			if ($_POST['lay_global'] != "") {
				$this->DB->FreeDatabaseResult($res);
			}
			$res  = $this->DB->ReturnQueryResult($sql);
			// Title-Layout
			$sql = "SELECT ".$this->DB->Field('sty','id')." FROM ".$this->DB->Table('sty')." WHERE ".$this->DB->Field('sty','para')." = 'TitleLayout' AND ".$this->DB->Field('sty','text')." = '".$id."'";
			$res = $this->DB->ReturnQueryResult($sql);
			if ($res) {
				$row  = mysql_fetch_assoc($res);
				$sql  = "UPDATE ".$this->DB->Table('sty')." SET ";
				$sql .= $this->DB->FieldOnly('sty','class')." = '".$_POST['lay_title']."'";
				$sql .= " WHERE ".$this->DB->FieldOnly('sty','id')." = '".$row[$this->DB->FieldOnly('sty','id')]."'";
			} else {
				$sql  = "INSERT INTO ".$this->DB->Table('sty')." SET ";
				$sql .= $this->DB->FieldOnly('sty','text')." = '".$id."', ";
				$sql .= $this->DB->FieldOnly('sty','para')." = 'TitleLayout', ";
				$sql .= $this->DB->FieldOnly('sty','class')." = '".$_POST['lay_title']."'";
			}
			if ($_POST['lay_title'] != "") {
				$this->DB->FreeDatabaseResult($res);
			}
			$res  = $this->DB->ReturnQueryResult($sql);
			// Text-Layout
			$sql = "SELECT ".$this->DB->Field('sty','id')." FROM ".$this->DB->Table('sty')." WHERE ".$this->DB->Field('sty','para')." = 'TextLayout' AND ".$this->DB->Field('sty','text')." = '".$id."'";
			$res = $this->DB->ReturnQueryResult($sql);
			if ($res) {
				$row  = mysql_fetch_assoc($res);
				$sql  = "UPDATE ".$this->DB->Table('sty')." SET ";
				$sql .= $this->DB->FieldOnly('sty','class')." = '".$_POST['lay_text']."'";
				$sql .= " WHERE ".$this->DB->FieldOnly('sty','id')." = '".$row[$this->DB->FieldOnly('sty','id')]."'";
			} else {
				$sql = "INSERT INTO ".$this->DB->Table('sty')." SET ";
				$sql .= $this->DB->FieldOnly('sty','text')." = '".$id."', ";
				$sql .= $this->DB->FieldOnly('sty','para')." = 'TextLayout', ";
				$sql .= $this->DB->FieldOnly('sty','class')." = '".$_POST['lay_text']."'";
			}
			if ($_POST['lay_text'] != "") {
				$this->DB->FreeDatabaseResult($res);
			}
			$res  = $this->DB->ReturnQueryResult($sql);
			// ImagePosition
			$sql = "SELECT ".$this->DB->Field('sty','id')." FROM ".$this->DB->Table('sty')." WHERE ".$this->DB->Field('sty','para')." = 'ImagePosition' AND ".$this->DB->Field('sty','text')." = '".$id."'";
			$res = $this->DB->ReturnQueryResult($sql);
			if ($res) {
				$row = mysql_fetch_assoc($res);
				$sql = "UPDATE ".$this->DB->Table('sty')." SET ";
				$sql .= $this->DB->FieldOnly('sty','class')." = '".$_POST['lay_picture']."'";
				$sql .= " WHERE ".$this->DB->FieldOnly('sty','id')." = '".$row[$this->DB->FieldOnly('sty','id')]."'";
			} else {
				$sql = "INSERT INTO ".$this->DB->Table('sty')." SET ";
				$sql .= $this->DB->FieldOnly('sty','text')." = '".$id."', ";
				$sql .= $this->DB->FieldOnly('sty','para')." = 'ImagePosition', ";
				$sql .= $this->DB->FieldOnly('sty','class')." = '".$_POST['lay_picture']."'";
			}
			if ($_POST['lay_picture'] != "") {
				$this->DB->FreeDatabaseResult($res);
			}
			$res  = $this->DB->ReturnQueryResult($sql);
			// Return false to not show any Text's etc.
			$this->Content = $SESS['admin']->GetHtml('ChangeText','Successful');
			throw new DWPException('FIX: $SESS["admin"]', 99);
			return false;
		} else {
			return true;
		}
	}

	function DeleteTextEntry($id="0") {
		global $_POST,$ChangeId,$MainMenu,$SubMenu,$ChangeAction;
		if ((isset($_POST['txt_did'])) && ($_POST['txt_did'] != "")) {
			$sql = "DELETE FROM ".$this->DB->Table('sty')." WHERE ".$this->DB->FieldOnly('sty','text')." = '".$_POST['txt_did']."'";
			$this->DB->ReturnQueryResult($sql);
			$sql = "DELETE FROM ".$this->DB->Table('txt')." WHERE ".$this->DB->FieldOnly('txt','id')." = '".$_POST['txt_did']."'";
			$this->DB->ReturnQueryResult($sql);
			$main = $SESS['admin']->GetHtml('ChangeText','Successful');
			throw new DWPException('FIX: $SESS["admin"]', 99);
		} else {
			$main = $SESS['admin']->GetHtml('ChangeText','DeleteConfirmation');
			throw new DWPException('FIX: $SESS["admin"]', 99);
			$main = str_replace("[CHANGE_TEXT_ID]", $id, $main);
			$main = str_replace("[MAIN_MENU_ID]", $MainMenu, $main);
			$main = str_replace("[SUB_MENU_ID]", $SubMenu, $main);
			$main = str_replace("[LANGUAGE]", $this->LANG->getLanguageName(), $main);
			$main = str_replace("[CHANGE_ACTION]", $ChangeAction, $main);
		}
		$this->Content = $main;
	}

	function ShowLayoutPreview() {
		global $_GET;
		// Set text-Variables
		$TEXT_title   = "Sample text entry";
		$TEXT_text    = "Lorem ipsum dolor sit amet, consectetuer adipiscing elit. In at velit. Maecenas eget lacus eget tortor tempus aliquam. Aenean malesuada, lorem eu tempor sodales, nisl risus auctor sapien, ut ornare nulla magna vel sapien. Nulla facilisi. Phasellus posuere sem ac mi blandit elementum. Sed sit amet ipsum. Curabitur lorem tortor, cursus ut, egestas ac, dignissim tincidunt, mauris.\r\nCurabitur in dolor. Mauris mollis nunc sed arcu. In venenatis, risus et vestibulum ullamcorper, odio est elementum quam, sit amet pretium ligula diam id libero.\r\nQuisque consectetuer odio eget purus. Cras nulla. Curabitur euismod viverra mi. Nam turpis orci, nonummy quis, sodales id, faucibus sed, purus. Nam iaculis faucibus urna. Nam semper. Nullam iaculis volutpat wisi. Vivamus nec enim. Aliquam ut tortor. Curabitur auctor placerat turpis. Aliquam erat. Vivamus lobortis wisi sed massa. In hac habitasse platea dictumst.";
		$TEXT_picture = $this->GetImageData("images/design/admin/sample_image.jpg","file");
		$LAY_Global   = $_GET['lay_global'];
		$LAY_Title    = $_GET['lay_title'];
		$LAY_Text     = $_GET['lay_text'];
		$LAY_Picture  = $_GET['lay_picture'];
		// Get Layout-File and Parse it
		$LayoutFile = $this->LayoutPath.$_GET['lay_file'].".html";
		// Get First file from Database if called file does not exist
		if (!(@file_exists($LayoutFile))) {
			$sql = "SELECT ".$this->DB->Field('lay','file')." FROM ".$this->DB->Table('lay')." ORDER BY ".$this->DB->Field('lay','text')."";
			$res = $this->DB->ReturnQueryResult($sql);
			if ($res) {
				$row = mysql_fetch_assoc($res);
			}
			$this->DB->FreeDatabaseResult($res);
			$LayoutFile = $this->LayoutPath.$row[$this->DB->FieldOnly('lay','file')].".html";
		}
		// Get Layout styles if the File exists
		if (@file_exists($LayoutFile)) {
			$this->ParseTextTemplateFile($LayoutFile);
			if (count($this->LayoutStyles) <= 0) {
				$REPL = "<b>".$TEXT_title."</b><br>".$TEXT_text."<br>".$TEXT_picture;
			} else {
				// Get templates for Current Preview
				$tpl   = $this->LayoutStyles[$LayoutFile];
				$REPL  = $tpl['GlobalLayout'][$LAY_Global];
				if ((substr_count($REPL,"[TITLE]") >= 1) && ($tpl['TitleLayout'][$LAY_Title] != "")) {
					$REPL = str_replace("[TITLE]", $tpl['TitleLayout'][$LAY_Title], $REPL);
				} else {
					$REPL = str_replace("[TITLE]", "", $REPL);
				}
				if ((substr_count($REPL,"[TEXT]") >= 1) && ($tpl['TextLayout'][$LAY_Text] != "")) {
					$REPL = str_replace("[TEXT]", $tpl['TextLayout'][$LAY_Text], $REPL);
				} else {
					$REPL = str_replace("[TEXT]", "", $REPL);
				}
				if ((substr_count($REPL,"[IMAGE]") >= 1) && ($tpl['ImagePosition'][$LAY_Picture] != "")) {
					$REPL = str_replace("[IMAGE]", $tpl['ImagePosition'][$LAY_Picture], $REPL);
				} else {
					$REPL = str_replace("[IMAGE]", "", $REPL);
				}
				// Replace Any Variables in REPL
				$REPL = $this->ReplaceImageVariables($REPL, $TEXT_picture);
				$REPL = $this->ReplaceTitleVariables($REPL, $TEXT_title);
				$REPL = $this->ReplaceTextVariables($REPL, $TEXT_text,$tpl['TextLayout'][$LAY_Text]);
				$REPL = str_replace("[TEXT_LAYOUT]", $tpl['TextLayout'][$LAY_Text], $REPL);
				$REPL = $this->ReplaceGlobalVariables($REPL);
			}
		} else {
			$REPL = "<b>".$TEXT_title."</b><br>".$TEXT_text."<br>".$TEXT_picture;
		}
		$this->Content .= str_replace("[TEXT_ENTRY]", $REPL, $this->Template);
	}

	function GetClickSectionArray() {
		$SectionArray = array("0");
		return $SectionArray;
	}

	// Will return the ID's of any Sections (only if an Section-Chooser can be visible)
	// Only required to create Static-Files
	function GetSectionIdArray($table="") {
		$SectionArray = array("0");
		if ($table != "") {
			global $MainMenu,$SubMenu,$StaticMenuId;
			// Check for the Menu-ID
			if ($StaticMenuId != "0") {
				$MenuId = $StaticMenuId;
			} else if ($SubMenu != "0") {
				$MenuId = $SubMenu;
			} else {
				$MenuId = $MainMenu;
			}

			if ($this->HasDifferentSections) {
				// Get the News-ID
				$sql = "SELECT ".$this->DB->Table('txt').".* FROM ".$this->DB->Table('txt')." WHERE ".$this->DB->Field('txt','menu')." = '".$MenuId."' ORDER BY ".$this->DB->Field('txt','sort')." ASC";
				$res = $this->DB->ReturnQueryResult($sql);
				if ($res) {
					while (($row = mysql_fetch_assoc($res)) !== false) {
						// Get id's if SubSections should be showed
						if ($row[$this->DB->FieldOnly('txt','image')] == "1") {
							$tmp = $this->GetSubSectionIdList($row[$this->DB->FieldOnly('txt','text')],$table);
						}

						// Add id's to the SectionArray
						if (is_array($tmp)) {
							$keys = array_keys($tmp);
							for ($i = 0; $i < count($keys); $i++) {
								array_push($SectionArray,$tmp[$keys[$i]]);
							}
						}
					}
					// remove first SectionId ("0"), but only if it's not the only one...
					if (count($SectionArray) > 1) {
						$tmp = array_shift($SectionArray);
					}
				}
				$this->DB->FreeDatabaseResult($res);
			}
		}
		return $SectionArray;
	}

	function GetNavigationList($tpl='',$count='0',$max='0',$cur='0') {
		global $SectionId,$ClickSection;
		$match = null;
		// Get Navigation-Delimiter
		if (preg_match("/(\[\-\->NAVIGATOR_DELIMITER\])(.*)(\[\-\-NAVIGATOR_DELIMITER\])/smi", $tpl, $match)) {
			$DELIMITER = $match[2];
			$tpl = str_replace($match[0], "", $tpl);
		} else {
			$DELIMITER = "";
		}
		// Get Navigation List-Entry
		if (preg_match("/(\[\-\->NAVIGATION_SITE\:)(\w+)(\:)(\d+)(\])(.*)(\[\-\-NAVIGATION_SITE\])/smi", $tpl, $match)) {
			$tpl  = str_replace($match[0],"[NAV_LIST]",$tpl);
			$entr = (string)$match[6];
			$mnum = (integer)$match[4];
			$type = strtoupper($match[2]);
			// Create Navigation-List
			$last_page = ceil((double)$max/(double)$count);
			$list = "";
			for ($i = 0; $i < $last_page; $i++) {
				switch ($type) {
					case 'NUMERIC':
					case 'NUM': $PAGE_NUM = ($i + 1); break;
					default:    $PAGE_NUM = ($i + 1); break;
				}
				// Check if Current page is right now
				if ($cur == $i) {
					$PAGE_NUM = "<b>".$PAGE_NUM."</b>";
				} else {
					$PAGE_NUM = $PAGE_NUM;
				}
				// Check if a Delimiter should be added
				if (strlen($list) > 0) {
					$list .= $DELIMITER;
				}
				// Check if a number should be added or not
				if (($mnum != "0") && ($i < ($cur + $mnum))) {
					// Add a new NavigationBar-Number
					$list .= $entr;
					$list  = str_replace("[NAV_PAGE_LINK:page]", "off=".$i, $list);
					$list  = str_replace("[NAV_PAGE_NUMBER]", $PAGE_NUM, $list);
					$list  = str_replace("[NAV_PAGE_LINK:begin]", "off=0", $list);
					$list  = str_replace("[NAV_PAGE_LINK:end]", "off=".($last_page-1), $list);
				} else {
					// Add a new NavigationBar-Number
					$list .= $entr;
					$list  = str_replace("[NAV_PAGE_LINK:page]", "off=".$i, $list);
					$list  = str_replace("[NAV_PAGE_NUMBER]", "...", $list);
					$list  = str_replace("[NAV_PAGE_LINK:begin]", "off=0", $list);
					$list  = str_replace("[NAV_PAGE_LINK:end]", "off=".($last_page-1), $list);
break;
				}
			}
			// Replace the List
			$tpl = str_replace("[NAV_LIST]", $list, $tpl);
			$tpl = str_replace("[NAV_PAGE_LINK:begin]", "off=0", $tpl);
			$tpl = str_replace("[NAV_PAGE_LINK:end]", "off=".($last_page-1), $tpl);
		}
		// replace some Variables
		$tpl = str_replace("[MENU_LANG]", "lan=".$this->LANG->getLanguageName(), $tpl);
		$tpl = str_replace("[SECTION_LINK]", "sec=".$SectionId."&cse=".$ClickSection, $tpl);
		return $tpl;
	}

	function CreateAccessDeniedContent() {
		$html = '
			<table cellpadding="0" cellspacing="2" style="width:400px;text-align:center;empty-cells:show;table-layout:fixed;border:6px double rgb(200,60,60);background-color:rgb(255,230,230);margin:50px;">
				<tr>
					<td style="padding:10px;height:50px;text-align:center;vertical-align:middle;font-weight:bold;font-size:18px;color:rgb(240,240,240);background-color:rgb(200,60,60);">'.$this->LANG->getValue('','txt','error_013').'</td>
				</tr>
				<tr>
					<td style="padding:10px;height:80px;text-align:center;vertical-align:middle;font-weight:bold;font-size:12px;color:rgb(50,50,50);">'.$this->LANG->getValue('','txt','error_014').'</td>
				</tr>
			</table>
		';
		return $html;
	}

	function CreateNoContentContent() {
		$userCheck = pCheckUserData::getInstance();
		if ((!($this->CreateStaticFiles)) && $userCheck->checkLogin()) {
			$html = '
		<table cellpadding="0" cellspacing="2" style="width:400px;text-align:center;empty-cells:show;table-layout:fixed;border:6px double rgb(20,150,20);background-color:rgb(192,240,192);margin:50px;">
		<tr>
		<td style="padding:10px;height:50px;text-align:center;vertical-align:middle;font-weight:bold;font-size:18px;color:rgb(240,240,240);background-color:rgb(20,150,20);">'.$this->LANG->getValue('','txt','error_015').'</td>
		</tr>
		<tr>
		<td style="padding:10px;height:80px;text-align:center;vertical-align:middle;font-weight:bold;font-size:12px;color:rgb(50,50,50);">'.$this->LANG->getValue('','txt','error_016').'</td>
		</tr>
		</table>
		';
			return $html;
		} else {
			return "";
		}
	}
}
?>
