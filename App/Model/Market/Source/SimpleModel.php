<?php
namespace App\Model\Market\Source;

use Base\BaseModel;
use Base\Db;

class SimpleModel extends BaseModel
{
    /**
     * * aryTerm transArrayTerms转化后的查询条件
     * @过滤没有输入的sql查询条件并转化成where条件.
     * @param $aryTerm
     * @param int $useOr
     * @return string
     */
    function getWhereSql($aryTerm,$useOr = 0) {
        $whereCause = '';
        if(is_array($aryTerm)){
            if (count($aryTerm) > 0) {
                $has_where = '';
                foreach ($aryTerm as $key=>$value) {
                    if($key == 'or_use') continue;
                    $has_where = $has_where ? ($useOr === 0 ? ' AND ' : ' OR ') : ' ';
                    $whereCause .= $has_where. " ".$key." = '".$value."'";
                }
            }
        }else{
            $whereCause = $aryTerm;
        }
        return $whereCause;
    }

    /**
     * 简单查询
     * @param $table
     * @param $field
     * @param $where_arr
     * @param string $aim_field
     * @param bool $single
     * @param array $order
     * @return mixed|null
     */
    public function simpleSelect($table, $field, $where_arr, $aim_field = '',$single=false,$order=['id','asc']){
        $where = $this->getWhereSql($where_arr,isset($where_arr['or_use'])?1:0);
        if(strtolower($order[1]) == 'asc'){
            $res =  Db::slave('zd_class')->select($field)
                ->from($table)
                ->where($where)
                ->orderByASC([$order[0]])
                ->query();
        }else{
            $res =  Db::slave('zd_class')->select($field)
                ->from($table)
                ->where($where)
                ->orderByDESC([$order[0]])
                ->query();
        }
        if($single){
            return $res ? $res[0]: null;
        }
        return $aim_field ? ($res?$res[0][$aim_field]:null) : $res;
    }

    public function simpleSelectRead($table, $field, $where_arr, $aim_field = '',$single=false,$order=['id','asc']){
        $where = $this->getWhereSql($where_arr,isset($where_arr['or_use'])?1:0);
        if(strtolower($order[1]) == 'asc'){
            $res =  Db::Master('zd_class')->select($field)
                ->from($table)
                ->where($where)
                ->orderByASC([$order[0]])
                ->query();
        }else{
            $res =  Db::Master('zd_class')->select($field)
                ->from($table)
                ->where($where)
                ->orderByDESC([$order[0]])
                ->query();
        }
        if($single){
            return $res ? $res[0]: null;
        }
        return $aim_field ? ($res?$res[0][$aim_field]:null) : $res;
    }

    /**
     * 简单左联询
     * @param $table
     * @param $left_table
     * @param $field
     * @param $where_arr
     * @param $cond
     * @return mixed
     * @throws \Exception
     */
    public function simpleLeftJoinSelect($table,$left_table,$field,$where_arr,$cond){
        $where = $this->getWhereSql($where_arr);
        $res = Db::slave('zd_class')
            ->select($field)
            ->from($table)
            ->leftJoin($left_table, $cond)
            ->where($where)
            ->query();
        return $res;
    }

    /**
     * 简单新增
     * @param $table
     * @param $data
     * @return mixed
     */
    public function simpleInsert($table,$data){
        $id = Db::master('zd_class')
            ->insert($table)
            ->cols($data)
            ->query();
        return $id;
    }

    /**
     * 简单更新
     * @param $table
     * @param $data
     * @param $where_arr
     * @return mixed
     */
    public function simpleUpdate($table,$data,$where_arr){
        $where = $this->getWhereSql($where_arr);
        $res = Db::master('zd_class')
            ->update($table)
            ->where($where)
            ->cols($data)
            ->query();
        return $res;
    }

    /**
     * 简单删除
     * @param $table
     * @param $where_arr
     * @return mixed
     */
    public function simpleDelete($table, $where_arr){
        $where = $this->getWhereSql($where_arr);
        $res = Db::master('zd_class')
            ->delete($table)
            ->where($where)
            ->query();
        return $res;
    }
}