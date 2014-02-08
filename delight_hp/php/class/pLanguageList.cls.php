<?php

class pLanguageList implements Iterator {

	private $list = array();
	private $position = 0;

	/**
	 * Initialitazion
	 *
	 * @param boolean $selectedFirst List all selected languages first
	 * @access public
	 */
	public function __construct($selectedFirst=false) {
		// We need the DB-Definitions defined in pLanguage
		$lan = new pLanguage();

		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = 'SELECT [lan.id] FROM [table.lan] ORDER BY '.($selectedFirst ? '[lan.active] DESC, ':'').'[lan.text] ASC;';
		$db->run($sql, $res);

		if ($res->getFirst()) {
			while ($res->getNext()) {
				$this->list[] = (int)$res->{$db->getFieldName('lan.id')};
			}
		}
		$res = null;
	}

	/**
	 * Get number of Languages
	 * @return int
	 */
	public function length() {
		return count($this->list);
	}

	public function numActive() {
		$num = 0;
		foreach ($this as $l) {
			if ($l->active) {
				$num++;
			}
		}
		return $num;
	}

	/**
	 * Get the current Language
	 *
	 * @return pLanguage
	 * @interface Iterator
	 */
	public function current() {
		return new pLanguage($this->key());
	}

	/**
	 * Get the current key
	 *
	 * @return string Short Language-Name
	 * @interface Iterator
	 */
	public function key() {
		return $this->list[$this->position];
	}

	/**
	 * Change to the next Entry
	 *
	 * @interface Iterator
	 */
	public function next() {
		$this->position += 1;
	}

	/**
	 * Change to the first Entry
	 *
	 * @interface Iterator
	 */
	public function rewind() {
		$this->position = 0;
	}

	/**
	 * Check if the current Entry is a valid one
	 *
	 * @return boolean
	 * @interface Iterator
	 */
	public function valid() {
		return ($this->position < count($this->list));
	}
}

?>