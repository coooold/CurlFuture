<?php
namespace CurlFuture;
/**
 * 异步HttpFuture，实现http请求的延迟执行
 * @author fang
 * @version 2015年11月25日09:45:05
 */
 class HttpFuture extends Future{
	 /**
	  * 构造方法，传入url和对应设置，目前仅支持get方法
	  * @autor fang
	  * @version 2015年11月27日17:34:56
	  *
	  * @param $url 请求url地址
	  * @param $options = array(), 
	  *		header:头信息(Array), 
	  *		proxy_url, 
	  *		timeout:超时时间，可以小于1
	  *		post_data: string|array post数据
	  *	@return Future
	  */
	 public function __construct($url, $options = array()){
		$mt = TaskManager::getInstance();
		
		$ch = $mt->addTask($url, $options);
		
		$this->callback = function($data)use($mt, $ch){
			return $mt->fetch($ch);
		};
	 }
 }