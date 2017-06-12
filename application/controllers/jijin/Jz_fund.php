<?php
if (!defined('BASEPATH')){
	exit('No direct script access allowed');
}
    
class Jz_fund extends MY_Controller
{
    private $logfile_suffix;
    function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper(array("comfunction"));   
        $this->load->library(array('Fund_interface','Logincontroller'));
        $this->logfile_suffix = '('.date('Y-m',time()).').txt';
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
// 		$data = $this->getFundPageData($activePage);
		$data['base'] = $this->base;
		if (isset($_SESSION['fundPageOper'])){
			$data['pageOper'] = $_SESSION['fundPageOper'];
			unset($_SESSION['fundPageOper']);
		}else{
			$data['pageOper'] = 'apply';
		}
		$this->load->view('jijin/buy_fund.html', $data);
	}
	
	//获取“购买基金”页面的内容
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
		if ($this->getAllFundInfo()){
			$res = $this->db->get('fundlist')->result_array();
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
					$i++;
				}
			}
		}
		return $fund_list;
	}
	
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
	}
	
	public function showprodetail()
	{
		$get = $this->input->get();
		$fund_list = $this->db->where(array('fundcode' => $get['fundcode']))->get('fundlist')->row_array();
		$this->load->config('jz_dict');
		$tmp = isset($this->config->item('fundtype')[$fund_list['fundtype']])?$this->config->item('fundtype')[$fund_list['fundtype']]:null;
		$fund_list['fundtype'] = is_null($tmp)?'-':$tmp;
		$tmp = isset($this->config->item('sharetype')[$fund_list['shareclasses']])?$this->config->item('sharetype')[$fund_list['shareclasses']]:null;
		$fund_list['sharetype'] = is_null($tmp)?'-':$tmp;
		$tmp = isset($this->config->item('fund_status')[$fund_list['status']])?$this->config->item('fund_status')[$fund_list['status']]['status']:null;
		$fund_list['status'] = is_null($tmp)?'-':$tmp;
		$tmp = isset($this->config->item('custrisk')[intval($fund_list['risklevel'])])?$this->config->item('custrisk')[intval($fund_list['risklevel'])]:null;
		$fund_list['risklevel'] = $fund_list['risklevel'].'('.$tmp.')';
		$data['fundlist'] = $fund_list;
		$data['purchasetype'] = $get['purchasetype'];
		if ($get['purchasetype'] == '申购'){
			$_SESSION['fundPageOper'] = 'apply';
		}elseif($get['purchasetype'] == '认购'){
			$_SESSION['fundPageOper'] = 'buy';
		}
		$data['base'] = $this->base;
		$data['next_url'] = isset($get['next_url']) ? $get['next_url'] : '/jijin/Jz_fund/index/fund';
		$this->load->view('/jijin/trade/prodetail', $data);
	}
	
	private function getCancelableList(){
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
	}

	private function getAllFundInfo(){
		$needtime = strtotime(date('Y-m-d',time()-32390).' 09:00:00');                //32400 = 3600*9-10  即8小时59分50秒  自动更新的时间点设为9:00所以提前10秒允许更新
		$updatetime = $this->db->where(array('dealitem' => 'fundlist'))->get('dealitems')->row_array()['updatetime'];
		if ($updatetime < $needtime){
			$res = $this->fund_interface->fund_list();
			var_dump($res);
			file_put_contents('log/trade/Jz_fund'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).'进行基金列表查询，返回信息：'.serialize($res)."\r\n\r\n",FILE_APPEND);
			$flag = TRUE;
			if (isset($res['code']) && $res['code'] == '0000' && isset($res['data'][0]) && !empty($res['data'][0])){
				$this->load->config('jz_dict');
				$dbFields = $this->db->list_fields('fundlist');
				$singleFund = $res['data'][0];
				foreach ($dbFields as $key=>$val){
					if (!isset($singleFund[$val])){
						unset($dbFields[$key]);
					}
				}
				$dbFields[] = 'fundincomeunit';
				$dbFunds = $this->db->get('fundlist')->result_array();
				$dbFunds = setkey($dbFunds,'fundcode');
				$insertFund = array();
				$i = 0;
				foreach ($res['data'] as $key => $val)
				{
					$fundinfo = $this->fund_interface->fund($val['tano'], $val['fundcode']);
					file_put_contents('log/trade/Jz_fund'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).'查询基金'.$val['fundcode']."返回信息为:".serialize($fundinfo)."\r\n\r\n",FILE_APPEND);
					if (isset($fundinfo['code']) && $fundinfo['code'] == '0000' && isset($fundinfo['data'][0]['fundincomeunit'])){
						$val['fundincomeunit'] = $fundinfo['data'][0]['fundincomeunit'];
					}
					$updateFund = array();
					if (isset($dbFunds[$val['fundcode']])){
						foreach ($dbFields as $v){
							if (isset($val[$v]) && $dbFunds[$val['fundcode']][$v] != $val[$v]){
								$updateFund[$v] = $val[$v];
							}
						}
						if (!empty($updateFund)){
							$flag =  $flag && $this->db->set($updateFund)->where(array('fundcode'=>$val['fundcode']))->update('fundlist');
						}
					}else{
						foreach ($dbFields as $v){
							$insertFund[$i][$v] = $val[$v];
						}
						$i++;
					}
				}
				if (!empty($insertFund)){
					$flag =  $flag && $this->db->insert_batch('fundlist',$insertFund);
				}
			}else{
				$flag = FALSE;
			}
			if ($flag){
				$this->db->set(array('updateTime' => time()))->where(array('dealitem' => 'fundlist'))->update('dealitems');
			}
			return $flag;
		}else{
			return TRUE;
		}
	}
	
	function getFundCurve(){
		$get = $this->input->get();
		$tableName = 'p2_netvalue_'.$get['fundCode'];
		$startDate = date('Y-m-d',time());
		$startDate = (substr($startDate,0,4)-1).substr($startDate,4);
		$fundCure = $this->db->select('net_date,net_day_growth')->where('net_date>',$startDate)->order_by('net_date','DESC')->get($tableName)->result_array();
		if (!empty($fundCure) && is_array($fundCure)){
			$return = array('code'=>0,'data'=>&$fundCure);
		}else{
			$return = array('code'=>1,'msg'=>'数据不存在');
		}
		echo json_encode($return);
	}
}
