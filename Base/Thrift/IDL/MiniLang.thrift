namespace php Services.MiniLang

service MiniLang {
    list<map<string, string>> serverContentList(1:map<string, string> params);//服务列表
    list<map<string, string>> getServiceByIds(1:map<string, string> ids,2:map<string, string> params);//指定id服务
}