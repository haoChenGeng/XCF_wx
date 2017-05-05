<?php
/**
 * 赎回-控制类
 */

if (!defined('BASEPATH')){
	exit('No direct script access allowed');
}

class RedeemFundController extends MY_Controller {
	private $logfile_suffix;
	function __construct()
	{
		parent::__construct();
		$this->load->library(array('Fund_interface','Logincontroller'));
		$this->load->model(array("Model_db"));
		$this->logfile_suffix = '('.date('Y-m',time()).').txt';
	}
	
	//赎回
	function Redeem() {
		if (!$this->logincontroller->isLogin()) {
			exit;
		}
		$get = $this->input->get();
// var_dump($get);
		$data =json_decode(base64_decode($get['json']),true);
// 		$data['fundcode'] = $data['fundid'];
// 		unset($data['fundid']);
		$data['base'] = $this->base;
		$data['public_key'] = file_get_contents($this->config->item('RSA_publickey')); //获取RSA_加密公钥
		$data['rand_code'] = "\t".mt_rand(100000,999999);                              //随机生成验证码
		$_SESSION['rand_code'] = $data['rand_code'];
		$this->load->view('jijin/trade/view_redeem_fund',$data);
	}
	
	//赎回确认
/* 	function RedeemConfirm() {
		if (!$this->logincontroller->isLogin()) {
			exit;
		}
		$data = $this->input->post();
		$data['public_key'] = file_get_contents($this->config->item('RSA_publickey')); //获取RSA_加密公钥
		$data['rand_code'] = "\t".mt_rand(100000,999999);                              //随机生成验证码
		$_SESSION['rand_code'] = $data['rand_code'];
		$data['base'] = $this->base;
		$this->load->view('jijin/trade/view_redeem_fund_confirm',$data);
	} */
	
	//赎回结果
	function RedeemResult() {
		if (!$this->logincontroller->isLogin()) {
			exit;
		}
		$data = $this->input->post();
// var_dump($data);
// exit;
		$private_key = openssl_get_privatekey(file_get_contents($this->config->item('RSA_privatekey')));
		$decryptData ='';
		openssl_private_decrypt(base64_decode($data['tpasswd']),$decryptData, $private_key, OPENSSL_PKCS1_PADDING);
		//判断一次性随机验证码是否存在
		$div_bit = strpos($decryptData,(string)$_SESSION['rand_code']);
		unset($data['tpasswd']);
		unset($_SESSION['rand_code']);
		if ($div_bit !== false){                           //找到一次性随机验证码
			$tpasswd = substr($decryptData, 0, $div_bit);
			$data['tpasswd'] = $tpasswd;
			$res = $this->fund_interface->redemption($data);
// var_dump($res,$data);
			file_put_contents('log/trade/redeem'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n用户:".$_SESSION ['customer_name']."进行赎回，交易数据为:".serialize($data)."\r\n返回数据:".serialize($res)."\r\n\r\n",FILE_APPEND);
			if (isset($res['code'])){
					$data['ret_code'] = $res['code'];
					if (in_array($res['code'], array('0016','0020','0021'))){
						$data['ret_msg'] = $res['msg'];
					}
			}else{
				$data['ret_code'] = 'AAAA';
				$log_msg = '调用赎回接口失败';
			}
		}else{
			$data['ret_code'] = 'BBBB';
			$log_msg = '一次性随机验证码未找到';
		}
		if (!isset($data['ret_msg'])){
			$data['ret_msg'] = '基金赎回操作失败，请稍候重试';
		}
		if (isset($log_msg)){
			file_put_contents('log/trade/redeem'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n用户:".$_SESSION ['customer_name']."进行赎回交易失败，失败原因为:".$log_msg."\r\n\r\n",FILE_APPEND);
		}
		$data['head_title'] = '赎回结果';
		$data['back_url'] = '/jijin/Jz_my';
		$data['base'] = $this->base;
		$this->load->view('ui/view_operate_result',$data);
	}
	
}