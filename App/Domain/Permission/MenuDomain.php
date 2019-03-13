<?php

namespace App\Domain\Permission;

use App\Model\Common\Menu;
use Base\BaseDomain;
use Base\Exception\BadRequestException;

class MenuDomain extends BaseDomain
{
    /**
     * 获取菜单
     * @return array
     */
    public function getMenuAll()
    {
        $project = (new Menu())->getMenuAll();
        return $project ?: [];
    }

    /**
     * 获取菜单树
     * @return array
     */
    public function getMenuTree()
    {
        $project = (new Menu())->getMenuTree();
        return $project ?: [];
    }

    /**
     * 获取菜单
     * @return array
     */
    public function getMenuList(array $params)
    {
        $project = (new Menu())->getMenuList($params['name'], $params['limit'], $params['page']);
        return $project ?: [];
    }

    /**
     * 获取菜单总数
     * @return array
     */
    public function getMenuCount(array $params)
    {
        $project = (new Menu())->getMenuCount($params['name']);
        return $project ?: [];
    }

    /**
     * 获取菜单总数
     * @return array
     */
    public function getSubMenu(array $params)
    {
        $project = (new Menu())->getSubMenu($params['id']);
        return $project ?: [];
    }

    /**
     * 根据ID获取菜单
     * @return array
     */
    public function getMenuById(array $params)
    {
        $project = (new Menu())->getMenuById($params['id']);
        return $project ?: [];
    }

    /**
     * 根据ID获取菜单
     * @return array
     */
    public function getMenuByParentId(array $params)
    {
        $project = (new Menu())->getMenuByParentId($params['parent_id']);
        return $project ?: [];
    }

    /**
     * 更新菜单
     * @param array $params
     * @return bool
     * @throws BadRequestException
     */
    public function addMenu(array $data)
    {
        $model = new Menu();
        $uid = intval($data['uid']);
        unset($data['uid']);
        if($data['id'] === 'auto' ){
            unset($data['id']);
        }
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['create_user'] = $uid;
        //验证zdmis下router
        if(strpos($data['link_uri'],'zdmis.izaodao.com') !== FALSE) {
            if (!empty($data['router'])) {
                $result = $model->getMenuRouter($data['router']);
                if (!empty($result)) {
                    return 501;
                }
            }
        }
        $res = $model->insertMenu($data);
        return $res;
    }

    /**
     * 更新菜单
     * @param array $params
     * @return bool
     * @throws BadRequestException
     */
    public function updateMenu(array $params)
    {
        $model = new Menu();
        $id = intval($params['id']);
        $uid = intval($params['uid']);
        $row = $model->getMenuById($id);
        if (!empty($row)) {
            if (!empty($params['parent_id'])) {
                $data['parent_id'] = $params['parent_id'];
            }
            if (!empty($params['name'])) {
                $data['name'] = $params['name'];
            }
            if (!empty($params['router'])) {
                $data['router'] = $params['router'];
                //验证zdmis下router
                if (!empty($params['link_uri'])) {
                    if(strpos($params['link_uri'],'zdmis.izaodao.com') !== FALSE) {
                        $result = $model->getMenuRouter($params['router']);
                        if (!empty($result)) {
                            return 501;
                        }
                    }
                }elseif ($row['platform'] == 1) {
                    $result = $model->getMenuRouter($params['router']);
                    if (!empty($result)) {
                        return 501;
                    }
                }
            }
            if (!empty($params['link_uri'])) {
                $data['link_uri'] = $params['link_uri'];
            }
            if (!empty($params['code'])) {
                $data['code'] = $params['code'];
            }
            if (!empty($params['order'])) {
                $data['order'] = $params['order'];
            }
            $data['modify_time'] = date('Y-m-d H:i:s');
            $data['modify_user'] = $uid;
            $res = $model->updateMenuById($id, $data);
        } else {
            return FALSE;
        }
        return $res;
    }

    /**
     * 删除菜单
     * @param array $params
     * @return bool
     * @throws BadRequestException
     */
    public function delMenu(array $params)
    {
        $model = new Menu();
        if ($id = intval($params['id'])) {
            $row = $model->getMenuById($id);
            if (empty($row)) {
                throw new BadRequestException('数据异常');
            }
            return $model->updateMenuById($id, [
                'is_del' => 1,
                'modify_user' => intval($params['uid']),
                'modify_time' => date('Y-m-d H:i:s')
            ]);
        }
        return FALSE;
    }
}