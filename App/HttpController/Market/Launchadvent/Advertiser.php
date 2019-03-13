<?php

namespace App\HttpController\Market\Launchadvent;

use App\Domain\Market\Launchadvent\AdvertiserDomain;
use Base\PassportApi;

/**
 * 广告商
 * Class Advertiser
 * @package App\HttpController\Market\Launchadvent
 */
class Advertiser extends PassportApi
{
    protected function getRules()
    {
        $rules = parent::getRules();
        return array_merge($rules, [
            'getAdList' => [
                'page' => ['type' => 'int', 'default' => 0],
                'limit' => ['type' => 'int', 'default' => 15],
                'platform' => ['type' => 'int', 'default' => 0],
                'ader' => ['type' => 'string', 'default' => ''],
                'contact' => ['type' => 'string', 'default' => '']
            ],
            'add' => [
                'platform' => ['type' => 'int', 'default' => 0],
                'name' => ['type' => 'string', 'require' => TRUE, 'min' => 1, 'max' => 50],
                'contact' => ['type' => 'string', 'require' => TRUE, 'min' => 1, 'max' => 50],
                'mobile' => ['type' => 'string', 'default' => '', 'max' => 15],
                'qq' => ['type' => 'string', 'default' => '', 'max' => 15],
                'url' => ['type' => 'string', 'default' => '', 'max' => 255]
            ],
            'getAdListNum' => [
                'platform' => ['type' => 'int', 'default' => 0],
                'ader' => ['type' => 'string', 'default' => ''],
                'contact' => ['type' => 'string', 'default' => '']
            ],
            'get' => [
                'id' => ['type' => 'int', 'require' => TRUE]
            ],
            'update' => [
                'id' => ['type' => 'int', 'require' => TRUE],
                'platform' => ['type' => 'int', 'default' => 0],
                'name' => ['type' => 'string', 'require' => TRUE, 'min' => 1, 'max' => 50],
                'contact' => ['type' => 'string', 'require' => TRUE, 'min' => 1, 'max' => 50],
                'mobile' => ['type' => 'string', 'default' => '', 'max' => 15],
                'qq' => ['type' => 'string', 'default' => '', 'max' => 15],
                'url' => ['type' => 'string', 'default' => '', 'max' => 255]
            ]
        ]);
    }

    /**
     * 获取广告商列表数据
     */
    public function getAdList()
    {
        $domain = new AdvertiserDomain();
        $list = $domain->getAdList($this->params['page'], $this->params['limit'], $this->params['platform'], $this->params['ader'],
            $this->params['contact']);
        $this->returnJson($list);
    }

    /**
     * 获取广告商列表总数
     */
    public function getAdListNum()
    {
        $domain = new AdvertiserDomain();
        $total = $domain->getAdListNum($this->params['platform'], $this->params['ader'], $this->params['contact']);
        $this->returnJson($total);
    }

    /**
     * 获取广告商详情
     */
    public function get()
    {
        $domain = new AdvertiserDomain();
        $info = $domain->getAdInfo($this->params['id']);
        $this->returnJson($info);
    }

    /**
     * 增加广告商
     */
    public function add()
    {
        $domain = new AdvertiserDomain();
        $id = $domain->addAdvertiser($this->params['platform'], $this->params['name'],$this->params['contact'],
            $this->params['mobile'], $this->params['qq'], $this->params['url'], $this->params['uid']);
        $this->returnJson(['id' => intval($id)]);
    }

    /**
     * 修改广告商
     */
    public function update()
    {
        $domain = new AdvertiserDomain();
        $ret = $domain->updateAdvertiser($this->params['id'], $this->params['platform'], $this->params['name'],$this->params['contact'],
            $this->params['mobile'], $this->params['qq'], $this->params['url'], $this->params['uid']);
        $this->returnJson(['ret' => $ret]);
    }
}