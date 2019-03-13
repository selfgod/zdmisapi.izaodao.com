<?php

namespace App\HttpController\Setting;

use App\Domain\Setting\WaitproDomain;
use Base\PassportApi;

class Waitpro extends PassportApi
{
    protected function getRules()
    {
        $rules = parent::getRules();
        return array_merge($rules, [
            'waitProject' => [

            ],
            'saveProject' => [
                'data' => [
                    'type' => 'array',
                    'format' => 'json',
                    'desc' => '请求数据'
                ]
            ],
            'delProject' => [
                'id' => [
                    'type' => 'int',
                    'default' => 0,
                    'desc' => 'ID'
                ]
            ]
        ]);
    }

    public function waitProject()
    {
        $result = (new WaitproDomain())->getWaitProjectList();
        $this->returnJson($result);
    }

    /**
     * 保存待办项目
     * @throws \Base\Exception\BadRequestException
     */
    public function saveProject()
    {
        (new WaitproDomain())->saveWaitProject($this->params);
        $this->returnJson();
    }

    /**
     * 删除待办项目
     * @throws \Base\Exception\BadRequestException
     */
    public function delProject()
    {
        (new WaitproDomain())->delWaitProject($this->params);
        $this->returnJson();
    }
}