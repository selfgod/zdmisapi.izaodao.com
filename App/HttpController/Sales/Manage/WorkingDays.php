<?php
namespace App\HttpController\Sales\Manage;

use App\Domain\Sales\Manage\Team\WorkingDaysDomain;
use Base\PassportApi;

/**
 * 工作日配置
 * Class WorkingDays
 * @package App\HttpController\Sales\Manage
 */
class WorkingDays extends PassportApi
{
    protected function getRules()
    {
        $rules = parent::getRules();
        return array_merge($rules, [
            'update' => [
                'id' => ['type' => 'int', 'require' => true],
                'days' => ['type' => 'string', 'require' => true, 'desc' => '工作日']
            ],
            'get' => [
                'business_type' => ['type' => 'string', 'require' => true, 'desc' => '业务类型'],
                'region' => ['type' => 'string', 'require' => true, 'desc' => '区域'],
                'data_date' => ['type' => 'string', 'require' => true, 'desc' => '生效月份'],
            ],
            'create' => [
                'business_type' => ['type' => 'string', 'require' => true, 'desc' => '业务类型'],
                'region' => ['type' => 'string', 'require' => true, 'desc' => '区域'],
                'data_date' => ['type' => 'string', 'require' => true, 'desc' => '生效月份'],
                'days' => ['type' => 'string', 'require' => true, 'desc' => '工作日']
            ],
            'isWorkingDay' => [
                'ignore_sign' => true,
                'ignore_auth' => true,
                'business_type' => ['type' => 'string', 'require' => true, 'desc' => '业务类型'],
                'region' => ['type' => 'string', 'require' => true, 'desc' => '区域'],
                'date' => ['type' => 'date', 'format' => 'timestamp', 'min' => '2000-01-01', 'max' => '2100-01-01', 'require' => true, 'desc' => '日期']
            ]
        ]);
    }

    /**
     * 更新工作日
     */
    public function update()
    {
        $ret = (new WorkingDaysDomain())->update($this->params['id'], $this->params['uid'], $this->params['days']);
        $this->returnJson($ret);
    }

    /**
     * 获取工作日列表
     */
    public function get()
    {
        $list = (new WorkingDaysDomain())->get($this->params['business_type'], $this->params['region'], $this->params['data_date']);
        $this->returnJson($list);
    }

    /**
     * 创建
     */
    public function create()
    {
        $ret = (new WorkingDaysDomain())->create($this->params['uid'], $this->params['business_type'], $this->params['region'],
            $this->params['data_date'], $this->params['days']);
        $this->returnJson(['id' => intval($ret)]);
    }

    /**
     * 判断某一天是否是工作日
     */
    public function isWorkingDay()
    {
        $ret = (new WorkingDaysDomain())->isWorkingDay($this->params['business_type'], $this->params['region'], $this->params['date']);
        $this->returnJson($ret);
    }
}