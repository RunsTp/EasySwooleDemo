<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/28
 * Time: 下午6:33
 */

namespace EasySwoole\EasySwoole;


use App\Process\Dev\HotReload;
use App\Utility\Pool\MysqlPool;
use App\Utility\Pool\RedisPool;
use EasySwoole\Component\Pool\PoolManager;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\Http\Message\Status;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;

class EasySwooleEvent implements Event
{

    /**
     * initialize
     */
    public static function initialize()
    {
        /** 设置时区 */
        date_default_timezone_set('Asia/Shanghai');
    }

    /**
     * mainServerCreate
     *
     * @param EventRegister $register
     * @throws \EasySwoole\Component\Pool\Exception\PoolException
     */
    public static function mainServerCreate(EventRegister $register)
    {
        /**
         * 获取 swooleServer 对象 方便注册事件
         * @var null|\swoole_server|\swoole_server_port|\swoole_websocket_server|\swoole_http_server
         */
        $swooleServer = ServerManager::getInstance()->getSwooleServer();

        /**
         * 获取环境状态 方便判断
         */
        $isDev = Core::getInstance()->isDev();

        /**
         * **************** 热重载 **********************
         */
        if ($isDev) {
            $swooleServer->addProcess((new HotReload('HotReload', ['disableInotify' => false]))->getProcess());
        }

        /**
         * **************** 连接池注册 **********************
         */
        PoolManager::getInstance()->register(MysqlPool::class, Config::getInstance()->getConf('MYSQL.POOL_MAX_NUM'));
        PoolManager::getInstance()->register(RedisPool::class, Config::getInstance()->getConf('REDIS.POOL_MAX_NUM'));
    }

    public static function onRequest(Request $request, Response $response): bool
    {
        /**
         * **************** 跨域处理 **********************
         * 此处可以增加具体的域名黑白名单进行跨域检查
         */
        $response->withHeader('Access-Control-Allow-Origin', '*');
        $response->withHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $response->withHeader('Access-Control-Allow-Credentials', 'true');
        $response->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-Token, ClientType');
        if ($request->getMethod() === 'OPTIONS') {
            $response->withStatus(Status::CODE_OK);
            $response->end();
            return false;
        }
        return true;
    }

    /**
     * afterRequest
     *
     * @param Request  $request
     * @param Response $response
     */
    public static function afterRequest(Request $request, Response $response): void
    {
    }
}