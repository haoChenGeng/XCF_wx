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
	}
	
	//测试题目
	function index() {
		//获取题目	
// 		$jz = new fund_interface();
		$ret = $this->fund_interface->risk_test_query('13','001','','','1','1');		
		$ret_code = $ret['code'];
		$ret_msg = $ret['msg'];
		$ret_trantype = $ret['trantype'];
		$ret_data = $ret['data'];		
		$questionArray = $ret_data; 		
 		$i = 0;
 		$j = 0;
 		$k = 0;
 		$question_no = 0;//题目编号
 		$papercode = '';
 		$papername = '';
 		$questioncode ='';
 		$questionname ='';
 		$multselect = '';
 		while($i<count($questionArray)) {
 			
 			$obj = $questionArray[$i];
 			if ($questioncode != $obj['questioncode']) {
 				$question_no = $j;
 				$j += 1;
 				$questioncode = $obj['questioncode'];
 				
 				$item[$question_no]['papercode']    = $obj['papercode'];
 				$item[$question_no]['papername']    = $obj['papername'];
 				$item[$question_no]['questioncode'] = $obj['questioncode'];
 				$item[$question_no]['questionname'] = $obj['questionname'];
 				$item[$question_no]['multselect']   = $obj['multselect'];
 				$k = 0;
 				$ans['result'] = $obj['result'];
 				$ans['resultcontent'] = $obj['resultcontent'];
 				$ans['resultpoint'] = $obj['resultpoint'];
 				$item[$question_no]['result'][$k] = $ans;
 				$k += 1;				
 			} else {
 				$ans['result'] = $obj['result'];
 				$ans['resultcontent'] = $obj['resultcontent'];
 				$ans['resultpoint'] = $obj['resultpoint'];
 				$item[$question_no]['result'][$k] = $ans;
 				$k += 1;
 			}
 			
 			$i += 1;
 		}
 		
 		$data['data'] = $item;
 		$data['base'] = $this->base;
		$this->load->view('jijin/account/view_risk_assessment',$data);
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
		
//   		$answerList = '01:A|02:B|03:C|04:D|05:A|06:A|07:A|08:A|09:A|10:B|11:B|12:B';
//   		$pointList = '5|5|5|5|0|0|0|0|0|0|0|0';

// 		var_dump($answerList);
		
		
// 		$jz = new fund_interface();
		
		$ret = $this->fund_interface->risk_test_result($_SESSION['JZ_account'],'001',$answerList,$pointList,'1','1');
		if (empty($ret)) {
			$data['ret_code'] = 'xxxx1';
			$data['ret_msg'] = '风险测试失败';
			$data['custrisk']='-';
		} else {
			if (!isset($ret['code']) || !isset($ret['msg']) || !isset($ret['trantype']) || !isset($ret['data'])) {
				$data['ret_code'] = 'xxxx2';
				$data['ret_msg'] = '风险测试失败';
				$data['custrisk']='-';
			} else {
				if ($ret['code'] == '0000') {
					if (count($ret['data']) > 0 && isset($ret['data'][0]['custrisk'])) {
						$data['ret_code'] = '0000';
						$data['ret_msg'] = '风险测试成功';
						switch ($ret['data'][0]['custrisk']) {////风险承受能力(1:安全型 2:保守型 3:稳健型 4:积极型 5:进取型)
							case 1:$data['custrisk']='安全型 ';break;
							case 2:$data['custrisk']='保守型 ';break;
							case 3:$data['custrisk']='稳健型 ';break;
							case 4:$data['custrisk']='积极型 ';break;
							case 5:$data['custrisk']='进取型 ';break;
							default:$data['custrisk']='安全型 ';break;
						}
					} else {
						$data['ret_code'] = 'xxxx3';
						$data['ret_msg'] = '返回数据格式有误';
						$data['custrisk']='-';
					}
				} else {
					$data['ret_code'] = $ret['code'];
					$data['ret_msg'] = '风险测试失败';
					$data['custrisk']='-';
				}
			}
		}
		
		$data['base'] = $this->base;
   		$this->load->view('jijin/account/view_risk_test_result',$data);
	}
}