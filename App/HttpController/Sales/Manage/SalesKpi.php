<?php
/**
 * Created by PhpStorm.
 * User: songwenyao
 * Date: 2018/11/29
 * Time: 4:40 PM
 */
namespace App\HttpController\Sales\Manage;

use App\Domain\Sales\Manage\Team\SalesKpiDomain;
use Base\BaseController;
use Base\PassportApi;

class SalesKpi extends PassportApi//PassportApi
{
    protected function getRules()
    {
        $rules = parent::getRules();
        return array_merge($rules, [
            'setStandardRate' => [
                'type'=>['type'=>'string','require'=>true, 'desc'=>'类型,reward,resign'],
                'business_type' => ['type' => 'string', 'require' => true, 'desc' => '业务类型'],
                'region' => ['type' => 'string', 'require' => true, 'desc' => '区域',],
                'sales_type'=>['type'=>'int', 'require'=>true, 'desc'=>'人员类别'],
                'min_rate'=>['type'=>'int', 'desc'=>'达成率小值'],
                'max_rate'=>['type'=>'int', 'desc'=>'达成率大值'],
                'bonus'=>['type'=>'float', 'desc'=>'系数'],
                //'operator'=>['type'=>'int', 'require'=>true, 'desc'=>'操作人UID'],
                'id'=>['type'=>'int', 'desc'=>'修改用id'],
            ],
            'getStandardRate' => [
                'type' => ['type' => 'string', 'require' => true, 'desc' => '类型'],
                'business_type' => ['type' => 'string', 'require' => true, 'desc' => '业务类型'],
                'region' => ['type' => 'string', 'require' => true, 'desc' => '区域',],
                'sales_type' => ['type' => 'string', 'require' => true, 'desc' => '类型',],
            ],
            'delStandardRateItem' => [
                'id' => ['type' => 'int', 'require' => true, 'desc' => 'id'],
            ],
            'getSalesTeam'=>[
                'business_type' => ['type' => 'string', 'require' => true, 'desc' => '业务类型'],
                'region' => ['type' => 'string', 'require' => true, 'desc' => '区域'],
                'year_month'=>['type'=>'string', 'require'=>true, 'desc'=>'年月，例如2018-11'],
                'view_type'=>['type'=>'string', 'desc'=>'列表类型'],
                'dept'=>['type'=>'int', 'desc'=>'部门id'],
                'team'=>['type'=>'int', 'desc'=>'团队id'],
                'salesman'=>['type'=>'int', 'desc'=>'顾问id'],
                'page'=>['type'=>'int', 'require'=>true, 'default'=>1, 'desc'=>'页数'],
                'count'=>['type'=>'int', 'desc'=>'是否获取总数']
            ],
            'getSalesKpi'=>[
                'salesman'=>['int', 'desc'=>'顾问id'],
                'structure_type'=>['string', 'desc'=>'类型'],
                'year_month'=>['type'=>'string', 'require'=>true, 'desc'=>'年月，例如2018-11'],
                'ignore_auth'=>true,
            ],
            'getFields' => [
                'type' => ['type' => 'string', 'require' => true, 'desc' => '类型'],
            ],
            'initSalesTeam'=>[
                'business_type' => ['type' => 'string', 'require' => true, 'desc' => '业务类型'],
                'region' => ['type' => 'string', 'require' => true, 'desc' => '区域'],
                'year_month'=>['type'=>'string', 'require'=>true, 'desc'=>'年月，例如2018-11'],
                'ignore_auth'=>true,
                'ignore_sign'=>true,
            ],
            'audit' => [
                'id' => ['type' => 'int', 'require' => TRUE]
            ],
            'exportSalary' => [
                'business_type' => ['type' => 'string', 'require' => true, 'desc' => '业务类型'],
                'region' => ['type' => 'string', 'require' => true, 'desc' => '区域'],
                'year_month' => ['type' => 'string', 'require' => true, 'desc' => '年月，例如2018-11'],
                'view_type' => ['type' => 'enum', 'desc' => '列表类型',
                    'range' => ['cc_calc', 'team_calc', 'dept_calc', 'cc_kpi_calc', 'team_kpi_calc', 'dept_kpi_calc']],
                'dept' => ['type' => 'int', 'desc' => '部门id'],
                'team' => ['type' => 'int', 'desc' => '团队id'],
                'salesman' => ['type' => 'int', 'desc' => '顾问id']
            ],
            'setSalesTeam'=>[
                'id'=> ['type' => 'int', 'require' => TRUE],
                'view_type'=>['type'=>'string'],
                'level'=>['type'=>'int'],
                'online_time'=>['type'=>'date'],
                'resign_time'=>['type'=>'date'],
                'is_share'=>['type'=>'int'],
                'share_day'=>['type'=>'int'],
                'salary_base'=>['type'=>'int'],
                'salary_kpi_base'=>['type'=>'float'],
                'perform_target'=>['type'=>'int'],
                'perform_weight'=>['type'=>'float'],
                'perform_weight_team'=>['type'=>'float'],
                'trans_rate_target'=>['type'=>'float'],
                'trans_rate_weight'=>['type'=>'float'],
                'call_sec_target'=>['type'=>'float'],
                'call_sec_weight'=>['type'=>'float'],
                'holiday_day'=>['type'=>'float'],
                'salary_kpi_minus'=>['type'=>'float'],
                'resign_target'=>['type'=>'float'],
                'resign_weight'=>['type'=>'float'],
                'sales_num'=>['type'=>'int'],
                'sales_num_new'=>['type'=>'int'],
                'sales_num_resign'=>['type'=>'int'],
            ],
            'updateTeamSalesKpi'=>[
                'date'=>['type'=>'string'],
                'data'=>['type'=>'string']
            ],
            'delTeamSalesKpi'=>[
                'type'=>['type'=>'string','require'=>true],
                'id'=>['type'=>'int','require'=>true]
            ],
            'addTeamSalesKpi'=>[
                'dept'=>['type'=>'string'],
                'region'=>['type'=>'string', 'require'=>true],
                'salesman'=>['type'=>'string', 'require'=>true],
                'team'=>['type'=>'string', 'require'=>true]
            ],
        ]);
    }

    public function addTeamSalesKpi()
    {
        $res = (new SalesKpiDomain())->addTeamSalesKpi($this->params);
        $this->returnJson($res);
    }
    public function updateTeamSalesKpi()
    {
        $res = 0;
        if($this->params['date']==date('Y-m')){
            $res = (new SalesKpiDomain())->updateTeamSalesKpi($this->params['data']);
        }
        $this->returnJson($res);
    }

    public function delTeamSalesKpi()
    {
        $res = (new SalesKpiDomain())->delTeamSalesKpi($this->params);
        $this->returnJson($res);
    }

    public function setStandardRate()
    {
        $res = (new SalesKpiDomain())->setStandardRate($this->params);
        $this->returnJson($res);
    }

    public function getStandardRate()
    {
        $res = (new SalesKpiDomain())->getStandardRate($this->params);
        $this->returnJson($res);
    }

    public function delStandardRateItem()
    {
        $res = (new SalesKpiDomain())->delStandardRateItem($this->params);
        $this->returnJson($res);
    }

    public function getSalesTeam()
    {
        $res = (new SalesKpiDomain())->getSalesTeam($this->params);
        $this->returnJson($res);
    }

    public function exportSalary()
    {
        $name = (new SalesKpiDomain())->export($this->params['uid'], $this->params);
        $this->returnJson($name);
    }

    public function getSalesKpi()
    {
        if(empty($this->params['salesman'])){
            if(!isset($this->params['uid'])){
                $this->checkUser();
            }
            $this->params['salesman'] = $this->params['uid'];
        }
        $res = (new SalesKpiDomain())->getMyKpi($this->params['salesman'], $this->params['year_month'], $this->params['structure_type']);

        $this->returnJson($res, '', empty($res) ? 404 : 200);
    }

    public function setSalesTeam()
    {
        list($res, $data) = (new SalesKpiDomain())->setSalesTeam($this->params);
        $this->returnJson(['state'=>$res, 'data'=>$data]);
    }

    public function getFields()
    {
        $res = (new SalesKpiDomain())->getFields($this->params['type']);
        $this->returnJson($res);
    }

    public function initSalesTeam()
    {
        $res = (new SalesKpiDomain())->initSalesTeam($this->params['year_month'],
            $this->params['business_type'], $this->params['region']);
        $this->returnJson($res);
    }

    /**
     * 修改日志
     */
    public function audit()
    {
        $info = (new SalesKpiDomain())->getAudit($this->params['id']);
        $this->returnJson($info);
    }
}