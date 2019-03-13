<?php
namespace App\Domain\Permission;

use App\Model\Common\Permission;
use Base\BaseDomain;

class PermissionApolloDomain extends BaseDomain
{
    public function __construct()
    {
        $this->baseModel = new Permission();
    }

    /**
     * 增加角色
     * @param $id
     * @param $uid
     * @param $name
     * @param $remark
     * @param $menuIds
     * @return int|mixed
     */
    public function addRole($id, $uid, $name, $remark, $menuIds)
    {
        $roleId = $this->baseModel->addRole($id, $uid, $name, $remark);
        if (!empty($roleId)) {
            $ret = $this->updateRoleMenu($roleId, $menuIds);
            return $ret ? $roleId : 0;
        } else {
            return $roleId;
        }
    }

    /**
     * 更新角色
     * @param $id
     * @param $uid
     * @param $name
     * @param $remark
     * @param $menuIds
     * @return bool
     */
    public function updateRole($id, $uid, $name, $remark, $menuIds)
    {
        $data = [];
        if (!empty($name)) {
            $data['name'] = $name;
        }
        if (!empty($remark)) {
            $data['remark'] = $remark;
        }
        $ret = $this->baseModel->updateRole($id, $uid, $data);
        if ($ret) {
            return $this->updateRoleMenu($id, $menuIds);
        } else {
            return $ret;
        }
    }

    /**
     * 删除角色
     * @param $id
     * @param $uid
     * @return bool
     */
    public function delRole($id, $uid)
    {
        $ret = $this->baseModel->delRole($id, $uid);
        if ($ret) {
            $this->baseModel->delMemberRoleByRole($id);
            return $this->updateRoleMenu($id, []);
        } else {
            return $ret;
        }
    }

    /**
     * 更新角色菜单
     * @param $roleId
     * @param $menuIds
     * @return bool
     */
    public function updateRoleMenu($roleId, $menuIds)
    {
        if (count($menuIds) === 1 && $menuIds[0] === '') {
            $menuIds = [];
        }
        $preList = $this->baseModel->getRoleMenu($roleId);
        if (!empty($preList)) {
            $ids = [];
            foreach ($preList as $pre) {
                $ids[] = $pre['id'];
            }
            $this->baseModel->delRoleMenu($ids);
        }
        return $this->baseModel->addRoleMenu($roleId, $menuIds);
    }

    /**
     * 角色列表
     * @return array
     */
    public function getRoleList()
    {
        $roleList = $this->baseModel->getRoleList();
        return $roleList ? $roleList : [];
    }

    /**
     * 角色列表
     * @return array
     */
    public function getRoleInfo($roleId, $returnMenuIds)
    {
        $roleList = $this->baseModel->getRoleInfo($roleId, $returnMenuIds);
        return $roleList ? $roleList : [];
    }

}
