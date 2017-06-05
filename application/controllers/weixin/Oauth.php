<?php
include_once 'CommonUtil.php';
include_once 'wxConfig.php';

class Oauth extends MY_Controller
{
    private $openId;

    private function log($string){
        CommonUtil::wLog('Oauth.txt',$string."\r\n");
    }

    /**
     * Oauth constructor.
     */
    public function __construct()
    {
        parent::__construct();
//        $this->load->model("model_common");
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
                if(!isset($resultArr['errcode'])) {
                    $this->openId= $resultArr['openid'];
                }else{
                    $this->log('error:'.$jsonStr);
                    redirect($this->base.explode('?',$_SERVER['REQUEST_URI'])[0]);
                }
            }else
                redirect($this->base.explode('?',$_SERVER['REQUEST_URI'])[0]);
        }
    }
    public function checkwxaccess(){
    	$code=$this->input->get('code');
		redirect('/user/homeaccess');
    }
}