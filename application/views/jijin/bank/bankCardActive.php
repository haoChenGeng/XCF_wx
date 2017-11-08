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
		<form  name="form" method="post" action="/jijin/Fund_bank/bankCardActive" id="login_form">      
			<section class="m-item-wrap" style="margin-top:0;border-top:0;">
				<?php 
					if(!isset($_SESSION['register_data']['verificationCode'])){
						echo '<div class="m-item">
								  <i class="icon icon-phone"></i>
								  <label>
						  			  <input type="text" name="verificationCode"  class="input" style="padding-left:10px;" placeholder="请输入短信验证码(必填)"/>
									  <a href="#" id="sendSms" class="input_btn">获取验证码</a>
								  </label>
							  </div>';
					}
				?>
			</section>
			<section class="m-btn-wrap">
				<input class="btn" type="submit" value="下一步"/>
			</section>
		</form>
		<section class="copy-right">
			<p>小牛新财富版权所有 © 如有任何问题请联系客服4006695666</p>
		</section>
	</section>
</body>

<script src="http://apps.bdimg.com/libs/zepto/1.1.4/zepto.min.js"></script>
<script>window.Zepto || document.write('<script src="/data/lib/zepto.min.js"><\/script>')</script>
<script src="/data/jijin/js/m.min.js"></script>

<script>
	window.onload = function (){smsDisplay();};
	
    $('#sendSms').on('click',function(){
    	$.post("/jijin/Fund_bank/sendSms", {operation:'bankcard_active'},function(res){
            M.alert({
                title:'提示',
                message:res==null||res==''||res==undefined?'发送失败':res
            });
            if( res == '验证码已发送！'){
            	smsDisplay();
            }
        })
    });

    function smsDisplay(){
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
</script>

</html>