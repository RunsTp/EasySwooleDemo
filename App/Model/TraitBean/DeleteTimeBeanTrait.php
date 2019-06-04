<?php


namespace App\Model\TraitBean;

/**
 * Trait DeleteTimeBeanTrait
 * Bean 内引入此 trait 设置软删除
 *
 * @package App\Model\Bean
 */
trait DeleteTimeBeanTrait
{
    /** @var int 删除时间 0 为正常 */
    protected $delete_time = 0;

    /**
     * @return int
     */
    public function getDeleteTime(): int
    {
        return $this->delete_time;
    }

    /**
     * @param int $delete_time
     */
    public function setDeleteTime(int $delete_time): void
    {
        $this->delete_time = $delete_time;
    }

    /**
     * setDeleted
     */
    public function setDeleted() : void
    {
        $this->setDeleteTime(time());
    }
}