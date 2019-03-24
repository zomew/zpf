<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/02/28
 * Time: 9:09
 * File: CommonTest.php
 */

require_once __DIR__ . '/../src/__INIT.php';

/**
 * Class CommonTest
 *
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2019.03.24
 */
class CommonTest extends PHPUnit\Framework\TestCase
{

    /**
     * SpecialReplace Tests
     *
     * @return void
     * @since  2019.03.24
     */
    public function testSpecialReplace()
    {
        $str1 = 'Hello, (@name@)！(@1@) (@any@)';
        $str2 = 'Hello, {{name}}！{{1}} {{any}}';
        $data = array(
            'name' => 'World',
            1   => 'php',
        );
        $result = 'Hello, World！php ';
        $this->assertEquals($result, \ZF\Common::specialReplace($str1, $data));
        $this->assertEquals(
            $result,
            \ZF\Common::specialReplace($str2,  $data,  array('{{', '}}',))
        );
    }

    /**
     * Request Tests
     *
     * @requires PHPUnit 8.0.4
     *
     * @return void
     */
    public function testRequest()
    {
        $this->assertStringContainsStringIgnoringCase(
            'www.baidu.com',
            \ZF\Common::getRequest('https://www.baidu.com')
        );
        $this->assertStringContainsStringIgnoringCase(
            'www.baidu.com',
            \ZF\Common::postRequest('https://www.baidu.com', [])
        );
    }

    /**
     * SaveLog Tests
     *
     * @return void
     * @since  2019.03.24
     */
    public function testSaveLog()
    {
        $file = __CLASS__ . '.txt';
        $abs = ZF_ROOT . 'Logs/' . $file;
        if (file_exists($abs)) {
            @unlink($abs);
        }
        \ZF\Common::saveLog($file, 'Hello World');
        $this->assertFileExists($abs);
        @unlink($abs);
    }

    /**
     * Input Tests
     *
     * @return void
     * @since  2019.03.24
     */
    public function testInput()
    {
        $def = 'input default value';
        $val = 'Hello World';
        $_REQUEST = [];
        $_GET = [];
        $_POST = [];
        $this->assertEquals($def, \ZF\Common::input('def', $def));
        $_REQUEST = ['def' => $val,];
        $this->assertEquals($val, \ZF\Common::input('def', $def));
        $this->assertEquals($def, \ZF\Common::input('get.def', $def));
        $this->assertEquals($def, \ZF\Common::input('post.def', $def));
        $_GET = ['def' => $val,];
        $_REQUEST = $_GET;
        $this->assertEquals($val, \ZF\Common::input('def', $def));
        $this->assertEquals($val, \ZF\Common::input('get.def', $def));
        $this->assertEquals($def, \ZF\Common::input('post.def', $def));
        $_POST = $_GET;
        $_GET = [];
        $this->assertEquals($val, \ZF\Common::input('def', $def));
        $this->assertEquals($def, \ZF\Common::input('get.def', $def));
        $this->assertEquals($val, \ZF\Common::input('post.def', $def));
    }

    /**
     * StrReplaceOnce Tests
     *
     * @return void
     * @since  2019.03.24
     */
    public function teststrReplaceOnce()
    {
        $str = 'ABCDABCDABCD';
        $search = 'ABCD';
        $replace = 'XYZ';
        $ret = 'XYZABCDABCD';
        $this->assertEquals(
            $ret,
            \ZF\Common::strReplaceOnce($search, $replace, $str)
        );
    }

    /**
     * Function teststrReplaceLimit
     *
     * @return void
     * @since  2019.03.24
     */
    public function teststrReplaceLimit()
    {
        $str = 'ABCDABCDABCD';
        $search = 'ABCD';
        $replace = 'XYZ';
        $ret = 'XYZABCDABCD';
        $this->assertEquals(
            'XYZXYZXYZ',
            \ZF\Common::strReplaceLimit($search, $replace, $str, -1)
        );
        $this->assertEquals(
            $str,
            \ZF\Common::strReplaceLimit($search, $replace, $str, 0)
        );
        $this->assertEquals(
            $ret,
            \ZF\Common::strReplaceLimit($search, $replace, $str, 1)
        );
        $this->assertEquals(
            'XYZXYZABCD',
            \ZF\Common::strReplaceLimit($search, $replace, $str, 2)
        );
    }

    /**
     * Function testjsonStr
     *
     * @return void
     * @since  2019.03.24
     */
    public function testjsonStr()
    {
        $ary = ['code' => 0, 'msg' => 'success',];
        $json = '{"code":0,"msg":"success"}';
        $jsonp = 'callback(' . $json . ');';
        $this->assertEquals($json, \ZF\Common::jsonStr($ary, 'callback', false));
        $_GET = ['cbstr' => 'callback',];
        $this->assertEquals($jsonp, \ZF\Common::jsonStr($ary, 'cbstr', false));
    }

    /**
     * Function testLockFile
     *
     * @return void
     * @since  2019.03.24
     */
    public function testLockFile()
    {
        $file = '_lockfile.txt';
        $fh = \ZF\Common::tryToLockFile($file);
        $this->assertTrue(!!$fh);
        \ZF\Common::unlockFile($fh, $file);
        $this->assertFileNotExists($file);
        $this->markTestIncomplete('测试过程相对独立，无法测试锁定，跳过');
    }

    /**
     * Function testgetNeedArray
     *
     * @return void
     * @since  2019.03.24
     */
    public function testgetNeedArray()
    {
        $data = ['s1' => 'SS1', 's2' => 'SSS2', 2 => 'Int2', '5' => 'String5',
            's4' => ['test' => 'Subvalue', 'name' => 'php',],
            ];
        $list1 = ['s1', '5', 's3',];
        $list2 = 's1,5,s3';
        $ret = ['s1' => 'SS1', '5' => 'String5', 's3' => null,];
        $this->assertEquals($ret, \ZF\Common::getNeedArray($data, $list1));
        $this->assertEquals($ret, \ZF\Common::getNeedArray($data, $list2));
        $list1 = ['Name1' => '5', 'Key' => 's4.test', 'Static' => ':001',];
        $ret = ['Name1' => 'String5', 'Key' => 'Subvalue', 'Static' => '001',];
        $this->assertEquals($ret, \ZF\Common::getNeedArray($data, $list1));

    }

    /**
     * Function testGuid
     *
     * @return void
     * @since  2019.03.24
     */
    public function testGuid()
    {
        $guid = \ZF\Common::generalGUID();
        $this->assertTrue(\ZF\Common::isGuid($guid));
        $guid = \ZF\Common::tagGuid();
        $this->assertTrue(\ZF\Common::checkTagGuid($guid));

    }

    /**
     * Function testgetPathInfo
     *
     * @return void
     * @since  2019.03.24
     */
    public function testgetPathInfo()
    {
        $ret = '/index/config';
        $url = '/index.php/index/config';
        $_SERVER['PATH_INFO'] = $ret;
        $this->assertEquals($ret, \ZF\Common::getPathInfo());
        unset($_SERVER['PATH_INFO']);
        $_SERVER['REQUEST_URI'] = $url;
        $this->assertEquals($ret, \ZF\Common::getPathInfo());
    }

    /**
     * Function testgetSelfUrl
     *
     * @return void
     * @since  2019.03.24
     */
    public function testgetSelfUrl()
    {
        $_SERVER['REQUEST_URI'] = '/index.php?test=001';
        $_SERVER['REQUEST_SCHEME'] = 'http';
        $_SERVER['HTTP_HOST'] = 'localhost';
        $full = 'http://localhost/index.php?test=001';
        $self = 'http://localhost/index.php';
        $this->assertEquals($full, \ZF\Common::getSelfUrl());
        $this->assertEquals($self, \ZF\Common::getSelfUrl(false));
    }

    /**
     * Function testphoneMask
     *
     * @return void
     * @since  2019.03.24
     */
    public function testphoneMask()
    {
        $name = '测试13900000000';
        $array = [
            ['name' => 'name1',],
            ['name' => $name,],
        ];
        $clean = '测试139****0000';
        $ary = [
            ['name' => 'name1',],
            ['name' => $clean,],
        ];
        $this->assertEquals($clean, \ZF\Common::phoneMask($name));
        $this->assertEquals($ary, \ZF\Common::phoneMaskArray($array, 'name'));
    }

    /**
     * Function testrandStr
     *
     * @return void
     * @since  2019.03.24
     */
    public function testrandStr()
    {
        $len = 10;
        $list = 'x';
        $this->assertEquals($len, strlen(\ZF\Common::randStr($len)));
        $this->assertEquals(
            str_repeat($list, $len),
            \ZF\Common::randStr($len, $list)
        );
    }

    /**
     * Function testisAjax
     *
     * @return void
     * @since  2019.03.24
     */
    public function testisAjax()
    {
        $this->assertFalse(\ZF\Common::isAjax());
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XmlHttpRequest';
        $this->assertTrue(\ZF\Common::isAjax());

    }

    /**
     * Function testgetHost
     *
     * @return void
     * @since  2019.03.24
     */
    public function testgetHost()
    {
        $_SERVER['HTTP_HOST'] = 'localhost';
        $this->assertEquals('http://localhost', \ZF\Common::getHost());
    }

    /**
     * Function testmbStringToArray
     *
     * @return void
     * @since  2019.03.24
     */
    public function testUtf8Function()
    {
        $str = '中文eng测试';
        $ret = ['中', '文', 'e', 'n', 'g', '测', '试',];
        $this->assertEquals($ret, \ZF\Common::mbStringToArray($str));
        $this->assertEquals(20013, \ZF\Common::uniord('中'));
        $this->assertEquals(11, \ZF\Common::getLength($str));
        $this->assertEquals('中文eng测...', \ZF\Common::cutStr($str, 6, true));
        $this->assertEquals('中文en...', \ZF\Common::cutStr($str, 6, false));
    }

    /**
     * Function testpaging
     *
     * @return void
     * @since  2019.03.24
     */
    public function testpaging()
    {
        $count = 99;
        $page = 6;
        $size = 20;
        $max = 0;
        \ZF\Common::paging($count, $page, $size, $max);
        $this->assertEquals(5, $max);
        $this->assertEquals(5, $page);
    }

    /**
     * Function testSkip
     *
     * @return void
     * @since  2019.03.24
     */
    public function testSkip()
    {
        $ret = [
            'headerSetNX',
            'arrayRandomAssoc',
            'getIP',
            'checkOrigin',
            'loadConfig',
            'loadConfigData',
        ];
        $this->markTestIncomplete("暂未测试的函数列表：" . implode(',', $ret));
    }
}
