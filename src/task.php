<?php
namespace CurlFuture;

/**
 * Task类，封装每个curl handle的输入输出方法，如果需要日志、异常处理，可以放在这个地方
 * @author fang
 * @version 2015年11月25日09:45:05
 */
class Task{
	public $url;
	public $ch;	//curl handle
	protected $curlOptions = array();
	
	/**
	 * 构造函数，供TaskManager调用
	 * @author fang
	 * @version 2015年11月25日09:52:00
	 */ 
	public function __construct($url, $options){
		$this->url = $url;
		$ch = curl_init();
		

		$curlOptions = array(
				CURLOPT_TIMEOUT => 1,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_URL => $url,

		);
		
		//这个地方需要合并cat的头信息
		$headers = isset($options['header'])?$options['header']:array();
		$curlOptions[CURLOPT_HTTPHEADER] = $headers;
		
		if(isset($options['proxy_url']) && $options['proxy_url']){
			$curlOptions[CURLOPT_PROXY] = $options['proxy_url'];
		}
		
		//设置超时时间
		$timeout = isset($options['timeout']) ? $options['timeout'] : 1;
		if($timeout<1){
			$curlOptions[CURLOPT_TIMEOUT_MS] = intval($timeout * 1000);
			$curlOptions[CURLOPT_NOSIGNAL] = 1;
		}else{
			$curlOptions[CURLOPT_TIMEOUT] = $timeout;
		}
		
		// 如果需要post数据
		if (isset($options['post_data']) && $options['post_data']) {
			$curlOptions[CURLOPT_POST] = true;
			
			curl_setopt($ch, CURLOPT_POST, true);
			$postData = $options['post_data'];
			if (is_array($options['post_data'])) {
				$postData = http_build_query($options['post_data']);
			}
			$curlOptions[CURLOPT_POSTFIELDS] = $postData;
		}
		
		curl_setopt_array($ch, $curlOptions);
		
		$this->ch = $ch;
	}

	
	/**
	 * 请求完成后调用，可以在这个函数里面加入日志与统计布点，返回http返回结果
	 * @author fang
	 * @version 2015年11月25日09:52:00
	 * @return 成功string，失败false
	 */ 
	public function complete(){
		return $this->getContent();
	}

	
	/**
	 * 如果curl已经完成，通过这个函数读取内容
	 * @author fang
	 * @version 2015年11月25日09:52:00
	 * @return 成功string，失败false
	 */ 
	private function getContent(){
		$error = curl_errno($this->ch);
		if($error !== 0){
			return false;
		}
	
		return curl_multi_getcontent($this->ch);
	}
}
