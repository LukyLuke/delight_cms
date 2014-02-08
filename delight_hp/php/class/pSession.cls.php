<?php
/** $id$
 * Class for handling Sessions
 *
 * @author Lukas Zurschmiede <l.zurschmiede@delightsoftware.com>
 * @copyright (c) 2008 by delight software gmbh
 *
 * @package delightWebProduct
 * @version 1.0
 */

// We need this constant and won't throw an Exception if it is not defined
if (!defined('SESSION_USE_PHP')) {
	define('SESSION_USE_PHP', true);
}
if (!defined('SESSION_TIMEOUT')) {
	define('SESSION_TIMEOUT', 1200);
}


$DBTables['session'] = $tablePrefix."_sessions";
$DBFields['session'] = array(
	'id' => 'id',
	'ip' => 'ip_address',
	'browser' => 'client_browser',
	'data' => 'session_data',
	'time' => 'last_update'
);

/**
 * At verry last, Destroy and save the Session
 * This must be also done by a ScriptTimeout so we need to register the Shutdownfunction by
 *   register_shutdown_function('globalSaveSession', $session)
 */
function globalSaveSession(&$sessVar) {
	unset($sessVar);
}

class pSession extends pProperty implements iUpdateIface {
	const MODULE_VERSION = 2008030400;

	private static $instance = null;

	private function __construct() {
		$domain = $_SERVER['HTTP_HOST'];
		if (($pos = strpos($domain, ':')) > 0) {
			$domain = substr($domain, 0, $pos);
		}

		// Update the Database
		if (SESSION_USE_PHP) {
			session_name(SESSION_NAME);
			if (version_compare(phpversion(), '5.2.0') >= 0) {
				session_set_cookie_params(SESSION_TIMEOUT, WEB_ROOT, $domain, false, false);
			} else {
				session_set_cookie_params(SESSION_TIMEOUT, WEB_ROOT, $domain, false);
			}
			session_start();

			// this is needed after session_start to adjust the session_timeout after the session was started - strange but needed
			if (array_key_exists(session_name(), $_COOKIE)) {
				if (version_compare(phpversion(), '5.2.0') >= 0) {
					setcookie(session_name(), $_COOKIE[session_name()], time()+SESSION_TIMEOUT, WEB_ROOT, $domain, false, false);
				} else {
					setcookie(session_name(), $_COOKIE[session_name()], time()+SESSION_TIMEOUT, WEB_ROOT, $domain, false);
				}
			}
		} else {
			$this->updateModule();
			$this->cleanSessions();
		}

		// create properties
		$this->define('sessionData', 'pProperty', new pProperty());
		$this->define('sessionId', 'string', '');
		$this->load();
	}

	/**
	 * Get a Session-Instance
	 *
	 * @return pSession
	 * @access public
	 * @static yes
	 */
	public static function getInstance() {
		if (pSession::$instance == null) {
			pSession::$instance = new pSession();
		}
		return pSession::$instance;
	}

	/**
	 * Called while destructing the Session
	 * Save the Session in Database
	 */
	public function __destruct() {
		if (SESSION_USE_PHP) {
			$_SESSION['data'] = base64_encode(serialize($this->sessionData));
			session_commit();
		} else {
			$res = null;
			$sql = 'UPDATE [table.session] SET [session.data]=\''.base64_encode(serialize($this->sessionData)).'\',[session.time]='.time().' WHERE [session.id]=\''.$this->sessionId.'\'';
			$db = pDatabaseConnection::getDatabaseInstance();
			$db->run($sql, $res);
		}
	}

	/**
	 * Loads a previous session or create a new one
	 *
	 */
	public function load() {
		if (SESSION_USE_PHP) {
			if (!isset($_SESSION['data'])) {
				$this->createNewSession();
			} else {
				$this->sessionData = unserialize(base64_decode($_SESSION['data']));
			}
		} else {
			// get the old session-id if one is available
			if (array_key_exists(SESSION_NAME, $_COOKIE)) {
				$oldId = $_COOKIE[SESSION_NAME];
			} else {
				$oldId = '';
			}
			$_ip = $_SERVER['REMOTE_ADDR'];
			$_ag = $_SERVER['HTTP_USER_AGENT'];
			$_tm = time() - SESSION_TIMEOUT;
			$res = null;
			$db = pDatabaseConnection::getDatabaseInstance();

			if (!empty($oldId)) {
				$sql = 'SELECT [session.data] FROM [table.session] WHERE [session.id]=\''.$oldId.'\''.
				' AND [session.ip]=\''.$_ip.'\''.
				' AND [session.browser]=\''.$_ag.'\''.
				' AND [session.time]>='.$_tm.';';
				$db->run($sql, $res);

				// get the Session-Data
				if ($res->getFirst()) {
					$this->sessionData = unserialize(base64_decode($res->{$db->getFieldName('session.data')}));
					$this->sessionId = $oldId;
					$res = null;

					// We update the Session-time
					$this->updateSessionTime();
				} else {
					$this->createNewSession();
				}
			} else {
				$this->createNewSession();
			}
		}
	}

	/**
	 * Set a Session-Variable
	 *
	 * @param string $name name of variable
	 * @param mixed $value Value for $name
	 */
	public function set($name, $value) {
		$session = $this->sessionData;
		if (!($session instanceof pProperty)) {
			$session = new pProperty();
		}
		$session->defineIfNotDefined($name, (gettype($value)=='object')?get_class($value):gettype($value), $value);
		$session->{$name} = $value;
		$this->sessionData = $session;
	}

	/**
	 * Get a Session-Variable
	 *
	 * @param string $name name of variable
	 * @param mixed $default The default value if there is no variable by this name
	 * @return mixed Value from $name
	 */
	public function get($name, $default=null) {
		if ($name == 'sessionId') {
			return $this->sessionId;
		}
		$back = $this->sessionData->{$name};
		if ($back == null) {
			$back = $default;
		}
		return $back;
	}

	/**
	 * Undefine a Session-Variable and its Value
	 *
	 * @param String $name Variablename to remove
	 */
	public function unregisterVariable($name) {
		$session = $this->sessionData;
		if ($session instanceof pProperty) {
			$session->undefine($name);
			$this->sessionData = $session;
		}
	}

	/**
	 * Clean all data from this session
	 *
	 */
	public function cleanSessionData() {
		if (SESSION_USE_PHP) {
			session_destroy();
		} else {
			$this->createNewSession();
		}
	}

	/**
	 * Create a new Session
	 *
	 */
	protected function createNewSession() {
		if (SESSION_USE_PHP) {
			// Create a new PHP-Session
			$this->sessionId = session_id();
			$_SESSION['data'] = new pProperty();

		} else {
			// Create a new Database-Session
			$this->sessionId = $this->createSessionId();
			$this->sessionData = new pProperty();
			$_ip = $_SERVER['REMOTE_ADDR'];
			$_ua = $_SERVER['HTTP_USER_AGENT'];

			$sql = 'INSERT INTO [table.session] ([session.data],[session.id],[session.time],[session.browser],[session.ip]) VALUES ('.
			       '\''.base64_encode(serialize($this->sessionData)).'\',\''.$this->sessionId.'\','.time().',\''.$_ua.'\',\''.$_ip.'\');';
			$db = pDatabaseConnection::getDatabaseInstance();
			$res = null;
			$db->run($sql, $res);

			setcookie(SESSION_NAME, $this->sessionId, time()+3600, '/');
		}
	}

	/**
	 * Create and return a Session-ID - a GUID in version 4
	 * based on http://www.ietf.org/rfc/rfc4122.txt
	 *
	 * @return string A Session-ID
	 */
	protected function createSessionId() {
		return sprintf('%04x%04x-%04x-%03x4-%04x-%04x%04x%04x',
		mt_rand(0, 65535), mt_rand(0, 65535), // 32 bits for "time_low"
		mt_rand(0, 65535), // 16 bits for "time_mid"
		mt_rand(0, 4095),  // 12 bits before the 0100 of (version) 4 for "time_hi_and_version"
		bindec(substr_replace(sprintf('%016b', mt_rand(0, 65535)), '01', 6, 2)),
		// 8 bits, the last two of which (positions 6 and 7) are 01, for "clk_seq_hi_res"
		// (hence, the 2nd hex digit after the 3rd hyphen can only be 1, 5, 9 or d)
		// 8 bits for "clk_seq_low"
		mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535) // 48 bits for "node"
		);
	}

	/**
	 * Update Session-Time so it will be valid for next SESSION_TIMEOUT seconds
	 *
	 */
	protected function updateSessionTime() {
		if ($this->sessionId != '') {
			$sql = 'UPDATE [table.session] SET [session.time]='.time().' WHERE [session.id]=\''.$this->sessionId.'\';';
			$db = pDatabaseConnection::getDatabaseInstance();
			$res = null;
			$db->run($sql, $res);
		}
	}

	/**
	 * Removes all timed out sessions
	 *
	 */
	protected function cleanSessions() {
		if (!SESSION_USE_PHP) {
			$time = time() - SESSION_TIMEOUT - 600;
			$sql = 'DELETE FROM [table.session] WHERE [session.time]<'.$time.';';
			$db = pDatabaseConnection::getDatabaseInstance();
			$res = null;
			$db->run($sql, $res);
		}
	}

	/**
	 * Interface-Function for updateing the Module
	 */
	public function updateModule() {
		// first get the version stored in the Database
		$db = pDatabaseConnection::getDatabaseInstance();
		$version = $db->getModuleVersion(get_class($this));
		$res = null;

		// Check if we need an Update
		if (self::MODULE_VERSION > $version) {
			// initial
			if ($version <= 0) {
				$sql = 'CREATE TABLE [table.session] ('.
				' [field.session.id] VARCHAR(100) NOT NULL default \'\','.
				' [field.session.ip] VARCHAR(15) NOT NULL default \'\','.
				' [field.session.browser] VARCHAR(250) NOT NULL default \'\','.
				' [field.session.data] TEXT NOT NULL default \'\','.
				' [field.session.time] BIGINT UNSIGNED NOT NULL default 0,'.
				' UNIQUE KEY [field.session.id] ([field.session.id])'.
				');';
				$db->run($sql, $res);
			}

			// Update the version in database for this module
			$db->updateModuleVersion(get_class($this), self::MODULE_VERSION);
		}
	}

}

?>