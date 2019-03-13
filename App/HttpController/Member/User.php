<?php

namespace App\HttpController\Member;

use App\Domain\Member\MemberDomain;
use Base\PassportApi;

class User extends PassportApi
{
    protected function getRules()
    {
        $rules = parent::getRules();
        return array_merge($rules, [
            'getOnlineUsers' => [

            ]
        ]);
    }

    /**
     * 在线用户
     */
    public function getOnlineUsers()
    {
        $result = (new MemberDomain())->getOnlineUsers($this->params['userInfo']);
        $this->returnJson($result);
    }
}