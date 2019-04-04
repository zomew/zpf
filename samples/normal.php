<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/01/24
 * Time: 9:45
 * File: test.php
 */

require_once '../src/__INIT.php';

//基本用法：调用指定模块函数
echo \ZF\Common::getFullUrl() . "<br>";

//新建实体封装类
$obj = new \ZF\Entity();

//调用mysql模块演示
//也可使用： $db = new \ZF\Pdomysql();
$obj->loadClass('db');
$rs = $obj->db->where(array('id' => 100,))->getOne('member');
var_dump($rs);

//调用redis模块演示
//也可使用： $rl = new \ZF\Redislock();
$obj->loadClass('rl');
$obj->rl->redis->set('demo', date('YmdHis'));
$str = $obj->rl->redis->get('demo');
var_dump($str);
