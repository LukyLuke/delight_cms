<?php
abstract class plg1500_plugin {

	private $pluginName;
	private $pluginSymbol;
	private $statisticData;
	
	protected $graphUrl;

	/**
	 * Initialization
	 *
	 * @param string $name PluginName
	 * @param string $symbol Plugin Symbol/Image
	 * @access public
	 */
	public function __construct($name='', $symbol='') {
		$this->pluginName = $name;
		$this->pluginSymbol = $symbol;
		$this->statisticData = array();
		$this->graphUrl = MAIN_DIRECTORY.'/graph/showCircle3D.graph.php?stat=';
	}

	/**
	 * Get the Statistic-Data
	 *
	 * @param string $sort Sort by this Statistic-Data
	 * @param string $order Order data this way (ASC|DESC)
	 * @return array/stdClass Statistic-Data to use with json_encode
	 * @access public
	 * @abstract 
	 */
	public abstract function getStatisticData($sort, $order);

	/**
	 * Get the URL to an Image which shows the statistic
	 *
	 * @return stdClass List with different Image-URL's
	 * @access public
	 */
	public function getImageGraphLinkList() {
		$back = new stdClass();
		return $back;
	}
	
	/**
	 * Set all Statistics-Data
	 *
	 * @param array $data
	 * @access protected
	 */
	protected function setStatisticsData(array $data) {
		$this->statisticData = $data;
	}
	
	/**
	 * Get all Statistics-Data
	 *
	 * @access protected
	 * @return array
	 */
	protected function getStatisticsData() {
		return $this->statisticData;
	}
	
	/**
	 * Return the Plugin-Name
	 *
	 * @return string
	 * @access public
	 */
	public function getName() {
		return $this->pluginName;
	}
	
	/**
	 * Get the CountryName based on a TLD
	 *
	 * @param string $val The DomainName to get the Country from
	 * @return string
	 * @access protected
	 */
	protected function getCountryByDomain($domain) {
		global $ToplevelDomainList;
		$domain = strtolower(substr($domain, strrpos($domain, '.')+1));
		if (array_key_exists($domain, $ToplevelDomainList)) {
			return $ToplevelDomainList[$domain];
		}
		return 'Unknown';
	}

	/**
	 * Check for a valid BrowserName and return it
	 *
	 * @param string $val Browser-String to check agains a BrowserName
	 * @return string
	 * @access protected
	 */
	protected function getBrowserName($browserString) {
		global $KnownBrowsers;
		foreach ($KnownBrowsers as $v) {
			$match = array();
			if (preg_match('/'.$v[1].'/i', $browserString, $match)) {
				return $v[0];
			}
		}
		return 'Unknown';
	}

	/**
	 * Get the Operating-System based on a Browser-String
	 *
	 * @param string $val Browser-String
	 * @return string
	 * @access protected
	 */
	protected function getOperatingSystem($browserString) {
		$oslist = array("Win 9x 4.9", "win98", "winnt", "windows 2000", "windows me", "windows xp", "winweb", "windows 95", "windows 98", "windows NT 5.1", "windows NT 5", "windows nt 5", "windows nt", "win16", "windows 3.1", "win32", "mac", "hp-ux", "sunos", "x11", "linux");
		foreach ($oslist as $v) {
			if (preg_match('/'.preg_quote($v).'/i', $browserString)) {
				return $v;
			}
		}
		return 'unknown';
	}

	/**
	 * Get a FileSize as a HumanReadable String
	 *
	 * @param int $size FileSize to convert to HumanReadable
	 * @return String
	 * @access protected
	 */
	protected function getHumanReadableSize($size) {
		$size = (float)$size;
		if ($size < pow(1024, 1)) {
			return round($size, 2) . " byte";
		} else if ($size < pow(1024, 2)) {
			return round(($size / pow(1024, 1)), 2) . " Kb";
		} else if ($size < pow(1024, 3)) {
			return round(($size / pow(1024, 2)), 2) . " Mb";
		} else if ($size < pow(1024, 4)) {
			return round(($size / pow(1024, 3)), 2) . " Gb";
		} else {
			return round(($size / pow(1024, 4)), 2) . " Tb";
		}
	}
	
}
?>