<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/01/25
 * Time: 13:25
 * File: test_self.php
 */

require_once '../src/__INIT.php';

//\Demo::hello();

$a = \PackDemo::sConnectDB();
$rs = $a->order('id desc')->getOne('member');
var_dump($rs);
