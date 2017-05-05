<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
    <meta name="format-detection" content="telephone=no" />
    <meta name="keywords" content="小牛资本">
    <meta name="description" content="小牛资本管理集团公募基金代销系统">
    <link href="/data/jijin/css/mobile.css" media="screen" rel="stylesheet" type="text/css">
    <title><?php echo $purchasetype.'基金';?></title>
</head>
<body>
<section class="wrap">
    <section class="m-item-wrap m-item-5-wrap">
        <div class="m-item-5 text-align:center">
            <h3 class="text-center" id="applyChange"><?php echo $purchasetype;?></h3>
        </div>
    </section>
    <section class="m2-item-wrap">
        <form  method="post" action="/jijin/PurchaseController/ApplyResult" id="info_form">
            <div class="m2-item mt30">
                <div class="item-width-wrap">
                    <span class="m2-item-t1">基金代码：</span>
                    <label>
                        <input type="text" id="fundcode" style="color:#333;" value='<?php echo $fundcode?>' data-key="<?php echo $public_key;?>"  data-code="<?php echo $rand_code;?>" readonly="true"/>
                    </label>
                </div>
            </div>
            <div class="m2-item">
                <div class="item-width-wrap">
                    <span class="m2-item-t1">基金名称：</span>
                    <label>
                        <input type="text" id="fundname" style="color:#333;" value='<?php echo $fundname?>' readonly="true"/>
                    </label>
                </div>
            </div>

            <div class="m2-item">
                <div class="item-width-wrap">
                    <span class="m2-item-t1">收费方式：</span>
                    <label>
                        <input type="text" id="sharetype" style="color:#333;" value='<?php echo $sharetypename?>' readonly="true"/>
                    </label>
                </div>
            </div>
            <div class="m2-item">
                <div class="item-width-wrap clearfix">
                    <span class="m2-item-t1">申购金额：</span>
                    <label>
                        <input type="number" id="sum" name="sum" style="color:#333;" data-error="金额错误"  placeholder=<?php echo "最小".intval($min_money+0.5)."元最大".($max_money>1000000?intval($max_money/10000)."万元":$max_money."元");?> />
                    </label>
                </div>
            </div>
            <div class="m2-item" id="payDiv">             
                <label style="float:none;margin:auto;display:block;width:90%;">
                    <span class="m2-item-t1">支付渠道：</span>
                    <select id="pay_way" name="pay_way" class="select-certificate">
                        <option value="0"><?php echo $bank_msg[0]?></option>
                    </select>
                </label>                       
            </div>
            <div class="light-content" id="pay-list" style="display:none;">
                <ul>                
				<?php
					foreach ($bank_msg as $key => $val)
					{
						echo '<li value='.$key.'>'.$val.'</li>';
					}
				?>
                </ul>
            </div>
            <section class="m-btn-wrap mt10 clearfix">
                <input class="btn btn-fix-left" id="backBtn" type="button" value="返回"/>
                <input class="btn btn-fix-right" id="nextBtn" type="button" style="display:block;" value="下一步"/>
                <input class="btn btn-fix-right" id="commit" type="button" style="display:none;" value="确定购买"/>
            </section>
            <input type="hidden" name="json" value='<?php echo $json;?>' />
            <input type="hidden" id="purchasetype" name="purchasetype" value='<?php echo $purchasetype;?>' />
        </form>
    </section>
</section>
<input type="hidden" id="range" data-min="<?php echo $min_money;?>" data-max="<?php echo $max_money;?>" data-min =>
</body>
<script src="/data/lib/zepto.min.js"></script>
<script src="/data/jijin/js/m.min.js"></script>
<script src="/data/jijin/js/common.js"></script>
<script src="/data/js/RSA.min.js"></script>
<script>
    Zepto(function(){
        M.checkBoxInit();
        $('#nextBtn').on('click',function(){
            M.checkForm(function () {
                var min = document.getElementById('range').attributes['data-min'].value,
                    max = document.getElementById('range').attributes['data-max'].value,
                    sum = document.getElementById('sum').value,
                    payDiv = document.getElementById('payDiv'),
                    purchasetype = document.getElementById('purchasetype').value,
                    div = document.createElement('div');
                if (!sum || parseInt(sum, 10) < parseInt(min, 10) || parseInt(sum, 10) > parseInt(max, 10)) {
                    alert('金额输入错误');
                    return false;
                }
                //验证全部通过回调               
                document.title = purchasetype+'确认';
                document.getElementById('applyChange').innerHTML = purchasetype+'确认';
                document.getElementById('nextBtn').style.display = 'none';
                document.getElementById('commit').style.display = 'block';
                document.getElementById('sum').setAttribute('readonly','true');
                document.getElementById('pay_way').disabled = true;
                document.getElementById('nextBtn').id = 'commit';
                div.setAttribute('class','m2-item');
                div.innerHTML = '<div class="item-width-wrap">'+
                                    '<span class="m2-item-t1">交易密码：</span>'+
                                    '<label>'+
                                        '<input type="password" id="passwd" name="tpasswd" placeholder="请输入交易密码" />'+
                                    '</label>'+
                                '</div>'; 
                document.getElementById('info_form').insertBefore(div, payDiv.nextSibling);
                $('#nextBtn').off();
            });
        });
        $('#commit').on('click',function () {
        	var encrypt = new JSEncrypt();
        	//alert($('#pass').attr('data-key'));        
        	encrypt.setPublicKey($('#fundcode').attr('data-key'));
        	var encrypted = encrypt.encrypt($('#passwd').val()+$('#fundcode').attr('data-code'));
        	$('#passwd').val(encrypted);
        	document.getElementById('pay_way').disabled = false;
            $('#info_form').submit();
        });
        
        $('#backBtn').on('click',function(){
            window.location.href='/jijin/Jz_fund';
        });
    });

    function selectLightbox (aa,bb) {       
        $(aa).on('click',function () {
            this.disabled = "true";
            M.createLightBox();         
            var list = bb.getElementsByTagName('li');
            bb.style.display = 'block';
            $(bb).on('click','li',function () {
                var cer_op = document.createElement('option');
                var op_value = this.attributes[0].value;
                cer_op.innerHTML =this.innerHTML;
                cer_op.setAttribute('value',op_value);
                cer_op.setAttribute('data-cname',this.dataset.cname);
                aa.replaceChild(cer_op,aa.childNodes[1]);
                bb.style.display = 'none';
                M.hideLightBox();
                aa.disabled = false;
            });     
        });
    }
    var pay = document.getElementById('pay_way'),
        payList = document.getElementById('pay-list');

    selectLightbox(pay,payList);
</script>
</html>