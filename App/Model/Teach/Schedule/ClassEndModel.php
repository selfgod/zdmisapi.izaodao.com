<?php

namespace App\Model\Teach\Schedule;

use App\Model\Common\Category;
use Base\BaseModel;
use Base\Db;

class ClassEndModel extends BaseModel
{
    /**
     * 获取结课未选课列表
     * @param $where
     * @param array $bindValues
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \Exception
     */
    public function getClassEndList($where, $where1, $page = 1, $limit = 0, $order = 't1.end_time desc')
    {
        $sql = 'SELECT * FROM (SELECT sus.uid,max(ss.end_time) as end_time, suli.grade_id, 
                suli.user_identity, suli.sub_identity, suli.learn_status, suli.last_expire as expire
                from sty_user_schedule as sus 
                LEFT JOIN sty_schedule as ss ON ss.id = sus.schedule_id 
                LEFT JOIN sty_user_learn_info as suli ON suli.uid = sus.uid 
                where ss.business_type in (1,3) and sus.is_del = 0 AND 
                suli.learn_status = 1 
                AND sus.uid not in 
                (
                    SELECT uid from sty_company_user WHERE is_del = 0
                )' . $where1 . '
                GROUP BY sus.uid ) as t1 WHERE 1=1 ' . $where . '
                ORDER BY ' . $order . ' LIMIT ' . ((($page-1)?:0) * $limit) . ',' . $limit .' ';
        $res = Db::slave('zd_netschool')->query($sql);
        return $res ?: [];
    }

    /**
     * 获取结课未选课数量
     * @param $where
     * @param array $bindValues
     * @return int
     * @throws \Exception
     */
    public function getClassEndCount($where, $where1)
    {
        $sql = 'SELECT t1.* from (
                    SELECT sus.uid,max(ss.end_time) as end_time
                    from sty_user_schedule as sus 
                    LEFT JOIN sty_schedule as ss ON ss.id = sus.schedule_id 
                    LEFT JOIN sty_user_learn_info as suli ON suli.uid = sus.uid 
                    where ss.business_type in (1,3) and sus.is_del = 0 AND 
                    suli.learn_status = 1 
                    AND sus.uid not in 
                    (
                        SELECT uid from sty_company_user WHERE is_del = 0
                    ) ' . $where1 . '
                    GROUP BY sus.uid ) as t1 where 1=1 ' . $where . ' ';
        $count = Db::slave('zd_netschool')->query($sql);
        return intval(count($count));
    }

    /**
     * 获取身份标识配置
     * @return array
     */
    public function resultConf()
    {
        $model_sys = new Category();
        $conf['identity'] = $model_sys->getConf('svip_key2');
        $userIdentity = $model_sys->getConf('svip_key2');
        $conf['sub_identity_1'] = $model_sys->getConf('identity_ordinary');
        $conf['sub_identity_2'] = $model_sys->getConf('identity_vip');
        $conf['sub_identity_3'] = $model_sys->getConf('identity_svip');
        $conf['sub_identity_4'] = $model_sys->getConf('identity_lifelong');
        $conf['sub_identity_5'] = $model_sys->getConf('identity_talent');
        $conf['sub_identity_6'] = $model_sys->getConf('identity_visitor');

        return ['conf'=>$conf, 'userIdentity'=>$userIdentity];
    }

}