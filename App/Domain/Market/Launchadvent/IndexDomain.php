<?php
namespace App\Domain\Market\Launchadvent;

use App\Domain\PermissionDomain;
use App\Model\Market\Launchadvent\AdvertiserModel;
use App\Model\Market\Launchadvent\IndexModel;
use App\Model\SysCategory;
use Base\BaseDomain;
use Lib\Export;

class IndexDomain extends BaseDomain
{
    public function __construct()
    {
        $this->baseModel = new IndexModel();
    }

    public function getOptsData()
    {
        $sysModel = new SysCategory();
        $adPlatforms = $sysModel->getValues('ad_platform');
        $advModel = new AdvertiserModel();
        $advertiser = $advModel->getAdvertiserList(0, 10000);
        return [
            'platform' => $adPlatforms,
            'advertiser' => $advertiser
        ];
    }

    /**
     * 获取用户广告权限
     * @param $uid
     * @return array
     * @throws \Exception
     */
    public function permission($uid)
    {
        $perDomain = new PermissionDomain();
        $hasViewAdverPer = $perDomain->hasFuncPermission('launchadvent_edit', $uid);
        return [
            'launchadvent_edit' => $hasViewAdverPer
        ];
    }

    /**
     * 增加广告
     * @param $businessType
     * @param $ads
     * @param $point
     * @param $content
     * @param $tag
     * @param $pushdate
     * @param $userCount
     * @param $cost
     * @param $expect_resources
     * @param $actual_resources
     * @param $uid
     * @return mixed
     */
    public function addAd($businessType, $ads, $point, $content, $tag, $pushdate, $userCount, $cost,
            $expect_resources, $actual_resources, $uid)
    {
        if ($this->hasDupAdsTag($ads, $tag)) {
            return -1;
        }
        return $this->baseModel->addAd($businessType, $ads, $point, $content, $tag, $pushdate, $userCount, $cost,
            $expect_resources, $actual_resources, $uid);
    }

    /**
     * 获取广告列表总数
     * @param $businessType
     * @param $platform
     * @param $ads
     * @param $pushDateStart
     * @param $pushDateEnd
     * @param $tag
     * @param $content
     * @param $contact
     * @return int
     * @throws \Exception
     */
    public function getAdListNum($businessType, $platform, $ads, $pushDateStart, $pushDateEnd, $tag, $content, $contact)
    {
        return $this->baseModel->getAdListNum($businessType, $platform, $ads, $pushDateStart, $pushDateEnd, $tag, $content, $contact);
    }

    /**
     * 获取广告列表数据
     * @param $page
     * @param $limit
     * @param $businessType
     * @param $platform
     * @param $ads
     * @param $pushDateStart
     * @param $pushDateEnd
     * @param $tag
     * @param $content
     * @param $contact
     * @return mixed
     * @throws \Exception
     */
    public function getAdList($page, $limit, $businessType, $platform, $ads, $pushDateStart, $pushDateEnd, $tag,
                              $content, $contact, $orderby, $uid)
    {
        $perDomain = new PermissionDomain();
        $hasViewAdverPer = $perDomain->hasFuncPermission('launchadvent_edit', $uid);
        $sysModel = new SysCategory();
        $adPlatforms = $sysModel->getValues('ad_platform');
        $pt = [];
        foreach ($adPlatforms as $adplatform) {
            $pt[$adplatform['order']] = $adplatform['name'];
        }
        $list = $this->baseModel->getAdList($page, $limit, $businessType, $platform, $ads, $pushDateStart, $pushDateEnd,
            $tag, $content, $contact, $orderby);
        foreach ($list as $index => $ad) {
            if (!$hasViewAdverPer) {
                $list[$index]['name'] = '****';
            }
            $list[$index]['platform_id'] = $ad['platform'];
            $list[$index]['platform'] = $pt[$ad['platform']];
            if (empty($ad['actual_resources'])) {
                $list[$index]['actual_resources'] = $ad['actual_resources_auto'];
            }
            unset($list[$index]['actual_resources_auto']);
        }
        return $list;
    }

    /**
     * 获取广告信息
     * @param $id
     * @return array
     * @throws \Exception
     */
    public function getAdInfo($id)
    {
        $info = $this->baseModel->getAdInfo($id);
        if (!empty($info)) {
            if (empty($info['actual_resources'])) {
                $info['actual_resources'] = $info['actual_resources_auto'];
                unset($info['actual_resources_auto']);
            }
        }
        return $info;
    }

    /**
     * 验证是否有重复tag和广告商
     * @param $ads
     * @param $tag
     * @param string $id
     * @return bool
     */
    protected function hasDupAdsTag($ads, $tag, $id = '')
    {
        $list = $this->baseModel->getAdByAdsTag($ads, $tag);
        $ids = array_column($list, 'id');
        if ($id !== '') {
            return !empty($list) && !in_array($id, $ids);
        } else {
            return !empty($list);
        }
    }

    /**
     * 修改广告
     * @param $id
     * @param $businessType
     * @param $ads
     * @param $point
     * @param $content
     * @param $tag
     * @param $pushdate
     * @param $userCount
     * @param $cost
     * @param $expect_resources
     * @param $actual_resources
     * @param $uid
     * @return bool
     */
    public function updateAd($id, $businessType, $ads, $point, $content, $tag, $pushdate, $userCount, $cost,
                             $expect_resources, $actual_resources, $uid)
    {
        if ($this->hasDupAdsTag($ads, $tag, $id)) {
            return -1;
        }
        return $this->baseModel->updateAd($id, $businessType, $ads, $point, $content, $tag, $pushdate, $userCount, $cost,
            $expect_resources, $actual_resources, $uid);
    }

    /**
     * 删除广告
     * @param $id
     * @return mixed
     */
    public function deleteAd($id)
    {
        return $this->baseModel->deleteAd($id);
    }

    /**
     * 导出Excel
     * @param $uid
     * @param $businessType
     * @param $platform
     * @param $ads
     * @param $pushDateStart
     * @param $pushDateEnd
     * @param $tag
     * @param $content
     * @param $contact
     * @return bool|string
     */
    public function export($uid, $businessType, $platform, $ads, $pushDateStart, $pushDateEnd, $tag,
                           $content, $contact)
    {
        $title = ['TAG' => 'string', '投放时间' => 'string', '费用' => 'price'];
        $filename = Export::export('广告投放', $uid, $title, function ($page, $limit) use ($businessType, $platform, $ads,
            $pushDateStart, $pushDateEnd, $tag, $content, $contact) {
            return $this->baseModel->getAdExportList($page, $limit, $businessType, $platform, $ads, $pushDateStart,
                $pushDateEnd, $tag, $content, $contact);
        });
        return $filename;
    }
}