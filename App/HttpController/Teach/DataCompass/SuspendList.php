<?php

namespace App\HttpController\Teach\DataCompass;

use App\Domain\Teach\DataCompass\SuspendListDomain;
use Base\PassportApi;

/**
 * 数据罗盘 - 数据列表
 * Created by PhpStorm.
 * User: wuheng
 * Date: 2018/9/25
 * Time: 09:50
 */
class SuspendList extends PassportApi
{
    protected function getRules()
    {
        $rules = parent::getRules();
        return array_merge($rules, [
            'querySuspendList' => [
                'page' => ['type' => 'int', 'default' => 0, 'desc' => '查询页数'],
                'limit' => ['type' => 'int', 'default' => 20, 'desc' => '每页条数'],
                'sa' => ['type' => 'string', 'default' => '', 'desc' => '学管师名称'],
                'search_user' => ['type' => 'string', 'default' => '', 'desc' => '学员名/真名/uid'],
                'status' => ['type' => 'enum', 'range' => [1, 2, 3], 'default' => 1, 'desc' => '休学状态 1:正在休学 2:休学结束 3:即将开始'],
                'search_type' => ['type' => 'enum', 'range' => [0, 1, 2, 3], 'default' => 0, 'desc' => '休学时间查询方式'],
                'suspend_start_time' => ['type' => 'string', 'default' => '', 'desc' => '开始时间'],
                'suspend_end_time' => ['type' => 'string', 'default' => '', 'desc' => '结束时间'],
                'sort_order' => ['type' => 'string', 'default' => 'a.end_time asc', 'desc' => '排序'],
                'type' => ['type' => 'enum', 'require' => TRUE, 'range' => ['list', 'count'], 'default' => 'list', 'desc' => '数据返回类型 list:列表 count:数量'],
                'ignore_sign' => true,
                'ignore_auth' => true
            ],
            'delSuspendInfo' => [
                'id' => ['type' => 'int', 'default' => 0, 'desc' => 'ID'],
                's_uid' => ['type' => 'int', 'default' => 0, 'desc' => '休学UID']
            ],
            'outputSuspendInfo' => [
                'page' => ['type' => 'int', 'default' => 0, 'desc' => '查询页数'],
                'limit' => ['type' => 'int', 'default' => 20, 'desc' => '每页条数'],
                'sa' => ['type' => 'string', 'default' => '', 'desc' => '学管师名称'],
                'search_user' => ['type' => 'string', 'default' => '', 'desc' => '学员名/真名/uid'],
                'status' => ['type' => 'enum', 'range' => [1, 2, 3], 'default' => 1, 'desc' => '休学状态 1:正在休学 2:休学结束 3:即将开始'],
                'search_type' => ['type' => 'enum', 'range' => [0, 1, 2, 3], 'default' => 0, 'desc' => '休学时间查询方式'],
                'suspend_start_time' => ['type' => 'string', 'default' => '', 'desc' => '开始时间'],
                'suspend_end_time' => ['type' => 'string', 'default' => '', 'desc' => '结束时间'],
                'sort_order' => ['type' => 'string', 'default' => 'a.end_time asc', 'desc' => '排序'],
                'type' => ['type' => 'enum', 'require' => TRUE, 'range' => ['list', 'count'], 'default' => 'list', 'desc' => '数据返回类型 list:列表 count:数量'],
                'ignore_sign' => true,
                'ignore_auth' => true
            ]
        ]);
    }

    /**
     * 数据罗盘 - 获取休学列表
     */
    public function querySuspendList()
    {
        $result = (new SuspendListDomain())->querySuspendList($this->params);
        $this->returnJson($result);
    }

    /**
     * 数据罗盘 - 获取休息列表
     */
    public function delSuspendInfo()
    {
        $result = (new SuspendListDomain())->delSuspendInfo($this->params);
        $this->returnJson($result);
    }

    /**
     * 数据罗盘 - 休学列表 - 导出
     */
    public function outputSuspendInfo()
    {
        $this->params['page'] = 0;
        $this->params['limit'] = 0;
        $this->params['type'] = 'list';
        $data = (new SuspendListDomain())->querySuspendList($this->params);
        $result = (new SuspendListDomain())->outputSuspendInfo($data['suspendList'], $this->params['status']);
        $this->returnJson($result);

    }

}