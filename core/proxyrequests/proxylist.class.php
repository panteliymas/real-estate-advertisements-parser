<?php

/**
 * @author Luka Pušić <luka@pusic.si>
 */
class proxylist {

    function __construct() {
        $this->proxylist = 'proxy.txt';
    }

    /**
     * Parse proxys from an online address (should be updated daily) 
     */
    function grab() {
        $content = file_get_contents('https://checkerproxy.net/api/archive/' . date('Y-m-d'));
        $ar1=json_decode($content,true);
        //$content = file_get_contents('http://checkerproxy.net/05-06-2014');
        //$content = file_get_contents('http://seprox.ru/ru/proxy_filter/0_0_0_0_0_0_0_0_0_0.html');
        //$content = file_get_contents('http://api.best-proxies.ru/feeds/proxylist.txt?key=uFCW2aXsCBn3qTvluwFO&type=http&unique=1&level=1&response=1500&limit=0');
        //$content = file_get_contents('http://api.best-proxies.ru/feeds/proxylist.txt?key=uFCW2aXsCBn3qTvluwFO&type=http&yandex=1&mailru=1&twitter=1&unique=1&google=1&level=1,2&limit=0');
        //$content = file_get_contents('http://api.best-proxies.ru/feeds/proxylist.txt?key=uFCW2aXsCBn3qTvluwFO&type=socks4&yandex=1&mailru=1&twitter=1&unique=1&google=1&level=1,2&limit=0');
        //$content = file_get_contents('http://api.best-proxies.ru/feeds/proxylist.txt?key=uFCW2aXsCBn3qTvluwFO&type=socks5&yandex=1&mailru=1&twitter=1&google=1&level=1,2&limit=0');
        //$content = file_get_contents('http://api.best-proxies.ru/feeds/proxylist.txt?key=uFCW2aXsCBn3qTvluwFO&type=http&yandex=1&mailru=1&twitter=1&google=1&level=1,2&limit=0');
//        preg_match_all('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\:[0-9]{1,5}/', $content, $match);
//		$proxies = array_unique($match[0]);
		return $ar1;

		/*
			# Write proxys to file
			$fh = fopen($this->proxylist, "w");
			for ($i = 0; $i < $count; $i++) {
				fwrite($fh, $match[0][$i] . "\n");
			}
			fclose($fh);
		*/
    }

    function grab2() {
        $ar1 = [];
        $content = file_get_contents('https://www.proxy-list.download/api/v1/get?type=https');
        file_put_contents('proxy_grab2.txt', $content);
        $handle = fopen("proxy_grab2.txt", "r");
        while (!feof($handle)) {
            $buffer = fgets($handle, 4096);
            $ar1[] = ['addr'=>$buffer, 'type'=>2, 'kind'=>2, 'timeout'=>100];
        }

        $content = file_get_contents('https://www.proxy-list.download/api/v1/get?type=http');
        file_put_contents('proxy_grab2.txt', $content);
        $handle = fopen("proxy_grab2.txt", "r");
        while (!feof($handle)) {
            $buffer = fgets($handle, 4096);
            $ar1[] = ['addr'=>$buffer, 'type'=>2, 'kind'=>2, 'timeout'=>100];
        }

        $content = file_get_contents('http://rootjazz.com/proxies/proxies.txt');
        file_put_contents('proxy_grab2.txt', $content);
        $handle = fopen("proxy_grab2.txt", "r");
        while (!feof($handle)) {
            $buffer = fgets($handle, 4096);
            $ar1[] = ['addr'=>$buffer, 'type'=>2, 'kind'=>2, 'timeout'=>100];
        }


        return $ar1;

    }

    function grab3()
    {
        $ar1 = [];
        $len = 1;
        $page = 0;

        while ($len > 0) {
            $url = "https://hidemy.name/ua/proxy-list/?type=hs4&anon=34&start=" . $page;
            $page += 64;
            echo " ========= page: $page\n";
            $ch = curl_init();

            if ($ch === false) {
                die('Failed to create curl object');
            }

            $timeout = 5;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru-RU; rv:1.7.12) Gecko/20050919 Firefox/1.0.7");
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);


            $data = curl_exec($ch);
            curl_close($ch);

            $dom = new DOMDocument();
            @$dom->loadHTML($data);
            $xpath = new DOMXPath($dom);


            $attribute_nodes = $xpath->evaluate("//table/tbody/tr");
            $len = $attribute_nodes->length;
            if ($len > 0) {
                foreach ($attribute_nodes as $attr) {
                    $attr_title = '';
                    $attr_title_nodes = $xpath->evaluate("./td[1]", $attr);
                    if ($attr_title_nodes->length > 0) {
                        $attr_title = $attr_title_nodes->item(0)->nodeValue;
                    }

                    $attr_value = '';
                    $attr_value_nodes = $xpath->evaluate("./td[2]", $attr);
                    if ($attr_value_nodes->length > 0) {
                        $attr_value = $attr_value_nodes->item(0)->nodeValue;
                    }
                    $ar1[] = ['addr'=>$attr_title.':'.$attr_value, 'type'=>2, 'kind'=>2, 'timeout'=>100];
                }
            }
        }
        return $ar1;
    }

    function grab4()
    {
        $ar1 = [];
        $url = "https://raw.githubusercontent.com/clarketm/proxy-list/master/proxy-list-raw.txt";
        $ch = curl_init();

        if ($ch === false) {
            die('Failed to create curl object');
        }

        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru-RU; rv:1.7.12) Gecko/20050919 Firefox/1.0.7");
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);


        $data = curl_exec($ch);
        curl_close($ch);

        file_put_contents('333.txt', $data);exit;

        $dom = new DOMDocument();
        @$dom->loadHTML($data);
        $xpath = new DOMXPath($dom);


        $attribute_nodes = $xpath->evaluate("//table/tbody/tr");
        $len = $attribute_nodes->length;
        if ($len > 0) {
            foreach ($attribute_nodes as $attr) {
                $attr_title = '';
                $attr_title_nodes = $xpath->evaluate("./td[1]", $attr);
                if ($attr_title_nodes->length > 0) {
                    $attr_title = $attr_title_nodes->item(0)->nodeValue;
                }

                $attr_value = '';
                $attr_value_nodes = $xpath->evaluate("./td[2]", $attr);
                if ($attr_value_nodes->length > 0) {
                    $attr_value = $attr_value_nodes->item(0)->nodeValue;
                }
                $ar1[] = ['addr' => $attr_title . ':' . $attr_value, 'type' => 2, 'kind' => 2, 'timeout' => 100];
            }
        }

        return $ar1;
    }

    function grab5()
    {
        $ar1 = [];
        $url = "https://free-proxy-list.net/anonymous-proxy.html";
        $ch = curl_init();

        if ($ch === false) {
            die('Failed to create curl object');
        }

        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru-RU; rv:1.7.12) Gecko/20050919 Firefox/1.0.7");
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);


        $data = curl_exec($ch);
        curl_close($ch);

        //file_put_contents('333.txt', $data);exit;

        $dom = new DOMDocument();
        @$dom->loadHTML($data);
        $xpath = new DOMXPath($dom);


        $attribute_nodes = $xpath->evaluate("//textarea");
        if ($attribute_nodes->length > 0) {
            $lst = $attribute_nodes->item(0)->nodeValue;
        }
        $matches = null;
        if($returnValue = preg_match_all('/[0-9]{1,3}\\.[0-9]{1,3}\\.[0-9]{1,3}\\.[0-9]{1,3}\\:[0-9]{1,5}/', $lst, $matches)){
            foreach($matches[0] as $el){
                $ar1[] = ['addr' => $el, 'type' => 2, 'kind' => 2, 'timeout' => 100];
            }
        }
        return $ar1;
    }

    function check() {
        # This URL has to always be online, use services like google, fb, yahoo...
        $url = 'http://m.google.com/robots.txt';

        $ch = new requests();
        $ch->url = $url;
        $ch->start();

        $proxies = file($this->proxylist);
        $fh = fopen("proxy.txt", "w");
        for ($i = 0; $i < sizeof($proxies); $i++) {
            if (!in_array($i, $ch->bad_proxies)) {
                fwrite($fh, $proxies[$i] . "\n");
            }
        }
        fclose($fh);

        echo '* Removed ' . sizeof($ch->bad_proxies) . " bad proxies!\n";
    }

}

?>