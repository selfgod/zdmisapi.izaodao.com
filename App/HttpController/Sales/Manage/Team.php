<?php
/**
 * Created by PhpStorm.
 * User: songwenyao
 * Date: 2018/11/16
 * Time: 上午8:41
 */
namespace App\HttpController\Sales\Manage;

use App\Domain\Sales\Manage\Team\RankingDomain;
use App\Domain\Sales\Manage\Team\TeamDomain;
use Base\BaseController;
use Base\PassportApi;
use Base\PayCenter\OrdersApi;
use Base\PayCenterApi;

class Team extends PassportApi
{
    protected function getRules()
    {
        $rules = parent::getRules();
        return array_merge($rules, [
            'queryRankingDay'=>[
                'date' => ['type' => 'string', 'desc' => '查询日期',],
                'business_type'=>['type'=>'string','require'=>true,'desc'=>'业务类型'],
            ],
            'queryRankingDuring'=>[
                'business_type'=>['type'=>'string','require'=>true,'desc'=>'业务类型'],
                'month'=>['type'=>'string','desc'=>'查询月份'],
                'start_date' => ['type' => 'string','desc' => '查询日期',],
                'end_date' => ['type' => 'string','desc' => '查询日期',],
            ],
            'queryRankingRateDuring'=>[
                'business_type'=>['type'=>'string','require'=>true,'desc'=>'业务类型'],
                'month'=>['type'=>'string','desc'=>'查询月份'],
                'start_date' => ['type' => 'string','desc' => '查询日期',],
                'end_date' => ['type' => 'string','desc' => '查询日期',],
            ],
            'getCustomerOrder'=>[
                'business_type'=>['type'=>'string','require'=>true,'desc'=>'业务类型'],
                'start_date' => ['type' => 'string','desc' => '查询日期',],
                'end_date' => ['type' => 'string','desc' => '查询日期',],
                'ignore_auth'=>true
            ]
        ]);
    }

    public function queryRankingDay()
    {
        $date = isset($this->params['date'])?$this->params['date']:date('Y-m-d');
/*if($this->params['business_type']=='kr'){
    $date='2018-11-14';
}elseif($this->params['business_type']=='bp'){
    $date='2018-11-15';
}elseif($this->params['business_type']=='de'){
    $date='2018-11-02';
}*/
        $result = (new RankingDomain())->duringAmount($this->params['business_type'],
            $date, $date);
        $this->returnJson($result);
    }

    public function getCustomerOrder()
    {
        $start_date = isset($this->params['start_date'])?date('Y-m-d',strtotime($this->params['start_date'])):date('Y-m-01');
        $end_date = isset($this->params['end_date'])?date('Y-m-d',strtotime($this->params['end_date'])):date('Y-m-t');
        $Api = new OrdersApi($this->params['business_type']);
        $result = $Api->classifySumPaidBySalePersonId($start_date, $end_date);
        $this->returnJson($result);
    }

    public function queryRankingDuring(){
        if(isset($this->params['month'])){
            $month_time = strtotime($this->params['month']);
            $start_date = date('Y-m-01', $month_time);
            $end_date = date('Y-m-t', $month_time);
        }else{
            $start_date = isset($this->params['start_date'])?$this->params['start_date']:date('Y-m-01');
            $end_date = isset($this->params['end_date'])?$this->params['end_date']:date('Y-m-t');
        }

        $result = (new RankingDomain())->duringAmount($this->params['business_type'],
            $start_date, $end_date);
        $this->returnJson($result);
    }

    public function queryRankingRateDuring(){
        if(isset($this->params['month'])){
            $month_time = strtotime($this->params['month']);
            $start_date = date('Y-m-01', $month_time);
            $end_date = date('Y-m-t', $month_time);
        }else{
            $start_date = isset($this->params['start_date'])?$this->params['start_date']:date('Y-m-01');
            $end_date = isset($this->params['end_date'])?$this->params['end_date']:date('Y-m-t');
        }

        $result = (new RankingDomain())->duringOrderRage($this->params['business_type'],
            $start_date, $end_date);
        $this->returnJson($result);
    }

}