<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PrivateFundType extends MY_Controller {
	function __construct() {
		parent::__construct ();
		$this->load->model(array("Model_pageDeal") );
	}
	
	public function type_list()
	{
	
		$type = $this->db->get('privatefund_type')->result_array();
		if (empty($type)){
			echo null;
		}else{
			echo json_encode($type);
		}
	
	}

}