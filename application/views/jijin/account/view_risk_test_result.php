<!DOCTYPE html>
<html>
<head lang="en">
	<meta charset="utf-8">
	<meta name="keywords" content="小牛资本">
	<meta name="description" content="小牛资本管理集团公募基金代销系统">
	<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
	<title>风险评测结果</title>
	<link rel="stylesheet" href="<?php echo $base ?>/data/jijin/css/risk.css">
</head>
<body>
	<div data-role="page" id="pageone">
		<div data-role="content">
			<section class="m-item-wrap m-item-5-wrap">
		        <div class="m-item-5">
		            <h3 class="text-center">测试结果</h3>
		        </div>
		    </section>
		    <?php 
	    	if ($ret_code == '0000') {
	    		echo '<i class="risk-result-icon iconfont">&#xe603;</i>';
	    	} else {
	    		echo '<i class="risk-result-icon iconfont">&#xe606;</i>';
	    	}
	    	?>
			<h2 style="text-align:center"><?php echo $ret_msg?></h2> 
			<h2 style="text-align:center">您的风险等级为： <?php echo $custrisk?></h2>
		</div>
		<a href="<?php  
					if (isset($_SESSION['url_afteroperation'])){
						echo $_SESSION['url_afteroperation'];
						unset($_SESSION['url_afteroperation']);
					}else{
						echo "/jijin/Jz_my/index";	
					}
				 ?>" data-role="button" data-theme="b" class="risk-btn risk-result" >返回</a>
	</div>
</body>
</html>
