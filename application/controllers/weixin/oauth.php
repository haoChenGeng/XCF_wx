<?php

/**
 * Created by PhpStorm.
 * User: Jensen
 * Date: 2015/10/14
 * Time: 9:49
 */
include_once(dirname(__DIR__).DIRECTORY_SEPARATOR.'CommonUtil.php');
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
        $this->load->model("model_common");
        $this->load->helper("url");
        $this->weixinOauth();
    }
    private function weixinOauth(){
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
    public function login(){
        if (isset($_SESSION['customer_id'])) {
            redirect($this->base . "/member");
        }
        $this->load->view('ui/login');
    }
    
    function register($type)
    {
        if (isset($_SESSION['customer_id'])) {
            redirect($this->base . "/user/home/".$type);
            exit;
        }
        $userScanInfo=$this->model_common->db_one('user_scanner', Array('openid' => $this->openId));
        if(!empty($userScanInfo)&&$userScanInfo['info_type']==1){ //永久二维码
            $qrInfo=$this->model_common->db_one_str('weixin_qr', Array('id' => $userScanInfo['info_id']));
            if($qrInfo['info_type']==0){ //理财师
                $planner_data = $this->model_common->db_one('money_planner', Array('id' => $qrInfo['info_id']));
                $data['planner_id'] = $planner_data['login_name'];
            }
        }
        $data['openid'] = $this->openId;
        $data['public_key'] = file_get_contents($this->config->item('RSA_publickey'));   //获取RSA_加密公钥
        $data['rand_code'] = mt_rand(100000,999999);                                     //随机生成验证码
        $_SESSION['rand_code'] = $data['rand_code'];
        $this->load->view ( 'user/register' , $data);
    }
    
}