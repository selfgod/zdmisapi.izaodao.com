<?php

namespace App\HttpController\Sales\Memo;

use App\Domain\Sales\Memo\MemoDomain;
use Base\BaseController;

/**
 *
 * Class Memo
 * @package App\HttpController\Sales\Memo
 */
class Memo extends BaseController
{
    protected function getRules()
    {
        $rules = parent::getRules();
        return array_merge($rules, [
            'getCCLastMemo' => [
//                'ignore_sign' => true,
//                'ignore_auth' => true,
                'user_openid' => ['type' => 'string', 'default' => ''],
                'cc_username' => ['type'=> 'string','default' => ''],
                'user_uid' => ['type'=> 'int','default' => 0]
            ],
            'getSALastMemo' => [
//                'ignore_sign' => true,
//                'ignore_auth' => true,
                'user_openid' => ['type' => 'string', 'default' => ''],
                'sa_username' => ['type'=> 'string','default' => ''],
                'user_uid' => ['type'=> 'int','default' => 0]
            ]
        ]);
    }


    /**
     * 获取CC最后回访信息
     * @throws \Exception
     */
    public function getCCLastMemo(){
        $result = (new MemoDomain())->getCCLastMemo($this->params);
        $this->returnJson($result);
    }

    /**
     * 获取SA最后回访信息
     * @throws \Exception
     */
    public function getSALastMemo(){
        $result = (new MemoDomain())->getSALastMemo($this->params);
        $this->returnJson($result);
    }
}