# YngORM

基于PHP7.1+ 和PDO实现的ORM，支持多数据库，1.0版本主要特性包括：

* 基于PDO和PHP强类型实现
* 支持原生查询和查询构造器
* 自动参数绑定和预查询
* 简洁易用的查询功能
* 强大灵活的模型用法
* 支持预载入关联查询和延迟关联查询
* 支持多数据库及动态切换
* 支持`MongoDb`
* 支持分布式及事务
* 支持断点重连
* 支持`JSON`查询
* 支持数据库日志
* 支持`PSR-16`缓存及`PSR-3`日志规范


## 安装
```sh
composer require yng/yng-orm
```

## 文档

详细参考 [YngORM开发指南](https://www.kancloud.cn/manual/Yng-orm/content)


# 代码执行流程
```php
User::find(1);

// // 有数据
// 1.先走继承的类 Yng\Model -> 找到find方法,它在数据查询基础类里Yng\Db\BaseQuery::find()
// 2.在Yng\Db\BaseQuery::find()里,它会跑到数据连接器里的 Ynd\Db\PDOConnection::find()
// 3.在Ynd\Db\PDOConnection::find()里,会先执行 Yng\Db\Builder::get() 生成一条sql语句 ,然后执行语句
```