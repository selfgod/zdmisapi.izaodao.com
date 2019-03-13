<?php
/**
 * 教学 - 标签管理
 * Created by PhpStorm.
 * User: wuheng
 * Date: 2018/9/25
 * Time: 09:50
 */

namespace App\Domain\Teach\Label;

use App\Model\Teach\Label\LabelModel;
use Base\BaseDomain;

class LabelDomain extends BaseDomain
{
    /**
     * 获取标签列表
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function queryLabelList(array $params)
    {
        $label = new LabelModel();
        $res = $label->getLabelList($params['p_id']);
        return ['label'=>$res];
    }

}