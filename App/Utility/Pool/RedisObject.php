<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/10/26
 * Time: 7:23 PM
 */

namespace App\Utility\Pool;


use EasySwoole\Component\Pool\PoolObjectInterface;
use Swoole\Coroutine\Redis;

class RedisObject extends Redis implements PoolObjectInterface
{
    public function gc()
    {
        $this->close();
    }

    public function objectRestore()
    {
    }

    public function beforeUse(): bool
    {
        return true;
    }
}