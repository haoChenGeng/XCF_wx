<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );

class AutoUpdate extends MY_Controller {

	function __construct() {
		parent::__construct ();
		$this->load->helper ( array('comfunction'));
	}
	
	public function index() {
		$this->updatePlannerInfo();
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
	
}