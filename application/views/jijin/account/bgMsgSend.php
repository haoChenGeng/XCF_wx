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
							<!-- <option value="0"><?php echo $certificatetype[0];?></option> -->
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
						<input type="text" id="ID_no" name="certificateno"  class="w80-p" data-key="<?php echo $public_key;?>"  data-code="<?php echo $rand_code;?>" placeholder="请输入证件号码"/>
					</label>
				</div>
		  <?php if (count($payment_channel)>1){
					echo '<div class="m-item"> 
							  <i class="icon icon-phone"></i> 
								  <label class="select-label" style="width:80%;">请选择银行
									  <select id="pay_way" name="channelid" class="select-certificate" onchange="chooseChannel(this.options[this.options.selectedIndex])">
									  	<option value="" disabled selected style="display: none;">请选择银行</option>';
										  foreach ($payment_channel as $key => $val)
					  					{
					  						echo '<option value='.$val['channelid'].' data-cname='.$val['channelname'].' '. (isset($val['needProvCity']) ? 'data-needProvCity="1"':'').'>'.$val['channelname'].'</option>';
					  					}
									  echo '</select>
								  </label>
						  </div>';
		  		}
		  		else
		  		{
		  			echo '<input name="channelid" type="hidden" value='.$payment_channel[0]['channelid'].'></input>';
		  		}
		  ?>
		  <div class="m-item" id="chooseCity" style="display: none;">
		  	<i class="icon icon-phone"></i>
		  	<label class="select-label" style="width: 80%;">请选择支付行<br>
		  		<select id="payProv" name="depositprov" class="select-certificate" style="margin-top: 10px;" onchange="show(this.options[this.options.selectedIndex])">
		  			<option value="" disabled selected style="display: none;">请选择省份</option>
		  		</select>
	  		</label><br>
	  		<label>
		  		<select id="payCity" name="depositcity" class="select-certificate" style="margin: 10px 0 10px 40px;" onchange="getBankAdd(this.options[this.options.selectedIndex])">
		  			<option value="" disabled selected style="display: none;">请选择城市</option>
		  		</select>
	  		</label><br>
	  		<label>
		  		<select id="payBankAdd" name="bankname" class="select-certificate" style="margin-left: 40px;">
		  			<option value="" disabled selected style="display: none;">请选择银行地址</option>
		  		</select>
		  	</label>
		  </div>
		  <input type="hidden" id="channelname" name="channelname">
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
			<section>
				<label for="tradeFile" class="trade-file"><input id="tradeFile" type="checkbox"> 我已阅读并同意<a href="/data/jijin/file/基金电子交易远程服务协议.pdf">《基金电子交易远程协议》</a><a href="/data/jijin/file/委托支付协议.pdf">《委托支付协议》</a><a href="/data/jijin/file/投资者须知.pdf">《投资者须知》</a></label>
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
		<div class="light-content" id="pay-list" style="display: none;">
			<ul>
				<?php
					foreach ($payment_channel as $key => $val)
					{
						echo '<li value='.$val['channelid'].' data-cname='.$val['channelname'].' '. (isset($val['needProvCity']) ? 'data-needProvCity="1"':'').'>'.$val['channelname'].'</li>';
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



	var provCity = <?php echo isset($provCity) ? $provCity : ''?>;
	var listOp = document.createDocumentFragment();
	for (var i in provCity) {
		var options = document.createElement('option');
		options.innerHTML = i;
		options.setAttribute('value', i);
		options.setAttribute('data-city', provCity[i]);
		listOp.appendChild(options);
	}
	document.getElementById('payProv').appendChild(listOp);

	function show(s) {
		var arr = s.dataset.city.split(',');
		var childOp = document.getElementById('payCity');
		childOp.innerHTML = '<option disabled selected style="display: none;">请选择城市</option>';
		var opList = document.createDocumentFragment();
		for (var i = 0; i < arr.length; i++) {
			var op = document.createElement('option');
			op.innerHTML = arr[i];
			op.setAttribute('value', arr[i]);
			opList.appendChild(op);
		}
		childOp.appendChild(opList);
	}

	var chooseChannel = function(s) {
// console.log(s);
		if (s.getAttribute("data-needProvCity")) {
			document.getElementById('chooseCity').style.display = 'block';
		}else {
			document.getElementById('chooseCity').style.display = 'none';
		}
		chosenBank = s.innerHTML;
	};

	function getBankAdd(s) {
		var bankNameSel = document.getElementById('pay_way');
		var bankName = bankNameSel.options[bankNameSel.options.selectedIndex];
		$.ajax({
			type: 'post',
			url: "<?php echo $this->base.'/jijin/Jz_account/openBank'?>",
			data: { 
				channelname: bankName.innerHTML,
				paracity: s.value
			},
			dataType: 'json',
			success: function(res) {
				if (res.code === '0000') {
				var childOp = document.getElementById('payBankAdd');
				childOp.innerHTML = '<option disabled selected style="display: none;">请选择银行地址</option>';
				var opList = document.createDocumentFragment();
				for (var i = 0; i < res.data.length; i++) {
					var op = document.createElement('option');
					op.innerHTML = res.data[i].paravalue;
					op.setAttribute('value', res.data[i].paravalue);
					opList.appendChild(op);
				}
				childOp.appendChild(opList);
				}else {
					alert(res.msg);
				}
			},
			error: function(res) {
				alert('请求错误');
			}
		});
	}

	
</script>
</html>
