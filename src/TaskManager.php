<?php
namespace CurlFuture;
/**
 * 封装了MultiCurl的类，实现了curl并行与轮转请求
 * @author fang
 * @version 2015年11月25日09:45:05
 */
class TaskManager{
	/**
	 * @var curl_multi_handle
	 */
	protected $multiHandle;
	/**
	 * 正在执行的任务
	 */
	protected $runningTasks = array();
	/**
	 * 已经完成的任务
	 */
	protected $finishedTasks = array();
	/**
	 * select的默认timeout时间，对于高版本curl扩展，这个没用用处
	 */
	const SELECT_TIMEOUT = 1;	//select超时时间1s
	
	protected function __construct(){
		$this->multiHandle = curl_multi_init();
	}
	
	function __destruct(){
		curl_multi_close($this->multiHandle);
	}
	
	/**
	 * 添加curl任务，options参考HttpFuture::__construct
	 * @author fang
	 * @version 2015年11月25日09:52:00
	 * @return curl_handle
	 */ 
	public function addTask($url, $options){
		$req = new Task($url, $options);
		$ch = $req->ch;

		$this->runningTasks[(int)$ch] = array(
			'return' => false,
			'req' => $req,
			'ch' => $ch,
		);

		curl_multi_add_handle($this->multiHandle, $ch);

		return $ch;
	}
	
	/**
	 * 如果ch未完成，阻塞并且并行执行curl请求，直到对应ch完成，返回对应结果
	 * @author fang
	 * @version 2015年11月25日09:52:00
	 * @return string
	 */ 
	public function fetch($ch){
		$chKey = (int)$ch;
		$this->debug("fetch ".(int)$ch);
		
		//如果两个队列里面都没有，那么退出
		if(!array_key_exists($chKey, $this->runningTasks) && !array_key_exists($chKey, $this->finishedTasks) )return false;
	
		$active = 1;
		do{
			//如果任务完成了，那么退出
			if(array_key_exists($chKey, $this->finishedTasks))break;

			//执行multiLoop，直到该任务完成
			$active = $this->multiLoop();
			//如果执行出错，那么停止循环
			if($active === false)break;
		}while(1);
		
		return $this->finishTask($ch);
	}
	
	/**
	 * 循环一次multi任务
	 * @author fang
	 * @version 2015年11月25日09:52:00
	 * @return bool true:可以继续执行 false:已经循环结束，无法继续执行
	 */ 
	protected function multiLoop(){
		//echo '.';
		$active = 1;

		// fix for https://bugs.php.net/bug.php?id=63411
		// see https://github.com/petewarden/ParallelCurl/blob/master/parallelcurl.php		
		// see http://blog.marchtea.com/archives/109
		while(curl_multi_exec($this->multiHandle, $active) === CURLM_CALL_MULTI_PERFORM);

		$ret = 0;
		//等待socket操作
		$ret = curl_multi_select($this->multiHandle, self::SELECT_TIMEOUT);

		//处理已经完成的句柄
		while ($info = curl_multi_info_read($this->multiHandle)) {
			$ch = $info['handle'];
			$this->debug('get content'.(int)$ch);
			
			$task = $this->runningTasks[(int)$ch];
			$task['return'] = $task['req']->complete();

			unset($this->runningTasks[(int)$ch]);
			$this->finishedTasks[(int)$ch] = $task;
			curl_multi_remove_handle($this->multiHandle, $ch);

		}

		return $active;
	}
	
	/**
	 * 完成任务，执行任务回调
	 * @author fang
	 * @version 2015年11月25日09:52:00
	 * @return mixed 输出该http请求的内容
	 */ 
	protected function finishTask($ch){
		$this->debug("finishTask ".(int)$ch);
		
		$ch = (int)$ch;
		$task = $this->finishedTasks[$ch];
		unset($this->finishedTasks[$ch]);
		return $task['return'];
	}
	
	protected function debug($s){
		//echo time()." {$s}\n";
	}
	
	static protected $instance;
	/**
	 * 获得TaskManager单例
	 * @author fang
	 * @version 2015年11月25日09:52:00
	 * @return TaskManager
	 */ 
	static public function getInstance(){
		if(!self::$instance){
			self::$instance = new self();
		}
		return self::$instance;
	}
}
