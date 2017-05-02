<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>可进行分红方式变更的列表</title>
    <meta name="keywords" content="小牛资本" />
    <meta name="description" content="小牛资本管理集团公募基金代销系统" />
    <meta name="format-detection" content="telephone=no" />
    <meta name="apple-mobile-web-app-capable" content="no" />
    <link href="<?php echo $base?>/data/css/mobile.css" media="screen" rel="stylesheet" type="text/css">
</head>
<body>
	<div>
		<!-- header-->
		<section class="m-item-wrap m-item-5-wrap">
	        <div class="m-item-5 text-align:center">
	            <h3 class="text-center">可进行分红方式变更的列表</h3>
	        </div>
	    </section>
		<section class="m-item-wrap"> 
	        <div class="m-item">
	            <div class="m-item-name-title">名称</div>
	            <div class="m-item-code-title">代码</div>
	            <div class="m-item-nav-title">收费方式</div>
	            <div class="m-item-nav-title">分红方式</div>
	            <div class="m-item-nav-title">更多</div>
	        </div>
			<div class="m-item">
				<?php for($i=0;$i<count($fundInfoArray);$i++) {?>
				    <a class='icon-arrow-display' href='/jijin/ModifyBonusModeController/Modify/?fundcode=<?php echo $fundInfoArray[$i]['fundcode']?>&tano=<?php echo $fundInfoArray[$i]['tano']?>&fundname=<?php echo $fundInfoArray[$i]['fundname']?>&dividendmethod=<?php echo $fundInfoArray[$i]['dividendmethod']?>&custno=<?php echo $fundInfoArray[$i]['custno']?>&transactionaccountid=<?php echo $fundInfoArray[$i]['transactionaccountid']?>&branchcode=<?php echo $fundInfoArray[$i]['branchcode']?>&sharetype=<?php echo $fundInfoArray[$i]['sharetype']?>'>
				     <div class="m-item-name text-center"  ><span><?php echo $fundInfoArray[$i]['fundname']?></span></div>
				     <div class="m-item-code"  ><span><?php echo $fundInfoArray[$i]['fundcode']?></span></div>
				     <div class="m-item-nav"  ><span><?php echo $fundInfoArray[$i]['sharetype']=='A'?'前收费':'后收费'?></span></div>
				     <div class="m-item-nav"  ><span><?php echo $fundInfoArray[$i]['dividendmethod']=='0'?'红利转投':'现金分红'?></span></div>
				     <div class="arrow-wrap ">
                		<b></b>
                		<i class="icon icon-arrow-right"></i>
            		</div>
            		</a>
				<?php }?>
			</div>
		</section>
		<div class="m-btn-wrap">
		    <input class="btn btn-2" type="button" onclick="history.go(-1)" value="返回上一页">
		</div>
	</div>
	<!-- /page -->
</body>
</html>