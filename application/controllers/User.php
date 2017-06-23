<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );
header ( "Content-type: text/html; charset=utf-8" );
include_once 'weixin/Api.php';

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
		if (!empty ($post['T_pwd'])){
			$private_key = openssl_get_privatekey(file_get_contents($this->config->item('RSA_privatekey')));
			$decryptData ='';
			openssl_private_decrypt(base64_decode($post['T_pwd']),$decryptData, $private_key, OPENSSL_PKCS1_PADDING);
			$div_bit = strpos($decryptData,(string)$_SESSION['loginRandCode']);
			unset($_SESSION['loginRandCode']);
			if ($div_bit){
				$T_name = trim(substr($decryptData, 0, $div_bit));
				$T_pwd = substr($decryptData, $div_bit+7);
				$user_info = $this->db->where(array('Customername'=>$T_name))->get('customer')->row_array();
				if (!empty($user_info)){
					if ($user_info['status'] != 0){
						$T_pwd = MD5($T_pwd);
						$passkey = $this->config->item ( 'passkey' );
						$T_pwd = MD5 ( MD5 ( $passkey ) . substr ( $T_pwd, 5, 20 ) );
						$trytimes = (time()-$user_info['logintime']>3600) ? 0 : $user_info['trytimes'];
						if ($trytimes < 3){
							var_dump($T_name);
							var_dump($T_pwd);
							if ($user_info ['Customername'] == $T_name && $user_info ['Password'] == $T_pwd) {
								$_SESSION ['customer_id'] = $user_info ['id'];
								$_SESSION ['customer_name'] = $user_info ['Customername'];
								$this->db->set(array('trytimes'=>0,'logintime'=>time()))->where(array('id'=>$user_info['id']))->update('customer');
								if (isset($_SESSION['next_url'])){
									$next_url = $_SESSION['next_url'];
									unset($_SESSION['next_url']);
									redirect ($next_url);
								}else{
									redirect ($this->base . "/User/home/");
								}
								exit ();
							}else{
								$trytimes ++;
								$this->db->set(array('trytimes'=>$trytimes,'logintime'=>time()))->where(array('id'=>$user_info['id']))->update('customer');
								$fail_message = (3-$trytimes) > 0 ? '密码错误，还可重试'.(3-$trytimes).'次' : '密码错误，请于1小时后重试';
							}
						}else{
							$fail_message = '密码错误次数超过3次，请'.intval((3600+$user_info['logintime']-time())/60).'分钟后重试';
						}
				
					}else{
						$fail_message = '该用户已被屏蔽，系统正在返回...';
					}
				}else{
					$fail_message = '用户不存在，系统正在返回...';
				}
			}else{
				$fail_message = '系统错误，正在返回...';
			}
			if (isset($fail_message))	{
				Message ( Array (
						'msgTy' => 'fail',
						'msgContent' => $fail_message,
						'msgUrl' => $this->base . "/user/login/",
						'base' => $this->base
						) );
			}
		}else {
			$data['public_key'] = file_get_contents($this->config->item('RSA_publickey'));   //获取RSA_加密公钥
			$_SESSION['loginRandCode'] = $data['rand_code'] = "\t".mt_rand(100000,999999);                                     //随机生成验证码
			$this->load->view ( 'user/login', $data);
		}
	}


	function home(){
		if (ISTESTING) {
			$this->getRecommendFunds($data);
			$this->load->view('index',$data);
		}
		else 
			redirect('/weixin/oauth/checkwxaccess');
	}
	
	function homeaccess(){
  		$data['headimgurl']=$_SESSION['headimgurl'];
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
			}else{
				$this->db->query('UPDATE `p2_dealitems` SET `times` = `times`-1 WHERE `dealitem` = "sendSms"');
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
						"planner_id" => !empty($post['planner_id']) ? $post['planner_id'] : '',
						);
				if (!ISTESTING) {
					if (! empty ($_SESSION['open_id'])) {
						$wxApi = new Api ();
						$userInfo = $wxApi->getUserInfo ( $_SESSION['open_id']);
						if (! empty ( $userInfo )) {
							$arr ['register_openid']=$_SESSION['open_id'];
							$arr ['headImg'] = $userInfo ['headimgurl'];
							$arr ['sex'] = $userInfo ['sex'];
							$arr ['nick_name'] = $userInfo ['nickname'];
							$arr ['province'] = $userInfo ['province'];
							$arr ['city'] = $userInfo ['city'];
						}
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
				$this->db->query('UPDATE `p2_dealitems` SET `times` = `times`-1 WHERE `dealitem` = "sendSms"');
				if (empty($post['tel'])){
					$failmessage = '用户名不能为空，系统正在返回...';
				}else{
					if($post['tel'] != $_SESSION['T_name']){
						$failmessage = '输入的手机号码和短信验证的手机号码不一致，系统正在返回...';
						$redirect_url = "/User/updatePass/";
					}else{
						$user = $this->db->where(array('Customername' => $post['tel']))->from('customer')->count_all_results();
						if ($user == 0) {
							$failmessage = '不存在此用户，系统正在返回...';
						}
					}
					unset($_SESSION['T_name']);
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
			}else{
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
		if ($post['type'] != 1){
			if (!empty($curtomerInfo)){
				echo '该手机号已注册'; exit;
			}
		}else{
			if (empty($curtomerInfo)){
				echo '该用户不存在'; exit;
			}
		}
		if (isset($_SESSION['send_sms'])){
			$timediff = time() - $_SESSION['send_sms'];
			if ($timediff < 60){
				echo '短信验证码已经发送,如未收到请在'.(60 - $timediff).'秒后重试';
				exit;
			}
		}
		$sendSms = $this->db->where(array('dealitem'=>'sendSms'))->get('p2_dealitems')->row_array();
		if (empty($sendSms)){
			$this->db->set(array('dealitem'=>'sendSms','updatetime'=>time(),'times'=>1))->insert('p2_dealitems');
		}else{
			if ((time()-$sendSms['updatetime'])> 3600){
				$this->db->set(array('updatetime'=>time(),'times'=>1))->where(array('dealitem'=>'sendSms'))->update('p2_dealitems');
			}else{
				$smsSetting = $this->db->where(array('name'=>'smsErrTimes'))->get('p2_interface')->row_array();
				if (empty($smsSetting)){
					$this->db->set(array('name'=>'smsErrTimes','description'=>'系统防短信攻击设置(通过设置1小时内，短信验证码未返回最大次数partnerId来阻止短信攻击)','partnerId'=>300,'url'=>'#'))->insert('p2_interface');
				}
				$allowtime = empty($smsSetting['partnerId']) ? 300 : $smsSetting['partnerId'];
				if ($sendSms['times'] > $allowtime){
					echo '短信验证码发送失败，请稍后重试';
					file_put_contents(FCPATH.'/log/sendSms'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n手机(".$post ['tel'].")申请短信验证被拒绝,短信接口可能遭受攻击，1小时内超过300条短信未返回正确验证码\r\n\r\n",FILE_APPEND);
					exit;
				}else{
					$this->db->set(array('times'=>$sendSms['times']+1))->where(array('dealitem'=>'sendSms'))->update('p2_dealitems');
				}
			}
		}
		if (ISTESTING) {
			$_SESSION ['telcode'] = $telcode = '1234';
			$_SESSION ['T_name'] = $post ['tel'];
			echo '您的验证码为:1234';
		}else{
			$_SESSION ['telcode'] = $telcode = $this->TelCode();
			$_SESSION ['T_name'] = $post ['tel'];
			$content = "您的验证码是:" . $telcode;
			$res2 = $this->NDFsendSms ( $post ['tel'], $content );
			$res = json_decode ( $res2, TRUE );
			file_put_contents('log/sendSms'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n手机(".$post ['tel'].")申请短信验证码,返回数据:".serialize($res2)."\r\n\r\n",FILE_APPEND);
			if (isset($res['returnCode']) && $res['returnCode'] == 0){
				$result = '验证码已发送！';
				$_SESSION['send_sms'] = time();
			}else{
				$result = '验证码发送失败';
			}
			echo $result;
		}
	}
		// 发短信
	private function NDFsendSms($mobile, $content = '') {
		if (empty ( $mobile )) {
			return false;
		}
		$ISTESTING = false;
		$messageInterface = $this->db->where(array('name'=>'MessageInterface'))->get('p2_interface')->row_array();
		$sms_url = $messageInterface['url'];
		$sms_signature_key = $messageInterface['password'];
		$partnerId = $messageInterface['partnerId'];
		$moduleId = $messageInterface['moduleId'];
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
		$res = $this->fund_interface->fund_list();
		$this->load->config('jz_dict');
		if (!isset($_SESSION['riskLevel'])){
			$_SESSION['riskLevel'] = '01';
		}
		$select = array('fundcode','tano','fundname','fundtype','nav','growthrate',/* 'fundincomeunit', */'status','growth_year');
		$candidateFunds = $this->db->select($select)->where(array('recommend >'=>0,'risklevel <='=>$_SESSION['riskLevel']))->get('fundlist')->result_array();//->get_compiled_select('fundlist');
		$candidateNum = count($candidateFunds);
		$selectNum = 0;
		if ($candidateNum <3){
			$data['Recommend'] = $candidateFunds;
			$selectNum = $candidateNum;
			$candidateFunds = $this->db->select($select)->where(array('recommend =' => 0,'risklevel <='=>$_SESSION['riskLevel']))->get('fundlist')->result_array();
			$candidateNum = count($candidateFunds);
		}
		if ($candidateNum > (3-$selectNum)){
			if ($candidateNum > 0){
				var_dump($candidateNum-1,3-$selectNum);
				$randSeq = array_rand(range(0,$candidateNum-1),3-$selectNum);
				if (is_array($randSeq)){
					foreach ($randSeq as $val){
						$data['Recommend'][] = $candidateFunds[$val];
					}
				}else{
					$data['Recommend'][] = $candidateFunds[$randSeq];
				}
			}
		}else{
			if ($candidateNum > 0){
				foreach ($candidateFunds as $val){
					$data['Recommend'][] = $val;
				}
			}
		}
		foreach ($data['Recommend'] as $key => $val){
			$data['Recommend'][$key]['fundtype'] = $this->config->item('fundtype')[$val['fundtype']];
			if ($this->config->item('fund_status')[$val['status']]['pre_purchase'] == 'Y'){
				$data['Recommend'][$key]['url'] = '/jijin/Jz_fund/showprodetail';
				$data['Recommend'][$key]['purchasetype'] = '认购';
			}elseif($this->config->item('fund_status')[$val['status']]['purchase'] == 'Y'){
				$data['Recommend'][$key]['url'] = '/jijin/Jz_fund/showprodetail';
				$data['Recommend'][$key]['purchasetype'] = '申购';
			}
			if ($val['fundtype'] == 2){
				$data['Recommend'][$key]['growthrate'] = ($val['growthrate']*100).'%';
				$data['Recommend'][$key]['growthDes'] = '七日年化收益率';
			}else{
				$data['Recommend'][$key]['growthDes'] = '近一年收益率';
				$data['Recommend'][$key]['growthrate'] = $val['growth_year'].'%';
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
	
	function queryPlanner(){
		$post = $this->input->post ();
		if (!empty($post['planner_id'])){
			$plannerInfo = $this->db->where(array('EmployeeID'=> $post['planner_id']))->get('p2_planner')->row_array();
			if (!empty($plannerInfo['FName'])){
				echo '您选择了'.$plannerInfo['FName'].'理财师';
				exit;
			}
		}
		echo '您输入的理财师工号不存在';
	}
}