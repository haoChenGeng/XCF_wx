<?php
if (!defined('BASEPATH')){
	exit('No direct script access allowed');
}
    
class Jz_fund extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper(array("comfunction"));   
        $this->load->library(array('Fund_interface','Logincontroller'));
    }
    
	//购买基金页面入口
	function index($activePage = 'fund')
	{
		$this->logincontroller->isLogin();
		$data = array();
		if ($activePage != 'buy' && $activePage != 'apply' && $activePage != 'today' && $activePage != 'history') {
			$activePage = isset($_SESSION['my_active_page'])? $_SESSION['my_active_page'] : 'buy';
		} else {
			$_SESSION['fund_active_page'] = $activePage;
		}
		$data['base'] = $this->base;
		if (isset($_SESSION['fundPageOper'])){
			$data['pageOper'] = $_SESSION['fundPageOper'];
			unset($_SESSION['fundPageOper']);
		}else{
			$data['pageOper'] = 'apply';
		}
		$this->load->view('jijin/buy_fund.php', $data);
	}
	
/* 	//获取“购买基金”页面的内容
	public function getFundPageData($activePage = 'buy', $startdate='', $enddate='') {
		$_SESSION['fund_active_page'] =  $activePage;
		switch ($activePage){
			case 'buy':                                                   //认购
				$data['buy'] = $this->getFundList('pre_purchase');
				break;
			case 'apply':                                                 //申购
				$data['apply'] = $this->getFundList('purchase');
				break;
			case 'today':
				if (isset($_SESSION['customer_id'])){
					$startdate = date('Ymd',time());
					$enddate = date('Ymd',time()+864000);                  //因当天收市后下的单会归到下一天，因此结束时间加1天
					$data['today'] = $this->getHistoryApply($startdate, $enddate);
					if(!empty($data['today']['data'])){
						$_SESSION['todayTrade'] = $data['today']['data'];
					}
				}else{
					$data['msg'] = "您还未登录，不能进行相关查询";
				}
				break;
			case 'history':
				if (isset($_SESSION['JZ_user_id'])){
					if ($enddate == date('Ymd',time())){                      //如果结束时间为当天
						$enddate = date('Ymd',time()+86400);                  //因当天收市后下的单会归到下一天，因此结束时间加1天
					}
					$data['history'] = $this->getHistoryApply($startdate, $enddate, 1);
				}else{
					$data['msg'] = "您还未登录，不能进行相关查询";
				}
				break;
		}
		echo json_encode($data);
	}
	
	function getFundList($type) {
		//调用接口
		$fund_list = array();
		$this->fund_interface->fund_list();
		if (!isset($_SESSION['qryallfund'])){
			$_SESSION['qryallfund'] = 0;
		}
		if ( 0 == $_SESSION['qryallfund'] && isset($_SESSION['riskLevel'])){
			$this->db->where(array('risklevel <='=>$_SESSION['riskLevel']));
		}
		$res = $this->db->select('status,fundcode,fundname,fundtype,nav,tano,taname,risklevel')->get('fundlist')->result_array();
		$this->load->config('jz_dict');
		$i = 0;
		foreach ($res as $key => $val)
		{
			if (!empty($val) && $this->config->item('fund_status')[$val['status']][$type] == 'Y'){
				$fund_list['data'][$i]['fundcode'] = $val['fundcode'];
				$fund_list['data'][$i]['fundname'] = $val['fundname'];
				$tmp = $this->config->item('fundtype')[$val['fundtype']];
				$fund_list['data'][$i]['fundtypename'] = is_null($tmp)?'-':$tmp;
				$fund_list['data'][$i]['nav'] = $val['nav'];
				$fund_list['data'][$i]['tano'] = $val['tano'];
				$fund_list['data'][$i]['taname'] = $val['taname'];
				$risklevel = $val['risklevel'];
				$fund_list['data'][$i]['risklevel'] = isset($this->config->item('productrisk')[$risklevel])?'['.$this->config->item('productrisk')[$risklevel].']':'';
				$i++;
			}
		}
		return $fund_list;
	} */
/* 	
	//获取历史申请（委托）
	private function getHistoryApply($startDate = '',$endDate = '', $type = 0) {
		//调用接口
		$fund_list = $this->fund_interface->Trans_applied($startDate, $endDate);
		if(isset($fund_list['data'])){
			foreach ($fund_list['data'] as $key=>$val){
				if (floatval($val['applicationamount']) == 0){
					$fund_list['data'][$key]['applicationamount'] = '--';
				}
				if (floatval($val['applicationvol']) == 0){
					$fund_list['data'][$key]['applicationvol'] = '--';
				}
			}
		}
		if (isset($_SESSION['customer_name'])){
			file_put_contents('log/trade/Jz_fund'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).'客户:'.$_SESSION['customer_name'].'进行历史交易申请查询('.$startDate.'-'.$endDate.')'.serialize($fund_list)."\r\n\r\n",FILE_APPEND);
		}
		return $fund_list;
	} */
	
	public function showprodetail()
	{
		$get = $this->input->get();
		$fund_list = $this->db->select('fundtype,fundname,fundcode,shareclasses,nav,navdate,growth_day,growthrate,fundincomeunit,status,risklevel,first_per_min_22,first_per_min_20')->where(array('fundcode' => $get['fundcode']))->get('fundlist')->row_array();
		if (2 == $fund_list['fundtype']){
			$fund_list['growth_day'] = round($fund_list['growthrate'],3);
			$fund_list['nav'] = $fund_list['fundincomeunit'];
			$data['field1'] = '七日年化收益率';
			$data['field2'] = '万份收益';
			$data['field3'] = '七日年化收益率走势(%)';
		}else{
			$data['field1'] = '日涨跌幅';
			$data['field2'] = '最新净值(元)';
			$data['field3'] = '净值走势(%)';
		}
		$this->load->config('jz_dict');
		$tmp = isset($this->config->item('fundtype')[$fund_list['fundtype']])?$this->config->item('fundtype')[$fund_list['fundtype']]:null;
		$fund_list['fundtype'] = is_null($tmp)?'-':$tmp;
		$tmp = isset($this->config->item('sharetype')[$fund_list['shareclasses']])?$this->config->item('sharetype')[$fund_list['shareclasses']]:null;
		$fund_status = $this->config->item('fund_status');
		if ($fund_status[$val['status']]['purchase'] == 'Y'){
			$data['purchasetype'] = '申购';
			$fund_list['firstMin'] = $fund_list['first_per_min_22'];
		}else{
			if ($fund_status[$val['status']]['pre_purchase'] == 'Y'){
				$data['purchasetype'] = '认购';
				$fund_list['firstMin'] = $fund_list['first_per_min_20'];
			}
		}
		$fund_list['sharetype'] = is_null($tmp)?'-':$tmp;
		$tmp = isset($this->config->item('fund_status')[$fund_list['status']])?$this->config->item('fund_status')[$fund_list['status']]['status']:null;
		$fund_list['status'] = is_null($tmp)?'-':$tmp;
		$productrisk = $fund_list['risklevel'];
		$tmp = isset($this->config->item('productrisk')[$productrisk])?$this->config->item('productrisk')[$productrisk]:null;
		$fund_list['risklevel'] = 'R'.$productrisk.'('.$tmp.')';
		$data['fundlist'] = $fund_list;
		$data['base'] = $this->base;
		$data['next_url'] = isset($get['next_url']) ? $get['next_url'] : '/jijin/Jz_fund/index/fund';
		$this->load->view('/jijin/trade/prodetail', $data);
	}
	
/* 	private function getCancelableList(){
		$cancelable_list = $this->fund_interface->cancelable($_SESSION['JZ_account']);
		if (isset($cancelable_list['code']) && $cancelable_list['code'] = '0000') {
			foreach ($cancelable_list['data'] as $key => $val)
			{
				if (isset($val['appsheetserialno'])){
					$applyList[$val['appsheetserialno']] = 1;
				}
			}
		}else{
			$log_msg = '接口返回信息：'.serialize($cancelable_list)."\r\n\r\n";
		}
		if (isset($log_msg)){
			file_put_contents('log/trade/Jz_fund'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).'进行'.$type.'查询客户'.$_SESSION['XN_acconut'].'可撤单列表出错,'.$log_msg,FILE_APPEND);
		}
		return $applyList;
	} */
	
	function getFundCurve(){
		$get = $this->input->get();
		$tableName = 'p2_netvalue_'.$get['fundCode'];
		$startDate = date('Y-m-d',time());
		$startDate = (substr($startDate,0,4)-1).substr($startDate,4);
		$fundtype = $this->db->select('fundtype')->where(array('fundCode'=>$get['fundCode']))->get('p2_fundlist')->row_array()['fundtype'];
		if (2 == $fundtype){
			$select = 'net_date,round(growthrate,3) as net_day_growth';
		}else{
			$select = 'net_date,net_day_growth';
		}
		$fundCure = $this->db->select($select)->where('net_date>',$startDate)->order_by('net_date','DESC')->get($tableName)->result_array();
		if (!empty($fundCure) && is_array($fundCure)){
			$return = array('code'=>0,'data'=>&$fundCure);
		}else{
			$return = array('code'=>1,'msg'=>'数据不存在');
		}
		$return['fundtype'] = $fundtype;
		echo json_encode($return);
	}
	
	public function viewAllFund(){
		$post = $this->input->post();
		if (isset($_SESSION ['customer_id'])){
			if (isset($post['allow'])){
				$res = $this->fund_interface->SDQryAllFund($post['allow']);	//$post['allow']
				if (isset($res['code']) && '0000'==$res['code']){
					$flag = $this->db->set(array('qryallfund'=>$post['allow']))->where(array('id'=>$_SESSION ['customer_id']))->update('p2_customer');
					if ($flag){
						$message = '修改成功';
						$flag = 'sucess';
						$_SESSION['qryallfund'] = $post['allow'];
					}else{
						$message = '修改失败';
						$flag = 'fail';
					}
				}else{
					$message = '修改失败';
					$flag = 'fail';
				}
			}else{
				$this->load->view('jijin/trade/viewAllFund');
			}
		}else{
			$flag = 'fail';
			$message = '您尚未登录，不能做相关修改';
		}
		if (isset($message)){
			$this->load->helper(array("output"));
			Message(Array(
					'msgTy' => $flag,
					'msgContent' => $message,
					'msgUrl' => '/jijin/jz_fund',                           //调用my界面
					'base' => $this->base
					));
		}
	}
	
	//获取“购买基金”页面的内容,按基金类型分类
	public function getFundData() {
		$post = $this->input->post();
		$fundtype = isset($post['fundtype']) ? $post['fundtype'] : '2';
		$data['code'] = '0000';
		if (isset($_SESSION['customer_name'])){
			if (isset($_SESSION['qryallfund'])){
				$data['qryallfund'] = $_SESSION['qryallfund'];
			}
		}
		else {
			$data['qryallfund'] = -1;
		}
		$this->load->config('jz_dict');
		$data['fundTypes'] = $this->config->item('fundtype');
		$this->fundClassify($data['data'],$fundtype);
		echo json_encode($data);
	}
	
	//对获取到的基金数据按类型进行分类
	private function fundClassify(&$classifyFund,$fundtype){
		$classifyFund = array();
		$this->fund_interface->fund_list();
		if (!isset($_SESSION['qryallfund'])){
			$_SESSION['qryallfund'] = 0;
		}
		if ( 0 == $_SESSION['qryallfund'] && isset($_SESSION['riskLevel'])){
			$this->db->where(array('risklevel <='=>$_SESSION['riskLevel']));
		}
		if ( 2 == $fundtype ){
			$this->db->select('status,fundcode,fundname,fundincomeunit,growthrate')->order_by('growthrate',"DESC");
		}else{
			$this->db->select('status,fundcode,fundname,nav,growth_day,growth_week,growth_onemonth,growth_threemonth,growth_sixmonth,growth_year')->order_by('growth_year',"DESC");
		}
		$res = $this->db->where(array('fundtype'=>$fundtype))->get('fundlist')->result_array();
		$this->load->config('jz_dict');
		$productrisk = $this->config->item('productrisk');
		foreach ($res as $key => &$val)
		{
			if (!empty($val)){
				unset($val['status']);
				$classifyFund[$fundtype][] = $val;
			}
		}
	}
	
}
