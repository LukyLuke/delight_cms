<?php

if (file_exists("./admin/plugin_1500/main_statistic.cls.php"))
	require_once("./admin/plugin_1500/main_statistic.cls.php");
else
	print("Failed to load Classfile: admin/plugin_1500/main_statistic.cls.php");

	class statistic_class_download_country extends statistic_class_main
	{
		function statistic_class_download_country()
		{
			statistic_class_main::statistic_class_main();
			$this->_pluginName   = 'download_country';
			$this->_pluginSymbol = 'statistic_download_country.gif';
		}

		function getSource()
		{
			$res = $this->getLogDataset();
			if ($res)
			{
				$data = array();
				$max = 0;
				while ($row = mysql_fetch_array($res, MYSQL_NUM))
				{
					$_country = $this->_parseCountry($row[0]);
					if (array_key_exists($_country, $data))
						$data[$_country]++;
					else
						$data[$_country] = 1;
					if ($data[$_country] > $max)
						$max = $data[$_country];
				}

				if ( (strtolower($this->_DBsort) == "country") && (strtolower($this->_DBsortOrder) == "asc") )
					ksort($data);
				else if ( (strtolower($this->_DBsort) == "country") && (strtolower($this->_DBsortOrder) == "desc") )
					krsort($data);
				else if ( (strtolower($this->_DBsortOrder) == "asc") )
					asort($data);
				else
					arsort($data);

				$html = $this->getStatisticHeader("", array("country","number","percent"));
				$cnt = 0;
				foreach ($data as $k => $v)
				{
					$country = $k;
					$current = $v;
					$percent = round(( ((integer)$current * 100) / $max ),2);
					$html .= $this->getStatisticRow("", array($country, $current, $percent."%"), $cnt, 250);
					$this->_statisticData[$country] = $current;
					$cnt++;
				}
				$html .= $this->getStatisticFooter("", array("country","number","percent"));
			}
			else
				$html = 'no statistic data found';

			return $html;
		}

		function getLogDataset()
		{
			$sql  = "SELECT ".$this->DB->Field('dll','domain')."";
			$sql .= " FROM ".$this->DB->Table('dll')."";
			$sql .= " WHERE ".$this->DB->Field('dll','time')." >= '".$this->_DBdateFrom."'";
			$sql .= " AND ".$this->DB->Field('dll','time')." <= '".$this->_DBdateTo."'";

			return $this->DB->ReturnQueryResult($sql);
		}
	}

?>