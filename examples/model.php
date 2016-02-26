<?php
/**
 * 这个文件主要演示如何将该框架与传统的Service/Model结合起来使用，并支持并行方法
 */
include __DIR__.'/../curl_future.php';

class BookModel{
	//接口串行调用的示例，通过then函数将处理过程串联起来
	static public function getTitleFuture($id){
		return curl_future("http://111.202.7.252/{$id}")
			->then(function($data){
				return strlen($data);
			})
			->then(function($data){
				$url = "http://111.202.7.252/{$data}";
				$html = curl_future($url)->fetch();
				preg_match('/title(.+?)\/title/is', $html, $matches);
				return $matches[1];
			});
	}
	
	//普通接口调用+后续处理的示例
	static public function getContentFuture($id){
		return curl_future("http://192.168.6.20/{$id}")
				->then(function($data){
					return substr($data, 0, 100);
				});
	}
}

//多个请求并行发出示例，这个地方用Model封装起来，便于和不同框架相结合
$t1 = BookModel::getTitleFuture('111');
$t2 = BookModel::getTitleFuture('222');
$t3 = BookModel::getTitleFuture('333');

$c1 = BookModel::getContentFuture('111');
$c2 = BookModel::getContentFuture('222');
$c3 = BookModel::getContentFuture('333');

//fetch函数会阻塞住，这个地方会把所有队列里面的请求发出，直到需要获取的t1的请求执行完再返回
var_dump($t1->fetch());
//由于上个fetch已经阻塞过了，下面的这个fetch很可能无需阻塞直接返回，也有可能上面的fetch没有执行完，此处阻塞住继续执行请求，直到拿到t2的数据
var_dump($t2->fetch());
var_dump($c3->fetch());