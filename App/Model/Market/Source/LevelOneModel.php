<?php
/**
 * Created by PhpStorm.
 * User: songwenyao
 * Date: 2019/1/24
 * Time: 9:43 AM
 */
namespace App\Model\Market\Source;

use Base\BaseModel;

class LevelOneModel extends BaseModel
{
    protected $level_one_table = 'zd_source_level_one';

    function setImport($id, $import=1, $log='')
    {
        $this->sWhereClean()->setSqlWhereAnd(['id'=>$id]);
        return $this->updateData($this->level_one_table, ['is_import'=>$import, 'log'=>$log.' '.date('Y-m-d H:i:s')]);
    }
}