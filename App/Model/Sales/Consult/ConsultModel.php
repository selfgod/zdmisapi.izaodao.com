<?php
/**
 * 特特
 * Created by PhpStorm.
 * User: songwenyao
 * Date: 2018/11/26
 * Time: 2:13 PM
 */
namespace App\Model\Sales\Consult;

use Base\BaseModel;
use Base\Db;

class ConsultModel extends BaseModel
{
    protected $consult_table = 'zd_zixun';
    protected $consult_uid_table = 'zd_zixun_uid';
    protected $consult_tag = 'zd_zixun_tag';


    function getBusinessType($zid)
    {
        $res = $this->getConsult(['id'=>$zid], 'business_type');
        if(!empty($res)) return $res['business_type'];
        return '';
    }
    /**
     * 基础条件 [mobile, qq, wechat, uid]
     * @param $condition
     * @param string $field
     * @return mixed
     * @throws \Exception
     */
    function getConsult($condition, $field='id')
    {
        if(isset($condition['uid']) && $condition['uid']){
            return Db::slave('zd_class')->select($field)
                ->from($this->consult_table.' as c')
                ->leftJoin($this->consult_uid_table.' as cu', 'cu.zid=c.id')
                ->where('cu.uid=:uid')
                ->bindValue('uid',$condition['uid'])
                ->groupBy(['cu.uid'])
                ->row();
        }

        $this->sWhereClean();
        $this->setSqlWhereAnd($condition);
        return Db::slave('zd_class')->select($field)
            ->from($this->consult_table)
            ->where($this->sWhere)
            ->bindValues($this->sBindValues)
            ->row();
    }

    function updateConsult($condition, $data)
    {
        $this->sWhereClean()->setSqlWhereAnd($condition);
        return $this->updateData($this->consult_table, $data);
    }

    function addConsult($data)
    {
        return $this->insertTable($this->consult_table, $data, 'zd_class');
    }

    function addTag($zid, $business_type, $tag)
    {
        //查询是否存在
        $this->sWhereClean()
            ->setSqlWhereAnd(['zid'=>$zid, 'business_type'=>$business_type]);
        $if_had = $this->selectData($this->consult_tag, 'id')->single();
        if(!empty($if_had)) return false;
        $_tag=explode('-',$tag);
        $data = [
            'zid'=>$zid,
            'business_type'=>$business_type,
            'tag'=>$tag,
            'tag0'=>isset($_tag[0])?$_tag[0]:'',
            'tag1'=>isset($_tag[1])?$_tag[1]:'',
            'tag2'=>isset($_tag[2])?$_tag[2]:''
        ];
        return $this->insertTable($this->consult_tag, $data, 'zd_class');
    }
}