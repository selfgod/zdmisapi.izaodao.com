<?php
/**
 * Created by PhpStorm.
 * User: aramis
 * Date: 2019-02-13
 * Time: 19:39
 */
namespace App\Model\Order;

use Base\BaseModel;
use Base\Db;

class OrderModel extends BaseModel
{
    /**
     * 获取订单详细信息
     * @param $orderId
     * @return array
     */
    public function getOrderInfo($orderId)
    {
        $data = Db::slave('zd_class')->select('*')
            ->from('netschool_pay_order')
            ->where('po_sid=:orderId and is_delete=0')
            ->bindValue('orderId', $orderId)->row();
        return $data ?: [];
    }

    /**
     * 获取改价订单对象
     * @param $po_sid
     * @return array
     */
    public function getModifyOrderInfo($po_sid)
    {
        $data = Db::slave('zd_class')->select('o.*,oc.manjian_limit_price,oc.manjian_discount_price,
        oc.xufei_discount_price,oc.youhui_card_price,oc.liquan_card_price,oc.recharge_give_num,
        oc.upgrade_discount_price,oc.off_price,oc.cardpay_discount_price')
            ->from('netschool_pay_order as o')
            ->leftJoin('netschool_pay_order_coupon as oc', 'o.po_sid=oc.po_sid')
            ->where('o.po_sid = :po_sid')
            ->bindValue('po_sid', $po_sid)
            ->orderByDESC(['o.po_submittime'])
            ->row();
        return $data;
    }

    public function modifyOrderPrice($oid, $price, $orderPrice)
    {
        $ret = Thrift::getInstance()->service('Order')->modifyOrderInfo($oid, intval($price), intval($orderPrice));
        return $ret->code === 200 ? TRUE : FALSE;
    }

    /**
     * 创建订单
     * @param array $save
     * @return boolean
     */
    public function insertrder(array $data)
    {
        return Db::master('zd_class')->insert('netschool_pay_order')->cols($data)->query();
    }

    /**
     * 更新订单
     * @param $where
     * @param array $bindValues
     * @param array $save
     * @return boolean
     */
    public function updateOrder($where, array $bindValues, array $save)
    {
        return Db::master('zd_class')->update('netschool_pay_order')
            ->where($where)->bindValues($bindValues)
            ->cols($save)->query();
    }
}