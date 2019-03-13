<?php

namespace App\HttpController\Market\Launchadvent;

use App\Domain\Market\Launchadvent\CodeManagerDomain;
use Base\PassportApi;

class CodeManager extends PassportApi
{
    protected function getRules()
    {
        $rules = parent::getRules();
        return array_merge($rules, [
            'searchOptions' => [],
            'codesList' => [
                'condition' => [
                    'type' => 'array',
                    'default' => [],
                    'format' => 'json',
                    'desc' => '条件(json串)'
                ],
                'count' => [
                    'type' => 'enum',
                    'range' => [0, 1],
                    'default' => 0,
                    'desc' => '是否返回总数 1:是 0:否'
                ],
                'page' => [
                    'type' => 'int',
                    'default' => 1,
                    'min' => 1,
                    'desc' => '页码'
                ],
                'limit' => [
                    'type' => 'int',
                    'default' => 20,
                    'min' => 1,
                    'desc' => '条目数'
                ]
            ],
            'saveCode' => [
                'code' => [
                    'type' => 'string',
                    'require' => TRUE,
                    'desc' => '代码'
                ],
                'platform' => [
                    'type' => 'int',
                    'require' => TRUE,
                    'min' => 1,
                    'desc' => '平台'
                ],
                'advertiser' => [
                    'type' => 'string',
                    'require' => TRUE,
                    'desc' => '广告商'
                ],
                'business' => [
                    'type' => 'enum',
                    'range' => ['日语', '留学', '倍普', '韩语', '德语'],
                    'require' => TRUE,
                    'desc' => '业务类型'
                ]
            ],
            'delCode' => [
                'id' => [
                    'type' => 'int',
                    'require' => TRUE,
                    'min' => 1,

                ]
            ],
            'updateSourceNum' => [
                'ids' => [
                    'type' => 'array',
                    'format' => 'explode',
                    'separator' => ',',
                    'default' => [],
                    'desc' => '代码数据ID , 分割'
                ]
            ],
            'export' => [
                'condition' => [
                    'type' => 'array',
                    'default' => [],
                    'format' => 'json',
                    'desc' => '条件(json串)'
                ]
            ],
        ]);
    }

    /**
     * 查询选项
     * @throws \Exception
     */
    public function searchOptions()
    {
        $res = (new CodeManagerDomain())->getSearchOptions($this->params['uid']);
        $this->returnJson($res);
    }

    /**
     * 代码管理列表
     * @throws \Exception
     */
    public function codesList()
    {
        $res = (new CodeManagerDomain())->getCodesList($this->params);
        $this->returnJson($res);
    }

    /**
     * 保存code
     * @throws \Base\Exception\BadRequestException
     */
    public function saveCode()
    {
        (new CodeManagerDomain())->saveCode($this->params);
        $this->returnJson();
    }

    /**
     * 删除代码
     * @throws \Base\Exception\BadRequestException
     */
    public function delCode(){
        (new CodeManagerDomain())->delCode($this->params);
        $this->returnJson();
    }

    /**
     * 更新资源数据
     * @throws \Base\Exception\BadRequestException
     */
    public function updateSourceNum()
    {
        (new CodeManagerDomain())->updateSourceNum($this->params);
        $this->returnJson();
    }

    /**
     * 导出
     */
    public function export()
    {
        $name = (new CodeManagerDomain())->exportSource($this->params);
        $this->returnJson($name);
    }
}