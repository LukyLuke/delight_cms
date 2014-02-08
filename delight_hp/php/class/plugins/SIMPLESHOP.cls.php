<?php

class SIMPLESHOP extends MENU implements iPlugin {
	const VERSION = 2008092502;
	const COOKIENAME = 'simpleshop';
	const MAX_QUANTITY = 65535;

	private $registered = false;
	private $shopTemplate;
	private $shopEntry;
	private $shopBaseContent;

	private $_entryContent;

	/**
	 * Initialization
	 */
	public function __construct() {
		$this->registered = (defined('DWP_PLUGIN_ACCESS_GRANTED') && (substr_count(DWP_PLUGIN_ACCESS_GRANTED, 'SIMPLESHOP') > -1));
		if ($this->registered) {
			parent::__construct();
			$this->_isTextPlugin = false;
			$this->_checkDatabase();

			/*if (!class_exists('pSimpleShopProduct')) {
				require_once('./php/class/classes/pSimpleShopProduct.cls.php');
			}*/
		}
	}

	/**
	 * Set the Parameters/Content from the TemplateTag
	 *
	 * @override iPlugin
	 * @param String $params The Parameters which are defined in the Template
	 * @access public
	 */
	public function setContentParameters($params) {
		$this->parsePluginContentParameters($params);
	}

	/**
	 * Call a Function in this Object (normally used by the TemplateTags)
	 *
	 * @override iPlugin
	 * @param String $function The Function to call
	 * @return mixed The Return value from the called function
	 * @access public
	 */
	public function callFunction($function) {

		// Show the Message "not registered" if it's a fact
		if (!$this->registered) {
			return "The SimpleShop-Plugin is not registered on this installation.";
		}

		// Check for a valid FunctionCall
		switch (strtolower($function)) {
			case 'showcategories':
				return $this->getShopCategories();
				break;

			case 'showcontent':
				return $this->showShopCategoryContent();
				break;
		}

		return null;
	}

	/**
	 * Get a List with all Pages (ant their links) this Plugin with given Parameters offers
	 *
	 * @overriden iPlugin
	 * @param integer $langId The Language-ID
	 * @param integer $menuId The Menu-ID
	 * @param string $shortMenu optional ShortMenu name
	 * @param boolean $menuIsActive optional If this menu is active or not - default active
	 * @return array File- and Linklist
	 */
	public function getStaticPagesList($langId, $menuId, $shortMenu='', $menuIsActive=true) {
		return $this->createStaticPagesList();
	}

	private function createStaticPagesList($parent=0, $previous='/') {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$back = array();

		$sql  = "SELECT [sscat.id],[sscat.name] FROM [table.sscat] WHERE [sscat.parent]=".(int)$parent.";";
		$db->run($sql, $res);
		if ($res->getFirst()) {
			while ($res->getNext()) {
				$id = $res->{$db->getFieldName('sscat.id')};
				$name = $res->{$db->getFieldName('sscat.name')};
				$back[] = array('link'=>$previous.'_'.$this->urlEncode($name), 'file'=>$previous.'_'.$this->urlEncode($name));
				$tmp = $this->createStaticPagesList($id, $previous.'_'.$name.'/');
				$back = array_merge($back, $tmp);
				unset($tmp);
			}
		}
		return $back;
	}

	/**
	 * Create the HTML-Source and return it
	 *
	 * @param string $method A special method-name - here we use it to switch between the complete List and just a list of news
	 * @param array $adminData If shown under Admin-Editor, this Array must be a complete DB-likeness Textentry
	 * @param array $templateTag If included in a Template, this array has keys 'layout','template','num' with equivalent values
	 * @return string
	 * @access public
	 */
	public function getSource($method="", $adminData=array(), $templateTag=array()) {
		$userCheck = pCheckUserData::getInstance();
		// Show the Message "not registered" if it's a fact
		if (!$this->registered) {
			return "The SimpleShop-Plugin is not registered on this installation.";
		}

		// Create the Content if this Plugin is registered
		if (count($adminData) > 5) {
			$_textData = $adminData;
		} else {
			$_textData = $this->_getTextEntryData();
		}
		$_template = $this->_readTemplateFile($_textData[$this->DB->FieldOnly('txt','layout')]);

		if (count($adminData) > 5) {
			$_template = str_replace("[ADMIN_FUNCTIONS]", "", $_template);
			$_template = preg_replace("/(\[TEXT_ADMIN_FUNCTIONS\])(.*?)(\[\/TEXT_ADMIN_FUNCTIONS\])/smi", "", $_template);
			$_template = preg_replace("/(\[ADMIN_REMOVE\])(.*?)(\[\/ADMIN_REMOVE\])/smi", "", $_template);
		}

		// Check for CSS-File, CSS-Content and SCRIPT-File
		if (strlen(trim($this->_cssFile)) > 0) {
			$this->_hasCssImportFile = true;
		}

		if (strlen(trim($this->_scriptFile)) > 0) {
			$this->_hasScriptImportFile = true;
		}

		if (strlen(trim($this->_cssContent)) > 0) {
			$this->_hasCssContent = true;
		}

		// Get the Content
		$_txtId = $_textData[$this->DB->FieldOnly('txt','id')];
		$_title = $_textData[$this->DB->FieldOnly('txt','title')];
		$_text  = $this->ReplaceTextVariables($_textData[$this->DB->FieldOnly('txt','text')]);

		// Indert an ID-Field into the Title-Field
		$_titleBefore = substr($_template, 0, strpos($_template, '[TITLE]')-1);
		$_titleAfter  = substr($_template, strpos($_template, '[TITLE]'));
		$_template = $_titleBefore.' id="title_'.$_txtId.'">'.$_titleAfter;

		// Replace [TITLE] or strip out the CAT_TITLE...
		if ( (strlen(trim($_title)) > 0) || ($userCheck->checkAccess('content')) ) {
			if ( (strlen($_title) <= 0) && ($userCheck->checkAccess('content'))) {
				$_title = "";
			}
			$_title = $this->_appendTitleAnchor($_title, $_textData[$this->DB->FieldOnly('txt','id')]);
			$html = str_replace("[TITLE]", $_title, $_template);
			$html = str_replace("[CAT_TITLE]", "", str_replace("[/CAT_TITLE]", "", $html));
		} else {
			$html = str_replace("[TITLE]", "", $_template);
			$html = preg_replace("/(\[CAT_TITLE\])(.*?)(\[\/CAT_TITLE\])/smi", "", $html);
		}

		// Replace [TEXT] or strip out the CAT_CONTENT...
		if ( (strlen(trim($_text)) > 0) || ($userCheck->checkAccess('content')) ) {
			$_text = $this->appendAdminTextEditAddon($_text, $_txtId, $_textData);
			$html = str_replace("[TEXT]",  $_text,  $html);
			$html = str_replace("[CAT_CONTENT]", "", str_replace("[/CAT_CONTENT]", "", $html));
		} else {
			$html = str_replace("[TEXT]",  "",  $html);
			$html = preg_replace("/(\[CAT_CONTENT\])(.*?)(\[\/CAT_CONTENT\])/smi", "", $html);
		}

		// Replace Text-Options
		$html = $this->ReplaceLayoutOptions($html, $_textData[$this->DB->FieldOnly('txt','options')]);

		//return $html;
		return "";
	}

	/**
	 * Just get the title
	 * @return string
	 */
	public function getTitle() {
		return $this->getTextEntryObject()->title;
	}

	/**
	 * Just get the Content
	 * @return string
	 */
	public function getContent() {
		return $this->getTextEntryObject()->text;
	}

	/**
	 * Create a list with all MenuID's where the current one is in
	 *
	 */
	protected function getBacktraceMenu() {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;

		$this->_btArray = array();
		$_btId = $this->_selected;

		while (true) {
			// Add the SelectedId to the Backtrace
			array_push($this->_btArray, $_btId);

			// Get the Parent-Menu
			$sql  = "SELECT [sscat.parent] FROM [table.sscat] WHERE [sscat.id]=".(int)$_btId.";";
			$db->run($sql, $res);
			if (!$res->getFirst()) {
				break;
			}

			// Get the Database-Entry
			$_btId = (int)$res->{$db->getFieldName('sscat.parent')};

			// Check for 'parent' is '0'
			if ($_btId == 0) {
				array_push($this->_btArray, $_btId);
				break;
			}
		}
		sort($this->_btArray);
	}

	/**
	 * Get the real ID from the menu if it is a LINK
	 *
	 * @param integer $id MenuID to get the real MenuID from
	 * @return integer the real MenuID
	 */
	protected function checkLinkMenu($id) {
		return $id;
	}

	/**
	 * Return all Values from a Menu
	 *
	 * @param integer $id The MenuID to get the values from
	 * @return array An Array with all needed values
	 */
	protected function getMenuValues($id) {
		$lang = pMessages::getLanguageInstance();
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$return = '';

		$sql  = "SELECT [sscat.name],[sscat.text] FROM [table.sscat] WHERE [sscat.id]=".(int)$id.";";
		$db->run($sql, $res);

		if ($res->getFirst()) {
			$return = array();
			$return[$db->getFieldName('mtx.text')]   = $res->{$db->getFieldName('sscat.text')};
			$return[$db->getFieldName('mtx.active')] = 1;
			$return[$db->getFieldName('men.short')]  = $res->{$db->getFieldName('sscat.name')};
			$return['translated'] = true;

		} else {
			$return = array($db->getFieldName('mtx.text') => 'Unknown', $db->getFieldName('mtx.active') => 1, $db->getFieldName('men.short') => 'unknown', 'translated' => false);
		}
		return $return;
	}

	/**
	 * Return a List with all Child-MenuID's this menu has
	 *
	 * @param integer $id All Childs from this MenuID
	 * @return array A list with all IDs
	 */
	protected function getMenuParentId($id) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$back = array();

		$sql  = "SELECT [sscat.id] FROM [table.sscat] WHERE [sscat.parent]=".(int)$id." ORDER BY [sscat.name];";
		$db->run($sql, $res);
		if ($res->getFirst()) {
			while ($res->getNext()) {
				array_push($back, $res->{$db->getFieldName('men.id')});
			}
		}
		return $back;
	}

	/**
	 * Return all SHOP-Categories as a Menu
	 *
	 * @return string HTML-String which identifies the Shop-Categories
	 */
	private function getShopCategories() {
		$back = '';

		// Check for valid menu_template
		if (!array_key_exists('template', $this->tagParams) || !is_file(ABS_TEMPLATE_DIR.$this->tagParams['template'])) {
			return 'Configuration failed';
		}
		if (!array_key_exists('level', $this->tagParams) || ((int)$this->tagParams['level'] < 0)) {
			$this->tagParams['level'] = 0;
		}

		$this->_template = $this->tagParams['template'];
		$this->_tplOffset = (int)$this->tagParams['level'];
		$this->_minParent = 0-(int)$this->tagParams['level'];
		$this->_maxParent = 65534;
		$this->_showAll = true;
		$this->_selected  = 0;
		$this->_hideLowerLevels = $this->_maxParent;
		$this->_showNoAdmin = true;

		// Create the Backtrace IdList
		$this->getBacktraceMenu();
		$this->parseLinkMenus();
		$this->_currentLink = $this->_selectedLinks[0].'/';

		// Check for the TemplateFile
		if ($this->ReadTemplateFile()) {
			$this->_currentLevel = 0;
			$this->createSubMenu(false);
		}
		$back = $this->_content;

		return $back;
	}

	/**
	 * Return all Products from the selected Category as HTML or whatever the template is like
	 *
	 * @return string All ShopProducts from the selected Category
	 */
	private function showShopCategoryContent() {
		$back = '';

		// Check for an ORDER-Request
		$basketSubmit = pURIParameters::get('basket', false, pURIParameters::$BOOLEAN);
		$orderId = pURIParameters::get('prod', -1, pURIParameters::$INTEGER);
		$quantity = pURIParameters::get('q', -1, pURIParameters::$INTEGER);
		if ($basketSubmit) {
			$this->saveOrderRequest(); // THE FUNCTIONS EXITS THE SCRIPT!
		} else if ($orderId >= 0) {
			$this->processOrderRequest($orderId, $quantity); // THE FUNCTIONS EXITS THE SCRIPT!
		}

		// Check for valid menu_template
		if (!array_key_exists('template', $this->tagParams) || !is_file(ABS_TEMPLATE_DIR.$this->tagParams['template'])) {
			return 'Configuration failed';
		}

		// Check for the "content"-Parameter
		if (!array_key_exists('content', $this->tagParams) || (substr_count($this->tagParams['content'], '[CONTENT]') <= 0)) {
			$this->tagParams['content'] = "<div>[CONTENT]</div>";
		}

		// Get the current Section to show all products from
		$this->parseLinkMenus();
		$currentSection = array_pop($this->_selectedLinks);
		if (preg_replace("/[^\d]+/smi", "", $currentSection) != $currentSection) {
			$currentSection = $this->getSectionIdByName($currentSection);
		}

		// Load the given Template
		$this->loadShopTemplate($this->tagParams['template']);

		// get all products
		$products = $this->getProductsFromSection($currentSection);
		foreach ($products as $product) {
			$tmp = $this->shopEntry;
			$tmp = str_replace('[NUMBER]', $product->number, $tmp);
			$tmp = str_replace('[TITLE]', $product->title, $tmp);
			$tmp = str_replace('[NAME]', $product->name, $tmp);
			$tmp = str_replace('[DESCRIPTION]', $product->description, $tmp);
			$tmp = str_replace('[PRICE]', $product->price, $tmp);
			$tmp = str_replace('[CURRENCY]', $product->currencySymbol, $tmp);
			$tmp = str_replace('[PRODUCT_ID]', $product->id, $tmp);
			$tmp = str_replace('[SECTION_ID]', $product->section, $tmp);
			$tmp = str_replace('[SECTION_NAME]', $product->section_name, $tmp);

			$tmp = preg_replace('/\[IMAGE(.*?)\]/smie', '$product->getImageTag("$1")', $tmp);

			if (substr_count($back, '[NEX_ENTRY]') > 0) {
				$back = str_replace('[NEXT_ENTRY]', $tmp);
			} else {
				$back .= $tmp;
			}
		}

		if (substr_count($this->shopBaseContent, '[ENTRIES]') > 0) {
			$back = str_replace('[ENTRIES]', $back, $this->shopBaseContent);
		}
		$back = '<script type="text/javascript" src="[DATA_DIR]plugins/simpleshop'.(JS_SRC_MODE ? '_src' : '').'.js"></script>'.$back;

		return str_replace('[CONTENT]', $back, $this->tagParams['content']);
	}

	/**
	 * Add an Order and return the whole Basket from the current User as a JSON-String
	 * !!! ATTENTION: THIS FUNCTION EXITS THE SCRIPT !!!
	 *
	 * @param integer $id The Product the User orders
	 * @param integer $quantity The Quantity
	 */
	private function processOrderRequest($id, $quantity) {
		$cookie = array_key_exists(self::COOKIENAME, $_COOKIE) ? $_COOKIE[self::COOKIENAME] : '';
		$booked = false;

		$cookieback = '';
		$back = 'while(1){};[';
		foreach (explode(';', $cookie) as $v) {
			$v = explode(",", $v);

			if (count($v) == 2) {
				if ($id == (int)$v[0]) {
					$booked = true;
				}

				$cookieback .= $this->getCOOKIEBasketProduct((int)$v[0], (int)$v[1], $id, $quantity).';';
				$back .= $this->getJSONBasketProduct((int)$v[0], (int)$v[1], $id, $quantity).',';
			}
		}

		// Add the Product if it's not already booked or remove the ";" at the end of $back and $cookieback
		if (!$booked && ((int)$id > 0)) {
			$cookieback .= $this->getCOOKIEBasketProduct($id, $quantity);
			$back .= $this->getJSONBasketProduct($id, $quantity);

		} else {
			$cookieback = substr($cookieback, 0, strlen($cookieback)-1);
		}
		$back .= ']';

		// Set the Cookie, show the JSON and exit the Script
		setcookie(self::COOKIENAME, $cookieback);
		echo $back;
		exit();
	}

	/**
	 * Save an Order trough the delightTrackingServer
	 * !!! ATTENTION: THIS FUNCTION EXITS THE SCRIPT !!!
	 *
	 */
	private function saveOrderRequest() {
		$products = pURIParameters::get('products', pURIParameters::$STRING);
		$back = 'while(1){};[';
		$xml = '';
		$track = '';
		foreach (explode(';', $products) as $v) {
			$v = explode(":", $v); // 0=>id, 1=>quantity
			if (count($v) == 2) {
				$xml .= $this->getXMLBasketProduct((int)$v[0], (int)$v[1]);
			}
		}

		// Get all settings
		$settings = $this->getShopSettings();

		// Get needed data from the Settings-DB
		$trackingURL = $settings->host;
		$username = $settings->user;
		$password = $settings->pass;
		$clientId = 'CMS: '.$settings->cid;
		$purchaserId = $settings->purchaser;
		$shopCurrency = $settings->currency;

		// Create the rest of the XML for the trackingServer
		$track .= '<?xml version="1.0" encoding="UTF-8"?>';
		$track .= '<mlxmldata version="1.0">';
		$track .= '<auth userkey="'.utf8_encode($username).'" password="'.utf8_encode($password).'" clientid="'.utf8_encode($clientId).'" />';
		$track .= '<module name="simpleshop" action="order">';
		$track .= '<order reference="'.utf8_encode('Order from Website '.$_SERVER['HTTP_HOST']).'" '.(($purchaserId != 0) ? 'purchaserid="'.$purchaserId.'"' : '').' >';
		$track .= '<customer company="'.utf8_encode(pURIParameters::get('company', false, pURIParameters::$STRING)).'"';
		$track .= ' title="'.utf8_encode(pURIParameters::get('title', false, pURIParameters::$STRING)).'"';
		$track .= ' surname="'.utf8_encode(pURIParameters::get('surname', false, pURIParameters::$STRING)).'"';
		$track .= ' lastname="'.utf8_encode(pURIParameters::get('lastname', false, pURIParameters::$STRING)).'"';
		$track .= ' address="'.utf8_encode(pURIParameters::get('address', false, pURIParameters::$STRING)).'"';
		$track .= ' postalcode="'.utf8_encode(pURIParameters::get('postalcode', false, pURIParameters::$STRING)).'"';
		$track .= ' city="'.utf8_encode(pURIParameters::get('city', false, pURIParameters::$STRING)).'"';
		$track .= ' email="'.utf8_encode(pURIParameters::get('email', false, pURIParameters::$STRING)).'"';
		$track .= ' tel="'.utf8_encode(pURIParameters::get('telephone', false, pURIParameters::$STRING)).'"';
		$track .= ' mobile="'.utf8_encode(pURIParameters::get('mobile', false, pURIParameters::$STRING)).'"';
		$track .= ' fax="'.utf8_encode(pURIParameters::get('fax', false, pURIParameters::$STRING)).'"';
		$track .= ' country="'.utf8_encode(pURIParameters::get('country', false, pURIParameters::$STRING)).'" />';
		$track .= '<comment><![CDATA['.utf8_encode(pURIParameters::get('comment', false, pURIParameters::$STRING)).']]></comment>';
		$track .= '<positions currency="'.utf8_encode($shopCurrency).'">';
		$track .= $xml;
		$track .= '</positions></order></module></mlxmldata>';

		// Send the XML to the TrackingServer
		$post = new pHTTPPost($trackingURL);
		if ($post->send($track)) {
			$response = $post->getResponseText();
			unset($post);
			$back .= $this->parseTrackingResponse($response);

		} else {
			$back .= '{"error":true,"state":"fatal","errormessage":"Unable to connect the delight tracking server: '.$post->getError().'"}';
		}

		// Set the Cookie, show the JSON and exit the Script
		setcookie(self::COOKIENAME, '');
		$back .= ']';
		echo $back;
		exit();
	}

	/**
	 * Load all Settings from the Shop
	 *
	 * @return stdClass List with all needed settings
	 * @access private
	 */
	private function getShopSettings() {
		$settings = new stdClass();
		$settings->user = '';
		$settings->pass = '';
		$settings->cid = '';
		$settings->host = '';
		$settings->currency = '';
		$settings->purchaser = 0;
		$category = 0;

		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = "SELECT * FROM [table.sssettings] WHERE [sssettings.category]=".(int)$category." ORDER BY [sssettings.name] ASC;";
		$db->run($sql, $res);
		if ($res->getFirst()) {
			while ($res->getNext()) {
				$settings->{$res->{$db->getFieldName('sssettings.name')}} = $res->{$db->getFieldName('sssettings.value')};
			}
		}

		if (!empty($settings->host) && (substr($settings->host, 0, 7) != 'http://')) {
			$settings->host = 'http://'.$settings->host;
		}
		if (!empty($settings->host) && (substr($settings->host, -9) != 'track.php')) {
			$settings->host = $settings->host.'/track.php';
		}

		return $settings;
	}

	/**
	 * Try to load the Response as an XML and parse it
	 *
	 * @param string $data Tracking-Server-Response
	 * @return JSON-String
	 * @access private
	 */
	private function parseTrackingResponse($data) {
		$back = '';
		try {
			$dom = @DOMDocument::loadXML($data);
			if (!$dom) {
				throw new Exception('Unable to load tracking server response: '.$data, 99);
			}
			$order = $dom->getElementsByTagName('order');
			if ($order->length > 0) {
				$order = $order->item(0);
				if ($order->hasAttribute('submitted') && ($order->getAttribute('submitted') == 'true')) {
					$back = '{"error":false,"state":"submitted","number":'.$order->getAttribute('number').'}';
				} else {
					$err = $order->getElementsByTagName('error')->item(0);
					$back = '{"error":true,"state":"failed","errormessage":"'.$err->textContent.'"}';
				}

			} else {
				$err = $dom->getElementsByTagName('error')->item(0);
				$back = '{"error":true,"state":"error","errormessage":"'.$err->textContent.'"}';
			}

		} catch (Exception $e) {
			$back = '{"error":true,"state":"error","errormessage":"'.$e->getMessage().'"}';
		}
		return $back;
	}

	/**
	 * Get a Product as a JSON-Object for the Basket
	 *
	 * @param integer $id The ProductID
	 * @param integer $quantity Quantity fpr $id
	 * @param integer $sid The ProductID which was added just now
	 * @param integer $squantity Quantity for $sid
	 * @return String JSON-Conorm Product for the Basket
	 */
	private function getJSONBasketProduct($id, $quantity, $sid=0, $squantity=0) {
		$quantity += ($id == $sid) ? $squantity : 0;
		if ($quantity > self::MAX_QUANTITY) {
			$quantity = self::MAX_QUANTITY;
		}
		if ($quantity < 0) {
			$quantity = 0;
		}
		$product = $this->getShopProductFromDB($id, $quantity);
		if (!$product->error) {
			return '{"num":'.$product->id.',"name":"'.htmlentities($product->name).'","title":"'.htmlentities($product->title).'","quantity":'.$product->quantity.',"price":'.$product->price.',"currency":"'.$product->currency.'"}';
		} else {
			return '{"num":0,"name":"not Found","title":"Not Found","quantity":0,"price":0,"currency":""}';
		}
	}

	/**
	 * Get a Product as a JSON-Object for the Basket
	 *
	 * @param integer $id The ProductID
	 * @param integer $quantity Quantity fpr $id
	 * @return String JSON-Conorm Product for the Basket
	 */
	private function getXMLBasketProduct($id, $quantity) {
		if ($quantity > self::MAX_QUANTITY) {
			$quantity = self::MAX_QUANTITY;
		}
		if ($quantity < 0) {
			$quantity = 0;
		}
		$product = $this->getShopProductFromDB($id, $quantity);
		$xml = '';
		if (!$product->error) {
			$xml = '<position number="'.utf8_encode($product->number).'" name="'.utf8_encode($product->name).'" title="'.utf8_encode($product->title).'" quantity="'.utf8_encode($product->quantity).'" price="'.utf8_encode($product->price).'">';
			// TODO: Add some Parameters if there are som in future releases
			$xml .= '</position>';
		}
		return $xml;
	}

	/**
	 * Get a Product
	 *
	 * @param integer $id The ProductID
	 * @param integer $quantity The Qunatity for this Product
	 * @return stdClass A ProductObject
	 */
	private function getShopProductFromDB($id, $quantity) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$sql = 'SELECT * FROM [table.ssproducts] WHERE [ssproducts.id]='.(int)$id.';';
		$db->run($sql, $res);

		if ($quantity > self::MAX_QUANTITY) {
			$quantity = self::MAX_QUANTITY;
		}
		if ($quantity < 0) {
			$quantity = 0;
		}

		$product = new stdClass();
		if ($res->getFirst()) {
			$product->error = false;
			$product->id = (int)$res->{$db->getFieldName('ssproducts.id')};
			$product->number = $res->{$db->getFieldName('ssproducts.number')};
			$product->quantity = (int)$quantity;
			$product->name = $res->{$db->getFieldName('ssproducts.name')};
			$product->title = $res->{$db->getFieldName('ssproducts.title')};
			$product->price = (double)$res->{$db->getFieldName('ssproducts.price')};
			$product->currency = 'CHF';//$res->{$db->getFieldName('ssproducts.currency')};  // TODO: Change to the real currency in future releases
		} else {
			$product->error = true;
		}
		return $product;
	}

	/**
	 * Get a Product for storing in a Cookie. The Format is ID,QUANTITY
	 *
	 * @param integer $id The ProductID
	 * @param integer $quantity Quantity fpr $id
	 * @param integer $sid The ProductID which was added just now
	 * @param integer $squantity Quantity for $sid
	 * @return String CookieString
	 */
	private function getCOOKIEBasketProduct($id, $quantity, $sid=0, $squantity=0) {
		if ($id == $sid) {
			$quantity += $squantity;
		}
		if ($quantity > self::MAX_QUANTITY) {
			$quantity = self::MAX_QUANTITY;
		}
		if ($quantity < 0) {
			$quantity = 0;
		}

		return $id.','.$quantity;
	}

	/**
	 * Load and parse a SHOP-Templatefile
	 *
	 * @param string $tpl A File in ABS_TEMPLATE_DIR which is a Shop-Templatefile
	 */
	private function loadShopTemplate($tpl) {
		if (is_file(ABS_TEMPLATE_DIR.$tpl)) {
			$this->shopTemplate = file_get_contents(ABS_TEMPLATE_DIR.$tpl);
		} else {
			$this->shopTemplate = '';
		}

		$match = array();
		if (preg_match("/(\[SIMPLESHOP_BASE\])(.*?)(\[\/SIMPLESHOP_BASE\])/smi", $this->shopTemplate, $match) && (count($match) > 0)) {
			$this->shopBaseContent = $match[2];
		}

		$match = array();
		if (preg_match("/(\[SIMPLESHOP_ENTRY\])(.*?)(\[\/SIMPLESHOP_ENTRY\])/smi", $this->shopTemplate, $match) && (count($match) > 0)) {
			$this->shopEntry = $match[2];
		}
	}

	/**
	 * Get the SectionId by the name of the Section
	 *
	 * @param string $section Name of the Section to get the ID from
	 * @return integer The SectionId
	 */
	private function getSectionIdByName($section) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$values = array('�', '�', '�', '�', '�', '�', ' ');
		$replace = array('ae','oe','ue','Ae','Oe','Ue', '%20');
		$section = str_replace($replace, $values, $section);
		$sql = "SELECT [sscat.id] FROM [table.sscat] WHERE [sscat.name]='".$section."';";
		$db->run($sql, $res);
		if ($res->getFirst()) {
			return (int)$res->{$db->getFieldName('sscat.id')};
		}
		return 0;
	}

	/**
	 * Load all Products from a given Section and return a list of pSimpleShopProduct-Objects
	 *
	 * @param integer $section The Section to get the Products from
	 * @return array List with pSimpleShopProduct-Objects
	 */
	private function getProductsFromSection($section) {
		$db = pDatabaseConnection::getDatabaseInstance();
		$res = null;
		$products = array();

		// Check for an OrderBy Parameter
		$order = 'ORDER BY ';
		switch (pURIParameters::get('order', 0, pURIParameters::$INTEGER)) {
			case 1:
				$order .= '[ssproducts.price],[ssproducts.name]';
				break;

			case 0:
			default:
				$order .= '[ssproducts.name],[ssproducts.price]';
				break;
		}
		if (pURIParameters::get('sort', 0, pURIParameters::$INTEGER) == 0) {
			$order .= 'ASC';
		} else {
			$order .= 'DESC';
		}

		// Get all Products in order
		$sql = "SELECT [ssproducts.id] FROM [table.ssproducts] WHERE [ssproducts.section]=".(int)$section." ".$order.";";
		$db->run($sql, $res);

		if ($res->getFirst()) {
			while ($res->getNext()) {
				$p = new pSimpleShopProduct();
				$p->load($res->{$db->getFieldName('ssproducts.id')});
				$products[] = $p;
			}
		}
		return $products;
	}

	/**
		 * Check Database integrity
		 *
		 * This function creates all required Tables, Updates, Inserts and Deletes.
		 */
	function _checkDatabase() {
		$db = pDatabaseConnection::getDatabaseInstance();

		// Get the current Version
		$v = $this->_checkMainDatabase();
		$version = $v[0];
		$versionid = $v[1];

		// Updates to the Database
		if ($version < 2008042500) {
			$sql  = "CREATE TABLE IF NOT EXISTS [table.ssproducts] (".
			" [field.ssproducts.id] INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,".
			" [field.ssproducts.section] INT(10) UNSIGNED NOT NULL DEFAULT 0,".
			" [field.ssproducts.name] VARCHAR(100) NOT NULL DEFAULT '',".
			" [field.ssproducts.title] VARCHAR(100) NOT NULL DEFAULT '',".
			" [field.ssproducts.descr] TEXT NOT NULL DEFAULT '',".
			" [field.ssproducts.price] INT(10) UNSIGNED NOT NULL DEFAULT 0,".
			" [field.ssproducts.currency] INT(10) UNSIGNED NOT NULL DEFAULT 0,".
			" PRIMARY KEY ([field.ssproducts.id]),".
			" UNIQUE KEY id ([field.ssproducts.id])".
			" );";
			$res = null;
			$db->run($sql, $res);

			$sql  = "CREATE TABLE IF NOT EXISTS [table.sscat] (".
			" [field.sscat.id] INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,".
			" [field.sscat.parent] INT(10) UNSIGNED NOT NULL DEFAULT 0,".
			" [field.sscat.name] VARCHAR(100) NOT NULL DEFAULT '',".
			" PRIMARY KEY ([field.sscat.id]),".
			" UNIQUE KEY id ([field.sscat.id])".
			" );";
			$res = null;
			$db->run($sql, $res);

			$sql  = "INSERT INTO [table.sscat] ([field.sscat.parent],[field.sscat.name]) VALUES (0,'default');".
			$res = null;
			$db->run($sql, $res);

			$sql  = "CREATE TABLE IF NOT EXISTS [table.ssparams] (".
			" [field.ssparams.product] INT(10) UNSIGNED NOT NULL DEFAULT 0,".
			" [field.ssparams.name] VARCHAR(10) NOT NULL DEFAULT '',".
			" [field.ssparams.value] VARCHAR(255) NOT NULL DEFAULT '',".
			" PRIMARY KEY ([field.ssparams.product]),".
			" KEY id ([field.ssparams.name])".
			" );";
			$res = null;
			$db->run($sql, $res);

			$sql  = "CREATE TABLE IF NOT EXISTS [table.sssettings] (".
			" [field.sssettings.category] INT(10) UNSIGNED NOT NULL DEFAULT 0,".
			" [field.sssettings.name] VARCHAR(10) NOT NULL DEFAULT '',".
			" [field.sssettings.value] VARCHAR(255) NOT NULL DEFAULT '',".
			" PRIMARY KEY ([field.sssettings.category]),".
			" KEY id ([field.sssettings.name])".
			" );";
			$res = null;
			$db->run($sql, $res);

			$sql  = "CREATE TABLE IF NOT EXISTS [table.ssorders] (".
			" [field.ssorders.id] INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,".
			" [field.ssorders.number] VARCHAR(20) NOT NULL DEFAULT '',".
			" [field.ssorders.user] INT(10) UNSIGNED NOT NULL DEFAULT 0,".
			" [field.ssorders.surname] VARCHAR(100) NOT NULL DEFAULT '',".
			" [field.ssorders.lastname] VARCHAR(100) NOT NULL DEFAULT '',".
			" [field.ssorders.address] VARCHAR(100) NOT NULL DEFAULT '',".
			" [field.ssorders.postal] VARCHAR(15) NOT NULL DEFAULT '',".
			" [field.ssorders.city] VARCHAR(50) NOT NULL DEFAULT '',".
			" [field.ssorders.country] VARCHAR(30) NOT NULL DEFAULT '',".
			" [field.ssorders.email] VARCHAR(100) NOT NULL DEFAULT '',".
			" [field.ssorders.tel] VARCHAR(20) NOT NULL DEFAULT '',".
			" [field.ssorders.comment] TEXT NOT NULL DEFAULT '',".
			" [field.ssorders.date] DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',".
			" [field.ssorders.fetch] DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',".
			" PRIMARY KEY ([field.ssorders.id]),".
			" UNIQUE KEY id ([field.ssorders.id])".
			" );";
			$res = null;
			$db->run($sql, $res);

			$sql  = "CREATE TABLE IF NOT EXISTS [table.ssoproducts] (".
			" [field.ssoproducts.order] INT(10) UNSIGNED NOT NULL DEFAULT 0,".
			" [field.ssoproducts.number] VARCHAR(20) NOT NULL DEFAULT '',".
			" [field.ssoproducts.name] VARCHAR(100) NOT NULL DEFAULT '',".
			" [field.ssoproducts.title] VARCHAR(100) NOT NULL DEFAULT '',".
			" [field.ssoproducts.descr] TEXT NOT NULL DEFAULT '',".
			" [field.ssoproducts.amount] INT(10) UNSIGNED NOT NULL DEFAULT 0,".
			" [field.ssoproducts.price] INT(10) UNSIGNED NOT NULL DEFAULT 0,".
			" [field.ssoproducts.currency] VARCHAR(10) NOT NULL DEFAULT 'sFr.',".
			" PRIMARY KEY ([field.ssoproducts.order])".
			" );";
			$res = null;
			$db->run($sql, $res);
		}

		if ($version < 2008062000) {
			$sql = "ALTER TABLE [table.ssproducts] ADD ([field.ssproducts.number] VARCHAR(150) DEFAULT '');";
			$res = null;
			$db->run($sql, $res);
		}

		if ($version < 2008092502) {
			$sql  = "DROP TABLE [table.sssettings];";
			$res = null;
			$db->run($sql, $res);

			$sql  = "CREATE TABLE IF NOT EXISTS [table.sssettings] (".
			" [field.sssettings.category] INT(10) UNSIGNED NOT NULL DEFAULT 0,".
			" [field.sssettings.name] VARCHAR(10) NOT NULL DEFAULT '',".
			" [field.sssettings.value] VARCHAR(255) NOT NULL DEFAULT '',".
			" KEY [field.sssettings.category] ([field.sssettings.category]),".
			" KEY id ([field.sssettings.name])".
			" );";
			$res = null;
			$db->run($sql, $res);
		}

		// Update the version
		$this->_updateVersionTable($version, $versionid, self::VERSION);
	}

}

?>