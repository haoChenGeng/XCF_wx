<?php
if (!defined('BASEPATH')){
	exit('No direct script access allowed');
}
    
class Jz_my extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library(array('Fund_interface','Logincontroller'));
    }

	//我的基金页面入口
	function index($activePage = 'fund')
	{
		if (!$this->logincontroller->isLogin()) {
			$_SESSION['next_url'] = $this->base.'/jijin/Jz_my';
		}
		$data = array();
		if (isset($_SESSION['myPageOper'])){
			$data['pageOper'] = $_SESSION['myPageOper'];
			unset($_SESSION['myPageOper']);
		}else{
			$data['pageOper'] = 'asset';
		}
		unset($_SESSION['myPageOper']);
		$data['base'] = $this->base;
		$this->load->view('jijin/my.html', $data);
	}
	
	public function myFundInfo(){
		$activePage = isset($_SESSION['myPageOper']) ? $_SESSION['myPageOper'] : 'asset';
		$data['activePage'] = $activePage;
		if (!isset($_SESSION['customer_id'])) {
			echo(json_encode(array('code'=>'9999','msg'=>'您尚未登录,请先登录')));
			exit;
		}else{
			if (0==$_SESSION['JZ_user_id']){
				echo(json_encode(array('code'=>'8888','msg'=>'您尚未开通基金账户')));
				exit;
			}
		}
		$data['code'] = '0000';
		$data['customerName'] = $_SESSION ['customer_name'];
		if (!isset($_SESSION['totalfundvolbalance'])){
			$fundlist = $this->getMyFundList();
			$data['totalfundvolbalance'] = $_SESSION['totalfundvolbalance'] = $fundlist['totalfundvolbalance'];
			$data['yestincomesum'] = $_SESSION['yestincomesum'] = $fundlist['yestincomesum'];
			$data['addincomesum'] = $_SESSION['addincomesum'] = $fundlist['addincomesum'];
		}else{
			$data['totalfundvolbalance'] = $_SESSION['totalfundvolbalance'];
			$data['yestincomesum'] = $_SESSION['yestincomesum'];
			$data['addincomesum'] = $_SESSION['addincomesum'];
		}
		echo json_encode($data);
	}
	
	//获取“我的基金”页面的内容
	public function getMyPageData($activePage = '') {
		if (!isset($_SESSION['customer_id'])) {
			echo(json_encode(array('error'=>true,'errorMsg'=>'您尚未登录,请先登录')));
			exit;
		}
		if (!isset($_SESSION['JZ_user_id']) || $_SESSION['JZ_user_id'] == 0) {
			echo(json_encode(array('error'=>true,'errorMsg'=>'您尚未开通基金账户,请先开户')));
			exit;
		}
		if (empty($activePage)){
			$activePage = isset($_SESSION['myPageOper']) ? $_SESSION['myPageOper'] : 'fund';
		}
		if (isset($_SESSION['JZ_user_id'])) {
			switch ($activePage) {
				case 'fund' :
					$data = $this->getMyFundList();
					break;
				case 'bonus_change':
					$res = $this->bonusChangeAbleList();
					$data['bonus_change'] = $res;
					break;
				case 'bank_card':
					//获取银行卡
					$res = $this->bank_info();
					//对res进行验证
					$data['bank_info'] = $this->bank_info();
					break;
				case 'risk_test':
					//获取风险测试
					$res = $this->getRiskLevel();
					if (isset($res['code']) && isset($res['msg']) && isset($res['data']) && !empty($res['data'])) {
						$data['custrisk'] = $res['data']['custrisk'] ;
						$data['custriskname'] = $res['data']['custriskname'] ;
					} else {
						$data['custrisk'] = '查询失败';
					}
					break;
				case 'history':
					//获取历史记录
					$res = $this->getTodayTran();
					if (isset($res['errorMsg'])) {
						$data['hisErrorMsg'] = $res['errorMsg'];
					} else {
						if (isset($res['code']) && isset($res['msg']) && isset($res['data']) && !empty($res['data'])) {
							$data['history_tran'] = $res;
						} else {
							$data['hisErrorMsg'] = '查询失败';
						}
					}
					break;
				default:
					$data['errorMsg'] = '不存在该页面';
			}
		}else {
			$data['errorMsg'] = '未登录';
		}
// file_put_contents('log/debug'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).'获取“我的基金”页面的内容'.serialize($data)."\r\n\r\n",FILE_APPEND);
// var_dump($data);
		echo json_encode($data);
	}
	
	//获取已购基金列表和总资产
	private function getMyFundList() {
		//调用接口
		$res = $this->fund_interface->asset();
		file_put_contents('log/trade/Jz_my'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).'客户:'.$_SESSION['customer_name'].'进行资产查询，返回数据为'.serialize($res)."\r\n\r\n",FILE_APPEND);
		$data = &$res['data'];
		$this->load->config('jz_dict');
		$productrisk = $this->config->item('productrisk');
		if (!empty($res['data']['fund_list'])) {
			$custrisk = $this->getRiskLevel(1);
			foreach ($data['fund_list'] as $key=>$val){
				if (is_array($val)){
					$data['fund_list']['data'][$key] = $val;
					$data['fund_list']['data'][$key]['json'] = base64_encode(json_encode($val));
					if ($val['risklevel'] > $custrisk){
						$risklevel = $val['risklevel'];
						$data['fund_list']['data'][$key]['riskDes'] = isset($productrisk[$risklevel])?'['.$productrisk[$risklevel].']':'';
					}else{
						$data['fund_list']['data'][$key]['riskDes'] = '';
					}
					unset($data['fund_list'][$key]);
				}
			}
		}else{
			$data['fund_list']['data'] = array();
		}
		return $data;
	}
	
	//风险测试
	private function getRiskLevel($type = 0) {
		//调用接口获取用户风险等级
		if (!isset($_SESSION['riskLevel'])){
			$res = $this->fund_interface->AccountInfo();
			if (!empty($res['data']['custrisk'])) {
				$_SESSION['riskLevel'] = $res['data']['custrisk'];
			}else{
				$_SESSION['riskLevel'] = '05';
			}
		}
		if (1 == $type){
			return $_SESSION['riskLevel'];
		}else{
			$res['code'] = '0000';
			$res['msg'] = '个人信息查询成功';
			$this->load->config('jz_dict');
			$custrisk = $this->config->item('custrisk');
			if (isset($custrisk[$_SESSION['riskLevel']])){
				$res['data']['custrisk'] = 'R'.intval($_SESSION['riskLevel']);
				$res['data']['custriskname'] = $custrisk[$_SESSION['riskLevel']];
			}else{
				$res['data']['custrisk'] = $res['data']['custriskname'] = '-';
			}
		}
		return $res;
	}
	
	private function bonusChangeAbleList() {
		$bonusChangeList =  $this->fund_interface->bonus_changeable();
		if (!empty($bonusChangeList['data'])){
			foreach ($bonusChangeList['data'] as $key=>$val){
				$bonusChangeList['data'][$key]['json'] = base64_encode(json_encode($val));
			}
		}else{
			$bonusChangeList['data'] = array();
		}
		return $bonusChangeList;
	}
	
	//获取历史申请（委托）
	function getHistoryApply() {
		$post = $this->input->post();
		if (isset($_SESSION['JZ_user_id'])){
			if (1 == $_SESSION['JZ_user_id']){
				$startDate = empty($post['startDate']) ? date('Ymd',time()) : $post['startDate'];
				$endDate = (empty($post['endDate']) || $post['endDate'] == date('Ymd',time())) ? date('Ymd',time()+86400) : $post['endDate'];                  					 				 //因当天收市后下的单会归到下一天，因此结束时间加1天
				$fund_list = $this->fund_interface->Trans_applied($startDate, $endDate);
				file_put_contents('log/trade/Jz_fund'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).'客户:'.$_SESSION['customer_name'].'进行历史交易申请查询('.$startDate.'-'.$endDate.')'.serialize($fund_list)."\r\n\r\n",FILE_APPEND);
				if (isset($fund_list['code']) && '0000'==$fund_list['code']){
					if(isset($fund_list['data'])){
						foreach ($fund_list['data'] as &$val){
							unset($val['bankname'],$val['depositacct']);
							if (floatval($val['applicationamount']) == 0){
								$val['applicationamount'] = '--';
							}
							if (floatval($val['applicationvol']) == 0){
								$val['applicationvol'] = '--';
							}
						}
					}
					foreach ($fund_list['data'] as $val){
						if ( 1 == $val['cancelable'] ){
							$_SESSION['cancelableApply'][$val['appsheetserialno']] = $val;
						}
					}
					if (!empty($_SESSION['cancelableApply'])){
						foreach ($_SESSION['cancelableApply'] as &$val){
							unset($val['opertime'],$val['transactiondate'],$val['status'],$val['paystatus'],$val['cancelable']);
						}
					}
					$data = &$fund_list;
				}else{
					$data = array('code'=>'9999','msg'=>'系统错误，请稍后重试');
				}
			}else{
				if ( 0 == $_SESSION['JZ_user_id']){
					$data= array('code'=>'0002','msg'=>"您还未开通基金账户，不能进行相关查询");
				}else{
					$data = array('code'=>'0001','msg'=>"您还未登录，不能进行相关查询");
				}
			}
		}else{
			$data = array('code'=>'0001','msg'=>"您还未登录，不能进行相关查询");
		}
		echo json_encode($data);
	}
	
	function investorManagement($nexturl=''){
		$_SESSION['myPageOper'] = 'account';
		$post = $this->input->post();
		if (!empty($post)){
			if (empty($post['investorInfo'])){
				$this->load->model('Model_db');
//调用投资者信息录入接口和准入接口,成功后写入数据库
// 				$post['custtype'] = 11;
// 				$post['invtp'] = 1;
				$res = $this->fund_interface->SDCustomAssetInfo($post);
// var_dump(serialize($post));			
// var_dump($res);exit;
				$this->db->trans_start();
				$post['customerId'] = $_SESSION['customer_id'];
				$flag = $this->db->replace('p2_investorinfo', $post, 'customerId');
				$this->db->replace('p2_investorinfo',$post);
				$data['back_url'] = '/jijin/Jz_my';
/* 				$fundadmittance = $this->db->select('fundadmittance')->where(array('id'=>$_SESSION['customer_id']))->get('p2_customer')->row_array()['fundadmittance'];
				if (!$fundadmittance){
					$flag = $flag && $this->db->set(array('fundadmittance'=>1))->where(array('id'=>$_SESSION['customer_id']))->update('p2_customer');
					$data['back_url'] = '/jijin/Risk_assessment';
					$data['nextDes'] = '继续风险测评';
				} */
				$this->db->trans_complete();
				if ($flag){
					$data['ret_code'] = '0000';
					$data['ret_msg'] = '投资者信息提交成功!';
				}else{
					$data['ret_code'] = 'TJSB';
					$data['ret_msg'] = '投资者信息提交失败，请重试!';
				}
				$data['head_title'] = '提交结果';
				$data['base'] = $this->base;
				$this->load->view('ui/view_operate_result',$data);
			}else{
				$investorInfo = json_decode($post['investorInfo']);
				$this->getInvestorPageData($data['formData'], $investorInfo);
				$this->load->view('/jijin/account/editInvestorInfo',$data);
			}
		}else{
			if ($nexturl == 'Risk_assessment'){
				$data['infoMessage'] = '风险测评前，请完善投资者信息。';
			}
			$investorInfo = $this->db->select('annualincome,debt,investmentedu,investmentwork,execeptedearning,affordableloss,cerditrecord')->where(array('customerId'=>$_SESSION['customer_id']))->get('p2_investorinfo')->row_array();
			if (empty($investorInfo)){
				$investorInfo = $data['formData'] = array();
				$this->getInvestorPageData($data['formData'], $investorInfo);
				$this->load->view('/jijin/account/editInvestorInfo',$data);
			}else{
				$data['investorInfo'] = json_encode($investorInfo);
				$this->load->view('/jijin/account/investorInfo',$data);
			}
		}
	}
	
	private function getInvestorPageData(&$formData,&$investorInfo){
// 		$formData['custtype'] = array('des'=>'您是怎样的投资者？','select'=>array('1','2','3'));
// 		$formData['perinvesttype'] = array('des'=>'您的职业是？','select'=>array(0=>'未从事相关职业',1=>'专业投资者的高级管理人员、从事金融相关业务的注册会计师或律师'));
// 		$formData['perfinancialassets'] = array('des'=>'您的年末金融资产是多少？','select'=>array('25'=>'0元—50万','200'=>'50万—300万','400'=>'300万—500万','500'=>'500万以上'));
// 		$formData['peravgincome'] = array('des'=>'您近三年年均收入是多少？','select'=>array('5'=>'0元—10万','25'=>'10万—30万','50'=>'30万—100万','100'=>'100万以上'));
// 		$formData['perinvestexp'] = array('des'=>'您投资证券、基金、期货、黄金、外汇等的投资经历有几年？','select'=>array('0.5'=>'不满1年','2'=>'1年—3年','5'=>'3年—10年','10'=>'10年以上'));
// 		$formData['perinvestwork'] = array('des'=>'您从事金融产品设计、投资、风险管理及相关工作经历有几年？','select'=>array('0.5'=>'不满1年','2'=>'1年—3年','5'=>'3年—10年','10'=>'10年以上'));
		$formData['annualincome'] = array('des'=>'年收入？','select'=>array('2'=>'0元—2万','6'=>'2万—10万','35'=>'20万—50万','50'=>'50万以上'));
		$formData['debt'] = array('des'=>'债务(万元)');
		$formData['investmentedu'] = array('des'=>'投资学习经历');
		$formData['investmentwork'] = array('des'=>'投资工作经历');
		$formData['execeptedearning'] = array('des'=>'预期收益(%)');
		$formData['affordableloss'] = array('des'=>'可承受损失(%)');
		$formData['cerditrecord'] = array('des'=>'诚信记录是否良好','select'=>array('1'=>'是','0'=>'否'));
		if (!empty($investorInfo)){
			foreach ($investorInfo as $key=>$val){
				$formData[$key]['value'] = $val;
			}
		}
	}
}
