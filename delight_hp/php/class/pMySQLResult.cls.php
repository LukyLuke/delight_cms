<?php
/**
 * This Class is for a MySQL-Result
 * 
 * @author Lukas Zurschmiede <l.zurschmiede@delightsoftware.com>
 * @copyright (c) 2007 by delight software gmbh, switzerland
 * 
 * @package delightcms
 * @version 2.0
 * @uses singelton
 */

require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'iDatabaseResult.iface.php');

class pMySQLResult implements iDatabaseResult {
	private $result;
	private $sql;
	private $queryType;
	private $currentIndex;
	private $numAffected;
	private $lastInsertId;
	private $currentRow;
	
	public function __construct() {
		$this->result = null;
		$this->sql = '';
		$this->queryType = '';
		$this->currentIndex = -1;
		$this->numAffected = -1;
		$this->lastInsertId = -1;
		$this->currentRow = array();
	}
	
	/**
	 * Set the SQL-Query
	 *
	 * @param string $query SQL-Query
	 */
	public function setQuery($query) {
		$this->currentIndex = -1;
		$this->maxRows = -1;
		$this->lastInsertId = -1;
		$this->sql = trim($query);
		$this->result = @mysql_query($this->sql);
		
		if ( (mysql_errno() > 0) && (stripos($this->sql, '_exceptions') == 0) ) {
			$ex = new DWPException('MySQL-Error('.mysql_errno().'): '.mysql_error(), 99);
			$ex->store();
		}
		
		$this->parseQueryType();
		
		if ($this->queryType == 'insert') {
			$this->lastInsertId = mysql_insert_id();
		}
		
		if (($this->queryType == 'select') || ($this->queryType == 'show')) {
			$this->numAffected = -1;
			$this->currentRow = array();
		} else {
			if ($this->queryType == 'replace') {
				$this->numAffected = (int)(mysql_affected_rows() / 2);
			} else if (($this->queryType == 'alter') || ($this->queryType == 'create')) {
				$this->numAffected = 0;
			} else {
				$this->numAffected = mysql_affected_rows();
			}
		}
	}
	
	/**
	 * Return the QueryType
	 * Type is one of: 'insert','select','update','delete','alter','replace','unknown'
	 *
	 * @return string Type of Query
	 */
	public function getType() {
		return $this->queryType;
	}
	
	/**
	 * Change to the first row if this is a SELECT-Result
	 *
	 * @return array Array with all fields from current row or FALSE on failure 
	 */
	public function getFirst() {
		if ( (($this->queryType == 'select') || ($this->queryType == 'show')) && ($this->numRows() > 0)) {
			$this->currentIndex = 0;
			if (mysql_data_seek($this->result, $this->currentIndex)) {
				$this->currentRow = mysql_fetch_assoc($this->result);
				return true;
			} else {
				return false;
			}
		} else {
			$this->currentRow = array();
			return false;
		}
	}
	
	/**
	 * Get the next Dataset
	 *
	 * @return array Array with all fields from current row or FALSE on failure 
	 */
	public function getNext() {
		if ($this->currentIndex == -1) {
			$this->currentIndex = 0;
		}
		if (($this->queryType == 'select') && ($this->currentIndex < $this->numRows())) {
			if (mysql_data_seek($this->result, $this->currentIndex)) {
				$this->currentRow = mysql_fetch_assoc($this->result);
				$this->currentIndex += 1;
				return true;
			} else {
				return false;
			}
		} else {
			$this->currentRow = array();
			return false;
		}
	}
	
	/**
	 * Check if the current Result has a next row to proceed
	 *
	 * @return boolean Returns TRUE if there is a next Row, FALSE if not
	 */
	public function hasNext() {
		if ($this->queryType == 'select') {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Return num rows in a SELECT-Statement
	 *
	 * @return integer Num rows or -1 on failure
	 */
	public function numRows() {
		if ( (($this->queryType == 'select') || ($this->queryType == 'show')) && is_resource($this->result)) {
			return mysql_num_rows($this->result);
		} else {
			return -1;
		}
	}
	
	/**
	 * Return num of affected rows from a NON-Select Statement
	 *
	 * @return integer Number of affected rows or -1 on failure
	 */
	public function numAffected() {
		return $this->numAffected;
	}
	
	/**
	 * Return the last InsertID
	 *
	 * @return integer The last ID which was inserted
	 */
	public function getInsertId() {
		return $this->lastInsertId;
	}
	
	/**
	 * Go to the last Row on current Dataset
	 *
	 * @return array Array with all fields from current row or FALSE on failure 
	 */
	public function getLast() {
		if (($this->queryType == 'select') && ($this->numRows() > 0)) {
			$this->currentIndex = $this->numRows() - 1;
			mysql_data_seek($this->result, $this->currentIndex);
			$this->currentRow = mysql_fetch_assoc($this->result);
			return true;
		} else {
			$this->currentRow = array();
			return false;
		}
	}
	
	/**
	 * Get the previous Dataset
	 *
	 * @return array Array with all fields from current row or FALSE on failure 
	 */
	public function getPrevious() {
		if (($this->queryType == 'select') && ($this->currentIndex < $this->numRows()) && ($this->currentIndex > 0)) {
			mysql_data_seek($this->result, $this->currentIndex);
			$this->currentRow = mysql_fetch_assoc($this->result);
			$this->currentIndex -= 1;
			return true;
		} else {
			$this->currentRow = array();
			return false;
		}
	}
	
	/**
	 * Get the specific rownumber from current ResultSet
	 *
	 * @param integer $index Row number to get (index begins by 0 and ends by numRows()-1)
	 * @return array Array with all fields from current row or FALSE on failure 
	 */
	public function get($index) {
		if (($this->queryType == 'select') && ($this->numRows() > $index) && ($index >= 0)) {
			$this->currentIndex = $index;
			mysql_data_seek($this->result, $this->currentIndex);
			$this->currentRow = mysql_fetch_assoc($this->result);
			return true;
		} else {
			$this->currentRow = array();
			return false;
		}
	}
	
	/**
	 * PHP-Function to get an undefined Property
	 * we use it to access to the DB-Fields
	 *
	 * @param string $name Property-Name to get the value from
	 * @return mixed Value from the current Database-Result-Row
	 */
	public function __get($name) {
		if (array_key_exists($name, $this->currentRow)) {
			return $this->currentRow[$name];
		} else {
			return null;
		}
	}
	
	/**
	 * Extract the type from query and set it to $this->queryType
	 *
	 */
	private function parseQueryType() {
		$check = strtolower(substr($this->sql, 0, 6));
		switch ($check) {
			case 'select':
			case 'delete':
			case 'insert':
			case 'update':
			case 'create':
				$this->queryType = $check;
			break;
			default:
				if (substr($check, 0, 5) == 'alter') {
					$this->queryType = 'alter';
				} else if ($check == 'replac') {
					$this->queryType = 'replace';
				} else if (substr($check, 0, 4) == 'show') {
					$this->queryType = 'show';
				} else {
					$this->queryType = 'unknown';
				}
				break;
		}
	}
	
	/**
	 * Get the Error-Message from MySQL
	 *
	 * @return String The Error-String or null if there wasn't an Error
	 */
	public function getError() {
		if (mysql_errno() > 0) {
			return mysql_error();
		}
		return null;
	}
}

?>