<?php
if (!defined('BASEPATH')){
	exit('No direct script access allowed');
}
    
class Logincontroller
{
	private $CI;
    function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->database();
        $this->CI->load->helper(array("url"));   
        $this->base = $this->CI->config->item("base_url");
    }
    
	//判断是否登录，返回值 true  false
    function isLogin()
    {
    	if (!isset($_SESSION['JZ_user_id']) || $_SESSION['JZ_user_id'] == -1){
    		$this->jz_login();
    	}
    	return ($_SESSION['JZ_user_id']>0);
    }
    
/*     function gotoXnLogin() {
    	redirect($this->CI->config->item('pre_homepage') . "/user/login/152");//102 服务器;152 - localhost
//     	redirect("http://10.10.78.109" . "/user/login/152");//102 服务器;152 - localhost
    } */
    
    
    function jz_login() {
    	if (!isset($_SESSION['JZ_user_id']) || $_SESSION['JZ_user_id'] == -1){
    		if (isset($_SESSION['customer_id'])){
    			$this->CI->load->library(array('Fund_interface'));
    			$res = $this->CI->fund_interface->AccountInfo();
    			if (key_exists('code', $res) && $res["code"] == '0000'){
    				$_SESSION['JZ_user_id'] = $res["data"]['JZ_account'];
    			}else{
    				$_SESSION['JZ_user_id'] = 0;
    			}
    		}else{
    			$_SESSION['JZ_user_id'] = -1;				
    			//$_SESSION['JZ_user_id'] = -1表示未登录微信账号，0表示已登录微信账号但未开通基金交易， 1表示已开通基金交易
    		}
    	}
    }
    
    /*
     * 和登录系统的接口，登录系统由此进入基金系统
     * */
    function entrance()
    {
    	$this->jz_login();
    	redirect($this->base . "/jijin/Jz_fund");
    }
    
    function logout()
    {
    	redirect ($this->CI->base . "/User/home/".$type);
    }
}
