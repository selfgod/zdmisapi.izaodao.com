<?php
namespace App\HttpController\Sales\Manage;

use App\Domain\Sales\Manage\KpiRankDomain;
use Base\PassportApi;

class KpiRank extends PassportApi
{
    protected function getRules()
    {
        $rules = parent::getRules();
        return array_merge($rules, [
            'getListNum' => [
                'business_type' => ['type' => 'string', 'require' => true, 'desc' => '业务类型'],
                'region' => ['type' => 'string', 'require' => true, 'desc' => '区域'],
                'date' => ['type' => 'string', 'require' => true, 'desc' => '生效月份'],
                'salesType' => ['type' => 'enum', 'require' => true, 'range' => ['1', '2', '3']]
            ],
            'getListData' => [
                'page' => ['type' => 'int', 'default' => 0],
                'limit' => ['type' => 'int', 'default' => 100],
                'business_type' => ['type' => 'string', 'require' => true, 'desc' => '业务类型'],
                'region' => ['type' => 'string', 'require' => true, 'desc' => '区域'],
                'date' => ['type' => 'string', 'require' => true, 'desc' => '生效月份'],
                'salesType' => ['type' => 'enum', 'require' => true, 'range' => ['1', '2', '3']],
                'orderBy' => ['type' => 'string', 'default' => '']
            ],
        ]);
    }

    /**
     * 获取列表有几条数据
     */
    public function getListNum()
    {
        $count = (new KpiRankDomain())->getListNum($this->params['salesType'], $this->params['business_type'], $this->params['region'],
            $this->params['date']);
        return $this->returnJson($count);
    }

    /**
     * 查询列表数据
     */
    public function getListData()
    {
        $list = (new KpiRankDomain())->getListData($this->params['salesType'], $this->params['business_type'], $this->params['region'],
            $this->params['date'], $this->params['page'], $this->params['limit'], $this->params['orderBy']);
        return $this->returnJson($list);
    }
}