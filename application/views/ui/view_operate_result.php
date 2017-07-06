<?php 
header("Cache-control:no-cache,no-store,must-revalidate"); 
header("Pragma:no-cache"); 
header("Expires:0"); 
?> 
<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
	<link rel="stylesheet" href="<?php echo $base ?>/data/jijin/css/risk.css">
</head>
<body>

<div data-role="page" id="pageone">
	<div data-role="content">
		<section class="m-item-wrap m-item-5-wrap">
	        <div class="m-item-5">
	            <h3 class="text-center"><?php echo $head_title?></h3>
	        </div>
	    </section>
	    <?php echo $ret_code=='0000'?'<i class="risk-result-icon iconfont">&#xe603;</i>':'<i class="risk-result-icon iconfont">&#xe606;</i>'?>
		<h2 style="text-align:center"><?php echo $ret_msg?></h2> 
	</div>
	<a href="<?php echo $back_url?>" data-role="button" data-theme="b" class="risk-btn risk-result" ><?php echo isset($nextDes) ? $nextDes : '返回';?></a>
</div>

</body>
</html>
