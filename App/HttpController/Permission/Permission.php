<?php
namespace App\HttpController\Permission;

use App\Domain\Permission\PermissionApolloDomain;
use Base\BaseController;

/**
 * Apollo 权限操作
 * Class Permission
 * @package App\HttpController\Permission
 */
class Permission extends BaseController
{
    protected function getRules()
    {
        $rules = parent::getRules();
        return array_merge($rules, [
            'addRole' => [
                'id' => ['type' => 'string', 'require' => true, 'desc' => '角色id'],
                'uid' => ['type' => 'string', 'require' => true, 'desc' => 'uid'],
                'name' => ['type' => 'string', 'require' => true, 'desc' => '角色名'],
                'remark' => ['type' => 'string', 'require' => true, 'desc' => '备注'],
                'menuIds' => ['type' => 'array', 'require' => true, 'format' => 'explode', 'desc' => '菜单id']
            ],
            'updateRole' => [
                'id' => ['type' => 'string', 'require' => true, 'desc' => '角色id'],
                'uid' => ['type' => 'string', 'require' => true, 'desc' => 'uid'],
                'name' => ['type' => 'string', 'default' => '', 'desc' => '角色名'],
                'remark' => ['type' => 'string', 'default' => '', 'desc' => '备注'],
                'menuIds' => ['type' => 'array', 'require' => true, 'format' => 'explode', 'desc' => '菜单id']
            ],
            'delRole' => [
                'id' => ['type' => 'string', 'require' => true, 'desc' => '角色id'],
                'uid' => ['type' => 'string', 'require' => true, 'desc' => 'uid'],
            ],
            'updateRoleMenu' => [
                'roleId' => ['type' => 'string', 'require' => true, 'desc' => '角色id'],
                'menuIds' => ['type' => 'array', 'require' => true, 'format' => 'explode', 'desc' => '菜单id']
            ],
            'getRoleList' => [],
            'getRoleInfo' => [
                'roleId' => ['type' => 'string', 'require' => true, 'desc' => '角色id'],
                'returnMenuIds' => ['type' => 'enum', 'range' => [0, 1], 'default' => 0, 'require' => true, 'desc' => '是否返回菜单ID'],
            ]
        ]);
    }

    /**
     * 增加角色
     */
    public function addRole()
    {
        $id = (new PermissionApolloDomain())->addRole($this->params['id'], $this->params['uid'], $this->params['name'],
            $this->params['remark'], $this->params['menuIds']);
        if ($id === 0) {
            return $this->returnJson($id, '插入失败', 500);
        } else {
            return $this->returnJson($id);
        }
    }

    /**
     * 更新角色
     */
    public function updateRole()
    {
        $ret = (new PermissionApolloDomain())->updateRole($this->params['id'], $this->params['uid'], $this->params['name'],
            $this->params['remark'], $this->params['menuIds']);
        return $this->returnJson($ret);
    }

    /**
     * 删除角色
     */
    public function delRole()
    {
        $ret = (new PermissionApolloDomain())->delRole($this->params['id'], $this->params['uid']);
        return $this->returnJson($ret);
    }

    /**
     * 更新角色菜单
     */
    public function updateRoleMenu()
    {
        $ret = (new PermissionApolloDomain())->updateRoleMenu($this->params['roleId'], $this->params['menuIds']);
        return $this->returnJson($ret);
    }

    /**
     * 角色列表
     */
    public function getRoleList()
    {
        $ret = (new PermissionApolloDomain())->getRoleList();
        return $this->returnJson($ret);
    }

    /**
     * 角色信息
     */
    public function getRoleInfo()
    {
        $ret = (new PermissionApolloDomain())->getRoleInfo($this->params['roleId'], $this->params['returnMenuIds']);
        return $this->returnJson($ret);
    }
}
