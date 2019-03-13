<?php
/**
 * Created by Seldoon.
 * User: Seldoon.
 * Date: 2018-12-20 14:31
 *                  女神保佑 代码无BUG
 *                      .::::.
 *                     .::::::::.
 *                    :::::::::::
 *                ..:::::::::::'
 *              '::::::::::::'
 *                .::::::::::
 *           '::::::::::::::..
 *               ..::::::::::::.
 *             ``::::::::::::::::
 *              ::::``:::::::::'        .:::.
 *             ::::'   ':::::'       .::::::::.
 *           .::::'     :::::     .:::::::'::::.
 *          .:::'       ::::   .:::::::::' ':::::.
 *          .::'        :::::.:::::::::'     ':::::.
 *         .::'         ::::::::::::::'        ``::::.
 *     ...:::           ::::::::::::'             ``::.
 *    ```` ':.          ':::::::::'                 ::::..
 *                      '.::::::'                   ':'````..
 *                       '.:::'
 */

namespace App\Model\Operate;

use Base\BaseModel;
use Base\Db;

class OperateManagement extends BaseModel
{
    /**
     * 获取规则
     * @return array
     */
    public function getRule(): array {
        return [
            'saveWallpaperCategory' => [
                'picture' => ['type' => 'string', 'require' => true, 'desc' => '图片',],
                'title' => ['type' => 'string', 'require' => true, 'desc' => '标题'],
                'uid' => ['type' => 'int', 'desc' => '操作人'],
                'id' => ['type' => 'int', 'ID']
            ],
            'delWallpaperCategory' => [
                'id' => ['type' => 'int', 'require' => true, 'ID']
            ],
            'getWallpaperCategoryList' => [
                'limit' => ['type' => 'int', '分页'],
                'page' => ['type' => 'int', '当前页']
            ],
            'saveWallpaper' => [
                'id' => ['type' => 'int', 'ID'],
                'picture' => ['type' => 'string', 'require' => true, 'desc' => '图片'],
                'title' => ['type' => 'string', 'require' => true, 'desc' => '标题'],
                'category' => ['type' => 'int', 'require' => true, 'desc' => '分类'],
                'uid' => ['type' => 'int', 'desc' => '操作人']
            ],
            'getWallpaperList' => [
                'category' => ['type' => 'int', 'desc' => '分类']
            ],
            'getWallpaperTotal' => [
                'category' => ['type' => 'int', 'desc' => '分类']
            ],
            'delWallpaper' => ['id' => ['type' => 'int', 'require' => true, 'ID']],
            'getArticlePageViewTotal' => [
                'startDate' => ['type' => 'string', 'desc' => '开始日期'],
                'endDate' => ['type' => 'string', 'desc' => '结束日期'],
                'articleId' => ['type' => 'int', 'require' => true, 'desc' => '文章ID']
            ],
            'getArticlePageList' => [
                'startDate' => ['type' => 'string', 'desc' => '开始日期'],
                'endDate' => ['type' => 'string', 'desc' => '结束日期'],
                'articleId' => ['type' => 'int', 'require' => true, 'desc' => '文章ID'],
                'limit' => ['type' => 'int', '分页'],
                'page' => ['type' => 'int', '当前页']
            ],
            'getArticlePvUvTotal' => [
                'startDate' => ['type' => 'string', 'desc' => '开始日期'],
                'endDate' => ['type' => 'string', 'desc' => '结束日期'],
                'articleId' => ['type' => 'int', 'require' => true, 'desc' => '文章ID'],
            ],
            'getAuditSwitchTotal' => [
                //'app' => ['type' => 'int', 'require' => true, 'desc' => '应用 1 五十音图'],
            ],
            'getAuditSwitchList' => [
                //'app' => ['type' => 'int', 'require' => true, 'desc' => '应用 1 五十音图'],
                'limit' => ['type' => 'int', '分页'],
                'page' => ['type' => 'int', '当前页']
            ],
            'saveAuditSwitch' => [
                'id' => ['type' => 'int', 'desc' => 'ID'],
                //'app' => ['type' => 'int', 'require' => true, 'desc' => '应用 1 五十音图'],
                //'key' => ['type' => 'string', 'require' => true, 'desc' => 'Key'],
                'is_open' => ['type' => 'int', 'require' => true, 'desc' => '是否开启标识'],
                'desc' => ['type' => 'string', 'require' => true, 'desc' => '描述信息'],
                'uid' => ['type' => 'int', 'desc' => '操作用户'],
            ],
            'delAuditSwitch' => [
                'id' => ['type' => 'int', 'require' => true, 'desc' => 'ID'],
            ]
            
        ];
    }
    
    /**
     * 内容管理 - 壁纸管理 获取壁纸
     * @param $condition
     * @return array
     * @throws \Exception
     */
    public function getWallpaperList($condition): array {
        $where = 'wallpaper.is_del = :wallpaper_is_del AND category.is_del = :category_is_del';
        $bind = ['wallpaper_is_del' => 0, 'category_is_del' => 0];
        if (!empty($condition['category'])) {
            $where .= ' AND wallpaper.category = :wallpaper_category';
            $bind['wallpaper_category'] = $condition['category'];
        }
        
        return Db::slave('zd_netschool')
            ->from('app_fifty_wallpaper AS wallpaper')
            ->leftJoin('app_fifty_wallpaper_category AS category', 'wallpaper.category = category.id')
            ->select('wallpaper.*, category.title as category_name')
            ->where($where)
            ->bindValues($bind)
            ->query();
    }
    
    /**
     * 内容管理 - 壁纸管理 获取壁纸分类
     * @return mixed
     */
    public function getWallpaperCategoryName() {
        return Db::slave('zd_netschool')
            ->from('app_fifty_wallpaper_category')
            ->select('id, title')
            ->where('is_del=0')
            ->query();
    }
    
    /**
     * 内容管理 - 壁纸管理 获取壁纸总数量
     * @param $condition
     * @return int
     */
    public function getWallpaperTotal($condition): int {
        $where = 'is_del = :is_del';
        $bind = ['is_del' => 0];
        if (!empty($condition['category'])) {
            $where .= ' AND category = :category';
            $bind['category'] = $condition['category'];
        }
        return Db::slave('zd_netschool')
            ->from('app_fifty_wallpaper')
            ->select('COUNT(*)')
            ->where($where)
            ->bindValues($bind)
            ->single();
    }
    
    /**
     * 内容管理 - 壁纸管理 保存壁纸
     * @param $params
     * @return int
     */
    public function saveWallpaper($params): int {
        $id = $params['id'];
        $data = [
            'title' => $params['title'],
            'picture' => $params['picture'],
            'category' => $params['category']
        ];
        
        if ($id) {
            $data['modify_user'] = $params['uid'];
            $result = Db::master('zd_netschool')
                ->update('app_fifty_wallpaper')
                ->cols($data)
                ->where('id = :id')
                ->bindValue('id', $id)
                ->query();
        } else {
            $data['create_user'] = $params['uid'];
            $result = Db::master('zd_netschool')
                ->insert('app_fifty_wallpaper')
                ->cols($data)
                ->query();
        }
        
        return $result;
    }
    
    /**
     * 内容管理 - 壁纸管理 删除壁纸
     * @param $id
     * @return int
     */
    public function delWallpaper($id): int {
        return Db::master('zd_netschool')
            ->update('app_fifty_wallpaper')
            ->cols(['is_del' => 1])
            ->where('id = :id')
            ->bindValue('id', $id)
            ->query();
    }
    
    /**
     * 内容管理 - 壁纸管理 获取壁纸分类列表
     * @param $params
     * @return array
     */
    public function getWallpaperCategoryList($params): array {
        return Db::slave('zd_netschool')
            ->from('app_fifty_wallpaper_category')
            ->select('*')
            ->where('is_del=0')
            ->limit($params['limit'])
            ->offset($params['page'] * $params['limit'])
            ->query();
    }
    
    /**
     * 内容管理 - 壁纸管理 获取壁纸分类总数
     * @return int
     */
    public function getWallpaperCategoryTotal(): int {
        return Db::slave('zd_netschool')
            ->select('count(*)')
            ->from('app_fifty_wallpaper_category')
            ->where('is_del = :is_del')
            ->bindValue('is_del', 0)
            ->single();
    }
    
    /**
     * 内容管理 - 壁纸管理 壁纸分类保存
     * @param $params
     * @return mixed
     */
    public function saveWallpaperCategory($params) {
        $id = $params['id'];
        $data = [
            'title' => $params['title'],
            'thumb' => $params['picture'],
        ];
        if (!empty($id)) {
            $data['modify_user'] = $params['uid'];
            $result = Db::master('zd_netschool')
                ->update('app_fifty_wallpaper_category')
                ->cols($data)
                ->where('id = :id')
                ->bindValue('id', $id)
                ->query();
        } else {
            $data['create_user'] = $params['uid'];
            $result = Db::master('zd_netschool')
                ->insert('app_fifty_wallpaper_category')
                ->cols($data)
                ->query();
        }
        
        return $result;
    }
    
    /**
     * 内容管理 - 壁纸管理 删除壁纸分类
     * @param $id
     * @return int
     */
    public function delWallpaperCategory($id): int {
        $result = Db::master('zd_netschool')
            ->update('app_fifty_wallpaper_category')
            ->cols(['is_del' => 1])
            ->where('id = :id')
            ->bindValue('id', $id)
            ->query();
        
        return $result;
    }
    
    /**
     * 内容管理 - 文章管理 获取文章PV总数
     * @param $condition
     * @return int
     */
    public function getArticlePageViewTotal($condition): int {
        $where = 'article_id = :article_id';
        $bind = ['article_id' => $condition['articleId']];
        if (!empty($condition['startDate'])) {
            $where .= ' AND date >= :start_date';
            $bind['start_date'] = $condition['startDate'];
        }
        if (!empty($condition['endDate'])) {
            $where .= ' AND date <= :end_date';
            $bind['end_date'] = $condition['endDate'];
        }
        $sql = "SELECT COUNT(*) FROM (SELECT `id` FROM app_fifty_article_page_view WHERE {$where} GROUP BY `date`) t";
        $result = Db::slave('zd_netschool')->single($sql, $bind);
        
        return (int)$result;
    }
    
    /**
     * 内容管理 - 文章管理 获取文章PV数据
     * @param $condition
     * @return array
     */
    public function getArticlePageView($condition): array {
        $where = 'article_id = :article_id';
        $bind = ['article_id' => $condition['articleId']];
        if (!empty($condition['startDate'])) {
            $where .= ' AND date >= :start_date';
            $bind['start_date'] = $condition['startDate'];
        }
        if (!empty($condition['endDate'])) {
            $where .= ' AND date <= :end_date';
            $bind['end_date'] = $condition['endDate'];
        }
        
        $result = Db::slave('zd_netschool')
            ->select('SUM(page_view) AS PV, SUM(user_view) AS UV, date')
            ->from('app_fifty_article_page_view')
            ->where($where)
            ->groupBy(['date'])
            ->orderByASC(['date'])
            ->bindValues($bind)
            ->limit($condition['limit'])
            ->offset($condition['limit'] * $condition['page'])
            ->query();
        
        return $result ?: [];
    }
    
    /**
     * 内容管理 - 文章管理 根据搜索条件获取文章总的PV/UV
     * @param $condition
     * @return array
     */
    public function getArticlePageViewAndUserViewTotal($condition): array {
        $where = 'article_id = :article_id';
        $bind = ['article_id' => $condition['articleId']];
        if (!empty($condition['startDate'])) {
            $where .= ' AND date >= :start_date';
            $bind['start_date'] = $condition['startDate'];
        }
        if (!empty($condition['endDate'])) {
            $where .= ' AND date <= :end_date';
            $bind['end_date'] = $condition['endDate'];
        }
    
        $result = Db::slave('zd_netschool')
            ->select('SUM(page_view) AS PV, SUM(user_view) AS UV')
            ->from('app_fifty_article_page_view')
            ->where($where)
            ->bindValues($bind)
            ->row();
    
        return ['PV' => (int)$result['PV'], 'UV' => (int)$result['UV']];
    }
    
    /**
     * 运营管理 - 审核开关 获取总数量
     * @param $condition
     * @return int
     */
    public function getAuditSwitchTotal($condition): int {
        $total = Db::slave('zd_netschool')
            ->select('COUNT(*)')
            ->from('app_audit_switch')
            //->where('app=:app AND is_del=0')
            ->where('is_del = :is_del')
            //->bindValues(['app' => $condition['app']])
            ->bindValues(['is_del' => 0])
            ->single();
        
        return (int)$total;
    }
    
    /**
     * 运营管理 - 审核开关 列表
     * @param $condition
     * @return array
     */
    public function getAuditSwitchList($condition): array {
        $result = Db::slave('zd_netschool')
            ->select()
            ->from('app_audit_switch')
            ->where('is_del = :is_del')
            //->where('app=:app AND is_del=0')
            //->bindValues(['app' => $condition['app']])
            ->bindValues(['is_del' => 0])
            ->orderByDESC(['is_open'])
            ->limit($condition['limit'])
            ->offset($condition['limit'] * $condition['page'])
            ->query();
        
        return $result ?? [];
    }
    
    /**
     * 运营管理 - 审核开关 保存数据
     * @param $params
     * @return array
     */
    public function saveAuditSwitch($params): array {
        $id = $params['id'];
        
        $data = [
            //'app' => $params['app'],
            //'key' => $params['key'],
            'is_open' => $params['is_open'],
            'desc' => $params['desc']
        ];
        $ret = ['code' => 200, 'msg' => ''];
        if ($id) {
            $data['modify_user'] = $params['uid'];
            $result = Db::master('zd_netschool')
                ->update('app_audit_switch')
                ->cols($data)
                ->where('id = :id')
                ->bindValue('id', $id)
                ->query();
        } else {
            $exists = Db::slave('zd_netschool')
                ->from('app_audit_switch')
                ->select('id')
                ->where('`is_del` = :is_del')
                //->where('`key` = :key AND `app` = :app AND `is_del` = :is_del')
                //->bindValues(['key' => $data['key'], 'app' => $data['app'], 'is_del' => 0])
                ->bindValues(['is_del' => 0])
                ->single();
            if ($exists) {
                return ['code' => 400, 'msg' => '当前应用下的key已存在!'];
            }
    
            $data['create_user'] = $params['uid'];
            $result = Db::master('zd_netschool')
                ->insert('app_audit_switch')
                ->cols($data)
                ->query();
        }
        
        return $result ? $ret : ['code' => 500, 'msg' => '保存失败!'];
    }
    
    /**
     * 运营管理 - 审核开关 删除
     * @param $id
     * @return mixed
     */
    public function delAuditSwitch($id) {
        $result = Db::master('zd_netschool')
            ->update('app_audit_switch')
            ->cols(['is_del' => 1])
            ->where('id = :id')
            ->bindValue('id', $id)
            ->query();
        
        return $result ? 200 : 500;
    }
}
