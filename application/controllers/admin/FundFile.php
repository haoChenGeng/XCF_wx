<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class FundFile extends MY_Controller {
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
		$data['heading_title'] = '基金文件管理';
		//设置本页面的链接地址
		$data['accessUrl'] = $this->unifyEntrance.$accessCode;
		//设置页面导航内容
		$data['breadcrumbs'][] = array(	'text' => '首页', 'href' => $this->config->item('home_page'));
		$data['breadcrumbs'][] = array(	'text' => $data['heading_title'], 'href' => $this->base.$data['accessUrl']);
		//设置页面所提供的操作
		$oper_arr = array('oper_add' => 'operadd', 'oper_delete' => 'operdelete','oper_download' => 'operdownload');
		//设置数据库表名
		$data['tableName'] = 'fundFile';
		//设置选择记录时，获取哪个字段的值
		$data['selcet_key'] = 'id';
		//设置页面操作内容描述
		$data['operContent'] ='基金文件管理';
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
				'filename' => array('filterType' => 'like', 'description' => '文件名称'),
		);
		//设置页面需要查询的数据库字段名
		$data['dbFields'] = array('id','fundcode','fundname','filename','url');
		//设置页面显示的字段名称
		$data['table_field']=array( 'id' => array('sort' => 1, 'description' => 'ID'),
									'fundcode' => array('sort' => 1, 'description' => '基金代码'),
									'fundname' => array('description' => '基金名称'),
									'filename' => array('description' => '文件名称'),
									'url' =>  array('sort' => 1, 'description' => '链接地址'),
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
	
	private function operadd(&$input, &$data){
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
				$fundname = $this->db->where(array('fundcode'=>$input['fundcode']))->get('p2_fundlist')->row_array()['fundname'];
				$filePath = FCPATH."\\data\\jijin\\fundFiles\\".$input['fundcode']."\\";
var_dump($input,$_FILES,$fundname);
				if (!empty($fundname)){
					if (!empty($_FILES['fundfile']['name']) && is_array($_FILES['fundfile']['name'])){
						$this->load->model("Model_db");
						foreach ($_FILES['fundfile']['name'] as $key => &$val){
							$explodeName[$key] = explode('.',$val);
							$filenames[] = $explodeName[$key][0];
						}
						$delFiles = $this->db->select('filename')->where(array('fundcode'=>$input['fundcode']))->where_in('filename',$filenames)->get($data['tableName'])->result_array();
						$system = strtoupper(substr(PHP_OS,0,3))==='WIN';
						if ($system){										//Windows系统
							$filePath = str_replace('/',"\\",$filePath);
							exec("md ".$filePath);
							if (!empty($delFiles)){
								foreach ($delFiles as $val){
									exec("del  ".$filePath.iconv("UTF-8", "GB2312//IGNORE", $val['filename']).".*");
								}
							}
						}else{												//UNIX系统
							$filePath = str_replace("\\",'/',$filePath);
							exec("mkdir -p ".$filePath);
							if (!empty($delFiles)){
								foreach ($delFiles as $val){
									exec("rm  ".$filePath.$val['filename'].".*");
								}
							}
						}
// exit;
						foreach ($_FILES['fundfile']['name'] as $key => $val){
							if ($system){
								$filename = iconv("UTF-8", "GB2312//IGNORE", $explodeName[$key][0]).'.'.end($explodeName[$key]);
var_dump($filename,$val);
								exec("copy ".$_FILES['fundfile']['tmp_name'][$key]." ".$filePath.$filename." /Y");
							}else{
								exec("cp ".$_FILES['logfile']['tmp_name'][$key]." ".$explodeName[$key][0].'.'.end($explodeName[$key]));
							}
							$newData[] = array('fundcode'=>$input['fundcode'],'fundname'=>$fundname,'filename'=>$explodeName[$key][0],'url'=>$explodeName[$key][0].'.'.end($explodeName[$key]));
						}
var_dump($newData);
						$this->Model_db->incremenUpdate($data['tableName'],$newData,array('fundcode','filename'));
					}else{
						$data['error_warning'] = '您未上传任何文件';          //设置操作失败提示
					}
exit;
/* 					$new_data = $this->getOperAddData($input,$data);
					if (!empty($new_data)){
						$flag = $this->db->set($new_data)->insert($data['tableName']);
					}
					file_put_contents('log/userOperation'.$this->logfile_suffix, date('Y-m-d H:i:s',time())."\r\n 用户".$_SESSION['admin_id']."(".$_SESSION['fullname'].")添加了".$data['operContent']."数据：".serialize($new_data)."\r\n\r\n",FILE_APPEND);
					if ($flag){
						$data['success'] = $data['operContent'].'添加成功';                //设置操作成功提示
					}else{
						$data['error_warning'] = $data['operContent'].'添加失败';          //设置操作失败提示
					} */
/* 					$new_data = $this->getOperAddData($input,$data);
 $data['success'] = $data['operContent'].'添加成功';                //设置操作成功提示
 }else{
 $data['error_warning'] = $data['operContent'].'添加失败';          //设置操作失败提示
 } */


				}else{
					$data['error_warning'] = $data['operContent'].'输入的基金'.$input['fundcode'].'不存在，添加失败';          //设置操作失败提示
				}
			}
			$data['selectoper'] = '';
			return $this->operdefault($input, $data);
		}else{
			$data['Model'] = substr(strrchr($data['accessCommand'],'/'),1);
			$this->getOperAddPage($input,$data);
			$data['selectoper'] = 'oper_add';
			return 'admin/fundFile';
		}
	}
	
	//获取oper_add的页面数据
	private function getOperAddPage(&$input,&$data){
		$data['heading_title'] = $data['text_form'] ='增加菜单';
		$fundcodes = $this->db->select('fundcode,fundname')->get('p2_fundlist')->result_array();
		foreach ($fundcodes as $val){
			$fundInfo[] = array('fundcode'=>$val['fundname']);
		}
		$data['fundInfo'] = json_encode($fundInfo);
		$data['forms'][] = array('type'=>'normal', 'description'=>'基金代码', 'required'=>1, 'content'=> 'type="text" name="fundcode" value="" placeholder="基金代码"');
		$data['forms'][] = array('type'=>'upload', 'description'=>'基金文件上传', 'required'=>1, 'name'=>"fundfile[]",'content'=>'multiple');
		$_SESSION[$data['Model'].'_randCode'] = $data['rand_code'] = "\t".mt_rand(100000,999999);
	}
	
	private function operdelete(&$input, &$data){
		if (isset($input['selected'])){
			$msg = '';
			$fileInfo = $this->db->select('fundcode,filename')->where_in($data['selcet_key'],$input['selected'])->get($data['tableName'])->result_array();
			$filePath = FCPATH."\\data\\jijin\\fundFiles\\";
//删除文件
			if (strtoupper(substr(PHP_OS,0,3))==='WIN'){										//Windows系统
				$filePath = str_replace('/',"\\",$filePath);
				foreach ($fileInfo as $val){
					exec("del  ".$filePath.$val['fundcode']."\\".iconv("UTF-8", "GB2312//IGNORE", $val['filename']).".*");
				}
			}else{												//UNIX系统
				$filePath = str_replace("\\",'/',$filePath);
				foreach ($fileInfo as $val){
					exec("del  ".$filePath.$val['fundcode']."/".$val['filename'].".*");
				}
			}
			$flag = $this->db->where_in($data['selcet_key'],$input['selected'])->delete($data['tableName']);
			file_put_contents('log/userOperation'.$this->logfile_suffix, date('Y-m-d H:i:s',time())."\r\n 用户".$_SESSION['admin_id']."(".$_SESSION['fullname'].")删除了".$data['operContent']."数据：".serialize($fileInfo)."\r\n\r\n",FILE_APPEND);
			if ($flag){
				$data['success'] = $data['operContent'].'删除成功';                //设置操作成功提示
			}else{
				$data['error_warning'] = $data['operContent'].'删除失败';          //设置操作失败提示
			}
			if (!empty($fileUrls)){
				foreach ($fileUrls as &$val){
				}
			}
		}else{
			$data['error_warning'] = '您没选中任何记录';
		}
		return $this->operdefault($input, $data);
	}
	
	
}