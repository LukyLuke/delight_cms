<?php

/**
 * Change own Data
 *
 */

class admin_10000_Settings extends admin_MAIN_Settings {

	function __construct() {
		parent::__construct();
	}

	function createActionBasedContent() {
		global $SectionId;
		//if ($userCheck->checkAccess($this->_adminAccess)) {
		if ($this->_userId > 0) {
			switch (1000 + $this->_mainAction) {
				case  1000:  $this->showChangeUser();      break;
				case (1000 + 50): $this->doChangeUser();   break;
			}
		} else {
			$this->showNoAccess();
		}
	}

	// Change a User
	function showChangeUser()
	{
		$userCheck = pCheckUserData::getInstance();
		//if ($userCheck->checkAccess($this->_adminAccess)) {
		if ($this->_userId > 0) {
			global $SectionId;

			$sql = "SELECT * FROM ".$this->DB->Table('per')." WHERE ".$this->DB->Field('per','id')." = '".$this->_userId."'";
			$res = $this->DB->ReturnQueryResult($sql);
			if ($res) {
				$row = mysql_fetch_assoc($res);
			} else {
				$row = array();
			}
			$this->DB->FreeDatabaseResult($res);

			$_baseLnk = "/".$this->_langShort."/".$this->_menuId."/adm=";
			$_adminHtml = $this->_getContent('userManagement', 'USER_CREATE');
			$_adminHtml = str_replace("[USER_CREATE_LINK]", $_baseLnk."10050", $_adminHtml);
			$_adminHtml = preg_replace("/(\[EDIT\])(.*?)(\[\/EDIT\])/smi", "\\2", $_adminHtml);
			$_adminHtml = preg_replace("/(\[CREATE\])(.*?)(\[\/CREATE\])/smi", "", $_adminHtml);
			$_adminHtml = preg_replace("/(\[ACCESS\])(.*?)(\[\/ACCESS\])/smi", "", $_adminHtml);
			$_adminHtml = str_replace("[CONFIRMATION]", $this->LANG->getValue('','txt','usr_031'), $_adminHtml);
			$_adminHtml = str_replace("[TITLE]", $this->LANG->getValue('','txt','usr_031'), $_adminHtml);

			$_adminHtml = str_replace("[FORM_LANGUAGE]", $this->_langShort, $_adminHtml);
			$_adminHtml = str_replace("[FORM_CHANGEUSER]", $this->_userId,  $_adminHtml);
			$_adminHtml = str_replace("[FORM_SECTION]",  $SectionId,  $_adminHtml);
			$_adminHtml = str_replace("[FORM_ACTION]", "10050", $_adminHtml);
			$_adminHtml = str_replace("[FORM_MENU]", $this->_menuId,  $_adminHtml);
			$_adminHtml = str_replace("[FORM_LINK]", MAIN_DIR."/index.php", $_adminHtml);

			$_adminHtml = str_replace("[USER_USERNAME]", $row[$this->DB->FieldOnly('per','user')], $_adminHtml);
			$_adminHtml = str_replace("[USER_PASSWORD]", $row[$this->DB->FieldOnly('per','clear')], $_adminHtml);
			$_adminHtml = str_replace("[USER_COMPANY]", $row[$this->DB->FieldOnly('per','company')], $_adminHtml);
			$_adminHtml = str_replace("[USER_NAME]", $row[$this->DB->FieldOnly('per','name')], $_adminHtml);
			$_adminHtml = str_replace("[USER_SURNAME]", $row[$this->DB->FieldOnly('per','surname')], $_adminHtml);
			$_adminHtml = str_replace("[USER_ADDRESS]", $row[$this->DB->FieldOnly('per','address')], $_adminHtml);
			$_adminHtml = str_replace("[USER_POSTALCODE]", $row[$this->DB->FieldOnly('per','postalcode')], $_adminHtml);
			$_adminHtml = str_replace("[USER_CITY]", $row[$this->DB->FieldOnly('per','city')], $_adminHtml);
			$_adminHtml = str_replace("[USER_COUNTRY]", $row[$this->DB->FieldOnly('per','country')], $_adminHtml);
			$_adminHtml = str_replace("[USER_EMAIL]", $row[$this->DB->FieldOnly('per','email')], $_adminHtml);
			$_adminHtml = str_replace("[USER_INTERNET]", $row[$this->DB->FieldOnly('per','internet')], $_adminHtml);

			$this->_content = $_adminHtml;
		} else {
			$this->showNoAccess();
		}
	}

	function doChangeUser() {
		$userCheck = pCheckUserData::getInstance();
		//if ($userCheck->checkAccess($this->_adminAccess)) {
		if ($this->_userId > 0) {
			global $SectionId, $_POST;
			
			$_defData = array();
			$_defData['tmpUserUsername'] = " ";
			$_defData['tmpUserName']     = " ";
			$_defData['tmpUserSurname']  = " ";
			$_defData['tmpUserPasswd']   = " ";
			$_defData['tmpUserCompany']  = " ";
			$_defData['tmpUserAddress']  = " ";
			$_defData['tmpUserPlz']      = " ";
			$_defData['tmpUserCity']     = " ";
			$_defData['tmpUserCountry']  = " ";
			$_defData['tmpUserEmail']    = " ";
			$_defData['tmpUserWeb']      = " ";

			foreach ($_POST  as $k => $v) {
				if (array_key_exists($k, $_defData)) {
					$_defData[$k] = trim($v);
				}
			}

			$sql  = "UPDATE ".$this->DB->Table('per')."";
			$sql .= " SET ".$this->DB->FieldOnly('per','name')." = '".$_defData['tmpUserName']."'";
			$sql .= ", ".$this->DB->FieldOnly('per','surname')." = '".$_defData['tmpUserSurname']."'";
			if (strlen(trim($_defData['tmpUserPasswd'])) > 0) {
				$sql .= ", ".$this->DB->FieldOnly('per','passwd')." = SHA1('".$_defData['tmpUserPasswd']."')";
				$sql .= ", ".$this->DB->FieldOnly('per','clear')." = '".$_defData['tmpUserPasswd']."'";
			}
			$sql .= ", ".$this->DB->FieldOnly('per','company')." = '".$_defData['tmpUserCompany']."'";
			$sql .= ", ".$this->DB->FieldOnly('per','address')." = '".$_defData['tmpUserAddress']."'";
			$sql .= ", ".$this->DB->FieldOnly('per','postalcode')." = '".$_defData['tmpUserPlz']."'";
			$sql .= ", ".$this->DB->FieldOnly('per','city')." = '".$_defData['tmpUserCity']."'";
			$sql .= ", ".$this->DB->FieldOnly('per','country')." = '".$_defData['tmpUserCountry']."'";
			$sql .= ", ".$this->DB->FieldOnly('per','email')." = '".$_defData['tmpUserEmail']."'";
			$sql .= ", ".$this->DB->FieldOnly('per','internet')." = '".$_defData['tmpUserWeb']."'";
			$sql .= " WHERE ".$this->DB->FieldOnly('per','id')." = '".$this->_userId."'";

			$res = $this->DB->ReturnQueryResult($sql);
			$this->_content = $this->_getContent("forwardOnly");
			$this->_content = str_replace("[FORWARD_LINK]", "/".$this->_langShort."/".$this->_menuId."/adm=10000", $this->_content);
		} else {
			$this->showNoAccess();
		}
	}

}
?>