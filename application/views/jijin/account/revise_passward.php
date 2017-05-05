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
            <h3 class="text-center">修改<?php if (isset($pwdtype)) echo $pwdtype == 1 ? '登录':'交易';?>密码</h3>
        </div>
    </section>
		<form  name="form" method="post" action="/jijin/Jz_account/revise_passward<?php if (isset($pwdtype)) echo '/'.$pwdtype;?>"id="login_form">
			<section class="m-item-wrap" style="margin-top:0;">
				<input type="hidden" name="pwdtype" value="<?php echo $pwdtype;?>" />
				<div class="m-item" style="border-top:0;">
					<i class="icon icon-pwd"></i>
					<label>
						<input type="password" id="oldpwd" class="w80-p"  name="oldpwd"   data-reg=".+"  data-error="密码不能为空" data-key="<?php echo $public_key;?>"  data-code="<?php echo $rand_code;?>" placeholder="请输入旧密码" />
					</label>
				</div>
				<div class="m-item">
					<i class="icon icon-pwd"></i>
					<label>
						<input type="password" id="pass1" class="w80-p"  name="newpwd"   data-reg=".+"  data-error="密码不能为空" placeholder="请输入新密码" />
					</label>
				</div>
				<div class="m-item">
					<i class="icon icon-pwd"></i>
					<label>
						<input type="password" id="pass2" class="w80-p"  name="newpwd2"   data-reg=".+"  data-error="密码不能为空" placeholder="请再次输入新密码" />
					</label>
				</div>
			</section>
			<section class="m-btn-wrap">
				<input class="btn" type="button" id ="submit_button" value="提交"/>
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
                var pass1 = $('#pass1').val();
                var pass2 = $('#pass2').val();
                if (pass1 === pass2)
                {
                	var encrypt = new JSEncrypt();
// alert($('#pass').attr('data-key'));             
    				encrypt.setPublicKey($('#oldpwd').attr('data-key'));
                    var encrypted = encrypt.encrypt($('#oldpwd').val()+$('#oldpwd').attr('data-code')+$('#pass1').val());
    				$('#oldpwd').val(encrypted);
    				$('#pass1').val('');
    				$('#pass2').val('');
// alert(encrypted);
                    $('#login_form').submit();
                }
                else
                {
                	M.alert({
                        title:'提示',
                        message:'两次输入密码不一致！'
                	});
                }
            });
        });
    });
</script>

</html>