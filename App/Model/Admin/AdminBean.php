<?php


namespace App\Model\Admin;


use EasySwoole\Spl\SplBean;

class AdminBean extends SplBean
{
    /** @var int 禁用状态 */
    const STATUS_DISABLE = 0;
    /** @var int 正常状态 */
    const STATUS_NORMAL = 1;

    /** @var int 管理员ID */
    protected $admin_id;
    /** @var string 昵称 */
    protected $nickname;
    /** @var string 头像地址 */
    protected $avatar;
    /** @var string 管理员账户 */
    protected $admin_account;
    /** @var string 管理员密码 */
    protected $admin_password;
    /** @var string 管理员session */
    protected $admin_session;
    /** @var int 上次登陆时间 */
    protected $last_login_time;
    /** @var int 管理员状态 */
    protected $status = self::STATUS_NORMAL;

    /**
     * @return int
     */
    public function getAdminId(): int
    {
        return $this->admin_id;
    }

    /**
     * @param int $admin_id
     */
    public function setAdminId(int $admin_id): void
    {
        $this->admin_id = $admin_id;
    }

    /**
     * @return string
     */
    public function getNickname(): string
    {
        return $this->nickname;
    }

    /**
     * @param string $nickname
     */
    public function setNickname(string $nickname): void
    {
        $this->nickname = $nickname;
    }

    /**
     * @return string
     */
    public function getAvatar(): string
    {
        return $this->avatar;
    }

    /**
     * @param string $avatar
     */
    public function setAvatar(string $avatar): void
    {
        $this->avatar = $avatar;
    }

    /**
     * @return string
     */
    public function getAdminAccount(): string
    {
        return $this->admin_account;
    }

    /**
     * @param string $admin_account
     */
    public function setAdminAccount(string $admin_account): void
    {
        $this->admin_account = $admin_account;
    }

    /**
     * @return string
     */
    public function getAdminPassword(): string
    {
        return $this->admin_password;
    }

    /**
     * @param string $admin_password
     */
    public function setAdminPassword(string $admin_password): void
    {
        $this->admin_password = password_hash($admin_password, PASSWORD_DEFAULT);
    }

    /**
     * checkAdminPassword
     *
     * @param string $admin_password
     * @return bool
     */
    public function checkAdminPassword(string $admin_password): bool
    {
        return password_verify($admin_password, $this->getAdminPassword());
    }

    /**
     * @return string
     */
    public function getAdminSession(): string
    {
        return $this->admin_session;
    }

    /**
     * @param string $admin_session
     */
    public function setAdminSession(?string $admin_session): void
    {
        $this->admin_session = $admin_session;
    }

    /**
     * @return int
     */
    public function getLastLoginTime(): int
    {
        return $this->last_login_time;
    }

    /**
     * @param int $last_login_time
     */
    public function setLastLoginTime(int $last_login_time): void
    {
        $this->last_login_time = $last_login_time;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    /**
     * isDisable
     *
     * @return bool
     */
    public function isDisable(): bool
    {
        return $this->getStatus() === self::STATUS_DISABLE;
    }
}