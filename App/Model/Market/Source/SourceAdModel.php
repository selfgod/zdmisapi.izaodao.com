<?php
/**
 * Created by PhpStorm.
 * User: songwenyao
 * Date: 2019/2/18
 * Time: 2:36 PM
 */
namespace App\Model\Market\Source;

use Base\BaseModel;

class SourceAdModel extends BaseModel
{
    private $base_table = 'zd_crm_source_ad_tag';

    function insertRow($store_id, $tag)
    {
        $this->sWhereClean()->setSqlWhereAnd(['store_id'=>$store_id]);
        $res = $this->selectData($this->base_table,'id')->row();
        if(!empty($res)) return false;
        return $this->insertTable($this->base_table, [
            'store_id'=>$store_id,
            'tag'=>$tag
        ], 'zd_class');
    }
}