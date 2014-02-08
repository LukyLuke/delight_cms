<?php

class plg1500_download extends plg1500_plugin {

	/**
	 * Initialization
	 *
	 */
	public function __construct() {
		parent::__construct('download', 'statistics_download.gif');
	}

	/**
	 * Get the URL to an Image which shows the statistic
	 *
	 * @return stdClass List with different Image-URL's
	 * @access public
	 */
	public function getImageGraphLinkList() {
		$back = new stdClass();
		$back->number = '';
		$back->size = '';
		$list = $this->getStatisticData('number', 'ASC');
		
		// Get Link for NUMBER OF DOWNLOADS
		$cnt = 0;
		$others = 0;
		foreach ($list as $data) {
			if ($cnt > 8) {
				$others += $data->num;
			} else {
				if ($cnt > 0) {
					$back->number .= ';';
				}
				$back->number .= $data->num.':'.$data->name;
			}
			$cnt++;
		}
		$back->number .= ';'.$others.':Others';
		
		// Get Link for MAX DOWNLOAD TRAFFIC
		usort($list, array('plg1500_download', '_sortByTraffic'));
		$cnt = 0;
		$others = 0;
		foreach ($list as $data) {
			if ($cnt > 8) {
				$others += ($data->num * $data->size);
			} else {
				if ($cnt > 0) {
					$back->size .= ';';
				}
				$back->size .= ($data->num * $data->size).':'.$data->name;
			}
			$cnt++;
		}
		//$back->size .= ';'.$others.':Others';
		
		$back->number = $this->graphUrl.base64_encode($back->number);
		$back->size = $this->graphUrl.base64_encode($back->size);
		
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
		$timeFrom = pURIParameters::get('from', date('Y-m-d 00:00:01', mktime(0, 0, 0, date('m'), 1, date('Y')) ), pURIParameters::$STRING);
		$timeTo = pURIParameters::get('to', date('Y-m-d 23:59:59', mktime(0, 0, 0, date('m'), date('t'), date('Y')) ), pURIParameters::$STRING);
		
		$timeFrom = strtotime($timeFrom);
		$timeTo = strtotime($timeTo);
		
		$sizeTotal = 0;
		$numTotal = 0;
		
		$sql = 'SELECT [dll.file],[dll.size] FROM [table.dll] WHERE [dll.time]>=\''.date('Y-m-d H:i:s', $timeFrom).'\' AND [dll.time]<=\''.date('Y-m-d H:i:s', $timeTo).'\' ORDER BY [dll.file];';
		$db->run($sql, $res);
		if ($res->getFirst()) {
			while ($res->getNext()) {
				$file = $res->{$db->getFieldName('dll.file')};
				$size = (int)$res->{$db->getFieldName('dll.size')};
				$key = sha1($file);
				
				if (!array_key_exists($key, $back)) {
					$back[$key] = new stdClass();
					$back[$key]->name = $file;
					$back[$key]->num = 0;
					$back[$key]->size = $size;
					$back[$key]->hrsize = $this->getHumanReadableSize($size);
				}
				
				$back[$key]->num += 1;
				$numTotal += 1;
				$sizeTotal += $size;
			}
			$res = null;

			// Sort the Data based on the requested order
			$order = (substr(strtolower($order), 0, 1) == 'a');
			switch (strtolower($sort)) {
				case 'name':
					usort($back, array('plg1500_download', $order ? '_sortByFilename' : '_rsortByFilename'));
					break;
				case 'size':
					usort($back, array('plg1500_download', $order ? '_sortByTraffic' : '_rsortByTraffic'));
					break;
				case 'number':
				default:
					usort($back, array('plg1500_download', $order ? '_sortByNumber' : '_rsortByNumber'));
					break;
			}
			
			// Calc Percent-Values
			foreach ($back as &$data) {
				$size = $data->num * $data->size;
				$data->total_hrsize = $this->getHumanReadableSize($size);
				$data->percent_size =round($size / $sizeTotal, 5);
				$data->percent_num = round($data->num / $numTotal, 5);
			}
			unset($size);
			unset($numTotal);
			unset($sizeTotal);
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
	public static function _sortByNumber($a, $b) {
		return $a->num < $b->num;
	}
	public static function _rsortByNumber($a, $b) {
		return $a->num > $b->num;
	}
	
	/**
	 * Sort-Helper-Function: Sort by Total Download-Size
	 *
	 * @param stdClass $a
	 * @param stdClass $b
	 * @return boolean
	 */
	public static function _sortByTraffic($a, $b) {
		return ($a->num * $a->size) < ($b->num * $b->size);
	}
	public static function _rsortByTraffic($a, $b) {
		return ($a->num * $a->size) > ($b->num * $b->size);
	}
	
	/**
	 * Sort-Helper-Function: Sort by Filename
	 *
	 * @param stdClass $a
	 * @param stdClass $b
	 * @return boolean
	 */
	public static function _sortByFilename($a, $b) {
		return strcasecmp($a->name, $b->name);
	}
	public static function _rsortByFilename($a, $b) {
		return strcasecmp($b->name, $a->name);
	}
}

?>