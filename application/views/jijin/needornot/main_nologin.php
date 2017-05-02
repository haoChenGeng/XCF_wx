<!DOCTYPE html>
<html>
<head lang="en">
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
	<meta name="format-detection" content="telephone=no" />
    <meta name="keywords" content="小牛资本">
    <meta name="description" content="小牛资本管理集团公募基金代销系统">
	<link href="/data/css/mobile.css" media="screen" rel="stylesheet" type="text/css">
	<title>登录</title>
</head>

<body>
<section class="wrap">
    <section class="m-item-wrap">
        <div class="m-item">
            <a class="m-item-a" href="/jijin/Jijin_center">
                <i class="icon icon-record"></i>
                <span class="m-item-text-1">main_nologin</span>
                <div class="arrow-wrap "><!--如果有has-message则红点显示-->
                    <b></b>
                    <i class="icon icon-arrow-right"></i>
                </div>
            </a>
        </div>
        <div class="m-item">
            <a class="m-item-a" href="/invest/product">
                <i class="icon icon-lcproduct"></i>
                <span class="m-item-text-1">基金产品介绍</span>
                <div class="arrow-wrap">
                    <i class="icon icon-arrow-right"></i>
                </div>
            </a>
        </div>
        <div class="m-item">
            <a class="m-item-a" href="/jijin/Jz_account/register">
                <i class="icon icon-authentication"></i>
                <span class="m-item-text-1">基金帐户注册</span>
                <div class="arrow-wrap"><!--如果有has-message则红点显示-->
                    <i class="icon icon-arrow-right"></i>
                </div>
            </a>
        </div>
        <div class="m-item">
            <a class="m-item-a" href="/member/safeCenter">
                <i class="icon icon-safe-center"></i>
                <span class="m-item-text-1">安全中心</span>
                <div class="arrow-wrap"><!--如果有has-message则红点显示-->
                    <i class="icon icon-arrow-right"></i>
                </div>
            </a>
        </div>
        <div class="m-item">
            <a class="m-item-a" href="/member/myMessage">
                <i class="icon icon-message"></i>
                <span class="m-item-text-1">我的消息</span>
                <div class="arrow-wrap "><!--如果有has-message则红点显示-->
                    <b></b>
                    <i class="icon icon-arrow-right"></i>
                </div>
            </a>
        </div>
        <div class="m-item">
            <a class="m-item-a" href="/user/layout">
                <i class="icon icon-exit"></i>
                <span class="m-item-text-1">安全退出</span>
            </a>
        </div>
    </section>
</section>
</body>
<script>
    function charge(){
        window.location.href='<?php echo $base?>/pay/charge';
    }
    function withdraw(){
        window.location.href='<?php echo $base?>/pay/withdraw';
    }
</script>
</html>
