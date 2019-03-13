<?php

namespace App\HttpController\Teach\Course;

use App\Domain\Teach\Course\CourseDomain;
use Base\PassportApi;

/**
 * 排课
 * Created by PhpStorm.
 * User: wuheng
 * Date: 2018/9/25
 * Time: 09:50
 */
class Course extends PassportApi
{
    protected function getRules()
    {
        $rules = parent::getRules();
        return array_merge($rules, [
            'queryCourseWeekList' => [
                'page' => ['type' => 'int', 'default' => 0, 'desc' => '查询页数'],
                'limit' => ['type' => 'int', 'default' => 20, 'desc' => '每页条数'],
                'start_time' => ['type' => 'string', 'default' => '', 'desc' => '开始时间'],
                'end_time' => ['type' => 'string', 'default' => '', 'desc' => '结束时间'],
                'action' => ['type' => 'enum',  'require' => TRUE, 'range' => ['week_all', 'week_other'], 'desc' => '查询 全职教师 或 兼职'],
                'teacher_id' => ['type' => 'int', 'default' => 0, 'desc' => '老师ID'],
                'group' => ['type' => 'enum', 'range' => [0, 1, 2, 3], 'default' => 0, 'desc' => '全职老师分组 1 一组 2二组 3 教研 0其他'],
                'type' => ['type' => 'enum', 'require' => TRUE, 'range' => ['list', 'count'], 'default' => 'list', 'desc' => '数据返回类型 list:列表 count:数量'],
                'ignore_sign' => true,
                'ignore_auth' => true
            ]
        ]);
    }

    /**
     * 排课 - 每周课表
     */
    public function queryCourseWeekList()
    {
        $result = (new CourseDomain())->queryCourseWeekList($this->params);
        $this->returnJson($result);
    }

}