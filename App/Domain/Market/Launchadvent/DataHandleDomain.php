<?php
/**
 * Created by PhpStorm.
 * User: aramis
 * Date: 2018/12/8
 * Time: 4:25 PM
 */

namespace App\Domain\Market\Launchadvent;


use App\Model\Market\Launchadvent\IndexModel;
use Base\BaseDomain;

class DataHandleDomain extends BaseDomain
{

    public function __construct()
    {
        $this->baseModel = new IndexModel();
    }
    
    
    public function updateData(){
        $this->clear_data();
        $this->income_pro();
        $this->buy_count_jp_pro();
        $this->actual_resources_jp_pro();
        $this->actual_resources_kr_pro();
        $this->actual_resources_de_pro();
        $this->actual_resources_bp_pro();
        $this->unit_price_pro();
        $this->roi_pro();
        $this->conversion_rate_pro();
        return 1;
    }

    /**
     * 清除数据
     */
    public function clear_data()
    {
        $sql = "update zd_ad_launchadvent set income=0,buy_count=0,actual_resources_auto=0,unit_price=0,roi=0,conversion_rate=0 where actual_resources<1";
        $this->baseModel->update_data($sql);
    }

    /**
     * 收入数据更新
     */
    public function income_pro()
    {
        $sql = 'update zd_ad_launchadvent as push,(
	select tag, sum(num) as amount from (
		select k.tag,o.num from zd_ad_launchadvent as k 
		left join jh_common_member_payfor as o on k.tag=o.first_tag
		where o.adddate<date_format(NOW(),"%Y-%m-%d 00:00:00") and (k.business_type="日语" or k.business_type="留学") and o.id is not null
	) as pay GROUP BY tag
) as s
set push.income=s.amount
where push.tag=s.tag and push.business_type="日语"';
        $this->baseModel->update_data($sql);
    }

    /**
     * 日语、留学购买人数更新
     */
    public function buy_count_jp_pro()
    {
        $sql = 'update zd_ad_launchadvent as push,(
	select tag, count(tag) as buy_count from (
		select k.tag,o.uid from zd_ad_launchadvent as k 
		left join jh_common_member_payfor as o on k.tag=o.first_tag
		where o.adddate<date_format(NOW(),"%Y-%m-%d 00:00:00") and (k.business_type="日语" or k.business_type="留学") and o.id is not null
		GROUP BY uid
	) as pay GROUP BY tag
) as s
set push.buy_count=s.buy_count
where push.tag=s.tag and push.business_type="日语"';
        $this->baseModel->update_data($sql);
    }

    /**
     * 日语、留学实际资源更新
     */
    public function actual_resources_jp_pro()
    {
        $sql = 'update zd_ad_launchadvent as push,(
select tag, count(*) as cnt from (
	select k.tag, k.id,s.mobile from zd_ad_launchadvent as k 
	left join zd_crm_source_store as s on k.tag=s.firsttag
	where k.business_type="日语" and s.sourcedate<date_format(NOW(),"%Y-%m-%d") and s.mobile is not null
) as source GROUP BY tag
) as s
set push.actual_resources_auto=s.cnt
where push.tag=s.tag and push.actual_resources<1';
        $this->baseModel->update_data($sql);
    }

    /**
     * 韩语实际资源更新
     */
    public function actual_resources_kr_pro()
    {
        $sql = 'update zd_ad_launchadvent as push,(
select tag, count(*) as cnt from (
	select k.tag, k.id,s.mobile from zd_ad_launchadvent as k 
	left join zd_crm_source_store_kr as s on k.tag=s.firsttag
	where k.business_type="韩语" and s.sourcedate<date_format(NOW(),"%Y-%m-%d") and s.mobile is not null
) as source GROUP BY tag
) as s
set push.actual_resources_auto=s.cnt
where push.tag=s.tag and push.actual_resources<1';
        $this->baseModel->update_data($sql);
    }

    /**
     * 德语实际资源更新
     */
    public function actual_resources_de_pro()
    {
        $sql = 'update zd_ad_launchadvent as push,(
select tag, count(*) as cnt from (
	select k.tag, k.id,s.mobile from zd_ad_launchadvent as k 
	left join zd_crm_source_store_de as s on k.tag=s.firsttag
	where k.business_type="德语" and s.sourcedate<date_format(NOW(),"%Y-%m-%d") and s.mobile is not null
) as source GROUP BY tag
) as s
set push.actual_resources_auto=s.cnt
where push.tag=s.tag and push.actual_resources<1';
        $this->baseModel->update_data($sql);
    }

    /**
     * 倍普实际资源更新
     */
    public function actual_resources_bp_pro()
    {
        $sql = 'update zd_ad_launchadvent as push,(
select tag, count(*) as cnt from (
	select k.tag, k.id,s.mobile from zd_ad_launchadvent as k 
	left join zd_crm_source_store_bp as s on k.tag=s.firsttag
	where k.business_type="倍普" and s.sourcedate<date_format(NOW(),"%Y-%m-%d") and s.mobile is not null
) as source GROUP BY tag
) as s
set push.actual_resources_auto=s.cnt
where push.tag=s.tag and push.actual_resources<1';
        $this->baseModel->update_data($sql);
    }

    /**
     * 客单价数据
     */
    public function unit_price_pro()
    {
        $sql = 'update zd_ad_launchadvent set unit_price=if(actual_resources>0,truncate(cost/actual_resources,2),truncate(cost/actual_resources_auto,2))
where  cost>0 and (actual_resources_auto>0 or actual_resources>0)';
        $this->baseModel->update_data($sql);
    }

    /**
     * ROI数据
     */
    public function roi_pro()
    {
        $sql = 'update zd_ad_launchadvent set roi=truncate(income/cost,2) 
where income>0 and cost>0';
        $this->baseModel->update_data($sql);
    }

    /**
     * 转化率数据
     */
    public function conversion_rate_pro()
    {
        $sql = 'update zd_ad_launchadvent set conversion_rate=if(actual_resources>0,truncate(buy_count/actual_resources*100,2),truncate(buy_count/actual_resources_auto*100,2))
where buy_count>0 and (expect_resources>0 or actual_resources_auto>0)';
        $this->baseModel->update_data($sql);
    }
}