<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Planner extends MY_Controller {

	function __construct() {
		parent::__construct ();
		$this->load->model (array("Model_pageDeal") );
	}
	
	public function index($accessCode) {
		$data['accessCommand'] = current($_SESSION['accessList'][$accessCode]);
		$this->Model_pageDeal->isLogin();                                                //判断是否登录
		if (!$this->Model_pageDeal->menuAuthorityCheck($data['accessCommand'])){         //权限控制
			exit;
		}
		//设置页面标题
		$data['heading_title'] = '理财经理信息';
		//设置本页面的链接地址
		$data['accessUrl'] = $this->unifyEntrance.$accessCode;
		//设置页面导航内容
		$data['breadcrumbs'][] = array(	'text' => '首页', 'href' => $this->config->item('home_page'));
		$data['breadcrumbs'][] = array(	'text' => $data['heading_title'], 'href' => $this->base.$data['accessUrl']);
		//设置页面所提供的操作
		$oper_arr = array( 'oper_save'=>'opersave', 'oper_import'=>'operimport');
// 		$arr = array( 'oper_import' => 'operimport', 'oper_edit'=>'operedit',
// 				'oper_importplanner' => 'operimportplanner', 'oper_export' => 'operexport','oper_export'=>'operexport',);
		//设置数据库表名
		$data['tableName'] = 'planner';
		//设置选择记录时，获取哪个字段的值
		$data['selcet_key'] = 'id';
		//选择输入值
		$input = $this->input->post();
		//依据$input['selectoper']设置需要跳转的函数
		if (isset($input['selectoper'])){
			if (!$this->Model_pageDeal->menuAuthorityCheck($data['accessCommand'],$input['selectoper'])){     //进行功能调用的权限检查
				exit;
			}
			$func = getfunction($input['selectoper'],$oper_arr);
		}else{
			$func = 'operdefault';
		}
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
		$data['heading_title'] = '理财经理信息';
		//设置搜索栏选项   例如('name'搜索项的数据库字段名,'filterType'搜索方式 =where =like 分别对应数据库查询的where和like, 'description'页面显示的搜索项名称)
		$statusItems = array(array('val'=>'all', 'name'=>'全部'),array('val'=>'1', 'name'=>'在职'),array('val'=>'0', 'name'=>'离职'));
		$data['query'] = array('EmployeeID' => array('filterType' => 'like', 'description' => '理财经理工号'), 
				'FName' => array('filterType' => 'like', 'description' => '理财经理姓名'),
				'status' => array('filterType' => 'select', 'description'=>'状态', 'default'=>'1,2', 'items' => $statusItems),
		);
		//设置页面右上角导航条按钮(函数中已进行权限检查)
		$data['buttons'] = $this->Model_pageDeal->getButtonList($data['accessCommand']);
		//设置form提交的url
		$data['form_action'] = $this->base.$data['accessUrl'];
		//获取页面的其他相关信息，并获得$filter_data 即数据库查询条件
		$filter_data = $this->Model_pageDeal->getPageData($input, $data);
		//设置页面需要查询的数据库字段名
		$data['dbFields'] = array('id','EmployeeID','FName','status');
		//设置页面显示的字段名称
		$data['table_field'] = array('id' => array('description' => 'ID'),
				'EmployeeID' => array('description' => '工号',),
				'FName' => array('description' => '理财经理姓名','sort'=>1),
				'status' => array('description' => '状态'),
		);
		//根据$filter_data获得满足查询条件的数据总数
		$this->load->model("Model_db");
		$record_total =  $this->Model_db->getnum($data['tableName'], $filter_data);
		//根据$filter_data从db获得所需数据
		$db_content = $this->Model_db->getdata($data['tableName'],$filter_data,$data['dbFields']);
		//判断是否有操作栏按钮，及是否有相应权限, 满足条件则进行相应设置
		$data['operButton'] = $this->Model_pageDeal->getButtonList($data['accessCommand'],3);        //'/businessData/Planner'
		if (!empty($data['operButton'])){
			$data['table_field']['operButton'] = array('description' => '操作');
		}
		//对页面需要显示的内容进行处理
		$i = 0;
		if (isset($input['filter_status']) && $input['filter_status'] == 2){
			$data['table_field']['belongCode']['type'] = 'input';
		}
		$data['table_field']['status']['type'] = 'select';
		unset($statusItems[0]);
		$statusItems[1] = array('val'=>'1', 'name'=>'在职');
		$data['table_field']['status']['items'] = $statusItems;
		foreach ($data['table_field'] as $key => $val){
			if (isset($val['type'])){
				$data['tableEdit'][] = array('type' => $val['type'], 'key'=>$key);
			}
		}
		foreach ($db_content as $val){
			$data['table_content'][$i] = $val;
			if (!empty($data['operButton'])){
				foreach ($data['operButton'] as $v){
					$data['table_content'][$i]['operButton'][$v['operation']]['description'] = $v['description'];
					$data['table_content'][$i]['operButton'][$v['operation']]['iconType'] = $v['iconType'];
				}
			}
			$i++;
		}
		//页面下方的分页导航
		$this->load->helper ( array("webpagtools"));
		$arr = array('total' => $record_total, 'page' => $filter_data['page'], 'limit' => $filter_data['pagesize'], 'semiLinks' => 3); //设置分页参数
		$data['pagination'] = pagination($arr);          //获得页面下方的分页导航
		return 'common/content_Style1';
	}
	
	private function opersave(&$input,&$data){
		$tableEditContent = json_decode($input['tableEditContent'],true);
		$belongCodes = array_column($tableEditContent, 'belongCode');
		if (!empty($belongCodes)){
			$cityCode =  $this->db->select('FeeCitycode')->get('city')->result_array();
			$cityCode = array_column($cityCode, 'FeeCitycode');
		}
		if (!empty($tableEditContent)){
			$updateData = array();
			$i = 0;
			foreach ($tableEditContent as $key => $val){
				$updateData[$i] = $val;
				$updateData[$i]['id'] = $key;
				$i++;
			}
			if (!empty($updateData)){
				$sucess = $this->db->update_batch($data['tableName'],$updateData,'id');
				if ($sucess){
					$data['success'] = '理财师信息更新成功';                //设置操作成功提示
				}else{
					$data['error_warning'] .= '理财师信息更新失败';         //设置操作失败提示
				}
			}else{
				$data['error_warning'] = '未修改任何记录';         	  //设置操作失败提示
			}
		}else{
			$data['error_warning'] = '未修改任何记录';
		}
		return $this->operdefault($input, $data);
	}
	
	private function operimport(&$input,&$data){
		if (!empty($_FILES['upload']['tmp_name'])){
			$this->load->model(array("Model_excelOper","Model_db"));
			$importDataDes = array(	'content' => array( 'sheet'=>0, 'limit'=>0, 'row'=>2,
					'fieldKey' => array(2=>'EmployeeID', 3=>'FName',))
			);
			$importData = $this->Model_excelOper->getExcelContent($_FILES['upload']['tmp_name'],$importDataDes)['content'];
			$this->db->set(array('status'=>0))->update($data['tableName']);			//首先将所有理财师设置为离职
			foreach ($importData as $key=>$val){
				$importData[$key]['status'] = 1;
			}
			$flag = $this->Model_db->incremenUpdate($data['tableName'],$importData,'EmployeeID');
			if ($flag){
				$data['success'] = '理财师信息导入成功';
			}else{
				$data['error_warning'] = '理财师信息导入过程中出现错误，请重试';
			}
			file_put_contents('log/userOperation'.$this->logfile_suffix, date('Y-m-d H:i:s',time())."\r\n 用户".$_SESSION['admin_id']."(".$_SESSION['fullname'].")执行了的导入人员架构信息的操作。\r\n\r\n",FILE_APPEND);
			$data['selectoper'] = '';
			return $this->operdefault($input, $data);
		}else{
			$data['heading_title'] = '导入人事架构表';
			$data['form_action'] = $data['return'] = $this->base.$data['accessUrl'];
			$data['selectoper'] = 'oper_import';
			return 'common/fileimport';
		}
	}
}