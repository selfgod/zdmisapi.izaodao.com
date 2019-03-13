<?php
namespace Base;

class BaseModel {
    //sql查询条件拼接串
    protected $sWhere = ' 1=1 ';
    protected $sBindValues = [];

    /**
     * 设置查询条件
     * @param array $perm [key => value]
     * @param string $type ['=', '>', '>=', '<', '<=', 'like', 'LIKE']
     */
    public function setSqlWhere($perm = [], $type = '=')
    {
        if(!empty($perm) && in_array($type, ['=', '>', '>=', '<', '<=', 'like', 'LIKE'])){
            foreach ($perm as $key => $item) {
                if(!empty($item)){
                    $this->sWhere .= ' AND ( `' . $key . '` ' . $type. ' :' . $key . ' ) ';
                    if(in_array($type, ['like', 'LIKE'])){
                        $this->sBindValues[$key] = "'%" . $item . "%'";
                    }else{
                        $this->sBindValues[$key] = $item;
                    }
                }
            }
        }
    }

    /**
     * 设置查询条件
     * @param array $perm [key => value]
     * @param string $type ['=', '>', '>=', '<', '<=', 'like', 'LIKE']
     */
    public function setSqlWhereOR($perm = [], $type = '=')
    {
        if(!empty($perm) && in_array($type, ['=', '>', '>=', '<', '<=', 'like', 'LIKE'])){
            $this->sWhere .= ' AND ( 1=2 ';
            foreach ($perm as $key => $item) {
                if(!empty($item)){
                    $this->sWhere .= ' OR ( `' . $key . '` ' . $type. ' :' . $key . ' ) ';
                    if(in_array($type, ['like', 'LIKE'])){
                        $this->sBindValues[$key] = "'%" . $item . "%'";
                    }else{
                        $this->sBindValues[$key] = $item;
                    }
                }
            }
            $this->sWhere .= ' ) ';
        }
    }

    /**
     * 设置查询条件 - 拼接where字符串
     * @param $info ' 1=1 and 2=2 '
     */
    public function setSqlWhereString($info)
    {
        if(!empty($info)){
            $this->sWhere .= ' and ( ' . $info . ' ) ';
        }
    }


    /**
     * 生成where in查询字符串
     * @param $key
     * @param array $values
     * @return string
     */
    public function whereIn($key, array $values)
    {
        if (empty($values)) {
            return '';
        }

        $where_in = [];
        foreach ($values as $value) {
            $where_in[] = $this->escape($value);
        }

        return $key . ' IN(' . implode(', ', $where_in) . ')';
    }

    /**
     * "Smart" Escape String
     *
     * Escapes data based on type
     * Sets boolean and null types
     *
     * @param    string
     * @return    mixed
     */
    public function escape($str)
    {
        if (is_array($str)) {
            $str = array_map([&$this, 'escape'], $str);
            return $str;
        } elseif (is_string($str)) {
            return "'" . $this->escape_str($str) . "'";
        } elseif (is_bool($str)) {
            return ($str === FALSE) ? 0 : 1;
        } elseif ($str === NULL) {
            return 'NULL';
        }

        return $str;
    }

    /**
     * Escape String
     *
     * @param    string|string[] $str Input string
     * @param    bool $like Whether or not the string will be used in a LIKE condition
     * @return    string
     */
    public function escape_str($str, $like = FALSE)
    {
        if (is_array($str)) {
            foreach ($str as $key => $val) {
                $str[$key] = $this->escape_str($val, $like);
            }
            return $str;
        }
        return $this->_escape_str($str);
    }

    /**
     * Platform-dependant string escape
     *
     * @param    string
     * @return    string
     */
    protected function _escape_str($str)
    {
        return str_replace("'", "''", $this->remove_invisible_characters($str));
    }

    /**
     * Remove Invisible Characters
     *
     * This prevents sandwiching null characters
     * between ascii characters, like Java\0script.
     *
     * @param    string
     * @return    string
     */
    protected function remove_invisible_characters($str)
    {
        $non_displayables = ['/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S'];    // 00-08, 11, 12, 14-31, 127
        do {
            $str = preg_replace($non_displayables, '', $str, -1, $count);
        } while ($count);
        return $str;
    }

    /**
     * 并条件，可指定操作符
     * @param array $condition 数组
     * @example [
        'type'=>['in'=>['new','oc']],
        'dateline'=>['>='=>$assign_date_start,'<='=>$assign_date_end],
        ]
     * @return object $this
     */
    public function setSqlWhereAnd($condition=[])
    {
        foreach($condition as $field=>$value){
            if(is_array($value)){
                $c=0;
                foreach($value as $op=>$val){
                    if(is_numeric($op)) $op = ' = ';
                    if(is_array($val)){
                        $val_arr = [];
                        foreach($val as $_v){
                            $val_arr[] = $this->_escape_str($_v);
                        }
                        $val = implode("','", $val_arr);
                        $this->sWhere .= " and $field {$op} ('{$val}')";
                    }else{
                        $var_field = str_replace('.','_', $field);
                        if(isset($this->sBindValues[$var_field])) $var_field.=++$c;
                        $this->sWhere .= " and $field {$op} :{$var_field}";
                        $this->sBindValues[$var_field] = $this->_escape_str($val);
                    }
                }
            }else{
                $var_field = str_replace('.','_', $field);
                $this->sWhere .= " and {$field} = :{$var_field}";
                $this->sBindValues[$var_field] = $this->_escape_str($value);
            }
        }
        return $this;
    }

    /**
     * @return $this
     */
    function sWhereClean()
    {
        $this->sWhere = '1=1';
        $this->sBindValues = [];
        return $this;
    }

    /**
     * 使用sWhere条件查询
     * @param $table
     * @param string $field
     * @param string $db
     * @return \Workerman\MySQL\Connection
     */
    function selectData($table, $field='*', $db = 'zd_class')
    {
        return Db::slave($db)->select($field)
            ->from($table)
            ->where($this->sWhere)
            ->bindValues($this->sBindValues);
    }
    /**
     * 使用sWhere条件删除
     * @param $table
     * @param string $db
     * @return bool
     */
    function deleteData($table, $db = 'zd_class')
    {
        return $this->deleteTable($table, $this->sWhere, $this->sBindValues, $db);
    }

    /**
     *  使用sWhere条件更新
     * @param $table
     * @param $data
     * @param string $db
     * @return bool
     */
    function updateData($table, $data, $db = 'zd_class')
    {
        return $this->updateTable($table, $this->sWhere, $this->sBindValues, $data, $db);
    }

    /**
     * 更新数据表
     * @param $table
     * @param $w
     * @param array $bindValues
     * @param array $save
     * @param string $DBName
     * @return bool
     */
    public function updateTable($table, $w, array $bindValues, array $save, $DBName = 'zd_netschool'): bool
    {
        $query = Db::master($DBName)->update($table)->where($w)->bindValues($bindValues)->cols($save)->query();
        return empty($query) ? FALSE : TRUE;
    }

    /**
     * 插入表
     * @param $table
     * @param array $add
     * @param string $DBName
     * @return mixed
     */
    public function insertTable($table, array $add, $DBName = 'zd_netschool')
    {
        return Db::master($DBName)->insert($table)->cols($add)->query();
    }

    /**
     * 删除表信息
     * @param $table
     * @param $w
     * @param array $bindValues
     * @param string $DBName
     * @return bool
     */
    public function deleteTable($table, $w, array $bindValues, $DBName = 'zd_netschool'): bool
    {
        $query = Db::master($DBName)->delete($table)->where($w)->bindValues($bindValues)->query();
        return empty($query) ? FALSE : TRUE;
    }
}