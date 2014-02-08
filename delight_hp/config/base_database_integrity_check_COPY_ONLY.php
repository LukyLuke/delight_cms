<?php
		die("not for direct access");

		/**
		 * Check Database integrity
		 *
		 * This function creates all required Tables, Updates, Inserts and Deletes.
		 */
		function _checkDatabase() {
			// Get the current Version
			$version = 0;
			$versionid = 0;
			$sql  = "SELECT *";
			$sql .= " FROM ".$this->DB->Table('opt')."";
			$sql .= " WHERE ".$this->DB->Field('opt','name')."='".get_class($this)."'";
			$res = $this->DB->ReturnQueryResult($sql);
			if ($res) {
				$row = mysql_fetch_assoc($res);
				$version = $row[$this->DB->FieldOnly('opt','version')];
				$versionid = $row[$this->DB->FieldOnly('opt','id')];
			}
			$this->DB->FreeDatabaseResult($res);

			// If the $version is '0' or lower, check if the table 'opt' exists and create it
			if ($version <= 0) {
				$sql  = "CREATE TABLE ".$this->DB->Table('opt')." IF NOT EXISTS".
				        " ".$this->DB->FieldOnly('opt','id')." INT(10) UNSIGNED NOT NULL DEFAULT '0' AUTO_INCREMENT,".
				        " ".$this->DB->FieldOnly('opt','version')." INT(10) UNSIGNED NOT NULL DEFAULT '0',".
				        " ".$this->DB->FieldOnly('opt','id')." VARCHAR(50) NOT NULL DEFAULT '',".
				        " ".$this->DB->FieldOnly('opt','lastmod')." INT(10) UNSIGNED NOT NULL DEFAULT '0',".
				        " PRIMARY KEY (id),".
				        " UNIQUE KEY id (id),".
				        " ) TYPE=MyISAM;";
				$this->DB->ReturnQueryResult($sql);
			}

			// Updates to the Database
			if ($version < 2005010500) {
			}

			// Update the Version-Table
			if ($version < $this->VERSION) {
				if ($versionid <= 0) {
					$sql  = "INSERT INTO ".$this->DB->Table('opt')."";
					$sql .= " (".$this->DB->FieldOnly('opt','version').",".$this->DB->FieldOnly('opt','name').",".$this->DB->FieldOnly('opt','lastmod').")";
					$sql .= " VALUES ('".$this->VERSION."','".get_class($this)."','".time()."');";
					$res = $this->DB->ReturnQueryResult($sql);
				} else {
					$sql  = "UPDATE ".$this->DB->Table('opt')."";
					$sql .= " SET ".$this->DB->FieldOnly('opt','version')."='".$this->VERSION."',";
					$sql .= " ".$this->DB->FieldOnly('opt','lastmod')."='".time()."'";
					$sql .= " WHERE ".$this->DB->FieldOnly('opt','id')."=".$versionid.";";
					$res = $this->DB->ReturnQueryResult($sql);
				}
			}
		}
?>