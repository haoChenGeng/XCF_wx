<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
header("Content-type: text/html; charset=utf-8");

class Member extends MY_Controller
{
	private $logfile_suffix;
    function member()
    {
        parent::__construct();
        $this->load->helper("url");
    }

    
    //会员

    
    function index(){
    	$this->load->view('user/my');
    }



    


}