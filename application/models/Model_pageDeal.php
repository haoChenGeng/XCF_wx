<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );
class model_pageDeal extends CI_Model {
	public function __construct() {
		parent::__construct ();
		$this->load->database ();
		$this->load->helper ( array (
				"comfunction",
				"url" 
		) );
	}
	public function header($data="") {
		$this->load->view ( 'common/header',$data );
	}
	public function isLogin() {
		if (isset ( $_SESSION ['admin_id'] )) {
			return true;
		} else {
			exit ();
		}
	}
	public function column_left() {
		$data ['menu'] = $this->getMenuList ( array (
				'type' => 1 
		) );
		foreach ( $_SESSION ['accessList'] as $key => $val ) {
			foreach ( $val as $k => $v ) {
				$data ['menuCode'] [$k] = $key;
			}
		}
		$this->load->view ( 'common/column_left', $data );
	}
	
	// 获取动态的菜单访问列表(保存到session中)，一个动态码对应一个菜单。
	public function getAccessList() {
		$menu = $this->getMenuList ( array (
				'type' => 1 
		) );
		$menu = $this->drawMenu ( $menu );
		foreach ( $menu as $key => $val ) {
			if ($val ['command'] != '') {
				do {
					$rand = mt_rand ( 100000, 999999 );
				} while ( isset ( $_SESSION ['accessList'] [$rand] ) );
				$_SESSION ['accessList'] [$rand] = array (
						$key => $val ['command'] 
				);
			}
		}
		return TRUE;
	}
	
	// 获取菜单的分级结构的json编码,菜单模块中设置上级菜单时使用。
	public function getMenuSelectList() {
		$selectMenu ['menu'] = $this->getMenuList ( array (
				'type' => 1 
		), FALSE );
		return json_encode ( $selectMenu );
	}
	
	// 获取菜单的级联列表结构
	private function getMenuList($where, $Auth = TRUE) {
		$menu = array ();
		$authority = '';
		if ($Auth) {
			if (isset ( $_SESSION ['admin_id'] ) && is_array ( $_SESSION ['authority'] )) {
				$authority = $_SESSION ['authority'];
			} else {
				return $menu;
			}
		}
		$menu_data = $this->db->where ( $where )->get ( 'menu' )->result_array ();
		$menu_data = setkey ( $menu_data, 'id' );
		$menu = $this->menuclassify ( $menu_data, $authority ) ['menu'];
		return $menu;
	}
	public function getMenuOrderList($type = 1) {
		$menu_data = $this->db->where ( array (
				'type>' => 0 
		) )->get ( 'menu' )->result_array ();
		$menu_data = setkey ( $menu_data, 'id' );
		$menu_classify = $this->menuclassify ( $menu_data ) ['menu'];
		if ($type == 0) {
			return $menu_classify;
		}
		$menu = $this->drawMenu ( $menu_classify );
		return $menu;
	}
	
	// 将级联菜单转换为菜单列表
	private function drawMenu(&$menu_classify) {
		foreach ( $menu_classify as $subMenu ) {
			$menu [$subMenu ['val']] = array (
					'command' => $subMenu ['command'] 
			);
			if (isset ( $subMenu ['menu'] )) {
				$followmenu = $this->drawmenu ( $subMenu ['menu'] );
				$menu = $menu + $followmenu;
			}
		}
		return $menu;
	}
	
	// 返回菜单的级联列表
	private function menuclassify(&$menu_data, $authority = '') { // $authority不为空时 返回有权限检查的菜单列表 反之返回菜无权限检查的单级联选择列表
		$menu = array ();
		foreach ( $menu_data as $key => $val ) {
			$menu ['quote'] [$key] = null;
		}
		// $NoAuth = is_array($authority)?FALSE:TRUE;
		foreach ( $menu_data as $key => $val ) {
			// $allow = $NoAuth || (isset($authority[$val['authIndex']])&&($authority[$val['authIndex']] & $val['authVal']));
			if (is_array ( $authority )) { // 表示依据权限值获得访问列表，满足权限或无权限控制的都满足要求
				$allow = (isset ( $authority [$val ['authIndex']] ) && ($authority [$val ['authIndex']] & $val ['authVal'])) || ($val ['authVal'] == 0);
			} else {
				$allow = $val ['authVal']; // 获得权限设置列表(无权限控制的不在列表内)，用于设置权限
			}
			if ($menu ['quote'] [$key] == null && $allow) {
				$i = 0;
				$chain = array ();
				$chain [$i] = $val ['id'];
				while ( $menu ['quote'] [$chain [$i]] == null && $menu_data [$chain [$i]] ['preMenu'] != 0 ) {
					$chain [$i + 1] = $menu_data [$chain [$i]] ['preMenu'];
					$i = $i + 1;
				}
				if ($menu ['quote'] [$chain [$i]] == null) {
					$quote = &$menu;
					if (is_array ( $authority )) {
						$quote ['menu'] [] = array (
								'val' => $chain [$i],
								'name' => $menu_data [$chain [$i]] ['name'],
								'command' => $menu_data [$chain [$i]] ['command'],
								'description' => $menu_data [$chain [$i]] ['description'] 
						);
					} else {
						$quote ['menu'] [] = array (
								'val' => $chain [$i],
								'name' => $menu_data [$chain [$i]] ['description'] 
						);
					}
					$quote = &$quote ['menu'] [count ( $quote ['menu'] ) - 1];
					$menu ['quote'] [$chain [$i]] = &$quote;
				} else {
					$quote = &$menu ['quote'] [$chain [$i]];
				}
				$i --;
				for(; $i >= 0; $i --) {
					if (is_array ( $authority )) {
						$quote ['menu'] [] = array (
								'val' => $chain [$i],
								'name' => $menu_data [$chain [$i]] ['name'],
								'command' => $menu_data [$chain [$i]] ['command'],
								'description' => $menu_data [$chain [$i]] ['description'] 
						);
					} else {
						$quote ['menu'] [] = array (
								'val' => $chain [$i],
								'name' => $menu_data [$chain [$i]] ['description'] 
						);
					}
					$quote = &$quote ['menu'] [count ( $quote ['menu'] ) - 1];
					$menu ['quote'] [$chain [$i]] = &$quote;
				}
			}
		}
		return $menu;
	}
	
	// 验证某个菜单或按钮访问的合法性，$oper=''表示菜单，$oper!=''表示按钮
	public function menuAuthorityCheck($command, $oper = '') {
		{
			$item = $this->db->where ( array (
					'command' => $command,
					'type' => 1 
			) )->get ( 'menu' )->row_array ();
			if ($oper != '') {
				$item = $this->db->where ( array (
						'command' => $oper,
						'preMenu' => $item ['id'] 
				) )->get ( 'menu' )->row_array ();
			}
			if (! isset ( $item ['authVal'] ) || $item ['authVal'] == 0) {
				return TRUE;
			}
			if (isset ( $_SESSION ['authority'] [$item ['authIndex']] ) && ($_SESSION ['authority'] [$item ['authIndex']] & $item ['authVal'])) {
				return TRUE;
			} else {
				return FALSE;
			}
		}
	}
	
	// 获取某个菜单下的导航条或操作栏按钮（包含权限控制） type =2 导航条按钮 =3操作栏按钮
	public function getButtonList($command, $type = 2) {
		$menuId = $this->db->where ( array (
				'command' => $command,
				'type' => 1 
		) )->get ( 'menu' )->row_array () ['id'];
		$buttons = $this->db->where ( array (
				'preMenu' => $menuId,
				'type' => $type 
		) )->get ( 'menu' )->result_array ();
		$allowButton = null;
		foreach ( $buttons as $button ) {
			if ($button ['authVal'] == 0 || (isset ( $_SESSION ['authority'] [$button ['authIndex']] ) && ($_SESSION ['authority'] [$button ['authIndex']] & $button ['authVal']))) {
				$allowButton [] = array (
						'operation' => $button ['command'],
						'description' => $button ['description'],
						'iconType' => $button ['iconType'] 
				);
			}
		}
		return $allowButton;
	}
	
	// 生成部分页面数据,并返回搜索条件
	public function getPageData(&$input, &$data) {
		// 设置数据库查询项的搜索栏的搜索选项，及获得数据查询条件
		if (isset ( $data ['query'] ) && ! empty ( $data ['query'] )) {
			$filter_data = array ();
			foreach ( $data ['query'] as $key => $val ) {
				$filter_val = isset ( $input ['filter_' . $key] ) ? $input ['filter_' . $key] : null;
				if (isset ( $val ['default'] ) && $filter_val == null) {
					$filter_val = $val ['default'];
				}
				// 设置$filter_data(数据库查询条件)
				switch ($val ['filterType']) {
					case 'select' : // 表示该查询项是以下拉菜单形式选择需要查询的内容
						if ($filter_val != null && $filter_val != 'all') {
							$filter_data ['where'] [$key] = $filter_val;
						}
						// 设置搜索栏的搜索选项
						$data ['filters'] ['select'] [] = array (
								'name' => 'filter_' . $key,
								'val' => $filter_val,
								'description' => $val ['description'],
								'items' => $val ['items'] 
						);
						break;
					case 'dates' :
						$data ['filters'] ['dates'] [$key] = array (
								'val' => $input [$key],
								'description' => $val ['description'] 
						);
						break;
					default :
						if ($filter_val != null) {
							$filter_data [$val ['filterType']] [$key] = $input ['filter_' . $key];
						}
						// 设置搜索栏的搜索选项
						$data ['filters'] ['fields'] [] = array (
								'name' => 'filter_' . $key,
								'val' => $filter_val,
								'description' => $val ['description'] 
						);
						break;
				}
			}
			unset ( $data ['query'] );
		}
		// 设置数据库查询项的搜索栏的搜索选项
		if (isset ( $data ['dates'] ) && ! empty ( $data ['dates'] )) {
			foreach ( $data ['dates'] as $key => $val ) {
				$data ['filters'] ['dates'] [$key] = array (
						'val' => $input [$key],
						'description' => $val 
				);
			}
			unset ( $data ['dates'] );
		}
		
		// 保留排序字段及方式,并设置数据库查询时的排序字段及方式
		$data ['sortField'] = isset ( $input ['sortField'] ) ? $input ['sortField'] : '';
		$data ['order'] = isset ( $input ['order'] ) ? $input ['order'] : 'ASC';
		
		if (! empty ( $data ['sortField'] )) {
			$filter_data ['order'] = array (
					$data ['sortField'],
					$data ['order'] 
			);
		}
		$filter_data ['page'] = isset ( $input ['page'] ) ? $input ['page'] : 1;
		$filter_data ['pagesize'] = isset ( $input ['pagesize'] ) ? $input ['pagesize'] : 20;
		$filter_data ['limit'] = array (
				($filter_data ['page'] - 1) * $filter_data ['pagesize'],
				$filter_data ['pagesize'] 
		);
		// 保留上一页面的记录选择，到下一页面，考虑到该选择未必会显示在下一页面中，进行操作不安全，故用$data['selected'] = array();
		// $data['selected'] = isset($post['selected']) ? (array)$post['selected'] : array();
		$data ['selected'] = array ();
		if(isset($_GET['subid']))
		{
			$filter_data['where']['subcategory_id']=$_GET['subid'];
			
		}
		return $filter_data;
	}
	public function getPageView($operdefaultpage, &$data, $operdeMidpage = "", $data_mid = "") {
		$this->header ($data);
		$this->column_left ();
		if ($operdeMidpage == "tree") {
			$trunk = $this->db->select ( array (
					'id',
					'name' 
			) )->where ( array (
					'type' => 0,
					'company' => 'zcpzpt' 
			) )->get ( 'category' )->result_array ();
			foreach ( $trunk as $key => $value ) {
				$tree_item = $this->db->select ( array (
						'id',
						'name' 
				) )->where ( array (
						'type' => 1,
						'company' => 'zcpzpt',
						'precategory_id' => $value ['id'] 
				) )->get ( 'category' )->result_array ();
				if(isset($tree_item))
					$trunk[$key]['tree_item'] = $tree_item;
			}
			$data ['trunk'] = $trunk;
		}
		$this->load->view ( $operdefaultpage, $data );
		$this->load->view ( 'common/footer' );
	}
}
