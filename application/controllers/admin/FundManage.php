<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class FundManage extends MY_Controller {
	private $logfile_suffix;
	function __construct() {
		parent::__construct ();
		$this->load->model(array("Model_pageDeal") );
		$this->logfile_suffix = date('Ym',time()).'.txt';
	}
	
	public function index($accessCode) {
		$data['accessCommand'] = current($_SESSION['accessList'][$accessCode]);
		$this->Model_pageDeal->isLogin();                                                //判断是否登录
		if (!$this->Model_pageDeal->menuAuthorityCheck($data['accessCommand'])){         //权限控制
			exit;
		}
		//设置页面标题
		$data['heading_title'] = '公募基金管理';
		//设置本页面的链接地址
		$data['accessUrl'] = $this->unifyEntrance.$accessCode;
		//设置页面导航内容
		$data['breadcrumbs'][] = array(	'text' => '首页', 'href' => $this->config->item('home_page'));
		$data['breadcrumbs'][] = array(	'text' => $data['heading_title'], 'href' => $this->base.$data['accessUrl']);
		//设置页面所提供的操作
		$oper_arr = array('oper_add' => 'operadd', 'oper_delete' => 'operdelete','oper_download' => 'operdownload');
		//设置数据库表名
		$data['tableName'] = 'fundlist';
		//设置选择记录时，获取哪个字段的值
		$data['selcet_key'] = 'id';
		//设置页面操作内容描述
		$data['operContent'] ='公募基金管理';
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
		//设置搜索栏选项   例如('name'搜索项的数据库字段名,'filterType'搜索方式 =where =like 分别对应数据库查询的where和like, 'description'页面显示的搜索项名称)
		$data['query'] = array(
				'fundcode' => array('filterType' => 'where', 'description' => '基金代码'),
				'fundname' => array('filterType' => 'like', 'description' => '基金名称'),
				'tano' => array('filterType' => 'like', 'description' => '基金公司'),
				'fundtype' => array('filterType' => 'where', 'description' => '基金类型'),
		);
		//设置页面需要查询的数据库字段名
		$data['dbFields'] = array('id','fundcode','tano','fundname','fundtype','nav','growthrate','fundincomeunit','shareclasses','risklevel','status','recommend');
		//设置页面显示的字段名称
		$data['table_field']=array( 'id' => array('sort' => 1, 'description' => 'ID'),
									'fundcode' => array('sort' => 1, 'description' => '基金代码'),
									'fundname' => array('sort' => 1, 'description' => '基金名称'),
									'tano' => array('sort' => 1, 'description' => '基金公司'),
									'fundtype' => array('sort' => 1, 'description' => '基金类型'),
									'nav' => array('sort' => 1, 'description' => '净值'),
									'growthrate' => array('sort' => 1, 'description' => '七日年化收益率'),
									'fundincomeunit' => array('sort' => 1, 'description' => '万份收益'),
									'shareclasses' => array('description' => '收费方式'),
									'risklevel' => array('sort' => 1, 'description' => '风险等级'),
									'status' => array('sort' => 1, 'description' => '基金状态'),
									'recommend' => array('sort' => 1, 'description' => '是否推荐'),);
		//设置页面右上角导航条按钮(函数中已进行权限检查)
		$data['buttons'] = $this->Model_pageDeal->getButtonList($data['accessCommand']);
		//设置form提交的url
		$data['form_action'] = $this->base.$data['accessUrl'];
		//获取页面的其他相关信息，并获得$filter_data 即数据库查询条件
		$filter_data = $this->Model_pageDeal->getPageData($input, $data);
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
		foreach ($db_content as $val){
			$data['table_content'][$i] = $val;
			$data['table_content'][$i]['recommend'] = $val['recommend'] == 1 ? '是' : '否';
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
	
	private function operadd(&$input, &$data){
		return $this->updateRecommend($input, $data, 1);
	}
	
	private function operdelete(&$input, &$data){
		return $this->updateRecommend($input, $data, 0);
	}
	
	private function operdownload(&$input, &$data){
		$fundcode = $this->db->select('fundcode')->where(array('id'=>$input['editItem']))->get('p2_fundlist')->row_array()['fundcode'];
		$this->load->library(array('Fund_interface'));
		$this->load->model("Model_db");
		if ($this->fund_interface->getFundNetvalue($fundcode)){
			$data['success'] = '历史净值数据更新成功';                //设置操作成功提示
		}else{
			$data['error_warning'] = '历史净值数据更新失败';          //设置操作失败提示
		}
		return $this->operdefault($input,$data);
	}
	
	private function updateRecommend(&$input, &$data, $setval){
		if (isset($input['selected'])){
			foreach ($input['selected'] as $key => $val){
				$updaterecord[] = $val;
			}
			if (isset($updaterecord)){
				$flag = $this->db->set(array('recommend' => $setval))->where_in($data['selcet_key'],$updaterecord)->update($data['tableName']);
				file_put_contents('log/userOperation'.$this->logfile_suffix, date('Y-m-d H:i:s',time())."\r\n 用户".$_SESSION['admin_id']."(".$_SESSION['fullname'].")修改了".$data['operContent']."数据：".serialize($updaterecord)."是否推荐为:".$setval."\r\n\r\n",FILE_APPEND);
				$str = $setval==1 ? '推荐' : '取消推荐';
				if ($flag){
					$data['success'] = $str.'成功';                //设置操作成功提示
				}else{
					$data['error_warning'] = $str.'失败';          //设置操作失败提示
				}
			}
		}else{
			$data['error_warning'] = '您没选中任何记录';
		}
		return $this->operdefault($input, $data);
	}
	
}