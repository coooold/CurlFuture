<?php
require_once "vendor/autoload.php";

use CurlFuture\HttpFuture; 

$uri = "http://www.baidu.com";
$oCurl = new HttpFuture($uri);

$oCurl = $oCurl->fetch();
