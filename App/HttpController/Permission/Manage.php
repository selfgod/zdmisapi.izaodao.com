<?php

namespace App\HttpController\Permission;

use App\Domain\Permission\ManageDomain;
use Base\BaseController;

class Manage extends BaseController
{
    protected function getRules()
    {
        $rules = parent::getRules();
        return array_merge($rules, [
            'groupListAll' => [],
            'groupList' => [
                'id' => ['type' => 'string', 'require' => true, 'default' => '', 'desc' => 'ID']
            ],
            'getManagerList' => [
                'page' => ['type' => 'string', 'default' => 0, 'desc' => ''],
                'limit' => ['type' => 'string', 'default' => 2, 'desc' => ''],
                'user_name' => ['type' => 'string', 'default' => '', 'desc' => '用户名'],
            ],
            'getManagerInfo' => [
                'open_id' => ['type' => 'string', 'require' => true, 'default' => '', 'desc' => 'ID']
            ],
            'getManagerCount' => [
                'user_name' => ['type' => 'string', 'default' => '', 'desc' => '用户名'],
            ],
            'addZdmisMember' => [
                'open_id' => ['type' => 'string', 'require' => true, 'desc' => 'open_id'],
                'email' => ['type' => 'string', 'default' => '', 'desc' => '用户邮箱'],
                'status' => ['type' => 'string', 'default' => '1', 'desc' => '用户状态'],
                'org_sale' => ['type' => 'string', 'default' => '0', 'desc' => '销售树ID'],
                'role_ids' => ['type' => 'array', 'default' => '', 'format' => 'explode', 'desc' => '角色ids'],
                'uid' => ['type' => 'string', 'require' => true, 'desc' => '操作人open_id'],
                'zd_uid' => ['type' => 'int', 'default' => 0, 'desc' => '管理员uid(zdmis中添加使用)'],
            ],
            'updateZdmisMember' => [
                'open_id' => ['type' => 'string', 'require' => true, 'desc' => 'open_id'],
                'user_name' => ['type' => 'string', 'default' => '', 'desc' => '用户名'],
                'real_name' => ['type' => 'string', 'default' => '', 'desc' => '真实姓名'],
                'email' => ['type' => 'string', 'default' => '', 'desc' => '用户邮箱'],
                'status' => ['type' => 'string', 'default' => '1', 'desc' => '用户状态'],
                'mobile' => ['type' => 'string', 'default' => '', 'desc' => '手机号'],
                'org_sale' => ['type' => 'string', 'default' => '', 'desc' => '销售树'],
                'role_ids' => ['type' => 'array', 'default' => '', 'format' => 'explode', 'desc' => '角色ids'],
                'uid' => ['type' => 'string', 'require' => true, 'desc' => '操作人open_id']
            ],
            'delZdmisMember' => [
                'open_id' => ['type' => 'string', 'require' => true, 'desc' => 'open_id'],
                'uid' => ['type' => 'string', 'require' => true, 'desc' => '操作人open_id']
            ],
            'updateOrgSale' => [
                'open_id' => ['type' => 'string', 'require' => true, 'desc' => 'open_id'],
                'org_sale' => ['type' => 'string', 'default' => '', 'desc' => '销售树'],
                'uid' => ['type' => 'string', 'require' => true, 'desc' => '操作人open_id']
            ],
            'updateMemberRole' => [
                'open_id' => ['type' => 'string', 'require' => true, 'desc' => 'open_id'],
                'roleIds' => ['type' => 'array', 'require' => true, 'format' => 'explode', 'desc' => '角色ids']
            ],
        ]);
    }

    //获取角色全部数据
    public function groupListAll()
    {
        $result = (new ManageDomain())->getRoleALL();
        $this->returnJson($result);
    }

    //获取角色ID对应菜单权限数据
    public function groupList()
    {
        $result = (new ManageDomain())->getRole($this->params);
        $this->returnJson($result);
    }

    //所有管理员接口
    public function getManagerList()
    {
        $result = (new ManageDomain())->getManagerList($this->params);
        $this->returnJson($result);
    }

    //根据open_id 获取管理角色等信息
    public function getManagerInfo()
    {
        $result = (new ManageDomain())->getManagerInfo($this->params['open_id']);
        $this->returnJson($result);
    }

    //所有管理员总数
    public function getManagerCount()
    {
        $result = (new ManageDomain())->getManagerCount($this->params);
        $this->returnJson($result);
    }

    /**
     * 管理员修改增加
     */
    public function addZdmisMember()
    {
        $result = (new ManageDomain())->addZdmisMember($this->params['open_id'], $this->params['uid'],
            $this->params['email'], $this->params['status'], $this->params['org_sale'], $this->params['role_ids'], $this->params['zd_uid']);
        if ($result === 0) {
            return $this->returnJson($result, '插入失败', 500);
        } else {
            $this->returnJson($result);
        }
    }

    /**
     * 管理员修改增加
     */
    public function updateZdmisMember()
    {
        $result = (new ManageDomain())->updateZdmisMember($this->params);
        $this->returnJson($result);
    }

    /**
     * 管理员删除
     */
    public function delZdmisMember()
    {
        $result = (new ManageDomain())->delZdmisMember($this->params);
        $this->returnJson($result);
    }

    /**
     * 修改管理员销售树
     * @throws \Base\Exception\BadRequestException
     */
    public function updateOrgSale()
    {
        $result = (new ManageDomain())->updateOrgSale($this->params);
        $this->returnJson($result);
    }

    /**
     * 设置管理员角色
     */
    public function updateMemberRole()
    {
        $ret = (new ManageDomain())->updateMemberRole($this->params['open_id'], $this->params['roleIds']);
        $this->returnJson($ret);
    }

}