<?php
/**
 * Created by PhpStorm.
 * User: songwenyao
 * Date: 2018/11/16
 * Time: 下午4:46
 */
namespace Base\PayCenter;

class OrdersApi extends PayCenterApi
{
    private $appId;
    private $version = 'web/v1';

    function __construct($businessType)
    {
        parent::__construct();
        $this->appId = $this->appIdArr[$businessType];
    }

    function sumOrderCountBySalePersonId($payStartTime, $payEndTime, $size=10, $orderPrice=800)
    {
        $condition = [];
        $condition['payStartTime'] = $payStartTime;
        $condition['payEndTime'] = $payEndTime;
        $condition['queryAppId'] = $this->appId;
        $condition['size'] = $size;
        $condition['orderPrice'] = $orderPrice;
        return parent::get($this->version.'/orders/sumOrderCountBySalePersonId', $condition);
    }

    function sumPaidBySalePersonId($payStartTime, $payEndTime, $size=10)
    {
        $condition = [];
        $condition['payStartTime'] = $payStartTime;
        $condition['payEndTime'] = $payEndTime;
        $condition['queryAppId'] = $this->appId;
        $condition['size'] = $size;
        return parent::get($this->version.'/orders/sumPaidBySalePersonId', $condition);
    }
    //获取每个销售人的售卖金额(按照续费和其他分开)
    function classifySumPaidBySalePersonId($payStartTime, $payEndTime, $size=0)
    {
        $condition = [];
        $condition['payStartTime'] = $payStartTime;
        $condition['payEndTime'] = $payEndTime;
        $condition['queryAppId'] = $this->appId;
        if($size) $condition['size'] = $size;
        return parent::get($this->version.'/orders/classifySumPaidBySalePersonId', $condition);
    }
}