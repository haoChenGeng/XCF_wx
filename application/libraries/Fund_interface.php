<?php

if (!defined('BASEPATH')){
	exit('No direct script access allowed');
}

if (!class_exists('Math_BigInteger')){
	include 'data/encrpty/BigInteger.php';
}
if (!class_exists('Crypt_Hash')){
	include 'data/encrpty/Hash.php';
}
if (!class_exists('Crypt_Rijndael')){
	include 'data/encrpty/Rijndael.php';
}
if (!class_exists('Crypt_AES')){
	include 'data/encrpty/AES.php';
}

class Fund_interface
{
	protected $CI;
	private $fundUrl;
	private $AESKey;
	function __construct()
	{
		$this->CI =& get_instance();
		$this->fundUrl = $this->CI->config->item('fundUrl');
		$this->CI->load->database();
		$this->CI->load->helper("comfunction");
		$this->AESKey = $this->CI->db->select('AESkey')->where(array('platformName'=>'Fund'))->get('communctionkey')->row_array()['AESkey'];
	}
	
	private function getCommunctionKey(){
		return $this->CI->db->where(array('platformName'=>'Fund'))->get('communctionkey')->row_array()['AESkey'];
	}
	
	private function getSubmitData($inputData){
		$AES = new Crypt_AES(CRYPT_AES_MODE_ECB);
		$AES->setKey($this->AESKey);
		$submitData = base64_encode($AES->encrypt(json_encode($inputData)));
		return array('data'=>$submitData);
	}
	
	private function getReturnData($inputData){
		$AES = new Crypt_AES(CRYPT_AES_MODE_ECB);
		$AES->setKey($this->AESKey);
		return json_decode($AES->decrypt(base64_decode($inputData)),true);
	}
	
	function RenewFundAESKey($newKey) {
		$public_key = $this->CI->config->item('fund_RSA_privatekey'); //获取RSA_加密公钥
		if (!class_exists('Math_BigInteger')){
			include 'data/encrpty/BigInteger.php';
		}
		if (!class_exists('Crypt_Hash')){
			include 'data/encrpty/Hash.php';
		}
		if (!class_exists('Crypt_RSA')){
			include 'data/encrpty/RSA.php';
		}
		// 加密类
		$RSA = new Crypt_RSA();
		$RSA->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
		$RSA->loadKey($public_key);
		$AESKey = array('encryptkey'=>base64_encode($RSA->encrypt($newKey)));
		if (!empty($AESKey)){
			$res = comm_curl($this->CI->config->item('fundUrl').'/jijin/XCFinterface/renewCryptKey',$AESKey);
			var_dump($res);
			if ($res == 'SUCESS'){
				$XCFkey = $this->CI->db->where(array('platformName'=>'Fund'))->get('communctionkey')->row_array();
				if(empty($XCFkey)){
					$flag = $this->CI->db->set(array('platformName'=>'Fund','AESkey'=>$newKey))->insert('communctionkey');
				}else{
					$flag = $this->CI->db->set(array('AESkey'=>$newKey))->where(array('platformName'=>'Fund'))->update('communctionkey');
				}
				if ($flag){
					return '重设基金后台通信秘钥成功';
				}
			}
			return '重设基金后台通信秘钥失败，请重试';
		}else{
			return '基金后台通信秘钥不能为空，请重试';
		}
	}

	function Trans_applied($startDate, $endDate){
// var_dump($_SESSION['customer_name']);
		$communctionData = $this->getSubmitData(array('customerNo'=>$_SESSION['customer_name'],"code"=>'transQuery','startDate'=>$startDate,'endDate'=>$endDate));
		$returnData = comm_curl($this->CI->config->item('fundUrl').'/jijin/XCFinterface',$communctionData);
		return ($this->getReturnData($returnData));
	}
	
	function fund_list(){
		$startTime = strtotime(date('Y-m-d',time()).' 09:00:00');						//从9:00到10:00每隔5分钟自动更新基金列表
		$endTime = strtotime(date('Y-m-d',time()).' 10:00:00');
		$currentTime = time();
		$this->CI->load->model("Model_db");
		$updatetime = $this->CI->db->where(array('dealitem' => 'fundlist'))->get('dealitems')->row_array()['updatetime'];
		if ($updatetime<$startTime || ($updatetime<$endTime && ($currentTime-$updatetime)>1800)){
			$communctionData = $this->getSubmitData(array("code"=>'fundlist'));
			$returnData = comm_curl($this->CI->config->item('fundUrl').'/jijin/XCFinterface',$communctionData);
			$funddata = $this->getReturnData($returnData)['data']['fundList'];
			if (is_array($funddata) && !empty($funddata)){
				$flag = $this->CI->Model_db->incremenUpdate('fundlist', $funddata, 'fundcode');
				if ($flag){
					$this->CI->db->set(array('updateTime' => time()))->where(array('dealitem' => 'fundlist'))->update('dealitems');
				}
			}
		}
	}
	
}