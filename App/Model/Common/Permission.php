<?php
namespace App\Model\Common;

use Base\BaseModel;
use Base\Db;

class Permission extends BaseModel
{
    const ROLE_TABLE = 'sys_zdmis_role';
    const ROLE_MENU_TABLE = 'sys_zdmis_role_menu';
    const MEMBER_ROLE = 'sys_zdmis_member_role';
    const MEMBER_TABLE = 'sys_zdmis_member';

    /**
     * 增加角色
     * @param $id
     * @param $uid
     * @param $name
     * @param $remark
     * @return int|mixed
     */
    public function addRole($id, $uid, $name, $remark)
    {
        try {
            Db::master('zd_class')->insert(self::ROLE_TABLE)->cols([
                'id' => $id,
                'name' => $name,
                'remark' => $remark,
                'create_user' => $uid
            ])->query();
        } catch (\Exception $e) {
            return 0;
        }
        return $id;
    }

    /**
     * 更新角色
     * @param $id
     * @param $uid
     * @param $params
     * @return bool
     */
    public function updateRole($id, $uid, $params)
    {
        if (empty($params)) {
            return TRUE;
        }
        $params['modify_time'] = date('Y-m-d H:i:s');
        $params['modify_user'] = $uid;
        $ret = Db::master('zd_class')->update(self::ROLE_TABLE)->cols($params)
            ->where('id = :id and is_del=0')
            ->bindValue('id', $id)
            ->query();
        return $ret > 0;
    }

    /**
     * 删除角色
     * @param $id
     * @param $uid
     * @return bool
     */
    public function delRole($id, $uid)
    {
        $ret = Db::master('zd_class')->update(self::ROLE_TABLE)->cols([
            'is_del' => 1,
            'modify_time' => date('Y-m-d H:i:s'),
            'modify_user' => $uid
        ])
            ->where('id = :id and is_del=0')
            ->bindValue('id', $id)
            ->query();
        return $ret > 0;
    }

    /**
     * 通过角色id获取菜单
     * @param $roleId
     * @return mixed
     */
    public function getRoleMenu($roleId)
    {
        $list = Db::slave('zd_class')->select('id,menu_id')
            ->from(self::ROLE_MENU_TABLE)
            ->where('role_id = :role_id')
            ->bindValue('role_id', $roleId)
            ->query();
        return $list;
    }

    /**
     * 删除角色菜单
     * @param $ids
     * @return bool
     */
    public function delRoleMenu($ids)
    {
        $idStr = implode(',', $ids);
        $num = Db::master('zd_class')->delete(self::ROLE_MENU_TABLE)
            ->where("id in ({$idStr})")
            ->query();
        return $num > 0;
    }

    /**
     * 添加角色菜单
     * @param $roleId
     * @param $menuIds
     * @return mixed|null
     */
    public function addRoleMenu($roleId, $menuIds)
    {
        if (empty($menuIds)) {
            return TRUE;
        }
        $sql = 'INSERT INTO `'. self::ROLE_MENU_TABLE . '` (`role_id`,`menu_id`) VALUES ';
        foreach ($menuIds as $menuId) {
            $sql .= "({$roleId}, {$menuId}),";
        }
        $sql = rtrim($sql, ',');
        $ret = Db::master('zd_class')->query($sql);
        return $ret > 0;
    }

    /**
     * 通过角色id删除用户角色
     * @param $roleId
     * @return bool
     */
    public function delMemberRoleByRole($roleId)
    {
        $ret = Db::master('zd_class')->delete(self::MEMBER_ROLE)
            ->where('role_id = :roleId')
            ->bindValue('roleId', $roleId)
            ->query();
        return $ret > 0;
    }

    /**
     * 角色列表
     * @param $roleId
     * @return bool
     */
    public function getRoleList()
    {
        $ret = Db::slave('zd_class')->from(self::ROLE_TABLE)
            ->select(['id', 'name', 'remark'])
            ->where('is_del = 0')
            ->query();
        if(!empty($ret)){
            foreach ($ret as $k => $v) {
                $ret[$k]['users'] = $this->getRoleUsers($v['id']);
            }
        }
        return $ret;
    }

    /**
     * 角色列表
     * @param $roleId
     * @return array
     */
    public function getRoleUsers($roleId)
    {
        $ret = Db::slave('zd_class')->from(self::MEMBER_ROLE . ' as mr')
            ->leftJoin(self::MEMBER_TABLE . ' AS mt', 'ON mr.uid = mt.uid')
            ->select(['mt.uid','mt.user_name'])
            ->where('mr.role_id = :role_id')
            ->bindValue('role_id', $roleId)
            ->query();
        return $ret;
    }

    /**
     * 角色列表
     * @param $roleId
     * @return array
     */
    public function getRoleInfo($roleId, $returnMenuids = 0)
    {
        $ret = Db::slave('zd_class')->from(self::ROLE_TABLE)
            ->select(['id', 'name', 'remark'])
            ->where('id = :id')
            ->bindValue('id', $roleId)
            ->row();
        if ($ret && $returnMenuids == 1) {
            $ret['menuIds'] = Db::slave('zd_class')
                ->from(self::ROLE_MENU_TABLE)
                ->select('menu_id')->where('role_id = :role_id')
                ->bindValue('role_id', $roleId)
                ->column();
        }
        return $ret;
    }

    /**
     * 为管理员增加角色
     * @param $uid
     * @param $open_id
     * @param $role_id
     * @return bool
     */
    public function addRoleToMember($uid, $open_id, $role_id)
    {
        $ret = Db::slave('zd_class')->from(self::MEMBER_ROLE)
            ->select(['id'])
            ->where('(uid = :uid or open_id = :open_id) and role_id = :role_id')
            ->bindValues(['uid' => $uid, 'open_id' => $open_id, 'role_id' => $role_id])
            ->row();
        if(empty($ret)){
            return Db::master('zd_class')->insert(self::MEMBER_ROLE)
                ->cols([
                    'uid' => $uid,
                    'open_id' => $open_id,
                    'role_id' => $role_id
                ])
                ->query();
        }
        return true;
    }

    /**
     * 为管理员删除角色
     * @param $uid
     * @param $role_id
     * @param $delete_all
     * @return bool
     */
    public function delRoleToMember($uid, $role_id, $delete_all = false)
    {
        if($delete_all === true){
            return Db::master('zd_class')->delete(self::MEMBER_ROLE)
                ->where('uid = :uid')
                ->bindValue('uid', $uid)
                ->query();
        }
        $ret = Db::slave('zd_class')->from(self::MEMBER_ROLE)
            ->select(['id'])
            ->where('uid = :uid')
            ->bindValue('uid', $uid)
            ->row();
        if(!empty($ret)){
            return Db::master('zd_class')->delete(self::MEMBER_ROLE)
                ->where('uid = :uid and role_id = :role_id')
                ->bindValues(['uid' => $uid, 'role_id' => $role_id])
                ->query();
        }
        return true;
    }

    /**
     * 获得管理员角色信息
     * @param $uid
     * @return array
     * @throws \Exception
     */
    function getMemberRole($uid){
        $res = Db::slave('zd_class')
            ->select("mr.uid, mr.open_id, mr.role_id, m.name, m.remark")
            ->from(self::MEMBER_ROLE . ' as mr')
            ->leftJoin(self::ROLE_TABLE . ' as m', 'on m.id = mr.role_id')
            ->where('mr.uid=:uid')->bindValue('uid', $uid)
            ->orderByASC(['mr.role_id'])
            ->query();
        return $res;
    }

    /**
     * 是否是创始人，如是，则拥有所有权限
     * @param $uid
     * @return bool
     * @throws \Exception
     */
    public function isFounder($uid)
    {
        $num = Db::slave('zd_class')->select('count(*)')->from('jh_common_member as jcm')
            ->leftJoin('zd_class.jh_common_zdmis_admincp_member as jczam', 'jcm.uid=jczam.uid')
            ->where('jczam.uid is null and jcm.adminid=1 and jcm.allowadmincp=1 and jcm.uid = :uid')
            ->bindValue('uid', $uid)
            ->single();
        return !empty($num);
    }

    /**
     * 获取功能权限信息
     * @param $key
     * @return array
     */
    public function getFuncPermission($key)
    {
        $permission = Db::slave('zd_class')->select('set_role,set_user')->from('zd_permission_func')
            ->where('set_key = :key')->bindValue('key', $key)->row();
        return $permission;
    }

    /**
     * 用户是否有某功能权限
     * @param $key
     * @param $uid
     * @return bool
     * @throws \Exception
     */
    function hasFuncPermission($key, $uid){
        if($this->isFounder($uid)) return true;
        $fun = $this->getFuncPermission($key);
        if(!empty($fun)){
            if($uid_arr_str = $fun['set_user']){
                $uid_arr = explode(',', $uid_arr_str);
                if(in_array($uid, $uid_arr)) return true;
            }
            $user_group = $this->getMemberRole($uid);
            if(($rid_arr_str = $fun['set_role']) && $user_group){
                $rid_arr = explode(',', $rid_arr_str);
                foreach ($user_group as $n){
                    if(in_array($n['role_id'], $rid_arr)) return true;
                }
            }
        }
        return false;
    }

    /**
     * 递归返回数组中的值，注意返回单值
     * @param $key
     * @param array $arr
     * @return array|mixed
     */
    function array_value_recursive($key, array $arr)
    {
        if (empty($arr)) return array();
        $val = array();
        array_walk_recursive($arr, function ($v, $k) use ($key, &$val) {
            if ($k == $key) array_push($val, $v);
        });
        return $val;
    }

}
