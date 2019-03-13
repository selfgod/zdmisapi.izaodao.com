<?php

namespace App\HttpController\Employees;

use App\Model\Employees\EmployeesModel;
use Base\BaseController;
use Base\WXBizMsgCrypt;
use EasySwoole\Core\Component\Logger;
use GuzzleHttp\Psr7\LazyOpenStream;

class WorkChatEvent extends BaseController
{

    protected $encodingAesKey = "lqyWPBl490lxhucNtIGfrpYTYAhIK0gcJennt3w1gzt";
    protected $token = "IZAODAOTOKEN";
    protected $corpId = "wwf5e7c8c9d346384e";

    /**
     * 获得所有组
     * @throws \Exception
     */
    public function index()
    {
        /*
        ------------使用示例一：验证回调URL---------------
        *企业开启回调模式时，企业号会向验证url发送一个get请求
        假设点击验证时，企业收到类似请求：
        * GET /cgi-bin/wxpush?msg_signature=5c45ff5e21c57e6ad56bac8758b79b1d9ac89fd3&timestamp=1409659589&nonce=263014780&echostr=P9nAzCzyDtyTWESHep1vC5X9xho%2FqYX3Zpb4yKa9SKld1DsH3Iyt3tP3zNdtp%2B4RPcs8TgAE7OaBO%2BFZXvnaqQ%3D%3D
        * HTTP/1.1 Host: qy.weixin.qq.com
        接收到该请求时，企业应
        1.解析出Get请求的参数，包括消息体签名(msg_signature)，时间戳(timestamp)，随机数字串(nonce)以及公众平台推送过来的随机加密字符串(echostr),
        这一步注意作URL解码。
        2.验证消息体签名的正确性
        3. 解密出echostr原文，将原文当作Get请求的response，返回给公众平台
        第2，3步可以用公众平台提供的库函数VerifyURL来实现。
        */
//        $sVerifyMsgSig = $this->request()->getRequestParam("msg_signature");
//        $sVerifyTimeStamp = $this->request()->getRequestParam("timestamp");
//        $sVerifyNonce = $this->request()->getRequestParam("nonce");
//        $sVerifyEchoStr = $this->request()->getRequestParam("echostr");
//        // 需要返回的明文
//        $sEchoStr = "";
//        $wxcpt = new WXBizMsgCrypt($this->token, $this->encodingAesKey, $this->corpId);
//        $errCode = $wxcpt->VerifyURL($sVerifyMsgSig, $sVerifyTimeStamp, $sVerifyNonce, $sVerifyEchoStr, $sEchoStr);
//        if ($errCode == 0) {
//            // 验证URL成功，将sEchoStr返回
//            $this->response()->write($sEchoStr);
//        } else {
//            $this->response()->write("ERR: " . $errCode . "\n\n");
//        }

        $sReqMsgSig = $this->request()->getRequestParam("msg_signature");
        $sReqTimeStamp = $this->request()->getRequestParam("timestamp");
        $sReqNonce = $this->request()->getRequestParam("nonce");
        //post请求的密文数据
        $sReqData = $this->request()->getBody()->__toString(); //file_get_contents("php://input");
        $sMsg = "";  // 解析之后的明文
        $wxcpt = new WXBizMsgCrypt($this->token, $this->encodingAesKey, $this->corpId);
        $errCode = $wxcpt->DecryptMsg($sReqMsgSig, $sReqTimeStamp, $sReqNonce, $sReqData, $sMsg);
        if ($errCode == 0) {
            // 解密成功，sMsg即为xml格式的明文
            $this->updateInfo($sMsg);
            // TODO: 对明文的处理
        } else {
            Logger::getInstance()->log("ERR: " . $errCode . "\n\n");
        }
        //$this->response()->write($sReqData);
        /*
        ------------使用示例三：企业回复用户消息的加密---------------
        企业被动回复用户的消息也需要进行加密，并且拼接成密文格式的xml串。
        假设企业需要回复用户的明文如下：
        <xml>
        <ToUserName><![CDATA[mycreate]]></ToUserName>
        <FromUserName><![CDATA[wx5823bf96d3bd56c7]]></FromUserName>
        <CreateTime>1348831860</CreateTime>
        <MsgType><![CDATA[text]]></MsgType>
        <Content><![CDATA[this is a test]]></Content>
        <MsgId>1234567890123456</MsgId>
        <AgentID>128</AgentID>
        </xml>
        为了将此段明文回复给用户，企业应：
        1.自己生成时间时间戳(timestamp),随机数字串(nonce)以便生成消息体签名，也可以直接用从公众平台的post url上解析出的对应值。
        2.将明文加密得到密文。
        3.用密文，步骤1生成的timestamp,nonce和企业在公众平台设定的token生成消息体签名。
        4.将密文，消息体签名，时间戳，随机数字串拼接成xml格式的字符串，发送给企业号。
        以上2，3，4步可以用公众平台提供的库函数EncryptMsg来实现。
        */
        // 需要发送的明文
//        $sRespData = "<xml><ToUserName><![CDATA[mycreate]]></ToUserName><FromUserName><![CDATA[wx5823bf96d3bd56c7]]></FromUserName><CreateTime>1348831860</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[this is a test]]></Content><MsgId>1234567890123456</MsgId><AgentID>128</AgentID></xml>";
//        $sEncryptMsg = ""; //xml格式的密文
//        $errCode = $wxcpt->EncryptMsg($sRespData, $sReqTimeStamp, $sReqNonce, $sEncryptMsg);
//        if ($errCode == 0) {
//            var_dump($sEncryptMsg);
//            // TODO:
//            // 加密成功，企业需要将加密之后的sEncryptMsg返回
//            // HttpUtils.SetResponce($sEncryptMsg);  //回复加密之后的密文
//        } else {
//            print("ERR: " . $errCode . "\n\n");
//            // exit(-1);
//        }
    }

    public function updateInfo($sMsg){
        Logger::getInstance()->log(json_encode($sMsg));
        $xml = simplexml_load_string($sMsg);
        if ($xml->Event->__toString() == "change_contact") {
            $employees = new EmployeesModel();
            if ($xml->ChangeType->__toString() == "create_user") {
                $data['qy_userid'] = $xml->UserID->__toString();
                $data['qy_name'] = $xml->Name->__toString();
                $data['department'] = '[' . $xml->Department->__toString() .']';
                $data['position'] = $xml->Position->__toString();
                $data['qy_mobile'] = $xml->Mobile->__toString();
                $data['gender'] = $xml->Gender->__toString();
                $data['email'] = $xml->Email->__toString();
                $data['enable'] = intval($xml->Status->__toString());
                $data['avatar'] = $xml->Avatar->__toString();
                $employees->saveCompanyUserInfoUserId($data);
            }
            if ($xml->ChangeType->__toString() == "update_user") {
                $data['qy_userid'] = $xml->UserID->__toString();
                if(isset($xml->Name)){
                    $data['qy_name'] = $xml->Name->__toString();
                }
                if(isset($xml->Department)){
                    $data['department'] = '[' . $xml->Department->__toString() .']';
                }
                if(isset($xml->Position)){
                $data['position'] = $xml->Position->__toString();
                }
                if(isset($xml->Mobile)){
                $data['qy_mobile'] = $xml->Mobile->__toString();
                }
                if(isset($xml->Gender)){
                $data['gender'] = intval($xml->Gender->__toString());
                }
                if(isset($xml->Email)){
                $data['email'] = $xml->Email->__toString();
                }
                if(isset($xml->Status)){
                $data['enable'] = intval($xml->Status->__toString());
                }
                if(isset($xml->Avatar)){
                $data['avatar'] = $xml->Avatar->__toString();
                }
                $employees->saveCompanyUserInfoUserId($data);
            }
            if ($xml->ChangeType->__toString() == "delete_user") {
                $data['qy_userid'] = $xml->UserID->__toString();
                $data['enable'] = 2;
                $employees->saveCompanyUserInfoUserId($data);
            }
            if ($xml->ChangeType->__toString() == "create_party") {
                $data['id'] = intval($xml->Id->__toString());
                $data['name'] = $xml->Name->__toString();
                $data['parentid'] = intval($xml->ParentId->__toString());
                $data['order'] = intval($xml->Order->__toString());
                $employees->saveDepartmentInfo($data);
            }
            if ($xml->ChangeType->__toString() == "update_party") {
                $data['id'] = intval($xml->Id->__toString());
                if(isset($xml->Name)){
                    $data['name'] = $xml->Name->__toString();
                }
                if(isset($xml->ParentId)){
                    $data['parentid'] = intval($xml->ParentId->__toString());
                }
                if(isset($xml->Order)){
                    $data['order'] = intval($xml->Order->__toString());
                }
                $employees->saveDepartmentInfo($data);
            }
            if ($xml->ChangeType->__toString() == "delete_party") {
                $data['id'] = intval($xml->Id->__toString());
                $data['is_del'] = 1;
                $employees->saveDepartmentInfo($data);
            }
        }
    }
}