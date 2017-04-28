<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class model_db extends CI_Model {
	
	public function __construct()
	{
		parent::__construct();
		$this->load->database();
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
 		$data = $this->db->get($table)->result_array();
//  		var_dump($this->db->get_compiled_select($table));
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
			}
		}
		$num = $this->db->from($table)->count_all_results();
		// 		var_dump($this->db->get_compiled_select($table));
		return $num;
	}
	
	public function incremenUpdate($tableName, &$newData, $majorKey){
		$dbFields = $this->db->list_fields($tableName);
		$singleData = end($newData);
		foreach ($dbFields as $key=>$val){
			if (!isset($singleData[$val])){
				unset($dbFields[$key]);
			}
		}
		$dbData = $this->db->get($tableName)->result_array();
		$dbData = setkey($dbData,$majorKey);
		$i = 0;
		$flag = TRUE;
		foreach ($newData as $key => $val)
		{
			$updateData = array();
			if (isset($dbData[$val[$majorKey]])){
				foreach ($dbFields as $v){
					if ($dbData[$val[$majorKey]][$v] != $val[$v]){
						$updateData[$v] = $val[$v];
					}
				}
				if (!empty($updateData)){
					$flag = $flag && $this->db->set($updateData)->where(array($majorKey=>$val[$majorKey]))->update($tableName);
				}
			}else{
				foreach ($dbFields as $v){
					$insertData[$i][$v] = $val[$v];
				}
				$i++;
			}
		}
		if (!empty($insertData)){
			$flag = $flag && $this->db->insert_batch($tableName,$insertData);
		}
		return $flag;
	}

}
