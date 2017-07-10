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
        $this->load->database();
    }
    
    function index() {
    	$arr = array('code'=>'bgMsgSend','certificatetype'=>'0','certificateno'=>'130426198906063501','depositacctname'=>'test','depositacct'=>'6225881209690998','channelid'=>'KQ03','mobiletelno'=>'13554719692','customerNo'=>'71','aa'=>'aaaaaaaaa');
    	var_dump(base64_encode(json_encode($arr)));
    	echo 'http://10.17.2.101/jijin/JFAPPinterface/test/'.base64_encode(json_encode($arr));
//     	var_dump($this->db->select('*')->from('fundlist')->count_all_results());
//     	var_dump($_SESSION['viewAllFund'],$_SESSION['riskLevel']);
//     	ob_start();
//     	$arr = Array(
//     			'msgTy' => 'sucess',
//     			'msgContent' => '基金开户成功',
//     			'base' => $this->base
//     			);
//     	$this->load->view('jijin/account/registerResult', $arr);
//     	ob_end_flush();
//     	exit();
//     	$randSeq = array_rand(range(0,0),1);
//     	var_dump($randSeq);
//     	var_dump(strtotime(date('Y-m-d',time()).' 10:00:00'));
//     	$data['url'] = $this->config->item('fundUrl').'/jijin/XCFinterface';
//     	$data['formData'] = array('code'=>'功能号','customerNo'=>'客户号','certificatetype'=>'证件类型','certificateno'=>'证件号码','custno'=>'金证客户号','lpasswd'=>'密码');
//     	$this->load->view('UrlTest',$data);
//     	$this->load->library('Fund_interface');
//     	var_dump($this->fund_interface->getReturnData('K4D8YD4bSe4UB6fGkIAYKMZQehk24aNoIeXssB8/Xc5vjauLR9dGLxPGl3Fo6okJ6REAFzWjIZ5/ZSup7mgTmKRaqnJzPFvJpRXUKe06I9Q='));
//     	var_dump($this->fund_interface->RenewFundAESKey('123456'));
//     	$res = $this->fund_interface->Trans_applied('20170301', '20170501');
//     	var_dump($res);
//     	var_dump(date('Y-m-d H:i:s',1493716505));
// $this->load->database();
//     	$this->db->query('UPDATE `p2_dealitems` SET `times` = `times`-1 WHERE `dealitem` = "sendSms"');
//     	$this->load->library('Fund_interface');
//     	$this->fund_interface->fund_list();
//     	$mtime = microtime();
//     	var_dump($mtime);
//     	$mtime=explode(' ',$mtime);
//     	$startTime=$mtime[1]+$mtime[0];
//     	var_dump($startTime,$mtime[0]);
// $_SERVER['CI_ENV'] = 'test';
// var_dump($_SERVER);
    }
    
}