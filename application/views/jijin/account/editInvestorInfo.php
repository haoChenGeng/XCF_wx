<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
    <meta name="format-detection" content="telephone=no" />
    <meta name="keywords" content="小牛资本">
    <meta name="description" content="小牛资本管理集团公募基金代销系统">
    <link href="/data/jijin/css/mobile.css" media="screen" rel="stylesheet" type="text/css">
    <title>投资者信息</title>
</head>
<body>
<section class="wrap">
    <section class="m-item-wrap m-item-5-wrap">
        <div class="m-item-5 text-align:center">
            <h3 class="text-center"><?php echo (isset($infoMessage)) ? $infoMessage : '投资者信息';?></h3>
        </div>
    </section>
    <section class="m2-item-wrap">
        <form  method="post" action="/jijin/Jz_my/investorManagement" id="info_form">
        	<?php
        		foreach ($formData as $key=>$val){
        			echo "<div class='m2-item'>
    						<label class='select-label' style='width:90%;'>".$val['des']."\r\n";
        			if (isset($val['select'])){
        				echo '<select id='.$key.' name='.$key.' class="select-certificate investorInfo" >';
        				foreach ($val['select'] as $k=>$v){
        					echo '<option value="'.$k.'"';
        					if (isset($val['value']) && $val['value'] == $k){
        						echo ' selected="selected"';
        					} 
        					echo '>'.$v.'</option>';
        				}
        				echo "</select>";
        			}else{
        				echo '<input id="'.$key.'" class="w80-p" name="'.$key. '"/>';
        			}
        			echo "\r\n</label>\r\n</div>\r\n";
        		}
        	?>
            <section class="m-btn-wrap mt10 clearfix">
                <input class="btn btn-fix-left" id="backBtn" type="button" value="返回"/>
                <input class="btn btn-fix-right" id="commit" type="button" value="提交"/>
            </section>
        </form>
    </section>
</section>
</body>
<script src="/data/lib/zepto.min.js"></script>
<script src="/data/jijin/js/m.min.js"></script>
<script src="/data/jijin/js/common.js"></script>
<script>
    Zepto(function(){
        M.checkBoxInit();
        $('#commit').on('click',function () {
        	M.checkForm(function () {
                $('#info_form').submit();
            });
        });
        
        $('#backBtn').on('click',function(){
            window.location.href='/jijin/Jz_my';
        });
    });
</script>
</html>