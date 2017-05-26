<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
    <meta name="format-detection" content="telephone=no"/>
    <link href="<?php echo $base ?>/data/jijin/css/mobile.css" media="screen" rel="stylesheet" type="text/css">
    <title>基金产品</title>
</head>
<body>
<section class="wrap">
    <section class="m-item-wrap m-item-5-wrap">
        <div class="m-item-5">
            <h3 class="text-center">基金产品</h3>
        </div>
    </section>
    <section class="m-item-wrap">
        <div class='m-item'>
            <i class='icon icon-lcproduct'></i>
            <span class='m-item-text-1'><label style='text-align:left; width:300px;'>基金名称:<?php echo $fundlist['fundname'];?></label></span>
        </div>
        <div class='m-item'>
            <i class='icon icon-lcproduct'></i>
            <span class='m-item-text-1'><label style='text-align:left; width:300px;'>基金代码:<?php echo $fundlist['fundcode'];?></label></span>
        </div>
        <div class='m-item'>
            <i class='icon icon-lcproduct'></i>
            <span class='m-item-text-1'><label style='text-align:left; width:300px;'>基金类型:<?php echo $fundlist['fundtype'];?></label></span>
        </div>
        <div class='m-item'>
            <i class='icon icon-lcproduct'></i>
            <span class='m-item-text-1'><label style='text-align:left; width:300px;'>风险等级:<?php echo $fundlist['risklevel'];?></label></span>
        </div>
        <div class='m-item'>
            <i class='icon icon-lcproduct'></i>
            <span class='m-item-text-1'><label style='text-align:left; width:300px;'>收费方式:<?php echo $fundlist['sharetype'];?></label></span>
        </div>
        <div class='m-item'>
            <i class='icon icon-lcproduct'></i>
            <span class='m-item-text-1'><label style='text-align:left; width:300px;'>基金净值:<?php echo $fundlist['nav']?></label></span>
        </div>
        <?php
        	if ($fundlist['fundtype'] == '货币型基金'){
        		echo '<div class="m-item">
            			<i class="icon icon-lcproduct"></i>
            			<span class="m-item-text-1"><label style="text-align:left; width:300px;">七日年化收益率:'.$fundlist['growthrate'].'</label></span>
    				  </div>';
        		echo '<div class="m-item">
            			<i class="icon icon-lcproduct"></i>
            			<span class="m-item-text-1"><label style="text-align:left; width:300px;">万份收益:'.$fundlist['fundincomeunit'].'</label></span>
    				  </div>';
        	}
        ?>
        <div class='m-item'>
            <i class='icon icon-lcproduct'></i>
            <span class='m-item-text-1'><label style='text-align:left; width:300px;'>基金状态:<?php echo $fundlist['status'];?></label></span>
        </div>
        </section>
        <section class="m-btn-wrap mt10 clearfix">
            <input class="btn btn-fix-left"  type="button" onclick="goto_buyfund()" value=<?php echo $purchasetype;?>></> 
            <input class="btn btn-fix-right" type="button"  onclick="goto_main()" value="返回"/>
        </section>
</section>
</body>
<script>
    function goto_main() {
        window.location.href = '<?php echo $base.$next_url?>'
    }
    function goto_buyfund() {
        window.location.href = "<?php echo "/jijin/PurchaseController/Apply?fundcode=".$fundlist['fundcode'].'&purchasetype='.$purchasetype;?>"
    }

</script>
</html>