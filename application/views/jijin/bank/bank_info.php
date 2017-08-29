<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
    <meta name="format-detection" content="telephone=no" />
    <meta name="keywords" content="小牛资本">
    <meta name="description" content="小牛资本管理集团公募基金代销系统">
    <link href="/data/jijin/css/mobile.css" media="screen" rel="stylesheet" type="text/css">
    <title>银行卡记录</title>
</head>
<body>
<section class="wrap" style="padding-bottom:0;">
    <section class="m2-item-wrap">
        <section class="m-item-wrap m-item-5-wrap">
            <div class="m-item-5">
                <h3 class="text-center">银行卡记录</h3>
            </div>
        </section>
        <?php if(isset($bank_info)){foreach ($bank_info as $key => $value) {?>

        <div class="m2-item dash-border bank-info">     
            <div class="bank-info-item1">
                <div class="color-blue">银行卡号：<?php echo substr($value['depositacct'],0,4).'***'.substr($value['depositacct'],-6);?></div>
                <div class="color-blue">所属银行：<?php echo $value['channelname'];?></div>
                <div class="color-black">状态：<?php echo $value['status'];?></div>
            </div>
            <?php
            	if (!isset($value['noOper'])){
            		echo '<a class="m-item-a bank-info-btn del" href="/jijin/Fund_bank/bankcard_delete/'.$value['channelid'].'">删除</a>';
            		echo '<a class="m-item-a bank-info-btn change" href="/jijin/Fund_bank/operation/bankcard_change/'.$value['channelid'].'/'.$value['moneyaccount'].'">更换</a>';
            	}
            ?>      
        </div>
        <?php }}?>
    </section>
</section>
<section class="m-btn-wrap">
	<?php
		if ($num_channel>0){
			echo ('<a class="overhidden disb mr10" href="/jijin/Fund_bank/operation/bankcard_add"><img class="disb fr" src="/data/jijin/img/add-card.png" alt="添加银行卡"></a>');
		}
	?>
<!--     <a class="overhidden disb mr10" href="/jijin/Fund_bank/bank_add"><img class="disb fr" src="/data/img/add-card.png" alt="添加银行卡"></a> -->
    <input class="btn mt10" onclick="window.location.href='/jijin/Jz_my'" type="button" value="返回"/>
</section> 
</body>
</html>