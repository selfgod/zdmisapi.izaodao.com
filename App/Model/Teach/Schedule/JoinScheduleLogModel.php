<?php

namespace App\Model\Teach\Schedule;

use App\Model\Common\Category;
use Base\BaseModel;
use Base\Db;

class JoinScheduleLogModel extends BaseModel
{
    /**
     * 获取结课未选课列表
     * @param $where
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \Exception
     */
    public function getJoinScheduleLogList($where, $page = 1, $limit = 0, $order = ['iol.dateline'])
    {
        $query = Db::slave('zd_class')->from('zd_info_opt_log as iol')
            ->select('iol.uid, iol.opt_uid, iol.type, iol.vip_course_id, iol.content, iol.dateline, 
            iol.flag, suli.grade_id, suli.first_time, suli.last_expire, suli.user_identity, suli.sub_identity')
            ->leftJoin('zd_netschool.sty_user_learn_info as suli', 'iol.uid = suli.uid')
            ->where($where)->orderByDESC($order);
        if ($limit > 0) $query->setPaging($limit)->page($page);
        $res = $query->query();
        return $res ?: [];
    }

    /**
     * 获取结课未选课数量
     * @param $where
     * @return int
     * @throws \Exception
     */
    public function getJoinScheduleLogCount($where)
    {
        $total = Db::slave('zd_class')->from('zd_info_opt_log as iol')
            ->select('count(*) as cnt')
            ->leftJoin('zd_netschool.sty_user_learn_info as suli', 'iol.uid = suli.uid')
            ->where($where)->single();
        return $total ?: 0;
    }

}