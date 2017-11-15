<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class OrderInfo extends MY_Controller {
	function __construct() {
		parent::__construct ();
		$this->load->model(array("Model_pageDeal") );
	}
	
	public function order_add()
	{
		$post = $this->input->post();
		//先判断数据库中是否有重复的数据
		$returnData = $this->db->select('custname,fundid')->where(array('custphone'=>$post['custphone'],'fundid'=>$post['fundid']))->get('orderinfo')->result_array();
		if(count($returnData)>0){
			echo json_encode(array('code'=>'9999','msg'=>'您已经预约过该基金！')); exit;
		}
		$insert_data = array(
				'custname' => $post['custname'],
				'custphone' => $post['custphone'],
				'fundid' =>  $post['fundid'],
				'fundname'=>  $post['fundname'],
				'orderdate' => date("Y-m-d")
		);
		$insert_res = $this->db->insert('orderinfo',$insert_data);   //写入数据库
		if ($insert_res){
			echo json_encode(array('code'=>'0000','msg'=>'私募基金预约成功'));
		}else{
			echo json_encode(array('code'=>'9999','msg'=>'私募基金预约失败'));
		}
	}

}