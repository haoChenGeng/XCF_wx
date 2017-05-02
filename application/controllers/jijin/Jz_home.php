<?php
if (!defined('BASEPATH')){
	exit('No direct script access allowed');
}
    
class Jz_home extends MY_Controller
{
    private $logfile_suffix;
    function __construct()
    {
        parent::__construct();
    }
    
	//基金主页页面入口
	function index()
	{
		$data['base'] = $this->base;
		$this->load->view('jijin/home.html', $data);
	}
}
