<?php

/**
 * HTTP Class
 *
 * This is a wrapper HTTP class that uses either cURL or fsockopen to
 * harvest resources from web. This can be used with scripts that need
 * a way to communicate with various APIs who support REST.
 *
 * @author      Md Emran Hasan <phpfour@gmail.com>
 * @package     HTTP Library
 * @copyright   2007-2008 Md Emran Hasan
 * @link        http://www.phpfour.com/lib/http
 * @since       Version 0.1
 */
class Http
{
	/**
	 * Contains the target URL
	 *
	 * @var string
	 */
	var $target;

	/**
	 * Contains the target host
	 *
	 * @var string
	 */
	var $host;

	/**
	 * Contains the target port
	 *
	 * @var integer
	 */
	var $port;

	/**
	 * Contains the target path
	 *
	 * @var string
	 */
	var $path;

	/**
	 * Contains the target schema
	 *
	 * @var string
	 */
	var $schema;

	/**
	 * Contains the http method (GET or POST)
	 *
	 * @var string
	 */
	var $method;

	/**
	 * Contains the parameters for request
	 *
	 * @var array
	 */
	var $params;

	/**
	 * Contains the cookies for request
	 *
	 * @var array
	 */
	var $cookies;

	/**
	 * Contains the cookies retrieved from response
	 *
	 * @var array
	 */
	var $_cookies;

	/**
	 * Number of seconds to timeout
	 *
	 * @var integer
	 */
	var $timeout;

	/**
	 * Whether to use cURL or not
	 *
	 * @var boolean
	 */
	var $useCurl;

	/**
	 * Contains the referrer URL
	 *
	 * @var string
	 */
	var $referrer;

	/**
	 * Contains the User agent string
	 *
	 * @var string
	 */
	var $userAgent;

	/**
	 * Contains the cookie path (to be used with cURL)
	 *
	 * @var string
	 */
	var $cookiePath;

	/**
	 * Whether to use cookie at all
	 *
	 * @var boolean
	 */
	var $useCookie;

	/**
	 * Whether to store cookie for subsequent requests
	 *
	 * @var boolean
	 */
	var $saveCookie;

	/**
	 * Contains the Username (for authentication)
	 *
	 * @var string
	 */
	var $username;

	/**
	 * Contains the Password (for authentication)
	 *
	 * @var string
	 */
	var $password;

	/**
	 * Contains the fetched web source
	 *
	 * @var string
	 */
	/**
	 * логин и пароль для платных прокси где требуется авторизация
	 */
	var $proxyLogin;
	var $proxyPassword;

	var $result;

	/**
	 * Contains the last headers
	 *
	 * @var string
	 */
	var $headers;

	/**
	 * Contains the last call's http status code
	 *
	 * @var string
	 */
	var $status;

	/**
	 * Whether to follow http redirect or not
	 *
	 * @var boolean
	 */
	var $redirect;

	/**
	 * The maximum number of redirect to follow
	 *
	 * @var integer
	 */
	var $maxRedirect;

	/**
	 * The current number of redirects
	 *
	 * @var integer
	 */
	var $curRedirect;

	/**
	 * Contains any error occurred
	 *
	 * @var string
	 */
	var $error;

	/**
	 * Store the next token
	 *
	 * @var string
	 */
	var $nextToken;

	/**
	 * Whether to keep debug messages
	 *
	 * @var boolean
	 */
	var $debug;

	/**
	 * Stores the debug messages
	 *
	 * @var array
	 * @todo will keep debug messages
	 */
	var $debugMsg;

	public $tor_using;
	public $is_proxy;
	public $is_secure;
	public $is_payProxy = false;
	public $proxy;
	public $http_code;
	public $n_exec;
	public $encoding;
	var $request_headers = array();

	public $use_index = 0;  // какой из пула адресов (могут быть разные учетки по получению прокси) использовать
	public $website_id = 0;  // используется для контроля, какой парсер использует прокси
	public $thread_id = 0;  // используется для контроля, какой парсер использует прокси
	public $proxyIP = '';   // используется для контроля, поиск прокси в БД, чтобы устанавливать активный он или нет
	public $proxyPort = ''; // используется для контроля, поиск прокси в БД, чтобы устанавливать активный он или нет
	public $use_limit = 900; // лимит ограничений на одновременно открытые соединения

	/**
	 * Constructor for initializing the class with default values.
	 *
	 * @return void
	 */
	function Http()
	{
		$this->clear();
	}

	/**
	 * Initialize preferences
	 *
	 * This function will take an associative array of config values and
	 * will initialize the class variables using them.
	 *
	 * Example use:
	 *
	 * <pre>
	 * $httpConfig['method']     = 'GET';
	 * $httpConfig['target']     = 'http://www.somedomain.com/index.html';
	 * $httpConfig['referrer']   = 'http://www.somedomain.com';
	 * $httpConfig['user_agent'] = 'My Crawler';
	 * $httpConfig['timeout']    = '30';
	 * $httpConfig['params']     = array('var1' => 'testvalue', 'var2' => 'somevalue');
	 *
	 * $http = new Http();
	 * $http->initialize($httpConfig);
	 * </pre>
	 *
	 * @param array Config values as associative array
	 * @return void
	 */
	function initialize($config = array())
	{
		$this->clear();
		foreach ($config as $key => $val) {
			if (isset($this->$key)) {
				$method = 'set' . ucfirst(str_replace('_', '', $key));

				if (method_exists($this, $method)) {
					$this->$method($val);
				} else {
					$this->$key = $val;
				}
			}
		}
	}

	/**
	 * Clear Everything
	 *
	 * Clears all the properties of the class and sets the object to
	 * the beginning state. Very handy if you are doing subsequent calls
	 * with different data.
	 *
	 * @return void
	 */
	function clear()
	{
		// Set the request defaults
		$this->host = '';
		$this->port = 0;
		$this->path = '';
		$this->target = '';
		$this->method = 'GET';
		$this->schema = 'http';
		$this->params = array();
		$this->headers = array();
		$this->cookies = array();
		$this->_cookies = array();

		// Set the config details
		$this->debug = FALSE;
		$this->error = '';
		$this->status = 0;
		$this->timeout = 15;
		$this->useCurl = TRUE;
		$this->referrer = '';
		$this->username = '';
		$this->password = '';
		$this->redirect = TRUE;

		$this->request_headers = array();

		// Set the cookie and agent defaults
		$this->nextToken = '';
		$this->useCookie = TRUE;
		$this->saveCookie = TRUE;
		$this->maxRedirect = 30;
		$this->cookiePath = ROOT_DIR . 'cookies/cookie.txt';
		$this->userAgent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.9';

		$this->tor_using = false;
		$this->is_proxy = false;
		$this->proxy = '';

		$this->proxyLogin = '';
		$this->proxyPassword = '';

		$this->use_index = 0;
		$this->website_id = 0;
		$this->thread_id = 0;
		$this->id_proxy = 0;
		$this->proxyIP = '';
		$this->proxyPort = '';
		$this->use_limit = 900;

	}

	/**
	 * Set target URL
	 *
	 * @param string URL of target resource
	 * @return void
	 */
	function setTarget($url)
	{
		if ($url) {
			$this->target = $url;
		}
	}

	/**
	 * Set http method
	 *
	 * @param string HTTP method to use (GET or POST)
	 * @return void
	 */
	function setMethod($method)
	{
		if ($method == 'GET' || $method == 'POST') {
			$this->method = $method;
		}
	}

	function setTorUsing($tor_using)
	{
		$this->tor_using = $tor_using;
	}

	function getTorUsing()
	{
		return $this->tor_using;
	}

	/**
	 * Set referrer URL
	 *
	 * @param string URL of referrer page
	 * @return void
	 */
	function setReferrer($referrer)
	{
		if ($referrer) {
			$this->referrer = $referrer;
		}
	}

	/**
	 * Get User agent string
	 *
	 * @param  Full user agent string
	 * @return string
	 */
	function getUseragent()
	{
		return $this->userAgent;
	}

	/**
	 * Set User agent string
	 *
	 * @param string Full user agent string
	 * @return void
	 */
	function setUseragent($agent)
	{
		if ($agent) {
			$this->userAgent = $agent;
		}
	}

	/**
	 * Set timeout of execution
	 *
	 * @param integer Timeout delay in seconds
	 * @return void
	 */
	function setTimeout($seconds)
	{
		if ($seconds > 0) {
			$this->timeout = $seconds;
		}
	}

	/**
	 * Set cookie path (cURL only)
	 *
	 * @param string File location of cookiejar
	 * @return void
	 */
	function setCookiepath($path)
	{
		if ($path) {
			$this->cookiePath = $path;
		}
	}

	/**
	 * Set request parameters
	 *
	 * @param array All the parameters for GET or POST
	 * @return void
	 */
	function setParams($dataArray)
	{
		if (is_array($dataArray)) {
			$this->params = array_merge($this->params, $dataArray);
		}
	}

	/**
	 * Set basic http authentication realm
	 *
	 * @param string Username for authentication
	 * @param string Password for authentication
	 * @return void
	 */
	function setAuth($username, $password)
	{
		if (!empty($username) && !empty($password)) {
			$this->username = $username;
			$this->password = $password;
		}
	}

	/**
	 * логин-пароль для платных прокси
	 *
	 * @param string Username for authentication
	 * @param string Password for authentication
	 * @return void
	 */
	function setProxyAuth($proxyLogin, $proxyPassword)
	{
		if (!empty($proxyLogin) && !empty($proxyPassword)) {
			$this->proxyLogin = $proxyLogin;
			$this->proxyPassword = $proxyPassword;
		}
	}

	/**
	 * Set maximum number of redirection to follow
	 *
	 * @param integer Maximum number of redirects
	 * @return void
	 */
	function setMaxredirect($value)
	{
		if (!empty($value)) {
			$this->maxRedirect = $value;
		}
	}

	/**
	 * Add request parameters
	 *
	 * @param string Name of the parameter
	 * @param string Value of the parameter
	 * @return void
	 */
	function addParam($name, $value)
	{
		if (!empty($name) && !empty($value)) {
			$this->params[$name] = $value;
		}
	}

	/**
	 * Add a cookie to the request
	 *
	 * @param string Name of cookie
	 * @param string Value of cookie
	 * @return void
	 */
	function addCookie($name, $value)
	{
		if (!empty($name) && !empty($value)) {
			$this->cookies[$name] = $value;
		}
	}

	/**
	 * Whether to use cURL or not
	 *
	 * @param boolean Whether to use cURL or not
	 * @return void
	 */
	function useCurl($value = TRUE)
	{
		if (is_bool($value)) {
			$this->useCurl = $value;
		}
	}

	/**
	 * Whether to use cookies or not
	 *
	 * @param boolean Whether to use cookies or not
	 * @return void
	 */
	function useCookie($value = TRUE)
	{
		if (is_bool($value)) {
			$this->useCookie = $value;
		}
	}

	/**
	 * Whether to save persistent cookies in subsequent calls
	 *
	 * @param boolean Whether to save persistent cookies or not
	 * @return void
	 */
	function saveCookie($value = TRUE)
	{
		if (is_bool($value)) {
			$this->saveCookie = $value;
		}
	}

	/**
	 * Whether to follow HTTP redirects
	 *
	 * @param boolean Whether to follow HTTP redirects or not
	 * @return void
	 */
	function followRedirects($value = TRUE)
	{
		if (is_bool($value)) {
			$this->redirect = $value;
		}
	}

	/**
	 * Get execution result body
	 *
	 * @return string output of execution
	 */
	function getResult()
	{
		return $this->result;
	}

	/**
	 * Get execution result headers
	 *
	 * @return array last headers of execution
	 */
	function getHeaders()
	{
		return $this->headers;
	}

	/**
	 * Get execution status code
	 *
	 * @return integer last http status code
	 */
	function getStatus()
	{
		return $this->status;
	}

	/**
	 * Get last execution error
	 *
	 * @return string last error message (if any)
	 */
	function getError()
	{
		return $this->error;
	}

	/**
	 * Execute a HTTP request
	 *
	 * Executes the http fetch using all the set properties. Intellegently
	 * switch to fsockopen if cURL is not present. And be smart to follow
	 * redirects (if asked so).
	 *
	 * @param string URL of the target page (optional)
	 * @param string URL of the referrer page (optional)
	 * @param string The http method (GET or POST) (optional)
	 * @param array Parameter array for GET or POST (optional)
	 * @return string Response body of the target page
	 */
	function execute($target = '', $referrer = '', $method = '', $data = array())
	{
		$this->error = '';
		$target = str_replace(' ', '%20', $target);
		// Populate the properties
		$this->target = ($target) ? $target : $this->target;
		$this->method = ($method) ? $method : $this->method;

		$this->referrer = ($referrer) ? $referrer : $this->referrer;

		// Add the new params
		if (is_array($data) && count($data) > 0) {
			$this->params = array_merge($this->params, $data);
		}

		// Process data, if presented
		if (is_array($this->params) && count($this->params) > 0) {
			// Get a blank slate
			$tempString = array();

			// Convert data array into a query string (ie animal=dog&sport=baseball)
			foreach ($this->params as $key => $value) {
				if (strlen(trim($value)) > 0) {
					$tempString[] = $key . "=" . urlencode($value);
				}
			}

			$queryString = join('&', $tempString);
		}

		// If cURL is not installed, we'll force fscokopen
		$this->useCurl = $this->useCurl && in_array('curl', get_loaded_extensions());

		// GET method configuration
		if ($this->method == 'GET') {
			if (isset($queryString)) {
				$this->target = $this->target . "?" . $queryString;
			}
		}

		// Parse target URL

		$urlParsed = parse_url($this->target);

		// Handle SSL connection request
		if (isset($urlParsed['scheme']) && $urlParsed['scheme'] == 'https') {
			$this->host = 'ssl://' . $urlParsed['host'];
			$this->port = ($this->port != 0) ? $this->port : 443;
		} else {
			$this->host = $urlParsed['host'];
			$this->port = ($this->port != 0) ? $this->port : 80;
		}

		// Finalize the target path
		$this->path = (isset($urlParsed['path']) ? $urlParsed['path'] : '/') . (isset($urlParsed['query']) ? '?' . $urlParsed['query'] : '');
		$this->schema = $urlParsed['scheme'];

		// Pass the requred cookies
		$this->_passCookies();

		$cookieString = '';
		// Process cookies, if requested
		if (is_array($this->cookies) && count($this->cookies) > 0) {
			// Get a blank slate
			$tempString = array();

			// Convert cookiesa array into a query string (ie animal=dog&sport=baseball)
			foreach ($this->cookies as $key => $value) {
				if (strlen(trim($value)) > 0) {
					$tempString[] = $key . "=" . urlencode($value);
				}
			}

			$cookieString = join('; ', $tempString);
		}
		// Do we need to use cURL
		// YES!!! Only!
		if ($this->useCurl) {
			//if ($this->is_proxy)  $this->setNewIdentity();
			$ch = $this->getCurl($queryString, $cookieString);
			if(!$this->is_proxy)
				$ne = 1;
			else
				$ne = empty($this->n_exec) ? 3 : $this->n_exec;

			$i=0;
			do{
				$ce_code = 100;
				$j=0;
				while ($ce_code != 0 && $j < 2) {
					$ch_copy = curl_copy_handle($ch);
					$content = curl_exec($ch_copy);
					$ce_code = curl_errno($ch_copy);
					$httpCode=$this->http_code = curl_getinfo($ch_copy, CURLINFO_HTTP_CODE);
					$ce_text = curl_error($ch_copy);
					curl_close($ch_copy);
					$j++;
					if ($ce_code != 0 || $httpCode==503) {
						echo "http_code: $httpCode curl_errno: $ce_code Attempt: $j\n";
						sleep(3);
					}
				}
				curl_close($ch);
				$ch = false;
				$this->clear_use_proxy_pay();
				$i++;

				if($ce_code)echo "\nCURL Error!\n" . $ce_text . "\nTarget: " . $this->target . "\nProxy: " . $this->proxy . "\n";
				//echo "\nHTTP code: $httpCode\ncurl error: $ce_text\n";

				if ($this->tor_using) {
					echo "\ntor: " . $this->setNewIdentity() . "\n";
					sleep(3);
				}

				$bi = $this->badIdentity($content,$ce_code,$httpCode);
				if ($this->is_proxy && $bi) {
					echo "Bad Identity - change proxy\n";
					$this->setNewIdentity();
					$this->clear_use_proxy_pay(false, 0);
					$ch = $this->getCurl($queryString, $cookieString);
				}else {
					break;
				}
			}while(($this->wrongContent($content,$ce_code,$httpCode) || $bi) && $i < $ne);

			if($ch !== false){
				curl_close($ch);
				$this->clear_use_proxy_pay();
			}

			// Store the error (if any)
			$this->_setError($ce_text);


			if ($httpCode == 404) {
				$this->_setError('404 Not Found');
			}

			$this->result = $content;

		} else {
			//akhinea
			//never use it
			// Get a file pointer
			$filePointer = fsockopen($this->host, $this->port, $errorNumber, $errorString, $this->timeout);

			// We have an error if pointer is not there
			if (!$filePointer) {
				$this->_setError('Failed opening http socket connection: ' . $errorString . ' (' . $errorNumber . ')');
				return FALSE;
			}

			// Set http headers with host, user-agent and content type
			$requestHeader = $this->method . " " . $this->path . "  HTTP/1.1\r\n";
			$requestHeader .= "Host: " . $urlParsed['host'] . "\r\n";
			$requestHeader .= "User-Agent: " . $this->userAgent . "\r\n";
			$requestHeader .= "Content-Type: application/x-www-form-urlencoded\r\n";

			// Specify the custom cookies
			if ($this->useCookie && $cookieString != '') {
				$requestHeader .= "Cookie: " . $cookieString . "\r\n";
			}

//var_dump($cookieString);

			// POST method configuration
			if ($this->method == "POST") {
				$requestHeader .= "Content-Length: " . strlen($queryString) . "\r\n";
			}

			// Specify the referrer
			if ($this->referrer != '') {
				$requestHeader .= "Referer: " . $this->referrer . "\r\n";
			}

			// Specify http authentication (basic)
			if ($this->username && $this->password) {
				$requestHeader .= "Authorization: Basic " . base64_encode($this->username . ':' . $this->password) . "\r\n";
			}

			$requestHeader .= "Connection: close\r\n\r\n";

			// POST method configuration
			if ($this->method == "POST") {
				$requestHeader .= $queryString;
			}

			// We're ready to launch
			fwrite($filePointer, $requestHeader);

			// Clean the slate
			$responseHeader = '';
			$responseContent = '';

			// 3...2...1...Launch !
			do {
				/**
				 * TODO: здесь скрипт зависает
				 */
				$responseHeader .= fread($filePointer, 1);
			} while (!preg_match('/\\r\\n\\r\\n$/', $responseHeader));

			// Parse the headers
			$this->_parseHeaders($responseHeader);

			// Do we have a 301/302 redirect ?
			if (($this->status == '301' || $this->status == '302') && $this->redirect == TRUE) {
				if ($this->curRedirect < $this->maxRedirect) {
					// Let's find out the new redirect URL
					$newUrlParsed = parse_url($this->headers['location']);

					if ($newUrlParsed['host']) {
						$newTarget = $this->headers['location'];
					} else {
						$newTarget = $this->schema . '://' . $this->host . '/' . $this->headers['location'];
					}

					// Reset some of the properties
					$this->port = 0;
					$this->status = 0;
					$this->params = array();
					$this->method = 'GET';
					$this->referrer = $this->target;

					// Increase the redirect counter
					$this->curRedirect++;

					// Let's go, go, go !
					$this->result = $this->execute($newTarget);
				} else {
					$this->_setError('Too many redirects.');
					return FALSE;
				}
			} else {
				// Nope...so lets get the rest of the contents (non-chunked)
				if (empty($this->headers['transfer-encoding'])) $this->headers['transfer-encoding'] = '';
				if ($this->headers['transfer-encoding'] != 'chunked') {
					while (!feof($filePointer)) {
						$responseContent .= fgets($filePointer, 128);
					}
				} else {
					// Get the contents (chunked)
					while ($chunkLength = hexdec(fgets($filePointer))) {
						$responseContentChunk = '';
						$readLength = 0;
						$j = 0;
						var_dump($chunkLength);
						while ($readLength < $chunkLength) {
							$responseContentChunk .= fread($filePointer, $chunkLength - $readLength);
							$readLength = strlen($responseContentChunk);
							$j++;
							if ($j > 1000) {
								throw new Exception('Error in $responseContentChunk');
							}
						}

						$responseContent .= $responseContentChunk;
						fgets($filePointer);
					}
				}

				// Store the target contents
				$this->result = chop($responseContent);
			}
		}

		$this->params = array();
		// There it is! We have it!! Return to base !!!
		return $this->result;
	}

function badIdentity($content,$ce_code,$httpCode){
	$blocked_sign = [
		'бнаружена подозрительная активность',
		'много запросов от вашего IP адреса',
		'зафиксирована подозрительная активность',
		'Доступ з Вашої IP-адреси обмежений',
	];
	
	if(!empty($blocked_sign) && is_array($blocked_sign))
		foreach ($blocked_sign as $bs) {
			if(mb_strpos($content, mb_convert_encoding($bs, 'UTF-8','cp1251'), 0, 'UTF-8')!==false 
				|| strpos($content, $bs) !== false ){
				echo "\nBad Identity\n";
				return true;
			}
		}

	if(
		$ce_code!==0 ||
		mb_strpos($content, iconv('cp1251', 'UTF-8', 'много запросов от вашего IP адреса.'), null, 'UTF-8') !== false ||
		strpos($content, 'много запросов от вашего IP адреса.') !== false 
		|| $httpCode===429
		|| $httpCode===404
	)
		return true;
	else
		return false;
}

function wrongContent($content,$ce_code,$httpCode){
	if (
		$ce_code !== 0 ||
		$httpCode >=400 ||
		$content === false ||
		strpos($content, '404 Not Found') !== false ||
		strpos($content, 'Internal Server Error') !== false ||
		strpos($content, '502 Bad Gateway') !== false ||
		strpos($content, '500 Internal Privoxy Error') !== false ||
		mb_strpos($content, iconv('cp1251', 'UTF-8', 'много запросов от вашего IP адреса.'), null, 'UTF-8') !== false ||
		strpos($content, 'много запросов от вашего IP адреса.') !== false 
	)
		return true;
	else 
		return false; 
}

	protected function getCurl($queryString, $cookieString)
	{
		// всегда использовать платные прокси
		//$this->is_payProxy = false;

		$ch = curl_init();
		// GET method configuration
		if ($this->method == 'GET') {
			curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
			curl_setopt($ch, CURLOPT_POST, FALSE);
		} // POST method configuration
		else {
			if (isset($queryString)) {
				curl_setopt($ch, CURLOPT_POSTFIELDS, $queryString);
			}

			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_HTTPGET, FALSE);
		}

		// Basic Authentication configuration
		if ($this->username && $this->password) {
			curl_setopt($ch, CURLOPT_USERPWD, $this->username . ':' . $this->password);
		}

		curl_setopt($ch, CURLOPT_HEADER, FALSE); // No need of headers
		curl_setopt($ch, CURLOPT_NOBODY, FALSE); // Return body

		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->request_headers);

		curl_setopt($ch, CURLOPT_COOKIESESSION, false);
		//curl_setopt($ch, CURLOPT_ENCODING,       'gzip,deflate');

		// Custom cookie configuration
		// Dont use CURLOPT_COOKIE if you want curl auto update cookie's value base on server response.
		// bcuz request cookie will hardcoded to the value of CURLOPT_COOKIE. CURLOPT_COOKIEFILE and
		// CURLOPT_COOKIEJAR will be ignored on this case.
		if ($this->useCookie && !empty($cookieString)) {
			curl_setopt($ch, CURLOPT_COOKIE, $cookieString);
		} else {
			if ($this->cookiePath) {
				curl_setopt($ch, CURLOPT_COOKIESESSION, false);
				curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookiePath); // Cookie management.
				curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookiePath); // Cookie management.
			} else {
				curl_setopt($ch, CURLOPT_COOKIESESSION, TRUE);
				curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookiePath); // Cookie management.
			}
		}

		//curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout); // Timeout

		if (defined('CURLOPT_TIMEOUT_MS')) {
			// Required for CURLOPT_TIMEOUT_MS to play nice on most Unix/Linux systems
			// http://www.php.net/manual/en/function.curl-setopt.php#104597
			curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT_MS, $this->timeout * 1000);
		} else {
			curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
		}


//debug str
		// if (defined('CURLOPT_TIMEOUT_MS')){
		// 	echo "\n\n".__FILE__.' CURLOPT_TIMEOUT_MS is defined'."\n\n";
		// }
		// echo "\n\n".__FILE__." Timeout: ".$this->timeout."\n\n";
		// if (defined('CURLOPT_CONNECTTIMEOUT_MS')){
		// 	echo "\n\n".__FILE__.' CURLOPT_CONNECTTIMEOUT_MS is defined. Redir: '.$this->redirect." maxredir:".$this->maxRedirect."\n\n";
		// }

//end debugstrr

		if (defined('CURLOPT_CONNECTTIMEOUT_MS')) {
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 15 * 1000); //was 6000
		} else {
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
		}
		// TRUE для принудительного закрытия соединения после завершения его обработки так,
		// чтобы его нельзя было использовать повторно.
		curl_setopt($ch, CURLOPT_FORBID_REUSE, true);

		// TRUE для принудительного использования нового соединения вместо закэшированного.
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);

		curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent); // Webbot name
		curl_setopt($ch, CURLOPT_URL, $this->target); // Target site
		curl_setopt($ch, CURLOPT_REFERER, $this->referrer); // Referer value

		curl_setopt($ch, CURLOPT_VERBOSE, FALSE); // Minimize logs
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // No certificate
		@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $this->redirect); // Follow redirects
		curl_setopt($ch, CURLOPT_MAXREDIRS, $this->maxRedirect); // Limit redirections to four
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); // Return in string
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);

		curl_setopt($ch, CURLOPT_ENCODING, $this->http->encoding);

		if ($this->tor_using) {
			curl_setopt($ch, CURLOPT_PROXY, PROXY_SOCKS5_TOR);
			curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
		} elseif ($this->is_proxy) {
			if(!$this->is_payProxy) {
				if ($this->proxy == '') {
					$this->proxy = $this->get_proxy();
				}
				curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
			} else {
				// ограничение в 900 потоков
				$this->proxy = '';
				$proxy = $this->get_proxy_pay();
				if($proxy) {
					echo 'get_proxy_pay: ' . $proxy['ip'] . ':' . $proxy['port'] . "\n";
					curl_setopt($ch, CURLOPT_PROXY, $proxy['ip'] . ':' . $proxy['port']);
					if (!empty($proxy['proxy_password'])) {
						curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxy['proxy_login'] . ':' . $proxy['proxy_password']);
					}
				}else{
					echo "pay proxy is disabled\n";
				}
			}
			//curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
		}
		return $ch;
	}


	public function get_proxy()
	{
		//var_dump($this);
		global $db;
		$date_last_check = date("Y-m-d H:i:s", time() - (60 * 60 * 12));
		if (empty($this->is_secure)) {
			$result = $db->super_query("SELECT ip, port FROM proxy WHERE
			date_last_check > '{$date_last_check}' AND
			alive = '1' AND
			anonymous = '1'
			ORDER by RAND()
			");
		} else {
			$result = $db->super_query("SELECT ip, port FROM proxy WHERE
			date_last_check > '{$date_last_check}' AND
			alive = '1' AND
			anonymous = '1' AND
			secure = '1'
			ORDER by RAND()
			");
			echo "\n\nSECURE PROXY SELECTED\n";
		}

		var_dump($result['ip'] . ':' . $result['port']);
		return $result['ip'] . ':' . $result['port'];
	}

	public function get_proxy_pay()
	{
		global $db;

		if($this->website_id == 0){
			echo "ERROR. the value website_id is required for the parameter proxy pay = true\n";
			return false;
		}

		$q = $db->super_query("SELECT COUNT(id) AS cnt FROM `proxy_pay` WHERE website_id>0 AND use_index={$this->use_index}");
		if (!empty($q['cnt']) AND $q['cnt']>$this->use_limit) {
			echo "ERROR. It is not allowed to use more than 450 proxies at a time\n";
			return false;
		}

		echo "COUNT USED PROXY (use_index={$this->use_index}; use_limit={$this->use_limit}) - {$q['cnt']}\n";

		//$q = $db->super_query("SELECT * FROM `proxy_pay` WHERE website_id=0 AND alive=1 AND use_index={$this->use_index} ORDER BY `proxy_pay`.`time_used` ASC LIMIT 0,1");
		$q = $db->super_query("SELECT `proxy_pay`.* FROM `proxy_pay` LEFT JOIN `proxy_pay_bad` ON `proxy_pay`.`id`=`proxy_pay_bad`.`id_proxy` AND `proxy_pay_bad`.`id_parser`={$this->website_id} WHERE website_id=0 AND use_index={$this->use_index} AND `proxy_pay_bad`.`id` IS NULL ORDER BY `proxy_pay`.`time_used` ASC LIMIT 0,1");
		if (!empty($q['id'])) {
			$theTime = array_sum( explode( ' ' , microtime() ) )*1000;
			$theTime = str_replace(',', '.',$theTime);
			$ms = explode('.',$theTime);

			$sql = "UPDATE `proxy_pay` SET website_id={$this->website_id}, thread_id={$this->thread_id},time_used='{$ms[0]}' WHERE id=" . $q['id'];
			$db->query($sql);
			$this->id_proxy = $q['id'];
			$this->proxyIP = $q['ip'];
			$this->proxyPort = $q['port'];
			return ['id' => $q['id'], 'ip' => $q['ip'], 'port' => $q['port'], 'proxy_login' => $q['login'], 'proxy_password' => $q['password']];
		}
		return false;
	}

	public function clear_use_proxy_pay($proxy = false, $alive = 1)
	{
		if(empty($this->proxyIP) && $proxy === false) {
			//echo "proxyIP is empty\n";
			return false;
		}
		global $db;
		$db->query("DELETE FROM `proxy_pay_bad` WHERE `dt` < '".date('Y-m-d H:i:s', time()-3600)."'"); // 3600 - година

		if($proxy === false) {
			$sql = "UPDATE `proxy_pay` SET website_id=0, thread_id=0, alive={$alive} WHERE ip='{$this->proxyIP}' AND port='{$this->proxyPort}'";
		}else{
			$sql = "UPDATE `proxy_pay` SET website_id=0, thread_id=0, alive={$alive} WHERE ip='{$proxy['ip']}' AND port='{$proxy['port']}'";
		}
		echo 'clear_use_proxy_pay: ' . $sql . "\n";
		$db->query($sql);
		if($alive != 1) {
			if ($proxy === false) {
				if($this->id_proxy > 0) {
					$sql = "INSERT INTO `realty_parser`.`proxy_pay_bad` (`id`, `id_proxy`, `id_parser`) VALUES (NULL, {$this->id_proxy}, {$this->website_id})";
					$db->query($sql);
				}
			} else {
				if($proxy['id'] > 0) {
					$sql = "INSERT INTO `realty_parser`.`proxy_pay_bad` (`id`, `id_proxy`, `id_parser`) VALUES (NULL, {$proxy['id']}, {$this->website_id})";
					$db->query($sql);
				}
			}
		}
		return true;
	}

	function debug_backtrace()
	{
		if (!function_exists('debug_backtrace')) {
			echo 'function debug_backtrace does not exists' . "\r\n";
			return;
		}
		//echo '<pre>';
		echo "\r\n" . '----------------' . "\r\n";
		echo 'Debug backtrace:' . "\r\n";
		echo '----------------' . "\r\n";
		foreach (debug_backtrace() as $t) {
			echo "\t" . '@ ';
			if (isset($t['file'])) echo basename($t['file']) . ':' . $t['line'];
			else {
				// if file was not set, I assumed the functioncall
				// was from PHP compiled source (ie XML-callbacks).
				echo '<PHP inner-code>';
			}

			echo ' -- ';

			if (isset($t['class'])) echo $t['class'] . $t['type'];

			echo $t['function'];

			if (isset($t['args']) && sizeof($t['args']) > 0) echo '(...)';
			else echo '()';

			echo "\r\n";
		}
	}

	public function setNewIdentityTor()
	{
		$this->debug_backtrace();
		$tor_config = explode(':', PROXY_CONTROL_TOR);
		$fp = fsockopen($tor_config[0], $tor_config[1]);
		if (!$fp) {
			return 2;
		}

		fputs($fp, "AUTHENTICATE \"" . PROXY_TOR_PASSWORD . "\"\r\n");
		$response = fread($fp, 1024);
		list($code, $text) = explode(' ', $response, 2);
		var_dump($code);
		if ($code != '250') {
			return 3;
		}

		fputs($fp, "signal NEWNYM\r\n");
		$response = fread($fp, 1024);
		list($code, $text) = explode(' ', $response, 2);
		var_dump($code);
		if ($code != '250') {
			return 4;
		}
		fclose($fp);

		//$this->setNewUserAgent();
	}

	public function setNewIdentity()
	{
		if ($this->tor_using) {
			echo "\n" . 'setNewIdentity TOR at ' . $this->target . "\n";

			// Если в это время уже меняется IP то ждем удаления torNewIdentity
			$is_torNewIdentity = false;
			while (mem::get('torNewIdentity')) {
				sleep(1);
				$is_torNewIdentity = true;
			}

			// был IP сменен.
			if ($is_torNewIdentity) {
				return true;
			}

			// сейчас будем менять IP
			mem::set('torNewIdentity', 1, 0, 8);
			$this->setNewIdentityTor();
			sleep(1);
			// сменили IP
			mem::del('torNewIdentity');
			/**
			 *
			 */
		} elseif ($this->is_proxy) {
			if(!$this->is_payProxy) {
				echo "\n" . 'setNewIdentity PROXY at ' . $this->target . "\n";
				$this->proxy = $this->get_proxy();
			}
		} else {
			echo "\n" . 'setNewIdentity NONE at ' . $this->target . "\n";
		}

		return 1;
	}

	/**
	 * set useragent
	 */
	private function setNewUserAgent()
	{
		//list of browsers
		$agentBrowser = array(
			'Firefox',
			'Safari',
			'Opera',
			'Flock',
			'Internet Explorer',
			'Seamonkey',
			'Konqueror',
			'GoogleBot'
		);
		//list of operating systems
		$agentOS = array(
			'Windows 3.1',
			'Windows 95',
			'Windows 98',
			'Windows 2000',
			'Windows NT',
			'Windows XP',
			'Windows Vista',
			'Redhat Linux',
			'Ubuntu',
			'Fedora',
			'AmigaOS',
			'OS 10.5'
		);

		//randomly generate UserAgent
		$this->userAgent = $agentBrowser[rand(0, 7)] . '/' . rand(1, 8) . '.' . rand(0, 9) . ' (' . $agentOS[rand(0, 11)] . ' ' . rand(1, 7) . '.' . rand(0, 9) . '; en-US;)';
	}

	/**
	 * Parse Headers (internal)
	 *
	 * Parse the response headers and store them for finding the resposne
	 * status, redirection location, cookies, etc.
	 *
	 * @param string Raw header response
	 * @return void
	 * @access private
	 */
	function _parseHeaders($responseHeader)
	{
		// Break up the headers
		$headers = explode("\r\n", $responseHeader);

		// Clear the header array
		$this->_clearHeaders();

		// Get resposne status
		if ($this->status == 0) {
			// Oooops !
			if (!preg_match($match = "#^http/[0-9]+\\.[0-9]+[ \t]+([0-9]+)[ \t]*(.*)\$#si", $headers[0], $matches)) {
				$this->_setError('Unexpected HTTP response status');
				return FALSE;
			}

			// Gotcha!
			$this->status = $matches[1];
			array_shift($headers);
		}

		// Prepare all the other headers
		foreach ($headers as $header) {
			// Get name and value
			$headerName = strtolower($this->_tokenize($header, ':'));
			$headerValue = trim(chop($this->_tokenize("\r\n")));

			// If its already there, then add as an array. Otherwise, just keep there
			if (isset($this->headers[$headerName])) {
				if (gettype($this->headers[$headerName]) == "string") {
					$this->headers[$headerName] = array($this->headers[$headerName]);
				}

				$this->headers[$headerName][] = $headerValue;
			} else {
				$this->headers[$headerName] = $headerValue;
			}
		}

		// Save cookies if asked
		if ($this->saveCookie && isset($this->headers['set-cookie'])) {
			$this->_parseCookie();
		}
	}

	/**
	 * Clear the headers array (internal)
	 *
	 * @return void
	 * @access private
	 */
	function _clearHeaders()
	{
		$this->headers = array();
	}

	/**
	 * Parse Cookies (internal)
	 *
	 * Parse the set-cookie headers from response and add them for inclusion.
	 *
	 * @return void
	 * @access private
	 */
	function _parseCookie()
	{
		// Get the cookie header as array
		if (gettype($this->headers['set-cookie']) == "array") {
			$cookieHeaders = $this->headers['set-cookie'];
		} else {
			$cookieHeaders = array($this->headers['set-cookie']);
		}

		// Loop through the cookies
		for ($cookie = 0; $cookie < count($cookieHeaders); $cookie++) {
			$cookieName = trim($this->_tokenize($cookieHeaders[$cookie], "="));
			$cookieValue = $this->_tokenize(";");

			$urlParsed = parse_url($this->target);

			$domain = $urlParsed['host'];
			$secure = '0';

			$path = "/";
			$expires = "";

			while (($name = trim(urldecode($this->_tokenize("=")))) != "") {
				$value = urldecode($this->_tokenize(";"));

				switch ($name) {
					case "path"     :
						$path = $value;
						break;
					case "domain"   :
						$domain = $value;
						break;
					case "secure"   :
						$secure = ($value != '') ? '1' : '0';
						break;
				}
			}

			$this->_setCookie($cookieName, $cookieValue, $expires, $path, $domain, $secure);
		}
	}

	/**
	 * Set cookie (internal)
	 *
	 * Populate the internal _cookies array for future inclusion in
	 * subsequent requests. This actually validates and then populates
	 * the object properties with a dimensional entry for cookie.
	 *
	 * @param string Cookie name
	 * @param string Cookie value
	 * @param string Cookie expire date
	 * @param string Cookie path
	 * @param string Cookie domain
	 * @param string Cookie security (0 = non-secure, 1 = secure)
	 * @return void
	 * @access private
	 */
	function _setCookie($name, $value, $expires = "", $path = "/", $domain = "", $secure = 0)
	{
		if (strlen($name) == 0) {
			return;
			return ($this->_setError("No valid cookie name was specified."));
		}

		if (strlen($path) == 0 || strcmp($path[0], "/")) {
			return ($this->_setError("$path is not a valid path for setting cookie $name."));
		}

		if ($domain == "" || !strpos($domain, ".", $domain[0] == "." ? 1 : 0)) {
			return ($this->_setError("$domain is not a valid domain for setting cookie $name."));
		}

		$domain = strtolower($domain);

		if (!strcmp($domain[0], ".")) {
			$domain = substr($domain, 1);
		}

		$name = $this->_encodeCookie($name, true);
		$value = $this->_encodeCookie($value, false);

		$secure = intval($secure);

		$this->_cookies[] = array("name" => $name,
			"value" => $value,
			"domain" => $domain,
			"path" => $path,
			"expires" => $expires,
			"secure" => $secure
		);
	}

	/**
	 * Encode cookie name/value (internal)
	 *
	 * @param string Value of cookie to encode
	 * @param string Name of cookie to encode
	 * @return string encoded string
	 * @access private
	 */
	function _encodeCookie($value, $name)
	{
		return ($name ? str_replace("=", "%25", $value) : str_replace(";", "%3B", $value));
	}

	/**
	 * Pass Cookies (internal)
	 *
	 * Get the cookies which are valid for the current request. Checks
	 * domain and path to decide the return.
	 *
	 * @return void
	 * @access private
	 */
	function _passCookies()
	{
		if (is_array($this->_cookies) && count($this->_cookies) > 0) {
			$urlParsed = parse_url($this->target);
			$tempCookies = array();

			foreach ($this->_cookies as $cookie) {
				if ($this->_domainMatch($urlParsed['host'], $cookie['domain']) && (0 === strpos($urlParsed['path'], $cookie['path']))
					&& (empty($cookie['secure']) || $urlParsed['protocol'] == 'https')
				) {
					$tempCookies[$cookie['name']][strlen($cookie['path'])] = $cookie['value'];
				}
			}

			// cookies with longer paths go first
			foreach ($tempCookies as $name => $values) {
				krsort($values);
				foreach ($values as $value) {
					$this->addCookie($name, $value);
				}
			}
		}
	}

	/**
	 * Checks if cookie domain matches a request host (internal)
	 *
	 * Cookie domain can begin with a dot, it also must contain at least
	 * two dots.
	 *
	 * @param string Request host
	 * @param string Cookie domain
	 * @return bool Match success
	 * @access private
	 */
	function _domainMatch($requestHost, $cookieDomain)
	{
		if ('.' != $cookieDomain{0}) {
			return $requestHost == $cookieDomain;
		} elseif (substr_count($cookieDomain, '.') < 2) {
			return false;
		} else {
			return substr('.' . $requestHost, -strlen($cookieDomain)) == $cookieDomain;
		}
	}

	/**
	 * Tokenize String (internal)
	 *
	 * Tokenize string for various internal usage. Omit the second parameter
	 * to tokenize the previous string that was provided in the prior call to
	 * the function.
	 *
	 * @param string The string to tokenize
	 * @param string The seperator to use
	 * @return string Tokenized string
	 * @access private
	 */
	function _tokenize($string, $separator = '')
	{
		if (!strcmp($separator, '')) {
			$separator = $string;
			$string = $this->nextToken;
		}

		for ($character = 0; $character < strlen($separator); $character++) {
			if (gettype($position = strpos($string, $separator[$character])) == "integer") {
				$found = (isset($found) ? min($found, $position) : $position);
			}
		}

		if (isset($found)) {
			$this->nextToken = substr($string, $found + 1);
			return (substr($string, 0, $found));
		} else {
			$this->nextToken = '';
			return ($string);
		}
	}

	/**
	 * Set error message (internal)
	 *
	 * @param string Error message
	 * @return string Error message
	 * @access private
	 */
	function _setError($error)
	{
		if ($error != '') {
			$this->error = $error;
			return $error;
		}
	}
}

?>