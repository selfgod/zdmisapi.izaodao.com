<?php
namespace App\Model\Sales\Team;

class SalesKpiTeamListModel extends SalesKpiTeamModel
{
    protected $key_map = [
        'data_date'=>'日期',
        'dept'=>'部门',
        'team'=>'团队',
        'salesman'=>'姓名',
        'level'=>'职级',
        //'online_time'=>'上线日期',
        'is_share'=>'实际分标人数',
        '"" as perform_target_team'=>'团队业绩指标',
        'perform_weight_team'=>'团队业绩权重',
        'perform_actual'=>'实际业绩',
        '"" as perform_rate'=>'业绩达标率',
        'trans_rate_target'=>'转化率指标',
        'trans_rate_weight'=>'转化率权重',
        'source_num'=>'资源量',
        'order_num'=>'有效订单',
        'trans_rate_actual'=>'转化率实际完成',
        'trans_rate_rate'=>'转化率达标率',
        '"" as perform_target'=>'人均产能指标',
        'perform_weight'=>'人均产能权重',
        '"" as perform_actual_team'=>'团队实际人均产能',
        '"" as perform_actual_team_rate'=>'团队人均产能达标率',
        'resign_target'=>'团队流失率指标',
        'resign_weight'=>'团队流失率权重',
        'sales_num'=>'月初人数',
        'sales_num_new'=>'新进人数',
        'sales_num_resign'=>'离职人数',
        'resign_rate'=>'团队流失率',
        '"" as resign_rate_rate'=>'团队流失率达标率',
        'call_sec_target'=>'团队通时指标',
        'call_sec_weight'=>'团队通时权重',
        'call_sec'=>'有效通时',
        '"" as share_day'=>'分标天数',
        '"" as work_day'=>'工作天数',
        '"" as call_sec_avg'=>'累计日均通时',
        '"" as call_sec_avg_rate'=>'日均通时达标率',
        '"" as composite_rate'=>'综合达标率',
        '"" as salary_kpi_weight_total'=>'绩效权重合计', //计算
    ];

    public function getKeyMap()
    {
        return $this->key_map;
    }

    public function getSalaryData($condition, $page=1, $limit=20)
    {
        $this->key_map['business_type'] = 1;
        $this->key_map['region'] = 1;
        $this->key_map['structure_type'] = 1;
        $base_data = parent::getSalaryData($condition, $page, $limit);
        if(!empty($base_data)){
            foreach($base_data as $k=>$item){
                $item['perform_actual'] = $this->getPerformActualSum($this->team_id[$k], $condition['data_date']);
                $item['perform_rate']= $this->calcPerform_rate_team($item);
                $base_data[$k]['source_num'] = $item['source_num'] = $this->getSourceNumSum($this->team_id[$k], $condition['data_date']);
                $base_data[$k]['order_num'] = $item['order_num'] = $this->getOrderNumSum($this->team_id[$k], $condition['data_date']);
                $item['trans_rate_actual'] = $this->calcTrans_rate_actual($item);
                $item['trans_rate_rate'] = $this->calcTrans_rate_rate($item);
                $item['perform_actual_team'] = $this->calcPerform_actual_team($item);
                $item['perform_actual_team_rate'] = $this->calcPerform_actual_team_rate($item);
                $item['resign_rate'] = $this->calcResign_rate($item);
                $item['resign_rate_rate'] = $this->calcResign_rate_rate($item);
                $base_data[$k]['call_sec'] = $item['call_sec'] = $this->getCallSecSum($this->team_id[$k], $condition['data_date']);
                $item['call_sec_avg'] = $this->calcCall_sec_avg($item);
                $item['call_sec_avg_rate'] = $this->calcCall_sec_avg_rate($item);
                $item['salary_kpi_weight_total']= $this->calcSalary_kpi_weight_total_team($item);

                $base_data[$k]['perform_actual'] = $this->numberFormat($item['perform_actual'],2);
                $base_data[$k]['perform_rate'] = $this->numberFormat($item['perform_rate'],2);
                $base_data[$k]['trans_rate_actual'] = $this->numberFormat($item['trans_rate_actual'], 2);
                $base_data[$k]['trans_rate_rate'] = $this->numberFormat($item['trans_rate_rate'], 2);
                $base_data[$k]['perform_actual_team'] = $this->numberFormat($item['perform_actual_team'],2);
                $base_data[$k]['perform_actual_team_rate'] = $this->numberFormat($item['perform_actual_team_rate'],2);
                $base_data[$k]['resign_rate'] = $this->numberFormat($item['resign_rate'],2);
                $base_data[$k]['resign_rate_rate'] = $this->numberFormat($item['resign_rate_rate'],2);
                $base_data[$k]['call_sec_avg'] = $this->numberFormat($item['call_sec_avg'],2);
                $base_data[$k]['call_sec_avg_rate'] = $this->numberFormat($item['call_sec_avg_rate'], 2);
                $base_data[$k]['salary_kpi_weight_total'] = $this->numberFormat($item['salary_kpi_weight_total'], 2);
                $base_data[$k]['composite_rate'] = $this->calcComposite_rate_team($item);

                unset($base_data[$k]['business_type'],
                    $base_data[$k]['region'],
                    $base_data[$k]['structure_type']);
            }
        }
        return $base_data;
    }

}