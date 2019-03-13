<?php

namespace Base;

use Base\Exception\BadRequestException;

class OpenApi extends BaseController
{
    /**
     * @var int uid
     */
    protected function getRules()
    {
        return [
            '*' => [
                'openId' => [
                    'require' => TRUE,
                    'desc' => '用户中心openId'
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
        if (!empty($this->params['openId'])) {
            $this->params['uid'] = Thrift::getInstance()->service('User')->getUidByOpenId($this->params['openId']);
        }
        if (empty(intval($this->params['uid']))) {
            throw new BadRequestException('用户未登录', 1);
        }
        //TODO
        $this->params['userInfo'] = [];
    }
}