<?php
namespace Api;

class Event {
	// 可以定义全局属性变量，在下面的各个函数中使用

	// 
	public static function onRequest($request, $response, $http) {

		$taskId = $http->task($request->post);
		// 其他逻辑业务
		.........
		$response->end('<h3>hello word!</h3>');
	}

	public static function onTask($serv, $taskId, $fromId, $data) {
		// 业务逻辑，例如下面例子，通过type区分不同的业务
		$type = $data['type'];
		switch($type) {
			// 请求推送本地文件给远程服务器
			case 'pushRequest':
					// 业务
			break;
			// 接收远程服务器给自己推送文件完成后的信息
			case 'pushReceive':
				// 业务
			break;

			case 'pullRequest' :
				// 业务
			break;

			case 'pullReceive' :
				
				// 业务
			break;

			default : return ;

		}
		// 务必返回，触发finish事件
		return $result;
	}

	public static function onFinish($serv, $taskId, $data) {
		// 完成task_worker之后的业务
	}

	public static function onShutdown($serv) {
		// 出现服务意外关闭停止的回调
	}
}