<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
    <meta name="format-detection" content="telephone=no" />
    <link href="/data/css/mobile.css" media="screen" rel="stylesheet" type="text/css">
    <link rel="stylesheet" type="text/css" href="/data/css/iconfont.css">
    <title>修改分红方式结果</title>
</head>
<body>
<section class="wrap">
	<section class="m-item-wrap m-item-5-wrap">
        <div class="m-item-5 text-align:center">
            <h3 class="text-center">修改分红方式结果</h3>
        </div>
    </section>
    <section class="m2-item-wrap">
		<section class="m-btn-wrap mt10">
			<?php 
			if (0 == strcmp($ret_code, '0000')) {
				echo '<i class="iconfont queding">&#xe600;</i>';
			} else {
				echo '<i class="iconfont queding">&#xe610;</i>';
			}
			?>
            <h3 class="mt20"><?php echo $ret_msg?></h3>
        </section>
        <section class="m-btn-wrap mt10">
            <input class="btn" onclick=goBack(); type="button" value="返回"/>
        </section>
    </section>
</section>
</body>
<script src="/data/lib/zepto.min.js"></script>
<script src="/data/js/m.min.js"></script>
<script src="/data/js/common.js"></script>
<script>
	function goBack(){
// 	    window.location.href='/jijin/Menu/menu3_2';
	    window.location.href='/jijin/Jz_my/index/fund';
	}
</script>
</html>