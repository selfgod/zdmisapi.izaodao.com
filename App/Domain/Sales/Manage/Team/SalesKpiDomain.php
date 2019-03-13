<?php
/**
 * Created by PhpStorm.
 * User: songwenyao
 * Date: 2018/11/29
 * Time: 2:01 PM
 */
namespace App\Domain\Sales\Manage\Team;

use App\Domain\Sales\Setting\TeamStructureDomain;
use App\Model\Common\Permission;
use App\Model\Common\User;
use App\Model\Sales\Setting\SalesLevelModel;
use App\Model\Sales\Setting\TeamStructureModel;
use App\Model\Sales\Team\SalesKpiCCCalcModel;
use App\Model\Sales\Team\SalesKpiCCListModel;
use App\Model\Sales\Team\SalesKpiCCModel;
use App\Model\Sales\Team\SalesKpiDeptCalcModel;
use App\Model\Sales\Team\SalesKpiDeptListModel;
use App\Model\Sales\Team\SalesKpiDeptModel;
use App\Model\Sales\Team\SalesKpiModel;
use App\Model\Sales\Team\SalesKpiTeamCalcModel;
use App\Model\Sales\Team\SalesKpiTeamListModel;
use App\Model\Sales\Team\SalesKpiTeamModel;
use Base\BaseDomain;
use Base\Helper\ArrayHelper;
use EasySwoole\Core\Component\Logger;
use Lib\Export;

class SalesKpiDomain extends BaseDomain
{

    function setStandardRate($param)
    {
        $operator = $param['userInfo']['uid'];
        $id = $param['id']?$param['id']:0;
        $data = [
            'type'=>$param['type'],
            'business_type'=>$param['business_type'],
            'region'=>$param['region'],
            'sales_type'=>$param['sales_type'],
            'min_rate'=>$param['min_rate'],
            'max_rate'=>$param['max_rate'],
            'bonus'=>$param['bonus'],
        ];
        return (new SalesKpiModel())->saveStandardRate($data, $operator, $id);
    }

    function getStandardRate($param)
    {
        $result = (new SalesKpiModel())->getStandardRate($param['type'],$param['business_type'], $param['region'], $param['sales_type']);
        return $result;
    }

    function delStandardRateItem($param)
    {
        $operator = $param['userInfo']['uid'];
        $id = $param['id']?$param['id']:0;
        $data = [
            'is_del'=>1,
        ];
        $result = (new SalesKpiModel())->saveStandardRate($data, $operator, $id);
        return $result;
    }

    function initSalesTeam($year_month, $business_type, $region)
    {
        $Structure = new TeamStructureDomain();
        $StructureModel = new TeamStructureModel();
        $init_condition = ['year_month'=>$year_month, 'business_type'=>$business_type, 'region'=>$region];
        $dept_arr = $Structure->getDept($init_condition);
        if(!empty($dept_arr)){
            $data = [];
            $init_data = [
                'data_date'=>$year_month,
                'business_type'=>$business_type,
                'region'=>$region,
            ];
            foreach($dept_arr as $item){
                //取得部门负责人
                $relation_arr = $StructureModel->getDeptUid($item['id']);
                if(!empty($relation_arr)){
                    foreach($relation_arr as $relation){
                        $_data = $init_data;
                        $_data['dept'] = $item['name'];
                        $_data['dept_id'] = $item['id'];
                        $_data['structure_type'] = 'dept';
                        $_data['salesman'] = $relation['username'];
                        $_data['salesman_id'] = $relation['uid'];
                        $data[] = $_data;
                    }
                }

                $team_arr = $Structure->getTeam(['year_month'=>$year_month, 'parent_id'=>$item['id']]);
                if(!empty($team_arr)){
                    foreach($team_arr as $item1){
                        //取团队负责人
                        $relation_arr = $StructureModel->getDeptUid($item1['id']);
                        foreach($relation_arr as $relation){
                            $_data = $init_data;
                            $_data['structure_type'] = 'team';
                            $_data['dept'] = $item['name'];
                            $_data['dept_id'] = $item['id'];
                            $_data['team'] = $item1['name'];
                            $_data['team_id'] = $item1['id'];
                            $_data['salesman'] = $relation['username'];
                            $_data['salesman_id'] = $relation['uid'];
                            $data[] = $_data;
                        }
                        $salesman_arr = $Structure->getSalesman(['year_month'=>$year_month, 'parent_id'=>$item1['id']]);
                        if(!empty($salesman_arr)){
                            foreach($salesman_arr as $item2){
                                $_data = $init_data;
                                $_data['structure_type'] = 'salesman';
                                $_data['dept'] = $item['name'];
                                $_data['dept_id'] = $item['id'];
                                $_data['team'] = $item1['name'];
                                $_data['team_id'] = $item1['id'];
                                $_data['salesman'] = $item2['name'];
                                $_data['salesman_id'] = $item2['id'];
                                $data[] = $_data;
                            }
                        }

                    }
                }
            }

            //插入数据
            if($num = (new SalesKpiModel())->insertSalaryDataMulti($data)){
                Logger::getInstance()->log('insert:'.$num);
                return $num;
            }
        }
        return 0;
    }

    function setSalesTeam($param)
    {
        Logger::getInstance()->log(print_r($param, true));
        $id = $param['id'];
        $uid = $param['uid'];
        if($id<=0) return 0;
        unset($param['id'],$param['userInfo'],$param['sessionId'],$param['uid']);
        switch ($param['view_type']){
            case 'cc_calc':
                $model = new SalesKpiCCCalcModel();
                break;
            case 'cc_list':
                $model = new SalesKpiCCModel();
                break;
            case 'team_list':
                $model = new SalesKpiTeamModel();
                break;
            case 'dept_list':
                $model = new SalesKpiDeptModel();
                break;
            default :
                $model = new SalesKpiModel();

        }
        unset($param['view_type']);

        if($param['level']===null) unset($param['level']);
        if($param['online_time']===null) unset($param['online_time']);
        if($param['resign_time']===null) unset($param['resign_time']);
        if($param['is_share']===null) unset($param['is_share']);
        if($param['holiday_day']===null) unset($param['holiday_day']);
        if($param['share_day']===null) unset($param['share_day']);
        if($param['salary_base']===null) unset($param['salary_base']);
        if($param['salary_kpi_base']===null) unset($param['salary_kpi_base']);
        if($param['perform_target']===null) unset($param['perform_target']);
        if($param['perform_weight']===null) unset($param['perform_weight']);
        if($param['perform_weight_team']===null) unset($param['perform_weight_team']);
        if($param['trans_rate_target']===null) unset($param['trans_rate_target']);
        if($param['trans_rate_weight']===null) unset($param['trans_rate_weight']);
        if($param['call_sec_target']===null) unset($param['call_sec_target']);
        if($param['call_sec_weight']===null) unset($param['call_sec_weight']);
        if($param['salary_kpi_minus']===null)unset($param['salary_kpi_minus']);
        if($param['resign_target']===null)unset($param['resign_target']);
        if($param['resign_weight']===null)unset($param['resign_weight']);
        if($param['sales_num']===null)unset($param['sales_num']);
        if($param['sales_num_new']===null)unset($param['sales_num_new']);
        if($param['sales_num_resign']===null)unset($param['sales_num_resign']);


        $model->addAuditLog($model->kpi_salary_table, $id, $uid, $param);

        $return_data = ['work_day'=>0, 'share_day'=>0];


        if(isset($param['level']) && $param['level']==0){
            $param['work_day'] = $return_data['work_day'] = 0;
            $param['share_day'] = $return_data['share_day'] = 0;
        }else{
            $data_item = (new SalesKpiModel())->getSalaryItem(['id'=>$id], 'is_share, business_type,online_time,resign_time,work_day, share_day,region, holiday_day');

            $is_share = isset($param['is_share'])?$param['is_share']:$data_item['is_share'];

            if(!isset($param['online_time'])&&$data_item['online_time']) {
                $param['online_time'] = date('Y-m-d', $data_item['online_time']);
            }
            if(!isset($param['resign_time'])&&$data_item['resign_time']) {
                $param['resign_time'] = date('Y-m-d', $data_item['resign_time']);
            }
            if(empty($param['online_time'])){
                $param['work_day'] = $return_data['work_day'] = 0;
                $param['share_day'] = $return_data['share_day'] = 0;
            }elseif((strtotime($param['online_time'])!=$data_item['online_time']) ||
                (strtotime($param['resign_time'])!=$data_item['resign_time'])
            ){
                //if(!isset($param['resign_time'])) $param['resign_time'] = date('Y-m-d', $data_item['resign_time']);
                //计算工作天数
                if($param['online_time']>=date('Y-m-01')){
                    $param['work_day'] = $this->calcWorkDay($param['online_time'], $param['resign_time'],
                        $data_item['business_type'], $data_item['region']);
                    if($is_share && !$data_item['share_day'])
                        $param['share_day'] = $this->calcShareDay($param['online_time'], $data_item['business_type'], $data_item['region']);
                }else{
                    $param['work_day'] = $this->calcWorkDay(date('Y-m-01'), $param['resign_time'],
                        $data_item['business_type'], $data_item['region']);
                    if($is_share && !$data_item['share_day'])
                        $param['share_day'] = $this->calcShareDay(date('Y-m-01'), $data_item['business_type'], $data_item['region']);
                }
            }

            if(isset($param['share_day'])){
                $return_data['share_day'] = $param['share_day'];
            }else{
                $return_data['share_day'] = $data_item['share_day'];
            }
            $return_data['work_day'] = (isset($param['work_day'])?$param['work_day']:$data_item['work_day'])-$data_item['holiday_day'];

        }
        if(isset($param['online_time'])){
            if($param['online_time']!=''){
                $param['online_time'] = strtotime($param['online_time']);
            }else{
                $param['online_time'] = 0;
            }
        }
        if(isset($param['resign_time'])){
            if($param['resign_time']!=''){
                $param['resign_time'] = strtotime($param['resign_time']);
            }else{
                $param['resign_time'] = 0;
            }
        }


        return [(new SalesKpiModel())->setSalaryData($id, $param), $return_data];
    }

    function calcShareDay($online_time, $business_type, $region)
    {
        $WorkingDays = new WorkingDaysDomain();
        $day =0;
        $first_day = date('Y-m-01');
        if($online_time>$first_day){
            for($d = $first_day;$d<$online_time;$d=date('Y-m-d', strtotime($d.' +1 day'))){
                if($WorkingDays->isWorkingDay($business_type, $region, strtotime($d))) $day++;
            }
        }
        $num = $WorkingDays->getNum($business_type, $region, date('Y-m'));
        return $num-$day;
    }

    //单人工作天数,更新用
    function calcWorkDay($online_time, $resign_time='', $business_type, $region)
    {
        $day =0;
        $today = date('Y-m-d');
        $cur = false;
        if($resign_time && $resign_time>date('Y-m-01') && $resign_time<=$today){
            if($resign_time<$today)
                $cur = true;
            $today = $resign_time;
        }
        if($online_time<$today){
            $WorkingDays = new WorkingDaysDomain();
            if($cur){
                for($d = $online_time;$d<=$today;$d=date('Y-m-d', strtotime($d.' +1 day'))){
                    if($WorkingDays->isWorkingDay($business_type, $region, strtotime($d))) $day++;
                }
            }else{
                for($d = $online_time;$d<$today;$d=date('Y-m-d', strtotime($d.' +1 day'))){
                    if($WorkingDays->isWorkingDay($business_type, $region, strtotime($d))) $day++;
                }
            }

        }else{
            $day = 0;
        }
        return $day;
    }

    function getSalesTeam($param)
    {
        $view_type = isset($param['view_type'])?$param['view_type']:'team_list';
        switch ($view_type){
            case 'cc_calc':
            case 'cc_list':
            case 'cc_kpi_calc':
                $condition = [
                    'data_date'=>$param['year_month'],
                    'business_type'=>$param['business_type'],
                    'region'=>$param['region'],
                    'structure_type'=>'salesman'
                    /*'dept_id'=>$param['dept'],
                    'team_id'=>$param['team'],
                    'salesman_id'=>$param['salesman'],*/
                ];
                if(isset($param['salesman'])){
                    $condition['salesman_id'] =$param['salesman'];
                }elseif(isset($param['team'])){
                    //查询team下所有cc
                    $salesman = (new TeamStructureDomain())->getSalesman([
                        'year_month'=>$param['year_month'],
                        'parent_id'=>$param['team'],
                    ]);
                    if(empty($salesman)) return false;
                    $condition['salesman_id'] = ['in'=>ArrayHelper::array_value_recursive('id', $salesman)];
                }elseif(isset($param['dept'])){
                    $salesman = (new TeamStructureDomain())->getSalesman([
                        'year_month'=>$param['year_month'],
                        'top_id'=>$param['dept'],
                    ]);
                    if(empty($salesman)) return false;
                    $condition['salesman_id'] = ['in'=>ArrayHelper::array_value_recursive('id', $salesman)];
                }
                if(isset($param['count'])){
                    $result = (new SalesKpiCCModel())->getSalaryDataCount($condition);
                }else{
                    $page = isset($param['page'])?$param['page']:1;
                    if($view_type=='cc_calc'){
                        $result = (new SalesKpiCCCalcModel())->getSalaryData($condition, $page);
                    } elseif ($view_type=='cc_kpi_calc'){
                        $result = (new SalesKpiCCListModel())->getSalaryData($condition, $page);
                    } else {
                        $result = (new SalesKpiCCModel())->getSalaryData($condition, $page);
                    }
                }
                return $result;
                break;
            case 'team_calc':
            case 'team_list':
            case 'team_kpi_calc':
                $condition = [
                    'data_date'=>$param['year_month'],
                    'business_type'=>$param['business_type'],
                    'region'=>$param['region'],
                    'structure_type'=>'team'
                    /*'dept_id'=>$param['dept'],
                    'team_id'=>$param['team'],
                    'salesman_id'=>$param['salesman'],*/
                ];
                if(isset($param['salesman'])){
                    $condition['salesman_id'] = $param['salesman'];
                }elseif(isset($param['team'])){
                    $condition['team_id'] = $param['team'];
                }elseif(isset($param['dept'])){
                    $team = (new TeamStructureDomain())->getTeam([
                        'year_month'=>$param['year_month'],
                        'parent_id'=>$param['dept'],

                    ]);
                    if(empty($team)) return false;
                    $condition['team_id'] = ['in'=>ArrayHelper::array_value_recursive('id', $team)];
                }
                if(isset($param['count'])){
                    $result = (new SalesKpiTeamModel())->getSalaryDataCount($condition);
                }else{
                    $page = isset($param['page'])?$param['page']:1;
                    if($view_type=='team_calc'){
                        $result = (new SalesKpiTeamCalcModel())->getSalaryData($condition, $page);
                    }elseif ($view_type=='team_kpi_calc') {
                        $result = (new SalesKpiTeamListModel())->getSalaryData($condition, $page);
                    } else {
                        $result = (new SalesKpiTeamModel())->getSalaryData($condition, $page);
                    }
                }
                return $result;
                break;
            case 'dept_calc':
            case 'dept_list':
            case 'dept_kpi_calc':
                $condition = [
                    'data_date'=>$param['year_month'],
                    'business_type'=>$param['business_type'],
                    'region'=>$param['region'],
                    'structure_type'=>'dept'
                    /*'dept_id'=>$param['dept'],
                    'team_id'=>$param['team'],
                    'salesman_id'=>$param['salesman'],*/
                ];
                if(isset($param['salesman'])){
                    $condition['salesman_id'] = $param['salesman'];
                }elseif(isset($param['dept'])){
                    $condition['dept_id'] = $param['dept'];
                }
                if(isset($param['count'])){
                    $result = (new SalesKpiDeptModel())->getSalaryDataCount($condition);
                }else{
                    $page = isset($param['page'])?$param['page']:1;
                    if($view_type=='dept_calc'){
                        $result = (new SalesKpiDeptCalcModel())->getSalaryData($condition, $page);
                    }elseif ($view_type=='dept_kpi_calc') {
                        $result = (new SalesKpiDeptListModel())->getSalaryData($condition, $page);
                    } else {
                        $result = (new SalesKpiDeptModel())->getSalaryData($condition, $page);
                    }
                }
                return $result;
                break;
        }
    }

    /**
     * @param $type [team_list,cc_list,team_kpi,cc_kpi,team_calc,cc_calc]
     * @return array
     */
    function getFields($type)
    {
        switch ($type){
            case 'dept_list':
                return (new SalesKpiDeptModel())->getKeyMap();
                break;
            case 'team_list':
                return (new SalesKpiTeamModel())->getKeyMap();
                break;
            case 'cc_list':
                return (new SalesKpiCCModel())->getKeyMap();
                break;
            case 'cc_calc':
                return (new SalesKpiCCCalcModel())->getKeyMap();
                break;
            case 'team_calc':
                return (new SalesKpiTeamCalcModel())->getKeyMap();
                break;
            case 'dept_calc':
                return (new SalesKpiDeptCalcModel())->getKeyMap();
                break;
            case 'cc_kpi_calc':
                return (new SalesKpiCCListModel())->getKeyMap();
                break;
            case 'team_kpi_calc':
                return (new SalesKpiTeamListModel())->getKeyMap();
                break;
            case 'dept_kpi_calc':
                return (new SalesKpiDeptListModel())->getKeyMap();
                break;
        }
    }

    /**
     * 获取个人kpi数据
     * @param $uid
     * @param $date
     * @param $structure_type
     * @return array|mixed|string
     */
    public function getMyKpi($uid, $date, $structure_type='')
    {
        $model = new SalesKpiModel();

        $w = [
            'salesman_id' => $uid,
            'data_date' => $date
        ];
        if($structure_type) $w['structure_type'] = $structure_type;
        $salesInfo = $model->getSalaryItem($w, 'structure_type, business_type, region');
        if (empty($salesInfo)) {
            return [];
        }
        $struct = $salesInfo['structure_type'];
        if ($struct === 'salesman') {
            $viewType = 'cc_calc';
        } elseif ($struct === 'team') {
            $viewType = 'team_calc';
        } elseif ($struct === 'dept') {
            $viewType = 'dept_calc';
        } else {
            return [];
        }
        $param = [
            'salesman' => $uid,
            'year_month' => $date,
            'business_type' => $salesInfo['business_type'],
            'region' => $salesInfo['region'],
            'view_type' => $viewType
        ];
        $kpi = $this->getSalesTeam($param);
        if (empty($kpi)) {
            return [];
        }
        $kpi = $kpi[0];
        $levelInfo = (new SalesLevelModel())->getOne($kpi['level']);
        $kpi['level'] = $levelInfo['sales_level'];
        $kpi['struct'] = $struct;
        return $kpi;
    }
    public function addTeamSalesKpi($param)
    {
        $team_str = explode(',',$param['team']);
        $team_id = $team_str[0];
        $team_name = $team_str[1];
        $salesman_arr = json_decode($param['salesman'], true);
        $SalesKpi = new SalesKpiModel();
        $team_info = $SalesKpi->getSalaryItem([
            'team_id'=>$team_id,
            'data_date'=>date('Y-m')
        ], 'id,business_type, region, dept, dept_id,is_delete');
        if(!empty($team_info)){
            if($team_info['is_delete']){
                $SalesKpi->_setSalaryData([
                    'id'=>$team_info['id']
                ],['is_delete'=>0]);
            }
            $init_data = [
                'data_date'=>date('Y-m'),
                'business_type'=>$team_info['business_type'],
                'region'=>$team_info['region'],
                'structure_type'=>'salesman',
                'dept'=>$team_info['dept'],
                'dept_id'=>$team_info['dept_id'],
                'team'=>$team_name,
                'team_id'=>$team_id,
            ];
            $Permission = new User();
            $data = [];

            foreach($salesman_arr as $item){
                //判断是否已存在
                $had = $SalesKpi->getSalaryItem([
                    'salesman_id'=>$item['value'],
                    'data_date'=>date('Y-m')
                ], 'id,is_delete');
                if(!empty($had)) {
                    if($had['is_delete']==1){
                        $SalesKpi->_setSalaryData([
                            'id'=>$had['id']
                        ],['is_delete'=>0]);
                    }
                    continue;
                }
                $_data = $init_data;
                $_data['salesman'] = $Permission->getUserName($item['value']);
                $_data['salesman_id'] = $item['value'];
                $data[] = $_data;
            }
            //插入数据
            if($num = (new SalesKpiModel())->insertSalaryDataMulti($data)){
                Logger::getInstance()->log('addTeamSalesKpi insert salesman:'.$num);
                return $num;
            }
        }else{
            $dept_str = explode(',',$param['dept']);
            $dept_id = $dept_str[0];
            $dept_name = $dept_str[1];
            $dept_info = $SalesKpi->getSalaryItem([
                'dept_id'=>$dept_id,
                'data_date'=>date('Y-m')
            ], 'id,business_type, region, dept, dept_id,is_delete');
            if(empty($dept_info)){
                $TeamStructure = new TeamStructureDomain();
                $team_info = $TeamStructure->getTeamInfo($team_id);
                $init_data = [
                    'data_date'=>date('Y-m'),
                    'business_type'=>$team_info['business_type'],
                    'region'=>$team_info['region'],
                    'structure_type'=>'dept',
                    'dept'=>$dept_name,
                    'dept_id'=>$dept_id,
                ];
                $dept_info = $init_data;
                //部门负责人
                $TeamModel = new TeamStructureModel();
                $relation_arr = $TeamModel->getDeptUid($dept_id);
                $data = [];
                foreach($relation_arr as $item){
                    $_data = $init_data;
                    $_data['salesman'] = $item['username'];
                    $_data['salesman_id'] = $item['uid'];
                    $data[] = $_data;
                }
                //插入数据
                if($num = (new SalesKpiModel())->insertSalaryDataMulti($data)){
                    Logger::getInstance()->log('addTeamSalesKpi insert dept:'.$num);
                }
            }else{
                if($dept_info['is_delete']){
                    $SalesKpi->_setSalaryData([
                        'id'=>$dept_info['id']
                    ],['is_delete'=>0]);
                }
            }
            //组织团队
            $init_data = [
                'data_date'=>date('Y-m'),
                'business_type'=>$dept_info['business_type'],
                'region'=>$dept_info['region'],
                'structure_type'=>'team',
                'dept'=>$dept_info['dept'],
                'dept_id'=>$dept_info['dept_id'],
                'team'=>$team_name,
                'team_id'=>$team_id,
            ];
            //团队负责人
            $TeamModel = new TeamStructureModel();
            $relation_arr = $TeamModel->getDeptUid($team_id);
            $data = [];
            foreach($relation_arr as $item){
                $_data = $init_data;
                $_data['salesman'] = $item['username'];
                $_data['salesman_id'] = $item['uid'];
                $data[] = $_data;
            }
            //插入数据
            if($num = (new SalesKpiModel())->insertSalaryDataMulti($data)){
                Logger::getInstance()->log('addTeamSalesKpi insert team:'.$num);
            }
            //组织顾问
            $user = new User();
            $data = [];
            foreach($salesman_arr as $item){
                $_data = $init_data;
                $_data['structure_type']='salesman';
                $_data['salesman'] = $user->getUserName($item['value']);
                $_data['salesman_id'] = $item['value'];
                $data[] = $_data;
            }
            //插入数据
            if($num = (new SalesKpiModel())->insertSalaryDataMulti($data)){
                Logger::getInstance()->log('addTeamSalesKpi insert salesman:'.$num);
                return $num;
            }
        }

    }

    public function delTeamSalesKpi($param)
    {
        $SalesKpi = new SalesKpiModel();
        if($param['type']=='group'){
            $id_field = 'team_id';
        }elseif($param['type']=='team'){
            $id_field = 'dept_id';
        }else{
            $id_field = 'salesman_id';
        }

        $num = $SalesKpi->_setSalaryData([
            'data_date'=>date('Y-m'),
            $id_field=>$param['id'],
        ], [
            'is_delete'=>1
        ]);
        return $num;
    }

    public function updateTeamSalesKpi($data)
    {
        $num = 0;
        if($data){
            $data = json_decode($data, true);
            if(is_array($data)){
                $SalesKpi = new SalesKpiModel();
                $TeamStructure = new TeamStructureModel();
                foreach($data as $item){
                    switch ($item['type']){
                        case 'per':
                            //查询组名与部门名
                            if($SalesKpi->_setSalaryData([
                                'data_date'=>date('Y-m'),
                                'salesman_id'=>$item['id']
                            ], [
                                'team_id'=>$item['pid'],
                                'team'=>$TeamStructure->getDeptName($item['pid']),
                                'dept_id'=>$item['tid'],
                                'dept'=>$TeamStructure->getDeptName($item['tid']),
                            ])) $num++;
                            break;
                        case 'group':
                            if($SalesKpi->_setSalaryData([
                                'data_date'=>date('Y-m'),
                                'team_id'=>$item['id']
                            ], [
                                'dept_id'=>$item['tid'],
                                'dept'=>$TeamStructure->getDeptName($item['tid']),
                            ])) $num++;
                            break;
                    }

                }
            }
        }
        return $num;
    }

    /**
     * 导出绩效
     * @param $uid
     * @param $params
     * @return bool|string
     */
    public function export($uid, $params)
    {
        $viewType = $params['view_type'];
        $levelInfo = [];
        $level = [];
        $teamTitle = ['日期' => 'string', '部门' => 'string', '团队' => 'string', '姓名' => 'string', '职级' => 'string',
            '实际分标人数	' => 'integer', '基本工资' => 'integer', '基本绩效工资' => '0.0', '团队业绩指标' => 'integer',
            '团队业绩权重' => '0.00%', '实际业绩' => '0.0', '业绩达标率' => '0.00%', '业绩指标绩效工资' => '0.00', '转化率指标' => '0.00%',
            '转化率权重' => '0.00%', '资源量' => 'integer', '有效订单' => 'integer', '转化率实际完成' => '0.00%', '转化率达标率' => '0.00%',
            '转化率绩效工资' => '0.00', '人均产能指标' => '0.0', '人均产能权重' => '0.00%', '团队实际人均产能' => '0.00', '团队人均产能达标率' => '0.00%',
            '人均产能绩效工资' => '0.00', '团队流失率指标' => '0.00%', '团队流失率权重' => '0.00%', '月初人数' => '0', '新进人数' => '0',
            '离职人数' => '0', '团队流失率' => '0.00%', '团队流失率达标率' => '0.00%', '团队流失绩效工资' => '0.00', '团队通时指标' => '0.0',
            '团队通时权重' => '0.00%', '有效通时' => '0.0', '分标天数' => 'integer', '工作天数' => '0.0',
            '累计日均通时' => '0.00', '日均通时达标率' => '0.00%', '日均通时绩效工资' => '0.00', '综合达标率' => '0.00%', '绩效扣减' => '0.0',
            '实际绩效考核工资' => '0.00', '绩效权重合计' => '0.00%', '达标奖' => '0.00', '月工资总和' => '0.00'];
        if ($viewType === 'cc_calc' || $viewType === 'cc_kpi_calc') {
            $key = $viewType === 'cc_calc' ? '顾问工资' : '顾问绩效';
            $levelInfo = (new SalesLevelModel())->getList(1);
            $title = ['日期' => 'string', '部门' => 'string', '团队' => 'string', '姓名' => 'string', '职级' => 'string', '上线日期' => 'string',
                '离职日期' => 'string', '是否分标' => 'string', '分标天数' => 'integer', '基本工资' => 'integer', '基本绩效工资' => '0.0', '业绩指标' => 'integer',
                '业绩权重' => '0.00%', '实际业绩' => '0.0', '业绩达标率' => '0.00%', '业绩指标绩效工资' => '0.00', '转化率指标' => '0.00%',
                '转化率权重' => '0.00%', '资源量' => 'integer', '有效订单' => 'integer', '转化率实际完成' => '0.00%', '转化率达标率' => '0.00%',
                '转化率绩效工资' => '0.00', '日均通时指标' => '0.0', '日均通时权重' => '0.00%', '工作天数' => '0.0', '有效通时' => '0.0',
                '累计日均通时' => '0.00', '日均通时达标率' => '0.00%', '日均通时绩效工资' => '0.00', '综合达标率' => '0.00%', '绩效扣减' => '0.0',
                '实际绩效考核工资' => '0.00', '绩效权重合计' => '0.00%', '达标奖' => '0.00', '月工资总和' => '0.00'];
            if ($viewType === 'cc_kpi_calc') {
                unset($title['基本工资']);
                unset($title['基本绩效工资']);
                unset($title['业绩指标绩效工资']);
                unset($title['转化率绩效工资']);
                unset($title['日均通时绩效工资']);
                unset($title['绩效扣减']);
                unset($title['实际绩效考核工资']);
                unset($title['达标奖']);
                unset($title['月工资总和']);
            }
        } elseif ($viewType === 'team_calc' || $viewType === 'team_kpi_calc') {
            $key = $viewType === 'team_calc' ? '主管工资' : '主管绩效';
            $levelInfo = (new SalesLevelModel())->getList(2);
            $title = $teamTitle;
            if ($viewType === 'team_kpi_calc') {
                unset($title['基本工资']);
                unset($title['基本绩效工资']);
                unset($title['业绩指标绩效工资']);
                unset($title['转化率绩效工资']);
                unset($title['人均产能绩效工资']);
                unset($title['日均通时绩效工资']);
                unset($title['团队流失绩效工资']);
                unset($title['绩效扣减']);
                unset($title['实际绩效考核工资']);
                unset($title['达标奖']);
                unset($title['月工资总和']);
            }
        } elseif ($viewType === 'dept_calc' || $viewType === 'dept_kpi_calc') {
            $key = $viewType === 'dept_calc' ? '经理工资' : '经理绩效';
            $levelInfo = (new SalesLevelModel())->getList(3);
            unset($teamTitle['团队']);
            $title = $teamTitle;
            if ($viewType === 'dept_kpi_calc') {
                unset($title['基本工资']);
                unset($title['基本绩效工资']);
                unset($title['业绩指标绩效工资']);
                unset($title['转化率绩效工资']);
                unset($title['人均产能绩效工资']);
                unset($title['日均通时绩效工资']);
                unset($title['团队流失绩效工资']);
                unset($title['绩效扣减']);
                unset($title['实际绩效考核工资']);
                unset($title['达标奖']);
                unset($title['月工资总和']);
            }
        }
        foreach ($levelInfo as $item) {
            $level[$item['id']] = $item['sales_level'];
        }

        $filename = Export::export($key, $uid, $title, function ($page, $limit) use ($viewType, $params, $level) {
            $params['page'] = $page + 1;
            $data = $this->getSalesTeam($params);
            foreach ($data as $i => $item) {
                $data[$i] = $this->formatExport($viewType, $item);
                $data[$i]['level'] = isset($level[$item['level']]) ? $level[$item['level']] : '';
                unset($data[$i]['id']);
            }
            return $data;
        }, 20);
        return $filename;
    }

    public function formatExport($type, $item)
    {
        $item['perform_rate'] = $item['perform_rate'] / 100;
        $item['trans_rate_target'] = $item['trans_rate_target'] / 100;
        $item['trans_rate_weight'] = $item['trans_rate_weight'] / 100;
        $item['trans_rate_actual'] = $item['trans_rate_actual'] / 100;
        $item['trans_rate_rate'] = $item['trans_rate_rate'] / 100;
        $item['perform_weight'] = $item['perform_weight'] / 100;
        $item['call_sec_weight'] = $item['call_sec_weight'] / 100;
        $item['call_sec_avg_rate'] = $item['call_sec_avg_rate'] / 100;
        $item['composite_rate'] = $item['composite_rate'] / 100;
        $item['salary_kpi_weight_total'] = $item['salary_kpi_weight_total'] / 100;
        if ($type === 'cc_calc' || $type === 'cc_kpi_calc') {
            $item['is_share'] = $item['is_share'] === '1' ? '是' : '否';
        } elseif (in_array($type, ['dept_calc', 'team_calc', 'dept_kpi_calc', 'team_kpi_calc'])) {
            $item['perform_actual_team_rate'] = $item['perform_actual_team_rate'] / 100;
            $item['perform_weight_team'] = $item['perform_weight_team'] / 100;
            $item['resign_target'] = $item['resign_target'] / 100;
            $item['resign_weight'] = $item['resign_weight'] / 100;
            $item['resign_rate'] = $item['resign_rate'] / 100;
            $item['resign_rate_rate'] = $item['resign_rate_rate'] / 100;
        }
        return $item;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getAudit($id)
    {
        $model = new SalesKpiModel();
        $info = $model->getAuditInfo($model->kpi_salary_table, $id);
        return $info;
    }
}
