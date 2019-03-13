<?php

namespace App\Model\Common;

use Base\BaseModel;
use Base\Db;

class Category extends BaseModel
{
    /**
     * 通过KEY取系统分类
     * @param string $key
     * @return bool | array
     */
    public function getCategoryValue($key = ''){
        if(!$key)
            return false;
        $category = Db::slave('zd_netschool')->from('sty_sys_category')
            ->select('`id`, `max_num`, `group_name`')->where('`key` = :key and is_del=0 ')
            ->bindValue('key', $key)->row();
        if(empty($category))
            return false;
        if(isset($category['id']) && $category['id']>0){
            $category['item'] = Db::slave('zd_netschool')->from('sty_sys_category_item')
                ->select('`id`, `category_id`, `name`, `order`')->where('category_id = :category_id and is_del = 0 ')
                ->bindValue('category_id', $category['id'])->orderByASC(['`order`'])->query();
        }
        return $category;
    }

    /**
     * 通过KEY取系统分类 序列化
     * @param string $key
     * @return bool
     */
    public function getConf($key = ''){
        $category = $this->getCategoryValue($key);
        if($category){
            foreach($category['item'] as $k=>$v){
                $conf[$v['order']] = $v['name'];
            }
            return $conf;
        }
        return false;
    }

}