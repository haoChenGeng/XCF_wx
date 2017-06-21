<?php
if (!defined('BASEPATH')){
	exit('No direct script access allowed');
}
    
// header("Content-type: text/html; charset=utf-8");
#include_once(__DIR__.DIRECTORY_SEPARATOR.'/weixin/api.php');

class Test extends MY_Controller
{
	protected $CI;
    function __construct()
    {
        parent::__construct();
    }
    
    function index() {
//     	$data['url'] = $this->config->item('fundUrl').'/jijin/XCFinterface';
//     	$data['formData'] = array('code'=>'功能号','customerNo'=>'客户号','certificatetype'=>'证件类型','certificateno'=>'证件号码','custno'=>'金证客户号','lpasswd'=>'密码');
//     	$this->load->view('UrlTest',$data);
    	$this->load->library('Fund_interface');
    	var_dump($this->fund_interface->getReturnData('K4D8YD4bSe4UB6fGkIAYKMZQehk24aNoIeXssB8/Xc5vjauLR9dGLxPGl3Fo6okJ6REAFzWjIZ5/ZSup7mgTmKRaqnJzPFvJpRXUKe06I9Q='));
//     	var_dump($this->fund_interface->RenewFundAESKey('123456'));
//     	$res = $this->fund_interface->Trans_applied('20170301', '20170501');
//     	var_dump($res);
//     	var_dump(date('Y-m-d H:i:s',1493716505));
// $this->load->database();
//     	$this->db->query('UPDATE `p2_dealitems` SET `times` = `times`-1 WHERE `dealitem` = "sendSms"');
//     	$this->load->library('Fund_interface');
//     	$this->fund_interface->fund_list();
// $_SERVER['CI_ENV'] = 'test';
// var_dump($_SERVER);
    }
    
}