<?php
if (!defined('BASEPATH')){
	exit('No direct script access allowed');
}
    
class Jz_account extends MY_Controller
{
    private $logfile_suffix;
    function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper(array("url","output","comfunction"));   
        $this->load->library(array('Fund_interface','Logincontroller'));
        $this->logfile_suffix = '('.date('Y-m',time()).').txt';
    }
    
	function register()
	{
		if (!empty($_SESSION['JZ_user_id'])) {
			if ($_SESSION ['JZ_user_id'] < 0)
			{
				$_SESSION['next_url'] = $this->base . "/jijin/Jz_my";
				redirect($this->base."/user/login");
			}else{
				redirect($this->base . "/jijin/Jz_fund");
			}
		}
		$res = $this->fund_interface->paymentChannel();
// 		file_put_contents('log/user/register'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n查询支付渠道返回数据:".serialize($res)."\r\n\r\n",FILE_APPEND);
		if ($res['code'] == '0000'){
			$this->load->config('jz_dict');
			$data['certificatetype'] = $this->config->item('certificatetype');
			$data['payment_channel'] = $res['data'];
			$data['public_key'] = file_get_contents($this->config->item('RSA_publickey')); //获取RSA_加密公钥
			$data['rand_code'] = "\t".mt_rand(100000,999999);                              //随机生成验证码
			$_SESSION['rand_code'] = $data['rand_code'];
			$data['provCity'] = json_encode($this->fund_interface->provCity());
			$this->load->view('jijin/account/bgMsgSend',$data);
		}
		else {
			Message(Array(
					'msgTy' => 'fail',
					'msgContent' => '<br/>注册失败，系统正在返回...',
					'msgUrl' => $this->base . '/jijin/Jz_my',
					'base' => $this->base
					));
		}
	}
    
	//基金开户银行卡鉴权
	function bgMsgSend()
	{
		$post = $this->input->post();
		//log注册提交的信息
// 		file_put_contents('log/user/register'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n用户:".$_SESSION ['customer_name']."注册post数据为:".serialize($post)."\r\n\r\n",FILE_APPEND);
		//-----------RSA解密----------------------------
		$private_key = openssl_get_privatekey(file_get_contents($this->config->item('RSA_privatekey')));
		$decryptData ='';
		openssl_private_decrypt(base64_decode($post['certificateno']),$decryptData, $private_key, OPENSSL_PKCS1_PADDING);
		//判断一次性随机验证码是否存在
		$div_bit = strpos($decryptData,(string)$_SESSION['rand_code']);
		if ($div_bit !== false){                      //找到一次性随机验证码
			//将解密后数据赋值给certificateno和depositacct，并对输入数据进行处理（删除头尾空格及银行卡号中间空格）
			$post['certificateno'] =  trim(substr($decryptData, 0, $div_bit));
			$post['depositacct'] =  trim(substr($decryptData, $div_bit+7));
			$post['depositacct'] =  str_replace(" ",'',$post['depositacct']);
			$post['depositacctname'] = trim($post['depositacctname']);
			$post['mobiletelno'] = trim($post['mobiletelno']);
			$_POST['certificateno'] = $post['certificateno'];
			$_POST['depositacct'] = $post['depositacct'];
				
			//--------以下设置客户输入错误提示--------------------------
			$this->load->library('form_validation');
			$this->form_validation->set_message('required', '%s不能为空.');
			$this->form_validation->set_message('max_length', '%s长度超出限制.');
			$this->form_validation->set_message('exact_length', '%s长度不符合要求.');
			$this->form_validation->set_message('numeric', '%s必须为数字.');
			
			//--------以下设置判断客户输入信息检测规则--------------------------
			if ($post['certificatetype'] == 0){
				$this->form_validation->set_rules('certificateno','身份证号码','required|exact_length[18]');
			}
			$this->form_validation->set_rules('depositacctname','银行帐户名','required|max_length[30]');
			$this->form_validation->set_rules('depositacct','银行卡号','required|max_length[30]|numeric');
			$this->form_validation->set_rules('mobiletelno','银行预留电话','required|max_length[20]|numeric');
			
			if ($this->form_validation->run() == TRUE)
			{
				//查询该用户或输入身份证已开户
				$seekAccount = $this->fund_interface->SeekAccount($post['certificatetype'],$post['certificateno']);
// $seekAccount['code'] = '0033';
// $seekAccount['custno'] = '37';
				if (key_exists('code', $seekAccount)){
					if ($seekAccount['code'] == '0000'){
						//调用银行鉴权接口及log
						$res_bMS = $this->fund_interface->bgMsgSend($post);
var_dump($res_bMS,$post);
						$logData = $post;
						$logData['certificateno'] = substr($post['certificateno'],0,6).'***'.substr($post['certificateno'],-3);
						$logData['depositacct'] = substr($post['depositacct'],0,3).'***'.substr($post['depositacct'],-3);
						$logData['depositacctname'] = substr($post['depositacctname'],0,3).'***';
						file_put_contents('log/user/register'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n用户:".$_SESSION ['customer_name']."调用bgMsgSend数据为:".serialize($logData)."\r\n调用bgMsgSend返回信息".serialize($post)."\r\n\r\n",FILE_APPEND);
						if ( !isset($res_bMS['code']) || $res_bMS['code'] != '0000' )        //鉴权失败  $res_bMS['data'][0]['comtype']表示该卡已经鉴权过
						{
							$err_msg = '银行鉴权失败';
						}
					}else{
						if ($seekAccount['code'] == '0033'){
							$info_msg = $seekAccount['msg'];
						}else{
							$err_msg = $seekAccount['msg'];
						}
					}
				}else{
					$err_msg = '系统故障，请稍后重试';
				}
				if ( !isset($err_msg) && !isset($info_msg))                                                             //判断是否鉴权成功
				{
					$_SESSION['register_data'] = array('channelid' => $post['channelid'],        //网点号(支付渠道)
							'channelname' => $post['channelname'],                               //网点名称
							'certificatetype' => $post['certificatetype'],                       //证件类型
							'certificateno' => $post['certificateno'],                           //身份证号
							'depositacctname' => $post['depositacctname'],                       //银行帐户名
							'depositacct' => $post['depositacct'],                               //银行卡号
							'mobileno' => $post['mobiletelno'],                                  //银行预留电话
					);
					$this->load_bgMsgCheck();
				}
			}
			else
			{
				$err_msg = validation_errors();
			}
		}else {
			$err_msg = '系统故障';
			$log_msg = '一次性随机验证码错误';
		}
		if (isset($err_msg))
		{
			$str = isset($log_msg)?$log_msg:$err_msg;
			file_put_contents('log/user/register'.$this->logfile_suffix, date('Y-m-d H:i:s',time()).":\r\n用户:".$_SESSION ['customer_name']."身份证为:".$post['certificateno']."开户失败原因为：".$str."\r\n\r\n",FILE_APPEND);
			Message(Array(
					'msgTy' => 'fail',
					'msgContent' => $err_msg.'<br/>注册失败，系统正在返回...',
					'msgUrl' => $this->base . '/jijin/Jz_my',
					'base' => $this->base
			));
		}
		if (isset($info_msg)){
			$str = isset($log_msg)?$log_msg:$info_msg;
			file_put_contents('log/user/register'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n用户:".$_SESSION ['customer_name']."身份证为:".$post['certificateno']."开户失败原因为：".$str."\r\n\r\n",FILE_APPEND);
			$arr = Array(
					'msgTy' => 'fail',
					'msgContent' => $info_msg,
					'msgUrl' => $this->base . '/jijin/Jz_account/open_phone_trans',
					'returnUrl' => $this->base . '/jijin/Jz_my',
					'base' => $this->base
			);
			$data_OPT = array('certificateno' => $post['certificateno'],'certificatetype' => $post['certificatetype'],'custno' => $seekAccount['custno']);
			$_SESSION['data_OPT'] = $data_OPT;								//通过session记录证件类型、证件号、基金帐号等信息
			Message_select('/jijin/account/info_OpenPhoneTtrans',$arr);
		}
	}
	
	//鉴权后基金开户
	function bgMsgCheck()
	{
		$post = $this->input->post();
		//记录开户时提交数据，裁减密码敏感信息。
		$tmp =$post;
		$tmp['lpasswd'] = substr($tmp['lpasswd'], 6,6);
		file_put_contents('log/user/register'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n用户:".$_SESSION ['customer_name']."开户输入数据为:".serialize($tmp)."\r\n\r\n",FILE_APPEND);
		//-----------RSA解密----------------------------
		$private_key = openssl_get_privatekey(file_get_contents($this->config->item('RSA_privatekey')));
		$decryptData ='';
		openssl_private_decrypt(base64_decode($post['lpasswd']),$decryptData, $private_key, OPENSSL_PKCS1_PADDING);
		//判断一次性随机验证码是否存在
		$div_bit = strpos($decryptData,(string)$_SESSION['rand_code']);
		if ($div_bit !== false){                      //找到一次性随机验证码
			$post['lpasswd'] =  substr($decryptData, 0, $div_bit);
			$post['tpasswd'] =  substr($decryptData, $div_bit+7);
			$_POST['lpasswd'] = $post['lpasswd'];
			$_POST['tpasswd'] = $post['tpasswd'];
			//--------以下设置客户输入错误提示--------------------------
			$this->load->library('form_validation');
			$this->form_validation->set_message('required', '%s不能为空.');
			$this->form_validation->set_message('max_length', '%s长度超出限制.');
			$this->form_validation->set_message('numeric', '%s必须为数字.');
			$this->form_validation->set_message('valid_email', '%s无效.');
			//--------以下设置判断客户输入信息检测规则--------------------------
			$this->form_validation->set_rules('verificationCode','短信验证码','required|numeric');
			$this->form_validation->set_rules('lpasswd','交易密码','required|max_length[20]');
			$this->form_validation->set_rules('tpasswd','交易密码','required|max_length[20]');
			$this->form_validation->set_rules('tpasswd','两次输入的交易密码不一致','matches[lpasswd]');
// 			$this->form_validation->set_rules('vailddate','证件有效期','required');
			$this->form_validation->set_rules('email','电子邮箱地址','valid_email');
			$this->form_validation->set_rules('postcode','邮编','numeric');
			$this->form_validation->set_rules('address','地址','max_length[100]');
			if ($this->form_validation->run() == TRUE)								//post数据合法性检查
			{
				$this->load->config('jz_dict');
// 				$this->load->library('fund_interface');
				//准备开户数据，并清除相关SESSION
				foreach ($_SESSION['register_data'] as $key=>$val)	{
					$post[$key] = $val;
				};
				unset($_SESSION['register_data']);
				if (empty($post['email'])) { unset($post['email']);};
				if (empty($post['postcode'])) { unset($post['postcode']);};
				if (empty($post['address'])) { unset($post['address']);};
				if (!isset($post['subbankno'])) {
					$post['subbankno'] = $post['channelid'];
				};
// 				$post['tano'] = $this->config->item('ta')[0]['no'];
				//查询基金公司信息
				$post['tano'] = $this->db->select("tano")->get('p2_fundlist')->row_array()['tano'];
				$post['custname'] = $post['depositacctname'];
				$post['bankname'] = '招商银行';
				$post['depositprov'] = '广东';
				$post['depositcity'] = '深圳';
				//调用金证开户接口,并记录调用金证接口数据及返回结果(去除密码部分)
				file_put_contents('log/user/register'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n用户:".$_SESSION ['customer_name']."调用金证bgMsgCheck数据为:".serialize($post),FILE_APPEND);
				$res_bMC = $this->fund_interface->bgMsgCheck($post);
				$post['tpasswd'] = $post['lpasswd'] = '***';
				file_put_contents('log/user/register'.$this->logfile_suffix,"\r\n调用bgMsgCheck接口返回数据为：".serialize($res_bMC)."\r\n\r\n",FILE_APPEND);
				if (isset($res_bMC['code']) && isset($res_bMC['code'])== '0000' && isset($res_bMC['data'][0][0]['custno']))        //判断调用金证接口开户是否成功    isset($res_bMC['code']) && $res_bMC['code'] == '0000'
				{
					//准备数据，并写入数据库
					$insert_data = array(
							'JZ_account' => $res_bMC['data'][0][0]['custno'],
							'XN_account' => $_SESSION ['customer_name'],
							'certificateno' => $post['certificateno'],
							'depositacctname' => $post['depositacctname'],
							'depositacct' => $post['depositacct'],
							'mobileno' => $post['mobileno'],
							'authority' => 1,
							'moneyaccount' => $res_bMC['data'][0][0]['moneyaccount'],
							'transactionaccountid' => $res_bMC['data'][1][0]['transactionaccountid'],
							'tpasswd' => my_md5($_SESSION ['customer_name'], $_POST['tpasswd']),
							'platform'=>'XCF',
					);
					$insert_res = $this->db->insert('jz_account',$insert_data);   //写入数据库
					//依据数据库写入操作成功与否给予用户成功的提示，并log记录
					if ($insert_res){
						//设置SESSION用于标识已登录
						$_SESSION['JZ_user_id'] = $this->db->insert_id();
						$_SESSION['JZ_account'] = $res_bMC['data'][0][0]['custno'];
						file_put_contents('log/user/register'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n用户:".$_SESSION ['customer_name']."用户基金开户成功，数据库写入成功".serialize($insert_data)."\r\n\r\n",FILE_APPEND);
						Message(Array(
								'msgTy' => 'sucess',
								'msgContent' => '注册成功',
								'msgUrl' => $this->base . '/jijin/Jz_my', //调用我的基金界面
								'base' => $this->base
								));
						exit;
					}else{
						$err_msg = '系统故障';
						$log_msg = "用户开户成功,写入数据".serialize($insert_data)."失败,失败原因：".serialize($this->db->error());
					}
				}
				else{                                      //调用金证接口开户失败
						$err_msg = '系统故障';
						$log_msg = 'bgMsgCheck接口调用失败';
				}
			}
			else                 //输入post数据未通过合法性检查
			{
				$err_msg = validation_errors();
			}
		}
		else {
			$err_msg = '系统故障';
			$log_msg = "一次性随机验证码错误";
		}

		//开户失败时，给客户的提示信息，并log
		if (!empty($err_msg))
		{
			$str = isset($log_msg)?$log_msg:$err_msg;
			file_put_contents('log/user/register'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n用户:".$_SESSION ['customer_name']."开户失败，原因为：".$str."\r\n\r\n",FILE_APPEND);
			Message(Array(
					'msgTy' => 'fail',
					'msgContent' => $err_msg.'<br/>基金开户失败，系统正在返回...',
					'msgUrl' => $this->base .'/jijin/Jz_my',
					'base' => $this->base
			));
		}
	}
	
//-------------------- 加载bgMsgCheck页面 -----------------------------------------------------
	private function load_bgMsgCheck()
	{
		$data['public_key'] = file_get_contents($this->config->item('RSA_publickey')); //获取RSA_加密公钥
		$data['rand_code'] = "\t".mt_rand(100000,999999);                              //随机生成验证码
		$_SESSION['rand_code'] = $data['rand_code'];
		$this->load->view('jijin/account/bgMsgCheck',$data);
	}


	
	function logout()
	{
		$this->logincontroller->logout();
	}

//------------ 获取直销客户开通手机交易后写入数据库的数据 ----------------------------------   
    private function data_openphonetrans($arr,$logfile){
    	//查询客户是否有已鉴权的银行卡，有的话记录相关信息
    	$bank_info = $this->fund_interface->auth_bankcard(0, $arr['JZ_account'],1);
    	//log查询客户是否有已鉴权的银行卡返回信息
    	file_put_contents($logfile,date('Y-m-d H:i:s',time()).":\r\n 用户:".$_SESSION ['customer_name']."客户号为".$arr['JZ_account']."的用户查询个人及银行卡(auth_bankcard接口)信息返回数据".serialize($bank_info)."\r\n\r\n",FILE_APPEND);
    	if ($bank_info['code'] == '0000' && isset($bank_info['data'][0]['custno'])){
    		$arr['depositacctname'] = $bank_info['data'][0]['depositacctname'];
    		$arr['depositacct'] = $bank_info['data'][0]['depositacct'];
//     		$arr['moneyaccount'] = $bank_info['data'][0]['moneyaccount'];            //非网上开户，不记录是合理的
    	}
    	return $arr;
    }

//-------------------      直销客户开通手机交易      --------------------------------------------------
    function open_phone_trans(){
    	$post = $this->input->post();
    	if(!empty($post) && isset($post['lpasswd'])){                   //通过isset($post['lpasswd'])存在判断调用是来自界面OpenPhoneTtrans，否则来自info_OpenPhoneTtrans
    		//----------- 记录post输入数据(其对包含密码的部分进行裁减)---------------------
    		$tmp = $post;
    		$tmp['lpasswd'] = '裁减后:'.substr($post['lpasswd'], 3,6);
    		file_put_contents('log/user/OpenPhoneTtrans'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n用户:".$_SESSION ['customer_name']." post数据".serialize($tmp)."\r\n\r\n",FILE_APPEND);
    		//-----------RSA解密----------------------------
    		$private_key = openssl_get_privatekey(file_get_contents($this->config->item('RSA_privatekey')));
    		$decryptData ='';
    		openssl_private_decrypt(base64_decode($post['lpasswd']),$decryptData, $private_key, OPENSSL_PKCS1_PADDING);
    		//判断一次性验证码是否存在
    		$div_bit = strpos($decryptData,(string)$_SESSION['rand_code']);
    		$openPhoneTrans = $_SESSION['data_OPT'];
$openPhoneTrans['certificateno'] =	'430104197704182539';
$openPhoneTrans['custno'] =	'37';
    		unset($_SESSION['data_OPT']);
    		unset($_SESSION['rand_code']);
    		if ($div_bit !== false){                      //找到一次性验证码
    			//----------- 记录解密后post数据 ---------------------
    			file_put_contents('log/user/OpenPhoneTtrans'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n用户:".$_SESSION ['customer_name']." 解密后数据：".serialize($post)."\r\n\r\n",FILE_APPEND);
    			$openPhoneTrans['lpasswd'] = substr($decryptData, 0, $div_bit);
    			//开通用户手机交易功能
    			$res = $this->fund_interface->openPhoneTrans($openPhoneTrans);
// $data['data'] = $res['data'];
// var_dump($res);
// $data['url'] = $this->config->item('fundUrl').'/jijin/XCFinterface';
// $this->load->view('UrlTest',$data);
// var_dump($res);
// return;
    			//log开通用户手机交易返回信息
    			file_put_contents('log/user/OpenPhoneTrans'.$this->logfile_suffix,date('Y-m-d H:i:s',time()). ":\r\n用户:".$_SESSION ['customer_name']."开通用户手机交易功能(open_phone_trans接口)返回数据".serialize($res)."\r\n\r\n",FILE_APPEND);
    			if ($res['code'] == '0000'){
    				Message(Array(
    						'msgTy' => 'sucess',
    						'msgContent' => '开通手机交易成功',
    						'msgUrl' => $this->base . '/jijin/Jz_my',                  //调用my界面
    						'base' => $this->base
    						));
    			}else{
    				if ($res['code'] == '0016'){
    					$message = '交易密码输入错误，请重试！';
    				}
    			}
    		}else{
    			$log_message = '一次性随机验证码未找到';
    		}
    		if (isset($log_message)){
    			file_put_contents('log/user/OpenPhoneTrans'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n用户:".$_SESSION ['customer_name']."开通手机交易失败，失败原因：".$log_message."\r\n\r\n",FILE_APPEND);
    		}
    		if (!isset($message)){
    			$message = '开通手机交易失败，系统正在返回';
    		}
    		Message(Array(
    				'msgTy' => 'fail',
    				'msgContent' => $message,
    				'msgUrl' => $this->base . '/jijin/Jz_my',                           //调用my界面
    				'base' => $this->base
    				));
    	}
    	else                                                                                   //调用来自info_OpenPhoneTtrans
    	{
    		$data['public_key'] = file_get_contents($this->config->item('RSA_publickey'));     //获取RSA_加密公钥
    		$data['rand_code'] = "\t".mt_rand(100000,999999);                                  //随机生成验证码
    		$_SESSION['rand_code'] = $data['rand_code'];
    		$this->load->view('/jijin/account/OpenPhoneTtrans',$data);
    	}
    }

//---------------- 修改基金帐户密码或交易密码 ----------------------------------------------------------------------
    function revise_passward($pwdtype)                              //$pwdtype='0'交易密码、= '1'登陆密码
    {
    	if (!$this->logincontroller->isLogin()) {
    		exit;
    	}
    	$post = $this->input->post();
    	if (!empty($post))
    	{
    		$str_info = $post['pwdtype']==1?'登录':'交易';
    		//-----------RSA解密----------------------------
    		$private_key = openssl_get_privatekey(file_get_contents($this->config->item('RSA_privatekey')));
    		openssl_private_decrypt(base64_decode($post['oldpwd']),$decryptData, $private_key, OPENSSL_PKCS1_PADDING);
    		//判断一次性随机验证码是否存在
    		$div_bit = strpos($decryptData,(string)$_SESSION['rand_code']);
    		unset($_SESSION['rand_code']);
    		if ($div_bit !== false){                      //找到一次性随机验证码
    			$oldpwd = substr($decryptData, 0, $div_bit);
    			$newpwd = substr($decryptData, $div_bit+7);
    			$res = $this->fund_interface->revisePassward($oldpwd, $newpwd, $post['pwdtype']);
    			file_put_contents('log/user/revise_passward'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n用户:".$_SESSION ['customer_name']."修改".$str_info.'密码，调用接口返回数据为'.serialize($res)."\r\n\r\n",FILE_APPEND);
    			if (isset($res['code']) && $res['code'] == '0000')
    			{
    				Message(Array(
    						'msgTy' => 'sucess',
    						'msgContent' => $str_info.'密码修改成功',
    						'msgUrl' => $this->base . "/jijin/Jz_my",
    						'base' => $this->base
    						));
    			} else {
    				$log_message = serialize($res);
    			}
    		}else{
    			$log_message = '一次性随机验证码错误';
    		}
    		if (isset($log_message)){
    			file_put_contents('log/user/revise_passward'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n用户:".$_SESSION ['customer_name']."修改".$str_info.'密码失败，原因为'.$log_message."\r\n\r\n",FILE_APPEND);
    			if (strpos($log_message,'密码错误') !== false){
    				$message = '旧密码输入错误，请重试！';
    			}else{
    				$message = '修改'.$str_info.'密码失败';
    			}
    			Message(Array(
    					'msgTy' => 'fail',
    					'msgContent' => $message,
    					'msgUrl' => $this->base . "/jijin/Jz_my",
    					'base' => $this->base
    					));
    		}
    	}else{
    		$data['pwdtype'] = $pwdtype;
    		$data['public_key'] = file_get_contents($this->config->item('RSA_publickey')); //获取RSA_加密公钥
    		$data['rand_code'] = "\t".mt_rand(100000,999999);                              //随机生成验证码
    		$_SESSION['rand_code'] = $data['rand_code'];
    		$this->load->view('jijin/account/revise_passward', $data);
    	}
    }
    
    /*
     * 和登录系统的接口，登录系统由此进入基金系统
     * */
    function entrance()
    {
    	$this->logincontroller->entrance();
    }

}
