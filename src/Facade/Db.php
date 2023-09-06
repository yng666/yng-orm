<?php
namespace Yng\Facade;

use Yng\Facade;

/**
 * @see \Yng\DbManager
 * @mixin \Yng\DbManager
 */
class Db extends Facade
{
    /**
     * 获取当前Facade对应类名（或者已经绑定的容器对象标识）
     *
     * @return string
     */
    protected static function getFacadeClass()
    {
        return 'Yng\DbManager';
    }
}
