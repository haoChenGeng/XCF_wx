<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
	    <meta name="viewport" content=" initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	    <title>我的基金</title>
	    <meta name="keywords" content="小牛新财富，公募基金" />
	    <meta name="description" content="" />
	    <meta name="format-detection" content="telephone=no" />
	    <meta name="apple-mobile-web-app-capable" content="no" />
	    <link href="<?php echo $base?>/data/css/mobile.css" media="screen" rel="stylesheet" type="text/css">
		<script src="http://code.jquery.com/jquery-1.8.3.min.js"></script>
	</head>
	<body>
		<div>
			<!-- header-->
			<section class="m-item-wrap m-item-5-wrap">
		        <div class="m-item-5 text-align:center">
		            <h3 class="text-center">我的基金</h3>
		        </div>
		    </section>
			<section class="m-item-wrap"> 
		        <div class="m-item">
		            <div class="m-item-name-title">名称</div>
		            <div class="m-item-code-title">代码</div>
		            <div class="m-item-nav-title">净值</div>
		            <div class="m-item-nav-title">当前份额</div>
		            <div class="m-item-nav-title">更多</div>
		        </div>
				<div class="m-item">
					<?php for($i=0;$i<count($fundInfoArray);$i++) {?>
						<?php if (0 == strcmp($operType, 'detail')) {?><!-- 基金详情 -->
						<a class='icon-arrow-display' href='/jijin/jijinpro/showprodetail/?fundid=<?php echo $fundInfoArray[$i]['fundcode']?>&tano=<?php echo $fundInfoArray[$i]['tano']?>'>
					    <?php } elseif (0 == strcmp($operType, 'redeem')) {?> <!-- 赎回 -->
					    <a class='icon-arrow-display' href='/jijin/RedeemFundController/Redeem/?fundcode=<?php echo $fundInfoArray[$i]['fundcode']?>&fundname=<?php echo $fundInfoArray[$i]['fundname']?>&tano=<?php echo $fundInfoArray[$i]['tano']?>&custno=<?php echo $fundInfoArray[$i]['custno']?>&transactionaccountid=<?php echo $fundInfoArray[$i]['transactionaccountid']?>&branchcode=<?php echo $fundInfoArray[$i]['branchcode']?>&channelname=<?php echo $fundInfoArray[$i]['channelname']?>&lastfundvol=<?php echo $fundInfoArray[$i]['last_fundvol']?>&sharetype=<?php echo $fundInfoArray[$i]['sharetype']?>'>
					    <?php }?> 
					     <div class="m-item-name text-center"  ><span><?php echo $fundInfoArray[$i]['fundname']?></span></div>
					     <div class="m-item-code"  ><span><?php echo $fundInfoArray[$i]['fundcode']?></span></div>
					     <div class="m-item-nav"  ><span><?php echo $fundInfoArray[$i]['last_fundvol']?></span></div>
					     <div class="m-item-nav"  ><span><?php echo $fundInfoArray[$i]['fundmarketvalue']?></span></div>
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