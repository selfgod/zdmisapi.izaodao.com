namespace php Services.BlessVideo

struct ListResult {
  1:i32 listCount = 0,
  2:list<map<string, string>> data = []
}
service BlessVideo
{
    ListResult getBlessVideoList(1:string title, 2:i32 status, 3:map<string, string> selectLimit);  //获取征集视频信息列表
    ListResult getBlessVideoDetail(1:i32 videoId);  //获取征集视频详细信息
    bool saveBlessVideoRecord(1:map<string, string> data);  //保存征集视频信息
    map<string, string> addPrizeQualification(1:map<string, string> params); // 增加用户的抽奖资格
}