<?php
/**
 * Created by PhpStorm.
 * User: songwenyao
 * Date: 2019/1/16
 * Time: 2:16 PM
 */
namespace App\Domain\Sales;

use App\Domain\Sales\Setting\TeamStructureDomain;
use App\Model\Sales\Setting\TeamStructureModel;
use Base\BaseDomain;
use Base\Cache\ZdRedis;

class SalesmanDomain extends BaseDomain
{
    function getSalesmanType($uid)
    {
        $redis_key = 'zdmisapi_getSalesmanType'.$uid;
        $cache = ZdRedis::instance(false)->get($redis_key);
        if($cache){
            return unserialize($cache);
        }else{
            $my_team = (new TeamStructureDomain())->getMyTeam($uid);
            $group_dept_type = '';
            if(!empty($my_team)){
                $group_dept = (new TeamStructureModel())->getDeptRow($my_team['group_uid']);
                $group_dept_type = $group_dept['type'];
            }
            ZdRedis::instance(false)->set($redis_key, serialize([$group_dept_type, $my_team]), 3600*2);
            return [$group_dept_type, $my_team];
        }
    }

    private function isResign($salesman)
    {
        $res = Mysql::slave('zd_class')->select('cclevel')->from('jh_common_setting_aim')
            ->where('team=:salesman and cat="ccteam_new" and yearmonth=:year_month')
            ->bindValues(['salesman'=>$salesman,'year_month'=>date('Y-m', $this->end_time)])
            ->single();
        if($res=='-1' || $res===false){
            return true;
        }
        return false;
    }
}