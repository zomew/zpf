<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/3/21
 * Time: 14:42
 * File: Cls___BASE.php
 */

namespace ZF;

/**
 * 动态调用Composer中的实体类底层实现
 *
 * @package ZF
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2019.03.21
 */
class ComposerBase
{
    /**
     * 已配置的根模块数组
     *
     * @var array
     */
    protected $modules = array();

    /**
     * 实际类调用的二级路径：例如在 \Twig\Loader\FilesystemLoader 中为Loader
     *
     * @var string
     */
    private $basename = '';

    /**
     * 实际类调用的一级路径：例如在 \Twig\Loader\FilesystemLoader 中为\Twig
     *
     * @var string
     */
    protected $root = '';

    /**
     * 镜象关系数组
     *
     * @var array
     */
    private $maped = array();

    /**
     * ComposerBase实例化，初始化部分参数
     *
     * @param string $name
     * @param string $root
     * @param string $dir
     */
    public function __construct($name = '', $root = '', $dir = '')
    {
        if ($name) {
            $this->basename = $name;
        }
        if ($root) {
            $this->root = $root;
        }
        if (!$this->maped && $root) {
            $file = self::getMapFile($root);
            if (file_exists($file)) {
                $this->maped = include $file;
            } else {
                $this->maped = self::createMapFiles($dir, $root);
            }
        }
    }

    /**
     * 属性调用魔术方法
     *
     * @param string $name
     *
     * @return \stdClass|ComposerBase
     * @since  2019.03.21
     */
    public function __get($name)
    {
        $ret = new \stdClass();
        $err = true;
        if (in_array($name, $this->modules)) {
            $cls = "{$this->root}\\{$name}";
            try {
                $this->$name = new $cls();
                $ret = $this->$name;
                $err = false;
            } catch (\Exception $e) {
                $name = $cls;
            }
        } elseif (isset($this->maped[$name])) {
            if ($this->basename) {
                $name = $this->basename . "\\{$name}";
            }
            $err = false;
            $ret = new self($name, $this->root);
        }
        if ($err) {
            trigger_error("try to create '{$name}' failed");
        }
        return $ret;
    }

    /**
     * 函数调用魔术方法
     *
     * @param string $name
     * @param mixed  $param
     *
     * @return mixed|\stdClass|ComposerBase
     * @throws \ReflectionException
     * @since  2019.03.21
     */
    public function __call($name, $param)
    {
        if (in_array($name, $this->modules)) {
            $cls = "{$this->root}\\{$name}";
            if (class_exists($cls)) {
                $this->$name = self::newInstance($cls, $param);
                return $this->$name;
            }
        } else {
            if ($this->basename && isset($this->maped[$this->basename])) {
                if (in_array($name, $this->maped[$this->basename])) {
                    $cls = "{$this->root}\\{$this->basename}\\{$name}";
                    $name = $cls;
                    if (class_exists($cls)) {
                        $this->$name = self::newInstance($cls, $param);
                        return $this->$name;
                    }
                }
            } else {
                foreach ($this->modules as $v) {
                    $cls = "{$this->root}\\{$v}";
                    $method = get_class_methods($cls);
                    if (in_array($name, $method)
                        && $this->$v
                        && method_exists($this->$v, $name)
                    ) {
                        return call_user_func_array(array($this->$v, $name), $param);
                    }
                }
            }
        }
        exit("Can't Find function '{$name}',params:". var_export($param, true));
    }

    /**
     * 生成镜象文件名
     *
     * @param string $root
     *
     * @return string
     * @static
     * @since  2019.03.21
     */
    public static function getMapFile($root = '')
    {
        return ZF_ROOT . "Maped_" .
            str_replace('\\', '_', trim($root, '\\')) .
            ".php";
    }

    /**
     * 创建指定目录下的镜象关系数组
     *
     * @param string $dir
     * @param string $root
     *
     * @return array
     * @static
     * @since  2019.03.21
     */
    public static function createMapFiles(
        $dir = ZF_ROOT . "vendor/twig/twig/src/",
        $root = '\\Twig'
    ) {
        $ret = array();
        $dir = rtrim($dir, '/') . '/';
        if ($dir && file_exists($dir)) {
            foreach (glob($dir . '*', GLOB_ONLYDIR) as $v) {
                foreach (self::getFileList($v, '*.php', $dir) as $name) {
                    $tmp = str_replace('\\', '/', str_ireplace('.php', '', $name));
                    $list = explode('/', $tmp);
                    $key = array_shift($list);
                    $value = implode('\\', $list);
                    try {
                        $ref = new \ReflectionClass("{$root}\\{$key}\\{$value}");
                        if ($ref->isInstantiable()) {
                            if (!isset($ret[$key])
                                || !in_array($value, $ret[$key])
                            ) {
                                $ret[$key][] = $value;
                            }
                        }
                    } catch (\Exception $e) {
                    }
                }
            }
        }
        if ($ret) {
            $body = "<?php\nreturn " . var_export($ret, true) . ";\n";
            file_put_contents(self::getMapFile($root), $body);
        }
        return $ret;
    }

    /**
     * 递归获取目录下的指定类型文件
     *
     * @param string $dir
     * @param string $match
     * @param string $basedir
     *
     * @return array
     * @static
     * @since  2019.03.21
     */
    public static function getFileList($dir, $match = '*', $basedir = '')
    {
        $ret = array();
        if ($dir && file_exists($dir)) {
            foreach (glob($dir.'/'.$match) as $v) {
                if (is_file($v)) {
                    if ($basedir == $dir) {
                        $ret[] = str_ireplace($basedir, '', $v);
                    } else {
                        $ret[] = str_ireplace($basedir, '', $v);
                    }
                }
            }
            foreach (glob($dir.'/*', GLOB_ONLYDIR) as $v) {
                $s = self::getFileList($v, $match, $basedir);
                $ret = array_merge($ret, $s);
            }
        }
        return $ret;
    }

    /**
     * 创建新实体
     *
     * @param string $name
     * @param array  $param
     *
     * @return null|object
     * @throws \ReflectionException
     * @static
     * @since  2019.03.21
     */
    public static function newInstance($name = '', $param = array())
    {
        $ret = null;
        if ($name && is_string($name)) {
            $ref = new \ReflectionClass($name);
            if ($ref->isInstantiable()) {
                $ret = $ref->newInstanceArgs($param);
            } else {
                trigger_error("'{$name}' can't instantiable!");
            }
        }
        return $ret;
    }
}
