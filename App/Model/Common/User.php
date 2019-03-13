<?php

namespace App\Model\Common;

use Base\BaseModel;
use Base\Db;
use Base\Thrift;

class User extends BaseModel
{
    /**
     * 获取教师信息
     * @param $teacherIds
     * @return mixed
     */
    public function getTeacherByIds($teacherIds)
    {
        return Thrift::getInstance()->service('User')->getTeacherByIds($teacherIds);
    }

    /**
     * 获取用户信息
     * @param $uids
     * @return mixed
     */
    public function getUsersByUids($uids)
    {
        return Thrift::getInstance()->service('User')->getUsersByUids($uids);
    }

    /**
     * 获取阶段课程老师信息
     * @param $scheduleId
     * @param int $category 1 主讲 2助教 3学管师
     * @return array
     */
    public function getScheduleTeacher($scheduleId, $category = 1)
    {
        $scheduleTeachers = Db::slave('zd_netschool')
            ->select('teacher_id,audition_link')
            ->from('sty_schedule_teacher')
            ->where('schedule_id = :schedule_id and category = :category and is_del = 0')
            ->bindValues([
                'schedule_id' => $scheduleId,
                'category' => $category
            ])->orderByASC(['id'])->query();
        return $scheduleTeachers ?: [];
    }

    /**
     * 免密注册
     * @param $mobile
     * @return array
     */
    public function regUser($mobile)
    {
        $data = [];
        $data['ip'] = '';
        $data['cat'] = 'PC页面-ZDMIS';
        $data['source'] = '';
        $res = Thrift::getInstance()->service('User')->userRegisterByMobile($mobile, $data);
        if($res->code == 200){
            return ['reg'=> true, 'data' => $res->data];
        }else{
            return ['reg'=> false, 'data' => $res->msg];
        }
    }

    /**
     * 由用户名/真名，或qq号获取uid
     * @param $search_data
     * @param bool $like
     * @return array
     * @throws \Exception
     */
    public function getUserUidFromUsername($search_data, $like = FALSE)
    {
        $uid_data = array();
        $where = ' 1=1 ';
        if(!isset($search_data['username']) && !isset($search_data['qq_number']) ){
            return [];
        }
        if(!empty($search_data['username'])){
            if($like === TRUE){
                $where .= " AND a.username like '%".$search_data['username']."%' OR b.realname like '%".$search_data['username']."%' ";
            }else{
                $where .= " AND a.username = '".$search_data['username']."' OR b.realname = '".$search_data['username']."' ";
            }
        }
        if(!empty($search_data['qq_number'])){
            $where .= ' AND b.qq =' . $search_data['qq_number'];
        }
        $query = Db::slave('zd_class')->from('jh_common_member as a')->select('a.uid')
            ->leftJoin('jh_common_member_profile as b', 'on a.uid = b.uid')
            ->where($where)
            ->query();
        if (!empty($query)) {
            foreach ($query as $item) {
                $uid_data[] = $item['uid'];
            }
        }
        return $uid_data;
    }

    /**
     * 获取 用户信息
     * @param $uid
     * @return mixed
     */
    function getUserInfo($uid){
        return Thrift::getInstance()->service('User')->getUserByUid($uid);
    }

    /**
     * 获取 用户名/真名
     * @param $uid
     * @return array
     */
    public function getUserNameInfo($uid)
    {
        $data = array();
        $user_info = $this->getUserInfo($uid);
        if (!empty($user_info)) {
            $user_name = $user_info['username'];
            $realname = $user_info['real_name'];
            $user_real_name = $user_name . '/' . $realname;
            $data['username'] = $user_info['username'];
            $data['user_real_name_text'] = $user_real_name;
            $data['user_real_name'] = '<a target="_blank" href="./../StudentManage/detail.html?uid='.$uid.'">'.$user_real_name.'</a>';
            $data['teacher_user_real_name'] = '<a target="_blank" href="/teacher/schedule?method=student_info&uid='.$uid.'">'.$user_real_name.'</a>';
        }
        return $data;
    }

    /**
     * 获取 用户学管师
     * @param $uid
     * @return array
     */
    public function getMyTeacher($uid){
        return Db::slave('zd_class')->from('jh_common_member_profile_stuff')
            ->where("uid='{$uid}' and type ='sa'")->select("stuffname")->row();
    }

    /**
     * 获取 用户续费顾问
     * @param $uid
     * @return array
     */
    public function getMyXzCc($uid){
        return  Db::slave('zd_class')->from('jh_common_member_profile_stuff')
            ->where("uid='{$uid}' and type ='xz_cc'")->select("stuffname")->row();
    }

    /**
     * 获取 用户最后激活时间
     * @param $uid
     * @return array
     */
    public function getUserLastActiveTime($uid){
        return  Db::slave('zd_netschool')->from('sty_user_goods as sug')
            ->select('sug.activate_time')
            ->leftJoin('sty_goods as sg', 'on sug.goods_id = sg.id')
            ->where("sug.uid='{$uid}' and sug.is_activate = 1 and sug.is_del = 0 and sg.is_active = 1")
            ->orderByDESC(['sug.activate_time'])
            ->row();
    }

    /**
     * 用户是否存在未分配学管师任务
     * @param $uid
     * @return bool
     */
    public function userIsExistUnAssignTeachTask($uid): bool
    {
        $exist = Db::slave('zd_netschool')->select('COUNT(*)')->from('sty_teach_task_class')
            ->where('uid = :uid AND is_assign = 0')->bindValue('uid', $uid)->single();
        return $exist > 0;
    }

    /**
     * 根据openId获得用户信息
     * @param $open_id
     * @return mixed
     */
    function getUserForOpenId($open_id){
        $userUid = $this->getUidByOpenId($open_id);
        if (empty($userUid)) {
            return FALSE;
        }
        return Thrift::getInstance()->service('User')->getUserByUid($userUid);
    }

    /**
     * 通过openId获取uid
     * @param $openId
     * @return mixed
     */
    public function getUidByOpenId($openId)
    {
        return Thrift::getInstance()->service('User')->getUidByOpenId($openId);
    }

    /**
     * 通过uid获取openId
     * @param $uid
     * @return mixed
     */
    public function getOpenIdByUid($uid)
    {
        return Thrift::getInstance()->service('User')->getOpenIdByUid($uid);
    }

    /**
     * 判断内部员工
     * @param $mobile
     * @return bool
     * @throws \Exception
     */
    function isEmployeeNew($mobile){
        if(!empty($mobile)){
            $where = "mobile = '{$mobile}' AND is_del = 0";
        }else{
            $where = 'is_del = 0';
        }
        $result = Db::slave('zd_netschool')->select('uid')->from('sty_company_user')->where($where)->query();
        if(!empty($result)){
            return true;
        }else{
            if(!empty($mobile)){
                $where = "jcm.mobile = '{$mobile}' AND scu.is_del = 0";
            }else{
                $where = 'scu.is_del = 0';
            }
            $res = Db::slave('zd_netschool')->select('jcm.uid')->from('sty_company_user as scu')
                ->leftJoin('zd_class.jh_common_member as jcm', 'scu.uid = jcm.uid')
                ->where($where)
                ->query();
            if(!empty($res)){
                return true;
            }
        }
        return false;
    }

    /**
     * 获取用户名
     * @param $uid
     * @return array|string
     */
    function getUserName($uid){
        if($uid){
            $res = Db::slave('zd_class')->select('username')->from('jh_common_member')
                ->where('uid = :uid')->bindValue('uid', $uid)->column();
            return $res ? $res[0] : '';
        }
        return '';
    }

    /**
     * 获取用户早元信息
     * @param $po_uid
     * @return array|string
     */
    public function getUseZyTotal($po_uid)
    {
        $data = Db::slave('zd_class')->select('extcredits8')
            ->from('jh_common_member_count')
            ->where('uid = :po_uid')
            ->bindValue('po_uid', $po_uid)
            ->row();
        return $data;
    }
}