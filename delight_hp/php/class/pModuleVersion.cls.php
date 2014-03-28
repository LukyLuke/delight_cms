<?php
/** $id$
 * Class for handling Module-Versions
 *
 * @author Lukas Zurschmiede <l.zurschmiede@delightsoftware.com>
 * @copyright (c) 2008 by delight software gmbh
 *
 * @package delightWebProduct
 * @version 1.0
 */

$DBTables['version'] = $tablePrefix.'_class_versions';
$DBFields['version'] = array(
	'module' => 'class',
	'version' => 'version',
	'last' => 'last_update'
);

class pModuleVersion {
	const MODULE_VERSION = 2009072300;
	
	private $modules = array();
	private static $instance = null;
	
	private function __construct() {
		$this->updateModule();
		$this->load();
	}
	
	/**
	 * Get a Session-Instance
	 *
	 * @return pModuleVersion
	 * @access public
	 * @static yes
	 */
	public static function getInstance() {
		if (pModuleVersion::$instance == null) {
			pModuleVersion::$instance = new pModuleVersion();
		}
		return pModuleVersion::$instance;
	}

	/**
	 * Load all Versions
	 *
	 */
	private function load() {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = 'SELECT * FROM [table.version] ORDER BY [version.module] ASC;';
		$db->run($sql, $res);
		if ($res->getFirst()) {
			while ($res->getNext()) {
				$this->modules[$res->{$db->getFieldName('version.module')}] = (int)$res->{$db->getFieldName('version.version')};
			}
		}
	}
	
	/**
	 * Get the last known version from a module
	 *
	 * @param String $module The Module-Name to get the verison from
	 * @return integer The Version number from the Database
	 */
	public function getModuleVersion($module) {
		$version = 0;
		if (array_key_exists($module, $this->modules)) {
			$version = $this->modules[$module];
		} else {
			$db = pDatabaseConnection::getDatabaseInstance();
			$res = null;
			$sql = 'SELECT [version.version] FROM [table.version] WHERE [version.module]=\''.$module.'\';';
			$db->run($sql, $res);
			if ($res->getFirst()) {
				$version = (int)$res->{$db->getFieldName('version.version')};
			}
		}
		return $version;
	}
	
	/**
	 * Update a Module with a newer Version
	 *
	 * @param String $module The Module-Name
	 * @param integer $version The new Version
	 */
	public function updateModuleVersion($module, $version) {
		if (array_key_exists($module, $this->modules)) {
			$this->modules[$module] = (int)$version;
		}
		
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = 'SELECT [version.version] FROM [table.version] WHERE [version.module]=\''.$module.'\';';
		$db->run($sql, $res);
		if ($res->getFirst()) {
			$sql = 'UPDATE [table.version] SET [field.version.version]='.(int)$version.',[field.version.last]='.time().' WHERE [field.version.module]=\''.$module.'\';';
		} else {
			$sql = 'INSERT INTO [table.version] ([field.version.module],[field.version.version],[field.version.last]) VALUES (\''.$module.'\','.(int)$version.','.time().');';
		}
		$res = null;
		$db->run($sql, $res);
	}

	/**
	 * Check the newest ModuleVersion and update something if needed
	 */
	private function updateModule() {
		// first get the version stored in the Database
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		
		// Check if the Table exists or not. We can only get versions if it exists
		$sql = 'SHOW TABLES LIKE "'.$db->getSqlTableName('version', false).'";';
		$db->run($sql, $res);
		if (!$res->getFirst()) {
			$sql = 'CREATE TABLE IF NOT EXISTS [table.version] ('.
			' [field.version.module] VARCHAR(100) NOT NULL default \'\','.
			' [field.version.version] INT UNSIGNED NOT NULL default 0,'.
			' [field.version.last] BIGINT UNSIGNED NOT NULL default 0,'.
			' UNIQUE KEY [field.version.module] ([field.version.module])'.
			');';
			$db->run($sql, $res);
			$this->updateModuleVersion(get_class($this), self::MODULE_VERSION);
		}

		// Check if we need an Update
		$version = $this->getModuleVersion(get_class($this));
		if (self::MODULE_VERSION > $version) {
			
			// Update the version
			$this->updateModuleVersion(get_class($this), self::MODULE_VERSION);
		}
	}

}

?>