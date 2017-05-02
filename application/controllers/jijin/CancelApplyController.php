<?php
/**
 * 撤单-控制类
 */

if (!defined('BASEPATH')){
	exit('No direct script access allowed');
}

class CancelApplyController extends MY_Controller {
	private $logfile_suffix;
	function __construct()
	{
		parent::__construct();
		$this->load->library(array('Jz_interface','Logincontroller'));
		$this->load->database();
		$this->logfile_suffix = '('.date('Y-m',time()).').txt';
	}
	
	//撤单
	function cancel() {
		if (!$this->logincontroller->isLogin()) {
			exit;
		}
		$get = $this->input->get();
// var_dump($get);
		$data =json_decode(base64_decode($get['json']),true);
// 		$data['fundcode'] = $data['fundid'];
// 		unset($data['fundid']);
		$data['base'] = $this->base;
// var_dump($data);
// exit;
		$data['public_key'] = file_get_contents($this->config->item('RSA_publickey')); //获取RSA_加密公钥
		$data['rand_code'] = "\t".mt_rand(100000,999999);                              //随机生成验证码
		$_SESSION['rand_code'] = $data['rand_code'];
		$this->load->view('jijin/trade/cancel_apply',$data);
	}
	
	//撤销结果
	function CancelResult() {
		if (!$this->logincontroller->isLogin()) {
			exit;
		}
		$data = $this->input->post();
// var_dump($data);
		$res = $this->jz_interface->revoke($_SESSION['JZ_account'], $data['appsheetserialno'], $transactorname='system', $transactorcerttype = 0, $transactorcertno='431003198702212590');
// $res['code'] = '1111';
		file_put_contents('log/trade/cancelApply'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n用户:".$_SESSION ['customer_name']."进行撤单操作，交易数据为:".$data['appsheetserialno']."\r\n返回数据:".serialize($res)."\r\n\r\n",FILE_APPEND);
		if (isset($res['code'])){
			if ($res['code'] == '0000' && isset($res['data'][0]['appsheetserialno'])){
				$insert_data = array('XN_account' => $_SESSION ['customer_name'],
						'JZ_account' => $_SESSION['JZ_account'],
						'appsheetserialno' => $res['data'][0]['appsheetserialno'],
						'relevantserialno' => $data['appsheetserialno'],
						'fundcode' => $data['fundcode'],
						'buy_type' => '撤单',
						'sum' => $data['applicationamount'],
						'status' => 0,
				);
				//写数据库
				$db_res = $this->db->insert('jz_fund_trade',$insert_data);     //写入数据库
				$str =  ":\r\n用户:".$_SESSION ['customer_name']."进行撤单操作成功。\r\n写入数据库数据为：".serialize($insert_data);
				if ($db_res){
					$str .= ' 写入成功';
				}else{
					$str .= ' 写入失败,失败原因：'.serialize($this->db->error());
				}
				file_put_contents('log/trade/cancelApply'.$this->logfile_suffix, date('Y-m-d H:i:s',time()).$str."\r\n\r\n",FILE_APPEND);
				$data['ret_code'] = '0000';
				$data['ret_msg'] = '撤单成功';
			}
		}else{
			$data['ret_code'] = 'AAAA';
			$log_msg = '调用撤单接口失败';
		}
		if (!isset($data['ret_msg'])){
			$data['ret_msg'] = '撤单失败，请稍候重试';
		}
		if (isset($log_msg)){
			file_put_contents('log/trade/cancelApply'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n用户:".$_SESSION ['customer_name']."进行撤单交易失败，失败原因为:".$log_msg."\r\n\r\n",FILE_APPEND);
		}
		$data['head_title'] = '撤单结果';
		$data['back_url'] = '/jijin/Jz_fund';
		$data['base'] = $this->base;
		$this->load->view('ui/view_operate_result',$data);
	}
	
}