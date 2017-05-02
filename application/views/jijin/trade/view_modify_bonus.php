<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
    <meta name="format-detection" content="telephone=no" />
    <link href="/data/css/mobile.css" media="screen" rel="stylesheet" type="text/css">
    <title>修改分红方式</title>
</head>
<body>
<section class="wrap">
	<section class="m-item-wrap m-item-5-wrap">
        <div class="m-item-5 text-align:center">
            <h3 class="text-center" id= "bonuschange">修改分红方式</h3>
        </div>
    </section>
    <section class="m2-item-wrap">
        <form  method="post" action="/jijin/ModifyBonusController/ModifyResult" id="info_form">
        <input type="hidden" id="json" name="json" value="<?php echo $json?>" data-key="<?php echo $public_key;?>"  data-code="<?php echo $rand_code;?>" />
        <div class="m2-item mt30">
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
        <div class="m2-item" id = "bounsMethod">
            <div class="item-width-wrap">
                <span class="m2-item-t1">分红方式：</span>
                <label style="line-height:30px;">
                    <input type="radio" id="bonusType" class="vertical-mid" name="bonusType" value="1" <?php echo $dividendmethod=='1'?'checked="checked"':'';?> />现金分红
					<input type="radio" id="bonusType" class="vertical-mid" name="bonusType" value="0" <?php echo $dividendmethod=='0'?'checked="checked"':'';?> />红利再投
                </label>
            </div>
        </div>
        <section class="m-btn-wrap mt10 clearfix" id ="jumpButtons" >
            <input class="btn btn-fix-left" id="backBtn" type="button" value="返回"/>
            <input class="btn btn-fix-right" id="nextBtn" type="button" value="下一步"/>
            <input class="btn btn-fix-right" id="commit" type="button" style="display:none;" value="确定变更"/>
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

        $('#nextBtn').on('click',function(){
            M.checkForm(function () {
            	var payDiv = document.getElementById('bounsMethod');
            	div = document.createElement('div');
               //验证全部通过回调               
                document.title = '分红方式变更确认';
                document.getElementById('bonuschange').innerHTML = '确定变更';
                document.getElementById('nextBtn').style.display = 'none';
                document.getElementById('commit').style.display = 'block';
                document.getElementById('bonusType').setAttribute('readonly','true');
                div.setAttribute('class','m2-item');
                div.innerHTML = '<div class="item-width-wrap">'+
                                    '<span class="m2-item-t1">交易密码：</span>'+
                                    '<label>'+
                                        '<input type="password" id="passwd" name="tpasswd" placeholder="请输入交易密码" />'+
                                    '</label>'+
                                '</div>'; 
                document.getElementById('info_form').insertBefore(div, payDiv.nextSibling);
                $('#nextBtn').off();
            });
        });
        
        $('#commit').on('click',function () {
        	var encrypt = new JSEncrypt();
        	//alert($('#pass').attr('data-key'));        
        	encrypt.setPublicKey($('#json').attr('data-key'));
        	var encrypted = encrypt.encrypt($('#passwd').val()+$('#json').attr('data-code'));
        	$('#passwd').val(encrypted);
            $('#info_form').submit();
        });

        $('#backBtn').on('click',function(){
//         	window.location.href='/jijin/Menu/menu3_2';
        	window.location.href='/jijin/Jz_my/index/fund';
        });
    });
</script>
</html>