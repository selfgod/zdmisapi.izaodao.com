<?php

namespace App\HttpController\Course;

use App\Domain\Course\LessonDomain;
use Base\PassportApi;

class Lesson extends PassportApi
{
    protected function getRules()
    {
        $rules = parent::getRules();
        return array_merge($rules, [
            'recordLessonData' => [
                'scheduleId' => [
                    'type' => 'int',
                    'default' => 0,
                    'desc' => '阶段课程ID'
                ],
                'name' => [
                    'type' => 'string',
                    'default' => '',
                    'desc' => '课件名（支持模糊搜索）'
                ],
                'teacherUid' => [
                    'type' => 'int',
                    'default' => 0,
                    'desc' => '主讲教师uid'
                ],
                'startTime' => [
                    'type' => 'date',
                    'default' => '',
                    'desc' => '开课时间'
                ],
                'setRecord' => [
                    'type' => 'enum',
                    'require' => TRUE,
                    'range' => [0, 1],
                    'desc' => '是否设置录播地址 0:未设置 1:已设置'
                ],
                'page' => [
                    'type' => 'int',
                    'default' => 1,
                    'min' => 1,
                    'desc' => '页数'
                ],
                'type' => [
                    'type' => 'enum',
                    'require' => TRUE,
                    'range' => ['list', 'count'],
                    'default' => 'list',
                    'desc' => '数据返回类型 list:列表 count:数量'
                ]
            ],
            'saveLessonRecordLink' => [
                'lessonId' => [
                    'type' => 'int',
                    'require' => TRUE,
                    'desc' => '课件ID'
                ],
                'link' => [
                    'type' => 'string',
                    'require' => TRUE,
                    'min' => 1,
                    'max' => 500,
                    'desc' => '录播课件地址'
                ],
                'timeLen' => [
                    'type' => 'int',
                    'require' => TRUE,
                    'min' => 1,
                    'desc' => '录播时长(单位秒)'
                ]
            ],
            'delLessonRecordLink' => [
                'lessonId' => [
                    'type' => 'int',
                    'require' => TRUE,
                    'desc' => '课件ID'
                ]
            ],
            'lessonRecordUrl' => [
                'lessonId' => [
                    'type' => 'int',
                    'require' => TRUE,
                    'desc' => '课件ID'
                ]
            ],
            'openLessonList' => [
                'name' => [
                    'type' => 'string',
                    'default' => '',
                    'desc' => '课件名（支持模糊搜索）'
                ],
                'teacherId' => [
                    'type' => 'int',
                    'default' => 0,
                    'min' => 0,
                    'desc' => '教师ID'
                ],
                'startDate' => [
                    'type' => 'date',
                    'default' => '',
                    'desc' => '上课开始日期'
                ],
                'endDate' => [
                    'type' => 'date',
                    'default' => '',
                    'desc' => '上课结束日期'
                ],
                'page' => [
                    'type' => 'int',
                    'default' => 1,
                    'min' => 1,
                    'desc' => '页码'
                ],
                'limit' => [
                    'type' => 'int',
                    'default' => 20,
                    'min' => 1,
                    'desc' => '条目数'
                ],
                'ignore_auth' => TRUE,
                'ignore_sign' => TRUE,
            ],
            'listenLessonList' => [
                'name' => [
                    'type' => 'string',
                    'default' => '',
                    'desc' => '班级名（支持模糊搜索）'
                ],
                'teacherId' => [
                    'type' => 'int',
                    'default' => 0,
                    'min' => 0,
                    'desc' => '教师ID'
                ],
                'startDate' => [
                    'type' => 'date',
                    'default' => '',
                    'desc' => '上课开始日期'
                ],
                'endDate' => [
                    'type' => 'date',
                    'default' => '',
                    'desc' => '上课结束日期'
                ],
                'page' => [
                    'type' => 'int',
                    'default' => 1,
                    'min' => 1,
                    'desc' => '页码'
                ],
                'limit' => [
                    'type' => 'int',
                    'default' => 20,
                    'min' => 1,
                    'desc' => '条目数'
                ],
                'ignore_auth' => TRUE,
                'ignore_sign' => TRUE,
            ]
        ]);
    }

    /**
     * 录播课件列表
     * @throws \Exception
     */
    public function recordLessonData()
    {
        $result = (new LessonDomain())->getRecordLessonList($this->params);
        $this->returnJson($result);
    }

    /**
     * 更新录播课件地址
     */
    public function saveLessonRecordLink()
    {
        (new LessonDomain())->saveLessonRecordLink($this->params);
        $this->returnJson();
    }

    /**
     * 删除课件录播地址
     */
    public function delLessonRecordLink()
    {
        (new LessonDomain())->delLessonRecordLink($this->params);
        $this->returnJson();
    }

    /**
     * 获取课程录播地址
     */
    public function lessonRecordUrl()
    {
        $url = (new LessonDomain())->getLessonRecordUrl($this->params['lessonId']);
        $this->returnJson(['recordUrl' => $url]);
    }

    /**
     * 公开课列表
     * @throws \Exception
     */
    public function openLessonList()
    {
        $res = (new LessonDomain())->getOpenLessonList($this->params);
        $this->returnJson($res);
    }

    /**
     * 旁听课列表
     * @throws \Exception
     */
    public function listenLessonList()
    {
        $res = (new LessonDomain())->getListenLessonList($this->params);
        $this->returnJson($res);
    }
}