<?php

namespace App\Model\Common;

use Base\BaseModel;
use Base\Db;

class Menu extends BaseModel
{
    /**
     * 获取菜单
     * @return array
     */
    public function getMenuAll()
    {
        $project = Db::slave('zd_class')
            ->select('id,name,parent_id,router,link_uri,`order`,platform')
            ->from('sys_zdmis_menu')
            ->where('is_del = 0')
            ->orderByASC(['`order`'])->query();
        return $project ?: [];
    }

    /**
     * 获取菜单
     * @return array
     */
    public function getMenuTree()
    {
        $project = $this->getMenuByParentId(0);
        if($project){
            foreach ($project as $k => $n) {
                $project[$k]['sub_menu'] = $this->getMenuByParentId($n['id']);
                if($project[$k]['sub_menu']){
                    foreach ($project[$k]['sub_menu'] as $k2 => $n2) {
                        $project[$k]['sub_menu'][$k2]['sub_menu'] = $this->getMenuByParentId($n2['id']);
                    }
                }
            }
        }
        return $project ?: [];
    }

    /**
     * 获取菜单数据
     * @return array
     */
    public function getMenuList($name, $limit = 100, $page = 0)
    {
        $offset = $page * $limit;
        $where = 'is_del = 0';
        if(!empty($name)){
            $where .= " AND name like '%".$name."%' ";
        }
        $project = Db::slave('zd_class')
            ->select('id, name, router, link_uri, platform, `order`, parent_id')
            ->from('sys_zdmis_menu')
            ->where($where)
            ->offset($offset)->limit($limit)
            ->orderByASC(['parent_id'])
            ->orderByASC(['`order`'])->query();
        if($project){
            foreach ($project as $k => $n) {
                if($n['parent_id'] != '0'){
                    $project[$k]['parent'] = $this->getParentName($n['parent_id']);
                }else{
                    $project[$k]['parent'] = '第一级菜单';
                }
            }
        }
        return $project ?: [];
    }

    /**
     * 获取父级菜单名称
     * @return array
     */
    public function getParentName($id)
    {
        $ret = '';
        if($id != '0' || $id != ''){
            $parent = Db::slave('zd_class')
                ->select('id, name, parent_id')
                ->from('sys_zdmis_menu')
                ->where('is_del = 0 and id = :id')
                ->bindValue('id', $id)
                ->row();
            if($parent){
                $ret = $parent['name'];
                if($parent['parent_id'] != '0'){
                    $parent2 = Db::slave('zd_class')
                        ->select('name')
                        ->from('sys_zdmis_menu')
                        ->where('is_del = 0 and id = :id')
                        ->bindValue('id', $parent['parent_id'])
                        ->row();
                    $ret = $parent2['name'] . '->' . $ret;
                }
            }
        }
        return $ret;
    }

    /**
     * 获取菜单数据总数
     * @return int
     */
    public function getMenuCount($name)
    {
        $where = 'is_del = 0';
        if(!empty($name)){
            $where .= " AND name like '%".$name."%' ";
        }
        $project = Db::slave('zd_class')
            ->select('count(*)')
            ->from('sys_zdmis_menu')
            ->where($where)->single();
        return $project ?: 0;
    }

    /**
     * 获取菜单数据总数
     * @return array
     */
    public function getSubMenu($id = '0')
    {
        if(empty($id)){
            $id = '0';
        }
        $where = 'is_del = 0 and parent_id = :parent_id';
        $project = Db::slave('zd_class')
            ->select('id, name, parent_id')
            ->from('sys_zdmis_menu')
            ->where($where)
            ->bindValue('parent_id', $id)
            ->orderByASC(['`order`'])
            ->query();
        return $project ?: [];
    }

    /**
     * 通过id获取内容
     * @param $id
     * @return array
     */
    public function getMenuById($id)
    {
        $row = Db::slave('zd_class')
            ->select('id,name,parent_id,router,link_uri,`order`,platform')
            ->from('sys_zdmis_menu')
            ->where('id = :id and is_del = 0')
            ->bindValue('id', $id)
            ->orderByASC(['`order`'])->limit(1)->row();
        return $row ?: [];
    }

    /**
     * 通过id获取内容
     * @param $id
     * @return array
     */
    public function getMenuByParentId($parent_id)
    {
        $row = Db::slave('zd_class')
            ->select('id,name,parent_id,router,link_uri,`order`,platform')
            ->from('sys_zdmis_menu')
            ->where('parent_id = :parent_id and is_del = 0')
            ->bindValue('parent_id', $parent_id)
            ->orderByASC(['`order`'])->query();
        return $row ?: [];
    }

    /**
     * 通过key获取内容
     * @param $keys
     * @return array
     */
    public function getMenuByKey(array $keys)
    {
        $data = Db::slave('zd_netschool')
            ->select('id,name,parent_id,router,link_uri,`order`,platform')
            ->from('sys_zdmis_menu')
            ->where($this->whereIn('`key`', $keys))
            ->where('is_del = 0')
            ->orderByASC(['`order`'])
            ->query();
        return $data ?: [];
    }

    /**
     * 更新菜单
     * @param $id
     * @param $data
     * @return bool
     */
    public function updateMenuById($id, $data)
    {
        if (!empty($data['link_uri'])) {
            if (strpos($data['link_uri'], 'zdmis.izaodao.com') === FALSE) {
                $data['platform'] = 2;
            } else {
                $data['platform'] = 1;
            }
        }
        $ret = Db::master('zd_class')->update('sys_zdmis_menu')
            ->cols($data)->where('id = :id and is_del = 0')
            ->bindValue('id', $id)->query();
        return $ret > 0;
    }

    /**
     * 新增菜单
     * @param $data
     * @return bool
     */
    public function insertMenu($data)
    {
        if(strpos($data['link_uri'],'zdmis.izaodao.com') === FALSE){
            $data['platform'] = 2;
        }else{
            $data['platform'] = 1;
        }
        try {
            Db::master('zd_class')->insert('sys_zdmis_menu')->cols($data)->query();
        } catch (\Exception $e) {
            return 0;
        }
        return $data['id'];
    }

    /**
     * 验证菜单
     * @param $data
     * @return bool
     */
    public function getMenuRouter($router)
    {
        $row = Db::slave('zd_class')
            ->select('id,name,router,is_del')
            ->from('sys_zdmis_menu')
            ->where('router = :router and is_del = 0 and platform = 1')
            ->bindValue('router', $router)
            ->orderByASC(['`order`'])->limit(1)->row();
        return $row ?: [];
    }
}