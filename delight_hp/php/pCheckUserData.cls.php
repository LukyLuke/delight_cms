<?php

class pCheckUserData {
	const MODULE_VERSION = 2009072302;
	private static $instance = null;
	private $userName = '';
	private $userPassword = '';
	private $isLoggedIn = false;
	private $hashUser = '';
	private $hashPass = '';
	private $user = null;

	private function __construct() {
		$this->user = new pUserAccount();
		$this->updateDatabase();
	}

	/**
	 * Get a pCheckUserData instance
	 *
	 * @return pCheckUserData
	 * @access private
	 */
	public static function getInstance() {
		if (is_null(self::$instance)) {
			self::$instance = new pCheckUserData();
		}
		return self::$instance;
	}

	/**
	 * Return the LoggedIn Person
	 *
	 * @return pUserAccount
	 * @access public
	 */
	public function getPerson() {
		return $this->user;
	}

	/**
	 * Set Username and Password HASH
	 *
	 * @param string $hash The Hash
	 * @access public
	 */
	public function setLoginHashData($hash) {
		$para = explode('::', $hash);
		if (count($para) != 2) {
			$para = array('','');
		}
		$this->hashUser = $para[0];
		$this->hashPass = $para[1];
		$this->userName = '';
		$this->userPassword = '';
	}

	/**
	 * Set (base64-Encoded) Username and Password
	 *
	 * @param string $username Username
	 * @param string $password Password
	 * @param boolean $encoded Optional: If the Username and the Password is base64 Encoded or not
	 */
	public function setUserData($username, $password, $encoded=false) {
		$this->userName = trim($encoded ? base64_decode($username) : $username);
		$this->userPassword = trim($encoded ? base64_decode($password) : $password);
		$this->hashPass = '';
		$this->hashUser = '';
	}

	/**
	 * Return a Username/Password Hash-Combination for future login
	 *
	 * @return string
	 * @access public
	 */
	public function getHashString() {
		$back = '';
		if (!empty($this->userName) && !empty($this->userPassword)) {
			$back = sha1($this->userName).'::'.sha1($this->userPassword);
		}
		return $back;
	}

	/**
	 * Logout the current User
	 *
	 * @access public
	 */
	public function doLogout() {
		$this->logLoginLogout(false, $this->user->get('username'));
		$this->user = new pUserAccount();
		$this->userName = '';
		$this->userPassword = '';
		$this->hashPass = '';
		$this->hashUser = '';
	}

	/**
	 * Check for valid LoginData
	 *
	 * @return boolean
	 * @access public
	 */
	public function checkLogin() {
		if ( !empty($this->userName) && !empty($this->userPassword) ) {
			$this->user->loadByCredentials($this->userName, $this->userPassword);
			if ( ($this->user instanceof pUserAccount) && ($this->user->isLoaded()) ) {
				$this->logLoginLogout(true);
			}

		} else if ( !empty($this->hashUser) && !empty($this->hashPass) ) {
			$this->user->loadByCredentials($this->hashUser, $this->hashPass, true);
			if ( ($this->user instanceof pUserAccount) && ($this->user->isLoaded()) ) {
				$this->logLoginLogout(true);
			}
		}

		return ( ($this->user instanceof pUserAccount) && ($this->user->isLoaded()) );
	}

	// DEPRECATED
	public function CheckUserInfo($para='') {
		return false;
		/*if (strlen(trim($para)) <= 0) {
			$back = false;
		} else {
			if (substr_count($this->userData[$this->DB->FieldOnly('per','info')], "#".$para."#") > 0) {
				$back = true;
			} else {
				$back = false;
			}
		}
		return $back;*/
	}

	// DEPRECATED
	function getUserAccess() {
		//return (integer)$this->userData[$this->DB->FieldOnly('per','right')];
		return 0;
	}

	/**
	 * Check for access
	 *
	 * @param string $type Access-Type (see RGT_[type] constants)
	 * @return boolean
	 * @access public
	 */
	public function checkAccess($type='') {
		if ( !($this->user instanceof pUserAccount) || (!$this->user->isLoaded()) ) {
			return false;
		}

		$const = '';
		if ( defined('RGT_'.strtoupper($type)) ) {
			$const = 'RGT_'.strtoupper($type);
		} else if (defined('ADM_'.strtoupper($type))) {
			$const = 'ADM_'.strtoupper($type);
		}
		$right = defined($const) ? constant($const) : 0;

		return $this->user->hasRight((int)$right);
	}

	/**
	 * Get all UserGroup IDs from given User
	 *
	 * @return array
	 * @access public
	 */
	public function getUserGroups() {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = 'SELECT [usrgrp.group] FROM [table.usrgrp] WHERE [usrgrp.user]='.(int)$this->user->get('userId').';';
		$db->run($sql, $res);
		$back = array();
		if ($res->getFirst()) {
			while ($res->getNext()) {
				$back[] = (int)$res->{$db->getFieldName('usrgrp.group')};
			}
		}
		return $back;
	}

	/**
	 * Replace each MENU_ACCESS_GROUPS:x,y,... with it's Content or with nothing
	 *
	 * @param string $html String to replace each ocurence
	 * @return string
	 * @access public
	 */
	public function replaceMenuAccessGroups($html) {
		$match = array();
		$groups = $this->getUserGroups();
		if (preg_match_all('/(\[MENU_ACCESS_GROUPS_(\d+)\:)([\d,]+)(\])(.*?)(\[\/MENU_ACCESS_GROUPS_\\2\])/smi', $html, $match, PREG_SET_ORDER)) {
			foreach ($match as $m) {
				$access = false;
				$menuGroups = explode(',', $m[3]);
				foreach ($menuGroups as $gid) {
					if (in_array((int)$gid, $groups)) {
						$access = true;
						break;
					}
				}

				if ($access) {
					$m[5] = $this->replaceMenuAccessGroups($m[5]);
					$html = str_replace($m[0], $m[5], $html);
				}
			}
		}


		$user = $this->getPerson();
		if (($user instanceof pUserAccount) && $user->isLoaded() && !$user->isDisabled()) {
			$html = preg_replace('/\[LOGIN_INFORMATION\](.*?)\[\/LOGIN_INFORMATION\]/smi', '\\1', $html);
			$html = str_replace('[LOGIN_NAME]', $user->username, $html);
			$html = str_replace('[LOGIN_SURNAME]', $user->surname, $html);
			$html = str_replace('[LOGIN_LASTNAME]', $user->lastname, $html);
			$html = str_replace('[LOGIN_TITLE]', $user->title, $html);
			$html = str_replace('[LOGIN_COMPANY]', $user->company, $html);
			$html = str_replace('[LOGIN_DOMAIN]', $user->domain, $html);
			if (!$this->checkAccess('content')) {
				$html = preg_replace('/\[LOGIN_FORM\].*\[\/LOGIN_FORM\]/smi', '', $html);
			}
		}

		$html = str_replace('[LOGIN_FORM]', '', $html);
		$html = str_replace('[/LOGIN_FORM]', '', $html);
		$html = preg_replace('/\[LOGIN_INFORMATION\](.*?)\[\/LOGIN_INFORMATION\]/smi', '', $html);

		// TODO: Implement here an Attribute on the Tag to be shown if the USer has no access
		//       For example to show when the User directly connects a text/download/image/news page
		$html = preg_replace('/\[MENU_ACCESS_GROUPS_(\d+):([\d,]+)\](.*?)\[\/MENU_ACCESS_GROUPS_\\1\]/smi', '', $html);
		return $html;
	}

	/**
	 * All Persons they have more rights than RGT_DOWNLOAD should be able to visit dynamic pages
	 * Persons with RGT_DOWNLOAD have limited access to dynamit pages - downloads will be visible
	 *
	 * TODO: This has to be combined with the UserGroups
	 *
	 * @return boolean
	 */
	public function showStaticFiles() {
		if ( !($this->user instanceof pUserAccount) || (!$this->user->isLoaded()) ) {
			return true;
		}
		return ($this->user->get('registerstate') <= RGT_DOWNLOAD);
	}

	/**
	 * Log Login/Logout Actions
	 *
	 * @param boolean $isLogin if this is a Login or a Logout Logging
	 * @param string $user Additional Username
	 * @access private
	 */
	protected function logLoginLogout($isLogin=false, $user="") {
		global $_SERVER;
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = 'INSERT INTO [table.plo]';
		if ($isLogin) {
			$sql .= ' SET [field.plo.user]=\''.$this->user->get('username').'\',[field.plo.action]=\'login\'';
		} else {
			$sql .= ' SET [field.plo.user]=\''.$user.'\',[field.plo.action]=\'logout\'';
		}
		$sql .= ',[field.plo.time]=\''.date("Y-m-d H:i:s").'\'';
		$sql .= ',[field.plo.ip]=\''.$_SERVER['REMOTE_ADDR'].'\'';
		$sql .= ',[field.plo.domain]=\''.gethostbyaddr($_SERVER['REMOTE_ADDR']).'\'';
		$sql .= ',[field.plo.info]=\'\'';
		$db->run($sql, $res);
	}

	/**
	 * Check Database integrity
	 *
	 * This function creates all required Tables, Updates, Inserts and Deletes.
	 */
	protected function updateDatabase() {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$version = $db->getModuleVersion(get_class($this));

		// for migration only
		if ($version == 0) {
			$sql = 'SELECT [opt.version] FROM [table.opt] WHERE [opt.name]=\''.get_class($this).'\';';
			$db->run($sql, $res);
			if ($res->getFirst()) {
				$version = $res->{$db->getFieldName('opt.version')};
			}
			$res = null;
		}

		if (self::MODULE_VERSION > $version) {
			// Initial
			if ($version <= 0) {
				// Create the Persons-Table
				$sql  = "CREATE TABLE IF NOT EXISTS [table.per] (".
				" [field.per.id] INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,".
				" [field.per.right] INT(10) UNSIGNED NOT NULL DEFAULT 0,".
				" [field.per.user] VARCHAR(50) NOT NULL DEFAULT '',".
				" [field.per.passwd] VARCHAR(50) NOT NULL DEFAULT '',".
				" [field.per.clear] VARCHAR(50) NOT NULL DEFAULT '',".
				" [field.per.company] VARCHAR(100) NOT NULL DEFAULT '',".
				" [field.per.name] VARCHAR(50) NOT NULL DEFAULT '',".
				" [field.per.surname] VARCHAR(50) NOT NULL DEFAULT '',".
				" [field.per.address] VARCHAR(50) NOT NULL DEFAULT '',".
				" [field.per.postalcode] VARCHAR(10) NOT NULL DEFAULT '',".
				" [field.per.city] VARCHAR(50) NOT NULL DEFAULT '',".
				" [field.per.country] INT(10) UNSIGNED NOT NULL DEFAULT 0,".
				" [field.per.email] VARCHAR(100) NOT NULL DEFAULT '',".
				" [field.per.internet] VARCHAR(200) NOT NULL DEFAULT '',".
				" [field.per.info] TEXT NOT NULL DEFAULT '',".
				" PRIMARY KEY (id),".
				" UNIQUE KEY id (id)".
				" ) TYPE=MyISAM;";
				$res = null;
				$db->run($sql, $res);

				// Insert base-Adminuser if not already exists
				$sql = "SELECT [per.user] FROM [table.per] WHERE [per.user]='admin'";
				$res = null;
				$db->run($sql, $res);
				if (!$res->getFirst()) {
					$sql = "INSERT INTO [table.per]".
					" ([field.per.right],[field.per.user],[field.per.passwd],[field.per.clear],[field.per.company],[field.per.name],[field.per.surname],[field.per.address],[field.per.postalcode],[field.per.city],[field.per.country],[field.per.email],[field.per.internet],[field.per.info])".
					" VALUES (".RGT_FULLADMIN.",'admin',SHA1('admin'),'admin','your company','Admin','Admin','','','','','postmaster@yourdomain.local','http://www.yourdomain.local/','');";
					$res = null;
					$db->run($sql, $res);
					$res = null;
				}
				$res = null;
			}

			// Update the version
			$db->updateModuleVersion(get_class($this), self::MODULE_VERSION);
		}
	}

}
?>
