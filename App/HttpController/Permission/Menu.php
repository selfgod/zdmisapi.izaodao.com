<?php

namespace App\HttpController\Permission;

use App\Domain\Permission\MenuDomain;
use Base\BaseController;

class Menu extends BaseController
{
    protected function getRules()
    {
        $rules = parent::getRules();
        return array_merge($rules, [
            'getMenuAll' => [ ],
            'getMenuTree' => [ ],
            'getMenuById' =>  [
                'id' => ['type' => 'string', 'require' => true, 'desc' => '菜单ID']
            ],
            'getMenuByParentId' =>  [
                'parent_id' => ['type' => 'string', 'require' => true, 'desc' => '菜单ID']
            ],
            'addMenu' => [
                'id' => ['type' => 'string', 'require' => true, 'desc' => '菜单ID'],
                'parent_id' => ['type' => 'string', 'default' => '0', 'desc' => '父菜单ID'],
                'name' => ['type' => 'string', 'require' => true, 'desc' => '名称'],
                'router' => ['type' => 'string', 'default' => '', 'desc' => '路由'],
                'link_uri' => ['type' => 'string', 'default' => '', 'desc' => '地址'],
                'code' => ['type' => 'string', 'default' => '', 'desc' => '权限代码'],
                'order' => ['type' => 'string', 'default' => '', 'desc' => '排序'],
                'uid' => ['type' => 'string', 'require' => true, 'desc' => '操作人'],
            ],
            'updateMenu' => [
                'id' => ['type' => 'string', 'require' => true, 'desc' => '菜单ID'],
                'parent_id' => ['type' => 'string', 'default' => '0', 'desc' => '父菜单ID'],
                'name' => ['type' => 'string', 'default' => '', 'desc' => '名称'],
                'router' => ['type' => 'string', 'default' => '', 'desc' => '路由'],
                'link_uri' => ['type' => 'string', 'default' => '', 'desc' => '地址'],
                'code' => ['type' => 'string', 'default' => '', 'desc' => '权限代码'],
                'order' => ['type' => 'string', 'default' => '', 'desc' => '排序'],
                'uid' => ['type' => 'string', 'require' => true, 'desc' => '操作人'],
            ],
            'delMenu' => [
                'id' => ['type' => 'int', 'require' => true, 'desc' => 'ID'],
                'uid' => ['type' => 'int', 'require' => true, 'desc' => 'user_openid']
            ],
            'getMenuList' => [
                'page' => ['type' => 'string', 'default' => 0, 'desc' => ''],
                'limit' => ['type' => 'string', 'default' => 2, 'desc' => ''],
                'name' => ['type' => 'string', 'default' => '', 'desc' => '菜单名'],
            ],
            'getMenuCount' => [
                'name' => ['type' => 'string', 'default' => '', 'desc' => '菜单名'],
            ],
            'getSubMenu' => [
                'id' => ['type' => 'string', 'default' => '0', 'desc' => 'ID'],
            ]
        ]);
    }

    /**
     * 获取所有菜单
     */
    public function getMenuAll()
    {
        $result = (new MenuDomain())->getMenuAll();
        $this->returnJson($result);
    }

    /**
     * 获取菜单树
     */
    public function getMenuTree()
    {
        $result = (new MenuDomain())->getMenuTree();
        $this->returnJson($result);
    }

    /**
 * 根据菜单ID获取数据
 */
    public function getMenuById()
    {
        $result = (new MenuDomain())->getMenuById($this->params);
        $this->returnJson($result);
    }

    /**
     * 根据菜单ID获取数据
     */
    public function getMenuByParentId()
    {
        $result = (new MenuDomain())->getMenuByParentId($this->params);
        $this->returnJson($result);
    }

    /**
     * 保存菜单
     */
    public function addMenu()
    {
        $result = (new MenuDomain())->addMenu($this->params);
        if ($result === 0) {
            return $this->returnJson($result, '插入失败', 500);
        } elseif($result === 501) {
            $this->returnJson(0, '插入失败，Router路由值已存在', 501);
        }else {
            $this->returnJson($result);
        }
    }

    /**
     * 保存菜单
     */
    public function updateMenu()
    {
        $result = (new MenuDomain())->updateMenu($this->params);
        if ($result === 501) {
            return $this->returnJson(0, '更新失败，Router路由值已存在', 501);
        }
        $this->returnJson($result);
    }

    /**
     * 删除菜单
     */
    public function delMenu()
    {
        $result = (new MenuDomain())->delMenu($this->params);
        $this->returnJson($result);
    }

    /**
     * 获取菜单
     */
    public function getMenuList()
    {
        $result = (new MenuDomain())->getMenuList($this->params);
        $this->returnJson($result);
    }

    /**
     * 获取菜单总数
     */
    public function getMenuCount()
    {
        $result = (new MenuDomain())->getMenuCount($this->params);
        $this->returnJson($result);
    }

    /**
     * 获取菜单总数
     */
    public function getSubMenu()
    {
        $result = (new MenuDomain())->getSubMenu($this->params);
        $this->returnJson($result);
    }
}