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
	$DBTables['per'] = "dhp_users";                 // registered Users                   OK
	$DBTables['plo'] = "dhp_loguser";               // Loging for Users                   OK
// 	$DBTables['pla'] = "dhp_userslang";             // Languages for Users (access)
// 	$DBTables['pdo'] = "dhp_userdownloadsections";  // DownlaodSections for Users
//	$DBTables['lan'] = "dhp_languages";             // Languages                          OK
// 	$DBTables['cou'] = "dhp_country";               // List of countries
	$DBTables['men'] = "dhp_menu";                  // MenuStructure                      OK
	$DBTables['mtx'] = "dhp_menutexts";             // Menutexts for MenuStructure        OK
	$DBTables['txt'] = "dhp_texts";                 // Any Texts                          OK
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

	// Secure Menus and others
	$DBTables['grp'] = "dhp_access_groups";         // Groups a User can be in - Groups can be attached to MEnus, Downloads, etc.
	$DBTables['usrgrp'] = "dhp_users_groups";       // Users <-> Groups assignment
	$DBTables['menugrp'] = "dhp_menuaccess_groups"; // Menu <-> Groups assignment

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

	// Static Menu-Structure
	$DBTables['staticmenu'] = 'dhp_menu_static';  // The complete Menustructure created for static pages/links

// Tablefields
	// Language-Table
/*
	$DBFields['lan']['id']     = "id";
	$DBFields['lan']['text']   = "text";
	$DBFields['lan']['short']  = "short";
	$DBFields['lan']['char']   = "charset";
	$DBFields['lan']['icon']   = "icon_path";
	$DBFields['lan']['active'] = "lang_is_active";
*/

	// Menu
	$DBFields['men'] = array(
		'id' => 'id',
		'parent' => 'parent',
		'pos' => 'show_order',
		'link' => 'link',
		'short' => 'short_link',
		'isform' => 'is_formular_page'
	);

	// Menu-Texts
	$DBFields['mtx'] = array(
		'id' => 'id',
		'text' => 'text',
		'menu' => 'menu_id',
		'lang' => 'lang_id',
		'active' => 'is_active',
		'title' => 'site_title',
		'description' => 'site_description',
		'keywords' => 'site_keywords',
		'image' => 'image_id',
		'transshort' => 'translated_shortlink'
	);

	$DBFields['staticmenu'] = array(
		'menu' => 'menu_id',
		'id' => 'menu_id',
		'short' => 'short_link',
		'translated' => 'translated_short_link',
		'lang' => 'lang_id',
	);

// 	// Link tyeps and Files
// 	$DBFields['lnk']['id']   = "id";
// 	$DBFields['lnk']['text'] = "text";
// 	$DBFields['lnk']['file'] = "link";

	// Texts
	$DBFields['txt']['id']      = "id";
	$DBFields['txt']['layout']  = "layout_file";
	$DBFields['txt']['sort']    = "textorder";
	$DBFields['txt']['text']    = "text";
	$DBFields['txt']['title']   = "short";
 	$DBFields['txt']['menu']    = "menu_id";
	$DBFields['txt']['lang']    = "lang_id";
	$DBFields['txt']['plugin']  = "text_parser";
	$DBFields['txt']['options'] = "layout_options";
	$DBFields['txt']['grouped'] = "grouped_text";

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

	// Users table
	$DBFields['per']['id']         = "id";
	$DBFields['per']['right']      = "login_type";
	$DBFields['per']['user']       = "login_name";
	$DBFields['per']['passwd']     = "login_password";
	$DBFields['per']['clear']      = "login_clear_password";
	$DBFields['per']['company']    = "user_company";
	$DBFields['per']['name']       = "user_name";
	$DBFields['per']['surname']    = "user_surname";
	$DBFields['per']['address']    = "user_address";
	$DBFields['per']['postalcode'] = "user_postalcode";
	$DBFields['per']['city']       = "user_city";
	$DBFields['per']['country']    = "user_country";
	$DBFields['per']['email']      = "user_email";
	$DBFields['per']['internet']   = "user_internet";
	$DBFields['per']['info']       = "user_info";

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

	// Login/Logout loging
	$DBFields['plo']['id']     = "id";
	$DBFields['plo']['user']   = "user";
	$DBFields['plo']['action'] = "action";
	$DBFields['plo']['time']   = "action_time";
	$DBFields['plo']['ip']     = "client_ip";
	$DBFields['plo']['domain'] = "client_domain";
	$DBFields['plo']['info']   = "action_info";

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

	// User-Groups
	$DBFields['grp'] = array(
		'id' => 'id',
		'name' => 'group_name',
		'descr' => 'description'
	);
	$DBFields['usrgrp'] = array(
		'group' => 'group_id',
		'user' => 'user_id'
	);
	$DBFields['menugrp'] = array(
		'menu' => 'menu_id',
		'group' => 'group_id'
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
