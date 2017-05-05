<?php
/**
 * 修改分红方式-控制类
 */

if (!defined('BASEPATH')){
	exit('No direct script access allowed');
}

class ModifyBonusController extends MY_Controller {
	private $logfile_suffix;
	function __construct()
	{
		parent::__construct();
		$this->load->library('Fund_interface');
		$this->base = $this->config->item("base_url");
		$this->load->database();
		$this->logfile_suffix = '('.date('Y-m',time()).').txt';
	}
	
	//修改
	function Modify() {
		$get = $this->input->get();
		$json_data = json_decode(base64_decode($get['json']),true);
		$data['json'] = $get['json'];
		$data['fundname'] = $json_data['fundname'];
		$data['fundcode'] = $json_data['fundcode'];
		$data['dividendmethod'] = $json_data['dividendmethod'];
		$data['public_key'] = file_get_contents($this->config->item('RSA_publickey')); //获取RSA_加密公钥
		$data['rand_code'] = "\t".mt_rand(100000,999999);
		$_SESSION['rand_code'] = $data['rand_code'];
		$data['base'] = $this->base;
		$this->load->view('jijin/trade/view_modify_bonus',$data);
	}
	
/* 	//修改确认
	function ModifyConfirm() {
		$fundcode = $_POST['fundcode'];
		$fundname = $_POST['fundname'];
		$bonusType = $_POST['bonusType'];
		$custno = $_POST['custno'];
		$transactionaccountid = $_POST['transactionaccountid'];
		$branchcode = $_POST['branchcode'];
		$sharetype = $_POST['sharetype'];
		
		$data['fundcode'] = $fundcode;
		$data['fundname'] = $fundname;
		$data['bonusType'] = $bonusType;
		$data['custno'] = $custno;
		$data['transactionaccountid'] = $transactionaccountid;
		$data['branchcode'] = $branchcode;
		$data['sharetype'] = $sharetype;
		
		$data['base'] = $this->base;
		$this->load->view('jijin/view_modify_bonus_confirm',$data);
	} */
	
	//修改结果
	function ModifyResult() {
		$post = $this->input->post();
		$data = json_decode(base64_decode($post['json']),true);
		$private_key = openssl_get_privatekey(file_get_contents($this->config->item('RSA_privatekey')));
		$decryptData ='';
		openssl_private_decrypt(base64_decode($post['tpasswd']),$decryptData, $private_key, OPENSSL_PKCS1_PADDING);
		//判断一次性随机验证码是否存在
		$div_bit = strpos($decryptData,(string)$_SESSION['rand_code']);
		unset($_SESSION['rand_code']);
		if ($div_bit !== false){                           //找到一次性随机验证码
			$tpasswd = substr($decryptData, 0, $div_bit);
			unset($data['fundname'],$data['dividendmethodname'],$data['sharetypename'],$data['nav'],$data['dividendmethod']);
			$data['bonusType'] = $post['bonusType'];
			$data['tpasswd'] = $tpasswd;
			$res = $this->fund_interface->bonus_mode($data);
			file_put_contents('log/trade/modifybonus'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n用户:".$_SESSION ['customer_name']."进行赎回，交易数据为:".serialize($data)."\r\n返回数据:".serialize($res)."\r\n\r\n",FILE_APPEND);
			if (isset($res['code'])){
				$data['ret_code'] = $res['code'];
				$data['ret_msg'] = $res['msg'];
			}else{
				$data['ret_code'] = 'AAAA';
				$log_msg = '调用分红方式变更接口失败';
			}
		}else{
			$data['ret_code'] = 'BBBB';
			$log_msg = '一次性随机验证码未找到';
		}
		if (!isset($data['ret_msg'])){
			$data['ret_msg'] = '分红方式变更失败，请稍候重试';
		}
		if (isset($log_msg)){
			file_put_contents('log/trade/redeem'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n用户:".$_SESSION ['customer_name']."进行分红方式变更交易失败，原因为:".$log_msg."\r\n\r\n",FILE_APPEND);
		}
		$data['head_title'] = '分红方式变更结果';
		$data['back_url'] = '/jijin/Jz_my';
		$data['base'] = $this->base;
		$this->load->view('ui/view_operate_result',$data);
		
/* 	$data['head_title'] = '修改分红结果';
		$data['back_url'] = '/jijin/Jz_my';
		
		if (empty($res)) {
			$data['ret_code'] = 'AAAA';
			$data['ret_msg'] = '操作失败';
		} else if (empty($res['code'])){
			$data['ret_code'] = 'BBBB';
			$data['ret_msg'] = '操作失败';
		} else {
			if ($res['code'] == '0000') {
				$data['ret_code'] = '0000';
				$data['ret_msg'] = '操作成功';
			} else {
				$data['ret_code'] = $res['code'];
				$data['ret_msg'] = '操作失败';
			}
		}
		
		$data['base'] = $this->base;
		$this->load->view('ui/view_operate_result',$data); */
	}
	
}