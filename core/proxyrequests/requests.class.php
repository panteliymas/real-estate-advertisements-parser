<?php

/**
 * @author Luka Pušić <luka@pusic.si>
 * proxyRequests can send loads of get requests through different proxys and user agents with ease
 */
class requests extends Core
{
	function __construct()
	{
		/**
		 * default options
		 */
		$this->postdata = false;
		$this->useragent = 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)'; //default uagent
		$this->uagents_list = false; //user agents file
		$this->gen_uagents = false;; //choose to generate uagents on the fly
		$this->cookiefile = 'cookies.txt';
		$this->strict_uniq = false; //if true and count > proxies, throw error. if set to false, use one proxy as many times as needed
		$this->extra_headers = array(); //extra http headers to send (those not in curl options)
		$this->multi_limit = 1020; // more paralell requests are not recommended
	}

	/**
	 * Function getMulti uses paralell requests curl_multi
	 * @param type $url
	 * @param type $count
	 */
	public function get($url, $count)
	{
		$result = array();
		if ($count < 1) {
			return $result;
		}

		$limit = $this->multi_limit;
		if ($count < $limit) {
			$limit = $count;
		}
		$passes = ceil($count / $limit);
		$offset = 0;

		for ($pass = 0; $pass < $passes; $pass++) {

			$ch = array();
			$master = curl_multi_init(); //create multi curl resource
			$proxies = array_slice($this->proxies, $limit * $pass, $limit);
			$offset = $pass * $limit;
			if (($pass != 0) && ($pass == $passes - 1)) {
				$limit = $count % ($pass * $limit);
			}

			$this->postdata = array(
				'time' => time(),
			);

			for ($i = 0; $i < $limit; $i++) {
				$ch[$i] = curl_init();
				curl_setopt($ch[$i], CURLOPT_URL, $url);
				curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch[$i], CURLOPT_USERAGENT, $this->useragent);
				curl_setopt($ch[$i], CURLOPT_HTTPHEADER, $this->extra_headers);
				curl_setopt($ch[$i], CURLOPT_FOLLOWLOCATION, 5);
				curl_setopt($ch[$i], CURLOPT_CONNECTTIMEOUT, 10);
				curl_setopt($ch[$i], CURLOPT_TIMEOUT, 10);
				if ($this->proxies) {
					curl_setopt($ch[$i], CURLOPT_PROXY, $proxies[$i]);
					//curl_setopt($ch[$i], CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
				}
				if ($this->postdata) {
					curl_setopt($ch[$i], CURLOPT_POST, true);
					curl_setopt($ch[$i], CURLOPT_POSTFIELDS, $this->postdata);
				}
				curl_multi_add_handle($master, $ch[$i]); //add the current curl handle to the master
			}
			$this->bad_proxies = array();
			$running = null;
			do {
				curl_multi_exec($master, $running); //while there are running connections just keep looping
			} while ($running > 0);

			for ($i = 0; $i < $limit; $i++) {

				if (curl_error($ch[$i])) {

					$data = array(
						'status' => false,
						'content' => curl_error($ch[$i])
					);
				} else {
					$content = curl_multi_getcontent($ch[$i]);
					$json = json_decode($content);
					var_dump($json);
					if(!empty($json)){
						echo "\n".($i + $offset) . ": " . var_export($json, true) . ": OK\n";
						$data = array(
							'status' => true,
							'content' => strip_tags(curl_multi_getcontent($ch[$i]))
						);
					}else{
						echo "\nWrong page\n";
						$data = array(
							'status' => false,
							'content' => 'wrong page'
						);
					}


				}
				$data['proxy'] = $proxies[$i];
				$result[] = $data;
			}
			curl_multi_close($master); //destory the multi curl resource
		}
		return $result;
	}

	/**
	 * Function getMulti uses paralell requests curl_multi
	 * @param type $url
	 * @param type $count
	 */
	public function get_https($url, $count)
	{
		$result = array();
		if ($count < 1) {
			return $result;
		}

		$limit = $this->multi_limit;
		if ($count < $limit) {
			$limit = $count;
		}
		$passes = ceil($count / $limit);
		$offset = 0;

		for ($pass = 0; $pass < $passes; $pass++) {

			$ch = array();
			$master = curl_multi_init(); //create multi curl resource
			$proxies = array_slice($this->proxies, $limit * $pass, $limit);
			$offset = $pass * $limit;
			if (($pass != 0) && ($pass == $passes - 1)) {
				$limit = $count % ($pass * $limit);
			}

			$this->postdata = array(
				'time' => time(),
			);

			for ($i = 0; $i < $limit; $i++) {
				$ch[$i] = curl_init();
				curl_setopt($ch[$i], CURLOPT_URL, "https://www.olx.ua");
				curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch[$i], CURLOPT_USERAGENT, $this->useragent);
				curl_setopt($ch[$i], CURLOPT_HTTPHEADER, $this->extra_headers);
				curl_setopt($ch[$i], CURLOPT_FOLLOWLOCATION, 5);
				curl_setopt($ch[$i], CURLOPT_CONNECTTIMEOUT, 30);
				curl_setopt($ch[$i], CURLOPT_TIMEOUT, 30);
				curl_setopt($ch[$i], CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch[$i], CURLOPT_SSL_VERIFYHOST, false);
				if ($this->proxies) {
					curl_setopt($ch[$i], CURLOPT_PROXY, $proxies[$i]);
					//curl_setopt($ch[$i], CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
				}

				curl_multi_add_handle($master, $ch[$i]); //add the current curl handle to the master
			}
			$this->bad_proxies = array();
			$running = null;
			do {
				curl_multi_exec($master, $running); //while there are running connections just keep looping
			} while ($running > 0);

			for ($i = 0; $i < $limit; $i++) {
				echo $proxies[$i]."\n\n";
				$html = curl_multi_getcontent($ch[$i]);
				if (curl_error($ch[$i]) || strpos($html, 'OLX.ua')===false) {
					echo "\n\n".$proxies[$i]." Curl_error: ".curl_error($ch[$i])."\n\n";
					echo "\n\n".mb_substr($html, 0,300)."\n\n";
					$data['status'] = false;
				} else {
					echo "\n\n".mb_substr($html, 0,300)."\n\n";
					echo "\n\n".$proxies[$i]." OK";
					$data['status']=true;
				}
				$data['proxy'] = $proxies[$i];
				$result[] = $data;
			}
			curl_multi_close($master); //destory the multi curl resource
		}
		return $result;
	}

	public function start($proxies)
	{
		$this->proxies = $proxies;
		echo '* Loaded ' . count($this->proxies) . " proxies\n";
		/*if ($this->uagents_list) {
			$this->uagents = file($this->uagents_list);
			echo '* Loaded ' . count($this->proxies) . " useragents\n";
		}*/
		$count = isset($this->count) ? $this->count : count($this->proxies);
		return $this->get($this->url, $count);
	}

	public function start_https($proxies)
	{
		$this->proxies = $proxies;
		echo '* Loaded for https check' . count($this->proxies) . " proxies\n";
		/*if ($this->uagents_list) {
			$this->uagents = file($this->uagents_list);
			echo '* Loaded ' . count($this->proxies) . " useragents\n";
		}*/
		$count = isset($this->count) ? $this->count : count($this->proxies);
		return $this->get_https($this->url, $count);
	}
}