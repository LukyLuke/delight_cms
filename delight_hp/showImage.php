<?php

// Call-URL is like this:
// $url = "../../".MAIN_DIR."/showImage.php?a=TABLE&b=BIN-FIELD&c=IMG-ID";

	if (isset($_GET['a']) && isset($_GET['b']) && isset($_GET['c'])) {
		require_once("config/config.inc.php");
		require_once("config/userconf.inc.php");
		require_once("php/class/Database.cls.php");

		// Create DatabaseConnection
		$DB = new Database();
		$DB->ConnectToDatabase();

		$_imgTbl = (string)$_GET['a'];
		$_imgFld = (string)$_GET['b'];
		$_imgId  = (int)$_GET['c'];

		$_img = null;
		$sql = "SELECT ".$DB->Field($_imgTbl, $_imgFld)." FROM ".$DB->Table($_imgTbl)." WHERE ".$DB->Field($_imgTbl, 'id')."='".$_imgId."'";
		$res = $DB->ReturnQueryResult($sql);
		if ($res) {
			$row = mysql_fetch_assoc($res);
			if (strlen($row[$DB->FieldOnly($_imgTbl, $_imgFld)]) > 0) {
				if (file_exists(realpath(dirname($_SERVER['SCRIPT_FILENAME']))."/images/page/".$row[$DB->FieldOnly($_imgTbl, $_imgFld)])) {
					$_img = imageCreateFromString(file_get_contents(realpath(dirname($_SERVER['SCRIPT_FILENAME']))."/images/page/".$row[$DB->FieldOnly($_imgTbl, $_imgFld)]));
				} else {
					$_img = imageCreateFromString($row[$DB->FieldOnly($_imgTbl, $_imgFld)]);
				}
				$_back  = imageColorAllocate($_img, 255, 255, 255);
				$_trans = imageColorTransparent($_img, $_back);
			}
		}

		if ($_img == null) {
			$_img    = imageCreateTruecolor((int)SHOPIMAGE_WIDTH, (int)SHOPIMAGE_HEIGHT);
			$_back   = imageColorAllocate($_img, 255, 255, 255);
			$_front1 = imageColorAllocate($_img, 100,100,100);
			$_front2 = imageColorAllocate($_img, 150,150,150);
			$_front3 = imageColorAllocate($_img, 180,180,180);
			$_front4 = imageColorAllocate($_img, 220,220,220);
			$_front5 = imageColorAllocate($_img, 230,230,230);
			$_front6 = imageColorAllocate($_img, 240,240,240);
			$tmp = imageFill($_img, 0, 0, $_back);
			$tmp = imageFtText($_img, 21,   0,  5, 19, $_front1, "./config/arialblack.ttf", "no image");
			$tmp = imageFtText($_img, 19, 350, 10, 40, $_front2, "./config/arialblack.ttf", "no image");
			$tmp = imageFtText($_img, 16, 335, 15, 57, $_front3, "./config/arialblack.ttf", "no image");
			$tmp = imageFtText($_img, 12, 320, 18, 72, $_front4, "./config/arialblack.ttf", "no image");
			$tmp = imageFtText($_img,  9, 300, 19, 87, $_front5, "./config/arialblack.ttf", "no image");
			$tmp = imageFtText($_img,  7, 280, 18, 99, $_front6, "./config/arialblack.ttf", "no image");
			$_trans = imageColorTransparent($_img, $_back);
		}
		$DB->FreeDatabaseResult($res);
		$DB->CloseDatabaseConnection();
	} else {
		// if (isset($_GET['d']) && ($_GET['d'] == 'b'))
		$_img = imageCreateTruecolor(1,1);
		$_back = imagecolorallocate($_img, 0, 0, 0);
		imagefill($_img, 0, 0, $_back);
		$_trans = imagecolortransparent($_img, $_back);
	}

	header("Content-Type: image/png");
	imagepng($_img);
	imagedestroy($_img);
?>