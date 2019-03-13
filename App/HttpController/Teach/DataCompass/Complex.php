<?php

namespace App\HttpController\Teach\DataCompass;

use App\Domain\Teach\DataCompass\ComplexDomain;
use Base\PassportApi;

class Complex extends PassportApi
{
    protected function getRules()
    {
        $rules = parent::getRules();
        return array_merge($rules, [
            'monthComplexSas' => [
                'action' => [
                    'type' => 'enum',
                    'require' => TRUE,
                    'range' => ['studentBase', 'behavior', 'addedMonth', 'chooseCourse', 'endCourse'],
                    'desc' => '统计项目名'
                ],
                'startTime' => [
                    'type' => 'string',
                    'default' => '',
                    'desc' => '开始时间'
                ],
                'endTime' => [
                    'type' => 'string',
                    'default' => '',
                    'desc' => '结束时间'
                ],
                'grade' => [
                    'type' => 'int',
                    'default' => 0,
                    'min' => -1,
                    'max' => 9,
                    'desc' => '等级'
                ]
            ]
        ]);
    }

    /**
     * 月份综合统计
     */
    public function monthComplexSas()
    {
        $result = (new ComplexDomain())->getMonthComplexSas($this->params);
        $this->returnJson($result);
    }
}