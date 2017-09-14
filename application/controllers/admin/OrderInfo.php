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
	
	public function index($accessCode) {
		$data['accessCommand'] = current($_SESSION['accessList'][$accessCode]);
		$this->Model_pageDeal->isLogin();                                                //判断是否登录
		if (!$this->Model_pageDeal->menuAuthorityCheck($data['accessCommand'])){         //权限控制
			exit;
		}
		
		//设置页面标题
		$data['heading_title'] = '私募基金预约信息';
		//设置本页面的链接地址
		$data['accessUrl'] = $this->unifyEntrance.$accessCode;
		//设置页面导航内容
		$data['breadcrumbs'][] = array(	'text' => '首页', 'href' => $this->config->item('home_page'));
		$data['breadcrumbs'][] = array(	'text' => $data['heading_title'], 'href' => $this->base.$data['accessUrl']);
		//设置页面所提供的操作
		$oper_arr = array('oper_add' => 'operadd', 'oper_edit'=> 'operedit', 'oper_delete' => 'operdelete');
		//设置数据库表名
		$data['tableName'] = 'orderinfo';
		//设置选择记录时，获取哪个字段的值
		$data['selcet_key'] = 'id';
		//设置页面操作内容描述
		$data['operContent'] ='私募基金预约信息';
		//选择输入值
		$input = $this->input->post();
		//依据$input['selectoper']设置需要跳转的函数
// 		if (isset($input['selectoper'])){
// 			if (!$this->Model_pageDeal->menuAuthorityCheck($data['accessCommand'],$input['selectoper'])){     //进行功能调用的权限检查
// 				exit;
// 			}
// 			$func = getfunction($input['selectoper'],$oper_arr);
// 		}else{
			$func = 'operdefault';
// 		}
		
		

		//调用对应的函数,并返回需要加载的内容页面
		if (method_exists($this, $func)){
			$operdefaultpage = $this->$func($input, $data);
		}else{
			echo '调用的方法不存在，请检查'; exit;
		}
		//加载页面
		$this->Model_pageDeal->getPageView($operdefaultpage,$data);
	}

	//默认操作，获取需要展示的数据
	private function operdefault(&$input,&$data) {
		//设置页面标题
		$data['heading_title'] = '私募基金预约信息';
		//设置搜索栏选项   例如('name'搜索项的数据库字段名,'filterType'搜索方式 =where =like 分别对应数据库查询的where和like, 'description'页面显示的搜索项名称)
		
		
// 		$filters = array('fundname' => array('filterType' => 'like', 'description' => '基金名称')
				
// 		);
// 		data['query'] = array('name'=> 'querydate', 'val'=> date('Y-m-d', $input['querydate']), 'description' => '日期');
		
// 		$data['query']
		
		$data['query'] = array('orderdate' => array('filterType' => 'dates', 'description' => '预约日期'));
		
		//$dates[] = array('name'=> 'querydate', 'val'=> date('Y-m-d', $input['querydate']), 'description' => '日期');
		//获取页面的其他相关信息，并获得$filter_data 即数据库查询条件
		//$filter_data = $this->Model_common->getPageData($input, $data, $filters, $dates);
		
		//'val'=> date('Y-m-d', $input['querydate']), 'description' => '日期'
		//设置页面需要查询的数据库字段名
		$data['dbFields'] = array('id','orderdate','custname', 'custphone','fundid','fundname');
		//设置页面显示的字段名称
		$data['table_field']=array('id' => array('sort' => 1, 'description' => 'ID'),
				'orderdate' => array('sort' => 1, 'description' => '预约日期'),
				'custname' => array('sort' => 1, 'description' => '客户姓名'),
				'custphone' => array('sort' => 1, 'description' => '客户手机号码'),
				'fundid' => array('description' => '预约产品ID'),
				'fundname' => array('description' => '预约产品名称'),
		);
		//设置页面右上角导航条按钮(函数中已进行权限检查)
		$data['buttons'] = $this->Model_pageDeal->getButtonList($data['accessCommand']);
		//设置form提交的url
		$data['form_action'] = $this->base.$data['accessUrl'];
		//获取页面的其他相关信息，并获得$filter_data 即数据库查询条件
		$filter_data = $this->Model_pageDeal->getPageData($input, $data);
		
		if(isset($input['orderdate'])){
		   $filter_data['where']['orderdate ='] = $input['orderdate'];
		}
		
		//根据$filter_data获得满足查询条件的数据总数
		$this->load->model("Model_db");
		$record_total =  $this->Model_db->getnum($data['tableName'], $filter_data);
		//根据$filter_data从db获得所需数据
		$db_content = $this->Model_db->getdata($data['tableName'],$filter_data,$data['dbFields']);
		
		//对页面需要显示的内容进行处理
		$i = 0;
		
		
		foreach ($db_content as $val){
			$data['table_content'][$i] = $val;
			$i++;
		}
		//页面下方的分页导航
		$this->load->helper ( array("webpagtools"));
		$arr = array('total' => $record_total, 'page' => $filter_data['page'], 'limit' => $filter_data['pagesize'], 'semiLinks' => 3); //设置分页参数
		$data['pagination'] = pagination($arr);          //获得页面下方的分页导航
		return 'common/content_Style1';
	}


}