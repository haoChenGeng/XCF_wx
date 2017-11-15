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
		$this->CI->load->helper(array("comfunction","logfuncs"));
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
		if (!empty($AESKey)){
			$res = comm_curl($fundUrl.'/jijin/XCFinterface/renewCryptKey',$AESKey);
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

	function fund_list($type = 0){
		$invalidTime = strtotime(date('Y-m-d',time()))-50400;			//设置基金列表失效时间，即昨天10点之前获得的基金信息必须进行更新。
		$startTime = strtotime(date('Y-m-d',time()).' 09:20:00');		//设置自动更新时间段[$startTime,$endTime](从9:20到10:00)每隔5分钟自动更新基金列表
		$endTime = strtotime(date('Y-m-d',time()).' 10:00:00');
		$currentTime = time();
		$this->CI->load->model("Model_db");
		$updatetime = $this->CI->db->where(array('dealitem' => 'fundlist'))->get('dealitems')->row_array()['updatetime'];
		$flag = TRUE;
		if ($type || $updatetime<$invalidTime || ($currentTime > $startTime && $updatetime<$endTime && ($currentTime-$updatetime)>300)){
			$submitData = $this->getSubmitData(array("code"=>'fundlist'));
			$returnData = comm_curl($this->fundUrl.'/jijin/XCFinterface',$submitData);
			$funddata = $this->getReturnData($returnData)['data']['fundList'];
			$flag = FALSE;
			if (is_array($funddata) && !empty($funddata)){
				$preFundInfo = $this->CI->db->select('fundcode,nav,navdate')->get('p2_fundlist')->result_array();
				$preFundInfo = setkey($preFundInfo, 'fundcode');
				$flag = $this->CI->Model_db->incremenUpdate('fundlist', $funddata, 'fundcode');
				if ($flag){
					$this->CI->db->set(array('updatetime' => time()))->where(array('dealitem' => 'fundlist'))->update('dealitems');
				}
				foreach ($funddata as $val){
					$tableName = 'p2_netvalue_'.$val['fundcode'];
					if ($this->CI->db->table_exists($tableName)){
						$preDate = $this->CI->db->select_max('net_date')->get($tableName)->row_array()['net_date'];
						if ($preDate < $val['navdate']){
							$this->getFundNetvalue($val['fundcode'],$preDate,$val['fundtype']);
						}
					}else{
						$this->getFundNetvalue($val['fundcode'],'',$val['fundtype']);
					}
				}
			}
		}
		return $flag;
	}
	
	function getFundNetvalue($fundcode,$startDate='',$fundType = 0){
		$tableName = 'p2_netvalue_'.$fundcode;
		$submitData = array("code"=>'fundNetvalue','fund_code'=>$fundcode);
		if (!empty($startDate)){
			$submitData['startDate'] = $startDate;
		}
		$submitData = $this->getSubmitData($submitData);
		$returnData = comm_curl($this->fundUrl.'/jijin/XCFinterface',$submitData);
		$fundNetvalue = $this->getReturnData($returnData);
		if ($fundNetvalue['code'] == '0000' && is_array($fundNetvalue['data'])){
			if (!$this->CI->db->table_exists($tableName)){
				$this->creatFundNetValue($tableName);
			}
			if (empty($startDate)){
				$this->CI->db->truncate($tableName);
			}
			$updateData = &$fundNetvalue['data'];
			$currentdate = date('Y-m-d',time());
			foreach ($updateData as $key=>$val){
				if (empty($val['net_date'])){
					unset ($updateData[$key]);
				}else{
					$updateData[$key]['net_day_growth'] = empty($val['net_day_growth']) ? 0 : $val['net_day_growth'];
					$updateData[$key]['growthrate'] = empty($val['growthrate']) ? 0 : $val['growthrate'];
					$updateData[$key]['XGRQ'] = $currentdate;
				}
			}
			$this->CI->load->model("Model_db");
			if (empty($startDate)){
				$flag = $this->CI->Model_db->batch_insert($tableName, $fundNetvalue['data']);
			}else{
				$flag = $this->CI->Model_db->incremenUpdate($tableName, $fundNetvalue['data'], 'net_date');
			}
			if ($fundType != 2){
				$fields = $this->CI->db->query("SHOW FULL COLUMNS FROM ".$tableName)->result_array();
				$fields = setkey($fields,'Field');
				if (!isset($fields['oneMonth'])){
					$sql = "ALTER TABLE `wx_xnxcf`.`".$tableName."` ADD COLUMN `oneMonth` DOUBLE NULL COMMENT '1个月增长率数据' AFTER `XGRQ`, ADD COLUMN `threeMonth` DOUBLE NULL COMMENT '3个月增长率数据' AFTER `oneMonth`, ADD COLUMN `sixMonth` DOUBLE NULL COMMENT '6个月增长数据' AFTER `threeMonth`, ADD COLUMN `oneYear` DOUBLE NULL COMMENT '1年增长数据' AFTER `sixMonth`;";
					$this->CI->db->query($sql);
				}
				$flag = $flag && $this->updateDrawData($tableName);
			}
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
				`fundincomeunit` varchar(24) DEFAULT NULL,
				`growthrate` varchar(24) NOT NULL DEFAULT '0',
				`XGRQ` datetime DEFAULT NULL COMMENT '更新日期',
				PRIMARY KEY (`net_date`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		// 				`oneMonth` double DEFAULT NULL COMMENT '1个月增长率数据',
		// 				`threeMonth` double DEFAULT NULL COMMENT '3个月增长率数据',
		// 				`sixMonth` double DEFAULT NULL COMMENT '6个月增长数据',
		// 				`oneYear` double DEFAULT NULL COMMENT '1年增长数据',		
		$flag = $this->CI->db->query($sql);
		return $flag;
	}
	
	private function updateDrawData($tableName){
		$this->CI->db->set(array("oneMonth"=>-1000,"threeMonth"=>-1000,"sixMonth"=>-1000,"oneYear"=>-1000))->update($tableName);
		$period = array(date("Y-m-d", strtotime("-1 month")),
				date("Y-m-d", strtotime("-3 month")),
				date("Y-m-d", strtotime("-6 month")),
				date("Y-m-d", strtotime("-1 year")));
		$netValue = $this->CI->db->select("net_date,net_unit,net_sum")->where(array("net_date >="=>$period[3]))->order_by("net_date","ASC")->get($tableName)->result_array();
		$i = 3;
		$fields = array("oneMonth","threeMonth","sixMonth","oneYear");
		$j = 0;
		$netBase[3] = current($netValue);
		foreach ($netValue as $val){
			while ($i > 0 && $val['net_date'] >= $period[$i-1]){
				$i --;
				$netBase[$i] = $val;
			}
			$newData[$j]['net_date'] = $val['net_date'];
			for ($ii = 3; $ii >= $i; $ii--){
				$newData[$j][$fields[$ii]] = round(100*($val['net_sum']-$netBase[$ii]['net_sum'])/$netBase[$ii]['net_unit'],2);
			}
			for (; $ii>=0; $ii--){
				$newData[$j][$fields[$ii]] = -1000;
			}
			$j++;
		}
		$flag = $this->CI->Model_db->incremenUpdate($tableName, $newData, 'net_date');
		return $flag;
	}
	
	function channel(){
		$currentTime = time();
		$updatetime = $this->CI->db->where(array('dealitem' => 'channelInfo'))->get('dealitems')->row_array()['updatetime'];
		if ($updatetime === null){
			$updatetime = 0;
			$this->CI->db->set(array('dealitem' => 'channelInfo','updatetime' => time()))->insert('dealitems');
		}
		if ($currentTime - $updatetime > 86400){
// 			$logfile_suffix = '-'.date('Ymd',time()).'.log';
			$submitData = $this->getSubmitData(array('code'=>'channel'));
			$channel = $this->getReturnData(comm_curl($this->fundUrl.'/jijin/XCFinterface',$submitData));
			if ($channel['code'] == '0000'){
				$updateData = &$channel['data'];
			}else{
// 				file_put_contents('log/trade/channel'.$this->CI->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n调用channel接口失败,返回数据为".serialize($channel)."\r\n\r\n",FILE_APPEND);
				myLog('trade/channel',"调用channel接口失败,返回数据为:".serialize($channel));
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
// 			$logfile_suffix = '-'.date('Ymd',time()).'.log';
			$submitData = $this->getSubmitData(array('code'=>'paymentChannel'));
			$paymentChannel = $this->getReturnData(comm_curl($this->fundUrl.'/jijin/XCFinterface',$submitData));
			if ($paymentChannel['code'] == '0000'){
				$updateData = &$paymentChannel['data'];
			}else{
// 				file_put_contents('log/trade/paymentChannel'.$this->CI->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n调用paymentChannel接口失败,返回数据为".serialize($paymentChannel)."\r\n\r\n",FILE_APPEND);
				myLog('trade/paymentChannel',"调用paymentChannel接口失败,返回数据为:".serialize($paymentChannel));
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
// 			$logfile_suffix = '-'.date('Ymd',time()).'.log';
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
// 						file_put_contents('log/trade/provCity'.$this->CI->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n调用provCity(参数".$val.")接口失败,返回数据为".serialize($city)."\r\n\r\n",FILE_APPEND);
						myLog('trade/provCity',"调用provCity(参数".$val.")接口失败,返回数据为".serialize($city));
					}
				}
			}else{
// 				file_put_contents('log/trade/provCity'.$this->CI->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n调用provCity接口失败,返回数据为".serialize($province)."\r\n\r\n",FILE_APPEND);
				myLog('trade/provCity',"调用provCity接口的省份数据失败,返回数据为".serialize($province));
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
		if (isset($_SESSION['customer_name'])){
			$submitData = $this->getSubmitData(array('customerNo'=>$_SESSION['customer_name'],"code"=>'transQuery','startDate'=>$startDate,'endDate'=>$endDate));
			$returnData = comm_curl($this->fundUrl.'/jijin/XCFinterface',$submitData);
			$returnData = $this->getReturnData($returnData);
			if (isset($returnData['code']) && $returnData['code'] == '0001'){
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
		$currentTime = time();
		$updatetime = $this->CI->db->where(array('dealitem' => 'riskQuestion'))->get('dealitems')->row_array()['updatetime'];
		if ($updatetime === null){
			$updatetime = 0;
			$this->CI->db->set(array('dealitem' => 'riskQuestion','updatetime' => time()))->insert('dealitems');
		}
		if ($currentTime - $updatetime > 86400){
			$submitData = $this->getSubmitData(array('customerNo'=>$_SESSION['customer_name'],"code"=>'riskQuery'));
			$returnData = comm_curl($this->fundUrl.'/jijin/XCFinterface',$submitData);
			$JZQuestion = $this->getReturnData($returnData);
			if ($JZQuestion['code'] == '0000'){
				foreach ($JZQuestion['data'] as &$val){
					$val['result'] = json_encode($val['result']);
					unset($val['papername']);
				}
				$updateData = &$JZQuestion['data'];
			}else{
				myLog('trade/risk_test_query',"调用riskQuery接口失败,返回数据为".serialize($JZQuestion));
			}
			if (!empty($updateData)){
				$flag = $this->CI->db->truncate('p2_riskquestion');
				$flag = $this->CI->db->insert_batch('p2_riskquestion',$updateData);
				if ($flag){
					$this->CI->db->set(array('updatetime' => time()))->where(array('dealitem' => 'riskQuestion'))->update('dealitems');
				}
			}
		}
		$riskQuestions = $this->CI->db->get('p2_riskquestion')->result_array();
		foreach ($riskQuestions as &$val){
			$val['result'] = json_decode($val['result'],true);
		}
		return $riskQuestions;
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
	
	function resetPassward($newpwd){
		$submitData = $this->getSubmitData(array('customerNo'=>$_SESSION['customer_name'],"code"=>'ResetPassward','newpwd'=>$newpwd));
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
		$returnData = comm_curl($this->fundUrl.'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
	}
	
	function feeQuery($feeQuery){
		$feeQuery['code'] = 'feeQuery';
		$feeQuery['customerNo'] = $_SESSION['customer_name'];
		$submitData = $this->getSubmitData($feeQuery);
		$returnData = comm_curl($this->fundUrl.'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
	}
	
	function SDQryAllFund($qryallfund = 1){
		$SDQryAllFund['code'] = 'SDQryAllFund';
		$SDQryAllFund['customerNo'] = $_SESSION['customer_name'];
		$SDQryAllFund['qryallfund'] = $qryallfund;
		$submitData = $this->getSubmitData($SDQryAllFund);
		$returnData = comm_curl($this->fundUrl.'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
	}
	
	function SDCustomAssetInfo($SDCustomAssetInfo){
		$SDCustomAssetInfo['code'] = 'SDCustomAssetInfo';
		$SDCustomAssetInfo['customerNo'] = $_SESSION['customer_name'];
		$submitData = $this->getSubmitData($SDCustomAssetInfo);
		$returnData = comm_curl($this->fundUrl.'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
	}
	
	function SDAccess($mobileno=''){
		$SDAccess['code'] = 'SDAccess';
		if (!empty($mobileno)){
			$SDAccess['mobileno'] = $mobileno;
		}
		$SDAccess['customerNo'] = $_SESSION['customer_name'];
		$submitData = $this->getSubmitData($SDAccess);
		$returnData = comm_curl($this->fundUrl.'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
	}
	
	function autoUpdateJZInfo($tableName = ''){
		$autoUpdateJZInfo['code'] = 'autoUpdateJZInfo';
		$autoUpdateJZInfo['tableName'] = $tableName;
		$submitData = $this->getSubmitData($autoUpdateJZInfo);
		$returnData = comm_curl($this->fundUrl.'/jijin/XCFinterface',$submitData);
		$returnData = $this->getReturnData($returnData);
		if ('0000' == $returnData['code']){
			if (empty($tableName)){
				if ($this->fund_list(1)){
					return array('code'=>'0000','msg'=>'从金证平台更新基金信息成功');
				}else{
					return array('code'=>'9997','msg'=>'从金证平台更新基金信息失败，请重试');
				}
			}else{
				return $returnData;
			}
		}else{
			return ($returnData);
		}
	}
	
	function bankCardActive($inputData){
		$inputData['code'] = 'bankCardActive';
		$inputData['customerNo'] = $_SESSION['customer_name'];
		$submitData = $this->getSubmitData($inputData);
		$returnData = comm_curl($this->fundUrl.'/jijin/XCFinterface',$submitData);
		$returnData = $this->getReturnData($returnData);
		return ($returnData);

	}

	function FixedInvestment($input){
		$neededFields = array('tpasswd','channelid','depositacct','investamount','fundcode','tano','moneyaccount','investcycle','investcyclevalue','investperiods','investperiodsvalue');
    	foreach ($neededFields as $val){
    		if (!isset($input[$val])){
    			return array('code'=>'9999', 'msg'=>'必填字段,'.$val.'不能为空！');
    		}
    	}
		$submitData = $this->getSubmitData(array(
			'customerNo'=>$_SESSION['customer_name'],
			"code"=>'FixedInvestment',
			'tpasswd'=>$input['tpasswd'],
			'channelid'=>$input['channelid'],
			'depositacct'=>$input['depositacct'],
			'investamount'=>$input['investamount'],
			'fundcode'=>$input['fundcode'],
			'tano'=>$input['tano'],
			'moneyaccount'=>$input['moneyaccount'],
			'investcycle'=>$input['investcycle'],
			'investcyclevalue'=>$input['investcyclevalue'],
			'investperiods'=>$input['investperiods'],
			'investperiodsvalue'=>$input['investperiodsvalue']));
		$returnData = comm_curl($this->fundUrl.'/jijin/XCFinterface',$submitData);
		return ($this->getReturnData($returnData));
	}
}