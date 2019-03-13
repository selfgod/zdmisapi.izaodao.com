<?php

namespace App\HttpController\Teach\DataCompass;

use App\Domain\Teach\DataCompass\ClassEndListDomain;
use App\Domain\Teach\DataCompass\JoinScheduleLogDomain;
use Base\PassportApi;

/**
 * 数据罗盘 - 加删课记录列表
 * Created by PhpStorm.
 * User: wuheng
 * Date: 2018/10/23
 * Time: 09:50
 */
class JoinScheduleLog extends PassportApi
{
    protected function getRules()
    {
        $rules = parent::getRules();
        return array_merge($rules, [
            'queryJoinScheduleLogList' => [
                'page' => ['type' => 'int', 'default' => 0, 'desc' => '查询页数'],
                'limit' => ['type' => 'int', 'default' => 20, 'desc' => '每页条数'],
                'grade_id' => ['type' => 'int', 'default' => -1, 'desc' => '等级'],
                'time_type' => ['type' => 'enum', 'range' => [0, 1, 2, 3], 'default' => 0, 'desc' => '时间查询方式'],
                'start_time' => ['type' => 'string', 'default' => '', 'desc' => '开始时间'],
                'end_time' => ['type' => 'string', 'default' => '', 'desc' => '结束时间'],
                'flag' => ['type' => 'enum', 'range' => [0, 1, 2], 'default' => 0, 'desc' => '操作类型 1学员操作 2后台操作'],
                'uid_name' => ['type' => 'string', 'default' => '', 'desc' => '学员UID/用户名'],
                'opt_uid_name' => ['type' => 'string', 'default' => '', 'desc' => '操作人UID/用户名'],
                'type' => ['type' => 'enum', 'require' => TRUE, 'range' => ['list', 'count'], 'default' => 'list', 'desc' => '数据返回类型 list:列表 count:数量'],
                'ignore_sign' => true,
                'ignore_auth' => true
            ],
            'outputJoinScheduleLog' => [
                'page' => ['type' => 'int', 'default' => 0, 'desc' => '查询页数'],
                'limit' => ['type' => 'int', 'default' => 20, 'desc' => '每页条数'],
                'grade_id' => ['type' => 'int', 'default' => -1, 'desc' => '等级'],
                'time_type' => ['type' => 'enum', 'range' => [0, 1, 2, 3], 'default' => 0, 'desc' => '时间查询方式'],
                'start_time' => ['type' => 'string', 'default' => '', 'desc' => '开始时间'],
                'end_time' => ['type' => 'string', 'default' => '', 'desc' => '结束时间'],
                'flag' => ['type' => 'enum', 'range' => [0, 1, 2], 'default' => 0, 'desc' => '操作类型 1学员操作 2后台操作'],
                'uid_name' => ['type' => 'string', 'default' => '', 'desc' => '学员UID/用户名'],
                'opt_uid_name' => ['type' => 'string', 'default' => '', 'desc' => '操作人UID/用户名'],
                'type' => ['type' => 'enum', 'require' => TRUE, 'range' => ['list', 'count'], 'default' => 'list', 'desc' => '数据返回类型 list:列表 count:数量'],
                'ignore_sign' => true,
                'ignore_auth' => true
            ]
        ]);
    }

    /**
     * 数据罗盘 - 获取结课未选课列表
     */
    public function queryJoinScheduleLogList()
    {
        $result = (new JoinScheduleLogDomain())->queryJoinScheduleLogList($this->params);
        $this->returnJson($result);
    }

    /**
     * 数据罗盘 - 结课未选课列表 - 导出
     */
    public function outputJoinScheduleLog()
    {
        $this->params['page'] = 0;
        $this->params['limit'] = 100000;
        $this->params['type'] = 'list';
        $data = (new JoinScheduleLogDomain())->queryJoinScheduleLogList($this->params);
        $result = (new JoinScheduleLogDomain())->outputJoinScheduleLog($data['joinScheduleLogList']);
        $this->returnJson($result);

    }

}