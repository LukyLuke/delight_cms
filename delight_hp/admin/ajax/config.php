<?php
	define('OPEN_AJAX_DATABASE_CONFIG', true);
	require_once("../../config/config.inc.php");
	require_once("../../config/userconf.inc.php");
	require_once("../../php/class/Database.cls.php");

	// Create DatabaseConnection
	$DB = new Database();
	$DB->ConnectToDatabase();

	// the ID is a field that mostly is used
	if (isset($_GET['id'])) {
		$id = (int)$_GET['id'];
	} else if (isset($_POST['id'])) {
		$id = (int)$_POST['id'];
	} else {
		$id = null;
	}

	// the Parameter SEC
	if (isset($_GET['sec'])) {
		$secid = (int)$_GET['sec'];
	} else if (isset($_POST['sec'])) {
		$secid = (int)$_POST['sec'];
	} else {
		$secid = null;
	}
?>