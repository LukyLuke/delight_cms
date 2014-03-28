<?php
/*  $dbconf = file("./.htDBConf");
// Databases
	$DBSettings['master']['host']     = trim($dbconf[0]);
	$DBSettings['master']['user']     = trim($dbconf[1]);
	$DBSettings['master']['password'] = trim($dbconf[2]);
	$DBSettings['master']['database'] = trim($dbconf[3]);
*/

// Databases
	$DBSettings['master']['host']     = DB_HOST;
	$DBSettings['master']['user']     = DB_USER;
	$DBSettings['master']['password'] = DB_PASSWORD;
	$DBSettings['master']['database'] = DB_DATABASE;

// Tables
// 	$DBTables['pla'] = "dhp_userslang";             // Languages for Users (access)
// 	$DBTables['pdo'] = "dhp_userdownloadsections";  // DownlaodSections for Users
// 	$DBTables['cou'] = "dhp_country";               // List of countries

	$DBTables['img'] = "dhp_images";                // All Images                         OK
	$DBTables['ims'] = "dhp_imagesections";         // Image Sections (like a Tree)       OK
	$DBTables['imt'] = "dhp_imagestexts";           // Image-Text (in dif. languages)     OK
	$DBTables['prg'] = "dhp_programms";             // All Programms                      OK
	$DBTables['prt'] = "dhp_programmstexts";        // Program-Texts (in dif. Languages)  OK
	$DBTables['prs'] = "dhp_programmssections";     // Program-Sections (like a tree)     OK
// 	$DBTables['pru'] = "dhp_programmstypes";        // Program- / Section- Types
	$DBTables['dll'] = "dhp_download";              // Download-Log                       OK
	$DBTables['mir'] = "dhp_mirror";                // All Download-Mirrors               OK
	$DBTables['ref'] = "dhp_referer";               // Referer-log                        OK
	$DBTables['opt'] = "dhp_versions";              // Just for store div. versions       OK

	// News and RSS-Feeds
	$DBTables['new'] = "dhp_news";                  // News-Texts
	$DBTables['nes'] = "dhp_newssections";          // News Sections
	$DBTables['rsscache'] = "dhp_news_rsscache";    // Cache for RSS-Newsfeeds from external sites
	$DBTables['rssnews'] = "dhp_news_rssnews";      // List with all RSS-Feeds to grab from external sites and cache in "rsscache"

	// SimpleShop
	$DBTables['ssproducts']  = "dhp_simpleshop_products";      // All Products inside the SimpleShop
	$DBTables['sscat']       = "dhp_simpleshop_categories";    // Categories for the SimpleShop to categorize the products
	$DBTables['ssparams']    = "dhp_simpleshop_parameters";    // Userdefined Parameters on Products and/or Categories
	$DBTables['sssettings']  = "dhp_simpleshop_settings";      // Global Settings for the Shop
	$DBTables['ssorders']    = "dhp_simpleshop_orders";        // All Orders including the complete Address of the buyer
	$DBTables['ssoproducts'] = "dhp_simpleshop_orderproducts"; // All Products ordered by the given Order
	$DBTables['sscurrency']  = "dhp_simpleshop_currencylist";  // All available currencies

	// Search-Function
	$DBTables['srchtxt']  = "dhp_search_text";   // All texts to search in
	$DBTables['srchidx']  = "dhp_search_index";  // Index to speedup the search-process

	// Formulars
	$DBTables['formular'] = "dhp_formulars";  // All Formular-Configurations-Values

// Tablefields

// 	// Link tyeps and Files
// 	$DBFields['lnk']['id']   = "id";
// 	$DBFields['lnk']['text'] = "text";
// 	$DBFields['lnk']['file'] = "link";

	// Image Table
	$DBFields['img']['id']      = "id";
	$DBFields['img']['image']   = "image";
	$DBFields['img']['section'] = "section";
	$DBFields['img']['date']    = "uploaded";
	$DBFields['img']['name']    = "original_name";
	$DBFields['img']['order']  = "order_position";

	// Image Texts
	$DBFields['imt']['id']    = "id";
	$DBFields['imt']['image'] = "picture";
	$DBFields['imt']['title'] = "desc_short";
	$DBFields['imt']['text']  = "desc_long";
	$DBFields['imt']['html']  = "desc_html";
	$DBFields['imt']['lang']  = "language";

	// Image Sections
	$DBFields['ims']['id']     = "id";
	$DBFields['ims']['parent'] = "parent";
	$DBFields['ims']['text']   = "text";

	// Programs Table
	$DBFields['prg']['id']       = "id";
	$DBFields['prg']['program']  = "program";
	$DBFields['prg']['name']     = "filename";
	$DBFields['prg']['section']  = "section";
	$DBFields['prg']['local']    = "local_download";
	$DBFields['prg']['register'] = "show_register";
	$DBFields['prg']['secure']   = "registered_user_only";
	$DBFields['prg']['mime']     = "real_mime_type";
	$DBFields['prg']['order']    = "order_position";

	// Programs Texts
	$DBFields['prt']['id']      = "id";
	$DBFields['prt']['program'] = "program";
	$DBFields['prt']['title']   = "desc_short";
	$DBFields['prt']['text']    = "desc_long";
	$DBFields['prt']['html']    = "desc_html";
	$DBFields['prt']['lang']    = "language";

	// Programs Sections
	$DBFields['prs']['id']     = "id";
	$DBFields['prs']['parent'] = "parent";
	$DBFields['prs']['text']   = "text";
	$DBFields['prs']['secure'] = "is_secure";

	// DownloadLog
	$DBFields['dll']['id']        = "id";
	$DBFields['dll']['file']      = "file";
	$DBFields['dll']['real']      = "file_real";
	$DBFields['dll']['fileid']    = "file_id";
	$DBFields['dll']['size']      = "file_size";
	$DBFields['dll']['section']   = "section_id";
	$DBFields['dll']['ip']        = "client_ip";
	$DBFields['dll']['domain']    = "client_domain";
	$DBFields['dll']['browser']   = "client_browser";
	$DBFields['dll']['time']      = "click_time";
	$DBFields['dll']['lang']      = "site_language";
	$DBFields['dll']['user']      = "user_id";

	// Mirrors
	$DBFields['mir']['id']      = "id";
	$DBFields['mir']['program'] = "program";
	$DBFields['mir']['url']     = "url";
	$DBFields['mir']['user']    = "login_user";
	$DBFields['mir']['passwd']  = "login_passwd";
	$DBFields['mir']['update']  = "last_update";
	$DBFields['mir']['active']  = "is_active";

	// Referer-Log
	$DBFields['ref']['id']       = "id";
	$DBFields['ref']['local']    = "url_called";
	$DBFields['ref']['protocol'] = "ref_protocol";
	$DBFields['ref']['domain']   = "ref_domain";
	$DBFields['ref']['url']      = "ref_url";
	$DBFields['ref']['param']    = "ref_parameter";
	$DBFields['ref']['date']     = "ref_date";
	$DBFields['ref']['client']   = "client_ip";
	$DBFields['ref']['browser']  = "client_browser";

	// Versions-Table
	$DBFields['opt']['id']      = "id";
	$DBFields['opt']['name']    = "name";
	$DBFields['opt']['version'] = "version";
	$DBFields['opt']['lastmod'] = "last_update";

	// News Table
	$DBFields['new'] = array(
		'id' => 'id',
		'section' => 'news',
		'title' => 'desc_short',
		'text' => 'desc_long',
		'html' => 'desc_html',
		'date' => 'news_date',
		'lang' => 'language',
		'rss' => 'rss_feed_news',
		'short' => 'short_message'
	);
	$DBFields['nes'] = array(
		'id' => 'id',
		'parent' => 'parent',
		'text' => 'text'
	);
	$DBFields['rssnews'] = array(
		'id' => 'id',
		'uri' => 'rss_uri',
		'last' => 'last_fetch'
	);
	$DBFields['rsscache'] = array(
		'rssnews' => 'rssnews_id',
		'date' => 'news_date',
		'title' => 'feed_title',
		'text' => 'feed_text',
		'uid' => 'feed_uid'
	);

	// SimpleShop Table (Products)
	$DBFields['ssproducts'] = array(
		'id' => 'id',
		'section' => 'producs_category',
		'name' => 'name',
		'number' => 'number',
		'title' => 'title',
		'descr' => 'description',
		'price' => 'price',
		'currency' => 'currency_id'
	);
	$DBFields['sscat'] = array(
		'id' => 'id',
		'parent' => 'parent',
		'name' => 'category_name',
		'text' => 'category_name'
	);
	$DBFields['ssparams'] = array(
		'product' => 'product_id',
		'name' => 'param_name',
		'value' => 'param_value'
	);
	$DBFields['sssettings'] = array(
		'name' => 'param_name',
		'value' => 'param_value',
		'category' => 'category_id'
	);
	$DBFields['ssorders'] = array(
		'id' => 'id',
		'number' => 'order_number',
		'user' => 'user_id',
		'surname' => 'buyer_surname',
		'lastname' => 'buyer_lastname',
		'address' => 'buyer_address',
		'postal' => 'buyer_postal',
		'city' => 'buyer_city',
		'country' => 'buyer_country',
		'email' => 'buyer_email',
		'tel' => 'buyer_telephone',
		'comment' => 'buyer_comment',
		'date' => 'order_date',
		'fetch' => 'order_last_fetch'
	);
	$DBFields['ssoproducts'] = array(
		'order' => 'order_id',
		'number' => 'product_number',
		'name' => 'product_name',
		'title' => 'product_title',
		'descr' => 'product_description',
		'amount' => 'amount',
		'price' => 'unit_price',
		'currency' => 'currency_name'
	);

	// Search Table
	$DBFields['srchtxt'] = array(
		'id' => 'id',
		'text' => 'text_content',
		'title' => 'text_title',
		'lang' => 'language_id',
		'link' => 'direct_link'
	);
	$DBFields['srchidx'] = array(
		'srchid' => 'search_id',
		'value' => 'search_value',
		'pithiness' => 'pithiness'
	);

	// Formulars
	$DBFields['formular'] = array(
		'textid' => 'text_id',
		'field' => 'field_name',
		'value' => 'field_value',
		'plugin' => 'plugin'
	);

	//$DBFields[''][''] = "";

?>
