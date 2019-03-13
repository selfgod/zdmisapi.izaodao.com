<?php

namespace Base;
use Base\Exception\BadRequestException;

class PassportApi extends BaseController
{
    /**
     * @var int uid
     */
    protected function getRules()
    {
        return [
            '*' => [
                'sessionId' => [
                    'type' => 'string',
                    'default' => '',
//                    'require' => TRUE,
                    'desc' => '当前登录用户zdSessionId'
                ],
            ]
        ];
    }

    /**
     * @throws BadRequestException
     */
    protected function checkUser()
    {
        $this->params['uid'] = '';
        $this->params['userInfo'] = [];
        $sessionId = empty($this->params['sessionId']) ? $this->request()->getCookieParams('ZDSESSIONID') : $this->params['sessionId'];
        if (!empty($sessionId)) {
            $userInfo = Thrift::getInstance()->service('User')->getUserBySessionId($sessionId);
            if (!empty($userInfo)) {
                $this->params['uid'] = $userInfo['uid'];
                $this->params['userInfo'] = $userInfo;
            }
        }
        if (empty(intval($this->params['uid']))) {
            throw new BadRequestException('用户未登录', 1);
        }
    }
}