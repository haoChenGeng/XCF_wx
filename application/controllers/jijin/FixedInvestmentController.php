<?php
if (!defined('BASEPATH')){
	exit('No direct script access allowed');
}
    
class FixedInvestmentController extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper(array("output","comfunction","logfuncs"));       //"page"  "log"   "func",
        $this->load->library(array('Fund_interface','Logincontroller'));
    }

    function index($activePage = 'fund'){
	}

	function beforeFixedInvestment(){
		$get = $this->input->get();
		$_SESSION['next_url'] = $this->base . "/jijin/Jz_fund";
		if (!$this->logincontroller->isLogin()) {
			redirect($this->base . "/jijin/Jz_account/register");
			exit;
		}

		if(!isset($get['fundcode'])){
			echo json_encode(array('code'=>1,'msg'=>'fundcode不存在!'));
			return;
		}

		$bank_info =$this->fund_interface->bankCardPhone();
		$channel_info = $this->fund_interface->paymentChannel();
		$channel_info = setkey($channel_info,'channelid');
		myLog('user/bank_info',"用户".$_SESSION ['customer_name']."查询银行卡信息，返回数据为:".serialize($bank_info));
		if (isset($bank_info['code']) && $bank_info['code'] == '0000'){
			if (!empty($bank_info['data'][0])){
				$data['bank_info'] = $bank_info['data'];
				$this->load->config('jz_dict');
				foreach ($data['bank_info'] as $key => $val){
					if(0 == $val['authenticateflag'] && 0 == $val['status']){
						$data['bank_info'][$key]['status'] = '未激活';
					}else{
						$data['bank_info'][$key]['status'] = $this->config->item('bankcard_status')[$val['status']];
						if (empty($data['bank_info'][$key]['status']))
							$data['bank_info'][$key]['status'] = '未知';

					}
					$data['bank_info'][$key]['channelname'] = $channel_info[$val['channelid']]['channelname'];
				}
				
				$fundinfo = &$data['fundinfo'];
				$fundInfo = $this->db->where(array('fundcode' => $get['fundcode']))->get('fundlist')->row_array();
				if(is_array($fundInfo)){
					$fundinfo['fundcode'] = $get['fundcode'];					
					$fundinfo['tano'] = $fundInfo['tano'];
					$fundinfo['shareclasses'] = $fundInfo['shareclasses'];
					$fundinfo['fundtype'] = $fundInfo['fundtype'];
					$fundinfo['risklevel'] = $fundInfo['risklevel'];
					$fundinfo['fundname'] = $fundInfo['fundname'];
					$fundinfo['per_min_39'] = $fundInfo['per_min_39'];
					$fundinfo['per_max_39'] = $fundInfo['per_max_39'];
					if((int)$_SESSION['riskLevel'] < (int)$fundinfo['risklevel'])
						$data['riskmatching'] = 0;
					else
						$data['riskmatching'] = 1;
					$this->load->config('jz_dict');
					$fundinfo['risklevel'] =isset($this->config->item('productrisk')[$fundinfo['risklevel']])?$this->config->item('productrisk')[$fundinfo['risklevel']]:null;
					$data['token'] = $_SESSION['token'] = mt_rand(100000,999999);
					$data['public_key'] = file_get_contents($this->config->item('RSA_publickey'));
					$return['code'] = 0;
					$return['data'] = $data;
				}else{
					$return['code'] = 1;
					$return['msg'] = '该基金不存在';
				}
				
			}else{
				$return['code'] = 1;
				$return['msg'] = '未找到相关银行卡信息';
			}
		}else{
			$return['code'] = 1;
			$return['msg'] = '银行卡查询失败,请稍候再试!';
		}
		
		echo json_encode($return);
	}

	function FixedInvestment(){
		$post = $this->input->post();
		if(!isset($post['token']) || !isset($_SESSION['token'])){
			echo json_encode(array('code' => 1,'msg'=>'非正常请求，请按照流程发起定投请求！'));
			return;
		}

		$private_key = openssl_get_privatekey(file_get_contents($this->config->item('RSA_privatekey')));
		$decryptData ='';
		
		openssl_private_decrypt(base64_decode($post['token']),$decryptData, $private_key, OPENSSL_PKCS1_PADDING);
		//判断一次性随机验证码是否存在
		$div_bit = strpos($decryptData,(string)$_SESSION['token']);
		if($div_bit !== false){
			unset($_SESSION['token']);
			$post['tpasswd'] = substr($decryptData, 0, $div_bit);
			$fixed =$this->fund_interface->FixedInvestment($post);
			if(isset($fixed['code'])&&$fixed['code'] == "0000"){
				$return['code'] = 0;
				$return['msg'] = $fixed['msg'];
			}else{
				$return['code'] = 1;
				$return['msg'] = $fixed['msg'];
			}
		}else{
			$return['code'] = 1;
			$return['msg'] = "非正常请求，请按照流程发起定投请求！";
		}

		echo json_encode($return);
	}
}