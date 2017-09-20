<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class UserManage extends MY_Controller {
	function __construct() {
		parent::__construct ();
		$this->load->model(array("Model_pageDeal") );
	}
	
	public function index($accessCode) {
		$data['accessCommand'] = current($_SESSION['accessList'][$accessCode]);
		$this->Model_pageDeal->isLogin();                                                //判断是否登录
		if (!$this->Model_pageDeal->menuAuthorityCheck($data['accessCommand'])){         //权限控制
			exit;
		}
		//设置页面标题
		$data['heading_title'] = '用户管理';
		//设置本页面的链接地址
		$data['accessUrl'] = $this->unifyEntrance.$accessCode;
		//设置页面导航内容
		$data['breadcrumbs'][] = array(	'text' => '首页', 'href' => $this->config->item('home_page'));
		$data['breadcrumbs'][] = array(	'text' => $data['heading_title'], 'href' => $this->base.$data['accessUrl']);
		//设置页面所提供的操作
		$oper_arr = array('oper_add' => 'operadd', 'oper_edit'=> 'operedit', 'oper_delete' => 'operdelete');
		//设置数据库表名
		$data['tableName'] = 'user';
		//设置选择记录时，获取哪个字段的值
		$data['selcet_key'] = 'id';
		//设置页面操作内容描述
		$data['operContent'] ='用户';
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
		$data['heading_title'] = '用户管理';
		//设置搜索栏选项   例如('name'搜索项的数据库字段名,'filterType'搜索方式 =where =like 分别对应数据库查询的where和like, 'description'页面显示的搜索项名称)
		$data['query'] = array('name' => array('filterType' => 'like', 'description' => '群组名称'));
		//设置页面需要查询的数据库字段名
		$data['dbFields'] = array('id','username', 'fullname','user_group_id','dept','company');
		//设置页面显示的字段名称
		$data['table_field']=array( 'id' => array('sort' => 1, 'description' => 'ID'),
									'username' => array('sort' => 1, 'description' => '用户帐号'),
									'fullname' => array('sort' => 1, 'description' => '用户姓名'),
									'user_group_id' => array('sort' => 1, 'description' => '所在群组id'),
									'user_group_name' => array('description' => '所在群组名称'),
									'company' => array('description' => '所属公司'),
									'dept' => array('description' => '部门'),
		);
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
		$this->load->config('jz_dict');
		$companyInfo = $this->config->item('XN_company');
		foreach ($db_content as $val){
			$userGroup = $this->db->get('usergroup')->result_array();
			$userGroup = setkey($userGroup,'id');
			$data['table_content'][$i] = $val;
			if (isset($userGroup[$val['user_group_id']])){
				$data['table_content'][$i]['user_group_name'] = $userGroup[$val['user_group_id']]['name'];
			}else{
				$data['table_content'][$i]['user_group_name'] = '';
			}
			$data['table_content'][$i]['company'] = isset($companyInfo[$val['company']]) ? $companyInfo[$val['company']] : '';
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
	
	private function operadd(&$input,&$data){
		$data['cancel'] = $this->base.$data['accessUrl'];
		$data['form_action'] = $this->base.$data['accessUrl'];      //form提交地址
		$data['heading_title'] = $data['text_form'] ='增加'.$data['operContent'];
		if (isset($input['rand_code'])){
			$data['Model'] = substr(strrchr($data['accessCommand'],'/'),1);
			if ($input['rand_code'] != $_SESSION[$data['Model'].'_randCode']){
				$data['error_warning'] = '操作未授权';
			}
			if (!$this->inputDataDecrypt($input,$data)){
				$data['error_warning'] = '系统错误，操作失败';
			}
			unset($_SESSION[$data['Model'].'_randCode']);
			if ($input['password'] == ''){
				$data['error_warning'] = '密码不能为空';
			}
			$res = $this->db->where(array('username'=>$input['username']))->get($data['tableName'])->row_array();
			if (!empty($res)){
				$data['error_warning'] = '记录已存在，请检查输入数据';
			}
			if  (!isset($data['error_warning'])){
				$new_data = $this->getOperAddData($input,$data);
				if (!empty($new_data)){
					$flag = $this->db->set($new_data)->insert($data['tableName']);
				}
				file_put_contents('log/userOperation'.$this->logfile_suffix, date('Y-m-d H:i:s',time())."\r\n 用户".$_SESSION['admin_id']."(".$_SESSION['fullname'].")添加了".$data['operContent']."数据：".serialize($new_data)."\r\n\r\n",FILE_APPEND);
				if ($flag){
					$data['success'] = $data['operContent'].'添加成功';                //设置操作成功提示
				}else{
					$data['error_warning'] = $data['operContent'].'添加失败';          //设置操作失败提示
				}
			}
			return $this->operdefault($input, $data);
		}else{
			$data['Model'] = substr(strrchr($data['accessCommand'],'/'),1);
			$this->getOperAddPage($input,$data);
			$data['selectoper'] = 'oper_add';
			return 'common/content_Style2';
		}
	}
	
	private function operedit(&$input,&$data){
		$data['cancel'] = $this->base.$data['accessUrl'];
		$data['form_action'] = $this->base.$data['accessUrl'];      //form提交地址
		$data['heading_title'] = $data['text_form'] ='修改'.$data['operContent'].'信息';
		if (isset($input['rand_code'])){
			$data['Model'] = substr(strrchr($data['accessCommand'],'/'),1);
			if ($input['rand_code'] != $_SESSION[$data['Model'].'_randCode']){
				$data['error_warning'] = '操作未授权';
			}
			if (!$this->inputDataDecrypt($input,$data)){
				$data['error_warning'] = '系统错误，操作失败';
			}
			unset($_SESSION[$data['Model'].'_randCode']);
			$res = $this->db->where(array('username'=>$input['username']))->get($data['tableName'])->result_array();
			if (count($res) > 1 || (count($res) == 1 && $res[0][$data['selcet_key']] != $input[$data['selcet_key']])){
				$data['error_warning'] = '记录已存在，请检查输入数据';
			}
			if  (!isset($data['error_warning'])){
				$new_data = $this->getOperEditData($input,$data);
				if (!empty($new_data)){
					$flag = $this->db->set($new_data)->where($data['selcet_key'],$input[$data['selcet_key']])->update($data['tableName']);
				}
				file_put_contents('log/userOperation'.$this->logfile_suffix, date('Y-m-d H:i:s',time())."\r\n 用户".$_SESSION['admin_id']."(".$_SESSION['fullname'].")修改了".$data['operContent']."数据：".serialize($new_data)."\r\n\r\n",FILE_APPEND);
				if ($flag){
					$data['success'] = $data['operContent'].'信息修改成功';                //设置操作成功提示
				}else{
					$data['error_warning'] = $data['operContent'].'信息修改失败';          //设置操作失败提示
				}
			}
			return $this->operdefault($input, $data);
		}else{
			$data['Model'] = substr(strrchr($data['accessCommand'],'/'),1);
			$this->getOperEditPage($input,$data);
			$data['selectoper'] = 'oper_edit';
			return 'common/content_Style2';
		}
	}
	
	private function operdelete(&$input, &$data){
		if (isset($input['selected'])){
			foreach ($input['selected'] as $key => $val){
				$deleterecord[] = $val;
			}
			if (isset($deleterecord)){
				$flag = $this->db->where_in($data['selcet_key'],$deleterecord)->delete($data['tableName']);
				file_put_contents('log/userOperation'.$this->logfile_suffix, date('Y-m-d H:i:s',time())."\r\n 用户".$_SESSION['admin_id']."(".$_SESSION['fullname'].")删除了".$data['operContent']."数据：".serialize($deleterecord)."\r\n\r\n",FILE_APPEND);
				if ($flag){
					$data['success'] = $data['operContent'].'删除成功';                //设置操作成功提示
				}else{
					$data['error_warning'] = $data['operContent'].'删除失败';          //设置操作失败提示
				}
			}
		}else{
			$data['error_warning'] = '您没选中任何记录';
		}
		return $this->operdefault($input, $data);
	}
	
	//------------------------- 其他辅助函数 --------------------------------------
	//输入数据解密
	private function inputDataDecrypt(&$input,&$data){
		if (isset($input['password'])){
			$password = comRASDecrypt($input['password'],$_SESSION[$data['Model'].'_randCode']);
			if ($password === FALSE){
				return FALSE;
			}
			if (!empty($password)){
				$passkey = $this->config->item ( 'passkey' );
				$input['password'] = MD5 ( MD5 ( $passkey ) . substr ( MD5($password), 5, 20 ) );
			}else{
				$input['password'] = '';
			}
		}
		return TRUE;
	}
	
	//获取oper_add的页面数据
	private function getOperAddPage(&$input,&$data){
		$user = array('username'=>'','fullname'=>'', 'user_group_id'=>'', 'dept'=>'');
		$this->getOperEditPage($input, $data, $user);
	}
	
	//获取oper_add的新增数据
	private function getOperAddData(&$input,&$data){                              //通过函数获取增加记录时需要输入的字段
		return $this->getOperEditData($input,$data);
	}
	
	//获取oper_edit的页面数据
	private function getOperEditPage(&$input, &$data, $user = ''){
		if ( $user == '' ){
			$user = $this->db->where(array('id'=>$input['editItem']))->get('user')->row_array();
			$data['forms'][] = array('type'=>'normal', 'description'=>'ID', 'content'=> 'type="text" name="id" value="'.$user['id'].'" readonly=true');
		}
		$data['forms'][] = array('type'=>'normal', 'description'=>'用户账号', 'required'=>1, 'content'=> 'type="text" name="username" value="'.$user['username'].'" placeholder="用户帐户"');
		$data['forms'][] = array('type'=>'normal', 'description'=>'用户姓名', 'required'=>1, 'content'=> 'type="text" name="fullname" value="'.$user['fullname'].'" placeholder="用户描述"');
		$data['forms'][] = array('type'=>'normal', 'description'=>'密码', 'required'=>1, 'content'=> 'type="password" id="password" name="password" placeholder="******"');
		$groups = $this->db->select("id val,name")->get('usergroup')->result_array();
		$data['forms'][] = array('type'=>'select','description'=>'所在群组', 'required'=>1, 'name'=>"user_group_id", 'val'=>$user['user_group_id'],
				'items'=> $groups);
		$this->load->config('jz_dict');
		foreach ( $this->config->item('XN_company') as $key=>$val){
			$compayItems[] = array('val'=>$key,'name'=>$val);
		}
		$data['forms'][] = array('type'=>'select','description'=>'所在公司', 'required'=> 1, 'name'=>"company", 'val'=>$user['company'], 'items'=>$compayItems);
		$data['forms'][] = array('type'=>'normal','description'=>'所在部门', 'required'=> 1, 'content'=> 'type="text" name="dept" value="'.$user['dept'].'" placeholder="所在部门"');
		$_SESSION[$data['Model'].'_randCode'] = $data['rand_code'] = "\t".mt_rand(100000,999999);
		$data['public_key'] = file_get_contents($this->config->item('RSA_publickey'));   //获取RSA_加密公钥
		$data['public_key'] = str_replace(array("\r","\n"),'', $data['public_key']);
	}
	
	//获取oper_edit的修改数据
	private function getOperEditData(&$input,&$data){                              //通过函数获取增加记录时需要输入的字段
		$arr =array('username' => $input['username'],
				'fullname' => $input['fullname'],
				'user_group_id' => $input['user_group_id'],
				'dept' => $input['dept'],
				'company' => $input['company'],
		);
		if ($input['password'] != ''){
			$arr['password'] = $input['password'];
		}
		return $arr;
	}

}