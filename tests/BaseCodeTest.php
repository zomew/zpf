<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/3/24
 * Time: 14:08
 * File: BaseCodeTest.php
 */

require_once __DIR__ . '/../src/__INIT.php';

/**
 * Class BaseCodeTest
 *
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2019.03.24
 */
class BaseCodeTest extends PHPUnit\Framework\TestCase
{

    /**
     * Function testBase32Decode
     *
     * @return void
     * @since  2019.03.24
     */
    public function testBase32()
    {
        $str = 'AbCdEf测试';
        $ret = 'IFREGZCFM3TLLC7IV6KQ====';
        $this->assertEquals($ret, \ZF\BaseCode::base32Encode($str));
        $this->assertEquals($str, \ZF\BaseCode::base32Decode($ret));
    }

    /**
     * Function testBase91
     *
     * @return void
     * @since  2019.03.24
     */
    public function testBase91()
    {
        $str = 'AbCdEf测试';
        $ret = 'fG_F/wAk"<C5"#S';
        $this->assertEquals($ret, \ZF\BaseCode::base91Encode($str));
        $this->assertEquals($str, \ZF\BaseCode::base91Decode($ret));
    }

    /**
     * Function testBase16
     *
     * @return void
     * @since  2019.03.24
     */
    public function testBase16()
    {
        $str = 'AbCdEf测试';
        $ret = '416243644566e6b58be8af95';
        $this->assertEquals($ret, \ZF\BaseCode::base16Encode($str));
        $this->assertEquals($str, \ZF\BaseCode::base16Decode($ret));
    }

}
