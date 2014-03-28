<?php

// feed.php?s=[SectionID]&r=[Recursive 1|0]&l=[Language de|en|...]&n=[NumberNews]

// Include needed files first
require_once("./config/config.inc.php");
//require_once("./php/RefererLog.php");

if (!defined('NUM_NEWS_PER_FEED')) {
	define('NUM_NEWS_PER_FEED', 20);
}

// Initialize the Feed
$feed = <<<EOF
<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">

	<title type="text">[FEED_TITLE_TEXT]</title>
	<subtitle type="text">[FEED_SUBTITLE_TEXT]</subtitle>

	<updated>[FEED_LAST_UPDATED]</updated>
	<id>[FEED_ID]</id>
	<link rel="alternate" type="text/html" hreflang="[FEED_LANG]" href="http://[FEED_DOMAIN]/"/>
	<link rel="self" type="application/atom+xml" href="http://[FEED_DOMAIN][FEED_URL]"/>
	<rights>Copyright (c) 2007 by delight software gmbh switzerland</rights>
	<generator uri="http://www.delightsoftware.com/de/delightcms/feed/" version="1.0">delight cms news feed generator</generator>

	[FEED_LOGO]
	[FEED_ICON]

	[FEED_ENTRIES]

</feed>
EOF;

// Get all SectionID's and fill them into an Array if it's a recursive call
$section = array(pURIParameters::get('s', 0, pURIParameters::$INT));
if (pURIParameters::get('r', 0, pURIParameters::$INT) > 0) {
	$section = array_merge($section, getChildSectionList($section[0]));
	$section = array_unique($section);
}
$lang = pURIParameters::get('l', 'de', pURIParameters::$STRING);
$num = pURIParameters::get('n', NUM_NEWS_PER_FEED, pURIParameters::$INT);
$feed = str_replace('[FEED_ENTRIES]', getAtomNewsFromSection($section, $lang, $num), $feed);

// Title and Subtitle
$feed = str_replace('[FEED_TITLE_TEXT]', DHP_NAME, $feed);
$feed = str_replace('[FEED_SUBTITLE_TEXT]', 'News: '.getSectionNameByIdlist($section), $feed);
$feed = str_replace('[FEED_PUBLISHER]', DHP_NAME, $feed);

// General-Data
$feed = str_replace('[FEED_LANG]', $lang, $feed);
$feed = str_replace('[FEED_ID]', 'tag:delightsoftware.com,'.date('Y:m'), $feed);
$feed = str_replace('[FEED_LAST_UPDATED]', '2007-12-21T12:29:29Z', $feed);
$feed = str_replace('[FEED_DOMAIN]', $_SERVER['HTTP_HOST'], $feed);
$feed = str_replace('[FEED_URL]', str_replace('%2F', '/', str_replace('&', '&amp;', str_replace('&amp;', '&', $_SERVER['REQUEST_URI']))), $feed);
$feed = str_replace('[FEED_ICON]', '<icon>http://'.$_SERVER['HTTP_HOST'].'/delight_hp/images/feed.png</icon>', $feed);
$feed = str_replace('[FEED_LOGO]', '<logo>http://'.$_SERVER['HTTP_HOST'].'/delight_hp/images/feed.png</logo>', $feed);

// show it
header("Content-Type: application/atom+xml");
//header("Content-Type: text/html; charset=utf-8");
echo $feed;

/**
 * Return the name from first Section by the SectionId-List
 *
 * @param array $section Array with all Sections
 * @return string Name of First Section
 */
function getSectionNameByIdlist($section) {
	if (count($section) > 0) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$sql = 'SELECT [nes.text] FROM [table.nes] WHERE [nes.id]='.(int)$section[0];
		$res = null;
		$db->run($sql, $res);
		$res->getFirst();
		if ((pURIParameters::get('r', 0, pURIParameters::$INT) > 0) && (count($section) > 2)) {
			$tmp = array_shift($section);
			return $res->{$db->getFieldName('nes.text')}.' '.getSectionNameByIdlist($section);
		} else {
			return $res->{$db->getFieldName('nes.text')};
		}
	} else {
		return DHP_DESCRIPTION;
	}
}

/**
 * Get all Child-Sections from $section
 *
 * @param integer $section SectionID to get all Childs from
 */
function getChildSectionList($section) {
	$db = pDatabaseConnection::getDatabaseInstance();
	$sql = 'SELECT [nes.id] FROM [table.nes] WHERE [nes.parent]='.(int)$section;
	$res = null;
	$db->run($sql, $res);
	$back = array();
	while ($res->getNext()) {
		$sid = $res->{$db->getFieldName('nes.id')};
		$back[] = $sid;
		$back = array_merge($back, getChildSectionList($sid));
	}
	return $back;
}

/**
 * Create News as ATOM+XML Content and return
 *
 * @param array $section Array with all Section-IDS to get news from
 * @return string ATOM+XML String to append to the ATOM-Newsfeed
 */
function getAtomNewsFromSection(array $section, $lang='de', $num=null) {
	$obj = new NEWS();
	$newsList = $obj->getFromSections($section, $lang, $num);

	// Append all entries to the feed
	$content = '';
	$count = 0;
	foreach ($newsList as $news) {
		$count++;
		$content .= '<entry>
		<link rel="alternate" type="text/html" href="http://[FEED_DOMAIN]/[FEED_LANG]/news/'.$news->id.'/sec='.$news->section.'"/>
		<id>[FEED_ID].'.$count.'</id>

		<updated>'.date('c', $news->timestamp).'</updated>
		<published>'.date('c', $news->timestamp).'</published>

		<author>
			<name>[FEED_PUBLISHER]</name>
			<uri>http://[FEED_DOMAIN]/</uri>
		</author>
		<contributor>
			<name>delight cms feed</name>
		</contributor>

		<title type="html"><![CDATA['.$news->title.']]></title>
		<summary type="html">
			<div xmlns="http://www.w3.org/1999/xhtml">
				'.prepareXHTMLContent(strlen($news->short) > 0 ? $news->short : substr($news->plaintext, 0, 250)).'
			</div>
		</summary>
		<content type="xhtml" xml:lang="en" xml:base="http://[FEED_DOMAIN]/">
			<div xmlns="http://www.w3.org/1999/xhtml">
				'.prepareXHTMLContent($news->text).'
			</div>
		</content>
	</entry>'."\n";
	}
	return $content;
}

/**
 * Create XHTML-Valid Content and return it
 *
 * @param string $data Content to encode for XHTML
 * @return string XHTML-Conform $data
 */
function prepareXHTMLContent($data) {
	// Remove comments, we not need them here
	$data = preg_replace('/<!--.*?-->/smi', '', $data);
	$data = html_entity_decode($data);

	/*$data = str_replace('&amp;', '&', $data);
	$data = str_replace('&bull;', '&#8226;', $data);

	$match = array();
	if (preg_match_all('/(&[a-z0-9]{2,8};)/smi', $data, $match, PREG_SET_ORDER)) {
		foreach ($match as $v) {
			$char = '&#'.ord(html_entity_decode($v[0])).';';
			$data = str_replace($v[0], $char, $data);
		}
	}*/

	$data = str_replace('&', '&amp;', $data);
	$data = str_replace('&amp;#', '&#', $data);
	$data = str_replace('&#43', '&#43;', $data);
	$data = str_replace('&#43;;', '&#43;', $data);

	return $data;
}

?>