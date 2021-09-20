<?php

define ("DBHOST", "localhost");
define ("DBNAME", "freelance");
define ("DBUSER", "root");
define ("DBPASS", "");  
define ("PREFIX", "dle");
define ("COLLATE", "cp1251");
$db = new db;

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
	define ("GOCR_PATCH", ROOT_DIR . '/core/gocr/gocr049.exe');
} else {
	define ("GOCR_PATCH", '/usr/local/bin/gocr');
}

define ("PROXY_SOCKS5_TOR", '127.0.0.1:9555');
define ("PROXY_CONTROL_TOR", '127.0.0.1:9051');

define ("PROXY_TOR_PASSWORD", 'Jymh9cwh');
