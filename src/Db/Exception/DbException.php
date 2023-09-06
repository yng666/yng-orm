<?php
declare(strict_types = 1);

namespace Yng\Db\Exception;

use Yng\Exception;

/**
 * Database相关异常处理类
 */
class DbException extends Exception
{
    /**
     * Db异常构造器
     *
     * @param string $message
     * @param array  $config
     * @param string $sql
     * @param int    $code
     */
    public function __construct(string $message, array $config = [], string $sql = '', int $code = 10500)
    {
        $this->message = $message;
        $this->code    = $code;

        $this->setData('Database Status', [
            'Error Code'    => $code,
            'Error Message' => $message,
            'Error SQL'     => $sql,
        ]);

        unset($config['username'], $config['password']);
        $this->setData('Database Config', $config);
    }
}
