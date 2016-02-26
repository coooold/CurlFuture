<?php
include __DIR__.'/inc/future.php';
include __DIR__.'/inc/http_future.php';
include __DIR__.'/inc/task.php';
include __DIR__.'/inc/task_manager.php';

/**
 * 获得一个延迟执行curl的类
 * @param $url 请求url地址
 * @param $options = array(), 
 *		header:头信息(Array), 
 *		proxy_url, 
 *		timeout:超时时间，可以小于1
 *		post_data: string|array post数据
 * @return CurlFuture\HttpFuture
 * @author fang
 * @version 2015年11月25日09:45:05
 */
function curl_future($url, $options = array()){
	return new CurlFuture\HttpFuture($url, $options);
}