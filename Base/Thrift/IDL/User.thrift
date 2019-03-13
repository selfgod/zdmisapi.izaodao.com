namespace php Services.User
struct JsonResult {
  1:i32 code = 200,
  2:string msg = "",
  3:map<string, string> data
}

service User
{
    JsonResult bindWX(1:string name, 2:string password, 3:string unionId);
    bool unbindWX(1:string openId);
    bool bindWXByOpenId(1:string openId, 2:string unionId);
    map<string, string> getUserByUnionId(1:string unionId);
    map<string, string> getUserCenterByOpenId(1:string openId); //通过openID获取最新用户信息
    JsonResult sendSMS(1:i32 uid, 2:string assignmentId, 3:map<string, string> params);
    JsonResult sendSMSByMobile(1:string mobile, 2:string assignmentId, 3:map<string, string> params);
    JsonResult sendWXNoticeByUid(1:string uids, 2:i32 platform_id, 3:i32 template_id, 4:string target, 5:map<string, map<string, string>> body);
    JsonResult sendWXNoticeByOpenId(1:string openIds, 2:i32 platform_id, 3:i32 template_id, 4:string target, 5:map<string, map<string, string>> body);
    map<string, string>getUserByOpenId(1:string openId);//通过openId获取用户信息
    map<string, string> getUserByUid(1:i32 uid);
    list<map<string, string>> getUsersByUids(1:list<i32> uids);
    JsonResult sendZNX(1:i32 uid, 2:string assignmentId, 3:map<string, string> params);
    JsonResult getUnreadMessageCount(1:i32 uid, 2:string zd_session_id);
    JsonResult sendEmail(1:i32 uid, 2:string assignmentId, 3:map<string, string> params,4:string title);
    map<string, string> getUserMessageInfo(1:i32 uid);
    map<string, string> getUserDetailByUid(1:i32 uid);
    bool updateStaffUser(1:i32 uid,2:string type,3:map<string, string> params);
    map<string, string> getUserTeacherInfo(1:i32 uid);//获取用户班主任信息
    i32 getUidByOpenId(1:string openId);//通过openId获取uid
    map<string, string> getUserBySessionId(1:string sessionId);//通过sessionId获取uid
    map<string, string> getUserLearnInfo(1:i32 uid);//获取用户学习信息
    map<string, string> getTeacherInfo(1:i32 teacherId);//获取教师信息
    map<string, map<string, string>> getTeacherByIds(1:list<i32> teacherIds);
    list<map<string, string>> getTeacherList(1:map<string, string> paging);//获取教师列表
    map<string, string> getUserMemberCountInfo(1:i32 uid);//用户学分早元相关信息
    bool isEmployee(1:i32 uid); // 是否为内部员工
    bool updateUserLearnInfoGrade(1:i32 uid,2:i32 gradeId); // 更新用户学习信息表 - 等级信息
    bool updateUserLearnInfoSuspend(1:i32 uid,2:i32 type); //更新用户学习信息表 - 休学信息
    bool updateUserSuspend(1:map<string,string> data); //用户休学
    bool updateUserEndSuspend(1:i32 uid); //用户结束休学
    bool updateUserLearnInfo(1:i32 uid,2:map<string, string> data); // 更新用户学习信息表
    bool incUserScoreFormJLPT(1:i32 uid,2:i32 score,3:i32 score_type,4:bool is_pass); //能力考用户增加学分
    bool delGoodsUpdate(1:i32 uid); //商品删改更新学习信息表
    i32 getUserNowLearnStatus(1:i32 uid,2:i32 type); //获取学员变化后学习状态
    bool updateUserInfo(1:i32 uid, 2:map<string,string> data); //更新用户信息
    JsonResult userRegisterByMobile(1:string mobile, 2:map<string,string> data); //手机号免密注册
    map<string, string> getUserInfoByMobile(1:string mobile);//通过mobile获取用户信息
    JsonResult checkAndBindWx(1:string openId, 2:map<string,string> data);//验证并绑定微信
    string getOpenIdByUid(1:i32 uid);//取openid
    bool updatePassword(1:string openId,2:string password,3:map<string, string> params);
    bool userEventPush(1:i32 uid,2:string event,3:map<string, string> params);//用户事件推送
}

