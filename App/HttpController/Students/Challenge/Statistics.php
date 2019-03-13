<?php

namespace App\HttpController\Students\Challenge;

use Base\PassportApi;
use App\Model\Students\Challenge\Activity;

/**
 * 挑战赛活动统计
 * Created by Seldoon.
 * User: Seldoon.
 * Date: 2019-01-24 10:38
 */
class Statistics extends PassportApi
{
    
    public function getRules(): array {
        $rules = parent::getRules();
        $this_rules = [
            'getActivityTotal' => [
                'id' => ['type' => 'int', 'required' => false, 'desc' => 'ID'],
                'title' => ['type' => 'string', 'required' => false, 'desc' => '活动名称'],
                'category' => ['type' => 'int', 'required' => false, 'desc' => '活动面向的对象: 班级/学员'],
                'target' => ['type' => 'int', 'required' => false, 'desc' => '活动的班级ID/学员类型'],
                /*'start_date' => ['type' => 'string', 'required' => false, 'desc' => '开始日期'],
                'end_date' => ['type' => 'string', 'required' => false, 'desc' => '结束日期'],
                'status' => ['type' => '', 'required' => false, 'desc' => '活动状态'],*/
            ],
            'getActivityList' => [
                'id' => ['type' => 'int', 'required' => false, 'desc' => 'ID'],
                'title' => ['type' => 'string', 'required' => false, 'desc' => '活动名称'],
                'category' => ['type' => 'int', 'required' => false, 'desc' => '活动面向的对象: 班级/学员'],
                'target' => ['type' => 'int', 'required' => false, 'desc' => '活动的班级ID/学员类型'],
                /*'start_date' => ['type' => 'string', 'required' => false, 'desc' => '开始日期'],
                'end_date' => ['type' => 'string', 'required' => false, 'desc' => '结束日期'],
                'status' => ['type' => 'int', 'required' => false, 'desc' => '活动状态'],*/
                'page' => ['type' => 'int', 'desc' => '当前页'],
                'limit' => ['type' => 'int', '条数']
            ],
            'getTaskList' => [
                'challengeId' => ['type' => 'int', 'required' => false, 'desc' => '活动ID'],
                'taskId' => ['type' => 'int', 'required' => false, 'desc' => '任务ID'],
            ],
            'getTaskDetailList' => [
                'challengeId' => ['type' => 'int', 'required' => false, 'desc' => '活动ID'],
                'relateId' => ['type' => 'int', 'required' => false, 'desc' => '任务ID'],
                'page' => ['type' => 'int', 'desc' => '当前页'],
                'limit' => ['type' => 'int', '条数']
            ],
            'getTaskDetailTotal' => [
                'challengeId' => ['type' => 'int', 'required' => false, 'desc' => '活动ID'],
                'relateId' => ['type' => 'int', 'required' => false, 'desc' => '任务ID'],
            ],
            'exportActivityList' => [
                'id' => ['type' => 'int', 'required' => false, 'desc' => 'ID'],
                'title' => ['type' => 'string', 'required' => false, 'desc' => '活动名称'],
                'category' => ['type' => 'int', 'required' => false, 'desc' => '活动面向的对象: 班级/学员'],
                'target' => ['type' => 'int', 'required' => false, 'desc' => '活动的班级ID/学员类型']
            ],
        ];
        
        return array_merge($rules, $this_rules);
    }
    
    /**
     * 挑战赛活动 - 活动总数量
     */
    public function getActivityTotal(): void {
        $this->returnJson((new Activity())->getActivityListTotal($this->params));
    }
    
    /**
     * 挑战赛活动 - 活动列表
     * @throws \Exception
     */
    public function getActivityList(): void {
        $this->returnJson((new Activity())->getActivityList($this->params));
    }
    
    /**
     * 挑战赛活动 - 活动导出
     * @throws \Exception
     */
    public function exportActivityList():void {
        $this->returnJson((new Activity())->exportActivityList($this->params));
    }
    
    /**
     * 挑战赛活动 - 任务列表
     * @throws \Exception
     */
    public function getTaskList(): void {
        $this->returnJson((new Activity())->getTaskList($this->params['challengeId']));
    }
    
    /**
     * 挑战赛活动 - 任务学员完成情况数量
     * @throws \Exception
     */
    public function getTaskDetailTotal(): void {
        $this->returnJson((new Activity())->getTaskDetailTotal($this->params));
    }
    
    /**
     * 挑战赛活动 - 任务学员完成情况列表
     * @throws \Exception
     */
    public function getTaskDetailList(): void {
        $this->returnJson((new Activity())->getTaskDetail($this->params));
    }
}