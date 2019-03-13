<?php

namespace App\HttpController\Operator;

use App\Domain\Operator\ListenDomain;
use Base\PassportApi;

class Listen extends PassportApi
{
    protected function getRules()
    {
        $rules = parent::getRules();
        return array_merge($rules, [
            'getListenCourse' => [
                'lessonId' => [
                    'type' => 'int',
                    'require' => TRUE,
                    'desc' => '课件ID'
                ],
            ],
            'setListenCourse' => [
                'lessonId' => [
                    'type' => 'int',
                    'require' => TRUE,
                    'desc' => '课件ID'
                ],
                'ob_status' => [
                    'type' => 'enum',
                    'range' => [0, 1],
                    'require' => TRUE,
                    'desc' => '旁听状态',
                ],
                'ob_name' => [
                    'type' => 'string',
                    'default' => '',
                    'desc' => '旁听名称',
                ],
                'ob_info' => [
                    'type' => 'string',
                    'default' => '',
                    'desc' => '旁听介绍',
                ],
                'ob_num' => [
                    'type' => 'int',
                    'default' => 0,
                    'min' => 0,
                    'desc' => '旁听人数',
                ]
            ]
        ]);
    }

    /**
     * 获取旁听课信息
     * @throws \Base\Exception\BadRequestException
     */
    public function getListenCourse()
    {
        $res = (new ListenDomain())->getListenCourse($this->params['lessonId']);
        $this->returnJson($res);
    }

    /**
     * 设置旁听课件
     * @throws \Base\Exception\BadRequestException
     */
    public function setListenCourse()
    {
        $res = (new ListenDomain())->setListenCourse($this->params);
        $res === TRUE ? $this->returnJson() : $this->errorJson('旁听课设置失败', 400);
    }
}
