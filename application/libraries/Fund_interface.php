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
	
	/* private  */function getReturnData($inputData){
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
// var_dump($AESKey,$this->CI->config->item('fundUrl').'/jijin/XCFinterface/renewCryptKey');
		if (!empty($AESKey)){
			$res = comm_curl($this->CI->config->item('fundUrl').'/jijin/XCFinterface/renewCryptKey',$AESKey);
// var_dump($res,strstr($res,'SUCESS'));
			if (strstr($res,'SUCESS')){
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

	function fund_list(){
		$startTime = strtotime(date('Y-m-d',time()).' 09:00:00');						//从9:00到10:00每隔5分钟自动更新基金列表
		$endTime = strtotime(date('Y-m-d',time()).' 10:00:00');
		$currentTime = time();
		$this->CI->load->model("Model_db");
		$updatetime = $this->CI->db->where(array('dealitem' => 'fundlist'))->get('dealitems')->row_array()['updatetime'];
		if ($updatetime<$startTime || ($updatetime<$endTime && ($currentTime-$updatetime)>1800)){
			$submitData = $this->getSubmitData(array("code"=>'fundlist'));
			$returnData = comm_curl($this->CI->config->item('fundUrl').'/jijin/XCFinterface',$submitData);
			$funddata = $this->getReturnData($returnData)['data']['fundList'];
			if (is_array($funddata) && !empty($funddata)){
				$flag = $this->CI->Model_db->incremenUpdate('fundlist', $funddata, 'fundcode');
				if ($flag){
					$this->CI->db->set(array('updatetime' => time()))->where(array('dealitem' => 'fundlist'))->update('dealitems');
				}
			}
		}
	}
	
	function channel(){
		$currentTime = time();
		$this->CI->load->model("Model_db");
		$updatetime = $this->CI->db->where(array('dealitem' => 'channelInfo'))->get('dealitems')->row_array()['updatetime'];
		if ($updatetime === null){
			$updatetime = 0;
			$this->CI->db->set(array('dealitem' => 'channelInfo','updatetime' => time()))->insert('dealitems');
		}
		if ($currentTime - $updatetime > 86400){
			$logfile_suffix = '('.date('Y-m',time()).').txt';
			$submitData = $this->getSubmitData(array('code'=>'channel'));
			$channel = $this->getReturnData(comm_curl($this->CI->config->item('fundUrl').'/jijin/XCFinterface',$submitData));
			if ($channel['code'] == '0000'){
				$updateData = &$channel['data'];
			}else{
				file_put_contents('log/trade/channel'.$logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n调用channel接口失败,返回数据为".serialize($channel)."\r\n\r\n",FILE_APPEND);
			}
			if (!empty($updateData)){
				$this->CI->load->model("Model_db");
				$flag = $this->CI->Model_db->incremenUpdate('p2_channelinfo', $updateData, 'channelid');
				if ($flag){
					$this->CI->db->set(array('updatetime' => time()))->where(array('dealitem' => 'channelInfo'))->update('dealitems');
				}
			}
		}
		$channelInfo = $this->CI->db->get('p2_channelinfo')->result_array();
		return $channelInfo;
	}
	
	function provCity(){
		$currentTime = time();
		$this->CI->load->model("Model_db");
		$updatetime = $this->CI->db->where(array('dealitem' => 'provCity'))->get('dealitems')->row_array()['updatetime'];
		if ($updatetime === null){
			$updatetime = 0;
			$this->CI->db->set(array('dealitem' => 'provCity','updatetime' => time()))->insert('dealitems');
		}
		if ($currentTime - $updatetime > 86400){
			$logfile_suffix = '('.date('Y-m',time()).').txt';
			$provCity['code'] = 'provCity';
			$provCity['customerNo'] = $_SESSION['customer_name'];
			$submitData = $this->getSubmitData($provCity);
			$province = $this->getReturnData(comm_curl($this->CI->config->item('fundUrl').'/jijin/XCFinterface',$submitData));
			if ($province['code'] == '0000'){
				foreach ($province['data'] as $key => $val){
					$provCity['province'] = $val;
					$submitData = $this->getSubmitData($provCity);
					$city = $this->getReturnData(comm_curl($this->CI->config->item('fundUrl').'/jijin/XCFinterface',$submitData));
					if ($city['code'] = '0000'){
						foreach ($city['data'] as $v){
							$updateData[] = array('province'=>$val,'city'=>$v);
						}
					}else{
						file_put_contents('log/trade/provCity'.$logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n调用provCity(参数".$val.")接口失败,返回数据为".serialize($city)."\r\n\r\n",FILE_APPEND);
					}
				}
			}else{
				file_put_contents('log/trade/provCity'.$logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n调用provCity接口失败,返回数据为".serialize($province)."\r\n\r\n",FILE_APPEND);
			}
			if (!empty($updateData)){
				$this->CI->load->model("Model_db");
				$flag = $this->CI->Model_db->incremenUpdate('p2_prov_city', $updateData, array('province','city'));
				if ($flag){
					$this->CI->db->set(array('updatetime' => time()))->where(array('dealitem' => 'provCity'))->update('dealitems');
				}
			}
		}
		$citys = $this->CI->db->get('p2_prov_city')->result_array();
		foreach ($citys as $val){
			$returnData[$val['province']][] = $val['city'];
		}
		return ($returnData);
	}
	
	function Trans_applied($startDate, $endDate){
		// var_dump($_SESSION['customer_name']);
		if (isset($_SESSION['customer_name'])){
			$submitData = $this->getSubmitData(array('customerNo'=>$_SESSION['customer_name'],"code"=>'transQuery','startDate'=>$startDate,'endDate'=>$endDate));
			$returnData = comm_curl($this->CI->config->item('fundUrl').'/jijin/XCFinterface',$submitData);
			$returnData = $this->getReturnData($returnData);
			if ($returnData['code'] == '0001'){
				$returnData['msg'] = '您尚未开通基金账户,不能进行相关查询';
			}
			return ($returnData);
		}
	}
	
	function bankAccount(){
		$submitData = $this->getSubmitData(array('customerNo'=>$_SESSION['customer_name'],"code"=>'bankAccount'));
		$returnData = comm_curl($this->CI->config->item('fundUrl').'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
	}
	
	function bankCardPhone(){
		$submitData = $this->getSubmitData(array('customerNo'=>$_SESSION['customer_name'],"code"=>'bankCardPhone'));
		$returnData = comm_curl($this->CI->config->item('fundUrl').'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
	}
	
	function beforePurchase(&$purchaseData){
		$submitData = $this->getSubmitData(array('customerNo'=>$_SESSION['customer_name'],"code"=>'beforePurchase',
				'fundcode'=>$purchaseData['fundcode'],'shareclasses'=>$purchaseData['shareclasses'],'tano'=>$purchaseData['tano']));
		$returnData = comm_curl($this->CI->config->item('fundUrl').'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
	}
	
	function risk_test_query(){
		$submitData = $this->getSubmitData(array('customerNo'=>$_SESSION['customer_name'],"code"=>'riskQuery'));
		$returnData = comm_curl($this->CI->config->item('fundUrl').'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
	}
	
	function risk_test_result($answerList,$pointList){
		$submitData = $this->getSubmitData(array('customerNo'=>$_SESSION['customer_name'],'answerList'=>$answerList,'pointList'=>$pointList,"code"=>'riskResult'));
		$returnData = comm_curl($this->CI->config->item('fundUrl').'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
	}
	
	function purchase($purchaseData){
		$purchaseData['customerNo'] = $_SESSION['customer_name'];
		$purchaseData['code'] = 'purchase';
		$submitData = $this->getSubmitData($purchaseData);
		$returnData = comm_curl($this->CI->config->item('fundUrl').'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
	}
	
	function asset(){
		$submitData = $this->getSubmitData(array('customerNo'=>$_SESSION['customer_name'],"code"=>'myAsset'));
		$returnData = comm_curl($this->CI->config->item('fundUrl').'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
	}
	
	function AccountInfo(){
		$submitData = $this->getSubmitData(array('customerNo'=>$_SESSION['customer_name'],"code"=>'accountInfo'));
		$returnData = comm_curl($this->CI->config->item('fundUrl').'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
	}
	
	function redemption(&$redempData){
		$redempData['customerNo'] = $_SESSION['customer_name'];
		$redempData['code'] = 'redemption';
		$submitData = $this->getSubmitData($redempData);
		$returnData = comm_curl($this->CI->config->item('fundUrl').'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
	}
	
	function bonus_changeable(){
		$submitData = $this->getSubmitData(array('customerNo'=>$_SESSION['customer_name'],"code"=>'bonusChangeable'));
		$returnData = comm_curl($this->CI->config->item('fundUrl').'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
		
	}
	
	function bonus_mode(&$bonusData){
		$bonusData['customerNo'] = $_SESSION['customer_name'];
		$bonusData['code'] = 'bonusMode';
		$submitData = $this->getSubmitData($bonusData);
		$returnData = comm_curl($this->CI->config->item('fundUrl').'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
	}
	
	function paymentChannel(){
		$submitData = $this->getSubmitData(array("code"=>'paymentChannel'));
		$returnData = comm_curl($this->CI->config->item('fundUrl').'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
	}
	
	function revisePassward($oldpwd, $newpwd, $pwdtype){
		$submitData = $this->getSubmitData(array('customerNo'=>$_SESSION['customer_name'],"code"=>'RevisePassward','oldpwd'=>$oldpwd,'newpwd'=>$newpwd,'pwdtype'=>$pwdtype));
		$returnData = comm_curl($this->CI->config->item('fundUrl').'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
	}
	
	function SeekAccount($certificatetype,$certificateno){
		$submitData = $this->getSubmitData(array('customerNo'=>$_SESSION['customer_name'],"code"=>'seekAccount','certificatetype'=>$certificatetype,'certificateno'=>$certificateno));
		$returnData = comm_curl($this->CI->config->item('fundUrl').'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
	}
	
	function bgMsgSend(&$bgMsgSend){
		$bgMsgSend['code'] = 'bgMsgSend';
		$bgMsgSend['customerNo'] = $_SESSION['customer_name'];
		$submitData = $this->getSubmitData($bgMsgSend);
		$returnData = comm_curl($this->CI->config->item('fundUrl').'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
	}
	
	function bgMsgCheck(&$bgMsgCheck){
		$bgMsgCheck['code'] = 'bgMsgCheck';
		$bgMsgCheck['customerNo'] = $_SESSION['customer_name'];
		$submitData = $this->getSubmitData($bgMsgCheck);
		$returnData = comm_curl($this->CI->config->item('fundUrl').'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
	}
	
	function openPhoneTrans(&$openPhoneTrans){
		$openPhoneTrans['code'] = 'openPhoneTrans';
		$openPhoneTrans['customerNo'] = $_SESSION['customer_name'];
		$submitData = $this->getSubmitData($openPhoneTrans);
		$returnData = comm_curl($this->CI->config->item('fundUrl').'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
	}
	
	function openBank($channelid,$PARM){
		$openBank['code'] = 'openBank';
		$openBank['channelid'] = $channelid;
		$openBank['PARM'] = $PARM;
		$submitData = $this->getSubmitData($openBank);
		$returnData = comm_curl($this->CI->config->item('fundUrl').'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
	}
	
}