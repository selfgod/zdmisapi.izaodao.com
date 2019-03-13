<?php
namespace App\Domain\Market\Launchadvent;

use App\Model\Market\Launchadvent\SemChannelModel;
use Base\BaseDomain;

class SemChannelDomain extends BaseDomain
{
    public function __construct()
    {
        $this->baseModel = new SemChannelModel();
    }

    /**
     * 增加sem渠道
     * @param $name
     * @param $uid
     * @return mixed
     */
    public function addSemChannel($name, $uid)
    {
        return $this->baseModel->addSemChannel($name, $uid);
    }

    /**
     * 获取sem渠道列表总数
     * @return int
     */
    public function getSemChannelListNum()
    {
        return $this->baseModel->getSemChannelNum();
    }

    /**
     * 获取sem渠道列表数据
     * @param $page
     * @param $limit
     * @return mixed
     * @throws \Exception
     */
    public function getSemChannelList($page, $limit)
    {
        $list = $this->baseModel->getSemChannelList($page, $limit);
        return $list;
    }

    /**
     * 更新sem渠道信息
     * @param $id
     * @param $name
     * @param $uid
     * @return bool
     */
    public function updateSemChannel($id, $name, $uid)
    {
        return $this->baseModel->updateSemChannel($id, $name, $uid);
    }

    /**
     * 获取sem渠道信息
     * @param $id
     * @return array
     */
    public function getSemChannelInfo($id)
    {
        return $this->baseModel->getSemChannelInfo($id);
    }

    /**
     * 获取下拉列表所需的全部渠道
     * @return mixed
     * @throws \Exception
     */
    public function getChannelOpts()
    {
        return $this->baseModel->getSemChannelList(0, 10000);
    }
}