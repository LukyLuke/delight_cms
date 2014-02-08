<?php

class pFeedHeader {
	private $fields = array(
		'id', // guid in RSS
		'title', // title in RSS
		'subtitle', // description in RSS
		'updated', //pubdate in RSS
		'author', // managingeditor in RSS
		'contributor', // webmaster in RSS
		'rights', // copyright in RSS
		'category', // category in RSS
		'link', // link in RSS
		'generator', // generator in RSS
		'logo' // image in RSS
	);
	private $date_fields = array(
		'updated'
	);
	
	private $attributes = null;
	private $data;
	
	public function __construct() {
		$this->data = new stdClass();
		foreach ($this->fields as $e) {
			$this->data->{$e} = '';
		}
	}
	
	public function setAttributes($attrs) {
		$this->attributes = $attrs;
	}
	
	public function endTag($tag, $content) {
		if (empty($content)) {
			return;
		}
		$this->setData($tag, $content);
	}
	
	private function setData($tag, $content) {
		if (in_array($tag, $this->date_fields)) {
			$content = str_replace('T', ' ', $content);
			$content = str_replace('hu', 'Thu', $content); // If we have replaced 'Thu'
			$content = str_replace('+', ' +', $content);
			$content = strtotime($content);
		}
		$this->data->{$tag} = $content;
	}
	
	public function __get($name) {
		if (property_exists($this->data, $name)) {
			return $this->data->{$name};
		}
		return '';
	}
	
}

?>