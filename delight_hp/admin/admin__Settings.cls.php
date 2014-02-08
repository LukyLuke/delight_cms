<?php
	class admin_XXX_Settings extends admin_MAIN_Settings {

		function __construct() {
			parent::__construct();
		}

		function createActionBasedContent() {
			global $SectionId;
			$userCheck = pCheckUserData::getInstance();
			if ($userCheck->checkAccess($this->_adminAccess)) {
				switch (1000 + $this->_mainAction) {
					case  1000:  $this->XYZ();      break;

					case (1000 + 50): $this->doXYZ();   break;
				}
			} else {
				$this->showNoAccess();
			}
		}

		// Comment
		function XYZ() {
			$userCheck = pCheckUserData::getInstance();
			if ($userCheck->checkAccess($this->_adminAccess)) {
				global $SectionId;


				$_baseLnk = "/".$this->_langShort."/".$this->_menuId."/adm=";

				$_adminHtml = $this->_getContent('layoutFile...', 'SECTION...');

				$this->_content = $_adminHtml;
			} else {
				$this->showNoAccess();
			}
		}

		function doXYZ() {
			$userCheck = pCheckUserData::getInstance();
			if ($userCheck->checkAccess($this->_adminAccess)) {
				global $SectionId, $_POST;

				$_defData = array();
				$_defData['FieldName...'] = " ";

				foreach ($_POST  as $k => $v) {
					if (array_key_exists($k, $_defData)) {
						$_defData[$k] = trim($v);
					}
				}
				$res = $this->DB->ReturnQueryResult($sql);

				$this->_content = $this->_getContent("forwardOnly");
				$this->_content = str_replace("[FORWARD_LINK]", "/".$this->_langShort."/".$this->_menuId."/adm=10000", $this->_content);
			} else {
				$this->showNoAccess();
			}
		}
	}
?>