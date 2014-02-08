<?php
/** $id$
 * Class which holds a UserAccount
 *
 * @author Lukas Zurschmiede <l.zurschmiede@delightsoftware.com>
 * @copyright 2008 (c) by delight software gmbh
 * @package delightWebProduct
 */

$DBTables['user'] = $tablePrefix."_access_users";
$DBTables['userchilds'] = $tablePrefix."_users_childs";
$DBTables['email'] = $tablePrefix."_users_email";
$DBTables['phone'] = $tablePrefix."_users_phone";
$DBTables['website'] = $tablePrefix."_users_website";
$DBTables['fax'] = $tablePrefix."_users_faxnumbers";

$DBFields['user'] = array(
	'id' => 'id',
	'number' => 'user_number',
	'user' => 'username',
	'password' => 'password',
	'title' => 'title',
	'company' => 'company',
	'surname' => 'surname',
	'lastname' => 'lastname',
	'address' => 'address',
	'zip' => 'zip',
	'city' => 'city',
	'state' => 'state_id',
	'country' => 'country_id',
	'email' => 'email_address',
	'tel' => 'telephone',
	'mobile' => 'telephone_mobile',
	'website' => 'website',
	'registered' => 'registered_at',
	'lastlogin' => 'last_login',
	'registerstate' => 'register_state',
	'key' => 'register_key',
	'update' => 'last_change',
	'disabled' => 'is_disabled',
	'domain' => 'user_domain'
);
$DBFields['userchilds'] = array(
	'user' => 'users_id',
	'child' => 'child_users_id'
);
$DBFields['email'] = array(
	'user' => 'users_id',
	'type' => 'type',
	'value' => 'value'
);
$DBFields['phone'] = array(
	'user' => 'users_id',
	'type' => 'type',
	'value' => 'value'
);
$DBFields['website'] = array(
	'user' => 'users_id',
	'type' => 'type',
	'value' => 'value'
);
$DBFields['fax'] = array(
	'user' => 'users_id',
	'type' => 'type',
	'value' => 'value'
);

class pUserAccount {
	const MODULE_VERSION = 2009111100;
	const REGISTER_STATE_REGISTERED = 0;         // just yet registered user
	const REGISTER_STATE_LOGINPERMITTED = 1;     // standard-user
	const REGISTER_STATE_DISABLED = 2;           // This user is disabled. No access at all
	const REGISTER_STATE_ADMINISTRATOR = 65535;  // this is only for the FULLADMIN

	private $userId;
	private $userData;
	private $childUsers;
	private $clientUID;
	private $tempUsers;
	private $jsonFields = array('usernumber','username','company','title','surname','lastname','address','zip','city');
	private $jsonFieldsImportant = array('usernumber','username','company','surname','lastname');

	public function __construct($userId=0) {
		$this->updateModule();
		$this->userId = (int)$userId;
		$this->userData = new pProperty();
		$this->childUsers = new stdClass();

		$this->userData->define('usernumber', 'string', '0');
		$this->userData->define('username', 'string');
		$this->userData->define('password', 'string');
		$this->userData->define('company', 'string', '');
		$this->userData->define('title', 'string', '');
		$this->userData->define('surname', 'string', '');
		$this->userData->define('lastname', 'string', '');
		$this->userData->define('address', 'string', '');
		$this->userData->define('zip', 'string', '');
		$this->userData->define('city', 'string', '');
		$this->userData->define('state', 'string', '');
		$this->userData->define('country', 'pCountry', new pCountry());
		$this->userData->define('registerdate', 'integer', 0);
		$this->userData->define('lastlogin', 'integer', 0);
		$this->userData->define('registerstate', 'integer', 0);
		$this->userData->define('registerkey', 'string', '');
		$this->userData->define('lastupdate', 'integer', 0);
		$this->userData->define('domain', 'string', '');
		$this->userData->define('disabled', 'boolean', 1);

		$this->loadUserData($this->userId);
	}

	/**
	 * Load Data from this UserID
	 * Set this value while creting the object is much faster
	 *
	 * @param integer $userId The UserID to load Data
	 */
	public function loadByUserid($userId) {
		$this->userId = $userId;
		$this->loadUserData();
	}

	/**
	 * Load a User by its UserNumber
	 *
	 * @param int $usernumber The Usernumber
	 */
	public function loadByUsernumber($usernumber) {
		$this->userId = 0;
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = 'SELECT [user.id] FROM [table.user] WHERE [user.number]='.(int)$usernumber;
		if ((int)$usernumber != 0) {
			$sql .= ' AND [user.domain]=\''.$this->getUserDomain().'\'';
		}
		$sql .= ';';
		$db->run($sql, $res);
		if ($res->getFirst()) {
			$this->userId = $res->{$db->getFieldName('user.id')};
			unset($res);
			$this->loadUserData();
		}
	}

	/**
	 * Load a User by its UserName
	 *
	 * @param String $username The UserName
	 */
	public function loadByUsername($username) {
		$this->userId = 0;
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = 'SELECT [user.id] FROM [table.user] WHERE [user.user]=\''.mysql_real_escape_string($username).'\'';
		if ($username != 'admin') {
			$sql .= ' AND [user.domain]=\''.$this->getUserDomain().'\'';
		}
		$sql .= ';';
		$db->run($sql, $res);
		if ($res->getFirst()) {
			$this->userId = $res->{$db->getFieldName('user.id')};
			unset($res);
			$this->loadUserData();
		}
	}

	/**
	 * Loads userdata by it's Credentials
	 *
	 * @param string $username The Username
	 * @param string $password The corrsponding Password
	 * @param boolean $hash Set to true if the Password is an SHA1-Hash
	 */
	public function loadByCredentials($username, $password, $hash=false) {
		$this->loadUserData($username, $password, $hash);
		if (!empty($this->userId) && ($this->userId > 0)) {
			$db = pDatabaseConnection::getDatabaseInstance();
			$res = null;
			$sql = 'UPDATE [table.user] SET [field.user.lastlogin]='.time().' WHERE [field.user.id]='.$this->userId.';';
			$db->run($sql, $res);
		}
	}

	/**
	 * Return if the USer was loaded or not
	 *
	 * @return boolean
	 * @access public
	 */
	public function isLoaded() {
		return ($this->userId > 0);
	}

	/**
	 * Get a Property from this User
	 *
	 * @param String $property Userdata-Propertyname to get
	 * @return mixed The Value from $property or null
	 */
	public function get($property) {
		if (($property == 'userId') || ($property == 'id')) {
			return $this->userId;
		}
		return $this->userData->{$property};
	}
	public function __get($name) {
		return $this->get($name);
	}

	/**
	 * Set a value for the given attribute on this UserAccount
	 *
	 * @param String $name The attribute to set
	 * @param mixed $value The Value to set for the Attribute
	 * @param boolean $doSave If an SQL-Request should be made or not
	 * @access public
	 */
	public function set($name, $value, $doSave=true) {
		// If both, the name and value is null, we just save all attributes
		if ( ($name == null) && ($value == null)) {
			$doSave = true;

		} else {
			// Set the Attribute
			switch (strtolower($name)) {
				case 'number':
					$this->userData->usernumber = pTypeCast::asInteger($value);
					break;
				case 'username':
					$this->userData->username = $this->cleanUsername(pTypeCast::asString($value));
					break;
				case 'password':
					$this->userData->password = sha1(pTypeCast::asString($value));
					break;
				case 'country':
					$this->userData->country = new pCountry(0, pTypeCast::asString($value));
					break;
				case 'id':
				case 'userid':
					// Canot set the ID/UserID
					break;
				default:
					$this->userData->{strtolower($name)} = pTypeCast::asString($value);
					break;
			}
		}

		// Save all Attributes
		if ($doSave) {
			$this->saveUserAccount();
		}
	}

	/**
	 * Sets the ClientUID (The UID from the Progamm the client uses)
	 *
	 * @param string $uid The Client-Programm-UID
	 */
	public function setClientUID($uid) {
		$this->clientUID = $uid;
	}

	/**
	 * Get the current ClientUID (The UID from the Progamm the client uses)
	 *
	 * @return string The Client-Programm-UID
	 */
	public function getClientUID() {
		return $this->clientUID;
	}

	/**
	 * Check whether the user is disabled or not
	 *
	 * @return boolean
	 */
	public function isDisabled() {
		return $this->get('disabled');
	}

	/**
	 * Disables this UserAccount
	 * The MainAdmin (Usernumber = 0) can't be disabled. This user is enabled always
	 */
	public function setDisabled() {
		if ($this->userData->usernumber != 0) {
			$this->userData->disabled = true;
			if ($this->userId > 0) {
				$db = pDatabaseConnection::getDatabaseInstance();
				$res = null;
				$sql = 'UPDATE [table.user] SET [field.user.disabled]=1 WHERE [field.user.id]='.$this->userId.';';
				$db->run($sql, $res);
			}
		}
	}

	/**
	 * Enables this UserAccount
	 * The MainAdmin (Usernumber = 0) can't be enabled. This user is enabled always
	 */
	public function setEnabled() {
		if ($this->userData->usernumber != 0) {
			$this->userData->disabled = false;
			if ($this->userId > 0) {
				$db = pDatabaseConnection::getDatabaseInstance();
				$res = null;
				$sql = 'UPDATE [table.user] SET [field.user.disabled]=0 WHERE [field.user.id]='.$this->userId.';';
				$db->run($sql, $res);
			}
		}
	}

	/**
	 * Save the User-Account
	 * Insert if this is a new one, or Update if it's an existing one
	 *
	 */
	public function saveUserAccount() {
		// Check if this user is the MainAdmin. If so, enable it
		if ($this->userData->usernumber == 0) {
			$this->userData->disabled = false;
		}

		// Update/Insert the User
		if ($this->userId <= 0) {
			$sql  = "INSERT INTO [table.user] ([field.user.number],[field.user.user],[field.user.password],[field.user.title],[field.user.company],";
			$sql .= "[field.user.surname],[field.user.lastname],[field.user.address],[field.user.zip],[field.user.city],";
			$sql .= "[field.user.state],[field.user.country],[field.user.registered],[field.user.lastlogin],[field.user.registerstate],";
			$sql .= "[field.user.key],[field.user.update],[field.user.disabled],[field.user.domain]) VALUES (";
			$sql .= "".$this->userData->usernumber.",'".$this->userData->username."','".$this->userData->password."','".$this->userData->title."',";
			$sql .= "'".$this->userData->company."','".$this->userData->surname."','".$this->userData->lastname."',";
			$sql .= "'".$this->userData->address."','".$this->userData->zip."','".$this->userData->city."','".$this->userData->state."',";
			$sql .= "".$this->userData->country->get('id').",".time().",0,".self::REGISTER_STATE_DISABLED.",'".pGUID::getGUID()."',".time().",";
			$sql .= "".($this->userData->disabled ? 1 : 0).",'".$this->getUserDomain()."');";

		} else {
			$sql  = "UPDATE [table.user] SET [field.user.number]=".$this->userData->usernumber.",[field.user.user]='".$this->userData->username."',";
			$sql .= "[field.user.password]='".$this->userData->password."',[field.user.company]='".$this->userData->company."',[field.user.title]='".$this->userData->title."',";
			$sql .= "[field.user.surname]='".$this->userData->surname."',[field.user.lastname]='".$this->userData->lastname."',";
			$sql .= "[field.user.address]='".$this->userData->address."',[field.user.zip]='".$this->userData->zip."',";
			$sql .= "[field.user.city]='".$this->userData->city."',[field.user.state]='".$this->userData->state."',";
			$sql .= "[field.user.country]=".$this->userData->country->get('id').",[field.user.lastlogin]=".$this->userData->lastlogin.",";
			$sql .= "[field.user.registerstate]=".$this->userData->registerstate.",";
			$sql .= "[field.user.update]=".time().",[field.user.disabled]=".($this->userData->disabled ? 1 : 0).",[field.user.domain]='".$this->getUserDomain()."'";
			$sql .= " WHERE [field.user.id]=".$this->userId.";";
		}
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$db->run($sql, $res);
		if ($this->userId <= 0) {
			$this->userId = $res->getInsertId();
		}
	}

	/**
	 * Delete this USerAccount and all assigned values and rights
	 * Attention: This action cannot be reversed
	 */
	public function deleteUserAccount() {
		if ($this->userId > 0) {
			$db  = pDatabaseConnection::getDatabaseInstance();
			$res = null;

			$sql = 'DELETE FROM [table.userchilds] WHERE [field.userchilds.user]='.$this->userId.' OR [field.userchilds.child]='.$this->userId.';';
			$db->run($sql, $res);
			$res = null;

			$sql = 'DELETE FROM [table.email] WHERE [field.email.user]='.$this->userId.';';
			$db->run($sql, $res);
			$res = null;

			$sql = 'DELETE FROM [table.phone] WHERE [field.phone.user]='.$this->userId.';';
			$db->run($sql, $res);
			$res = null;

			$sql = 'DELETE FROM [table.website] WHERE [field.website.user]='.$this->userId.';';
			$db->run($sql, $res);
			$res = null;

			$sql = 'DELETE FROM [table.fax] WHERE [field.fax.user]='.$this->userId.';';
			$db->run($sql, $res);
			$res = null;

			$sql = 'DELETE FROM [table.user] WHERE [field.user.id]='.$this->userId.';';
			$db->run($sql, $res);
			$res = null;
		}
	}

	/**
	 * Set a Data-Value on this UserAccount
	 * A Data-Value is for example the EMail-Address in this users office
	 *
	 * valid $type-Values: email,phone,web
	 * valid $category-Values: private,office,mobile
	 *
	 * @param String $type Type of Data to set
	 * @param String $category The Data-Category where to set it
	 * @param String $value The value for it
	 */
	public function setData($type, $category, $value) {
		switch (strtolower($type)) {

		}
	}

	/**
	 * Register a new User
	 *
	 * @param string $username Username
	 * @param string $password Users Password
	 * @param string $email Users EMail-Address
	 * @param boolean $sendActivation if true, send out an activation-EMail
	 * @return string Possible failure
	 */
	public function registerUser($username, $password, $email, $sendActivation=false) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;

		// Clean Username, Password and Email
		if ($username != $this->cleanUsername($username)) {
			return 'invalidusername';
		}
		if ($email != $this->cleanEmailAddress($email)) {
			return 'invalidemail';
		}

		// Check for existent Username
		$sql = 'SELECT [user.id] FROM [table.user] WHERE [user.user]=\''.$username.'\';';
		$db->run($sql, $res);
		if ($res->getFirst()) {
			return 'username';
		}
		$res = null;

		// TODO: Check the EMail-Table - The Field "email" does not exists on Table "user"
		// Check for existent EmailAddress
		$sql = 'SELECT [user.id] FROM [table.user] WHERE [user.email]=\''.$email.'\';';
		$db->run($sql, $res);
		if ($res->getFirst()) {
			return 'email';
		}
		$res = null;

		// Insert the User
		$key = pGUID::getGUID();
		if ($sendActivation) {
			$state = self::REGISTER_STATE_REGISTERED;
		} else {
			$state = self::REGISTER_STATE_LOGINPERMITTED;
		}
		$sql = 'INSERT INTO [table.user] ([field.user.user],[field.user.password],[field.user.key],[field.user.registerstate],[field.user.registered],[field.user.update]) VALUES (\''.$username.'\',\''.sha1($password).'\',\''.$key.'\','.$state.','.time().','.time().');';
		$db->run($sql, $res);
		$this->userId = $res->getInsertId();
		$res = null;

		// TODO: Insert the EMail-Address

		if ($sendActivation) {
			if (!$this->sendRegisterMail($username, $password, $key, $email)) {
				$sql = 'DELETE FROM [table.user] WHERE [user.user]=\''.$username.'\';';
				$db->run($sql, $res);
				$res = null;
				return 'sendfailed';
			}
		}

		return '';
	}

	/**
	 * Confirm a Registration based on the Registration-Key
	 *
	 * @param string $key The Registration-Key
	 * @param boolean $sendActivation If an activation is needed or not
	 * @return boolean if the registration succeed or not
	 */
	public function confirmRegistration($key, $sendActivation=false) {
		if (!empty($key) && $sendActivation) {
			$key = preg_replace('/[^a-z0-9_-]/smi', '', $key);
			$db = pDatabaseConnection::getDatabaseInstance();
			$res = null;
			$sql = 'SELECT [user.id],[user.registerstate] FROM [table.user] WHERE [user.key]=\''.$key.'\';';
			$db->run($sql, $res);
			if ($res->getFirst()) {
				$id = $res->{$db->getFieldName('user.id')};
				$state = (int)$res->{$db->getFieldName('user.registerstate')};
				if ($state == self::REGISTER_STATE_REGISTERED) {
					$res = null;
					$sql = 'UPDATE [table.user] SET [user.registerstate]='.self::REGISTER_STATE_LOGINPERMITTED.',[field.user.update]='.time().' WHERE [user.id]='.$id.';';
					$db->run($sql, $res);
					return true;
				}
			}
			return false;
		}

		return !$sendActivation;
	}

	/**
	 * Sends a Registration-EMail out to the user
	 *
	 * @param string $user The Username
	 * @param string $pass The Password
	 * @param string $key The Registration-Key
	 * @param string $email The USers EMail-Address
	 * @return boolean If the mail could be sent or not
	 */
	protected function sendRegisterMail($user, $pass, $key, $email) {
		if (defined('REGISTER_NEEDS_CONFIRMATION') && REGISTER_NEEDS_CONFIRMATION) {
			$link = 'http://'.$this->getUserDomain().WEB_ROOT.'?confirm='.$key;
			$textContent = '';
			$htmlContent = '';
			$mailFrom = $_SERVER['SERVER_ADMIN'];
			$mailBCC = '';
			$session = pSession::getInstance();

			if (defined('REGISTER_CONFIRMATION_TEXT') && file_exists(TEMPLATE_ABSOLUTE.str_replace('[LANGUAGE]', $session->get('lang'), REGISTER_CONFIRMATION_TEXT))) {
				$textContent = file_get_contents(TEMPLATE_ABSOLUTE.str_replace('[LANGUAGE]', $session->get('lang'), REGISTER_CONFIRMATION_TEXT));
				$textContent = str_replace('[USERNAME]', $user, $textContent);
				$textContent = str_replace('[PASSWORD]', $pass, $textContent);
				$textContent = str_replace('[EMAIL]', $email, $textContent);
				$textContent = str_replace('[REGISTER_KEY]', $key, $textContent);
				$textContent = str_replace('[REGISTER_LINK]', $link, $textContent);
			}
			if (defined('REGISTER_CONFIRMATION_HTML') && file_exists(TEMPLATE_ABSOLUTE.str_replace('[LANGUAGE]', $session->get('lang'), REGISTER_CONFIRMATION_HTML))) {
				$htmlContent = file_get_contents(TEMPLATE_ABSOLUTE.str_replace('[LANGUAGE]', $session->get('lang'), REGISTER_CONFIRMATION_HTML));
				$htmlContent = str_replace('[USERNAME]', $user, $htmlContent);
				$htmlContent = str_replace('[PASSWORD]', $pass, $htmlContent);
				$htmlContent = str_replace('[EMAIL]', $email, $htmlContent);
				$htmlContent = str_replace('[REGISTER_KEY]', $key, $htmlContent);
				$htmlContent = str_replace('[REGISTER_LINK]', $link, $htmlContent);
			}
			if (defined('REGISTER_MAIL_FROM')) {
				$mailFrom = REGISTER_MAIL_FROM;
				if (defined('REGISTER_MAIL_NAME')) {
					$mailFrom = '"'.REGISTER_MAIL_NAME.'" <'.$mailFrom.'>';
				}
			}
			if (defined('REGISTER_MAIL_INFORM_SENDER') && REGISTER_MAIL_INFORM_SENDER) {
				$mailBCC = $mailFrom;
			}

			$mail = new pSimpleMIMEMail();
			$mail->setHTMLContent($htmlContent);
			$mail->setTextContent($textContent);
			$mail->setSubject(DWP_PRODUCT.' Registration');
			return $mail->send($mailFrom, $email, null, $mailBCC);
		}
		return false;
	}

	/**
	 * Remove invalid characters from the Username
	 *
	 * @param string $username
	 * @return String Cleaned Username
	 */
	private function cleanUsername($username) {
		return preg_replace('/[^\w\d\[\]\(\)\{\}\.,\:;$\!?\+@#%&-]+/smi', '', $username);
	}

	/**
	 * Clean EMail-Address
	 *
	 * @param string $email The EMail-Address to clean/validate
	 * @return String The EMail-Address or an empty String if it's an invalid Address
	 */
	private function cleanEmailAddress($email) {
		if (preg_match('/[a-z0-9]([a-z0-9_\.-]+)?@[a-z0-9]([a-z0-9_\.-]+)?\.[a-z]{2,5}/smi', $email)) {
			return $email;
		}
		return '';
	}

	/**
	 * Load all USerdata and create the Propertylist
	 * If $user or $pass is not set, the global $userId is taken for loading
	 *
	 * @param string $user [optional] The Username
	 * @param string $pass [optional] The Password
	 * @param boolean $hash Set to true if the Password is a Hash
	 */
	private function loadUserData($user='', $pass='', $hash=false) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = 'SELECT * FROM [table.user] WHERE ';
		if (!empty($user) && !empty($pass)) {
			if ($hash) {
				$sql .= 'SHA1([user.user])=\''.$user.'\' AND [user.password]=\''.$pass.'\'';
			} else {
				$sql .= '[user.user]=\''.$user.'\' AND [user.password]=\''.sha1($pass).'\'';
			}
			if (($user != 'admin') && (sha1('admin') != $user)) {
				$sql .= ' AND [user.domain]=\''.$this->getUserDomain().'\'';
			}
		} else {
			$sql .= '[user.id]='.(int)$this->userId;
		}
		$sql .= ';';
		$db->run($sql, $res);
		if ($res->getFirst()) {
			$this->userId = (int)$res->{$db->getFieldName('user.id')};
			$this->userData->usernumber = $res->{$db->getFieldName('user.number')};
			$this->userData->username   = $res->{$db->getFieldName('user.user')};
			$this->userData->password   = $res->{$db->getFieldName('user.password')};
			$this->userData->company    = $res->{$db->getFieldName('user.company')};
			$this->userData->title      = $res->{$db->getFieldName('user.title')};
			$this->userData->surname    = $res->{$db->getFieldName('user.surname')};
			$this->userData->lastname   = $res->{$db->getFieldName('user.lastname')};
			$this->userData->address    = $res->{$db->getFieldName('user.address')};
			$this->userData->zip        = $res->{$db->getFieldName('user.zip')};
			$this->userData->city       = $res->{$db->getFieldName('user.city')};
			$this->userData->state      = $res->{$db->getFieldName('user.state')};
			$this->userData->country    = new pCountry((int)$res->{$db->getFieldName('user.country')});
			$this->userData->registerdate  = $res->{$db->getFieldName('user.registered')};
			$this->userData->lastlogin     = $res->{$db->getFieldName('user.lastlogin')};
			$this->userData->registerstate = $res->{$db->getFieldName('user.registerstate')};
			$this->userData->registerkey   = $res->{$db->getFieldName('user.key')};
			$this->userData->lastupdate    = $res->{$db->getFieldName('user.update')};
			$this->userData->domain        = $res->{$db->getFieldName('user.domain')};
			$this->userData->disabled      = ((int)$res->{$db->getFieldName('user.disabled')} == 0) ? false : true;
		} else {
			$this->userId = 0;
		}
		$this->loadChildUsers();
		$this->tempUsers = null;
	}

	/**
	 * Return the current Domain
	 *
	 * @return string The Domain this User currently tries to access to
	 */
	public function getUserDomain() {
		$domain = $_SERVER['HTTP_HOST'];
		$portPos = strpos($domain, ':');
		if ($portPos > 0) {
			$domain = substr($domain, 0, $portPos);
		}
		return $domain;
	}

	/**
	 * Truncate all Userdata as if this would be a new one
	 */
	public function truncateData() {
		$this->userData->usernumber = "";
		$this->userData->username   = "";
		$this->userData->password   = "";
		$this->userData->company    = "";
		$this->userData->title      = "";
		$this->userData->surname    = "";
		$this->userData->lastname   = "";
		$this->userData->address    = "";
		$this->userData->zip        = "";
		$this->userData->city       = "";
		$this->userData->state      = 0;
		$this->userData->country    = new pCountry(0);
		$this->userData->registerdate  = time();
		$this->userData->lastlogin     = 0;
		$this->userData->registerstate = 0;
		$this->userData->registerkey   = pGUID::getGUID();
		$this->userData->lastupdate    = 0;
		$this->userData->domain        = '';
		$this->userData->disabled      = false;

		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = 'DELETE FROM [table.userchilds] WHERE [field.userchilds.user]='.$this->userId.';';
		$db->run($sql, $res);
		$this->loadChildUsers();
		$this->tempUsers = null;
	}

	/**
	 * Load all ChildUsers and store the appropriate ID in $childUsers
	 *
	 * @param intger $uid Load ChildUsers from this UserID
	 * @return stdClass the Parent $parent with all Childs
	 * @access private
	 */
	private function loadChildUsers($uid = 0, stdClass $parent = null) {
		if ($this->userId > 0) {
			$db = pDatabaseConnection::getDatabaseInstance();
			$res = null;

			if (!is_array($this->tempUsers)) {
				$this->tempUsers = array();
			}

			$uid = ((int)$uid <= 0) ? (int)$this->userId : (int)$uid;
			$child = null;

			if (!$parent instanceof stdClass) {
				$parent = &$this->childUsers;
				$parent = new stdClass();
				$parent->user = $uid;
				$parent->number = $this->getUserNumberById($uid);
				$parent->childs = array();
				$res = null;
			}

			$sql = 'SELECT [userchilds.child] FROM [table.userchilds]'.
			' WHERE [userchilds.user]='.$uid.' AND [userchilds.child]!='.$uid.''.
			' ORDER BY [userchilds.child] ASC;';
			$db->run($sql, $res);

			if ($res->getFirst()) {
				while ($res->getNext()) {
					if (!in_array($res->{$db->getFieldName('userchilds.child')}, $this->tempUsers)) {
						$child = new stdClass();
						$child->user = $res->{$db->getFieldName('userchilds.child')};
						$child->number = $this->getUserNumberById($child->user);
						$child->childs = array();
						$this->tempUsers[] = $child->user;
						$child = $this->loadChildUsers($child->user, $child);
						$parent->childs[] = $child;
					}
				}
			}
		}
		return $parent;
	}

	/**
	 * Get the UserNumber based on the ID
	 *
	 * @param int $userId The UserID to get the UserNumber from
	 * @return int The UserNumber or 0 instead
	 */
	private function getUserNumberById($userId) {
		$num = 0;
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = 'SELECT [user.number] FROM [table.user] WHERE [user.id]='.$userId.';';
		$db->run($sql, $res);
		if ($res->getFirst()) {
			$num = (int)$res->{$db->getFieldName('user.number')};
		}
		return $num;
	}

	/**
	 * Check if this User is the parent of the given one
	 *
	 * @param int $userNumber The UserNumber (not ID) of the user to check if it is a child of this one
	 * @return boolean If the userNumber is a child of this user
	 */
	public function hasChildUser($userNumber) {
		$user = $this->searchChildByNumber((int)$userNumber, array($this->childUsers), false);
		return ($user == $userNumber);
	}

	/**
	 * Check if this User is the parent of the given one
	 *
	 * @param int $userId The UserID (not Number) of the user to check if it is a child of this one
	 * @return boolean If the UserID is a child of this user
	 */
	public function hasChildId($userId) {
		$user = $this->searchChildById((int)$userId, array($this->childUsers));
		return ($user == $userId);
	}

	/**
	 * Get all ChildUsers
	 *
	 * @param boolean $plain [optional, default=true] If a flat-List should be returned, if false, a stdClass-Tree is returned
	 * @return array/stdClass if $plain is true, an Array is returned. Otherwise a stdClass
	 */
	public function getAllChildUsers($plain=true) {
		if (!$plain) {
			return $this->childUsers;
		} else {
			return $this->getPlainChildList($this->childUsers);
		}
	}

	/**
	 * Get a plain List from all ChildUsers the given User is the Master-Parent from
	 *
	 * @param stdClass $parent The parent to get all Childs from as an array
	 * @return array All Child Users
	 */
	private function getPlainChildList(stdClass $parent) {
		$list = array();
		if (is_array($parent->childs)) {
			foreach ($parent->childs as $child) {
				$list[] = $child->user;
				$list = array_merge($list, $this->getPlainChildList($child));
			}
		}
		return array_unique($list);
	}

	/**
	 * Search the UserNumber inside the childUsers and return it's UserID if found or -1 otherwise
	 *
	 * @param int $userNumber The UserNumber to search for
	 * @param array $list A subPart of $this->childUsers
	 * @param boolean $returnId if the UserID should be returned - if false, the userNumber will
	 * @return int The UserID or -1
	 */
	private function searchChildByNumber($userNumber, $list, $returnId=true) {
		$found = -1;
		foreach ($list as $child) {
			if ($child instanceof stdClass) {
				if ($child->number == $userNumber) {
					$found = $returnId ? $child->user : $child->number;
				} else {
					$found = $this->searchChildByNumber($userNumber, $child->childs, $returnId);
				}
				if ($found >= 0) {
					break;
				}
			}
		}
		return $found;
	}

	/**
	 * Search the UserId inside the childUsers and return it's UserID if found or -1 otherwise
	 *
	 * @param int $userId The UserId to search for
	 * @param array $list A subPart of $this->childUsers
	 * @param boolean $returnId if the UserID should be returned - if false, the userNumber will
	 * @return int The UserID or -1
	 */
	private function searchChildById($userId, $list, $returnId=true) {
		$found = -1;
		foreach ($list as $child) {
			if ($child instanceof stdClass) {
				if ($child->user == $userId) {
					$found = $returnId ? $child->user : $child->number;
				} else {
					$found = $this->searchChildById($userId, $child->childs);
				}
				if ($found >= 0) {
					break;
				}
			}
		}
		return $found;
	}

	/**
	 * Check if the user has the given Right
	 *
	 * The given right can be one of:
	 * 	  pUserAccount::REGISTER_STATE_REGISTERED
	 *    pUserAccount::REGISTER_STATE_LOGINPERMITTED
	 *    pUserAccount::REGISTER_STATE_DISABLED
	 *    pUserAccount::REGISTER_STATE_ADMINISTRATOR
	 * or a userdefined REGISTER_STATE_* from userconf.inc.php
	 * In any way, it must be an Integer based on 2^x [ -> pow(2,x) ]
	 *
	 * @param integer $right The right to check for
	 * @return boolean If the user has this right or not
	 * @access public
	 */
	public function hasRight($right = 0) {
		if ($this->userData->registerstate > 0) {
			return ( ($this->userData->registerstate & (int)$right) == (int)$right);
		}
		return false;
	}

	/**
	 * Add a right to the user
	 *
	 * The given right can be one of:
	 * 	  pUserAccount::REGISTER_STATE_REGISTERED
	 *    pUserAccount::REGISTER_STATE_LOGINPERMITTED
	 *    pUserAccount::REGISTER_STATE_DISABLED
	 *    pUserAccount::REGISTER_STATE_ADMINISTRATOR
	 * or a userdefined REGISTER_STATE_* from userconf.inc.php
	 * In any way, it must be an Integer based on 2^x [ -> pow(2,x) ]
	 *
	 * @param integer $right The give this user
	 * @access public
	 */
	public function addRight($right = 0) {
		if ($this->userId > 0) {
			$this->userData->registerstate = $this->userData->registerstate | $right;
			$db = pDatabaseConnection::getDatabaseInstance();
			$res = null;
			$sql = 'UPDATE [table.user] SET [field.user.registerstate]='.(int)$this->userData->registerstate.' WHERE [field.user.id]='.$this->userId.';';
			$db->run($sql, $res);
		}
	}

	/**
	 * Add a right to the user
	 *
	 * The given right can be one of:
	 * 	  pUserAccount::REGISTER_STATE_REGISTERED
	 *    pUserAccount::REGISTER_STATE_LOGINPERMITTED
	 *    pUserAccount::REGISTER_STATE_DISABLED
	 *    pUserAccount::REGISTER_STATE_ADMINISTRATOR
	 * or a userdefined REGISTER_STATE_* from userconf.inc.php
	 * In any way, it must be an Integer based on 2^x [ -> pow(2,x) ]
	 *
	 * @param array $rights List of Rights to give this user
	 * @access public
	 */
	public function setRight($rights = array()) {
		if ($this->userId > 0) {
			$this->userData->registerstate = (int)self::REGISTER_STATE_REGISTERED;
			$newRight = (int)self::REGISTER_STATE_REGISTERED;
			foreach ($rights as $right) {
				$newRight = $newRight | (int)$right;
			}
			$this->addRight($newRight);
		}
	}

	/**
	 * Get all data as a JSON-Object
	 *
	 * @param boolean $encode If the result should be json-string or not
	 * @param boolean $onlyImportant Just get the important userdata and not all
	 * @access public
	 * @return string/stdClass
	 */
	public function getJSONObject($encode=true, $onlyImportant=false) {
		$back = new stdClass();
		$back->id = $this->userId;
		if ($onlyImportant) {
			$fields = $this->jsonFieldsImportant;
		} else {
			$fields = $this->jsonFields;
		}
		foreach ($fields as $key) {
			$back->{$key} = $this->userData->{$key};
		}
		if (empty($back->username)) {
			$back->username = '-';
		}
		return $encode ? json_encode($back) : $back;
	}

	/**
	 * Interface-Function for updateing the Module
	 */
	public function updateModule() {
		// first get the version stored in the Database
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$version = $db->getModuleVersion(get_class($this));

		// Check if we need an Update
		if (self::MODULE_VERSION > $version) {
			// initial
			if ($version <= 0) {
				$sql = 'CREATE TABLE IF NOT EXISTS [table.user] ('.
				' [field.user.id] INT(11) UNSIGNED NOT NULL auto_increment,'.
				' [field.user.number] INT(11) UNSIGNED NOT NULL default 0,'.
				' [field.user.user] VARCHAR(50) NOT NULL default \'\','.
				' [field.user.password] VARCHAR(150) NOT NULL default \'\','.
				' [field.user.domain] VARCHAR(100) NOT NULL default \'\','.
				' [field.user.surname] VARCHAR(50) NOT NULL default \'\','.
				' [field.user.lastname] VARCHAR(50) NOT NULL default \'\','.
				' [field.user.company] VARCHAR(250) NOT NULL default \'\','.
				' [field.user.address] VARCHAR(50) NOT NULL default \'\','.
				' [field.user.zip] VARCHAR(10) NOT NULL default \'\','.
				' [field.user.city] VARCHAR(50) NOT NULL default \'\','.
				' [field.user.state] INT(11) UNSIGNED NOT NULL default 0,'.
				' [field.user.country] INT(11) UNSIGNED NOT NULL default 0,'.
				' [field.user.registered] INT(11) UNSIGNED NOT NULL default 0,'.
				' [field.user.lastlogin] INT(11) UNSIGNED NOT NULL default 0,'.
				' [field.user.registerstate] INT(11) UNSIGNED NOT NULL default 0,'.
				' [field.user.key] VARCHAR(70) NOT NULL default \'\','.
				' [field.user.update] INT(11) UNSIGNED NOT NULL default 0,'.
				' [field.user.disabled] TINYINT(1) NOT NULL default 0,'.
				' KEY [field.user.number] ([field.user.number]),'.
				' KEY [field.user.user] ([field.user.user]),'.
				' KEY [field.user.country] ([field.user.country]),'.
				' UNIQUE KEY [field.user.id] ([field.user.id])'.
				');';
				$db->run($sql, $res);

				$sql = 'CREATE TABLE IF NOT EXISTS [table.email] ('.
				' [field.email.user] INT(11) UNSIGNED NOT NULL default 0,'.
				' [field.email.type] VARCHAR(10) NOT NULL default \'private\','.
				' [field.email.value] VARCHAR(100) NOT NULL default \'\','.
				' KEY [field.email.user] ([field.email.user])'.
				');';
				$db->run($sql, $res);

				$sql = 'CREATE TABLE IF NOT EXISTS [table.phone] ('.
				' [field.phone.user] INT(11) UNSIGNED NOT NULL default 0,'.
				' [field.phone.type] VARCHAR(10) NOT NULL default \'private\','.
				' [field.phone.value] VARCHAR(100) NOT NULL default \'\','.
				' KEY [field.phone.user] ([field.phone.user])'.
				');';
				$db->run($sql, $res);

				$sql = 'CREATE TABLE IF NOT EXISTS [table.website] ('.
				' [field.website.user] INT(11) UNSIGNED NOT NULL default 0,'.
				' [field.website.type] VARCHAR(10) NOT NULL default \'private\','.
				' [field.website.value] VARCHAR(250) NOT NULL default \'\','.
				' KEY [field.website.user] ([field.website.user])'.
				');';
				$db->run($sql, $res);

				$sql = 'CREATE TABLE IF NOT EXISTS [table.fax] ('.
				' [field.fax.user] INT(11) UNSIGNED NOT NULL default 0,'.
				' [field.fax.type] VARCHAR(10) NOT NULL default \'private\','.
				' [field.fax.value] VARCHAR(250) NOT NULL default \'\','.
				' KEY [field.fax.user] ([field.fax.user])'.
				');';
				$db->run($sql, $res);

				$sql = 'INSERT INTO [table.user] ([field.user.number],[field.user.user],[field.user.password],[field.user.registerstate])'.
				' VALUES (\''.DEFAULT_ADMIN_NUMBER.'\',\''.DEFAULT_ADMIN_USER.'\',SHA1(\''.DEFAULT_ADMIN_PASSWORD.'\'),\''.DEFAULT_ADMIN_ACCESS.'\');';
				$db->run($sql, $res);
			}

			// Forgotten title
			if ($version < 2009080400) {
				$sql = 'ALTER TABLE [table.user] ADD COLUMN [field.user.title] VARCHAR(20) NOT NULL default \'\';';
				$db->run($sql, $res);
			}

			if ($version < 2009111100) {
				$sql = 'CREATE TABLE IF NOT EXISTS [table.userchilds] ('.
				' [field.userchilds.user] INT(11) UNSIGNED NOT NULL default 0,'.
				' [field.userchilds.child] INT(11) UNSIGNED NOT NULL default 0,'.
				' KEY [field.userchilds.user] ([field.userchilds.user])'.
				');';
				$db->run($sql, $res);
			}

			// Update the version
			$db->updateModuleVersion(get_class($this), self::MODULE_VERSION);
		}
	}

}

?>