<?php
/**
 * Created by PhpStorm.
 * User: songwenyao
 * Date: 2019/1/24
 * Time: 9:16 AM
 */
namespace App\Domain\Market\Source;

use App\Domain\Member\MemberDomain;
use App\Domain\Sales\Customer\Consult\ConsultDomain;
use App\Domain\Sales\Customer\CustomerDomain;
use App\Domain\Sales\SalesmanDomain;
use App\Model\Common\User;
use App\Model\Market\Setting\IntoRuleModel;
use App\Model\Market\Source\CallInModel;
use App\Model\Market\Source\LevelOneModel;
use App\Model\Market\Source\MoveLogModel;
use App\Model\Market\Source\SourceModel;
use App\Model\Sales\Consult\ConsultModel;
use App\Model\Sales\Customer\CustomerModel;
use Base\BaseDomain;
use Base\Thrift;
use EasySwoole\Config;
use EasySwoole\Core\Component\Logger;

class CallInDomain extends BaseDomain
{
    private $customerDomain = null;
    private $levelOneModel = null;
    private $error_msg = '';

    function __construct()
    {
        $this->baseModel = new CallInModel();
        $this->customerDomain = new CustomerDomain();
        $this->levelOneModel = new LevelOneModel();
    }

    function getErrorMsg()
    {
        return $this->error_msg;
    }

    function assignCallIn($id_arr)
    {
        $data = $this->baseModel->getCallInData(['id'=>['in'=>$id_arr]],
            'id as call_id, lang_type, getinfo, uid, mobile, qq, wechat, memo, tag, adddate as dateline');
        $state = false;
        $msg_arr = [];
        if(!empty($data)){
            foreach($data as $item){
                if($this->assign($item)){
                    $state = true;
                }else{
                    $msg_arr[] = $this->getErrorMsg();
                }
            }
        }
        return [$state, $msg_arr];
    }

    function assign($data)
    {
        $error_msg = [
            '100'=>'未取到销售顾问',
            '101'=>'数据在跟，不重分',
            '102'=>'未知原因失败重分',
        ];

        //Logger::getInstance()->log(print_r($data));
        $consult = [];
        $consult_w = [];
        if($data['uid']){
            $consult = (new ConsultModel())->getConsult(['uid'=>$data['uid']], 'id, mobile, wechat, qq, business_type');
        }elseif($data['mobile']) $consult_w['mobile'] = $data['mobile'];
        elseif($data['qq']) $consult_w['qq'] = $data['qq'];
        elseif($data['wechat']) $consult_w['wechat'] = $data['wechat'];
        if(empty($consult)&&$consult_w){
            $consult = (new ConsultModel())->getConsult($consult_w, 'id, mobile, wechat, qq, business_type');
        }
        $getinfo = $this->getFirstType($data['lang_type'], $data['getinfo']);
        if(empty($consult)){
            //新
            $assign_sales_info = $this->baseModel->getCallInCC($data['lang_type'], $getinfo);
            if(!$this->getCallInCC($assign_sales_info,$data)) {
                if($data['data_id'])
                    $this->levelOneModel->setImport($data['data_id'], 0, $error_msg['100']);
                else $this->error_msg = $error_msg['100'];
                return false;
            }
            $assign_res = $this->customerDomain->addCustomer($assign_sales_info['salesman_uid'], $data['mobile'],
                $data['wechat'], $data['qq'], $data['tag'], $data['getinfo']);
            if($assign_res>0){
                $this->niceProcess($data, $assign_sales_info, $data['getinfo'], MoveLogModel::TYPE_LEYU,'new','您有一条新的乐语数据！');
                return true;
            }elseif($assign_res<0){
                if($data['data_id'])
                    $this->levelOneModel->setImport($data['data_id'], 1, $error_msg['101']."({$assign_sales_info['cc']})");
                else $this->error_msg = $error_msg['101']."({$assign_sales_info['cc']})";
                return false;
            }else{
                if($data['data_id'])
                    $this->levelOneModel->setImport($data['data_id'], 0, $error_msg['102']);
                else $this->error_msg = $error_msg['102'];
                return false;
            }
        }else{
            $CustomerModel = new CustomerModel();
            $consult['cid'] = $CustomerModel->getCidForZid($consult['id']);
            //看跟踪情况
            $follow = $CustomerModel->getCustomerFollow($consult['cid'], $data['lang_type']);
            if(empty($follow)){
                $assign_sales_info = $this->baseModel->getCallInCC($data['lang_type'], $getinfo);
                if(!$this->getCallInCC($assign_sales_info,$data)) {
                    if($data['data_id'])
                        $this->levelOneModel->setImport($data['data_id'], 0, $error_msg['100']);
                    else $this->error_msg = $error_msg['100'];
                    return false;
                }
                //手机号不同，添加关联
                if($data['mobile'] && $consult['mobile'] && $data['mobile']!=$consult['mobile']){
                    $this->addCustomer($data, $consult['id']);
                }
                //数据重分
                $assign_res = $this->customerDomain->moveTo($consult['cid'], $assign_sales_info['salesman_uid'], '', $data['getinfo']);
                if($assign_res>0){
                    $this->noticeBeforeSales($consult['business_type'], $data['lang_type'], $consult['cid']);
                    $this->niceProcess($data, $assign_sales_info, $data['getinfo'], MoveLogModel::TYPE_LEYU,'new','您有一条新的乐语数据！');
                    return true;
                }else{
                    if($data['data_id'])
                        $this->levelOneModel->setImport($data['data_id'], 0, $error_msg['102']);
                    else $this->error_msg = $error_msg['102'];
                    return false;
                }
            }elseif((new ConsultDomain())->consultExpire($consult['cid'], $consult['business_type'], $data['lang_type'])===1){
                $assign_sales_info = $this->baseModel->getCallInCC($data['lang_type'], $getinfo);
                if(!$this->getCallInCC($assign_sales_info,$data, $getinfo)) {
                    if($data['data_id'])
                        $this->levelOneModel->setImport($data['data_id'], 0, $error_msg['100']);
                    else $this->error_msg = $error_msg['100'];
                    return false;
                }
                //手机号不同，添加关联
                if($data['mobile'] && $consult['mobile'] && $data['mobile']!=$consult['mobile']){
                    $this->addCustomer($data, $consult['id']);
                }
                //数据重分
                $assign_res = $this->customerDomain->moveTo($consult['cid'], $assign_sales_info['salesman_uid'], '', $data['getinfo']);
                if($assign_res>0){
                    $this->noticeBeforeSales($consult['business_type'], $data['lang_type'], $consult['cid']);
                    $this->niceProcess($data, $assign_sales_info, $data['getinfo'], MoveLogModel::TYPE_LEYU_RENEW,'oc','您有一条新的乐语数据！');
                    return true;
                }else{
                    if($data['data_id'])
                        $this->levelOneModel->setImport($data['data_id'], 0, $error_msg['102']);
                    else $this->error_msg = $error_msg['102'];
                    return false;
                }
            }else{ //推送，包括跨业务保护期重启
                if($data['mobile'] && $consult['mobile'] && $data['mobile']!=$consult['mobile']){
                    $this->addCustomer($data, $consult['id']);
                }
                $salesman_uid = Thrift::getInstance()->service('User')->getUidByOpenId($follow['open_id']);
                //$salesman_uid = (new User())->getUserForOpenId($follow['open_id'])['uid'];
                $new_data = ['getinfo'=>$data['getinfo'], 'salesman_uid'=>$salesman_uid];
                if($consult['mobile']!=$data['mobile'] && $data['mobile']) $new_data['mobile'] = $data['mobile'];
                if($consult['qq']!=$data['qq'] && $data['qq']) $new_data['qq'] = $data['qq'];
                if($consult['wechat']!=$data['wechat'] && $data['wechat']) $new_data['wechat'] = $data['wechat'];
                $set_it = false;
                if($data['lang_type']=='jp' && isset($follow['is_order']) && $follow['is_order']){
                    $up_follow = $CustomerModel->getCustomerFollow($consult['cid'], 'up');
                    if(!empty($up_follow)){
                        $this->customerDomain->updateCustomer($consult['cid'], $new_data, 'up', $consult['business_type']);
                        $set_it = true;
                    }
                }
                if(!$set_it)
                    $this->customerDomain->updateCustomer($consult['cid'], $new_data, $data['lang_type'], $consult['business_type']);

                //插入网资数据
                if($data['data_id'])
                    $this->baseModel->addCallInData($data['lang_type'], $data['mobile'], $data['qq'], $data['wechat'],
                        $data['uid'], $data['getinfo'].'：'.$data['memo'].' '.$data['dateline'], $data['getinfo'], $data['tag'], date('Y-m-d H:i:s'), $consult['id']);
                elseif($data['call_id'])
                    $this->baseModel->updateCallIn($data['call_id'], ['assign_date'=>date('Y-m-d H:i:s')]);

                //记录分配记录
                (new MoveLogDomain())->addLog($salesman_uid, MoveLogModel::TYPE_LEYU_PUSH,
                    $data['getinfo'], 'oc', $consult['cid'], $data['tag']);
                //如果是留学，记录留学记录
                if($data['lang_type']=='os') (new MoveLogModel())->addMoveLogOs(MoveLogModel::TYPE_LEYU_PUSH, $follow['salesman'], $consult['id'], $data['tag']);
                //推送消息
                $this->noticeSales($salesman_uid, $consult['cid'], '您有一条新的乐语推送！');
                return true;
            }
        }

    }

    function addCustomer($data, $consult_id)
    {
        $this->customerDomain->_addCustomer([
            'mobile'=>$data['mobile'],
            'salesman_uid'=>$data['salesman_uid'],
            'getinfo'=>$data['getinfo'],
            'huifangdate'=>date('Y-m-d H:i:s'),
            'tag'=>$data['tag']
        ], false, false, $consult_id);
    }

    function getCallInCC($assign_sales_info, $data)
    {
        if(empty($assign_sales_info)){
            if(isset($data['data_id']))
                $this->levelOneModel->setImport($data['data_id'], 0, '未取到顾问重分');
            return false;
        }
        //判断sales是否在团队中
        list($dept_type, $team) = (new SalesmanDomain())->getSalesmanType($assign_sales_info['salesman_uid']);

        if(empty($team)){
            $this->levelOneModel->setImport($data['data_id'], 0, '顾问'.$assign_sales_info['cc'].'不在团队中，重分');
            return false;
        }
        return true;
    }

    function noticeBeforeSales($active_business_type, $business_type, $cid)
    {
        $open_rule = (new IntoRuleModel())->getOpenRule();
        if($active_business_type)
            $active_business_type = implode(',', $active_business_type);
        $is_transfer_cc = false;
        $lock_business_type = [];
        foreach($open_rule as $set){
            if($set['business_type']==$business_type && $set['is_lock']==1){
                $is_transfer_cc = true;
            }elseif($set['is_lock']==1 && in_array($set['business_type'], $active_business_type)){
                $lock_business_type[] = $set['business_type'];
            }
        }
        if($is_transfer_cc && $lock_business_type){
            $follow = (new CustomerModel())->getCustomerFollowIn($cid, $lock_business_type);
            if(!empty($follow)){
                $this->noticeSales(Thrift::getInstance()->service('User')->getUidByOpenId($follow['open_id'])
                    , $cid, '您有一条数据被转移，点击查看详情', 'transfer_cc');
            }
        }
    }

    function niceProcess($data, $assign_sales_info,
                         $getinfo, $log_type, $data_type, $message)
    {
        //清库
        if($data['mobile']) $this->setSourceClean($data['lang_type'], $data['mobile']);
        //为cc增长数量
        $this->baseModel->setCallInCCInc($assign_sales_info['id']);
        //插入网资数据
        if($data['data_id'])
            $this->baseModel->addCallInData($data['lang_type'], $data['mobile'], $data['qq'], $data['wechat'],
                $data['uid'], $getinfo.'：'.$data['memo'].' '.$data['dateline'], $getinfo, $data['tag'], date('Y-m-d H:i:s'), $this->customerDomain->cur_zid);
        elseif($data['call_id'])
            $this->baseModel->updateCallIn($data['call_id'], ['assign_date'=>date('Y-m-d H:i:s')]);
        //记录分配记录
        (new MoveLogDomain())->addLog($assign_sales_info['salesman_uid'], $log_type,
            $getinfo, $data_type, $this->customerDomain->cur_cid, $data['tag']);
        //如果是留学，记录留学记录
        if($data['lang_type']=='os') (new MoveLogModel())->addMoveLogOs(MoveLogModel::TYPE_LEYU_PUSH, $assign_sales_info['cc'], $this->customerDomain->cur_zid, $data['tag']);
        //推送消息
        $this->noticeSales($assign_sales_info['salesman_uid'], $this->customerDomain->cur_cid, $message);

    }

    function noticeSales($salesman_uid, $cid, $msg, $type='leyu')
    {
        list($dept_type, $team) = (new SalesmanDomain())->getSalesmanType($salesman_uid);
        $param = [
            'uid'=>$salesman_uid,
            'task_key'=>'leyu',
            'num'=>1,
            'link'=>Config::getInstance()->getConf('LINK_HOST_ZDMIS').'customer/info?cur_dept='.$team['team'].'&cid='.$cid,
            'msg'=>$msg
        ];
        (new MemberDomain())->sendUserTaskRemind($param);
    }

    function getFirstType($lang_type, $getinfo)
    {
        $callin_list = $this->baseModel->getCallInList('sat.id,sat.first_type,scst.id as sid,scst.second_type', [
            'sat.business_type'=>$lang_type
        ]);
        foreach ($callin_list as $key=>$item){
            if(in_array($getinfo, $item['second_type'])){
                return $item['first_type'];
            }
        }
        return $getinfo;
    }

    /**
     * 库处理，锁定及已分标记
     * @param $business_type
     * @param $mobile
     */
    function setSourceClean($business_type, $mobile)
    {
        $rule = (new IntoRuleModel())->getItem($business_type);
        if($rule['is_lock']){
            (new GenerateSourceDomain())->checkLock(['tel'=>$mobile]);
        }
        if($rule['is_open']){
            (new SourceModel())->setSourceAssigned($business_type, $mobile);
        }
    }
}