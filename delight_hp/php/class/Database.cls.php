<?php
// Include Configuration
if ((defined("OPEN_DOWNLOAD_DATABASE_CONFIG")) && (OPEN_DOWNLOAD_DATABASE_CONFIG))
	require_once(dirname(__FILE__)."/../../config/database.conf.php");
else if ((defined("OPEN_AJAX_DATABASE_CONFIG")) && (OPEN_AJAX_DATABASE_CONFIG))
	require_once(dirname(__FILE__)."/../../config/database.conf.php");
else
	require_once(dirname(__FILE__)."/../../config/database.conf.php");

// The Database Class
class Database {
	var $DBConnection;
	var $DBState;

	function Database() {
		$this->DBConnection = array();
		$this->DBState = array();
	}

	function ConnectToDatabase() {
		global $DBSettings;

		// Connect to the Masters-Database and disable strict mode on success
		array_push($this->DBState,"Connect the Masters-Database '".$DBSettings['master']['database']."' on server '".$DBSettings['master']['host']."'");
		if (!($this->DBConnection['master'] = @mysql_connect($DBSettings['master']['host'],$DBSettings['master']['user'],$DBSettings['master']['password']))){
			array_push($this->DBState,"Error: ".mysql_error()." - ".mysql_errno());
			print("Please wait, there is currently a Problem with the Database-Server.<br><br>Bite haben sie einen Moment geduld. Es ist ein Problem mit dem Datenbankserver aufgetreten.");
		} else {
			mysql_query('set sql_mode := ""', $this->DBConnection['master']);
		}
	}

	function CloseDatabaseConnection() {
		$keys = array_keys($this->DBConnection);
		for ($i = 0; $i < count($keys); $i++) {
			@mysql_close($this->DBConnection[$keys[$i]]);
			unset($this->DBConnection[$keys[$i]]);
		}
		unset($this->DBConnection);
		$this->DBConnection = array();
	}

	function ReturnQueryResult($sql,$con="master") {
		global $DBSettings;
		switch ($con) {
			default:    $DBCON = "master"; break;
		}
		if (!(@mysql_select_db($DBSettings[$DBCON]['database']))) {
			array_push($this->DBState,"Error by selecting Database '".$DBSettings[$DBCON]['database']."'");
			array_push($this->DBState,"Error: ".mysql_error()." - ".mysql_errno());
		}

		$res = @mysql_query($sql,$this->DBConnection[$DBCON]);
		if (is_resource($res) && @mysql_data_seek($res,0)) {
			return $res;
		} else {
			if (mysql_errno() > 0) {
				array_push($this->DBState,"Error by SQL-Query: '".$sql."'");
				array_push($this->DBState,"Error: ".mysql_error()." - ".mysql_errno());
			}
			return false;
		}
	}

	function FreeDatabaseResult($res) {
		if (is_resource($res)) {
			@mysql_free_result($res);
		}
	}

	function LogDatabaseError($msg) {
		global $_SERVER,$DBTables,$DBFields;
		$time = date("Y-m-d H:i:s");
		$link = $_SERVER["REQUEST_URI"];
		$sql  = "INSERT INTO ".$DBTables['error']." (error_time,error_message,error_link) VALUES ('".$time."','".$msg."','".$link."')";
		$res  = $this->ReturnQueryResult($sql);
		$this->FreeDatabaseResult($res);
	}

	// Return the Tablename
	function Table($tbl="") {
		global $DBTables;
		if (array_key_exists($tbl,$DBTables)) {
			return "`".$DBTables[$tbl]."`";
		} else {
			return "";
		}
	}

	// Return the Fieldname (incl. Tablename)
	function Field($tbl="",$fld="") {
		global $DBFields,$DBTables;
		if ((is_array($DBFields[$tbl])) && (array_key_exists($fld,$DBFields[$tbl]))) {
			return $this->Table($tbl).".`".$DBFields[$tbl][$fld]."`";
		} else {
			return "";
		}
	}

	// Return the Fieldname (incl. Tablename)
	function FieldOnly($tbl="",$fld="") {
		global $DBFields;
		if ((array_key_exists($tbl, $DBFields)) && (is_array($DBFields[$tbl])) && (array_key_exists($fld,$DBFields[$tbl]))) {
			return $DBFields[$tbl][$fld];
		} else { 
			return "";
		}
	}

	function GetMaxDataset($table,$DbConnection,$where) {
		$sql = "SELECT COUNT('".$this->Field($table,'id')."') AS FldCount FROM ".$this->Table($table)." ".$where;
		$res = $this->ReturnQueryResult($sql,$DbConnection);
		if ($res) {
			$row = mysql_fetch_assoc($res);
		} else {
			$row = array("FldCount"=>"0");
		}
		$this->FreeDatabaseResult($res);
		return $row['FldCount'];
	}

	function TableNavigation($title,$table,$CurrentOffset,$link,$DbConnection="",$where="") {
		global $_POST,$_GET;
		if ((isset($_POST['SearchString'])) && ($_POST['SearchString'] != "")) {
			$link = $link."&SearchString=".$_POST['SearchString'];
		} else if ((isset($_GET['SearchString'])) && ($_GET['SearchString'] != "")) {
			$link = $link."&SearchString=".$_GET['SearchString'];
		}

		$Curr  = (int)$CurrentOffset;
		$Page  = (int)SHOW_MAX_ROWS;
		$Total = (int)$this->GetMaxDataset($table,$DbConnection,$where);
		$Total = ceil($Total / $Page);
		$NavLine = (int)NAVIGATION_LINE;

		// First Page
		$NavigationList1 .= "<a href=\"".$link."&o=0\" class=\"menu\">";
		$NavigationList1 .= "<img src=\"images/arrow_left.gif\"     style=\"width:16px;height:16px;border:0px solid black;\" alt=\"arrow_left\" />";
		$NavigationList1 .= "</a>";
		// Previous Page
		if (($Curr - $Page) >= 0) {
			$NavigationList1 .= "<a href=\"".$link."&o=".($Curr - $Page)."\" class=\"menu\">";
		}
		$NavigationList1   .= "<img src=\"images/arrow_left_one.gif\" style=\"width:16px;height:16px;border:0px solid black;\" alt=\"arrow_left\" />";
		if (($Curr - $Page) >= 0) {
			$NavigationList1 .= "</a>";
		}

		// Table Navigation with PageNumbers
		for ($i = 0; $i < $Total; $i++) {
			$Offset = ($i * $Page);
			$num = ($i + 1);
			if ($num < 10) {
				$num = "0".$num;
			}
			if ($Offset != $Curr) {
				$NavigationList2 .= "<a href=\"".$link."&o=".$Offset."\" class=\"smallmenu\">".$num."</a>";
			} else {
				$NavigationList2 .= "<span class=\"NavListPipe\">".$num."</span>";
			}
			if (($i + 1)%$NavLine == 0) {
				$NavigationList2 .= "<br>";
			} else if ($i < ($Total - 1)) {
				$NavigationList2 .= "<span class=\"NavListPipe\">&nbsp;&#124;&nbsp;</span>";
			}
		}

		// Next Page
		if (($Curr + $Page) <= (($Total * $Page) - $Page)) {
			$NavigationList3 .= "<a href=\"".$link."&o=".($Curr + $Page)."\" class=\"menu\">";
		}
		$NavigationList3   .= "<img src=\"images/arrow_right_one.gif\" style=\"width:16px;height:16px;border:0px solid black;\" alt=\"arrow_left\" />";
		if (($Curr + $Page) <= ($Total * $Page)) {
			$NavigationList3 .= "</a>";
		}
		
		// Last Page
		$NavigationList3 .= "<a href=\"".$link."&o=".(($Total * $Page) - $Page)."\" class=\"menu\">";
		$NavigationList3 .= "<img src=\"images/arrow_right.gif\"     style=\"width:16px;height:16px;border:0px solid black;\" alt=\"arrow_left\" />";
		$NavigationList3 .= "</a>";

		//$NavigationList = "max: ".$Max." / curr: ".$CurrentOffset." / link: ".$link."";
		$html .= "<table cellpadding=\"0\" cellspacing=\"0\" style=\"width:100%;text-align:center;\"> \r\n";
		$html .= "<colgroup> \r\n";
		$html .= "<col /> \r\n";
		$html .= "<col style=\"min-width:50px;\" /> \r\n";
		$html .= "<col style=\"min-width:200px;width:200px;\" /> \r\n";
		$html .= "<col style=\"min-width:50px;\" /> \r\n";
		$html .= "<col /> \r\n";
		$html .= "</colgroup> \r\n";
		$html .= "<tr> \r\n";
		$html .= "<td class=\"Tborder11\"></td> \r\n";
		$html .= "<td class=\"Tborder12\" style=\"text-align:center;\" colspan=\"3\">".$title."</td> \r\n";
		$html .= "<td class=\"Tborder13\"></td> \r\n";
		$html .= "</tr> \r\n";
		$html .= "<tr> \r\n";
		$html .= "<td class=\"border21\"></td> \r\n";
		$html .= "<td class=\"border22\" style=\"text-align:right;vertical-align:middle;\">".$NavigationList1."</td> \r\n";
		$html .= "<td class=\"border22\" style=\"text-align:center;vertical-align:middle;\">".$NavigationList2."</td> \r\n";
		$html .= "<td class=\"border22\" style=\"text-align:left;vertical-align:middle;\">".$NavigationList3."</td> \r\n";
		$html .= "<td class=\"border23\"></td> \r\n";
		$html .= "</tr> \r\n";
		$html .= "<tr> \r\n";
		$html .= "<td class=\"border31\"></td> \r\n";
		$html .= "<td class=\"border32\" colspan=\"3\"></td> \r\n";
		$html .= "<td class=\"border33\"></td> \r\n";
		$html .= "</tr> \r\n";
		$html .= "</table> \r\n";
		return $html;
	}
}
?>
