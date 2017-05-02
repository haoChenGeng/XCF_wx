<?php
if (!defined('BASEPATH')){
	exit('No direct script access allowed');
}
    
class InfoTransmit extends MY_Controller
{
    private $logfile_suffix;
    function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->logfile_suffix = '('.date('Y-m',time()).').txt';
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
