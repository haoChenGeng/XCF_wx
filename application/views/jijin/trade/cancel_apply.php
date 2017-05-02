<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
    <meta name="format-detection" content="telephone=no" />
    <link href="/data/css/mobile.css" media="screen" rel="stylesheet" type="text/css">
    <title>撤单确认</title>
</head>
<body>
<section class="wrap">
	<section class="m-item-wrap m-item-5-wrap">
        <div class="m-item-5 text-align:center">
            <h3 class="text-center" id="redeemChange">撤单</h3>
        </div>
    </section>
    <section class="m2-item-wrap mt30">
        <form  method="post" action="/jijin/CancelApplyController/CancelResult" id="info_form">
        <div class="m2-item mt30">
            <div class="item-width-wrap">
                <span class="m2-item-t1">申请单号：</span>
                <label>
                    <input type="text" style="color:#333;" id="appsheetserialno" name="appsheetserialno" value="<?php echo $appsheetserialno?>" readonly="true"/>
                </label>
            </div>
        </div>
        <div class="m2-item">
            <div class="item-width-wrap">
                <span class="m2-item-t1">基金代码：</span>
                <label>
                    <input type="text" style="color:#333;" id="fundcode" name="fundcode" value="<?php echo $fundcode?>" readonly="true"/>
                </label>
            </div>
        </div>
        <div class="m2-item">
            <div class="item-width-wrap">
                <span class="m2-item-t1">基金名称：</span>
                <label>
                    <input type="text" style="color:#333;" id="fundname" value="<?php echo $fundname?>" readonly="true"/>
                </label>
            </div>
        </div>
        <div class="m2-item">
            <div class="item-width-wrap">
                <span class="m2-item-t1">金额：</span>
                <label>
                    <input type="text" style="color:#333;" id="applicationamount"  name="applicationamount" value="<?php echo $applicationamount?>"  readonly="true"/>
                </label>
            </div>
        </div>
        <div class="m2-item">
            <div class="item-width-wrap">
                <span class="m2-item-t1">份额：</span>
                <label>
                    <input type="text" style="color:#333;" id="applicationvol" value="<?php echo $applicationvol?>"  readonly="true"/>
                </label>
            </div>
        </div>
        <div class="m2-item">
            <div class="item-width-wrap">
                <span class="m2-item-t1">申请日期：</span>
                <label>
                    <input type="text" style="color:#333;" id="operdate" value="<?php echo $operdate?>"  readonly="true"/>
                </label>
            </div>
        </div>
        <div class="m2-item">
            <div class="item-width-wrap">
                <span class="m2-item-t1">交易类型：</span>
                <label>
                    <input type="text" style="color:#333;" id="businesscode" value="<?php echo $businesscode?>"  readonly="true"/>
                </label>
            </div>
        </div>
        <section class="m-btn-wrap mt10 clearfix">
            <input class="btn btn-fix-left" id="backBtn" type="button" value="返回"/> 
            <input class="btn btn-fix-right" id="commit" type="button" value="确定撤销"/>
        </section>
        </form>
    </section>
</section>
</body>
<script src="/data/lib/zepto.min.js"></script>
<script src="/data/js/m.min.js"></script>
<script src="/data/js/common.js"></script>
<script src="/data/js/RSA.js"></script>
<script>
    Zepto(function(){
        M.checkBoxInit();
        $('#commit').on('click',function () {
            $('#info_form').submit();
        });
        
        $('#backBtn').on('click',function(){
            window.location.href='/jijin/Jz_my';
        });
    });
</script>
</html>