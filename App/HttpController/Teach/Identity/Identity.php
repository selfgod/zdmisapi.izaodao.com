<?php

namespace App\HttpController\Teach\Identity;

use App\Domain\Teach\Identity\IdentityDomain;
use Base\PassportApi;

class Identity extends PassportApi
{
    protected function getRules()
    {
        $rules = parent::getRules();
        return array_merge($rules, [
            'queryIdentityList' => [
                'user_identity' => ['type' => 'int', 'default' => 0, 'desc' => '一级身份标识'],
                'ignore_sign' => true,
                'ignore_auth' => true
            ]
        ]);
    }

    /**
     * 获取身份标识列表
     */
    public function queryIdentityList()
    {
        $result = (new IdentityDomain())->queryIdentityList($this->params);
        $this->returnJson($result);
    }

}