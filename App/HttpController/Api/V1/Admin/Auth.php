<?php


namespace App\HttpController\Api\V1\Admin;



use App\Model\Admin\AdminUserModel;
use App\Service\Exception\ServiceException;
use App\Service\Exception\ValidateException;
use App\Utility\Pool\MysqlObject;
use App\Utility\Pool\MysqlPool;
use EasySwoole\Http\Message\Status;
use EasySwoole\Validate\Validate;

class Auth extends BaseAdminControoler
{
    /** @var array  */
    protected $skipCheckAuthActionList = ['login'];

    /**
     * login
     *
     * @throws ServiceException
     * @throws ValidateException
     * @throws \EasySwoole\Component\Pool\Exception\PoolEmpty
     * @throws \EasySwoole\Component\Pool\Exception\PoolException
     * @throws \Throwable
     */
    public function login()
    {
        $validate = new Validate();
        $validate->addColumn('admin_account', '管理员账户')->required()->lengthMax(18);
        $validate->addColumn('admin_password', '管理员密码')->required()->lengthMax(18);

        $params = $this->json();
        if ($validate->validate($params) === false) {
            throw new ValidateException($validate->getError());
        }

        /** @var MysqlObject $db */
        $db = MysqlPool::defer();
        $adminUserModel = new AdminUserModel($db);

        $adminUser = $adminUserModel->getAdminUserByAccount($params['admin_account']);
        if (empty($adminUser)) {
            throw new ValidateException('未找到此管理员');
        }

        if ($adminUser->checkAdminPassword($params['admin_password']) === false) {
            throw new ValidateException('密码错误!');
        }

        if ($adminUser->isDisable()) {
            throw new ValidateException('此管理员账户已被禁用!');
        }

        $adminUser = $adminUserModel->login($adminUser);
        if (empty($adminUser)) {
            throw new ServiceException($db->getLastError());
        }

        $this->writeJsonResponse(Status::CODE_OK, $adminUser->toArray(['admin_id', 'nickname', 'avatar', 'admin_session']));
    }

    /**
     * logout
     *
     * @throws ServiceException
     * @throws \EasySwoole\Component\Pool\Exception\PoolEmpty
     * @throws \EasySwoole\Component\Pool\Exception\PoolException
     * @throws \Throwable
     */
    public function logout()
    {
        /** @var MysqlObject $db */
        $db = MysqlPool::defer();
        $adminUserModel = new AdminUserModel($db);

        /** 登出 */
        if ($adminUserModel->logout($this->currentAdmin()) === false) {
            throw new ServiceException($db->getLastError());
        }
        $this->writeJsonResponse(Status::CODE_OK);
    }
}