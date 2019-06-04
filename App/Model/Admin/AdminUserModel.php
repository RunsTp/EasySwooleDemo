<?php


namespace App\Model\Admin;


use App\Model\BaseModel;
use EasySwoole\Utility\Random;

class AdminUserModel extends BaseModel
{
    protected $table = 'main_admin_list';
    protected $pk = 'admin_id';

    /** @var array 允许更新的字段 */
    private $allowUpdatingFields = ['avatar', 'nickname', 'admin_password', 'admin_session', 'last_login_time', 'status'];

    /**
     * getArticleList
     *
     * @param int         $page
     * @param string|null $keyword
     * @param int         $size
     * @return array
     * @throws \EasySwoole\Mysqli\Exceptions\ConnectFail
     * @throws \EasySwoole\Mysqli\Exceptions\Option
     * @throws \EasySwoole\Mysqli\Exceptions\PrepareQueryFail
     * @throws \Throwable
     */
    public function getArticleList(int $page = 1, ?string $keyword = null, int $size = 20) : array
    {
        if (!empty($keyword)) {
            $this->dbConnector()->whereLike('admin_account', "%{$keyword}%");
        }

        $result = [
            'list' => $this->dbConnector()->withTotalCount()->get($this->table, [$size * ($page - 1), $size]),
            'total' => $this->dbConnector()->getTotalCount()
        ];
        return $result;
    }

    /**
     * getAdminUserById
     *
     * @param int $adminId
     * @return AdminBean|null
     * @throws \EasySwoole\Mysqli\Exceptions\ConnectFail
     * @throws \EasySwoole\Mysqli\Exceptions\PrepareQueryFail
     * @throws \Throwable
     */
    public function getAdminUserById(int $adminId) :?AdminBean
    {
        $info = $this->dbConnector()->where($this->pk, $adminId)->getOne($this->table);
        return empty($info) ? null : new AdminBean($info);
    }

    /**
     * getAdminUserByAccount
     *
     * @param string $account
     * @return AdminBean|null
     * @throws \EasySwoole\Mysqli\Exceptions\ConnectFail
     * @throws \EasySwoole\Mysqli\Exceptions\PrepareQueryFail
     * @throws \Throwable
     */
    public function getAdminUserByAccount(string $account) :?AdminBean
    {
        $info = $this->dbConnector()->where('admin_account', $account)->getOne($this->table);
        return empty($info) ? null : new AdminBean($info);
    }

    /**
     * getAdminUserBySession
     *
     * @param string $session
     * @return AdminBean|null
     * @throws \EasySwoole\Mysqli\Exceptions\ConnectFail
     * @throws \EasySwoole\Mysqli\Exceptions\PrepareQueryFail
     * @throws \Throwable
     */
    public function getAdminUserBySession(string $session) :?AdminBean
    {
        $info = $this->dbConnector()->where('admin_session', $session)->getOne($this->table);
        return empty($info) ? null : new AdminBean($info);
    }

    /**
     * createAdminUser
     *
     * @param AdminBean $bean
     * @return AdminBean|null
     * @throws \Throwable
     */
    public function create(AdminBean $bean) :? AdminBean
    {
        $id = $this->dbConnector()->insert($this->table, $bean->toArray(null, AdminBean::FILTER_NOT_NULL));
        if ($id) {
            $bean->setAdminId($id);
            return $bean;
        }
        return null;
    }

    /**
     * updateAdminUser
     *
     * @param AdminBean $bean
     * @return bool
     * @throws \Throwable
     */
    public function update(AdminBean $bean) : bool
    {
        return (bool)$this->dbConnector()->where($this->pk, $bean->getAdminId())
            ->update($this->table, $bean->toArray($this->allowUpdatingFields, AdminBean::FILTER_NOT_NULL));
    }

    /**
     * deleteAdminUser
     *
     * @param AdminBean $bean
     * @return bool
     * @throws \Throwable
     */
    public function delete(AdminBean $bean) : bool
    {
        $bean->setStatus(AdminBean::STATUS_DISABLE);
        return $this->update($bean);
    }

    /**
     * login
     *
     * @param AdminBean $bean
     * @return AdminBean|null
     * @throws \Throwable
     */
    public function login(AdminBean $bean) :?AdminBean
    {
        $bean->setAdminSession($this->generateSession($bean));
        $bean->setLastLoginTime(time());
        if ($this->update($bean)) {
            return $bean;
        }
        return null;
    }

    /**
     * logout
     *
     * @param AdminBean $bean
     * @return bool
     * @throws \Throwable
     */
    public function logout(AdminBean $bean) : bool
    {
        return (bool)$this->dbConnector()->where($this->pk, $bean->getAdminId())
            ->update($this->table, ['admin_session' => null]);
    }

    /**
     * generateSession
     *
     * @param AdminBean $bean
     * @return string
     */
    private function generateSession(AdminBean $bean) : string
    {
        $str = Random::character(16);
        return md5($str. $bean->getAdminId(). time());
    }
}