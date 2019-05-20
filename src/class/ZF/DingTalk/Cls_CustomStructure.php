<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/5/16
 * Time: 19:36
 * File: Cls_CustomStructure.php
 */

namespace ZF\DingTalk;

/**
 * 自定义JSON对象基础类，用于处理复杂JSON字符串组装
 *
 * @package ZF\DingTalk
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2019.05.16
 */
abstract class CustomStructure
{
    /**
     * @var array 需要将数组转换成JSON串的字段
     */
    protected $jsonStrField = [];

    /**
     * @var array 更新的字段
     */
    protected $updateField = [];

    /**
     * @var array 需要将数组转换成连接串的字段
     */
    protected $splitStrField = [];

    /**
     * @var string 数组转换成连接字符串的字符
     */
    protected $splitStr = '|';

    /**
     * @var bool 是否已实例化
     */
    protected $isConstruct = false;

    /**
     * @var object 子类对象
     */
    protected $self;

    /**
     * @var array 更新数组必须有的字段
     */
    protected $updateNeedField = [];

    /**
     * @var array 转字符串需要忽略的字段
     */
    protected $toStringNeedHideField = [];

    /**
     * 将类转为对应JSON串魔术方法
     *
     * @return false|string
     * @since  2019.05.16
     */
    public function __toString()
    {
        return $this->magicToString();
    }

    /**
     * 让魔术方法可以复用
     *
     * @return false|string
     * @since  2019.05.18
     */
    protected function magicToString()
    {
        $ret = [];
        try {
            $ref = new \ReflectionClass(get_class($this));
            foreach ($ref->getProperties(\ReflectionProperty::IS_PROTECTED) as $v) {
                $name = $v->getName();
                if (strpos($name, 'p_') === 0) {
                    $outname = str_replace('p_', '', $name);
                    if (!in_array($outname, $this->toStringNeedHideField)) {
                        $value = $this->self->$name;
                        if ($value || (gettype($value) == 'integer' && $value !== null)) {
                            if (is_array($value)) {
                                if (in_array($outname, $this->self->jsonStrField)) {
                                    $ret[$outname] = json_encode($value, JSON_UNESCAPED_UNICODE);
                                } elseif (in_array($outname, $this->self->splitStrField)) {
                                    $ret[$outname] = implode($this->self->splitStr, $value);
                                } else {
                                    $ret[$outname] = $value;
                                }
                            } else {
                                $ret[$outname] = $value;
                            }
                        }
                    }
                }
            }
            if ($this->self->updateField) {
                foreach ($this->self->updateField as $k => $v) {
                    if (!in_array($k, $this->toStringNeedHideField)) {
                        if (!isset($ret[$k])) {
                            if (is_array($v) && in_array($k, $this->self->jsonStrField)) {
                                $ret[$k] = json_encode($v, JSON_UNESCAPED_UNICODE);
                            } elseif (is_array($v) && in_array($k, $this->self->splitStrField)) {
                                $ret[$k] = implode($this->self->splitStr, $v);
                            } else {
                                $ret[$k] = $v;
                            }
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
                        } elseif (in_array($k, $this->splitStrField)) {
                            $this->$name = explode($this->splitStr, $v);
                        } else {
                            $this->$name = $v;
                        }
                    } elseif ($v || is_bool($v)) {
                        $this->$name = $v;
                    }
                }
            }
        }
        $this->self = &$this;
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
                $ref = new \ReflectionClass(get_class($this));
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
     * 静态调用将JSON串或数组转换成用户信息对象
     * @param string $data
     *
     * @return object
     * @static
     * @since  2019.05.16
     */
    public static function parseData($data = '')
    {
        $cls = get_called_class();
        return new $cls($data);
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
        return $this->magicGet($name);
    }

    /**
     * 定义可调用的方法，保证代码复用
     * @param string $name
     *
     * @return mixed|null
     * @since  2019.05.18
     */
    protected function magicGet(string $name)
    {
        $ret = null;
        $real = 'p_' . $name;
        if (isset($this->self->$real)) {
            $ret = $this->self->$real;
        } elseif (isset($this->self->updateField[$name])) {
            $ret = $this->self->updateField[$name];
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
        $this->magicSet($name, $value);
    }

    /**
     * 保证正常情况下还能调用，简单重复代码段
     * @param string $name
     * @param        $value
     *
     * @return void
     * @since  2019.05.18
     */
    protected function magicSet(string $name, $value)
    {
        $real = 'p_' . $name;
        $update = true;
        if (isset($this->$real)) {
            $type = gettype($this->$real);
            switch ($type) {
                case 'boolean':
                    $value = boolval($value);
                    break;
                case 'integer':
                    $value = intval($value);
                    break;
                case 'double':
                case 'float':
                    $value = floatval($value);
                    break;
                case 'string':
                    $value = strval($value);
                    break;
                case 'array':
                    if ($this->$real == $value) {
                        $value = $this->$real;
                    }
                    break;
                case 'object':
                    if (gettype($value) == $type) {
                        $cls = get_class($this->$real);
                        $cur = get_class($value);
                        if ($cls != $cur) {
                            trigger_error("设置值与原值不是同一类，需要类名：{$cls}，当前类名：{$cur}", E_USER_ERROR);
                            return;
                        }
                    } else {
                        trigger_error("设置值与原值类型不同", E_USER_ERROR);
                        return;
                    }
                    break;
                case 'NULL':
                    //空值，直接设置
                    break;
                default:
                    if (gettype($value) != $type) {
                        trigger_error("设置值与原值类型不同", E_USER_ERROR);
                        return;
                    }
                    break;
            }
            if ($this->$real === $value) {
                $update = false;
            }
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
        if ($this->self->updateField) {
            if ($this->self->updateNeedField) {
                foreach ($this->self->updateNeedField as $k => $v) {
                    $value = $this->$v;
                    if (is_numeric($k)) {
                        if (is_string($v) && $v && ($value || gettype($value) == 'boolean')) {
                            $this->addField($ret, $v, $value);
                        }
                    } elseif (is_string($k)) {
                        if (is_string($v) && $v && ($value || gettype($value) == 'boolean')) {
                            $this->addField($ret, $k, $value);
                        }
                    }
                }
            }
            foreach ($this->self->updateField as $k => $v) {
                $this->addField($ret, $k, $v);
            }
        }
        return $ret;
    }

    /**
     * 添加字段到数组中
     * @param $ret
     * @param $k
     * @param $v
     *
     * @return void
     * @since  2019.05.18
     */
    protected function addField(&$ret, $k, $v)
    {
        if (in_array($k, $this->self->jsonStrField) && is_array($v)) {
            $ret[$k] = json_encode($v);
        } elseif (in_array($k, $this->self->splitStrField) && is_array($v)) {
            $ret[$k] = implode($this->self->splitStr, $v);
        } else {
            $ret[$k] = $v;
        }
    }

    /**
     * 获取用户数据数组
     *
     * @return mixed
     * @since  2019.05.16
     */
    public function getArray()
    {
        return json_decode($this->self->__toString(), true);
    }
}
