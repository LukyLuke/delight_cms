<?php

class pFileLogger {
	private static $instance = null;
	private $file;

	private function __construct() {
		$this->file = ABS_DATA_DIR.'log'.DIRECTORY_SEPARATOR.'system.log';
		if (!is_dir(dirname($this->file))) {
			mkdir(dirname($this->file), 0777, true);
		}
		if (!is_file($this->file)) {
			file_put_contents($this->file, '');
		}
	}

	public static function getInstance() {
		if (self::$instance == null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public static function log($msg) {
		$inst = self::getInstance();
		file_put_contents($inst->file, date('c').chr(9).$msg.chr(10), FILE_APPEND);
	}

}