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
    private $self;

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
            $ref = new \ReflectionClass(get_class($this));
            foreach ($ref->getProperties(\ReflectionProperty::IS_PROTECTED) as $v) {
                $name = $v->getName();
                if (strpos($name, 'p_') === 0) {
                    $outname = str_replace('p_', '', $name);
                    $value = $this->self->$name;
                    if ($value) {
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
            if ($this->self->updateField) {
                foreach ($this->self->updateField as $k => $v) {
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
        $real = 'p_' . $name;
        $update = true;
        if (isset($this->self->$real) && $this->self->$real == $value) {
            $update = false;
        }
        if (is_string($this->self->$real)) {
            $value = strval($value);
        }
        if ($update) {
            $this->self->updateField[$name] = $value;
            if (isset($this->self->$real)) {
                $this->self->$real = $value;
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
            foreach ($this->self->updateField as $k => $v) {
                if (in_array($k, $this->self->jsonStrField)) {
                    $ret[$k] = json_encode($v);
                } elseif (in_array($k, $this->self->splitStrField)) {
                    $ret[$k] = implode($this->self->splitStr, $v);
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
        return json_decode($this->self->__toString(), true);
    }
}
