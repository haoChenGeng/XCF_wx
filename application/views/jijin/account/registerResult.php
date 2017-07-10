<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>系统消息提醒-<?php echo 111#$GLOBALS['site_title'];?></title>
<meta name="Keywords" content="<?php echo 222#$GLOBALS['site_keyword'];?>" />
<meta name="Description" content="<?php echo 333#$GLOBALS['site_desc'];?>" />
<style type="text/css">
	*{font-family: 微软雅黑;font-size: 30px;}
	#error_t{height: 549px;margin-top: 50px;}
	body{
		text-align: center;
		width: 446px;
		margin: 0 auto;
	}
    #nextBtn {
        width: 100%;
        height: 75px;
        border-radius: 5px;
        background-color: #f5bc44;
        border: 0;
        color: #fff;
    }
    #backBtn {
        width: 100%;
        height: 75px;
        border-radius: 5px;
        background-color: #f5bc44;
        border: 0;
        color: #fff;
        margin-top: 25px;
    }
</style>
</head>
<body>
<div class="mians mian-affs">
<!--	<div id="error_t"><?php if($msgTy == 'fail'){?><img src="<?php echo $base;?>/data/images/error.png"><?php }else{?><img src="<?php echo $base;?>/data/images/sess.png"><?php }?></div> -->
<!-- <div id="error_t"><img src="<?php if($msgTy == 'fail') echo ($base."/data/img/error.png"); else echo ($base."/data/img/sess.png");?>"></div> -->
    <div id="error_t"><img src="<?php if($msgTy == 'fail') echo ($base."/data/img/error.png"); else echo ($base."/data/img/sess.png");?>" /></div>	
	<div class="affirms">
    	<p class="affs"><b<?php if($msgTy == 'fail'){?> class="cuo"<?php }?>></b><?php echo $msgContent;?></p>
    	<?php
    		if ($msgTy == 'sucess'){
    			echo '<p>为了维护投资者合法权益，根据《证券期货投资者适当性管理办法》，在进行基金交易前需完善个人信息，请尽快完善。</p>';
    			echo '<section class="m-btn-wrap"><input class="btn" id="nextBtn" type="button" value="完善信息"/>
					  <input class="btn" id="backBtn" type="button" value="稍后完善"/></section>';
    		}
    	?>
    </div>
</div>
</body>
<script src="/data/lib/zepto.min.js"></script>
<script>
Zepto(function(){
    $('#nextBtn').on('click',function(){
        window.location.href='/jijin/Jz_my/investorManagement';
    });
    
    $('#backBtn').on('click',function(){
        window.location.href='/jijin/Jz_account/entrance';
    });
});
</script>
</html>
