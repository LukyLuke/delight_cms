<?php

/**
 * @copyright 2007 by delight software gmbh
 * @author Lukas Zurschmiede <l.zurschmiede@delightsoftware.com>
 * @name pKdeMimeType
 * @version 1.0
 *
 * This Class reads all KDE-Mimelnk-Files.
 * So it's really easy to get some informations about a file
 * by the fileextension.
 *
 * For better performance, you should make the directory writable for
 * PHP, so we could make a Cache-File there.
 *
 * If you update the mimelnk-database (the files), just delete the
 * cachefile in the root-directory of the mimelnk-database and
 * by the next access to this class, the cachefile would be created again.
 * You can also call the function pKdeMimeType->createNewCache()
 * to reread the whole cache.
 *
 */
class pKdeMimeType {
	const CACHE_FILE = 'mimecache';

	private $mimePath;
	private $mimeContent;
	private static $instance = null;

	/**
	 * Constructor
	 *
	 * @param string $path (optional) Path where the KDE-mimelnk folders and files are stored
	 */
	public function __construct($path="") {
		$this->setPath($path);
		$this->mimeContent = array();
	}

	/**
	 * Use a Singelton
	 * @return pKdeMimeType
	 * @static
	 * @access public
	 */
	public static function getInstance() {
		if (is_null(pKdeMimeType::$instance)) {
			pKdeMimeType::$instance = new pKdeMimeType(defined('MIMELNK_PATH') ? MIMELNK_PATH : '');
		}
		return pKdeMimeType::$instance;
	}

	/**
	 * Set Path to KDE mimelnk folder and files
	 *
	 * @param string $path path where KDE mimelnk is stored
	 */
	public function setPath($path) {
		if (!empty($path)) {
			$this->mimePath = $path;
		}
	}

	/**
	 * Check if it's a valid subfolder in KDEs mimelnk folder
	 *
	 * @param string $path check if it's a subfolder
	 * @return boolean
	 */
	private function isValidPath($path) {
		if ( (strlen($path) > 2) && is_dir($this->mimePath.DIRECTORY_SEPARATOR.$path)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Check if we can cache the informations
	 *
	 * @return boolean
	 */
	private function checkCacheable() {
		$cachefile = $this->mimePath.DIRECTORY_SEPARATOR.self::CACHE_FILE;
		if (is_writable($cachefile)) {
			return true;
		} else if (file_exists($cachefile)) {
			return false;
		} else {
			if (($fp = @fopen($cachefile, 'a')) !== false) {
				fclose($fp);
				return true;
			} else {
				return false;
			}
		}
	}

	/**
	 * Write mimeContent into cachefile
	 *
	 */
	private function writeCache() {
		$cachefile = $this->mimePath.DIRECTORY_SEPARATOR.self::CACHE_FILE;
		if ($this->checkCacheable()) {
			if (($fp = @fopen($cachefile, 'w+')) !== false) {
				@fwrite($fp, serialize($this->mimeContent));
				@fclose($fp);
				chmod($cachefile, 0666);
			}
		}
	}

	/**
	 * Read the Cachefile
	 *
	 */
	private function readCache() {
		if ($this->checkCacheable()) {
			$cachefile = $this->mimePath.DIRECTORY_SEPARATOR.self::CACHE_FILE;
			if (($fp = @fopen($cachefile, 'r')) !== false) {
				$data = '';
				while (!feof($fp)) {
					$data .= @fread($fp, 1024);
				}
				@fclose($fp);
				$data = unserialize($data);
				if (is_array($data)) {
					$this->mimeContent = $data;
				}
				unset($data);
			}
		}
	}

	/**
	 * read all files in the directories inside the mimelnk folder and parse them
	 *
	 */
	private function readAllMimefiles($reread=false) {
		// first try to read the cache if we wont recreate the cache
		if ( !$reread && (count($this->mimeContent) <= 0) ) {
			$this->readCache();
		}

		// If we can't read the cache or the cache should be re-created, parse all files
		if ( $reread || (count($this->mimeContent) <= 0) ) {
			$mimebase = scandir($this->mimePath);
			foreach ($mimebase as $k => $mimepath) {
				// if it's a directory, loop trough all files there in
				if ($this->isValidPath($mimepath)) {
					$flist = scandir($this->mimePath.DIRECTORY_SEPARATOR.$mimepath);
					foreach ($flist as $k => $mimefile) {
						// if this is a file, open it as a INI-File
						if (is_file($this->mimePath.DIRECTORY_SEPARATOR.$mimepath.DIRECTORY_SEPARATOR.$mimefile)) {
							$this->parseMimeFile($this->mimePath.DIRECTORY_SEPARATOR.$mimepath.DIRECTORY_SEPARATOR.$mimefile);
						}
					}
				}
			}

			// Write the CacheFile
			$this->writeCache();
		}
	}

	/**
	 * Parse a mimelnk-file and store the informations in mimeContent var
	 *
	 * @param string $mimeFile filename incl. full path
	 */
	private function parseMimeFile($mimeFile) {
		$file = file($mimeFile);
		$mime = substr($mimeFile, 0, strrpos($mimeFile, '.'));
		$mime = substr($mime, strrpos($mime, '/')+1);
		if (!array_key_exists($mime, $this->mimeContent)) {
			$this->mimeContent[$mime] = array();
			$seccnt = 0;
			foreach ($file as $k => $v) {
				$tmp = trim($v);
				$delim = strpos($tmp, '=');
				if ($v[0] == '[') {
					$seccnt++;
					if ($seccnt > 1) {
						break;
					}
				} else if (($v[0] != '#') && ($delim > 0)) {
					$tag = substr($tmp, 0, $delim);
					$val = substr($tmp, $delim+1);
					if (substr($tag, 0, strlen("Comment")) == "Comment") {
						$val = htmlentities(utf8_decode($val));
					}
					$this->mimeContent[$mime][$tag] = $val;
				}
			}
		}
	}

	/**
	 * Check if a mime exist in mimeContent
	 * note: mime is not the mimetype, it's the filename from
	 *       mimelnk-folders without the fileextension
	 *
	 * @param string $mime name of mimefile to check if exists
	 * @return unknown
	 */
	private function checkMimeExists($mime) {
		return (array_key_exists($mime, $this->mimeContent));
	}

	/**
	 * Check if a Tag exists in  mimefile
	 * note: mime is not the mimetype, it's the filename from
	 *       mimelnk-folders without the fileextension
	 *
	 * @param string $mime Check for $tag in this mimefile
	 * @param string $tag Tagname to check if it exists
	 * @return boolean
	 */
	private function checkTagExists($mime, $tag) {
		return ($this->checkMimeExists($mime) && array_key_exists($tag, $this->mimeContent[$mime]));
	}

	/**
	 * Check if a tag has given value in mimefile
	 * note: mime is not the mimetype, it's the filename from
	 *       mimelnk-folders without the fileextension
	 *
	 * @param string $mime mimefile to check
	 * @param string $tag tag to check against $val
	 * @param string $val value to check
	 * @return boolean
	 */
	private function checkTag($mime, $tag, $val) {
		return ($this->checkTagExists($mime, $tag) && ($this->mimeContent[$mime][$tag] == $val));
	}

	/**
	 * Check if given mimefile has the requested pattern
	 * note: mime is not the mimetype, it's the filename from
	 *       mimelnk-folders without the fileextension
	 *
	 * @param string $mime mimefile to check the pattern
	 * @param string $fext Fileextension to check in patterns
	 * @return boolean
	 */
	private function hasPattern($mime, $fext) {
		return ($this->checkTagExists($mime, 'Patterns') && (substr_count(strtolower($this->mimeContent[$mime]['Patterns']), "*.".strtolower($fext)) > 0) );
	}

	/**
	 * Get value from a tag
	 * note: mime is not the mimetype, it's the filename from
	 *       mimelnk-folders without the fileextension
	 *
	 * @param string $mime mimefile to get the value from
	 * @param string $tag tag to return the value from
	 * @return string
	 */
	private function getTagValue($mime, $tag) {
		if ($this->checkTagExists($mime, $tag)) {
			return $this->mimeContent[$mime][$tag];
		} else {
			return '';
		}
	}

	/**
	 * Search for a mimefile, based on a fileextension
	 *
	 * @param string $fext Fileextension to search the mimefile for
	 * @return string mimename - false if none was found
	 */
	private function searchMimeForFileExtension($fext) {
		$this->readAllMimefiles();
		$back = false;
		foreach (array_keys($this->mimeContent) as $k => $mime) {
			if ($this->hasPattern($mime, $fext)) {
				$back = $mime;
				break;
			}
		}
		return $back;
	}

	/**
	 * Seperate a FileExtension from a Filename and return it
	 * note: If the file has more than one dot, the fileextension
	 *       will be the string from second last point until the end
	 *       if the substring from secondlast until last dot is no longer
	 *       than 3 chars
	 *
	 * @param string $file Filename to get the Extension from
	 * @return string The Fileextension
	 */
	private function getFileExtension($file) {
		$ext = '';
		if (substr_count($file, DIRECTORY_SEPARATOR) > 0) {
			$file = substr($file, strrpos($file, DIRECTORY_SEPARATOR) + 1);
		}
		$split = explode(".", $file);
		if (count($split) > 1) {
			$ext = $split[count($split)-1];
			if ((count($split) > 2) && (strlen($split[count($split)-2]) <= 3) && (strlen($split[count($split)-3]) > 0)) {
				$ext = $split[count($split)-2].".".$ext;
			}
		}
		return $ext;
	}

	/**
	 * Get Mimeinformations about a file - based on it's fileextension
	 *
	 * @param string $filename filename to get Mimeinformations from
	 * @param string $lang 2char language identifier
	 * @return array Mimeinformations - Array( [Comment], [Icon], [MimeType], [Patterns] )
	 */
	public function getMimeInfo($filename, $lang='de') {
		$fext = $this->getFileExtension($filename);
		$back = array("Comment"=>'', "Icon" => "", "MimeType" => "", "Patterns" => "", "Extension" => $fext);
		if (($mime = $this->searchMimeForFileExtension($fext)) !== false) {
			$back["Comment"] = $this->getTagValue($mime, "Comment[".$lang."]");
			if (strlen($back["Comment"]) <= 0) {
				$back["Comment"] = $this->getTagValue($mime, "Comment");
			}
			$back["Icon"] = $this->getTagValue($mime, "Icon");
			$back["MimeType"] = $this->getTagValue($mime, "MimeType");
			$back["Patterns"] = str_replace("*.", "", $this->getTagValue($mime, "Patterns"));
		}
		return $back;
	}

	/**
	 * Recreate the whole cache
	 * Call this  function after you update some of the Mimelnk-Files
	 * in the directory you set
	 *
	 */
	public function createNewCache() {
		$this->readAllMimefiles(true);
	}

	/**
	 * Return a List with all Icons you need with the current mimelnk-database
	 *
	 * @return array with IconName as keys and Mimetypelist as Value
	 */
	public function getAllIconNames() {
		$this->readAllMimefiles();
		$back = array();
		foreach (array_keys($this->mimeContent) as $mime) {
			$mimeType = $this->getTagValue($mime, 'MimeType');
			$mimeIcon = $this->getTagValue($mime, 'Icon');
			if (strlen($mimeIcon) > 0) {
				if (!array_key_exists($mimeIcon, $back)) {
					$back[$mimeIcon] = $mimeIcon;
				} else {
					$back[$mimeIcon] .= ";".$mimeIcon;
				}
			}
		}
		return $back;
	}

	/**
	 * Check the aviability of Icons
	 *
	 * @param string $path Path where the Icons are
	 * @param string $ext Icon-Extension - default "png"
	 * @return unknown
	 */
	public function checkIconAviability($path, $ext="png") {
		$back = array("unavailable" => array(), "available" => array());
		$list = array_keys($this->getAllIconNames());
		if ($path[strlen($path)-1] != DIRECTORY_SEPARATOR) {
			$path .= DIRECTORY_SEPARATOR;
		}
		foreach ($list as $k => $icon) {
			if (file_exists($path.$icon.'.'.$ext)) {
				array_push($back['available'], $icon.'.png');
			} else {
				array_push($back['unavailable'], $icon.'.png');
			}
		}
		sort($back['available']);
		sort($back['unavailable']);
		return $back;
	}

}

?>