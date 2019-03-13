<?php
/**
 * Created by PhpStorm.
 * User: dean
 * Date: 2019/2/15
 * Time: 上午10:20
 */

namespace App\Domain\Order\Modify;

use Base\BaseDomain;
use App\Model\Order\OrderModel;
use App\Model\Common\User;

class OrderModifyDomain extends BaseDomain
{
    public function modifyPrice($params) {
        $validate = true;
        if ($po_sid = $params['po_sid']) {
        } else {
            $res = array("state" => 501, "msg"=>"订单编号不能未空！！！", "value"=>[]);
            $validate = false;
        }
        $new_price = $params['new_price'];
        if (isset($new_price)) {
            if ($new_price < 0) {
                $res = array("state" => 501, "msg"=>"改价金额不能小于0！！！", "value"=>[]);
                $validate = false;
            }
        } else {
            $res = array("state" => 501, "msg"=>"改价金额为必填项！！！", "value"=>[]);
            $validate = false;
        }
        if ($old_price = $params['old_price']) {
        } else {
            $res = array("state" => 501, "msg"=>"请查看原订单金额，原订单金额为空！！！", "value"=>[]);
            $validate = false;
        }
        if ($reason = $params['reason']) {
        } else {
            $res = array("state" => 501, "msg"=>"改价原因为必填项！！！", "value"=>[]);
            $validate = false;
        }
        if ($params['operator_is_cc']) {
            if ($old_price < $new_price || ($old_price - $new_price) > 100) {
                $res = array(
                    "state" => 502,
                    "msg"=>"CC只有100元当配的改价权限，如需特殊处理，请联系技术支持。！！！",
                    "value"=>array("cur_price" => $old_price - 100)
                );
                $validate = false;
            }
        }
        if ($validate) {
            $orderModel = new OrderModel();
            $row = $orderModel->getModifyOrderInfo($po_sid);
            if (empty($row)) {
                $res = array(
                    "state" => 503,
                    "msg"=>"订单不存在！！！",
                    "value"=>array("po_sid" => $po_sid)
                );
                $validate = false;
            }
            if ($validate) {
                if ($new_price == 0) {
                    if ($row['use_zy'] == 0) {
                        $res = array("state" => 501, "msg"=>"订单中未包含使用早元，请修改其他价格或重新下单再试！！！", "value"=>[]);
                        $validate = false;
                    }
                    if ($validate) {
                        $userModel = new User();
                        $user_zaoyuan = $userModel->getUseZyTotal($row['po_uid']);
                        if ($user_zaoyuan['extcredits8'] < $row['use_zy']) {
                            $res = array("state" => 501, "msg"=>"早元余额不足！！！", "value"=>[]);
                            $validate = false;
                        }
                    }
                }
                if ($validate) {
                    if ($row['po_out'] > $new_price) {//金额减少
                        $order_price = $row['order_price'] - ($row['po_out'] - $new_price);
                    } else {//金额增加
                        $order_price = $row['order_price'] + ($new_price - $row['po_out']);
                    }
                    if ($orderModel->modifyOrderPrice($po_sid, $new_price, $order_price)) {
                        $res = array("state" => 0, "msg"=>"订单改价成功！！！");
                    } else {
                        $res = array("state" => 1, "msg"=>"订单改价失败！！！", "value"=>[]);
                    }
                }
            }
        }
        return $res;
    }
}