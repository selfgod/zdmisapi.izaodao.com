namespace php Services.MpTemplate

service MpTemplate
{
    bool sendTemplateForCreditChange(1:map<string, string> data);
    bool sendTemplateForCommit(1:map<string, string> data);
    map<string, string> getOpenIdFromTagName(1:string tagName);
    string getTagIdFromUnionId(1:string unionId);
}