<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );
header ( "Content-type: text/html; charset=utf-8" );

class User extends MY_Controller {
	private $logfile_suffix;
	function __construct() {
		parent::__construct ();
		$this->load->helper ( array("output","url",'comfunction'));
		$this->load->model("Model_db");
		$this->logfile_suffix = '('.date('Y-m',time()).').txt';
	}

	private function userinfo(){
		if (!isset($_SESSION['customer_id'])) {
			redirect($this->base . "/user/login/");
		}
	}
	
	function login() {
		if (isset ( $_SESSION ['customer_id'] )) {
			redirect ( $this->base . "/User/home/");
		}
		$post = $this->input->post ();
		if (! empty ( $post )){
			if (empty ( $post ['T_pwd'] )){
				$fail_message = '密码不能为空，系统正在返回...';
			}
			else{
				$private_key = openssl_get_privatekey(file_get_contents($this->config->item('RSA_privatekey')));
				$decryptData ='';
				openssl_private_decrypt(base64_decode($post['T_pwd']),$decryptData, $private_key, OPENSSL_PKCS1_PADDING);
				$div_bit = strpos($decryptData,(string)$_SESSION['rand_code']);
				unset($_SESSION['rand_code']);
				if ($div_bit)	{
					$post ['T_name'] = substr($decryptData, 0, $div_bit);
					if (!preg_match ('/^[1][34578][0-9]{9}$/', $post ['T_name'])) {
						$fail_message = '输入用户名不正确，系统正在返回...';
					}
					else {
						$T_pwd = substr($decryptData, $div_bit+7);
						if(empty($T_pwd))
						{
							$fail_message = '密码不能为空，系统正在返回...';
						}
						else
						{
							$post['T_pwd'] = MD5($T_pwd);
						}
					}
				}else{
					$fail_message = '随机校验码错误，系统正在返回...';
				}
			}
			if (isset($fail_message))	{
				Message ( Array (
						'msgTy' => 'fail',
						'msgContent' => $fail_message,
						'msgUrl' => $this->base . "/user/login/",
						'base' => $this->base
						) );
			}
			if (! empty ( $post ) && preg_match ( '/^[1][34578][0-9]{9}$/', $post ['T_name'] )) {
				$T_name = trim($post["T_name"]);
				$T_pwd = $post ["T_pwd"];
				$passkey = $this->config->item ( 'passkey' );
				$T_pwd = MD5 ( MD5 ( $passkey ) . substr ( $T_pwd, 5, 20 ) );
				$info = $this->db->where(array('Customername'=>$T_name))->get('customer')->row_array();	
				if ($info ['Customername'] == $T_name && $info ['Password'] == $T_pwd) {
					// $info = $this->model_common->db_one_str('customer', "((Username = '" . $T_name . "') or (tel = '" . $T_name . "') or (email = '" . $T_name . "')) and Password = '" . $T_pwd . "'");
					if ($info ['status'] == 0) {
						Message ( Array (
								'msgTy' => 'fail',
								'msgContent' => '该用户已被屏蔽，系统正在返回...',
								'msgUrl' => $this->base . "/user/login/",
								'base' => $this->base
								) );
					}
					$_SESSION ['customer_id'] = $info ['id'];
					$_SESSION ['customer_name'] = $info ['Customername'];
					if (isset($_SESSION['next_url'])){
						redirect ($_SESSION['next_url']);
						unset($_SESSION['next_url']);
					}else{
						redirect ($this->base . "/User/home/");
					}
					exit ();
				}
			}
			Message ( Array (
					'msgTy' => 'fail',
					'msgContent' => '账户名或密码不正确，系统正在返回...',
					'msgUrl' => $this->base . "/user/login/",
					'base' => $this->base
					) );
		}else {
			$data['public_key'] = file_get_contents($this->config->item('RSA_publickey'));   //获取RSA_加密公钥
			$_SESSION['rand_code'] = $data['rand_code'] = "\t".mt_rand(100000,999999);                                     //随机生成验证码
			$this->load->view ( 'user/login', $data);
		}
	}
	
	function home(){
		$this->getRecommendFunds($data);
		$this->load->view('index',$data);
	}
	
	function register() {
		if (isset ( $_SESSION ['customer_id'] )) {
			redirect ( $this->base . "/User/home");
			exit ();
		}
		$post = $this->input->post();
		if (! empty ( $post )) {
			if (empty ( $post['sms_code'] ) || strtolower ( $_SESSION ['telcode'] ) != strtolower ( $post['sms_code'] )) {
				$fail_message = '验证码不正确，系统正在返回...';
			}
			if (!isset($fail_message) && empty ( $post ['pwd'] )){
				$fail_message = '密码不能为空，系统正在返回...';
			}else {
				$private_key = openssl_get_privatekey(file_get_contents($this->config->item('RSA_privatekey')));
				$decryptData ='';
				openssl_private_decrypt(base64_decode($post['pwd']),$decryptData, $private_key, OPENSSL_PKCS1_PADDING);
				$div_bit = strpos($decryptData,(string)$_SESSION['rand_code']);
				unset($_SESSION['rand_code']);
				if ($div_bit)
				{
					$T_name = substr($decryptData, 0,$div_bit);
					$post ['login_pwd'] = substr($decryptData, $div_bit+7);
					if (empty($T_name) || !preg_match('/1[34578]{1}\d{9}$/',$T_name))
					{
						$fail_message = '手机号格式不正确，系统正在返回...';
					}elseif($T_name != $_SESSION['T_name']){
						$fail_message = '输入的手机号码和短信验证的手机号码不一致，系统正在返回...';
					}elseif(empty($post['login_pwd'])){
						$fail_message = '请输入登录密码，系统正在返回...';
					}
				}
				else {
					$fail_message = '随机校验码错误，系统正在返回...';
				}
				unset($_SESSION['T_name']);
			}
			if (!isset($fail_message)) {
				$user_data = $this->db->where(Array ('Customername' => $T_name))->get('customer')->row_array();
				if (!empty ( $user_data )) 
				{
					$fail_message = '手机号已经存在，系统正在返回...';
				}
			}
			if (!isset($fail_message)){
				$T_pwd = $post ["login_pwd"];
				$passkey = $this->config->item ( "passkey" );
				$T_pwd = MD5 ( MD5 ( $passkey ) . substr ( MD5($T_pwd), 5, 20 ) );
				$arr = Array (
						"Customername" => $T_name,
						"Password" => $T_pwd,
						"addtime" => time(),
						"status" => 1,
						);
				if (! empty ( $arr ['register_openid'] )) {
					$wxApi = new Api ();
					$userInfo = $wxApi->getUserInfo ( $arr ['register_openid'] );
					if (! empty ( $userInfo )) {
						$arr ['headImg'] = $userInfo ['headimgurl'];
						$arr ['sex'] = $userInfo ['sex'];
						$arr ['nick_name'] = $userInfo ['nickname'];
						$arr ['province'] = $userInfo ['province'];
						$arr ['city'] = $userInfo ['city'];
					}
				}
				$res = $this->db->set($arr)->insert('customer');
				if ($res) {
					Message ( Array (
							'msgTy' => 'success',
							'msgContent' => '注册成功，系统正在返回...',
							'msgUrl' => $this->base . '/user/login/',
							'base' => $this->base
							) );
				}else {
					$fail_message = '注册失败，系统正在返回...';
				}
			}
			if (isset($fail_message)){
				Message ( Array (
						'msgTy' => 'fail',
						'msgContent' => $fail_message,
						'msgUrl' => $this->base . '/user/register/',
						'base' => $this->base
						) );
			}
		}else{
// 			if (ISTESTING) {
				$data['public_key'] = file_get_contents($this->config->item('RSA_publickey'));   //获取RSA_加密公钥
				$_SESSION['rand_code'] = $data['rand_code'] = "\t".mt_rand(100000,999999);                                     //随机生成验证码
				$this->load->view ( 'user/register' , $data);
// 			}
/* 			else {
				redirect ( '/weixin/oauth/register/');
			}
 */		}
	}

	function logout() {
		if (isset ( $_SESSION ['customer_id'] )) {
			unset ( $_SESSION ['customer_id'] );
		}
		if (isset ( $_SESSION ['customer_name'] )) {
			unset ( $_SESSION ['customer_name'] );
		}
		if (isset($_SESSION['JZ_user_id'])) {
			unset($_SESSION['JZ_user_id']);
		}
		session_destroy ();
		redirect ( $this->base . "/user/login");
	}
	
	// 找回密码
	function findPass() {
		$post = $this->input->post();
		if (!empty($post)) {
			if (empty ( $post['sms_code'] ) || strtolower ( $_SESSION ['telcode'] ) != strtolower ( $post['sms_code'] )) {
				$failmessage = '验证码不正确，系统正在返回...';
			}else{
				if (empty($post['tel'])){
					$failmessage = '用户名不能为空，系统正在返回...';
				}else{
					$user = $this->db->where(array('Customername' => $post['tel']))->from('customer')->count_all_results();
					if ($user == 0) {
						$failmessage = '不存在此用户，系统正在返回...';
					}
				}
			}
			if (!isset($failmessage)){
				$private_key = openssl_get_privatekey(file_get_contents($this->config->item('RSA_privatekey')));
				$decryptData ='';
				openssl_private_decrypt(base64_decode($post['pwd']),$decryptData, $private_key, OPENSSL_PKCS1_PADDING);
				$div_bit = strpos($decryptData,(string)$_SESSION['rand_code']);
				unset($_SESSION['rand_code']);
				if ($div_bit){
					$passkey = $this->config->item ( "passkey" );
					$newPassword = substr($decryptData, 0,$div_bit);
					$T_pwd = MD5 ( MD5 ( $passkey ) . substr ( MD5 ( $newPassword ), 5, 20 ) );
					$res = $this->db->set(array('Password' => $T_pwd, 'updatetime' => time()))->where(Array ('Customername' => $post['tel']))->update('customer');
					if ($res) {
						Message ( Array (
								'msgTy' => 'success',
								'msgContent' => '密码修改成功，系统正在返回...',
								'msgUrl' => $this->base . '/user/login',
								'base' => $this->base
								) );
					} else {
						$failmessage = '密码修改失败，系统正在返回...';
						$jumpUrl = $this->base . '/user/findPass';
					}
				}else{
					$failmessage = '密码修改失败，系统正在返回...';
				}
			}
			if (!isset($jumpUrl)){
				$jumpUrl = $this->base . $_SERVER ['REQUEST_URI'];
			}
			Message ( Array (
					'msgTy' => 'fail',
					'msgContent' => $failmessage,
					'msgUrl' => $jumpUrl,
					'base' => $this->base
					) );
		}else{
			$data['public_key'] = file_get_contents($this->config->item('RSA_publickey'));   //获取RSA_加密公钥
			$_SESSION['rand_code'] = $data['rand_code'] = "\t".mt_rand(100000,999999);
			$this->load->view('user/ret_password',$data);
		}
	}
	
	function updatePass()
	{
		$this->userinfo();
		$post = $this->input->post();
		if (!empty($post)) {
			$private_key = openssl_get_privatekey(file_get_contents($this->config->item('RSA_privatekey')));
			$decryptData ='';
			openssl_private_decrypt(base64_decode($post['newPass']),$decryptData, $private_key, OPENSSL_PKCS1_PADDING);
			$div_bit = strpos($decryptData,(string)$_SESSION['rand_code']);
			if ($div_bit)
			{
				unset($_SESSION['rand_code']);
				$T_pwd = MD5(substr($decryptData, 0,$div_bit));
				$new_login_pwd = MD5(substr($decryptData, $div_bit+7));
				$user_data = $this->db->where(array("id" => $_SESSION ['customer_id']))->get('customer')->row_array();
				$passkey = $this->config->item("passkey");
				$T_pwd = MD5(MD5($passkey) . substr($T_pwd, 5, 20));
				if ($user_data['Password'] != $T_pwd) {
					$fail_message = '账户旧密码不正确，系统正在返回...';
					$redirect_url = "/User/updatePass/";
				}
				else
				{
					$new_login_pwd = MD5(MD5($passkey) . substr($new_login_pwd, 5, 20));
					$arr = array(
							'updatetime' => time(),
							'Password' => $new_login_pwd,
					);
					$res = $this->db->set($arr)->where(Array('id' => $_SESSION ['customer_id']))->update('customer');
					if ($res) {
						Message(Array(
								'msgTy' => 'success',
								'msgContent' => '账户密码修改成功，系统正在返回...',
								'msgUrl' => $this->base . "/user/logout",
								'base' => $this->base
								));
						exit;
					}
					else
					{
						$fail_message = '账户密码修改失败，系统正在返回...';
						$redirect_url = "/User/home";
					}
				}
			}
			else
			{
				$fail_message = '随机验证码错误，请重试！';
				$redirect_url = "/member/updatePass";
			}
			Message(Array(
					'msgTy' => 'fail',
					'msgContent' => $fail_message,
					'msgUrl' => $this->base . $redirect_url,
					'base' => $this->base
					));
		} else
			$data['public_key'] = file_get_contents($this->config->item('RSA_publickey'));   //获取RSA_加密公钥
			$data['rand_code'] = "\t".strval(mt_rand(100000,999999));                                     //随机生成验证码
			$_SESSION['rand_code'] = $data['rand_code'];
			$this->load->view('user/xglogin_password', $data);
	}
	public function send_sms() { //牛鼎丰的短信发送渠道 
		$post = $this->input->post ();
		if (empty ( $post ['tel'] )) {
			echo '手机号不能为空'; exit;
		}
		if (strlen ( $post ['tel'] ) > 11) {
			echo '手机号错误'; exit;
		}
		$curtomerInfo = $this->db->where('Customername',$post ['tel'])->get('p2_customer')->row_array();
		if (!empty($curtomerInfo)){
			echo '该手机号已注册'; exit;
		}
		if (isset($_SESSION['send_sms'])){
			$timediff = time() - $_SESSION['send_sms'];
			if ($timediff < 60){
				echo '短信验证码已经发送,如未收到请在'.(60 - $timediff).'秒后重试';
				exit;
			}else{
				$_SESSION['send_sms'] = time();
			}
		}else{
			$_SESSION['send_sms'] = time();
		}
		if (ISTESTING) {
			$_SESSION ['telcode'] = $telcode = '1234';
			echo '您的验证码为:1234';
		}else{
			$_SESSION ['telcode'] = $telcode = $this->TelCode();
			$_SESSION ['T_name'] = $post ['tel'];
			$content = "您的验证码是:" . $telcode;
			$res2 = $this->NDFsendSms ( $post ['tel'], $content );
			if($res2===false){
				$res ['returnCode']=999999;
			}
			else{
				$res = json_decode ( $res2, TRUE );
			}
			switch ($res ['returnCode']) {
				case 0 :
					$result = '验证码已发送！';
					break;
				case 130001 :
					$result = '参数为空';
					break;
				case 130002 :
					$result = '手机号码格式错误';
					break;
				case 130003 :
					$result = '签名错误';
					break;
				case 130004 :
					$result = '短信服务器内部错误';
					break;
				case 130005 :
					$result = '找不到业务的信息';
					break;
				case 130006 :
					$result = '业务下找不到模块的信息';
					break;
				case 130011 :
					$result = '验证码验证失败';
					break;
				case 130012 :
					$result = '验证码已过期';
					break;
				case 130013 :
					$result = '错误次数超过3次';
					break;
				case 130021 :
					$result = '实际号码个数超过100';
					break;
				case 130022 :
					$result = '短信发送失败';
					break;
				case 130023 :
					$result = '请设置短信模板或消息模板';
					break;
				case 130031 :
					$result = '推送消息失败';
					break;
				case 999999 :  //网络连接不上自定义代码,前端显示给用户或运维同事
					$result = '网络超时或短信系统没反应';
					break;
				default :
					$result = '';
					break;
			}
			echo $result;
		}
	}
		// 发短信
	private function NDFsendSms($mobile, $content = '') {
		// $this->check_login();
		if (empty ( $mobile )) {
			return false;
		}
		// $this =& get_instance();
		$ISTESTING = false;
		if ($ISTESTING) {
			$sms_url = $this->config->item ( 'test_sms_url' );
			$sms_signature_key = $this->config->item ( 'test_sms_signature_key' );
			$partnerId = $this->config->item ( 'test_partnerId' );
			$moduleId = $this->config->item ( 'test_moduleId' );
		} else {
			$sms_url = $this->config->item ( 'sms_url' );
			$sms_signature_key = $this->config->item ( 'sms_signature_key' );
			$partnerId = $this->config->item ( 'partnerId' );
			$moduleId = $this->config->item ( 'moduleId' );
		}
		$signTextTemp = "mobile=$mobile&moduleId=$moduleId&partnerId=$partnerId&value=$content" . $sms_signature_key;
		$signature = sha1 ( $signTextTemp );
		$post_data = "partnerId=$partnerId&mobile=$mobile&signature=$signature&value=$content&moduleId=SENDSMS";
		$this->load->helper('comfunction');
		$ret = comm_curl($sms_url,$post_data );
		return $ret;
	}
	
//获取最新的基金列表
	private function getRecommendFunds(&$data){
		$this->load->library('Fund_interface');
		$this->fund_interface->fund_list();
 		$select = '';
		$arr = array('fundcode','tano','fundname','fundtype','nav','growthrate','fundincomeunit','shareclasses','risklevel','status');
		foreach ($arr as $val){
			$select .= $val.',';
		}
		$select = substr($select,0,-1);
		$arr = array('select'=>$select);
		$this->load->config('jz_dict');
		$RecommendFunds = $this->db->where(array('recommend' => 1))->get('fundlist')->result_array();
		$totalFunds = count($RecommendFunds);
		if ($totalFunds <3){
			$RecommendFunds = $this->db->get('fundlist')->result_array();
			$totalFunds = count($RecommendFunds);
		}
		foreach ($RecommendFunds as $key => $val){
			foreach ($val as $k => $v){
				switch ($k){
/* 					case 'tano':
						$RecommendFunds[$key]['tano'] = $v.'/'.$val['taname'];
						break; */
					case 'fundtype':
						$RecommendFunds[$key][$k] = $this->config->item('fundtype')[$v];
						break;
					case 'shareclasses':
/* 						$RecommendFunds[$key][$k] = $this->config->item('sharetype')[$v];
						break;
					case 'risklevel':
						$RecommendFunds[$key][$k] = $this->config->item('custrisk')[intval($v)];
						break;
					case 'status':
						$RecommendFunds[$key][$k] = $this->config->item('fund_status')[intval($v)]['status'];
						break; */
				}
			}
		}
		if ($totalFunds > 0){
			$RecommendNum = $totalFunds >3 ? 3 : $totalFunds;
			$randSeq = array_rand(range(0,$totalFunds-1),$RecommendNum);
			for($i = 0; $i<$RecommendNum; $i++){
				$data['Recommend'][$i]['fundname'] = $RecommendFunds[$randSeq[$i]]['fundname'];
				$data['Recommend'][$i]['fundtype'] = $RecommendFunds[$randSeq[$i]]['fundtype'];
				$data['Recommend'][$i]['growthrate'] = ($RecommendFunds[$randSeq[$i]]['growthrate']*100).'%';
			}
		}
	}
	
	function commingsoon()
	{
		$this->load->view('commingsoon');
	}
	
	function TelCode() {
		$arr = Array(
				0,
				1,
				2,
				3,
				5,
				6,
				8,
				9
				);
		$randNum = "";
		for ($i = 0; $i < 4; $i++) {
			$randKey = mt_rand(0, 7);
			$randNum .= $arr[$randKey];
		}
		return $randNum;
	}
	
}