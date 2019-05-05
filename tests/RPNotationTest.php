<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/5/5
 * Time: 15:35
 * File: RPNotationTest.php
 */
require_once __DIR__ . '/../src/__INIT.php';

/**
 * Class RPNotationTest
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2019.05.05
 */
class RPNotationTest extends PHPUnit\Framework\TestCase
{
    /**
     * Function testCalculate
     *
     * @return void
     * @since  2019.05.05
     */
    public function testCalculate()
    {
        $str0 = 'a+b*(c+d)';
        $str1 = 'a + b * [c+(d - a)]+1.5';
        $str2 = 'a + d % c * (b+1)^(c-1)';
        $data = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4];
        $result0 = 15;
        $result1 = 14.5;
        $result2 = 10;
        $this->assertEquals($result0, \ZF\RPNotation::calculate($str0, $data));
        $this->assertEquals($result1, \ZF\RPNotation::calculate($str1, $data));
        $this->assertEquals($result2, \ZF\RPNotation::calculate($str2, $data));
    }
}
