<?php
	function setkey($data,$key){
		$arr = array();
		if (!empty($data)){
			foreach ($data as $val){
				$arr[$val[$key]] = $val;
			}
		}
		return $arr;
	}
	
	function setMutliKey($data,$multiKey){
		$arr = array();
		if (!empty($data)){
			foreach ($data as $val){
				$key = '';
				foreach ($multiKey as $v){
					$key .= $val[$v];
				}
				$arr[$key] = $val;
			}
		}
		return $arr;
	}
	
	function getfunction($selectoper,$arr){
		foreach ($arr as $key => $val){
			if ($key == $selectoper){
				return $val;
			}
		}
		return 'operdefault';
	}
	
	function comRASDecrypt($encryptData, $checkCode){
		$CI = &get_instance();
		$private_key = openssl_get_privatekey(file_get_contents($CI->config->item('RSA_privatekey')));
		$decryptData ='';
		openssl_private_decrypt(base64_decode($encryptData),$decryptData, $private_key, OPENSSL_PKCS1_PADDING);
		//判断一次性随机验证码是否存在
		$div_bit = strpos($decryptData,$checkCode);
		if ($div_bit !== false){                      //找到一次性随机验证码
			$password =  substr($decryptData, 0, $div_bit);
			return $password;
		}
		return FALSE;
	}
	

	function comm_curl($url, $arr=array() , $header=array())
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,30);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		if (!empty($arr)){
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $arr);
		}
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}
	
	function is_required($input,$neededFields){
	     
    	
	}
	
	function my_md5($key,$data){
		return(md5(substr(md5($key),6,12).'#@AFG'.md5($data)));
	}
	
?>