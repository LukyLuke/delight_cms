<?php
// HTML-Header
$HtmlBody = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
//$HtmlBody = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">';

//    <meta http-eqiuv="expires"       content="0">
/* The rest of the HTML-Header, showen in all Browser (also IE) */
$HtmlBody = $HtmlBody.'
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="[SHORT]" lang="[SHORT]">
	<head>
		<title>[PAGE_TITLE]</title>
		<meta http-equiv="Content-Type"  content="text/html; charset=utf-8" />
		<meta http-equiv="pragma"        content="no-cache" />
		<meta name="robots"              content="INDEX,FOLLOW" />
		<meta name="date"                content="[CHANGE_DATE]" />
		<meta name="revisit-after"       content="10 days" />
		<meta name="robots"              content="index, follow" />
		<meta name="title"               content="[PAGE_TITLE]" />
		<meta name="description"         content="[PAGE_DESCRIPTION]" />
		<meta name="keywords"            content="[PAGE_KEYWORDS]" />
		<meta name="author"              content="delight cms development" />
		<meta name="publisher"           content="delight software gmbh" />
		<meta name="copyright"           content="delight cms development" />
		<meta name="DC.Title"            content="delight cms development" />
		<meta name="DC.Creator"          content="Zurschmiede Lukas" />
		<meta name="DC.Subject"          content="[PAGE_TITLE]" />
		<meta name="DC.Description"      content="[PAGE_DESCRIPTION]" />
		<meta name="DC.Publisher"        content="delight software gmbh" />
		<meta name="DC.Contributor"      content="Zurschmiede Lukas, Zurschmiede Elias" />
		<meta name="DC.Date"             content="[CHANGE_DATE]" />
		<meta name="DC.Type"             content="Text" />
		<meta name="DC.Type"             content="Software" />
		<meta name="DC.Type"             content="Service" />
		<meta name="DC.Type"             content="Collection" />
		<meta name="DC.Type"             content="Image" />
		<meta name="DC.Type"             content="Dataset" />
		<meta name="DC.Format"           content="text/html" />
		<meta name="DC.Identifier"       content="http://'.$_SERVER['SERVER_NAME'].'/" />
		<meta name="DC.Language"         content="[SHORT]" />
		<meta name="DC.Rights"           content="All rights reserved by delight software gmbh" />
		<meta http-equiv="imagetoolbar"  content="no" />
		<link rel="icon" type="image/png" href="[MAIN_DIR]images/favicon.png" />
		<link rel="apple-itouch-icon" type="image/png" href="[MAIN_DIR]images/favicon.png" />
		<style type="text/css">
			.login.field { display: none; }
		</style>
';

$HtmlBodyEND  = '	</head>';
?>
