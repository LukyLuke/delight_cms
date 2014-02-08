<?php
/**
 * The Configuration-File for users
 */

define("DB_USER",     "delight_cms");
define("DB_PASSWORD", "delight_cms_pass");
define("DB_DATABASE", "delight_cms");
define("DB_HOST",     "localhost");

// Small-CMS installation
define('CMS_SMALL', false);
//define('CMS_SMALL_NUM_MENU', 5); // MENU::MAX_SMALL_MENU_ENTRIES=10 if not defined

// FF AntiClickJacking
define('ANTI_CLICKJACKING', false);

// name, Description and keywords
define("DHP_NAME",              "delight software gmbh - delight cms");
define("DHP_DESCRIPTION",       "delight software gmbh - delight cms");
define("DHP_KEYWORDS",          "delight software gmbh - delight cms");
define("PIWIK_SITE_ID",         "9999");
define("DATA_DIR",              MAIN_DIR.'data/');
define("ABS_DATA_DIR",          realpath(dirname($_SERVER['SCRIPT_FILENAME'])).DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR);
define("ABS_FTP_UPLOAD",        realpath(dirname($_SERVER['SCRIPT_FILENAME']).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'userdata'.DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR);

// If true, the _src version of Javascript-Files is used, otherwise the compressed on
define("JS_SRC_MODE", true);

// Define all additional Plugins this user has access to (Non-Standard-Plugins)
define('DWP_PLUGIN_ACCESS_GRANTED', 'SEARCH');

// Which Template to use
define("TEMPLATE_DIR",          MAIN_DIR.'template/');

// Absolute Template-Dirs
define("ABS_TEMPLATE_DIR",       realpath(realpath(dirname($_SERVER['SCRIPT_FILENAME'])).DIRECTORY_SEPARATOR.'..'.TEMPLATE_DIR).DIRECTORY_SEPARATOR);
define("ABS_ADMIN_TEMPLATE_DIR", realpath(realpath(dirname($_SERVER['SCRIPT_FILENAME'])).DIRECTORY_SEPARATOR.'..'.MAIN_DIR.'template'.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR);

// Where to store static files
define("ABS_STATIC_DIR",        ABS_ADMIN_TEMPLATE_DIR.'..'.DIRECTORY_SEPARATOR.'static'.DIRECTORY_SEPARATOR);

// Admin-Stuff
define("SESSION_NAME",          "ddelight_cms");
define("MAIN_DIRECTORY",        MAIN_DIR);
define("LAYOUT_PREVIEW_TEXT",   ABS_TEMPLATE_DIR."txtPreview.txt");
define("PRIVACY_POLICY_LINK",   "<strong class=\"privacyPolicy\" onclick=\"window.open('".constant("MAIN_DIRECTORY")."/privacy_policy.php?lan=[LANGUAGE]','privacypolicy','width=350px,height=450px,locationbar=no,statusbar=no,menubar=no,toolbar=no,resizable=yes');\">[LINK_TEXT]</strong>");
define("NEWS_DATE_FORMAT",      "l, j. F Y, H:i:s");
define("MASTER_LANGUAGE",       "de");

// Text-Images
define('IMAGE_BORDER_WIDTH', 0);
define('IMAGE_BORDER_SPACE', 0);
define('IMAGE_BORDER_COLOR', '000000');
define('IMAGE_BACKGROUND_COLOR', 'FFFFFF');
define('IMAGE_TEXT_COLOR', '000000');

// Screenshot-Definitions
define("SCREENSHOT_FLASH",      TEMPLATE_DIR."images/StandardImages/FlashAnimation_Logo.png");
define("SCREENSHOT_WIDTH",      150);
define("SCREENSHOT_HEIGHT",     170);
define("SCREENSHOT_WIDTH_MAX",  1050);
define("SCREENSHOT_HEIGHT_MAX", 852);
define("SCREENSHOT_BACKGROUND", "255,255,255");  // "RED,BLUE,GREEN" for screenshot-background
define("SCREENSHOTS_USE_GD",    true);           // true if the GD-2 functions should be used to create the thumbnails (in otherway, "ImageMagick" is used)
define("SCREENSHOTS_USE_BG",    false);          // true if the small Image should in center of a HEIGHTxWIDTH image with BACKGROUND as BgColor

// Menu-Definitions
define('MAX_MAIN_MENU_ENTRIES', 10);
define('MAX_LINK_LEVEL', 5);

// should only the Static-Page be visible in Live-Version [true / false]
define("SHOW_ONLY_STATIC", true);

// Set the Referer-Blacklist (this domains will not be logged as referer)
define('REFERER_BLACKLIST', $_SERVER['SERVER_NAME']);

?>
