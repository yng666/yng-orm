<?php
declare(strict_types = 1);

namespace Yng\Db\Exception;

/**
 * 模型找不到异常
 */
class ModelNotFoundException extends DbException
{
    protected $model;

    /**
     * 构造方法
     *
     * @param string $message
     * @param string $model
     * @param array  $config
     */
    public function __construct(string $message, string $model = '', array $config = [])
    {
        $this->message = $message;
        $this->model   = $model;

        $this->setData('Database Config', $config);
    }

    /**
     * 获取模型类名
     *
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }
}
