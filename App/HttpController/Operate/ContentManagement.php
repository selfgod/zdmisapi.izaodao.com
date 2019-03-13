<?php

namespace App\HttpController\Operate;

use Base\PassportApi;
use App\Model\Operate\OperateManagement AS Model;

/**
 * ZDMis 运营管理栏目
 * Created by Seldoon.
 * User: Seldoon.
 * Date: 2018-12-20 14:29
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
class ContentManagement extends PassportApi
{
    public function getRules(): array {
        $rules = parent::getRules();
        $this_rules = (new Model())->getRule();
        
        return array_merge($rules, $this_rules);
    }
    
    /**
     * 内容管理 - 壁纸管理 获取壁纸分类总数
     */
    public function getWallpaperCategoryTotal(): void {
        $this->returnJson((new Model())->getWallpaperCategoryTotal());
    }
    
    /**
     * 内容管理 - 壁纸管理 获取壁纸列表
     */
    public function getWallpaperList(): void {
        $this->returnJson((new Model())->getWallpaperList($this->params));
    }
    
    
    /**
     * 内容管理 - 壁纸管理 壁纸保存
     */
    public function saveWallpaper(): void {
        $this->returnJson((new Model())->saveWallpaper($this->params));
    }
    
    /**
     * 内容管理 - 壁纸管理 获取壁纸的分类名称
     */
    public function getWallpaperCategoryName(): void {
        $this->returnJson((new Model())->getWallpaperCategoryName());
    }
    
    /**
     * 内容管理 - 壁纸管理 获取壁纸总数量
     */
    public function getWallpaperTotal(): void {
        $this->returnJson((new Model())->getWallpaperTotal($this->params));
    }
    
    /**
     * 内容管理 - 壁纸管理 删除壁纸
     */
    public function delWallpaper(): void {
        $this->returnJson((new Model())->delWallpaper($this->params['id']));
    }
    
    /**
     * 内容管理 - 壁纸管理 获取壁纸分类列表
     */
    public function getWallpaperCategoryList(): void {
        $this->returnJson((new Model())->getWallpaperCategoryList($this->params));
    }
    
    /**
     * 内容管理 - 壁纸管理 保存壁纸分类
     */
    public function saveWallpaperCategory(): void {
        $this->returnJson((new Model())->saveWallpaperCategory($this->params));
    }
    
    /**
     * 内容管理 - 壁纸管理 删除壁纸分类
     */
    public function delWallpaperCategory(): void {
        $this->returnJson((new Model())->delWallpaperCategory($this->params['id']));
    }
    
    /**
     * 内容管理 - 文章管理 获取PV总数量
     */
    public function getArticlePageViewTotal(): void {
        $this->returnJson((new Model())->getArticlePageViewTotal($this->params));
    }
    
    /**
     * 内容管理 - 文章管理 获取PV数据
     */
    public function getArticlePageList(): void {
        $this->returnJson((new Model())->getArticlePageView($this->params));
    }
    
    /**
     * 内容管理 - 文章管理 根据条件获取PV/UV数量
     */
    public function getArticlePvUvTotal(): void {
        $this->returnJson((new Model())->getArticlePageViewAndUserViewTotal($this->params));
    }
    
    /**
     * APP 审核接口 获取总数量
     */
    public function getAuditSwitchTotal(): void {
        $this->returnJson((new Model())->getAuditSwitchTotal($this->params));
    }
    
    /**
     * APP 审核接口 获取列表
     */
    public function getAuditSwitchList(): void {
        $this->returnJson((new Model())->getAuditSwitchList($this->params));
    }
    
    /**
     * APP 审核接口 保存数据
     */
    public function saveAuditSwitch(): void {
        $this->returnJson((new Model())->saveAuditSwitch($this->params));
    }
    
    /**
     * APP 审核接口 删除数据
     */
    public function delAuditSwitch(): void {
        $this->returnJson((new Model())->delAuditSwitch($this->params['id']));
    }
}
