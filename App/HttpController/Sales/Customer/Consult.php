<?php
/**
 * Created by PhpStorm.
 * User: songwenyao
 * Date: 2018/11/13
 * Time: 上午11:09
 */

namespace App\HttpController\Sales\Customer;

use App\Domain\Sales\Customer\Consult\ConsultDomain;
use Base\BaseController;
use Base\PassportApi;

class Consult extends PassportApi
{
    protected function getRules()
    {
        $rules = parent::getRules();
        return array_merge($rules, [
            'setOperateLog' => [
                'act'=>['type'=>'string', 'require'=>true, 'desc'=> '操作类型'],
                'content' => ['type' => 'string', 'desc' => '操作说明',],
                'cid'=>['type'=>'int','require'=>true,'desc'=>'客户id'],
                'operator'=>['type'=>'string','desc'=>'操作人'],
                'alert_time'=>['type'=>'string', 'desc'=>'提醒时间']
            ]
        ]);
    }

    function setOperateLog()
    {
        //print_r($this->params['userInfo']);
        $result = (new ConsultDomain())->setOperateLog($this->params);
        $this->returnJson($result);
    }

}