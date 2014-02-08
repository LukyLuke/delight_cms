<?php
/**
 * This Interface is for a MySQL-Result
 * 
 * @author Lukas Zurschmiede <l.zurschmiede@delightsoftware.com>
 * @copyright (c) 2007 by delight software gmbh, switzerland
 * 
 * @package delightcms
 * @version 2.0
 * @uses singelton
 */

interface iDatabaseResult {
	public function setQuery($query);
	public function getFirst();
	public function hasNext();
	public function numRows();
	public function numAffected();
	public function getLast();
	public function getPrevious();
	public function get($index);
	public function getType();
	public function __get($name);
}

?>