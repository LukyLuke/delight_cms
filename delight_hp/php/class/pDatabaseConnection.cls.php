<?php
/**
 * This Class is for Database connections
 * 
 * @author Lukas Zurschmiede <l.zurschmiede@delightsoftware.com>
 * @copyright (c) 2007 by delight software gmbh, switzerland
 * 
 * @package delightcms
 * @version 2.0
 * @uses singelton
 */

// we use MySQL as default Database
if (!defined('DB_CONNECTION')) {
	define('DB_CONNECTION', 'mysql');
}

class pDatabaseConnection {
	const MODULE_VERSION = 2008040300;
	
	private $connection;
	private $connectionError;
	private $dbTables;
	private $dbFields;
	private $versions;
	private static $instance = null;
	
	/**
	 * Initialization
	 */
	private function __construct() {
		global $DBTables, $DBFields;
		$this->dbTables = &$DBTables;
		$this->dbFields = &$DBFields;
		$this->connection = pMySQLConnection::getDatabaseInstance();
		$this->connectionError = !$this->connection->connectSuccessfully();
	}
	
	/**
	 * Get a Database-Instance
	 *
	 * @return pDatabaseConnection
	 * @access public
	 * @static yes
	 */
	public static function getDatabaseInstance() {
		if (pDatabaseConnection::$instance == null) {
			pDatabaseConnection::$instance = new pDatabaseConnection();
			pDatabaseConnection::$instance->versions = pModuleVersion::getInstance();
		}
		return pDatabaseConnection::$instance;
	}
	
	/**
	 * Check if the Connection was successfully or not
	 *
	 * @return boolean
	 */
	public function connectSuccessfully() {
		return !$this->connectionError;
	}
	
	/**
	 * Get the SQL-Tablename from given Table $table
	 *
	 * @param string $table Short Tablename (mostly three chars)
	 * @param boolean $doEscape set to false if you only need the Tablename without any quotes
	 * @return string (Fully escaped) Database-Tablename
	 */
	public function getSqlTableName($table, $doEscape=true) {
		if (array_key_exists($table, $this->dbTables)) {
			return $doEscape ? $this->connection->escape($this->dbTables[$table]) : $this->dbTables[$table];
		} else {
			return "";
		}
	}
	
	/**
	 * Get a fully escaped Fieldname for an SQL-Query
	 *
	 * @param string $field Fieldname to get for SQL-Statement (FieldName or TableName.FieldName)
	 * @param string $table (optional) Tablename - only if the table is not given in $field
	 * @return string fully escaped FieldName
	 */
	public function getSqlFieldName($field, $table=null) {
		if ((substr_count($field, '.') > 0) || empty($table)) {
			$field = explode('.', $field);
		} else {
			$field = array($table, $field);
		}
		if ($field[0] == 'table') {
			return $this->connection->escape($this->dbTables[$field[1]]);
		} else if (($field[0] == 'field') && (count($field) >= 2) && (array_key_exists($field[1], $this->dbFields) && array_key_exists($field[2], $this->dbFields[$field[1]]) )) {
			return $this->connection->escape($this->dbFields[$field[1]][$field[2]]);
		} else if (array_key_exists($field[0], $this->dbFields) && array_key_exists($field[1], $this->dbFields[$field[0]]) ) {
			return $this->connection->escape($this->dbTables[$field[0]]).$this->connection->getSqlSeparator().$this->connection->escape($this->dbFields[$field[0]][$field[1]]);
		} else {
			return "";
		}
	}
	
	/**
	 * Get a Fieldname only - for access to a Result for example
	 *
	 * @param string $field Fieldname to get for SQL-Statement (FieldName or TableName.FieldName)
	 * @param string $table (optional) Tablename - only if the table is not given in $field
	 * @return string FieldName
	 */
	public function getFieldName($field, $table=null) {
		if ($field{0} == '[') {
			$field = substr($field, 1, strlen($field)-2);
		}
		if ((substr_count($field, '.') > 0) || empty($table)) {
			$field = explode('.', $field);
		} else {
			$field = array($table, $field);
		}
		if (array_key_exists($field[0], $this->dbFields) && array_key_exists($field[1], $this->dbFields[$field[0]]) ) {
			return $this->dbFields[$field[0]][$field[1]];
		} else {
			return "";
		}
	}
	
	/**
	 * Parse and run an SQL-Query
	 *
	 * @param string $sql the Query to run
	 * @param iDatabaseResult &$result Pointer to the DatabaseResult
	 * @param  boolean $debug If this is true, the Query will be shown to stdout
	 */
	public function run($sql, &$result, $debug=false) {
		$match = array();
		if ( preg_match_all('/(\[)([a-z0-9]+\.[a-z0-9]+(\.[a-z0-9]+)?)(\])/smi', $sql, $match, PREG_SET_ORDER) ) {
			foreach ($match as $v) {
				$fieldName = $this->getSqlFieldName($v[2]);
				if (!empty($fieldName)) {
					$sql = str_replace($v[0], $fieldName, $sql);
				}
			}
		}
		if ($debug) {
			echo wordwrap(str_replace('<', '&lt;', str_replace('>', '&gt;', $sql)), 150)."\n<br/>";
		}
		$this->connection->getResult($sql, $result);
	}
	
	/**
	 * Get the last known version from a module
	 *
	 * @param String $module The Module-Name to get the verison from
	 * @return integer The Version number from the Database
	 */
	public function getModuleVersion($module) {
		if ($this->versions instanceof pModuleVersion) {
			return (int)$this->versions->getModuleVersion($module);
		} else {
			return 0;
		}
	}
	
	/**
	 * Update a Module with a newer Version
	 *
	 * @param String $module The Module-Name
	 * @param integer $version The new Version
	 */
	public function updateModuleVersion($module, $version) {
		$this->versions->updateModuleVersion($module, $version);
	}
	
}

?>