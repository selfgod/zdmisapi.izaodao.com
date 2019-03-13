<?php
/**
 * Created by PhpStorm.
 * User: songwenyao
 * Date: 2019/1/23
 * Time: 3:04 PM
 */
namespace App\Model\Market\Source;

use Base\BaseModel;
use Base\Db;
use EasySwoole\Core\Component\Logger;

class CallInModel extends BaseModel
{
    protected $callIn_table = 'zd_zixun_callin';
    protected $callIn_type_table = 'zd_source_callin_type';
    protected $callIn_second_type_table = 'zd_source_callin_second_type';
    protected $callIn_cc_table = 'zd_leyu_cc';
    protected $limit_setting = 'zd_leyu_limit_setting';

    function updateCallIn($id, $data)
    {
        $this->sWhereClean()->setSqlWhereAnd(['id'=>$id]);
        return $this->updateData($this->callIn_table, $data);
    }

    function addCallInData($lang_type, $mobile='', $qq='', $wechat='', $uid=0, $memo, $getinfo, $tag, $assign_date='', $zid='')
    {
        $data = [
            'lang_type'=>$lang_type,
            'mobile'=>$mobile,
            'qq'=>$qq,
            'wechat'=>$wechat,
            'uid'=>$uid,
            'memo'=>$memo,
            'getinfo'=>$getinfo,
            'tag'=>$tag,
            'name'=>'',
            'email'=>'',
            'stuff'=>'',
            'ip'=>'',
            'shenfen'=>'',
            'yixiang'=>'',
            'ocupdate'=>'0000-00-00',
            'isbm'=>0,
            'aim'=>0,
            'storeid'=>0,
            'adddate'=>date('Y-m-d H:i:s')
        ];
        if($assign_date) $data['assign_date'] = $assign_date;
        if($zid) $data['zid'] = $zid;
        if($tag){
            $_tag=explode('-',$tag);
            $data['tag0'] = isset($_tag[0])?$_tag[0]:'';
            $data['tag1'] = isset($_tag[1])?$_tag[1]:'';
        }else{
            $data['tag0'] = $data['tag1']= '';
        }
        return $this->insertTable($this->callIn_table, $data, 'zd_class');
    }

    function setCallInCCInc($id)
    {
        return Db::master('zd_class')->query("update {$this->callIn_cc_table} set num=num+1 where id={$id}");
    }

    /**
     * 网资值班顾问
     * @param $business_type
     * @param $category
     * @return mixed
     * @throws \Exception
     */
    function getCallInCC($business_type, $category)
    {
        $list = [];
        $this->sWhereClean()->setSqlWhereAnd([
            'lc.state'=> 1,
            'lc.team' => '课程顾问',
            'lc.data_type' => 'leyu',
            'lc.lang_type' => $business_type,
            'lc.callin_type' => $category,
        ]);
        $today = date('Y-m-d');
        $result = $this->selectData($this->callIn_cc_table.' as lc', 'lc.*,m.uid as salesman_uid,lls.limit_num')
            ->innerJoin($this->limit_setting.' as lls',
                'lc.lang_type = lls.business_type AND lc.callin_type = lls.callin_type AND lc.`order` = lls.`order`')
            ->innerJoin('jh_common_member m', 'm.username=lc.cc')
            ->where("lc.set_date='{$today}'")
            ->orderByASC(['lc.order','lc.num'])
            ->query();

        if(!empty($result)){
            foreach ($result as $key=>$value){
                if($value['num']<$value['limit_num'] || empty($value['limit_num'])){
                    $list[$key] = $value;
                }
            }
        }
        return reset($list);
    }

    function getCallInData($condition, $field='*')
    {
        $this->sWhereClean()->setSqlWhereAnd($condition);
        return $this->selectData($this->callIn_table, $field)->query();
    }

    /**
     * 网资类型
     * @param $field
     * @param $where
     * @return array
     * @throws \Exception
     */
    function getCallInList($field, $where)
    {
        $result_list= [];
        $this->sWhereClean()->setSqlWhereAnd($where);
        $result = $this->selectData($this->callIn_type_table.' as sat', $field)
            ->leftJoin($this->callIn_second_type_table.' as scst', 'sat.id = scst.first_id')
            ->query();

        if(!empty($result)){
            foreach ($result as $k=>$v){
                $result_list[$v['id']]['id'] = $v['id'];
                if(isset($v['business_type'])) $result_list[$v['id']]['business_type'] = $v['business_type'];
                $result_list[$v['id']]['first_type'] = $v['first_type'];
                if(!empty($v['second_type'])){
                    $result_list[$v['id']]['second_type'][$v['sid']] = $v['second_type'];
                }else{
                    $result_list[$v['id']]['second_type'] = [];
                }
            }
        }
        return $result_list?$result_list:[];
    }
}