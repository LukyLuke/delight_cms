<?php

class pSimpleShopProduct {
	var $productId;
	var $data;
	var $params;
	
	public function __construct() {
		$this->productId = 0;
		$this->params = array();
		$this->data = array();
		
		$this->data['id'] = null;
		$this->data['section'] = null;
		$this->data['section_name'] = null;
		$this->data['name'] = null;
		$this->data['number'] = null;
		$this->data['title'] = null;
		$this->data['descr'] = null;
		$this->data['price'] = null;
		$this->data['currency'] = null;
	}
	
	public function load($product) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = "SELECT * FROM [table.ssproducts] WHERE [ssproducts.id]=".(int)$product.";";
		$db->run($sql, $res);
		if ($res->getNext()) {
			$this->productId = (int)$product;
			$this->data['id'] = $res->{$db->getFieldName('ssproducts.id')};
			$this->data['section'] = $res->{$db->getFieldName('ssproducts.producs_category')};
			$this->data['name'] = $res->{$db->getFieldName('ssproducts.name')};
			$this->data['number'] = $res->{$db->getFieldName('ssproducts.number')};
			$this->data['title'] = $res->{$db->getFieldName('ssproducts.title')};
			$this->data['descr'] = $res->{$db->getFieldName('ssproducts.descr')};
			$this->data['price'] = number_format((float)$res->{$db->getFieldName('ssproducts.price')}, 2, '.', '');
			$this->data['currency'] = $res->{$db->getFieldName('ssproducts.currency_id')};
			
			// Load the Category
			$sql = "SELECT [sscat.name] FROM [table.sscat] WHERE [sscat.id]=".(int)$this->data['section'].";";
			$res = null;
			$db->run($sql, $res);
			if ($res->getNext()) {
				$this->data['section_name'] = $res->{$db->getFieldName('sscat.name')};
			}
			
			// Load the Currency-Symbol
			switch ((int)$this->data['currency']) {
				case 0: $this->data['currencySymbol']  = 'CHF'; break;
				case 1: $this->data['currencySymbol']  = 'EUR'; break;
				default: $this->data['currencySymbol'] = 'CHF'; break;
			}
			
			
			// Load all parameters
			$sql = "SELECT [ssparams.name],[ssparams.value] FROM [table.ssparams] WHERE [ssparams.product]=".(int)$this->data['id'].";";
			$res = null;
			$db->run($sql, $res);
			if ($res->getFirst()) {
				while ($res->getNext()) {
					$this->params[$res->{$db->getFieldName('ssparams.name')}] = $res->{$db->getFieldName('ssparams.value')};
				}
			}
			
		}
	}
	
	public function save() {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$first = true;
		$error = false;
		
		if ($this->productId > 0) {
			$sql = "UPDATE [table.ssproducts] SET ";
			$first = true;
			foreach ($this->data as $k => $v) {
				if ( ($k != 'id') && ($k != 'section_name') && ($k != 'currencySymbol') ) {
					if (!$first) {
						$sql .= ",";
					}
					$sql .= "[ssproducts.".$k."]='".$v."'";
					$first = false;
				}
			}
			$sql .= " WHERE [ssproducts.id]=".$this->productId.";";
			$db->run($sql, $res);
			$error = ($res->errorNumber() > 0);
			$res = null;
			
		} else {
			$values = "";
			$sql = "INSERT INTO [table.ssproducts] (";
			$first = true;
			foreach ($this->data as $k => $v) {
				if ( ($k != 'id') && ($k != 'section_name') && ($k != 'currencySymbol') ) {
					if (!$first) {
						$sql .= ",";
						$values .= ",";
					}
					$sql .= "[ssproducts.".$k."]";
					$values .= "'".$v."'";
					$first = false;
				}
			}
			$sql .= ") VALUES (".$values.");";
			$db->run($sql, $res);
			$this->productId = $res->getInsertId();
			$error = ($res->errorNumber() > 0);
			$res = null;
		}
		
		if (!$error) {
			// Remove all Product-Parameters and insert them after
			$sql = "DELETE FROM [table.ssparams] WHERE [ssparams.product]=".$this->productId.";";
			$db->run($sql, $res);
			$res = null;
			
			$first = true;
			$sql = "INSERT INTO [table.ssparams] ([ssparams.product],[ssparams.name],[ssparams.value]) VALUES ";
			foreach ($this->params as $k => $v) {
				if (!$first) {
					$sql = ',';
				}
				$sql .= "(".$this->productId.",'".$k."','".$v."')";
			}
			$sql .= ";";
			$db->run($sql, $res);
			$res = null;
		}
		return !$error;
	}
	
	public function delete() {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		if ($this->productId > 0) {
			$sql = "DELETE FROM [table.ssproducts] WHERE [field.ssproducts.id]=".$this->productId.";";
			$db->run($sql, $res);
			$res = null;
			
			// Remove all Product-Parameters and insert them after
			$sql = "DELETE FROM [table.ssparams] WHERE [ssparams.product]=".$this->productId.";";
			$db->run($sql, $res);
			$res = null;
		}
	}
	
	public function getJSONProduct($product) {
		$this->load($product);
		
		$back = '{';
		$first = true;
		foreach ($this->data as $k => $v) {
			if (!$first) {
				$back .= ',';
			} else {
				$first = false;
			}
			$back .= '"'.$k.'":"'.$this->escapeJSON($v).'"';
		}
		
		$back .= ',"params":[';
		$first = false;
		foreach ($this->params as $k => $v) {
			if (!$first) {
				$back .= ',';
			} else {
				$first = false;
			}
			$back .= '"'.$this->escapeJSON($k).'":"'.$this->escapeJSON($v).'"';
		}
		$back .= ']';
		$back .= '}';
		return $back;
	}
	
	private function escapeJSON($val) {
		$val = htmlentities($val);
		$val = str_replace("\n", '\n', $val);
		return $val;
	}
	
	public function getImageTag($params) {
		if ($params{0} == ':') {
			$params = substr($params, 1);
		}
		$params = split(',', $params);
		
		// TODO: Implement Images
		return '';
	}
	
	public function __get($name) {
		if ($name == 'description') {
			$name = 'descr';
		}
		
		if (array_key_exists($name, $this->data)) {
			return $this->data[$name];
		} else if (array_key_exists($name, $this->params)) {
			return $this->params[$name];
		}
		return null;
	}
	
	public function __set($name, $value) {
		if ($name == 'description') {
			$name = 'descr';
		}
		
		if (array_key_exists($name, $this->data)) {
			$this->data[$name] = $value;
		} else {
			$this->params[$name] = $value;
		}
	}
	
}

?>