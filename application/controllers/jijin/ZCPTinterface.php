<?php
if (!defined('BASEPATH')){
	exit('No direct script access allowed');
}
    
class ZCPTinterface extends MY_Controller
{
    private $logfile_suffix;
    function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->logfile_suffix = '('.date('Y-m',time()).').txt';
    }
    
    function index(){
    	if (!class_exists('Math_BigInteger'))
    		include 'data/encrpty/BigInteger.php';
    	if (!class_exists('Crypt_Hash'))
    		include 'data/encrpty/Hash.php';
    	if (!class_exists('Crypt_Rijndael'))
    		include 'data/encrpty/Rijndael.php';
		if (!class_exists('Crypt_AES'))
			include 'data/encrpty/AES.php';
		$input = $this->input->post();
		$AES = new Crypt_AES(CRYPT_AES_MODE_ECB);
		$AESKey = $this->db->select('AESkey')->where(array('platformName'=>'ZCPT'))->get('communctionkey')->row_array()['AESkey'];
		$AES->setKey($AESKey);
		file_put_contents(FCPATH.'log/user/interface'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n请求入参:".serialize($input),FILE_APPEND);
		$decrptyData = json_decode($AES->decrypt(base64_decode($input['data'])),true);
		file_put_contents(FCPATH.'log/user/interface'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n请求解密人参:".serialize($decrptyData),FILE_APPEND);
		$noNeedCustomer = array('fundlist','paymentChannel','rollImage','category','recommendFund','information','helpCenter',
				'fundinfo','fundDistribution','fundManager','fundNetvalue','fundPosition','fundNetvalueGrowth','feeQuery','affirmDate',
				'openBank','channel','PEfund','PEfundValue','provCity'
		);
		$ingoreCustomer = array('bgMsgSend','bgMsgCheck');
	    
		if (!in_array($decrptyData["code"],$noNeedCustomer)){
			if(isset($decrptyData["customerNo"])){
				if (in_array($decrptyData["code"],$ingoreCustomer)){
					$customerNo = $decrptyData['customerNo'];
				}else{
					$customerNo = $this->getCustomerNo($decrptyData['customerNo']);
				}
			}else{
				$customerNo = '';
			}
		}else{
			$customerNo = 1;
		}
		if ($customerNo!=''){
// 			if (isset($decrptyData["customerNo"])){
				$callClass = ucfirst($decrptyData["code"]);
				require_once APPPATH."controllers/ZCPTinterface/".$callClass.'.php';
				$callFunc = new $callClass();
// 				file_put_contents('log/debug.txt',$callClass."\r\n\r\n",FILE_APPEND);
				$returnData = $callFunc->index($decrptyData,$customerNo);
// 			}else{
// 				$returnData = array('code'=>'0001','msg'=>'用户账号不存在');
// 			}
		}else{
// 			$returnData = array('code'=>'0001','msg'=>'基金系统不存在该用户的账号');
			$returnData = array('code'=>'0001','msg'=>'基金系统不存在该用户的账号');
		}

// 		$this->$decrptyData['code'];
/* 		switch (){
			case 'fundlist':
				require_once APPPATH.'controllers\fundinterface\Fundlist.php';
				$Fundlist = new Fundlist();
				$returnData = $Fundlist->fundInfo($decrptyData);
				break;
			case 'paymentChannel'
		} */
		file_put_contents(FCPATH.'log/user/interface'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n响应输出:".serialize($returnData),FILE_APPEND);
		$returnData = base64_encode($AES->encrypt(json_encode($returnData)));
		file_put_contents(FCPATH.'log/user/interface'.$this->logfile_suffix,date('Y-m-d H:i:s',time()).":\r\n响应加密输出:".serialize($returnData),FILE_APPEND);
		echo $returnData;
		
    }
    
    private function fundlist($decrptyData){
    	
    	$Fundlist = new Fundlist();
    	$returnData = $Fundlist->fundInfo($decrptyData);
    }
    
    private function getCustomerNo($customerNo){
    	return $this->db->select('JZ_account')->where(array('XN_account'=>$customerNo,'platform'=>'ZCPT'))->get('jz_account')->row_array()['JZ_account'];
    }
    
    function renewCryptKey(){
    	if (!class_exists('Math_BigInteger'))
    		include 'data/encrpty/BigInteger.php';
    	if (!class_exists('Crypt_Hash'))
    		include 'data/encrpty/Hash.php';
    	if (!class_exists('Crypt_RSA'))
    		include 'data/encrpty/RSA.php';
    	$input = $this->input->post();
    	$public_key = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuSDQOvKPPh7WevLgeeOO/cuAmao2XH8/8k760bq3Zh8GoWXj+iG9W8asF7U4lpJkm77fsjSuGaW8kSW1q4bDTpvuioIQNLYeSQWf5D1AFjxKqOGBZbPOIq09TqWzJ7oPWC7EtiGytABSvBIomLDUbtnlDAxzTcDm1+o9Vq8TQDx9lJYLiMUqUV4Iat/a/QOL5APebnVpFIZ3+bDU7wvV4LFQ0ueI3FWCK6EcXqD/xB7KPWBoYyW+hYH2KkabPO1ItuOE16cnJyDxGzWGuEtMY/FFKz3LoS0/bFJ4QxAzdVxSVLDAbpxVY/bLESAIqp2MfSzpMKv9q2rRfQUAG0zOxQIDAQAB'; //获取RSA_加密公钥
    	$private_key = 'MIIEowIBAAKCAQEAuSDQOvKPPh7WevLgeeOO/cuAmao2XH8/8k760bq3Zh8GoWXj+iG9W8asF7U4lpJkm77fsjSuGaW8kSW1q4bDTpvuioIQNLYeSQWf5D1AFjxKqOGBZbPOIq09TqWzJ7oPWC7EtiGytABSvBIomLDUbtnlDAxzTcDm1+o9Vq8TQDx9lJYLiMUqUV4Iat/a/QOL5APebnVpFIZ3+bDU7wvV4LFQ0ueI3FWCK6EcXqD/xB7KPWBoYyW+hYH2KkabPO1ItuOE16cnJyDxGzWGuEtMY/FFKz3LoS0/bFJ4QxAzdVxSVLDAbpxVY/bLESAIqp2MfSzpMKv9q2rRfQUAG0zOxQIDAQABAoIBAAlFXWg2UXoY7UDG/PrdrIGFOXF4lrRXIwqtbd4m7ZxNnXVjtuEF44e/EUs2phjUR/mMu4MfJEDgjeru6oQmgY1kPbPuA4XAQRADGhjCAH2ck1iVwncnZAFUj6dqoOgyZyZRYUSFt39QLNSCTEopQNo0S0YpMXUJXgYeEuhOaDZlOz+4eJkNpHHxDxY3+EhqH98P5hcfMRXberIM10StqoEko1TA3kPMLS4xeQfcAmHjXlKgGU2ba4tcI35EI129SxuYBFjXmZdwt/NbBp89n41afA0DhDrhbbOl96N1WKGKcI37MyPCf/ztFkxBsW2FJg/i9USIZoQLki68rupugYECgYEA6k6Ubxop7X+VSuiszQwX58dkyp4y64KibnleunHF7yrQjhthJuCdApF5p5CKXTN4gkaZ2MUl/l9hlexXjLU0qxNbpWX2DvFYe5FP9WD5Y2IVMDgZtojwHyZzHOVYNjIg2QhmL6bXpKFw3+J7crs0UuuF0tArio8upQ6HTABCsPECgYEAykSfxc9PJcN4eYxB1ef/HnstQwCtLhUzZjQSWgUUC07TxoIoE+4dMyHTRoY/BVKUevosv4hqDe7tI/5QRmN6JhJcWk6YaY/top4Z1I6ug4eNCPoJ7tfWHnkRLWPhyn4eI07OdMfz0qg7GRkdgx7Kcm3ZOuvWezAKZBm209fY+xUCgYA+U4LGfwYyJ+L1lykILjRZsj+MakKPRSOiEWTyYXtOYGwzsPLJ3avGWB4tRZSYsC1ZMiCQefjeTk7uC31Kb5VAAJk7SQEH/okT7ZaAZjhQiHGsbu/gD1MYZijuwc8SM4lrUgGkoVPxdgRJebxuy39io0XoyvkaFXZJ77BrthIHcQKBgQCuAOkd56DkEMuEbQd5+DG9zCN834kb+rsT8jnTXUkIDVEcFX5a3t4ZzcCOjBCNCKSZQrzaLIVn5SH2c+IlG5DKTcNLIQ/2yA2bdr0r4W750dOfZFLFOMKHxojjbmigpWxR3Yq4Zgc4MudcSQHdedCZLizRhm8l3Icj5AcnKSoNnQKBgBqynphWmpXzIlEJijCpe7tuDOjyrzojDNV7J6NJyZMrJpi0ri2eYgXHucresbsng665asUdCpLstpZsLZkF1hZlDUO8Mb1aUXoNLrZhKB5s35EfVO8bpvuqiOiKL+ZedcUwpzplyRs6Eq7nrtfHKwnFzZ1cvsYpIHHltUPzFskh';
    	$RSA = new Crypt_RSA();
    	$RSA->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
    	$RSA->loadKey($private_key);
    	$AESkey = $RSA->decrypt(base64_decode($input["encryptkey"]));
    	$this->db->set(array('AESkey'=>$AESkey))->where(array('platformName'=>'ZCPT'))->update('communctionkey');
    	if ($this->db->affected_rows()>0){
    		echo 'SUCESS';
    	}else{
    		echo 'FAILURE';
    	}
    }
    
    function fundInfo(){
    	$post = $this->input->post();
    	$select = isset($post['select']) ? $post['select'] : '*';
    	$funddata = $this->db->select($select)->get('jz_fundlist')->result_array();
    	$this->load->config('jz_dict');
    	$TAs = $this->config->item('ta');
    	foreach ($funddata as $key => $val){
    		foreach ($val as $k => $v){
    			switch ($k){
    				case 'tano':
    					if (isset($TAs[$v])){
    						$funddata[$key][$k] = $v.'/'.$TAs[$v];
    					}
    					break;
    				case 'fundtype':
    					$funddata[$key][$k] = $this->config->item('fundtype')[$v];
    					break;
    				case 'shareclasses':
    					$funddata[$key][$k] = $this->config->item('sharetype')[$v];
    					break;
    				case 'risklevel':
    					$funddata[$key][$k] = $this->config->item('custrisk')[intval($v)];
    					break;
    				case 'status':
    					$funddata[$key][$k] = $this->config->item('fund_status')[intval($v)]['status'];
    					break;
    			}
    		}
    	}
    	echo json_encode($funddata);
    }
    
}
