<?php
namespace App\Model\Market\Source;

use Base\BaseModel;
use Base\Db;
use function GuzzleHttp\Psr7\str;

class SourceModel extends BaseModel
{
    public $simple;
    public $crm_table = 'zd_crm';
    public $source_table = 'zd_crm_source_store';
    public $source_across = 'zd_crm_source_store_across';
    public $into_set_table = 'zd_channel_into_set';
    public $customer_follow = 'zd_customer_follow';
    public $customer_table = 'zd_customer';
    public $custom_action = 'zd_customer_action';
    public $zixun_table = 'zd_zixun';
    public $zixun_callin = 'zd_zixun_callin';
    public $channel_action = 'zd_channel_action';
    public $channel_action_tag = 'zd_channel_action_tag';
    public $channel_normal = 'zd_channel_normal_tag';
    public $channel_level = 'zd_channel_level';
    public $level_one = 'zd_source_level_one';
    public $fine_class_index = 'zd_fine_class_index';
    public $channel_action_remove = 'zd_channel_action_remove';
    public $source_black_list = 'zd_source_black_list';

    static $map_lang_type_source = [
        'jp'=>'zd_crm_source_store',
        'up'=>'zd_crm_source_store',
        'kr'=>'zd_crm_source_store_kr',
        'de'=>'zd_crm_source_store_de',
        'bp'=>'zd_crm_source_store_bp',
        'sp'=>'zd_crm_source_store_sp',
        'fr'=>'zd_crm_source_store_fr',
    ];

    public function __construct()
    {
        $this->simple = new SimpleModel();
    }

    /**
     * 获取入库配置表数据
     * @param $field
     * @param $where
     * @param string $aim_field
     * @return mixed
     */
    public function getChannelIntoSetData($field, $where,$aim_field=''){
        return $this->simple->simpleSelect($this->into_set_table,$field,$where,$aim_field);
    }

    /**
     * 获取一类v2数据
     * @param $field
     * @param $where
     * @param string $aim_field
     * @return mixed
     */
    public function getLevelOneData($field, $where, $aim_field=''){
        return $this->simple->simpleSelect($this->level_one,$field,$where,$aim_field);
    }

    /**
     * 获取渠道配置
     * @param $field
     * @param $where
     * @param string $aim_field
     * @return mixed
     */
    public function getChannelLevelData($field, $where, $aim_field=''){
        return $this->simple->simpleSelect($this->channel_level,$field,$where,$aim_field);
    }

    /**
     * 获取精品课数据数量
     * @param $mobile
     * @return mixed
     */
    public function getFineClassNum($mobile){
        $query = "select count(*) as c from ".$this->fine_class_index." WHERE tel='{$mobile}' and class_id>0";
        return Db::slave('zd_class')->query($query);
    }

    /**
     * 获取咨询数据
     * @param $field
     * @param $where
     * @param string $aim_field
     * @param bool $single
     * @param array $order
     * @return mixed
     */
    public function getZiXunData($field, $where, $aim_field='',$single=false,$order=['id','asc']){
        return $this->simple->simpleSelect($this->zixun_table,$field,$where,$aim_field,$single,$order);
    }

    /**
     * 获取咨询Callin数据
     * @param $field
     * @param $where
     * @param string $aim_field
     * @return mixed
     */
    public function getZiXunCallinData($field, $where, $aim_field=''){
        return $this->simple->simpleSelect($this->zixun_callin,$field,$where,$aim_field);
    }

    /**
     * 获取客户关系数据
     * @param $field
     * @param $where
     * @param string $aim_field
     * @param bool $single
     * @param array $order
     * @return mixed|null
     */
    public function getCustomData($field, $where, $aim_field='',$single=false,$order=['cid','desc']){
        return $this->simple->simpleSelect($this->customer_table,$field,$where,$aim_field,$single,$order);
    }

    /**
     * 获取用户跟踪数据
     * @param $field
     * @param $where
     * @param string $aim_field
     * @param bool $single
     * @return mixed
     */
    public function getCustomFollowData($field, $where, $aim_field='',$single=false){
        return $this->simple->simpleSelect($this->customer_follow,$field,$where,$aim_field,$single);
    }

    /**
     * 获取crm数据
     * @param $field
     * @param $where
     * @param string $aim_field
     * @param bool $single
     * @param array $order
     * @return mixed|null
     */
    public function getCrmData($field, $where, $aim_field='',$single=false,$order = ['id','asc']){
        return $this->simple->simpleSelect($this->crm_table,$field,$where,$aim_field,$single,$order);
    }

    /**
     * 资源库数据查询
     * @param $field
     * @param $where
     * @param string $aim_field
     * @param string $table
     * @param string $ext
     * @param bool $single
     * @return mixed
     */
    public function getCrmSourceStore($field, $where, $aim_field='', $table='',$ext='',$single=false){
        if(empty($table)){
            $table = $this->source_table;
        }
        if(!empty($ext)){
            $ext = '_'.$ext;
        }
        $res = $this->simple->simpleSelect($table.$ext,$field,$where,$aim_field,$single);
        return $res;
    }

    public function getCrmSourceStoreRead($field, $where, $aim_field='', $table='',$ext='',$single=false){
        if(empty($table)){
            $table = $this->source_table;
        }
        if(!empty($ext)){
            $ext = '_'.$ext;
        }
        $res = $this->simple->simpleSelectRead($table.$ext,$field,$where,$aim_field,$single);
        return $res;
    }

    /**
     * 获取贯穿业务库数据
     * @param $field
     * @param $where
     * @param $aim_field
     * @return mixed
     */
    public function getSourceAcrossData($field,$where,$aim_field){
        return $this->simple->simpleSelect($this->source_across,$field,$where,$aim_field);
    }

    /**
     * 获取动作数据
     * @param $where
     * @return mixed
     * @throws \Exception
     */
    public function getActionSourceData($where){
        return $this->simple->simpleLeftJoinSelect(
            $this->channel_action.' as ca',
            $this->channel_action_tag.' as cat',
            'ca.id,ca.assign_type,ca.new,ca.action_name,ca.s_source,ca.s_cat,ca.lang_type,ca.into_confine,ca.tag0, ca.tag1, ca.tag2, ca.tag3, cat.tag0 as tag_0, cat.tag1 as tag_1, cat.tag2 as tag_2, cat.tag3 as tag_3',
            $where,
            'ca.id = cat.channel_id'
        );
    }

    /**
     * 获取入库数据
     * @param $where
     * @return mixed
     * @throws \Exception
     */
    public function getGenerateSourceData($where){
        return $this->simple->simpleLeftJoinSelect(
            $this->channel_normal.' as cn',
            $this->channel_action_tag.' as cat',
            'cn.id,cn.s_source,cn.s_cat,cn.lang_type,cn.tag0, cn.tag1, cn.tag2, cn.tag3, cat.tag0 as tag_0, cat.tag1 as tag_1, cat.tag2 as tag_2, cat.tag3 as tag_3',
            $where,
            'cn.id = cat.channel_id'
        );
    }

    /**
     * 获取tag排除项存在
     * @param $where
     * @return mixed
     * @throws \Exception
     */
    public function getRemoveTagExitInfo($where){
        return $this->simple->simpleLeftJoinSelect(
            $this->channel_action_remove.' as car',
            $this->channel_action.' as ca',
            '*',
            $where,
            'car.channel_id = ca.id'
        );
    }

    /**
     * 获取tag排除项数据
     * @param $where
     * @return mixed
     * @throws \Exception
     */
    public function getRemoveTagData($where){
        return $this->simple->simpleLeftJoinSelect(
            $this->channel_action.' as ca',
            $this->channel_action_remove.' as car',
            'ca.id as channel_id,ca.lang_type,ca.action_name,car.id,car.tag0,car.tag1,car.tag2,car.tag3,car.remove_id',
            $where,
            'car.channel_id = ca.id'
        );
    }


    /**
     * 获取跟踪数据
     * @param $business_type_where
     * @param $zixun_where
     * @return mixed
     */
    public function getFollowData($business_type_where, $zixun_where){
        $query = "select z.id,z.is_employee,cf.salesman,z.mobile,cf.is_order,cf.is_oc,cf.business_type 
                              from ".$this->customer_follow." cf
                              JOIN  ".$this->customer_table." c on cf.cid=c.cid 
                              JOIN ".$this->zixun_table." z ON c.zid=z.id WHERE 1=1 ".$business_type_where.$zixun_where;
        return Db::slave('zd_class')->query($query);
    }

    /**
     * 判断是否有删除tag
     * @param $action_name
     * @return mixed
     */
    public function isRemoveAction($action_name){
        $query = "SELECT COUNT(*) AS c FROM zd_channel_action_remove as zcar LEFT JOIN zd_channel_action as zca ON zcar.channel_id = zca.id WHERE zca.action_name = '{$action_name}'";
        return Db::slave('zd_class')->query($query);
    }

    /**
     * 获取黑名单
     * @param $where
     * @param $order
     * @return mixed
     */
    public function getBlackList($where,$order){
        if($order){
            $result =  Db::slave('zd_spread')->select('*')
                ->from($this->source_black_list)
                ->where($where)
                ->orderByDESC(['id'])
                ->query();
        }else{
            $result =  Db::slave('zd_spread')->select('*')
                ->from($this->source_black_list)
                ->where($where)
                ->single();
        }
        return $result;
    }

    /**
     * 获取咨询的相关数据（zixun_uid）
     * @param $uid
     * @return mixed
     * @throws \Exception
     */
    public function getConsultData($uid){
        return Db::slave('zd_class')
            ->select('z.first_tag, z.adddate,z.id')
            ->from('zd_zixun z')
            ->leftJoin('zd_class.zd_zixun_uid','z.id=zu.zid')
            ->where('zu.uid = '.$uid.' and first_tag <> ZR')
            ->orderByASC(['z.adddate'])
            ->query();
    }

    /**
     * 数据进入资源库
     * @param $data
     * @param string $table
     * @param string $ext
     * @return mixed
     */
    public function addCrmSourceStore($data,$table='',$ext=''){
        if(empty($table)){
            $table = $this->source_table;
        }
        if(!empty($ext)){
            $ext = '_'.$ext;
        }
        return $this->simple->simpleInsert($table.$ext,$data);
    }

    /**
     * 数据进入咨询库
     * @param $data
     * @return mixed
     */
    public function addZiXun($data){
        return $this->simple->simpleInsert($this->zixun_table,$data);
    }

    /**
     * 数据进入贯穿业务库数据
     * @param $data
     * @return mixed
     */
    public function addSourceAcross($data){
        return $this->simple->simpleInsert($this->source_across,$data);
    }

    /**
     * 新增一类V2数据
     * @param $data
     * @return mixed
     */
    public function addLevelOneData($data){
        return $this->simple->simpleInsert($this->level_one,$data);
    }

    /**
     * 新增crm数据
     * @param $data
     * @return mixed
     */
    public function addCrm($data){
        return $this->simple->simpleInsert($this->crm_table,$data);
    }

    /**
     * 更新一类V2数据
     * @param $data
     * @param $where
     * @return mixed
     */
    public function saveLevelOneData($data,$where){
        return $this->simple->simpleUpdate($this->level_one,$data,$where);
    }
    /**
     * 更新贯穿业务库数据
     * @param $data
     * @param $where
     * @return mixed
     */
    public function saveSourceAcross($data,$where){
        return $this->simple->simpleUpdate($this->source_across,$data,$where);
    }

    /**
     * 资源库数据更新
     * @param $where
     * @param $data
     * @param string $table
     * @param string $ext
     * @return mixed
     */
    public function saveCrmSourceStore($data, $where, $table='',$ext=''){
        if(empty($table)){
            $table = $this->source_table;
        }
        if(!empty($ext)){
            $ext = '_'.$ext;
        }
        return $this->simple->simpleUpdate($table.$ext,$data,$where);
    }

    /**
     * 资源库数据删除
     * @param $where
     * @param string $table
     * @param string $ext
     * @return mixed
     */
    public function delCrmSourceStore($where, $table='',$ext=''){
        if(empty($table)){
            $table = $this->source_table;
        }
        if(!empty($ext)){
            $ext = '_'.$ext;
        }
        return $this->simple->simpleDelete($table.$ext,$where);
    }

    /**
     * 删除一类v2数据
     * @param $where
     * @return mixed
     */
    public function delLevelOneData($where){
        return $this->simple->simpleDelete($this->level_one,$where);
    }

    /**
     * 根据时间获取crm数据
     * @param $start_time
     * @param $end_time
     * @return mixed
     */
    public function getCrmDataByTime($start_time, $end_time){
        return Db::slave('zd_class')
            ->from('zd_crm')
            ->select('uid,tel,source,platform,cat,lang_type,tag')
            ->where('dateline > '.strtotime($start_time).' and dateline < '.strtotime($end_time))
            ->orderByASC(['id'])
            ->query();
    }

    /**
     * 数据进入客户动作表
     * @param $data
     * @return mixed
     */
    public function addCustomAction($data){
        return $this->simple->simpleInsert($this->custom_action,$data);
    }

    /**
     * 更新客户动作表
     * @param $data
     * @param $where
     * @return mixed
     */
    public function updateCustomAction($data,$where){
        return $this->simple->simpleUpdate($this->custom_action,$data,$where);
    }

    /**
     * 根据条件获取跟踪用户的数据
     * @param $param
     * @param $field
     * @return mixed
     * @throws \Exception
     */
    public function getCustomFollowInfo($param,$field){
        $where = $this->simple->getWhereSql($param);
        return Db::slave('zd_class')
            ->select($field)
            ->from($this->zixun_table.' zz')
            ->leftJoin($this->customer_table.' zc','zz.id = zc.zid')
            ->leftJoin($this->customer_follow.' zcf','zc.cid = zcf.cid')
            ->where($where)
            ->query();
    }

    /**
     * 获取首tag
     * @param $tel
     * @return string
     */
    public function getFirstTag($tel){
        $res =  Db::slave('zd_class')->select('tag')
            ->from($this->crm_table)
            ->where("tel ='{$tel}' AND tag <> ''")
            ->orderByASC(['id'])
            ->single();
        return $res ?: '';
    }

    /**
     * 设置资源库数据为已分配
     * @param $business_type
     * @param $mobile
     * @return boolean
     */
    function setSourceAssigned($business_type, $mobile)
    {
        if(isset(self::$map_lang_type_source[$business_type])){
            $this->sWhereClean()->setSqlWhereAnd(['mobile'=>$mobile]);
            $this->updateData(self::$map_lang_type_source[$business_type], ['new'=>1, 'admintime'=>time()]);
            $this->updateData(self::$map_lang_type_source[$business_type].'_action', ['new'=>1, 'admintime'=>time()]);
        }
    }

    /**
     * 获取cid有关信息
     * @param $mobile
     * @return mixed
     * @throws \Exception
     */
    public function getCidInfo($mobile){
        $where = $this->simple->getWhereSql(['mobile'=>$mobile]);
        return Db::slave('zd_class')
            ->select('cid,business_type')
            ->from($this->zixun_table.' zz')
            ->leftJoin($this->customer_table.' zc','zz.id = zc.zid')
            ->where($where)
            ->row();
    }
}