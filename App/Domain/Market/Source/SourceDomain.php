<?php
namespace App\Domain\Market\Source;

use App\Model\Common\Permission;
use App\Model\Common\User;
use App\Model\Market\Source\SourceModel;
use App\Model\Market\Source\AssignModel;
use Base\BaseDomain;
use Base\Cache\ZdRedis;
use EasySwoole\Core\Component\Logger;

class SourceDomain extends BaseDomain
{
    public $lock_action = false;
    public $SourceModel;
    public $Permission;
    public $AssignModel;

    static $map_lang_type_source = [
        'jp'=>'zd_crm_source_store',
        '网销部'=>'zd_crm_source_store',
        'kr'=>'zd_crm_source_store_kr',
        'de'=>'zd_crm_source_store_de',
        'sp'=>'zd_crm_source_store_sp',
        'fr'=>'zd_crm_source_store_fr',
        'bp'=>'zd_crm_source_store_bp',
    ];

    public function __construct()
    {
        $this->AssignModel = new AssignModel();
        $this->SourceModel = new SourceModel();
        $this->Permission = new Permission();
    }

    /**
     * 添加数据到crm表
     * @param $param
     * @return array
     * @throws \Exception
     */
    public function addDataIntoCrm($param){
        $Permission = new Permission();
        $black_arr = [];
        $tag = $param['tag'];
        $black_list = $this->getBlackList(0);
        if(!empty($black_list) && is_array($black_list) ){
            $black_arr = $Permission->array_value_recursive('value',$black_list);
        }
        if(in_array($param['ip'],$black_arr) || in_array($param['tel'],$black_arr)){
            return ['state'=>1,'msg'=>'插入失败','add_res'=>[],'data'=>[],'user'=>[]];
        }
        if(strpos($param['cat'], '内部数据')!==false){
            return ['state'=>0,'msg'=>'内部数据不进crm','add_res'=>[],'data'=>[],'user'=>[]];
        }
        $user = null;
        $user = new User();
        if(!empty($param['uid'])){
            $user = $user->getUserInfo($param['uid']);
        }elseif (!empty($param['openId'])){
            $user = $user->getUserForOpenId($param['openId']);
        }
        $data = [
            'uid'=>$user?$user['uid']:0,
            'name'=>$user ? $user['username'] : '',
            'tel'=>isset($param['tel'])?$param['tel']:0,
            'source'=>$param['source'],
            'platform'=>$param['platform'],
            'lang_type'=>$param['lang_type'],
            'ip'=>$param['ip'],
            'dateline'=>$param['dateline']?$param['dateline']:time(),
            'cat'=>$param['cat'],
            'tag'=>$param['tag'],
            'keywords'=>$param['keywords'],
            'operate'=>$param['operate'],
            'interCode'=>$param['interCode']
        ];
        if(!$param['tag'] && ($param['cat'] != 'APP-早道网校')){
            $tag = $this->getFirstTag($param['tel']);
        }
        if($tag){
            $data['tag'] = $tag;
            $split_tag = explode('-', $tag);
            if(is_array($split_tag)){
                $data['ctag0'] = isset($split_tag[0])?$split_tag[0]:'';
                $data['ctag1'] = isset($split_tag[1])?$split_tag[1]:'';
                $data['ctag2'] = isset($split_tag[2])?$split_tag[2]:'';
                $data['ctag3'] = isset($split_tag[3])?$split_tag[3]:'';
            }
        }
        logger::getInstance()->log('入CRM前数据:'.json_encode($data));
        $crm_res = $this->SourceModel->addCrm($data);
        return ['state'=>1,'msg'=>'','add_res'=>$crm_res,'data'=>$data,'user'=>$user];
    }

    /**
     * 数据进入v2、动作库，资源库表
     * @param $crm_res
     * @param $param
     * @throws \Exception
     */
    public function addDataIntoSource($crm_res,$param){
        $GenerateSourceDomain = new GenerateSourceDomain();
        $LevelOneSourceDomain = new LevelOneSourceDomain();
        $g_param = $crm_res['data'];
        $g_param['id'] = intval($crm_res['add_res']);
        //数据进入一类资源(新)
        $username = $crm_res['user'] ? $crm_res['user']['username'] : '';
        $user = new User();
        if(!$user->isEmployeeNew($crm_res['data']['tel'])){
            if($param['cat'] == '录入'){
                $g_param['is_action'] = '';
                $g_param['is_callin'] = isset($param['getinfo'])?$param['getinfo']:'';
                $g_param['empty_callin'] = '';
            }else{
                $action_result = $LevelOneSourceDomain->setLevelOne($param['lang_type'], $param['platform'], $param['cat'],
                    $crm_res['user']?$crm_res['user']['uid']:0, $username, $param['tel'], $param['source'], $param['tag'], $param['dateline'], 'action');
                $g_param['is_action'] = isset($action_result['category'])?$action_result['category']:'';
                $g_param['is_callin'] = isset($action_result['is_callin'])?$action_result['is_callin']:'';
                $g_param['empty_callin'] = isset($action_result['empty_callin'])?$action_result['empty_callin']:'';
            }
            $GenerateSourceDomain->setGenerateSource($g_param);
        }
    }

    /**
     * 获取匹配信息
     * @param $data
     * @param $tag
     * @param $source
     * @param $cat
     * @param $lang_type
     * @param $field
     * @param $mode
     * @return array
     */
    public function getMatchInfo($data, $tag, $source, $cat, $lang_type,$field,$mode){
        $result = [];
        if(!empty($data)){
            foreach ($data as $key=>$item){
                if(empty($item['s_cat']) && empty($item['s_source']) && empty($item['tag0']) &&
                    empty($item['tag1']) && empty($item['tag2']) && empty($item['tag3'])){
                    unset($data[$key]);
                }
            }
            $source_ids = $this->getMatchTypeIds($data, $source, 's_source');
            if(!empty($lang_type) && $mode == 'action'){
                $lang_ids = $this->getMatchTypeIds($data, $lang_type, 'lang_type');
                $arr_ids_temp = array_intersect($lang_ids, $source_ids);
            }else{
                $arr_ids_temp = $source_ids;
            }
            $cat_Ids = $this->getMatchTypeIds($data, $cat,'s_cat');
            $arr_ids = array_intersect($arr_ids_temp, $cat_Ids);
            $result = $this->tagMatch($arr_ids, $data, $tag,$field);
        }
        return $result;
    }


    /**
     * 进行tag匹配
     * @param $arr_ids
     * @param $data
     * @param $tag
     * @param $back_field
     * @return array
     */
    public function tagMatch($arr_ids, $data, $tag, $back_field){
        $result = [];
        if(!empty($data)){
            $result = [];
            foreach ($data as $k=>$v){
                $arr_new = [];
                if(in_array($v['id'], $arr_ids)){
                    $arr_new[$v['id']][0] = strtolower($v['tag0'].'-'.$v['tag1'].'-'.$v['tag2'].'-'.$v['tag3']);
                    $arr_new[$v['id']][1] = strtolower($v['tag_0'].'-'.$v['tag_1'].'-'.$v['tag_2'].'-'.$v['tag_3']);
                    $arr_new[$v['id']] = ($arr_new[$v['id']][0] == $arr_new[$v['id']][1])?$arr_new[$v['id']][0]:$arr_new[$v['id']][1];
                    $tag_arr = explode('-', strtolower($tag));
                    if(count($tag_arr) == 1 && empty($tag_arr[0])){
                        $tag_arr = [0=>'',1=>'',2=>'',3=>''];
                    }
                    $ret = 0;
                    if(!empty($arr_new)){
                        $v2 = explode('-', $arr_new[$v['id']]);
                        for($i = 0; $i<count($v2);$i++){
                            if(isset($v2[$i]) && isset($tag_arr[$i]) && ($v2[$i] == $tag_arr[$i]) || empty($v2[$i])){
                                $ret += 1;
                            }
                        }
                        if($ret == 4){
                            $temp = [];
                            foreach (explode(',',$back_field) as $item){
                                $temp[$item] = (isset($v[$item]) && !empty($v[$item]))?$v[$item]:null;
                            }
                            $result[] = $temp;
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * 获取符合语种的ids
     * @param $data
     * @param $param
     * @param $type
     * @return array
     */
    public function getMatchTypeIds($data, $param, $type){
        $channel_source = [];
        foreach ($data as $k=>$v){
            $channel_source[$v['id']] =  $v[$type];
        }
        $arr_ids = [];
        foreach ($channel_source as $k=>$v){
            if(empty($v)){
                $arr_ids[] = $k;
            }elseif($v == $param){
                $arr_ids[] = $k;
            }
        }
        return $arr_ids;
    }


    /**
     * 取数据的第一个tag
     * @param $tel
     * @return mixed
     */
    public function getFirstTag($tel){
        return $this->SourceModel->getFirstTag($tel);
    }


//    /**
//     * 取数据的第一个tag
//     * @param $mobile
//     * @param int $uid
//     * @param string $email
//     * @return mixed|string
//     * @throws \Exception
//     */
//    function getFirstTag($mobile, $uid=0, $email=''){
//        $res = null;
//        $tag_arr = [];
//        if($mobile || $email){
//            $w = [];
//            if($uid){
//                $consult_arr = $this->SourceModel->getConsultData($uid);
//                if(!empty($consult_arr)){
//                    foreach($consult_arr as $item){
//                        if($item['first_tag'])
//                            $tag_arr[$item['adddate']] = $item['first_tag'];
//                    }
//                }
//            }else{
//                if($mobile){
//                    $w['mobile'] = $mobile;
//                }else{
//                    $w['email'] = $email;
//                }
//
//                $consult = $this->SourceModel->getZiXunData('first_tag, adddate,id',$w,'',true,['adddate','asc']);
//                if(!empty($consult) && trim($consult['first_tag'])){
//                    $tag_arr[$consult['adddate']] = $consult['first_tag'];
//                }
//            }
//
//            if($mobile){
//                $cw = "tel='{$mobile}' and tag<>'' and tag is not null";
//            }else{
//                $cw = "email='{$email}' and tag<>'' and tag is not null";
//            }
//            $res = $this->SourceModel->getCrmData('FROM_UNIXTIME(dateline) as dateline,tag',$cw,'',true,['dateline','asc']);
//            if(!empty($res) && trim($res['tag'])){
//                $tag_arr[$res['dateline']] = $res['tag'];
//            }
//
//            if(!empty($consult)){
//                $other_zid = $this->AssignModel->getOtherZid($consult['id']);
//                if(!empty($other_zid)){
//                    $_consult_arr = $this->SourceModel->getZiXunData(
//                        'first_tag, adddate, mobile',
//                        'id in ('.implode('',$other_zid).')',
//                        'zid',
//                        '',
//                        ['adddate','asc']
//                    );
//                    if(!empty($_consult_arr)){
//                        foreach($_consult_arr as $_consult){
//                            if(trim($_consult['first_tag'])){
//                                $tag_arr[$_consult['adddate']] = $_consult['first_tag'];
//                            }
//                            if($_consult['mobile'] && $_consult['mobile']!=$consult['mobile']){
//                                $res = $this->SourceModel->getCrmData(
//                                    'FROM_UNIXTIME(dateline) as dateline,tag',
//                                    "tel='{$_consult['mobile']}' and tag<>'' and tag is not null",
//                                    '',
//                                    true,
//                                    ['dateline','asc']
//                                );
//                                if(!empty($res) && trim($res['tag'])){
//                                    $tag_arr[$res['dateline']] = $res['tag'];
//                                }
//                            }
//                        }
//                    }
//
//                }
//            }
//        }
//
//        if(empty($res) && $uid){
//            $res = $this->SourceModel->getCrmData(
//                'FROM_UNIXTIME(dateline) as dateline,tag',
//                "uid='{$uid}' and tag<>'' and tag is not null",
//                '',
//                true,
//                ['dateline','asc']
//            );
//            if(!empty($res) && trim($res['tag'])){
//                $tag_arr[$res['dateline']] = $res['tag'];
//            }
//        }
//        if(!empty($tag_arr)){
//            ksort($tag_arr);
//            return array_shift($tag_arr);
//        }
//        return '';
//    }

    /**
     * 获取黑名单
     * @param $id
     * @return mixed
     */
    public function getBlackList($id){
        if(empty($id)){ //取全部
            $where = 'id > 0';
            $blackList = ZdRedis::instance(false)->get('sourceBlackList');
            if(!empty($blackList)){
                $result = unserialize($blackList);
            }else{
                $result = $this->SourceModel->getBlackList($where,1);
                ZdRedis::instance(false)->set('sourceBlackList', serialize($result));
            }
        }else{  //取单条
            $where = 'id = '.$id;
            $result = $this->SourceModel->getBlackList($where,0);
        }
        logger::getInstance()->log('黑名单数据:'.json_encode($result));
        return $result;
    }

    /**
     * 获取crm数据
     * @param $param
     * @return mixed|null
     */
    public function getCrmData($param){
        $result = $this->SourceModel->getCrmDataByTime($param['start_time'], $param['end_time']);
        return (!empty($result))?json_encode($result):'';
    }

}