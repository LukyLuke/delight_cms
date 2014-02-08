<?php

/**
 * AtomReader Class
 * 
 * @author Lukas Zurschmiede <l.zurschmiede@delightsoftware.com>
 * @version 1.0
 * @copyright 2009 by delight software gmbh
 */
class pAtomReader implements iFeedReader,Iterator {
	public static $NS = 'http://www.w3.org/2005/Atom';
	public static $ATOM_TAGS_HEADER = array(
		'title'=>'title',
		'subtitle'=>'subtitle',
		'updated'=>'updated',
		'id'=>'id',
		'rights'=>'rights',
		'link'=>'link',
		'generator'=>'generator',
		'logo'=>'logo'
	);
	public static $ATOM_TAGS_ENTRY = array(
		'link'=>'link',
		'id'=>'id',
		'updated'=>'updated',
		'published'=>'updated',
		//'author'=>'author', // contains subtags so this must be handled specially
		//'contributor'=>'contributor', // contains subtags so this must be handled specially
		'title'=>'title',
		'summary'=>'summary',
		'content'=>'content'
	);
	
	private $depth = 0;
	private $cdataContent = '';
	private $feedHeader;
	private $feedList;
	private $isAuthor = false;
	private $isContributor = false;
	private $person = null;
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
		
		// "feed" is the HeaderTag and "entry" are FeedItems
		if ($tag == 'feed') {
			$this->feedHeader = new pFeedHeader();
			return;
		} else if ($tag == 'entry') {
			$this->feedList[++$this->entryIndex] = new pFeedEntry();
			return;
		}
		
		if (($this->current instanceof pFeedHeader) && in_array($tag, self::$ATOM_TAGS_HEADER)) {
			$this->feedHeader->setAttributes($attrs);
			
		} else if (($this->current instanceof pFeedEntry) && in_array($tag, self::$ATOM_TAGS_ENTRY)) {
			$this->feedList[$this->entryIndex]->setAttributes($attrs);
			
		} else if (($this->current instanceof pFeedEntry) && ($tag == 'author')) {
			$this->isAuthor = true;
			
		} else if (($this->current instanceof pFeedEntry) && ($tag == 'contributor')) {
			$this->isContributor = true;
			
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
		
		if ($tag == 'feed') {
			// Nothing to to with the Header-EndTag
			
		} else if ($tag == 'entry') {
			
		} else if (($this->current instanceof pFeedHeader) && array_key_exists($tag, self::$ATOM_TAGS_HEADER)) {
			$this->feedList[$this->entryIndex]->endTag(self::$ATOM_TAGS_HEADER[$tag], $this->cdataContent);
			
		} else if (($this->current instanceof pFeedEntry) && array_key_exists($tag, self::$ATOM_TAGS_ENTRY)) {
			$this->feedList[$this->entryIndex]->endTag(self::$ATOM_TAGS_ENTRY[$tag], $this->cdataContent);
			
		} else if (($this->current instanceof pFeedEntry) && ($tag == 'author')) {
			$this->feedList[$this->entryIndex]->endTag($tag, $this->person->name.(!empty($this->person->email)?' <'.$this->person->email.'>':'').(!empty($this->person->uri)?' ['.$this->person->uri.']':''));
			$this->isAuthor = false;
			$this->isContributor = false;
			$this->person = null;
			
		} else if (($this->current instanceof pFeedEntry) && ($tag == 'contributor')) {
			$this->feedList[$this->entryIndex]->endTag($tag, $this->person->name.(!empty($this->person->email)?' <'.$this->person->email.'>':'').(!empty($this->person->uri)?' ['.$this->person->uri.']':''));
			$this->isAuthor = false;
			$this->isContributor = false;
			$this->person = null;
			
		} else if ($this->isAuthor || $this->isContributor) {
			if (is_null($this->person)) {
				$this->person = new stdClass();
				$this->person->name = '';
				$this->person->uri = '';
				$this->person->email = '';
			}
			
			if ($tag == 'name') {
				$this->person->name = $this->cdataContent;
			} else if ($tag == 'uri') {
				$this->person->uri = $this->cdataContent;
			} else if ($tag == 'email') {
				$this->person->email = $this->cdataContent;
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