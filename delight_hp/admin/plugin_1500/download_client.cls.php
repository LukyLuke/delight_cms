<?php

if (file_exists("./admin/plugin_1500/main_statistic.cls.php"))
	require_once("./admin/plugin_1500/main_statistic.cls.php");
else
	print("Failed to load Classfile: admin/plugin_1500/main_statistic.cls.php");

	class statistic_class_download_client extends statistic_class_main
	{
		function statistic_class_download_client()
		{
			statistic_class_main::statistic_class_main();
			$this->_pluginName   = 'download_client';
			$this->_pluginSymbol = 'statistic_download_client.gif';
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
					$_browser = $this->_parseBrowser($row[0]);

					if (array_key_exists($_browser, $data))
						$data[$_browser]++;
					else
						$data[$_browser] = 1;

					if ($data[$_browser] > $max)
						$max = $data[$_browser];
				}

				if ( (strtolower($this->_DBsort) == "browser") && (strtolower($this->_DBsortOrder) == "asc") )
					ksort($data);
				else if ( (strtolower($this->_DBsort) == "browser") && (strtolower($this->_DBsortOrder) == "desc") )
					krsort($data);
				else if ( (strtolower($this->_DBsortOrder) == "asc") )
					asort($data);
				else
					arsort($data);

				$html = $this->getStatisticHeader("", array("browser", "number", "percent"));
				$cnt = 0;
				foreach ($data as $k => $v)
				{
					$current = $v;
					$percent = round(( ((integer)$current * 100) / $max ),2);
					$browser = $k;
					$html .= $this->getStatisticRow("", array($browser, $current, $percent."%"), $cnt, 250);
					$this->_statisticData[$browser] = $current;
					$cnt++;
				}
				$html .= $this->getStatisticFooter("", array("browser", "number", "percent"));
			}
			else
				$html = 'no statistic data found';

			return $html;
		}

		function getLogDataset()
		{
			$sql  = "SELECT ".$this->DB->Field('dll','browser')."";
			$sql .= " FROM ".$this->DB->Table('dll')."";
			$sql .= " WHERE ".$this->DB->Field('dll','time')." >= '".$this->_DBdateFrom."'";
			$sql .= " AND ".$this->DB->Field('dll','time')." <= '".$this->_DBdateTo."'";

			return $this->DB->ReturnQueryResult($sql);
		}
	}

?>