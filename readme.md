首先要安装swoole扩展。    
这是基于swoole-http-server的异步多任务调度服务，在php的cli模式下启动Task.php   
在cli模式下执行php Task.php    
   
Event.php专注于各种业务，自己根据业务扩展功能。        
Task.php的配置   
$http->set(array(
				'reactor_num' => 2, //reactor thread num    
			    'worker_num' => 4,    //worker process num    
			    'max_request' => 50,   
			    'open_tcp_nodelay' => true,    
			    'open_tcp_keepalive' => 1,     
	    		'task_worker_num' => 4,     
			)    
		);    
可以根据自己的实际情况重新配置。        
具体参考：http://wiki.swoole.com/wiki/index/prid-1     
这是swoole的官网和文档说明。   