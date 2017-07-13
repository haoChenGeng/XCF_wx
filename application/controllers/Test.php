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
//     	$this->load->model('Model_db');
//     	$arr = array(0=>array('Customername'=>13211112222));
//     	$this->Model_db->incremenUpdate('customer',$arr,'Customername');
//     	var_dump($this->db->dbprefix('p2_customer'));
//     	var_dump(date('Y-m-d H:i:s',1499652514));
//     	$arr = array('code'=>'bgMsgSend','certificatetype'=>'0','certificateno'=>'130426198906063501','depositacctname'=>'test','depositacct'=>'6225881209690998','channelid'=>'KQ03','mobiletelno'=>'13554719692','customerNo'=>'60','channelname'=>'bankName111111bank');
//     	var_dump(base64_encode(json_encode($arr)));
//     	echo 'http://10.17.2.101/jijin/JFAPPinterface/test/'.base64_encode(json_encode($arr));
//     	var_dump(unserialize('a:4:{s:4:"code";s:4:"0000";s:3:"msg";s:25:"数据字典查询成功!";s:8:"trantype";s:6:"520020";s:4:"data";a:11:{i:0;a:4:{s:5:"sysid";s:1:"4";s:8:"dictitem";s:6:"120048";s:7:"subitem";s:2:"01";s:11:"subitemname";s:21:"法人或其他组织";}i:1;a:4:{s:5:"sysid";s:1:"4";s:8:"dictitem";s:6:"120048";s:7:"subitem";s:2:"02";s:11:"subitemname";s:12:"金融机构";}i:2;a:4:{s:5:"sysid";s:1:"4";s:8:"dictitem";s:6:"120048";s:7:"subitem";s:2:"03";s:11:"subitemname";s:21:"证券公司子公司";}i:3;a:4:{s:5:"sysid";s:1:"4";s:8:"dictitem";s:6:"120048";s:7:"subitem";s:2:"04";s:11:"subitemname";s:21:"期货公司子公司";}i:4;a:4:{s:5:"sysid";s:1:"4";s:8:"dictitem";s:6:"120048";s:7:"subitem";s:2:"05";s:11:"subitemname";s:21:"私募基金管理人";}i:5;a:4:{s:5:"sysid";s:1:"4";s:8:"dictitem";s:6:"120048";s:7:"subitem";s:2:"06";s:11:"subitemname";s:18:"社会保障基金";}i:6;a:4:{s:5:"sysid";s:1:"4";s:8:"dictitem";s:6:"120048";s:7:"subitem";s:2:"07";s:11:"subitemname";s:27:"企业年金等养老基金";}i:7;a:4:{s:5:"sysid";s:1:"4";s:8:"dictitem";s:6:"120048";s:7:"subitem";s:2:"08";s:11:"subitemname";s:33:"慈善基金等社会公益基金";}i:8;a:4:{s:5:"sysid";s:1:"4";s:8:"dictitem";s:6:"120048";s:7:"subitem";s:2:"09";s:11:"subitemname";s:37:"合格境外机构投资者（QFII）";}i:9;a:4:{s:5:"sysid";s:1:"4";s:8:"dictitem";s:6:"120048";s:7:"subitem";s:2:"10";s:11:"subitemname";s:47:"人民币合格境外机构投资者（RQFII）";}i:10;a:4:{s:5:"sysid";s:1:"4";s:8:"dictitem";s:6:"120048";s:7:"subitem";s:2:"11";s:11:"subitemname";s:9:"自然人";}}}'));
//     	var_dump(unserialize('a:4:{s:4:"code";s:4:"0000";s:3:"msg";s:25:"数据字典查询成功!";s:8:"trantype";s:6:"520020";s:4:"data";a:2:{i:0;a:4:{s:5:"sysid";s:1:"4";s:8:"dictitem";s:6:"120050";s:7:"subitem";s:1:"0";s:11:"subitemname";s:21:"未从事相关职业";}i:1;a:4:{s:5:"sysid";s:1:"4";s:8:"dictitem";s:6:"120050";s:7:"subitem";s:1:"1";s:11:"subitemname";s:90:"专业投资者的高级管理人员、从事金融相关业务的注册会计师或律师";}}}'));
//     	$arr = array('code'=>'bgMsgCheck','token'=>'625491','customerNo'=>'60','tpasswd'=>'1234567891231','verificationCode'=>324048);
//     	var_dump(base64_encode(json_encode($arr)));
//     	echo 'http://10.17.2.101/jijin/JFAPPinterface/test/'.base64_encode(json_encode($arr));
//     	var_dump($this->db->select('*')->from('fundlist')->count_all_results());
//     	var_dump($_SESSION['viewAllFund'],$_SESSION['riskLevel']);
//     	ob_start();
    	$arr = Array(
    			'msgTy' => 'sucess',
    			'msgContent' => '基金开户成功',
    			'base' => $this->base
    			);
    	$this->load->view('jijin/account/registerResult', $arr);
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
//     	var_dump($this->fund_interface->RenewFundAESKey('10.10.78.107','123456'));
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