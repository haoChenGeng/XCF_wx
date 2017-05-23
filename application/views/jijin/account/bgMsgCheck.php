<!DOCTYPE html>
<html>

<head lang="en">
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
	<meta name="format-detection" content="telephone=no" />
	<link href="/data/jijin/css/mobile.css" media="screen" rel="stylesheet" type="text/css">
	<title>注册</title>
</head>

<body>
	<section class="wrap">
		<section class="m-item-wrap m-item-5-wrap">
		    <div class="m-item-5">
		        <h3 class="text-center" >开户</h3>
		    </div>
		</section>
		<form  name="form" method="post" action="/jijin/Jz_account/bgMsgCheck" id="login_form">      
			<section class="m-item-wrap" style="margin-top:0;border-top:0;">
				<?php 
					if(!isset($_SESSION['register_data']['verificationCode'])){
						echo '<div class="m-item">
								  <i class="icon icon-phone"></i>
								  <label>
						  			  <input type="text" name="verificationCode"  class="w80-p" placeholder="请输入短信验证码(必填)"/>
								  </label>
							  </div>';
					}
				?>
				<div class="m-item">
					<i class="icon icon-phone"></i>
            		<label>
                		<input type="password" id="pass1" class="w80-p"  name="lpasswd"   data-reg=".+"  data-error="交易密码不能为空" data-key="<?php echo $public_key;?>"  data-code="<?php echo $rand_code;?>" placeholder="请设置交易密码(必填)" />
            		</label>
        		</div>
				<div class="m-item">
					<i class="icon icon-phone"></i>
            		<label>
                		<input type="password" id="pass2" class="w80-p"  name="tpasswd"   data-reg=".+"  data-error="交易密码不能为空" placeholder="请重输交易密码(必填)" />
            		</label>
            	</div>
				<div class="m-item">
					<i class="icon icon-phone"></i>
					<label>
						<input type="text" name="email"  class="w80-p" placeholder="请输入电子邮箱(可选)"/>
					</label>
				</div>
				<div class="m-item">
					<i class="icon icon-phone"></i>
					<label>
						<input type="text" name="postcode"  class="w80-p" placeholder="请输入邮编(可选)"/>
					</label>
				</div>				        		
				<div class="m-item">
					<i class="icon icon-phone"></i>
					<label>
						<input type="text" name="address"  class="w80-p" placeholder="请输入地址(可选)"/>
					</label>
				</div>			

				
			</section>
			<section class="m-btn-wrap">
				<input class="btn" type="button" value="下一步"/>
			</section>
		</form>
		<section class="copy-right">
			<p>小牛新财富版权所有 © 如有任何问题请联系客服4006695666</p>
		</section>
	</section>
</body>

<script src="http://apps.bdimg.com/libs/zepto/1.1.4/zepto.min.js"></script>
<script>window.Zepto || document.write('<script src="/data/lib/zepto.min.js"><\/script>')</script>
<script src="/data/js/m.min.js"></script>
<script src="/data/js/RSA.min.js"></script>
<script>
	Zepto(function($) {
		M.checkBoxInit();
        $('.btn').on('click', function () {
            M.checkForm(function () {
            	var encrypt = new JSEncrypt();
				encrypt.setPublicKey($('#pass1').attr('data-key'));
                var encrypted = encrypt.encrypt($('#pass1').val()+$('#pass1').attr('data-code')+$('#pass2').val());
				$('#pass1').val(encrypted);
				$('#pass2').val('');
                $('#login_form').submit();
            });
        });
	});
</script>

</html>