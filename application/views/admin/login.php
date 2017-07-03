<!DOCTYPE html>
<html dir="ltr" lang="cn">
<head>
<meta charset="UTF-8" />
<title>新财富公募基金平台</title>
<base href="<?php echo $base;?>" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
<script type="text/javascript" src="/data/javascript/jquery/jquery-2.1.1.min.js"></script>
<link href="/data/stylesheet/bootstrap.css" type="text/css" rel="stylesheet" />
<link href="/data/javascript/font-awesome/css/font-awesome.min.css" type="text/css" rel="stylesheet" />
<link type="text/css" href="/data/stylesheet/stylesheet.css" rel="stylesheet" media="screen" />
</head>
<body>
<div id="container">
<header id="header" class="navbar navbar-static-top">
  <div class="navbar-header">
        <a href="<?php echo $base;?>" class="navbar-brand"><img src="/data/image/logo.png" alt="系统管理" title="系统管理" /></a></div>
  </header>
<div id="content">
  <div class="container-fluid"><br />
    <br />
    <?php if (isset($success)) { ?>
		<div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo $success; ?>
			<button type="button" class="close" data-dismiss="alert">&times;</button>
		</div>
	<?php } ?>
    <div class="row">
      <div class="col-sm-offset-4 col-sm-4">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h1 class="panel-title"><i class="fa fa-lock"></i>请输入登录信息。</h1>
          </div>
          <div class="panel-body">
            <?php if (isset($error_warning)) { ?>
            <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
              <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
            <?php } ?>
            <form action="<?php echo $base.'/admin/account/login';?>" id="login_form" method="post" enctype="multipart/form-data">
              <div class="form-group">
                <label for="input-username">用户名</label>
                <div class="input-group"><span class="input-group-addon"><i class="fa fa-user"></i></span>
                  <input type="text" name="username"  placeholder="用户名" id="input-username" class="form-control" />
                </div>
              </div>
              <div class="form-group">
                <label for="input-password">密码</label>
                <div class="input-group"><span class="input-group-addon"><i class="fa fa-lock"></i></span>
                  <input type="password" name="password"  placeholder="密码" id="input-password" class="form-control" />
                  <input type="hidden" id="RSA" data-key="<?php echo $public_key;?>" data-code="<?php echo $rand_code;?>" />
                </div>
                <span class="help-block"><a href="user/user/forgotten">忘记密码?</a></span>
              </div>
              <div class="text-right">
                <button type="submit" class="btn btn-primary"><i class="fa fa-key"></i>登录</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="/data/javascript/RSA.min.js"></script>
<script>
        $('.btn').on('click', function () {
        	var encrypt = new JSEncrypt();
			encrypt.setPublicKey($('#RSA').attr('data-key'));
            var encrypted = encrypt.encrypt($('#input-password').val()+$('#RSA').attr('data-code'));
			$('#input-password').val(encrypted);
            $('#login_form').submit();
        });

        $('#login_form').on('keydown',function(e) {
        	//IE浏览器
        	if(CheckBrowserIsIE()){
        	 	keycode = e.keyCode;
        	}else{
        	//火狐浏览器
        	keycode = e.which;
        	}
        	if (keycode == 13 ) //回车键是13
        	{
        	    $('#login_form').submit();
        	}
        });
        	//判断访问者的浏览器是否是IE
        	function CheckBrowserIsIE(){
        	 	var result = false;
        	 	var browser = navigator.appName;
        	 	if(browser == "Microsoft Internet Explorer"){
        	  		result = true;
        	 	}
        	 	return result;
        	}

</script>

