<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/5/16
 * Time: 13:54
 * File: Cls_UserInfo.php
 */

namespace ZF\DingTalk;

/**
 * 阿里钉钉用户信息结构
 *
 * @package ZF\DingTalk
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2019.05.16
 */
class UserInfo extends CustomStructure
{
    /**
     * @var string 用户ID
     */
    public $userid = '';
    protected $p_userid = '';

    /**
     * @var string 姓名
     */
    public $name = '';
    protected $p_name = '';

    /**
     * @var array 部门中的排序
     */
    public $orderInDepts = [];
    protected $p_orderInDepts = [];

    /**
     * @var array 部门列表数组
     */
    public $department = [];
    protected $p_department = [];

    /**
     * @var string 手机号码
     */
    public $mobile = '';
    protected $p_mobile = '';

    /**
     * @var string 电话号码
     */
    public $tel = '';
    protected $p_tel = '';

    /**
     * @var string 工作地点
     */
    public $workPlace = '';
    protected $p_workPlace = '';

    /**
     * @var string 备注
     */
    public $remark = '';
    protected $p_remark = '';

    /**
     * @var string 邮箱地址
     */
    public $email = '';
    protected $p_email = '';

    /**
     * @var string 企业邮箱
     */
    public $orgEmail = '';
    protected $p_orgEmail = '';

    /**
     * @var string 工号
     */
    public $jobnumber = '';
    protected $p_jobnumber = '';

    /**
     * @var bool 是否隐藏手机号
     */
    public $isHide = false;
    protected $p_isHide = false;

    /**
     * @var bool 是否高管
     */
    public $isSenior = false;
    protected $p_isSenior = false;

    /**
     * @var array 扩展属性
     */
    public $extattr = [];
    protected $p_extattr = [];

    /**
     * @var array 需要将数组转换成JSON串的字段
     */
    protected $jsonStrField = ['orderInDepts', 'extattr',];

    /**
     * @var array 更新的字段
     */
    protected $updateField = [];

    /**
     * 实例化，将传入字符串或数组转换成对象
     * @param string $data
     */
    public function __construct($data = '')
    {
        parent::__construct($data);
    }

    /**
     * 魔术方法设置值，并确定更新字段
     * @param string $name
     * @param        $value
     *
     * @return void
     * @since  2019.05.16
     */
    public function __set(string $name, $value)
    {
        $real = 'p_' . $name;
        if ($name == 'userid' && $this->$real) {
            //已经有userid就不允许再修改了
            return;
        }
        $update = true;
        if (isset($this->$real) && $this->$real == $value) {
            $update = false;
        }
        if ($update) {
            $this->updateField[$name] = $value;
            if (isset($this->$real)) {
                $this->$real = $value;
            }
        }
    }

    /**
     * 获取更新字段数据
     *
     * @return array
     * @since  2019.05.16
     */
    public function getUpdateData()
    {
        $ret = [];
        if ($this->updateField) {
            if ($this->p_userid) {
                $ret['userid'] = $this->p_userid;
            }
            foreach ($this->updateField as $k => $v) {
                if (in_array($k, $this->jsonStrField)) {
                    $ret[$k] = json_encode($v);
                } else {
                    $ret[$k] = $v;
                }
            }
        }
        return $ret;
    }
}
