<?php

/**
 * class pMimeMail
 * 
 * Check Email-Adresses and send's Text and HTML-Emails including Attachments
 * 
 * @package simple
 * @author Lukas Zurschmiede <l.zurschmiede@delightsoftware.com>
 * @copyright 2007 by delight software gmbh
 */
class pMimeMail
{
	/**
	 * LineEnding - CRLF, LF or CR
	 */
	const CRLF = "\r\n";
	
	/**
	 * Servername for EHLO commmand on Remote-MTA - set this to a Public Domain or IP
	 * @access private
	 * @var string
	 */
	private $ehloServerString;
	
	/**
	 * Mailserver to connect to for sending the email
	 * @access private
	 * @var string
	 */
	private $serverAddress;
	
	/**
	 * If the SMTP-Server needs authentication, set this to true
	 * @access private
	 * @var boolean
	 */
	private $auth;
	
	/**
	 * Athentication-Type. can be: LOGIN,PLAIN,POP
	 * @access private
	 * @var string
	 */
	private $authType;
	
	/**
	 * Data for POP-before-SMTP Authentication. Array(user=>'', password=>'', server=>'', port=>'')
	 * @access private
	 * @var array
	 */
	private $POPAuth;
	
	/**
	 * Username for Authentication methods LOGIN and PLAIN
	 * @access private
	 * @var string
	 */
	private $authUser;
	
	/**
	 * Password for Authentication methods LOGIN and PLAIN
	 * @access private
	 * @var string
	 */
	private $authPass;
	
	/**
	 * Date for MailHeader
	 * @access private
	 * @var string
	 */
	private $mailDate;
	
	/**
	 * Name, Surname or Organization from Sender
	 * @access private
	 * @var string
	 */
	private $mailSenderName;
	
	/**
	 * Organization-Name for Email-Header
	 * @access private
	 * @var string
	 */
	private $mailOrganization;
	
	/**
	 * Emailaddress from Sender
	 * @access private
	 * @var string
	 */
	private $senderMail;
	
	/**
	 * List of Recipients, seperated by comma
	 * @access private
	 * @var array
	 */
	private $mailRecipientList;
	
	/**
	 * The Subject for the Email to send
	 * @access private
	 * @var string
	 */
	private $mailSubject;
	
	/**
	 * Mailcontent to send (TEXT or HTML)
	 * @access private
	 * @var string
	 */
	private $mailContent;
	
	/**
	 * Array with all Attachments
	 * array(filename, contentType, filename, contentType, ...)
	 * @access private
	 * @var array
	 */
	private $mailAttachments;
	
	/**
	 * ContentType for this Email - can be text or html
	 * @access private
	 * @var string
	 */
	private $mailContentType;
	
	/**
	 * Name of the EmailClient, perhaps "Outlook" or something like that
	 * @access private
	 * @var string
	 */
	private $mailerName;
	
	/**
	 * Priority of this Email 1: high, 3: normal, 5: low
	 * @access private
	 * @var string
	 */
	private $mailPriority;
	
	/**
	 * List of CC-Addresses, seperated by comma
	 * @access private
	 * @var array
	 */
	private $mailCCRecipientList;
	
	/**
	 * List of BCC-Recipients, seperated by comma
	 * @access private
	 * @var array
	 */
	private $mailBCCRecipientList;
	
	/**
	 * SMTP-Server port
	 * @access private
	 * @var integer
	 */
	private $serverPort;
	
	/**
	 * Show debug-output, yes or no
	 * @access private
	 * @var boolean
	 */
	private $debug;
	
	/**
	 * 
	 * @access private
	 * @var boolean
	 */
	private $sendStateError;

	/**
	 * All Adresses the email sill be sent to
	 * @access private
	 * @var array
	 */
	private $recipientAdresses;
	
	/**
	 * Current boundary to use for Attachments etc.
	 * @access private
	 * @var string
	 */
	private $contentBoundary;
	
	/**
	 * POP-Connection-Handle while POP-before-SMTP Authentication
	 * @access private
	 * @var resource
	 */
	private $POPhandle;
	
	/**
	 * SMTP-Connection-Handle while sending the Email
	 * @access private
	 * @var resource
	 */
	private $SMTPhandle;
	
	/**
	 * List with all Responce-States the SMTP-Server should give
	 * @access private
	 * @var array
	 */
	private $responceStates;
	
	/**
	 * stores the last message we receive from SMTP-Server
	 * @access private
	 * @var string
	 */
	private $lastState;
	
	/**
	 * All occured Error-Messages
	 * @access private
	 * @var unknown_type
	 */
	private $errorMessage;

	/**
   * Class constructor
   *
   * @access public
   */
	public function __construct() {
		// default authentication is disabled
		$this->auth = false;
		$this->authType = 'LOGIN';
		$this->authUser = '';
		$this->authPass = '';
		$this->POPAuth = array('user'=>'', 'password'=>'', 'server'=>'', 'port'=>'110');
		
		// Clear Recipients and Attachments
		$this->mailRecipientList = array();
		$this->mailCCRecipientList = array();
		$this->mailBCCRecipientList = array();
		$this->mailAttachments = array();

		// Basic mail settings
		$this->setMailFrom('unknown', $this->getAdminEmailAddress());
		$this->setMailSubject('no subject');
		$this->SetPriority(3);
		$this->setMailerName('delight software gmbh pMimeMail-PHPClass');
		$this->setEmailContent('no content', 'text');
		
		//$this->addMailRecipient($email, 'to,cc,bcc');
		//$this->addMailCCRecipient($email);
		//$this->addMailBCCRecipient($email);
		//$this->addMailAttachment($file);
		
		// Special mail settings
		$this->setMailDate(date('r'));
		
		// Some server settings
		$this->ehloServerString = $this->getLocalServerName();
		$this->setSMTPServer('localhost');
		$this->setSMTPServerPort(25);
		
		// not email specific variables
		$this->debug = false;
		$this->sendStateError = '';
		$this->recipientAdresses = array();
		$this->contentBoundary = '';
		$this->POPhandle = null;
		$this->SMTPhandle = null;
		$this->responceStates = array();
		$this->lastState = '';
		$this->errorMessage = '';
	}

	/**
	 * Return the local server name
	 *
	 * @return string ServerName
	 * @access private
	 */
	private function getLocalServerName() {
		$server = $_SERVER['SERVER_ADDR'];
		return trim($server);
	}
	
	/**
	 * Return the Email address from Server-Administrator
	 *
	 * @return string
	 * @access private
	 */
	private function getAdminEmailAddress() {
		return $_SERVER['SERVER_ADMIN'];
	}

	/**
	 * Sends the email out to all recipients
	 *
	 * @return boolean true on success, false on failure
	 * @access public
	 */
	public function sendMail() {
		// Check for POP-Authentication
		if (strtoupper($this->authType) == 'POP') {
			if (!$this->doAuthPOPbeforeSMTP($this->POPAuth['server'], $this->POPAuth['port'], $this->POPAuth['user'], $this->POPAuth['password'])) {
				$this->errorMessage = 'POP-Authentication failed'.self::CRLF;
			}
		}

		// Create the SMTP-Header / SMTP-Commands
		$commands = $this->createSMTPHeader();

		// Create the Status-Array with the Correct Server-Messages
		$this->createCodeArray();

		// Set main Status and initiate connection with the SMTP server
		$status = 0;
		$this->SMTPhandle = @fsockopen($this->serverAddress, $this->serverPort);

		// Check for correct response if false, end
		$this->sendStateError = true;
		if (!is_resource($this->SMTPhandle)) {
			$this->sendStateError = true;
		} else {
			// go through the datas from handle
			while($this->lastState = fgets($this->SMTPhandle, 8192)) {
				// Check if the returnd Status is the same as the status in State-Array
				if (strcmp(substr($this->lastState, 0, 3), $this->responceStates[$status]) != 0) {
					$this->showDebug('<b>ERROR</b> in step '.$status.'<br />');
					$this->showDebug(' Required State : '.$this->responceStates[$status].'<br />');
					$this->showDebug(' Received State : '.$this->lastState.'<br />');

					// There is occured an Error, so we close the Connection and break the sending-process
					$this->errorMessage = 'SMTP-Server response: '.$this->lastState.self::CRLF;
					@fclose($this->SMTPhandle);
					break;
				}
				$this->showDebug(' Required State : '.$this->responceStates[$status].'<br />');
				$this->showDebug(' Received State : '.$this->lastState.'<br />');

				// Check if the returnd Message is not zero-length and continue
				if (strcmp(substr($this->lastState, 3, 1), " ") != 0) {
					continue;
				}

				// if the Status is count($this->responceStates), the sending is finished
				if ($status == count($this->responceStates)-1) {
					@fclose($this->SMTPhandle);
					$this->sendStateError = false;
					break;
				}

				// Send Datas
				$this->showDebug('<br> Send command: '.$commands[$status].'<br />');
				fputs($this->SMTPhandle, $commands[$status++].self::CRLF);
			}
		}

		// if an Error occure
		if ($this->sendStateError) {
			@fclose($this->SMTPhandle);
		}

		// Close connection for POP-Authentication
		if (is_resource($this->POPAuth)) {
			@fclose($this->POPhandle);
		}
		
		return !$this->sendStateError;
	}
	
	/**
	 * Get the last message from the SMTP-Server
	 *
	 * @return string the last Message from SMTP-Server
	 */
	public function getSendErrorMessage() {
		return $this->errorMessage;
	}

	/**
	 * Get all Mail-Addresses from $addresses as MailHeader in given $type
	 *
	 * @param string $type Type, can te to,cc,bcc
	 * @param array $addresses List with all Adresses which should be returnd as given Header
	 * @return string
	 * @access private
	 */
	private function parseMailAddress($type, $addresses) {
		if (count($addresses) > 0) {
			// initialize the Header-String
			switch (strtolower($type)) {
				case 'to':
					$back = "To: ";
					break;

				case 'cc':
					$back = "Cc: ";
					break;

				case 'bcc':
					$back = "Bcc: ";
					break;
			}

			// Add all Adresses to recipientAddresses
			foreach ($addresses as $v) {
				$this->recipientAdresses[] = $v;
			}
			$this->recipientAdresses = array_unique($this->recipientAdresses);

			// Append all Adresses
			$back .= implode(',',$addresses);
			return $back.self::CRLF;
		} else {
			return '';
		}
	}

	/**
	 * Create the complete SMTP-Commands, which will be sent
	 *
	 * @return array all SMTP-Commands
	 * @access private
	 */
	private function createSMTPHeader() {
		// Create the Content
		$content = $this->createMessage();

		// Set the EHLO (Public-Domain)
		$cmdList = array("EHLO ".$this->ehloServerString);

		// Check for the Authentication
		if ($this->auth) {
			if ($this->authType == 'LOGIN') {
				array_push($cmdList, "AUTH LOGIN", base64_encode($this->authUser), base64_encode($this->authPass));
			} else if ($this->authType == 'PLAIN') {
				array_push($cmdList, "AUTH PLAIN ".base64_encode($this->authUser."\0".$this->authUser."\0".$this->authPass));
			}
		}

		// Mail From
		array_push($cmdList, "MAIL FROM: <".$this->senderMail.">");

		// Add the recipients
		for ($i = 0; $i < count($this->recipientAdresses); $i++) {
			array_push($cmdList, "RCPT TO: <".$this->recipientAdresses[$i].">");
		}

		// Add the end of the Array
		/*for ($lf = 0; $lf < count($Data); $lf++) {
			array_push($cmdList, $Data[$lf]);
		}*/

		// Add the Message
		array_push($cmdList, "DATA", $content, "QUIT","");

		return $cmdList;
	}

	/**
	 * Create an Array with all states the SMTP-Server should responce
	 *
	 * @access private
	 */
	private function createCodeArray() {
		$this->responceStates = array("220", "250");
		
		if ($this->auth && ($this->authType == "LOGIN")) {
			array_push($this->responceStates,"334", "334", "235");
		} else if ($this->auth && ($this->authType == "PLAIN")) {
			array_push($this->responceStates,"235");
		}

		for ($i = 0; $i < count($this->recipientAdresses); $i++) {
			$this->responceStates[] = "250";
		}
		array_push($this->responceStates, "250", "354", "250", "221");
	}

	/**
	 * Append needed Content-Type headers and add all Attachments on the end of the Message
	 *
	 * @access private
	 */
	private function createContent() {
		// We need a newline before the Content begins (to seperate from the headers)
		$content = self::CRLF;
		$content .= "--".$this->contentBoundary.self::CRLF;

		// Append the right ContentType for given Message
		switch ($this->mailContentType) {
			case 'html':
				$content .= "Content-Type: text/html; charset=\"iso-8859-15\"".self::CRLF;
				break;

			default:
				$content .= "Content-Type: text/plain; charset=\"iso-8859-15\"".self::CRLF;
				break;
		}

		// Append the real Mail-Content as quoted-printable
		$content .= "Content-Transfer-Encoding: quoted-printable".self::CRLF;
		$content .= self::CRLF.$this->mailContent.self::CRLF;

		// Get each Attachement and Add it to the Content
		foreach ($this->mailAttachments as $k => $attachment) {
			// Set the File and the Contenttype
			$file = $attachment[0];
			$filename = basename($file);

			// Check if the File exists
			if (file_exists($file) && is_readable($file)) {
				// Read the File chunck-split it
				$fileContent = chunk_split(base64_encode(file_get_contents($file)));

				// If the Messageformat is HTML, create a ContentID
				$ContentId = "";
				if ($this->mailContentType == "html") {
					$ContentId = "Content-ID: <".base64_encode($file)."@".$this->serverAddress.">".self::CRLF;
				}

				// ADDED: 2007-05-05
				// Add a Blank-Line between the different attachments
				$content .= self::CRLF;
				
				// Add the Filename and the Contenttype to the Content
				$content .= "--".$this->contentBoundary.self::CRLF;
				$content .= "Content-Transfer-Encoding: base64".self::CRLF;
				$content .= "Content-Type: ".$attachment[1]."; charset=\"iso-8859-1\"; name=\"".$filename."\"".self::CRLF;
				$content .= "Content-Disposition: attachement; filename=\"".$filename."\"".self::CRLF;
				$content .= $ContentId.self::CRLF;
				$content .= $fileContent.self::CRLF;
			} else {
				$content .= "--".$this->contentBoundary.self::CRLF;
				$content .= "Content-Type: text/plain; charset=\"iso-8859-1\";".self::CRLF;
				$content .= $ContentId.self::CRLF;
				$content .= "Image not available".self::CRLF;
			}
		}
		$content .= self::CRLF."--".$this->contentBoundary."--";
		$this->mailContent = $content;
	}
	
	/**
	 * Set the subject for the Email
	 *
	 * @param string $subject
	 * @access public
	 */
	public function setMailSubject($subject) {
		$this->mailSubject = preg_replace('/[^\w\. -]/smi', '', $subject);
	}

	/**
	 * Set the Priority for this Email
	 *
	 * @param int $priority Priority for the Mail (1-5)
	 * @access public
	 */
	public function SetPriority($priority) {
		$prio = array('','low','lower','normal','higher','high');
		
		if ( ((int)$priority > 0) && ((int)$priority < 6) ) {
			$this->mailPriority = "X-Priority: ".$priority." (".$prio[$priority].")".self::CRLF."Priority: ".$priority;
		} else {
			$this->mailPriority = "X-Priority: 3 (".$prio[3].")".self::CRLF."Priority: 3";
		}
	}
	
	/**
	 * Set Emailclient-name
	 *
	 * @param string $mailer
	 * @access public
	 */
	public function setMailerName($mailer) {
		$this->mailerName = preg_replace('/[^\w\. -]/smi', '', $mailer);
	}
	
	/**
	 * Set the EmailContent and the equivalent ContentType for it
	 *
	 * @param string $content Content to send
	 * @param string $type ContentType for the Email - can be "text" of "html"
	 * @access public
	 */
	public function setEmailContent($content, $type='text') {
		$this->mailContent = $content;
		$this->mailContentType = $type;
	}
	
	/**
	 * Add an EmailAddress to the Recipient-List
	 *
	 * @param string $email Emailaddress to add as given Recipient
	 * @param string $type Recipient-Type (to,cc,bcc)
	 * @access public
	 */
	public function addMailRecipient($email, $type=null) {
		$type = strtolower($type);
		if ($type == 'cc') {
			$this->addMailCCRecipient($email);
		} else if ($type == 'bcc') {
			$this->addMailBCCRecipient($email);
		} else {
			$this->mailRecipientList[] = $email;
		}
	}
	
	/**
	 * Add an EmailAddress to the List of CC-Recipients
	 *
	 * @param string $email EmailAddress to add as CC-Recipient
	 * @access public
	 */
	public function addMailCCRecipient($email) {
		$this->mailCCRecipientList[] = $email;
	}
	
	/**
	 * Add an EmailAddress to the List of BCC-Recipients
	 *
	 * @param string $email EmailAddress to add as BCC-Recipient
	 * @access public
	 */
	public function addMailBCCRecipient($email) {
		$this->mailBCCRecipientList[] = $email;
	}
	
	/**
	 * Set NAME and EMAIL from sender - optionally xou can give an organization
	 *
	 * @param string $name Senders name
	 * @param string $email Senders email
	 * @param string $organization Senders organization
	 */
	public function setMailFrom($name, $email, $organization='') {
		$this->mailSenderName = $name;
		$this->mailOrganization = $organization;
		$this->senderMail = $email;
	}
	
	/**
	 * Set the Maildate
	 *
	 * @param string $date Datestring which can be parsed by strtotime
	 * @access public
	 */
	public function setMailDate($date) {
		$this->mailDate = strtotime($date, time());
		if ( ($this->mailDate === false) || ($this->mailDate < 0)) {
			$this->mailDate = time();
		}
	}
	
	/**
	 * Add an Attachment - only if the file exists
	 *
	 * @param string $file File to add as an attachment
	 */
	public function addMailAttachment($file) {
		if (file_exists($file) && is_readable($file)) {
			$kdeMime = new pKdeMimeType();
			$mime = $kdeMime->getMimeInfo($file);
			unset($kdeMime);
			array_push($this->mailAttachments, array($file, $mime['MimeType']));
		}
	}
	
	/**
	 * Set the Port for the SMTP-Connection
	 *
	 * @param int $port SMTP-Port
	 * @access public
	 */
	public function setSMTPServerPort($port) {
		$this->serverPort = preg_replace('/[\D]/smi', '', $port);
	}
	
	/**
	 * Set the SMTP-Server address (IP or Domain)
	 *
	 * @param string $server SMTP-Server address
	 * @access public
	 */
	public function setSMTPServer($server) {
		$this->serverAddress = preg_replace('/[^\w\. -]/smi', '', $server);
	}
	
	/**
	 * Set the string this script sends with the EHLO-Command
	 * Normally this setting should not be changed
	 *
	 * @param string $ehlo EHLOString
	 * @access public
	 */
	public function setEHLOServerString($ehlo) {
		$this->ehloServerString = preg_replace('/[^\w\. -]/smi', '', $ehlo);
	}
	
	/**
	 * Set the Authentication for sending mailings over the given SMTP-Server
	 *
	 * @param string $authType Can be 'PLAIN','LOGIN','POP'
	 * @param string $authUser Username for authentication
	 * @param string $authPass Password for given Username
	 * @param string $authServer Server to authenticate against POP on port $authPort
	 * @param int $authPort PORT for POP-before-SMTP Authentication
	 */
	public function setAuthentication($authType, $authUser, $authPass, $authServer='', $authPort=110) {
		$authType = strtoupper($authType);
		if ( ($authType == 'LOGIN') || ($authType == 'PLAIN') ) {
			$this->authType = $authType;
			$this->authUser = $authUser;
			$this->authPass = $authPass;
		} else if ($authType == 'POP') {
			$this->auth = true;
			$this->authType = $authType;
			$this->POPAuth['user'] = $authUser;
			$this->POPAuth['password'] = $authPass;
			$this->POPAuth['server'] = $authServer;
			$this->POPAuth['port'] = $authPort;
		}
	}

	/**
	 * Create a new Boundary and return it
	 *
	 * @return string A new Boundary
	 * @access private
	 */
	private function createNewBoundary() {
		return strtoupper(md5(uniqid(time())));
	}

	/**
	 * Create the Message and return it
	 *
	 * @return string the Message with all Headers included
	 * @access private
	 */
	private function createMessage() {
		$content = "";

		// Set Return-Path
		$content .= "Return-Path: <".$this->senderMail.">".self::CRLF;

		// Set the Boundry
		$this->contentBoundary = $this->createNewBoundary();

		// Set the ContentType
		switch ($this->mailContentType) {
			case 'html':
				$this->createHTMLMessage();
				if (count($this->mailAttachments) > 0) {
					$content .= "Content-Type: multipart/mixed; charset=\"iso-8859-15\"; boundary=\"".$this->contentBoundary."\"".self::CRLF;
					$this->createContent();
				} else {
					$content .= "Content-Type: text/html; charset=\"iso-8859-15\"".self::CRLF;
				}
				break;

			case 'text':
			default:
				if (count($this->mailAttachments) > 0) {
					$content .= "Content-Type: multipart/mixed; charset=\"iso-8859-15\"; boundary=\"".$this->contentBoundary."\"".self::CRLF;
					$this->createContent();
				} else {
					$content .= "Content-Type: text/plain; charset=\"iso-8859-15\"".self::CRLF;
				}
				break;
		}

		// Add From
		$content .= "From: ".$this->mailSenderName." <".$this->senderMail.">".self::CRLF;

		// Set Reply-To
		$content .= "Reply-To: ".$this->senderMail.self::CRLF;

		// Set Organization
		if (strlen($this->mailOrganization) > 0) {
			$content .= "Organization: ".$this->mailOrganization.self::CRLF;
		}

		// Add all Recipients
		$content .= $this->parseMailAddress("to",  $this->mailRecipientList);
		$content .= $this->parseMailAddress("cc",  $this->mailCCRecipientList);
		$content .= $this->parseMailAddress("bcc", $this->mailBCCRecipientList);
		
		$this->showDebug('<br />Add TO : '.implode(',', $this->mailRecipientList).'<br />');
		$this->showDebug('Add CC : '.implode(',', $this->mailCCRecipientList).'<br />');
		$this->showDebug('Add BCC: '.implode(',', $this->mailBCCRecipientList).'<br />');

		// Set the Subject
		$content .= "Subject: ".$this->mailSubject.self::CRLF;

		// Set the Date
		$content .= "Date: ".date("r", $this->mailDate).self::CRLF;

		// Set useable Headers
		$content .= "X-Sender: <".$this->senderMail.">".self::CRLF;
		$content .= "X-Mailer: ".$this->mailerName.self::CRLF;
		$content .= $this->mailPriority.self::CRLF;
		$content .= "MIME-Version: 1.0".self::CRLF;

		// Insert the Content
		$content .= self::CRLF.$this->mailContent.self::CRLF.".";

		return $content;
	}

	/**
	 * Create the correct HTML-Code from a HTML-Message
	 * Add Images as Attachments, etc.
	 *
	 * @access private
	 */
	private function createHTMLMessage() {
		// Search all Images in content
		$match = array();
		$imgPattern = '/(\<img)(.*?)(src=[\'"])(.*?)([\'"])(.*?)(\>)/smi';
		if ( (preg_match_all($imgPattern, $this->mailContent, $match, PREG_SET_ORDER) !== false) && (count($match) > 0) ) {
			foreach ($match as $k => $img) {
				$file = $img[4];
				$contId = "3D\"cid:".base64_encode($File)."@".$this->serverAddress."\"";
				$this->mailContent = str_replace($file, $contId, $this->mailContent);
				$this->addMailAttachment($file);
			}
		}
		
		// Check if an Attribute is in Content
		/*if (substr_count($content,"<img") > 0) {
			$OldPos = 0;
			do {
				// Set the Content to lowerstring (only to Check the Required Tags)
				$content = strtolower($this->mailContent);

				// Check for the next Tag
				$Open = strpos($content,"<img",$OldPos);
				if (!(is_integer($Open))) {
					break 1;
				}

				// Check for the ">" after the finded Tag
				$Close = strpos($content,">",$Open);
				if (!(is_integer($Close))) {
					break 1;
				}

				// Check for "src=" between $Open and $Close
				$SrcStart = strpos($content,"src=",$Open);
				if (!(is_integer($SrcStart))) {
					break 1;
				}

				// Check for a " " between $Open and $Close
				$SrcEnd = strpos($content," ",$SrcStart);
				if (!(is_integer($SrcEnd))) {
					break 1;
				}

				if (($SrcStart > $Close) || ($SrcEnd > $Close)) {
					break 1;
				}

				// Set the File
				$OrigFile = substr($this->mailContent,($SrcStart + 4),($SrcEnd - $SrcStart - 4));
				$File = str_replace("\"","",$OrigFile);
				$File = str_replace("\'","",$File);

				// Check the Type
				$TypeArray = explode(".",$File);
				switch ($TypeArray[count($TypeArray)-1]) {
					case 'gif':  $Type = "image/gif";  break;
					case 'jpg':  $Type = "image/jpeg"; break;
					case 'jpeg': $Type = "image/jpeg"; break;
					case 'jpe':  $Type = "image/jpeg"; break;
					case 'png':  $Type = "image/png";  break;
					case 'bmp':  $Type = "image/bmp";  break;
				}

				// Create the ContentId
				$Id = "3D\"cid:".base64_encode($File)."@".$this->serverAddress."\"";

				// DEBUG //
				if ($this->debug) {
					print("\n\n<pre>Position: <b>".$SrcStart."</b> to <b>".$SrcEnd."</b> - ");
					print("Set <b>".$File."</b> to <b>".$Id."</b></pre>\n\n");
				}
				// END DEBUG //

				// Check the Offset between Old and New String and reset $Close
				$OLength = strlen($OrigFile);
				$NLength = strlen($Id);
				if ($OLength > $NLength) {
					$OldPos = $SrcEnd - ($OLength - $NLength);
				} else if ($OLength == $NLength) {
					$OldPos = $SrcEnd;
				} else {
					$OldPos = $SrcEnd + ($NLength - $OLength);
				}

				// Reset the String
				$this->Content = substr_replace($this->mailContent,$Id,($SrcStart + 4),($SrcEnd - $SrcStart - 4));

				// Add the File to the Attachement-Array
				array_push($this->mailAttachments,$File);
				array_push($this->mailAttachments,$Type);

			} while (true);
		}*/
	}

	/**
	 * Authenticate against a POP-Server before sending an Email over a SMTP-Server
	 *
	 * @param string $pServer
	 * @param int $pPort
	 * @param string $pUser
	 * @param string $pPasswd
	 * @return boolean State of authentication
	 */
	private function doAuthPOPbeforeSMTP($pServer, $pPort, $pUser, $pPasswd) {
		// DEBUG
		$this->showDebug("<br /><b><u>POP-before-SMTP: BEGIN</u></b><br />");

		// Set main Status and initiate connection with the POP server
		$popStat = true;
		$this->POPhandle = @fsockopen($pServer, $pPort);
		$this->showDebug(' - Connect to: '.$pServer.' on Port '.$pPort.'<br>');

		// Check for correct response if false, end
		if (!$this->POPhandle) {
			$popStat = false;
			$this->showDebug(" - <b>FAILED to Connect to the POP-Server</b><br />");
		} else {
			// Get banner (+OK)
			$s = fgets($this->POPhandle, 1024);
			$this->showDebug(' - needed state : +OK<br />');
			$this->showDebug(' - receive state: '.$s.'<br />');

			if (substr_count($s, '+OK') <= 0) {
				$popStat = false;
			}

			// Send UserName
			if ($popStat) {
				$this->showDebug(' - put command: USER '.$pUser.'<br />');
				$s = $this->sendPOPRequest('USER '.$pUser);
				
				$this->showDebug(' - needed state : +OK<br />');
				$this->showDebug(' - receive state: '.$s.'<br />');
				
				if (substr_count($s, '+OK') <= 0) {
					$popStat = false;
				}
			}
			
			// Send Password
			if ($popStat) {
				$this->showDebug(' - put command: PASS '.str_repeat('x', strlen($pPasswd)).'<br />');
				$s = $this->sendPOPRequest('PASS '.$pPasswd);
				
				$this->showDebug(' - needed state : +OK<br />');
				$this->showDebug(' - receive state: '.$s.'<br />');
				
				if (substr_count($s, '+OK') <= 0) {
					$popStat = false;
				}
			}
			
			// Send Logout
			if ($popStat) {
				$this->showDebug(' - put command: QUIT<br />');
				$s = $this->sendPOPRequest('QUIT');
				
				$this->showDebug(' - needed state : +OK<br />');
				$this->showDebug(' - receive state: '.$s.'<br />');
			} else {
				$this->showDebug(' - <b>POP-Authentication failed</b><br />');
			}
			
		}
		$this->showDebug("<b>POP-before-SMTP: END</b><br />");
		return $popStat;
	}
	
	/**
	 * Send a Command to the POP-Handle and retur the answere
	 *
	 * @param string $cmd Command to send
	 * @return string POP-Server answere
	 * @access private
	 */
	private function sendPOPRequest($cmd) {
		$back = null;
		if (is_resource($this->POPhandle)) {
			fputs($this->POPhandle, trim($cmd).self::CRLF);
			$back = fgets($this->POPhandle, 1024);
		}
		return $back;
	}
	
	/**
	 * Show a Debug-Message if debug is enabled
	 *
	 * @param string $msg Message to print out
	 * @access private
	 */
	private function showDebug($msg) {
		if ($this->debug) {
			echo $msg.self::CRLF;
			flush();
		}
	}
}
?>
