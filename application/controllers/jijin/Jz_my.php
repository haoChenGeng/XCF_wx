<?php
if (!defined('BASEPATH')){
	exit('No direct script access allowed');
}
    
class Jz_my extends MY_Controller
{
    private $logfile_suffix;
    function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library(array('Logincontroller'));
        $this->logfile_suffix = '('.date('Y-m',time()).').txt';
    }

	//我的基金页面入口
	function index($activePage = 'fund')
	{
		
		if (!$this->logincontroller->isLogin()) {
			$_SESSION['next_url'] = $this->base.'/jijin/Jz_my';
// 			$this->logincontroller->login();
		}
		
		$data = array();
		
		if ($activePage != 'fund' && $activePage != 'bank_card' && $activePage != 'risk_test' && $activePage != 'history') {
			$activePage = isset($_SESSION['my_active_page'])? $_SESSION['my_active_page'] : 'fund';
		} else {
			$_SESSION['my_active_page'] = $activePage;
		}
		
		$data['base'] = $this->base;
		$this->load->view('jijin/my.html', $data);
	}
	
	//获取“我的基金”页面的内容
	public function getMyPageData($activePage = 'fund') {
		
		if (!$this->logincontroller->isLogin()) {
			echo(json_encode(array('errorMsg'=>true)));
			exit;
		}
		
		if (isset($_SESSION['JZ_user_id'])) {
			$_SESSION['my_active_page'] =  $activePage;
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
						$data['custrisk'] = $res['data'][0]['custrisk'] ;
						$data['custriskname'] = $res['data'][0]['custriskname'] ;
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
					$data['errorMsg'] = '未登录';
			}
		}else {
			$data['errorMsg'] = '未登录';
		}
// file_put_contents('log/debug'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).'获取“我的基金”页面的内容'.serialize($data)."\r\n\r\n",FILE_APPEND);
		echo json_encode($data);
// 			if ($activePage == 'fund') {
				//已购基金列表
/* 				$res = $this->getMyFundList();
				$res = $this->getMyFundList();
				$data['totalfundvolbalance'] = 0;
				$data['totalfundmarketvalue'] = 0;
				$data['fund_list'] = null;
				if (isset($res['code']) && $res['code']=='0000' && !empty($res['data'][0])) {
					$data['totalfundvolbalance'] = $res['totalfundvolbalance_mode1'];
					$data['totalfundmarketvalue'] = $res['totalfundmarketvalue_mode1'];
					$data['fund_list'] = $res;
					 	
				}*/
// 			} else if ($activePage == 'bonus_change') {
/* 				$res = $this->bonusChangeAbleList();
				$data['bonus_change'] = $res; */
// 			} else if ($activePage == 'bank_card') {
/* 				//获取银行卡
				$res = $this->bank_info();
				
				//对res进行验证
				
				$data['bank_info'] = $this->bank_info(); */
// 			} else if ($activePage == 'risk_test') {
/* 				//获取风险测试
				$res = $this->getRiskLevel();
				
				if (isset($res['code']) && isset($res['msg']) && isset($res['data']) && !empty($res['data'])) {
					$data['custrisk'] = $res['data'][0]['custrisk'] ;
					$data['custriskname'] = $res['data'][0]['custriskname'] ;
				} else {
					$data['custrisk'] = '查询失败';
				} */
// 			} else if ($activePage == 'history') {
				//获取历史记录
/* 				$res = $this->getTodayTran();
				if (isset($res['errorMsg'])) {
					$data['hisErrorMsg'] = $res['errorMsg'];
				} else {
					if (isset($res['code']) && isset($res['msg']) && isset($res['data']) && !empty($res['data'])) {
						$data['history_tran'] = $res;
					} else {
						$data['hisErrorMsg'] = '查询失败';
					}
				} */
// 			}

		// return $data;
	}
	
	//获取已购基金列表和总资产
	private function getMyFundList() {
		
		//调用接口
		$res = $this->jz_interface->asset($_SESSION['JZ_account'],1,'');
		
		file_put_contents('log/trade/Jz_my'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).'客户:'.$_SESSION['JZ_account'].'进行资产查询，返回数据为'.serialize($res)."\r\n\r\n",FILE_APPEND);
		$data['totalfundvolbalance'] = 0;
		$data['totalfundmarketvalue'] = 0;
		$fund_list['code'] = $res['code'];
		$fund_list['msg'] = $res['msg' ];
		$data['fund_list'] = null;
		if (isset($res['code']) && $res['code']=='0000' && !empty($res['data'][0])) {
			$data['totalfundvolbalance'] = $res['data'][0]['totalfundvolbalance_mode1'];
			$data['totalfundmarketvalue'] = $res['data'][0]['totalfundmarketvalue_mode1'];
			$this->load->config('jz_dict');
			$i = 0;
			foreach ($res['data'] as $key => $val)
			{
				if (!empty($val)){
					$list['fundcode'] = $val['fundcode'];
					$list['fundname'] = $val['fundname'];
					$list['fundvolbalance'] = $val['fundvolbalance'];
					$list['nav'] = $val['nav'];
					$tmp = $this->config->item('fundtype')[$val['fundtype']];
					$list['fundtypename'] = is_null($tmp)?'-':$tmp;
					$list['redeem'] = $this->config->item('fund_status')[$val['status']]['redeem'];
					$fund_list['data'][$i] = $list;
					unset($list['redeem']);
					unset($list['nav']);
					$list['sharetype'] = $val['sharetype'];
					$list['transactionaccountid'] = $val['transactionaccountid'];
					$list['fundfrozenbalance'] = $val['fundfrozenbalance'];
					$list['availablevol'] = $val['availablevol'];
					$list['branchcode'] = $val['branchcode'];
					$list['tano'] = $val['tano'];
					$fund_list['data'][$i]['json'] = base64_encode(json_encode($list));
// 					$fund_list['data'][$i]['risklevel'] = $res['data'][$key]['risklevel'];
// 					$fund_list['totalfundmarketvalue'] = $val['totalfundmarketvalue'];
// 					$fund_list['totalfundmarketvalue'] = $val['totalfundmarketvalue'];
// 					$fund_list['totalfundvolbalance_mode1'] = $val['totalfundvolbalance_mode1'];
// 					$fund_list['totalfundmarketvalue_mode1'] = $val['totalfundmarketvalue_mode1'];
					$i++;
				}
			}
			$data['fund_list'] = $fund_list;
		}
		
/* 		for ($i=0;$i<count($res['data']);$i++) {
			if (!empty($res['data'][i])) {
				$tmp = $this->config->item('fundtype')[$res['data'][$i]['fundtype']];
				$res['data'][$i]['fundtypename'] = is_null($tmp)?'-':$tmp;
				
				$tmp = $this->config->item('sharetype')[$res['data'][$i]['sharetype']];
				$res['data'][$i]['sharetypename'] = is_null($tmp)?'-':$tmp;
			}
		} */
// file_put_contents('log/debug'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).serialize($res)."\r\n\r\n",FILE_APPEND);		
		return $data;
	}
	
	//风险测试
	private function getRiskLevel() {
		//调用接口
		$res = $this->jz_interface->account_info($_SESSION['JZ_account']);
		
		
		$this->load->config('jz_dict');
		for ($i=0;$i<count($res['data']);$i++) {
			if (!empty($res['data'][$i])) {
				$tmp = null;
				$tmp = $this->config->item('custrisk')[(int)$res['data'][$i]['custrisk']];
				$res['data'][$i]['custriskname'] = is_null($tmp)?'-':$tmp;
			}
		}
		
		return $res;
	}
	
	
	private function bonusChangeAbleList() {
		$custno = $_SESSION['JZ_account'];
		$res =  $this->jz_interface->bonus_changeable($custno);
		
		$this->load->config('jz_dict');
		$bonusChangeList = array();
		for ($i=0;$i<count($res['data']);$i++) {
			if (!empty($res['data'][$i])) {
				$tmp = null;
				$tmp = $this->config->item('sharetype')[$res['data'][$i]['sharetype']];
				$sharetypename = is_null($tmp)?'-':$tmp;
					
				$tmp = null;
				$tmp = $this->config->item('dividendmethod')[$res['data'][$i]['dividendmethod']];
				$dividendmethodname = is_null($tmp)?'-':$tmp;
				
				$bonusChangeList['data'][$i]['fundname'] = $res['data'][$i]['fundname'];
				$bonusChangeList['data'][$i]['dividendmethodname'] = $dividendmethodname;
				$bonusChangeList['data'][$i]['sharetypename'] = $sharetypename;
				$bonusChangeList['data'][$i]['fundcode'] = $res['data'][$i]['fundcode'];
				$bonusChangeList['data'][$i]['nav'] = $res['data'][$i]['nav'];
				$bonusChangeList['data'][$i]['sharetype'] = $res['data'][$i]['sharetype'];
				$bonusChangeList['data'][$i]['dividendmethod'] = $res['data'][$i]['dividendmethod'];
				$bonusChangeList['data'][$i]['transactionaccountid'] = $res['data'][$i]['transactionaccountid'];
				$bonusChangeList['data'][$i]['branchcode'] = $res['data'][$i]['branchcode'];
				$bonusChangeList['data'][$i]['json'] = base64_encode(json_encode($bonusChangeList['data'][$i]));
			}
		}		
		return $bonusChangeList;
	}
	

	
	//获取当天交易
	private function getTodayTran() {
		$startDate = date('ymd',time());
		$res = $this->getHistoryTran($startDate,$startDate);
		return $res;
	}


	//获取历史交易
	function getHistoryTran($startDate = '',$endDate = '') {
		
		if (!$this->logincontroller->isLogin()) {
			echo(json_encode(array('errorMsg'=>true)));
			exit;
		}
		
		if (empty($startDate)) {
			$res['errorMsg'] = '请输入开始时间';
		}
	
		if (strtotime($startDate) === false) {
			$res['errorMsg'] = '开始日期格式有误';
		}
	
		if (empty($endDate)) {
			$res['errorMsg'] = '请输入结束时间';
		}
	
		if (strtotime($endDate) === false) {
			$res['errorMsg'] = '结束日期格式有误';
		}
	
		//调用接口
		$res = $this->jz_interface->Trans_confirmed($_SESSION['JZ_account'], $startDate, $endDate, 25 ,700001, 1000);
	
		
		$this->load->config('jz_dict');
		for ($i=0;$i<count($res['data']);$i++) {
			if (!empty($res['data'][$i])) {
				$res['data'][$i]['businessnote'] = $this->config->item('businesscode')[$res['data'][$i]['businesscode']];
			}
		}
		
		// return $res;
		echo json_encode($res);
	}

}
