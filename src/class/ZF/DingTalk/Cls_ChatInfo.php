<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/5/20
 * Time: 14:09
 * File: Cls_ChatInfo.php
 */

namespace ZF\DingTalk;

/**
 * 群信息结构定义
 *
 * @package ZF\DingTalk
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2019.05.20
 */
class ChatInfo extends \ZF\DingTalk\CustomStructure
{
    /**
     * @var string 聊天ID
     */
    public $chatid = null;
    protected $p_chatid = null;

    /**
     * @var string 群名称
     */
    public $name = '';
    protected $p_name = '';

    /**
     * @var string 群主userid
     */
    public $owner = '';
    protected $p_owner = '';

    /**
     * @var array 成员userid列表
     */
    public $useridlist = [];
    protected $p_useridlist = [];

    /**
     * @var array 外部联系人列表
     */
    public $extidlist = null;
    protected $p_extidlist = null;

    /**
     * @var integer 新成员是否可查看聊天历史消息
     */
    public $showHistoryType = null;
    protected $p_showHistoryType = null;

    /**
     * @var integer 群是否可以被搜索到
     */
    public $searchable = null;
    protected $p_searchable = null;

    /**
     * @var integer 入群是否需要验证
     */
    public $validationType = null;
    protected $p_validationType = null;

    /**
     * @var integer 是否只有群主能够发送给所有人
     */
    public $mentionAllAuthority = null;
    protected $p_mentionAllAuthority = null;

    /**
     * @var integer 是否群禁言
     */
    public $chatBannedType = null;
    protected $p_chatBannedType = null;

    /**
     * @var integer 是否只有群主可以管理群
     */
    public $managementType = null;
    protected $p_managementType = null;

    /**
     * @var array 添加用户ID
     */
    //public $add_useridlist = null;
    protected $p_add_useridlist = null;
    protected $p_add_extidlist = null;

    /**
     * @var array 删除用户ID
     */
    //public $del_useridlist = null;
    protected $p_del_useridlist = null;
    protected $p_del_extidlist = null;

    /**
     * @var string 群头像资源ID
     */
    public $icon = null;
    protected $p_icon = null;

    /**
     * @var integer 群会话类型
     */
    protected $p_conversationTag = null;

    /**
     * @var array 更新数组必须有的字段
     */
    protected $updateNeedField = ['chatid',];

    /**
     * @var array 转字符串需要忽略的字段
     */
    protected $toStringNeedHideField = ['add_useridlist', 'del_useridlist', 'add_extidlist', 'del_extidlist',];

    /**
     * 特殊变量设置方式
     * @param string $name
     * @param        $value
     *
     * @return void
     * @since  2019.05.20
     */
    public function __set(string $name, $value)
    {
        if ($name == 'useridlist' && is_array($value) && $value) {
            $value = array_unique($value);
            if ($this->p_useridlist) {
                foreach ($this->p_useridlist as $v) {
                    if (!in_array($v, $value)) {
                        $this->p_del_useridlist[] = $v;
                    }
                }
                foreach ($value as $v) {
                    if (!in_array($v, $this->p_useridlist)) {
                        $this->p_add_useridlist[] = $v;
                    }
                }
            } else {
                //没有useridlist，全部新增
                $this->p_add_useridlist = $value;
            }
            $this->p_useridlist = $value;
            if ($this->p_add_useridlist) {
                $this->updateField['add_useridlist'] = $this->p_add_useridlist;
            }
            if ($this->p_del_useridlist) {
                $this->updateField['del_useridlist'] = $this->p_del_useridlist;
            }
        } elseif ($name == 'extidlist' && is_array($value) && $value) {
            $value = array_unique($value);
            if ($this->p_extidlist) {
                foreach ($this->p_extidlist as $v) {
                    if (!in_array($v, $value)) {
                        $this->p_del_extidlist[] = $v;
                    }
                }
                foreach ($value as $v) {
                    if (!in_array($v, $this->p_extidlist)) {
                        $this->p_add_extidlist[] = $v;
                    }
                }
            } else {
                //没有extidlist，全部新增
                $this->p_add_extidlist = $value;
            }
            $this->p_extidlist = $value;
            if ($this->p_add_extidlist) {
                $this->updateField['add_extidlist'] = $this->p_add_extidlist;
            }
            if ($this->p_del_useridlist) {
                $this->updateField['del_extidlist'] = $this->p_del_extidlist;
            }
        } else {
            parent::__set($name, $value);
        }
    }

    /**
     * 获取更新字段
     *
     * @return array
     * @since  2019.05.20
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
                if (!in_array($k, ['useridlist', 'extidlist',])) {
                    $this->addField($ret, $k, $v);
                }
            }
        }
        return $ret;
    }
}
