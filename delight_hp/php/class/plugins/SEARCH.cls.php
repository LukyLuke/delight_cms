<?php

class SEARCH extends MainPlugin implements iPlugin {
	const VERSION = 2008031903;
	const SEARCH_PARAM_NAME = 's';

	private $registered = false;
	private $template;
	private $entry;
	private $baseContent;

	private $_entryContent;

	/**
	 * Initialization
	 */
	public function __construct() {
		$this->registered = (defined('DWP_PLUGIN_ACCESS_GRANTED') && (substr_count(DWP_PLUGIN_ACCESS_GRANTED, 'SEARCH') > -1));
		if ($this->registered) {
			parent::__construct();
			$this->_isTextPlugin = false;
			$this->_checkDatabase();
		}
	}

	public function getAdditionalOptions() {
		return '';
	}

	/**
	 * Set the Parameters/Content from the TemplateTag
	 *
	 * @override iPlugin
	 * @param String $params The Parameters which are defined in the Template
	 * @access public
	 */
	public function setContentParameters($params) {
		$this->parsePluginContentParameters($params);
	}

	/**
	 * Call a Function in this Object (normally used by the TemplateTags)
	 *
	 * @override iPlugin
	 * @param String $function The Function to call
	 * @return mixed The Return value from the called function
	 * @access public
	 */
	public function callFunction($function) {
		// Show the Message "not registered" if it's a fact
		if (!$this->registered) {
			return "The Search-Plugin is not available on this installation.";
		}
		$searchString = pURIParameters::get(self::SEARCH_PARAM_NAME, '', pURIParameters::$STRING);
		if (!empty($searchString)) {
			$function = 'showsearchcontent';
		}

		// Check for a valid FunctionCall
		switch (strtolower($function)) {
			case 'showsearchform':
				return $this->getSearchForm($searchString);
				break;

			case 'showsearchcontent':
				return $this->runSearchRequest($searchString);
				break;
		}

		return null;
	}

	/**
	 * Get a List with all Pages (ant their links) this Plugin with given Parameters offers
	 *
	 * @overriden iPlugin
	 * @param integer $langId The Language-ID
	 * @param integer $menuId The MenuID to create static sites
	 * @param string $shortMenu optional Short-Menu name
	 * @param boolean $menuIsActive optional If this menu is active or not - default active
	 * @return array File- and Linklist
	 */
	public function getStaticPagesList($langId, $menuId, $shortMenu='', $menuIsActive=true) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$res1 = null;
		if (empty($menuId)) {
			$sql = "DELETE FROM [table.srchtxt] WHERE [field.srchtxt.lang]=".$langId.";";
			$db->run($sql, $res);

		} else if ($menuIsActive) {
			$sql = "SELECT [txt.title],[txt.text] FROM [table.txt] WHERE [txt.menu]=".$menuId.";";
			$db->run($sql, $res);
			if ($res->getFirst()) {
				$lang = new pLanguage($langId);
				while ($res->getNext()) {
					$text  = mysql_real_escape_string(preg_replace('/\s+/smi', ' ', html_entity_decode(strip_tags($res->{$db->getFieldName('txt.text')}))));
					$title = mysql_real_escape_string(preg_replace('/\s+/smi', ' ', html_entity_decode(strip_tags($res->{$db->getFieldName('txt.title')}))));
					$link = '/'.$lang->short.'/'.(empty($shortMenu)?$menuId:$shortMenu);
					$res1 = null;
					$sql1 = "INSERT INTO [table.srchtxt] ([field.srchtxt.text],[field.srchtxt.title],[field.srchtxt.lang],[field.srchtxt.link]) VALUES ('".$text."','".$title."',".$langId.",'".$link."');";
					$db->run($sql1, $res1);
				}
			}
		}
		return array();
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
		return '';
	}

	/**
	 * Show the Search-Formular
	 *
	 * @return string The Search-Formular
	 */
	private function getSearchForm($searchString='') {
		// Check for the "content"-Parameter
		$this->loadTemplate($this->tagParams['template']);
		if (strlen($this->baseContent) > 0) {
			$this->tagParams['content'] = $this->baseContent;
		} else if (!array_key_exists('content', $this->tagParams) || (strlen($this->tagParams['content']) <= 0)) {
			$this->tagParams['content'] = '<div><input name="[SEARCH_INPUT_NAME]" id="[SEARCH_INPUT_NAME]" type="text" /><input type="button" value="Search" [SEARCH_INPUT_ACTION]></div>';
		}

		$content = $this->tagParams['content'];
		$content = str_replace('[SEARCH_INPUT_ACTION]', '', $content);
		$content = str_replace('[SEARCH_INPUT_NAME]', self::SEARCH_PARAM_NAME, $content);
		$content = str_replace('[SEARCH_HTML_ID]', 'search', $content);
		return $content;
	}

	/**
	 * Return all Products from the selected Category as HTML or whatever the template is like
	 *
	 * @return string All ShopProducts from the selected Category
	 */
	private function runSearchRequest($searchString='') {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$back = '';
		$where = '';
		$this->loadTemplate($this->tagParams['template']);

		$searchList = $this->getSearchArray($searchString);
		$listAND = array();
		$listOR  = array();
		for ($i = 0; $i < count($searchList); $i++) {
			if ($searchList[$i]{0} == '+') {
				$listAND[] = "'%".substr($searchList[$i], 1)."%'";
			} else {
				$listOR[] = "'%".$searchList[$i]."%'";
			}
		}
		if (count($listAND) > 0) {
			$where .= '(([srchtxt.text] LIKE '.implode(' AND [srchtxt.text] LIKE', $listAND).')';
			$where .= ' OR ([srchtxt.title] LIKE '.implode(' AND [srchtxt.title] LIKE', $listAND).'))';
		}
		if (count($listOR) > 0) {
			if (count($listAND) > 0) {
				$where .= ' AND ';
			}
			$where .= '([srchtxt.text] LIKE '.implode(' OR [srchtxt.text] LIKE', $listOR);
			$where .= ' OR [srchtxt.title] LIKE '.implode(' OR [srchtxt.title] LIKE', $listOR).')';
		}

		$sql = "SELECT * FROM [table.srchtxt] WHERE ".$where.";";
		$db->run($sql, $res);
		if ($res->getFirst()) {
			$cnt = 1;
			while ($res->getNext()) {
				$title = $this->prepareSearchResultText($res->{$db->getFieldName('srchtxt.title')}, $searchList);
				$text = $this->prepareSearchResultText($res->{$db->getFieldName('srchtxt.text')}, $searchList);

				$tmp = $this->entry;
				$tmp = str_replace('[TITLE]', $title, $tmp);
				$tmp = str_replace('[TEXT]', $text, $tmp);
				$tmp = str_replace('[NUMBER]', $cnt, $tmp);
				$tmp = str_replace('[EVEN_ODD]', ($cnt%2)?'odd':'even', $tmp);
				$tmp = str_replace('[LINK]', 'http://'.$_SERVER["SERVER_NAME"].($_SERVER["SERVER_PORT"] != 80 ? ':'.$_SERVER["SERVER_PORT"] : '').$res->{$db->getFieldName('srchtxt.link')}, $tmp);
				$back .= $tmp;
				$cnt++;
			}
		} else {
			$lang = pMessages::getLanguageInstance();
			$back = $lang->getValue('','text','srch_003');
		}

		header('Publisher: delightcms');
		header('Content-Type: text/html');
		echo $back;
		exit();
	}

	private function getSearchArray($search) {
		$list = array();
		$block = false;
		$part = '';

		foreach (str_split($search) as $c) {
			// Initialize a Block encapsulated in "
			if (($c == '"') && !$block) {
				$block = true;
				$part = '';

			// Finalize the current Part
			} else if ((($c == '"') && $block) || (($c == ' ') && !$block)) {
				$block = false;
				$list[] = $part;
				$part = '';

			// Add the char to the current SearchPart
			} else {
				$part .= $c;
			}
		}

		// Add the last part if it is not empty
		if (!empty($part)) {
			$list[] = $part;
		}
		return $list;
	}

	private function prepareSearchResultText($str, array $search) {
		$str = substr($str, 0, 250);
		foreach ($search as $s) {
			$str = str_ireplace($s, '<strong><em>'.$s.'</em></strong>', $str);
		}
		return $str;
	}

	/**
	 * Load and parse a SHOP-Templatefile
	 *
	 * @param string $tpl A File in ABS_TEMPLATE_DIR which is a Shop-Templatefile
	 */
	private function loadTemplate($tpl) {
		if (is_file(ABS_TEMPLATE_DIR.$tpl)) {
			$this->template = file_get_contents(ABS_TEMPLATE_DIR.$tpl);
		} else {
			$this->template = '';
		}

		$match = array();
		if (preg_match("/(\[SEARCH_CONTAINER\])(.*?)(\[\/SEARCH_CONTAINER\])/smi", $this->template, $match) && (count($match) > 0)) {
			$this->baseContent = $match[2];
		}

		$match = array();
		if (preg_match("/(\[SEARCH_ENTRY\])(.*?)(\[\/SEARCH_ENTRY\])/smi", $this->template, $match) && (count($match) > 0)) {
			$this->entry= $match[2];
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
		if ($version < 2008031903) {
			$sql  = "CREATE TABLE IF NOT EXISTS [table.srchtxt] (".
			" [field.srchtxt.id] INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,".
			" [field.srchtxt.text] TEXT NOT NULL DEFAULT '',".
			" [field.srchtxt.lang] INT(10) UNSIGNED NOT NULL DEFAULT 0,".
			" [field.srchtxt.link] VARCHAR(250) NOT NULL DEFAULT '',".
			" [field.srchtxt.title] VARCHAR(250) NOT NULL DEFAULT '',".
			" PRIMARY KEY ([field.srchtxt.id]),".
			" UNIQUE KEY id ([field.srchtxt.id])".
			" );";
			$res = null;
			$db->run($sql, $res);
			echo mysql_error();

			$sql  = "CREATE TABLE IF NOT EXISTS [table.srchidx] (".
			" [field.srchidx.srchid] INT(10) UNSIGNED NOT NULL DEFAULT 0,".
			" [field.srchidx.value] VARCHAR(250) NOT NULL DEFAULT '',".
			" [field.srchidx.pithiness] INT(10) UNSIGNED NOT NULL DEFAULT 0,".
			" KEY ([field.srchidx.pithiness]),".
			" KEY ([field.srchidx.value])".
			" );";
			$res = null;
			$db->run($sql, $res);
		}
		echo mysql_error();
		/*if ($version < 2008031801) {
			$sql = "ALTER TABLE [table.ssproducts] ADD ([field.ssproducts.number] VARCHAR(150) DEFAULT '');";
			$res = null;
			$db->run($sql, $res);
		}*/

		// Update the version
		$this->_updateVersionTable($version, $versionid, self::VERSION);
	}

}

?>