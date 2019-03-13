<?php
/**
 * Created by PhpStorm.
 * User: songwenyao
 * Date: 2018/11/29
 * Time: 11:25 AM
 */
namespace App\Model\Sales\Team;

use Base\BaseModel;
use Base\Db;

class SalesmanModel extends BaseModel
{
    protected $team_table = 'jh_common_setting_aim';

    const TYPE_CC = 'cc';//日语课程顾问
    const TYPE_DECC = 'decc';//德语课程顾问
    const TYPE_KRCC = 'krcc';//韩语课程顾问
    const TYPE_RC = 'rc';//续费顾问
    const TYPE_OSC = 'osc';//留学顾问
    const TYPE_BPCC = 'bpcc';//倍普顾问

    function isResign($salesman)
    {
        $res = Db::slave('zd_class')->select('cclevel')->from($this->team_table)
            ->where('team=:salesman and cat="ccteam_new" and yearmonth=:year_month')
            ->bindValues(['salesman'=>$salesman,'year_month'=>date('Y-m')])
            ->single();
        if($res=='-1' || $res===false){
            return true;
        }
        return false;
    }
}