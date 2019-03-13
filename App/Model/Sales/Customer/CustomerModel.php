<?php
/**
 * 客户
 * Created by PhpStorm.
 * User: songwenyao
 * Date: 2018/11/13
 * Time: 上午11:11
 */

namespace App\Model\Sales\Customer;

use App\Model\Market\Source\IntoRule;
use Base\BaseModel;
use Base\Db;
use Base\Helper\ArrayHelper;

class CustomerModel extends BaseModel
{
    protected $customer_table = 'zd_customer';
    protected $customer_follow_table = 'zd_customer_follow';

    /**
     * @param $cid
     * @param string $business_type
     * @return mixed 二维或一维数组
     */
    function getCustomerFollow($cid, $business_type='')
    {

        $condition['cid'] = $cid;
        if($business_type){
            $condition['business_type'] = $business_type;
        }
        return $this->_getCustomerFollow($condition);
    }

    function _getCustomerFollow($condition, $field='*')
    {
        $this->sWhereClean();
        $this->setSqlWhereAnd($condition);
        $res = Db::slave('zd_class')->select($field)
            ->from($this->customer_follow_table)
            ->where($this->sWhere)
            ->bindValues($this->sBindValues)
            ->query();
        if(!empty($res) && count($res)==1) return $res[0];
        return $res;
    }

    function setCustomer($condition, $data)
    {
        $this->sWhereClean();
        $this->setSqlWhereAnd($condition);
        $res = $this->updateData($this->customer_table, $data);
        return $res;
    }

    function setCustomerFollow($condition, $data)
    {
        $this->sWhereClean();
        $this->setSqlWhereAnd($condition);
        $res = $this->updateData($this->customer_follow_table, $data);
        return $res;
    }

    function addCustomerFollow($data)
    {
        return $this->insertTable($this->customer_follow_table, $data, 'zd_class');
    }

    function addCustomer($cid, $zid, $adddate=null){
        return $this->insertTable($this->customer_table, [
            'zid'=>$zid,
            'cid'=>$cid,
            'add_date'=>$adddate
        ], 'zd_class');
    }

    function getCustomerFollowIn($cid, $business_type=[])
    {
        $this->sWhereClean();
        $condition['cid'] = $cid;
        $condition['is_follow'] = 1;
        $condition['is_oc'] = 0;
        if($business_type){
            $condition['business_type'] = ['in'=>$business_type];
        }
        $this->setSqlWhereAnd($condition);
        $res = Db::slave('zd_class')->select('*')
            ->from($this->customer_follow_table)
            ->where($this->sWhere)
            ->bindValues($this->sBindValues)
            ->query();
        return $res;
    }

    function getCidForZid($zid)
    {
        $res = Db::master('zd_class')->select('cid')
            ->from($this->customer_table)
            ->where('zid=:zid')
            ->bindValue('zid', $zid)
            ->single();
        return $res;
    }

    function getZidForCid($cid)
    {
        $res = Db::slave('zd_class')->select('zid')
            ->from($this->customer_table)
            ->where('cid=:cid')
            ->bindValue('cid', $cid)
            ->query();
        return ArrayHelper::array_value_recursive('zid', $res);
    }

    function getLatestCid()
    {
        $res = Db::slave('zd_class')->select('cid')
            ->from($this->customer_table)
            ->orderByDESC(['cid'])
            ->single();
        return $res;
    }


}