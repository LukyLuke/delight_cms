<?php
/** $id$
 * Class which holds all Informations about a country
 * 
 * @author Lukas Zurschmiede <l.zurschmiede@delightsoftware.com>
 * @copyright 2008 (c) by delight software gmbh
 * @package delightWebProduct
 */

$DBTables['country'] = $tablePrefix."_countries";
$DBFields['country'] = array(
	'id' => 'id',
	'short2' => 'short_name2',
	'short3' => 'short_name3',
	'name' => 'state_name',
	'currency' => 'currency',
	'symbol' => 'currency_symbol',
	'prefix' => 'telephone_prefix'
);

class pCountry {
	const MODULE_VERSION = 2009060400;
	
	private $countryId;
	private $countryData;
	
	public function __construct($countryId=0, $countryName="") {
		$this->updateModule();
		$this->countryId = $countryId;
		$this->countryData = new pProperty();
		
		$this->countryData->define('short2', 'string');
		$this->countryData->define('short3', 'string');
		$this->countryData->define('name', 'string');
		$this->countryData->define('currency', 'string');
		$this->countryData->define('currency_symbol', 'string');
		$this->countryData->define('telephone_prefix', 'string');
		
		if ($this->countryId == 0) {
			$this->loadCountryName($countryName);
		} else {
			$this->loadCountry();
		}
	}
	
	/**
	 * Set the ID for a Country and load all Data
	 * Defineing the ID while create the Object is much faster
	 *
	 * @param integer $coutryId ID from Country to get Data from
	 */
	public function setCountryId($coutryId) {
		$this->countryId = $coutryId;
		$this->loadCountry();
	}
	
	/**
	 * Load Country by its Name or short form (two or three char)
	 *
	 * @param string $country Country Name or 2-/3-Char short form
	 */
	public function loadCountryName($country) {
		$country = preg_replace('/[^a-zA-Z]+/smi', '', $country);
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = 'SELECT [country.id] FROM [table.country] WHERE [country.short2]=\''.strtoupper($country).'\' OR [country.short3]=\''.strtoupper($country).'\' OR [country.name]=\''.$country.'\';';
		$db->run($sql, $res);
		if ($res->getFirst()) {
			$this->countryId = (int)$res->{$db->getFieldName('country.id')};
			$this->loadCountry();
		}
	}
	
	/**
	 * Get a Country-Value
	 *
	 * @param string $param Parameter to get from the country
	 * @return string The Value for given $param
	 */
	public function get($param) {
		if ( ($param == 'id') || ($param == 'countryId') ) {
			return $this->countryId;
		}
		return $this->countryData->{$param};
	}
	
	/**
	 * Load Data from country defined by $this->countryId
	 *
	 */
	private function loadCountry() {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = 'SELECT * FROM [table.country] WHERE [country.id]='.(int)$this->countryId.';';
		$db->run($sql, $res);
		if ($res->getFirst()) {
			$this->countryData->short2   = $res->{$db->getFieldName('country.short2')};
			$this->countryData->short3   = $res->{$db->getFieldName('country.short3')};
			$this->countryData->name     = $res->{$db->getFieldName('country.name')};
			$this->countryData->currency = $res->{$db->getFieldName('country.currency')};
			$this->countryData->currency_symbol  = $res->{$db->getFieldName('country.symbol')};
			$this->countryData->telephone_prefix = $res->{$db->getFieldName('country.prefix')};
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
			if (self::MODULE_VERSION > 0) {
				$sql = 'CREATE TABLE [table.country] ('.
				' [field.country.id] INT(11) UNSIGNED NOT NULL auto_increment,'.
				' [field.country.short2] CHAR(2) NOT NULL default \'\','.
				' [field.country.short3] CHAR(3) NOT NULL default \'\','.
				' [field.country.name] VARCHAR(50) NOT NULL default \'\','.
				' [field.country.currency] VARCHAR(20) NOT NULL default \'\','.
				' [field.country.symbol] CHAR(3) NOT NULL default \'\','.
				' [field.country.prefix] VARCHAR(10) NOT NULL default \'\','.
				' UNIQUE KEY [field.country.id] ([field.country.id])'.
				');';
				$db->run($sql, $res);
				
				$sql = "";
				require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'pCountry.sql.php');
				$db->run($sql, $res);
			}

			// Update the version in database for this module
			$db->updateModuleVersion(get_class($this), self::MODULE_VERSION);
		}
	}
	
}

?>