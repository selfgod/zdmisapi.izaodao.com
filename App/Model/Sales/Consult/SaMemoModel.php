<?php

namespace App\Model\Sales\Consult;

use Base\BaseModel;
use Base\Db;

class SaMemoModel extends BaseModel
{
    /**
     * 班主任最后一次回访详情
     * @param $uid
     * @return array
     * @throws \Exception
     */
    public function getSaLastMemo($uid, $sa_username = ''){
        $where = "t2.uid = '{$uid}'";
        if ($sa_username)
        {
            $where .= " and t1.stuff = '{$sa_username}'";
        }
        return Db::slave('zd_class')->from('zd_zixun_memo_sa as t1')
            ->select('t1.stuff as stuff_sa, t1.memo as memo_sa, t1.adddate as adddate_sa')
            ->leftJoin('zd_zixun_uid as t2', 'on t1.fid = t2.zid')
            ->where($where)->orderByDESC(['t1.adddate'])->row();
    }
}