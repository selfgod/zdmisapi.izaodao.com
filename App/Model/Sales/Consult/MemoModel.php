<?php

namespace App\Model\Sales\Consult;

use Base\BaseModel;
use Base\Db;
use EasySwoole\Core\Component\Logger;

class MemoModel extends BaseModel
{
    protected $memo_table = 'zd_zixun_memo';
    /**
     * 销售最后一次回访详情
     * @param $uid
     * @param $cc_username
     * @return array
     * @throws \Exception
     */
    public function getLastMemo($uid, $cc_username){
        $where = "t2.uid = '{$uid}'";
        if ($cc_username)
        {
            $where .= " and t1.stuff = '{$cc_username}'";
        }
        return Db::slave('zd_class')->from('zd_zixun_memo as t1')
            ->select('t1.stuff, t1.memo, t1.adddate')
            ->leftJoin('zd_zixun_uid as t2', 'on t1.fid = t2.zid')
            ->where($where)->orderByDESC(['t1.adddate'])->row();
    }

    function isMemo($condition)
    {
        $this->sWhereClean();
        $this->setSqlWhereAnd($condition);
        $res = Db::slave('zd_class')->select('count(id)')
            ->from($this->memo_table)
            ->where($this->sWhere)
            ->bindValues($this->sBindValues)
            ->single();
        $sql = Db::slave('zd_class')->lastSQL();
        //Logger::getInstance()->log('memo sql:'.$sql);
        //Logger::getInstance()->log(print_r($this->sBindValues, true));
        return $res?true:false;
    }

    /**
     * 顾问是否有备注
     * @param $zid
     * @param $salesman
     * @return boolean
     */
    function isMemoStuff($zid, $salesman)
    {
        return $this->isMemo([
            'fid'=>$zid,
            'stuff'=>$salesman
        ]);
    }

    /**
     * 同业务顾问是否有备注
     * @param $zid
     * @param $dept_type
     * @return boolean
     */
    function isMemoDeptType($zid, $dept_type)
    {
        return $this->isMemo([
            'fid'=>$zid,
            'dept_type'=>$dept_type
        ]);
    }
}