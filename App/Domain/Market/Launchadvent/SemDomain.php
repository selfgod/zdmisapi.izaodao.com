<?php
namespace App\Domain\Market\Launchadvent;

use App\Model\Market\Launchadvent\SemModel;
use Base\BaseDomain;
use EasySwoole\Config;
use Lib\Export;

class SemDomain extends BaseDomain
{
    public function __construct()
    {
        $this->baseModel = new SemModel();
    }

    /**
     * 增加sem
     * @param $businessType
     * @param $channel
     * @param $tag
     * @param $pushdate
     * @param $cost
     * @param $uid
     * @return mixed
     */
    public function addSem($businessType, $channel, $tag, $pushdate, $cost, $uid)
    {
        return $this->baseModel->addSem($businessType, $channel, $tag, $pushdate, $cost, $uid);
    }

    /**
     * 获取sem列表总数
     * @param $businessType
     * @param $channel
     * @param $pushDateStart
     * @param $pushDateEnd
     * @param $costMin
     * @param $costMax
     * @return int
     */
    public function getSemListNum($businessType, $channel, $pushDateStart, $pushDateEnd, $costMin, $costMax)
    {
        return $this->baseModel->getSemListNum($businessType, $channel, $pushDateStart, $pushDateEnd, $costMin, $costMax);
    }

    /**
     * 获取sem列表数据
     * @param $page
     * @param $limit
     * @param $businessType
     * @param $channel
     * @param $pushDateStart
     * @param $pushDateEnd
     * @param $costMin
     * @param $costMax
     * @return mixed
     * @throws \Exception
     */
    public function getSemList($page, $limit, $businessType, $channel, $pushDateStart, $pushDateEnd, $costMin, $costMax)
    {
        $list = $this->baseModel->getSemList($page, $limit, $businessType, $channel, $pushDateStart, $pushDateEnd, $costMin, $costMax);
        return $list;
    }

    /**
     * 获取sem信息
     * @param $id
     * @return array
     */
    public function getSemInfo($id)
    {
        return $this->baseModel->getSemInfo($id);
    }

    /**
     * 修改sem
     * @param $id
     * @param $businessType
     * @param $channel
     * @param $tag
     * @param $pushdate
     * @param $cost
     * @param $uid
     * @return bool
     */
    public function updateSem($id, $businessType, $channel, $tag, $pushdate, $cost, $uid)
    {
        return $this->baseModel->updateSem($id, $businessType, $channel, $tag, $pushdate, $cost, $uid);
    }

    /**
     * 删除sem
     * @param $id
     * @return mixed
     */
    public function deleteSem($id)
    {
        return $this->baseModel->deleteSem($id);
    }

    /**
     * sem导出Excel
     * @param $uid
     * @param $businessType
     * @param $channel
     * @param $pushDateStart
     * @param $pushDateEnd
     * @param $costMin
     * @param $costMax
     * @return bool
     * @throws \Exception
     */
    public function export($uid, $businessType, $channel, $pushDateStart, $pushDateEnd, $costMin, $costMax)
    {
        $title = ['TAG' => 'string', '投放时间' => 'string', '费用' => 'price'];
        $filename = Export::export('sem', $uid, $title, function ($page, $limit) use ($businessType, $channel, $pushDateStart,
            $pushDateEnd, $costMin, $costMax) {
            return $this->baseModel->getSemExportList($page, $limit, $businessType, $channel, $pushDateStart,
                $pushDateEnd, $costMin, $costMax);
        });
        return $filename;
    }
}