<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<link rel="Shortcut Icon" href="/favicon.ico?v1" type="image/x-icon" />

<meta name="Keywords" content="小牛新财富" />
<meta name="Description" content="小牛新财富" />
<meta name="robots" content="index,follow,noodp,noydir" />
<meta name="viewport" content="width=640px, maximum-scale=1.0, user-scalable=no, target-densitydpi=320" />
<title>修改登录密码</title>

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
<header class="head">
  <div class="head-back">
    <span class="head-back-icon" onclick="window.history.go(-1)">返回</span>
  </div>
</header>
<form  method="post" action="/User/updatePass" id="info_form">
<section class="content ret_password xglogin wrap">
 	<ul class="con_password my_per">
    	<li class="li01">
        	<span class="names"></span>
            <input type="password" class="input" name="oldPass" id="oldPass" data-reg=".+"  data-error="原密码不能为空" placeholder="请输入旧密码">
        </li>
    </ul>
    <ul class="my_per con_password" style="margin-top: 10px;">
    	<li class="li02">
        	<span class="names"></span>
            <input type="password" class="input" id="newPass" name="newPass" data-reg="^.{6,20}$" data-error="密码不符合要求" data-key="<?php echo $public_key;?>"  data-code="<?php echo $rand_code;?>" placeholder="请输入6-20位新密码">
        </li>
        <li class="li02">
<!--         	<span class="names"></span> -->
              <input type="password" class="input" name="reNewPass"  id="reNewPass"  data-reg="^.{6,20}$"  data-error="密码不符合要求" placeholder="请再次输入新密码"/>
        </li>
    </ul>
    
    <a href="#" id = "submit_button" class="xglogin_btn">提交</a>
<!--     <input class="ret_paw_btn ret_paw_btn02 btn" id = "submit_button" type="submit" style="border: none;" value=""/>--> 
    
</section>
</form>
</body>

<script src="/data/js/zepto.min.js"></script>
<script src="/data/js/m.min.js"></script>
<script src="/data/js/RSA.min.js"></script>
<script>
    Zepto(function(){
        M.checkBoxInit();

        $('#submit_button').on('click', function (event) {
            event.preventDefault();
            M.checkForm(function () {
                var pass1 = $('#newPass').val();
                var pass2 = $('#reNewPass').val();
                if (pass1 === pass2)
                {
                	var encrypt = new JSEncrypt();
    				encrypt.setPublicKey($('#newPass').attr('data-key'));
                    var encrypted = encrypt.encrypt($('#oldPass').val() + $('#newPass').attr('data-code') + $('#newPass').val());
    				$('#newPass').val(encrypted);
    				$('#reNewPass').val('');
    				$('#oldPass').val('');
// alert($('#newPass').attr('data-code'));
// alert(encrypted); 				
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
    })
</script>
</html>
