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
use \ZF\DingTalk\{
    UserInfo,
    DepartmentInfo,
    ExtContactInfo,
    MessageInfo,
    ChatInfo,
    Crypto
};

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
{"userid":"jamers","name":"测试用户","orderInDepts":"{\"1\":10,\"2\":20}","department":[1,2],"mobile":"13000000000","extattr":"{\"爱好\":\"旅游\",\"测试\":\"Test\"}","custom":{"0":"自定义1","1":"自定义2","6":"测试"}}
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

    /**
     * 部门信息实体类测试
     *
     * @return void
     * @since  2019.05.17
     */
    public function testDeparmmentInfo()
    {
        $source = '{"parentid":"1","name":"测试部门","deptPermits":"2|3|4","outerPermitUsers":"demo|test"}';
        $actual = '{"parentid":"1","name":"测试","deptPermits":"2|3|4","outerPermitUsers":"demo|test|333","custom":{"0":"自定义1","1":"自定义2","6":"测试"}}';
        $custom = ['自定义1', '自定义2', 6 => '测试',];
        $name = '测试';
        $update = ['name' => $name, 'outerPermitUsers' => 'demo|test|333', 'custom' => $custom,];
        $dept = new DepartmentInfo($source);
        $dept->name = $name;
        $dept->parentid = 1;
        $dept->deptPermits = [2,3,4,];
        $dept->outerPermitUsers = ['demo', 'test', '333',];
        $dept->custom = $custom;
        $this->assertEquals($dept->name, $name);
        $this->assertEquals($dept->__toString(), $actual);
        $this->assertEquals($dept->getUpdateData(), $update);
        $data = json_decode($source, true);
        $this->assertEquals(new DepartmentInfo($data), new DepartmentInfo($source));
    }

    /**
     * 外部客户信息实体类测试
     *
     * @return void
     * @since  2019.05.18
     */
    public function testExtContactInfo()
    {
        $source = '{"title":"CEO","label_ids":[1,3,5],"follower_user_id":"manage","name":"外部客户","state_code":"86","company_name":"测试公司名称","mobile":"1xxxxxxxxxx"}';
        $actual = '{"title":"首席执行官","label_ids":[1,5],"follower_user_id":"manage","name":"外部客户","state_code":"086","company_name":"测试公司名称","mobile":"1xxxxxxxxx1"}';
        $title = '首席执行官';
        $mobile = '1xxxxxxxxx1';
        $ids = [1, 5,];
        $update = ['title' => $title, 'label_ids' => $ids, 'state_code' => '086', 'mobile' => $mobile, 'follower_user_id' => 'manage', 'name' => '外部客户'];
        $ext = new ExtContactInfo($source);
        $ext->title = $title;
        $ext->label_ids = $ids;
        $ext->follower_user_id = 'manage';
        $ext->name = '外部客户';
        $ext->state_code = '086';
        $ext->company_name = '测试公司名称';
        $ext->mobile = $mobile;
        $this->assertEquals($ext->title, $title);
        $this->assertEquals($ext->__toString(), $actual);
        $this->assertEquals($ext->getUpdateData(), $update);
        $data = json_decode($source, true);
        $this->assertEquals(new ExtContactInfo($data), new ExtContactInfo($source));
    }

    /**
     * 发送消息实体类测试
     *
     * @return void
     * @since  2019.05.20
     */
    public function testMessageInfo()
    {
        $source = '{"msgtype":"oa","oa":{"message_url":"","head":{"bgcolor":"FFBBBBBB","text":"TEST Info"},"body":[]}}';
        $actual = '{"msgtype":"oa","oa":{"message_url":"","head":{"bgcolor":"FFBBBBBB","text":"Demo"},"body":[]}}';
        $msg = new MessageInfo($source);
        $msg->safe = true;
        $msg->head = ['demo' => '1234', 'demo1' => [1,2,3,], 'text' => 'Demo',];
        $this->assertEquals($msg->__toString(), $actual);
        $msg = new MessageInfo();
        $msg->msgtype = 'text';
        $msg->content = 'TESTTEST';
        $actual = '{"msgtype":"text","text":{"content":"TESTTEST"}}';
        $this->assertEquals($msg->__toString(), $actual);
    }

    /**
     * 群消息实体类测试
     *
     * @return void
     * @since  2019.05.20
     */
    public function testChatInfo()
    {
        $actual = '{"name":"Demo","owner":"manage","useridlist":["test","5555"],"extidlist":["9999","1111"],"validationType":0,"mentionAllAuthority":1}';
        $update = ['name' => 'Demo', 'owner' => 'manage', 'add_useridlist' => ['test', '5555',], 'add_extidlist' => ['9999', '1111',], "validationType" => 0,"mentionAllAuthority" => 1,];
        $ci = new ChatInfo();
        $ci->name = 'Demo';
        $ci->owner = 'manage';
        $ci->useridlist = ['test', '5555',];
        $ci->extidlist = ['9999', '1111',];
        $ci->validationType = 0;
        $ci->mentionAllAuthority = 1;
        $this->assertEquals($ci->__toString(), $actual);
        $this->assertEquals($ci->getUpdateData(), $update);
    }

    /**
     * 测试解密模块
     *
     * @return void
     * @since  2019.05.22
     */
    public function testCrypto()
    {
        $time = 1558509909;
        $msg = date('Y-m-d H:i:s', $time) . '_Jamers';
        $aes = 'gqt5mlqa5y2leqzdn7xnqzuyxhh7op9xwu584t06vqz';
        $token = 'demodemo';
        $nonce = 'oqctu1be52';
        $suite = '1011111';

        $co = new Crypto($token, $aes, $suite);
        $data = $co->encryptMsg($msg, $nonce, $time);
        $dec = $co->decryptMsg($data['msg_signature'], $data['timeStamp'], $data['nonce'], $data['encrypt']);
        $this->assertEquals($dec, $msg);
    }
}
