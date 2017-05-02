<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
    <meta name="format-detection" content="telephone=no" />
    <link href="<?php echo $base?>/data/css/mobile.css" media="screen" rel="stylesheet" type="text/css">
    <title>风险评测</title>
</head>
<body>
<section class="wrap">
    <section class="m-item-wrap m-item-5-wrap">
        <div class="m-item-5">
            <h3 class="text-center">风险评测</h3>
        </div>
    </section>
    <section class="m-item-wrap">
        <div class="m-item">
            <a class="m-item-a" href="">
                <i class="icon icon-lcproduct"></i>
                <span class="m-item-text-1">风险评测正建设中,精彩内容即将推出,谢谢关注!</span>
                <div class="arrow-wrap">
                    <i class="icon icon-arrow-right"></i>
                </div>
            </a>
        </div>
        <div class="m-btn-wrap">
            <input class="btn btn-2" type="button" onclick="goto_main()" value="返回主页">
        </div>
    </section>
</section>
</body>
<script>
    function goto_main(){
        window.location.href='<?php echo $base?>/info/goto_main';
    }
</script>
</html>