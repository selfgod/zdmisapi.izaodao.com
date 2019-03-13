<?php

namespace App\HttpController\Teach\Label;

use App\Domain\Teach\Label\LabelDomain;
use Base\PassportApi;

class Label extends PassportApi
{
    protected function getRules()
    {
        $rules = parent::getRules();
        return array_merge($rules, [
            'queryLabelList' => [
                'p_id' => ['type' => 'int', 'default' => 0, 'desc' => '父类ID'],
                'ignore_sign' => true,
                'ignore_auth' => true
            ]
        ]);
    }

    /**
     * 获取标签列表
     */
    public function queryLabelList()
    {
        $result = (new LabelDomain())->queryLabelList($this->params);
        $this->returnJson($result);
    }

}