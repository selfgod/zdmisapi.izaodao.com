<?php
/**
 * 咨询
 * Created by PhpStorm.
 * User: songwenyao
 * Date: 2018/11/13
 * Time: 上午11:16
 */

namespace App\Domain\Sales\Customer\Consult;

use App\Domain\Sales\SalesmanDomain;
use App\Model\Common\User;
use App\Model\Market\Setting\IntoRuleModel;
use App\Model\Market\Source\MoveLogModel;
use App\Model\Sales\Consult\ConsultLogModel;
use App\Model\Sales\Consult\MemoModel;
use App\Model\Sales\Customer\CustomerModel;
use App\Model\Sales\Team\SalesmanModel;
use Base\BaseDomain;
use Base\Thrift;
use EasySwoole\Core\Component\Logger;

class ConsultDomain extends BaseDomain
{
    function setOperateLog($params)
    {
        $ConsultLogModel = new ConsultLogModel();
        $ConsultLogModel->clearAlertTime($params['cid'], $params['act']);
        $res = $ConsultLogModel->insertLog($params['act'], $params['content'],
            $params['cid'], isset($params['operator'])?$params['operator']:$params['userInfo']['user_name'], isset($params['alert_time'])?strtotime($params['alert_time']):0);
        if($res) return true;
        return false;
    }

    /**
     * 数据咨询有效期，没有cid直接过期
     * @param int $cid
     * @param string $active_business_type
     * @param string $dst_business_type
     * @return bool 1:过期, 2跨业务保护期内, 0:保护期内
     */
    function consultExpire($cid, $active_business_type, $dst_business_type)
    {
        //激活业务空，过期
        if(!$active_business_type) return 1;
        if($dst_business_type=='up') $dst_business_type='jp';
        $CustomerModel = new CustomerModel();
        $customer_follow = $CustomerModel->getCustomerFollow($cid, $dst_business_type);
        //Logger::getInstance()->log('有效期判断：'."cid:{$cid},active_business:{$active_business_type},dst_business:{$dst_business_type}");
        //Logger::getInstance()->log(print_r($customer_follow, true));
        //报名数据，保护期内
        if(!empty($customer_follow) && $customer_follow['is_order'])
            return 0;

        //跨业务
        if(strpos($active_business_type, $dst_business_type)===false) {
            $now_business_type = explode(',', $active_business_type);
            $lock_business_type = (new IntoRuleModel())->getIntoSetLockBusinessType();
            foreach($now_business_type as $business_type){
                if(!in_array($business_type, $lock_business_type)) continue;
                $follow = $CustomerModel->getCustomerFollow($cid, $business_type);
                //如果不跟踪，或oc则过期，否则不过期
                if(empty($follow)) continue;
                if($follow['is_follow']==0 || $follow['is_oc']==1 || strtotime($follow['assign_date'])<=0)
                    continue;
                $move_log = (new MoveLogModel())->getLatestLog($cid);
                if(!empty($move_log)){
                    if(in_array($move_log['type'], ['new_direct','new','action_m'])){
                        if(time()-strtotime($move_log['dateline'])<=3600){
                            return 2;
                        }
                    }
                    if(in_array($move_log['type'], ['leyu','leyu_push','leyu_renew','leyu_push_tech','leyu_push_rc','leyu_push_osc'])){
                        if(time()-strtotime($move_log['dateline'])<=3600+30*60){
                            return 2;
                        }
                    }
                }
                //看touch
                if($this->isRealFollow($cid, $follow)){
                    return 2;
                }
            }
            return 1;
        }

        $move_log = (new MoveLogModel())->getLatestLog($customer_follow['cid']);
        if(!empty($move_log)){
            if(in_array($move_log['type'], ['new_direct','action_m','oc_direct'])){
                if(time()<strtotime(date('Y-m-d 23:59:59', strtotime($move_log['dateline'])))){
                    return 0;
                }
            }
        }

        //不是cc的跟踪，返回过期，oc数据 返回过期
        if(!empty($customer_follow) && ($customer_follow['salesman']=='不跟踪' ||
                $customer_follow['is_follow']==0 ||
                $customer_follow['is_oc']==1 ) && !$customer_follow['is_order']) {
            Logger::getInstance()->log('不跟踪或已报名判断ok('.$cid.')');
            return 1;
        }

        //离职顾问数据返回过期
        if(!empty($customer_follow)) {
            if((new SalesmanModel())->isResign($customer_follow['salesman'])) {
                Logger::getInstance()->log('已离职判断ok('.$cid.')：'.$customer_follow['salesman']);
                return 1;
            }
        }

        if($customer_follow['getinfo']){
            $move_log = (new MoveLogModel())->getLatestLog($cid);
            if(!empty($move_log)){
                if(in_array($move_log['type'], ['leyu','leyu_push','leyu_renew','leyu_push_tech','leyu_push_rc','leyu_push_osc'])){
                    if(time()-strtotime($move_log['dateline'])<=3600+30*60){
                        return 0;
                    }else{
                        if($customer_follow['is_order']){
                            return 0;
                        }else{
                            return (int)!$this->isRealFollow($cid, $customer_follow,  date('Y-m-d 23:59:59', strtotime('-15 day')));

                        }
                    }
                }
            }
        }

        //没报名的数据看有效touch，没有有效touch 过期
        return (int)!$this->isRealFollow($cid, $customer_follow);
    }

    function isRealFollow($cid, $follow, $move_date='')
    {
        $consult_ids = (new CustomerModel())->getZidForCid($cid);
        foreach($consult_ids as $id){
            if($this->_isRealFollow($id, $follow, $move_date)){
                return true;
            }
        }
        return false;
    }

    static $is_touch = null;
    function _isRealFollow($zid, $follow, $move_date='')
    {
        self::$is_touch = false;
        if(empty($follow)) return false;
        $end_date = date('Y-m-d H:i:s');
        $start_date = date('Y-m-d 23:59:59', strtotime('-15 day'));
        $today = date('Y-m-d 00:00:00');
        //最后一次分配时间小于14用14，否则不用14
        list($dept_type, $my_team) = (new SalesmanDomain())->getSalesmanType(
           Thrift::getInstance()->service('User')->getUidByOpenId($follow['open_id'])

        );
        if(empty($dept_type)) {
            if((new MemoModel())->isMemoStuff($zid, $follow['salesman'])){
                self::$is_touch = true;
            }
            return false;
        }

        //判断当前语种有没有被touch过
        if((new MemoModel())->isMemoDeptType($zid, $dept_type)){
            self::$is_touch = true;
        }
        if($move_date===''){

            $w = array(
                'fid'=>$zid,
                'stuff'=>$follow['salesman'],
                'dept_type'=>$dept_type,
                'adddate'=>['>='=>$today, '<='=>$end_date],
                'method'=>['in'=>['接通','呼入','呼出','微信/QQ']],
            );

            $res = (new MemoModel())->isMemo($w);

            if($res){
                $w = array(
                    'fid'=>$zid,
                    'dept_type'=>$dept_type,
                    'stuff'=>$follow['salesman'],
                    'adddate'=>['>='=>$today, '<='=>$end_date],
                    'memostate'=>['<>'=>'不跟踪'],
                    'method'=>['in'=>['接通','呼入','呼出','微信/QQ']],
                );

                $res = (new MemoModel())->isMemo($w);
                return $res;
            }else{
                $w = array('zid'=>$zid,'type'=>['in'=>['new_direct','new','direct','oc','oc_direct']]);
                $move_arr = (new MoveLogModel())->queryMoveLog('dateline,cc', $w);
                if(!empty($move_arr)){
                    if($move_arr['dateline']>=$today){
                        return true;
                    }
                }
            }

        }else{
            $start_date = $move_date;
        }

        $w = array(
            'fid'=>$zid,
            'dept_type'=>$dept_type,
            'stuff'=>$follow['salesman'],
            'adddate'=>['>='=>$start_date, '<='=>$end_date],
            'memostate'=>['<>'=>'不跟踪'],
            'method'=>['in'=>['接通','呼入','呼出','微信/QQ']],
        );

        $res = (new MemoModel())->isMemo($w);
        return $res;
    }
}