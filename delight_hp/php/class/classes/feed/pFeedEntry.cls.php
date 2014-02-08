<?php

class pFeedEntry {
	
	private $fields = array(
		'id', // guid in RSS
		'title', // title in RSS
		'updated', // pubdate in RSS
		'author', // author in RSS
		'content', // description in RSS
		'link', // link in RSS
		'summary', // same as content for RSS
		'category', // category
		'contributor', // author in RSS
		'published', // pubdate in RSS
		'source', // source in RSS
		'rights' // Copyright from pFeedHeader for RSS
	);
	private $date_fields = array(
		'published',
		'updated'
	);
	private $data = null;
	private $attributes = null;
	
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