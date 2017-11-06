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
		$this->maxOperitem = 500;
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
		$i = $j = 0;
		$flag = TRUE;
		$insertData = array();
		foreach ($newData as $key => $val)
		{
			$updateFlag = 0;
			if (isset($dbData[$key])){
				foreach ($dbFields as $v){
					if (isset($val[$v])){
						$renewData[$v] = $val[$v];
						if ($dbData[$key][$v] != $val[$v]){
							$updateFlag = 1;
						}
					}else{
						$renewData[$v] = $dbData[$key][$v];
					}
				}
				if ($updateFlag == 1){
					$updateData[] = $renewData;
					$j++;
				}
			}else{
				foreach ($dbFields as $v){
					if (isset($val[$v])){
						$insertData[$i][$v] = $val[$v];
					}else{
						$insertData[$i][$v] = $dbFieldDefault[$v];
					}
				}
				$i++;
			}
		}
		if (!empty($insertData)){
			$flag = $flag && $this->batch_insert($tableName,$insertData);
		}
		if (!empty($updateData)){
			$flag = $flag && $this->batchUpdate($tableName,$updateData,$majorKey);
		}
		return $flag;
	}
	
	function batch_insert($tableName, &$newData,$db=null){
		if (empty($db)){
			$db = $this->db;
		}
		$flag = true;
		if (!empty($newData)){
			$insertData = array_chunk($newData, $this->maxOperitem);
			foreach ($insertData as $val){
				$db->insert_batch($tableName,$val);
				$flag = $flag && ($db->error()['code'] == 0);
			}
		}
		return $flag;
	}
	
	//-----------将多个数据的多个字段批量设置为同一个值----------
	// $key字符串  where_in或where_not_in关联的字段
	// $type =1 采用where_in，其它采用where_not_in
	function batchSet($tableName,$updateData,&$whereIn,$key,$type = 1){
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
		if (empty($db)){
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
	
	//-----------批量更新数据函数----------
	// $majorKey为空，采用insert into的方式进行更新，会依据数据库中表$tableName设置的主键进行更新,同时会进行增量更新
	// $majorKey为数组采用建立临时表的方式进行更新
	// $majorKey为字符串时，采用CI自带update_batch进行更新
	public function batchUpdate($tableName,&$datas,$majorKey='',$db=null){
		if (empty($db)){
			$db = $this->db;
		}
		if (empty($datas)){
			return false;
		}else{
			$flag = true;
		}
		if (is_array($majorKey) || empty($majorKey)){
			$fields = $db->query("SHOW FULL COLUMNS FROM ".$tableName)->result_array();
			if (!empty($majorKey)){
				$creatFields = "(";
				$setStr = $whereStr = "";
				$tmpTab = "Tmp_".$tableName.time();
				foreach ($fields as $val){
					$creatFields .= $val['Field']." ".$val['Type'].',';
					if (!in_array($val['Field'], $majorKey)){
						$setStr .= " org.".$val['Field']."=tmp.".$val['Field'].",";
					}else{
						$whereStr .= " org.".$val['Field']."=tmp.".$val['Field']." AND ";
					}
				}
				if (!empty($setStr) && !empty($whereStr)){
					$setStr[strlen($setStr)-1] = " ";
					$whereStr = substr($whereStr,0,-4);
				}else{
					var_dump("majorKey 设置错误！");
					return false;
				}
				$creatFields[strlen($creatFields)-1] = ")";
			}else{
				$PRIKeys = array();
				foreach ($fields as $val){
					if ( 'PRI' == $val['Key']){
						$PRIKeys[] = $val['Field'];
					}
				}
				if (empty($PRIKeys)){
					var_dump("表格".$tableName."的主键设置为空");
					return false;
				}else{
					$fields = " (";
					$updateFields = '';
					foreach (array_keys(current($datas)) as $val){
						$fields .= '`'.$val.'`,';
						if (!in_array($val, $PRIKeys)){
							$updateFields .= "`".$val."`=values(`".$val."`),";
						}
					}
					$fields[strlen($fields)-1] = ")";
					if (!empty($updateFields)){
						$updateFields[strlen($updateFields)-1] = " ";
					}else{
						return 0;
					}
				}
			}
		}
		$updateDatas = array_chunk($datas, $this->maxOperitem);
		foreach ($updateDatas as &$updateData){
			if (empty($majorKey)){
				$sql = "insert into ".$tableName.$fields." values";
				foreach ($updateData as $value){
					$sql .=' (';
					foreach ($value as $val){
						$sql .= "'".$val."',";
					}
					$sql[strlen($sql)-1] = ")";
					$sql .=",";
				}
				$sql[strlen($sql)-1] = " ";
				$sql .= "on duplicate key update ".$updateFields;
				$db->query($sql);
				$flag = $flag && ($db->error()['code'] == 0);
			}else{
				if (is_array($majorKey)){
					$tmpData = "";
					foreach ($updateData as $val){
						$tmpData .= " (";
						foreach ($fields as $v){
							$tmpData .= "'".$val[$v['Field']]."',";
						}
						$tmpData[strlen($tmpData)-1] = ")";
						$tmpData .=',';
					}
					$tmpData[strlen($tmpData)-1] = ";";
					$db->trans_start();
					$sql = "create temporary table ".$tmpTab.$creatFields.";";
					$db->query($sql);
					$flag = $flag && ($db->error()['code'] == 0);
					$sql = "insert into ".$tmpTab." values".$tmpData;
					$db->query($sql);
					$flag = $flag && ($db->error()['code'] == 0);
					$sql = "update ".$tableName." org,".$tmpTab." tmp set".$setStr."where".$whereStr.";";
					$db->query($sql);
					$flag = $flag && ($db->error()['code'] == 0);
					$sql = " DROP TABLE ".$tmpTab.";";
					$db->query($sql);
					$db->trans_complete();
				}else{
					$db->update_batch($tableName, $updateData, $majorKey);
					$flag = $flag && ($db->error()['code'] == 0);
				}
			}
		}
		return $flag;
	}
/* 	
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
	} */

}
