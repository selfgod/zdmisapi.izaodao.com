<?php
namespace App\Domain\Market\Launchadvent;

use App\Model\Market\Launchadvent\AdvertiserModel;
use App\Model\SysCategory;
use Base\BaseDomain;

class AdvertiserDomain extends BaseDomain
{
    public function __construct()
    {
        $this->baseModel = new AdvertiserModel();
    }

    /**
     * 获取广告商列表数据
     * @param $page
     * @param $limit
     * @param $platform
     * @param $ader
     * @param $contact
     * @return mixed
     * @throws \Exception
     */
    public function getAdList($page, $limit, $platform, $ader, $contact)
    {
        $list = $this->baseModel->getAdvertiserList($page, $limit, $platform, $ader, $contact);
        $sysModel = new SysCategory();
        $pt = [];
        $adPlatforms = $sysModel->getValues('ad_platform');
        foreach ($adPlatforms as $platform) {
            $pt[$platform['order']] = $platform['name'];
        }
        foreach ($list as $index => $ad) {
            $list[$index]['platform'] = $pt[$ad['platform']];
        }
        return $list;
    }

    /**
     * 获取广告商列表总数
     * @param $platform
     * @param $ader
     * @param $contact
     * @return int
     */
    public function getAdListNum($platform, $ader, $contact)
    {
        return $this->baseModel->getAdvertiserNum($platform, $ader, $contact);
    }

    /**
     * 获取广告商信息
     * @param $id
     * @return array
     */
    public function getAdInfo($id)
    {
        return $this->baseModel->getAdvertiserInfo($id);
    }

    /**
     * 增加广告商
     * @param $platform
     * @param $name
     * @param $contact
     * @param $mobile
     * @param $qq
     * @param $url
     * @param $uid
     * @return mixed
     */
    public function addAdvertiser($platform, $name, $contact, $mobile, $qq, $url, $uid)
    {
        return $this->baseModel->addAdvertiser($platform, $name, $contact, $mobile, $qq, $url, $uid);
    }

    /**
     * 修改广告商
     * @param $id
     * @param $platform
     * @param $name
     * @param $contact
     * @param $mobile
     * @param $qq
     * @param $url
     * @param $uid
     * @return bool
     */
    public function updateAdvertiser($id, $platform, $name, $contact, $mobile, $qq, $url, $uid)
    {
        return $this->baseModel->updateAdvertiser($id, $platform, $name, $contact, $mobile, $qq, $url, $uid);
    }
}