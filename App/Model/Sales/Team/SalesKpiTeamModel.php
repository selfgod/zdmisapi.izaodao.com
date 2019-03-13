<?php
/**
 * Created by PhpStorm.
 * User: songwenyao
 * Date: 2018/11/29
 * Time: 2:09 PM
 */
namespace App\Model\Sales\Team;

use Base\Db;

class SalesKpiTeamModel extends SalesKpiModel
{
    protected $team_id = null;
    public function getKeyMap()
    {
        $key_map = $this->key_map;
        return $key_map;
    }

    public function getSalaryData($condition, $page=1, $limit=20)
    {
        $this->work_day_default = (new WorkingDaysModel())->getNum($condition['business_type'],
            $condition['region'], $condition['data_date']);
        $this->key_map['id'] = 'id';
        $this->key_map['team_id'] = 'id';
        $base_data = parent::getSalaryData($condition, $page, $limit);
        if(!empty($base_data)){
            foreach($base_data as $k=>$item){
                //$base_data[$k]['online_time'] = $item['online_time']?date('Y-m-d', $item['online_time']):'';
                $base_data[$k]['perform_target_team'] = $item['perform_target_team'] = $this->getPerformTargetTeam($item['team_id'], $condition['data_date']);
                $base_data[$k]['is_share'] = $this->getShareNum($item['team_id'], $condition['data_date']);
                $base_data[$k]['share_day'] = $item['share_day'] = $this->getShareDaySum($item['team_id'], $condition['data_date']);
                $base_data[$k]['work_day'] = $item['work_day'] = $this->getWorkDaySum($item['team_id'], $condition['data_date']);
                $item['perform_target'] = $this->calcPerformTarget($item);
                $base_data[$k]['perform_target'] = $this->numberFormat($item['perform_target'],2);
                $this->team_id[$k] = $item['team_id'];
                unset($base_data[$k]['team_id']);
            }
        }
        return $base_data;
    }

    function getSumField($team_id, $data_date, $sum_field)
    {
        $this->sWhere = '1=1';
        $this->sBindValues = [];
        $this->setSqlWhereAnd([
            'team_id'=>$team_id,
            'structure_type'=>'salesman',
            'data_date'=>$data_date,
            'is_delete'=>0
        ]);
        $res = Db::slave('zd_sales')
            ->from($this->kpi_salary_table)->select("sum({$sum_field})")
            ->where($this->sWhere)
            ->bindValues($this->sBindValues)
            ->single();
        return $res?$res:0;
    }

}