namespace php Services.Admin

service Admin
{
    bool delCache(1:string key,2:string id);
    bool delCacheByIds(1:string key,2:list<string> ids);
    bool delCacheByKeyPrefix(1:string key);
}