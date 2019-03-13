<?php

namespace App\HttpController\Market\Launchadvent;

use App\Domain\Market\Launchadvent\SemDomain;
use Base\PassportApi;

class Sem extends PassportApi
{
    protected function getRules()
    {
        $rules = parent::getRules();
        return array_merge($rules, [
            'add' => [
                'business_type' => ['type' => 'enum', 'require' => TRUE, 'range' => ['日语', '留学', '倍普', '韩语', '德语']],
                'channel' => ['type' => 'int', 'require' => TRUE],
                'tag' => ['type' => 'callable', 'require' => TRUE, 'callback' => 'Base\\Request\\Validator::validateSemTag'],
                'pushdate' => ['type' => 'string', 'require' => TRUE],
                'cost' => ['type' => 'int', 'default' => 0, 'min' => 0, 'max' => 999999999],
            ],
            'getSemListNum' => [
                'business_type' => ['type' => 'enum', 'require' => TRUE, 'range' => ['0', '日语', '留学', '倍普', '韩语', '德语']],
                'channel' => ['type' => 'int', 'default' => 0],
                'push_date_start' => ['type' => 'string', 'default' => ''],
                'push_date_end' => ['type' => 'string', 'default' => ''],
                'cost_min' => ['type' => 'int', 'default' => 0],
                'cost_max' => ['type' => 'int', 'default' => 0],
            ],
            'getSemList' => [
                'page' => ['type' => 'int', 'default' => 0],
                'limit' => ['type' => 'int', 'default' => 15],
                'business_type' => ['type' => 'enum', 'require' => TRUE, 'range' => ['0', '日语', '留学', '倍普', '韩语', '德语']],
                'channel' => ['type' => 'int', 'default' => 0],
                'push_date_start' => ['type' => 'string', 'default' => ''],
                'push_date_end' => ['type' => 'string', 'default' => ''],
                'cost_min' => ['type' => 'int', 'default' => 0],
                'cost_max' => ['type' => 'int', 'default' => 0],
            ],
            'get' => [
                'id' => ['type' => 'int', 'require' => TRUE]
            ],
            'update' => [
                'id' => ['type' => 'int', 'require' => TRUE],
                'business_type' => ['type' => 'enum', 'require' => TRUE, 'range' => ['日语', '留学', '倍普', '韩语', '德语']],
                'channel' => ['type' => 'int', 'require' => TRUE],
                'tag' => ['type' => 'callable', 'require' => TRUE, 'callback' => 'Base\\Request\\Validator::validateSemTag'],
                'pushdate' => ['type' => 'string', 'require' => TRUE],
                'cost' => ['type' => 'int', 'default' => 0, 'min' => 0, 'max' => 999999999],
            ],
            'delete' => [
                'id' => ['type' => 'int', 'require' => TRUE]
            ],
            'export' => [
                'business_type' => ['type' => 'enum', 'require' => TRUE, 'range' => ['0', '日语', '留学', '倍普', '韩语', '德语']],
                'channel' => ['type' => 'int', 'default' => 0],
                'push_date_start' => ['type' => 'string', 'default' => ''],
                'push_date_end' => ['type' => 'string', 'default' => ''],
                'cost_min' => ['type' => 'int', 'default' => 0],
                'cost_max' => ['type' => 'int', 'default' => 0],
            ]
        ]);
    }

    /**
     * 增加sem
     */
    public function add()
    {
        $domain = new SemDomain();
        $id = $domain->addSem($this->params['business_type'], $this->params['channel'],$this->params['tag'],
            $this->params['pushdate'], $this->params['cost'], $this->params['uid']);
        $this->returnJson(['id' => intval($id)]);
    }

    /**
     * 获取sem列表总数
     */
    public function getSemListNum()
    {
        $domain = new SemDomain();
        $total = $domain->getSemListNum($this->params['business_type'], $this->params['channel'],
            $this->params['push_date_start'], $this->params['push_date_end'], $this->params['cost_min'], $this->params['cost_max']);
        $this->returnJson($total);
    }

    /**
     * 获取sem列表数据
     */
    public function getSemList()
    {
        $domain = new SemDomain();
        $list = $domain->getSemList($this->params['page'], $this->params['limit'], $this->params['business_type'], $this->params['channel'],
            $this->params['push_date_start'], $this->params['push_date_end'], $this->params['cost_min'], $this->params['cost_max']);
        $this->returnJson($list);
    }

    /**
     * 获取sem详情
     */
    public function get()
    {
        $domain = new SemDomain();
        $info = $domain->getSemInfo($this->params['id']);
        $this->returnJson($info);
    }

    /**
     * 修改sem
     */
    public function update()
    {
        $domain = new SemDomain();
        $ret = $domain->updateSem($this->params['id'], $this->params['business_type'], $this->params['channel'],$this->params['tag'],
            $this->params['pushdate'], $this->params['cost'], $this->params['uid']);
        $this->returnJson(['ret' => $ret]);
    }

    /**
     * 删除sem详情
     */
    public function delete()
    {
        $domain = new SemDomain();
        $info = $domain->deleteSem($this->params['id']);
        $this->returnJson($info);
    }

    public function export()
    {
        $domain = new SemDomain();
        $name = $domain->export($this->params['uid'], $this->params['business_type'], $this->params['channel'],
            $this->params['push_date_start'], $this->params['push_date_end'], $this->params['cost_min'], $this->params['cost_max']);
        $this->returnJson($name);
    }

}