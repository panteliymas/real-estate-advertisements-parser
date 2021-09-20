<?php

if(!defined('DATALIFEENGINE')) {
  die("Hacking attempt!");
}

if ( extension_loaded('mysqli') ) {
	include_once(ROOT_DIR . "core/mysqli.class.php" );
} else {
	include_once(ROOT_DIR . "core/mysql.class.php" );
}
