<?php
/**
 * 老师、学管师管理
 * Created by PhpStorm.
 * User: wuheng
 * Date: 2018/9/25
 * Time: 09:50
 */

namespace App\Domain\Teach\Teacher;

use App\Model\Teach\Teacher\TeacherModel;
use Base\BaseDomain;

class TeacherDomain extends BaseDomain
{
    /**
     * 获取学管师列表
     * @return array
     * @throws \Exception
     */
    public function querySaTeacherList()
    {
        $teacher = new TeacherModel();
        $res = $teacher->querySaTeacherList();
        return ['sa'=>$res];
    }

    /**
     * 获取老师列表
     * @param array $where
     * @return array
     * @throws \Exception
     */
    public function queryTeacherList($perm)
    {
        $where = '';
        $bindValues = [];
        if(isset($perm['is_often']) && intval($perm['is_often']) >= 0){
            $where = 'is_often = :is_often';
            $bindValues = ['is_often' => $perm['is_often']];
        }
        $teacher = new TeacherModel();
        $res = $teacher->queryTeacherList($where, $bindValues);
        return ['teacher'=>$res];
    }

}