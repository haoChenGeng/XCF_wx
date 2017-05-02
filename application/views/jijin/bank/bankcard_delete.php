<!DOCTYPE html>
<html>

<head lang="en">
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
	<meta name="format-detection" content="telephone=no" />
	<link href="/data/css/mobile.css" media="screen" rel="stylesheet" type="text/css">
	<title>删除银行卡</title>
</head>

<body>
	<section class="wrap">
		<section class="m-item-wrap m-item-5-wrap">
		    <div class="m-item-5">
		        <h3 class="text-center" >删除银行卡</h3>
		    </div>
		</section>
		<form  name="form" method="post" action="/jijin/Fund_bank/bankcard_delete" id="login_form">      
			<section class="m-item-wrap" style="margin-top:0;border-top:0;">
				<div class="m-item">
					<i class="icon icon-phone"></i>
            		<label>
                		<input type="password" id="tpasswd" class="w80-p"  name="tpasswd"   data-reg=".+"  data-error="交易密码不能为空" data-key="<?php echo $public_key;?>"  data-code="<?php echo $rand_code;?>" placeholder="请输入交易密码" />
                		<input type="hidden" name="channelid"  value=<?php echo $channelid?>></input>
                		<input type="hidden" name="depositacct"  value=<?php echo $depositacct?>></input>
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
<script src="/data/js/RSA.js"></script>
<script>
	Zepto(function($) {
		M.checkBoxInit();
        $('.btn').on('click', function () {
            M.checkForm(function () {
            	var encrypt = new JSEncrypt();
				encrypt.setPublicKey($('#tpasswd').attr('data-key'));
                var encrypted = encrypt.encrypt($('#tpasswd').val()+$('#tpasswd').attr('data-code'));
				$('#tpasswd').val(encrypted);
                $('#login_form').submit();
            });
        });
	});
</script>

</html>