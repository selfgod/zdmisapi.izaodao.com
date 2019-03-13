<?php
namespace App\Domain\Market\Source;

use App\Domain\Sales\Customer\Consult\ConsultDomain;
use App\Model\Common\User;
use Base\Db;
use EasySwoole\Core\Component\Logger;
use App\Model\Common\Permission;
use App\Model\Market\Source\SourceModel;
use App\Domain\Sales\Setting\TeamStructureDomain;
use Base\BaseDomain;
use Base\Cache\ZdRedis;
use EasySwoole\Config;
use Base\HttpClient;
use App\Domain\Member\MemberDomain;

class LevelOneSourceDomain extends BaseDomain
{
    public $lock_action = false;
    public $SourceModel;
    public $Permission;
    public $TeamStructureDomain;
    public $MemberDomain;

    static $map_lang_type_source = [
        'jp'=>'zd_crm_source_store',
        '网销部'=>'zd_crm_source_store',
        'kr'=>'zd_crm_source_store_kr',
        'de'=>'zd_crm_source_store_de',
        'bp'=>'zd_crm_source_store_bp',
        'sp'=>'zd_crm_source_store_sp',
        'fr'=>'zd_crm_source_store_fr',
    ];

    public function __construct()
    {
        $this->SourceModel = new SourceModel();
        $this->Permission = new Permission();
        $this->TeamStructureDomain = new TeamStructureDomain();
        $this->MemberDomain = new MemberDomain();
    }

    /**
     * 设置一类资源v2
     * @param $lang_type
     * @param $platform
     * @param $cat
     * @param $uid
     * @param $username
     * @param $mobile
     * @param $source
     * @param $tag
     * @param $dateline
     * @param string $type
     * @return array
     * @throws \Exception
     */
    public function setLevelOne($lang_type, $platform, $cat, $uid, $username, $mobile, $source, $tag, $dateline, $type='action'){
        $result = ['category'=>'','is_callin'=>'','empty_callin'=>''];
        $match_info = $this->getLevelOneMatchData($cat, $source, $tag, $type,$lang_type,'action');
        logger::getInstance()->log('动作规则的匹配结果:'.json_encode($match_info));
        if(empty($match_info)){
            return $result;
        }
        $match_data = $match_info[0];   //此处为衔接后面程序，实际按需处理去掉
        $data = [
            'lang_type'=>$match_data['lang_type'],
            'platform'=>$platform,
            'category'=>$match_data['action_name'],
            'uid'=>$uid,
            'username'=>$username,
            'mobile'=>$mobile,
            'memo'=>$source,
            'tag'=>$tag,
            'dateline'=>$dateline?date('Y-m-d H:i:s',$dateline):date('Y-m-d H:i:s',time()),
        ];
        logger::getInstance()->log('测试1:'.json_encode($match_data['assign_type']));
        if($match_data['assign_type'] == 'callin'){
            $result['is_callin'] = $this->callinProcess($match_data,$mobile,$tag,$data);
        }else{
            logger::getInstance()->log('测试2:'.json_encode($mobile));
            $process_res = $this->autoProcess($mobile,$tag,$data);
            $result['empty_callin']= $process_res['empty_callin'];
            $result['category']= $process_res['category'];
        }
        return $result;
    }


    /**
     * 动作数据入一类v2
     * @param $match_data
     * @param $mobile
     * @param $tag
     * @param $data
     * @return mixed
     * @throws \Exception
     */
    public function callinProcess($match_data,$mobile,$tag,$data){
        if($match_data['into_confine']){
            $into_confine = explode(',', $match_data['into_confine']);
//            $expire_state = HttpClient::get(Config::getInstance()->getConf('LINK_HOST_ZDMIS').'Api/getCallInExpire',['mobile'=>$mobile,'lang_type'=>$data['lang_type']], ['timeout'=>300]);
            $expire_state = $this->getConsultExpire($mobile,$data['lang_type']);
            if(!in_array($expire_state, $into_confine)){
                $this->addCustomActionData($data,$expire_state);
                $this->deleteExclusionsData($mobile,$tag,'callin',$data['category'],$data['lang_type']);
            }else{
                $where = ['lang_type'=>$data['lang_type'],'mobile'=>$mobile,'is_import'=>0];
                $result = $this->SourceModel->getLevelOneData('*',$where);
                if(!empty($result)){
                    $this->SourceModel->saveLevelOneData($data, $where);
                }else{
                    $this->SourceModel->addLevelOneData($data);
                }
                $this->deleteExclusionsData($mobile,$tag,'callin',$data['category'],$data['lang_type']);
            }
        }else{
            $this->deleteExclusionsData($mobile,$tag,'callin',$data['category'],$data['lang_type']);
        }
        return $data['category'];
    }

    /**
     * 根据电话和语种获取有效期
     * @param $mobile
     * @param $lang_type
     * @return bool
     * @throws \Exception
     */
    public function getConsultExpire($mobile, $lang_type){
        $ConsultDomain = new ConsultDomain();
        $cidInfo = $this->SourceModel->getCidInfo($mobile);
        if(empty($cidInfo)){
            $expire_state = 3;
        }else{
            $callIn_expire = $ConsultDomain->consultExpire($cidInfo['cid'],$cidInfo['business_type'],$lang_type);
            if($callIn_expire===1){ //非保护期
                $expire_state = 3;
            }elseif($callIn_expire===2){ //跨业务保护期内
                $expire_state = 2;
            }else{ //保护期内
                $expire_state = 1;
            }
        }
        return $expire_state;
    }


    /**
     * 动作数据入库
     * @param $mobile
     * @param $tag
     * @param $data
     * @return mixed
     * @throws \Exception
     */
    public function autoProcess($mobile,$tag,$data){
        logger::getInstance()->log('测试3:'.json_encode($data));
        $this->intoActionProcess($data);
        $del_res = $this->deleteExclusionsData($mobile,$tag,'auto',$data['category'],$data['lang_type']);
        $result['empty_callin'] = $del_res['state'];
        $result['category'] = $data['category'];
        return $result;
    }

    /**
     * 数据进入动作库
     * @param $data
     * @throws \Exception
     */
    public function intoActionProcess($data){
        $into_set_lock = $this->getIntoSetLockBusinessType();
        logger::getInstance()->log('测试4:'.json_encode(['lang_type'=>$data['lang_type'], 'into_set_lock'=>$into_set_lock]));
        if(in_array($data['lang_type'], $into_set_lock)){
            $cid = 0;
            $follow = null;
            $cur_business_type  = $this->SourceModel->getZiXunData('id',['mobile'=>$data['mobile']],'',true);
            $zid = !empty($cur_business_type)?$cur_business_type['id']:0;
            if($zid) $cid = $this->SourceModel->getCustomData('cid',['zid'=>$zid],'cid');//通用（新老）
            if($cid) $follow = $this->SourceModel->getCustomFollowData('is_order,is_follow',[ 'cid'=>$cid, 'business_type'=>$data['lang_type']],'', true);
            logger::getInstance()->log('测试4.5:'.json_encode(['mobile'=>$data['mobile'],'cid'=>$cid,'zid'=>$zid]));
            logger::getInstance()->log('测试5:'.json_encode($follow));
            if(empty($follow)){
                $this->addActionData($data);
            }else{
                if($follow['is_order'] != 1  && $follow['is_follow'] != 1){
                    $this->addActionData($data,$data['lang_type']);
                }else{
                    $this->addCustomActionData($data,'1');
                }
            }
        }
        //当前的业务类型 锁定排他
        /*$cur_business_type  = $this->SourceModel->getZiXunData('business_type,id',['mobile'=>$data['mobile']],'',true);
        $zid = isset($cur_business_type['id'])?$cur_business_type['id']:0;
        $cur_business_type = isset($cur_business_type['business_type'])?$cur_business_type['business_type']:'';
        if($cur_business_type){
            $cur_business_type_arr = explode(',', $cur_business_type);
            $cid = $this->SourceModel->getCustomData('cid',['zid'=>$zid],'cid');//通用（新老）
            if(!$is_new){
                foreach($cur_business_type_arr as $type){ //如果是排他的，当前只有有一种排他性业务
                    if(in_array($type, $into_set_lock)){
                        $follow = $this->SourceModel->getCustomFollowData('is_order,is_follow',[ 'cid'=>$cid, 'business_type'=>$type],'', true);
                        if($follow['is_order'] != 1   && $follow['is_follow'] != 1){
                            $this->addActionData($data,$type);
                        }else{
                            $this->addCustomActionData($data,'1');
                        }
                    }
                }
            }else{
                $this->addActionData($data);
            }
        }else{
            $this->addActionData($data);
        }*/
    }

    /**
     * 入库
     * @param $rows
     * @param string $business
     * @return bool|string
     * @throws \Exception
     */
    function addActionData($rows, $business=''){
        logger::getInstance()->log('跟踪数据0:'.json_encode(['rows'=>$rows,'business'=>$business]));
        if(!empty($business)){
            $business_type = $business;
            $oc = 1;
        }else{
            $business_type = $rows['lang_type'];
            $oc = 0;
        }
        $action_rule = $this->getActionRule($business_type);
        logger::getInstance()->log('跟踪数据2:'.json_encode($action_rule));
        if(empty($action_rule)) return false;
        $source_action_table = self::$map_lang_type_source[$business_type].'_action';
        logger::getInstance()->log('跟踪数据3:'.json_encode($source_action_table));
        $_tag=explode('-',trim($rows['tag']));
        $tag0=isset($_tag[0]) && !empty($_tag[0])?strtolower($_tag[0]):'';
        $tag1 = isset($_tag[1]) && !empty($_tag[1])?strtolower($_tag[1]):'';
        $tag2 = isset($_tag[2]) && !empty($_tag[2])?strtolower($_tag[2]):'';
        $tag3 = isset($_tag[3]) && !empty($_tag[3])?strtolower($_tag[3]):'';
        $res = false;
        $match_all_oc_action_name = '';
        //如果存在，并且是新的更新，如果存在，并且没有新的，则插入
        $if_exists = $this->SourceModel->getCrmSourceStore('*',['mobile'=>$rows['mobile'],'new'=>0],'',$source_action_table,'',true);
        $data = [
            'tag'=>$rows['tag'],
            'tag0'=>$tag0,
            'tag1'=>$tag1,
            'tag2'=>$tag2,
            'tag3'=>$tag3,
            'dateline'=>strtotime($rows['dateline']),
            'sourcedate'=>$rows['dateline'],
            'source_type'=>$oc?'oc':'new',
            'sub_type'=>$rows['category'],
        ];
        if($if_exists&&!$this->isRemoveAction($if_exists['sub_type'])){
            $data['info'] = serialize($if_exists);
        }
        $action_name = $rows['category'];
        logger::getInstance()->log('跟踪数据4:'.json_encode($if_exists));
        if(!empty($if_exists)){
            $this->SourceModel->saveCrmSourceStore($data, ['id'=>$if_exists['id']],$source_action_table);
            $res = true;
        }else{
            $data['mobile'] = $rows['mobile'];
            $this->SourceModel->addCrmSourceStore($data,$source_action_table);
        }
        if(!$res && $match_all_oc_action_name && $oc && !$this->exceptFirstTag($business_type, $rows['tel'])){
            $action_name = $match_all_oc_action_name;
            $if_exists = $this->SourceModel->getCrmSourceStore('id',['mobile'=>$rows['mobile'],'new'=>0],'',$source_action_table,'',true);
            logger::getInstance()->log('跟踪数据5:'.json_encode($if_exists));
            $data = [
                'tag'=>$rows['tag'],
                'tag0'=>$tag0,
                'tag1'=>$tag1,
                'tag2'=>$tag2,
                'tag3'=>$tag3,
                'dateline'=>strtotime($rows['dateline']),
                'sourcedate'=>$rows['dateline'],
                'source_type'=>$oc?'oc':'new',
                'sub_type'=>$action_name,
            ];
            if(!empty($if_exists)){
                $this->SourceModel->saveCrmSourceStore($data,['id'=>$if_exists['id']],$source_action_table);return $action_name;
            }else{
                $data['mobile'] = $rows['tel'];
                if($this->SourceModel->addCrmSourceStore($data,$source_action_table)) return $action_name;
            }
        }
        if($res){
            return $action_name;
        }
        return false;
    }


    /**
     * 入客户动作
     * @param $param
     * @param $expire_state
     * @return array
     * @throws \Exception
     */
    public function addCustomActionData($param,$expire_state){
        $result = [];
        $insert = false;
        $field = 'zcf.business_type,zcf.salesman,zz.uid,zcf.is_follow,zcf.is_order';
        switch ($expire_state){
            case '1':   //保护期内
                $result_follow = $this->SourceModel->getCustomFollowInfo(['zz.mobile'=>$param['mobile']],$field);
                $result_s =  $this->getMatchTypeData($result_follow,['business_type'=>$param['lang_type']],true);
                if($result_s['is_order'] && $param['lang_type'] == 'jp'){
                    $result =  $this->getMatchTypeData($result_follow,['business_type'=>'up'],true);
                    $result['is_order'] = 1;
                }else{
                    $result = $result_s;
                    if($param['lang_type'] == 'up'){
                        $result['is_order'] = 1;
                    }
                }
                $insert = true;break;
            case '2':   //跨业务保护期内
                $into_set = $this->SourceModel->getChannelIntoSetData('business_type',['is_lock'=>1]);
                $lock_arr = $this->Permission->array_value_recursive('business_type', $into_set);
                $res_zixun = $this->SourceModel->getZiXunData('id,business_type',['mobile'=>$param['mobile']]);
                $business_arr = explode(',', $res_zixun[0]['business_type']);
                foreach ($business_arr as $item){
                    if(in_array($item, $lock_arr)){
                        $lang_type = $item;
                        $result_follow = $this->SourceModel->getCustomFollowInfo(['zz.id'=>$res_zixun[0]['id']], $field);
                        $result =  $this->getMatchTypeData($result_follow,['business_type'=>$lang_type],true);
                        $insert = true;break;
                    }
                }
        }
        return $insert?$this->addCustomAction($result, $param):[];
    }

    /**
     * 插入客户动作
     * @param $result
     * @param $param
     * @return mixed
     * @throws \Base\Exception\BadRequestException
     */
    public function addCustomAction($result,$param){
        $user_info = [];
        $sales_info = [];
        $insert = false;
        if(!empty($result['uid'])){ //用户信息
            $user = new User();
            $user_info = $user->getUserInfo($result['uid']);
        }
        if(!empty($result['salesman'])){
            $sales_info = $this->TeamStructureDomain->getMyTeam($result['salesman']);
            if(!empty($sales_info['cc_uid'])){
                $insert = true;
            }
        }
        $data = [
            'lang_type'=> isset($param['lang_type'])?$param['lang_type']:'',
            'team'=>isset($sales_info['team'])?$sales_info['team']:'',
            'group'=>isset($sales_info['group'])?$sales_info['group']:'',
            'stuff'=>isset($sales_info['cc'])?$sales_info['cc']:'',
            'uid'=>isset($user_info['uid'])?$user_info['uid']:0,
            'username'=>isset($user_info['username'])?$user_info['username']:'',
            'tel'=>$param['mobile'],
            'is_order'=>$result['is_order'],
            'action_name'=>isset($param['category'])?$param['category']:'',
            'dateline'=>date('Y-m-d H:i:s', time()),
        ];
        if(!empty($result['is_order'])){
            $this->SourceModel->updateCustomAction(['is_order'=>1],['lang_type'=>$data['lang_type'],'tel'=>$param['mobile'],'is_order'=>0]);
        }
        if($insert){
            $this->SourceModel->addCustomAction($data);
            $this->MemberDomain->sendUserTaskRemind(['uid'=>$sales_info['cc_uid'], 'task_key'=>'action', 'num'=>1, 'link'=>Config::getInstance()->getConf('LINK_HOST_ZDMIS').'customer/action', 'msg'=>'']);
        }
    }

    /**
     * 获取符合的数据
     * @param $data
     * @param $param
     * @param bool $single
     * @return array
     */
    public function getMatchTypeData($data, $param, $single = false){
        $result_arr = [];
        $where = [];
        $type_list = [];
        foreach ($param as $k=>$item){
            if(isset($param[$k])){
                $where[$k] = $item;
                $type_list[] = $k;
            }
        }
        foreach ($type_list as $key=>$value){
            $result_arr = $this->foreachMatch(($key == 0)?$data:$result_arr,$where,$value);
        }
        return $single?array_values($result_arr)[0]:$result_arr;
    }

    /**
     * 数据的便利匹配
     * @param $data
     * @param $where_data
     * @param $type
     * @return array
     */
    public function foreachMatch($data,$where_data,$type){
        $result_arr = [];
        if(!empty($data)){
            foreach ($data as $key=>$value){
                if(isset($value[$type])){
                    if($value[$type] == $where_data[$type]){
                        $result_arr[$key] = $value;
                    }
                }
            }
        }
        return $result_arr;
    }

    /**
     * 获取动作数据
     * @param string $business_type
     * @return mixed
     * @throws \Exception
     */
    function getActionRule($business_type=''){
        $where = ['ca.assign_type'=>'auto','cat.type'=>'action','cat.is_del'=>0];
        if($business_type){
            $where['ca.lang_type'] = $business_type;
        }
        $action_rules = $this->SourceModel->getActionSourceData($where);
        return $action_rules;
    }

//    /**
//     * 获取排除tag数据
//     * @param string $tag0
//     * @param string $tag1
//     * @param string $tag2
//     * @param string $tag3
//     * @return bool
//     * @throws \Exception
//     */
//    function getRemoveTagData($tag0='', $tag1='', $tag2='', $tag3=''){
//        $res = $this->SourceModel->getRemoveTagData([
//            'car.tag0'=>$tag0,
//            'car.tag1'=>$tag1,
//            'car.tag2'=>$tag2,
//            'car.tag3'=>$tag3
//        ]);
//        if(!empty($res)){
//            return $res[0]['action_name'];
//        }
//        return false;
//    }

    /**
     * 判断是否有删除tag
     * @param $action_name
     * @return bool
     */
    function isRemoveAction($action_name){
        $res = $this->SourceModel->isRemoveAction($action_name);
        if(!empty($res)){
            return (bool)$res[0]['c'];
        }
        return false;
    }

    /**
     * 是不下发tag
     * @param $business_type
     * @param $tel
     * @return true 为不下发 false 正常下发
     */
    function exceptFirstTag($business_type, $tel){
        //查找首tag
        if(!isset(self::$map_lang_type_source[$business_type])) return false;
        $source_table = self::$map_lang_type_source[$business_type];
        $firsttag = $this->SourceModel->getCrmSourceStore('firsttag',['mobile'=>$tel],'firsttag',$source_table);
        return !$this->channelTagAssign($business_type, $firsttag);
    }

    /**
     * 获取渠道配置
     * @param $business_type
     * @param $tag
     * @return bool
     */
    function channelTagAssign($business_type, $tag){
        $firsttag = $tag;
        if($firsttag){
            $compare = function ($set, $rule){
                if($rule=='') return true;
                if(($ppos = strpos($rule, '%'))!==false){
                    if(strtolower(substr($rule, 0, $ppos))==strtolower(substr($set, 0, $ppos))){
                        return true;
                    }
                }else{
                    if(strtolower($rule)==strtolower($set)) return true;
                }
                return false;
            };
            $_tag=explode('-',trim($firsttag));
            $tag0=isset($_tag[0]) && !empty($_tag[0])?strtolower($_tag[0]):'';
            $tag1 = isset($_tag[1]) && !empty($_tag[1])?strtolower($_tag[1]):'';
            $tag2 = isset($_tag[2]) && !empty($_tag[2])?strtolower($_tag[2]):'';
            $tag3 = isset($_tag[3]) && !empty($_tag[3])?strtolower($_tag[3]):'';
            //直接查询tag0
            $channel_tag0 = $this->SourceModel->getChannelLevelData('is_assign, tag0, tag1, tag2, tag3',['languageId'=>$business_type, 'tag0'=>$tag0]);
            if(!empty($channel_tag0)){
                $tag1_ok = false;
                $tag1_ex = null;
                $tag2_ok = false;
                $tag2_ex = null;
                foreach($channel_tag0 as $item){
                    if($item['tag1']=='EX') $tag1_ex = $item['is_assign'];
                    if($item['tag2']=='EX') $tag2_ex = $item['is_assign'];
                    //对比tag1, tag2, tag3
                    if($compare($tag1, $item['tag1'])){
                        $tag1_ok = true;
                        if($compare($tag2, $item['tag2'])){
                            $tag2_ok = true;
                            if($compare($tag3, $item['tag3'])){
                                return boolval($item['is_assign']);
                            }
                        }
                    }
                }
                if(!$tag1_ok && $tag1_ex!==null){
                    return boolval($tag1_ex);
                }
                if(!$tag2_ok && $tag2_ex!==null){
                    return boolval($tag2_ex);
                }
            }
            $tt = $this->SourceModel->getChannelLevelData('is_assign',['languageId'=>$business_type, 'tag0'=>'TT'],'is_assign');
            return boolval($tt);
        }
        return false;
    }


    /**
     * 获取锁定业务
     * @return array|mixed
     */
    function getIntoSetLockBusinessType(){
        $res = $this->SourceModel->getChannelIntoSetData('*',['is_lock'=>1]);
        return $this->Permission->array_value_recursive('business_type', $res);
    }

    /**
     * 获取一类资源匹配数据(cat,source,mobile,tag数据匹配)
     * @param $cat
     * @param $source
     * @param $tag
     * @param $type
     * @param $lang_type
     * @param $mode
     * @return array
     * @throws \Exception
     */
    public function getLevelOneMatchData($cat, $source, $tag, $type, $lang_type,$mode){
        $result = [];
        if($cat != '' || $tag != '' || $source !=''){
            $where = [
                'cat.type'=>$type,
                'cat.remove'=>0,
                'cat.is_del'=>0
            ];
            $levelOneConf = ZdRedis::instance(false)->get('levelOneConf_'.$type);
            if(!empty($levelOneConf)){
                $res = unserialize($levelOneConf);
            }else{
                $res = $this->SourceModel->getActionSourceData($where);
                ZdRedis::instance(false)->set('levelOneConf_'.$type, serialize($res));
            }
            if(!empty($res)){
                $SourceDomain = new SourceDomain();
                $result = $SourceDomain->getMatchInfo($res, $tag, $source, $cat, $lang_type,'id,lang_type,action_name,assign_type,into_confine,new',$mode);
            }
        }
        return $result;
    }


    /**
     * 由排除项删除数据
     * @param $mobile
     * @param $tag
     * @param $type
     * @param string $action_name
     * @param string $lang_type
     * @return array
     * @throws \Exception
     */
    public function deleteExclusionsData($mobile,$tag,$type,$action_name='',$lang_type='') {
        $result = ['state'=>0];
        $res_act = $res_tag = [];
        if (empty($mobile)) {
            return ['state'=>0];
        }
        $tag_arr = explode('-',$tag);
        //用传来的tag匹配，符合哪一条规则的排除tag，从而找到该规则,最终根据匹配到的action_name进行删除
        if(!empty($tag_arr[0]) && !empty($tag_arr[1]) && !empty($tag_arr[2]) && !empty($tag_arr[3])){
            $res_tag = $this->getRemoveTag(['car.tag0'=>$tag_arr[0],'car.tag1'=>$tag_arr[1],'car.tag2'=>$tag_arr[2],'car.tag3'=>$tag_arr[3]]);
        }
        //用传来的action_name匹配，符合哪一条规则，最终根据匹配到的remove_id,关联后进行删除
        if(!empty($action_name) && !empty($lang_type)){
            $res_act = $this->getRemoveTag(['ca.action_name'=>$action_name,'ca.lang_type'=>$lang_type]);
        }
        if(!empty($res_tag['action_name'])){
            $this->delLevelOneData($mobile, $res_tag,'tag');
            $this->delCrmData($mobile, $res_tag,'tag');
        }
        if(!empty($res_act['remove_id'])){
            $this->delLevelOneData($mobile, $res_act,'act');
            $action_source = $this->SourceModel->getActionSourceData(['ca.id'=>$res_act['remove_id']]);
            $res_act['action_name'] = (!empty($action_source[0]['action_name']))?$action_source[0]['action_name']:'';
            $this->delCrmData($mobile, $res_act,'act');
            if($type == 'auto'){
                $result = ['state'=>1];
            }
        }
        return $result;
    }

    /**
     * 获取排除配置tag表
     * @param $where
     * @param bool $count
     * @return int
     * @throws \Exception
     */
    public function getRemoveTag($where, $count = false){
        if($count){
            $num = $this->SourceModel->getRemoveTagExitInfo($where);
            $result = ($num>0)?1:0;
        }else{
            $res = $this->SourceModel->getRemoveTagData($where);
            $result['channel_id'] = isset($res[0]['channel_id'])?$res[0]['channel_id']:'';
            $result['lang_type'] = isset($res[0]['lang_type'])?$res[0]['lang_type']:'';
            $result['action_name'] = isset($res[0]['action_name'])?$res[0]['action_name']:'';
            $result['remove_id'] = isset($res[0]['remove_id'])?$res[0]['remove_id']:'';
            foreach ($res as $k=>$item){
                if(!empty($item['id'])){
                    $result['r_tag'][$k]['id'] = $item['id'];
                    $result['r_tag'][$k]['tag0'] = $item['tag0'];
                    $result['r_tag'][$k]['tag1'] = $item['tag1'];
                    $result['r_tag'][$k]['tag2'] = $item['tag2'];
                    $result['r_tag'][$k]['tag3'] = $item['tag3'];
                }
            }
        }
        return $result;
    }

    /**
     * 根据tag删除一类v2数据
     * @param $mobile
     * @param $res
     * @param $mode
     * @throws \Exception
     */
    public function delLevelOneData($mobile, $res, $mode){
        $where["mobile"] = $mobile;
        $where["is_import"] = 0;
        if($mode =='act'){
            $remove_tag_info = $this->getRemoveTag(['ca.id'=>$res['remove_id']]);
            $action_name = $remove_tag_info['action_name'];
        }else{
            $action_name = $res['action_name'];
        }
        $where["category"] = $action_name;
        $this->SourceModel->delLevelOneData($where);
    }

    /**
     * 删除入库数据
     * @param $mobile
     * @param $res
     * @param $mode
     */
    public function delCrmData($mobile, $res,$mode){
        $where["mobile"] = $mobile;
        $where["sub_type"] = $res['action_name'];
        $where["new"] = 0;
        $pre_info = ['sub_type'=>''];
        $lang_type = $res['lang_type'];
        if(in_array($lang_type,['jp','kr','de','bp'])){
            $action_info = $this->SourceModel->getCrmSourceStore('*',$where,'',self::$map_lang_type_source[$lang_type].'_action','',true);
            if(!empty($action_info['info'])){
                $pre_info = unserialize($action_info['info']);
                $this->SourceModel->saveCrmSourceStore([
                    'admin'=>$pre_info['admin'],
                    'new'=>$pre_info['new'],
                    'tag0'=>$pre_info['tag0'],
                    'tag1'=>$pre_info['tag1'],
                    'tag2'=>$pre_info['tag2'],
                    'tag3'=>$pre_info['tag3'],
                    'tag'=>$pre_info['tag'],
                    'mobile'=>$pre_info['mobile'],
                    'dateline'=>$pre_info['dateline'],
                    'sourcedate'=>$pre_info['sourcedate'],
                    'source_type'=>$pre_info['source_type'],
                    'sub_type'=>$pre_info['sub_type'],
                    'is_lock'=>$pre_info['is_lock'],
                    'admintime'=>$pre_info['admintime'],
                ],['id'=>$action_info['id']],self::$map_lang_type_source[$lang_type].'_action');
            }else{
                $this->SourceModel->delCrmSourceStore(['id'=>$action_info['id']],self::$map_lang_type_source[$lang_type].'_action');
            }
            if($mode == 'tag'){
                $where = ['is_action'=>$pre_info['sub_type'],'is_lock'=>2];
            }else{
                $where = ['is_action'=>$pre_info['sub_type']];
            }
            $this->SourceModel->saveCrmSourceStore($where,['mobile'=>$mobile],self::$map_lang_type_source[$lang_type]);
        }
    }

}