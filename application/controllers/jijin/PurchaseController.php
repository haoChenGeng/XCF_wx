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
		$this->load->library(array('Fund_interface','Logincontroller'));
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
		$purchase_info =$this->fund_interface->beforePurchase($data);
		file_put_contents('log/trade/apply_fund'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n用户".$_SESSION ['customer_name']."访问公募基金接口，返回数据为:".serialize($purchase_info)."\r\n\r\n",FILE_APPEND);
// var_dump($purchase_info,$data);
// 		file_put_contents('log/trade/apply_fund'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n查询用户".$_SESSION ['customer_name']."银行卡返回数据:".serialize($bank_info)."\r\n\r\n",FILE_APPEND);
		if (key_exists('code',$purchase_info)){
			if ($purchase_info['code'] == '0000' ){
				if (empty($purchase_info['data']['custrisk'])){
					$error_code =1;
					$errMsg = '尚未进行风险等级测试';
				}else{
					//生成用户信息
					$data['bank_info'] = $purchase_info['data']['bank_info'];
					$data['mobileno'] = $purchase_info['data']['mobileno'];
					//生成基金购买信息
					if (key_exists('isfirstbuy',$purchase_info['data']) && $purchase_info['data']['isfirstbuy'] == 0){
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
					unset($json['fundname']);
					unset($json['sharetypename']);
					$data['json'] = base64_encode(json_encode($json));
					//生成用户银行卡信息
					foreach ($purchase_info['data']['bank_info'] as $key => $val){
						if (!empty($val)){
							$data['bank_msg'][$key] = $this->config->item('channelid')[$val['channelid']].' 卡号:'.$val['depositacct'];
						}
					}
					if ($purchase_info['data']['custrisk'] >= intval($data['risklevel'])){
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
						$errMsg = '风险等级和产品不匹配';
					}
				}
			}else{
				$error_code = $purchase_info['code'];
				$errMsg = $purchase_info['msg'];
			}
		}else{
			$error_code = 'AAAA';
		}
		if (isset($error_code)){
			$arr['base'] = $this->base;
			$arr['back_url'] = '/jijin/Jz_fund';
			$arr['ret_msg'] = isset($errMsg) ? $errMsg :'系统故障，请稍候重试';
			switch ($error_code){
				case 0:
					$arr['data'] = base64_encode(json_encode($data));
					$arr['forward_url'] = '/jijin/PurchaseController/load_apply_fund';
					$arr['forward_msg'] = '继续够买';
					$arr['head_title'] = '购买提醒';
					$this->load->view('ui/operate_result2',$arr);
					break;
				case 1:
					$arr['forward_url'] = '/jijin/Risk_assessment';
					$arr['forward_msg'] = '进行风险等级测试';
					$arr['head_title'] = '购买提醒';
					$_SESSION['url_afteroperation'] = '/jijin/Jz_fund';
					$this->load->view('ui/operate_result2',$arr);
					break;
				case 3:
					$arr['forward_url'] = '/jijin/Fund_bank/operation/bankcard_add';
					$arr['forward_msg'] = '赠加银行卡';
					$arr['head_title'] = '购买提醒';
					$_SESSION['url_afteroperation'] = '/jijin/Jz_fund';
					$this->load->view('ui/operate_result2',$arr);
				break;
				default:
					$arr['ret_code'] ='AAAA';
					$arr['head_title'] = $data['purchasetype'].'结果';
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
			$purchaseData = json_decode(base64_decode($post['json']),true);
// var_dump($purchaseData,$post);
			//调用申购、认购接口
			foreach ($purchaseData['bank_info'][$post['pay_way']] as $key=>$val){
				$purchaseData[$key] = $val;
			}
			$purchaseData['tpasswd'] = $tpasswd;
			$purchaseData['applicationamt'] = $post['sum'];
			unset($purchaseData['bank_info']);
			$purchase = $this->fund_interface->purchase($purchaseData);
// var_dump($purchase);
			file_put_contents('log/trade/apply_fund'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n用户".$_SESSION ['customer_name']."进行".$post['purchasetype']."基金(purchase<520003>)操作\r\n申请数据为：".serialize($purchaseData)."\r\n返回数据:".serialize($purchase)."\r\n\r\n",FILE_APPEND);
			if (key_exists('code',$purchase)){
				$arr['ret_code'] = $purchase['code'];
				$log_msg = '调用'.$post['purchasetype'].'接口失败';
				if ($purchase['code'] == '0016' || $purchase['code'] == '0017' || $purchase['code'] == '0018') {
					$log_msg = $arr['ret_msg'] = $purchase['msg'];
				}
			}else{
				$log_msg = '调用'.$post['purchasetype'].'接口失败';
				$arr['ret_code'] = $purchase['code'];
			}		
		}else{
			$log_msg = '一次性随机验证码未找到';
			$arr['ret_code'] = 'SJME';
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