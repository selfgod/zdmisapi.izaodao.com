<?php

namespace App\Domain\Teach\Grade;

use Base\BaseDomain;
use Base\Thrift;

class GradeDomain extends BaseDomain
{
    /**
     * 获取等级列表
     * @return array
     */
    public function queryGradeList()
    {
        $res = Thrift::getInstance()->service('Grade')->getGradeList();
        return ['grade' => $res];
    }

}