<?php
// 申购 认购-控制类
 
if (!defined('BASEPATH')){
	exit('No direct script access allowed');
}

class PurchaseController extends MY_Controller {
    private $logfile_suffix;
	function __construct()
	{
		parent::__construct();
		$this->load->library(array('Jz_interface','Logincontroller'));
		$this->load->helper(array("url"));
		$this->logfile_suffix = '('.date('Y-m',time()).').txt';
	}
	
	//申购 认购前准备
	function Apply() {
		if (!$this->logincontroller->isLogin()) {
			redirect($this->base . "/jijin/Jz_account/register");
			exit;
		}
		$get = $this->input->get();
		$data = json_decode(base64_decode($get['json']),true);
		$data['purchasetype'] = $get['purchasetype'];
		$bank_info =$this->jz_interface->bankcard_phone($_SESSION['JZ_account'], 1);
		file_put_contents('log/trade/apply_fund'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n查询用户".$_SESSION ['customer_name']."银行卡返回数据:".serialize($bank_info)."\r\n\r\n",FILE_APPEND);
		if (isset($bank_info['code']) && $bank_info['code'] == '0000' )
		{
			$data['bank_info'] = array();
			$this->load->config('jz_dict');
			foreach ($bank_info['data'] as $key => $val){
				$i =0;
				if (!empty($val)){
					if ($val['status'] == 0 && $val['isopenmobiletrade'] == 1){
						$data['bank_msg'][$i] = $this->config->item('channelid')[$val['channelid']].' 卡号:'.$val['depositacct'];
						unset($val['custno']);
						unset($val['status']);
						unset($val['isopenmobiletrade']);
						unset($val['depositacct']);
						//是否要给出银行卡支付渠道的信息？
						$data['bank_info'][$i] = $val;
						$i++;
					}
				}
			}
			if (!empty($data['bank_info'])) {
				$user_info = $this->jz_interface->account_info($_SESSION['JZ_account']);
				file_put_contents('log/trade/apply_fund'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n查询用户".$_SESSION ['customer_name']."风险等级信息(account_info<520101>)返回数据:".serialize($user_info)."\r\n\r\n",FILE_APPEND);
				if ($user_info['code'] == '0000' || !isset($user_info['data'][0]['certificateno'])){
					$data['custrisk'] = intval($user_info['data'][0]['custrisk']);
					$data['transactionaccountid'] = $user_info['data'][0]['transactionaccountid'];
					$data['mobileno'] = $user_info['data'][0]['mobileno'];
					$this->load->config('jz_dict');
					if (isset($this->config->item('custrisk')[$data['custrisk']])){
						//查询首次购买标志
						$FP = $this->jz_interface->first_purchase($data['fundcode'], $data['shareclasses'], $data['tano'], $_SESSION['JZ_account']);
						file_put_contents('log/trade/apply_fund'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n查询用户".$_SESSION ['customer_name']."首次购买信息(first_purchase<430406>)查询数据:fundcode=".$data['fundcode'].'shareclasses='.$data['shareclasses']."tano=".$data['tano']."\r\n返回数据:".serialize($FP)."\r\n\r\n",FILE_APPEND);
						if (isset($FP['code']) && $FP['code'] = '0000' && $FP['data'][0]['isfirstbuy'] == 0){
							$data['min_money'] = $data['con_per_min'];
							$data['max_money'] = $data['con_per_max'];
						}else{
							$data['min_money'] = $data['first_per_min'];
							$data['max_money'] = $data['first_per_max'];
						}
						unset($data['first_per_min']);
						unset($data['first_per_max']);
						unset($data['con_per_min']);
						unset($data['con_per_max']);
						unset($data['nav']);
						unset($data['fundtypename']);
						$json = $data;
						unset($json['min_money']);
						unset($json['max_money']);
						unset($json['risklevel']);
						unset($json['custrisk']);
						unset($json['bank_msg']);
						unset($json['fundname']);
						unset($json['sharetypename']);
						$data['json'] = base64_encode(json_encode($json));
						if ($data['custrisk'] >= intval($data['risklevel'])){
							$data['base'] = $this->base;
							$data['public_key'] = file_get_contents($this->config->item('RSA_publickey')); //获取RSA_加密公钥
							$data['rand_code'] = "\t".mt_rand(100000,999999);                              //随机生成验证码
							$_SESSION['rand_code'] = $data['rand_code'];
							ob_start();
							$this->load->view('jijin/trade/view_apply_fund',$data);
							ob_end_flush();
							exit();
						}else{
							$error_code = 0;                              
							$log_msg = '风险等级和产品不匹配';
						}
					}else{
						$error_code =1;
						$log_msg = '尚未进行风险等级测试';
					}
				}else{
						$error_code =2;
						$log_msg = '查询用户风险等级信息失败';
				}
			}else{
				$error_code = 3;
				$log_msg = '没有可交易的银行卡，请先增加银行卡';
			}
		}else{
			$error_code = 5;
			$log_msg = '查询银行卡信息失败';
		}
		file_put_contents('log/trade/apply_fund'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n用户".$_SESSION ['customer_name']."购买基金失败，失败原因为:".$log_msg."\r\n\r\n",FILE_APPEND);
		if (isset($error_code)){
			switch ($error_code){
				case 0:
					$arr['data'] = base64_encode(json_encode($data));
					$arr['forward_url'] = '/jijin/PurchaseController/load_apply_fund';
					$arr['forward_msg'] = '继续够买';
					$arr['back_url'] = '/jijin/Jz_fund';
					$arr['base'] = $this->base;
					$arr['head_title'] = '购买提醒';
					$arr['ret_msg'] = $log_msg;
					$this->load->view('ui/operate_result2',$arr);
					break;
				case 1:
					$arr['forward_url'] = '/jijin/Risk_assessment';
					$arr['forward_msg'] = '进行风险等级测试';
					$arr['back_url'] = '/jijin/Jz_fund';
					$arr['base'] = $this->base;
					$arr['head_title'] = '购买提醒';
					$arr['ret_msg'] = $log_msg;
					$_SESSION['url_afteroperation'] = '/jijin/Jz_fund';
					$this->load->view('ui/operate_result2',$arr);
					break;
				case 3:
					$arr['forward_url'] = '/jijin/Fund_bank/operation/bankcard_add';
					$arr['forward_msg'] = '赠加银行卡';
					$arr['back_url'] = '/jijin/Jz_fund';
					$arr['base'] = $this->base;
					$arr['head_title'] = '购买提醒';
					$arr['ret_msg'] = $log_msg;
					$_SESSION['url_afteroperation'] = '/jijin/Jz_fund';
					$this->load->view('ui/operate_result2',$arr);
				break;
				default:
					$arr['ret_code'] ='AAAA';
					$arr['ret_msg'] = '系统故障，请稍候重试';
					$arr['head_title'] = $data['purchasetype'].'结果';
					$arr['back_url'] = '/jijin/Jz_fund';
					$arr['base'] = $this->base;
					$this->load->view('ui/view_operate_result',$arr);
			}
		}

	}
	
	function load_apply_fund(){
		if (!$this->logincontroller->isLogin()) {
			exit;
		}

		$post = $this->input->post();
		$data = json_decode(base64_decode($post['data']),true);
		$data['base'] = $this->base;
		$data['public_key'] = file_get_contents($this->config->item('RSA_publickey')); //获取RSA_加密公钥
		$data['rand_code'] = "\t".mt_rand(100000,999999);
		$_SESSION['rand_code'] = $data['rand_code'];
		$this->load->view('jijin/trade/view_apply_fund',$data);
	}
	
	//提交申购、认购申请并支付
	function ApplyResult() {
		if (!$this->logincontroller->isLogin()) {
			exit;
		}
		$post = $this->input->post();
		$private_key = openssl_get_privatekey(file_get_contents($this->config->item('RSA_privatekey')));
		$decryptData = '';
		openssl_private_decrypt(base64_decode($post['tpasswd']),$decryptData, $private_key, OPENSSL_PKCS1_PADDING);
		//判断一次性随机验证码是否存在
		$div_bit = strpos($decryptData,(string)$_SESSION['rand_code']);
		unset($_SESSION['rand_code']);
		if ($div_bit !== false){                           //找到一次性随机验证码
			$tpasswd = substr($decryptData, 0, $div_bit);
			$data = json_decode(base64_decode($post['json']),true);
			$channelid = $data['bank_info'][$post['pay_way']]['channelid'];
			$moneyaccount = $data['bank_info'][$post['pay_way']]['moneyaccount'];
			$branchcode = $data['bank_info'][$post['pay_way']]['paycenterid'];
			$log_str = 'transactionaccountid:'.$data['transactionaccountid'] . ' branchcode:'.$branchcode . ' tano:'.$data['tano'] . ' fundcode:'.$data['fundcode']. ' sharetype:'.$data['shareclasses']. ' applicationamt:'.$post['sum']. ' moneyaccount:'.$moneyaccount. ' channelid:'.$channelid. ' buyflag:1';
			//调用申购、认购接口
			$purchase = $this->jz_interface->purchase($_SESSION['JZ_account'], $data['transactionaccountid'],$branchcode, $data['tano'], $data['fundcode'], $data['shareclasses'], $post['sum'], $moneyaccount, $channelid, 1, $tpasswd);
			file_put_contents('log/trade/apply_fund'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n用户".$_SESSION ['customer_name']."进行".$post['purchasetype']."基金(purchase<520003>)操作\r\n申请数据为：".$log_str."\r\n返回数据:".serialize($purchase)."\r\n\r\n",FILE_APPEND);
			if (isset($purchase['code'])){
				if( $purchase['code'] == '0000' && isset($purchase['data'][0]['appsheetserialno'])){
					$appsheetserialno = $purchase['data'][0]['appsheetserialno'];
					$liqdate = $purchase['data'][0]['appsheetserialno'];
					$paySend = $this->jz_interface->paySend($_SESSION['JZ_account'], $appsheetserialno, $moneyaccount, $data['fundcode'], $data['fundtype'], $liqdate, $data['mobileno']);
					$log_str = 'appsheetserialno:'.$appsheetserialno.' moneyaccount:'.$moneyaccount.' fundcode:'.$data['fundcode'].' fundtype:'.$data['fundtype'].' liqdate:'.$liqdate.' mobileno:'.$data['mobileno'];
					file_put_contents('log/trade/apply_fund'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n用户".$_SESSION ['customer_name']."进行支付操作(paySend)操作\r\n申请数据为：".$log_str."\r\n返回数据:".serialize($paySend)."\r\n\r\n",FILE_APPEND);
					if (isset($paySend['code']) && $paySend['code'] == '0000' && isset($paySend['data']['0']['serialno']))
					{
						$arr['ret_msg'] = '基金'.$post['purchasetype'].'成功';
						$arr['ret_code'] = $paySend['code'];
						$insert_data = array('XN_account' => $_SESSION ['customer_name'],
								'JZ_account' => $_SESSION['JZ_account'],
								'appsheetserialno' => $appsheetserialno,
								'relevantserialno' => $paySend['data']['0']['serialno'],
								'fundcode' => $data['fundcode'],
								'buy_type' => $post['purchasetype'],
								'sharetype' => $data['shareclasses'],
								'sum' => $post['sum'],
								'status' => 0,
						);
						$db_res = $this->db->insert('jz_fund_trade',$insert_data);     //写入数据库
	 					$str =  ":\r\n用户:".$_SESSION ['customer_name']."进行基金".$post['purchasetype']."操作成功。\r\n写入数据库数据为：".serialize($insert_data);
	 					if ($db_res){
	 						$str .= ' 写入成功';
	 					}else{
	 						$str .= ' 写入失败,失败原因：'.serialize($this->db->error());
	 					}
	 					file_put_contents('log/trade/apply_fund'.$this->logfile_suffix, date('Y-m-d H:i:s',time()).$str."\r\n\r\n",FILE_APPEND);
					}else{
						$log_msg = '调用支付接口失败';
						$arr['ret_code'] = $paySend['code'];
						$arr['ret_msg'] = $post['purchasetype'].'申请已提交，请稍候查询'.$post['purchasetype'].'结果';
					}
				}else{
					$arr['ret_code'] = $purchase['code'];
					$log_msg = '调用'.$post['purchasetype'].'接口失败';
					if ($purchase['code'] == '-409999999' && strpos($purchase['msg'],'密码') !== false){
						$log_msg = $arr['ret_msg'] = '交易密码输入错误，请重试';
					}
					if ($purchase['code'] == '-400301031' && strpos($purchase['msg'],'委托金额不满足递增步长') !== false){
						$log_msg = $arr['ret_msg'] = $purchase['msg'];
					}
				}
			}else{
				$log_msg = '调用'.$post['purchasetype'].'接口失败';
				$arr['ret_code'] = $purchase['code'];
			}		
		}else{
			$log_msg = '一次性随机验证码未找到';
			$arr['ret_code'] = '0000';
		}
		if (isset($log_msg)){
			file_put_contents('log/trade/apply_fund'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n用户".$_SESSION ['customer_name'].$post['purchasetype']."基金操作失败，失败原因为：".$log_msg."\r\n\r\n",FILE_APPEND);
		}
		if (!isset($arr['ret_msg'])){
			$arr['ret_msg'] = '系统错误，基金'.$post['purchasetype'].'失败';
		}
		$arr['head_title'] = $post['purchasetype'].'结果';
		$arr['back_url'] = '/jijin/Jz_fund';
		$arr['base'] = $this->base;
		$this->load->view('ui/view_operate_result',$arr);
	}
	
}