<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );

class Account extends MY_Controller {
	private $logfile_suffix;
	function __construct() {
		parent::__construct ();
		$this->load->database();
		$this->load->model ( "Model_pageDeal" );
		$this->logfile_suffix = '('.date('Y-m',time()).').txt';
	}
	
	public function login() {
		if (isset ($_SESSION ['admin_id'])) {
			$this->home();
		}else{
			$post = $this->input->post();
			if (! empty ( $post ))	{
				if (empty ( $post['username'] ))
				{
					$data['error_warning'] = '用户名不能为空';
				}
				else
				{
					$private_key = openssl_get_privatekey(file_get_contents($this->config->item('RSA_privatekey')));
					$decryptData ='';
					openssl_private_decrypt(base64_decode($post['password']),$decryptData, $private_key, OPENSSL_PKCS1_PADDING);
					//判断一次性随机验证码是否存在
					$div_bit = strpos($decryptData,(string)$_SESSION['rand_code']);
					unset($_SESSION['rand_code']);
					if ($div_bit !== false){                      //找到一次性随机验证码
						$password =  substr($decryptData, 0, $div_bit);
						$user_info = $this->db->where(array('username' => $post['username']))->get('user')->row_array();
						$passkey = $this->config->item ( 'passkey' );
						$password = $T_pwd = MD5 ( MD5 ( $passkey ) . substr ( MD5($password), 5, 20 ) );
						if ($user_info['password'] == $password){
							$_SESSION['admin_id'] = $user_info['id'];
							$_SESSION['fullname'] = $user_info['fullname'];
							// 						$_SESSION['deptID'] = $user_info['deptID'];
							$usergroup = $this->db->where('id',$user_info['user_group_id'])->get('usergroup')->row_array();
							$_SESSION['groupName'] = $usergroup['name'];
			
							$_SESSION['authority'] = json_decode($usergroup['authority'],true);
							$this->Model_pageDeal->getAccessList();
							file_put_contents('log/userOperation'.$this->logfile_suffix, date('Y-m-d H:i:s',time())."\r\n 用户".$_SESSION['admin_id']."(".$_SESSION['fullname'].")登录了系统。\r\n\r\n",FILE_APPEND);
							$this->home();
						}else{
							$data['error_warning'] = '密码错误';
						}
					}else{
						$data['error_warning'] = '系统错误，请重试';
					}
				}
			}else{
				$data['base'] = $this->base;
				$data['public_key'] = file_get_contents($this->config->item('RSA_publickey'));   //获取RSA_加密公钥
				$data['rand_code'] = "\t".mt_rand(100000,999999);                                //随机生成验证码
				$_SESSION['rand_code'] = $data['rand_code'];
				$this->load->view('admin/login.php', $data);
				$this->load->view('common/footer.php');
			}
		}
	}
	
	public function home(){
		if (!isset ($_SESSION ['admin_id'])) {
			$this->login();
		}else{
			$this->Model_pageDeal->header();
			$this->Model_pageDeal->column_left();
			$this->load->view('common/footer.php');
		}
	}
	
	public function revisePassword(){
		$post = $this->input->post();
		$data['heading_title'] = '修改密码';
		$data['breadcrumbs'][] = array( 'text' => '首页', 'href' => $this->base."/admin/Account/home");
		if (!empty($post)){
			$private_key = openssl_get_privatekey(file_get_contents($this->config->item('RSA_privatekey')));
			$decryptData ='';
			openssl_private_decrypt(base64_decode($post['oldpassword']),$decryptData, $private_key, OPENSSL_PKCS1_PADDING);
			//判断一次性随机验证码是否存在
			$div_bit = strpos($decryptData,(string)$_SESSION['rand_code']);
			unset($_SESSION[$_SESSION['rand_code']]);
			if ($div_bit !== false){                      //找到一次性随机验证码
				$oldpassword =  substr($decryptData, 0, $div_bit);
				$passkey = $this->config->item ( 'passkey' );
				$oldpassword = MD5 ( MD5 ( $passkey ) . substr ( MD5($oldpassword), 5, 20 ) );
				$password = $this->db->select('password')->where('id',$_SESSION['admin_id'])->get('cg_user')->row_array()['password'];
				if ( $password == $oldpassword ){
					$newpassword =  substr($decryptData, $div_bit+7);
					$arr['password'] =  MD5 ( MD5 ( $passkey ) . substr ( MD5($newpassword), 5, 20 ) );
					$flag = $this->db->set($arr)->where('id',$_SESSION['admin_id'])->update('cg_user');
					if ($flag){
						$data['success'] = '密码修改成功';
					}else{
						$data['error_warning'] = '密码修改失败,请重试';
					}
				}else{
					$data['error_warning'] = '旧密码输入错误，请重试';
				}
			}else{
				$data['error_warning'] = '系统错误，请重试';
			}
		}
		$data['public_key'] = file_get_contents($this->config->item('RSA_publickey'));   //获取RSA_加密公钥
		$data['public_key'] = str_replace("\n",'', $data['public_key']);
		$_SESSION['rand_code'] = $data['rand_code'] = "\t".mt_rand(100000,999999);
		if (!isset($data['success'])){
			//设置跳转地址
			$data['cancel'] = $this->base."/admin/Account/home";
			$data['form_action'] = $this->base."/admin/Account/revisePassword";      //form提交地址
			$data['text_form'] = '修改密码';
			$data['forms'][] = array('type'=>'normal', 'description'=>'旧密码', 'required'=>1, 'content'=> 'type="password" id="oldpassword" name="oldpassword" placeholder="请输入旧密码"');
			$data['forms'][] = array('type'=>'normal', 'description'=>'新密码', 'required'=>1, 'content'=> 'type="password" id="newpassword" name="newpassword" placeholder="请输入新密码"');
			$data['forms'][] = array('type'=>'normal', 'description'=>'密码确认', 'required'=>1, 'content'=> 'type="password" id="confirmpassword" name="confirmpassword" placeholder="请再次输入新密码"');
			//'content' 输入其它需要设置的参数   'error' , 'required' 可选
			$this->Model_pageDeal->header();
			$this->Model_pageDeal->column_left();
			$this->load->view('admin/revisePassword.php',$data);
			$this->load->view('common/footer.php');
		}else{
			if (isset ( $_SESSION ['admin_id'] )) {
				unset ( $_SESSION ['admin_id'],$_SESSION['authority'], $_SESSION['deptID'], $_SESSION['fullname'], $_SESSION['groupName']);
			}
			$data['base'] = $this->base;
			$this->load->view('admin/login.php',$data);
			$this->load->view('common/footer.php');
		}
	}
	
	public function logout() {
		file_put_contents('log/userOperation'.$this->logfile_suffix, date('Y-m-d H:i:s',time())."\r\n 用户".$_SESSION['admin_id']."(".$_SESSION['fullname'].")退出了系统。\r\n\r\n",FILE_APPEND);
		if (isset ( $_SESSION ['admin_id'] )) {
			unset ( $_SESSION ['admin_id'] );
		}
		session_destroy();
		session_start();
		$this->login();
	}
	
}