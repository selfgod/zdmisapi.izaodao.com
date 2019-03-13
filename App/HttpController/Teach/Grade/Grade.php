<?php

namespace App\HttpController\Teach\Grade;

use App\Domain\Teach\Grade\GradeDomain;
use Base\PassportApi;

class Grade extends PassportApi
{
    protected function getRules()
    {
        $rules = parent::getRules();
        return array_merge($rules, [
            'queryGradeList' => [
                'ignore_sign' => true,
                'ignore_auth' => true
            ]
        ]);
    }

    /**
     * 获取等级列表
     */
    public function queryGradeList()
    {
        $result = (new GradeDomain())->queryGradeList();
        $this->returnJson($result);
    }

}