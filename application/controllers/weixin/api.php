<?php
/**
 * Created by PhpStorm.
 * User: Jensen
 * Date: 2015/10/12
 * Time: 10:24
 */
include_once(dirname(__DIR__).DIRECTORY_SEPARATOR.'CommonUtil.php');
include_once "wxBizMsgCrypt.php";
include_once 'wxConfig.php';

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
        $this->load->model("model_common");
    }
    private function log($string){
        CommonUtil::wLog(__DIR__.DIRECTORY_SEPARATOR.'weixin.txt',$string."\r\n");
    }
    private function logOpenID($string){
        CommonUtil::wLog(__DIR__.DIRECTORY_SEPARATOR.'weixin_openID.txt',$string."\r\n");
    }

    public function test(){
        $keyword=strtolower('ZHmm#xn016395');
        $rule='/^zhmm#(xn[0-9]{6})$/';
        $res= preg_match($rule,$keyword,$result);
        $dsf=$res;
    }
    /**
     * 显示当前菜单json
     */
    public function showCurrentMenu(){
        $this->verifyAccessToken();
        $getMenuUrl="https://api.weixin.qq.com/cgi-bin/menu/get?access_token=".$this->access_token;
        echo CommonUtil::httpsRequest($getMenuUrl,"GET",null);
    }

    /**
     * 更新微信菜单  菜单内容 -- menu.json
     */
    public function updateMenu(){
        $this->verifyAccessToken();
        $createMenuUrl="https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$this->access_token;
        $menuJson= file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'menu.json');
        $jsonStr= CommonUtil::httpsRequest($createMenuUrl,"POST",$menuJson);
        $resultArr = json_decode($jsonStr, true);
        if(isset($resultArr['errcode'])&&$resultArr['errcode']==0){
            echo '微信公众号菜单更新成功';
        }else
            echo '微信公众号菜单更新失败，请重试'.$jsonStr;
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

    /**
     * 微信事件消息推送
     * @throws Exception
     */
    public function handle()
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
        $postObj = simplexml_load_string($xmlStr, 'SimpleXMLElement', LIBXML_NOCDATA);
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
            $contentArr['Content']="您好,这里是小牛新财富!\n全新平台,全新体验。操作有疑问请您点击界面下方【周到服务】中的“操作指引”和“常见问题”查看。\n更多理财咨询,请拨打客服热线:400-669-5666。";
        }
        return CommonUtil::array_to_xml(array_merge($resArr,$contentArr));
    }

    private function dealTextMsg($postObj){
        $keyword = trim($postObj->Content);
        $resArr=array();
        $this->logOpenID( "TEXT"." | " .trim($postObj->FromUserName)  ." | " .trim($postObj->ToUserName). " | ". trim($postObj->Content));
        if(preg_match('/^zhmm#((xn|dl)[0-9]{6})$/',strtolower(trim($keyword)),$result)){
                    $plannerId=$result[1];
                    $plannerInfo=$this->model_common->db_one('money_planner', Array('login_name' => $plannerId));
                    if(!empty($plannerInfo)) {
                        $resArr['MsgType'] = MSG_TYPE_NEWS;
                        $resArr['Articles'] = array(array('Title' => '嘘..小牛新财富理财师重置密码秘密通道!',
                            'Description' => '小牛新财富理财师找回密码',
                            'PicUrl' => 'https://mmbiz.qlogo.cn/mmbiz/icvr2XhtuM8xFia16L7jDF3ypoUShUxymZR0Go7zhBOlicP6MyfLK9RAIXcmx8FvB3RPcLXiccSjSFXvULUck6oLwA/0?wx_fmt=jpeg',
                            'Url' => $this->base . '/user/findPlannerPass?plannerId=' . $plannerId));
                        $resArr['ArticleCount'] = count($resArr['Articles']);
					}
        }
		else
		{
			$resArr['MsgType']='transfer_customer_service';
		}
/*
		// 微信的多客转接不完善，根据文档，一旦用'MsgType'='transfer_customer_service'后，用户的后续消息自动转多客服
		// 实测几天下来，有时是后续消息会自动转到多客服，有时却不是这样子，每条后续都要加'MsgType'='transfer_customer_service'才能转多客服
		//yks add for client service system
        $sessid='m'.substr(trim($postObj->FromUserName),strlen($postObj->FromUserName)-12,12);
		//yks add log for check bind user

        $this->logOpenID( "TEXT"." | " .trim($postObj->FromUserName)  ." | " .trim($postObj->ToUserName). " | ". trim($postObj->Content)." | ".$_SESSION[$sessid]." | ".$sessid);
        //yks add log for check bind user
		
        if(strpos($keyword,'客服')!==false)
        {
            $resArr['MsgType']='transfer_customer_service';
            if(!isset($_SESSION[$sessid]))
                $_SESSION[$sessid]='inclientsystem';
            return $resArr;
        }
        if(strcmp( strtoupper(trim($keyword)),'Q')==0)
        {
            if(isset($_SESSION[$sessid]))
			{
                unset($_SESSION[$sessid]);
            $resArr['MsgType']=MSG_TYPE_TEXT;
            $resArr['Content']="离开客服系统！";
            return $resArr;
			}
        }

        if($_SESSION[$sessid]=='inclientsystem')
        {
            $resArr['MsgType']='transfer_customer_service';
            return $resArr;
        }

        switch($keyword){
            default:
                if(preg_match('/^zhmm#((xn|dl)[0-9]{6})$/',strtolower(trim($keyword)),$result)){
                    $plannerId=$result[1];
                    $plannerInfo=$this->model_common->db_one('money_planner', Array('login_name' => $plannerId));
                    if(!empty($plannerInfo)) {
                        $resArr['MsgType'] = MSG_TYPE_NEWS;
                        $resArr['Articles'] = array(array('Title' => '嘘..小牛新财富理财师重置密码秘密通道!',
                            'Description' => '小牛新财富理财师找回密码',
                            'PicUrl' => 'https://mmbiz.qlogo.cn/mmbiz/icvr2XhtuM8xFia16L7jDF3ypoUShUxymZR0Go7zhBOlicP6MyfLK9RAIXcmx8FvB3RPcLXiccSjSFXvULUck6oLwA/0?wx_fmt=jpeg',
                            'Url' => $this->base . '/user/findPlannerPass?plannerId=' . $plannerId));
                        $resArr['ArticleCount'] = count($resArr['Articles']);
                    }
                }
                break;
        }
*/
        return $resArr;
    }
    private function dealEventMsg($postObj){
        $event = trim($postObj->Event);
        $fromUser=trim($postObj->FromUserName);
        $resArr=array();
        switch($event){
            case EVENT_SUBSCRIBE: //用户关注&扫码关注
                $num=$this->model_common->db_num('user_scanner', Array('openid' => $fromUser));
                if($num>0){
                    break;
                }
                $qrKey= trim($postObj->EventKey);
                if(!empty($qrKey)&&is_string($qrKey)){
                    $qrKey=str_replace('qrscene_','',$qrKey);
                    $scanner=array('openid'=>$fromUser,'addtime'=>time());
                    if(is_numeric($qrKey)){ //临时二维码  --普通用户
                        $scanner['info_id']=$qrKey;
                        $scanner['info_type']=0;
                    }else{ //永久二维码 --理财师 or 活动 or组织机构
                        $scene_id=substr($qrKey,1);
                        $qrInfo=$this->model_common->db_one_str('weixin_qr', Array('scene_id' => $scene_id,'status'=>1));
                        if(empty($qrInfo)){
                            $qrInfo=$this->db->query("select id from p2_weixin_qr where scene_id = {$scene_id} order by addtime desc limit 1")->row_array();
                        }
                        $scanner['info_id']=$qrInfo['id'];
                        $scanner['info_type']=1;
                    }
                    $this->model_common->db_insert('user_scanner', $scanner);
                }
                break;
            case EVENT_SCAN: //已关注--扫码
                $num=$this->model_common->db_num('user_scanner', Array('openid' => $fromUser));
                if($num>0){
                    break;
                }
                $qrKey= trim($postObj->EventKey);
                if(!empty($qrKey)){
                    $scanner=array('openid'=>$fromUser,'addtime'=>time());
                    if(is_numeric($qrKey)){ //临时二维码  --普通用户
                        $scanner['info_id']=$qrKey;
                        $scanner['info_type']=0;
                    }else{ //永久二维码 --理财师 or 活动 or组织机构
                        $scene_id=substr($qrKey,1);
                        $qrInfo=$this->model_common->db_one_str('weixin_qr', Array('scene_id' => $scene_id,'status'=>1));
                        if(empty($qrInfo)){
                            $qrInfo=$this->db->query("select id from p2_weixin_qr where scene_id = {$scene_id} order by addtime desc limit 1")->row_array();
                        }
                        $scanner['info_id']=$qrInfo['id'];
                        $scanner['info_type']=1;
                    }
                    $this->model_common->db_insert('user_scanner', $scanner);
                }
                break;
            case EVENT_CLICK: //菜单点击事件
                $clickKey= trim($postObj->EventKey);
                switch($clickKey){
                    case 'k_activity': //福利在这
                        $resArr['MsgType']=MSG_TYPE_TEXT;
                        $resArr['Content']="福利活动正在紧急筹备中...";
                        break;
                    case 'k_vip': //会员特权
                        $resArr['MsgType']=MSG_TYPE_TEXT;
                        $resArr['Content']="会员特权秘密规划中...";
                        break;
                    case 'k_service_tel': //客服电话
                        $resArr['MsgType']=MSG_TYPE_TEXT;
                        $resArr['Content']="您好！很高兴为您服务，我们的客服热线是\n400-669-5666或0755-82760370也可以直接回复“人工咨询”联系在线客服。";
                        break;
                }
                break;
        }

        return $resArr;
    }

    /**
     * 新增永久微信二维码信息
     * @param $infoId
     * @param $type  0->理财师 1->组织团体 2->活动
     */
    public function addWeixinQrInfo($infoId,$type=0){
        $num=$this->model_common->db_num('weixin_qr', Array('info_id' => $infoId,'info_type'=>$type));
        if($num>0){
            return ;
        }
        $recoveryQr=$this->db->query("select id,scene_id,qr_url from p2_weixin_qr where status = 0 and info_type={$type}  limit 1")->row_array();
        if(empty($recoveryQr)){
            $nowTime=time();
            $sql="INSERT INTO p2_weixin_qr(info_id,info_type,scene_id,addtime) select {$infoId},{$type},IFNULL((select max(scene_id)+1 from p2_weixin_qr),1),{$nowTime}";
            $res= $this->db->query($sql);
            if($res&&$this->db->affected_rows()>0){
                $qrInfo=$this->model_common->db_one_str('weixin_qr', Array('info_id' => $infoId,'info_type'=>$type));
                $qr_url=$this->createQrUrl('s'.($qrInfo['scene_id']));
                $this->model_common->db_update('weixin_qr', array('qr_url'=>$qr_url), Array('id' => $qrInfo['id']));
            }else
                $this->addWeixinQrInfo($infoId,$type);
        }else{
            $res=$this->model_common->db_update('weixin_qr', array('status'=>-1), Array('id' => $recoveryQr['id'],'status'=>0));
            if($res&&$this->db->affected_rows()>0) {
                $data=array(
                    'info_id' => $infoId,
                    'info_type' => $type,
                    'scene_id' => $recoveryQr['scene_id'],
                    'qr_url'=>$recoveryQr['qr_url'],
                    'status' => 1,
                    'addtime' => time(),
                );
                $this->model_common->db_insert('weixin_qr',$data);
            }else
                $this->addWeixinQrInfo($infoId,$type);
        }
    }

    /**
     * @param $scene_id 1~100000（永久二维码）
     * @return string
     */
    private function createQrUrl($scene_id){
        $this->verifyAccessToken();
        $createQrCodeUrl="https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".$this->access_token;
        $data=array();
        if(is_numeric($scene_id)){
            $data['expire_seconds']=604800;
            $data['action_name']='QR_SCENE';
            $data['action_info']=array('scene'=>array('scene_id'=>$scene_id));
        }else{
            $data['action_name']='QR_LIMIT_STR_SCENE';
            $data['action_info']=array('scene'=>array('scene_str'=>$scene_id));
        }
        $jsonStr= CommonUtil::httpsRequest($createQrCodeUrl,"POST",json_encode($data));
        $qr_url='';
        if (!empty($jsonStr)) {
            $resultArr = json_decode($jsonStr, true);
            if (isset($resultArr['url'])) {
                $qr_url=$resultArr['url'];
            } else {
                $this->log('createQrUrl-failure:' . $jsonStr);
            }
        } else {
            $this->log('createQrUrl-failure:' . date('Y-m-d H:i:s'));
        }
        return $qr_url;
    }


    public function initPersistQr(){
        $this->initMoneyPlannerQr();
    }
    private function initMoneyPlannerQr($page=0,$perPageNum=1000){
        $start=$page*$perPageNum;
        $sql="SELECT * FROM p2_money_planner limit {$start},{$perPageNum}";
        $data = $this->db->query($sql)->result_array();
        foreach ($data as $key => $value) {
            $this->addWeixinQrInfo($value['id']);
        }
        if(count($data)>=$perPageNum)
            $this->initMoneyPlannerQr(++$page);
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