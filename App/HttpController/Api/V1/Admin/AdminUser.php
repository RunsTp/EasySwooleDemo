<?php


namespace App\HttpController\Api\V1\Admin;


use App\Model\Admin\AdminBean;
use App\Model\Admin\AdminUserModel;
use App\Service\Exception\ServiceException;
use App\Service\Exception\ValidateException;
use App\Utility\Pool\MysqlObject;
use App\Utility\Pool\MysqlPool;
use EasySwoole\Http\Message\Status;
use EasySwoole\Validate\Validate;

class AdminUser extends BaseAdminControoler
{
    /**
     * get
     *
     * @throws ValidateException
     * @throws \EasySwoole\Component\Pool\Exception\PoolEmpty
     * @throws \EasySwoole\Component\Pool\Exception\PoolException
     * @throws \EasySwoole\Mysqli\Exceptions\ConnectFail
     * @throws \EasySwoole\Mysqli\Exceptions\PrepareQueryFail
     * @throws \Throwable
     */
    public function get()
    {
        $validate = new Validate();
        $validate->addColumn('admin_id', '管理员ID')->required()->numeric();

        $params = $this->request()->getQueryParams();
        if ($validate->validate($params) === false) {
            throw new ValidateException($validate->getError());
        }

        /** @var MysqlObject $db */
        $db = MysqlPool::defer();
        $adminModel = new AdminUserModel($db);

        /**
         * 未提供 admin_id 时 查询当前管理员信息
         */
        $adminBean = $adminModel->getAdminUserById($params['admin_id'] ?? $this->currentAdmin()->getAdminId());
        if (empty($adminBean)) {
            throw new ValidateException('未找到此管理员');
        }

        $result = $adminBean->toArray();
        unset($result['admin_password'], $result['admin_session']);

        $this->writeJsonResponse(Status::CODE_OK, $result);
    }

    /**
     * list
     *
     * @throws \EasySwoole\Component\Pool\Exception\PoolEmpty
     * @throws \EasySwoole\Component\Pool\Exception\PoolException
     * @throws \EasySwoole\Mysqli\Exceptions\ConnectFail
     * @throws \EasySwoole\Mysqli\Exceptions\Option
     * @throws \EasySwoole\Mysqli\Exceptions\PrepareQueryFail
     * @throws \Throwable
     */
    public function list()
    {
        /** @var MysqlObject $db */
        $db = MysqlPool::defer();
        $adminModel = new AdminUserModel($db);

        $result = $adminModel->getArticleList($this->queryPage(), $this->queryKeyword(), $this->queryPageSize());
        $this->writeJsonResponse(Status::CODE_OK, $result);
    }

    /**
     * create
     *
     * @throws ServiceException
     * @throws ValidateException
     * @throws \EasySwoole\Component\Pool\Exception\PoolEmpty
     * @throws \EasySwoole\Component\Pool\Exception\PoolException
     * @throws \Throwable
     */
    public function create()
    {
        $validate = new Validate();
        $validate->addColumn('admin_account', '管理员账户')->required()->lengthMax(18)->alphaDash();
        $validate->addColumn('admin_password', '管理员密码')->required()->lengthMax(18);
        $validate->addColumn('nickname', '管理员昵称')->required()->lengthMax(32)->alphaDash();
        $validate->addColumn('avatar', '管理员头像')->optional()->lengthMax(255)->url();
        $validate->addColumn('status', '管理员状态')->optional()->inArray([0,1]);

        $params = $this->json();
        if ($validate->validate($params) === false) {
            throw new ValidateException($validate->getError());
        }

        $adminBean = new AdminBean($params);
        $adminBean->setAdminPassword($params['admin_password']);

        /** @var MysqlObject $db */
        $db = MysqlPool::defer();
        $adminModel = new AdminUserModel($db);

        /** 重复性检查 */
        if ($adminModel->getAdminUserByAccount($adminBean->getAdminAccount()) !== null) {
            throw new ValidateException('此管理员账户已占用');
        }

        $adminBean = $adminModel->create($adminBean);
        if (empty($adminBean)) {
            throw new ServiceException($db->getLastError());
        }

        $this->writeJsonResponse(Status::CODE_OK, $adminBean->toArray(['admin_id']));
    }

    /**
     * update
     *
     * @throws ServiceException
     * @throws ValidateException
     * @throws \EasySwoole\Component\Pool\Exception\PoolEmpty
     * @throws \EasySwoole\Component\Pool\Exception\PoolException
     * @throws \Throwable
     */
    public function update()
    {
        $validate = new Validate();
        $validate->addColumn('admin_id', '管理员ID')->required()->numeric();
        $validate->addColumn('admin_password', '管理员密码')->optional()->lengthMax(18);
        $validate->addColumn('nickname', '管理员昵称')->optional()->lengthMax(32)->alphaDash();
        $validate->addColumn('avatar', '管理员头像')->optional()->lengthMax(255)->url();
        $validate->addColumn('status', '管理员状态')->optional()->inArray([0,1]);

        $params = $this->json();
        if ($validate->validate($params) === false) {
            throw new ValidateException($validate->getError());
        }

        /** @var MysqlObject $db */
        $db = MysqlPool::defer();
        $adminModel = new AdminUserModel($db);

        $adminBean = $adminModel->getAdminUserById($params['admin_id']);
        if (empty($adminBean)) {
            throw new ValidateException('未找到此管理员');
        }

        if (!empty($params['admin_password'])) {
            $adminBean->setAdminPassword($params['admin_password']);
            /** 修改密码则废弃session */
            $adminBean->setAdminSession(null);
        }

        $adminBean->setStatus($params['status'] ?? $adminBean->getStatus());
        $adminBean->setNickname($params['nickname'] ?? $adminBean->getNickname());
        $adminBean->setAvatar($params['avatar'] ?? $adminBean->getAvatar());

        if ($adminModel->update($adminBean) === false) {
            throw new ServiceException($db->getLastError());
        }

        $this->writeJsonResponse(Status::CODE_OK);
    }

    /**
     * delete
     *
     * @throws ServiceException
     * @throws ValidateException
     * @throws \EasySwoole\Component\Pool\Exception\PoolEmpty
     * @throws \EasySwoole\Component\Pool\Exception\PoolException
     * @throws \Throwable
     */
    public function delete()
    {
        $validate = new Validate();
        $validate->addColumn('admin_id', '管理员ID')->required()->numeric();

        $params = $this->json();
        if ($validate->validate($params) === false) {
            throw new ValidateException($validate->getError());
        }

        /** @var MysqlObject $db */
        $db = MysqlPool::defer();
        $adminModel = new AdminUserModel($db);

        $adminBean = $adminModel->getAdminUserById($params['admin_id']);
        if (empty($adminBean)) {
            throw new ValidateException('未找到此管理员');
        }

        if ($adminModel->delete($adminBean) === false) {
            throw new ServiceException($db->getLastError());
        }

        $this->writeJsonResponse(Status::CODE_OK);
    }
}