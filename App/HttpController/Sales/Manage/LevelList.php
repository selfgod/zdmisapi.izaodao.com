<?php
namespace App\HttpController\Sales\Manage;

use App\Domain\Sales\Manage\LevelListDomain;
use Base\PassportApi;

/**
 * 级别列表
 * Class LevelList
 * @package App\HttpController\Sales\Manage
 */
class LevelList extends PassportApi
{
    protected function getRules()
    {
        $rules = parent::getRules();
        return array_merge($rules, [
            'getList' => [
                'business_type' => ['type' => 'string', 'require' => true, 'desc' => '业务类型'],
                'region' => ['type' => 'string', 'require' => true, 'desc' => '区域'],
                'date' => ['type' => 'string', 'require' => true, 'desc' => '生效月份']
            ],
            'update' => [
                'id' => ['type' => 'int', 'require' => TRUE],
                'salary_base' => ['type' => 'int', 'require' => TRUE, 'min' => 0],
                'salary_kpi_base' => ['type' => 'float', 'require' => TRUE, 'min' => 0],
                'perform_target' => ['type' => 'int', 'require' => false, 'min' => 0],
                'perform_weight' => ['type' => 'float', 'require' => TRUE, 'min' => 0, 'max' => 100],
                'perform_weight_team' => ['type' => 'float', 'require' => false, 'default'=>0, 'min' => 0, 'max' => 100],
                'trans_rate_target' => ['type' => 'float', 'require' => TRUE, 'min' => 0],
                'trans_rate_weight' => ['type' => 'float', 'require' => TRUE, 'min' => 0, 'max' => 100],
                'call_sec_target' => ['type' => 'float', 'require' => TRUE, 'min' => 0],
                'call_sec_weight' => ['type' => 'float', 'require' => TRUE, 'min' => 0, 'max' => 100],
                'resign_target' => ['type' => 'float', 'default' => 0, 'min' => 0],
                'resign_weight' => ['type' => 'float', 'default' => 0, 'min' => 0, 'max' => 100],
                'business_type' => ['type' => 'string', 'require' => true, 'desc' => '业务类型'],
                'region' => ['type' => 'string', 'require' => true, 'desc' => '区域'],
                'level_id' => ['type' => 'int', 'require' => TRUE, 'desc' => '职级配置id']
            ],
            'audit' => [
                'id' => ['type' => 'int', 'require' => TRUE]
            ],
            'getLevelInfo' => [
                'business_type' => ['type' => 'string', 'require' => true, 'desc' => '业务类型'],
                'region' => ['type' => 'string', 'require' => true, 'desc' => '区域'],
                'date' => ['type' => 'string', 'require' => true, 'desc' => '生效月份'],
                'level_id' => ['type' => 'int', 'require' => TRUE, 'desc' => '职级配置id']
            ]
        ]);
    }

    /**
     * 获取级别列表
     */
    public function getList()
    {
        $list = (new LevelListDomain())->getList($this->params['business_type'], $this->params['region'], $this->params['date']);
        $this->returnJson($list);
    }

    /**
     * 更新数据
     */
    public function update()
    {
        $id = $this->params['id'];
        $uid = $this->params['uid'];
        $ret = (new LevelListDomain())->update($id, $uid, $this->params);
        $this->returnJson($ret);
    }

    /**
     * 修改日志
     */
    public function audit()
    {
        $info = (new LevelListDomain())->getAudit($this->params['id']);
        $this->returnJson($info);
    }

    /**
     * 获取单条级别信息
     */
    public function getLevelInfo()
    {
        $info = (new LevelListDomain())->getOneByLevel($this->params['business_type'], $this->params['region'], $this->params['date'], $this->params['level_id']);
        $this->returnJson($info);
    }
}