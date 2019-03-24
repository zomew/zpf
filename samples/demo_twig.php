<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/3/21
 * Time: 10:00
 * File: demo_twig.php
 */

require_once '../src/__INIT.php';

$twig = new \ZF\Twig();
$data = array(
    'navigation' => array(
        array(
            'href' => 'http://bbs.zomew.com',
            'caption' => 'BBS',
        ),
        array(
            'href' => 'http://www.baidu.com',
            'caption' => 'baidu',
        ),
    ),
    'attr' => array(
        'name' => 'testattribute',
    ),
    'a_variable' => 100,
    'name' => 'test',
);
/**
 * 测试直接调用Environment内的函数
 */
echo $twig->render('child.twig', $data);

/**
 * 测试未封装方法(直接调用TWIG中的类对象)
 */
$l = $twig->Loader->FilesystemLoader('views');
echo $twig->Environment($l, array())->render('child.twig', $data);
