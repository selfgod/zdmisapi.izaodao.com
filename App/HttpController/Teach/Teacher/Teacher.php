<?php

namespace App\HttpController\Teach\Teacher;

use App\Domain\Teach\Teacher\TeacherDomain;
use Base\PassportApi;

class Teacher extends PassportApi
{
    protected function getRules()
    {
        $rules = parent::getRules();
        return array_merge($rules, [
            'querySaTeacherList' => [
                'ignore_sign' => true,
                'ignore_auth' => true
            ],
            'queryTeacherList' => [
                'is_often' => [
                    'type' => 'enum',
                    'range' => [-1, 0, 1],
                    'default' => '-1',
                    'desc' => '是否是常用教师 -1:全部 1 常用 0 不常用'
                ],
                'ignore_sign' => true,
                'ignore_auth' => true
            ],
            'teacherList' => [
                'often' => [
                    'type' => 'enum',
                    'range' => [-1, 0, 1],
                    'default' => '-1',
                    'desc' => '是否是常用教师 -1:全部 1 常用 0 不常用'
                ],
                //'ignore_sign' => true,
                'ignore_auth' => true
            ]
        ]);
    }

    /**
     * 获取学管师列表
     */
    public function querySaTeacherList()
    {
        $result = (new TeacherDomain())->querySaTeacherList();
        $this->returnJson($result);
    }

    /**
     * 获取老师列表
     */
    public function queryTeacherList()
    {
        $result = (new TeacherDomain())->queryTeacherList(['is_often' => $this->params['is_often']]);
        $this->returnJson($result);
    }

    /**
     * 获取老师列表
     * @throws \Exception
     */
    public function teacherList(){
        $result = (new TeacherDomain())->queryTeacherList(['is_often' => intval($this->params['often'])]);
        $teacher = $result['teacher'] ?: [];
        $this->returnJson($teacher);
    }

}