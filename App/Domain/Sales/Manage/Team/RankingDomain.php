<?php
/**
 * Created by PhpStorm.
 * User: songwenyao
 * Date: 2018/11/16
 * Time: 上午9:03
 */
namespace App\Domain\Sales\Manage\Team;

use App\Domain\Sales\Setting\TeamStructureDomain;
use App\Model\Market\Source\MoveLogModel;
use Base\BaseDomain;
use Base\Helper\ArrayHelper;
use Base\PayCenter\OrdersApi;

class RankingDomain extends BaseDomain
{
    /**
     * 纯新数据+动作新数据+网资直分数据+网资重分数据
     * @param $business_type
     * @param $start_date
     * @param $end_date
     * @return array
     */
    public function duringOrderRage($business_type, $start_date, $end_date)
    {
        $result = [];
        $TeamDomain = new TeamStructureDomain();
        $data = (new OrdersApi($business_type))->sumOrderCountBySalePersonId($start_date, $end_date);
        if($data){
            /**
             * "salePersonName": "测试号e9",
            "salePersonOpenId": "857788787718094848",
            "num": 1
             */
            $TeamDomain->setDate($end_date);
            $team_cc = [];
            foreach($data as $item){
                $team_cc[] = $item['salePersonName'];
                $my_team = $TeamDomain->getMyTeam($item['salePersonName']);
                //Logger::getInstance()->log($my_team);
                if(!empty($my_team)){
                    $result[] = [
                        'teamName'=>$my_team['group'],
                        'salePersonName'=>$item['salePersonName'],
                        'salePersonUid'=>isset($my_team['cc_uid'])?$my_team['cc_uid']:0,
                        'num'=>$item['num'],
                        'rate'=>0
                    ];
                }
            }

            //查询数据量
            $data_arr = (new MoveLogModel())->getNewDataCount($team_cc, $start_date, $end_date);
            if(!empty($data_arr)){
                $data_arr = ArrayHelper::array_key_value('cc', 'num', $data_arr);
                foreach($result as $k=>$item){
                    $result[$k]['rate'] = isset($data_arr[$item['salePersonName']])&&$item['num']?
                        number_format(($item['num']/$data_arr[$item['salePersonName']])*100, 2):0;
                }
            }
            usort($result, function ($a, $b){
                if($a['rate']<$b['rate']) return 1;
                else return 0;
            });
            //print_r($result);
            //print_r($data_arr);
            return $result;
        }
        return [];
    }

    public function duringAmount($business_type, $start_date, $end_date)
    {
        //$my_team = (new TeamDomain())->getMyTeam('测试号e5');
        $result = [];
        $TeamDomain = new TeamStructureDomain();
        $data = (new OrdersApi($business_type))->sumPaidBySalePersonId($start_date,
            $end_date);
        if($data){
            /**
             * "salePersonName": "测试号e9",
            "salePersonOpenId": "857788787718094848",
            "paid": 10999
             */
            $TeamDomain->setDate($end_date);
            foreach($data as $item){
                $my_team = $TeamDomain->getMyTeam($item['salePersonName']);
                //Logger::getInstance()->log($my_team);
                if(!empty($my_team)){
                    $result[] = [
                        'teamName'=>$my_team['group'],
                        'salePersonName'=>$item['salePersonName'],
                        'salePersonUid'=>isset($my_team['cc_uid'])?$my_team['cc_uid']:0,
                        'paid'=>$item['paid']
                    ];
                }
            }
            return $result;
        }
        return [];
    }
}