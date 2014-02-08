<?php
/**
 * The main Exception-Handler for a delight web product
 *
 * @author Lukas Zurschmiede <l.zurschmiede@delightsoftware.com>
 * @copyright (c) 2008 by delight software gmbh, switzerland
 *
 * @package delightWebProduct
 * @version 1.0
 */

$DBTables['exception'] = $tablePrefix.'_exceptions';
$DBFields['exception'] = array(
	'id' => "id",
	'date' => "thrown_on",
	'code' => "code",
	'file' => "file",
	'line' => "line",
	'message' => "message",
	'trace' => "trace",
	'cause' => "cause",
	'post' => 'post_data',
	'get' => 'get_data',
	'raw' => 'raw_post_data'
);

class DWPException extends Exception {
	const MODULE_VERSION = 2008101001;
	private $cause;
	private $fileName;
	private $lineNumber;
	private $parseError = false;
	private $trace = null;

	/**
	 * Create a DWPException
	 *
	 * @param $message string The Exception-message
	 * @param $code int the Exception-code, can be a Userdefined Code which will be shown
	 * @param $cause Exception
	 */
	function __construct($message=null, $code=0, Exception $cause=null) {
		parent::__construct($message, $code);
		$this->cause = $cause;
	}

	/**
	 * Set the Filename this Error is Occured
	 * USE ONLY if this Exception is throwed/created by a Parse-Error
	 *
	 * @param string $fileName The Filename the Exception is thored in
	 */
	public function setFile($fileName) {
		$this->fileName = $fileName;
		$this->parseError = true;
	}

	/**
	 * Set the LineNumber the Error is Occured
	 * USE ONLY if this Exception is throwed/created by a Parse-Error
	 *
	 * @param int $lineNumber The LineNumber the Error is Occured
	 */
	public function setLine($lineNumber) {
		$this->lineNumber = $lineNumber;
		$this->parseError = true;
	}

	/**
	 *
	 * return Exception
	 */
	public function getCause() {
		return $this->cause;
	}

	/**
	 * Return an Array with the complete Stack-Trace
	 *
	 * @return Array The complete StackTrace
	 */
	public function getStackTrace() {
		if ($this->cause !== null) {
			$arr = array();
			if ($this->trace === null) {
				$trace = $this->getTrace();
			} else {
				$trace = $this->trace;
			}
			array_push($arr, $trace[0]);
			unset($trace);
			if (get_class($this->cause) == get_class($this)) {
				foreach ($this->cause->getStackTrace() as $trace) {
					array_push($arr, $trace);
				}
			} else {
				foreach ($this->cause->getTrace() as $trace) {
					array_push($arr, $trace);
				}
			}
			return $arr;
		} else {
			if ($this->trace === null) {
				return $this->getTrace();
			} else {
				return $this->trace;
			}
		}
	}

	public function setTrace($trace) {
		$this->trace = $trace;
	}

	/**
	 * Get the whole StackTrace formated as HTML
	 *
	 * @return string StackTrace as HTML
	 */
	public function showStackTrace() {
		$i = 0;
		$trace = null;

		$html  = '<div style="margin:10;font-family:monospace;border:1px solid #104B7F;color:black;background:#49ACF1;font-size:1em;">';
		$html .= '<p style="padding:3px;margin:0;background:#104B7F;color:#49ACF1;font-weight:bold;font-size:1.3em;">An exception was thrown:</p>';
		$html .= '<p style="padding:3px;"><u>Exception code</u>: <b>'.$this->code.'</b><br/>';
		$html .= '<u>Exception message</u>: '.$this->message.'</p>';
		$html .= '<div style="padding:10px 3px 3px 3px;color:#0000FF;overflow:auto;">';
		foreach ($this->getStackTrace() as $trace) {
			$html .= $this->showTrace($trace, $i);
			$i++;
		}
		$html .= '#'.$i.' {main}';
		$html .= '</div></div>';

		unset($i);
		unset($trace);
		return $html;
	}

	/**
	 * Store the Exception in the Database
	 *
	 */
	public function store() {
		$back = 0;
		try {
			$this->updateDatabase();
			$db = pDatabaseConnection::getDatabaseInstance();
			if ($db->connectSuccessfully()) {
				$res = null;

				$get = serialize($_GET);
				$post = serialize($_POST);
				if (!defined('EXCEPTION_NO_RAW') || EXCEPTION_NO_RAW) {
					$raw = serialize(pHttpRequest::getRequestData());
				}

				$sql = 'INSERT INTO [table.exception] ([field.exception.date],[field.exception.code],[field.exception.cause],[field.exception.trace],[field.exception.file],[field.exception.line],[field.exception.message],[field.exception.get],[field.exception.post],[field.exception.raw])';
				$sql .= ' VALUES (\''.date('Y-m-d H:i:s').'\','.$this->getCode().',\''.mysql_real_escape_string(serialize($this->getCause())).'\',\''.mysql_real_escape_string(serialize($this->getTrace())).'\',\''.($this->parseError ? $this->fileName : $this->getFile()).'\','.($this->parseError ? $this->lineNumber : $this->getLine()).',\''.mysql_real_escape_string($this->getMessage()).'\',\''.mysql_real_escape_string($get).'\',\''.mysql_real_escape_string($post).'\',\''.mysql_real_escape_string($raw).'\');';
				$db->run($sql, $res);
				$back = $res->getInsertId();
			}
		} catch (Exception $e) {
			echo '<div style="border:1px solid #650404;padding:0;background:#BF8E8E;font-size:10pt;"><div style="background:#650404;color:white;padding:5px;font-weight:bold;">Unable to save the Exception</div><div style="padding:5px;">'.$e->getMessage().'</div>';
			if (defined('DWP_DEBUG') && DWP_DEBUG) {
				$this->showStackTrace();
			}
			echo '</div>'.PHP_EOL;
		}
		return $back;
	}

	/**
	 * Get a Trace based on the Backtrace $trace and on a number
	 *
	 * @param $trace Array The complete Backtrace as an Array
	 * @param $num integer the Number to show
	 * @param $trim boolean Trim messages and Objects
	 * @return String a HTML-Formated Trace
	 */
	public function showTrace($trace, $num, $trim=true) {
		$html = '<span style="white-space:nowrap;"><span class="exNum">#'.$num.'</span> ';
		if (array_key_exists("file", $trace)) {
			$html .= $trace["file"];
		}

		if (array_key_exists("line",$trace)) {
			$html .= "<span class='exLine'>(".$trace["line"]."):</span> ";
		}

		if (array_key_exists("class",$trace) && array_key_exists("type",$trace)) {
			$html .= '<span class="exClass">'.$trace["class"].'<span class="exClassType">'.$trace["type"].'</span></span>';
		}

		if (array_key_exists("function", $trace)) {
			$html .= '<span class="exFunction">'.$trace["function"]."(";
			if (array_key_exists("args", $trace)) {
				if (count($trace["args"]) > 0) {
					$argcnt = 0;
					foreach ($trace["args"] as $arg) {
						$type = gettype($arg);
						$value = $arg;

						if ($argcnt > 0) {
							$html .= ', ';
						}

						if ($type == "boolean") {
							$html .= '<span class="exBoolean">';
							if ($value) {
								$html .= "true";
							} else {
								$html .= "false";
							}
							$html .= '</span>';

						} elseif ($type == "integer" || $type == "double") {
							$html .= '<span class="exNumber">';
							if (settype($value, "string")) {
								$html .= $this->getObjectVarValueHTML(($trim && strlen($value)>17) ? substr($value,0,17).'...' : $value);
							} else {
								if ($type == "integer" ) {
									$html .= "? integer ?";
								} else {
									$html .= "? double or float ?";
								}
							}
							$html .= '</span>';

						} elseif ($type == "string") {
							$html .= '<span class="exString">';
							$html .= $this->getObjectVarValueHTML(($trim && strlen($value)>17) ? substr($value,0,17).'...' : $value);
							$html .= '</span>';

						} elseif ($type == "array") {
							$html .= '<span class="exArray">';
							$html .= $trim ? 'Array' : $this->getObjectVarValueHTML($value);
							$html .= '</span>';

						} elseif ($type == "object") {
							$html .= '<span class="exObject">';
							$html .= $trim ? 'Object' : $this->getObjectVarValueHTML($value);
							$html .= '</span>';

						} elseif ($type == "resource") {
							$html .= '<span class="exResource">';
							$html .= "Resource";
							$html .= '</span>';

						} elseif ($type == "NULL") {
							$html .= '<span class="exNull">';
							$html .= "null";
							$html .= '</span>';

						} elseif ($type == "unknown type") {
							$html .= '<span class="exUnknown">';
							$html .= "? unknown type ?";
							$html .= '</span>';
						}
						$argcnt++;
					}
				}
			}
			$html .= ")</span></span><br/>";
		}
		return $html;
	}

	public static function getObjectHTML($o) {
		$html = '';
		if (gettype($o) == 'object') {
			$i = 0;
			$vars = get_object_vars($o);
			foreach ($vars as $var => $val) {
				if ( ($i > 0) && !is_array($val) && !is_array($val) ) {
					$html .= '<br/>';
				}
				$html .= '<span class="exObjectVar" style="float:left;">['.$var.']=</span>'.self::getObjectVarValueHTML($val);
				$i++;
			}
		} else {
			$html = self::getObjectVarValueHTML($o);
		}
		return $html;
	}

	public static function getObjectVarValueHTML($val) {
		$html = '';
		switch (gettype($val)) {
			case 'boolean':
				if ($val) {
					$html .= '<span class="exBoolean">true</span>';
				} else {
					$html .= '<span class="exBoolean">false</span>';
				}
				break;
			case 'integer':
				$html .= '<span class="exInteger">'.$val.'</span>';
				break;
			case 'double':
				$html .= '<span class="exDouble">'.$val.'</span>';
				break;
			case 'string':
				$val = preg_replace('/([^a-zA-Z0-9\.\-_:;&#])/smie', '"&#".ord("\\1").";"', $val);
				if (strlen($val) > 50) {
					$arId = sha1(uniqid(rand(), true));
					$valStrip = substr($val, 0, 47).'...';
					$val = '<span onclick="app && app.switchVisibility(\''.$arId.'\');" class="exStringStrip">'.$valStrip.'</span><div class="exStringStripDisplay" id="'.$arId.'" onclick="app && app.switchVisibility(\''.$arId.'\');" style="display:none;">'.$val.'</div>';
				}
				$html .= '<span class="exString">\''.$val.'\'</span>';
				break;
			case 'array':
				$arId = sha1(uniqid(rand(), true));
				$html .= '<div class="exArray"><span onclick="app && app.switchVisibility(\''.$arId.'\');">Array</span>(<span id="'.$arId.'" style="display:none;">';
				$i = 0;
				foreach ($val as $k => $v) {
					if ( ($i > 0) && !is_array($v) && !is_array($v) ) {
						$html .= "<br/>";
					}
					$html .= '<span class="exArrayKey" style="float:left;">['.$k.']=</span>'.self::getObjectVarValueHTML($v);
					$i++;
				}
				$html .= '</span>)</div>';
				break;
			case 'object':
				$arId = sha1(uniqid(rand(), true));
				$html .= '<div class="exObject"><span onclick="app && app.switchVisibility(\''.$arId.'\');">Object</span>(<span id="'.$arId.'" style="display:none;">'.self::getObjectHTML($val).'</span>)</div>';
				break;
			case 'resource':
				$html .= '<span class="exResource">#Resource</string>';
				break;
			default:
				$html .= '<span class="exUnknown">? Unknown ?</span>';
				break;
		}
		return $html;
	}

	private function updateDatabase() {
		// first get the version stored in the Database
		$db = pDatabaseConnection::getDatabaseInstance();
		$version = $db->getModuleVersion('DWPException');
		$res = null;

		// Check if we need an Update
		if (self::MODULE_VERSION > $version) {
			// initial
			if ($version <= 0) {
				$sql = 'CREATE TABLE IF NOT EXISTS [table.exception] ('.
				' [field.exception.id] INT(11) UNSIGNED NOT NULL auto_increment,'.
				' [field.exception.date] DATETIME NOT NULL default \'0000-00-00 00:00:00\','.
				' [field.exception.code] INT(11) UNSIGNED NOT NULL default 0,'.
				' [field.exception.file] VARCHAR(250) NOT NULL default \'\','.
				' [field.exception.line] INT(10) UNSIGNED NOT NULL default 0,'.
				' [field.exception.message] TEXT NOT NULL default \'\','.
				' [field.exception.trace] TEXT NOT NULL default \'\','.
				' [field.exception.cause] TEXT NOT NULL default \'\','.
				' KEY [field.exception.date] ([field.exception.date]),'.
				' UNIQUE KEY [field.exception.id] ([field.exception.id])'.
				');';
				$db->run($sql, $res);
			}

			if ($version < 2008101001) {
				$sql = 'ALTER TABLE [table.exception] ADD ([field.exception.post] TEXT NOT NULL default \'\',[field.exception.get] TEXT NOT NULL default \'\',[field.exception.raw] TEXT NOT NULL default \'\');';
				$db->run($sql, $res);
			}

			// Update the version in database for this module
			$db->updateModuleVersion('DWPException', self::MODULE_VERSION);
		}
	}
}
?>
