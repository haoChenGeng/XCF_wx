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
								  <label class="select-label" style="width:80%;">请选择银行
									  <select id="pay_way" name="channelid" class="select-certificate" onchange="chooseChannel(this.options[this.options.selectedIndex])">
									  		<option disabled selected style="display: none;">请选择银行</option>';
                      foreach ($payment_channel as $key => $val)
                      {
                        echo '<option value='.$val['channelid'].' data-cname='.$val['channelname'].' '. (isset($val['needProvCity']) ? 'data-needProvCity="1"':'').'>'.$val['channelname'].'</option>';
                      }
                    echo '</select>
								  </label>
						  	  </div>';
		  			}else{
		  				echo '<input name="channelname" id="channelname" type="hidden" value='.$channelname.'></input>';
		  			}
				?>
		  		<!-- <input type="hidden"  name="channelname"> -->
		  		<input type="hidden" name="operation" id="bankChange" value=<?php echo $operation?>>
				<div class="m-item" style="margin-top:-1px;">
					<i class="icon icon-phone"></i>
					<label>
						<input type="text" id="bankcard_no" name="depositacct"  class="w80-p" data-key="<?php echo $public_key;?>"  data-code="<?php echo $rand_code;?>" <?php if(isset($depositacct)) echo 'value='.$depositacct.' readonly=true';?> placeholder="请输入银行卡号"/>
					</label>
				</div>
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
				<div class="m-item">
					<i class="icon icon-phone"></i>
					<label>
						<input type="text" name="mobiletelno"  class="w80-p" data-reg="^[1][34578][0-9]{9}$" data-error="手机号错误" placeholder="请输入银行预留电话"/>
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
        	if(!validate()) return;
            M.checkForm(function () {
            	var encrypt = new JSEncrypt();
				encrypt.setPublicKey($('#bankcard_no').attr('data-key'));
                var encrypted = encrypt.encrypt($('#bankcard_no').val()+$('#bankcard_no').attr('data-code'));
				$('#bankcard_no').val(encrypted);
				$('#channelname').val($('#pay_way').find('option').attr('data-cname'));
                $('#login_form').submit();
            });
        });
	});

	function validate() {
		var res = true;
		var inputList = $('input[type=text]');
		for(var i = 0, length1 = inputList.length; i < length1; i++){
			if(!inputList[i].value.toString().trim()) {
			  alert(inputList[i].getAttribute('placeholder'));
			  res = false;
			  return;
			}
		}
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

	<?php if(isset($provCity)){
		echo 'var provCity = '.$provCity;
	}
	?>;

    var bankChange = document.getElementById('bankChange').getAttribute('value');
    if (bankChange === 'bankcard_change' && typeof provCity !== "undefined" ) {
      document.getElementById('chooseCity').style.display = 'block';
      createBank('payProv');
    }



    function createBank(id) {
      var listOp = document.createDocumentFragment();
      for (var i in provCity) {
        var options = document.createElement('option');
        options.innerHTML = i;
        options.setAttribute('value', i);
        options.setAttribute('data-city', provCity[i]);
        listOp.appendChild(options);
      }
      document.getElementById(id).appendChild(listOp);
    };

    function getBankAdd(s) {
      var bankNameSel = document.getElementById('channelname');
      // var bankName;
      if (bankNameSel) {
        var bankName = bankNameSel.getAttribute('value');        
      }
      var bankNameSelAdd = document.getElementById('pay_way');
      if (bankNameSelAdd) {
        var bankNameAdd = bankNameSelAdd.options[bankNameSelAdd.options.selectedIndex];        
      }
      $.ajax({
        type: 'post',
        url: "<?php echo $this->base.'/jijin/Jz_account/openBank'?>",
        data: { 
          channelname: bankName || bankNameAdd.innerHTML,
          paracity: s.value
        },
        dataType: 'json',
        success: function(res) {
          if (res.code === '0000') {
//   console.log(res);
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
// console.log(s.getAttribute("data-needProvCity"));
// console.log(s.attributes["data-needProvCity"].nodeValue);
      if (s.getAttribute("data-needProvCity")) {
        document.getElementById('chooseCity').style.display = 'block';
        createBank('payProv');
      }else {
        document.getElementById('chooseCity').style.display = 'none';
      }
      chosenBank = s.innerHTML;
    };
  
</script>

</html>
