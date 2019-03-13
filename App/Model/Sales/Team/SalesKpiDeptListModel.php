<?php
namespace App\Model\Sales\Team;

class SalesKpiDeptListModel extends SalesKpiDeptModel
{

    public function getKeyMap()
    {
        $key_map = (new SalesKpiTeamListModel())->getKeyMap();
        unset($key_map['team']);
        return $key_map;
    }

    public function getSalaryData($condition, $page=1, $limit=20)
    {
        $this->key_map = $this->getKeyMap();
        $this->key_map['business_type'] = 1;
        $this->key_map['region'] = 1;
        $this->key_map['structure_type'] = 1;
        $base_data = parent::getSalaryData($condition, $page, $limit);
        if(!empty($base_data)){
            foreach($base_data as $k=>$item){
                $item['perform_actual'] = $this->getPerformActualSum($this->dept_id[$k], $condition['data_date']);
                $item['perform_rate']= $this->calcPerform_rate_team($item);
                $base_data[$k]['source_num'] = $item['source_num'] = $this->getSourceNumSum($this->dept_id[$k], $condition['data_date']);
                $base_data[$k]['order_num'] = $item['order_num'] = $this->getOrderNumSum($this->dept_id[$k], $condition['data_date']);
                $item['trans_rate_actual'] = $this->calcTrans_rate_actual($item);
                $item['trans_rate_rate'] = $this->calcTrans_rate_rate($item);
                $item['perform_actual_team'] = $this->calcPerform_actual_team($item);
                $item['perform_actual_team_rate'] = $this->calcPerform_actual_team_rate($item);
                $item['resign_rate'] = $this->calcResign_rate($item);
                $item['resign_rate_rate'] = $this->calcResign_rate_rate($item);
                $base_data[$k]['call_sec'] = $item['call_sec'] = $this->getCallSecSum($this->dept_id[$k], $condition['data_date']);
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