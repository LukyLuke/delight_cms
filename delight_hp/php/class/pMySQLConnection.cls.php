<?php
/**
 * This Class is for a MySQL-Connection
 * 
 * @author Lukas Zurschmiede <l.zurschmiede@delightsoftware.com>
 * @copyright (c) 2007 by delight software gmbh, switzerland
 * 
 * @package delightcms
 * @version 2.0
 * @uses singelton
 */

require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'iDatabaseResult.iface.php');
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'pMySQLResult.cls.php');

class pMySQLConnection implements iDatabaseConnection {
	private $connection;
	private $connectionError;
	private $errorString;
	private static $instance = null;
	
	/**
	 * Initialization
	 */
	private function __construct() {
		$this->connection = null;
		$this->connectionError = false;
		$this->errorString = '';
		$this->connect();
	}
	
	/**
	 * Get a Database-Instance
	 *
	 * @return pDatabaseConnection
	 * @access public
	 * @static yes
	 */
	public static function getDatabaseInstance() {
		if (pMySQLConnection::$instance == null) {
			pMySQLConnection::$instance = new pMySQLConnection();
		}
		return pMySQLConnection::$instance;
	}
	
	/**
	 * Connect to the Database if not already connected
	 * 
	 * @throws DWPException
	 */
	private function connect() {
		if ( ($this->connection == null) && !$this->connectionError) {
			if (($this->connection = @mysql_connect(DB_HOST, DB_USER, DB_PASSWORD, false)) === false) {
				$this->errorString = 'Unable to connect to the Database-Server: '.mysql_error();
				$this->connection = null;
				$this->connectionError = true;
				throw new DWPException($this->errorString, mysql_errno());
			} else {
				if (@mysql_select_db(DB_DATABASE) === false) {
					$this->errorString = 'Unable to select the given Database: '.mysql_error();
					$this->connection = null;
					$this->connectionError = true;
					throw new DWPException($this->errorString, mysql_errno());
				}
			}
		}
	}
	
	/**
	 * Disconnect from the Database
	 */
	private function disconnect() {
		if ($this->connection == null) {
			@mysql_close($this->connection);
		}
	}
	
	/**
	 * Return ConnectionState
	 *
	 * @return boolean true if connected, false on failure
	 */
	public function connectSuccessfully() {
		return !$this->connectionError;
	}
	
	/**
	 * Get the current Error from the Connection
	 *
	 * @return String the connection error
	 */
	public function getConnectionError() {
		return $this->errorString;
	}
	
	/**
	 * Excape a Table- or Fieldname
	 *
	 * @param string $table Table or Field to escape
	 * @return string Fully escaped Database-Tablename
	 */
	public function escape($value) {
		return "`".$value."`";
	}
	
	/**
	 * Return the Separator which we need between TABLE and FIELD in a SQL-Query
	 *
	 * @return string SQL.Table-Field-Separator
	 */
	public function getSqlSeparator() {
		return '.';
	}
	
	/**
	 * Escape a Data-Value for a SQL-Query
	 *
	 * @param string $value Value to escape for a SQL-Query
	 * @return string Escaped value
	 */
	public function escapeValue($value) {
		return mysql_real_escape_string($value);
	}
	
	/**
	 * Get a Database-Result-Object
	 *
	 * @param string $sql SQL to execute and get a Result from
	 * @param iDatabaseResult &$result Result which holds the DatabaseResult
	 */
	public function getResult($sql, &$result) {
		$result = new pMySQLResult();
		$result->setQuery($sql);
	}
	
}

?>