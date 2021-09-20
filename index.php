<?php

function die_me() {
	$size = memory_get_peak_usage(true) - MEM_START;
	$unit=array('b','kb','mb','gb','tb','pb');
	echo 'mem:' . @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
	die();
}

define('MEM_START' , memory_get_usage(true));

define ( 'ROOT_DIR', dirname ( __FILE__ ) . '/' );
include ROOT_DIR . '/core/Core.php';

function exception_handler($exception) {
	echo "exception: " , $exception->getMessage(), "\n";
}
//$_GET['q'] = 'slavyansk.biz';

set_exception_handler('exception_handler');

Router::route();

