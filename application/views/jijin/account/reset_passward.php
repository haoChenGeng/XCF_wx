<!DOCTYPE html>
<html>

<head lang="en">
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
	<meta name="format-detection" content="telephone=no" />
	<meta name="keywords" content="小牛资本">
	<meta name="description" content="小牛资本管理集团公募基金代销系统">
	<link href="/data/jijin/css/mobile.css" media="screen" rel="stylesheet" type="text/css">
	<title>密码修改</title>
</head>

<body>
	<section class="wrap">
	<section class="m-item-wrap m-item-5-wrap">
        <div class="m-item-5">
            <h3 class="text-center">重置交易密码</h3>
        </div>
    </section>
		<form  name="form" method="post" action="/jijin/Jz_account/resetPassward" id="login_form">
			<section class="m-item-wrap" style="margin-top:0;">
				<div class="m-item" style="border-top:0;">
					<i class="icon icon-pwd"></i>
					<label>
						<input type="text" id="certificateno" class="w260" name="certificateno"   data-reg=".+"  data-error="证件号码不能为空" data-key="<?php echo $public_key;?>"  data-code="<?php echo $rand_code;?>" placeholder="请输入基金开户使用的<?php echo $certificatetype?>号码" />
					</label>
				</div>
				<div class="m-item">
					<i class="icon icon-pwd"></i>
					<label>
						<input type="text" id="verifyCode" class="input"  name="verifyCode"   data-reg=".+"  data-error="手机验证码不能为空" placeholder="请输入手机验证码" />
						<a href="#" id="sendSms" class="input_btn">获取验证码</a>
					</label>
				</div>
				<div class="m-item">
					<i class="icon icon-pwd"></i>
					<label>
						<input type="password" id="pass" class="w80-p"  name="newpwd"   data-reg=".+"  data-error="密码不能为空" placeholder="请输入新密码" />
					</label>
				</div>
			</section>
			<section class="m-btn-wrap">
				<input class="btn  btn-fix-left" type="button" id ="submit_button" value="提交"/>
				<input class="btn  btn-fix-right" onclick="window.location.href='/jijin/Jz_my'" type="button" value="返回"/>
			</section>
		</form>
	</section>
</body>

<script src="/data/lib/zepto.min.js"></script>
<script src="/data/jijin/js/m.min.js"></script>
<script src="/data/jijin/js/common.js"></script>
<script src="/data/js/RSA.min.js"></script>

<script>
    Zepto(function($){
        M.checkBoxInit();

        $('#submit_button').on('click', function (event) {
            event.preventDefault();
            M.checkForm(function () {
            	var encrypt = new JSEncrypt();
            	encrypt.setPublicKey($('#certificateno').attr('data-key'));
            	var encrypted = encrypt.encrypt($('#certificateno').val()+$('#certificateno').attr('data-code')+$('#pass').val());
            	$('#certificateno').val(encrypted);
            	$('#pass').val('');
            	$('#login_form').submit();
            });
        });
    });

    $('#sendSms').on('click',function(){
    	$.post("/jijin/Jz_account/sendSms", {},function(res){
            M.alert({
                title:'提示',
                message:res==null||res==''||res==undefined?'发送失败':res
            });
            if( res == '验证码已发送！'){
//                alert("发送成功");
                var timer = null;
                var times = 60;
                var oldStr = $('#sendSms').html();

                $('#sendSms').html(times+' 秒');
                $('#sendSms').attr('disabled','disabled').addClass('disabled');
                timer = setInterval(function(){
                    if(times==0){
                        clearInterval(timer);
                        $('#sendSms').html(oldStr);
                        $('#sendSms').removeAttr('disabled').removeClass('disabled');
                    }else{
                        times--;
                        $('#sendSms').html(times+' 秒');
                    }
                },1000);
            }
        })
    });
</script>

</html>