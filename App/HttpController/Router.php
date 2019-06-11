<?php


namespace App\HttpController;
use EasySwoole\Http\AbstractInterface\AbstractRouter;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use FastRoute\RouteCollector;
class Router extends AbstractRouter
{
	function initialize(RouteCollector $routeCollector)
	{
        $this->setGlobalMode(true);
		
		$this->setMethodNotAllowCallBack(function (Request $request,Response $response){
			$response->write('未找到处理方法');
			return false;//结束此次响应
		});
		$this->setRouterNotFoundCallBack(function (Request $request,Response $response){
			$response->write('未找到路由匹配');
			return false;//重定向到index路由
		});
		// TODO: Implement initialize() method.
		$routeCollector->get('/','/Index/index');
		$routeCollector->get('/zf','/Index/zf');
		$routeCollector->get('/user','/Test/user');
		$routeCollector->get('/test','/Index/test');
		
	}
}