<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );

class AutoUpdate extends MY_Controller {

	function __construct() {
		parent::__construct ();
		$this->load->helper ( array('comfunction'));
		$this->load->library(array('Fund_interface'));
	}
	
	public function index() {
		$tableNames = array('hsindexvalue'=>'fund_hsindexvalue','fundmanager'=>'fund_manager','fundmanagerinfo'=>'fund_managerinfo');
		foreach ($tableNames as $key => $val){
			$returnData = $this->fund_interface->autoUpdateJZInfo($val);
			if (!empty($returnData['data'])){
				$this->db->truncate($key);
				$this->db->insert_batch($key,$returnData['data']);
			}
		}
	}

	function updatePlannerInfo(){
		$this->load->config('jz_dict');
		$plannerInfo = json_decode(comm_curl($this->config->item('XNPlannerUrl'),array()),true)[0];
		if ('0000' == $plannerInfo['code']){
			foreach ($plannerInfo['data'] as &$val){
				$newData[] = array('FName'=>$val['name'],'EmployeeID'=>$val['workNum'],'status'=>1); 
			}
			$this->load->model("Model_db");
			$this->db->set(array('status'=>0))->update('p2_planner');
			$this->Model_db->incremenUpdate('p2_planner',$newData,'EmployeeID');
		}
	}
	
	function updateFundData(){
				
	}
	
}