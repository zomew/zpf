<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/02/28
 * Time: 9:09
 * File: CommonTest.php
 */

include_once('../src/__INIT.php');

class CommonTest extends PHPUnit\Framework\TestCase
{

    public function testSpecialReplace()
    {
        $str1 = 'Hello, (@name@)！(@1@) (@any@)';
        $str2 = 'Hello, {{name}}！{{1}} {{any}}';
        $data = array(
            'name' => 'World',
            1   => 'php',
        );
        $result = 'Hello, World！php ';
        $this->assertEquals($result, \ZF\Common::SpecialReplace($str1,$data));
        $this->assertEquals($result, \ZF\Common::SpecialReplace($str2, $data, array('{{', '}}',)));
    }

    /**
     * @requires PHPUnit 8.0.4
     */
    public function testRequest() {
        $this->assertStringContainsStringIgnoringCase('www.baidu.com', \ZF\Common::_getRequest('https://www.baidu.com'));
    }
}
