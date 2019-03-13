<?php

namespace App\Model\Common;

use Base\BaseModel;
use Base\Db;
use Base\Request\Formatter\IntFormatter;

class Manage extends BaseModel
{
    /**
     * 获取数据
     * @return array
     */
    public function getRoleAll()
    {
        $project = Db::slave('zd_class')
            ->select('concat(zr.id, "") as id,zr.name,zr.remark,mu.name,mu.router,mu.link_uri')
            ->from('sys_zdmis_role AS zr')
            ->leftJoin('zd_class.sys_zdmis_role_menu AS zrm', 'ON zr.id=zrm.role_id')
            ->leftJoin('zd_class.sys_zdmis_menu AS mu', 'ON zrm.menu_id=mu.id')
            ->where('zr.is_del = 0')
            ->orderByASC(['zr.id'])->query();
        return $project ?: [];
    }

    /**
     * 根据条件获取数据
     * @return array
     */
    public function getRole($id)
    {
        $project = Db::slave('zd_class')
            ->select('concat(zr.id, "") as id,zr.name,zr.remark,mu.name,mu.router,mu.link_uri')
            ->from('sys_zdmis_role AS zr')
            ->leftJoin('zd_class.sys_zdmis_role_menu AS zrm', 'ON zr.id=zrm.role_id')
            ->leftJoin('zd_class.sys_zdmis_menu AS mu', 'ON zrm.menu_id=mu.id')
            ->where('zr.is_del = 0 AND zr.id= :id')
            ->bindValue('id', $id)
            ->orderByASC(['zr.id'])->query();
        return $project ?: [];
    }

    /**
     * 获取数据
     * @return array
     */
    public function getManagerCount($user_name)
    {
        $where = ' 1=1 AND is_del = 0';
        if(!empty($user_name)){
            $where .= " AND user_name like '%".$user_name."%' ";
        }
        $project = Db::slave('zd_class')
            ->select('COUNT(*)')
            ->from('sys_zdmis_member')
            ->where($where)
            ->orderByDESC(['create_time'])->single();
        return $project;
    }

    /**
     * 获取数据
     * @return array
     */
    public function getManagerList($user_name, $limit = 100, $page = 0)
    {
        $offset = $page * $limit;
        $where = ' 1=1 AND is_del = 0';
        if(!empty($user_name)){
            $where .= " AND user_name like '%".$user_name."%' ";
        }
        $project = Db::slave('zd_class')
            ->select('concat(uid, "") as uid,concat(open_id, "") as open_id,user_name,real_name,email,org_sale,status')
            ->from('sys_zdmis_member')
            ->where($where)
            ->offset($offset)->limit($limit)
            ->orderByDESC(['create_time'])->query();
        return $project ?: [];
    }

    /**
     * 根据条件获取数据
     * @return array
     */
    public function getManagerInfo($open_id)
    {
        $project = Db::slave('zd_class')
            ->select('szm.uid,concat(szm.open_id, "") as open_id,szm.user_name,szm.real_name,szm.email,szm.org_sale,concat(szmr.role_id, "") as role_id,szr.name AS role_name,szr.remark')
            ->from('sys_zdmis_member AS szm')
            ->leftJoin('zd_class.sys_zdmis_member_role AS szmr', 'ON szmr.open_id=szm.open_id')
            ->leftJoin('zd_class.sys_zdmis_role AS szr', 'ON szr.id=szmr.role_id AND szr.is_del = 0')
            ->where('szm.is_del = 0 AND szr.is_del = 0 AND szm.open_id= :open_id')
            ->bindValue('open_id', $open_id)
            ->orderByDESC(['szm.create_time'])->query();
        return $project ?: [];
    }

    /**
     * 通过id获取内容
     * @param $id
     * @return array
     */
    public function getSaleDepartmentById($id)
    {
        $row = Db::slave('zd_class')
            ->select('id,code,parent_id,parent_code,name,region,leaf')
            ->from('sys_zdmis_sale_department')
            ->where('id = :id and is_del = 0')
            ->bindValue('id', $id)
            ->orderByASC(['id'])->limit(1)->row();
        return $row ?: [];
    }

    /**
     * 通过id获取管理员信息
     * @param $id
     * @return array
     */
    public function getZdmisMemberId($open_id, $flag = TRUE)
    {
        $row = Db::slave('zd_class')
            ->select('concat(uid, "") as uid,concat(open_id, "") as open_id,user_name,real_name,email,mobile,org_sale')
            ->from('sys_zdmis_member');
        if ($flag === TRUE) {
            $row = $row->where('open_id = :open_id and is_del = 0');
        }else {
            $row = $row->where('open_id = :open_id');
        }
        $row = $row->bindValue('open_id', $open_id)
            ->orderByASC(['uid'])->limit(1)->row();
        return $row ?: [];
    }

    /**
     * 更新管理员
     * @param $id
     * @param $data
     * @return bool
     */
    public function updateZdmisMember($open_id, $data)
    {
        $ret = Db::master('zd_class')->update('sys_zdmis_member')
            ->cols($data)->where('open_id = :open_id')
            ->bindValue('open_id', $open_id)->query();
        return $ret > 0;
    }

    /**
     * 删除管理员
     * @param $id
     * @param $data
     * @return bool
     */
    public function delZdmisMember($open_id, $data)
    {
        $ret = Db::master('zd_class')->update('sys_zdmis_member')
            ->cols($data)->where('open_id = :open_id')
            ->bindValue('open_id', $open_id)->query();
        if ($ret) {
            if ($this->getMemberRole($open_id)) {
                $this->delMemberRole($open_id);
            }
        }
        return $ret > 0;
    }

    /**
     * 更新管理员销售树
     * @param $id
     * @param $data
     * @return bool
     */
    public function updateOrgSale($open_id, $data)
    {
        $ret = Db::master('zd_class')->update('sys_zdmis_member')
            ->cols($data)->where('open_id = :open_id')
            ->bindValue('open_id', $open_id)->query();
        return $ret > 0;
    }

    /**
     * 通过id获取用户角色对应表
     * @param $id
     * @return array
     */
    public function getMemberRole($open_id)
    {
        $row = Db::slave('zd_class')
            ->select('id,open_id,uid,role_id')
            ->from('sys_zdmis_member_role')
            ->where('open_id = :open_id')
            ->bindValue('open_id', $open_id)
            ->orderByASC(['open_id'])->limit(1)->row();
        return $row ?: FALSE;
    }

    /**
     * 通过id获取用户角色对应表
     * @param $id
     * @return array
     */
    public function delMemberRole($open_id)
    {
        $row = Db::slave('zd_class')
            ->delete('sys_zdmis_member_role')
            ->where('open_id = :open_id')
            ->bindValue('open_id', $open_id)
            ->query();
        return $row ?: FALSE;
    }

    /**
     * 新增管理员
     * @param $data
     * @return bool
     */
    public function insertZdmisMember($data)
    {
        try {
            Db::master('zd_class')->insert('sys_zdmis_member')->cols($data)->query();
        } catch (\Exception $e) {
            return 0;
        }
        return (string)$data['open_id'];
    }

}