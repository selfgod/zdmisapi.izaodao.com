namespace php Services.ZDtalk
struct JsonResult {
  1:i32 code = 500,
  2:string msg = "fail",
  3:map<string, string> data = []
}

service ZDtalk
{
    JsonResult getZDtalkParameter(1:map<string, string> data);
    JsonResult getZdTalkAuthData(1:i32 uid, 2:string token, 3:string sessionId)//获取网校授权zdTalk数据
    map<string, string> getRoomInfo(1:i32 lessonId);
}