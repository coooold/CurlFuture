<?php
include __DIR__.'/../curl_future.php';

function http_get($url){
	return curl_future($url)->fetch();
}

echo  strlen(http_get("http://s.newhua.com/2015/1113/304528.shtml?1"));
echo "\n";
echo  strlen(http_get("http://s.newhua.com/2015/1113/304528.shtml?2"));
echo "\n";
echo  strlen(http_get("http://s.newhua.com/2015/1113/304528.shtml?3"));
echo "\n";
echo  strlen(http_get("http://s.newhua.com/2015/1113/304528.shtml?4"));
echo "\n";
echo  strlen(http_get("http://s.newhua.com/2015/1113/304528.shtml?5"));
echo "\n";
