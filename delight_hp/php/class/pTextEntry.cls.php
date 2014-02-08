<?php
/**
 * This Class represents the complete Menu
 *
 * @author Lukas Zurschmiede <l.zurschmiede@delightsoftware.com>
 * @copyright (c) 2007 by delight software gmbh, switzerland
 *
 * @package delightcms
 * @version 2.0
 * @uses singelton, MainPlugin
 */

class pTextEntry {
	protected $textData;
	protected $textId;
	protected $loaded;
	protected $contentPlugin;
	protected $renderOptions = array();

	public function __construct($textId=0) {
		$this->loaded = false;
		$this->textId = $textId;
		$this->textData = array();
		$this->contentPlugin = null;
		$this->getTextData();
	}

	public function getMenuEntry() {
		return new pMenuEntry($this->menu);
	}

	public function getMenuAccessGroups() {
		return $this->getMenuEntry()->getAccessGroups(true);
	}

	public function getDate($time, $showtime=false, $showseconds=false) {
		return strftime('%Y-%m-%d'.($showtime ? $showseconds ? ' %T' : ' %H:%M' : ''), (int)$time);
	}

	public function getExtendedDate($time, $extended=false, $showtime=false, $showseconds=false) {
		$fmt = $extended ? '%A, %e %B %Y' : '%a, %e %b %Y';
		return strftime($fmt.($showtime ? $showseconds ? ' %T' : ' %H:%M' : ''), (int)$time);
	}

	protected function getTextData() {
		if ($this->textId != null) {
			$lang = pMessages::getLanguageInstance();
			$db = pDatabaseConnection::getDatabaseInstance();
			$sql  = "SELECT * FROM [table.txt] WHERE [txt.id]=".(int)$this->textId;
			$res = null;
			$db->run($sql, $res);
			if ($res->getFirst()) {
				$this->loaded = true;
				$this->textId = (int)$res->{$db->getFieldName('txt.id')};
				$this->textData['layout']  = $res->{$db->getFieldName('txt.layout')};
				$this->textData['sort']    = $res->{$db->getFieldName('txt.sort')};
				$this->textData['text']    = $res->{$db->getFieldName('txt.text')};
				$this->textData['title']   = $res->{$db->getFieldName('txt.title')};
				$this->textData['menu']    = $res->{$db->getFieldName('txt.menu')};
				$this->textData['lang']    = $res->{$db->getFieldName('txt.lang')};
				$this->textData['plugin']  = $res->{$db->getFieldName('txt.plugin')};
				$this->textData['options'] = $res->{$db->getFieldName('txt.options')};
				$this->textData['grouped'] = $res->{$db->getFieldName('txt.grouped')} > 0;

				$menu = new pMenuEntry($this->textData['menu']);
				$this->textData['menu_link'] = $menu->link;
				$this->textData['menu_name'] = $menu->title;
				$this->textData['url']       = '/'.$lang->getShortLanguageName().'/text/'.$this->textId.'/';
			} else {
				$this->textData = array();
				$this->textId = null;
			}
		}
	}

	protected function loadContentPlugin() {
		if ($this->loaded) {
			if (is_null($this->contentPlugin) || (!$this->contentPlugin instanceof $this->plugin)) {
				$this->contentPlugin = new $this->plugin();
				$this->contentPlugin->setTextId($this->textId);
			}
		}
	}

	public function save() {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		if ($this->loaded) {
			$sql = 'UPDATE [table.txt] SET [field.txt.layout]=\''.$this->layout.'\',[field.txt.sort]='.$this->sort.',[field.txt.text]=\''.mysql_real_escape_string($this->text).'\',[field.txt.title]=\''.mysql_real_escape_string($this->title).'\',[field.txt.lang]='.$this->lang.',[field.txt.plugin]=\''.$this->plugin.'\',[field.txt.options]=\''.$this->options.'\',[field.txt.grouped]='.($this->grouped?1:0).' WHERE [field.txt.id]='.$this->textId.';';
			$db->run($sql, $res);

		} else {
			$this->sort = 0;
			$sql = 'SELECT MAX([txt.sort]) AS pos FROM [table.txt] WHERE [txt.menu]='.$this->menu.';';
			$db->run($sql, $res);
			if ($res->getFirst()) {
				$this->sort = (int)$res->pos + 1;
			}
			$res = null;

			$sql = 'INSERT INTO [table.txt] ([field.txt.layout],[field.txt.sort],[field.txt.text],[field.txt.title],[field.txt.lang],[field.txt.plugin],[field.txt.options],[field.txt.grouped]) VALUES (\''.$this->layout.'\','.$this->sort.',\''.$this->text.'\',\''.$this->title.'\','.$this->lang.',\''.$this->plugin.'\',\''.$this->options.'\','.($this->grouped?1:0).');';
			$db->run($sql, $res);
			$this->textId = $res->getInsertId();
		}
		return $res->getError() == null;
	}

	public function getId() {
		return $this->getTextId();
	}

	public function getTextId() {
		return $this->textId;
	}

	public function getPluginContent() {
		$this->loadContentPlugin();
		if ($this->loaded) {
			return $this->contentPlugin->getContent();
		}
		return '';
	}

	public function getPluginTitle() {
		$this->loadContentPlugin();
		if ($this->loaded) {
			return $this->contentPlugin->getTitle();
		}
		return '';
	}

	public function getMetaContent() {
		$this->loadContentPlugin();
		$meta = new stdClass();
		$meta->cssFiles = false;
		$meta->cssContent = false;
		$meta->jsFiles = false;
		if ($this->loaded) {
			$meta->cssFiles = $this->contentPlugin->getCssImportFile();
			$meta->cssContent = $this->contentPlugin->getCssContent();
			$meta->jsFiles = $this->contentPlugin->getScriptImportFile();
		}
		return $meta;
	}

	public function getPluginSource() {
		$this->loadContentPlugin();
		if ($this->loaded) {
			return $this->contentPlugin->getSource('');
		}
		return '';
	}

	public function getEditFunction() {
		$this->loadContentPlugin();
		if ($this->loaded) {
			return $this->contentPlugin->getEditFunction($this->textId);
		}
		return 'javascript:;';
	}

	public function getDeleteFunction() {
		$this->loadContentPlugin();
		if ($this->loaded) {
			return 'javascript:deleteText('.$this->textId.')';
		}
		return 'javascript:;';
	}

	public function setRenderOptions(array $options) {
		$this->renderOptions = $options;
	}

	public function __get($name) {
		// Check for Special "content" option (link, popup, text)
		if (($name == 'content') && array_key_exists('show', $this->renderOptions) && ($this->renderOptions['show'] == 'popup')) {
			$name = 'popup';
		} else if (($name == 'content') && array_key_exists('show', $this->renderOptions) && ($this->renderOptions['show'] == 'link')) {
			$name = 'link_tag';
		} else if (($name == 'content') && array_key_exists('show', $this->renderOptions) && ($this->renderOptions['show'] == 'text')) {
			$name = 'text';
		}

		if ($name == 'id') {
			return $this->getTextId();

		} else if (($name == 'plugin') && (empty($this->textData['plugin']))) {
			return 'TEXT';

		} else if (($name == 'optionsArray') || ($name == 'optionsList')) {
			$back = array();
			$options = explode('##', $this->textData['options']);
			foreach ($options as $v) {
				$v = str_replace('#', '', $v);
				if (!empty($v)) {
					$name = substr($v, 0, strpos($v, '='));
					$value = substr($v, strpos($v, '=')+1);
					$back[$name] = $value;
				}
			}
			if (empty($back)) {
				return new stdClass();
			} else {
				return $back;
			}

		} else if ($name == 'object') {
			$back = new stdClass();
			foreach ($this->textData as $k => $v) {
				$back->{$k} = $v;
			}
			$back->optionsList = $this->optionsList;
			$back->id = $this->textId;
			return $back;

		} else if (($name == 'content') || ($name == 'source_value')) {
			return array_key_exists('title', $this->textData) ? $this->textData['title'] : '';

		} else if ($name == 'value') {
			return array_key_exists('text', $this->textData) ? $this->textData['text'] : '';

		} else if ($name == 'link_tag') {
			return '<a href="'.$this->menu_link.'" title="'.$this->title.'">'.$this->menu_name.'</a>';

		} else if ($name == 'popup') {
			return '<a href="'.$this->url.'" title="'.$this->title.'" onclick="openWindow(\''.$this->url.'\',420,450);return false;">'.$this->title.'</a>';

		} else if (array_key_exists($name, $this->textData)) {
			return $this->textData[$name];

		} else {
			return null;
		}
	}

	public function __set($name, $value) {
		if ($name == 'id') {
			$this->textId = (int)$value;

		} else if (($name == 'options') && is_object($value)) {
			$opt = '';
			foreach ($value as $k=>$v) {
				$opt .= '#'.$k.'='.$v.'#';
			}
			$this->textData['options'] = $opt;

		} else {
			if (is_string($value)) {
				$value = self::clean($value);
			}
			$this->textData[$name] = $value;
		}
	}

	/**
	 * Cleans the given String from leading and ending spaces and HTML-Comments
	 * and stupid hidden MS-Word information which occure by CopyPaste from MS Products
	 * @param string $string
	 * @return string
	 */
	public static function clean($string) {
		if (!is_string($string)) {
			return $string;
		}
		$string = trim($string);
		$string = preg_replace('/<!--(?:.*?)-->/smi', '', $string);
		return $string;
	}

	/**
	 * This we need in pFileEntry and pImageEntry
	 * @param unknown_type $name
	 */
	public static function isValidMime($name) {
		return true;
	}

	/**
	 * Get Mime.Informations, based on a Filename-Extension
	 * Returns just the Iconname, no extension, no path, etc.
	 *
	 * @param string $file Filename to get an Icon for
	 * @return String Name of the Icon to use for this File
	 */
	protected static function getMimeInfo($file) {
		$kdemime = pKdeMimeType::getInstance();
		$messages = pMessages::getLanguageInstance();
		$mime = $kdemime->getMimeInfo($file, $messages->getShortLanguageName());

		$mime['IconAbsolute'] = ABS_TEMPLATE_DIR.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'mimetypes'.DIRECTORY_SEPARATOR.$mime['Icon'].'.png';
		if (file_exists($mime['IconAbsolute'])) {
			$mime['IconRelative'] = TEMPLATE_DIR.'/images/mimetypes/'.$mime['Icon'].'.png';
			return $mime;
		}

		$mime['IconAbsolute'] = ABS_MAIN_DIR.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'mimetypes'.DIRECTORY_SEPARATOR.$mime['Icon'].'.png';
		if (file_exists($mime['IconAbsolute'])) {
			$mime['IconRelative'] = MAIN_DIR.'/images/mimetypes/'.$mime['Icon'].'.png';
			return $mime;
		}

		$mime['IconAbsolute'] = ABS_TEMPLATE_DIR.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'mimetypes'.DIRECTORY_SEPARATOR.'unknown.png';
		if (file_exists($mime['IconAbsolute'])) {
			$mime['IconRelative'] = TEMPLATE_DIR.'/images/mimetypes/unknown.png';
			return $mime;
		}

		$mime['IconAbsolute'] = ABS_MAIN_DIR.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'mimetypes'.DIRECTORY_SEPARATOR.'unknown.png';
		$mime['IconRelative'] = MAIN_DIR.'/images/mimetypes/unknown.png';
		return $mime;
	}

}

?>