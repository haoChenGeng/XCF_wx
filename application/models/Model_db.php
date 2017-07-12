<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Model_db extends CI_Model {
	
	private $maxOperitem;
	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->helper('comfunction_helper');
		$this->maxOperitem = 200;
	}
	
	public function getdata($table, $filter,$select='*' ){
		$this->db->select($select);
		if (!empty($filter['where'])){
			$this->db->where($filter['where']);
		}
		if (!empty($filter['like'])){
			$this->db->like($filter['like']);
		}
		if (!empty($filter['order'])){
			$this->db->order_by($filter['order'][0],$filter['order'][1]);
		}
		if (!empty($filter['limit'])){
			$this->db->limit($filter['limit'][1],$filter['limit'][0]);
		}
		if (!empty($filter['where_in'])){
			foreach ($filter['where_in'] as $key => $val){
				$this->db->where_in($key , $val);
			}
		}
		if (!empty($filter['strWhere'])){
			foreach ($filter['strWhere'] as $val){
				$this->db->where($val);
			}
		}
//  		var_dump($this->db->get_compiled_select($table));
 		$data = $this->db->get($table)->result_array();
		return $data;
	}
	
	public function getnum($table, $filter,$select='*' ){
		$this->db->select($select);
		foreach ($filter as $key => $val){
			switch ($key){
				case 'where':
					$this->db->where($val);
					break;
				case 'like':
					$this->db->like($val);
					break;
				case 'where_in':
					foreach ($filter['where_in'] as $key => $val){
						$this->db->where_in($key , $val);
					}
					break;
			}
		}
		$num = $this->db->from($table)->count_all_results();
		// 		var_dump($this->db->get_compiled_select($table));
		return $num;
	}
	
	public function incremenUpdate($tableName, &$newData, $majorKey){                  //增量更新函数，可插入新记录
		$dbData = $this->db->get($tableName)->result_array();
		if (!strstr($tableName,$this->db->dbprefix)){
			$tableName = $this->db->dbprefix($tableName);
		}
		$tableInfo = $this->db->query("SHOW FULL COLUMNS FROM ".$tableName)->result_array();
		if (!empty($tableInfo)){
			$dbFields = array_column($tableInfo,'Field');
			foreach ($tableInfo as $val){
				$dbFieldDefault[$val['Field']] = $val['Default'];
			}
		}else{
			return false;
		}
		if (is_array($majorKey)){
			$dbData = setMutliKey($dbData,$majorKey);
			$newData = setMutliKey($newData,$majorKey);
		}else{
			$dbData = setkey($dbData,$majorKey);
			$newData = setkey($newData,$majorKey);
		}
		$i = 0;
		$flag = TRUE;
		$insertData = array();
		foreach ($newData as $key => $val)
		{
			$updateData = array();
			if (isset($dbData[$key])){
				foreach ($dbFields as $v){
					if (isset($val[$v]) && $dbData[$key][$v] != $val[$v]){
						$updateData[$v] = $val[$v];
					}
				}
				if (!empty($updateData)){
					if(is_array($majorKey)){
						foreach ($majorKey as $k){
							$where[$k] = $val[$k];
						}
					}else{
						$where = array($majorKey=>$key);
					}
					$flag = $flag && $this->db->set($updateData)->where($where)->update($tableName);
				}
			}else{
				foreach ($dbFields as $v){
					if (isset($val[$v])){
						$insertData[$i][$v] = $val[$v];
					}else{
						$insertData[$i][$v] = $dbFieldDefault[$v];
					}
				}
				if ($i >= $this->maxOperitem) {
					$flag = $flag && $this->db->insert_batch($tableName,$insertData);
					$i = 0;
					$insertData = array();
				}else{
					$i++;
				}
			}
		}
		if (!empty($insertData)){
			$flag = $flag && $this->db->insert_batch($tableName,$insertData);
		}
		return $flag;
	}
	
	function renewDatas(&$newData,$tableName,$majorKey,&$msg=''){                    //更新函数,不插入新记录
		$dbData = $this->db->get($tableName)->result_array();
		if (empty($dbData)){
			$dbInfo = $res = $this->db->field_data($tableName);
			foreach ($dbInfo as $val){
				$dbFields[] = $val->name;
			}
		}else{
			$dbFields = array_keys(current($dbData));
		}
/* 		$singleData = end($newData);
		foreach ($dbFields as $key=>$val){
			if (!array_key_exists($val,$singleData)){
				unset($dbFields[$key]);
			}
		} */
		if (is_array($majorKey)){
			$dbData = setMutliKey($dbData,$majorKey);
			$newData = setMutliKey($newData,$majorKey);
		}else{
			$dbData = setkey($dbData,$majorKey);
			$newData = setkey($newData,$majorKey);
		}
		$flag = TRUE;
		foreach ($newData as $key => $val)
		{
			$updateData = array();
			if (isset($dbData[$key])){
				foreach ($dbFields as $v){
					if (isset($val[$v]) && $dbData[$key][$v] != $val[$v]){
						$updateData[$v] = $val[$v];
					}
				}
				if (!empty($updateData)){
					if(is_array($majorKey)){
						foreach ($majorKey as $k){
							$where[$k] = $val[$k];
						}
					}else{
						$where = array($majorKey=>$key);
					}
					$flag = $flag && $this->db->set($updateData)->where($where)->update($tableName);
				}
			}else{
				$msg .= $key.', ';
			}
		}
		return $flag;
	}
	
	function batch_insert($tableName, &$newData){
		$flag = true;
		if (!empty($newData)){
			$insertData = array_chunk($newData, $this->maxOperitem);
			foreach ($insertData as $val){
				$flag = $flag && $this->db->insert_batch($tableName,$val);
			}
		}
		return $flag;
	}
	
	function batch_update($tableName,$updateData,&$whereIn,$key,$type = 1){
		if (!empty($updateData) && !empty($whereIn)){
			$subWhereIN = array_chunk($whereIn, $this->maxOperitem);
			if ($type ==1){
				foreach ($subWhereIN as $val){
					$this->db->set($updateData)->where_in($key,$val)->update($tableName);;
				}
			}else{
				foreach ($subWhereIN as $val){
					$this->db->set($updateData)->where_not_in($key,$val)->update($tableName);;
				}
			}
		}
	}
	
	function batch_get($tableName,$select,&$whereIn,$key,$db=null,$where=null){
		if (null == $db){
			$db = $this->db;
		}
		$returnData = array();
		if (!empty($select) && !empty($whereIn)){
			$subWhereIN = array_chunk($whereIn, $this->maxOperitem);
			foreach ($subWhereIN as $val){
				if (!empty($where)){
					$db->where($where);
				}
				$data = $db->select($select)->where_in($key,$val)->get($tableName)->result_array();
				$returnData = array_merge($returnData,$data);
			}
		}
		return $returnData;
	}

}
