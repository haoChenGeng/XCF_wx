<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Model_sms extends CI_Model {
	
	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}
	
	public function send_sms($mobile,&$verifyCode) { //牛鼎丰的短信发送渠道 
		$sendSms = $this->db->where(array('dealitem'=>'sendSms'))->get('p2_dealitems')->row_array();
		$this->load->helper(array("logfuncs"));
		if (empty($sendSms)){
			$this->db->set(array('dealitem'=>'sendSms','updatetime'=>time(),'times'=>1))->insert('p2_dealitems');
		}else{
			if ((time()-$sendSms['updatetime'])> 3600){
				$this->db->set(array('updatetime'=>time(),'times'=>1))->where(array('dealitem'=>'sendSms'))->update('p2_dealitems');
			}else{
				$smsSetting = $this->db->where(array('name'=>'smsErrTimes'))->get('p2_interface')->row_array();
				if (empty($smsSetting)){
					$this->db->set(array('name'=>'smsErrTimes','description'=>'系统防短信攻击设置(通过设置1小时内，短信验证码未返回最大次数partnerId来阻止短信攻击)','partnerId'=>300,'url'=>'#'))->insert('p2_interface');
				}
				$allowtime = empty($smsSetting['partnerId']) ? 300 : $smsSetting['partnerId'];
				if ($sendSms['times'] > $allowtime){
// 					file_put_contents(FCPATH.'/log/sendSms'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n手机(".$post ['tel'].")申请短信验证被拒绝,短信接口可能遭受攻击，1小时内超过300条短信未返回正确验证码\r\n\r\n",FILE_APPEND);
					myLog('sendSms',"手机(".$post ['tel'].")申请短信验证被拒绝,短信接口可能遭受攻击，1小时内超过".$allowtime."条短信未返回正确验证码");
					return '短信验证码发送失败，请稍后重试';
				}else{
					$this->db->set(array('times'=>$sendSms['times']+1))->where(array('dealitem'=>'sendSms'))->update('p2_dealitems');
				}
			}
		}
		if (ISTESTING) {
			$verifyCode = '123456';
			return '您的验证码为:123456';
		}else{
			$verifyCode = $this->TelCode();
			$content = "您的验证码是:" . $verifyCode;
			$res2 = $this->NDFsendSms ( $mobile, $content );
			$res = json_decode ( $res2, TRUE );
// 			file_put_contents('log/sendSms'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n手机(".$post ['tel'].")申请短信验证码,返回数据:".serialize($res2)."\r\n\r\n",FILE_APPEND);
			myLog('sendSms',"手机(".$mobile.")申请短信验证码,返回数据:".serialize($res2));
			if (isset($res['returnCode']) && $res['returnCode'] == 0){
				$result = '验证码已发送！';
				$_SESSION['send_sms'] = time();
			}else{
				$result = '验证码发送失败';
			}
			return $result;
		}
	}
		// 发短信
	private function NDFsendSms($mobile, $content = '') {
		if (empty ( $mobile )) {
			return false;
		}
		$ISTESTING = false;
		$messageInterface = $this->db->where(array('name'=>'MessageInterface'))->get('p2_interface')->row_array();
		$sms_url = $messageInterface['url'];
		$sms_signature_key = $messageInterface['password'];
		$partnerId = $messageInterface['partnerId'];
		$moduleId = $messageInterface['moduleId'];
		$signTextTemp = "mobile=$mobile&moduleId=$moduleId&partnerId=$partnerId&value=$content" . $sms_signature_key;
		$signature = sha1 ( $signTextTemp );
		$post_data = "partnerId=$partnerId&mobile=$mobile&signature=$signature&value=$content&moduleId=SENDSMS";
		$this->load->helper('comfunction');
		$ret = comm_curl($sms_url,$post_data );
		return $ret;
	}
	
	private function TelCode() {
		$arr = Array(
				0,
				1,
				2,
				3,
				5,
				6,
				8,
				9
				);
		$randNum = "";
		for ($i = 0; $i < 6; $i++) {
			$randKey = mt_rand(0, 7);
			$randNum .= $arr[$randKey];
		}
		return $randNum;
	}
	
}
