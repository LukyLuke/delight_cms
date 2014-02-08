<?php

class pImageEntry extends pTextEntry {
	protected $size = array('w'=>0, 'h'=>0);
	protected $showTitle = false;
	protected $returnUrlAsContent = false;
	protected $stretchImage = false;
	protected $scaleImage = true;

	public static function isValidMime($name) {
		$mime = self::getMimeInfo($name);
		switch ($mime['MimeType']) {
			case 'image/png':
			case 'image/gif':
			case 'image/jpg':
			case 'image/jpeg':
			case 'image/pjpeg':
			case 'image/svg-xml':
			case 'image/svg+xml':
			case 'application/x-shockwave-flash':
				return true;
		}
		return false;
	}

	public function setSize($w, $h) {
		$this->size['w'] = (int)$w;
		$this->size['h'] = (int)$h;

		// If the height or width is zero, scale the Image
		$this->scaleImage = !(($this->size['w'] > 0) && ($this->size['h'] > 0) && $this->stretchImage);

		// preserve dimension ratio
		$image = ABS_IMAGE_DIR.$this->image;
		if (is_file($image) && $this->scaleImage) {
			$size = getImageSize($image);
			$s = $this->size;
			if (($this->size['h'] <= 0) || ($size[0] >= $size[1])) {
				$ratio = $size[0]/$size[1];
				$this->size['h'] = (int)($this->size['w'] / $ratio);
				if (($s['h'] > 0) && ($this->size['h'] > $s['h'])) {
					$ratio = $size[1]/$size[0];
					$this->size['h'] = $s['h'];
					$this->size['w'] = (int)($this->size['h'] / $ratio);
				}
			} else {
				$ratio = $size[1]/$size[0];
				$this->size['w'] = (int)($this->size['h'] / $ratio);
				if (($s['w'] > 0) && ($this->size['w'] > $s['w'])) {
					$ratio = $size[0]/$size[1];
					$this->size['w'] = $s['w'];
					$this->size['h'] = (int)($this->size['w'] / $ratio);
				}
			}
		}
	}

	public function setShowTitle($show=false) {
		$this->showTitle = $show;
	}

	protected function getTextData() {
		if ($this->textId != null) {
			$lang = pMessages::getLanguageInstance();
			$db = pDatabaseConnection::getDatabaseInstance();
			$sql  = "SELECT * FROM [table.img] WHERE [img.id]=".(int)$this->textId;
			$res = null;
			$db->run($sql, $res);
			if ($res->getFirst()) {
				$mime = $this->getMimeInfo($res->{$db->getFieldName('img.image')});

				$this->loaded = true;
				$this->textId = (int)$res->{$db->getFieldName('img.id')};
				$this->textData['image']   = $res->{$db->getFieldName('img.image')};
				$this->textData['section'] = $res->{$db->getFieldName('img.section')};
				$this->textData['date']    = $res->{$db->getFieldName('img.date')};
				$this->textData['name']    = $res->{$db->getFieldName('img.name')};
				$this->textData['order']   = $res->{$db->getFieldName('img.order')};
				$this->textData['text_id']  = 0;
				$this->textData['title']    = '';
				$this->textData['text']     = '';
				$this->textData['mime']     = $mime['MimeType'];
				$this->textData['icon']     = $mime['IconRelative'];
				$this->textData['comment']  = $mime['Comment'];

				$res = null;
				$sql = 'SELECT * FROM [table.imt] WHERE [imt.image]='.$this->textId.' AND [imt.lang]='.$lang->getLanguageId().';';
				$db->run($sql, $res);
				if ($res->getFirst()) {
					$this->textData['text_id'] = $res->{$db->getFieldName('imt.id')};
					$this->textData['title']   = $res->{$db->getFieldName('imt.title')};
					$this->textData['text']    = $res->{$db->getFieldName('imt.text')};
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
		if (is_file($this->real) && (basename($this->real) != 'unknownImage.png')) {
			unlink($this->real);
		}
		if ($this->textId > 0) {
			$sql = 'DELETE FROM [table.imt] WHERE [field.imt.image]='.$this->textId.';';
			$db->run($sql);
			$sql = 'DELETE FROM [table.img] WHERE [field.img.id]='.$this->textId.';';
			$db->run($sql);
		}
	}

	public function save() {
		$lang = pMessages::getLanguageInstance();
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;

		if ($this->loaded) {
			$sql = 'UPDATE [table.img] SET [field.img.name]=\''.$this->textData['name'].'\',[field.img.section]='.$this->textData['section'].',[field.img.image]=\''.$this->textData['image'].'\' WHERE [field.img.id]='.(int)$this->textId.';';
			$db->run($sql, $res);

		} else {
			$pos = 0;
			$sql = 'SELECT MAX([img.order]) AS order FROM [table.img] WHERE [img.section]='.$this->textData['section'].';';
			$db->run($sql, $res);
			if ($res->getFirst()) {
				$pos = (int)$res->order;
				$pos++;
			}
			$res = null;

			$sql = 'INSERT INTO [table.img] ([field.img.image],[field.img.section],[field.img.name],[field.img.order],[field.img.date]) VALUES';
			$sql .= ' (\''.$this->textData['image'].'\','.$this->textData['section'].',\''.$this->textData['name'].'\','.$pos.','.time().');';
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
			$sql = 'UPDATE [table.imt] SET [field.imt.title]=\'\''.mysql_real_escape_string($this->textData['title']).'\',[ield.imt.text]=\'\''.mysql_real_escape_string($this->textData['text']).'\' WHERE [prt.field.id]='.$this->textData['text_id'].';';
			$db->run($sql, $res);
		} else {
			$sql = 'INSERT INTO [table.imt] ([field.imt.title],[field.imt.text],[field.imt.image],[field.imt.lang]) VALUES (\''.mysql_real_escape_string($this->textData['title']).'\',\'\''.mysql_real_escape_string($this->textData['text']).'\','.$this->textId.','.$lang->getLanguageId().');';
			$db->run($sql, $res);
			$this->textData['text_id'] = $res->getInsertId();
		}
		$res = null;
	}

	public function setRenderOptions(array $options) {
		$s = $this->size;
		if (array_key_exists('width', $options)) {
			$s['w'] = $options['width'];
		} else {
			$options['width'] = 0;
		}

		if (array_key_exists('height', $options)) {
			$s['h'] = $options['height'];
		} else {
			$options['height'] = 0;
		}

		if (array_key_exists('url', $options)) {
			$this->returnUrlAsContent = strtolower($options['url']) == 'true';
		}

		if (array_key_exists('stretch', $options)) {
			$this->stretchImage = strtolower($options['stretch']) == 'true';
		}

		if (array_key_exists('scale', $options)) {
			$this->scaleImage = strtolower($options['scale']) == 'true';
		}

		$this->setSize($s['w'], $s['h']);
		parent::setRenderOptions($options);
	}

	public function __get($name) {
		$lang = pMessages::getLanguageInstance();
		if (($name == 'url') || ($name == 'value') || ($name == 'content')) {
			if ($this->returnUrlAsContent) {
				$name = 'url';
			}
			if (!array_key_exists('image', $this->textData)) {
				$src = 'about:blank';
			} else {
				if (($this->size['w'] <= 0) || ($this->size['h'] <= 0)) {
					//$_size = getImageSize(ABS_IMAGE_DIR.$this->textData['image']);
					//$this->size = array('w' => $_size[0], 'h' => $_size[1]);
					$this->setSize($this->size['w'], $this->size['h']);
				}
				$keys = array_keys($this->renderOptions);
				if (in_array('mask', $keys) || in_array('opacity', $keys) || in_array('blur', $keys)) {
					$opt = new stdClass();
					$opt->width = 0;
					$opt->height = 0;
					foreach ($this->renderOptions as $k => $v) {
						if (($k == 'content') || ($k == 'empty')) continue;
						$opt->{$k} = $v;
					}
					$src = '/image/'.$lang->getShortLanguageName().'/'.urlencode(json_encode($opt)).'/'.$this->textData['image'];
				} else {
					$src = '/image/'.$lang->getShortLanguageName().'/'.$this->size['w'].'x'.$this->size['h'].'/'.($this->showTitle ? '1' : '0').'/'.$this->textData['image'];
				}
			}
			if ($name == 'url') {
				return $src;
			}
			return '<img src="'.$src.'" style="width:'.$this->size['w'].'px;height:'.$this->size['h'].'px;" alt="'.$this->title.'" title="'.$this->title.'" />';

		} else if ($name == 'realurl') {
			if (!array_key_exists('image', $this->textData)) {
				return 'about:blank';
			}
			return IMAGE_DIR.$this->textData['image'];

		} else if ($name == 'real') {
			if (!array_key_exists('image', $this->textData)) {
				return ABS_IMAGE_DIR.'unknownImage.png';
			}
			return ABS_IMAGE_DIR.$this->textData['image'];

		} else if ($name == 'source_value') {
			return $this->getTextId();

		} else if ($name == 'width') {
			return $this->size['w'];

		} else if ($name == 'height') {
			return $this->size['h'];

		} else if ($name == 'real_width') {
			$size = getimagesize($this->real);
			return $size[0];

		} else if ($name == 'real_height') {
			$size = getimagesize($this->real);
			return $size[1];

		} else if ($name == 'size') {
			return filesize($this->real);

		}
		return parent::__get($name);
	}

	public function __set($name, $value) {
		if ($name == 'name') {
			$mime = $this->getMimeInfo($value);
			$this->textData['name']     = $value;
			$this->textData['mime']     = $mime['MimeType'];
			$this->textData['icon']     = $mime['IconRelative'];
			$this->textData['comment']  = $mime['Comment'];

			if (empty($this->textData['image'])) {
				$this->textData['image'] = md5(uniqid(rand(), true)).'.'.$mime['Extension'];
			}

		}
		parent::__set($name, $value);
	}
}