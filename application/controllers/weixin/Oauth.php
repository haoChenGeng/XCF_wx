<?php
include_once 'CommonUtil.php';
include_once 'wxConfig.php';
include_once 'Api.php';

class Oauth extends MY_Controller
{
    private $openId;
    private $access_token='';
    private $accessTokenInvalidTime=0;
    
    private function log($string){
       // CommonUtil::wLog('Oauth.txt',$string."\r\n");
    }

    /**
     * Oauth constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        //$this->load->model("Model_dbwx");
        $this->load->helper("url");
        $this->weixinOauth();
    }
    public function weixinOauth(){
        $code=$this->input->get('code');
        if(empty($code)){
            $accessCodeUrl='https://open.weixin.qq.com/connect/oauth2/authorize?appid=APPID&redirect_uri=REDIRECT_URL&response_type=code&scope=snsapi_base#wechat_redirect';
			$redirectUrl=$this->base.$_SERVER['REQUEST_URI'];
			$accessCodeUrl=strtr($accessCodeUrl,array('APPID'=>APPID,'REDIRECT_URL'=>urlencode($redirectUrl)));
			redirect($accessCodeUrl);
        }else{
            $accessTokenUrl='https://api.weixin.qq.com/sns/oauth2/access_token?appid=APPID&secret=SECRET&code=CODE&grant_type=authorization_code';
            $accessTokenUrl=strtr($accessTokenUrl,array('APPID'=>APPID,'SECRET'=>APPSECRET,'CODE'=>$code));
            $jsonStr= CommonUtil::httpsRequest($accessTokenUrl);
            if (!empty($jsonStr)) {
            	$resultArr = json_decode($jsonStr, true);
            	if (! empty ( $resultArr['openid'])) {
            		$wxApi = new Api ();
    	        	$userInfo = $wxApi->getUserInfo ($resultArr['openid']);
            		if (! empty ( $userInfo )) {
            			$_SESSION['headimgurl']=$userInfo['headimgurl'];
            			$_SESSION['open_id']=$userInfo[openid];
            			$arr ['headimgurl'] = $userInfo ['headimgurl'];
            			$arr ['sex'] = $userInfo ['sex'];
            			$arr ['nickname'] = $userInfo ['nickname'];
            			$arr ['province'] = $userInfo ['province'];
            			$arr ['city'] = $userInfo ['city'];
            			$arr ['country'] = $userInfo ['country'];
            			$arr ['language'] = $userInfo ['language'];
            			$arr ['openid'] = $userInfo ['openid'];
            			$arr ['subscribe'] =$userInfo ['subscribe'];
            			$arr ['subscribe_time'] =$userInfo ['subscribe_time'];
            			$res = $this->db->insert('wxuserinfo',$arr);
            		}
            	}
            	else 
            	{
            		$_SESSION['headimgurl']=null;
            		$_SESSION['open_id']=null;
            	}
            }
        }
    }
    public function checkwxaccess(){
   		redirect('/user/homeaccess');
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
}