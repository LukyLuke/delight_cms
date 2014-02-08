<?php

$DBTables['gtext'] = $tablePrefix.'_global_texts';
$DBTables['gtsec'] = $tablePrefix.'_global_texts_sections';
$DBFields['gtext'] = array(
	'id' => 'id',
	'title' => 'title',
	'text' => 'content',
	'lang' => 'lang_id',
	'plugin' => 'text_parser',
	'section' => 'section_id',
	'date' => 'last_update'
);
$DBFields['gtsec'] = array(
	'id' => 'id',
	'parent' => 'parent',
	'text' => 'text'
);

class pGlobalText extends pTextEntry implements iUpdateIface {
	const MODULE_VERSION = 2010081303;

	public function __construct($textId) {
		parent::__construct($textId);
		$this->updateModule();
	}

	protected function getTextData() {
		if ($this->textId != null) {
			$lang = pMessages::getLanguageInstance();
			$db = pDatabaseConnection::getDatabaseInstance();
			$sql  = 'SELECT * FROM [table.gtext] WHERE [gtext.id]='.(int)$this->textId.';';
			$res = null;
			$db->run($sql, $res);
			if ($res->getFirst()) {
				$this->loaded = true;
				$this->textId = (int)$res->{$db->getFieldName('gtext.id')};
				$this->textData['title']   = $res->{$db->getFieldName('gtext.title')};
				$this->textData['text']    = $res->{$db->getFieldName('gtext.text')};
				$this->textData['lang']    = $res->{$db->getFieldName('gtext.lang')};
				$this->textData['plugin']  = $res->{$db->getFieldName('gtext.plugin')};
				$this->textData['section'] = $res->{$db->getFieldName('gtext.section')};
				$this->textData['date']    = $res->{$db->getFieldName('gtext.date')};
			} else {
				$this->textData = array();
				$this->textId = null;
			}
		}
	}

	public function save() {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		if ($this->loaded) {
			$sql = 'UPDATE [table.gtext] SET [field.gtext.text]=\''.mysql_real_escape_string($this->text).'\',[field.gtext.title]=\''.mysql_real_escape_string($this->title).'\',[field.gtext.lang]='.$this->lang.',[field.gtext.plugin]=\''.$this->plugin.'\',[field.gtext.section]=\''.$this->section.'\',[field.gtext.date]='.time().' WHERE [field.gtext.id]='.$this->textId.';';
			$db->run($sql, $res);
		} else {
			$sql = 'INSERT INTO [table.gtext] ([field.gtext.text],[field.gtext.title],[field.gtext.lang],[field.gtext.plugin],[field.gtext.section],[field.gtext.date]) VALUES (\''.$this->text.'\',\''.$this->title.'\','.$this->lang.',\''.$this->plugin.'\','.$this->section.','.time().');';
			$db->run($sql, $res);
			$this->textId = $res->getInsertId();
		}
		return $res->getError() == null;
	}

	public function delete() {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		if ($this->loaded) {
			$sql = 'DELETE FROM [table.gtext] WHERE [field.gtext.id]='.$this->textId.';';
			$db->run($sql, $res);
		}
		return false;
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
			if ($version <= 0) {
				$sql = 'CREATE TABLE [table.gtext] ('.
				' [field.gtext.id] INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,'.
				' [field.gtext.title] VARCHAR(250) NOT NULL default \'\','.
				' [field.gtext.text] TEXT NOT NULL default \'\','.
				' [field.gtext.lang] INT(10) UNSIGNED NOT NULL default 0,'.
				' [field.gtext.plugin] VARCHAR(50) NOT NULL default \'TEXT\','.
				' [field.gtext.date] INT(10) UNSIGNED NOT NULL default 0,'.
				' PRIMARY KEY ([field.gtext.id]),'.
				' UNIQUE KEY [field.gtext.id] ([field.gtext.id])'.
				');';
				$db->run($sql, $res);
			}

			if ($version <= 2010081301) {
				$sql = 'CREATE TABLE [table.gtsec] ('.
				' [field.gtsec.id] INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,'.
				' [field.gtsec.parent] INT(10) UNSIGNED NOT NULL default 0,'.
				' [field.gtsec.text] VARCHAR(100) NOT NULL default \'\','.
				' PRIMARY KEY ([field.gtsec.id]),'.
				' UNIQUE KEY [field.gtsec.id] ([field.gtsec.id])'.
				');';
				$db->run($sql, $res);
			}

			if ($version <= 2010081302) {
				$sql = 'SELECT [gtsec.text] FROM [table.gtsec] WHERE [gtsec.text]=\'default\';';
				$db->run($sql, $res);
				if (!$res->getFirst()) {
					$sql = 'INSERT INTO [table.gtsec] ([field.gtsec.parent],[field.gtsec.text]) VALUES (0,\'default\');';
					$db->run($sql, $res);
				}
			}

			if ($version <= 2010081303) {
				$sql = 'ALTER TABLE [table.gtext] ADD COLUMN ([field.gtext.section] INT(10) UNSIGNED NOT NULL default 0);';
				$db->run($sql, $res);
			}

			// Update the version in database for this module
			$db->updateModuleVersion(get_class($this), self::MODULE_VERSION);
		}
	}
}