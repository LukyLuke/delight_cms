<?php

if (file_exists("./admin/plugin_1500/main_statistic.cls.php"))
	require_once("./admin/plugin_1500/main_statistic.cls.php");
else
	print("Failed to load Classfile: admin/plugin_1500/main_statistic.cls.php");

	class statistic_class_login extends statistic_class_main
	{
		function statistic_class_login()
		{
			statistic_class_main::statistic_class_main();
			$this->_pluginName   = 'users';
			$this->_pluginSymbol = 'statistic_users.gif';
		}

		function getSource()
		{
			$res = $this->getLogDataset();
			if ($res)
			{
				$html = $this->getStatisticHeader($res);
				$cnt = 0;
				while ($row = mysql_fetch_array($res, MYSQL_NUM))
				{
					$html .= $this->getStatisticRow($res, $row, $cnt);
					if (!(isset($this->_statisticData[$row[0]])))
						$this->_statisticData[$row[0]] = 1;
					else
						$this->_statisticData[$row[0]]++;
					$cnt++;
				}
				$html .= $this->getStatisticFooter($res);
			}
			else
				$html = 'no statistic data found';

			return $html;
		}

		function getLogDataset()
		{
			$sql  = "SELECT ".$this->DB->Field('plo','user').",".$this->DB->Field('plo','action').",".$this->DB->Field('plo','time').",".$this->DB->Field('plo','ip').",".$this->DB->Field('plo','domain')."";
			$sql .= " FROM ".$this->DB->Table('plo')."";
			$sql .= " WHERE ".$this->DB->Field('plo','time')." >= '".$this->_DBdateFrom."'";
			$sql .= " AND ".$this->DB->Field('plo','time')." <= '".$this->_DBdateTo."'";

			switch($this->_DBGroupByFunction)
			{
				case '1': $sql .= " GROUP BY ".$this->DB->Field('plo','user');   break;
				case '2': $sql .= " GROUP BY ".$this->DB->Field('plo','action'); break;
				case '3': $sql .= " GROUP BY ".$this->DB->Field('plo','ip');     break;
				default: break;
			}

			if (strlen(trim($this->_DBsort)) > 0)
				$sql .= " ORDER BY ".$this->DB->Table('plo').".".$this->_DBsort." ".$this->_DBsortOrder;

			return $this->DB->ReturnQueryResult($sql);
		}
	}

?>