<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
header("Content-type: text/html; charset=utf-8");

class FindPaper extends MY_Controller {
    function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->helper (array("comfunction"));
    }
    
    function index() {
    	$data['papers'] = $this->db->order_by('id','DESC')->get('p2_paper')->result_array();
    	$this->load->view('Public/head.html');
    	$this->load->view('find/findPaper',$data);
    }
    
    function getPaper($paperId){
    	$paperInfo = $this->db->where(array('id'=>$paperId))->get('p2_paper')->row_array();
    	$paperInfo['readTimes'] ++;
    	$this->db->set(array('readTimes'=>$paperInfo['readTimes']))->where(array('id'=>$paperId))->update('p2_paper');
    	$this->load->view('Public/head.html');
    	$data['filePath'] = $this->base.'/data/find/'.$paperId.'/';
    	$this->load->view('find/'.$paperId.'/'.$paperInfo['url'],$data);
    	$data = array('id'=>$paperInfo['id'],'readTimes'=>$paperInfo['readTimes']);
    	$this->load->view('find/foot.php',$data);
    }
    
    function comment(){
    	$post = $this->input->post();
    	if (isset($_SESSION['customer_id'])){
    		$res = $this->db->set(array('customerId'=>$_SESSION['customer_id'],'paperId'=>$post['paperId'],'comment'=>$post['comment']))->insert('p2_papercomment');
    		if ($res){
    			$msg= '评论成功！';
    		}else{
    			$msg = '系统错误，请稍候重试';
    		}
    	}else{
    		$msg = '您尚未登录！';
    	}
    	echo $msg;
    }
    
    function collection($paperId){
    	$collectionPaper = $this->db->where(array('customerId'=>$_SESSION['customer_id']))->get('p2_papercollection')->row_array();
    	if (!empty($collectionPaper)){
    		if (!empty($collectionPaper['collection'])){
    			$existCollection = explode(',', $collectionPaper['collection']);
    			if (in_array($paperId,$existCollection)){
    				$msg = '文章已收藏';
    			}else{
    				$updateCollection = $collectionPaper['collection'].','.$paperId;
    			}
    		}else{
    			$updateCollection = $paperId;
    		}
    		if (isset($updateCollection)){
    			$flag = $this->db->set(array('collection'=>$updateCollection))->where(array('customerId'=>$_SESSION['customer_id']))->update('p2_papercollection');
    		}
    	}else{
    		$flag = $this->db->set(array('customerId'=>$_SESSION['customer_id'],'collection'=>$paperId))->insert('p2_papercollection');
    	}
    	if (isset($flag)){
    		if ($flag){
    			$msg = '收藏成功！';
    		}else{
    			$msg = '收藏失败！';
    		}
    	}
    	echo $msg;
    }
    
    function articleManagement(){
    	$comments = $this->db->where(array('customerId'=>$_SESSION['customer_id']))->get('p2_papercomment')->result_array();
    	$commentPaperId = array_column($comments,'paperId','paperId');
    	$collections = $this->db->where(array('customerId'=>$_SESSION['customer_id']))->get('p2_papercollection')->row_array()['collection'];
    	$collectionPapers = array();
    	if (!empty($collections)){
    		$collectionPapers = explode(',', $collections);
    	}
    	$paperIds = array_merge($commentPaperId,$collectionPapers);
    	$data = array('comment_data'=>array(),'collect_data'=>array());
    	if (!empty($paperIds)){
    		$paperInfo = $this->db->where_in('id',$paperIds)->get('p2_paper')->result_array();
    		$paperInfo = setkey($paperInfo,'id');
    		foreach ($paperInfo as $key=>$val){
    			if (in_array($key,$commentPaperId)){
    				$data['comment_data'][$key] = array('url'=>'FindPaper/getPaper/'.$key,'title'=>$val['title']);
    			}
    		}
    		foreach ($comments as $val){
    			if (isset($data['comment_data'][$val['paperId']])){
    				$data['comment_data'][$val['paperId']]['comment'][] = array('comment_time'=>$val['updateTime'],'content'=>$val['comment']);
    			}
    		}
    		foreach ($collectionPapers as $key =>$val){
    			if (isset($paperInfo[$val])){
    				$data['collect_data'][] = array('url'=>'FindPaper/getPaper/'.$val,'title'=>$paperInfo[$val]['title']);
    			}
    		}
    	}
    	$this->load->view('find/articleManagement.php',$data);
    }
    
}