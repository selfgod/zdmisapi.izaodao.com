<?php
/**
 * Created by PhpStorm.
 * User: songwenyao
 * Date: 2019/1/22
 * Time: 3:17 PM
 */
namespace App\Model\Market\Source;

use Base\BaseModel;
use Base\Db;
use function GuzzleHttp\Psr7\str;

class SourceOcModel extends BaseModel
{
    protected $source_oc_table = 'zd_source_oc';

    function deleteOc($condition)
    {
        $this->sWhereClean();
        $this->setSqlWhereAnd($condition);
        return $this->deleteData($this->source_oc_table);
    }
    function updateOc($condition, $data)
    {
        $this->sWhereClean();
        $this->setSqlWhereAnd($condition);
        return $this->updateData($this->source_oc_table, $data);
    }
}