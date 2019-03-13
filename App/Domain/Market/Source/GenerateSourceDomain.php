<?php
namespace App\Domain\Market\Source;

use App\Model\Market\Source\AssignModel;
use App\Model\Market\Source\SourceAdModel;
use App\Model\Market\Source\SourceModel;
use Base\BaseDomain;
use App\Model\Common\Permission;
use Base\HttpClient;
use EasySwoole\Core\Component\Logger;

class GenerateSourceDomain extends BaseDomain
{
    public $lock_action = false;
    public $assignModel;
    public $SourceModel;

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
        $this->assignModel = new AssignModel();
    }

    /**
     * 数据入库
     * @param $data
     * @return array
     * @throws \Exception
     */
    public function setGenerateSource($data){
        if(!empty($data['lang_type'])){
            $match_info[0] = ['lang_type'=>$data['lang_type']];
        }else{
            $match_info = $this->getGenerateSourceMatchData($data['cat'],$data['source'],$data['tag'],'normal',$data['lang_type'],'normal');
        }
        logger::getInstance()->log('入库规则的匹配结果:'.json_encode($match_info));
        if(empty($match_info[0])){
            $this->intoSourceProcess('jp', $data);
        }else{
            $this->intoSourceProcess($match_info[0]['lang_type'], $data);
        }
        return isset($match_info[0])?$match_info[0]:[];
    }

    /**
     * 获取业务锁定开启配置表信息
     * @return array|mixed
     */
    function getIntoSetLockBusinessType(){
        $Permission = new Permission();
        $res = $this->SourceModel->getChannelIntoSetData('*', ['is_lock'=>1]);
        return $Permission->array_value_recursive('business_type', $res);
    }

    /**
     * 由语种获取锁定状态
     * @param $business_type
     * @return mixed
     */
    function getLockByBusiness($business_type){
        return $this->SourceModel->getChannelIntoSetData('is_lock', ['business_type'=>$business_type],'is_lock');
    }

    /**
     * 获取入库配置
     * @param string $business_type
     * @param bool $is_open
     * @return mixed
     */
    function getIntoSet($business_type='', $is_open=true){
        $w = [];
        if($is_open){
            $w['is_open'] = 1;
        }
        if($business_type){
            $w['business_type'] = $business_type;
        }
        return $this->SourceModel->getChannelIntoSetData('*', $w);
    }

    function getMobileCity($mobile){
        //查询手机归属地
        $HttpClient = new HttpClient();
        $c_res = $HttpClient->get("http://mobile.izaodao.com:3000",['mobile'=>$mobile]);
//        $c_res = curlGet("http://mobile.izaodao.com:2000/mobile/{$mobile}");
        if(empty($c_res)) return array('','');
        $c_res = json_decode($c_res, true);
        if(!empty($c_res) && $c_res['status']){
            return array($c_res['data']['province'], $c_res['data']['city']);
        }
        return array('','');
    }

    /**
     * 入库处理流程
     * @param $b_type
     * @param $rows
     * @return bool
     */
    function intoSourceProcess($b_type,$rows){
        $is_lock=$this->getLockByBusiness($b_type);
        $business_type = $b_type;
        if($b_type == 'up'){
            $business_type = 'jp';
        }
        $source_table = self::$map_lang_type_source[$business_type];
        $business_type_where = $this->getBusinessCond($business_type);
        $check_res = $this->specialCheck($rows,$b_type);
        if(!$check_res){
            return false;
        }
        $rows['tel']=htmlspecialchars(trim($rows['tel']));
        if(is_numeric($rows['tel'])){
            $_tag=explode('-',trim(isset($rows['tag'])?$rows['tag']:''));
            $tag0=isset($_tag[0]) && !empty($_tag[0])?$_tag[0]:'ZR';
            $tag0=($tag0=='360')?'SS360':$tag0;
            $tag1 = isset($_tag[1]) && !empty($_tag[1])?$_tag[1]:'';
            $tag2 = isset($_tag[2]) && !empty($_tag[2])?$_tag[2]:'';
            $tag3 = isset($_tag[3]) && !empty($_tag[3])?$_tag[3]:'';

            $add_zixun_cus_res = $this->addZixunCustom($rows, $b_type,$business_type_where,$source_table);
            $rows['isnew'] = $add_zixun_cus_res['isnew'];
            $rows['admin'] = $add_zixun_cus_res['admin'];
            $store_id  = $add_zixun_cus_res['store_id'];
            $store = $add_zixun_cus_res['store'];
            $category = $this->getCategory($b_type,$rows,$tag0,$tag2,$tag3);
            $update_arr = '';
            if(!$store_id){ //首次入库
                $store_id = $this->firstIntoGenerate($rows,$category,$tag0,$source_table,$business_type);
                //记录ad tag
                if($category=='app_ad') (new SourceAdModel())->insertRow($store_id, $rows['tag']);
            }else{  //再次入库
                $update_arr = $this->nextIntoGenerate($rows,$business_type,$store,$store_id,$tag0,$tag2,$tag3);
                if(isset($update_arr['update_category']) && $update_arr['update_category']=='app_ad')
                    (new SourceAdModel())->insertRow($store_id, $rows['tag']);
            }
            $update_data = $this->getUpdateData($rows,$update_arr);

            $this->SourceModel->saveCrmSourceStore($update_data, ['id'=>$store_id],$source_table);//每次更新更新时间
            //入库时对于多语种同时的资源，要进行提取锁定判断
            //查询所有库的最新时间，判断最后的更新库，关闭锁定
            if($is_lock && !$this->lock_action){
                $this->checkLock($rows);
            }
            $this->SourceModel->addCrmSourceStore([
                'fid'=>$store_id,
                'ip'=>$rows['ip']?$rows['ip']:'',
                'cat'=>$rows['cat']?$rows['cat']:'',
                'dateline'=>isset($rows['dateline'])?$rows['dateline']:time(),
                'source'=>$rows['source']?$rows['source']:'',
                'tag0'=>$tag0,
                'tag1'=>isset($_tag[1])?$_tag[1]:'',
                'tag2'=>isset($_tag[2])?$_tag[2]:'',
                'tag3'=>isset($_tag[3])?$_tag[3]:'',
                'tag4'=>isset($_tag[4])?$_tag[4]:'',
                'operate' =>isset($rows['operate'])?$rows['operate']:''
            ],$source_table,'ext');
        }
        return false;
    }


    /**
     * 特殊验证
     * @param $rows
     * @param $b_type
     * @return bool
     */
    public function specialCheck($rows,$b_type){
        if($b_type == 'jp'){
            $this->lock_action = false;
            //倍普
            if($rows['cat']=='倍普'){
                return false;
            }
            if($rows['cat']=='轻松过N1【wap】' && !$rows['tag']) return false;
        }
        if($b_type != 'jp'){
            if(preg_match("/^111000\d{5}$/", $rows['tel'])) return false;
            if(preg_match("/^121000\d{5}$/", $rows['tel'])) return false;
            if(preg_match("/^120000\d{5}$/", $rows['tel'])) return false;
        }
        return true;
    }

    /**
     * 获取业务查询的条件
     * @param $business_type
     * @return string
     */
    public function getBusinessCond($business_type){
        $lock_business_types = $this->getIntoSetLockBusinessType();
        $business_type_where = " and cf.business_type='{$business_type}'";
        if(in_array($business_type, $lock_business_types)){
            $lock_business_types_str = implode("','", $lock_business_types);
            $business_type_where = " and cf.business_type in('{$lock_business_types_str}')";
        }
        return $business_type_where;
    }


    /**
     * 获取category
     * @param $business_type
     * @param $rows
     * @param $tag0
     * @param $tag2
     * @param $tag3
     * @return string
     */
    public function getCategory($business_type,$rows,$tag0,$tag2,$tag3){
        $category = '';
        if($business_type == 'jp'){
            if(in_array($rows['tag'], ['WX-ZY-ZDEWM-ZJ1031', 'WX-ZY-ZDWZ-ZJ1031', 'WX-ZY-ZDCD-ZJ1031','SPZJ'])
                || strpos($rows['tag'], 'GG-AC')===0 || $tag0=='ZZD' || strpos(strtolower($tag3), 'ac')===0
            ){
                $category = 'except';
            }
            if(strtolower($tag0)=='app'){
                $category = 'app';
                if($rows['isnew']!=1 && (strtolower($tag2)=='ad' || strtolower($tag3)=='ad' ||strpos(strtolower($tag3),'ad_')===0)){ //新资源才标记为
                    $category = 'app_ad';
                }
            }elseif(strtolower($tag0)=='xd'){
                $category = 'scl_xd';
            }

            if($category==''){
                //判断是否为低价课记录
                $count = $this->SourceModel->getFineClassNum($rows['tel']);
                if(!empty($count) && $count[0]['c']>0){
                    $category = 'fine_class';
                }
            }
        }else{
            if(!empty($rows) && isset($rows['category'])){
                $category = $rows['category'];
            }

            if(strtolower($tag0)=='app'){
                if($category=='') $category = 'app';
                if($rows['isnew']!=1 && (strtolower($tag2)=='ad' || strtolower($tag3)=='ad')){ //新资源才标记为
                    $category = 'app_ad';
                }
            }
            if($category==''){
                $if_exists = $this->SourceModel->getLevelOneData('category', ['lang_type'=>$business_type,'mobile'=>$rows['tel']]);
                if(!empty($if_exists)){
                    $category = $if_exists[0]['category'];
                }
            }
        }
        if($category==''){
            $if_exists = $this->SourceModel->getZiXunCallinData('getinfo', ['lang_type'=>$business_type,'mobile'=>$rows['tel']]);
            if(!empty($if_exists)){
                $category = $if_exists[0]['getinfo'];
            }
        }
        return $category;
    }

    /**
     * 首次入库(入渠道库，新增或更新贯穿库)
     * @param $rows
     * @param $category
     * @param $tag0
     * @param $source_table
     * @param $business_type
     * @return mixed
     */
    public function firstIntoGenerate($rows,$category,$tag0,$source_table,$business_type){
        $_tag=explode('-',trim($rows['tag']));
        /***使用框架数组插入方法时一定要严格判断null**/
        $province = $city = '';
        if($rows['tel']){
            list($province, $city) = $this->getMobileCity($rows['tel']);
        }
        $_data = array(
            'tag0'=>$tag0,
            'tag1'=>isset($_tag[1])?$_tag[1]:'',
            'tag2'=>isset($_tag[2])?$_tag[2]:'',
            'tag3'=>isset($_tag[3])?$_tag[3]:'',
            'firsttag'=>$rows['tag']?$rows['tag']:'',
            'admin'=>isset($rows['admin'])?$rows['admin']:'',
            'new'=>$rows['isnew'],
            'fid'=>isset($rows['id'])?$rows['id']:0,
            'uid'=>$rows['uid']?$rows['uid']:0,
            'mobile'=>$rows['tel'],
            'name'=>$rows['name']?$rows['name']:'',
            'email'=>isset($rows['email'])?$rows['email']:'',
            'dateline'=>isset($rows['dateline'])?$rows['dateline']:time(),
            'sourcedate'=>isset($rows['dateline'])?date('Y-m-d',$rows['dateline']):date('Y-m-d', time()),
            'category'=>$category,
            'province'=>$province,
            'city'=>$city,
            'updatedate'=>isset($rows['dateline'])?date('Y-m-d H:i:s', $rows['dateline']):date('Y-m-d H:i:s',time()),
            'is_action'=>isset($rows['is_action'])?$rows['is_action']:'',
            'is_callin'=>isset($rows['is_callin'])?$rows['is_callin']:'',
        );
        $store_id = $this->SourceModel->addCrmSourceStore($_data,$source_table);

        if($store_id) {
            //入库后，贯穿业务库标记 zd_crm_source_store_across , 当前业务
            $across_type = $this->SourceModel->getSourceAcrossData('lang_type', ['mobile'=>$rows['tel']],'lang_type');
            if($across_type){
                if(strpos($across_type, $business_type)===false && $business_type){
                    $across_type=$across_type.','.$business_type;
                    $this->SourceModel->saveSourceAcross(['lang_type'=>$across_type],['mobile'=>$rows['tel']]);
                }
            }elseif($business_type){
                $this->SourceModel->addSourceAcross(['lang_type'=>$business_type, 'mobile'=>$rows['tel']]);
            }
        }
        return $store_id;
    }


    /**
     * 再次入库
     * @param $rows
     * @param $store
     * @param $business_type
     * @param $store_id
     * @param $tag0
     * @param $tag2
     * @param $tag3
     * @return array
     */
    public function nextIntoGenerate($rows,$business_type,$store,$store_id,$tag0,$tag2,$tag3){
        $result = ['update_category'=>'','update_callin'=>''];
        if($business_type == 'jp'){
            if(isset($store[0]['tag0']) && strtolower($store[0]['tag0'])=='app'){
                //判断之前有没有pay
                $if_pay_exists = $this->SourceModel->getCrmSourceStore('id',['fid'=>$store_id, 'tag2'=>'PAY'],'','','ext',true);
                $if_pay_exists2 = $this->SourceModel->getCrmSourceStore('id',['fid'=>$store_id, 'tag3'=>'pay'],'','','ext',true);
                if($store[0]['admintime']==0 && (strtolower($tag2)=='ad'||strtolower($tag3)=='ad'||strpos(strtolower($tag3),'ad_')===0) && empty($if_pay_exists) && empty($if_pay_exists2)){ //新资源才标记为
                    $result['update_category'] = 'app_ad';
                }
            }
            //查询上次tag是否是早知道
            if(isset($store[0]['category']) && $store[0]['category']=='except' && strtolower($store[0]['tag0'])=='zzd' && strtolower($tag0)!='zzd'){
                $result['update_category'] = 'zzd';
            }
        }
        if($result['update_callin']==''){
            $if_exists = $this->SourceModel->getLevelOneData('category', ['lang_type'=>$business_type,'mobile'=>$rows['tel']]);
            if(!empty($if_exists)){
                $result['update_callin'] = $if_exists[0]['category'];
            }
        }
        if($result['update_callin']==''){
            $if_exists = $this->SourceModel->getZiXunCallinData('getinfo', ['lang_type'=>$business_type,'mobile'=>$rows['tel']]);
            if(!empty($if_exists)){
                $result['update_callin'] = $if_exists[0]['getinfo'];
            }
        }
        return $result;
    }

    /**
     * 获取更新数据集
     * @param $rows
     * @param $update_arr
     * @return array
     */
    public function getUpdateData($rows,$update_arr){
        $update_data = array('new'=>$rows['isnew']);
        if(!$this->lock_action){
            $update_data['updatedate']=isset($rows['dateline'])?date('Y-m-d H:i:s', $rows['dateline']):date('Y-m-d H:i:s',time());
        }else{
            $update_data['is_lock'] = 1;
        }
        if(isset($update_arr['update_category']) && $update_arr['update_category']){
            $update_data['category'] = $update_arr['update_category'];
        }
        if(isset($update_arr['update_callin']) && $update_arr['update_callin']){
            $update_data['is_callin'] = $update_arr['update_callin'];
        }
        if(!empty($rows['is_action'])){
            $update_data['is_action'] = $rows['is_action'];
        }
        if($rows['empty_callin']){
            $update_data['is_callin'] = '';
        }
        return $update_data;
    }

    /**
     * 加入咨询和用户关系表
     * @param $rows
     * @param $b_type
     * @param $business_type_where
     * @param $source_table
     * @return array
     */
    public function addZixunCustom($rows, $b_type,$business_type_where,$source_table){
        $store_id=0;
        if($rows['uid']){   //有uid根据uid或tel查
            $zixun_where = " and uid='{$rows['uid']}' or mobile='{$rows['tel']}'";
            $store = $this->SourceModel->getCrmSourceStoreRead('id,category,tag0,admintime',
                ['uid'=>$rows['uid'],'mobile'=>$rows['tel'],'or_use'=>true],'',$source_table);
            if(!empty($store)){
                $store_id = $store[0]['id'];
            }
        }else{  //没uid根据tel查
            $store = $this->SourceModel->getCrmSourceStoreRead('id,category,tag0,admintime', ['mobile'=>$rows['tel']],'',$source_table);
            if(!empty($store)){
                $store_id = $store[0]['id'];
            }
            $zixun_where = " and mobile='{$rows['tel']}'";
        }
//        $cc_field = self::$consult_salesman_fixed[$b_type];
        $follow_arr = $this->SourceModel->getFollowData($business_type_where, $zixun_where);
        $rows['isnew'] = 0;
        $follow_item = null;
        $follow_up_item = $this->SourceModel->getFollowData(" and cf.business_type= 'bp' ",$zixun_where);
        $zixun_mobile = [];
        $business_num = [];
        $rows['admin'] = '';
        $business_oc_num = 0;
        if(!empty($follow_arr)){
            foreach($follow_arr as $item){
                if($item['business_type']==$b_type) $follow_item = $item;
                if(!in_array($item['mobile'], $zixun_mobile)) $zixun_mobile[] = $item['mobile'];
                if(!in_array($item['business_type'], $business_num)) {
                    $business_num[] = $item['business_type'];
                    if($item['is_oc']) $business_oc_num++;
                }
            }
        }
        if(!empty($follow_up_item)){
            $follow_item = $follow_up_item[0];
        }
        if(!empty($follow_item)){
            $rows['isnew'] = 1;
            $rows['admin'] = $follow_item['salesman'];
            if(!in_array($rows['tel'], $zixun_mobile) && $rows['admin']){
                $_d = [
                    'mobile'=>$rows['tel'],
//                    $cc_field=>$rows['admin'],//此处老逻辑兼容
                    'uid' => 0,
                    'name' => 0,
                    'storeid' => $store_id
                ];
                $new_zixun_id = $this->SourceModel->addZiXun($_d);
                $this->assignModel->addCustomerRelation($follow_item['id'], $new_zixun_id);
            }
        }
        return ['isnew'=>$rows['isnew'],'admin'=>$rows['admin'],'store_id'=>$store_id,'store'=>$store];
    }

    function checkLock($rows){
        $latest_time = 0;
        $into_set = $this->getIntoSet();
        $into_set_lock = $this->getIntoSetLockBusinessType();
        $set_it = '';
        foreach($into_set as $set){
            if(!$set['is_lock']) continue;
            if(!isset(self::$map_lang_type_source[$set['business_type']])) continue;
            $source_table = self::$map_lang_type_source[$set['business_type']];
            $business_sourcetime = $set['business_type'].'_sourcetime';
            $business_id = $set['business_type'].'_id';
            $source_row = $this->SourceModel->getCrmSourceStoreRead('updatedate,id', ['new'=>0, 'mobile'=>$rows['tel']], '', $source_table);
            if(!empty($source_row)){
                $$business_sourcetime = strtotime($source_row[0]['updatedate']);
                $$business_id = $source_row[0]['id'];
                if($latest_time<$$business_sourcetime){
                    $latest_time = $$business_sourcetime;
                    $set_it = $set['business_type'];
                }
            }
            //取所有设置锁定业务的库id
            if(!isset($$business_id)){
                $$business_id = $this->SourceModel->getCrmSourceStoreRead('id', ['mobile'=>$rows['tel']], 'id',$source_table);
            }
        }
        //当前的业务类型 锁定排他
        $cur_business_type = $this->SourceModel->getZiXunData('business_type,id',['mobile'=>$rows['tel']],'',true);
        $zid = isset($cur_business_type['id'])?$cur_business_type['id']:0;
        $cur_business_type = isset($cur_business_type['business_type'])?$cur_business_type['business_type']:'';
        if($cur_business_type){
            $cur_business_type_arr = explode(',', $cur_business_type);
            foreach($cur_business_type_arr as $type){ //如果是排他的，当前只有有一种排他性业务
                if(in_array($type, $into_set_lock)){
                    //查询当前在跟人是否为空或不跟踪，并且不为oc
                    $cid_res = $this->SourceModel->getCustomData('cid',['zid'=>$zid],'',true);
                    $follow = $this->SourceModel->getCustomFollowData('salesman, is_follow, is_oc', ['cid'=>$cid_res['cid'],'business_type'=>$type],'',true);
                    if(!empty($follow)){
                        if($follow['salesman']!='' && $follow['is_follow'] && $follow['salesman']!='不跟踪' && $follow['is_oc']==0){
                            $set_it = $type;
                        }
                    }else{
                        $set_it = $type;
                    }

                }
            }
        }

        if($set_it=='') {
            return true;
        }

        //如果排他型，并且不是当前业务，这锁定
        foreach($into_set as $set){
            if($set['is_lock'] && $set['business_type']!=$set_it){
                if(!isset(self::$map_lang_type_source[$set['business_type']])) continue;
                $source_table = self::$map_lang_type_source[$set['business_type']];
                $business_id = $set['business_type'].'_id';
                $this->SourceModel->saveCrmSourceStore(['is_lock'=>1], ['id'=>$$business_id, 'new'=>0], $source_table);
                $this->SourceModel->saveCrmSourceStore(['is_lock'=>1], ['mobile'=>$rows['tel'], 'new'=>0], $source_table.'_action');
            }
        }
        //解锁当前的
        if($set_it && in_array($set_it, $into_set_lock)){
            if(!isset(self::$map_lang_type_source[$set_it])) return false;
            $source_table = self::$map_lang_type_source[$set_it];
            $business_id = $set_it.'_id';
            $this->SourceModel->saveCrmSourceStore(['is_lock'=>0], ['id'=>$$business_id], $source_table);
            $this->SourceModel->saveCrmSourceStore(['is_lock'=>0], ['mobile'=>$rows['tel']], $source_table.'_action');
        }
    }

    /**
     * 获取入库规则的匹配结果
     * @param $cat
     * @param $source
     * @param $tag
     * @param $type
     * @param $lang_type
     * @param $mode
     * @return array
     * @throws \Exception
     */
    public function getGenerateSourceMatchData($cat, $source, $tag, $type, $lang_type, $mode){
        $result = [];
        if($cat != '' || $tag != '' || $source !=''){
            $where = [
                'cat.type'=>$type,
                'cat.remove'=>0,
                'cat.is_del'=>0
            ];
            $res = $this->SourceModel->getGenerateSourceData($where);
            if(!empty($res)){
                $SourceDomain = new SourceDomain();
                $result = $SourceDomain->getMatchInfo($res, $tag, $source, $cat, $lang_type,'id,lang_type',$mode);
            }
        }
        return $result;
    }

}