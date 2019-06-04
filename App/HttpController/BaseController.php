<?php


namespace App\HttpController;


use App\Service\Enum\ClientType;
use EasySwoole\Component\Pool\Exception\PoolEmpty;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Core;
use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\EasySwoole\Trigger;
use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\Http\Message\Status;
use Throwable;

abstract class BaseController extends Controller
{
    /**
     * config
     *
     * @param string $keyPath
     * @return array|mixed|null
     */
    protected function config(string $keyPath)
    {
        return Config::getInstance()->getConf($keyPath);
    }

    /**
     * isDev
     *
     * @return bool
     */
    protected function isDev(): bool
    {
        return Core::getInstance()->isDev();
    }

    /**
     * 获取用户的真实IP
     *
     * @param string $headerName 代理服务器传递的标头名称
     * @return string
     */
    protected function clientRealIP(string $headerName = 'x-real-ip')
    {
        $server = ServerManager::getInstance()->getSwooleServer();
        $client = $server->getClientInfo($this->request()->getSwooleRequest()->fd);
        $clientAddress = $client['remote_ip'];
        $xri = $this->request()->getHeader($headerName);
        $xff = $this->request()->getHeader('x-forwarded-for');
        if ($clientAddress === '127.0.0.1') {
            if (!empty($xri)) {  // 如果有xri 则判定为前端有 Nginx 等代理
                $clientAddress = $xri[0];
            } elseif (!empty($xff)) {  // 如果不存在xri 则继续判断xff
                $list = explode(',', $xff[0]);
                if (isset($list[0])) $clientAddress = $list[0];
            }
        }
        return $clientAddress;
    }

    /**
     * 获取客户端类型
     *
     * @param string $headerName
     * @return ClientType
     * @throws \Exception
     */
    protected function clientType(string $headerName = 'client-type') : ClientType
    {
        /**
         * 此方法依赖Nginx获取客户端类型
         */
        $clientType = $this->request()->getHeader($headerName);
        if (!empty($clientType) && strtoupper($clientType) === 'MOBILE') {
            return new ClientType(ClientType::MOBILE);
        }

        return new ClientType(ClientType::PC);
    }

    /**
     * writeJsonResponse
     * 向客户端响应Json类型数据
     *
     * @param int         $code       Http状态码(默认200)
     * @param null        $result     返回内容
     * @param string|null $msg        返回信息
     * @param int|null    $statusCode 业务状态码
     * @return bool
     */
    protected function writeJsonResponse(int $code = Status::CODE_OK, $result = null, string $msg = 'ok', int $statusCode = null): bool
    {
        if (!$this->response()->isEndResponse()) {
            $data = [
                "code"   => $statusCode ?? $code,
                "result" => $result,
                "msg"    => $msg
            ];
            $this->response()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $this->response()->withHeader('Content-type', 'application/json;charset=utf-8');
            $this->response()->withStatus($code);
            $this->response()->end();
            return true;
        } else {
            return false;
        }
    }

    /**
     * writeTextResponse
     *
     * @param int    $code
     * @param string $body
     * @return bool
     */
    protected function writeTextResponse(int $code = Status::CODE_OK, string $body = ''): bool
    {
        if (!$this->response()->isEndResponse()) {
            $this->response()->write($body);
            $this->response()->withHeader('Content-type', 'text/plain;charset=utf-8');
            $this->response()->withStatus($code);
            $this->response()->end();
            return true;
        } else {
            return false;
        }
    }

    /**
     * onException
     *
     * @param Throwable $throwable
     * @throws Throwable
     */
    protected function onException(Throwable $throwable): void
    {
        /** 空池处理 服务降级 */
        if ($throwable instanceof PoolEmpty && !$this->response()->isEndResponse()) {
            $this->writeJsonResponse(Status::CODE_SERVICE_UNAVAILABLE, null, '系统繁忙，请稍后再试！');
            /**
             * 记录错误异常日志
             * 如果需要可以进一步发送短信或者邮件等
             */
            Trigger::getInstance()->error($throwable->getMessage(), E_WARNING);
            return;
        }

        $this->writeJsonResponse(Status::CODE_INTERNAL_SERVER_ERROR, null, '系统了点小问题，请联系管理员!');
        Trigger::getInstance()->throwable($throwable);
    }
}