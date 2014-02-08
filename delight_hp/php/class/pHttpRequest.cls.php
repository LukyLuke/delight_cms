<?php
/** $id$
 * Class to get Informations about the HTTP-Request
 *
 * @author Lukas Zurschmiede <l.zurschmiede@delightsoftware.com>
 * @copyright (c) 2008 by delight software gmbh
 *
 * @package delightWebProduct
 * @version 1.0
 */

class pHttpRequest {

	private static $rawPost;

	/**
	 * Initialization
	 */
	public function __construct() {
		self::$rawPost = self::getRequestData();
	}

	/**
	 * Return the HTTP-RawPost Data
	 *
	 * @return string The RawPostData
	 */
	public static function getRequestData($binary=false) {
		if (!empty(self::$rawPost)) {
			return self::$rawPost;
		}
		if (isset($_POST['DELIGHT_BROWSER_DEBUG'])) {
			self::$rawPost = (get_magic_quotes_gpc() == 1) ? stripslashes(trim(urldecode($_POST['DELIGHT_BROWSER_DEBUG']))) : trim(urldecode($_POST['DELIGHT_BROWSER_DEBUG']));
		} else {
			self::$rawPost = file_get_contents("php://input");
		}
		return ((get_magic_quotes_gpc() == 1) && !$binary) ? stripslashes(self::$rawPost) : self::$rawPost;
	}

}

?>
