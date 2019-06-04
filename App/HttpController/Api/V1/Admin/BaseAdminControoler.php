<?php


namespace App\HttpController\Api\V1\Admin;


use App\HttpController\Api\BaseApiController;
use App\HttpController\Api\V1\Admin\Exception\AuthException;
use App\Model\Admin\AdminBean;
use App\Model\Admin\AdminUserModel;
use App\Utility\Pool\MysqlObject;
use App\Utility\Pool\MysqlPool;
use EasySwoole\Http\Message\Status;
use Throwable;

class BaseAdminControoler extends BaseApiController
{
    /** @var string session key */
    const SessionKey = 'x-token';

    /** @var array 跳过身份验证的方法 */
    protected $skipCheckAuthActionList = [];

    /** @var AdminBean|null */
    private $currentAdmin;

    /**
     * index
     */
    public function index()
    {
        $this->actionNotFound($this->getActionName());
    }

    /**
     * gc
     * 所有自定义属性必须主动清空
     */
    protected function gc()
    {
        $this->currentAdmin = null;
        parent::gc();
    }

    /**
     * onRequest
     *
     * @param string|null $action
     * @return bool|null
     * @throws AuthException
     * @throws Throwable
     * @throws \EasySwoole\Component\Pool\Exception\PoolEmpty
     * @throws \EasySwoole\Component\Pool\Exception\PoolException
     */
    protected function onRequest(?string $action): ?bool
    {
        $this->checkAdminUser();
        return parent::onRequest($action);
    }

    /**
     * getSession
     *
     * @return string|null
     */
    protected function getSession(): ?string
    {
        return $this->request()->getHeader(self::SessionKey)[0] ?? null;
    }

    /**
     * currentAdmin
     *
     * @return AdminBean|null
     */
    protected function currentAdmin(): ?AdminBean
    {
        return $this->currentAdmin;
    }

    /**
     * checkAdminUser
     *
     * @throws AuthException
     * @throws Throwable
     * @throws \EasySwoole\Component\Pool\Exception\PoolEmpty
     * @throws \EasySwoole\Component\Pool\Exception\PoolException
     */
    protected function checkAdminUser(): void
    {
        try {
            $session = $this->getSession();
            if (empty($session)) {
                throw new AuthException('not find session.');
            }

            /** @var AdminBean $adminUser */
            $adminUser = MysqlPool::invoke(function (MysqlObject $mysqlObject) use ($session) {
                return (new AdminUserModel($mysqlObject))->getAdminUserBySession($session);
            });

            if (empty($adminUser)) {
                throw new AuthException('invalid session.');
            }

            if ($adminUser->isDisable()) {
                throw new AuthException('account disable.');
            }

            if ($adminUser->getLastLoginTime() < time() - 7200) {
                throw new AuthException('session expiration.');
            }

            $this->currentAdmin = $adminUser;
        } catch (AuthException $authException) {
            /** 检查是否是需要跳过身份验证的方法 */
            if (!in_array($this->getActionName(), $this->skipCheckAuthActionList, true)) {
                throw $authException;
            }
        }
    }

    /**
     * queryKeyword
     *
     * @param string $keyName
     * @return string|null
     */
    protected function queryKeyword(string $keyName = 'keyword') : ?string
    {
        $keyword = $this->json()[$keyName] ?? null;
        if (empty($keyword)) {
            $keyword = $this->request()->getQueryParam($keyName);
        }

        return $keyword ?? null;
    }

    /**
     * onException
     *
     * @param Throwable $throwable
     * @throws Throwable
     */
    protected function onException(Throwable $throwable): void
    {
        /** 身份鉴权类异常 */
        if ($throwable instanceof AuthException) {
            $this->writeJsonResponse(Status::CODE_UNAUTHORIZED, null, $throwable->getMessage());
            return;
        }

        parent::onException($throwable);
    }
}