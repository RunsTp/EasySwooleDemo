<?php


namespace App\Service\Enum;


use EasySwoole\Spl\SplEnum;

/**
 * Class ClientType
 * 客户端类型
 *
 * @package App\Service\Enum
 */
class ClientType extends SplEnum
{
    /** @var int pc端 */
    const PC = 1;
    /** @var int 移动端 */
    const MOBILE = 2;
    /** @var int ios */
    const APP_IOS = 3;
    /** @var int 安卓 */
    const APP_ANDROID = 4;
}