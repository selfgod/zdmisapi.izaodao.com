<?php

namespace App\Domain\Teach\DataCompass;

use App\Model\DataCompass\QingNing;
use Base\BaseDomain;

class QingNingDomain extends BaseDomain
{
    /**
     * 获取青柠统计数据
     * @param array $openIds
     * @return array
     * @throws \Exception
     */
    public function getDataSas(array $openIds): array
    {
        $model = new QingNing();
        $userMap = $model->getUidOpenIdMap($openIds);
        if (empty($userMap)) return [];
        $data = [];
        $uids = array_keys($userMap);
        $fTagMap = $model->uidFirstTagMap($uids);
        $yhqMap = $model->uidYhqTimeMap($uids);
        $ccMap = $model->uidTrackCCMap($uids);
        $goodsMap = $model->uidGoodsMap($uids);
        foreach ($uids as $uid) {
            $value['open_id'] = $userMap[$uid];
            $value['first_tag'] = $fTagMap[$uid] ?? '';
            $value['ccname'] = $ccMap[$uid] ?? '';
            $value['adtag_date'] = $yhqMap[$uid] ?? '';
            $value['goodsname'] = $value['goodsdateline'] = '';
            if (isset($goodsMap[$uid])) {
                $value['goodsname'] = $goodsMap[$uid]['goods_name'];
                $value['goodsdateline'] = $goodsMap[$uid]['create_time'];
            }
            $data[] = $value;
        }
        return $data;
    }
}