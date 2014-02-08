<?php

/**
 * Log a referer
 */
function log_referer() {
	return;
	$db = pDatabaseConnection::getDatabaseInstance();

	$blackList = explode(',', REFERER_BLACKLIST);

	$referer = '';
	$client  = '';
	$request = '';
	$browser = '';

	// RefererL
	if (array_key_exists('HTTP_REFERER', $_SERVER)) {
		$referer = $_SERVER['HTTP_REFERER'];
	}
	// DB: client
	if (array_key_exists('REMOTE_ADDR', $_SERVER)) {
		$client  = $_SERVER['REMOTE_ADDR'];
	}
	// DB: local
	if (array_key_exists('REQUEST_URI', $_SERVER)) {
		$request = $_SERVER['REQUEST_URI'];
	}
	// DB: browser
	if (array_key_exists('HTTP_USER_AGENT', $_SERVER)) {
		$browser = $_SERVER['HTTP_USER_AGENT'];
	}
	// DB: date
	$date = date("Y-m-d H:i:s");

	if (trim($referer) != '') {
		//$tmp = preg_match('/((http|https)?(:\/\/))?([^\/]+)((\/)?([^\?\&]+^([\w]+=)))?((\?)?(.*))/i', trim($referer), $hostMatch);
		//http://cms1.delight.local/dtest.html
		$hostMatch = parse_url($referer);
		$tmp = preg_match('/[^\.\/]+\.[^\.\/]+$/smi', $hostMatch['host'], $hostCheck);

		if ( ((count($hostCheck) > 0) && (count($hostMatch) > 0)) && !(in_array($hostCheck[0], $blackList)) && !(in_array($hostMatch['host'], $blackList)) ) {
			// DB: protocol, domain, url, param
			$protocol   = $hostMatch['scheme'];
			$domain     = $hostMatch['host'];
			$scriptfile = '';
			$parameters = '';
			if (array_key_exists('path', $hostCheck)) {
				$scriptfile = $hostMatch['path'];
			}
			if (array_key_exists('query', $hostCheck)) {
				$parameters = $hostMatch['query'];
			}

			// Insert into Database
			$sql  = "INSERT INTO [table.ref] SET [ref.local]='".$request."',[ref.protocol]='".$protocol."',[ref.domain]='".$domain."'";
			$sql .= ",[ref.url]='".$scriptfile."',[ref.param]='".$parameters."',[ref.date]='".$date."',[ref.client]='".$client."',[ref.browser]='".$browser."'";

			$res = null;
			$db->run($sql, $res);
			unset($res);
		}
	}
}

?>