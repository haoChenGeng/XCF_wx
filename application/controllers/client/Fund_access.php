<?php
if (!defined('BASEPATH')){
	exit('No direct script access allowed');
}
    
#header("Content-type: text/html; charset=utf-8");
#include_once(__DIR__.DIRECTORY_SEPARATOR.'/weixin/api.php');

class Fund_access extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
//         $this->load->model(array("model_common"));
        $this->load->database();
        $this->load->helper(array("encrpty","url"));
//         $this->Isnav = "User";
    }
    
    function access()
    {
    	$post = $this->input->post();
    	if (!isset($_SESSION['customer_id']))                          //用户未登录，用户作为访客，直接返回基金的访客首页的url
    	{
    		$arr['code']='m0';
    		$arr['url']= "/jijin/Jz_account/entrance";
    		echo json_encode($arr);
    		exit;
    	}
    	$arr = array();
    	if (isset($post['pass_key']))
    	{
    		$customer_info = $this->db->where(array('id' => $_SESSION['customer_id']))->get('customer')->row_array();
    		if (!empty($customer_info))
    		{
    			$res = $this->db->where(array('Customername' =>$customer_info['Customername'],'access_site' => '基金系统'))->get("access_application")->result_array();
    			if (!empty($res))
    			{
    				foreach ($res as $key => $val){
    					unset($val['encrypt_key']);
    					unset($val['id']);
    					$this->db->insert('log_access_application', $val);
    				}
    				$this->db->delete('access_application', array('Customername' => $customer_info['Customername'],'access_site' => '基金系统'));
    			}
    			$private_key = openssl_get_privatekey(file_get_contents($this->config->item('RSA_privatekey')));
    			$encrypt_key ='';
    			openssl_private_decrypt(base64_decode($post['pass_key']),$encrypt_key, $private_key, OPENSSL_PKCS1_PADDING);
    			$apply_data = array(
    					'Customername' => $customer_info['Customername'],
    					'access_site' => '基金系统',
    					'encrypt_key' => $encrypt_key,
    					'status' => 0,
    			);
    			$res = $this->db->insert("access_application", $apply_data);
    			if ($res == true)
    			{
    				$arr['code']='ok';
    				$arr['type'] = $post['access_type'];
    				$arr['apply_code'] = $this->db->insert_id ();
//     				$arr['url']= 'http://localhost:8080/client/Fund_access/access_auth';    //测试用
    				$arr['url']= '/jijin/Jz_account/entrance';
    			}
    			else {
    				$arr['code'] = 'e1';
    				$arr['err_msg'] = '申请数据写入数据库失败';
    			}
    		}
    		else {
    			$arr['code']='e2';
    			$arr['err_msg'] = '用户不存在或该用户未开通会员功能';
    		}
    	}else{
    		$arr['code']='e3';
    		$arr['err_msg'] = '基金访问申请提交的信息不正确,$post'."[\'pass_key\']"."为空";
    	}
    	file_put_contents('log/access/fund_access('.date('Y-m',time()).').txt',date('Y-m-d H:i:s',time()).":\r\n".serialize($arr)."\r\n\r\n",FILE_APPEND);
    	echo json_encode($arr);
    	exit();
    }
    
    function access_auth()
    {
    	$post = $this->input->post();
    	if (!empty($post))
    	{
    		$sql = "SELECT * FROM p2_access_application WHERE id = ? and access_site = '基金系统'";
    		$apply_info = $this->db->query($sql, $post['apply_code'])->row_array();
    		if (!empty($apply_info))
    		{
    			$verify_code = cryptoJsAesDecrypt($apply_info['encrypt_key'], $post['encrypted_code']);
    			if ($verify_code == $apply_info['encrypt_key'])
    			{
    				$apply_time = strtotime($apply_info['addtime']);
    				if (time()-$apply_time < 600)
    				{
    					$arr = array(
    							'status' => 'SUCCESS',
    							'Customername' => $apply_info['Customername'],
    							'type' => $post['type']
    					);
    				}
    				else
    				{
    					$arr['err_msg'] = '访问超时，请重试';
    				}
    			} else {
    				$arr['err_msg'] = '随机验证码校验错误';
    			}
    			if (isset($arr['err_msg']))
    			{
    				$arr['status'] = 'FAILURE';
    			}
    			unset($apply_info['id']);
    			unset($apply_info['encrypt_key']);
    			$apply_info['status'] =1;
    			$this->db->insert('log_access_application', $apply_info);
    			$this->db->delete('access_application', array('Customername' => $apply_info['Customername'],'access_site' => '会员专区'));
    		}
    		else
    		{
    			$arr['status'] = 'FAILURE';
    			$arr['err_msg'] = '无相应的验证申请';
    		}
    	}
    	else
    	{
    		$arr['status'] = 'FAILURE';
    		$arr['err_msg'] = '输入验证信息为空';
    	}
    	file_put_contents('log/access/fund_auth('.date('Y-m',time()).').txt',date('Y-m-d H:i:s',time()).":\r\n".serialize($arr)."\r\n\r\n",FILE_APPEND);
    	$this->load->library('encryption');
    	$this->encryption->initialize(
    			array(
    					'cipher' => 'aes-256',
    					'mode' => 'ctr',
    					'key' => $this->config->item('JZ_AES_key')
    			)
    			);
    	echo $this->encryption->encrypt(json_encode($arr));
    }
    
/*     function auto_jump($type = 0)
    {
    	$data['type'] = $type;
    	$this->load->view('index', $data);
    }
     */
//基金系统需要再次登录时，采用的基金访问方式，送加密的用户名过去    
//     //判断用户是否登录
//     function is_login()
//     {
//         if (!isset($_SESSION['customer_id']))
//         {
//             redirect($this->base . "/user/login");
//         } 
//     }

//     function access($type = 100)
//     {
//     	$user_name = '';
//     	if(isset($_SESSION ['user_name']))
//     	{
//     		$this->load->library('encryption');
//     		$this->encryption->initialize(
//     				array(
//     						'cipher' => 'aes-256',
//     						'mode' => 'ctr',
//     						'key' => $this->config->item('JZ_AES_key')
//     				)
//     		);
//     		$user_name = bin2hex($this->encryption->encrypt($_SESSION ['user_name']));
//     	}
    	
//     	switch ($type)
//     	{
//     		case $type < 150:
//     			$fund_url = . "/jijin/Jz_account/entrance" ;
//     			break;
//     		case $type >= 150:
//     			$fund_url = "http://localhost/jijin/Jz_account/entrance";
//     			break;
//     	}

//     	redirect($fund_url."?user_name=". $user_name ."& type=".$type);
//     }
}