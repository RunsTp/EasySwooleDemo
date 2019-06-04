<?php


namespace App\Model;


use EasySwoole\Mysqli\Mysqli;

/**
 * Class BaseModel
 *
 * @package App\Model
 */
abstract class BaseModel
{
    /** @var Mysqli|null 数据库句柄 */
    private $databaseHandler;

    /** @var string 数据库表名 */
    protected $table;
    /** @var string 数据库主键字段 */
    protected $pk;

    /**
     * BaseModel constructor.
     *
     * @param Mysqli $databaseHandler
     */
    public function __construct(Mysqli $databaseHandler)
    {
        $this->databaseHandler = $databaseHandler;
    }

    /**
     * @return Mysqli
     */
    protected function dbConnector() : Mysqli
    {
        return $this->databaseHandler;
    }
}