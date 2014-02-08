<?php
/**
 * SimpleShop
 */

class admin_100000_Settings extends admin_MAIN_Settings {
	const OBJ_NAME = 'SIMPLESHOP';
	private $imageRealPath;

	public function __construct() {
		parent::__construct();
		$this->imageRealPath = realpath('./images/page/simpleshop/');

		if (!is_dir($this->imageRealPath)) {
			@mkdir($this->imageRealPath, 0777);
		}

		// Insert and create the Class - this action will create all needed tables and Entries
		$obj = self::OBJ_NAME;
		$obj = new $obj();
		unset($obj);
	}

	/**
	 * Call a function, based on parameter adm
	 * This is the main function, which will be called for getting some content
	 */
	public function createActionBasedContent() {
		$userCheck = pCheckUserData::getInstance();
		die('The Simpleshop-Plugin is currently disabled while the new Administration-Interface is under development');

		if ($userCheck->checkAccess(self::OBJ_NAME)) {
			switch (1000 + $this->_mainAction) {
				case  1013: // SectionList over new Admin-Interface
					echo $this->_getJSONSectionList(0, 'sscat', 'sscat');
					exit();
					break;

				case  1014: // Add and change a Section over new AdminInterface
					$id = pURIParameters::get('id', 0, pURIParameters::$INT);
					$parent = pURIParameters::get('parent', 0, pURIParameters::$INT);
					$name = utf8_decode(pURIParameters::get('sectionname', 'Neuer Ordner', pURIParameters::$STRING));
					$newid = $this->_changeSectionParameters($id, $name, $parent, 'sscat');
					echo '{"name":"'.$name.'", "oldid":'.$id.', "id":'.$newid.', "parent":'.$parent.'}';
					exit();
					break;

				case  1015: // Delete a Section and all it's images and subsections over new AdminInterface
					$id = pURIParameters::get('id', 0, pURIParameters::$INT);
					$this->_deleteSection($id);
					echo '{"id":'.$id.', "deleted":true}';
					exit();
					break;

				case  1016: // Show all Products from a Section over new AdminInterface
					$section = pURIParameters::get('section', 0, pURIParameters::$INT);
					echo '{"action":100000,"method":16,"data":';
					echo $this->_getJSONProductsList($section);
					echo '}';
					exit();
					break;

				case  1017:  // Store an uploaded image over new AdminInterface
					$section = pURIParameters::get('section', 0, pURIParameters::$INT);
					$replace = pURIParameters::get('replace', 0, pURIParameters::$INT);
					$window = pURIParameters::get('window', 0, pURIParameters::$INT);
					$action = pURIParameters::get('action', 0, pURIParameters::$STRING);
					$error = $this->_storeUploadedImage($section, $_FILES['import'], $replace);
					$redirect = $_SERVER['HTTP_REFERER'];
					echo "<html><head><script type=\"text/javascript\">location.href='".$redirect."?win=".$window."&action=".$action."&section=".$section."&error=".urlencode($error)."';</script></head></html>";
					exit();
					break;

				case  1018:  // Deletes a product over new AdminInterface
					$id = pURIParameters::get('id', 0, pURIParameters::$INT);
					$section = $this->_getSectionIdFromObject($id, 'ssproducts');
					$this->_deleteProduct($id);
					echo '{"section":'.$section.', "deleted":true}';
					exit();
					break;

				case  1019:  // Show all Data from a single image as JSON for new AdminInterface
					$id = pURIParameters::get('id', 0, pURIParameters::$INT);
					$obj = new pSimpleShopProduct();
					echo $obj->getJSONProduct($id);
					exit();
					break;
				case  1068:  // Show all Data from a single image as JSON for new AdminInterface
					$id = pURIParameters::get('id', 0, pURIParameters::$INT);
					$obj = new pSimpleShopProduct();
					$obj->load($id);
					$obj->section = pURIParameters::get('section', 0, pURIParameters::$INT);
					$obj->title = utf8_decode(pURIParameters::get('_title', '', pURIParameters::$STRING));
					$obj->name = utf8_decode(pURIParameters::get('_name', '', pURIParameters::$STRING));
					$obj->number = pURIParameters::get('_number', '', pURIParameters::$STRING);
					$obj->price = pURIParameters::get('_price', 0, pURIParameters::$DOUBLE);
					$obj->currency = pURIParameters::get('_currency', 1, pURIParameters::$INT);
					$obj->description = utf8_decode(pURIParameters::get('_descr', '', pURIParameters::$STRING));
					if ($obj->save()) {
						echo '{"success":true}';
					} else {
						echo '{"success":false}';
					}
					exit();
					break;

				case  1070:
				case  1069:  // Show all Currencies from a single image as JSON for new AdminInterface
					$id = pURIParameters::get('id', 0, pURIParameters::$INT);
					echo '{"list":[[1,"CHF"]]}';
					exit();
					break;

				case  1020:  // Save all Configuration-Parameters
					$currency = pURIParameters::get('_currency', '', pURIParameters::$STRING);
					$currency = 'CHF';

					$this->_storeSetting('user', pURIParameters::get('_user', '', pURIParameters::$STRING));
					$this->_storeSetting('pass', pURIParameters::get('_pass', '', pURIParameters::$STRING));
					$this->_storeSetting('tid', pURIParameters::get('_clientid', '', pURIParameters::$STRING));
					$this->_storeSetting('host', pURIParameters::get('_host', '', pURIParameters::$STRING));
					$this->_storeSetting('currency', $currency);
					echo '{"success":true}';
					exit();
					break;
				case  1021:  // Load all Configuration-Parameters
					$settings = $this->_getJsonSettings();
					echo '{"settings":'.$settings.'}';
					exit();
					break;

			}
		} else {
			$this->showNoAccess();
		}
	}

	/**
	 * Get all Products as a JSON-Formated List
	 *
	 * @param integer $section The Section to get all Products from
	 * @return String The Products from the given Section as a JSON-Formated-List
	 */
	private function _getJSONProductsList($section) {
		$back = '';
		$product = new pSimpleShopProduct();
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = "SELECT [ssproducts.id] FROM [table.ssproducts] WHERE [ssproducts.section]=".(int)$section." ORDER BY [ssproducts.id];";
		$db->run($sql, $res);
		$back = '[';
		if ($res->getFirst()) {
			while ($res->getNext()) {
				if (strlen($back) > 1) {
					$back .= ',';
				}
				$back .= $product->getJSONProduct($res->{$db->getFieldName('ssproducts.id')});
			}
		}
		$res = null;
		$back .= ']';
		return $back;
	}

	/**
	 * Delete the given Product
	 *
	 * @param integer $id The ProductID
	 */
	private function _deleteProduct($id) {
		$obj = new pSimpleShopProduct();
		$obj->load($id);
		$obj->delete();
	}

	/**
	 * Get a list with all settings and retun them as a JSON-Object
	 *
	 * @param integer $category default=0 The Category to get all settings from
	 * @return JSON-String
	 */
	private function _getJsonSettings($category=0) {
		$back = '';
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = "SELECT * FROM [table.sssettings] WHERE [sssettings.category]=".(int)$category." ORDER BY [sssettings.name] ASC;";
		$db->run($sql, $res);
		if ($res->getFirst()) {
			while ($res->getNext()) {
				if (!empty($back)) {
					$back .= ',';
				}
				$back .= '"'.$res->{$db->getFieldName('sssettings.name')}.'":';
				$back .= '"'.str_replace('"', '&#34;', $res->{$db->getFieldName('sssettings.value')}).'"';
			}
		}
		return '{'.$back.'}';
	}

	/**
	 * Save a Setting defined by the name and the category
	 *
	 * @param string $name The Name of the Setting
	 * @param string $value The Value
	 * @param integer $category default=0, The Category to save the setting for
	 */
	private function _storeSetting($name, $value, $category=0) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = "DELETE FROM [table.sssettings] WHERE [sssettings.category]=".(int)$category." AND [sssettings.name]='".mysql_real_escape_string($name)."';";
		$db->run($sql, $res);
		if (!empty($value)) {
			$sql = "INSERT INTO [table.sssettings] SET [field.sssettings.category]=".(int)$category.", [field.sssettings.name]='".mysql_real_escape_string($name)."', [field.sssettings.value]='".mysql_real_escape_string($value)."';";
			$db->run($sql, $res);
		}
	}

	private function _deleteSection($id) {
		// First get the Parent-Section to switch to after a successfull delete
		$_parent = $this->_getParentSectionData($id, 'sscat');
		$_parent = $_parent['par'];
		$_childs = $this->_getChildSections($id, 'sscat', true, array($id));
		$_childs = array_reverse($_childs);
		$_success = true;

		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;

		// Delete each Section
		foreach ($_childs as $k => $v) {
			$sql = "SELECT [ssproducts.id] FROM [table.ssproducts] WHERE [ssproducts.section]=".(int)$v.";";
			$db->run($sql, $res);
			if ($res->getFirst()) {
				while ($res->getNext()) {
					$this->_deleteProduct($res->{$db->getFieldName('ssproducts.id')});
				}
			}
			// finally delete the Section
			$sql = "DELETE FROM [table.sscat] WHERE [sscat.id]=".(int)$v.";";
			$db->run($sql, $res);
			$res = null;
		}
	}
}
?>