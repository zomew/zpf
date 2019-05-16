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
class UserInfo
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
    private $jsonStrField = ['orderInDepts', 'extattr',];

    /**
     * @var array 更新的字段
     */
    private $updateField = [];
    /**
     * @var bool 是否已实例化
     */
    private $isConstruct = false;

    /**
     * 将类转为对应JSON串魔术方法
     *
     * @return false|string
     * @since  2019.05.16
     */
    public function __toString()
    {
        $ret = [];
        try {
            $ref = new \ReflectionClass($this);
            foreach ($ref->getProperties(\ReflectionProperty::IS_PROTECTED) as $v) {
                $name = $v->getName();
                $outname = str_replace('p_', '', $name);
                $value = $this->$name;
                if ($value || is_bool($value)) {
                    if (is_array($value)) {
                        if (in_array($outname, $this->jsonStrField)) {
                            $ret[$outname] = json_encode($value, JSON_UNESCAPED_UNICODE);
                        } else {
                            $ret[$outname] = $value;
                        }
                    } else {
                        $ret[$outname] = $value;
                    }
                }
            }
            if ($this->updateField) {
                foreach ($this->updateField as $k => $v) {
                    if (!isset($ret[$k])) {
                        if (is_array($v) && in_array($k, $this->jsonStrField)) {
                            $ret[$k] = json_encode($v, JSON_UNESCAPED_UNICODE);
                        } else {
                            $ret[$k] = $v;
                        }
                    }
                }
            }
        } catch (\ReflectionException $e) {
        }
        return json_encode($ret, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 实例化，将传入字符串或数组转换成对象
     * @param string $data
     */
    public function __construct($data = '')
    {
        $this->cleanPublicProperty();
        if ($data) {
            if (is_string($data)) {
                $data = @json_decode($data, true);
            }
            if (is_array($data)) {
                foreach ($data as $k => $v) {
                    $name = 'p_' . $k;
                    if (is_string($v) && $v) {
                        if (in_array($k, $this->jsonStrField)) {
                            $this->$name = @json_decode($v, true);
                        } else {
                            $this->$name = $v;
                        }
                    } elseif ($v || is_bool($v)) {
                        $this->$name = $v;
                    }
                }
            }
        }
    }

    /**
     * 静态调用将JSON串或数组转换成用户信息对象
     * @param string $data
     *
     * @return UserInfo
     * @static
     * @since  2019.05.16
     */
    public static function parseData($data = '')
    {
        return new self($data);
    }

    /**
     * 清除公用属性，保证魔术方法接管
     *
     * @return void
     * @since  2019.05.16
     */
    private function cleanPublicProperty()
    {
        if (!$this->isConstruct) {
            try {
                $ref = new \ReflectionClass($this);
                foreach ($ref->getProperties(\ReflectionProperty::IS_PUBLIC) as $v) {
                    $name = $v->getName();
                    unset($this->$name);
                }
            } catch (\ReflectionException $e) {
            }
            $this->isConstruct = true;
        }
    }

    /**
     * 魔术方法获取值
     * @param string $name
     *
     * @return mixed
     * @since  2019.05.16
     */
    public function __get(string $name)
    {
        $ret = null;
        $real = 'p_' . $name;
        if (isset($this->$real)) {
            $ret = $this->$real;
        } elseif (isset($this->updateField[$name])) {
            $ret = $this->updateField[$name];
        }
        return $ret;
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

    /**
     * 获取用户数据数组
     *
     * @return mixed
     * @since  2019.05.16
     */
    public function getArray()
    {
        return json_decode($this->__toString(), true);
    }
}
