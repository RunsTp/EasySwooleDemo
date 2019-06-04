<?php


namespace App\Model;


use EasySwoole\Spl\SplBean;

class BaseTimeBean extends SplBean
{
    /** @var int|null 创建时间 */
    protected $create_time;
    /** @var int|null 更新时间 */
    protected $update_time;

    /**
     * initialize
     */
    protected function initialize(): void
    {
        parent::initialize();
        $this->create_time = $this->create_time ?? time();
        $this->update_time = $this->update_time ?? time();
    }

    /**
     * @return int|null
     */
    public function getCreateTime(): ?int
    {
        return $this->create_time;
    }

    /**
     * @param int|null $create_time
     */
    public function setCreateTime(?int $create_time): void
    {
        $this->create_time = $create_time;
    }

    /**
     * @return int|null
     */
    public function getUpdateTime(): ?int
    {
        return $this->update_time;
    }

    /**
     * @param int|null $update_time
     */
    public function setUpdateTime(?int $update_time): void
    {
        $this->update_time = $update_time;
    }

    /**
     * setUpdated
     */
    public function setUpdated() : void
    {
        $this->setUpdateTime(time());
    }
}