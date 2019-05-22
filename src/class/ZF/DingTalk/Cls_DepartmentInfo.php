<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/5/17
 * Time: 14:29
 * File: Cls_DepartmentInfo.php
 */

namespace ZF\DingTalk;

/**
 * 阿里钉钉部门信息结构
 *
 * @package ZF\DingTalk
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2019.05.17
 */
class DepartmentInfo extends CustomStructure
{
    /**
     * @var string 父级部门ID
     */
    public $parentid = '';
    protected $p_parentid = '';

    /**
     * @var int 部门ID
     */
    public $id = null;
    protected $p_id = null;

    /**
     * @var string 部门名称
     */
    public $name = '';
    protected $p_name = '';

    /**
     * @var string 父部门中的排序值，小在前
     */
    public $order = '';
    protected $p_order = '';

    /**
     * @var bool 是否创建一个关联此部门的企业群
     */
    public $createDeptGroup = false;
    protected $p_createDeptGroup = false;

    /**
     * @var bool 是否隐藏部门
     */
    public $deptHiding = false;
    protected $p_deptHiding = false;

    /**
     * @var string 可以查看指定隐藏部门的其他部门列表
     */
    public $deptPermits = [];
    protected $p_deptPermits = [];

    /**
     * @var string 可以查看指定隐藏部门的其他人员列表
     */
    public $userPermits = [];
    protected $p_userPermits = [];

    /**
     * @var bool 限制本部门成员查看通讯录
     */
    public $outerDept = false;
    protected $p_outerDept = false;

    /**
     * @var string outerDept为true时，可以配置额外可见部门
     */
    public $outerPermitDepts = [];
    protected $p_outerPermitDepts = [];

    /**
     * @var string outerDept为true时，可以配置额外可见人员
     */
    public $outerPermitUsers = [];
    protected $p_outerPermitUsers = [];

    /**
     * @var bool 只能看到所在部门及下级部门通讯录
     */
    public $outerDeptOnlySelf = false;
    protected $p_outerDeptOnlySelf = false;

    /**
     * @var string 部门标识字段，开发者可用该字段来唯一标识一个部门
     */
    public $sourceIdentifier = '';
    protected $p_sourceIdentifier = '';

    /**
     * @var array 需要将数组转换成JSON串的字段
     */
    protected $splitStrField = ['deptPermits', 'userPermits', 'outerPermitDepts', 'outerPermitUsers',];

    /**
     * 魔术设置变量方法
     * @param string $name
     * @param        $value
     *
     * @return void
     * @since  2019.05.17
     */
    public function __set(string $name, $value)
    {
        $real = 'p_' . $name;
        if ($name == 'id' && $this->$real) {
            //已经有id就不允许再修改了
            return;
        }
        parent::__set($name, $value);
    }
}
