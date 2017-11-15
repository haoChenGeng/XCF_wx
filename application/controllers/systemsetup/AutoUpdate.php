<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );

class AutoUpdate extends MY_Controller {

	function __construct() {
		parent::__construct ();
		set_time_limit(1800);
		$this->load->helper ( array('comfunction'));
		$this->load->library(array('Fund_interface'));
	}
	
	public function index() {
		$this->fund_interface->autoUpdateJZInfo();		//更新p2_fundlist信息
		$tableNames = array('hsindexvalue'=>'fund_hsindexvalue','fundmanager'=>'fund_manager','fundmanagerinfo'=>'fund_managerinfo',
							'funddistribution'=>'fund_distribution','fundposition'=>'fund_position');
		foreach ($tableNames as $key => $val){			//更新基金其它信息
			$returnData = $this->fund_interface->autoUpdateJZInfo($val);
			if (!empty($returnData['data'])){
// 				$this->db->truncate($key);
				$this->db->where('1=1')->delete($key);
				$this->db->insert_batch($key,$returnData['data']);
			}
			if ($key == 'hsindexvalue'){
				$this->updateHsindexCurve();
			}
		}
		$this->updatePlannerInfo();						//更新理财师信息
	}

	function updatePlannerInfo(){
		$this->load->config('jz_dict');
		$plannerInfo = json_decode(comm_curl($this->config->item('XNPlannerUrl'),array()),true)[0];
		if ('0000' == $plannerInfo['code']){
			$newData = array();
			foreach ($plannerInfo['data'] as &$val){
				$newData[] = array('FName'=>$val['name'],'EmployeeID'=>$val['workNum'],'status'=>1,'area'=>$val['area'],'city'=>$val['city']); 
			}
			if (!empty($newData)){
				$this->load->model("Model_db");
				$this->db->set(array('status'=>0))->update('p2_planner');
				$this->Model_db->incremenUpdate('p2_planner',$newData,'EmployeeID');
			}
		}
	}
	
	private function updateHsindexCurve(){
		$this->db->set(array("oneMonth"=>-1000,"threeMonth"=>-1000,"sixMonth"=>-1000,"oneYear"=>-1000))->update("p2_hsindexvalue");
		$period = array(date("Y-m-d", strtotime("-1 month")),
				date("Y-m-d", strtotime("-3 month")),
				date("Y-m-d", strtotime("-6 month")),
				date("Y-m-d", strtotime("-1 year")));
		$hsindex = $this->db->select("TradingDay,IndexValue")->where(array("TradingDay >="=>$period[3]))->order_by("TradingDay","ASC")->get("p2_hsindexvalue")->result_array();
		$i = 3;
		$j = 0;
		$fields = array("oneMonth","threeMonth","sixMonth","oneYear");
		$netBase[3] = current($hsindex);
		foreach ($hsindex as $val){
			while ($i > 0 && $val['TradingDay'] >= $period[$i-1]){
				$i --;
				$netBase[$i] = $val;
			}
			$newData[$j]['TradingDay'] = $val['TradingDay'];
			for ($ii = 3; $ii >= $i; $ii--){
				$newData[$j][$fields[$ii]] = round(100*($val['IndexValue']-$netBase[$ii]['IndexValue'])/$netBase[$ii]['IndexValue'],2);
			}
			for (; $ii>=0; $ii--){
				$newData[$j][$fields[$ii]] = -1000;
			}
			$j++;
		}
		$this->load->model("Model_db");
		$this->Model_db->incremenUpdate("p2_hsindexvalue", $newData, 'TradingDay');
	}
}