<!DOCTYPE html>
<html>

<head lang="en">
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
	<meta name="format-detection" content="telephone=no" />
	<link href="/data/jijin/css/mobile.css" media="screen" rel="stylesheet" type="text/css">
	<title><?php echo $pag_title?></title>	
</head>

<body>
	<section class="wrap">
		<section class="m-item-wrap m-item-5-wrap">
		    <div class="m-item-5">
		        <h3 class="text-center" ><?php echo $pag_title?></h3>
		    </div>
		</section>
		<form  name="form" method="post" action="/jijin/Fund_bank/bgMsgSend" id="login_form">
			<section class="m-item-wrap">
		  		<?php 
		  			if ($operation =='bankcard_add' && !empty($payment_channel))
		  			{	
		  				$index = key($payment_channel);
		  				echo '<div class="m-item"> 
							  	  <i class="icon icon-phone"></i> 
								  <label class="select-label" style="width:80%;">请选择支付渠道
									  <select id="pay_way" name="channelid" class="select-certificate">
										  <option value='.$payment_channel[$index]['channelid']." data-cname=".$payment_channel[$index]['channelname'].' '. (isset($val['needProvCity']) ? 'data-needProvCity="1"':'').">".$payment_channel[$index]['channelname'].'</option>
									  </select>
								  </label>
						  	  </div>';
		  			}
// 		  			else
// 		  			{
// 		  				echo '<input name="channelid" type="hidden" value='.$payment_channel[0]['channelid'].'></input>';
// 		  			}
		  		?>
		  		<input type="hidden" id="channelname" name="channelname"></input>
		  		<input type="hidden" name="operation"  value=<?php echo $operation?>></input>
				<div class="m-item" style="margin-top:-1px;">
					<i class="icon icon-phone"></i>
					<label>
						<input type="text" id="bankcard_no" name="depositacct"  class="w80-p" data-key="<?php echo $public_key;?>"  data-code="<?php echo $rand_code;?>" placeholder="请输入银行卡号"/>
					</label>
				</div>
				<div class="m-item" id="chooseCity" style="display: none;">
		  			<i class="icon icon-phone"></i>
		  			<label class="select-label" style="width: 80%;">请选择支付行<br>
		  				<select id="payProv" name="depositprov" class="select-certificate" style="margin-top: 10px;" onchange="show(this.options[this.options.selectedIndex])">
		  					<option value="1">请选择省份</option>
		  				</select>
	  				</label><br>
	  				<label>
		  				<select id="payCity" name="depositcity" class="select-certificate" style="margin: 10px 0 10px 40px;" onchange="getBankAdd(this.options[this.options.selectedIndex])">
		  					<option value="1">请选择城市</option>
		  				</select>
	  				</label><br>
	  				<label>
		  				<select id="payBankAdd" name="bankname" class="select-certificate" style="margin-left: 40px;">
		  					<option value="1">请选择银行地址</option>
		  				</select>
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
				encrypt.setPublicKey($('#bankcard_no').attr('data-key'));
//                 var encrypted = encrypt.encrypt($('#ID_no').val()+$('#ID_no').attr('data-code')+$('#bankcard_no').val());
                var encrypted = encrypt.encrypt($('#bankcard_no').val()+$('#bankcard_no').attr('data-code'));
// 				$('#ID_no').val(encrypted);
				$('#bankcard_no').val(encrypted);
// alert(encrypted);
// 				var channelName = document.getElementById('pay_way').querySelector('option').innerHTML;
				$('#channelname').val($('#pay_way').find('option').attr('data-cname'));
// alert($('#channelname').val());
// console.log($('#pay_way').find('option').attr('data-cname'));
// 				var inHidden = document.getElementById('channelname');
// alert(inHidden);
// 				inHidden.value = channelName;
                $('#login_form').submit();
            });
        });
	});

	<?php if(isset($provCity)){
		echo 'var provCity = '.$provCity;
	}
	?>;
	var cer_select = document.getElementById('ID');
	var cer_div = document.getElementById('certificateno'),
		pay = document.getElementById('pay_way'),
		payList = document.getElementById('pay-list');

	selectLightbox(cer_select,cer_div);
	selectLightbox(pay,payList);
	function selectLightbox (aa,bb) {		
// 		aa.addEventListener('click',function () {
	    $(aa).on('click',function () {
			this.disabled = "true";
			M.createLightBox();			
			var list = bb.getElementsByTagName('li');
			bb.style.display = 'block';
			$(bb).on('click','li',function () {
// console.log(this.dataset.cname);
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
