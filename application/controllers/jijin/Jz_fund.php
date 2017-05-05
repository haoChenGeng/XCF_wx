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
		$data = array();
		if ($activePage != 'buy' && $activePage != 'apply' && $activePage != 'today' && $activePage != 'history') {
			$activePage = isset($_SESSION['my_active_page'])? $_SESSION['my_active_page'] : 'buy';
		} else {
			$_SESSION['fund_active_page'] = $activePage;
		}
// 		$data = $this->getFundPageData($activePage);
		$data['base'] = $this->base;
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
					$enddate = date('Ymd',time()+86400);                  //因当天收市后下的单会归到下一天，因此结束时间加1天
					$data['today'] = $this->getHistoryApply($startdate, $enddate);
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
			$this->fund_interface->fund_list();
			$res = $this->db->get('fundlist')->result_array();
			$this->load->config('jz_dict');
			$i = 0;
			foreach ($res as $key => $val)
			{
				if (!empty($val) && $this->config->item('fund_status')[$val['status']][$type] == 'Y'){
					$json = array();
					$json['fundcode'] = $val['fundcode'];
					$json['fundname'] = $val['fundname'];
					$tmp = $this->config->item('fundtype')[$val['fundtype']];
					$json['fundtypename'] = is_null($tmp)?'-':$tmp;
					$json['nav'] = $val['nav'];
					$json['tano'] = $val['tano'];
					$json['taname'] = $val['taname'];
						
					$fund_list['data'][$i] = $json;
						
					$json['shareclasses'] = $val['shareclasses'];
					$tmp = isset($this->config->item('sharetype')[$val['shareclasses']])?$this->config->item('sharetype')[$val['shareclasses']]:null;
					$json['sharetypename'] = is_null($tmp)?'-':$tmp;
						
					$json['fundtype'] = $val['fundtype'];
					$json['risklevel'] = $val['risklevel'];
					if ($type == 'pre_purchase'){
						$json['first_per_min'] = $val['first_per_min_20'];
						$json['first_per_max'] = $val['first_per_max_20'];
						$json['con_per_min'] = $val['con_per_min_20'];
						$json['con_per_max'] = $val['con_per_max_20'];
					}else{
						$json['first_per_min'] = $val['first_per_min_22'];
						$json['first_per_max'] = $val['first_per_max_22'];
						$json['con_per_min'] = $val['con_per_min_22'];
						$json['con_per_max'] = $val['con_per_max_22'];
					}
					$fund_list['data'][$i]['json'] = base64_encode(json_encode($json));
					$i++;
				}
			}
		}
		return $fund_list;
	}
	
	//获取历史申请（委托）
	private function getHistoryApply($startDate = '',$endDate = '', $type = 0) {
		//调用接口
// $startDate = '20160103';
		$fund_list = $this->fund_interface->Trans_applied($startDate, $endDate);
		if (isset($_SESSION['customer_name'])){
			file_put_contents('log/trade/Jz_fund'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).'客户:'.$_SESSION['customer_name'].'进行历史交易申请查询('.$startDate.'-'.$endDate.')'.serialize($fund_list)."\r\n\r\n",FILE_APPEND);
		}
/* 		if ($type == 1){
			$transConfirmed = $this->fund_interface->Trans_confirmed($_SESSION['JZ_account'], $startDate, $endDate);
			file_put_contents('log/trade/Jz_fund'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).'客户:'.$_SESSION['JZ_account'].'进行历史交易确认查询('. $startDate.' - '.$endDate.')，返回信息：'.serialize($ConfirmedTrans)."\r\n\r\n",FILE_APPEND);
			if (isset($transConfirmed['code']) && $transConfirmed['code'] == '0000' && is_array($transConfirmed['data'])){
				$ConfirmedTrans = array();
				foreach ($transConfirmed['data'] as $val){
					if (!isset($ConfirmedTrans[$val['appsheetserialno']])){
						$ConfirmedTrans[$val['appsheetserialno']]['transactioncfmdate'] = $val['transactioncfmdate'];
						$ConfirmedTrans[$val['appsheetserialno']]['confirmedvol'] = $val['confirmedvol'];
						$ConfirmedTrans[$val['appsheetserialno']]['confirmedamount'] = $val['confirmedamount'];
						$ConfirmedTrans[$val['appsheetserialno']]['charge'] = $val['charge'];
					}
				}
			}
		} 
		if (isset($res['code']) && $res['code'] == '0000'){
			$this->load->config('jz_dict');
			$fund_list['code'] = '0000';
			$fund_list['msg'] = "无相关交易记录";
			$i = 0;
			$cancelableList = $this->getCancelableList();
			foreach ($res['data'] as $key => $val)
			{
				if (!empty($val)){
					$fund_list['data'][$i]['operdate'] = $val['operdate'];
					$fund_list['data'][$i]['fundname'] = $val['fundname'];
					$fund_list['data'][$i]['fundcode'] = $val['fundcode'];
					$fund_list['data'][$i]['applicationamount'] = $val['applicationamount'];
					$fund_list['data'][$i]['applicationvol'] = $val['applicationvol'];
					$fund_list['data'][$i]['appsheetserialno'] = $val['appsheetserialno'];
					$this->load->config('jz_dict');
					$tmp = isset($this->config->item('businesscode')[$val['businesscode']])?$this->config->item('businesscode')[$val['businesscode']]:$val['businesscode'];
					$fund_list['data'][$i]['businesscode'] = $tmp;
					$fund_list['data'][$i]['json'] = base64_encode(json_encode($fund_list['data'][$i]));
					if (isset($cancelableList[$val['appsheetserialno']])){
						$fund_list['data'][$i]['cancelable'] = 1;
					}else{
						$fund_list['data'][$i]['cancelable'] = 0;
					}
					$fund_list['data'][$i]['transactiondate'] = $val['transactiondate'];
					$tmp = isset($this->config->item('applaystatus')[$val['status']])?$this->config->item('applaystatus')[$val['status']]:$val['status'];
					$fund_list['data'][$i]['status'] = $tmp;
					$tmp = isset($this->config->item('paystatus')[$val['paystatus']])?$this->config->item('paystatus')[$val['paystatus']]:NULL;
					if ($tmp != NULL){
						$fund_list['data'][$i]['paystatus'] = $tmp;
					}
					if ($val['businesscode'] == '36'){
						$fund_list['data'][$i]['targetfundcode'] = $val['targetfundcode'];
					}
					if ($val['businesscode'] == '29'){
						$tmp = isset($this->config->item('dividendmethod')[$val['defdividendmethod']])?$this->config->item('dividendmethod')[$val['defdividendmethod']]:$val['defdividendmethod'];
						$fund_list['data'][$i]['defdividendmethod'] = $tmp;
					}
					if (isset($ConfirmedTrans[$val['appsheetserialno']])){
						$fund_list['data'][$i]['transactioncfmdate'] = $ConfirmedTrans[$val['appsheetserialno']]['transactioncfmdate'];
						$fund_list['data'][$i]['confirmedvol'] = $ConfirmedTrans[$val['appsheetserialno']]['confirmedvol'];
						$fund_list['data'][$i]['confirmedamount'] = $ConfirmedTrans[$val['appsheetserialno']]['confirmedamount'];
						$fund_list['data'][$i]['charge'] = $ConfirmedTrans[$val['appsheetserialno']]['charge'];
					}
					$i++;
				}
			}
		}else{
			file_put_contents('log/trade/Jz_fund'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).'客户:'.$_SESSION['JZ_account'].'进行历史交易申请查询('. $startDate.' - '.$endDate.')失败'."\r\n\r\n",FILE_APPEND);
		}*/
		return $fund_list;
	}
	
	public function showprodetail()
	{
		$get = $this->input->get();
// 		$post = $this->input->post();
		$fund_list = $this->db->where(array('fundcode' => $get['fundid']))->get('fundlist')->row_array();
		$this->load->config('jz_dict');
// 		$fund_list = $fund_list['data'][0];
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
		$data['json'] = $get['json'];
		$data['base'] = $this->base;
		$data['base'] = $this->base;
		$this->load->view('/jijin/trade/jijinprodetail', $data);
		
// 		if (!is_null($get['tano']) && !is_null($get['fundid'])) {
// 			$this->load->config('jz_dict');
// 			$fund_list = $this->fund_interface->fund($get['tano'], $get['fundid']);
// 			if (isset($fund_list['code']) && $fund_list['code'] = '0000' && $fund_list['data'][0]['fundcode'] == $get['fundid']) {
// 				$val = $fund_list['data'][0];
// 				$tmp = isset($this->config->item('fundtype')[$val['fundtype']])?$this->config->item('fundtype')[$val['fundtype']]:null;
// 				$val['fundtype'] = is_null($tmp)?'-':$tmp;
// 				$tmp = isset($this->config->item('sharetype')[$val['sharetype']])?$this->config->item('sharetype')[$val['sharetype']]:null;
// 				$val['sharetype'] = is_null($tmp)?'-':$tmp;
// 				$tmp = isset($this->config->item('fund_status')[$val['status']])?$this->config->item('fund_status')[$val['status']]['status']:null;
// 				$val['status'] = is_null($tmp)?'-':$tmp;
// 				$tmp = isset($this->config->item('custrisk')[intval($val['risklevel'])])?$this->config->item('custrisk')[intval($val['risklevel'])]:null;
// 				$val['risklevel'] = $val['risklevel'].'('.$tmp.')';
// 				$data['fundlist'] = $val;
// 				$data['purchasetype'] = $get['purchasetype'];
// 				$data['json'] = $get['json'];
// 				$data['base'] = $this->base;
// 				//$fundlist详细基金列表,用于手机传送,信息是否比较多,将来考虑较少详细信息,提高浏览速度
				
// 				$data['base'] = $this->base;
// 				$this->load->view('/jijin/trade/jijinprodetail', $data);
// 			}else{
// 				$log_msg = '接口返回信息：'.serialize($fund_list)."\r\n\r\n";
// 			}
// 		}else{
// 			$log_msg = "失败原因：输入参数不正确\r\n\r\n";
// 		}
// 		if (isset($log_msg)){
// 			$arr['head_title'] = '基金查询结果';
// 			$arr['ret_msg'] = '基金查询失败';
// 			$arr['back_url'] = '/jijin/Jz_fund';
// 			$arr['ret_code'] = 'CCCC';
// 			$arr['base'] = $this->base;
// 			$this->load->view('ui/view_operate_result',$arr);
// 			file_put_contents('log/trade/Jz_fund'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).'查询基金'.$get['fundid'].'详细信息失败'.$log_msg,FILE_APPEND);
// 		}
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
	
}
