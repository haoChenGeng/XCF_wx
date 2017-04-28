<?php
if (!defined('BASEPATH')){
	exit('No direct script access allowed');
}

class Member_access extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
//         $this->load->model(array("model_common"));
        $this->load->database();
        $this->load->helper(array("encrpty","url"));
    }
    
    function access()
    {
    	$post = $this->input->post();
    	if (!isset($_SESSION['customer_id']))                          //用户未登录，返回用登录界面的url
    	{
    		$arr['code']='e0';
    		$arr['url']= $this->base.'/user/login/'.$post['access_type'];
    		echo json_encode($arr);
    		exit;
    	}
    	$arr = array();
    	if (isset($post['pass_key']))
    	{
    		$customer_info = $this->db->where(array('id' =>$_SESSION['customer_id']))->get('customer')->row_array();
    		if (!empty($customer_info))
    		{
    			$res = $this->db->where(array('Customername' =>$customer_info['Customername'],'access_site' => '会员专区'))->get("access_application")->result_array();
    			if (!empty($res))
    			{
    				foreach ($res as $key => $val){
    					unset($val['encrypt_key']);
    					unset($val['id']);
    					$this->db->insert('log_access_application', $val);
    				}
    				$this->db->delete('access_application', array('Customername' => $customer_info['Customername'],'access_site' => '会员专区'));
    			}
    			$private_key = openssl_get_privatekey(file_get_contents($this->config->item('RSA_privatekey')));
    			$encrypt_key ='';
    			openssl_private_decrypt(base64_decode($post['pass_key']),$encrypt_key, $private_key, OPENSSL_PKCS1_PADDING);
    			$apply_data = array(
    					'Customername' => $customer_info['Customername'],
    					'access_site' => '会员专区',
    					'encrypt_key' => $encrypt_key,
    					'status' => 0,
    			);
    			$res = $this->db->insert("access_application", $apply_data);
    			if ($res == true)
    			{
    				$arr['code']='ok';
    				$arr['type'] = $post['access_type'];
    				$arr['apply_code'] = $this->db->insert_id ();
//     				$arr['url']= $this->base.'/client/Member_access/access_auth';    //测试用
    				$arr['url']= $this->config->item('member_url').'/index.php/api/P2P/entrance';
    			}
    			else {
    				$arr['code'] = 'e1';
    				$arr['err_msg'] = '申请数据写入数据库失败';
    			}
    		}
    		else {
    			$arr['code']='e2';
    			$arr['err_msg'] = '用户不存在';
    		}
    	} else
    	{
    		$arr['code']='e3';
    		$arr['err_msg'] = '会员申请提交的信息不正确,$post'."[\'pass_key\']"."为空";
    	}
    	file_put_contents('log/access/member_access('.date('Y-m',time()).').txt',date('Y-m-d H:i:s',time()).":\r\n".serialize($arr)."\r\n\r\n",FILE_APPEND);
    	echo json_encode($arr);
    	exit();
    }
    
    function access_auth()
    {
    	$post = $this->input->post();
    	if (!empty($post))
    	{
    		$sql = "SELECT * FROM p2_access_application WHERE id = ? and access_site = '会员专区'";
    		$apply_info = $this->db->query($sql, $post['apply_code'])->row_array();
    		if (!empty($apply_info))
    		{
    			$verify_code = cryptoJsAesDecrypt($apply_info['encrypt_key'], $post['encrypted_code']);
    			if ($verify_code == $apply_info['encrypt_key'])
    			{
    				$apply_time = strtotime($apply_info['addtime']);
    				if (time()-$apply_time < 600)
    				{
    					$description = array('201' =>'会员专区访问', '202' => '会员申请', '203' => '会员签到', '204' => '会员管理');
    					$arr = array(
    							'status' => 'SUCCESS',
    							'member_account' => $apply_info['Customername'],
    							'type' => $post['type'],
    							'acess_description' => $description[$post['type']]
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
    	file_put_contents('log/access/member_auth('.date('Y-m',time()).').txt',date('Y-m-d H:i:s',time()).":\r\n".serialize($arr)."\r\n\r\n",FILE_APPEND);
    	echo json_encode($arr);
    }
    
    function discovery()
    {
    	$arr = array();
    	if (isset($_SESSION ['customer_id']))
    	{
    		$customerInfo = $this->db->where(array('Customername' => $_SESSION ['customer_name']))->get('customer')->row_array();
    		$arr['member_account'] = $_SESSION ['customer_name'];
    		$arr['class'] = $customerInfo['class'];
    		$arr['type'] = 223;
    		$arr['acess_description'] = '发现';
    	}
    	redirect($this->config->item('member_url').'/index.php/api/P2P/entrance?data='.bin2hex(json_encode($arr)));
    }
    
    function sign_query()
    {
    	if (!isset($_SESSION['customer_id']))                          //用户未登录，返回用登录界面的url
    	{
    		redirect($this->base.'/user/login/1');
    	}
    	if (isset($_SESSION ['customer_id']))
    	{
    		$customerInfo = $this->db->where(array('Customername' => $_SESSION ['customer_name']))->get('customer')->row_array();
    		$arr['member_account'] = $_SESSION ['customer_name'];
    		$arr['class'] = $customerInfo['class'];
    		$arr['type'] = 221;
    		$arr['acess_description'] = '签到查询';
    	}
    	redirect($this->config->item('member_url').'/index.php/api/P2P/entrance?data='.bin2hex(json_encode($arr)));
    }
    
    function activity_query()
    {
    	$customerInfo = $this->db->where(array('Customername' => $_SESSION ['customer_name']))->get('customer')->row_array();
    	$arr['member_account'] = $_SESSION ['customer_name'];
    	$arr['class'] = $customerInfo['class'];
    	$arr['type'] = 222;
    	$arr['acess_description'] = '活动';

    	redirect($this->config->item('member_url').'/index.php/api/P2P/entrance?data='.bin2hex(json_encode($arr)));
    }
    
    function auto_jump($type = 0)
    {
    	$data['type'] = $type;
    	$this->load->view('ui/index', $data);
    }

//     function display($message)
//     {
//     	var_dump($message);
//     	var_dump(json_decode(hex2bin($message)));
//     }
    
//以下部分为会员专区的登录程序，不在此平台，放在此处作参考    
/*     function member_login()
    {
    	$post = $this->input->post();
        
        if ($post != '')
        {
            $timeout = 5;
            $url = 'http://127.0.0.1/client/Member_access/access_auth';
            $ci = curl_init();
            curl_setopt($ci, CURLOPT_USERAGENT, 0);
            curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ci, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ci, CURLOPT_HEADER, FALSE);
            curl_setopt($ci, CURLOPT_POSTFIELDS, $post);
            curl_setopt($ci, CURLOPT_URL, $url);
            $response = curl_exec($ci);
            var_dump($response);
            curl_close($ci);
            
        }
        else
        {
        	echo '未输入相关登录信息';             //或选择跳转到PC端的登陆页面。
        }
    } */
    
}