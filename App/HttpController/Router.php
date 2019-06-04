<?php


namespace App\HttpController;


use EasySwoole\Http\AbstractInterface\AbstractRouter;
use EasySwoole\Http\Message\Status;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use FastRoute\RouteCollector;

/**
 * Class Router
 * 路由 参考 FastRoute https://github.com/nikic/FastRoute
 * 其他 参考 自定义路由 https://www.easyswoole.com/Manual/3.x/Cn/_book/Http/FastRoute.html
 *
 * @package App\HttpController
 */
class Router extends AbstractRouter
{
    /**
     * initialize
     * 注册路由
     * 利用路由分组来方便的管理路由
     *
     * @param RouteCollector $routeCollector
     */
    public function initialize(RouteCollector $routeCollector)
    {
        /** 强制路由 */
        $this->setGlobalMode(true);

        /** 未找到路由处理方法 */
        $this->setMethodNotAllowCallBack(function (Request $request, Response $response) {
            $response->withStatus(Status::CODE_INTERNAL_SERVER_ERROR);
            $response->write('Internal Server Error');
            return false;
        });

        /** 路由未匹配 */
        $this->setRouterNotFoundCallBack(function (Request $request, Response $response) {
            $response->withStatus(Status::CODE_NOT_FOUND);
            $response->write('File Not Found');
            return false;
        });

        /** web hook */
        $routeCollector->addGroup('/api/hook', function (RouteCollector $routeCollector) {
            /** 码云 */
            $routeCollector->post('/gitee', '/Api/WebHook/Gitee/index');
        });

        /** 后台相关 */
        $routeCollector->addGroup('/api/v1/admin', function (RouteCollector $routeCollector) {

            /** 身份验证 */
            $routeCollector->addGroup('/auth', function (RouteCollector $routeCollector) {
                $routeCollector->post('/login', '/Api/V1/Admin/Auth/login');
                $routeCollector->post('/logout', '/Api/V1/Admin/Auth/logout');
            });

            /** 上传相关 */
            $routeCollector->addGroup('/upload', function (RouteCollector $routeCollector) {
                $routeCollector->post('/img', '/Api/V1/Admin/Upload/img');
                $routeCollector->post('/excel', '/Api/V1/Admin/Upload/excel');
            });

            /** 管理员管理 */
            $routeCollector->addGroup('/admin_user', function (RouteCollector $routeCollector) {
                $routeCollector->get('/get', '/Api/V1/Admin/AdminUser/get');
                $routeCollector->get('/list', '/Api/V1/Admin/AdminUser/list');
                $routeCollector->post('/create', '/Api/V1/Admin/AdminUser/create');
                $routeCollector->post('/update', '/Api/V1/Admin/AdminUser/update');
                $routeCollector->post('/delete', '/Api/V1/Admin/AdminUser/delete');
            });
        });
    }
}