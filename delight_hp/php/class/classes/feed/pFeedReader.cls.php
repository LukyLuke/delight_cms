<?php

class pFeedReader implements IteratorAggregate {

	private $url = '';
	private $feedParser = null;
	private $maxNews = 10;

	/**
	 * Initialization
	 *
	 * @param string $url Feed-URL to parse
	 * @access public
	 */
	public function __construct($url) {
		$this->url = $url;
	}

	/**
	 * Parse the Feed
	 *
	 * @return boolean
	 * @access public
	 */
	public function parse($maxNews=10) {
		$this->maxNews = (int)$maxNews;
		set_error_handler(array(&$this, 'error_handler'));

		// Create and parametrize the XML-Parser
		$parser = xml_parser_create_ns();
		xml_set_object($parser, $this);
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 0);
		xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, 'utf-8');
		xml_set_character_data_handler($parser, 'cdataHandler');
		xml_set_default_handler($parser, 'cdataHandler');
		xml_set_element_handler($parser, 'start_element', 'end_element');
		xml_set_start_namespace_decl_handler($parser, 'startNSHandler');
		xml_set_end_namespace_decl_handler($parser, 'endNSHandler');

		// Read the Feed and parse it
		$success = true;
		$fp = fopen($this->url, 'r');
		while (!feof($fp)) {
			$data = fread($fp, 4096);
			if (!xml_parse($parser, $data, feof($fp))) {
				trigger_error( sprintf('XML error: %s at line %d, col %d', xml_error_string(xml_get_error_code($parser)), xml_get_current_line_number($parser), xml_get_current_column_number($parser)) );
			}
		}
		fclose($fp);
		xml_parser_free($parser);

		restore_error_handler();
		return $success;
	}

	/**
	 * Return the Header from the parsed Feed
	 *
	 * @return pFeedHeader or null
	 * @access public
	 */
	public function getFeedHeader() {
		if (!is_null($this->feedParser)) {
			return $this->feedParser->getFeedHeader();
		}
		return null;
	}

	/**
	 * Return a List with all Feed-Entries
	 *
	 * @return array(pFeedEntry) or null
	 * @access public
	 * @interface IteratorAggregate
	 */
	public function getIterator() {
		return $this->feedParser;
	}

	/**
	 * Start-Element Handler for XML-Parser
	 *
	 * @param unknown_type $parser
	 * @param unknown_type $name
	 * @param unknown_type $attrs
	 * @access public
	 */
	public function start_element($parser, $name, $attrs) {
		// real TagName and possible NameSpace
		$tmp = split(':', $name);
		$tag = strtolower(array_pop($tmp));
		$ns = strtolower(count($tmp)>0 ? $tmp[0] : '');
		unset($tmp);

		if (is_null($this->feedParser)) {
			if ($tag == 'feed') {
				$this->feedParser = new pAtomReader($this->maxNews);
			} else if ($tag == 'rss') {
				$this->feedParser = new pRSSReader($this->maxNews);
			}
		}

		if (!is_null($this->feedParser)) {
			$this->feedParser->start_element($parser, $tag, $ns, $attrs);
		}
	}

	/**
	 * End-Element Handler for XML-Parser
	 *
	 * @param unknown_type $parser
	 * @param unknown_type $name
	 * @access public
	 */
	public function end_element($parser, $name) {
		// real TagName and possible NameSpace
		$tmp = split(':', $name);
		$tag = strtolower(array_pop($tmp));
		$ns = strtolower(count($tmp)>0 ? $tmp[0] : '');
		unset($tmp);

		if (!is_null($this->feedParser)) {
			$this->feedParser->end_element($parser, $tag, $ns);
		}
	}

	/**
	 * Start-Namespace Handler for XML-Parser
	 *
	 * @param unknown_type $parser
	 * @param unknown_type $user_data
	 * @param unknown_type $prefix
	 * @param unknown_type $uri
	 * @access public
	 */
	public function startNSHandler($parser, $user_data, $prefix, $uri) {
	if (!is_null($this->feedParser)) {
			$this->feedParser->startNSHandler($parser, $user_data, $prefix, $uri);
		}
	}

	/**
	 * End-Namespace Handler for XML-Parser
	 *
	 * @param unknown_type $parser
	 * @param unknown_type $user_data
	 * @param unknown_type $prefix
	 * @access public
	 */
	public function endNSHandler($parser, $user_data, $prefix) {
		if (!is_null($this->feedParser)) {
			$this->feedParser->endNSHandler($parser, $user_data, $prefix);
		}
	}

	/**
	 * CDATA-Section Handler for XML-Parser
	 *
	 * @param unknown_type $parser
	 * @param unknown_type $data
	 * @access public
	 */
	public function cdataHandler($parser, $data) {
		if (!is_null($this->feedParser)) {
			$this->feedParser->cdataHandler($parser, $data);
		}
	}


	/**
	 * Error-Reporting for XML-Parsing
	 *
	 * @param int $log_level
	 * @param string $log_text
	 * @param string $error_file
	 * @param int $error_line
	 * @access public
	 */
	public function error_handler($log_level, $log_text, $error_file, $error_line) {
		$this->error[] = 'Error('.$log_level.') in File "'.$error_file.'('.$error_line.')": '.$log_text;
	}
}

?>