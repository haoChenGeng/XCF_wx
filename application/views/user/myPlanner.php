<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<link rel="Shortcut Icon" href="/favicon.ico?v1" type="image/x-icon" />

<meta name="Keywords" content="小牛新财富" />
<meta name="Description" content="小牛新财富" />
<meta name="robots" content="index,follow,noodp,noydir" />
<meta name="viewport" content="width=640px, maximum-scale=1.0, user-scalable=no, target-densitydpi=320" />
<title>注册</title>

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
<form  method="post" action="/user/myPlanner" id="info_form" onsubmit="return false">
<section class="content ret_password wrap">
 	<ul class="con_password" style="margin-top: 80px;">
      	<li style="text-align: center;">
      		<?php echo $plannerInfo?>
    	</li>
        <li style="padding-left: 30px;">
        	<span class="names">理财师工号</span>
            <input type="text"  class="input" style="padding-left: 25px;height: 81px;" id="planner_id" name="planner_id" data-reg="^(|xn[0-9]{6})$" data-error="理财师工号错误" placeholder="<?php echo $inputInfo;?>"/>
            <a href="#" id="queryPlanner" style="line-height: 81px;" class="input_btn">理财师信息</a>
        </li>
    </ul>
<!--     <a href="#" class="ret_paw_btn">&nbsp;</a> -->
    <input class="ret_paw_btn btn" id = "submit_button" type="submit" style="border: none;" value="<?php echo $buttonInfo?>"/>
</section>
</form>
</body>
<script src="/data/js/zepto.min.js"></script>
<script src="/data/js/m.min.js"></script>
<script src="/data/js/common.js"></script>
<script>
    Zepto(function($){
        M.checkBoxInit();
        $('#queryPlanner').on('click',function(){
            var str= $('#planner_id').val();
            var regstr = "^xn[0-9]{6}$";
            var reg = new RegExp(regstr,'g');
            if(reg!=null&& !reg.test( str )){
                M.alert({
                    title:'提示',
                    message:$('#planner_id').attr('data-error')
                });
            }else{
                $.post("/User/queryPlanner", {planner_id:str},function(res){
                    M.alert({
                        title:'提示',
                        message:res==null||res==''||res==undefined?'理财师查询失败':res
                    });
                })
            }
        });
        $('#submit_button').on('click',function(){
        	$('#info_form').attr('onsubmit','return true');
        });
    });
</script>        

</html>
