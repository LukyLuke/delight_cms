<?php

/**
 * Languages
 *
 */

class admin_1300_Settings extends admin_MAIN_Settings {
	const VERSION = '2010060200';

	/**
	 * initialization
	 *
	 * @access public
	 */
	public function __construct() {
		parent::__construct();
		$this->_checkDatabase(self::VERSION);
	}

	/**
	 * Call a function, based on parameter adm
	 * This is the main function, which will be called for getting some content
	 *
	 * @access public
	 */
	public function createActionBasedContent() {
		$userCheck = pCheckUserData::getInstance();

		// Check for Access
		if ($userCheck->checkAccess($this->_adminAccess)) {
			// Check which Action is called and return the appropriate content or JSON
			switch (pURIParameters::get('action', '', pURIParameters::$STRING)) {
				case 'template':
					$tpl = pURIParameters::get('template', 'languages', pURIParameters::$STRING);
					$tpl = $this->getAdminContent($tpl, 1300);
					$tpl = $this->showLanguageList($tpl);
					echo $this->ReplaceAjaxLanguageParameters($tpl);
					exit();
					break;

				case 'state':
					$lang = new pLanguage(pURIParameters::get('language', 0, pURIParameters::$INTEGER));
					$lang->changeLanguageState();
					echo '{"success":true}';
					exit();
			}
		}
	}

	/**
	 * Create the Language-List
	 *
	 * @param string $tpl The Template to parse and to create the LanguageList inside
	 * @access private
	 */
	private function showLanguageList($tpl) {
		$html = '';
		$entry = '';
		$match = null;
		if (preg_match("/(\[ENTRY\])(.*?)(\[\/ENTRY\])/smi", $tpl, $match)) {
			$entry = $match[2];

			$list = new pLanguageList(true);
			$cnt = 0;
			foreach ($list as $lang) {
				$_tmp = $entry;
				$_tmp = str_replace('[EVEN_ODD]',     ($cnt%2==0 ? 'even' : 'odd'), $_tmp);
				$_tmp = str_replace("[ICON]",         $lang->icon, $_tmp);
				$_tmp = str_replace("[ICON_NAME]",    $lang->iconName, $_tmp);
				$_tmp = str_replace("[NAME]",         $lang->extendedLanguage, $_tmp);
				$_tmp = str_replace("[SHORT]",        $lang->shortLanguage, $_tmp);
				$_tmp = str_replace("[CHARSET]",      $lang->charset, $_tmp);
				$_tmp = str_replace("[ID]",           $lang->languageId, $_tmp);
				$_tmp = str_replace("[STATE]",        $lang->active ? 'enabled' : 'disabled', $_tmp);
				$_tmp = str_replace("[CHECKBOX_ACTIVE]", $lang->active ? 'checked="checked"' : '', $_tmp);
				$html .= $_tmp;
				$cnt += 1;
			}
			return str_replace($match[0], $html, $tpl);
		}
		return $tpl;
	}

	/**
	 * Create Standard/Updates in Database and perhaps other things if needed
	 *
	 * @param integer $version Current version from Database
	 */
	public function _checkDatabaseUpdates($version=0) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;

		// in this Update we will install german, english, italian, french, spain
		if ($version < 2006060700) {
			// The Table is created in the SITE-Plugin
			// German is added in the SITE-Plugin
			$_langArray = array(array('english','en'),array('french','fr'),array('italian','it'));
			foreach ($_langArray as $k => $v) {
				$check = "SELECT [lan.id] FROM [table.lan] WHERE [lan.short]='".$v[1]."';";
				$res = $this->DB->ReturnQueryResult($check);
				if (!$res || (mysql_num_rows($res) <= 0)) {
					// German language will be active by default.
					$_act = 0;
					if (strtolower($v[1]) == 'de') {
						$_act = 1;
					}
					$sql  = "INSERT INTO [table.lan] ([field.lan.text],[field.lan.short],[field.lan.char],[field.lan.icon],[field.lan.active])";
					$sql .= " VALUES ('".$v[0]."','".$v[1]."','iso-8859-15','".$v[0].".gif',".$_act.");";
					$db->run($sql, $res);
				}
			}
		}

		// add some languages for iso-8859-5
		if ($version < 2006071500) {
			$_langArray = array(array('bulgaria','bg'), array('macedonia','mk'), array('russia','ru'), array('serbia','sb'), array('ukraine','ua'));
			foreach ($_langArray as $k => $v) {
				$check = "SELECT [lan.id] FROM [table.lan] WHERE [lan.short]='".$v[1]."';";
				$res = $this->DB->ReturnQueryResult($check);
				if (!$res || (mysql_num_rows($res) <= 0)) {
					$_act = 0;
					$sql  = "INSERT INTO [table.lan] ([field.lan.text],[field.lan.short],[field.lan.char],[field.lan.icon],[field.lan.active])";
					$sql .= " VALUES ('".$v[0]."','".$v[1]."','iso-8859-5','".$v[0].".gif',".$_act.");";
					$db->run($sql, $res);
				}
			}
		}

		// add some languages for iso-8859-3
		if ($version < 2006071501) {
			$_langArray = array(array('turkey','tr'), array('malta','mt'), array('gallego','gl'), array('esperanto','eo'));
			foreach ($_langArray as $k => $v) {
				$check = "SELECT [lan.id] FROM [table.lan] WHERE [lan.short]='".$v[1]."';";
				$res = $this->DB->ReturnQueryResult($check);
				if (!$res || (mysql_num_rows($res) <= 0)) {
					$_act = 0;
					$sql  = "INSERT INTO [table.lan] ([field.lan.text],[field.lan.short],[field.lan.char],[field.lan.icon],[field.lan.active])";
					$sql .= " VALUES ('".$v[0]."','".$v[1]."','iso-8859-3','".$v[0].".gif',".$_act.");";
					$db->run($sql, $res);
				}
			}
		}

		// add some languages for iso-8859-2
		if ($version < 2006071502) {
			$_langArray = array(array('kroatia','hr'),array('poland','pl'),array('romania','ro'),array('slovakia','sk'),array('slovenian','sl'),array('czech','cz'),array('hungary','hu'));
			foreach ($_langArray as $k => $v) {
				$check = "SELECT [lan.id] FROM [table.lan] WHERE [lan.short]='".$v[1]."';";
				$res = $this->DB->ReturnQueryResult($check);
				if (!$res || (mysql_num_rows($res) <= 0)) {
					$_act = 0;
					$sql  = "INSERT INTO [table.lan] ([field.lan.text],[field.lan.short],[field.lan.char],[field.lan.icon],[field.lan.active])";
					$sql .= " VALUES ('".$v[0]."','".$v[1]."','iso-8859-2','".$v[0].".gif',".$_act.");";
					$db->run($sql, $res);
				}
			}
		}

		// change turkey to 8859-9
		if ($version < 2006071503) {
			$sql  = "UPDATE [table.lan] SET [field.lan.char]='iso-8859-9' WHERE [field.lan.short]='tr';";
			$db->run($sql, $res);
		}

		// Change and add some iso-8859-2 languages
		if ($version < 2007020601) {
			$sql  = "UPDATE [table.lan] SET [field.lan.short]='sl',[field.lan.text]='slovenian' WHERE [field.lan.short]='si';";
			$db->run($sql, $res);
			$sql  = "UPDATE [table.lan] SET [field.lan.text]='bulgarian' WHERE [field.lan.short]='bg';";
			$db->run($sql, $res);
			$sql  = "UPDATE [table.lan] SET [field.lan.text]='serbian' WHERE [field.lan.short]='sb';";
			$db->run($sql, $res);
			$sql  = "UPDATE [table.lan] SET [field.lan.text]='bulgarian' WHERE [field.lan.short]='bg';";
			$db->run($sql, $res);
			$sql  = "UPDATE [table.lan] SET [field.lan.text]='hungarian' WHERE [field.lan.short]='hu';";
			$db->run($sql, $res);
			$sql  = "UPDATE [table.lan] SET [field.lan.text]='polish' WHERE [field.lan.short]='pl';";
			$db->run($sql, $res);
			$sql  = "UPDATE [table.lan] SET [field.lan.text]='romanian' WHERE [field.lan.short]='ro';";
			$db->run($sql, $res);
			$sql  = "UPDATE [table.lan] SET [field.lan.text]='slovak' WHERE [field.lan.short]='sk';";
			$db->run($sql, $res);

			$_langArray = array(array('bosnian','bs'),array('croatian','hr'));
			foreach ($_langArray as $k => $v) {
				$check = "SELECT [lan.id] FROM [table.lan] WHERE [lan.short]='".$v[1]."';";
				$db->run($check, $res);
				if (!$res->getFirst()) {
					$res = null;
					$_act = 0;
					$sql  = "INSERT INTO [table.lan] ([field.lan.text],[field.lan.short],[field.lan.char],[field.lan.icon],[field.lan.active])";
					$sql .= " VALUES ('".$v[0]."','".$v[1]."','iso-8859-2','".$v[0].".gif',".$_act.");";
					$db->run($sql, $res);
				}
			}
		}

		// add some languages for iso-8859-15 (aka latin-9, revision of iso-8859-1)
		if ($version < 2007020601) {
			$sql = "DELETE FROM [table.lan] WHERE [field.lan.short]='gl' OR [field.lan.short]='sp';";
			$db->run($sql, $res);
			$_langArray = array(array('afrikaans','af'),array('basque','eu'),array('breton','br'),array('catalan','ca'),array('danish','da'),array('dutch','nl'),array('estonian','et'),array('faroese','fo'),array('finnish','fi'),array('galician','gl'),array('icelandic','is'),array('irish','ga'),array('latin','la'),array('luxembourgish','lb'),array('malay','ms'),array('norwegian','no'),array('norwegianbokmal','nb'),array('norwegiannynorsk','nn'),array('occitan','oc'),array('portuguese','pt'),array('rhaetoromanic','rm'),array('scottishgaelic','gd'),array('spanish','es'),array('swahili','sw'),array('swedish','sv'),array('walloon','wa'));
			foreach ($_langArray as $k => $v) {
				$check = "SELECT [lan.id] FROM [table.lan] WHERE [lan.short]='".$v[1]."';";
				$db->run($check, $res);
				if (!$res->getFirst()) {
					$res = null;
					$_act = 0;
					$sql  = "INSERT INTO [table.lan] ([field.lan.text],[field.lan.short],[field.lan.char],[field.lan.icon],[field.lan.active])";
					$sql .= " VALUES ('".$v[0]."','".$v[1]."','iso-8859-15','".$v[0].".gif',".$_act.");";
					$db->run($sql, $res);
				}
			}
		}

		// add some languages for iso-8859-7 (greece only)
		if ($version < 2007020601) {
			$_langArray = array(array('greece','el'));
			foreach ($_langArray as $k => $v) {
				$check = "SELECT [lan.id] FROM [table.lan] WHERE [lan.short]='".$v[1]."';";
				$db->run($check, $res);
				if (!$res->getFirst()) {
					$res = null;
					$_act = 0;
					$sql  = "INSERT INTO [table.lan] ([field.lan.text],[field.lan.short],[field.lan.char],[field.lan.icon],[field.lan.active])";
					$sql .= " VALUES ('".$v[0]."','".$v[1]."','iso-8859-7','".$v[0].".gif',".$_act.");";
					$db->run($sql, $res);
				}
			}
		}

		// add some languages for iso-8859-13 (aka latin-7 [latin-4 previously], revision of iso-8859-4 and iso-8859-10)
		if ($version < 2007020601) {
			$_langArray = array(array('latvian','lv'),array('lithuanian','lt'),array('greenlandic','kl'),array('sami','se'));
			foreach ($_langArray as $k => $v) {
				$check = "SELECT [lan.id] FROM [table.lan] WHERE [lan.short]='".$v[1]."';";
				$db->run($check, $res);
				if (!$res->getFirst()) {
					$res = null;
					$_act = 0;
					$sql  = "INSERT INTO [table.lan] ([field.lan.text],[field.lan.short],[field.lan.char],[field.lan.icon],[field.lan.active])";
					$sql .= " VALUES ('".$v[0]."','".$v[1]."','iso-8859-13','".$v[0].".gif',".$_act.");";
					$db->run($sql, $res);
				}
			}
		}

		// add some languages for iso-8859-11 (thai only)
		if ($version < 2007020601) {
			$_langArray = array(array('thai','th'));
			foreach ($_langArray as $k => $v) {
				$check = "SELECT [lan.id] FROM [table.lan] WHERE [lan.short]='".$v[1]."';";
				$db->run($check, $res);
				if (!$res->getFirst()) {
					$res = null;
					$_act = 0;
					$sql  = "INSERT INTO [table.lan] ([field.lan.text],[field.lan.short],[field.lan.char],[field.lan.icon],[field.lan.active])";
					$sql .= " VALUES ('".$v[0]."','".$v[1]."','iso-8859-11','".$v[0].".gif',".$_act.");";
					$db->run($sql, $res);
				}
			}
		}

		// Change hsort language to ISO 3166-beta2
		if ($version < 2007021500) {
			$_langArray = array(array('greenlandic','gl'),array('sami','no'),array('greece','gr'),array('afrikaans','za'),array('basque','es'),
			                    array('breton','fr'),array('catalan','ad'),array('danish','dk'),array('estonian','ee'),array('faroese','fo'),
			                    array('galician','es'),array('irish','ie'),array('latin','va'),array('luxembourgish','lu'),array('malay','my'),
			                    array('norwegianbokmal','no'),array('norwegiannynorsk','no'),array('occitan','fr'),array('rhaetoromanic','ch'),
			                    array('scottishgaelic','ca-ns'),array('swahili','tz'),array('swedish','se'),array('walloon','be'),array('bosnian','ba'),
			                    array('slovenian','si'),array('serbian','rs'),array('german','de'),array('english','en'),array('french','fr'),
			                    array('italian','it'));
			foreach ($_langArray as $k => $v) {
				$sql  = "UPDATE [table.lan] [field.lan.short]='".$v[1]."' WHERE [field.lan.text]='".$v[0]."';";
				$db->run($sql, $res);
			}
		}

	}

}
?>