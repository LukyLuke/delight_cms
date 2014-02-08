<?php

class plg1500_referer extends plg1500_plugin {

	/**
	 * Initialization
	 *
	 */
	public function __construct() {
		parent::__construct('referer', 'statistics_referer.gif');
	}

	/**
	 * Get the URL to an Image which shows the statistic
	 *
	 * @return stdClass List with different Image-URL's
	 * @access public
	 */
	public function getImageGraphLinkList() {
		$back = '';
		$list = $this->getStatisticData('number', 'ASC');
		
		// Get Link for current (URI-Parameter 'group') Request
		usort($list, array('plg1500_referer', '_sortByNumber'));
		$cnt = 0;
		$others = 0;
		foreach ($list as $data) {
			if ($cnt > 8) {
				$others += $data->num;
			} else {
				if ($cnt > 0) {
					$back .= ';';
				}
				$back .= $data->num.':'.$data->name;
			}
			$cnt++;
		}
		$back .= ';'.$others.':Others';
		
		$back = $this->graphUrl.base64_encode($back);
		
		return $back;
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
	public function getStatisticData($sort, $order) {
		$back = array();
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$groupBy = pURIParameters::get('group', 'local', pURIParameters::$STRING);
		$timeFrom = pURIParameters::get('from', date('Y-m-d 00:00:01', mktime(0, 0, 0, date('m'), 1, date('Y')) ), pURIParameters::$STRING);
		$timeTo = pURIParameters::get('to', date('Y-m-d 23:59:59', mktime(0, 0, 0, date('m'), date('t'), date('Y')) ), pURIParameters::$STRING);
		
		$timeFrom = strtotime($timeFrom);
		$timeTo = strtotime($timeTo);
		
		$numTotal = 0;
		
		$sql = 'SELECT [ref.domain],[ref.local],[ref.url],[ref.param] FROM [table.ref] WHERE [ref.date]>=\''.date('Y-m-d H:i:s', $timeFrom).'\' AND [ref.date]<=\''.date('Y-m-d H:i:s', $timeTo).'\' ORDER BY [ref.domain];';
		$db->run($sql, $res);
		if ($res->getFirst()) {
			while ($res->getNext()) {
				$location = $res->{$db->getFieldName('ref.local')};
				$refdom = $res->{$db->getFieldName('ref.domain')};
				$refurl = $res->{$db->getFieldName('ref.url')};
				$refparam = $res->{$db->getFieldName('ref.param')};
				
				switch ($groupBy) {
					case 'referer':
						$key = $refdom;
						break;
					case 'local':
					default:
						$key = $location;
						break;
				}
				$key = sha1($key);
				
				if (!array_key_exists($key, $back)) {
					$back[$key] = new stdClass();
					switch ($groupBy) {
						case 'referer':
							$back[$key]->name = $refdom;
							break;
						case 'local':
						default:
							$back[$key]->name = $location;
							break;
					}
					$back[$key]->num = 0;
					$back[$key]->list = array();
				}
				$o = new stdClass();
				$o->location = $location;
				$o->ref_domain = $refdom;
				$o->ref_url = $refurl;
				$o->ref_param = $refparam;
				
				$back[$key]->list[] = $o;
				$back[$key]->num += 1;
				$numTotal += 1;
			}
			$res = null;

			// Sort the Data based on the requested order
			$order = (substr(strtolower($order), 0, 1) == 'a');
			switch (strtolower($sort)) {
				case 'number':
					usort($back, array('plg1500_referer', $order ? '_sortByNumber' : '_rsortByNumber'));
					break;
				case 'name':
				default:
					usort($back, array('plg1500_referer', $order ? '_sortByName' : '_rsortByName'));
					break;
			}
			
			// Calc Percent-Values
			foreach ($back as &$data) {
				$data->percent_num = round($data->num / $numTotal, 5);
			}
			unset($numTotal);
		}
		$this->setStatisticsData($back);
		
		return $back;
	}
	
	/**
	 * Sort-Helper-Function: Sort by num Downloads
	 *
	 * @param stdClass $a
	 * @param stdClass $b
	 * @return boolean
	 */
	public static function _sortByName($a, $b) {
		return strcasecmp($a->name, $b->name);
	}
	public static function _rsortByName($a, $b) {
		return strcasecmp($b->name, $a->name);
	}
	
	/**
	 * Sort-Helper-Function: Sort by Total Download-Size
	 *
	 * @param stdClass $a
	 * @param stdClass $b
	 * @return boolean
	 */
	public static function _sortByNumber($a, $b) {
		return $a->num < $b->num;
	}
	public static function _rsortByNumber($a, $b) {
		return $a->num > $b->num;
	}
	
	
	
	
		function getLogDataset()
		{
			$sql  = "SELECT ".$this->DB->Field('ref','local').",".$this->DB->Field('ref','domain').",".$this->DB->Field('ref','date').",".$this->DB->Field('ref','client')."";
			$sql .= " FROM ".$this->DB->Table('ref')."";
			$sql .= " WHERE ".$this->DB->Field('ref','date')." >= '".$this->_DBdateFrom."'";
			$sql .= " AND ".$this->DB->Field('ref','date')." <= '".$this->_DBdateTo."'";

			switch($this->_DBGroupByFunction)
			{
				case '1': $sql .= " GROUP BY ".$this->DB->Field('ref','local');   break;
				case '2': $sql .= " GROUP BY ".$this->DB->Field('ref','domain'); break;
				case '3': $sql .= " GROUP BY ".$this->DB->Field('ref','client');     break;
				default: break;
			}

			if (strlen(trim($this->_DBsort)) > 0)
				$sql .= " ORDER BY ".$this->DB->Table('ref').".".$this->_DBsort." ".$this->_DBsortOrder;

			return $this->DB->ReturnQueryResult($sql);
		}
	}

?>