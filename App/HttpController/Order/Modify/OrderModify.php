<?php
/**
 * Created by PhpStorm.
 * User: dean
 * Date: 2019/2/15
 * Time: 上午9:55
 */

namespace App\HttpController\Order\Modify;

use App\Domain\Order\Modify\OrderModifyDomain;
use Base\BaseController;

class OrderModify extends BaseController
{
    protected function getRules() {
        $rules = parent::getRules();
        return array_merge($rules, [
            'modifyPrice' => [
                'po_sid' => [
                    'type' => 'string',
                    'require' => TRUE,
                    'desc' => '改价订单号'
                ],
                'new_price' => [
                    'type' => 'int',
                    'require' => TRUE,
                    'desc' => '变更后金额'
                ],
                'old_price' => [
                    'type' => 'int',
                    'require' => TRUE,
                    'desc' => '变更前金额'
                ],
                'operator_is_cc' => [
                    'type' => 'int',
                    'require' => TRUE,
                    'desc' => '改价人是否是CC'
                ],
                'reason' => [
                    'type' => 'string',
                    'require' => TRUE,
                    'default' => '',
                    'desc' => '改价原因'
                ]
            ]
        ]);
    }

    public function modifyPrice()
    {
        $orderModifyDomain = new OrderModifyDomain();
        $res = $orderModifyDomain->modifyPrice($this->params);
        if ($res['state'] != 0) {
            if ($res['state'] == 1) {
                return $this->returnJson($res, $res['msg'], 500);
            } else {
                return $this->returnJson($res, $res['msg'], 200);
            }
        } else {
            return $this->returnJson();
        }
    }
}