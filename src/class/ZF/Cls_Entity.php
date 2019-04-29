<?php
/**
* Created by PhpStorm.
 * User: jamer
* Date: 2018/10/25
* Time: 14:10
*/

namespace ZF;

/**
 * 新架构下的直接实体类，从这个实体内可以自动加载对应符合要求的模块
 *
 * @package ZF
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2018.10.25
 */
class Entity
{
    /**
     * ALL对应可以自动加载的变量及类名，键是类变量名称，值为完整类名
     * 没有添加在这里的也可以加载，但只能用完整类名
     *
     * @var array
     */
    public $class = array(
        'db' => 'Pdomysql',
        'rl' => 'Redislock',
        'md' => 'Mongodb',
    );

    /**
     * Mysql连接对象
     *
     * @var Pdomysql
     */
    public $db;

    /**
     * Redis连接对象
     *
     * @var Redislock
     */
    public $rl;

    /**
     * MongoDb连接对象
     *
     * @var Mongodb
     */
    public $md;

    /**
     * 自动加载模块函数
     *
     * @param string $class
     * @param array  $params
     *
     * @return bool
     */
    public function loadClass($class, $params = array())
    {
        if (empty($class)) {
            return false;
        }
        $name = $class;
        if (is_string($class)) {
            if (isset($this->class[$class])) {
                $class = $this->class[$class];
            }
            if ($tmp = array_search($class, $this->class)) {
                $name = $tmp;
            }
            if ($class == 'ALL') {
                foreach ($this->class as $k) {
                    if (($k != 'ALL')&&($k != 'Common')) {
                        $this->loadClass($k, $params);
                    }
                }
                return true;
            }
            if ($this->$name && is_object($this->$name)) {
                return $this->$name;
            }
            $uclass = ucfirst($class);
            $file = dirname(__FILE__).DIRECTORY_SEPARATOR.'Cls_'.$uclass.'.php';
            if (!file_exists($file)) {
                return false;
            }
            $c = "\\".__NAMESPACE__."\\{$uclass}";
            $this->$name = $this->newInst($c, $params);
            return $this->$name;
        } elseif (is_array($class)) {
            foreach ($class as $v) {
                $this->loadClass($v, $params);
            }
        }
        return $this->$name;
    }

    /**
     * 动态创建类
     *
     * @return mixed
     * @since  2019.03.23
     */
    private function newInst()
    {
        $arguments = func_get_args();
        $className = array_shift($arguments);
        $newClass = function ($arg) use ($className) {
            return new $className($arg);
        };
        return call_user_func_array($newClass, $arguments);
    }

    /**
     * 魔术方法调用
     *
     * @param string $name
     * @param mixed  $args
     *
     * @return bool
     */
    public function __call($name, $args)
    {
        if ($name) {
            if (count($args) == 1 && isset($args[0])) {
                $args = $args[0];
            }
            if (method_exists($this, $name)) {
                return $this->$name($args);
            } else {
                if (!isset($this->$name) || !$this->$name) {
                    return $this->loadClass($name, $args);
                } else {
                    return $this->$name;
                }
            }
        }

        $msg = "Can't Found Function {$name}";
        trigger_error($msg, E_USER_ERROR);
    }

    /**
     * 魔术方法调用
     *
     * @param string $name
     *
     * @return bool
     */
    public function __get($name)
    {
        if (!is_object($this->$name) || !$this->$name) {
            return $this->loadClass($name);
        }
        return $this->$name;
    }

    /**
     * 取函数的参数名称及默认值，没有的话默认取NULL
     *
     * @param string $class
     * @param string $name
     *
     * @return array
     * @throws \ReflectionException
     */
    public function getFuncParams($class, $name)
    {
        $p = new \ReflectionMethod($class, $name);
        $ret = array();
        foreach ($p->getParameters() as $n) {
            $ret[$n->getName()] = $n->isDefaultValueAvailable() ? $n->getDefaultValue():null;
        }
        return $ret;
    }
}
