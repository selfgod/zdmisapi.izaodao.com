<?php

namespace App\HttpController\Goods;

use App\Domain\Goods\GoodsDomain;
use Base\OpenApi;

class UserGoods extends OpenApi
{
    protected function getRules()
    {
        $rules = parent::getRules();
        return array_merge($rules, [
            'delUserGoods' => [
                'ignore_sign' => TRUE,
                'goodsId' => [
                    'type' => 'int',
                    'require' => TRUE,
                    'desc' => '商品ID'
                ],
                'userOpenId' => [
                    'require' => TRUE,
                    'desc' => '删除商品用户openId'
                ],
                'options' => [
                    'type' => 'array',
                    'default' => [],
                    'format' => 'json',
                    'desc' => '选项参数'
                ]
            ]
        ]);
    }

    /**
     * 删除用户商品
     * @throws \Base\Exception\BadRequestException
     */
    public function delUserGoods()
    {
        $res = (new GoodsDomain())->delUserGoods($this->params);
        return $res ? $this->returnJson() : $this->errorJson('刪除失败', 500);
    }
}
