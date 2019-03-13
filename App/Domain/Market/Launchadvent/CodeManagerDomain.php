<?php

namespace App\Domain\Market\Launchadvent;

use App\Model\Common\User;
use App\Model\Market\Launchadvent\CodeManager;
use App\Model\SysCategory;
use Base\BaseDomain;
use Base\Exception\BadRequestException;
use EasySwoole\Core\Component\Logger;
use Lib\Export;

class CodeManagerDomain extends BaseDomain
{
    /**
     * 获取查询选项
     * @param $uid
     * @return array
     * @throws \Exception
     */
    public function getSearchOptions($uid): array
    {
        $platform = [];
        $platformMap = (new SysCategory())->getValues('ad_platform');
        if (!empty($platformMap)) {
            foreach ($platformMap as $item) {
                $value['name'] = $item['name'];
                $value['val'] = $item['order'];
                $platform[] = $value;
            }
            unset($value);
        }
        $manager = [];
        if ($this->hasFuncAuth($uid)) {
            $managers = (new CodeManager())->getAllManager();
            if (!empty($managers)) {
                $members = (new User())->getUsersByUids($managers);
                if (!empty($members)) {
                    foreach ($members as $user) {
                        $value['name'] = $user['username'] ?: '';
                        $value['val'] = $user['uid'];
                        $manager[] = $value;
                    }
                    unset($value);
                }
            }
        }
        return ['platform' => $platform, 'manager' => $manager];
    }

    /**
     * 获取代码管理列表信息
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function getCodesList(array $params): array
    {
        $uid = (int)$params['uid'];
        $code = $params['condition']['code'] ?? '';
        $platform = $params['condition']['platform'] ?? 0;
        $advertiser = $params['condition']['advertiser'] ?? '';
        if ($this->hasFuncAuth($uid)) {
            $manager = $params['condition']['manager'] ?? 0;
        } else {
            $manager = $uid;
        }
        $where = 'is_del = :is_del';
        $bindValues['is_del'] = 0;
        if ((int)$manager) {
            $where .= ' AND manager = :manager';
            $bindValues['manager'] = (int)$manager;
        }
        if (!empty($code)) {
            $where .= ' AND code LIKE :code';
            $bindValues['code'] = '%' . addslashes($code) . '%';
        }
        if ((int)$platform) {
            $where .= ' AND platform = :platform';
            $bindValues['platform'] = (int)$platform;
        }
        if (!empty($advertiser)) {
            $where .= ' AND advertiser LIKE :advertiser';
            $bindValues['advertiser'] = '%' . addslashes($advertiser) . '%';
        }
        $result = ['list' => []];
        $model = new CodeManager();
        if ((int)$params['count'] === 1) {
            $count = $model->getCodesCount($where, $bindValues);
            $result['count'] = $count;
            if (!$count) return $result;
        }
        $codesList = $model->getCodesList($where, $bindValues, $params['page'], $params['limit']);//code管理列表
        if (!empty($codesList)) {
            $platforms = (new SysCategory())->getValues('ad_platform');//平台
            $pfMap = empty($platforms) ? [] : array_reduce($platforms, function ($list, $v) {
                $list[$v['order']] = $v['name'];
                return $list;
            });
            $uids = array_values(array_unique(array_column($codesList, 'manager')));//管理者组
            $unames = [];
            if (count($uids) === 1 && (int)$uids[0] === $uid) {//是否只有当前用户
                $unames[$uid] = $params['userInfo']['user_name'] ?: '';
            } else {
                $users = (new User())->getUsersByUids($uids);//管理者用户信息
                if (!empty($users)) {
                    foreach ($users as $user) {
                        $unames[$user['uid']] = $user['username'] ?: '';
                    }
                }
            }
            foreach ($codesList as $item) {
                $value = $item;
                $value['platform_zh'] = $pfMap[$value['platform']] ?? '';
                $value['manager_name'] = $unames[$value['manager']] ?? '';
                $result['list'][] = $value;
            }
        }
        return $result;
    }

    /**
     * 保存code
     * @param array $params
     * @return bool
     * @throws BadRequestException
     */
    public function saveCode(array $params): bool
    {
        $model = new CodeManager();
        $save = [];
        $save['code'] = addslashes($params['code']);
        if ($model->existCode($save['code']))
            throw new BadRequestException('代码已存在！', 1);
        $save['platform'] = (int)$params['platform'];
        $save['advertiser'] = addslashes($params['advertiser']);
        $save['business_type'] = $params['business'];
        $save['manager'] = (int)$params['uid'];
        try {
            $model->insertCode($save);
        } catch (\PDOException $e) {
            Logger::getInstance()->log('CodeManager\saveCode ERROR:' . $e->getMessage() . ' params:' . \GuzzleHttp\json_encode($save));
            throw new BadRequestException('添加失败！');
        }
        return TRUE;
    }

    /**
     * 删除代码
     * @param array $params
     * @return bool
     * @throws BadRequestException
     */
    public function delCode(array $params): bool
    {
        $model = new CodeManager();
        $uid = (int)$params['uid'];
        $id = (int)$params['id'];
        $codeInfo = $model->getCodeById($id);
        if (empty($codeInfo))
            throw new BadRequestException('无此代码！');
        if (!$this->hasFuncAuth($uid) && $uid !== (int)$codeInfo['manager'])
            throw new BadRequestException('无删除权限！');
        try {
            $model->updateCode('id = :id AND is_del = 0', ['id' => $id], [
                'code' => $codeInfo['code'] . '_' . $id,
                'modify_user' => $uid,
                'is_del' => 1
            ]);
        } catch (\PDOException $e) {
            Logger::getInstance()->log('CodeManager\delCode ERROR:' . $e->getMessage() . ' id:' . $id);
            throw new BadRequestException('删除失败！');
        }
        return TRUE;
    }

    /**
     * 是否有功能权限
     * @param $uid
     * @return bool
     * @throws \Exception
     */
    private function hasFuncAuth($uid): bool
    {
        $auth = (new \App\Model\Common\Permission())->hasFuncPermission('marketAdCodeManager', $uid);
        return $auth !== FALSE;
    }

    /**
     * 更新资源数量
     * @return bool
     * @throws BadRequestException
     */
    public function updateSourceNum(): bool
    {
        $model = new CodeManager();
        try {
            foreach (CodeManager::$tableTypeMap as $type => $table) {
                $model->updateSourceNum($table, $type);
            }
        } catch (\Exception $e) {
            Logger::getInstance()->log('CodeManagerDomain\updateSourceNum ERROR:' . $e->getMessage());
            throw new BadRequestException('资源量更新失败！');
        }
        return TRUE;
    }

    /**
     * 导出代码资源
     * @param array $params
     * @return bool|string
     */
    public function exportSource(array $params)
    {
        $title = ['代码' => 'string', '投放平台' => 'string', '广告商' => 'string', '业务' => 'string', '资源量' => 'integer', '管理人员' => 'string', '录入时间' => 'string'];
        $filename = Export::export('代码管理', $params['uid'], $title, function ($page, $limit) use ($params) {
            return $this->formatExportData($page, $limit, $params);
        });
        return $filename;
    }

    /**
     * 格式化导出数据
     * @param $page
     * @param $limit
     * @param array $params
     * @return array
     * @throws \Exception
     */
    protected function formatExportData($page, $limit, array $params): array
    {
        $data = [];
        $params['page'] = $page;
        $params['limit'] = $limit;
        $params['count'] = 0;
        $codesInfo = $this->getCodesList($params);
        $codeList = $codesInfo['list'] ?? [];
        if (!empty($codeList)) {
            foreach ($codeList as $item) {
                $value['code'] = $item['code'];
                $value['platform_zh'] = $item['platform_zh'];
                $value['advertiser'] = $item['advertiser'];
                $value['business_type'] = $item['business_type'];
                $value['resource_count'] = (int)$item['resource_count'];
                $value['manager_name'] = $item['manager_name'];
                $value['create_time'] = $item['create_time'];
                $data[] = $value;
            }
        }
        return $data;
    }
}