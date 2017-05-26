<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<link rel="Shortcut Icon" href="/favicon.ico?v1" type="image/x-icon" />

<meta name="Keywords" content="小牛新财富" />
<meta name="Description" content="小牛新财富" />
<meta name="robots" content="index,follow,noodp,noydir" />
<meta name="viewport" content="width=640px, maximum-scale=1.0, user-scalable=no, target-densitydpi=320" />
<title>找回密码</title>

<link rel="stylesheet" href="/data/css/style.css" />
<style type="text/css">
	.pop-box {
    position: fixed;
    width: 70%;
    max-width: 300px;
    top: 30%;
    left: 27%;
    z-index: 90;
    opacity: 0;
    border-radius: 5px;
    -webkit-animation: fadeIn2 .5s;
    animation: fadeIn2 .5s;
    -webkit-animation-fill-mode: forwards;
    animation-fill-mode: forwards;
    background-color: #fff;
    overflow: hidden;
    }
    
    .pop-box > .pop-title {
    padding: 10px 5px;
    text-align: center;
    background-color: #c9c9c9;
    color: #000;
	}
	.pop-box > .pop-content {
    padding: 20px 5px;
    text-align: center;
}
.pop-box > .pop-btn {
    padding: 10px 5px;
    text-align: center;
    border-top: 1px solid #dcdcdc;
    color: #ccc;
}
.light-box {
    position: fixed;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    background-color: #000;
    opacity: 0;
    z-index: 1;
    -webkit-animation: fadeIn .5s;
    animation: fadeIn .5s;
    -webkit-animation-fill-mode: forwards;
    animation-fill-mode: forwards;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    100% {
        opacity: .6;
    }
}
@-webkit-keyframes fadeIn {
    from {
        opacity: 0;
    }
    100% {
        opacity: .6;
    }
}
@keyframes fadeOut {
    from {
        opacity: 0.6;
    }
    100% {
        opacity: 0;
    }
}
@-webkit-keyframes fadeOut {
    from {
        opacity: 0.6;
    }
    100% {
        opacity: 0;
    }
}
@keyframes fadeIn2 {
    from {
        opacity: 0;
    }
    100% {
        opacity: 1;
    }
}
@-webkit-keyframes fadeIn2 {
    from {
        opacity: 0;
    }
    100% {
        opacity: 1;
    }
}
@keyframes fadeOut2 {
    from {
        opacity: 1;
    }
    100% {
        opacity: 0;
    }
}
@-webkit-keyframes fadeOut2 {
    from {
        opacity: 1;
    }
    100% {
        opacity: 0;
    }
}
</style>
</head>

<body>
<form method="post" action="/user/findPass/1" id="info_form">
<section class="content ret_password wrap">
 	<ul class="con_password" style="margin-top: 80px;">
    	<li>
        	<span class="names">+86</span>
            <input type="text"  class="input" id="tel" name="tel" data-reg="^[1][34578][0-9]{9}$" data-error="手机号错误" placeholder="输入手机号"/>
            <a href="#" id="sendSms" class="input_btn">获取验证码</a>
        </li>
        <li>
        	<span class="names">验证码</span>
            <input type="text"  class="input" name="sms_code" data-reg="^\d{4}$" data-error="验证码错误" placeholder="请输入验证码"/>
        </li>
        <li>
        	<span class="names">新密码</span>
            <input type="password"  id="pass" data-pwd data-reg=".{6,20}$" name="pwd" data-error="密码不符合要求"  data-key="<?php echo $public_key;?>" data-code="<?php echo $rand_code;?>" placeholder="请输入6~20位新密码"class="input"/>
        </li>
        <li>
        	<span class="names">确认密码</span>
            <input type="password"  class="input" id="repass" data-repwd data-reg=".{6,20}$" name="rePwd"  data-error="密码不符合要求" placeholder="请再次输入新密码"/>
        </li>
    </ul>
    <input class="ret_paw_btn ret_paw_btn02 btn" id = "submit_button" type="submit" style="border: none;" value="提交"/>
</section>
</form>

</body>
<script src="/data/js/zepto.min.js"></script>
<script src="/data/js/m.min.js"></script>
<script src="/data/js/common.js"></script>
<script src="/data/js/RSA.min.js"></script>
<script>
    Zepto(function($){
        M.checkBoxInit();

        $('#submit_button').on('click', function (event) {
            event.preventDefault();
            M.checkForm(function () {
                var pass1 = $('#pass').val();
                var pass2 = $('#repass').val();
                if (pass1 === pass2)
                {
                	var encrypt = new JSEncrypt();
    				encrypt.setPublicKey($('#pass').attr('data-key'));
                    var encrypted = encrypt.encrypt($('#pass').val()+$('#pass').attr('data-code'));
    				$('#pass').val(encrypted);
    				$('#repass').val('');
                    $('#info_form').submit();
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
        
        $('#sendSms').on('click',function(){
            sendSms($("#tel"),$(this),1);
        });
    });
</script>        

</html>
