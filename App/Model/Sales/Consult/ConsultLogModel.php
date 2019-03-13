<?php
/**
 * 咨询其他操作动作记录
 * Created by PhpStorm.
 * User: songwenyao
 * Date: 2018/11/20
 * Time: 下午2:43
 */

namespace App\Model\Sales\Consult;

use Base\BaseModel;
use Base\Db;

class ConsultLogModel extends BaseModel
{
    protected $consult_log_table = 'zd_consult_log';

    function insertLog($act, $content, $cid, $operator, $alert_time=0){
        return Db::master('zd_class')
            ->insert($this->consult_log_table)
            ->cols([
                'act'=>$act,
                'operate_content'=>$content,
                'dateline'=>date('Y-m-d H:i:s'),
                'cid'=>$cid,
                'alert_time'=>$alert_time,
                'zixun_id'=>0,
                'operator'=>$operator
            ])
            ->query();
    }

    function clearAlertTime($cid, $act){
        return Db::master('zd_class')->update($this->consult_log_table)
            ->where("act='{$act}' and cid='{$cid}'")->cols(['alert_time'=>0])->query();
    }
}