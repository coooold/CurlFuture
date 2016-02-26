# CurlFuture: PHP Curl并行轮转请求库

multicurl系列方法是提高php请求后端http接口的一种途径。但是直接使用的话，存在几方面问题：

- 部分版本的curl扩展有bug，需要用特定的方式来调用([Rolling cURL: PHP并发最佳实践](http://www.searchtb.com/2012/06/rolling-curl-best-practices.html))
- 网上流传的CurlRolling库都只支持前面加入，最后一并执行这种使用模式。而最理想的是随时加入，需要的时候从里面取出所需的结果，且不需等待其他请求返回
- 为了提升效率，大部分库选择使用回调函数的方式来执行，对已有程序改造成本较高

为了解决这些问题，开发了CurlFuture库，实现了并行请求，先到先取，链式执行的特性。

## 应用场景

对于一些大型公司，PHP作为接口聚合层来使用，而接口通过HTTP协议给出。对于一些复杂的页面，可能需要请求几十个相互独立的接口，
如果使用并行模式，则可以极大的提升性能。

## 安装方法
引入入口php文件即可：`include __DIR__.'/curl_future.php';`

## 使用方法

```php
/**
 * 获得一个延迟执行curl的类
 * @param $url 请求url地址
 * @param $options = array(), 
 *		header:头信息(Array), 
 *		proxy_url:代理服务器地址, 
 *		timeout:超时时间，可以小于1
 *		post_data: string|array post数据
 * @return CurlFuture\HttpFuture
 */
function curl_future($url, $options = array());

echo curl_future("http://s.newhua.com/2015/1113/304528.shtml?4", array())
		->fetch();
```

## 并行请求的实例(async.php)

```php
include __DIR__.'/curl_future.php';

$f4 = curl_future("http://s.newhua.com/2015/1113/304528.shtml?4");
$f5 = curl_future("http://s.newhua.com/2015/1113/304528.shtml?5");

echo strlen($f1->fetch());	//这个地方会并行执行
echo "\n";
echo strlen($f2->fetch());
echo "\n";
```

## 链式执行的示例(then.php)

```php
include __DIR__.'/curl_future.php';

echo curl_future("http://s.newhua.com/2015/1113/304528.shtml")
	->then(function($data){
		return strlen($data);
	})
	->then(function($len){
		return "Length: $len";
	})
	->fetch();
```

## 和Model/Service结合的示例(model.php)

```php
include __DIR__.'/curl_future.php';

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
		return curl_future("http://111.202.7.252/{$id}")
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
```

## 原理

在每次fetch的时候，开始事件循环。当所需http返回后，结束循环。继续执行php逻辑。
```php
	//task_manager.php
	public function fetch($ch){
		$chKey = (int)$ch;

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
```

## 性能测试

请求本机接口200次，nginx默认页面，同步、异步与file_get_contents对比

	/example/bench.php
	
	curl_future sync：384 ms
	file_get_contents：390 ms
	curl_futhre async：68 ms
	
	curl_future sync：624 ms
	file_get_contents：460 ms
	curl_futhre async：69 ms
	
	curl_future sync：463 ms
	file_get_contents：355 ms
	curl_futhre async：70 ms
	
	curl_future sync：447 ms
	file_get_contents：409 ms
	curl_futhre async：66 ms

同步方式没有file_get_contents稳定，但是异步批量方式性能提升很明显。

## 参考项目

- [Client URL Library](http://www.php.net/manual/en/book.curl.php)
- [Parallel CURL Requests with PHP](http://blog.rob.cx/multi-curl)
- [A more efficient multi-curl library for PHP (non-blocking)](http://code.google.com/p/rolling-curl/)
- [PHP: Parallel cURL Performance](http://stackoverflow.com/questions/10485199/php-parallel-curl-performance-rollingcurl-vs-parallelcurl)