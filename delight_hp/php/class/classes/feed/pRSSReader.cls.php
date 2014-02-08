<?php

/**
 * AtomReader Class
 * 
 * @author Lukas Zurschmiede <l.zurschmiede@delightsoftware.com>
 * @version 1.0
 * @copyright 2009 by delight software gmbh
 */
class pRSSReader implements iFeedReader,Iterator {
	public static $RSS_TAGS_HEADER = array(
		'guid'=>'id',
		'title'=>'title',
		'link'=>'link',
		'description'=>'subtitle',
		'pubdate'=>'updated',
		'lastbuilddate'=>'updated', // if not pubdate is given, we use the value from lastbuilddate if given
		'generator'=>'generator',
		'managingeditor'=>'author',
		'webmaster'=>'contributor',
		'copyright'=>'rights',
		'category'=>'category',
		'url'=>'logo'
	);
	public static $RSS_TAGS_ENTRY  = array(
		'guid'=>'id',
		'title'=>'title',
		'link'=>'link',
		'description'=>'content', // set also summary
		'author'=>'author', // set also contributor
		'category'=>'category',
		'source'=>'source',
		'pubdate'=>'updated' // set also published
	);
	
	private $version = '2.0';
	private $url;
	private $depth = 0;
	private $cdataContent = '';
	private $feedHeader;
	private $feedList;
	private $isHeader = false;
	private $isEntry = false;
	private $entryIndex = -1;
	private $maxNews = 10;
	
	private $iteratorPosition = 0;
	
	/**
	 * Initialization
	 *
	 * @interface iFeedReader
	 */
	public function __construct($maxNews) {
		$this->maxNews = (int)$maxNews;
		$this->feedList = array();
		$this->feedHeader = new pFeedHeader();
	}
	
	/**
	 * Get the FeedHeader
	 *
	 * @return pFeedHeader
	 * @interface iFeedReader
	 */
	public function getFeedHeader() {
		return $this->feedHeader;
	}
	
	/**
	 * Get all Feeds
	 *
	 * @return array(pFeedEntry)
	 * @interface iFeedReader
	 */
	public function getFeedList() {
		return $this->feedList;
	}
	
	/**
	 * Iterator: Rewind to the first Position
	 *
	 * @interface Iterator
	 */
	public function rewind() {
		$this->iteratorPosition = 0;
	}
	
	/**
	 * Iterator: Check if the current Position is valid or not
	 *
	 * @return boolean
	 * @interface Iterator
	 */
	public function valid() {
		return ($this->iteratorPosition < count($this->feedList)) && ($this->iteratorPosition >= 0);
	}
	
	/**
	 * Iterator: Return the Key of the current Element
	 *
	 * @return string
	 * @interface Iterator
	 */
	public function key() {
		return $this->feedList[$this->iteratorPosition]->id;
	}
	
	/**
	 * Iterator: Return the current Element
	 *
	 * @return pFeedEntry
	 * @interface Iterator
	 */
	public function current() {
		if ($this->valid()) {
			return $this->feedList[$this->iteratorPosition];
		}
		return null;
	}
	
	/**
	 * Iterator: Increase the Counter to get the next entry
	 *
	 * @interface Iterator
	 */
	public function next() {
		$this->iteratorPosition++;
	}
	
	/**
	 * XML-PARSER
	 *
	 * @param unknown_type $parser
	 * @param unknown_type $tag
	 * @param unknown_type $ns
	 * @param unknown_type $attrs
	 * @interface iFeedReader
	 */
	public function start_element($parser, $tag, $ns, $attrs) {
		if ($this->entryIndex+1 >= $this->maxNews) {
			return;
		}
		
		$this->depth++;
		
		// "rss" is the Main Tag, "item" identifies a FeedEntry and "channel" is the main Header (and contains all feed-items)
		if ($tag == 'rss') {
			if (array_key_exists('version', $attrs)) {
				$this->version = $attrs['version'];
			}
			return;
			
		} else if ($tag == 'item') {
			$this->feedList[++$this->entryIndex] = new pFeedEntry();
			$this->isHeader = false;
			$this->isEntry = true;
			return;
			
		} else if ($tag == 'channel') {
			$this->feedHeader = new pFeedHeader();
			$this->isHeader = true;
			$this->isEntry = false;
			return;
		}
		
		if ($this->isHeader && array_key_exists($tag, self::$RSS_TAGS_HEADER)) {
			$this->feedHeader->setAttributes($attrs);
			
		} else if ($this->isEntry && array_key_exists($tag, self::$RSS_TAGS_ENTRY)) {
			$this->feedList[$this->entryIndex]->setAttributes($attrs);
			
		}
		
	}
	
	/**
	 * XML-PARSER
	 *
	 * @param unknown_type $parser
	 * @param unknown_type $tag
	 * @param unknown_type $ns
	 * @interface iFeedReader
	 */
	public function end_element($parser, $tag, $ns) {
		if ($this->entryIndex >= $this->maxNews) {
			return;
		}
		
		$this->cdataContent = trim(html_entity_decode($this->cdataContent));
		
		if ($tag == 'channel') {
			// Nothing to do with the Header-EndTag
			
		} else if ($tag == 'item') {
			$this->feedList[$this->entryIndex]->endTag('rights', $this->feedHeader->rights);
			
		} else if ($this->isHeader && array_key_exists($tag, self::$RSS_TAGS_HEADER)) {
			if (($tag == 'lastbuilddate') && empty($this->feedHeader->updated)) {
				$tag = 'pubdate';
			}
			$this->feedHeader->endTag(self::$RSS_TAGS_HEADER[$tag], $this->cdataContent);
			
		} else if ($this->isEntry && array_key_exists($tag, self::$RSS_TAGS_ENTRY)) {
			$this->feedList[$this->entryIndex]->endTag(self::$RSS_TAGS_ENTRY[$tag], $this->cdataContent);
			
			// Some special fields have to be set twice
			if ($tag == 'description') {
				$this->feedList[$this->entryIndex]->endTag('summary', $this->cdataContent);
			} else if ($tag == 'author') {
				$this->feedList[$this->entryIndex]->endTag('contributor', $this->cdataContent);
			} else if ($tag == 'pubdate') {
				$this->feedList[$this->entryIndex]->endTag('published', $this->cdataContent);
			}
			
		}
		$this->cdataContent = '';
		
		$this->depth--;
	}
	
	/**
	 * XML-PARSER
	 *
	 * @param unknown_type $parser
	 * @param unknown_type $user_data
	 * @param unknown_type $prefix
	 * @param unknown_type $uri
	 * @interface iFeedReader
	 */
	public function startNSHandler($parser, $user_data, $prefix, $uri) {
		if ($this->entryIndex >= $this->maxNews) {
			return;
		}
		
	}
	
	/**
	 * XML-PARSER
	 *
	 * @param unknown_type $parser
	 * @param unknown_type $user_data
	 * @param unknown_type $prefix
	 * @interface iFeedReader
	 */
	public function endNSHandler($parser, $user_data, $prefix) {
		if ($this->entryIndex >= $this->maxNews) {
			return;
		}
		
	}
	
	/**
	 * XML-PARSER
	 *
	 * @param unknown_type $parser
	 * @param unknown_type $data
	 * @interface iFeedReader
	 */
	public function cdataHandler($parser, $data) {
		if ($this->entryIndex >= $this->maxNews) {
			return;
		}
		$this->cdataContent .= $data;
	}

}

?>