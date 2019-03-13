<?php
namespace App\HttpController\Market\Launchadvent;

use App\Domain\Market\Launchadvent\SemChannelDomain;
use Base\PassportApi;

class SemChannel extends PassportApi
{
    protected function getRules()
    {
        $rules = parent::getRules();
        return array_merge($rules, [
            'add' => [
                'name' => ['type' => 'string', 'require' => TRUE, 'min' => 1, 'max' => 100]
            ],
            'getSemChannelListNum' => [],
            'getSemChannelList' => [
                'page' => ['type' => 'int', 'default' => 0],
                'limit' => ['type' => 'int', 'default' => 15],
            ],
            'update' => [
                'id' => ['type' => 'int', 'require' => TRUE],
                'name' => ['type' => 'string', 'require' => TRUE, 'min' => 1, 'max' => 100],
            ],
            'get' => [
                'id' => ['type' => 'int', 'require' => TRUE]
            ],
            'getChannelOpts' => []
        ]);
    }

    /**
     * 获取sem渠道列表数据
     */
    public function getSemChannelList()
    {
        $domain = new SemChannelDomain();
        $list = $domain->getSemChannelList($this->params['page'], $this->params['limit']);
        $this->returnJson($list);
    }

    /**
     * 增加sem渠道
     */
    public function add()
    {
        $domain = new SemChannelDomain();
        $id = $domain->addSemChannel($this->params['name'], $this->params['uid']);
        $this->returnJson(['id' => intval($id)]);
    }

    /**
     * 获取sem渠道列表总数
     */
    public function getSemChannelListNum()
    {
        $domain = new SemChannelDomain();
        $total = $domain->getSemChannelListNum();
        $this->returnJson($total);
    }

    /**
     * 修改sem渠道
     */
    public function update()
    {
        $domain = new SemChannelDomain();
        $ret = $domain->updateSemChannel($this->params['id'], $this->params['name'], $this->params['uid']);
        $this->returnJson(['ret' => $ret]);
    }

    /**
     * 获取sem渠道详情
     */
    public function get()
    {
        $domain = new SemChannelDomain();
        $info = $domain->getSemChannelInfo($this->params['id']);
        $this->returnJson($info);
    }

    /**
     * 获取下拉列表所需的全部渠道
     */
    public function getChannelOpts()
    {
        $domain = new SemChannelDomain();
        $opts = $domain->getChannelOpts();
        $this->returnJson($opts);
    }
}