<?php

namespace App\HttpController\Market\Launchadvent;

use App\Domain\Market\Launchadvent\DataHandleDomain;
use App\Domain\Market\Launchadvent\IndexDomain;
use Base\PassportApi;

class Index extends PassportApi
{
    protected function getRules()
    {
        $rules = parent::getRules();
        return array_merge($rules, [
            'getOptsData' => [],
            'add' => [
                'business_type' => ['type' => 'enum', 'require' => TRUE, 'range' => ['日语', '留学', '倍普', '韩语', '德语']],
                'platform' => ['type' => 'int', 'require' => TRUE],
                'ads' => ['type' => 'int', 'require' => TRUE],
                'point' => ['type' => 'int', 'require' => TRUE],
                'content' => ['type' => 'string', 'require' => TRUE, 'min' => 1, 'max' => 255],
                'tag' => ['type' => 'string', 'require' => TRUE, 'min' => 1, 'max' => 128],
                'pushdate' => ['type' => 'string', 'require' => TRUE],
                'user_count' => ['type' => 'int', 'default' => 0, 'min' => 0],
                'cost' => ['type' => 'int', 'default' => 0, 'min' => 0, 'max' => 999999999],
                'expect_resources' => ['type' => 'int', 'default' => 0, 'min' => 0],
                'actual_resources' => ['type' => 'int', 'default' => 0, 'min' => 0]
            ],
            'getAdListNum' => [
                'business_type' => ['type' => 'enum', 'require' => TRUE, 'range' => ['0', '日语', '留学', '倍普', '韩语', '德语']],
                'platform' => ['type' => 'int', 'default' => 0],
                'ads' => ['type' => 'int', 'default' => 0],
                'push_date_start' => ['type' => 'string', 'default' => ''],
                'push_date_end' => ['type' => 'string', 'default' => ''],
                'content' => ['type' => 'string', 'default' => ''],
                'tag' => ['type' => 'string', 'default' => ''],
                'contact' => ['type' => 'string', 'default' => ''],
            ],
            'getAdList' => [
                'page' => ['type' => 'int', 'default' => 0],
                'limit' => ['type' => 'int', 'default' => 15],
                'business_type' => ['type' => 'enum', 'require' => TRUE, 'range' => ['0', '日语', '留学', '倍普', '韩语', '德语']],
                'platform' => ['type' => 'int', 'default' => 0],
                'ads' => ['type' => 'int', 'default' => 0],
                'push_date_start' => ['type' => 'string', 'default' => ''],
                'push_date_end' => ['type' => 'string', 'default' => ''],
                'content' => ['type' => 'string', 'default' => ''],
                'tag' => ['type' => 'string', 'default' => ''],
                'contact' => ['type' => 'string', 'default' => ''],
                'orderby' => ['type' => 'string', 'default' => '']
            ],
            'get' => [
                'id' => ['type' => 'int', 'require' => TRUE]
            ],
            'update' => [
                'id' => ['type' => 'int', 'require' => TRUE],
                'business_type' => ['type' => 'enum', 'require' => TRUE, 'range' => ['日语', '留学', '倍普', '韩语', '德语']],
                'platform' => ['type' => 'int', 'require' => TRUE],
                'ads' => ['type' => 'int', 'require' => TRUE],
                'point' => ['type' => 'int', 'require' => TRUE],
                'content' => ['type' => 'string', 'require' => TRUE, 'min' => 1, 'max' => 255],
                'tag' => ['type' => 'string', 'require' => TRUE, 'min' => 1, 'max' => 128],
                'pushdate' => ['type' => 'string', 'require' => TRUE],
                'user_count' => ['type' => 'int', 'default' => 0, 'min' => 0],
                'cost' => ['type' => 'int', 'default' => 0, 'min' => 0, 'max' => 999999999],
                'expect_resources' => ['type' => 'int', 'default' => 0, 'min' => 0],
                'actual_resources' => ['type' => 'int', 'default' => 0, 'min' => 0]
            ],
            'delete' => [
                'id' => ['type' => 'int', 'require' => TRUE]
            ],
            'permission' => [],
            'export' => [
                'business_type' => ['type' => 'enum', 'require' => TRUE, 'range' => ['0', '日语', '留学', '倍普', '韩语', '德语']],
                'platform' => ['type' => 'int', 'default' => 0],
                'ads' => ['type' => 'int', 'default' => 0],
                'push_date_start' => ['type' => 'string', 'default' => ''],
                'push_date_end' => ['type' => 'string', 'default' => ''],
                'content' => ['type' => 'string', 'default' => ''],
                'tag' => ['type' => 'string', 'default' => ''],
                'contact' => ['type' => 'string', 'default' => '']
            ]
        ]);
    }

    public function getOptsData()
    {
        $domain = new IndexDomain();
        $conf = $domain->getOptsData();
        $this->returnJson($conf);
    }

    /**
     * 增加广告
     */
    public function add()
    {
        $domain = new IndexDomain();
        $id = $domain->addAd($this->params['business_type'], $this->params['ads'],
            $this->params['point'], $this->params['content'], $this->params['tag'],$this->params['pushdate'],
            $this->params['user_count'], $this->params['cost'], $this->params['expect_resources'], $this->params['actual_resources'],
            $this->params['uid']);
        $this->returnJson(['id' => intval($id)]);
    }

    /**
     * 获取广告列表总数
     */
    public function getAdListNum()
    {
        $domain = new IndexDomain();
        $total = $domain->getAdListNum($this->params['business_type'], $this->params['platform'], $this->params['ads'],
            $this->params['push_date_start'], $this->params['push_date_end'], $this->params['tag'],$this->params['content'],
            $this->params['contact']);
        $this->returnJson($total);
    }

    /**
     * 获取广告列表数据
     */
    public function getAdList()
    {
        $domain = new IndexDomain();
        $list = $domain->getAdList($this->params['page'], $this->params['limit'], $this->params['business_type'],
            $this->params['platform'], $this->params['ads'], $this->params['push_date_start'], $this->params['push_date_end'],
            $this->params['tag'],$this->params['content'], $this->params['contact'], $this->params['orderby'], $this->params['uid']);
        $this->returnJson($list);
    }

    /**
     * 获取广告详情
     */
    public function get()
    {
        $domain = new IndexDomain();
        $info = $domain->getAdInfo($this->params['id']);
        $this->returnJson($info);
    }

    /**
     * 修改广告
     */
    public function update()
    {
        $domain = new IndexDomain();
        $ret = $domain->updateAd($this->params['id'], $this->params['business_type'], $this->params['ads'],
            $this->params['point'], $this->params['content'], $this->params['tag'],$this->params['pushdate'],
            $this->params['user_count'], $this->params['cost'], $this->params['expect_resources'], $this->params['actual_resources'],
            $this->params['uid']);
        $this->returnJson(['ret' => $ret]);
    }

    /**
     * 删除广告详情
     */
    public function delete()
    {
        $domain = new IndexDomain();
        $info = $domain->deleteAd($this->params['id']);
        $this->returnJson($info);
    }

    /**
     * 获取用户广告权限
     */
    public function permission()
    {
        $domain = new IndexDomain();
        $permission = $domain->permission($this->params['uid']);
        $this->returnJson($permission);
    }

    public function export()
    {
        $domain = new IndexDomain();
        $name = $domain->export($this->params['uid'], $this->params['business_type'],
            $this->params['platform'], $this->params['ads'], $this->params['push_date_start'], $this->params['push_date_end'],
            $this->params['tag'],$this->params['content'], $this->params['contact']);
        $this->returnJson($name);
    }

    /**
     * 更新数据
     */
    public function updateHandle()
    {
        $domain = new DataHandleDomain();
        $ret = $domain->updateData();
        $this->returnJson($ret);
    }
}