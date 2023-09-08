<?php
declare(strict_types = 1);

namespace Yng\Db\Concern;

use Closure;
use Yng\Collection;
use Yng\Db\Exception\DataNotFoundException;
use Yng\Db\Exception\DbException;
use Yng\Db\Exception\ModelNotFoundException;
use Yng\Db\Query;
use Yng\Helper\Str;
use Yng\Model;

/**
 * 查询数据处理
 */
trait ResultOperation
{
    /**
     * 设置数据处理（支持模型）
     *
     * @param callable $filter 数据处理Callable
     * @param string   $index  索引（唯一）
     *
     * @return $this
     */
    public function filter(callable $filter, string $index = null)
    {
        if ($index) {
            $this->options['filter'][$index] = $filter;
        } else {
            $this->options['filter'][] = $filter;
        }

        return $this;
    }

    /**
     * 是否允许返回空数据（或空模型）
     *
     * @param bool $allowEmpty 是否允许为空
     *
     * @return $this
     */
    public function allowEmpty(bool $allowEmpty = true)
    {
        $this->options['allow_empty'] = $allowEmpty;

        return $this;
    }

    /**
     * 设置查询数据不存在是否抛出异常
     *
     * @param bool $fail 数据不存在是否抛出异常
     *
     * @return $this
     */
    public function failException(bool $fail = true)
    {
        $this->options['fail'] = $fail;

        return $this;
    }

    /**
     * 处理数据
     *
     * @param array $result 查询数据
     *
     * @return void
     */
    protected function result(array &$result): void
    {
        // JSON数据处理
        if (!empty($this->options['json'])) {
            $this->jsonResult($result);
        }

        // 实时读取延迟数据
        if (!empty($this->options['lazy_fields'])) {
            $id = $this->getKey($result);
            foreach ($this->options['lazy_fields'] as $field) {
                $result[$field] += $this->getLazyFieldValue($field, $id);
            }
        }

        // 查询数据处理
        foreach ($this->options['filter'] as $filter) {
            $result = call_user_func_array($filter, [$result, $this->options]);
        }

        // 获取器
        if (!empty($this->options['with_attr'])) {
            $this->getResultAttr($result, $this->options['with_attr']);
        }
    }

    /**
     * 处理数据集
     *
     * @param array $resultSet    数据集
     * @param bool  $toCollection 是否转为对象
     *
     * @return void
     */
    protected function resultSet(array &$resultSet, bool $toCollection = true): void
    {
        foreach ($resultSet as &$result) {
            $this->result($result);
        }

        // 返回Collection对象
        if ($toCollection) {
            $resultSet = new Collection($resultSet);
        }
    }

    /**
     * 使用获取器处理数据
     *
     * @param array $result   查询数据
     * @param array $withAttr 字段获取器
     *
     * @return void
     */
    protected function getResultAttr(array &$result, array $withAttr = []): void
    {
        foreach ($withAttr as $name => $closure) {
            $name = Str::snake($name);

            if (str_contains($name, '.')) {
                // 支持JSON字段 获取器定义
                [$key, $field] = explode('.', $name);

                if (isset($result[$key])) {
                    $result[$key][$field] = $closure($result[$key][$field] ?? null, $result[$key]);
                }
            } else {
                $result[$name] = $closure($result[$name] ?? null, $result);
            }
        }
    }

    /**
     * 处理空数据
     *
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws DataNotFoundException
     *
     * @return array|Model|null|static
     */
    protected function resultToEmpty()
    {
        if (!empty($this->options['fail'])) {
            $this->throwNotFound();
        } elseif (!empty($this->options['allow_empty'])) {
            return !empty($this->model) ? $this->model->newInstance() : [];
        }
    }

    /**
     * 查找单条记录 不存在返回空数据（或者空模型）
     *
     * @param mixed $data 数据
     *
     * @return array|Model|static|mixed
     */
    public function findOrEmpty($data = null)
    {
        return $this->allowEmpty(true)->find($data);
    }

    /**
     * JSON字段数据转换
     *
     * @param array $result 查询数据
     *
     * @return void
     */
    protected function jsonResult(array &$result): void
    {
        foreach ($this->options['json'] as $name) {
            if (!isset($result[$name])) {
                continue;
            }

            $result[$name] = json_decode($result[$name], true);
        }
    }

    /**
     * 查询失败 抛出异常
     *
     * @throws ModelNotFoundException
     * @throws DataNotFoundException
     *
     * @return void
     */
    protected function throwNotFound(): void
    {
        if (!empty($this->model)) {
            $class = get_class($this->model);

            throw new ModelNotFoundException('model data Not Found:'.$class, $class, $this->options);
        }

        $table = $this->getTable();

        throw new DataNotFoundException('table data not Found:'.$table, $table, $this->options);
    }

    /**
     * 查找多条记录 如果不存在则抛出异常
     *
     * @param array|string|Query|Closure $data 数据
     *
     * @throws ModelNotFoundException
     * @throws DataNotFoundException
     *
     * @return array|Collection|static[]
     */
    public function getOrFail($data = [])
    {
        return $this->failException(true)->get($data);
    }

    /**
     * 查找单条记录 如果不存在则抛出异常
     *
     * @param array|string|Query|Closure $data 数据
     *
     * @throws ModelNotFoundException
     * @throws DataNotFoundException
     *
     * @return array|Model|static|mixed
     */
    public function findOrFail($data = null)
    {
        return $this->failException(true)->find($data);
    }
}
