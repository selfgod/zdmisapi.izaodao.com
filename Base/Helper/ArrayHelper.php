<?php
/**
 * Created by PhpStorm.
 * User: songwenyao
 * Date: 2018/11/16
 * Time: 上午11:15
 */
namespace Base\Helper;

class ArrayHelper
{
    /**
     * 对二维数组，给定两个键作为新数据的键值返回
     * @param $key
     * @param $val_key
     * @param $arr
     * @return array
     */
    static function array_key_value($key, $val_key, $arr)
    {
        $res = array();
        if (!empty($arr)) {
            foreach ($arr as $item) {
                if (isset($item[$key]) && isset($item[$val_key])) {
                    $res[$item[$key]] = $item[$val_key];
                }
            }
        }
        return $res;
    }

    /**
     * 一维数组排除
     * @param $val
     * @param $arr
     * @return mixed
     */
    static function array_except($val, $arr)
    {
        if (is_array($arr) && !empty($arr)) {
            foreach ($arr as $k => $item) {
                if ($item == $val) {
                    unset($arr[$k]);
                }
            }
        }
        return $arr;
    }

    /**
     * 在数组中删除一个给定key的值并返回
     * @param $key
     * @param $arr
     * @return string
     */
    static function array_key_shift($key, &$arr)
    {
        $val = '';
        if (isset($arr[$key])) {
            $val = $arr[$key];
            unset($arr[$key]);
        }
        return $val;
    }

    /**
     * 匹配键值
     * @param $arr
     * @param $key_arr
     * @return array
     */
    static function array_intersect_key_val(&$arr, $key_arr){
        $n_arr = [];
        if(is_array($arr) && !empty($arr)){
            foreach($arr as $k=>$v){
                if(in_array($k, $key_arr)) $n_arr[$k] = $v;
            }
        }
        return $n_arr?$n_arr:$arr;
    }

    /**
     * 递归返回数组中的值，注意返回单值
     * @param $key
     * @param array $arr
     * @return array|mixed
     */
    static function array_value_recursive($key, array $arr)
    {
        if (empty($arr)) return array();
        $val = array();
        array_walk_recursive($arr, function ($v, $k) use ($key, &$val) {
            if ($k == $key) array_push($val, $v);
        });
        return $val;
    }
}