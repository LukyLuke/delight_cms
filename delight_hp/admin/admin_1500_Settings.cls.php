<?php

/**
 * Statistics
 *
 */

class admin_1500_Settings extends admin_MAIN_Settings {

	private $viewType;
	private $pluginList;

	/**
	 * Initialization
	 *
	 * @access public
	 */
	public function __construct() {
		parent::__construct();
		$this->VERSION = 2009081900;
		$this->loadPlugins(dirname(realpath(__FILE__)).DIRECTORY_SEPARATOR.'plugin_1500'.DIRECTORY_SEPARATOR);
	}

	/**
	 * Calls from Administration-Interface
	 *
	 * @access public
	 */
	public function createActionBasedContent() {
		$userCheck = pCheckUserData::getInstance();
		if ($userCheck->checkAccess($this->_adminAccess)) {
			$sort = pURIParameters::get('sort', 'number', pURIParameters::$STRING);
			$order = pURIParameters::get('order', 'ASC', pURIParameters::$STRING);
			switch (1000 + $this->_mainAction) {
				case 1000:
					echo '{"action":1500,"method":0,"data":';
					echo $this->getJSONStatistics('download', $sort, $order);
					echo ',"graph":'.$this->getGraphURLList('download');
					echo '}';
					exit();
					break;
				case 1001:
					echo '{"action":1500,"method":1,"data":';
					echo $this->getJSONStatistics('referer', $sort, $order);
					echo ',"graph":'.$this->getGraphURLList('referer');
					echo '}';
					exit();
					break;
				case 1002:
					echo '{"action":1500,"method":2,"data":';
					echo $this->getJSONStatistics('login', $sort, $order);
					echo ',"graph":'.$this->getGraphURLList('login');
					echo '}';
					exit();
					break;
			}
		} else {
			$this->showNoAccess();
		}
	}

	/**
	 * Get Download-Statistics as JSON-String
	 *
	 * @param string $plugin The Plugin to get Statistics from
	 * @param string $sort Sort by this Statistic-Data
	 * @param string $order Order data this way (ASC|DESC)
	 * @access private
	 * @return string JSON-Array
	 */
	private function getJSONStatistics($plugin, $sort, $order) {
		$plg = $this->getPlugin($plugin);
		$back = array();
		if (!is_null($plg)) {
			$back = $plg->getStatisticData($sort, $order);
		}
		return json_encode($back);
	}
	
	/**
	 * Get all Image-URL's as a JSON-Encoded array
	 *
	 * @param string $plugin Plugin to get Statistics from
	 * @return string JSON-Array
	 */
	private function getGraphURLList($plugin) {
		$plg = $this->getPlugin($plugin);
		$back = array();
		if (!is_null($plg)) {
			$back = $plg->getImageGraphLinkList();
		}
		return json_encode($back);
	}
	
	/**
	 * Get the Plugin
	 *
	 * @param string $name Plugin Name
	 * @return plg1500_plugin The Plugin or "null"
	 * @access private
	 */
	private function getPlugin($name) {
		if (!empty($name)) {
			foreach ($this->pluginList as &$plg) {
				if ($plg->getName() == $name) {
					return $plg;
				}
			}
		}
		return null;
	}
	
	/**
	 * Load all Statistic-Plugins
	 *
	 * @param string $pluginDir Directory to the Plugin-Directory
	 * @access private
	 */
	private function loadPlugins($pluginDir) {
		if (!is_array($this->pluginList)) {
			$this->pluginList = array();
		}
		if (substr($pluginDir, -1, 1) != DIRECTORY_SEPARATOR) {
			$pluginDir .= DIRECTORY_SEPARATOR;
		}
		if (!is_dir($pluginDir)) {
			return;
		}
		
		if (!class_exists('plg1500_plugin')) {
			require_once($pluginDir.'plg1500_plugin.cls.php');
		}
		
		$fileList = scandir($pluginDir);
		if ($fileList === false) {
			return;
		}
		
		foreach ($fileList as $file) {
			if (!is_file($pluginDir.$file) || ($file == 'plg1500_plugin.cls.php') || (substr($file, 0, 7) != 'plg1500') ) {
				continue;
			}
			$class = substr($file, 0, strpos($file, '.'));
			if (!array_key_exists($class, $this->pluginList)) {
				require_once($pluginDir.$file);
				$this->pluginList[$class] = new $class();
			}
		}
	}
	
	/**
	 * Create Standard/Updates in Database and perhaps other things if needed
	 *
	 * @param integer $version Current version from Database
	 */
	function _checkDatabaseUpdates($version=0) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;

		// Initial
		if ($version < 2009081900) {
			$sql = 'CREATE TABLE IF NOT EXISTS [table.ref] ('.
			' [field.ref.id] INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,'.
			' [field.ref.local] VARCHAR(250) NOT NULL DEFAULT \'\','.
			' [field.ref.protocol] VARCHAR(10) NOT NULL DEFAULT \'\','.
			' [field.ref.domain] VARCHAR(50) NOT NULL DEFAULT \'\','.
			' [field.ref.url] VARCHAR(250) NOT NULL DEFAULT \'\','.
			' [field.ref.param] VARCHAR(250) NOT NULL DEFAULT \'\','.
			' [field.ref.date] DATETIME NOT NULL DEFAULT \'0000-00-00 00:00:00\','.
			' [field.ref.client] VARCHAR(80) NOT NULL DEFAULT \'\','.
			' [field.ref.browser] VARCHAR(150) NOT NULL DEFAULT \'\','.
			' PRIMARY KEY ([field.ref.id]),'.
			' UNIQUE KEY [field.ref.id] ([field.ref.id]),'.
			' KEY [ref.domain] ([ref.domain])'.
			') TYPE=MyISAM;';
			$db->run($sql, $res);
			$res = null;
		}

	}
}
?>