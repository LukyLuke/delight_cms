<?php

class LANG extends MainPlugin {
	const VERSION = 2006010600;

	private $_content;
	private $templateFile;

	protected $_isTextPlugin = false;

	function __construct() {
		$this->_content   = '';
		$this->templateFile  = 'nonexistent';
		$this->_selected  = '0';
		$this->updateDatabase();
	}

	/**
	 * Set which Templatefile shoud be used for the Language-List
	 *
	 * @param string $template TemplateFile
	 * @access public
	 */
	public function setTemplateFile($template='') {
		$this->templateFile = $template;
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
		// Check for the TemplateFile
		if ($this->readTemplateFile()) {
			$menu = pMenu::getMenuInstance();

			// Parse the Template
			$match = array();
			$content = '';
			$html = '';
			if (preg_match('/(\[LANGUAGE\])(.*?)(\[\/LANGUAGE\])/smi', $this->_content, $match)) {
				$this->_content = str_replace($match[0], '[LANG_REPLACE]', $this->_content);
				$content = $match[2];
			}
			if (empty($content)) {
				return '';
			}

			// Getting Availabele Languages
			$language = new pLanguage();
			$languages = new pLanguageList(true);
			$num = $languages->numActive() - 1;
			$count = 0;
			foreach ($languages as $lang) {
				if (!$lang->active) {
					break;
				}

				$tmp = $content;
				$tmp = str_replace('[LANG_LINK]', '/'.$lang->short.'/'.$menu->getShortMenuName(), $tmp);
				$tmp = str_replace('[LANG_TEXT]',  $lang->extendedLanguage, $tmp);
				$tmp = str_replace('[LANG_SHORT]',  $lang->short, $tmp);
				$tmp = str_replace('[LANG_ICON]',  $lang->icon, $tmp);

				if ($language->short == $lang->short) {
					$tmp = preg_replace('/(\[LANG_SELECTED\:["\'])(.*?)(["\']\])/smi', '\\2', $tmp);
				} else {
					$tmp = preg_replace('/(\[LANG_SELECTED\:["\'])(.*?)(["\']\])/smi', '', $tmp);
				}

				if ($count < $num) {
					$tmp = preg_replace('/\[IF_LAST:"([^"]*)"(:"([^"]*)")?\]/smi', '\\3', $tmp);
				} else {
					$tmp = preg_replace('/\[IF_LAST:"([^"]*)"(:"([^"]*)")?\]/smi', '\\1', $tmp);
				}

				$html .= $tmp;
				$count++;
			}
			$this->_content = str_replace('[LANG_REPLACE]', $html, $this->_content);
		}
		return $this->_content;
	}

	/**
	 * Return Additional options for TextEditor
	 *
	 * @return string Options like defined in a Template
	 * @access public
	 * @abstract
	 */
	public function getAdditionalOptions() {

	}

	/**
	 * Read the Template-File
	 *
	 * @return boolean if the read-process was successfull
	 */
	function readTemplateFile() {
		$file = ABS_TEMPLATE_DIR.$this->templateFile;
		if (file_exists($file) && is_readable($file)) {
			$fp = fopen($file, 'r');
			$this->_content = fread($fp, filesize($file));
			fclose($fp);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Check Database integrity
	 *
	 * This function creates all required Tables, Updates, Inserts and Deletes.
	 */
	private function updateDatabase() {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;

		// Get the current Version
		$v = $this->_checkMainDatabase();
		$version = $v[0];
		$versionid = $v[1];

		// Updates to the Database
		if ($version < 2006010600) {
			// Create the Languages-Table
			$sql  = "CREATE TABLE IF NOT EXISTS [table.lan] (".
			        "[field.lan.id] INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,".
			        "[field.lan.text] VARCHAR(100) NOT NULL DEFAULT '',".
			        "[field.lan.short] VARCHAR(5) NOT NULL DEFAULT '',".
			        "[field.lan.char] VARCHAR(50) NOT NULL DEFAULT '',".
			        "[field.lan.icon] VARCHAR(50) NOT NULL DEFAULT '',".
			        "[field.lan.active] INT(1) UNSIGNED NOT NULL DEFAULT 0,".
			        " PRIMARY KEY (id),".
			        " UNIQUE KEY id (id)".
			        " ) TYPE=MyISAM;";
			$db->run($sql, $res);
		}

		// Update the version
		$this->_updateVersionTable($version, $versionid, self::VERSION);
	}

}
?>
