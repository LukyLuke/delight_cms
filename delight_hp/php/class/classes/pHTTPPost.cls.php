<?php

class pHTTPPost {
	
	private $postURI = null;
	private $timeout = 30;
	private $postData = '';
	private $postParam = array();
	private $errorNumber = 0;
	private $errorString = '';
	private $headers = array();
	private $textContent = '';
	
	/**
	 * Initialize
	 * - If nor URL is given here, set it with setURI()
	 * - Use setData() or setParam() to define POST-Data
	 * - use send() to send the params or the postData
	 *
	 * @param String $uri The URL to post data to
	 * @access public
	 */
	public function __construct($uri=null) {
		$this->postURI = $uri;
	}
	
	/**
	 * Set the URL to post the Data to
	 * The URL must be in the format
	 * http://Server.dom/Path/file.ext
	 *
	 * @param string $uri The URL to post data to
	 * @access public
	 */
	public function setURI($uri) {
		$this->postURI = (string)$uri;
	}
	
	/**
	 * Define the Socket-Timeout
	 * default it's 30 Seconds
	 *
	 * @param integer $timeout The timeout
	 * @access public
	 */
	public function setTimeout($timeout) {
		$this->timeout = (int)$timeout;
	}
	
	/**
	 * Set data to be posted
	 *
	 * @param string $data Post-DATA
	 * @access public
	 */
	public function setData($data) {
		$this->postData = (string)$data;
	}
	
	/**
	 * Set a POST-Parameter
	 *
	 * @param string $name Parameter name
	 * @param string $value The value
	 * @access public
	 */
	public function setParam($name, $value) {
		$this->postParam[$name] = $value;
	}
	
	public function getResponseText() {
		return $this->textContent;
	}
	
	public function getHeaders() {
		return $this->headers;
	}
	
	public function getHeader($name) {
		if (array_key_exists(strtolower($name), $this->headers)) {
			return $this->headers[strtolower($name)];
		}
		return false;
	}
	
	public function getErrno() {
		return $this->errorNumber;
	}
	
	public function getError() {
		return $this->errorString;
	}
	
	public function getResponseState() {
		if (array_key_exists('httpstate', $this->headers)) {
			return (int)$this->headers['httpstate'];
		}
		return false;
	}
	
	/**
	 * Send the POST-Request and receive all data
	 *
	 * @param string $data Data to send
	 * @access public
	 * @return boolean If the Connection was successfull or not
	 */
	public function send($data=null) {
		if ($data !== null) {
			$this->setData($data);
		}
		if (empty($this->postData)) {
			$this->makePostDataFromParams();
		}
		
		$uri = parse_url($this->postURI);
		$uri['port'] = !isset($uri['port']) ? 80 : $uri['port'];
		$sock = @fsockopen($uri['host'], $uri['port'], $this->errorNumber, $this->errorString, $this->timeout);
		
		if (get_resource_type($sock) == 'stream') {
			$out = "POST ".$uri['path']." HTTP/1.1\r\n";
			$out .= "Host: ".$uri['host'].":".$uri['port']."\r\n";
			$out .= "Content-Length: ".strlen($this->postData)."\r\n";
			$out .= "Content-Type: text/xml\r\n";
			$out .= "Connection: Close\r\n\r\n";
			$out .= $this->postData;
			
			if (!fwrite($sock, $out)) {
				$this->errorNumber = 99;
				$this->errorString = 'Unable to send data to '.$this->postURI;

			} else {
				$response = '';
				while (!feof($sock)) {
					$response .= fgets($sock, 8192);
				}
				
				if (($response = $this->parseResponse($response)) !== false) {
					$this->parseResponseHeaders($response['headers']);
					$this->textContent = trim($response['content']);
				}
			}
		}
		@fclose($sock);
		return ($this->errorNumber <= 0);
	}
	
	/**
	 * Create the POST-String based on all given Parameters
	 *
	 * @access private
	 */
	private function makePostDataFromParams() {
		$data = '';
		foreach ($this->postParam as $k => $v) {
			if (!empty($data)) {
				$data .= '&';
			}
			$data = urlencode($k).'='.urlencode($v);
		}
		$this->setData($data);
	}
	
	/**
	 * Parse headers and set the $this->headers Array
	 *
	 * @param string $headers Headers received from a HTTP-Request
	 * @access private
	 */
	private function parseResponseHeaders($headers) {
		$lines = split("\n", $headers);
		$stateParsed = false;
		foreach ($lines as $header) {
			$header = trim($header);
			
			// Check if this is the STATE-Header
			if (!$stateParsed && (substr($header, 0, 4) == 'HTTP')) {
				// This header looks like "HTTP/1.1 200 OK"
				$p1 = strpos($header, '/', 0);
				$p2 = strpos($header, ' ', $p1 + 1);
				$p3 = strpos($header, ' ', $p2 + 1);

				$this->headers['httpversion'] = trim(substr($header, $p1, $p2));
				$this->headers['httpstate'] = trim(substr($header, $p2, $p3));
				$this->headers['httpstatestr'] = trim(substr($header, $p3));
				
				if ($this->headers['httpstate'] != 200) {
					$this->errorNumber = 98;
					$this->errorString = 'Connection-State is '.$this->headers['httpstate'];
				}

				$stateParsed = true;
				continue;
			}
			
			// Parse each default-Header "HeaderName: HeaderValue"
			$k = substr($header, 0, strpos($header, ':'));
			$this->headers[strtolower($k)] = substr($header, strlen($k)+1);
		}
	}
	
	/**
	 * Parse a Response from the TrackingServer into an Array
	 * Key 'headers' holds all Headers
	 * Key 'content' holds the UnChunked Content
	 * 
	 * @param String $resp The Response from a HTTP-Server (Tracking-Server)
	 * @return array The Reponse-Array (headers, content)
	 */
	private function parseResponse($resp=null) {
		if (empty($resp)) {
			$this->errorNumber = 99;
			$this->errorString = 'No data received from the Host';
			return false;
		}
		
		$chunks = explode("\r\n\r\n", trim($resp));
		if (!is_array($chunks) || (count($chunks) < 2)) {
			$this->errorNumber = 99;
			$this->errorString = 'Corrupt data received from the Host';
			return false;
		}
		
		$back = array();
		$back['headers'] = array_shift($chunks);
		$back['content'] = '';
		$resp = implode("\r\n", $chunks);
		$check = '';
		$eol = "\r\n";
		$eollen = strlen($eol);
		if (strlen($resp) > 4) {
			do {
				$resp = ltrim($resp);
				$pos = strpos($resp, $eol);
				if ($pos === false) {
					$back['content'] .= trim($resp);
					break;
				}
				$len = hexdec(substr($resp, 0, $pos));
				if (!is_numeric($len) || ($len < 0)) {
					$back['content'] .= trim($resp);
					break;
				}
				$back['content'] .= substr($resp, $pos + $eollen, $len);
				$resp  = substr($resp, $len + $pos + $eollen);
				$check = trim($resp);
			} while(!empty($check));
		}
		return $back;
	}
	
}

?>
