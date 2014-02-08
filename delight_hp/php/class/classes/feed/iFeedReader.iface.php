<?php

interface iFeedReader {
	public function __construct($maxNews);
	public function start_element($parser, $tag, $ns, $attrs);
	public function end_element($parser, $tag, $ns);
	public function startNSHandler($parser, $user_data, $prefix, $uri);
	public function endNSHandler($parser, $user_data, $prefix);
	public function cdataHandler($parser, $data);
	public function getFeedHeader();
	public function getFeedList();
}

?>