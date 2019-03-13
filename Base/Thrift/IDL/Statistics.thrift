namespace php Services.Statistics
service Statistics
{
    map<string, string> getSasScheduleInfo(1:i32 scheduleId);//获取统计表阶段课程信息
    list<map<string, string>>getSasLessonList(1:i32 scheduleId,2:map<string, string> data);//获取统计表课件数据
    list<map<string, string>> getJoinScheduleUsersByDate(1:i32 scheduleId,2:map<string, string> data);//加入阶段课程的用户数
    list<map<string, string>> getScheduleLessonInfo(1:i32 scheduleId);
    map<string, string> getSasLessonByLessonId(1:i32 scheduleId,2:i32 lessonId);
    list<map<string, string>>getScheduleUserInfo(1:i32 scheduleId,2:i32 type,3:map<string, string> whereArr);
    list<map<string, string>>getScheduleUserStudyInfo(1:i32 scheduleId,2:map<string, string> where,3:bool count,4:map<string, string> page,5:map<string, string> order);
    list<list<map<string, string>>>getScheduleUserAttendanceInfo(1:i32 scheduleId,2:map<string, string> data,3:bool count ,4:map<string, string> page);
    list<map<string, string>>getScheduleUserAttendanceInfoByLessonId(1:i32 scheduleId,2:i32 lessonId,3:i32 type,4:map<string, string> where,5:bool count ,6:map<string, string> page);
    map<string, string> getScheduleNowInfo(1:i32 scheduleId);//获取阶段课程当前信息
    map<string, string> getScheduleLessonAbsentAttendanceInfo(1:i32 scheduleId, 2:i32 lessonId);//获取阶段课程课件未出勤中请假休学人数
    map<string, string> getSasScheduleList (1:i32 scheduleId);
    bool overdueUserUpdate(1:i32 uid,2:i32 type);//处理逾期停课学员状态
    list<map<string, string>> getUserInfoFromGoodsId(1:i32 goodsId,2:map<string, string> where);//获取某商品下学员信息
    list<map<string, string>> getUserReportInfo(1:map<string, string> where);//获取学员报道信息统计
    list<map<string, string>> getUserGradeDistribution(1:map<string, string> data);//获取学员等级分布信息
    list<map<string, string>> getUserGradeList(1:map<string, string> data);//获取学员等级列表信息
    list<map<string, string>> getUserGraduationList(1:map<string, string> data);//获取毕业学员列表信息
    list<map<string, string>> getUserGraduationInfo(1:map<string, string> data);//获取学员报道数出勤率
    i32 getUserReportNumByCondition(1:map<string, string> where); // 根据条件获取用户的报道数
    list<map<string, string>> getSaScheduleAvgAtt(); //获取各个学管师本月结课班级平均出勤
    list<map<string, string>> getAllUserReportNumByCondition(1:map<string, string> where); //获取指定条件下的学员直播出勤次数
    list<map<string, string>>getSasLessonListForMajor(1:i32 scheduleId,2:map<string, string> data);//获取统计表课件数据(仅主修课)
    list<map<string, string>> getScheduleUserEveryday(1:map<string, string> whereArr);//获取加删课统计每日概况
    list<map<string, string>> getScheduleUserCount(1:map<string, string> whereArr);//获取加删课统计
}
