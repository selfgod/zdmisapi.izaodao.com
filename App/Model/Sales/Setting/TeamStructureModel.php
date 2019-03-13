<?php
/**
 * Created by PhpStorm.
 * User: songwenyao
 * Date: 2018/11/16
 * Time: 上午9:10
 */

namespace App\Model\Sales\Setting;

use App\Model\Sales\Team\SalesmanModel;
use Base\BaseModel;
use Base\Db;

class TeamStructureModel extends BaseModel
{
    protected $team_table = 'jh_common_setting_aim';
    protected $department_table = 'zd_department';
    protected $dept_relation_table = 'zd_dept_relation';

    static $business_type_sales = [
        'jp'=>[
            SalesmanModel::TYPE_CC,
        ],
        'de'=>[SalesmanModel::TYPE_DECC,],
        'kr'=>[SalesmanModel::TYPE_KRCC,],
        'os'=>[SalesmanModel::TYPE_OSC],
        'up'=>[SalesmanModel::TYPE_RC],
        'bp'=>[SalesmanModel::TYPE_BPCC]
    ];

    function queryTeam($field, $condition)
    {
        $this->sWhere = '1=1';
        $this->sBindValues = [];
        $this->setSqlWhereAnd($condition);
        return Db::slave('zd_class')->select($field)
            ->from($this->team_table)
            ->where($this->sWhere)
            ->bindValues($this->sBindValues)
            ->orderByDESC(['dateline'])
            ->row();
    }

    /**
     * 根据部门id获取负责人
     * @param $dept_id
     * @return array|bool|null
     */
    function getDeptUid($dept_id){
        return Db::slave('zd_class')->select('*')
            ->from($this->dept_relation_table)
            ->where('dept_id=:dept_id')
            ->bindValues(['dept_id'=>$dept_id])
            ->query();
    }

    function getDeptRow($name_or_id)
    {
        if(is_numeric($name_or_id)){
            $condition = ['id'=>$name_or_id];
        }else{
            $condition = ['name'=>$name_or_id];
        }
        $this->sWhere = '1=1';
        $this->sBindValues = [];
        $this->setSqlWhereAnd($condition);
        return Db::slave('zd_class')->select('*')
            ->from($this->department_table)
            ->where($this->sWhere)
            ->bindValues($this->sBindValues)
            ->row();
    }

    function queryDepartment($condition, $field='*', $group=[])
    {
        $this->sWhere = '1=1';
        $this->sBindValues = [];
        $this->setSqlWhereAnd($condition);
        $res = Db::slave('zd_class')->select($field)
            ->from($this->department_table)
            ->where($this->sWhere)
            ->bindValues($this->sBindValues)
            ->groupBy($group)
            ->query();
        return $res;
    }

    function getDeptName($id)
    {
        $res = $this->queryDepartment([
            'id'=>$id
        ],'name');
        if(!empty($res)){
            return $res[0]['name'];
        }
        return '';
    }

    function getSalesman($year_month, $parent_id=0, $top_id=0)
    {
        $this->sWhere = "t1.yearmonth=:year_month and t1.cat='ccteam_new' and t1.dept='per' ";
        $this->sBindValues = [
            'year_month'=>$year_month
        ];
        if($parent_id){
            $this->setSqlWhereAnd(['t1.parent_id'=>$parent_id]);
        }
        if($top_id){
            $this->setSqlWhereAnd(['t1.top_id'=>$top_id]);
        }
        $res = Db::slave('zd_class')->select('t1.uid as id, t1.team as name')
            ->from($this->team_table.' as t1')
            ->where($this->sWhere)
            ->bindValues($this->sBindValues)
            ->query();
        return $res;
    }

    function getDeptTeam($year_month, $business_type='', $region='', $level='team', $parent_id=0)
    {
        $this->sWhere = "t1.yearmonth=:year_month and t1.cat='ccteam_new' and t1.dept='{$level}' and t2.level='{$level}'";
        $this->sBindValues = [
            'year_month'=>$year_month
        ];

        if($business_type) {
            $this->setSqlWhereAnd(['type'=>['in'=> self::$business_type_sales[$business_type]]]);
        }
        if($region){
            $this->setSqlWhereAnd(['region'=>$region]);
        }
        if($parent_id){
            $this->setSqlWhereAnd(['t1.parent_id'=>$parent_id]);
        }
        $res = Db::slave('zd_class')->select('t2.id, t2.name')
            ->from($this->team_table.' as t1')
            ->leftJoin($this->department_table.' as t2', 'on t1.uid = t2.id')
            ->where($this->sWhere)
            ->bindValues($this->sBindValues)
            ->query();
        return $res;
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
        try {
            Db::master('zd_class')->insert('sys_zdmis_sale_department')->cols([
                'id' => $id,
                'code' => $code,
                'parent_id' => $parentId,
                'parent_code' => $parentCode,
                'name' => $name,
                'region' => $region,
                'leaf' => $leaf,
                'create_time' => date('Y-m-d H:i:s'),
                'create_user' => $uid
            ])->query();
        } catch (\Exception $e) {
            return 0;
        }
        return $id;
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
        $ret = Db::master('zd_class')->update('sys_zdmis_sale_department')
            ->cols([
                'name' => $name,
                'region' => $region,
                'leaf' => $leaf,
                'modify_time' => date('Y-m-d H:i:s'),
                'modify_user' => $uid
            ])
            ->where('id = :id and is_del=0')
            ->bindValue('id', $id)
            ->query();
        return $ret > 0;
    }

    /**
     * 删除组织架构
     * @param $id
     * @param $uid
     * @return bool
     */
    public function delStructure($id, $uid)
    {
        $ret = Db::master('zd_class')->update('sys_zdmis_sale_department')
            ->cols([
                'is_del' => 1,
                'modify_time' => date('Y-m-d H:i:s'),
                'modify_user' => $uid
            ])
            ->where('id = :id and is_del=0')
            ->bindValue('id', $id)
            ->query();
        return $ret > 0;
    }
}