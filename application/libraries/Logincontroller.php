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
    	return isset($_SESSION['JZ_user_id']);
    }
    
    function gotoXnLogin() {
    	redirect($this->CI->config->item('pre_homepage') . "/user/login/152");//102 服务器;152 - localhost
//     	redirect("http://10.10.78.109" . "/user/login/152");//102 服务器;152 - localhost
    }
    
    
    function jz_login($XN_account) {
    	$sql = "SELECT * FROM jz_account WHERE XN_account = ?";
    	$user_info = $this->CI->db->query($sql, array($XN_account))->row_array();            //查询不到返回null
    	if ($user_info != null)
    	{
    		$_SESSION['JZ_account'] =$user_info['JZ_account'];
    		$_SESSION['JZ_user_id'] = $user_info['id'];
    	}
    }
    
    //基金登录
/*     function login()
    {
    	if (!isset($_SESSION ['customer_name']))//如果用户未登录，则进入用户登录界面
    	{
    		$this->gotoXnLogin();
    		exit;
    	} else {
    		$this->jz_login($_SESSION ['customer_name']);
    	}
    	 
    	if (!isset($_SESSION['JZ_account']))//如果基金账号未开户，则进入基金中心（登录入口）
    	{
    		redirect($this->base . "/jijin/Jz_my");
    	} else {
    		redirect($_SESSION['next_url']);
    	}
    } */
    
    /*
     * 和登录系统的接口，登录系统由此进入基金系统
     * */
    function entrance()
    {
    	if (isset($_SESSION ['customer_name']))
    	{
    		$this->jz_login($_SESSION ['customer_name']);
    	}
    	redirect($this->base . "/jijin/Jz_fund");
    }
    
    function logout()
    {
    	if (isset($_SESSION['JZ_user_id'])) {
    		unset($_SESSION['JZ_user_id']);
    	}
    
    	if (isset($_SESSION ['customer_name'])) {
    		unset($_SESSION ['customer_name']);
    	}
    
    	if (isset($_SESSION['JZ_account'])) {
    		unset($_SESSION['JZ_account']);
    	}
    	$CI = &get_instance();
    	redirect($CI->config->item("pre_homepage"));
    
    }
}
