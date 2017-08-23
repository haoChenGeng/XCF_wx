<!DOCTYPE html>
<html>
<head>
	<title>产品展示适当性说明</title>
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
	            <h3 class="text-center">产品展示适当性说明</h3>
	        </div>
	    </section>
		<!-- <h2 style="text-align:center;color: #333;">风险不匹配警示函</br></h2> -->
		<p style="text-align:center;padding: 10px 20px;color: #333;">尊敬的投资者您好！根据证监会《证券期货投资者适当性管理办法》的以下相关规定，产品列表已根据您的风险等级作筛选展示</br></p>
		<p class="protocol-context">
			第十八条 经营机构应当根据产品或者服务的不同风险等级，对其适合销售产品或者提供服务的投资者类型作出判断，根据投资者的不同分类，对其适合购买的产品或者接受的服务作出判断。<br>
			第十九条 经营机构告知投资者不适合购买相关产品或者接受相关服务后，投资者主动要求购买风险等级高于其风险承受能力的产品或者接受相关服务的，经营机构在确认其不属于风险承受能力最低类别的投资者后，应当就产品或者服务风险高于其承受能力进行特别的书面风险警示，投资者仍坚持购买的，可以向其销售相关产品或者提供相关服务。<br>
			第二十条 经营机构向普通投资者销售高风险产品或者提供相关服务，应当履行特别的注意义务，包括制定专门的工作程序，追加了解相关信息，告知特别的风险点，给予普通投资者更多的考虑时间，或者增加回访频次等。<br>
			第二十一条 经营机构应当根据投资者和产品或者服务的信息变化情况，主动调整投资者分类、产品或者服务分级以及适当性匹配意见，并告知投资者上述情况。<br>
			第二十二条 禁止经营机构进行下列销售产品或者提供服务的活动：<br>
			（一）向不符合准入要求的投资者销售产品或者提供服务；<br>
			（二）向投资者就不确定事项提供确定性的判断，或者告知投资者有可能使其误认为具有确定性的意见；<br>
			（三）向普通投资者主动推介风险等级高于其风险承受能力的产品或者服务；<br>
			（四）向普通投资者主动推介不符合其投资目标的产品或者服务；<br>
			（五）向风险承受能力最低类别的投资者销售或者提供风险等级高于其风险承受能力的产品或者服务；<br>
			（六）其他违背适当性要求，损害投资者合法权益的行为。<br>
		</p>
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

