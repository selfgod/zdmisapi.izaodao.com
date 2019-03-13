<?php
namespace App\HttpController\Sales\Setting;

use App\Domain\Sales\Setting\LevelDomain;
use Base\PassportApi;

class Level extends PassportApi
{
    protected function getRules()
    {
        $rules = parent::getRules();
        return array_merge($rules, [
            'salesType' => [],
            'getList' => [],
            'create' => [
                'sales_type' => ['type' => 'enum', 'range' => [1, 2, 3], 'require' => true, 'desc' => '人员类别'],
                'sales_level' => ['type' => 'string', 'require' => true, 'desc' => '职级'],
                'code' => ['type' => 'string', 'require' => true, 'desc' => '岗位代码'],
                'color' => ['type' => 'string', 'default' => '', 'desc' => '颜色'],
                'level_order' => ['type' => 'int', 'require' => true, 'desc' => '序号']
            ],
            'get' => [
                'id' => ['type' => 'int', 'require' => TRUE]
            ],
            'update' => [
                'id' => ['type' => 'int', 'require' => TRUE],
                'sales_type' => ['type' => 'enum', 'range' => [1, 2, 3], 'require' => true, 'desc' => '人员类别'],
                'sales_level' => ['type' => 'string', 'require' => true, 'desc' => '职级'],
                'code' => ['type' => 'string', 'require' => true, 'desc' => '岗位代码'],
                'color' => ['type' => 'string', 'default' => '', 'desc' => '颜色'],
                'level_order' => ['type' => 'int', 'require' => true, 'desc' => '序号']
            ],
            'audit' => [
                'id' => ['type' => 'int', 'require' => TRUE]
            ],
            'getListByType' => [
                'sales_type' => ['type' => 'enum', 'range' => [1, 2, 3], 'require' => true, 'desc' => '人员类别'],
            ],
            'getByUid' => [
                'salesman_id' => [
                    ['type' => 'int', 'require' => TRUE, 'desc' => '销售uid'],
                ],
                'month'=>['type' => 'string', 'default'=>'', 'desc' => 'Y-m'],
            ]
        ]);
    }

    /**
     * 销售类型
     */
    public function salesType()
    {
        $type = (new LevelDomain())->getSalesType();
        $this->returnJson($type);
    }

    /**
     * 创建销售职级
     */
    public function create()
    {
        $ret = (new LevelDomain())->createSalesLevel($this->params['uid'], $this->params['sales_type'], $this->params['sales_level'],
            $this->params['code'], $this->params['color'], $this->params['level_order']);
        $this->returnJson(['id' => intval($ret)]);
    }

    /**
     * 获取列表
     */
    public function getList()
    {
        $list = (new LevelDomain())->getList();
        $this->returnJson($list);
    }

    /**
     * 获取职级数据
     */
    public function get()
    {
        $info = (new LevelDomain())->getLevelInfo($this->params['id']);
        $this->returnJson($info);
    }

    /**
     * 更新职级
     */
    public function update()
    {
        $ret = (new LevelDomain())->updateLevel($this->params['id'], $this->params['uid'], $this->params['sales_type'],
            $this->params['sales_level'], $this->params['code'], $this->params['color'], $this->params['level_order']);
        $this->returnJson(['ret' => $ret]);
    }

    /**
     * 修改日志
     */
    public function audit()
    {
        $info = (new LevelDomain())->getAudit($this->params['id']);
        $this->returnJson($info);
    }

    /**
     * 通过人员类型获取配置信息
     */
    public function getListByType()
    {
        $list = (new LevelDomain())->getListByType($this->params['sales_type']);
        $this->returnJson($list);
    }

    /**
     * 通过uid获取对应的职级信息
     */
    public function getByUid()
    {
        if($this->params['month']){
            $this->params['month'] = date('Y-m', strtotime($this->params['month']));
        }
        $info = (new LevelDomain())->getLevelInfoByUid($this->params['salesman_id'], $this->params['month']);
        $this->returnJson($info);
    }
}