<?php
if (!defined('BASEPATH')){
	exit('No direct script access allowed');
}

/**
 * 功能：【风险等级测试】功能控制类
 */
class Risk_assessment extends MY_Controller {
	function __construct()
	{
		parent::__construct();
        $this->load->library('Fund_interface');
        $_SESSION['myPageOper'] = 'account';
	}
	
	//测试题目
	function index() {
		//获取题目	
		$ret = $this->fund_interface->risk_test_query('13','001','','','1','1');
		$data['base'] = $this->base;
		if (key_exists('code',$ret) && $ret['code'] == '0000'){
			$data['data'] = $ret['data'];
			$this->load->view('jijin/account/view_risk_assessment',$data);
		}else{
			$arr['ret_code'] ='AAAA';
			$arr['head_title'] = '风险等级测试结果';
			$arr['ret_msg'] = '系统故障，请稍候重试';
			$arr['back_url'] = '/jijin/Jz_my';
			$arr['base'] = $this->base;
			$this->load->view('ui/view_operate_result',$arr);
		}
	}
	
	//提交测试结果
	function submit() {		
		$answerList = '';
		$pointList = '';
		$resultArray;
		
		$post = $this->input->post();
		if (!empty($post['cout'])) {
			$cout = (int)$post['cout'];
			for ($i=0;$i<$cout;$i++) {
				if ($answerList != '') {
					$answerList = $answerList.'|';
					$pointList = $pointList.'|';
				}
				
				$questioncode = $post['questioncode'.$i];
				$res = explode('|',(empty($post[$i])?'-|0':$post[$i]));
				$answerList = $answerList.$questioncode.':'.$res[0];
				$pointList = $pointList.$res[1];
			}
		}
		$ret = $this->fund_interface->risk_test_result($answerList,$pointList);
		if (empty($ret)) {
			$data['ret_code'] = 'xxxx1';
			$data['ret_msg'] = '风险测试失败';
			$data['custrisk']='-';
		} else {
			if ($ret['code'] == '0000') {
				$data['ret_code'] = '0000';
				$data['ret_msg'] = '风险测试成功';
				switch ($ret['data']['custrisk']) {////风险承受能力(1:安全型 2:保守型 3:稳健型 4:积极型 5:进取型)
					case 1:$data['custrisk']='安全型 ';break;
					case 2:$data['custrisk']='保守型 ';break;
					case 3:$data['custrisk']='稳健型 ';break;
					case 4:$data['custrisk']='积极型 ';break;
					case 5:$data['custrisk']='进取型 ';break;
					default:$data['custrisk']='安全型 ';break;
				}
			} else {
				$data['ret_code'] = $ret['code'];
				$data['ret_msg'] = '风险测试失败';
				$data['custrisk']='-';
			}
		}
		$data['base'] = $this->base;
   		$this->load->view('jijin/account/view_risk_test_result',$data);
	}
}