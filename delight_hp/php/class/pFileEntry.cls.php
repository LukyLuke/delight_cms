<?php

class pFileEntry extends pTextEntry {
	protected $showTitle = false;

	public function setShowTitle($show=false) {
		$this->showTitle = $show;
	}

	protected function getTextData() {
		if ($this->textId != null) {
			$lang = pMessages::getLanguageInstance();
			$db = pDatabaseConnection::getDatabaseInstance();
			$sql  = "SELECT * FROM [table.prg] WHERE [prg.id]=".(int)$this->textId.";";
			$res = null;
			$db->run($sql, $res);
			if ($res->getFirst()) {
				$absoluteFile = ABS_DATA_DIR.'downloadfiles'.DIRECTORY_SEPARATOR.utf8_decode($res->{$db->getFieldName('prg.program')});
				$relativeFile = DATA_DIR.'downloadfiles/'.utf8_decode($res->{$db->getFieldName('prg.program')});

				if (file_exists($absoluteFile)) {
					$mime = $this->getMimeInfo($res->{$db->getFieldName('prg.name')});
					$fstat = stat($absoluteFile);

					$this->loaded = true;
					$this->textId = (int)$res->{$db->getFieldName('img.id')};
					$this->textData['file']     = $res->{$db->getFieldName('prg.program')};
					$this->textData['name']     = $res->{$db->getFieldName('prg.name')};
					$this->textData['public']   = $res->{$db->getFieldName('prg.register')} < 1;
					$this->textData['secure']   = $res->{$db->getFieldName('prg.secure')} > 0;
					$this->textData['local']    = $res->{$db->getFieldName('prg.local')};
					$this->textData['section']  = $res->{$db->getFieldName('prg.section')};
					$this->textData['direct']   = $relativeFile;
					$this->textData['download'] = '/download/'.$this->textId.'/'.$this->textData['name'];
					$this->textData['file_src'] = $absoluteFile;
					$this->textData['mime']     = $mime['MimeType'];
					$this->textData['icon']     = $mime['IconRelative'];
					$this->textData['icon_width'] = 32;
					$this->textData['icon_height'] = 32;
					$this->textData['comment']  = $mime['Comment'];
					$this->textData['size']     = $fstat[7];
					$this->textData['date']     = $fstat[9];
					$this->textData['last']     = 0;
					$this->textData['viewed']   = 0;
					$this->textData['text_id']  = 0;
					$this->textData['title']    = '';
					$this->textData['text']     = '';

					$res = null;
					$sql = 'SELECT * FROM [table.prt] WHERE [prt.program]='.$this->textId.' AND [prt.lang]='.$lang->getLanguageId().';';
					$db->run($sql, $res);
					if ($res->getFirst()) {
						$this->textData['text_id'] = $res->{$db->getFieldName('prt.id')};
						$this->textData['title']   = $res->{$db->getFieldName('prt.title')};
						$this->textData['text']    = $res->{$db->getFieldName('prt.text')};
					}
					$res = null;

					$sql = 'SELECT [dll.time] as last FROM [table.dll] WHERE [dll.file]='.$this->textId.' LIMIT 0,1;';
					$db->run($sql, $res);
					if ($res->getFirst()) {
						$this->textData['last'] = strtotime($res->last);
					}
					$res = null;

					$sql = 'SELECT COUNT([dll.file]) AS num FROM [table.dll] WHERE [dll.file]='.$this->textId.';';
					$db->run($sql, $res);
					if ($res->getFirst()) {
						$this->textData['viewed'] = $res->num;
					}
					$res = null;

				} else {
					$this->textData = array();
					$this->textId = null;
				}
			} else {
				$this->textData = array();
				$this->textId = null;
			}
		}
	}

	public function delete() {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		if (is_file($this->real)) {
			unlink($this->real);
		}
		if ($this->textId > 0) {
			$sql = 'DELETE FROM [table.prt] WHERE [field.prt.program]='.$this->textId.';';
			$db->run($sql);
			$sql = 'DELETE FROM [table.prg] WHERE [field.prg.id]='.$this->textId.';';
			$db->run($sql);
		}
	}

	public function save() {
		$lang = pMessages::getLanguageInstance();
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;

		if ($this->loaded) {
			$sql = 'UPDATE [table.prg] SET [field.prg.name]=\''.$this->textData['name'].'\',[field.prg.section]='.$this->textData['section'].',[field.prg.program]=\''.$this->textData['file'].'\' WHERE [field.prg.id]='.(int)$this->textId.';';
			$db->run($sql, $res);

		} else {
			$pos = 0;
			$sql = 'SELECT MAX([prg.order]) AS order FROM [table.prg] WHERE [prg.section]='.$this->textData['section'].';';
			$db->run($sql, $res);
			if ($res->getFirst()) {
				$pos = (int)$res->order;
				$pos++;
			}
			$res = null;

			$sql = 'INSERT INTO [table.prg] ([field.prg.program],[field.prg.section],[field.prg.name],[field.prg.mime],[field.prg.order]) VALUES';
			$sql .= ' (\''.$this->textData['file'].'\','.$this->textData['section'].',\''.$this->textData['name'].'\',\''.$this->textData['mime'].'\','.$pos.');';
			$db->run($sql, $res);
			$this->textId = $res->getInsertId();
		}
		$res = null;

		if (!array_key_exists('text_id', $this->textData)) {
			$this->textData['text_id'] = 0;
			$this->textData['title'] = '';
			$this->textData['text'] = '';
		}

		if ($this->textData['text_id'] > 0) {
			$sql = 'UPDATE [table.prt] SET [field.prt.title]=\'\''.mysql_real_escape_string($this->textData['title']).'\',[ield.prt.text]=\'\''.mysql_real_escape_string($this->textData['text']).'\' WHERE [prt.field.id]='.$this->textData['text_id'].';';
			$db->run($sql, $res);
		} else {
			$sql = 'INSERT INTO [table.prt] ([field.prt.title],[field.prt.text],[field.prt.program],[field.prt.lang]) VALUES (\''.mysql_real_escape_string($this->textData['title']).'\',\'\''.mysql_real_escape_string($this->textData['text']).'\','.$this->textId.','.$lang->getLanguageId().');';
			$db->run($sql, $res);
			$this->textData['text_id'] = $res->getInsertId();
		}
		$res = null;
	}

	public function setRenderOptions(array $options) {
		parent::setRenderOptions($options);
	}

	public function __get($name) {
		$lang = pMessages::getLanguageInstance();

		if (($name == 'url') || ($name == 'value') || ($name == 'content')) {
			if (!array_key_exists('download', $this->textData)) {
				return 'about:blank';
			}
			if ($name == 'url') {
				return $this->download;
			}
			return '<a href="'.$this->download.'" title="'.$this->title.'">'.$this->name.'</a>';

		} else if ($name == 'real') {
			return ABS_DATA_DIR.'downloadfiles'.DIRECTORY_SEPARATOR.$this->file;

		} else if ($name == 'file') {
			if (empty($this->textData['file'])) {
				return 'unknown';
			}
			return $this->textData['file'];

		} else if ($name == 'icon_tag') {
			return '<img src="'.$this->icon.'" style="width:32px;height:32px;vertical-align:middle;" alt="'.$this->mime.'" title="'.$this->comment.'" />';

		} else if ($name == 'source_value') {
			return $this->getTextId();

		}
		return parent::__get($name);
	}

	public function __set($name, $value) {
		if ($name == 'name') {
			$mime = $this->getMimeInfo($value);
			$this->textData['name']     = $value;
			$this->textData['download'] = '/download/'.$this->textId.'/'.$this->textData['name'];
			$this->textData['mime']     = $mime['MimeType'];
			$this->textData['icon']     = $mime['IconRelative'];
			$this->textData['comment']  = $mime['Comment'];

			if (empty($this->textData['file'])) {
				$this->textData['file'] = md5(uniqid(rand(), true)).'.'.$mime['Extension'];;
			}

		}
		parent::__set($name, $value);
	}
}