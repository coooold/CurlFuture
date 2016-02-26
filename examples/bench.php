<?php
include __DIR__.'/../curl_future.php';

function http_get($url){
	return curl_future($url)->fetch();
}

function http_get2($url){
	return file_get_contents($url);
}

function http_get3($url){
	$f1 = curl_future($url);
	$f2 = curl_future($url);
	$f3 = curl_future($url);
	$f4 = curl_future($url);
	$f5 = curl_future($url);
	
	return array(
		$f1->fetch(),
		$f2->fetch(),
		$f3->fetch(),
		$f4->fetch(),
		$f5->fetch(),
	);
}

$url = 'http://127.0.0.1/';

$s = microtime(true);
for($i=0;$i<200;$i++)http_get($url);
$t = intval((microtime(true) - $s)*1000);
echo "curl_future sync：$t ms\n";

$s = microtime(true);
for($i=0;$i<200;$i++)http_get2($url);
$t = intval((microtime(true) - $s)*1000);
echo "file_get_contents：$t ms\n";


$s = microtime(true);
for($i=0;$i<40;$i++)http_get2($url);
$t = intval((microtime(true) - $s)*1000);
echo "curl_futhre async：$t ms\n";
