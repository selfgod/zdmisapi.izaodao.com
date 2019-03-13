<?php
/**
 * Created by PhpStorm.
 * User: aramis
 * Date: 2019-02-13
 * Time: 19:39
 */

namespace App\HttpController\Order;

use Base\BaseController;
use App\Domain\Order\OrderDomain;

class Order extends BaseController
{
    protected function getRules()
    {
        $rules = parent::getRules();
        return array_merge($rules, [
            'splitOrder' => [
                'order' => [
                    'type' => 'string',
                    'require' => TRUE,
                    'desc' => '母单信息果断串'
                ],
                'earnest' => [
                    'type' => 'string',
                    'require' => TRUE,
                    'desc' => '定金单订单信息'
                ]
            ]
        ]);
    }

    /**
     * 拆分定金单
     * @return mixed
     */
    public function splitOrder()
    {
        $orderDomain = new OrderDomain();
        $orderInfo = \GuzzleHttp\json_decode($this->params['order'], TRUE);
        $earnestOrder = \GuzzleHttp\json_decode($this->params['earnest'], TRUE);
        $res = $orderDomain->splitOrder($orderInfo, $earnestOrder);
        return $res ? $this->returnJson() : $this->errorJson('定金单创建失败', 500);
    }
}