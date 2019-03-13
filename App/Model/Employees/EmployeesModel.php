<?php

namespace App\Model\Employees;

use App\Model\Common\Permission;
use Base\BaseModel;
use Base\Db;
use Base\Thrift;

class EmployeesModel extends BaseModel
{
    /**
     * 获取内部员工列表
     * @param $where
     * @param array $bindValues
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \Exception
     */
    public function getEmployeesList($where, array $bindValues, $page = 1, $limit = 0)
    {
        $query = Db::slave('zd_netschool')->select('*')
            ->from('sty_company_user')
            ->where($where)->bindValues($bindValues)->orderByDESC(['modify_time']);
        if ($limit > 0) $query->setPaging($limit)->page($page);
        $res = $query->query();
        if($res){
            $zdpm = new Permission();
            foreach ($res as $k => $v) {
                $de = json_decode($v['department'],true);
                $res[$k]['department_info'] = '';
                if($de){
                    foreach ($de as $k2 => $n){
                        $res[$k]['department_info'] .= '部门'. ($k2 + 1) . '：' . $this->getDepartmentStr($n) . '<br>';
                    }
                }else{
                    $res[$k]['department_info'] = '无';
                }
                $role_info = $zdpm->getMemberRole($v['uid']);
                $res[$k]['role_name'] = "";
                if($role_info){
                    foreach ($role_info as $k2 => $n){
                        $res[$k]['role_name'] = '角色' . ( $k2 + 1 ) .  '：' . $n['name'] . '；';
                    }
                }
            }
        }
        return $res ?: [];
    }

    /**
     * 获取内部员工数量
     * @param $where
     * @param array $bindValues
     * @return int
     * @throws \Exception
     */
    public function getEmployeesCount($where, array $bindValues)
    {
        $count = Db::slave('zd_netschool')->select('COUNT(*)')
            ->from('sty_company_user')
            ->where($where)->bindValues($bindValues)->single();
        return intval($count);
    }

    /**
     * 获取内部员工信息
     * @param $uid
     * @return array
     * @throws \Exception
     */
    public function getEmployeesInfo($uid)
    {
        $res = Db::slave('zd_netschool')->select('*')
            ->from('sty_company_user')
            ->where('uid = :uid')->bindValues(['uid' => $uid])->row();
        if($res){
            $zdpm = new Permission();
            $de = json_decode($res['department'],true);
            foreach ($de as $k2 => $n){
                $res['department_info'][$n] = $this->getDepartmentStr($n);
            }
        }
        return $res ?: [];
    }

    /**
     * 手机号获取网校信息
     * @param $mobile
     * @return array
     * @throws \Exception
     */
    public function getZdEmployeesInfo($mobile)
    {
        $res = Db::slave('zd_class')->select('jcm.uid, jcm.mobile, jcm.username, jcmp.realname, scu.uid as s_uid')
            ->from('jh_common_member as jcm')
            ->leftJoin('jh_common_member_profile as jcmp', 'jcmp.uid = jcm.uid')
            ->leftJoin('zd_netschool.sty_company_user as scu', 'scu.uid = jcm.uid')
            ->where('jcm.mobile = :mobile')->bindValues(['mobile' => $mobile])->row();
        return $res ?: [];
    }

    /**
     * 组织架构 - 通过部门ID获取部门串信息
     * @param $id
     * @return string
     */
    public function getDepartmentStr($id)
    {
        $res = '';
        $info =  Db::slave('zd_netschool')->select('id, name, parentid')
            ->from('sty_company_department')->where('id = :id')
            ->bindValue('id',$id)
            ->row();
        if($info){
            $res = $info['name'] . $res;
            while($info['parentid'] > 1){
                $info = Db::slave('zd_netschool')->select('id, name, parentid')
                    ->from('sty_company_department')->where('id = :id')
                    ->bindValue('id',$info['parentid'])
                    ->row();
                if($info){
                    $res = $info['name'] . '/' . $res;
                }
            }
        }
        return $res;
    }

    /**
     * 组织架构 - 获取信息
     * @param $data
     * @return array
     */
    public function getDepartmentAll()
    {
        $res =  Db::slave('zd_netschool')->select('`id`, `name`, `parentid`, `order`')
            ->from('sty_company_department')
            ->orderByDESC(['`parentid` ASC','`order`'])
            ->query();
        return $res ?: [];
    }

    /**
     * 组织架构 - 保存信息
     * @param $data
     * @return bool
     */
    public function saveDepartmentInfo($data = array())
    {
        $res = FALSE;
        if($data)
        {
            $info = Db::slave('zd_netschool')->select('id')
                ->from('sty_company_department')->where('id = :id')
                ->bindValue('id',$data['id'])
                ->row();
            if(isset($data['is_del'])){
                $data['is_del'] = 0;
            }
            if($info['id'])
            {
                unset($data['id']);
                $data['modify_time'] = date('Y-m-d H:i:s');
                $res = Db::master('zd_netschool')->update('sty_company_department')
                    ->cols($data)->where('id = :id')
                    ->bindValue('id',$info['id'])->query();
            }
            else
            {
                $data['create_time'] = date('Y-m-d H:i:s');
                $res = Db::master('zd_netschool')->insert('sty_company_department')
                    ->cols($data)->query();
            }
        }
        return $res;
    }

    /**
     * 员工 - 保存信息 一键更新
     * @param $data
     * @return bool
     */
    public function saveCompanyUserInfo($data = array())
    {
        $res = FALSE;
        if($data['mobile'])
        {
            $info = Db::slave('zd_netschool')->select('uid')
                ->from('sty_company_user')->where('qy_mobile = :qy_mobile')
                ->bindValue('qy_mobile',$data['mobile'])
                ->row();
            if(isset($info['uid']))
            {
                $mobile = $data['mobile'];
                unset($data['mobile']);
                $res = Db::master('zd_netschool')->update('sty_company_user')
                    ->cols($data)->where('qy_mobile = :qy_mobile')
                    ->bindValue('qy_mobile',$mobile)
                    ->query();
            } else {
                $info2 = Db::slave('zd_netschool')->select('uid')
                    ->from('sty_company_user')->where('mobile = :mobile')
                    ->bindValue('mobile',$data['mobile'])
                    ->row();
                if($info2['uid'])
                {
                    $data['qy_mobile'] = $data['mobile'];
                    unset($data['mobile']);
                    $res =  Db::master('zd_netschool')->update('sty_company_user')
                        ->cols($data)->where('mobile = :mobile')
                        ->bindValue('mobile', $data['qy_mobile'])
                        ->query();
                }else{
                    $info3 = Db::slave('zd_class')->select('uid, username')
                        ->from('jh_common_member')->where('mobile = :mobile')
                        ->bindValue('mobile',$data['mobile'])
                        ->row();
                    if($info3['uid']){
                        $info4 = Db::slave('zd_netschool')->select('uid, mobile, realname')
                            ->from('sty_company_user')->where('uid = :uid')
                            ->bindValue('uid',$info3['uid'])
                            ->row();
                        if($info4['uid']){
                            $data['username'] = $info3['username'];
                            $data['qy_mobile'] = $data['mobile'];
                            $data['mobile'] = $info4['mobile'] ?: $data['mobile'];
                            $data['realname'] = $info4['realname'] ?: $data['qy_name'];
                            $res =  Db::master('zd_netschool')->update('sty_company_user')
                                ->cols($data)->where('uid = :uid')
                                ->bindValue('uid',$info3['uid'])
                                ->query();
                        }else{
                            $data['uid'] = $info3['uid'];
                            $data['username'] = $info3['username'];
                            $data['qy_mobile'] = $data['mobile'];
                            $data['realname'] = $data['qy_name'];
                            $res =  Db::master('zd_netschool')->insert('sty_company_user')
                                ->cols($data)
                                ->query();
                        }
                    }
                }
            }
        }
        return $res;
    }

    /**
     * 员工 - 保存信息 被动更新
     * @param $data
     * @return bool
     */
    public function saveCompanyUserInfoUserId($data = array())
    {
        $res = FALSE;
        if($data['qy_userid'])
        {
            $info = Db::slave('zd_netschool')->select('uid')
                ->from('sty_company_user')->where('qy_userid = :qy_userid')
                ->bindValue('qy_userid',$data['qy_userid'])
                ->row();
            if(isset($info['uid']))
            {
                $userId = $data['qy_userid'];
                unset($data['qy_userid']);
                $res = Db::master('zd_netschool')->update('sty_company_user')
                    ->cols($data)->where('qy_userid = :qy_userid')
                    ->bindValue('qy_userid',$userId)
                    ->query();
            } else {
                if(isset($data['qy_mobile'])){
                    $info2 = Db::slave('zd_netschool')->select('uid')
                        ->from('sty_company_user')->where('mobile = :mobile')
                        ->bindValue('mobile',$data['qy_mobile'])
                        ->row();
                    if($info2['uid'])
                    {
                        $res =  Db::master('zd_netschool')->update('sty_company_user')
                            ->cols($data)->where('mobile = :mobile')
                            ->bindValue('mobile', $data['qy_mobile'])
                            ->query();
                    }
                }
            }
        }
        return $res;
    }

    /**
     * 员工 - 保存信息 添加修改
     * @param $data
     * @return bool
     */
    public function saveCompanyUserInfoUid($data = array())
    {
        $res = FALSE;
        if($data['e_uid'])
        {
            $info = Db::slave('zd_netschool')->select('uid')
                ->from('sty_company_user')->where('uid = :uid')
                ->bindValue('uid',$data['e_uid'])
                ->row();
            $save_data['uid'] = $data['e_uid'];
            $save_data['username'] = $data['username']?:'';
            $save_data['realname'] = $data['realname']?:'';
            $save_data['mobile'] = $data['mobile'];
            $save_data['qy_mobile'] = $data['wx_mobile']?:'';
            $save_data['qy_userid'] = $data['wx_userid']?:'';
            $save_data['qy_name'] = $data['wx_name']?:'';
            $save_data['department'] = $data['department']?$data['department']:'';
            $save_data['position'] = $data['position']?:'';
            $save_data['gender'] = $data['gender']?:0;
            $save_data['email'] = $data['email']?:'';
            $save_data['enable'] = isset($data['enable'])? $data['enable'] :0;
            if(isset($info['uid']))
            {
                unset($save_data['uid']);
                $save_data['modify_time'] = date('Y-m-d H:i:s');
                $res = Db::master('zd_netschool')->update('sty_company_user')
                    ->cols($save_data)->where('uid = :uid')
                    ->bindValue('uid',$data['e_uid'])
                    ->query();
                if($res === 0){
                    $res = 1;
                }
            } else {
                $save_data['create_time'] = date('Y-m-d H:i:s');
                $save_data['modify_time'] = date('Y-m-d H:i:s');
                $res =  Db::master('zd_netschool')->insert('sty_company_user')
                    ->cols($save_data)
                    ->query();
            }
        }
        return $res;
    }

    /**
     * 员工 - 保存信息 删除
     * @param $data
     * @return bool
     */
    public function delCompanyUserInfo($uid)
    {
        $res = FALSE;
        if($uid) {
            $save_data['is_del'] = 1;
            $save_data['modify_time'] = date('Y-m-d H:i:s');
            $res = Db::master('zd_netschool')->update('sty_company_user')
                ->cols($save_data)->where('uid = :uid')
                ->bindValue('uid', $uid)
                ->query();
        }
        return $res;
    }

    /**
     * 员工 - 保存信息 离职
     * @param $data
     * @return bool
     */
    public function leaveCompanyUserInfo($uid)
    {
        $res = FALSE;
        if($uid) {
            $save_data['enable'] = 2;
            $save_data['modify_time'] = date('Y-m-d H:i:s');
            $save_data['leave_time'] = date('Y-m-d H:i:s');
            $res = Db::master('zd_netschool')->update('sty_company_user')
                ->cols($save_data)->where('uid = :uid')
                ->bindValue('uid', $uid)
                ->query();
        }
        return $res;
    }

    /**
     * 员工 - 离职 清理商品
     * @param $uid
     * @return bool
     */
    public function goodsClear($uid)
    {
        $res = FALSE;
        if($uid) {
            $save_data['is_del'] = 1;
            $res = Db::master('zd_netschool')->update('sty_user_goods')
                ->cols($save_data)->where('uid = :uid')
                ->bindValue('uid', $uid)
                ->query();
            Db::master('zd_netschool')->update('sty_user_goods_info')
                ->cols($save_data)->where('uid = :uid')
                ->bindValue('uid', $uid)
                ->query();

            $save_data['modify_time'] = date('Y-m-d H:i:s');
            Db::master('zd_netschool')->update('sty_user_schedule')
                ->cols($save_data)->where('uid = :uid')
                ->bindValue('uid', $uid)
                ->query();
            $save_data2['is_join'] = 0;
            $save_data2['exit_time'] = date('Y-m-d H:i:s');
            Db::master('zd_jpdata')->update('sas_user_schedule')
                ->cols($save_data2)->where('uid = :uid')
                ->bindValue('uid', $uid)
                ->query();

            $learn_data = [];
            $learn_data['official_class'] = 0;
            $learn_data['learn_status'] = 0;
            $learn_data['user_identity'] = 6;
            $learn_data['sub_identity'] = 1;
            $learn_data['last_expire'] = '';
            Thrift::getInstance()->service('User')->updateUserLearnInfo($uid, $learn_data);
        }
        return $res;
    }

    /**
     * 员工 - 离职 清理早元
     * @param $uid
     * @return bool
     */
    public function zaoyuanClear($uid)
    {
        $res = FALSE;
        if($uid) {
            $save_data['extcredits1'] = 0;
            $save_data['extcredits2'] = 0;
            $save_data['extcredits3'] = 0;
            $save_data['extcredits4'] = 0;
            $save_data['extcredits5'] = 0;
            $save_data['extcredits6'] = 0;
            $save_data['extcredits7'] = 0;
            $save_data['extcredits8'] = 0;
            $res = Db::master('zd_class')->update('jh_common_member_count')
                ->cols($save_data)->where('uid = :uid')
                ->bindValue('uid', $uid)
                ->query();
        }
        return $res;
    }

    /**
     * 员工 - 离职 清理学分
     * @param $uid
     * @return bool
     */
    public function xuefenClear($uid)
    {
        $res = FALSE;
        if($uid) {
            $save_data['studyscore'] = 0;
            $res = Db::master('zd_class')->update('jh_common_member_count')
                ->cols($save_data)->where('uid = :uid')
                ->bindValue('uid', $uid)
                ->query();
        }
        return $res;
    }

    /**
     * 员工 - 更新工作流 企业微信信息
     * @param $uid
     * @return bool
     */
    public function updateZdMemberProfile($uid, $wx_userid, $wx_name, $wx_mobile)
    {
        $res = false;
        $info = Db::master('zd_class')->from('jh_common_member_profile')
            ->select('work_wechat_id')->where('uid = :uid')
            ->bindValue('uid', $uid)->row();
        if(!empty($info) || isset($info['work_wechat_id'])){
            $res = Db::master('zd_class')->update('jh_common_member_profile')
                ->cols([
                    'workmobile' => ($wx_mobile ?: ''),
                    'work_wechat' => ($wx_name ?: ''),
                    'work_wechat_id' => ($wx_userid ?: '')
                ])->where('uid = :uid')
                ->bindValue('uid', $uid)->row();
        }
        return (intval($res) >= 0) ? true : false;
    }
}