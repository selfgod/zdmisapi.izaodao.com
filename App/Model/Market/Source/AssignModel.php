<?php
namespace App\Model\Market\Source;

use Base\BaseModel;
use Base\Db;
use Base\Cache\ZdRedis;
use App\Model\Common\Permission;

class AssignModel extends BaseModel
{
    public $simple;
    public $Permission;
    public $SourceModel;
    public $common_member = 'jh_common_member';
    public $customer = 'zd_customer';
    public $zixun = 'zd_zixun';

    public function __construct()
    {
        $this->simple = new SimpleModel();
        $this->Permission = new Permission();
        $this->SourceModel = new SourceModel();
    }
    /**
     * 通过uid或username获取open id
     * @param int or string $uid or $username
     * @return mixed
     */
    public function getUserOpenID($uid)
    {
        if (is_numeric($uid)) {
            $w['uid'] = $uid;
        } else {
            $w['username'] = $uid;
        }
        return $this->simple->simpleSelect($this->common_member, 'open_id', $w, 'open_id');
    }

    /**
     * 添加用户关联
     * @param $zid
     * @param string $other_zid
     * @return bool|mixed
     */
    function addCustomerRelation($zid, $other_zid=''){
        $acid = $this->simple->simpleSelect($this->customer, 'cid', ['zid'=>$zid], 'cid', '', ['cid','desc']);
        if($other_zid){
            $bcid = $this->simple->simpleSelect($this->customer, 'cid', ['zid'=>$other_zid], 'cid', '', ['cid','desc']);
            if($acid && $acid==$bcid) return true;
            if(($acid && $bcid)){
                $r = $this->simple->simpleUpdate($this->customer, ['cid'=>$bcid], ['zid'=>$zid]);
                return $r;
            }elseif(!$acid && $bcid){
                $r = $this->simple->simpleInsert($this->customer, ['cid'=>$bcid,'zid'=>$zid]);
                return $r;
            }elseif($acid && !$bcid){
                $r = $this->simple->simpleInsert($this->customer, ['cid'=>$acid,'zid'=>$other_zid]);
                return $r;
            }else{
                //$this->execute('LOCK TABLES zd_customer WRITE');
                $cid = $this->getCidIncr();
                $zid_add_date = $this->simple->simpleSelect($this->zixun, 'cid', ['id'=>$zid], 'adddate');
                $other_zid_add_date = $this->simple->simpleSelect($this->zixun, 'cid', ['id'=>$other_zid], 'adddate');
                if($zid_add_date<$other_zid_add_date){
                    $r1 = $this->simple->simpleInsert($this->customer, ['cid'=>$cid,'zid'=>$zid,'add_date'=>$zid_add_date]);
                    $r2 = $this->simple->simpleInsert($this->customer, ['cid'=>$cid,'zid'=>$other_zid]);
                }else{
                    $r1 = $this->simple->simpleInsert($this->customer, ['cid'=>$cid,'zid'=>$zid]);
                    $r2 = $this->simple->simpleInsert($this->customer, ['cid'=>$cid,'zid'=>$other_zid,'add_date'=>$zid_add_date]);
                }
                //$this->execute('UNLOCK TABLES');
                return $r1 && $r2;
            }
        }else{
            if($acid) return false;
            $cid = $this->getCidIncr();
            $zid_add_date = $this->simple->simpleSelect($this->zixun, 'adddate', ['id'=>$zid], 'adddate');
            $r = $this->simple->simpleInsert($this->customer, ['cid'=>$cid,'zid'=>$zid,'add_date'=>$zid_add_date]);
            return $r;
        }
    }

    /**
     * 获取Cid
     * @return mixed
     */
    function getCidIncr(){
        $cid = ZdRedis::instance()->get('zd_customer_cid_incr');
        if(!$cid){
            $query = 'select cid from zd_customer GROUP BY cid ORDER BY cid DESC limit 1';
            $customer =  Db::slave('zd_class')->query($query);
            $cid = $customer[0]['cid'];
            ZdRedis::instance()->set('zd_customer_cid_incr', $cid, 3600*24*30);
            return ZdRedis::instance()->incr('zd_customer_cid_incr');
        }else{
            return ZdRedis::instance()->incr('zd_customer_cid_incr');
            //$redis->get('zd_customer_cid_incr');
        }
    }

    /**
     * 获取其他zid
     * @param $zid
     * @param array $except
     * @return array|mixed
     */
    function getOtherZid($zid, $except=[]){
        $cid = $this->SourceModel->getCustomData('cid',['zid'=>$zid],'cid');
        if(!$cid) return [];
        $except[] = $zid;
        $res = $this->SourceModel->getCustomData(
            'zid',
            'cid = '.$cid.' and zid not in ('.implode('',$except).')',
            'zid'
        );
        if(!empty($res)){
            return $this->Permission->array_value_recursive('zid', $res);
        }
        return [];
    }
}