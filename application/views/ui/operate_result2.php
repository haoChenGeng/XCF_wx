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
		<h3 style="text-align:center;padding: 30px;"><?php echo $ret_msg?></h3> 
	</div>
	<form  name="form" method="post" action=<?php echo $forward_url?> id="login_form">
	    	<input type='hidden' id="data123" name="data" value=<?php echo isset($data)?$data:'';?> />
	    	<a id="sub" data-role="button" data-theme="b" class="risk-btn risk-result" ><?php echo $forward_msg?></a>
	</form>
	<a href="<?php echo $back_url?>" data-role="button" data-theme="b" class="risk-btn risk-result" >返回</a>
</div>
<script>	
	document.getElementById('sub').addEventListener('click', function() {
		document.getElementById('login_form').submit();
	});
</script>
</body>
</html>
