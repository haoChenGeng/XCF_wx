<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
    <meta name="format-detection" content="telephone=no" />
    <link href="/data/jijin/css/mobile.css" media="screen" rel="stylesheet" type="text/css">
    <title>赎回基金</title>
</head>
<body>
<section class="wrap">
	<section class="m-item-wrap m-item-5-wrap">
        <div class="m-item-5 text-align:center">
            <h3 class="text-center" id="redeemChange">赎回</h3>
        </div>
    </section>
    <section class="m2-item-wrap">
        <form  method="post" action="/jijin/RedeemFundController/RedeemResult" id="info_form">
        <input type="hidden" id="tano" name="tano" value="<?php echo $tano?>" />
        <input type="hidden" id="transactionaccountid" name="transactionaccountid" value="<?php echo $transactionaccountid?>" />
        <input type="hidden" id="branchcode" name="branchcode" value="<?php echo $branchcode?>" />
        <input type="hidden" id="sharetype" name="sharetype" value="<?php echo $sharetype?>" />

        <div class="m2-item mt30">
            <div class="item-width-wrap">
                <span class="m2-item-t1">基金代码：</span>
                <label>
                    <input type="text" style="color:#333;" id="fundcode" name="fundcode" value="<?php echo $fundcode?>" data-key="<?php echo $public_key;?>"  data-code="<?php echo $rand_code;?>" readonly="true"/>
                </label>
            </div>
        </div>
        <div class="m2-item">
            <div class="item-width-wrap">
                <span class="m2-item-t1">基金名称：</span>
                <label>
                    <input type="text" style="color:#333;" id="fundname" name="fundname" value="<?php echo $fundname?>" readonly="true"/>
                </label>
            </div>
        </div>
        <div class="m2-item">
            <div class="item-width-wrap">
                <span class="m2-item-t1">总份额：</span>
                <label>
                    <input type="text" style="color:#333;" id="fundvolbalance"  value="<?php echo $fundvolbalance?>"  readonly="true"/>
                </label>
            </div>
        </div>
        <div class="m2-item">
            <div class="item-width-wrap">
                <span class="m2-item-t1">冻结份额：</span>
                <label>
                    <input type="text" style="color:#333;" id="fundfrozenbalance" value="<?php echo $fundfrozenbalance?>"  readonly="true"/>
                </label>
            </div>
        </div>
        <div class="m2-item">
            <div class="item-width-wrap">
                <span class="m2-item-t1">可用份额：</span>
                <label>
                    <input type="text" style="color:#333;" id="availablevol" value="<?php echo floatval($availablevol)?>"  readonly="true"/>
                </label>
            </div>
        </div>
        <div class="m2-item">
            <div class="item-width-wrap">
                <span class="m2-item-t1">赎回份额：</span>
                <label>
                    <input type="number" style="color:#333;" id="applicationval" name="applicationval" data-error="赎回份额错误"  placeholder="请输入赎回份额" oninput="complete(this);" />
                </label>
            </div>
        </div>
        <div class="m2-item" id="payDiv">
            <div class="item-width-wrap">
                <span class="m2-item-t1">巨额赎回：</span>
                <label style="line-height:30px;">
                    <input type="radio" id="largeRedemptionFlag" class="vertical-mid" name="largeRedemptionFlag" checked="checked" value="1" />顺延
					<input type="radio" id="largeRedemptionFlag" class="vertical-mid" name="largeRedemptionFlag" value="0" />取消
                </label>
            </div>
        </div>
        <section class="m-btn-wrap mt10 clearfix">
            <input class="btn btn-fix-left" id="backBtn" type="button" value="返回"/> 
            <input class="btn btn-fix-right" id="nextBtn" type="button" value="下一步"/>
            <input class="btn btn-fix-right" id="commit" type="button" style="display:none;" value="确定赎回"/>
        </section>
        </form>
    </section>
</section>
</body>
<script src="/data/lib/zepto.min.js"></script>
<script src="/data/jijin/js/m.min.js"></script>
<script src="/data/jijin/js/common.js"></script>
<script src="/data/js/RSA.min.js"></script>
<script>
function complete(obj) {
    var a = obj.value.toString();
    var b = /^\./;
    var c = b.test(a);
    if (c) {
        obj.value = '0' + a;
    }
}

    Zepto(function(){
        M.checkBoxInit();
        $('#nextBtn').on('click',function(){
            M.checkForm(function () {
            	var payDiv = document.getElementById('payDiv'),
            	applicationval = parseFloat(document.getElementById('applicationval').value),
            	availablevol = parseFloat(document.getElementById('availablevol').value),
                div1 = document.createElement('div');
            	div2 = document.createElement('div');
                if ((applicationval-parseInt(applicationval*100)/100) > 0.0000000001) {
                    alert('份额最小单位为0.01');
                    return false;
                }else if (applicationval <= 0 ||  applicationval > availablevol) {
                    alert('输入份额错误或无效');
                    return false;
                }else if (!applicationval) {
                    alert('请输入赎回份额');
                    return false;
                }
                document.getElementById('applicationval').value = applicationval;
                $.post("/jijin/RedeemFundController/redeemFee", {channelid:"<?php echo $channelid?>",applicationvol:applicationval,businesscode:24,tano:"<?php echo $tano?>",fundcode:$('#fundcode').val(),sharetype:"<?php echo $sharetype?>"},function(res){
                	retData = JSON.parse(res);
                	if (retData.code == 0){
                        //验证全部通过回调        
                        document.title = '赎回确认';
                        document.getElementById('redeemChange').innerHTML = '赎回确认';
                        document.getElementById('nextBtn').style.display = 'none';
                        document.getElementById('commit').style.display = 'block';
                        document.getElementById('applicationval').setAttribute('readonly','true');
                        div1.setAttribute('class','m2-item');
                        div1.innerHTML = '<div class="item-width-wrap">'+
                                            '<span class="m2-item-t1">交易密码：</span>'+
                                            '<label>'+
                                                '<input type="password" id="passwd" name="tpasswd" data-reg=".+"  data-error="交易密码不能为空" placeholder="请输入交易密码" />'+
                                            '</label>'+
                                        '</div>'; 
                        document.getElementById('info_form').insertBefore(div1, payDiv.nextSibling);
                        div2.setAttribute('class','m2-item');
                        div2.innerHTML = '<div class="item-width-wrap">'+
                        					'<span class="m2-item-t1">赎回费用：</span>'+
                                            '<label>'+
                                            	'<input type="text"  style="color:#333;" value="'+retData.charge+'"/>'+
                                            '</label>'+
                                        '</div>'; 
                        document.getElementById('info_form').insertBefore(div2, payDiv.nextSibling);
                        $('#nextBtn').off();
                    }else{
                    	alert('查询赎回费用失败');
                	}
                }); 

            });
        });
        $('#commit').on('click',function () {
        	M.checkForm(function () {
            	var encrypt = new JSEncrypt();
            	encrypt.setPublicKey($('#fundcode').attr('data-key'));
            	var encrypted = encrypt.encrypt($('#passwd').val()+$('#fundcode').attr('data-code'));
            	$('#passwd').val(encrypted);
                $('#info_form').submit();
        	});
        });
        
        $('#backBtn').on('click',function(){
            window.location.href='/jijin/Jz_my';
        });
    });
</script>
</html>