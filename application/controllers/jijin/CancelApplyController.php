<?php
/**
 * 撤单-控制类
 */

if (!defined('BASEPATH')){
	exit('No direct script access allowed');
}

class CancelApplyController extends MY_Controller {
	function __construct()
	{
		parent::__construct();
		$this->load->library(array('Fund_interface','Logincontroller'));
		$this->load->database();
	}
	
	//撤单
	function cancel() {
		if (!$this->logincontroller->isLogin()) {
			exit;
		}
		$_SESSION['myPageOper'] = 'history';
		$get = $this->input->get();
		if (isset($_SESSION['cancelableApply'][$get['appsheetserialno']])){
			$data = $_SESSION['cancelableApply'][$get['appsheetserialno']];
		}
/* 		if(isset($_SESSION['todayTrade'])){
			foreach ($_SESSION['todayTrade'] as $val){
				if ($val['appsheetserialno'] == $get['appsheetserialno']){
					$data['appsheetserialno'] = $val['appsheetserialno'];
					$data['fundcode'] = $val['fundcode'];
					$data['fundname'] = $val['fundname'];
					$data['applicationamount'] = $val['applicationamount'];
					$data['applicationvol'] = $val['applicationvol'];
					$data['operdate'] = $val['operdate'];
					$data['businesscode'] = $val['businesscode'];
					break;
				}
			}
			unset($_SESSION['todayTrade']);
		} */
		else{
			$this->load->helper(array("url"));
			redirect($this->base . "/jijin/Jz_my");
		}
		$data['base'] = $this->base;
		$data['public_key'] = file_get_contents($this->config->item('RSA_publickey')); //获取RSA_加密公钥
		$data['rand_code'] = "\t".mt_rand(100000,999999);                              //随机生成验证码
		$_SESSION['cancel_rand_code'] = $data['rand_code'];
		$this->load->view('jijin/trade/cancel_apply',$data);
	}
	
	//撤销结果
	function CancelResult() {
		if (!$this->logincontroller->isLogin()) {
			exit;
		}
		$post = $this->input->post();
		if (!isset($_SESSION['cancel_rand_code'])){
			$this->load->helper(array("url"));
			redirect($this->base . "/jijin/Jz_my");
		}else{
			$post = $this->input->post();
			$private_key = openssl_get_privatekey(file_get_contents($this->config->item('RSA_privatekey')));
			$decryptData ='';
			openssl_private_decrypt(base64_decode($post['tpasswd']),$decryptData, $private_key, OPENSSL_PKCS1_PADDING);
			$div_bit = strpos($decryptData,(string)$_SESSION['cancel_rand_code']);
			unset($_SESSION['cancel_rand_code']);
			if ($div_bit !== false){                           //找到一次性随机验证码
				$revoke['appsheetserialno'] = $post['appsheetserialno'];
				$revoke['tpasswd'] = substr($decryptData, 0, $div_bit);
				$res = $this->fund_interface->revoke($revoke);
// $data['data'] = $res['data'];
// var_dump($res);
// $data['url'] = $this->config->item('fundUrl').'/jijin/XCFinterface';
// $this->load->view('UrlTest',$data);
// var_dump($res);
// return;
				file_put_contents('log/trade/cancelApply'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n用户:".$_SESSION ['customer_name']."进行撤单操作，交易数据为:".$revoke['appsheetserialno']."\r\n返回数据:".serialize($res)."\r\n\r\n",FILE_APPEND);
				if (isset($res['code'])){
					$data['ret_code'] = '0000';
					if ($res['code'] == '0000'){
						$data['ret_msg'] = '撤单成功';
					}elseif ($res['code'] == '-409999999'){
						$data['ret_msg'] = '交易密码错误，撤单失败';
					}
				}else{
					$data['ret_code'] = 'AAAA';
					$log_msg = '调用撤单接口失败';
				}
			}else{
				$data['ret_code'] = 'SJME';
				$log_msg = '一次性随机验证码未找到';
			}
			if (!isset($data['ret_msg'])){
				$data['ret_msg'] = '撤单失败，请稍候重试';
			}
			if (isset($log_msg)){
				file_put_contents('log/trade/cancelApply'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n用户:".$_SESSION ['customer_name']."进行撤单交易失败，失败原因为:".$log_msg."\r\n\r\n",FILE_APPEND);
			}
			$data['head_title'] = '撤单结果';
			$data['back_url'] = '/jijin/Jz_my';
			$data['base'] = $this->base;
			$this->load->view('ui/view_operate_result',$data);
		}
	}
	
}