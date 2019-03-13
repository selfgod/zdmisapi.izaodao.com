<?php
/**
 * 客户
 * Created by PhpStorm.
 * User: songwenyao
 * Date: 2018/11/20
 * Time: 下午2:10
 */
namespace App\Domain\Sales\Customer;

use App\Domain\Market\Source\MoveLogDomain;
use App\Domain\Market\Source\SourceDomain;
use App\Domain\Sales\Setting\TeamStructureDomain;
use App\Model\Common\User;
use App\Model\Market\Setting\IntoRuleModel;
use App\Model\Market\Source\MoveLogModel;
use App\Model\Market\Source\SourceOcModel;
use App\Model\Sales\Consult\ConsultModel;
use App\Model\Sales\Customer\CustomerModel;
use Base\BaseDomain;
use Base\Cache\ZdRedis;
use Base\Helper\ArrayHelper;
use Base\Thrift;

class CustomerDomain extends BaseDomain
{
    public $cur_cid = null;
    public $cur_zid = null;

    function __construct()
    {
        $this->baseModel = new CustomerModel();
    }

    /**
     * 数据易主
     * @param $cid
     * @param $new_sales_uid
     * @param string $huifangdate
     * @param string $getInfo
     * @return bool|mixed
     */
    function moveTo($cid, $new_sales_uid, $huifangdate='', $getInfo='')
    {
        $this->cur_cid = $cid;
        $huifangdate = $huifangdate?$huifangdate:date('Y-m-d H:i:s');
        $business_type = (new TeamStructureDomain())->getBusinessTypeForSalesman($new_sales_uid);
        //调库，如果是日语，并且是报名数据，自动转到续费业务
        //判断是否报名
        $zid_arr = $this->baseModel->getZidForCid($cid);
        $ConsultModel = new ConsultModel();
        $this->cur_zid = $zid_arr[0];
        $had_business_type = $ConsultModel->getBusinessType($zid_arr[0]);
        if($business_type=='jp'){
            $follow = $this->baseModel->getCustomerFollow($cid, $business_type);
            if(!empty($follow) && $follow['is_order']){
                $this->setCustomerFollow($cid, 'up', $new_sales_uid, $huifangdate, $getInfo);

                $reject_business_type = $this->businessTypeReject('up', $had_business_type );

                return $ConsultModel->updateConsult(['id'=>['in'=>$zid_arr]], ['business_type'=>$reject_business_type, 'updatedate'=>date('Y-m-d H:i:s')]);
            }
        }
        $r = $this->setCustomerFollow($cid, $business_type, $new_sales_uid, $huifangdate, $getInfo);
        $reject_business_type = $this->businessTypeReject($business_type, $had_business_type );
        $ConsultModel->updateConsult(['id'=>['in'=>$zid_arr]], ['business_type'=>$reject_business_type, 'updatedate'=>date('Y-m-d H:i:s')]);
        return $r;
    }

    //检测业务类型排它
    //如果要设置的业务是排他的，那将清除已有业务的排他业务，否则直接加入即可
    function businessTypeReject($set_business_type, $had_business_type){
        if($set_business_type==$had_business_type || $had_business_type=='') return $set_business_type;
        $into_set = (new IntoRuleModel())->getIntoSetLockBusinessType(true);
        $business_type_lock_arr = ArrayHelper::array_key_value('business_type', 'is_lock', $into_set);
        $had_business_type_arr = explode(',', $had_business_type);
        if(isset($business_type_lock_arr[$set_business_type]) && $business_type_lock_arr[$set_business_type]){
            foreach($had_business_type_arr as $k=>$type){
                if(isset($business_type_lock_arr[$type]) && $business_type_lock_arr[$type]){
                    unset($had_business_type_arr[$k]);
                }
            }
        }
        $had_business_type_arr[] = $set_business_type;
        return implode(',', array_unique($had_business_type_arr));
    }

    function addCustomer($salesman_uid, $mobile='', $wechat='', $qq='', $tag='', $getinfo='', $huifangdate='')
    {
        if(empty($mobile) && empty($wechat) && empty($qq)) return false;
        $data = [
            'salesman_uid'=>$salesman_uid,
            'mobile'=>$mobile,
            'wechat'=>$wechat,
            'qq'=>$qq,
            'tag'=>$tag,
            'getinfo'=>$getinfo,
            'huifangdate'=>$huifangdate?$huifangdate:date('Y-m-d H:i:s'),
        ];
        return $this->_addCustomer($data);
    }

    function updateCustomer($cid, $data, $business_type, $active_business_type)
    {
        if($data['getinfo']){
            $data['is_callin'] = 1;
        }
        $data['business_type'] = $this->businessTypeReject($business_type, $active_business_type);
        $ConsultModel = new ConsultModel();
        $salesman_uid = $data['salesman_uid'];
        unset($data['salesman_uid']);
        $r1 = $ConsultModel->updateConsult(['id'=>['in'=>$this->baseModel->getZidForCid($cid)]], $data);
        return $this->setCustomerFollow($cid, $business_type, $salesman_uid, '', $data['getinfo']);
    }

    /**
     * @param array $data
     * @param bool $insert_crm
     * @param bool $insert_log
     * @param int $relation_id
     * @return int >0 成功 0 失败 -1 已存在
     * @throws \Exception
     */
    function _addCustomer($data=array(), $insert_crm = false, $insert_log=false, $relation_id=0){
        //判断是否已存在
        $w = [];
        if($data['mobile']) $w['mobile'] = $data['mobile'];
        elseif($data['wechat']) $w['wechat'] = $data['wechat'];
        elseif($data['qq']) $w['qq'] = $data['qq'];
        $consult_item = (new ConsultModel())->getConsult($w);

        //获取并设置bussiness_type
        //根据销售获取业务类型
        if(!isset($data['business_type']))
            $data['business_type'] = (new TeamStructureDomain())->getBusinessTypeForSalesman($data['salesman_uid']);

        if(!empty($consult_item)){
            $this->cur_zid = $consult_item['id'];
            $cid = $this->baseModel->getCidForZid($consult_item['id']);
            if(empty($cid)){
                if($this->addCustomerRelation($consult_item['id'])){
                    //保存销售顾问
                    $this->cur_cid = $cid;
                    return (int)$this->setCustomerFollow(
                        $this->baseModel->getCidForZid($consult_item['id']),
                        $data['business_type'], $data['salesman_uid'],
                        $data['huifangdate'], $data['getinfo']);
                }
            }
            return -1;
        }

        $data['adddate'] = $data['updatedate'] = date('Y-m-d H:i:s');

        if(isset($data['mobile'])){
            $first_tag = (new SourceDomain())->getFirstTag($data['mobile']);
            $data['first_tag'] = $first_tag?$first_tag:$data['tag'];
        }
        if($data['getinfo']){
            $data['is_callin'] = 1;
        }
        $salesman_uid = $data['salesman_uid'];
        unset($data['salesman_uid']);
        $ConsultModel = new ConsultModel();
        $r1 = $ConsultModel->addConsult($data);
        //插入分配记录
        if($r1){
            $this->cur_zid = $r1;
            //保存tag
            $ConsultModel->addTag($r1, $data['business_type'], $data['tag']);

            //添加customer
            if($relation_id){
                $cid = $this->addCustomerRelation($r1, $relation_id);
            }else{
                $cid = $this->addCustomerRelation($r1);
            }
            $this->cur_cid = $cid;

            if($insert_log){
                (new MoveLogDomain())->addLog($salesman_uid, MoveLogModel::TYPE_MANUAL, '', 'new',
                    $cid, $data['tag']);
            }

            //保存销售顾问
            return $this->setCustomerFollow($cid, $data['business_type'], $salesman_uid, $data['huifangdate'], $data['getinfo']);
        }

        return 0;
    }

    function addCustomerRelation($zid, $other_zid='')
    {
        if($other_zid){
            $acid = $this->baseModel->getCidForZid($zid);
            $bcid = $this->baseModel->getCidForZid($other_zid);
            if($acid && $acid==$bcid) return true;
            if(($acid && $bcid)){
                $r = $this->baseModel->setCustomer(['zid'=>$zid], ['cid'=>$bcid]);
                return $bcid;
            }elseif(!$acid && $bcid){
                $r = $this->baseModel->addCustomer($bcid, $zid);
                return $bcid;
            }elseif($acid && !$bcid){
                $r = $this->baseModel->addCustomer($acid, $other_zid);
                return $acid;
            }else{
                $cid = $this->getCidIncr();
                $ConsultModel = new ConsultModel();
                $zid_add_date = $ConsultModel->getConsult(['id'=>$zid],'adddate')['adddate'];
                $other_zid_add_date = $ConsultModel->getConsult(['id'=>$other_zid], 'adddate')['adddate'];
                if(strtotime($zid_add_date)<strtotime($other_zid_add_date)){
                    $r1 = $this->baseModel->addCustomer($cid, $zid, $zid_add_date);
                    $r2 = $this->baseModel->addCustomer($cid, $other_zid);
                }else{
                    $r1 = $this->baseModel->addCustomer($cid, $zid);
                    $r2 = $this->baseModel->addCustomer($cid, $other_zid, $zid_add_date);
                }
                return $cid;
            }
        }else{
            $acid = $this->baseModel->getCidForZid($zid);
            if($acid) return $acid;
            $cid = $this->getCidIncr();
            $ConsultModel = new ConsultModel();
            $zid_add_date = $ConsultModel->getConsult(['id'=>$zid],'adddate')['adddate'];
            $r = $this->baseModel->addCustomer($cid, $zid, $zid_add_date);
            return $cid;
        }
    }

    function getCidIncr()
    {
        $cid = ZdRedis::instance(false)->get('zd_customer_cid_incr');
        if(!$cid){
            $cid = $this->baseModel->getLatestCid();
            ZdRedis::instance(false)->set('zd_customer_cid_incr', $cid, 3600*24*30);
            return ZdRedis::instance(false)->incr('zd_customer_cid_incr');
        }else{
            return ZdRedis::instance(false)->incr('zd_customer_cid_incr');
        }
    }

    /**
     * @param $cid
     * @param $business_type
     * @param $sales_id open_id
     * @param string $huifang_date
     * @param string $getinfo
     * @return mixed
     */
    protected $lock_arr = null;
    function setCustomerFollow($cid, $business_type, $sales_id, $huifang_date='', $getinfo='')
    {
        $user_info = Thrift::getInstance()->service('User')->getUserByUid($sales_id);
        $data = [
            'salesman'=>$user_info['username'],
            'open_id'=>$user_info['open_id'],
            'huifangdate'=>$huifang_date?$huifang_date:date('Y-m-d H:i:s'),
            'is_oc'=>0,
            'call_state'=>0,
            'oc_lock'=>0,
            'is_follow'=>1
        ];
        if($getinfo){
            $data['getinfo'] = $getinfo;
        }
        //设置当前业务为非oc
        $if_had = $this->baseModel->getCustomerFollow($cid, $business_type);
        //查找是否存在
        if(!empty($if_had)){
            if($if_had['is_oc']){
                if($this->lock_arr===null){
                    $into_set = (new IntoRuleModel())->getIntoSetLockBusinessType(true);
                    //独占的回收业务类型
                    $lock_arr = [];
                    foreach($into_set as $set){
                        if($set['is_lock'] && $set['is_retrieve']){
                            $lock_arr[] = $set['business_type'];
                        }
                    }
                    $this->lock_arr = $lock_arr;
                }
                //如果当前是独占业务，锁定其他oc的独占业务
                if(in_array($business_type, $this->lock_arr)){
                    $this->baseModel->setCustomerFollow([
                        'cid'=>$cid,
                        'is_oc'=>1,
                        'business_type'=>['in'=>$this->lock_arr]
                    ], ['oc_lock'=>1]);
                }
            }

            $res = $this->baseModel->setCustomerFollow(['cid'=>$cid,'business_type'=>$business_type], $data);
            if($business_type=='jp'){
                //查询续费
                $if_had = $this->baseModel->getCustomerFollow($cid, 'up');
                if(!empty($if_had)){
                    $res = $this->baseModel->setCustomerFollow(['cid'=>$cid,'business_type'=>'up'], $data);
                }
            }elseif($business_type=='up'){
                $res = $this->baseModel->setCustomerFollow(['cid'=>$cid,'business_type'=>'jp'], $data);
            }

            //删除oc数据，如果所有业务都没有oc，则删掉oc
            $oc_count = $this->baseModel->_getCustomerFollow(['cid'=>$cid, 'is_oc'=>1], 'count(id) as count')['count'];
            if($oc_count<=0){
                (new SourceOcModel())->deleteOc(['cid'=>$cid]);
            }else{
                (new SourceOcModel())->updateOc(['cid'=>$cid], ['admin'=>'', 'admintime'=>0]);
            }

            return $res;
        }else{
            $data['cid'] = $cid;
            $data['business_type'] = $business_type;
            $data['assign_date'] = date('Y-m-d H:i:s');
            return $this->baseModel->addCustomerFollow($data);
        }
    }
}