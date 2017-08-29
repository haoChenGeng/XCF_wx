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
            <h3 class="text-center" id="applyChange">投资者信息</h3>
        </div>
    </section>
    <section class="m2-item-wrap">
    <div class="text" id="u174">
    <div class="text" id="u176" style="padding: 0 20px;font-size: 15px;">
        <p style="font-size: 18px;">
            <span style="color: rgb(51, 51, 51); font-size: 13px;text-align: center;display: block;">根据您所提交的相关信息，您的投资者类为</span>
            <span style="color: rgb(255, 0, 0); font-size: 18px;text-align: center;display: block;">普通投资者</span>
        </p>
        <p style="font-size: 13px;"><span style="color: rgb(51, 51, 51); font-size: 13px;">&nbsp;</span></p>
        <p>根据证监会关于基金销售管理的相关规定，投资者需要分为普通投资者与专业投资者。普通投资者在信息告知、风险警示、适当性匹配等方面享有特别保护，具体如下：</p>
        <p><span>（1）不向普通投资者主动推介不符合其投资目标或者风险等级高于其风险承受能力的产品或者服务。</span></p>
        <p><span>（2）向普通投资者销售高风险产品或提供相关服务时，制定专门的工作程序，告知特别的风险点。</span></p>
        <p><span>（3）向普通投资者销售产品或者提供服务前，告知下列信息：</span><span>&nbsp;</span></p><p><span>1）可能直接导致本金亏损的事项；</span></p><p><span>2）可能直接导致超过原始本金损失的事项；</span></p><p><span>3）因公司业务或者财产状况变化，可能导致本金或者原始本金亏损的事项；</span></p><p><span>4）因公司业务或者财产状况变化，影响客户判断的重要事由；</span></p><p><span>5）限制投资者权利行使期限或者可解除合同期限等全部限制内容；</span></p>
        <p><span>（4）不向安全型普通投资者销售或提供风险等级高于其风险承受能力的产品。 普通投资者和专业投资者在一定条件下可以相互转化。</span></p>

        <form  method="post" action="/jijin/Jz_my/investorManagement" id="info_form">
        	<section class="m-btn-wrap mt10 clearfix">
        		<input type="hidden" id="investorInfo" name="investorInfo" value='<?php echo $investorInfo?>'/>
            	<input class="btn btn-fix-left" id="backBtn" type="button" value="确认"/>
            	<input class="btn btn-fix-right" id="commit" type="button" value="修改个人信息"/>
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