<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/01/25
 * Time: 13:23
 * File: Cls_Demo.php
 */

/**
 * 演示如何建立自己的类
 * @author Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since 2019.01.25
 *
 * Class Demo
 */

class Demo {
    /**
     * 在自己的类里可以直接调用框架里的类
     * @since 2019.01.25
     *
     */
    public static function hello() {
        $name = \ZF\Common::input('name', 'World');
        echo "Hello {$name}";
    }
}