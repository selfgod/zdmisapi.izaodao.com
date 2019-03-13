<?php
/**
 * Created by PhpStorm.
 * User: songwenyao
 * Date: 2018/11/29
 * Time: 2:09 PM
 */
namespace App\Model\Sales\Team;

use App\HttpController\Sales\Manage\WorkingDays;
use App\Traits\SalesManageAuditLog;
use Base\BaseModel;
use Base\Db;

class SalesKpiModel extends BaseModel
{
    use SalesManageAuditLog;

    protected $kpi_standard_table = 'sales_kpi_standard';
    protected $kpi_salary_table = 'sales_kpi_salary';
    protected $key_map = [
        'data_date'=>'日期',
        'dept'=>'部门',
        'team'=>'团队',
        'salesman'=>'姓名',
        'level'=>'职级',
        //'online_time'=>'上线日期',
        'is_share'=>'实际分标人数',
        'salary_base'=>'基本工资',
        'salary_kpi_base'=>'基本绩效工资',
        '"" as perform_target_team'=>'团队业绩指标',
        'perform_weight_team'=>'团队业绩权重',
        'trans_rate_target'=>'团队转化率指标',
        'trans_rate_weight'=>'团队转化率权重',
        '"" as perform_target'=>'人均产能指标',
        'perform_weight'=>'人均产能权重',
        //'share_num_team'=>'实际分标人数',
        'resign_target'=>'团队流失率指标',
        'resign_weight'=>'团队流失率权重',
        'sales_num'=>'月初人数',
        'sales_num_new'=>'新进人数',
        'sales_num_resign'=>'离职人数',
        'call_sec_target'=>'团队通时指标',
        'call_sec_weight'=>'团队通时权重',
        '"" as share_day'=>'分标天数',
        'work_day'=>'工作天数',
    ];
    protected $work_day_default = 0; //设置的工作天数，用于团队工资人均产能计算

    function __get($name)
    {
        if(isset($this->$name)){
            return($this->$name);
        }else{
            return(NULL);
        }
    }

    public function getOne($id)
    {
        $info = Db::slave('zd_sales')->select('*')->from($this->kpi_salary_table)
            ->where('id = :id and is_delete=0')
            ->bindValue('id', $id)
            ->row();
        return $info;
    }

    public function formatAuditBefore($before)
    {
        $before['online_time'] = date('Y-m-d', $before['online_time']);
        return $before;
    }

    //团队
    public function getKeyMap()
    {
        return $this->key_map;
    }


    function saveStandardRate($data, $operator=0, $id=0)
    {
        if($id){
            $data['modify_user'] = $operator;
            $data['modify_time'] = date('Y-m-d H:i:s');
            $res = Db::master('zd_sales')->update($this->kpi_standard_table)
                ->where("id=:id")
                ->bindValues(['id'=>$id])
                ->cols($data)->query();
            if($res) $res = $id;
        }else{
            $data['create_user'] = $operator;
            $res = Db::master('zd_sales')->insert($this->kpi_standard_table)
                ->cols($data)->query();
        }
        return $res;
    }

    function getStandardRate($type, $business_type, $region, $sales_type)
    {
        $cur_month = date('Y-m');
        $res = Db::slave('zd_sales')->select('*')
            ->from($this->kpi_standard_table)
            ->where('type=:type and data_date="'.$cur_month.'" and business_type=:business_type and region=:region and sales_type=:sales_type and is_del=0')
            ->bindValues([
                'business_type'=>$business_type,
                'region'=>$region,
                'sales_type'=>$sales_type,
                'type'=>$type
            ])->orderByASC(['id'])
            ->query();
        return $res;
    }

    function getSalaryDataCount($condition)
    {
        $this->sWhere = '1=1';
        $this->sBindValues = [];
        $condition['is_delete'] = 0;
        $this->setSqlWhereAnd($condition);
        $res = Db::slave('zd_sales')
            ->from($this->kpi_salary_table)->select('COUNT(*)')
            ->where($this->sWhere)
            ->bindValues($this->sBindValues)
            ->single();
        return $res;
    }

    function getSalaryData($condition, $page=1, $limit=20)
    {
        $fields = array_keys($this->key_map);
        $this->sWhere = '1=1';
        $this->sBindValues = [];
        $condition['is_delete'] = 0;
        $this->setSqlWhereAnd($condition);
        $query = Db::slave('zd_sales')->select($fields)
            ->from($this->kpi_salary_table)
            ->where($this->sWhere)
            ->bindValues($this->sBindValues)->orderByASC(['salesman_id']);

        if ($limit > 0) $query->setPaging($limit)->page($page);
        $res = $query->query();
        return $res;
    }

    function getSalaryItem($condition, $fields='*')
    {
        $this->sWhere = '1=1';
        $this->sBindValues = [];
        $condition['is_delete'] = 0;
        $this->setSqlWhereAnd($condition);
        return Db::slave('zd_sales')->select($fields)
            ->from($this->kpi_salary_table)
            ->where($this->sWhere)
            ->bindValues($this->sBindValues)
            ->row();
    }

    function setSalaryData($id, $data)
    {
        return $this->_setSalaryData(['id'=>$id], $data);
    }

    function _setSalaryData($condition, $data)
    {
        $this->sWhere = '1=1';
        $this->sBindValues = [];
        $this->setSqlWhereAnd($condition);
        $res = Db::master('zd_sales')
            ->update($this->kpi_salary_table)
            ->cols($data)
            ->where($this->sWhere)
            ->bindValues($this->sBindValues)
            ->query();
        return $res;
    }

    function insertSalaryDataMulti($data)
    {
        $insert_num = 0;
        foreach($data as $item){
            $res = Db::master('zd_sales')
                ->insert($this->kpi_salary_table)
                ->cols($item)
                ->query();
            if($res) $insert_num++;
        }
        return $insert_num;
    }

    function getWorkDaySum($team_id, $data_date)
    {
        $work_day = $this->getSumField($team_id, $data_date, 'work_day');
        $holiday_day = $this->getSumField($team_id, $data_date, 'holiday_day');
        return $work_day-$holiday_day;
    }

    function getCallSecSum($team_id, $data_date)
    {
        return $this->getSumField($team_id, $data_date, 'call_sec');
    }

    function getOrderNumSum($team_id, $data_date)
    {
        return $this->getSumField($team_id, $data_date, 'order_num');
    }

    function getSourceNumSum($team_id, $data_date)
    {
        return $this->getSumField($team_id, $data_date, 'source_num');
    }

    function getPerformActualSum($team_id, $data_date)
    {
        return $this->getSumField($team_id, $data_date, 'perform_actual');
    }

    function getShareNum($team_id, $data_date)
    {
        return $this->getSumField($team_id, $data_date, 'if(is_share="1",1,0)');
    }
    function getPerformTargetTeam($team_id, $data_date)
    {
        return $this->getSumField($team_id, $data_date, 'if(is_share="1",perform_target,0)');
    }
    function getShareDaySum($team_id, $data_date)
    {
        return $this->getSumField($team_id, $data_date, 'if(is_share="1",share_day,0)');
    }
    function getSalesNum($team_id, $data_date)
    {
        return $this->getSumFieldGroup($team_id, $data_date, 'sales_num');
    }
    function getSalesNumNew($team_id, $data_date)
    {
        return $this->getSumFieldGroup($team_id, $data_date, 'sales_num_new');
    }
    function getSalesNumResign($team_id, $data_date)
    {
        return $this->getSumFieldGroup($team_id, $data_date, 'sales_num_resign');
    }

    //业绩达标率
    function calcPerform_rate($item)
    {
        $res = $item['perform_target']&&$item['perform_actual']?$this->numberFormat(($item['perform_actual']/$item['perform_target'])*100,3):0;
        //if($res>=120) return 120;
        return $res;
    }

    //业绩达标率团队
    function calcPerform_rate_team($item)
    {
        $res = $item['perform_target_team']&&$item['perform_actual']?$this->numberFormat(($item['perform_actual']/$item['perform_target_team'])*100,3):0;
        if($res>=120) return 120;
        return $res;
    }

    //团队人均产能
    function calcPerformTarget($item)
    {
        $res = $item['share_day']?($item['perform_target_team']/$item['share_day']*$this->work_day_default):0;
        return $res;
    }

    //业绩指标绩效工资
    function calcSalary_kpi($item)
    {
        return (($item['perform_rate']/100)*$item['salary_kpi_base']*($item['perform_weight']/100));
    }
    //业绩指标绩效工资
    function calcSalary_kpi_team($item)
    {
        return (($item['perform_rate']/100)*$item['salary_kpi_base']*($item['perform_weight_team']/100));
    }
    //转化率实际完成
    function calcTrans_rate_actual($item)
    {
        $res = $item['source_num']&&$item['order_num']?$this->numberFormat(($item['order_num']/$item['source_num'])*100,3):0;
        if($res>=120) return 120;
        return $res;
    }
    //转化率达标率
    function calcTrans_rate_rate($item)
    {
        $res = ((float)$item['trans_rate_target']&&(float)$item['trans_rate_actual'])?$this->numberFormat(($item['trans_rate_actual']/$item['trans_rate_target'])*100, 3):0;
        if($res>=100) return 100;
        return $res;
    }
    //转化率绩效工资
    function calcSalary_trans_rate($item)
    {
        return (($item['trans_rate_weight']/100)*($item['trans_rate_rate']/100)*$item['salary_kpi_base']);
    }
    //日均通时达标率
    function calcCall_sec_avg_rate($item)
    {
        //Logger::getInstance()->log(print_r($item, true));
        $res = ((float)$item['call_sec']&&$item['work_day']&&(float)$item['call_sec_target'])?
            $this->numberFormat((($item['call_sec']/$item['work_day'])/$item['call_sec_target'])*100,3):0;
        if($res>=120) return 120;
        return $res;
    }
    //日均通时绩效工资
    function calcSalary_kpi_sec($item)
    {
        return (($item['call_sec_avg_rate']/100)*$item['salary_kpi_base']*($item['call_sec_weight']/100));
    }
    //综合达标率
    function calcComposite_rate($item)
    {
        return $this->numberFormat($item['perform_rate']+$item['trans_rate_rate']+$item['call_sec_avg_rate'],3);
    }
    function calcComposite_rate_team($item)
    {
        return $this->numberFormat($item['perform_rate']+$item['resign_rate_rate']+$item['perform_actual_team_rate']+$item['trans_rate_rate']+$item['call_sec_avg_rate'],2);
    }
    //实际绩效考核工资
    function calcSalary_kpi_actual($item)
    {
        return ($item['salary_kpi']+$item['salary_trans_rate']+$item['salary_kpi_sec']);
    }
    //绩效权重合计
    function calcSalary_kpi_weight_total($item)
    {
        $val = ((($item['perform_rate']/100)*($item['perform_weight']/100))+
            (($item['trans_rate_rate']/100)*($item['trans_rate_weight']/100))+
            (($item['call_sec_avg_rate']/100)*($item['call_sec_weight']/100)))*100;
        return $this->numberFormat($val, 3);
    }
    function calcSalary_kpi_weight_total_team($item)
    {
        $val = (
                (($item['perform_rate']/100)*($item['perform_weight_team']/100))+
                (($item['trans_rate_rate']/100)*($item['trans_rate_weight']/100))+
                (($item['call_sec_avg_rate']/100)*($item['call_sec_weight']/100))+
                (($item['perform_actual_team_rate']/100)*($item['perform_weight']/100))+
                (($item['resign_rate_rate']/100)*($item['resign_weight']/100))
            )*100;
        return $this->numberFormat($val, 3);
    }
    //团队实际人均产能
    function calcPerform_actual_team($item)
    {
        //Logger::getInstance()->log(print_r($item, true));
        return ($item['perform_actual']&&$item['share_day']&&$this->work_day_default)
        ?$item['perform_actual']/($item['share_day']/$this->work_day_default):0;
    }
    //团队人均产能达成率
    function calcPerform_actual_team_rate($item)
    {
        $res = ($item['perform_actual_team'] && $item['perform_target'])?$this->numberFormat(($item['perform_actual_team']/$item['perform_target'])*100,3):0;
        if($res>=120) return 120;
        return $res;
    }
    //人均产能绩效工资
    function calcSalary_perform_actual_team($item)
    {
        return $this->numberFormat(($item['perform_actual_team_rate']/100)*$item['salary_kpi_base']*($item['perform_weight']/100),3);
    }
    //团队流失率
    function calcResign_rate($item)
    {
        $res = ($item['sales_num_resign']&&($item['sales_num']+$item['sales_num_new']))
            ?$this->numberFormat(($item['sales_num_resign']/($item['sales_num']+$item['sales_num_new']))*100,3):0;
        if($res>=120) return 120;
        return $res;
    }
    //团队达成率
    function calcResign_rate_rate($item)
    {
        $standard_rate = $this->getStandardRate('resign',$item['business_type'],$item['region'],
            $this->getSalesTypeForStructureType($item['structure_type']));
        //print_r($standard_rate);
        if(!empty($standard_rate)){
            $bonus = 0;
            foreach($standard_rate as $_item){
                if($_item['max_rate']==0 && $_item['min_rate'] && $item['resign_rate']>=$_item['min_rate']){
                    $bonus = $_item['bonus'];
                    break;
                }elseif($item['resign_rate']>=$_item['min_rate'] && $item['resign_rate']<$_item['max_rate']){
                    $bonus = $_item['bonus'];
                    break;
                }
            }
            return $bonus;//(($bonus/100)*(float)$item['salary_kpi_base']*((float)$item['resign_weight']/100));
        }
        return 0;
    }
    //团队流失绩效工资
    function calcSalary_kpi_resign($item)
    {
        return (($item['resign_rate_rate']/100)*(float)$item['salary_kpi_base']*((float)$item['resign_weight']/100));
    }
    //累计日均通时
    function calcCall_sec_avg($item)
    {
        return ($item['call_sec']&&$item['work_day'])?$this->numberFormat($item['call_sec']/$item['work_day'],3):0;
    }
    //达标奖
    function getSalary_kpi_reward($item)
    {
        $standard_rate = $this->getStandardRate('reward',$item['business_type'],$item['region'],
            $this->getSalesTypeForStructureType($item['structure_type']));
        //print_r($standard_rate);
        if(!empty($standard_rate)){
            $bonus = 0;
            foreach($standard_rate as $_item){
                if($_item['max_rate']==0 && $_item['min_rate'] && $item['salary_kpi_weight_total']>=$_item['min_rate']){
                    $bonus = $_item['bonus'];
                    break;
                }elseif($item['salary_kpi_weight_total']>=$_item['min_rate'] && $item['salary_kpi_weight_total']<$_item['max_rate']){
                    $bonus = $_item['bonus'];
                    break;
                }
            }
            return $item['perform_actual']>0?($item['perform_actual']*($bonus/100)):0;
        }
        return 0;
    }

    function getSalesTypeForStructureType($structure_type)
    {

        $sales_type = 0;
        switch ($structure_type){
            case 'salesman':
                $sales_type = 1;
                break;
            case 'team':
                $sales_type = 2;
                break;
            case 'dept':
                $sales_type = 3;
                break;
        }
        return $sales_type;
    }

    /**
     * 获取排行列表数量
     * @param $salesType
     * @param $businessType
     * @param $region
     * @param $date
     * @return string
     */
    public function getRankListNum($salesType, $businessType, $region, $date)
    {
        $where = 'data_date = :date and business_type = :businessType and region = :region and is_delete = 0';
        if ($salesType === '1') {
            $where .= " and structure_type = 'salesman'";
        } elseif ($salesType === '2') {
            $where .= " and structure_type = 'team'";
        } elseif ($salesType === '3') {
            $where .= " and structure_type = 'dept'";
        }
        $count = Db::slave('zd_sales')->select('count(*)')
            ->from($this->kpi_salary_table)
            ->where($where)
            ->bindValues([
                'date' => $date,
                'businessType' => $businessType,
                'region' => $region
            ])->single();
        return $count;
    }

    /**
     * 获取排行列表数据
     * @param $salesType
     * @param $businessType
     * @param $region
     * @param $date
     * @param $page
     * @param $limit
     * @param $order
     * @return mixed
     */
    public function getRankListData($salesType, $businessType, $region, $date, $page, $limit, $order)
    {
        $offset = $page * $limit;
        $fields = 'perform_actual,perform_rate,trans_rate_rate,call_sec_avg_rate,salary_kpi_weight_total';
        $where = 'data_date = :date and business_type = :businessType and region = :region and is_delete = 0';
        if ($salesType === '1') {
            $fields .= ',salesman';
            $where .= " and structure_type = 'salesman'";
        } elseif ($salesType === '2') {
            $fields .= ',team as salesman,perform_actual_team,perform_actual_team_rate,resign_rate_rate';
            $where .= " and structure_type = 'team'";
        } elseif ($salesType === '3') {
            $fields .= ',dept as salesman,perform_actual_team,perform_actual_team_rate,resign_rate_rate';
            $where .= " and structure_type = 'dept'";
        }
        $list = Db::slave('zd_sales')->select($fields)
            ->from($this->kpi_salary_table)
            ->where($where)
            ->bindValues([
                'date' => $date,
                'businessType' => $businessType,
                'region' => $region
            ])
            ->offset($offset)->limit($limit)->orderByDESC([$order])
            ->query();
        return empty($list) ? [] : $list;
    }

    function numberFormat($val, $decimals=2)
    {
        return number_format($val, $decimals, '.', '');
    }
}