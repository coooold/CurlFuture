<?php
include __DIR__.'/../curl_future.php';

$f1 = curl_future("http://s.newhua.com/2015/1113/304528.shtml?1");
$f2 = curl_future("http://s.newhua.com/2015/1113/304528.shtml?2");
$f3 = curl_future("http://s.newhua.com/2015/1113/304528.shtml?3");
$f4 = curl_future("http://s.newhua.com/2015/1113/304528.shtml?4");
$f5 = curl_future("http://s.newhua.com/2015/1113/304528.shtml?5");


echo strlen($f1->fetch());	//这个地方会并行执行
echo "\n";
echo strlen($f2->fetch());
echo "\n";
echo strlen($f3->fetch());
echo "\n";
echo strlen($f4->fetch());
echo "\n";
echo strlen($f5->fetch());
echo "\n";

