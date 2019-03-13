<?php

namespace App\Model\Market\Launchadvent;

use Base\BaseModel;
use Base\Db;

class CodeManager extends BaseModel
{
    /**
     * 表与业务类型映射
     * @var array
     */
    public static $tableTypeMap = [
        '日语' => 'zd_crm_source_store',
        '留学' => 'zd_crm_source_store',
        '倍普' => 'zd_crm_source_store_bp',
        '韩语' => 'zd_crm_source_store_kr',
        '德语' => 'zd_crm_source_store_de',
    ];

    /**
     * 获取全部管理者
     * @return array
     */
    public function getAllManager(): array
    {
        $managers = Db::slave('zd_class')->select('manager')->from('zd_ad_code')
            ->where('is_del = 0')->groupBy(['manager'])->column();
        return $managers ?: [];
    }

    /**
     * 获取代码管理列表
     * @param $where
     * @param array $bindValues
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getCodesList($where, array $bindValues, $page = 1, $limit = 0): array
    {
        $query = Db::slave('zd_class')->select('id,code,platform,advertiser,business_type,resource_count,manager,create_time')
            ->from('zd_ad_code')->where($where)->bindValues($bindValues)->orderByASC(['id']);
        if ($limit > 0) $query->setPaging($limit)->page($page);
        $data = $query->query();
        return $data ?: [];
    }

    /**
     * 获取代码管理总数
     * @param $where
     * @param array $bindValues
     * @return int
     */
    public function getCodesCount($where, array $bindValues): int
    {
        $count = Db::slave('zd_class')->select('COUNT(*)')->from('zd_ad_code')
            ->where($where)->bindValues($bindValues)->single();
        return (int)$count;
    }

    /**
     * 是否存在此code
     * @param $code
     * @return bool
     */
    public function existCode($code): bool
    {
        $exist = Db::slave('zd_class')->select('COUNT(*)')->from('zd_ad_code')
            ->where('code = :code AND is_del = 0')->bindValue('code', $code)->single();
        return $exist > 0;
    }

    /**
     * 通过ID获取code信息
     * @param $id
     * @return array
     */
    public function getCodeById($id): array
    {
        $data = Db::slave('zd_class')->select('code,manager')->from('zd_ad_code')
            ->where('id = :id AND is_del = 0')->bindValue('id', $id)->row();
        return $data ?: [];
    }

    /**
     * 增加
     * @param array $add
     * @return bool
     */
    public function insertCode(array $add): bool
    {
        $add = Db::master('zd_class')->insert('zd_ad_code')->cols($add)->query();
        return $add > 0;
    }

    /**
     * 更新
     * @param $where
     * @param array $bindValues
     * @param array $cols
     * @return bool
     */
    public function updateCode($where, array $bindValues, array $cols): bool
    {
        $save = Db::master('zd_class')->update('zd_ad_code')
            ->where($where)->bindValues($bindValues)->cols($cols)->query();
        return $save > 0;
    }

    /**
     * 更新资源数量
     * @param $table
     * @param $type
     */
    public function updateSourceNum($table, $type): void
    {
        $sql = "UPDATE `zd_ad_code` AS zac,
        (SELECT ac.code,COUNT(cs.firsttag) as num FROM `zd_ad_code` AS ac LEFT JOIN `%s` AS cs 
        ON ac.code = cs.firsttag WHERE ac.business_type = '%s' AND cs.sourcedate < '%s' AND cs.mobile <> '' GROUP BY cs.firsttag) AS acs 
        SET zac.resource_count = acs.num WHERE zac.code = acs.code";
        Db::master('zd_class')->query(sprintf($sql, $table, $type, date('Y-m-d')));
    }
}
