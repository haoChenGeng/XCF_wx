<!DOCTYPE html>
<html>
<head>
	<title>适当性管理</title>
	<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
	<link href="/data/jijin/css/mobile.css" media="screen" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="/data/jijin/css/risk.css">
</head>
<body>

<div data-role="page" id="pageone">
	<!-- <div class="head-back">
		<span class="head-back-icon" onclick="window.history.go(-1)">返回</span>
	</div> -->
	<div data-role="content">
		<section class="m-item-wrap m-item-5-wrap">
	        <div class="m-item-5">
	            <h3 class="text-center">适当性管理</h3>
	        </div>
	    </section>
		<h2 style="text-align:center">风险不匹配警示函</br></h2>
		<p style="text-align:center;padding: 50px 20px;">本人对申请购买产品的风险等级高于本人风险承受能力情况已知悉，并且已充分了解该产品的风险特征和可能的不利后果。</br></br>经本人审慎考虑，坚持申请查看或者购买高于本人本人风险承受能力外的产品，并自愿承担由此可能产生的一切不利后果和损失。</br></p>
	</div>
	<form  name="form" method="post" action="/jijin/jz_fund/viewAllFund" id="login_form">
		<?php $allow = (isset($_SESSION['qryallfund']) && !$_SESSION['qryallfund']) ? 1 : 0;?>
	    	<input type='hidden' name="allow" value="<?php echo $allow?>" />
	    	<input type='submit' data-role="button" data-theme="b" class="risk-btn risk-result mt20" value=<?php echo ($allow ? '确认' : '取消').'查看所有基金产品'?> />
	    	<input type='button'  class="risk-btn risk-result" onclick="history.go(-1);" style="margin-top: 20px;" value="返回" />
	</form>
</div>

</body>
</html>

