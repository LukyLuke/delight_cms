<?php
/**
 * This Interface is for a Database-Connection
 * 
 * @author Lukas Zurschmiede <l.zurschmiede@delightsoftware.com>
 * @copyright (c) 2007 by delight software gmbh, switzerland
 * 
 * @package delightcms
 * @version 2.0
 * @uses singelton
 */

interface iDatabaseConnection {
	public static function getDatabaseInstance();
	public function escape($value);
	public function escapeValue($value);
	public function getSqlSeparator();
	public function connectSuccessfully();
	public function getResult($sql, &$result);
}

?>