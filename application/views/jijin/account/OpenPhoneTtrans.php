<!DOCTYPE html>
<html>

<head lang="en">
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
	<meta name="format-detection" content="telephone=no" />
	<link href="/data/css/swiper.3.1.7.min.css" media="screen" rel="stylesheet" type="text/css">
	<link href="/data/css/mobile.css" media="screen" rel="stylesheet" type="text/css">
	<title>开通手机交易</title>
	
</head>

<body>
	<section class="wrap">
		<section class="m-item-wrap m-item-5-wrap">
		    <div class="m-item-5">
		        <h3 class="text-center">开通手机交易</h3>
		    </div>
		</section>
		<form  name="form" method="post" action="/jijin/Jz_account/open_phone_trans" id="login_form" onsubmit="return false">
			<section class="m-item-wrap" style="margin-top:0;">
				<div class="m-item">
					<i class="icon icon-pwd"></i>
					<label>
						<input type="password" id="lpasswd" class="w80-p"  name="lpasswd"  data-reg=".+"  data-error="密码不能为空" data-key="<?php echo $public_key;?>"  data-code="<?php echo $rand_code;?>" placeholder="请输入基金登录密码" />
					</label>
				</div>
				<div class="m-item">
					<i class="icon icon-pwd"></i>
					<label>
						<input type="password" id="tpasswd" class="w80-p"  name="tpasswd"  data-reg=".+"  data-error="密码不能为空" data-key="<?php echo $public_key;?>"  data-code="<?php echo $rand_code;?>" placeholder="请设置基金交易密码" />
					</label>
				</div>
			</section>
			<section class="m-btn-wrap">
				<input class="btn" type="submit" value="下一步"/>
			</section>
		</form>
		
	</section>
</body>

<script src="http://apps.bdimg.com/libs/zepto/1.1.4/zepto.min.js"></script>
<script>window.Zepto || document.write('<script src="/data/lib/zepto.min.js"><\/script>')</script>
<!-- <script src="/data/lib/zepto.min.js"></script> -->
<script src="/data/js/m.min.js"></script>
<!-- <script src="/data/js/encrypted.js"></script> -->
<script src="/data/js/RSA.js"></script>
<script>
	Zepto(function($) {
		M.checkBoxInit();
        $('.btn').on('click', function () {
            M.checkForm(function () {
            	var encrypt = new JSEncrypt();
				encrypt.setPublicKey($('#lpasswd').attr('data-key'));
                var encrypted = encrypt.encrypt($('#lpasswd').val()+$('#lpasswd').attr('data-code')+$('#tpasswd').val());
				$('#lpasswd').val(encrypted);
				$('#tpasswd').val('');
                $('#login_form').attr('onsubmit','return true');

            });
        });
	});
</script>

</html>