<?php
namespace CurlFuture;

/**
 * Future类，提供延迟执行的基础方法
 * @author fang
 * @version 2015年11月25日09:45:05
 */
class Future{
	protected $callback = null;
	protected $nextFuture = null;

	/**
	 * 构造函数，创建一个延迟执行的方法，在fetch的时候才真正执行
	 * @param @callback 一个可执行的函数
	 * @author fang
	 * @version 2015年11月25日09:52:00
	 */ 
	public function __construct($callback){
		assert( is_callable($callback) );
		$this->callback = $callback;
	}
	
	/**
	 * 链式执行的函数，避免大量回调，上一个future执行的结果会作为下一个future执行结果的参数来执行
	 * @param @callback 一个可执行的函数
	 * @author fang
	 * @version 2015年11月25日09:52:00
	 */ 
	public function then($callback){
		if($this->nextFuture){
			$this->nextFuture->then($callback);
		}else{
			$this->nextFuture = new self($callback);
		}
		
		return $this;
	}
	
	/**
	 * future真正执行的方法，一直执行到future链到最后一个，并返回最后一个的执行结果
	 * @param @input 初始输入参数
	 * @author fang
	 * @version 2015年11月25日09:52:00
	 */ 
	public function fetch($input = null){
		$ret = call_user_func_array($this->callback, array($input));
		if($this->nextFuture){
			return $this->nextFuture->fetch($ret);
		}else{
			return $ret;
		}
	}
}
