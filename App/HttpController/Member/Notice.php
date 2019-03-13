<?php

namespace App\HttpController\Member;

use App\Domain\Member\MemberDomain;
use Base\OpenApi;

class Notice extends OpenApi
{
    protected function getRules()
    {
        $rules = parent::getRules();
        return array_merge($rules, [
            'userTaskRemind' => [
                'ignore_sign' => TRUE,
                'task_key' => [
                    'type' => 'string',
                    'require' => TRUE,
                    'desc' => '任务key'
                ],
                'link' => [
                    'type' => 'string',
                    'require' => TRUE,
                    'desc' => '链接地址'
                ],
                'msg' => [
                    'type' => 'string',
                    'default' => '',
                    'desc' => '提示信息默认取组名'
                ],
                'num' => [
                    'type' => 'int',
                    'default' => 1,
                    'desc' => '消息数'
                ],
            ],
            'userEventPush' => [
                'ignore_sign' => TRUE,
                'event' => [
                    'type' => 'string',
                    'require' => TRUE,
                    'desc' => '事件名'
                ],
                'data' => [
                    'type' => 'array',
                    'format' => 'json',
                    'desc' => '请求参数'
                ]

            ]
        ]);
    }

    /**
     * 提醒用户处理任务
     * @throws \Base\Exception\BadRequestException
     */
    public function userTaskRemind()
    {
        (new MemberDomain())->sendUserTaskRemind($this->params);
        $this->returnJson();
    }

    /**
     * 用户事件推送消息
     */
    public function userEventPush()
    {
        (new MemberDomain())->userEventPush($this->params);
        $this->returnJson();
    }
}