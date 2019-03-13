<?php
/**
 * Created by PhpStorm.
 * User: songwenyao
 * Date: 2018/11/16
 * Time: 上午9:14
 */

namespace App\Domain\Sales\Setting;

use App\Domain\Sales\SalesmanDomain;
use App\Model\Common\User;
use App\Model\Sales\Setting\TeamStructureModel;
use Base\BaseDomain;
use Base\Helper\ArrayHelper;

class TeamStructureDomain extends BaseDomain
{

    private $team_year;
    private $team_month;

    static $type_region=array(
        'dalian'=>'大连',
        'wuhan'=>'武汉'
    );

    function setDate($date){
        $time = strtotime($date);
        $this->team_year = date('Y', $time);
        $this->team_month = date('m', $time);
    }

    function getMyTeam($cc, $all=true){
        $Model = new TeamStructureModel();
        if(is_numeric($cc)){
            $w = array('uid'=>$cc, 'cat'=>'ccteam_new');
        }else{
            $w = array('team'=>$cc, 'cat'=>'ccteam_new');
        }
        $year = $this->team_year?$this->team_year:date('Y');
        $month = $this->team_month?$this->team_month:date('m');
        $w['yearmonth'] = $year.'-'.$month;
        $res = $Model->queryTeam('uid,team,parent_id,top_id,dept', $w);
        if(!empty($res)){
            $team = array();
            if($all){
                $team['dept'] = $res['dept'];
                $team['cc'] = $res['team'];
                $team['cc_uid'] = $res['uid'];
                if($res['parent_id']==0){
                    $team['group']=$cc;
                    $team['group_uid'] = $res['uid'];
                    $dept_uid = $Model->getDeptUid($res['uid']);
                    $team['group_dept'] = ArrayHelper::array_key_value('uid','username', $dept_uid);
                }else{
                    //print_r(array('uid'=>$res['parent_id'],'cat'=>'ccteam_new','yearmonth'=>$w['yearmonth']));
                    $group_info = $Model->queryTeam('team,remark', array('uid'=>$res['parent_id'],'cat'=>'ccteam_new','yearmonth'=>$w['yearmonth']));
                    $team['group'] = $group_info['team'];
                    $team['group_dept_str'] = $group_info['remark'];
                    $team['group_uid'] = $res['parent_id'];
                    $dept_uid = $Model->getDeptUid($res['parent_id']);
                    $team['group_dept'] = ArrayHelper::array_key_value('uid','username', $dept_uid);
                }
            }
            if($res['top_id']==0){
                $team['team']=$cc;
                $team['team_uid']=$res['uid'];
                $dept_uid = $Model->getDeptUid($res['parent_id']);
                $team['team_dept'] = ArrayHelper::array_key_value('uid','username', $dept_uid);
            }else{
                $team_info = $Model->queryTeam('team,remark', array('uid'=>$res['top_id'],'cat'=>'ccteam_new','yearmonth'=>$w['yearmonth']));
                $team['team'] =$team_info['team'];
                $team['team_dept_str'] =$team_info['remark'];
                $team['team_uid'] = $res['top_id'];
                $dept_uid = $Model->getDeptUid($res['top_id']);
                $team['team_dept'] = ArrayHelper::array_key_value('uid','username', $dept_uid);
            }
            return $all?$team:$team['team'];
        }elseif(is_string($cc)){ //查询remark
            $w = array('remark'=>$cc, 'cat'=>'ccteam_new');
            $w['yearmonth'] = $year.'-'.$month;
            $res = $Model->queryTeam('uid,team,remark,parent_id,top_id,dept', $w);
            if(!empty($res)){
                $team = array();
                if($all){
                    $team['dept'] = $res['dept'];
                    $team['cc'] = $res['remark'];
                }
                if($res['dept']=='group'){
                    $team['group'] = $res['team'];
                    $team['group_dept_str'] = $res['remark'];
                    $team['group_uid'] = $res['uid'];
                    $dept_uid = $Model->getDeptUid($res['uid']);
                    $team['group_dept'] = ArrayHelper::array_key_value('uid','username', $dept_uid);
                    $team_info = $Model->queryTeam('team,remark', array('uid'=>$res['top_id'],'cat'=>'ccteam_new','yearmonth'=>$w['yearmonth']));
                    $team['team'] =$team_info['team'];
                    $team['team_dept_str'] =$team_info['remark'];
                    $team['team_uid'] = $res['top_id'];
                    $dept_uid = $Model->getDeptUid($res['top_id']);
                    $team['team_dept'] = ArrayHelper::array_key_value('uid','username', $dept_uid);
                }else{
                    $team['team'] = $res['team'];
                    $team['team_dept_str'] = $res['remark'];
                    $team['team_uid'] = $res['uid'];
                    $dept_uid = $Model->getDeptUid($res['uid']);
                    $team['team_dept'] = ArrayHelper::array_key_value('uid','username', $dept_uid);
                }
                return $all?$team:$team['team'];
            }
        }
        return [];
    }

    public function getRegionForBusinessType($business_type)
    {
        $region_arr = (new TeamStructureModel())->queryDepartment([
            'type'=>['in'=>$this->getSalesmanTypeForBusinessType($business_type)],
        ], 'region', ['region']);
        $result = [];
        if(!empty($region_arr)){
            foreach($region_arr as $item){
                $result[$item['region']] = self::$type_region[$item['region']];
            }
        }
        return $result;
    }

    public function getSalesmanTypeForBusinessType($business_type)
    {

        return isset(TeamStructureModel::$business_type_sales[$business_type])?
            TeamStructureModel::$business_type_sales[$business_type]:[];
    }
    public function getBusinessTypeForSalesmanType($salesman_type)
    {
        foreach(TeamStructureModel::$business_type_sales as $type=>$item){
            if(in_array($salesman_type, $item)) return $type;
        }
        return null;
    }

    function getBusinessTypeForSalesman($salesman_uid)
    {
        list($dept_type,) = (new SalesmanDomain())->getSalesmanType($salesman_uid);
        return $this->getBusinessTypeForSalesmanType($dept_type);
    }

    public function getDept($param)
    {
        $year_month = $param['year_month'];
        $business_type = isset($param['business_type'])?$param['business_type']:'';
        $region = isset($param['region'])?$param['region']:'';
        return (new TeamStructureModel())->getDeptTeam($year_month, $business_type, $region);
    }

    function getTeam($param)
    {
        $year_month = $param['year_month'];
        $parent_id = isset($param['parent_id'])?$param['parent_id']:0;
        $Model = new TeamStructureModel();
        if($parent_id){
            $result = $Model->getDeptTeam($year_month, '', '', 'group', $parent_id);
        }else{
            $business_type = isset($param['business_type'])?$param['business_type']:'';
            $region = isset($param['region'])?$param['region']:'';
            $result = $Model->getDeptTeam($year_month, $business_type, $region, 'group');
        }


        return $result;
    }

    function getSalesman($param)
    {
        $year_month = $param['year_month'];
        $parent_id = isset($param['parent_id'])?$param['parent_id']:0;
        $top_id = isset($param['top_id'])?$param['top_id']:0;
        $Model = new TeamStructureModel();
        if($parent_id){
            $result = $Model->getSalesman($year_month, $parent_id);
        }elseif($top_id){
            $result = $Model->getSalesman($year_month, 0, $top_id);
        }else{
            $result = $Model->getSalesman($year_month);
        }
        return $result;
    }

    /**
     * @param $team_id
     * @return array [business_type, region]
     */
    function getTeamInfo($team_id)
    {
        $info = (new TeamStructureModel())->queryDepartment([
            'id'=>$team_id
        ], 'region,name,type');
        if(!empty($info)){
            $info = $info[0];
            $info['business_type'] = $this->getBusinessTypeForSalesmanType($info['type']);
        }
        return $info;
    }

    /**
     * 增加组织架构
     * @param $id
     * @param $uid
     * @param $code
     * @param $parentId
     * @param $parentCode
     * @param $name
     * @param $region
     * @param $leaf
     * @return int
     */
    public function addStructure($id, $uid, $code, $parentId, $parentCode, $name, $region, $leaf)
    {
        return (new TeamStructureModel())->addStructure($id, $uid, $code, $parentId, $parentCode, $name, $region, $leaf);
    }

    /**
     * 更新组织架构
     * @param $id
     * @param $uid
     * @param $name
     * @param $region
     * @param $leaf
     * @return bool
     */
    public function updateStructure($id, $uid, $name, $region, $leaf)
    {
        return (new TeamStructureModel())->updateStructure($id, $uid, $name, $region, $leaf);
    }

    /**
     * 删除组织架构
     * @param $id
     * @param $uid
     * @return bool
     */
    public function delStructure($id, $uid)
    {
        return (new TeamStructureModel())->delStructure($id, $uid);
    }

}
