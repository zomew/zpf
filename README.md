# zpf
Zomew PHP framework, 一个原自用按需加载PHP框架，可自由扩展，包含mysql/redis/mongodb常用模块

如果有任何使用上的不便，欢迎批评指教。

[github](https://github.com/zomew/zpf "github")  Author: Jamers 

配置文件使用方式：
- 复制config.example.php为config.php修改内部设置
- 考虑到多环境配置问题，可复制一个config.xxx.php，xxx为运行代码服务器的名称，运行过程中将优先加载此文件，配置文件加载顺序为：config.xxx.php > config.php > config.example.php

调用方式：
```php
//加载初始化文件
include('__INIT.php');

//基本用法：调用指定模块函数
echo \ZF\Common::GetFullUrl() . "<br>";

//新建实体封装类
$obj = new \ZF\Entity();

//调用mysql模块演示
//也可使用： $db = new \ZF\Pdomysql();
$obj->LoadClass('db');
$rs = $obj->db->where(array('id' => 100,))->getOne('member');
var_dump($rs);

//调用redis模块演示
//也可使用： $rl = new \ZF\Redislock();
$obj->LoadClass('rl');
$obj->rl->redis->set('demo', date('YmdHis'));
$str = $obj->rl->redis->get('demo');
var_dump($str);

```
