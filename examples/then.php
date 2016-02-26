<?php
include __DIR__.'/../curl_future.php';

echo curl_future("http://s.newhua.com/2015/1113/304528.shtml")
	->then(function($data){
		return strlen($data);
	})
	->then(function($len){
		return "Length: $len";
	})
	->fetch();