<?php
/**
 * Created by PhpStorm.
 * User: songwenyao
 * Date: 2018/11/19
 * Time: 下午4:01
 */
namespace App\Model\Market\Source;

use App\Model\Sales\Customer\CustomerModel;
use Base\BaseModel;
use Base\Db;

class MoveLogModel extends BaseModel
{
    protected $move_log_table = 'zd_source_move_log';
    protected $move_log_os_table = 'zd_oversea_assign'; //留学记录

    const TYPE_NEW_DIRECT = 'new_direct'; //新资源提取
    const TYPE_OC_DIRECT = 'oc_direct'; //oc资源提取
    const TYPE_ACTION_M = 'action_m'; //动作资源提取
    const TYPE_LEYU = 'leyu'; //网资直分
    const TYPE_LEYU_PUSH = 'leyu_push'; // 网资推送
    const TYPE_LEYU_RENEW = 'leyu_renew'; //网资重分
    const TYPE_MANUAL = 'manual'; //手动录入

    static $TYPE_LANG = [
        self::TYPE_NEW_DIRECT=>'新资源直分',
        self::TYPE_OC_DIRECT=>'OC资源直分',
        self::TYPE_LEYU=>'网资直分',
        self::TYPE_LEYU_PUSH=>'网资推送',
        self::TYPE_LEYU_RENEW => '网资重分',
        self::TYPE_MANUAL=>'手动录入',
        self::TYPE_ACTION_M=>'动作资源',
    ];

    function addMoveLogOs($type, $sales_name, $zid, $tag, $admin='系统')
    {
        return $this->insertTable($this->move_log_os_table, [
            'tstype'=>$type,
            'username'=>$sales_name,
            'zid'=>$zid,
            'tag'=>$tag,
            'admin'=>$admin,
            'dateline'=>date('Y-m-d H:i:s')
        ], 'zd_class');
    }

    function addMoveLog($data)
    {
        return $this->insertTable($this->move_log_table, $data, 'zd_class');
    }

    function queryMoveLog($field, $condition)
    {
        $this->sWhereClean();
        $this->setSqlWhereAnd($condition);
        return Db::slave('zd_class')->select($field)
            ->from($this->move_log_table)
            ->where($this->sWhere)
            ->bindValues($this->sBindValues)
            ->orderByDESC(['dateline'])
            ->row();
    }

    function getLatestLog($cid)
    {
        $consult_ids = (new CustomerModel())->getZidForCid($cid);
        if(!empty($consult_ids)){
            return $this->queryMoveLog('*', ['zid'=>['in'=>$consult_ids]]);
        }
        return [];
    }

    function getNewDataCount($cc, $start_date, $end_date){
        $this->sWhere = ' 1=1';
        if(is_array($cc)){
            $cc_str = implode("','", $cc);
            $this->sWhere.=" and cc in('{$cc_str}')";
        }else{
            $this->sWhere.=" and cc='{$cc}'";
        }
        $this->sWhere .= " and dateline>=:start_date and dateline<=:end_date and type in('leyu', 'new_direct', 'action_m', 'leyu_renew')";
        $this->sBindValues = ['start_date'=>$start_date.' 00:00:00', 'end_date'=>$end_date.' 23:59:59'];
        return Db::slave('zd_class')->select('cc, count(id) as num')
            ->from($this->move_log_table)
            ->where($this->sWhere)
            ->bindValues($this->sBindValues)
            ->groupBy(['cc'])
            ->query();
    }
    function getLastSql(){
        return Db::slave('zd_class')->lastSQL();
    }
}