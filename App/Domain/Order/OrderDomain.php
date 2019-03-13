<?php
/**
 * Created by PhpStorm.
 * User: aramis
 * Date: 2019-02-13
 * Time: 19:38
 */
namespace App\Domain\Order;

use Base\BaseDomain;
use App\Model\Order\OrderModel;
use Base\Db;
use EasySwoole\Core\Component\Logger;

class OrderDomain extends BaseDomain
{

    /**
     * @param $oid
     * @param $earnestOrder
     * @param $balanceOrder
     * @return boolean
     */
    public function splitOrder($oldOrder, $earnestOrder)
    {
        $orderModel = new OrderModel();
        $OrderInfo = $orderModel->getOrderInfo($oldOrder['orderNum']);
        if(!empty($OrderInfo)){
            $OrderInfo = array_filter($OrderInfo, function($v){return !($v===false||$v===''||is_null($v));});
            $OrderInfo['exp_time'] = $oldOrder['expireTimeString'];
            //清除订单金额商品优惠等信息
            $OrderInfo['po_in'] = 0;
            $OrderInfo['po_out'] = 0;
            $OrderInfo['pay_price'] = 0;
            $OrderInfo['rate_price'] = 0;
            $OrderInfo['original_price'] = 0;
            $OrderInfo['po_submittime'] = $oldOrder['createTimeString'];
            $OrderInfo['card_id'] = 0;
            $OrderInfo['coupon_id'] = 0;
            $OrderInfo['use_zy'] = 0;
            $OrderInfo['goods_id'] = 0;
            $OrderInfo['schedule_id'] = 0;
            $OrderInfo['origin_goods_id'] = 0;
            $earnest = $OrderInfo;

            //定金单订单信息
            $earnest['po_sid'] = $earnestOrder['orderNum'];
            $earnest['order_branch'] = 2;
            $earnest['branch_price'] = $earnestOrder['salePrice'];
            $earnest['mother_oid'] = $oldOrder['orderNum'];
            $status = FALSE;
            Db::master('zd_class')->beginTrans();
            try {
                $orderModel->insertrder($earnest);
                $orderModel->updateOrder('po_sid=:po_sid', ['po_sid' => $oldOrder['orderNum']], [
                    'order_branch' => 4,
                    'exp_time' => $oldOrder['expireTimeString']
                ]);
                Db::master('zd_class')->commitTrans();
                $status = TRUE;
            } catch (\Exception $e) {
                //事务回滚
                Db::master('zd_class')->rollBackTrans();
                Logger::getInstance()->log('OrderDomain\splitOrder ERROR:' . $e->getMessage() .
                    '\n params:' . \GuzzleHttp\json_encode($OrderInfo) .
                    \GuzzleHttp\json_encode($oldOrder) .
                    \GuzzleHttp\json_encode($earnestOrder)
                );
            }
        }else{
            Logger::getInstance()->log('OrderDomain\splitOrder ERROR: 订单号不存在，订单信息:' . \GuzzleHttp\json_encode($oldOrder) );
        }

        return $status;
    }
}