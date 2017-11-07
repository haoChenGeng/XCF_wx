<?php
if (!defined('BASEPATH')){
	exit('No direct script access allowed');
}

class Fund_bank extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper(array("output","comfunction","logfuncs"));       //"page"  "log"   "func",
        $this->load->library(array('Fund_interface','Logincontroller'));
        $_SESSION['myPageOper'] = 'account';
    }

    //赠加银行卡
    function operation($operation,$channelid='')
    {
    	if (!$this->logincontroller->isLogin()) {
    		exit;
    	}
    	switch ($operation){
    		case 'bankcard_add':
    			$oper_des = '增加银行卡';
    			break;
    		case 'bankcard_change':
    			$oper_des = '更换银行卡';
    			break;
    		case 'bankcard_active':
    			$oper_des = '激活银行卡';
    			break;
    		default:
    			$oper_des = '';
    	}
    	//查询获得用户姓名、证件类型、证件号码信息
    	$user_info = $this->fund_interface->AccountInfo();
    	if (!isset($user_info['code']) || $user_info['code'] != '0000' || !isset($user_info['data']['certificateno'])){
    		$log_msg = '查询用户信息(account_info<520101>)失败';
//     		file_put_contents(.$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n查询用户".$_SESSION ['customer_name']."信息失败,返回数据:".serialize($user_info)."\r\n\r\n",FILE_APPEND);
    		myLog('user/'.$operation,"查询用户".$_SESSION ['customer_name']."信息失败,返回数据:".serialize($user_info));
    	}
    	$this->load->config('jz_dict');
//     	$needPCBank = $this->config->item('needProvCity')[$this->config->item('selectChannel')];
    	if ($operation == 'bankcard_add'){
    		$data['payment_channel'] = $this->void_paymentchannel();
//     		foreach ($data['payment_channel'] as $key => $val){
//     			if (in_array($val['channelname'],$needPCBank)){
//     				$data['payment_channel'][$key]['needProvCity'] = 1;
//     				if (!isset($data['provCity'])){
//     					$data['provCity'] = json_encode($this->fund_interface->provCity());
//     				}
//     			}
//     		}
    	}elseif ('bankcard_change' == $operation || 'bankcard_active' == $operation){
    		$paymentChannel = $this->db->where(array('channelid'=>$channelid))->get('p2_paymentchannel')->row_array();
//     		if (in_array($paymentChannel['channelname'],$needPCBank)){
//     			$data['provCity'] = json_encode($this->fund_interface->provCity());
//     		}
    		$bankCardInfo = $this->getBankCardInfo($channelid);
    		if (empty($bankCardInfo)){
    			$log_msg = '查询用户银行卡信息失败';
    		}else{
    			$data['channelname'] = $paymentChannel['channelname'];
    			if ('bankcard_active' == $operation){
    				$data['depositacct'] = $bankCardInfo['depositacct'];
    				$_SESSION['bankCard_operData']['paycenterid'] = $bankCardInfo['paycenterid'];
    			}else{
    				$_SESSION['bankCard_operData']['depositacct_old'] = $bankCardInfo['depositacct'];
    			}
    			$_SESSION['bankCard_operData']['channelid'] = $channelid;
    			$_SESSION['bankCard_operData']['channelname'] = $paymentChannel['channelname'];
    			$_SESSION['bankCard_operData']['moneyaccount'] = $bankCardInfo['moneyaccount'];
    		}
    	}
    	if (isset($log_msg)){
//     		file_put_contents('log/user/'.$operation.$this->logfile_suffix,date('Y-m-d H:i:s',time()).$oper_des."失败:".$log_msg."\r\n\r\n",FILE_APPEND);
    		myLog('user/'.$operation,$oper_des."失败:".$log_msg);
    		Message(Array(
    				'msgTy' => 'fail',
    				'msgContent' => $oper_des.'失败，系统正在返回...',
    				'msgUrl' => $this->base . '/jijin/Jz_my',
    				'base' => $this->base
    				));
    	}else{
    		$this->load->config('jz_dict');
    		$data['certificatetype'] = $this->config->item('certificatetype');
    		$data['public_key'] = file_get_contents($this->config->item('RSA_publickey')); //获取RSA_加密公钥
    		$data['rand_code'] = "\t".mt_rand(100000,999999);                              //随机生成验证码
    		$data['operation'] = $operation;
    		$data['pag_title'] = $oper_des;
    		$_SESSION['bankCard_operData']['certificateno'] = $user_info['data']['certificateno'];
    		$_SESSION['bankCard_operData']['certificatetype'] = $user_info['data']['certificatetype'];
    		$_SESSION['bankCard_operData']['depositacctname'] = $user_info['data']['depositacctname'];
    		$_SESSION['rand_code'] = $data['rand_code'];
// var_dump($_SESSION['bankCard_operData']);
// var_dump($data);
    		$this->load->view('jijin/bank/bgMsgSend',$data);
    	}
    }
    
    //银行卡鉴权
    function bgMsgSend()
    {
// var_dump($_SESSION['bankCard_operData']);
    	if (!$this->logincontroller->isLogin()) {
    		exit;
    	}
    	$post = $this->input->post();
    	//-----------RSA解密----------------------------
    	$private_key = openssl_get_privatekey(file_get_contents($this->config->item('RSA_privatekey')));
    	$decryptData ='';
    	openssl_private_decrypt(base64_decode($post['depositacct']),$decryptData, $private_key, OPENSSL_PKCS1_PADDING);
    	//判断一次性随机验证码是否存在
    	$div_bit = strpos($decryptData,(string)$_SESSION['rand_code']);
    	unset($_SESSION['rand_code']);
    	if ($div_bit !== false){                      //找到一次性随机验证码
    		//将解密后数据赋值给certificateno和depositacct，并对输入数据进行处理（删除头尾空格及银行卡号中间空格）
    		$post['depositacct'] =  trim(substr($decryptData, 0, $div_bit));
    		$post['depositacct'] =  str_replace(" ",'',$post['depositacct']);
    		if ( 'bankcard_change' == $post['operation'] && $post['depositacct'] == $_SESSION['bankCard_operData']['depositacct_old']){
    			$err_msg = $log_msg = '新银行卡和原银行卡卡号相同';
    		}else{
    			$_POST['depositacct'] = $post['depositacct'];
    			$post['mobiletelno'] = trim($post['mobiletelno']);
    			//--------以下设置客户输入错误提示--------------------------
    			$this->load->library('form_validation');
    			$this->form_validation->set_message('required', '%s不能为空.');
    			$this->form_validation->set_message('max_length', '%s长度超出限制.');
    			$this->form_validation->set_message('numeric', '%s必须为数字.');
    			 
    			//--------以下设置判断客户输入信息检测规则--------------------------
    			$this->form_validation->set_rules('depositacct','银行卡号','required|max_length[30]|numeric');
    			$this->form_validation->set_rules('mobiletelno','银行预留电话','required|max_length[20]|numeric');
    			if ($post['operation'] == 'bankcard_add'){
    				$this->form_validation->set_rules('channelid','银行','required');
    				$_SESSION['bankCard_operData']['channelid'] = $post['channelid'];
    			}
    			if ($this->form_validation->run() == TRUE)
    			{
    				$submitData = $_SESSION['bankCard_operData'];
    				unset($submitData['depositacct_old'],$submitData['channelname'],$submitData['moneyaccount']);
    				$submitData['depositacct'] = $post['depositacct'];
    				$submitData['mobiletelno'] = $post['mobiletelno'];
    				//     			if (!isset($submitData['channelid'])){
    				//     				$submitData['channelid'] = $post['channelid'];
    				//     			}
    				$submitData['addBankCard'] = 1;
    				$logData = $submitData;
    				$logData['certificateno'] = substr($logData['certificateno'],0,6).'***'.substr($logData['certificateno'],-3);
    				$logData['depositacct'] = substr($logData['depositacct'],0,3).'***'.substr($logData['depositacct'],-3);
    				$res_bMS = $this->fund_interface->bgMsgSend($submitData);
    				// var_dump($res_bMS);
    				//     			file_put_contents('log/user/'.$post['operation'].$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n用户:".$_SESSION ['customer_name']."调用bgMsgSend接口\r\n调用数据:".serialize($logData)."\r\n返回数据:".serialize($res_bMS)."\r\n\r\n",FILE_APPEND);
    				myLog('user/'.$post['operation'],"用户:".$_SESSION ['customer_name']."调用bgMsgSend接口\t调用数据:".serialize($logData)."\t返回数据:".serialize($res_bMS));
    				if ( !isset($res_bMS['code']) || $res_bMS['code'] != '0000' )                  //鉴权失败  $res_bMS['data'][0]['comtype']表示该卡已经鉴权过
    				{
    					$err_msg = '银行卡鉴权失败';
    				}else{
    					if ($post['operation'] == 'bankcard_add'){
    						$_SESSION['bankCard_operData']['channelid'] = $post['channelid'];         //网点号(支付渠道)
    						$paymentChannel = $this->db->where(array('channelid'=>$submitData['channelid']))->get('p2_paymentchannel')->row_array();
    						$_SESSION['bankCard_operData']['channelname'] = $paymentChannel['channelname'];     //网点名
    					}
    					$_SESSION['bankCard_operData']['bankname'] = isset($post['bankname']) ? $post['bankname'] : $_SESSION['bankCard_operData']['channelname'];
    					$_SESSION['bankCard_operData']['depositacct'] = $post['depositacct'];         //银行卡号
    					$_SESSION['bankCard_operData']['mobileno'] = $post['mobiletelno'];            //银行预留电话
    					if (isset($post['depositprov'])){
    						$_SESSION['bankCard_operData']['depositprov'] = $post['depositprov'];
    						$_SESSION['bankCard_operData']['depositcity'] = $post['depositcity'];
    					}
    					// var_dump($_SESSION['bankCard_operData']);
    					$this->load_bgMsgCheckOnly($post['operation']);
    				}
    			}else{
    				$err_msg = validation_errors();
    			}
    		}
    	}else{
    		$err_msg = '系统故障';
    		$log_msg = '一次性随机验证码错误';
    	}
    	if (isset($err_msg)){
    		$str = isset($log_msg)?$log_msg:$err_msg;
//     		file_put_contents('log/user/'.$post['operation'].$this->logfile_suffix, date('Y-m-d H:i:s',time()).":\r\n用户(".$_SESSION ['customer_name'].")进行".$post['operation']."操作时，鉴权失败，原因为：".$str."\r\n\r\n",FILE_APPEND);
    		myLog('user/'.$post['operation'],"用户(".$_SESSION ['customer_name'].")进行".$post['operation']."操作时，鉴权失败，原因为：".$str);
    		Message(Array(
    				'msgTy' => 'fail',
    				'msgContent' => $err_msg.'<br/>系统正在返回...',
    				'msgUrl' => $this->base . '/jijin/Fund_bank/bank_info',
    				'base' => $this->base
    				));
    	}
    }
  
    //-------------------- 加载bgMsgCheckOnly页面 -----------------------------------------------------
    private function load_bgMsgCheckOnly($operation)
    {
    	$data['public_key'] = file_get_contents($this->config->item('RSA_publickey')); //获取RSA_加密公钥
    	$data['rand_code'] = "\t".mt_rand(100000,999999);                              //随机生成验证码
    	$data['operation'] = $operation;
    	switch ($operation){
    		case 'bankcard_add':
    			$data['pag_title'] = '增加银行卡';
    			$nextView = 'jijin/bank/bgMsgCheckOnly';
    			break;
    		case 'bankcard_change':
    			$data['pag_title'] = '更换银行卡';
    			$nextView = 'jijin/bank/bgMsgCheckOnly';
    			break;
    		case 'bankcard_active':
    			$data['pag_title'] = '激活银行卡';
    			$nextView = 'jijin/bank/bankCardActive';
    			break;
    	}
    	$_SESSION['rand_code'] = $data['rand_code'];
    	$this->load->view($nextView,$data);
    }
    
    function operation_submit()
    {
    	if (!$this->logincontroller->isLogin()) {
    		exit;
    	}
    	$post = $this->input->post();
    	//记录post提交数据，裁减密码敏感信息。
    	$tmp =$post;
    	$tmp['tpasswd'] = substr($tmp['tpasswd'], 6,6);
//     	file_put_contents('log/user/'.$post['operation'].$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n用户".$_SESSION ['customer_name']."进行".$post['operation']."操作,输入数据为:".serialize($tmp)."\r\n\r\n",FILE_APPEND);
    	myLog('user/'.$post['operation'],"用户".$_SESSION ['customer_name']."进行".$post['operation']."操作,输入数据为:".serialize($tmp));
    	//-----------RSA解密----------------------------
    	$private_key = openssl_get_privatekey(file_get_contents($this->config->item('RSA_privatekey')));
    	$decryptData ='';
    	openssl_private_decrypt(base64_decode($post['tpasswd']),$decryptData, $private_key, OPENSSL_PKCS1_PADDING);
    	//判断一次性随机验证码是否存在
    	$div_bit = strpos($decryptData,(string)$_SESSION['rand_code']);
    	unset($_SESSION['rand_code']);
    	if ($div_bit !== false){                           //找到一次性随机验证码
    		$post['tpasswd'] = substr($decryptData, 0, $div_bit);
//     		$tpasswd =  my_md5($_SESSION ['customer_name'], $post['tpasswd']);
//     		$user_info = $this->db->where(array('XN_account' => $_SESSION ['customer_name']))->get('jz_account')->row_array();
//     		if ($user_info['tpasswd'] == $tpasswd)         //交易密码校验
//     		{
//     			$this->load->config('jz_dict');
//     			$this->load->library('Fund_interface');
//-----------------------------------------------
    		//准备数据，并清除相关SESSION
    		$oper_data = $_SESSION['bankCard_operData'];
    		unset($_SESSION['bankCard_operData']);
    		$oper_data['tpasswd'] = $post['tpasswd'];
    		$oper_data['verificationCode'] = $post['verificationCode'];
// var_dump($oper_data);
    		//根据不同银行账户操作，调用金证接口,并记录调用金证接口数据及返回结果
    		switch ($post['operation']){
    			case 'bankcard_add':
    				$oper_res = $this->fund_interface->bgAddCard($oper_data);
// var_dump($oper_res);
// $data['data'] = $oper_res['data'];
// var_dump($oper_res);
// $data['url'] = $this->config->item('fundUrl').'/jijin/XCFinterface';
// $this->load->view('UrlTest',$data);
// return;
    				$oper_des = '增加银行卡';
    				break;
    			case 'bankcard_change':
    				$oper_res = $this->fund_interface->bankcardChange($oper_data);
// $data['data'] = $oper_res['data'];
// var_dump($oper_res);
// $data['url'] = $this->config->item('fundUrl').'/jijin/XCFinterface';
// $this->load->view('UrlTest',$data);
// return;
    				$oper_des = '更换银行卡';
    				break;
    			case 'bankcard_active':
//     				$data['pag_title'] = '激活银行卡';
    			break; 
    		}
    		$logData = $oper_data;
    		$logData['certificateno'] = substr($logData['certificateno'],0,6).'***'.substr($logData['certificateno'],-3);
    		$logData['depositacct'] = substr($logData['depositacct'],0,3).'***'.substr($logData['depositacct'],-3);
    		$logData['tpasswd'] = "***";
    		if (isset($logData['depositacct_old'])){
    			$logData['depositacct_old'] = substr($logData['depositacct_old'],0,3).'***'.substr($logData['depositacct_old'],-3);
    		}
//     		file_put_contents('log/user/'.$post['operation'].$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n用户:".$_SESSION ['customer_name'].")调用金证接口进行".$oper_des.'操作，输入数据为:'.serialize($logData).'返回数据为:'.serialize($oper_res)."\r\n\r\n",FILE_APPEND);
    		myLog('user/'.$post['operation'],"用户:".$_SESSION ['customer_name'].")调用金证接口进行".$oper_des.'操作，输入数据为:'.serialize($logData).'返回数据为:'.serialize($oper_res));
    		 
    		if (isset($oper_res['code']))
    		{
    			if (isset($_SESSION['next_url'])){
    				$msgUrl = $_SESSION['next_url'];
    				unset($_SESSION['next_url']);
    			}else{
    				$msgUrl = '/jijin/Fund_bank/bank_info';
    			}
    			if ($oper_res['code']== '0000'){
    				Message(Array(
    						'msgTy' => 'sucess',
    						'msgContent' => $oper_des.'成功',
    						'msgUrl' => $this->base.$msgUrl, //调用我的基金界面
    						'base' => $this->base
    						));
    				exit;
    			}else{
    				if ($oper_res['code']== '-409999999'){
    					$err_msg = '密码输入错误';
    				}else{
    					if (in_array($oper_res['code'], array('-440117034','-440114020'))){
    						$err_msg = $oper_res['msg'];
    					}else{
    						$err_msg = '系统故障';
    					}
    				}
    				$log_msg = '调用'.$oper_des.'返回错误信息为：'.$oper_res['msg'];
    			}
    		}
    		else{                                      //调用金证接口失败
    			$err_msg = '系统故障';
    			$log_msg = $oper_des.'接口调用失败';
    		}
    	}
    	else {
    		$err_msg = '系统故障';
    		$log_msg = "一次性随机验证码错误";
    	}
    	//失败时，给客户的提示信息，并log
    	if (!empty($err_msg))
    	{
    		$str = isset($log_msg)?$log_msg:$err_msg;
//     		file_put_contents('log/user/'.$post['operation'].$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n用户".$_SESSION ['customer_name'].$oper_des."操作失败，原因为：".$str."\r\n\r\n",FILE_APPEND);
    		myLog('user/'.$post['operation'],"用户".$_SESSION ['customer_name'].$oper_des."操作失败，原因为：".$str);
    		Message(Array(
    				'msgTy' => 'fail',
    				'msgContent' => $err_msg.', 正在返回...',
    				'msgUrl' => $this->base .'/jijin/Fund_bank/bank_info',
    				'base' => $this->base
    				));
    	}
    }
    
    function bankCardActive()
    {
    	if (!$this->logincontroller->isLogin()) {
    		exit;
    	}
    	$post = $this->input->post();
    	$oper_data = $_SESSION['bankCard_operData'];
    	unset($_SESSION['bankCard_operData']);
    	$oper_data['verificationCode'] = $post['verificationCode'];
    	myLog('user/bankcard_active',"用户".$_SESSION ['customer_name']."进行银行卡激活操作,输入数据为:".serialize($oper_data));
    	$res = $this->fund_interface->bankCardActive($oper_data);
    	myLog('user/bankcard_active',"用户".$_SESSION ['customer_name']."进行银行卡激活操作,返回数据为:".serialize($res));
    	if (isset($res['code']))
    	{
    		if (isset($_SESSION['next_url'])){
    			$msgUrl = $_SESSION['next_url'];
    			unset($_SESSION['next_url']);
    		}else{
    			$msgUrl = '/jijin/Fund_bank/bank_info';
    		}
    		if ($res['code']== '0000'){
    			Message(Array(
    					'msgTy' => 'sucess',
    					'msgContent' => '银行卡激活成功',
    					'msgUrl' => $this->base.$msgUrl, //调用我的基金界面
    					'base' => $this->base
    					));
    			exit;
    		}else{
    			if ('0054' == $res['code']){
    				$err_msg = $res['msg'];
    			}else{
    				$err_msg = '银行卡激活失败，请稍后重试！';
    			}
    			$log_msg = '调用银行卡激活接口返回错误信息为：'.$res['msg'];
    		}
    	}else{                                      //调用金证接口失败
    		$err_msg = '系统故障';
    		$log_msg = '银行卡激活接口调用失败';
    	}
    	//失败时，给客户的提示信息，并log
    	if (!empty($err_msg))
    	{
    		$str = isset($log_msg)?$log_msg:$err_msg;
    		//     		file_put_contents('log/user/'.$post['operation'].$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n用户".$_SESSION ['customer_name'].$oper_des."操作失败，原因为：".$str."\r\n\r\n",FILE_APPEND);
    		myLog('user/bankcard_active',"用户".$_SESSION ['customer_name']."进行银行卡激活操作失败，原因为：".$str);
    		Message(Array(
    				'msgTy' => 'fail',
    				'msgContent' => $err_msg.', 正在返回...',
    				'msgUrl' => $this->base .'/jijin/Fund_bank/bank_info',
    				'base' => $this->base
    				));
    	}
    }
    
	function bank_info()
	{
		if (!$this->logincontroller->isLogin()) {
			exit;
		}
		$bank_info =$this->fund_interface->bankCardPhone();
		$channel_info = $this->fund_interface->paymentChannel();
		$channel_info = setkey($channel_info,'channelid');
		myLog('user/bank_info',"用户".$_SESSION ['customer_name']."查询银行卡信息，返回数据为:".serialize($bank_info));
		if (isset($bank_info['code']) && $bank_info['code'] == '0000')
		{
			if (!empty($bank_info['data'][0]))
			{
				$data['bank_info'] = $bank_info['data'];
				$this->load->config('jz_dict');
				foreach ($data['bank_info'] as $key => $val)
				{
					if(0 == $val['authenticateflag'] && 0 == $val['status']){
						$data['bank_info'][$key]['status'] = '未激活';
					}else{
						$data['bank_info'][$key]['status'] = $this->config->item('bankcard_status')[$val['status']];
						if (empty($data['bank_info'][$key]['status'])) {
							$data['bank_info'][$key]['status'] = '未知';
						};
					}
					$data['bank_info'][$key]['channelname'] = $channel_info[$val['channelid']]['channelname'];
				}
			}
			else
			{
				$data['fail_message'] = '未找到相关银行卡信息';
			}
			$data['num_channel'] = count($channel_info)-count($bank_info['data']);
		}else{
			$data['fail_message'] = '银行卡查询失败,请稍候再试!';
			$data['num_channel'] = 0;
		}
		$this->load->view('/jijin/bank/bank_info',$data);
	}
	
	private function void_paymentchannel(){                        //获取未邦定银行卡的支付渠道
		$channel_info = $this->fund_interface->paymentChannel();
		$channel_info =  setkey($channel_info,'channelid');
		$bank_info =$this->fund_interface->bankCardPhone();
		if (isset($bank_info['code']) && $bank_info['code'] == '0000')
		{
			if (!empty($bank_info['data'])){
				foreach ($bank_info['data'] as $key => $val)
				{
					unset($channel_info[$val['channelid']]);
				}
			}
		}
		return $channel_info;
	}
	
	
	
	function bankcard_delete($channelid='')
	{
		if (!$this->logincontroller->isLogin()) {
			exit;
		}
 		$post = $this->input->post();
 		if (!empty($post))
 		{
 			//记录时提交数据，裁减密码敏感信息。
 			$tmp =$post;
 			$tmp['tpasswd'] = substr($tmp['tpasswd'], 6,6);
//  			file_put_contents('log/user/bankcard_delete'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n用户:".$_SESSION ['customer_name']."进行删除银行卡操作,调用数据为:".serialize($tmp)."\r\n\r\n",FILE_APPEND);
 			myLog('user/bankcard_delete',"用户:".$_SESSION ['customer_name']."进行删除银行卡操作,调用数据为:".serialize($tmp));
 			$private_key = openssl_get_privatekey(file_get_contents($this->config->item('RSA_privatekey')));
 			$decryptData ='';
 			openssl_private_decrypt(base64_decode($post['tpasswd']),$decryptData, $private_key, OPENSSL_PKCS1_PADDING);
 			//判断一次性随机验证码是否存在
 			$div_bit = strpos($decryptData,(string)$_SESSION['rand_code']);
 			unset($_SESSION['rand_code']);
 			if ($div_bit !== false){                           //找到一次性随机验证码
 				$post['tpasswd'] = substr($decryptData, 0, $div_bit);
 				$bankCardInfo = $this->getBankCardInfo($post['channelid']);
 				if (empty($bankCardInfo)){
 					$log_msg = '查询用户银行卡信息失败';
 				}else{
 					$post['depositacct'] = $bankCardInfo['depositacct'];
 					$oper_res = $this->fund_interface->bankcardDelete($post);
//  					file_put_contents('log/user/bankcard_delete'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n用户:".$_SESSION ['customer_name']."进行银行卡删除操作，返回数据为:".serialize($oper_res)."\r\n\r\n",FILE_APPEND);
 					myLog('user/bankcard_delete',"用户:".$_SESSION ['customer_name']."进行银行卡删除操作，返回数据为:".serialize($oper_res));
 					if (isset($oper_res['code']))
 					{
 						if ($oper_res['code'] =='0000'){
 							Message(Array(
 									'msgTy' => 'success',
 									'msgContent' => '银行卡注销成功!',
 									'msgUrl' => $this->base . "/jijin/Fund_bank/bank_info",
 									'base' => $this->base
 									));
 						}else{
 							switch ($oper_res['code']){
 								case '-409999999':
 									$err_msg = '密码输入错误';
 									break;
 								case '-440117034':
 								case '-440114020':
 									$err_msg = $oper_res['msg'];
 									break;
 								default:
 									$err_msg = '系统故障, 银行卡注销失败!';
 							}
 							$log_msg = $oper_res['msg'];
 						}
 					}else{
 						$log_msg = '调用删除银行卡接口失败';
 					}
 				}
 			}else{
 				$log_msg = "一次性随机验证码错误";
 			}
 			if (isset($log_msg)){
//  				file_put_contents('log/user/bankcard_delete'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n用户:".$_SESSION ['customer_name']."银行卡注销失败，原因为：".$log_msg."\r\n\r\n",FILE_APPEND);
 				myLog('user/bankcard_delete',"用户:".$_SESSION ['customer_name']."银行卡注销失败，原因为：".$log_msg);
 				Message(Array(
 						'msgTy' => 'fail',
 						'msgContent' => isset($err_msg) ? $err_msg : '系统故障, 银行卡注销失败!',
 						'msgUrl' => $this->base . "/jijin/Fund_bank/bank_info",
 						'base' => $this->base
 				));
 			}
 		}else{
 			$data['public_key'] = file_get_contents($this->config->item('RSA_publickey')); //获取RSA_加密公钥
 			$data['rand_code'] = "\t".mt_rand(100000,999999);                              //随机生成验证码
 			$data['channelid'] = $channelid;
 			$_SESSION['rand_code'] = $data['rand_code'];
 			$this->load->view('/jijin/bank/bankcard_delete',$data);
 		}
	}
	
	private function getBankCardInfo($channelid){
		$bank_info =$this->fund_interface->bankCardPhone();
		if (isset($bank_info['code']) && $bank_info['code'] == '0000'){
			foreach ($bank_info['data'] as $val){
				if ($val['channelid'] == $channelid){
					return $val;
				}
			}
		}
		return;
	}

}