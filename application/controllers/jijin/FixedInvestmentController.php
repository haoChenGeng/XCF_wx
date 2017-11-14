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
				}else{
					$return['code'] = 1;
					$data['fail_message'] = '该基金不存在';
				}
				if((int)$_SESSION['riskLevel'] < (int)$fundinfo['risklevel'])
					$data['riskmatching'] = 0;
				else
					$data['riskmatching'] = 1;
				$return['code'] = 0;
				$return['data'] = $data;
			}else{
				$return['code'] = 1;
				$data['fail_message'] = '未找到相关银行卡信息';
			}
		}else{
			$return['code'] = 1;
			$data['fail_message'] = '银行卡查询失败,请稍候再试!';
		}
		
		echo json_encode($return);
	}
}