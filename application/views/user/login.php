<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<link href="/data/css/mobile.css" media="screen" rel="stylesheet" type="text/css">
<meta name="Keywords" content="小牛新财富" />
<meta name="Description" content="小牛新财富" />
<meta name="robots" content="index,follow,noodp,noydir" />
<meta name="viewport" content="width=640px, maximum-scale=1.0, user-scalable=no, target-densitydpi=320" />
<title>小牛新财富</title>
<link rel="stylesheet" href="/data/css/style.css" />
</head>

<body>
    <section class="engin wrap">
        <form  name="form" method="post" action="/user/login/<?php echo isset($type)?$type:1;?>" id="login_form" onsubmit="return false">
            <a href="<?php echo $this->base;?>"><img src="/data/img/logo-new.png" alt="小牛新财富logo" class="logo dib"></a>
        	<article class="content01">
            	<label class="engin_label">
                	<input type="text" id="account" name="T_name" class="name_input" placeholder="手机号码"/>
                </label>
                <label class="engin_label engin_label2">
                	<input type="password" id="pass" class="name_input" name="T_pwd"  data-reg=".+"  data-error="密码不能为空" data-key="<?php echo $public_key;?>"  data-code="<?php echo $rand_code;?>" placeholder="密码"/>
                </label>
                <label class="engin_label engin_label3">
                	<input class="name_input name_input2" id = "login" type="submit" value="登 录"/>
                </label>
                <label class="engin_label engin_label3">
                    <a href="/user/register/1" class="name_input name_input2"/>注 册</a>
                </label>
            </article>
        </form>
             <!-- <a href="/user/register/1" class="en_name">注册新的用户</a> -->
             <a href="/user/findPass/1" class="en_name">忘记密码 ?</a>
    </section>
</body>
<script src="/data/js/zepto.min.js"></script>
<script src="/data/js/m.min.js"></script>
<script src="/data/js/RSA.min.js"></script>
<script>
    Zepto(function($) {
        M.checkBoxInit();
        $('#login').on('click', function () {
            M.checkForm(function () {
            	var encrypt = new JSEncrypt();
				encrypt.setPublicKey($('#pass').attr('data-key'));
                var encrypted = encrypt.encrypt($('#account').val()+$('#pass').attr('data-code')+$('#pass').val());
                $('#account').val('');
				$('#pass').val(encrypted);
                $('#login_form').attr('onsubmit','return true');
            });
        });
    });
</script>        
</html>
