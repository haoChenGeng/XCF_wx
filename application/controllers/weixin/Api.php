<?php
include_once(dirname(__DIR__).DIRECTORY_SEPARATOR.'weixin/CommonUtil.php');
include_once "wxBizMsgCrypt.php";
include_once 'wxConfig.php';
include_once 'CommonUtil.php';
class Api extends MY_Controller
{
    private $access_token='';
    private $accessTokenInvalidTime=0;

    /**
     * Weixin constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }
    private function log($string){
        CommonUtil::wLog(__DIR__.DIRECTORY_SEPARATOR.'weixin.txt',$string."\r\n");
    }
    private function logOpenID($string){
        CommonUtil::wLog(__DIR__.DIRECTORY_SEPARATOR.'weixin_openID.txt',$string."\r\n");
    }
    /**
     * 检查accessToken是否失效
     */
    private function verifyAccessToken(){
        $tokenJson= $this->getAccessTokenJson();
        if(!empty($tokenJson)){
            $resultArr= json_decode($tokenJson,true);
            $this->access_token=$resultArr['access_token'];
            $this->accessTokenInvalidTime=$resultArr['accessTokenInvalidTime'];
        }
        if(empty($this->access_token)||time()>=$this->accessTokenInvalidTime){
            $this->updateAccessToken();
        }
    }

    /**
     * 更新accessToken
     */
    private function updateAccessToken(){
        $fp = fopen(tokenFile , 'w');
        if(flock($fp , LOCK_EX)) {
            if (empty($this->access_token) || time() >= $this->accessTokenInvalidTime) {
                $accessTokenUrl = "https://api.weixin.qq.com/cgi-bin/token";
                $queryStr = "grant_type=client_credential&appid=" . APPID . "&secret=" . APPSECRET;
                $jsonStr = CommonUtil::httpsRequest($accessTokenUrl, "GET", $queryStr);
				if (!empty($jsonStr)) {
                    $resultArr = json_decode($jsonStr, true);
                    $token = $resultArr['access_token'];
                    if (!empty($token)) {
                        $this->access_token = $token;
                        $this->accessTokenInvalidTime = time() + $resultArr['expires_in'] - 5 * 60;
                        $resultArr['accessTokenInvalidTime']=$this->accessTokenInvalidTime;
                        fwrite($fp, json_encode($resultArr));
                        flock($fp, LOCK_UN);
                    } else {
                        $this->log('updateAccessToken-failure:' . $jsonStr);
                    }
                }else{
                    $this->log('updateAccessToken-request-failure:' . date('Y-m-d H:i:s'));
                }
            }
        }
        fclose($fp);
    }
    private function getAccessTokenJson(){
        $fp = fopen(tokenFile , 'r');
        $result='';
        if(flock($fp , LOCK_EX)){
            while(!feof($fp)){
                $result .= fread($fp, 1000);
            }
            flock($fp , LOCK_UN);
        }
        fclose($fp);
        return $result;
    }
	private function pushmsgtowx($open_id,$content)
	{
		$this->verifyAccessToken();
        $Url="https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$this->access_token;
        $data['touser']=$open_id;
		$data['msgtype']="text";
		$data['text']= Array("content"=>"$content");
		$msgJson=json_encode($data,JSON_UNESCAPED_UNICODE);
		$jsonStr= CommonUtil::httpsRequest($Url,"POST",$msgJson,true);
		$resultArr = json_decode($jsonStr, true);
        if(isset($resultArr['errcode'])&&$resultArr['errcode']==0){
            return true;
        }else
            return false;
	}

	public function getpostdata()
    {
		$from_xml = $GLOBALS["HTTP_RAW_POST_DATA"];
        if(empty($from_xml)) {
            $echoStr = $_GET["echostr"];
            if ($this->checkSignature()) {
				echo $echoStr;
                exit;
            }
        }else{
        }
    }
	

    /**
     * 微信事件消息推送
     * @throws Exception
     */
    public function handleaxcf()
    {
		$from_xml = $GLOBALS["HTTP_RAW_POST_DATA"];
        if(empty($from_xml)) {
            $echoStr = $_GET["echostr"];
            if ($this->checkSignature()) {
				echo $echoStr;
                exit;
            }
        }else{
            $xmlMsg = '';
            $pc = new WXBizMsgCrypt(TOKEN, encodingAesKey, APPID);
            $errCode = $pc->decryptMsg($_GET['msg_signature'], $_GET['timestamp'], $_GET['nonce'], $from_xml, $xmlMsg);
			if ($errCode == 0) {
                libxml_disable_entity_loader(true);
                $resMsg= $this->dealMsg($xmlMsg);

                $encryptMsg = '';
                $errCode = $pc->encryptMsg($resMsg, $_GET['timestamp'], $_GET['nonce'], $encryptMsg);
                if ($errCode == 0) {
                    echo $encryptMsg;
                } else {
                    $this->log('receive content:'.$xmlMsg);
                    $this->log('消息加密失败, $errCode:'.$errCode);
                }
            } else {
                $this->log('receive msg:'.$from_xml);
                $this->log('解密失败， $errCode:'.$errCode);
            }
        }
    }
    private function checkSignature()
    {
        if (!defined("TOKEN")) {
            throw new Exception('token is not defined!');
        }

        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $tmpArr = array(TOKEN, $timestamp, $nonce);
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
	
    /**
     * 微信推送消息处理
     * @param $xmlStr
     * @return string
     */
    private function dealMsg($xmlStr){
		CommonUtil::wLog(__DIR__.DIRECTORY_SEPARATOR.'weixinMsg.txt',serialize($xmlStr)."\r\n");

		$postObj = simplexml_load_string($xmlStr, 'SimpleXMLElement', LIBXML_NOCDATA);
	    if(isset($postObj->ComponentVerifyTicket))
			CommonUtil::wLog(__DIR__.DIRECTORY_SEPARATOR.'weixinMsg.txt',$postObj->ComponentVerifyTicket."\r\n");
		$msgType = $postObj->MsgType;
        $resArr=array('ToUserName'=>trim($postObj->FromUserName),'FromUserName'=> trim($postObj->ToUserName),'CreateTime'=>time());
        $contentArr=array();
		switch($msgType){
            case MSG_TYPE_TEXT: //文本消息推送
				$contentArr=$this->dealTextMsg($postObj);
                break;
            case MSG_TYPE_EVENT://事件推送
                $contentArr=$this->dealEventMsg($postObj);
                break;
        }
        if(count($contentArr)==0){
            $contentArr['MsgType']=MSG_TYPE_TEXT;
            $contentArr['Content']="您好,谢谢关注!";
        }
        return CommonUtil::array_to_xml(array_merge($resArr,$contentArr));
    }
    private function dealTextMsg($postObj){
        $keyword = trim($postObj->Content);
        $resArr=array();
        $this->logOpenID( "TEXT"." | " .trim($postObj->FromUserName)  ." | " .trim($postObj->ToUserName). " | ". trim($postObj->Content));
        if(strtolower(trim($keyword))=='s33'){
            $resArr['MsgType']=MSG_TYPE_TEXT;
            $resArr['Content']="欢迎您的到来！";
        }
		else
		{
				$resArr['MsgType']=MSG_TYPE_TEXT;
				$resArr['Content']="欢迎您的到来！";
			//$resArr['MsgType']='transfer_customer_service';
		}
        return $resArr;
    }
    private function dealEventMsg($postObj){
        $event = trim($postObj->Event);
        $fromUser=trim($postObj->FromUserName);
        $resArr=array();
        switch($event){
            case EVENT_SUBSCRIBE: //用户关注&扫码关注
                //$num=$this->model_common->db_num('user_scanner', Array('openid' => $fromUser));
                break;
            case EVENT_SCAN: //已关注--扫码
                break;
            case EVENT_CLICK: //菜单点击事件
                $clickKey= trim($postObj->EventKey);
                switch($clickKey){
                    case 'k_activity': //福利在这
                        $resArr['MsgType']=MSG_TYPE_TEXT;
                        $resArr['Content']="筹备中...";
                        break;
                    case 'k_vip': //会员特权
                        $resArr['MsgType']=MSG_TYPE_TEXT;
                        $resArr['Content']="会员规划中...";
                        break;
                    case 'k_service_tel': //客服电话
                        $resArr['MsgType']=MSG_TYPE_TEXT;
                        $resArr['Content']="您好！这里是小牛投资咨询.\r\n客服电话:\r\n400-669-5666\r\n0755-82760370";
                        break;
                }
                break;
        }

        return $resArr;
    }
    public function getUserInfo($openid){
    	$resArr=array();
    	if(!empty($openid)) {
    		$this->verifyAccessToken();
    		$userInfoUrl='https://api.weixin.qq.com/cgi-bin/user/info?access_token=ACCESS_TOKEN&openid=OPENID&lang=zh_CN';
    		$userInfoUrl=strtr($userInfoUrl,array('ACCESS_TOKEN'=>$this->access_token,'OPENID'=>$openid));
    		$jsonStr= CommonUtil::httpsRequest($userInfoUrl);
    		if (!empty($jsonStr)) {
    			$resultArr = json_decode($jsonStr, true);
    			if(!isset($resultArr['errcode'])) {
    				$resArr=$resultArr;
    			}
    		}
    	}
    	return $resArr;
    }
}
