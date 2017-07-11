<?php
if (! defined ( 'BASEPATH' )) {
	exit ( 'No direct script access allowed' );
}

/**
 * 功能：【风险等级测试】私募风险评测
 */
class Pf_assessment extends MY_Controller {
	function __construct() {
		parent::__construct ();
		$this->load->database ();
	}
	function userpfa() {
		if (empty ( $_SESSION ['customer_id'] ))
			exit ();
		if ($this->get_user_status ())
			$data ['needdoaccessment'] = 0;
		else
			$data ['needdoaccessment'] = 1;
		$this->load->view ( "/privateFund/private.html", $data ); // 已经评测过显示基金页面
	}
	private function get_user_status() {
		$user_data = $this->db->select ( Array (
				'pflevel',
				'readpfmsg' 
		) )->where ( Array (
				'id' => $_SESSION ['customer_id'] 
		) )->get ( 'p2_customer' )->row_array ();
		return $user_data;
	}
	function updateReadPfMsg() {
		if (empty ( $_SESSION ['customer_id'] ))
			exit ();
		$post = $this->input->post ();
		$post ['donereadmsg'] = 1;
		if ($post ['donereadmsg'] == 1) {
			$res = $this->db->set ( array (
					'readpfmsg' => 1 
			) )->where ( Array (
					'id' => $_SESSION ['customer_id'] 
			) )->update ( 'p2_customer' );
			if ($res) {
				$ret ['code'] = 0;
				$ret ['msg'] = '保存成功!';
			} else {
				$ret ['code'] = 1;
				$ret ['msg'] = '保存失败!';
			}
		} else {
			$ret ['code'] = 0;
			$ret ['msg'] = '未作任何操作!';
		}
		echo json_encode ( $ret );
	}
	function accessmentstatus() {
		if (empty ( $_SESSION ['customer_id'] ))
			exit ();
		$user_status = $this->get_user_status ();
		if (empty ( $user_status ['pflevel'] ))
			$ret ['data'] ['pflevel'] = 0;
		else {
			$ret ['data'] ['pflevel'] = 1;
			$ret ['data'] ['latestpflevel'] = $user_status ['pflevel'];
			$risk_level_text = "";
			switch ($user_status ['pflevel']) {
				case 1 :
					$risk_level_text = 'C1 级 - 保守型投资者';
					break;
				case 2 :
					$risk_level_text = 'C2 级 - 稳健型投资者';
					break;
				case 3 :
					$risk_level_text = 'C3 级 - 平衡型投资者';
					break;
				case 4 :
					$risk_level_text = 'C4 级 - 成长型投资者';
					break;
				case 5 :
					$risk_level_text = 'C5 级 - 进取型投资者';
					break;
			}
			$ret ['data'] ['levelcomment']=$risk_level_text;
		}
		if (empty ( $user_status ['readpfmsg'] ))
			$ret ['data'] ['readpfmsg'] = 0;
		else
			$ret ['data'] ['readpfmsg'] = 1;
		$ret ['code'] = 0;
		$ret ['msg'] = '成功';
		echo json_encode ( $ret );
	}
	// 提交测试结果
	function get_assessment() {
		if (empty ( $_SESSION ['customer_id'] ))
			exit ();
		$post = $this->input->post (); //
		// $post ['1_1'] = "A";
		// $post ['2_1'] = "A";
		// $post ['3_5'] = "A";
		
		// $risk_level = 11;
		// if (empty ( $post )) {
		// 	echo "参数不对";
		// }
//前端传送过来的格式		$post_temp='[{"0_1":"A"},{"1_1":"C"},{"1_2":"B"},{"1_3":"C"},{"1_4":"B"},{"2_1":"B"},{"2_2":"B"},{"2_3":"B"},{"3_1":"B"},{"3_2":"C"},{"3_3":"C"},{"3_4_B":"B"},{"3_4_C":"C"},{"4_1":"C"},{"4_2":"C"},{"4_3_C":"C"},{"5_1":"B"},{"5_2":"C"},{"5_3":"C"},{"6_1":"D"},{"6_2":"C"},{"6_3":"B"}]';
		$post_temp2=json_decode ($post,true);
		unset($post);
		foreach ($post_temp2 as $key =>$value)
		{
			foreach($value as $key2=>$value2)
			{
				$post[$key2]=$value2;
			}
			
		}
		$mark = $this->count_right_answer ( $post );
		$risk_level = 0;
		$risk_level_text = "";
		if ($mark <= 20) {
			$risk_level_text = 'C1 级 - 保守型投资者';
			$risk_level = 1;
		} elseif ($mark <= 30) {
			$risk_level_text = 'C2 级 - 稳健型投资者';
			$risk_level = 2;
		} elseif ($mark <= 50) {
			$risk_level_text = 'C3 级 - 平衡型投资者';
			$risk_level = 3;
		} elseif ($mark <= 80) {
			$risk_level_text = 'C4 级 - 成长型投资者';
			$risk_level = 4;
		} else {
			$risk_level_text = 'C5 级 - 进取型投资者';
			$risk_level = 5;
		}
		$retsave = $this->save_self_confirm_data ( $_SESSION ['customer_id'], serialize ( $post ), $risk_level_text . ',' . $mark );
		$res = $this->db->set ( array (
				'pflevel' => $risk_level 
		) )->where ( Array (
				'id' => $_SESSION ['customer_id'] 
		) )->update ( 'p2_customer' );
		
		$ret ['code'] = 0;
		$ret ['data'] ['score'] = $mark;
		$ret ['data'] ['level'] = $risk_level;
		$ret ['data'] ['levelcomment'] = $risk_level_text;
		$ret ['msg'] = '成功';
		echo json_encode ( $ret );
	}
	private function save_self_confirm_data($id, $answer, $level = "") {
		$time = time ();
		$ip = " ";
		$user_confirm_date ['user_id'] = $id;
		$user_confirm_date ['ip'] = $ip;
		$user_confirm_date ['user_submit'] = $answer;
		$user_confirm_date ['addtime'] = $time;
		$user_confirm_date ['version'] = '2017';
		$user_confirm_date ['result'] = $level;
		$res = $this->db->set ( $user_confirm_date )->insert ( "p2_assessment_record" );
		$id = $this->db->insert_id ();
		
		// save to database
		if ($res == true)
			return true;
		else
			return false;
	}
	private function count_right_answer($post) {
		$question = $this->read_questionwithscore ();
		$mark = 0;
		$mark_3_4 = 0;
		$mark_4_3 = 0;
		foreach ( $post as $key => $value ) {
			$type = substr ( $key, 0, strpos ( $key, '_' ) );
			$question_no = substr ( $key, strpos ( $key, '_' ) + 1, strlen ( $key ) - strpos ( $key, '_' ) - 1 );
			// $v['type']."_".$v['question_no'];
			$this_row_mark = $this->db->where ( Array (
					'type' => $type,
					'question_no' => $question_no 
			) )->get ( 'p2_pfa_question' )->row_array ();
			$this_mark = 0;
			if ($type == 3 and $question_no == 4) {
				switch ($value) {
					case 'A' :
						$temp_mark = $this_row_mark ['A'];
						break;
					case 'B' :
						$temp_mark = $this_row_mark ['B'];
						break;
					case 'C' :
						$temp_mark = $this_row_mark ['C'];
						break;
					case 'D' :
						$temp_mark = $this_row_mark ['D'];
						break;
					case 'E' :
						$temp_mark = $this_row_mark ['E'];
						break;
				}
				if ($temp_mark > $mark_3_4)
					$mark_3_4 = $temp_mark;
			} elseif ($type == 4 and $question_no == 3) {
				switch ($value) {
					case 'A' :
						$temp_mark = $this_row_mark ['A'];
						break;
					case 'B' :
						$temp_mark = $this_row_mark ['B'];
						break;
					case 'C' :
						$temp_mark = $this_row_mark ['C'];
						break;
					case 'D' :
						$temp_mark = $this_row_mark ['D'];
						break;
					case 'E' :
						$temp_mark = $this_row_mark ['E'];
						break;
				}
				if ($temp_mark > $mark_4_3)
					$mark_4_3 = $temp_mark;
			} else {
				switch ($value) {
					case 'A' :
						$this_mark = $this_row_mark ['A'];
						break;
					case 'B' :
						$this_mark = $this_row_mark ['B'];
						break;
					case 'C' :
						$this_mark = $this_row_mark ['C'];
						break;
					case 'D' :
						$this_mark = $this_row_mark ['D'];
						break;
					case 'E' :
						$this_mark = $this_row_mark ['E'];
						break;
				}
			}
			$mark = $mark + $this_mark;
		}
		$mark = $mark + $mark_3_4 + $mark_4_3;
		return $mark;
	}
	function read_question() {
		if (empty ( $_SESSION ['customer_id'] ))
			exit ();
		$assessment_question = $this->db->select ( Array (
				'type_name',
				'type',
				'question_no',
				'question',
				'A1',
				'A2',
				'A3',
				'A4',
				'A5',
				'A6' 
		) )->where ( Array (
				'type!=' => - 1 
		) )->get ( 'pfa_question' )->result_array ();
		// 转为数组供前端调用
		foreach ( $assessment_question as $key => $value ) {
			unset ( $answerlist );
			$answerlist = array (
					empty ( $value ['A1'] ) ? "" : $value ['A1'],
					empty ( $value ['A2'] ) ? "" : $value ['A2'],
					empty ( $value ['A3'] ) ? "" : $value ['A3'],
					empty ( $value ['A4'] ) ? "" : $value ['A4'],
					empty ( $value ['A5'] ) ? "" : $value ['A5'],
					empty ( $value ['A6'] ) ? "" : $value ['A6'] 
			);
			unset ( $assessment_question [$key] ['A1'] );
			unset ( $assessment_question [$key] ['A2'] );
			unset ( $assessment_question [$key] ['A3'] );
			unset ( $assessment_question [$key] ['A4'] );
			unset ( $assessment_question [$key] ['A5'] );
			unset ( $assessment_question [$key] ['A6'] );
			
			// if (! empty ( $value['A1'] )) {
			// $answerlist [] = $value['A1'];
			// unset ( $assessment_question[$key]['A1'] );
			// }
			// if (! empty ( $assessment_question ['A2'] )) {
			// $answerlist [] = $value['A2'];
			// unset ($assessment_question[$key]['A2'] );
			// }
			// if (! empty ( $assessment_question ['A3'] )) {
			// $answerlist [] =$value['A3'];
			// unset ( $assessment_question[$key]['A3'] );
			// }
			// if (! empty ( $assessment_question ['A4'] )) {
			// $answerlist [] =$value['A4'];
			// unset ( $assessment_question[$key]['A4'] );
			// }
			// if (! empty ( $assessment_question ['A5'] )) {
			// $answerlist [] = $value['A5'];
			// unset ( $assessment_question[$key]['A5'] );
			// }
			// if (! empty ( $assessment_question ['A6'] )) {
			// $answerlist [] =$value['A6'];
			// unset ( $assessment_question[$key]['A6'] );
			// }
			$assessment_question [$key] ['answerlist'] = $answerlist;
		}
		
		$ret ['code'] = 0;
		$ret ['data'] = $assessment_question;
		$ret ['msg'] = '成功';
		echo json_encode ( $ret );
	}
	private function read_questionwithscore() {
		$assessment_question = $this->db->where ( Array (
				'type!=' => - 1 
		) )->get ( 'pfa_question' )->result_array ();
		// if ($detail == false) {
			// 处理成建档的产品列表
		// }
		return $assessment_question;
	}
}