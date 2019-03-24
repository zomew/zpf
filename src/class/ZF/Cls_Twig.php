<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/3/21
 * Time: 9:26
 * File: Cls_Twig.php
 */

namespace ZF;

/**
 * Twig简易封装实体类，可直接用子属性调用对应的实体类
 *
 * @package ZF
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2019.03.21
 */
class Twig extends ComposerBase
{
    /**
     * \Twig\Environment实例化的默认参数
     *
     * @var array
     */
    private $_options = array(
        'templates_dir' => 'views',
        'cache' => 'cache',
        'auto_reload' => true,
    );

    /**
     * Twig的一级调用路径
     *
     * @var string
     */
    protected $root = '\\Twig';

    /**
     * 镜象文件搜索路径 如果Twig没有结构上的大变化，一般不需要修改
     *
     * @var string
     */
    protected $dir = ZF_ROOT . "vendor/twig/twig/src/";

    /**
     * 是否已经实例化标志，用于判断全局变量清除依据
     *
     * @var bool
     */
    private $_isConstract = false;


    /**
     * 以下是一些便于开发过程中用于IDE中代码自动提示而定义的变量
     */
    /**
     * @var \Twig\Loader\FilesystemLoader
     */
    protected $loader;

    /**
     * @var \Twig\Environment
     */
    public $Environment;

    /**
     * @var \Twig\Compiler
     */
    public $Compiler;

    /**
     * @var \Twig\ExpressionParser
     */
    public $ExpressionParser;

    /**
     * @var \Twig\ExtensionSet
     */
    public $ExtensionSet;

    /**
     * @var \Twig\FileExtensionEscapingStrategy
     */
    public $FileExtensionEscapingStrategy;

    /**
     * @var \Twig\Lexer
     */
    public $Lexer;

    /**
     * @var \Twig\Markup
     */
    public $Markup;

    /**
     * @var \Twig\NodeTraverser
     */
    public $NodeTraverser;

    /**
     * @var \Twig\Parser
     */
    public $Parser;

    /**
     * @var \Twig\Source
     */
    public $Source;

    /**
     * @var \Twig\TemplateWrapper
     */
    public $TemplateWrapper;

    /**
     * @var \Twig\Token
     */
    public $Token;

    /**
     * @var \Twig\TokenStream
     */
    public $TokenStream;

    /**
     * @var \Twig\TwigFilter
     */
    public $TwigFilter;

    /**
     * @var \Twig\TwigFunction
     */
    public $TwigFunction;

    /**
     * @var \Twig\TwigTest
     */
    public $TwigTest;

    /**
     * Twig constructor.
     *
     * @param array $options
     */
    public function __construct($options = array())
    {
        parent::__construct('', $this->root, $this->dir);
        $this->_cleanPublicProperty();
        if ($options && is_array($options)) {
            $options = array_merge($this->_options, $options);
        } else {
            $options = $this->_options;
        }
        $this->loader = new \Twig\Loader\FilesystemLoader($options['templates_dir']);
        unset($options['templates_dir']);
        $this->Environment = new \Twig\Environment($this->loader, $options);
    }

    /**
     * 为了使魔术方法接管，清除公用属性
     *
     * @return void
     * @since  2019.03.21
     */
    private function _cleanPublicProperty()
    {
        if (!$this->_isConstract) {
            try {
                $ref = new \ReflectionClass($this);
                foreach ($ref->getProperties(\ReflectionProperty::IS_PUBLIC) as $v) {
                    $name = $v->getName();
                    $this->modules[] = $name;
                    unset($this->$name);
                }
            }catch(\ReflectionException $e) {
            }
            $this->_isConstract = true;
        }
    }
}
