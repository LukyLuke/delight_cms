<?php

class pNewsEntry extends pTextEntry {

	private $rssKey = '';
	private $selectedFeed;

	public function __construct($textId, $rssKey='') {
		$this->rssKey = $rssKey;
		$this->selectedFeed = new stdClass();
		parent::__construct($textId);
	}

	protected function getTextData() {
		if ($this->textId != null) {
			$lang = pMessages::getLanguageInstance();
			$db = pDatabaseConnection::getDatabaseInstance();
			$sql  = "SELECT * FROM [table.new] WHERE [new.id]=".(int)$this->textId.' AND [new.lang]='.$lang->getLanguageId().';';
			$res = null;
			$db->run($sql, $res);
			if ($res->getFirst()) {
				$this->loaded = true;
				$this->textId = (int)$res->{$db->getFieldName('img.id')};
				$this->textData['section']    = $res->{$db->getFieldName('new.section')};
				$this->textData['timestamp']  = strtotime($res->{$db->getFieldName('new.date')});
				$this->textData['date']       = $this->getDate($this->textData['timestamp'], true);
				$this->textData['date_short'] = $this->getExtendedDate($this->textData['timestamp'], false, true);
				$this->textData['date_extended'] = $this->getExtendedDate($this->textData['timestamp'], true, true);
				$this->textData['title']      = $res->{$db->getFieldName('new.title')};
				$this->textData['text']       = $res->{$db->getFieldName('new.text')};
				$this->textData['short']      = $res->{$db->getFieldName('new.short')};
				$this->textData['rss']        = $res->{$db->getFieldName('new.rss')} > 0 ? true : false;
				$this->textData['url']        = '/'.$lang->getShortLanguageName().'/news/'.$this->textId.'/sec='.$this->textData['section'];
				$this->textData['feed_list']  = new stdClass();

				// If this is an RSS-Feed-Definition we need to parse the Content as a INI-File
				if ($this->textData['rss']) {
					$ini = @parse_ini_string($this->textData['text'], true);
					if (($ini !== FALSE) && array_key_exists('feed', $ini)) {
						$this->textData['feed_list']->title         = array_key_exists('title', $ini['feed']) ? $ini['feed']['title'] : '';
						$this->textData['feed_list']->summarize     = array_key_exists('summarize', $ini['feed']) ? $ini['feed']['summarize'] : 3600;
						$this->textData['feed_list']->max_cache_age = array_key_exists('cacheage', $ini['feed']) ? $ini['feed']['cacheage'] : 86400;
						$this->textData['feed_list']->feeds         = array_key_exists('feed', $ini['feed']) ? $ini['feed']['feed'] : array();
						$this->textData['feed_list']->list          = array();

						// Reformat the News-Content to get a better look in AdminEditor
						$this->textData['text'] = nl2br($this->textData['text']);
					} else {
						$this->textData['text'] = '[feed]';
					}

					if (!empty($this->rssKey)) {
						$this->loadRSS();
						$id = $this->textId.'_'.$this->rssKey;
						foreach ($this->textData['feed_list']->list as $feed) {
							if ($id == $feed->id) {
								$this->selectedFeed = $feed;
								break;
							}
						}
					}
				}

			} else {
				$this->textData = array();
				$this->textId = null;
			}
		}
	}

	public function loadRSS() {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		if ($this->textData['rss']) {
			if (!property_exists($this->textData['feed_list'], 'feeds')) {
				$this->textData['feed_list']->feeds = array();
			}
			if (!property_exists($this->textData['feed_list'], 'list')) {
				$this->textData['feed_list']->list = array();
			}
			if (!is_array($this->textData['feed_list']->feeds) || (is_array($this->textData['feed_list']->list) && (count($this->textData['feed_list']->list) > 0))) {
				return;
			}

			foreach ($this->textData['feed_list']->feeds as $feed) {
				$sql = 'SELECT * FROM [table.rssnews] WHERE [rssnews.uri]=\''.mysql_real_escape_string($feed).'\';';
				$db->run($sql, $res);
				$fid = 0;
				$last = 0;
				if ($res->getFirst()) {
					$fid = (int)$res->{$db->getFieldName('rssnews.id')};
					$last = (int)$res->{$db->getFieldName('rssnews.last')};
				}
				$res = null;
				$maxAge = (time() - $this->textData['feed_list']->max_cache_age);
				if ($last <= $maxAge) {
					$feedReader = new pFeedReader($feed);
					if ($feedReader->parse()) {
						// Upgrade the rssNews-Table
						if ($fid > 0) {
							$sql = 'UPDATE [table.rssnews] SET [field.rssnews.last]='.time().' WHERE [field.rssnews.id]='.$fid.';';
							$db->run($sql, $res);
						} else {
							$last = time();
							$sql = 'INSERT INTO [table.rssnews] ([field.rssnews.uri],[field.rssnews.last]) VALUES (\''.mysql_real_escape_string($feed).'\','.$last.');';
							$db->run($sql, $res);
							$fid = $res->getInsertId();
						}
						$res = null;

						// Add all news received from the feed
						foreach ($feedReader as $f) {
							// Check if this News is not already grabbed
							$sql = 'SELECT [rsscache.date] FROM [table.rsscache] WHERE [rsscache.uid]=\''.mysql_real_escape_string($f->id).'\' AND [rsscache.rssnews]='.$fid.';';
							$db->run($sql, $res);
							$feeddate = $f->updated > 0 ? $f->updated : $f->published;
							if ($res->getFirst()) {
								$date = (int)$res->{$db->getFieldName('rsscache.date')};
								if ($feeddate > $date) {
									$sql = 'UPDATE [table.rsscache] SET [field.rsscache.date]='.$feeddate.', [field.rsscache.title]=\''.mysql_real_escape_string($f->title).'\', [field.rsscache.text]=\''.mysql_real_escape_string($f->content).'\' WHERE [field.rsscache.uid]=\''.mysql_real_escape_string($f->id).'\' AND [field.rsscache.rssnews]='.$fid.';';
									$db->run($sql, $res);
								}

							} else {
								$sql = 'INSERT INTO [table.rsscache] ([field.rsscache.rssnews],[field.rsscache.date],[field.rsscache.title],[field.rsscache.text],[field.rsscache.uid]) VALUES ('.$fid.','.$feeddate.',\''.mysql_real_escape_string($f->title).'\',\''.mysql_real_escape_string($f->content).'\',\''.mysql_real_escape_string($f->id).'\');';
								$db->run($sql, $res);
							}
							$res = null;
						}
					}
				}
				$res = null;

				// Get the RSS-News
				$sql = 'SELECT * FROM [table.rsscache] WHERE [rsscache.rssnews]='.$fid.' ORDER BY [rsscache.date] DESC;';
				$db->run($sql, $res);
				if ($res->getFirst()) {
					while($res->getNext()) {
						$n = new stdClass();
						$n->title = $res->{$db->getFieldName('rsscache.title')};
						$n->text = $res->{$db->getFieldName('rsscache.text')};
						$n->plaintext = strip_tags($n->text);
						$n->timestamp = $res->{$db->getFieldName('rsscache.date')};
						$n->date = $this->getDate($n->timestamp, true);
						$n->date_short = $this->getExtendedDate($n->timestamp, false, true);
						$n->date_extended = $this->getExtendedDate($n->timestamp, true, true);
						$n->uri = $res->{$db->getFieldName('rsscache.uid')};
						$n->id = $this->textId.'_'.sha1($n->uri);
						$n->section = $this->section;
						$this->textData['feed_list']->list[] = $n;
					}
				}
			}
		}

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
		if ($name == 'date_ext') {
			$name = 'date_extended';
		}

		if (!empty($this->rssKey)) {
			if (isset($this->selectedFeed->{$name})) {
				return $this->selectedFeed->{$name};
			}
			return '';
		}

		if ($name == 'link_tag') {
			return '<a href="'.$this->url.'" title="'.$this->title.'">'.$this->title.'</a>';

		} else if ($name == 'popup') {
			return '<a href="'.$this->url.'" title="'.$this->title.'" onclick="openWindow(\''.$this->url.'\',420,450);return false;">'.$this->title.'</a>';

		} else if ($name == 'length') {
			$this->loadRSS();
			return $this->rss ? count($this->textData['feed_list']->list) : 1;

		} else if ($name == 'feed_array') {
			$this->loadRSS();
			return $this->rss ? $this->textData['feed_list']->list : array();

		} else if ($name == 'plaintext') {
			return strip_tags($this->text);
		}

		return parent::__get($name);
	}
}