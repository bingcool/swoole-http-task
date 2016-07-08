<?php
namespace Api;
use swoole_http_server;//需要安装swoole扩展

class Task {
	/**
	*@var 常量，当前文件所在路径
	*
	*/
	const DIR_PATH = __DIR__;


	/**
	*	在这里可以集训定义一些属性变量，这些变量将会是这个swoole服务中的全局变量，启动时候就会常驻内存的。
	*	在下面的这些函数中，都可以使用这个变量，通过$this->buffer就可以访问到这个变量。
	*
	*/

	/**swoole的任务服务函数
	*/
	public function task() {
		// 实例$http的对象
		$http = new swoole_http_server('0.0.0.0',9501);
		// 配置相关信息
		$http->set(array(
				'reactor_num' => 2, //reactor thread num
			    'worker_num' => 4,    //worker process num
			    'max_request' => 50,
			    'open_tcp_nodelay' => true,
			    'open_tcp_keepalive' => 1,
	    		'task_worker_num' => 4,
			)
		);
		//进程启动，将需要用到的类加载，并保存在内存中，这些类只能在worker进程，taskworkerj进程中使用
		$http->on('WorkerStart', function($serv, $workerId) {
			/***
			*Event.php分别处理不同逻辑业务
			*/
			include(static::DIR_PATH.'/Event.php');

		});

		// 函数可以用在worker进程内。向主进程发送SIGTERM也可以实现关闭服务器eg：kill -15 主进程PID
		$http->on('shutdown',function($serv) {
			Event::onShutdown($serv);
		});

		/** 当worker/task_worker进程发生异常后会在Manager进程内回调此函数
		*	$worker_id是异常进程的编号
		*	$worker_pid是异常进程的ID
		*	$exit_code退出的状态码，范围是 1 ～255
		*/
		$http->on('WorkerError',function($serv, $worker_id, $worker_pid, $exit_code) {
			
		});

		/** http服务器接受http的请求
		*	$resquest是http的请求对象，具体可以查看swoole的文档说明
		*	$response是http的响应对象，具体可以查看swoole的文档说明
		*	在回调回调函数中，需要使用外部的对象，eg:$http,可以使用php的闭包函数use()的用法将对象参递
		*	$http->task()投递任务至异步的任务池执行，可以传递任务参数，并且返回任务的id
		*   $response->end()将返回内容，并结束本次的http请求，销毁$request, $response对象
		*/
		$http->on('request', function($request, $response) use($http) {
			Event::onRequest($request, $response, $http);	
		});

		/**异步任务池
		*	每次接收请求投递的任务在这个task_worker进程中执行
		*	$data是$http->task投递的任务数据，可以为除资源类型之外的任意PHP变量
		*/

		$http->on('task',function($serv, $taskId, $fromId, $data) {
			/**这里进行业务逻辑处理
			*	可以通过类型判断不同的业务然后执行。
			*
			*/
			$result = Event::onTask($serv, $taskId, $fromId, $data);
			//retrun 返回结果，将会触发$http->onfinish()事件
			return $result;


		}

		/**task_worker的异步任务完成后，将触发finish事件回调函数。。
		*	将会做一些后续处理，eg:写入数据库或者redis，短信邮件，通知订单发送等。
		*
		*/
		$http->on('finish', function($serv, $taskId, $data) {
			// 做一些后续的回调处理
			Event::onFinish($serv, $taskId, $data);
			return ;
		}

		// 启动服务
		$http->start();

		
	}


}