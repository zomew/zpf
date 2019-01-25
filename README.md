# Zomew PHP framework
Zomew PHP framework(zpf), 一个原自用按需加载PHP框架，可自由扩展，包含mysql/redis/mongodb常用模块。
该框架可以与任何框架组合使用而不影响原框架的加载或调用方法。可以快速实现代码复用。目前框架语法为PHP 7.0，低版本不适用。

如果有任何使用上的不便，欢迎批评指教。

[github](https://github.com/zomew/zpf "github")  Author: Jamers 

配置文件使用方式：
- 复制config.example.php为config.php修改内部设置
- 考虑到多环境配置问题，可复制一个config.xxx.php，xxx为运行代码服务器的名称，运行过程中将优先加载此文件，配置文件加载顺序为：config.xxx.php > config.php > config.example.php

原生调用演示：
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

自行封装调用演示：
```php
//加载初始化文件
include_once("__INIT.php");

//调用简单的自行封装函数
\Demo::hello();

//快速调用自定义配置数据连接 根据演示代码读取配置为 SELF_CONFIG下的db设置
//其它类型连接也是同理
$db = \PackDemo::sConnectDB();
$rs = $db->order('id desc')->getOne('member');
var_dump($rs);
```