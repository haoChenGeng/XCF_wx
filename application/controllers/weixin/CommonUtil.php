<?php
class CommonUtil
{

    function __construct() {
        $this->load->helper('log');
        $this->load->helper("json");
    }
    /**写log
     * @param string $file
     * @param string $string
     */
    static function wLog($file='', $string='') {
        if(empty($file)){
            $file=$_SERVER['DOCUMENT_ROOT']. DIRECTORY_SEPARATOR.'log.txt';
        }
        if (!empty($string)) {
            file_put_contents($file,date('Y-m-d H:i:s').':'.$string,FILE_APPEND);
        }
    }

    static function gbk_to_utf8($str) {
        return mb_convert_encoding($str, 'utf-8', 'gbk');
    }

    static function utf8_to_gbk($str) {
        return mb_convert_encoding($str, 'gbk', 'utf-8');
    }

    static function httpsRequest($url, $method='GET',$queryStr='',$needsetjason=false){
//        $queryStr=http_build_query($queryArr);
        $ci = curl_init();
        $header = array('Content-Type: application/json','Content-Length:'.strlen($queryStr));
	    
		//为微信添加这两行
		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, FALSE);
		//======================
		
		curl_setopt($ci, CURLOPT_CONNECTTIMEOUT,20);   //连接超时
        curl_setopt($ci, CURLOPT_TIMEOUT,30);   //请求超时
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ci, CURLOPT_HTTPHEADER, array('Expect:'));
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
        if($needsetjason)
			curl_setopt($ci, CURLOPT_HTTPHEADER, $header);
		else
			curl_setopt($ci, CURLOPT_HEADER, FALSE);

		$method = strtoupper($method);
        switch ($method) {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, TRUE);
                if (!empty($queryStr))
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $queryStr);
                break;
            case 'GET':
                if (!empty($queryStr))
                    $url .= '?' . $queryStr;
        }
        curl_setopt($ci, CURLOPT_URL, $url);
        $response = curl_exec($ci);
        if (curl_errno($ci)) {
            CommonUtil::wLog('request-error.txt',date('Y-m-d H:i:s').'请求异常,url:'.$url.' error'.curl_error($ci)."\r\n");
            $response='';
        }
        curl_close($ci);
        return $response;
    }
    static function array_to_xml($arr){
        $doc = new DOMDocument('1.0','UTF-8');
        $doc->formatOutput = true;
        $root = $doc->createElement('xml');

        $root = $doc->appendChild($root);
        self::createXml($arr, $doc,$root);

        return $doc->saveXML();
    }
    private static function createXml($arr, $doc,$ele) {
        foreach($arr as $k=>$val){
            if(is_array($val)){
                $node = $ele->appendChild($doc->createElement(is_numeric($k)?'item':$k));
                self::createXml($val,$doc,$node);
            }else{
                $node = $ele->appendChild($doc->createElement($k));
                $node->appendChild($doc->createTextNode($val));
            }
        }
    }

    //生成xml
    static function create_xml($new_arr) {
        if (empty($new_arr)) {
            return false;
        }
        //构造xml
        $xmldata_str = "<?xml version='1.0' encoding='GBK'?><data>";
        foreach ($new_arr as $key => $value) {
            if (is_array($value)) {
                continue;
            }
            $xmldata_str .= "<{$key}>{$value}</{$key}>";
        }
        if (isset($new_arr['list'])) {
            $xmldata_str .= "<list>";
            foreach ($new_arr['list'] as $k => $val) {
                $xmldata_str .= "<item>";
                foreach ($val as $k_ => $v_) {
                    $xmldata_str .= "<{$k_}>{$v_}</{$k_}>";
                }
                $xmldata_str .= "</item>";
            }
            $xmldata_str .= "</list>";
        }
        $xmldata_str .= "</data>";
        return $xmldata_str;
    }

    static function xml_to_array($xml) {
        $reg = "/<(\\w+)[^>]*?>([\\x00-\\xFF]*?)<\\/\\1>/";
        if (preg_match_all($reg, $xml, $matches)) {
            $count = count($matches[0]);
            $arr = array();
            for ($i = 0; $i < $count; $i++) {
                $key = $matches[1][$i];
                $val = CommonUtil::xml_to_array($matches[2][$i]);  // 递归
                if (array_key_exists($key, $arr)) {
                    if (is_array($arr[$key])) {
                        if (!array_key_exists(0, $arr[$key])) {
                            $arr[$key] = array($arr[$key]);
                        }
                    } else {
                        $arr[$key] = array($arr[$key]);
                    }
                    $arr[$key][] = $val;
                } else {
                    $arr[$key] = $val;
                }
            }
            return $arr;
        } else {
            return $xml;
        }
    }
    static function createLogoQRImage($qrPath,$logoPath){
        //生成二维码图片
        if ($logoPath !== FALSE) {
            $QR = imagecreatefromstring(file_get_contents($qrPath));
            $logo = imagecreatefromstring(file_get_contents($logoPath));
            $QR_width = imagesx($QR);//二维码图片宽度
            $QR_height = imagesy($QR);//二维码图片高度
            $logo_width = imagesx($logo);//logo图片宽度
            $logo_height = imagesy($logo);//logo图片高度
            $logo_qr_width = $QR_width / 4;
            $scale = $logo_width/$logo_qr_width;
            $logo_qr_height = $logo_height/$scale;
            $from_width = ($QR_width - $logo_qr_width) / 2;
            //重新组合图片并调整大小
            imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width,
                $logo_qr_height, $logo_width, $logo_height);
            //输出图片
            imagepng($QR, $qrPath);
        }
    }
}