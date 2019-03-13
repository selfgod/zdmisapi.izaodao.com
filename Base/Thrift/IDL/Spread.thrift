namespace php Services.Spread
struct ResultCode {
  1:i32 code = 500,
  2:string msg = "fail"
  3:map<string, string> data = []
}

service Spread
{
    map<string, string> getFineSchedule(1:i32 scheduleId);//获取精品课阶段课程信息
    map<string, string> getGoodsInfo(1:i32 goodsId);//获取商品信息
    map<string, string> getFineNumByGoods(1:i32 goodsId);//通过商品id获取精品课统计数量
    void saveFineClassRecord(1:i32 uid,2:string tel,3:map<string, string> data);//保存精品课记录
    list<map<string, string>> getAdDetails(1:i32 categoryId,2:bool all);//获取广告
    bool verifyUserFirstlLesson(1:i32 uid, 2:bool firstLesson);//验证用户首课
    bool verifyUserBuyGoods(1:i32 uid,2:i32 goodsId);//验证用户是否购买商品
    map<string, string> getActivityTopicsById(1:i32 activityId);//获取某活动配置
    map<string, string> getCCMemo(1:i32 uid,2:string tel);//获取CC memo
    map<string, string> patchUserInfo(1:i32 uid,2:string tel);//拼凑用户信息
    map<string, string> getUserInfo(1:i32 uid);//获取用户信息
    i32 getUserConsumeAmount(1:i32 uid);//用户消费金额
    bool checkUserReceiveCard(1:i32 uid,2:map<string, string> params);//检查用户是否领取卡券
    ResultCode makeCard(1:i32 uid, 2:i32 coupon_id, 3:i32 goods_id 4:i32 give_num);//给用户发放卡券
    ResultCode receiveCard(1:i32 uid, 2:i32 goodsId,3:i32 activeId,4:i32 couponId,5:map<string, string> params);//用户领取卡券
    i32 getDownCount();//配置下载总数
    list<map<string, string>> getDownPageList(1:i32 limit, 2:i32 page);//获取配置下载列表
    i32 getUserPayAmount(1:i32 uid, 2:map<string, string> params); // 获取用户支付的总金额
    list<map<string, string>> getDistributionInfo(1:map<string, string> params); // 获取后台FA 实物发放信息
    bool saveDistribution(1:map<string, string> params); // 增加/保存后台FA 实物发放数据
    list<map<string, string>> getNoticeList(1:i32 categoryId);//获取通知类信息
    list<map<string, string>> getExperienceListFromNowOn(1:map<string, string> params);//获取体验课当天及以后的数据
    bool addLevelTest(1:list<map<string, string>> params); // 增加水平测试结果
    list<map<string, string>> getLevelTest(1:string mobile); // 获取水平测试结果
    map<string, string> getGoodsScheduleInfo(1:string mobile); // 获取水平测试结果
    list<map<string, string>> getTeacherByIds(1:map<string, string> data); // 获取老师信息
    ResultCode sendSmsVerifyCode(1:string mobile, 2:map<string, string> params);//发送短信验证码
    map<string, bool> verifySmsCode(1:string mobile, 2:string code);//验证短信验证码
    void addCrmRecord(1:string mobile, 2:map<string, string> params);//添加crm记录
    void YkbSaveWeiXinResource(1:string openId, 2:string mobile, 3:map<string, string> params); // 预科班获取微信openid和手机号资源的关联数据
    bool toOccupy(1:map<string, string> data);//6周年活动 抢占
    bool haveOccupy(1:map<string, string> where);//6周年活动 是否抢占过
    map<string, string> getBrandPageEwm(1:string openId);//获取品牌着陆页二维码
    bool updateBrandPageEwm(1:string openId, 2:string mobile);//获取品牌着陆页二维码
    map<string, i32> getOrderNumberByCondition(1:map<string, string> params); // 双十一获取订单总数量
}