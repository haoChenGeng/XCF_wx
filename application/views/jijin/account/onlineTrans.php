<!DOCTYPE html>
<html>

<head lang="en">
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
	<meta name="format-detection" content="telephone=no" />
	<link href="/data/jijin/css/mobile.css" media="screen" rel="stylesheet" type="text/css">
	<title>开户</title>	
</head>

<body>
	<section class="wrap">
		<section class="m-item-wrap m-item-5-wrap">
		    <div class="m-item-5">
		        <h3 class="text-center" >开通手机交易</h3>
		    </div>
		</section>
		<form  name="form" method="post" action="/jijin/Jz_account/onlineTrans" id="login_form">
			<section class="m-item-wrap" style="margin-top:0;">
				<div class="m-item">
					<i class="icon icon-phone"></i>
					<label class="select-label" style="width:90%;">请选择证件类型
						<select id="ID" name="certificatetype" class="select-certificate" >
							<option value="" disabled selected style="display: none;">请选择证件类型</option>
							<?php
								foreach ($certificatetype as $key => $val)
								{
									echo '<option value='.$key.'>'.$val.'</option>';
								}
							?>
						</select>
					</label>
				</div>
				<div class="m-item">
					<i class="icon icon-phone"></i>
					<label>
						<input type="text" id="ID_no" name="certificateno"  class="w260" data-key="<?php echo $public_key;?>"  data-code="<?php echo $rand_code;?>" placeholder="请输入开户使用的证件号码"/>
					</label>
				</div>
			</section>
			<section>
				<label for="tradeFile" class="trade-file"><input id="tradeFile" type="checkbox"> 我已阅读并同意<a href="/data/jijin/file/基金电子交易远程服务协议.pdf">《基金电子交易远程协议》</a><a href="/data/jijin/file/委托支付协议.pdf">《委托支付协议》</a><a href="/data/jijin/file/投资者须知.pdf">《投资者须知》</a></label>
			</section>
			<section class="m-btn-wrap">
				<input class="btn" id="openAccount" type="button" value="下一步"/>
			</section>
		</form>
		<section class="copy-right">
			<p>小牛新财富版权所有 © 如有任何问题请联系客服4006695666</p>
		</section>
	</section>
</body>

<script src="/data/lib/zepto.min.js"></script>
<script src="/data/jijin/js/m.min.js"></script>
<!-- <script src="/data/js/md5.js"></script> -->
<!-- <script src="/data/js/common.js"></script> -->
<script src="/data/js/RSA.min.js"></script>

<script>
	Zepto(function($) {
		M.checkBoxInit();
        $('.btn').on('click', function () {
        	if(!validate()) return;
            M.checkForm(function () {
            	var encrypt = new JSEncrypt();
				encrypt.setPublicKey($('#ID_no').attr('data-key'));
                var encrypted = encrypt.encrypt($('#ID_no').val()+$('#ID_no').attr('data-code'));
				$('#ID_no').val(encrypted);
				$('#login_form').submit();
            });
        });
	});

	function validate() {
		var res = true;
		if (!document.getElementById('tradeFile').checked) {
            M.alert({
                title:'提示',
                message:'请先阅读并同意相关协议'
            });
			res = false;
		}else {
			res = true;
		}
		return res;
	}
</script>
</html>
