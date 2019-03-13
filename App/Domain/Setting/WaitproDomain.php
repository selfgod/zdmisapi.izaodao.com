<?php

namespace App\Domain\Setting;

use App\Model\Setting\WaitproModel;
use Base\BaseDomain;
use Base\Exception\BadRequestException;

class WaitproDomain extends BaseDomain
{
    /**
     * 获取待处理项目
     * @return array
     */
    public function getWaitProjectList()
    {
        $project = (new WaitproModel())->getWaitProject();
        return $project ?: [];
    }

    /**
     * 更新待处理项目
     * @param array $params
     * @return bool
     * @throws BadRequestException
     */
    public function saveWaitProject(array $params)
    {
        $data = $params['data'];
        if (empty($data['key']) || empty($data['name']) || empty($data['link'])) {
            throw new BadRequestException('缺失参数');
        }
        $model = new WaitproModel();
        if ($id = intval($data['id'])) {
            $row = $model->getWaitProjectById($id);
            if (empty($row)) {
                throw new BadRequestException('数据异常');
            }
            $data['modify_user'] = $params['uid'];
            $data['modify_time'] = date('Y-m-d H:i:s');
            unset($data['id']);
            $res = $model->updateWaitProjectById($id, $data);
        } else {
            $data['create_user'] = $params['uid'];
            $res = $model->insertWaitProject($data);
        }
        return $res;
    }

    /**
     * 删除待处理项目
     * @param array $params
     * @return bool
     * @throws BadRequestException
     */
    public function delWaitProject(array $params)
    {
        $model = new WaitproModel();
        if ($id = intval($params['id'])) {
            $row = $model->getWaitProjectById($id);
            if (empty($row)) {
                throw new BadRequestException('数据异常');
            }
            return $model->updateWaitProjectById($id, [
                'is_del' => 1,
                'key' => $row['key'] . '_' . $row['id'],
                'modify_user' => $params['uid'],
                'modify_time' => date('Y-m-d H:i:s')
            ]);
        }
        return FALSE;
    }
}