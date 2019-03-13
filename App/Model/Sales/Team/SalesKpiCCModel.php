<?php
/**
 * Created by PhpStorm.
 * User: songwenyao
 * Date: 2018/11/29
 * Time: 2:09 PM
 */
namespace App\Model\Sales\Team;

use Base\Db;

class SalesKpiCCModel extends SalesKpiModel
{
    protected $key_map = [
        'data_date'=>'日期',
        'dept'=>'部门',
        'team'=>'团队',
        'salesman'=>'姓名',
        'level'=>'职级',
        'online_time'=>'上线日期',
        'resign_time'=>'离职日期',
        'is_share'=>'是否分标',
        'share_day'=>'分标天数',
        'salary_base'=>'基本工资',
        'salary_kpi_base'=>'基本绩效工资',
        'perform_target'=>'业绩指标',
        'perform_weight'=>'业绩权重',
        'trans_rate_target'=>'转化率指标',
        'trans_rate_weight'=>'转化率权重',
        'call_sec_target'=>'日均通时指标',
        'call_sec_weight'=>'日均通时权重',
        'holiday_day'=>'请假天数',
        'work_day'=>'工作天数',
    ];

    //cc
    public function getKeyMap()
    {
        return $this->key_map;
    }

    public function getSalaryData($condition, $page=1, $limit=20)
    {
        $this->key_map['id'] = 'id';
        $base_data = parent::getSalaryData($condition, $page, $limit);
        if(!empty($base_data)){
            foreach($base_data as $k=>$item){
                $base_data[$k]['work_day'] = $item['work_day']= ($item['work_day']>=$item['holiday_day'])?$item['work_day']-$item['holiday_day']:0;
                $base_data[$k]['online_time'] = $item['online_time']?date('Y-m-d', $item['online_time']):'';
                $base_data[$k]['resign_time'] = $item['resign_time']?date('Y-m-d', $item['resign_time']):'';
            }
        }
        return $base_data;
    }

}