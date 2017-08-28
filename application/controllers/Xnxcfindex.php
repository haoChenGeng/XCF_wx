<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
header("Content-type: text/html; charset=utf-8");

class Xnxcfindex extends MY_Controller {
    function __construct() {
		parent::__construct();
    }
    
    function index() {
		$fileName = str_replace('//', '/', APPPATH.'/controllers/User.php');
    	require_once $fileName;
    	$callClass = new User;
    	$callClass->home();
    }
    
	public function WK8YGc3Yi2oP3($accessCode=''){
		if (!empty($accessCode) && isset($_SESSION['accessList'][$accessCode])){
			$loadPHP = current($_SESSION['accessList'][$accessCode]);
			$fileName = str_replace('//', '/', APPPATH.'/controllers'.$loadPHP.'.php');
			require_once $fileName;
			$className = explode('/', $loadPHP);
			$className = end($className);
			$callClass = new $className;
			$callClass->index($accessCode);
		}else{
			$accessCode= empty($accessCode) || $accessCode == 'index.php' ? 'login' : $accessCode;
			$fileName = str_replace('//', '/', APPPATH.'controllers/admin/Account.php');
			require_once $fileName;
			$callClass = new Account;
			$callClass->$accessCode();
		}
	}
	
}