<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MenuSetting extends MY_Controller {

	private $logfile_suffix;
	function __construct() {
		parent::__construct ();
		$this->load->model ( array("Model_pageDeal") );
		$this->logfile_suffix = '('.date('Y-m',time()).').txt';
	}
	
	public function index($accessCode='') {
		$this->Model_pageDeal->isLogin();                                                //判断是否登录
		$data['accessCommand'] = current($_SESSION['accessList'][$accessCode]);
		if (!$this->Model_pageDeal->menuAuthorityCheck($data['accessCommand'])){         //权限控制
			exit;
		}
		//设置页面标题
		$data['heading_title'] = '菜单管理';
		//设置本页面的链接地址
		$data['accessUrl'] = $this->unifyEntrance.$accessCode;
		//设置页面导航内容
		$data['breadcrumbs'][] = array(	'text' => '首页', 'href' => $this->config->item('home_page'));
		$data['breadcrumbs'][] = array(	'text' => $data['heading_title'], 'href' => $this->base.$data['accessUrl']);
		//设置页面所提供的操作
		$oper_arr = array('oper_add' => 'operadd', 'oper_edit'=> 'operedit', 'oper_delete' => 'operdelete');
		//设置数据库表名
		$data['tableName'] = 'menu';
		//设置选择记录时，获取哪个字段的值
		$data['selcet_key'] = 'id';
		//设置页面操作内容描述
		$data['operContent'] ='菜单';
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
		$data['heading_title'] = '菜单管理';
		//设置搜索栏选项   例如('name'搜索项的数据库字段名,'filterType'搜索方式 =where =like 分别对应数据库查询的where和like, 'description'页面显示的搜索项名称)
		$data['query'] = array('name' => array('filterType' => 'like', 'description' => '菜单名称'));
		//设置页面需要查询的数据库字段名
		$data['dbFields'] = array('id','name', 'description','command','type','authIndex');
		//设置页面显示的字段名称
		$data['table_field']=array( 'id' => array('sort' => 1, 'description' => 'ID'),
									'name' => array('sort' => 1, 'description' => '菜单名称'),
									'description' => array('description' => '菜单功能描述'),
									'command' => array('description' => 'command'),
									'type' => array('description' => '类型'),
									'authority' => array('description' => '权限控制'),);
		//设置页面右上角导航条按钮(函数中已进行权限检查)
		$data['buttons'] = $this->Model_pageDeal->getButtonList($data['accessCommand']);
		//设置form提交的url
		$data['form_action'] = $this->base.$data['accessUrl'];
		//获取页面的其他相关信息，并获得$filter_data 即数据库查询条件
		$filter_data = $this->Model_pageDeal->getPageData($input, $data);
		if (isset($data['filters']['dates'])){
			dateTrans($data['filters']['dates'],$filter_data);
		}
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
	 	$button_description = array('0' => '隐藏菜单', '1'=>'菜单', '2' => '导航条按钮', '3' =>'操作栏按钮', '4'=>'其他');
	 	foreach ($db_content as $val){
	 		$data['table_content'][$i] = $val;
	 		$data['table_content'][$i]['type'] = $button_description[$val['type']];
	 		$data['table_content'][$i]['authority'] = $val['authIndex'] == 0 ? '否' : '是';
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
	
	private function operdelete(&$input, &$data){
		if (isset($input['selected'])){
			$msg = '';
			foreach ($input['selected'] as $key => $val){
				$checkResult = $this->deleteCheck($val);
				if ($checkResult['code'] == FALSE){
					$msg .= $checkResult['msg'];
				}
				if ($checkResult['code'] == TRUE){
					$this->beforeDelOper($val);                  //执行删除前操作
					$deleterecord[] = $val;
				}
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
			if ($msg !=''){
				if (isset($data['error_warning'])){
					$data['error_warning'] .= $msg;
				}else{
					$data['error_warning'] = $msg;
				}
			}
		}else{
			$data['error_warning'] = '您没选中任何记录';
		}
		return $this->operdefault($input, $data);
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
			unset($_SESSION[$data['Model'].'_randCode']);
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
			$data['selectoper'] = '';
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
			unset($_SESSION[$data['Model'].'_randCode']);
			if  (!isset($data['error_warning'])){
				$res = $this->editCheck($input);
				if ($res['code']==FALSE){
					$data['error_warning'] = $res['msg'];
				}
				if (!isset($data['error_warning'])){
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
			}
			$data['selectoper'] = '';
			return $this->operdefault($input, $data);
		}else{
			$data['Model'] = substr(strrchr($data['accessCommand'],'/'),1);
			$this->getOperEditPage($input,$data);
			$data['selectoper'] = 'oper_edit';
			return 'common/content_Style2';
		}
	}

//------------------------- oper_add 辅助函数   --------------------------------------
	//获取oper_add的页面数据
	private function getOperAddPage(&$input,&$data){
		$data['heading_title'] = $data['text_form'] ='增加菜单';
		$data['forms'][] = array('type'=>'normal', 'description'=>'菜单描述', 'required'=>1, 'content'=> 'type="text" name="description" value="" placeholder="菜单描述"');
		$data['forms'][] = array('type'=>'normal', 'description'=>'command', 'required'=>1, 'content'=> 'type="text" name="command" value=""');
		$data['forms'][] = array('type'=>'select', 'description'=>'菜单类型', 'required'=>1, 'name'=>'type', 'val'=>'',
				'items'=> array(array('val'=>0, 'name'=>'隐藏菜单'),array('val'=>1, 'name'=>'菜单'),array('val'=>2, 'name'=>'导航条按钮'),array('val'=>3, 'name'=>'操作栏按钮'),array('val'=>4, 'name'=>'其他')));
		$this->load->config('icon');
		$data['forms'][] = array('type'=>'select', 'description'=>'图标', 'required'=>1, 'name'=>'iconType', 'val'=>'fa-pencil',
				'items'=> $this->config->item('awesome'));
		$data['forms'][] = array('type'=>'select', 'description'=>'权限控制', 'required'=>1, 'name'=>'authority', 'val'=>0,
				'items'=> array(array('val'=>1, 'name'=>'是'),array('val'=>0, 'name'=>'否')));
		$data['cascade_num'] = $this->Model_pageDeal->getMenuSelectList($data,0);
		$data['forms'][] = array('type'=>'cascade','description'=>'上级菜单', 'num'=>$data['cascade_num'], 'required'=>1);
		$_SESSION[$data['Model'].'_randCode'] = $data['rand_code'] = "\t".mt_rand(100000,999999);
	}
	
	private function getOperAddData(&$input,&$data){                              //通过函数获取增加记录时需要输入的字段
		$arr =array('description' => $input['description'],
					'command' => $input['command'],
					'type' => $input['type'],
					'iconType' => 'fa-'.$input['iconType'],
		);
		for ( $i=6; $i>0; $i--){
			$key = 'cascade'.$i;
			if (!empty($input[$key])){
				$arr['preMenu'] = $input[$key];
				break;
			}
		}
		$menu_data = $this->db->get('menu')->result_array();
		$menu_data = setkey($menu_data, 'id');
		$arr['name'] = $this->getMenuName($menu_data,$arr);       //设置菜单名称
		if ($input['authority'] == 1){
			$this->getAuthorityCode($menu_data,$arr);
		}else{
			$arr['authIndex'] = $arr['authVal'] = 0;
		}
		return $arr;
	}
	
	//获得设置菜单名称
	private function getMenuName(&$menu_data,$arr){
		if (!isset($arr['preMenu']) || $arr['preMenu'] == 0){
			$arr['preMenu'] = 0;
			$name = 'menu';
		}else{
			$name = $menu_data[$arr['preMenu']]['name'].'_';
		}
		foreach ($menu_data as $key => $val){
			$menuName[$val['name']] = 1;
		}
		for ($i = 1; array_key_exists($name.$i, $menuName) ; $i++);
		$name = $name.$i;
		return $name;
	}
	
	//获得菜单的权限控制码
	private function getAuthorityCode(&$menu_data,&$arr){
		foreach ($menu_data as $key => $val){
			$existauthority[$val['authIndex'].':'.$val['authVal']] = 1;
		}
		//为该菜单设置权限匹配值
		$flag = 1;
		for ($i = 1; $flag == 1; $i++){
			for ($j=$k=1; $j<32 ; $j++){
				if (!array_key_exists($i.':'.$k, $existauthority)){
					$flag = 0;
					$arr['authIndex'] = $i;
					$arr['authVal'] = $k;
					break;
				}
				$k = $k<<1;
			}
		}
	}
	
//------------------------- oper_delete辅助函数   --------------------------------------
	//删除菜单前检查是否有子菜单或按钮
	private function deleteCheck($menuId){
		$num = $this->db->where(array('preMenu'=>$menuId))->from('menu')->count_all_results();
		if ($num > 0){
			$menu = $this->db->where(array('id'=>$menuId))->get('menu')->row_array()['name'];
			return array('code'=>FALSE, 'msg'=>$menu.'菜单下存在子菜单或按钮，请先删除子菜单及按钮; ');
		}else{
			return array('code'=>TRUE);
		}
	}
	//删除菜单前删除相关群组的对应权限
	private function beforeDelOper($menuid){
		$menuAuthority = $this->db->select('authIndex, authVal')->where(array('id'=>$menuid))->get('menu')->row_array();
		$authIndex = $menuAuthority['authIndex'];
		$authVal = $menuAuthority['authVal'];
		if ($authVal != 0){
			$groups = $this->db->get('usergroup')->result_array();
			foreach ($groups as $group){
				$authority = json_decode($group['authority'],true);
				if (isset($authority[$authIndex])){
					$authority[$authIndex] = intval($authority[$authIndex]) & (~ intval($authVal));
					$authority = json_encode($authority);
					$this->db->set( array('authority' =>$authority) )->where(array('id'=>$group['id']))->update('usergroup');
				}
			}
		}
	}
	
//------------------------- oper_edit 辅助函数   --------------------------------------
	//获取oper_edit的页面数据
	private function getOperEditPage(&$input,&$data){
		$data['heading_title'] = $data['text_form'] ='修改菜单';
		$menu = $this->db->where(array('id'=>$input['editItem']))->get('menu')->row_array();
		$data['forms'][] = array('type'=>'normal', 'description'=>'ID', 'content'=> 'type="text" name="id" value="'.$menu['id'].'" readonly=true');
		$data['forms'][] = array('type'=>'normal', 'description'=>'菜单名称', 'required'=>1, 'content'=> 'type="text" name="name" value="'.$menu['name'].'" readonly=true');
		$data['forms'][] = array('type'=>'normal', 'description'=>'菜单描述', 'required'=>1, 'content'=> 'type="text" name="description" value="'.$menu['description'].'" placeholder="菜单描述"');
		$data['forms'][] = array('type'=>'normal', 'description'=>'command', 'required'=>1, 'content'=> 'type="text" name="command" value="'.$menu['command'].'" placeholder="command"');
		$data['forms'][] = array('type'=>'select', 'description'=>'菜单类型', 'required'=>1, 'name'=>'type', 'val'=>$menu['type'],
				'items'=> array(array('val'=>0, 'name'=>'隐藏菜单'),array('val'=>1, 'name'=>'菜单'),array('val'=>2, 'name'=>'导航条按钮'),array('val'=>3, 'name'=>'操作栏按钮'),array('val'=>4, 'name'=>'其他')));
		$this->load->config('icon');
		$data['forms'][] = array('type'=>'select', 'description'=>'图标', 'required'=>1, 'name'=>'iconType', 'val'=>str_replace('fa-', '', $menu['iconType']),
				'items'=> $this->config->item('awesome'));
		$authority = $menu['authVal'] == 0 ? 0 : 1;
		$data['forms'][] = array('type'=>'select', 'description'=>'权限控制', 'required'=>1, 'name'=>'authority', 'val'=>$authority,
				'items'=> array(array('val'=>1, 'name'=>'是'),array('val'=>0, 'name'=>'否')));
		$data['cascade_num'] = $this->Model_pageDeal->getMenuSelectList($data,0);
		$data['forms'][] = array('type'=>'cascade','description'=>'上级菜单', 'num'=>$data['cascade_num'], 'required'=>1);
		$_SESSION[$data['Model'].'_randCode'] = $data['rand_code'] = "\t".mt_rand(100000,999999);
	}
	
	//检查修改后数据的合法性检查
	private function editCheck(&$input){
		for ( $i=6; $i>0; $i--){
			$key = 'cascade'.$i;
			if (!empty($input[$key])){
				$arr['preMenu'] = $input[$key];
				break;
			}
		}
		$menu_data = $this->db->get('menu')->result_array();
		$menu_data = setkey($menu_data, 'id');
		if (isset($arr['preMenu']) && $arr['preMenu']!=$menu_data[$input['id']]['preMenu']){          //如果preMenu发生改变检查是否存在循环的情况，并修改
			$menu_data[$input['id']]['preMenu'] = $arr['preMenu'];
			$menuid = $arr['preMenu'];
			$chain = array();
			while ($menuid != 0){
				if (array_key_exists($menuid, $chain)){
					return array('code'=>FALSE, 'msg'=>'上级菜单设置错误，引起菜单级联出现循环');
				}
				$chain[$menuid] = 1;
				$menuid = $menu_data[$menuid]['preMenu'];
			}
		}
		return array('code'=>TRUE);
	}
	
	//获取修改记录时需要输入的字段
	private function getOperEditData(&$input,&$data){                              
		$arr =array('description' => $input['description'],
					'command' => $input['command'],
					'type' => $input['type'],
					'iconType' => 'fa-'.$input['iconType'],
		);
		for ( $i=6; $i>0; $i--){
			$key = 'cascade'.$i;
			if (isset($input[$key]) && $input[$key] !== ''){
				$arr['preMenu'] = $input[$key];
				break;
			}
		}
		$menu_data = $this->db->get('menu')->result_array();
		$menu_data = setkey($menu_data, 'id');
		if (isset($arr['preMenu']) && $arr['preMenu']!=$menu_data[$input['id']]['preMenu']){          //如果preMenu发生改变，修改菜单名称
			$arr['name'] = $this->getMenuName($menu_data,$arr);
		}
		$thisMenu = $this->db->where(array('id'=>$input['id']))->get('menu')->row_array();
		if ($thisMenu['authVal'] == 0 && $input['authority'] == 1){               //权限设置发生改变
			$this->getAuthorityCode($menu_data,$arr);
		}
		if ($thisMenu['authVal'] != 0 && $input['authority'] == 0){
			$this->beforeDelOper($input['id']);
			$arr['authIndex'] = $arr['authVal'] = 0;
		}
		return $arr;
	}
	
}