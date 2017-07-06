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
		$this->CI->load->database();
		$fundInterface = $this->CI->db->where(array('name'=>'FundInterface'))->get('p2_interface')->row_array();
		if (!empty($fundInterface)){
			$this->fundUrl = $fundInterface['url'];
			$this->AESKey = $fundInterface['password'];
		}
		$this->CI->load->helper("comfunction");
	}
	
	private function getSubmitData($inputData){
		$AES = new Crypt_AES(CRYPT_AES_MODE_ECB);
		$AES->setKey($this->AESKey);
		$submitData = base64_encode($AES->encrypt(json_encode($inputData)));
		return array('data'=>$submitData);
	}
	
	private function getReturnData($inputData){
		if(!empty($inputData)){
			$AES = new Crypt_AES(CRYPT_AES_MODE_ECB);
			$AES->setKey($this->AESKey);
			return json_decode($AES->decrypt(base64_decode($inputData)),true);
		}else{
			return FALSE;
		}
	}
	
	function RenewFundAESKey($fundUrl,$newKey) {
		$public_key = $this->CI->config->item('fund_RSA_publickey'); //获取RSA_加密公钥
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
// var_dump($AESKey,$fundUrl.'/jijin/XCFinterface/renewCryptKey');
		if (!empty($AESKey)){
			$res = comm_curl($fundUrl.'/jijin/XCFinterface/renewCryptKey',$AESKey);
// var_dump($res,strstr($res,'SUCESS'));
			if (strstr($res,'SUCESS')){
				$XCFkey = $this->CI->db->where(array('name'=>'FundInterface'))->get('interface')->row_array();
				if(empty($XCFkey)){
					$flag = $this->CI->db->set(array('name'=>'FundInterface','password'=>$newKey))->insert('interface');
				}else{
					$flag = $this->CI->db->set(array('password'=>$newKey))->where(array('name'=>'FundInterface'))->update('interface');
				}
				if ($flag){
					return true;
				}
			}
		}
		return false;
	}

	function fund_list(){
		$invalidTime = strtotime(date('Y-m-d',time()))-50400;			//设置基金列表失效时间，即昨天10点之前获得的基金信息必须进行更新。
		$startTime = strtotime(date('Y-m-d',time()).' 09:20:00');		//设置自动更新时间段[$startTime,$endTime](从9:20到10:00)每隔5分钟自动更新基金列表
		$endTime = strtotime(date('Y-m-d',time()).' 10:00:00');
		$currentTime = time();
		$this->CI->load->model("Model_db");
		$updatetime = $this->CI->db->where(array('dealitem' => 'fundlist'))->get('dealitems')->row_array()['updatetime'];
		if ($updatetime<$invalidTime || ($currentTime > $startTime && $updatetime<$endTime && ($currentTime-$updatetime)>300)){
			$submitData = $this->getSubmitData(array("code"=>'fundlist'));
			$returnData = comm_curl($this->fundUrl.'/jijin/XCFinterface',$submitData);
			$funddata = $this->getReturnData($returnData)['data']['fundList'];
			if (is_array($funddata) && !empty($funddata)){
				$preFundInfo = $this->CI->db->select('fundcode,nav,navdate')->get('p2_fundlist')->result_array();
				$preFundInfo = setkey($preFundInfo, 'fundcode');
				$flag = $this->CI->Model_db->incremenUpdate('fundlist', $funddata, 'fundcode');
				if ($flag){
					$this->CI->db->set(array('updatetime' => time()))->where(array('dealitem' => 'fundlist'))->update('dealitems');
				}
				$fundcodes = array_column($funddata, 'fundcode');
				$tables = $this->CI->db->list_tables();
				foreach ($fundcodes as $key=>$val){
					if (in_array('p2_netvalue_'.$val, $tables)){
						unset($fundcodes[$key]);
					}
				}
				if (!empty($fundcodes)){
					foreach ($fundcodes as $val){
						$this->getFundNetvalue($val);
					}
				}
				$currentdate = date('Y-m-d',time());
				foreach ($funddata as $val){
					if ($val['navdate'] != $preFundInfo[$val['fundcode']]['navdate']){
						$val['navdate'] = date('Y-m-d',strtotime($val['navdate']));
						$updateNav = array('net_date' => $val['navdate'],
								'net_unit' => $val['nav'],
								'net_sum' => empty($val['totalnav']) ? 0 : $val['totalnav'],
								'net_day_growth' => ($val['nav']-$preFundInfo[$val['fundcode']]['nav'])/$preFundInfo[$val['fundcode']]['nav'],
								'XGRQ' => $currentdate,
						);
						$this->CI->db->replace('p2_netvalue_'.$val['fundcode'],$updateNav);
					}
				}
			}
		}
	}
	
	function getFundNetvalue($fundcode){
		$submitData = $this->getSubmitData(array("code"=>'fundNetvalue','fund_code'=>$fundcode));
		$returnData = comm_curl($this->fundUrl.'/jijin/XCFinterface',$submitData);
		$fundNetvalue = $this->getReturnData($returnData);
		if ($fundNetvalue['code'] == '0000' && is_array($fundNetvalue['data'])){
			if (!$this->CI->db->table_exists('p2_netvalue_'.$fundcode)){
				$this->creatFundNetValue('p2_netvalue_'.$fundcode);
			}
			$updateData = &$fundNetvalue['data'];
			$currentdate = date('Y-m-d',time());
			foreach ($updateData as $key=>$val){
				if (empty($val['net_date'])){
					unset ($updateData[$key]);
				}else{
					$updateData[$key]['net_day_growth'] = empty($val['net_day_growth']) ? 0 : $val['net_day_growth'];
					$updateData[$key]['XGRQ'] = $currentdate;
				}
			}
			$flag = $this->CI->Model_db->incremenUpdate('p2_netvalue_'.$fundcode, $fundNetvalue['data'], 'net_date');
		}else{
			$flag = FALSE;
		}
		return $flag;
	}
	
	private function creatFundNetValue($tableName){
		$sql = "CREATE TABLE `".$tableName."` (
				`net_date` varchar(24) ,
				`net_unit` varchar(24) DEFAULT '0',
				`net_sum` varchar(24) DEFAULT '0',
				`net_day_growth` varchar(24) NOT NULL DEFAULT '0',
				`XGRQ` datetime DEFAULT NULL COMMENT '更新日期',
				PRIMARY KEY (`net_date`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		$flag = $this->CI->db->query($sql);
		return $flag;
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
			$channel = $this->getReturnData(comm_curl($this->fundUrl.'/jijin/XCFinterface',$submitData));
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

	function paymentChannel(){
		$currentTime = time();
		$this->CI->load->model("Model_db");
		$updatetime = $this->CI->db->where(array('dealitem' => 'paymentChannel'))->get('dealitems')->row_array()['updatetime'];
		if ($updatetime === null){
			$updatetime = 0;
			$this->CI->db->set(array('dealitem' => 'paymentChannel','updatetime' => time()))->insert('dealitems');
		}
		if ($currentTime - $updatetime > 86400){
			$logfile_suffix = '('.date('Y-m',time()).').txt';
			$submitData = $this->getSubmitData(array('code'=>'paymentChannel'));
			$paymentChannel = $this->getReturnData(comm_curl($this->fundUrl.'/jijin/XCFinterface',$submitData));
			if ($paymentChannel['code'] == '0000'){
				$updateData = &$paymentChannel['data'];
			}else{
				file_put_contents('log/trade/paymentChannel'.$logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n调用paymentChannel接口失败,返回数据为".serialize($paymentChannel)."\r\n\r\n",FILE_APPEND);
			}
			if (!empty($updateData)){
				$this->CI->load->model("Model_db");
				$flag = $this->CI->Model_db->incremenUpdate('p2_paymentchannel', $updateData, 'channelid');
				if ($flag){
					$this->CI->db->set(array('updatetime' => time()))->where(array('dealitem' => 'paymentChannel'))->update('dealitems');
				}
			}
		}
		$paymentChannel = $this->CI->db->get('p2_paymentchannel')->result_array();
		return $paymentChannel;
// 		$submitData = $this->getSubmitData(array("code"=>'paymentChannel'));
// 		$returnData = comm_curl($this->fundUrl.'/jijin/XCFinterface',$submitData);
// 		return ($this->getReturnData($returnData));
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
			$province = $this->getReturnData(comm_curl($this->fundUrl.'/jijin/XCFinterface',$submitData));
			if ($province['code'] == '0000'){
				foreach ($province['data'] as $key => $val){
					$provCity['province'] = $val;
					$submitData = $this->getSubmitData($provCity);
					$city = $this->getReturnData(comm_curl($this->fundUrl.'/jijin/XCFinterface',$submitData));
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
			$returnData = comm_curl($this->fundUrl.'/jijin/XCFinterface',$submitData);
			$returnData = $this->getReturnData($returnData);
			if ($returnData['code'] == '0001'){
				$returnData['msg'] = '您尚未开通基金账户,不能进行相关查询';
			}
			return ($returnData);
		}
	}
	
	function bankAccount(){
		$submitData = $this->getSubmitData(array('customerNo'=>$_SESSION['customer_name'],"code"=>'bankAccount'));
		$returnData = comm_curl($this->fundUrl.'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
	}
	
	function bankCardPhone(){
		$submitData = $this->getSubmitData(array('customerNo'=>$_SESSION['customer_name'],"code"=>'bankCardPhone'));
		$returnData = comm_curl($this->fundUrl.'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
	}
	
	function beforePurchase(&$purchaseData){
		$submitData = $this->getSubmitData(array('customerNo'=>$_SESSION['customer_name'],"code"=>'beforePurchase',
				'fundcode'=>$purchaseData['fundcode'],'shareclasses'=>$purchaseData['shareclasses'],'tano'=>$purchaseData['tano']));
		$returnData = comm_curl($this->fundUrl.'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
	}
	
	function risk_test_query(){
		$submitData = $this->getSubmitData(array('customerNo'=>$_SESSION['customer_name'],"code"=>'riskQuery'));
		$returnData = comm_curl($this->fundUrl.'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
	}
	
	function risk_test_result($answerList,$pointList){
		$submitData = $this->getSubmitData(array('customerNo'=>$_SESSION['customer_name'],'answerList'=>$answerList,'pointList'=>$pointList,"code"=>'riskResult'));
		$returnData = comm_curl($this->fundUrl.'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
	}
	
	function purchase($purchaseData){
		$purchaseData['customerNo'] = $_SESSION['customer_name'];
		$purchaseData['code'] = 'purchase';
		$submitData = $this->getSubmitData($purchaseData);
// return $submitData;
		$returnData = comm_curl($this->fundUrl.'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
	}
	
	function asset(){
		$submitData = $this->getSubmitData(array('customerNo'=>$_SESSION['customer_name'],"code"=>'myAsset'));
		$returnData = comm_curl($this->fundUrl.'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
	}
	
	function AccountInfo(){
		$submitData = $this->getSubmitData(array('customerNo'=>$_SESSION['customer_name'],"code"=>'accountInfo'));
		$returnData = comm_curl($this->fundUrl.'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
	}
	
	function redemption(&$redempData){
		$redempData['customerNo'] = $_SESSION['customer_name'];
		$redempData['code'] = 'redemption';
		$submitData = $this->getSubmitData($redempData);
		$returnData = comm_curl($this->fundUrl.'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
	}
	
	function bonus_changeable(){
		$submitData = $this->getSubmitData(array('customerNo'=>$_SESSION['customer_name'],"code"=>'bonusChangeable'));
		$returnData = comm_curl($this->fundUrl.'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
		
	}
	
	function bonus_mode(&$bonusData){
		$bonusData['customerNo'] = $_SESSION['customer_name'];
		$bonusData['code'] = 'bonusMode';
		$submitData = $this->getSubmitData($bonusData);
		$returnData = comm_curl($this->fundUrl.'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
	}
	
	function revisePassward($oldpwd, $newpwd, $pwdtype){
		$submitData = $this->getSubmitData(array('customerNo'=>$_SESSION['customer_name'],"code"=>'RevisePassward','oldpwd'=>$oldpwd,'newpwd'=>$newpwd,'pwdtype'=>$pwdtype));
		$returnData = comm_curl($this->fundUrl.'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
	}
	
	function SeekAccount($certificatetype,$certificateno){
		$submitData = $this->getSubmitData(array('customerNo'=>$_SESSION['customer_name'],"code"=>'seekAccount','certificatetype'=>$certificatetype,'certificateno'=>$certificateno));
		$returnData = comm_curl($this->fundUrl.'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
	}
	
	function bgMsgSend(&$bgMsgSend){
		$bgMsgSend['code'] = 'bgMsgSend';
		$bgMsgSend['customerNo'] = $_SESSION['customer_name'];
		$submitData = $this->getSubmitData($bgMsgSend);
		$returnData = comm_curl($this->fundUrl.'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
	}
	
	function bgMsgCheck(&$bgMsgCheck){
		$bgMsgCheck['code'] = 'bgMsgCheck';
		$bgMsgCheck['customerNo'] = $_SESSION['customer_name'];
		$submitData = $this->getSubmitData($bgMsgCheck);
		$returnData = comm_curl($this->fundUrl.'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
	}
	
	function openPhoneTrans(&$openPhoneTrans){
		$openPhoneTrans['code'] = 'openPhoneTrans';
		$openPhoneTrans['customerNo'] = $_SESSION['customer_name'];
		$submitData = $this->getSubmitData($openPhoneTrans);
		$returnData = comm_curl($this->fundUrl.'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
	}
	
	function openBank($channelid,$PARM){
		$openBank['code'] = 'openBank';
		$openBank['channelid'] = $channelid;
		$openBank['PARM'] = $PARM;
		$submitData = $this->getSubmitData($openBank);
		$returnData = comm_curl($this->fundUrl.'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
	}
	
	function bgAddCard($bgCardAdd){
		$bgCardAdd['code'] = 'bgAddCard';
		$bgCardAdd['customerNo'] = $_SESSION['customer_name'];
		$submitData = $this->getSubmitData($bgCardAdd);
		$returnData = comm_curl($this->fundUrl.'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
	}
	
	function bankcardDelete($bankcardDelete){
		$bankcardDelete['code'] = 'bankcardDelete';
		$bankcardDelete['customerNo'] = $_SESSION['customer_name'];
		$submitData = $this->getSubmitData($bankcardDelete);
		$returnData = comm_curl($this->fundUrl.'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
	}
	
	function bankcardChange($bankcardChange){
		$bankcardChange['code'] = 'bankcardChange';
		$bankcardChange['customerNo'] = $_SESSION['customer_name'];
		$submitData = $this->getSubmitData($bankcardChange);
		$returnData = comm_curl($this->fundUrl.'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
	}
	
	function revoke($revoke){
		$revoke['code'] = 'revoke';
		$revoke['customerNo'] = $_SESSION['customer_name'];
		$submitData = $this->getSubmitData($revoke);
// return $submitData;
		$returnData = comm_curl($this->fundUrl.'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
	}
	
	function feeQuery($feeQuery){
		$feeQuery['code'] = 'feeQuery';
		$feeQuery['customerNo'] = $_SESSION['customer_name'];
		$submitData = $this->getSubmitData($feeQuery);
		// return $submitData;
		$returnData = comm_curl($this->fundUrl.'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
	}
}