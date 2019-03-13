<?php
namespace App\Model\Sales\Team;

class SalesKpiCCListModel extends SalesKpiCCModel
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
        'perform_target'=>'业绩指标',
        'perform_weight'=>'业绩权重',
        'perform_actual'=>'实际业绩',
        '"" as perform_rate'=>'业绩达标率',
        'trans_rate_target'=>'转化率指标',
        'trans_rate_weight'=>'转化率权重',
        'source_num'=>'资源量',
        'order_num'=>'有效订单',
        'trans_rate_actual'=>'转化率实际完成',
        'trans_rate_rate'=>'转化率达标率',
        'call_sec_target'=>'日均通时指标',
        'call_sec_weight'=>'日均通时权重',
        'work_day'=>'工作天数',
        'call_sec'=>'有效通时',
        '"" as call_sec_avg'=>'累计日均通时',
        '"" as call_sec_avg_rate'=>'日均通时达标率',
        '"" as composite_rate'=>'综合达标率',
        '"" as salary_kpi_weight_total'=>'绩效权重合计' //计算
    ];

    public function getSalaryData($condition, $page=1, $limit=20)
    {
        unset($this->key_map['salary_kpi_weight_total']);
        $this->key_map['business_type'] = 1;
        $this->key_map['region'] = 1;
        $this->key_map['structure_type'] = 1;
        $this->key_map['holiday_day'] = 1;
        $base_data = parent::getSalaryData($condition, $page, $limit);
        if(!empty($base_data)){
            foreach($base_data as $k=>$item){
                $item['perform_rate']= $this->calcPerform_rate($item);
                $item['trans_rate_actual'] = $this->calcTrans_rate_actual($item);
                $item['trans_rate_rate'] = $this->calcTrans_rate_rate($item);
                $item['call_sec_avg'] = $this->calcCall_sec_avg($item);
                $item['call_sec_avg_rate'] = $this->calcCall_sec_avg_rate($item);
                $base_data[$k]['composite_rate'] = $this->calcComposite_rate($item);
                $item['salary_kpi_weight_total'] = $this->calcSalary_kpi_weight_total($item);

                $base_data[$k]['perform_rate'] = $this->numberFormat($item['perform_rate'], 2);
                $base_data[$k]['trans_rate_actual'] = $this->numberFormat($item['trans_rate_actual'], 2);
                $base_data[$k]['trans_rate_rate'] = $this->numberFormat($item['trans_rate_rate'], 2);
                $base_data[$k]['call_sec_avg'] = $this->numberFormat($item['call_sec_avg'], 2);
                $base_data[$k]['call_sec_avg_rate'] = $this->numberFormat($item['call_sec_avg_rate'], 2);
                $base_data[$k]['salary_kpi_weight_total'] = $this->numberFormat($item['salary_kpi_weight_total'], 2);

                unset($base_data[$k]['business_type'], $base_data[$k]['region'], $base_data[$k]['structure_type'], $base_data[$k]['holiday_day']);
            }
        }
        return $base_data;
    }

}