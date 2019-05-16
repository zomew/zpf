<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/5/16
 * Time: 16:08
 * File: DingTalkTest.php
 */
require_once __DIR__ . '/../src/__INIT.php';

use \ZF\DingTalk;
use \ZF\DingTalk\UserInfo;

class DingTalkTest extends PHPUnit\Framework\TestCase
{
    /**
     * 获取配置文件测试
     *
     * @return void
     * @since  2019.05.16
     */
    public function testGetConfig()
    {
        $config = DingTalk::getConfig();
        $this->assertNotEmpty($config);
        $this->assertArrayHasKey('APP_KEY', $config);
    }

    /**
     * 生成操作URL测试
     *
     * @return void
     * @since  2019.05.16
     */
    public function testBuildOperateUrl()
    {
        $opt = 'demo/test';
        $url = DingTalk::buildOperateUrl($opt, ['debug' => 1,]);
        $this->assertStringContainsString($opt, $url);
        $this->assertStringContainsString('debug=1', $url);
    }

    /**
     * 获取AccessToken测试
     *
     * @return void
     * @since  2019.05.16
     */
    public function testGetAccessToken()
    {
        $this->assertNotEmpty(DingTalk::getAccessToken());
    }

    /**
     * 用户信息实体类测试
     *
     * @return void
     * @since  2019.05.16
     */
    public function testUserInfo()
    {
        $source = <<<EOT
{"userid":"jamers","name":"测试","orderInDepts":"{\"1\":10,\"2\":20}","department":[1,2],"mobile":"13000000000","isHide":false,"isSenior":false,"extattr":"{\"\\u7231\\u597d\":\"\\u65c5\\u6e38\",\"\\u6d4b\\u8bd5\":\"Test\"}"}
EOT;
        $actual = <<<EOT
{"userid":"jamers","name":"测试用户","orderInDepts":"{\"1\":10,\"2\":20}","department":[1,2],"mobile":"13000000000","isHide":false,"isSenior":false,"extattr":"{\"爱好\":\"旅游\",\"测试\":\"Test\"}","custom":{"0":"自定义1","1":"自定义2","6":"测试"}}
EOT;
        $update = ['userid' => 'jamers', 'name' => '测试用户', 'custom' => ['自定义1', '自定义2', 6 => '测试',],];
        $mobile = '13000000000';
        $user = new UserInfo($source);
        $user->name = '测试用户';
        $user->department = [1, 2,];
        $user->orderInDepts = [1 => 10, 2 => 20,];
        $user->mobile = $mobile;
        $user->extattr = ["爱好" => "旅游", "测试" => 'Test',];
        $user->custom = ['自定义1', '自定义2', 6 => '测试',];
        $this->assertEquals($user->mobile, $mobile);
        $this->assertEquals($user->__toString(), $actual);
        $this->assertEquals($user->getUpdateData(), $update);
        $data = json_decode($source, true);
        $this->assertEquals(new UserInfo($data), new UserInfo($source));
    }
}
