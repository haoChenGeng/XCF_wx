<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
    <meta name="format-detection" content="telephone=no" />
    <link href="/data/css/mobile.css" media="screen" rel="stylesheet" type="text/css">
    <title>修改分红方式确认</title>
</head>
<body>
<section class="wrap">
	<section class="m-item-wrap m-item-5-wrap">
        <div class="m-item-5 text-align:center">
            <h3 class="text-center">修改分红方式确认</h3>
        </div>
    </section>
    <section class="m2-item-wrap">
        <form  method="post" action="/jijin/ModifyBonusModeController/ModifyResult" id="info_form">
        <input type="hidden" id="custno" name="custno" value="<?php echo $custno?>" />
        <input type="hidden" id="transactionaccountid" name="transactionaccountid" value="<?php echo $transactionaccountid?>" />
        <input type="hidden" id="branchcode" name="branchcode" value="<?php echo $branchcode?>" />
        <input type="hidden" id="sharetype" name="sharetype" value="<?php echo $sharetype?>" />
        <div class="m2-item mt30">
            <div class="item-width-wrap">
                <span class="m2-item-t1">基金代码：</span>
                <label>
                    <input type="text" id="fundcode" name="fundcode" value="<?php echo $fundcode?>" placeholder="请输入基金代码" readonly="true"/>
                </label>
            </div>
        </div>
        <div class="m2-item">
            <div class="item-width-wrap">
                <span class="m2-item-t1">基金名称：</span>
                <label>
                    <input type="text" id="fundname" name="fundname" value="<?php echo $fundname?>" placeholder="请输入基金名称" readonly="true"/>
                </label>
            </div>
        </div>
        <div class="m2-item">
            <div class="item-width-wrap">
                <span class="m2-item-t1">分红方式：</span>
                <label style="line-height:30px;">
                    <input type="radio" id="bonusType" class="vertical-mid" name="bonusType" value="1" <?php echo $bonusType=='1'?'checked="checked"':''?>/>现金分红
					<input type="radio" id="bonusType" class="vertical-mid" name="bonusType" value="0" <?php echo $bonusType=='0'?'checked="checked"':''?> />红利再投
                </label>
            </div>
        </div>
        <div class="m2-item">
            <div class="item-width-wrap">
                <span class="m2-item-t1">交易密码：</span>
                <label>
                    <input type="text" id="passwd" name="passwd" placeholder="请输入交易密码" />
                </label>
            </div>
        </div>
        <section class="m-btn-wrap mt10 clearfix">
            <input class="btn btn-fix-left" id="backBtn" type="button" onclick="history.go(-1)" value="返回"/>
            <input class="btn btn-fix-right" id="nextBtn" type="button" value="确定修改"/>
        </section>
        </form>
    </section>
</section>
</body>
<script src="/data/lib/zepto.min.js"></script>
<script src="/data/js/m.min.js"></script>
<script src="/data/js/common.js"></script>
<script>
    Zepto(function(){
        M.checkBoxInit();
        $('#nextBtn').on('click',function(){
            M.checkForm(function () {
                //验证全部通过回调
                $('#info_form').submit();
            });
        });

//         $('#backBtn').on('click',function(){
//         	window.location.href='/jijin/Menu/menu3_2';
//         });
    })
</script>
</html>