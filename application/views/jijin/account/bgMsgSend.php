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
		        <h3 class="text-center" >开户</h3>
		    </div>
		</section>
		<form  name="form" method="post" action="/jijin/Jz_account/bgMsgSend" id="login_form">
			<section class="m-item-wrap" style="margin-top:0;">
				<div class="m-item">
					<i class="icon icon-phone"></i>
					<label class="select-label" style="width:90%;">请选择证件类型
						<select id="ID" name="certificatetype" class="select-certificate" >
							<option value="0"><?php echo $certificatetype[0];?></option>							
						</select>
					</label>
				</div>
				<div class="m-item">
					<i class="icon icon-phone"></i>
					<label>
						<input type="text" id="ID_no" name="certificateno"  class="w80-p" data-key="<?php echo $public_key;?>"  data-code="<?php echo $rand_code;?>" placeholder="请输入证件号码"/>
					</label>
				</div>
		  <?php if (count($payment_channel)>1)
					echo '<div class="m-item"> 
							  <i class="icon icon-phone"></i> 
								  <label class="select-label" style="width:80%;">请选择支付渠道
									  <select id="pay_way" name="channelid" class="select-certificate">
										  <option value='.$payment_channel[0]['channelid']." data-cname=".$payment_channel[0]['channelname'].">".$payment_channel[0]['channelname'].'</option>
									  </select>
								  </label>
						  </div>';
		  		else
		  			echo '<input name="channelid" type="hidden" value='.$payment_channel[0]['channelid'].'></input>';
		  ?>
		  <input type="hidden" id="channelname" name="channelname"></input>
				<div class="m-item">
					<i class="icon icon-phone"></i>
					<label>
						<input type="text" name="depositacctname"  class="w80-p" placeholder="请输入银行卡户名"/>
					</label>
				</div>
				<div class="m-item">
					<i class="icon icon-phone"></i>
					<label>
						<input type="text" id="bankcard_no" name="depositacct"  class="w80-p" placeholder="请输入银行卡号"/>
					</label>
				</div>
				<div class="m-item">
					<i class="icon icon-phone"></i>
					<label>
						<input type="text" name="mobiletelno"  class="w80-p" placeholder="请输入银行预留电话"/>
					</label>
				</div>

			</section>
			<section class="m-btn-wrap">
				<input class="btn" type="button" value="下一步"/>
			</section>
		</form>
		<div class="light-content" id="certificateno" style="display:none;">
			<ul>
				<?php
					foreach ($certificatetype as $key => $val)
					{
						echo '<li value='.$key.'>'.$val.'</li>';
					}
				?>
			</ul>
		</div>
		<div class="light-content" id="pay-list" style="display:none;">
			<ul>
				<?php
					foreach ($payment_channel as $key => $val)
					{
						echo '<li value='.$val['channelid'].' data-cname='.$val['channelname']. '>'.$val['channelname'].'</li>';
					}
				?>
			</ul>
		</div>
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
            M.checkForm(function () {
            	var encrypt = new JSEncrypt();
				encrypt.setPublicKey($('#ID_no').attr('data-key'));
                var encrypted = encrypt.encrypt($('#ID_no').val()+$('#ID_no').attr('data-code')+$('#bankcard_no').val());
				$('#ID_no').val(encrypted);
				$('#bankcard_no').val('');
				$('#channelname').val($('#pay_way').find('option').attr('data-cname'));
                $('#login_form').submit();
            });
        });
	});
	
	var cer_select = document.getElementById('ID');
	var cer_div = document.getElementById('certificateno'),
		pay = document.getElementById('pay_way'),
		payList = document.getElementById('pay-list');

	selectLightbox(cer_select,cer_div);
	selectLightbox(pay,payList);
	function selectLightbox (aa,bb) {		
	    $(aa).on('click',function () {
			this.disabled = "true";
			M.createLightBox();			
			var list = bb.getElementsByTagName('li');
			bb.style.display = 'block';
			$(bb).on('click','li',function () {
				var cer_op = document.createElement('option');
				var op_value = this.attributes[0].value;
				cer_op.innerHTML =this.innerHTML;
				cer_op.setAttribute('value',op_value);
				cer_op.setAttribute('data-cname',this.dataset.cname);
				aa.replaceChild(cer_op,aa.childNodes[1]);
				bb.style.display = 'none';
				M.hideLightBox();
				aa.disabled = false;
			});		
		});
	}
</script>

</html>
