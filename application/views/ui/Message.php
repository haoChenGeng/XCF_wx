<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>系统消息提醒</title>
<style type="text/css">
	*{font-family: 微软雅黑;font-size: 30px;}
	#error_t{height: 549px;margin-top: 50px;}
	body{
		text-align: center;
		width: 446px;
		margin: 0 auto;
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
        <p class="affis"><a href="<?php echo $msgUrl;?>">如果你的浏览器没有自动跳转，请点击这里</a></p>
    </div>
</div>
<script language="javascript">setTimeout("location.href='<?php echo $msgUrl;?>';", 3000);</script>
</body>
</html>
