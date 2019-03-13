<?php

namespace App\HttpController\Teach\DataCompass;

use App\Domain\Teach\DataCompass\ClassEndListDomain;
use Base\PassportApi;

/**
 * 数据罗盘 - 结课未选课列表
 * Created by PhpStorm.
 * User: wuheng
 * Date: 2018/9/25
 * Time: 09:50
 */
class ClassEndList extends PassportApi
{
    protected function getRules()
    {
        $rules = parent::getRules();
        return array_merge($rules, [
            'queryClassEndList' => [
                'page' => ['type' => 'int', 'default' => 0, 'desc' => '查询页数'],
                'limit' => ['type' => 'int', 'default' => 20, 'desc' => '每页条数'],
                'end_days_start' => ['type' => 'int', 'default' => -1, 'desc' => '距上个班结课时间天数'],
                'end_days_end' => ['type' => 'int', 'default' => -1, 'desc' => '距上个班结课时间天数'],
                'sa_uid' => ['type' => 'int', 'default' => 0, 'desc' => '学管师'],
                'grade_id' => ['type' => 'int', 'default' => -1, 'desc' => '等级'],
                'time_type' => ['type' => 'enum', 'range' => [0, 1, 2, 3], 'default' => 0, 'desc' => '时间查询方式'],
                'start_time' => ['type' => 'string', 'default' => '', 'desc' => '开始时间'],
                'end_time' => ['type' => 'string', 'default' => '', 'desc' => '结束时间'],
                'user_identity' => ['type' => 'int', 'default' => 0, 'desc' => '身份标识'],
                'sub_identity' => ['type' => 'int', 'default' => 0, 'desc' => '身份标识'],
                'label' => ['type' => 'string', 'default' => '', 'desc' => '标签'],
                'type' => ['type' => 'enum', 'require' => TRUE, 'range' => ['list', 'count'], 'default' => 'list', 'desc' => '数据返回类型 list:列表 count:数量'],
                'ignore_sign' => true,
                'ignore_auth' => true
            ],
            'outputClassEndInfo' => [
                'page' => ['type' => 'int', 'default' => 0, 'desc' => '查询页数'],
                'limit' => ['type' => 'int', 'default' => 20, 'desc' => '每页条数'],
                'end_days_start' => ['type' => 'int', 'default' => -1, 'desc' => '距上个班结课时间天数'],
                'end_days_end' => ['type' => 'int', 'default' => -1, 'desc' => '距上个班结课时间天数'],
                'sa_uid' => ['type' => 'int', 'default' => 0, 'desc' => '学管师'],
                'grade_id' => ['type' => 'int', 'default' => -1, 'desc' => '等级'],
                'time_type' => ['type' => 'enum', 'range' => [0, 1, 2, 3], 'default' => 0, 'desc' => '时间查询方式'],
                'start_time' => ['type' => 'string', 'default' => '', 'desc' => '开始时间'],
                'end_time' => ['type' => 'string', 'default' => '', 'desc' => '结束时间'],
                'user_identity' => ['type' => 'int', 'default' => 0, 'desc' => '身份标识'],
                'sub_identity' => ['type' => 'int', 'default' => 0, 'desc' => '身份标识'],
                'label' => ['type' => 'string', 'default' => '', 'desc' => '标签'],
                'type' => ['type' => 'enum', 'require' => TRUE, 'range' => ['list', 'count'], 'default' => 'list', 'desc' => '数据返回类型 list:列表 count:数量'],
                'ignore_sign' => true,
                'ignore_auth' => true
            ]
        ]);
    }

    /**
     * 数据罗盘 - 获取结课未选课列表
     */
    public function queryClassEndList()
    {
        $result = (new ClassEndListDomain())->queryClassEndList($this->params);
        $this->returnJson($result);
    }

    /**
     * 数据罗盘 - 结课未选课列表 - 导出
     */
    public function outputClassEndInfo()
    {
        $this->params['page'] = 0;
        $this->params['limit'] = 100000;
        $this->params['type'] = 'list';
        $data = (new ClassEndListDomain())->queryClassEndList($this->params);
        $result = (new ClassEndListDomain())->outputClassEndInfo($data['endClassList']);
        $this->returnJson($result);

    }

}