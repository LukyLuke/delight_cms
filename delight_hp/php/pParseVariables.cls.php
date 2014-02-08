<?php
	// Set default the StaticMenuId to Menu "0"
	$StaticMenuId = "0";

	// Set Language
	if (isset($_GET['lan']))
		$lang = $_GET['lan'];
	else if (isset($_POST['lan']))
		$lang = $_POST['lan'];
	else if (isset($_GET['lang']))
		$lang = $_GET['lang'];
	else if (isset($_POST['lang']))
		$lang = $_POST['lang'];
	else
		$lang = "de";

	// Check for MainMenu
	if (isset($_GET['m']))
		$MainMenu = $_GET['m'];
	else if (isset($_POST['m']))
		$MainMenu = $_POST['m'];
	else
		$MainMenu = "1";
	$MainMenu = preg_replace("/([\d]+)/smi", "\\1", $MainMenu);

	// Check for MainMenu
	if (isset($_GET['sm']))
		$shortMenu = $_GET['sm'];
	else if (isset($_POST['sm']))
		$shortMenu = $_POST['sm'];
	else
		$shortMenu = "";

	// Check for posted ID
	if (isset($_GET['i']))
		$postId = $_GET['i'];
	else if (isset($_POST['i']))
		$postId = $_POST['i'];
	else
		$postId = "0";

	// Check for posted SectionId (images, progamms, etc.)
	if (isset($_GET['sec']))
		$sectionId = $_GET['sec'];
	else if (isset($_POST['sec']))
		$sectionId = $_POST['sec'];
	else
		$sectionId = "0";

	// Check for posted TemplateType
	if (isset($_GET['tpl']))
		$template = $_GET['tpl'];
	else if (isset($_POST['tpl']))
		$template = $_POST['tpl'];
	else
		$template = "";

	// Check for posted PageOffset
	if (isset($_GET['off']))
		$pageOffset = (integer)$_GET['off'];
	else if (isset($_POST['off']))
		$pageOffset = (integer)$_POST['off'];
	else
		$pageOffset = 0;

	// Check for Admin-Action
	if (isset($_GET['adm']))
		$AdminAction = (integer)$_GET['adm'];
	else if (isset($_POST['adm']))
		$AdminAction = (integer)$_POST['adm'];
	else
		$AdminAction = 0;

	// Check for Admin-Section
	if (isset($_GET['adminSection']))
		$AdminSection = substr($_GET['adminSection'],0,3);
	else if (isset($_POST['adminSection']))
		$AdminSection = substr($_POST['adminSection'],0,3);
	else
		$AdminSection = '';

	// Check for TextLayout
	if (isset($_GET['textLayout']))
		$TextLayout = $_GET['textLayout'];
	else if (isset($_POST['textLayout']))
		$TextLayout = $_POST['textLayout'];
	else
		$TextLayout = 'plain_text';

	// Check for TextLayout
	if (isset($_GET['textContent']))
		$TextContent = $_GET['textContent'];
	else if (isset($_POST['textContent']))
		$TextContent = $_POST['textContent'];
	else
		$TextContent = '';

	// Check for TextLayout
	if (isset($_GET['textTitle']))
		$TextTitle = urldecode($_GET['textTitle']);
	else if (isset($_POST['textTitle']))
		$TextTitle = urldecode($_POST['textTitle']);
	else
		$TextTitle = '';

	// Check for TextLayout
	if (isset($_GET['textOptions']))
		$TextOptions = $_GET['textOptions'];
	else if (isset($_POST['textOptions']))
		$TextOptions = $_POST['textOptions'];
	else
		$TextOptions = '';

	// Check for TextLayout
	if (isset($_GET['textParser']))
		$TextParser = $_GET['textParser'];
	else if (isset($_POST['textParser']))
		$TextParser = $_POST['textParser'];
	else
		$TextParser = 'TEXT';

	// Check for Post-ID (mostly in AdminSection)
	if (isset($_GET['i']))
		$ChangeId = (integer)$_GET['i'];
	else if (isset($_POST['i']))
		$ChangeId = (integer)$_POST['i'];
	else
		$ChangeId = 0;

	// Check for Username
	if (isset($_GET['us']))
		$UsrName = $_GET['us'];
	else if (isset($_POST['us']))
		$UsrName = $_POST['us'];
	else
		unset($UsrName);

	// Check for Password
	if (isset($_GET['ps']))
		$UsrPass = $_GET['ps'];
	else if (isset($_POST['ps']))
		$UsrPass = $_POST['ps'];
	else
		unset($UsrPass);

/*	// Check for AutoLogin
	if (isset($_GET['al']))
		$AutoLogin = (boolean)$_GET['al'];
	else if (isset($_POST['al']))
		$AutoLogin = (boolean)$_POST['al'];
	else
		unset($AutoLogin);
*/

	// Get all Language-Parameters (ID as $langid, SHORT as $langshort and DESCR as $lang)
	$shortlang = '';
	$langid    = 0;
	function getLanguageParameters(&$short) {
		global $lang, $langshort, $langid;

		$l = new pLanguage($short);
		if ($l->shortLanguage == $short) {
			$lang = $l->extendedLanguage;
			$langid = $l->languageId;
			$langshort = $l->shortLanguage;
		} else {
			$lang = "german";
			$langid = 0;
			$langshort = "de";
		}
		setLocaleCode($langshort);
	}

?>
