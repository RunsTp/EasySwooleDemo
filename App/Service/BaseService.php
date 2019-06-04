<?php


namespace App\Service;

use App\Utility\Pool\MysqlPool;
use EasySwoole\Mysqli\Mysqli;

/**
 * 基础服务类
 *
 * @package App\Service
 */
abstract class BaseService
{
    /**
     * @var Mysqli|null
     */
    private $databaseHandler;

    /**
     * BaseService constructor.
     *
     * @param Mysqli|null $databaseHandler
     */
    final public function __construct(Mysqli $databaseHandler = null)
    {
        if (!empty($databaseHandler)) {
            $this->databaseHandler = $databaseHandler;
        }
    }

    /**
     * dbConnector
     *
     * @param int|null $timeout
     * @return Mysqli
     * @throws \EasySwoole\Component\Pool\Exception\PoolEmpty
     * @throws \EasySwoole\Component\Pool\Exception\PoolException
     */
    final protected function dbConnector(int $timeout = null) : Mysqli
    {
        if (empty($this->databaseHandler)) {
            $this->databaseHandler = MysqlPool::defer($timeout);
        }
        return $this->databaseHandler;
    }
}