
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PrivateFund extends MY_Controller {
	function __construct() {
		parent::__construct ();
		$this->load->model(array("Model_pageDeal") );
	}
	
	public function fund_list($type)
	{

	    $fund = $this->db->where(array('type'=>$type))->get('privatefund')->result_array();
	    if (empty($fund)){
        echo null;
	    }else{
	    	echo json_encode($fund);
	    }

	}

}