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
        //$_SESSION['myPageOper'] = 'account';
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
	}
	
	function getRiskQuestion(){
		if (isset($_SESSION['customer_id'])){
			if (isset($_SESSION['JZ_user_id']) && 1==$_SESSION['JZ_user_id']){
				$fundadmittance = $this->db->select('fundadmittance')->where(array('id'=>$_SESSION ['customer_id']))->get('p2_customer')->row_array()['fundadmittance'];
				if(!$fundadmittance){
					$accessRes = $this->fund_interface->SDAccess();
					if (isset($accessRes['code']) && '0000' == $accessRes['code']){
						$this->db->set(array('fundadmittance'=>1))->where(array('id'=>$_SESSION['customer_id']))->update('p2_customer');
					}
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
				$data['ret_msg'] = '风险评测成功';
				if ($_SESSION['riskLevel'] > $ret['data']['custrisk']){
					$data['cautionFlag'] = 1;
				}
				$_SESSION['riskLevel'] = $ret['data']['custrisk'];
				$this->load->config('jz_dict');
				$data['custrisk'] = $this->config->item('custrisk')[$_SESSION['riskLevel']];
				$paperCode = $this->db->select('paperCode')->get('p2_riskquestion')->row_array()['paperCode'];
				$riskData = array('customerId'=>$_SESSION['customer_id'],'paperCode'=>$paperCode,'answer'=>$answerList,'point'=>$pointList);
				$riskData['riskLevel'] = empty($_SESSION['riskLevel']) ? '' : $_SESSION['riskLevel'];
				$this->db->replace('p2_riskanswer',$riskData);
			} else {
				$data['ret_code'] = $ret['code'];
				$data['ret_msg'] = '风险评测失败';
				$data['custrisk']='-';
			}
		}
	}
	
	//智能投顾端提交测试结果，保存用户提交答案到本地数据库，如用户已开基金账户则提交给金证系统，否则自动判定风险等级
	function ZNTGsubmit() {
		$data = array();
		$post = $this->input->post();
		$answerList = $pointList = '';
		$result = json_decode($post['res'],true);
		$questionNum = $this->db->from('p2_riskquestion')->count_all_results();
		if (count($result) < $questionNum){
			echo json_encode(array('code'=>'0001',"msg"=>'您尚有题目未完成'));
			exit;
		}
		foreach ( $result as $val){
			$answerList .= "|".$val['num'].":".$val['result'];
			$pointList .= "|".$val['point'];
		}
		$answerList = substr($answerList,1);
		$pointList = substr($pointList,1);
		//计算获得$answerList，$pointList
		if (isset($_SESSION['JZ_user_id']) && 1==$_SESSION['JZ_user_id']){
			$res = $this->JZsubmit($answerList, $pointList, $data);
			echo json_encode(array('code'=>$data['ret_code'],"msg"=>$data['ret_msg']));
			exit;			
		}else{
			$scores = 0;
			$points = explode('|', $pointList);
			foreach ($points as $val){
				$scores += $val;
			}
			$this->load->config('jz_dict');
			$riskSetting = $this->config->item('riskSetting');
			foreach ($riskSetting as $key=>$val){
				if ($scores >= $key){
					$riskLevel = $val;
				}else{
					break;
				}
			}
			$paperCode = $this->db->select('paperCode')->get('p2_riskquestion')->row_array()['paperCode'];
			$riskData = array('customerId'=>$_SESSION['customer_id'],'paperCode'=>$paperCode,'answer'=>$answerList,'point'=>$pointList,'riskLevel'=>$riskLevel);
			$flag = $this->db->replace('p2_riskanswer',$riskData);
			if ($flag){
				echo json_encode(array('code'=>'0000',"msg"=>'风险评测成功'));
			}
		}
	}
	
	function getZNTGResult() {
		$ZNTGResult = array('code' => '0000', 'login'=>0);							//,'msg'=>'系统错误，请稍后重试')
		if (isset($_SESSION['customer_id'])){
			$riskInfo = $this->db->select('answer,riskLevel')->where(array('customerId'=>$_SESSION['customer_id']))->get('p2_riskanswer')->row_array();
			if (!empty($riskInfo['riskLevel'])){
				$this->load->config('jz_dict');
				$ZNTGResult['riskLevel'] = $riskInfo['riskLevel'];
				$ZNTGResult['riskName'] = $this->config->item('custrisk')[$riskInfo['riskLevel']];
				$answer = array_slice(explode('|', $riskInfo['answer']),0,6);
				foreach ($answer as $val){
					$cutVal = explode(':', $val);
					$riskAnswer[$cutVal[0]] = $cutVal[1];
				}
				$ret = $this->fund_interface->risk_test_query();
				$ret = array_slice($ret,0,6);
				foreach ($ret as &$val){
					foreach ($val['result'] as $v){
						$ZNTGQuestion[$val['questioncode']][$v['result']] = $v['resultcontent'];
					}
				}
				$chartDes = array('3002'=>'房地产资产', '3003'=>'股权投资资产', '3004'=>'固定收益资产', '3005'=>'现金类资产', '3006'=>'境外资产',);
				foreach ($riskAnswer as $key=>$val){
					if ('3001'==$key){
						$ZNTGResult['totalAsset'] = $ZNTGQuestion[$key][$val];
					}else{
						if (strstr($ZNTGQuestion[$key][$val],'以下')){
							$value =20;
						}else{
							if (strstr($ZNTGQuestion[$key][$val],'以上')){
								$value =35;
							}else{
								$tmp = explode('%', $ZNTGQuestion[$key][$val]);
								if ($tmp[0]<40){
									$value =25;
								}else{
									$value =30;
								}
							}
						}
						$ZNTGResult['chartData'][] = array('name' => $chartDes[$key], 'value'=>$value,'des' => $ZNTGQuestion[$key][$val]);
					}
				}
			}else{
				$ZNTGResult['riskLevel'] = '';
			}
		}else{
			$ZNTGResult['login'] = 1;
		}
		echo json_encode($ZNTGResult);
	}
	
}