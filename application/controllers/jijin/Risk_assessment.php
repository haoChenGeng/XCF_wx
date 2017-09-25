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
        $this->load->helper(array("url"));
        $_SESSION['myPageOper'] = 'account';
	}
	
	//测试题目
	function index() {
		//判断客户是否完成投资者信息录入
		$fundadmittance = $this->db->select('fundadmittance')->where(array('id'=>$_SESSION ['customer_id']))->get('p2_customer')->row_array()['fundadmittance'];
		if(!$fundadmittance){
// 			redirect('/jijin/jz_my/investorManagement/Risk_assessment');
			$accessRes = $this->fund_interface->SDAccess();
			if (isset($accessRes['code']) && '0000' == $accessRes['code']){
				$this->db->set(array('fundadmittance'=>1))->where(array('id'=>$_SESSION['customer_id']))->update('p2_customer');
			}
		}
		//获取题目	
		$ret = $this->fund_interface->risk_test_query();
		$data['data'] = $ret;
		$data['base'] = $this->base;
		$this->load->view('jijin/account/view_risk_assessment',$data);
/* 		if (key_exists('code',$ret) && $ret['code'] == '0000'){
			$data['data'] = $ret['data'];
			$this->load->view('jijin/account/view_risk_assessment',$data);
		}else{
			$arr['ret_code'] ='AAAA';
			$arr['head_title'] = '风险等级测试结果';
			$arr['ret_msg'] = '系统故障，请稍候重试';
			$arr['back_url'] = '/jijin/Jz_my';
			$arr['base'] = $this->base;
			$this->load->view('ui/view_operate_result',$arr);
		} */
	}
	
	function getRiskQuestion(){
		if (isset($_SESSION['customer_id'])){
			$fundadmittance = $this->db->select('fundadmittance')->where(array('id'=>$_SESSION ['customer_id']))->get('p2_customer')->row_array()['fundadmittance'];
			if(!$fundadmittance){
				// 			redirect('/jijin/jz_my/investorManagement/Risk_assessment');
				$accessRes = $this->fund_interface->SDAccess();
				if (isset($accessRes['code']) && '0000' == $accessRes['code']){
					$this->db->set(array('fundadmittance'=>1))->where(array('id'=>$_SESSION['customer_id']))->update('p2_customer');
				}
			}
			$ret = $this->fund_interface->risk_test_query();
			echo json_encode(array('code'=>'0000','data'=>&$ret));
		}else{
			echo json_encode(array('code'=>'R001','msg'=>'您尚未登录'));
		}
	}
	
	//向金证系统提交测试结果
	function submit() {		
		$answerList = '';
		$pointList = '';
		$resultArray;
		
		$post = $this->input->post();
		$data = array();
		if (!empty($post['cout'])) {
			$cout = (int)$post['cout'];
			for ($i=0;$i<$cout;$i++) {
				if ($answerList != '') {
					$answerList = $answerList.'|';
					$pointList = $pointList.'|';
				}
				
				$questioncode = $post['questioncode'.$i];
				if (!empty($post[$i])){
					$res = explode('|',$post[$i]);
					$answerList = $answerList.$questioncode.':'.$res[0];
					$pointList = $pointList.$res[1];
				}else{
					$data['ret_code'] = 'xxxx2';
					$data['ret_msg'] = '您有题目尚未作答';
					$data['custrisk']='-';
					break;
				}
			}
		}
		if (empty($data['ret_code'])){
			$this->JZsubmit($answerList, $pointList, $data);
		}
		$data['base'] = $this->base;
   		$this->load->view('jijin/account/view_risk_test_result',$data);
	}
	
	function JZsubmit($answerList,$pointList,&$data){
		$ret = $this->fund_interface->risk_test_result($answerList,$pointList);
		if (empty($ret)) {
			$data['ret_code'] = 'xxxx1';
			$data['ret_msg'] = '风险测试失败';
			$data['custrisk']='-';
		}else{
			if ($ret['code'] == '0000') {
				$data['ret_code'] = '0000';
				$data['ret_msg'] = '风险测试成功';
				$_SESSION['riskLevel'] = $ret['data']['custrisk'];
				switch ($ret['data']['custrisk']) {////风险承受能力(1:安全型 2:保守型 3:稳健型 4:积极型 5:进取型)
					case 1:$data['custrisk']='安全型 ';break;
					case 2:$data['custrisk']='保守型 ';break;
					case 3:$data['custrisk']='稳健型 ';break;
					case 4:$data['custrisk']='积极型 ';break;
					case 5:$data['custrisk']='进取型 ';break;
					default:$data['custrisk']='安全型 ';break;
				}
				$paperCode = $this->db->select('paperCode')->get('p2_riskquestion')->row_array()['paperCode'];
				$riskData = array('customerId'=>$_SESSION['customer_id'],'paperCode'=>$paperCode,'answer'=>$answerList,'point'=>$pointList);
				$riskData['riskLevel'] = empty($_SESSION['riskLevel']) ? '' : $_SESSION['riskLevel'];
				$this->db->replace('p2_riskanswer',$riskData);
			} else {
				$data['ret_code'] = $ret['code'];
				$data['ret_msg'] = '风险测试失败';
				$data['custrisk']='-';
			}
		}
	}
	
	//智能投顾端提交测试结果，保存用户提交答案到本地数据库，如用户已开基金账户则提交给金证系统，否则自动判定风险等级
	function ZNTGsubmit() {
		$data = array();
		$post = $this->input->post();
		//计算获得$answerList，$pointList
		if (isset($_SESSION['JZ_user_id']) && 1==$_SESSION['JZ_user_id']){
			$this->JZsubmit($answerList, $pointList, $data);
		}else{
			//进行评分
		}
	}
	
	function getZNTGResult() {
		$ZNTGResult = array('code' => '0000', 'login'=>0);							//,'msg'=>'系统错误，请稍后重试')
		if (isset($_SESSION['customer_id'])){
			$riskInfo = $this->db->select('answer,riskLevel')->where(array('customerId'=>$_SESSION['customer_id']))->get('p2_riskanswer')->row_array();
			if (!empty($riskInfo['riskLevel'])){
				$ZNTGResult['riskLevel'] = $riskInfo['riskLevel'];
			}else{
				$ZNTGResult['riskLevel'] = '';
			}
		}else{
			$ZNTGResult['login'] = 1;
		}
		echo json_encode($ZNTGResult);
	}
	
}