<?php

namespace App\Model\Teach\Teacher;

use Base\BaseModel;
use Base\Db;

class TeacherModel extends BaseModel
{

    /**
     * 获取全部学管师
     * @return mixed
     * @throws \Exception
     */
    public function  querySaTeacherList(){
        return  Db::slave("zd_class")->from('jh_common_zdmis_admincp_member as t1')
            ->leftJoin("jh_common_member t2", "on t1.uid=t2.uid")
            ->where("cpgroupid IN(21,33) and t2.username is not null")
            ->orderByASC(["uid"])->select("t1.*,t2.username")->query();
    }

    /**
     * 获取全部老师
     * @return mixed
     * @throws \Exception
     */
    public function  queryTeacherList($where = '', $bindValues = [], $count = FALSE, $page = 1, $limit = 0){
        if(empty($where)){
            $where = ' uid > :uid ';
            $bindValues = ['uid' => 0];
        }
        if($count === TRUE) {
            return Db::slave("zd_class")->from('zd_teacher_info')
                ->select("count(*)")
                ->where($where)->bindValues($bindValues)->single();
        }
        $query = Db::slave("zd_class")->from('zd_teacher_info')
            ->select("uid, name, work_type, work_type_group")
            ->where($where)->bindValues($bindValues)->orderByASC(["uid"]);
        if($limit > 0 ) $query->setPaging($limit)->page($page);
        $res = $query->query();
        return $res ?: [];
    }
}