<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<meta name="Keywords" content="小牛新财富" />
<meta name="Description" content="小牛新财富" />
<meta name="robots" content="index,follow,noodp,noydir" />
<meta name="viewport" content="width=640px, maximum-scale=1.0, user-scalable=no, target-densitydpi=320" />
<title>首页</title>
<link rel="stylesheet" href="/data/css/style.css" />
<link rel="stylesheet" href="/data/css/idangerous.swiper.css" />
</head>

<body>
<section class="content content_index">
	<article class="content01">
		<div class="indslide swiper-container pos-re">
		    <div class="slide swiper-wrapper">
	        <div class="item swiper-slide">
	        	<a href="javascript:;" class="dib">
							<img src="/data/img/banner-1.jpg" alt="活动1">
						</a>
					</div>
					<div class="item swiper-slide">
	        	<a href="javascript:;" class="dib">
							<img src="/data/img/banner-2.jpg" alt="活动2">
						</a>
					</div>
					<div class="item swiper-slide">
	        	<a href="javascript:;" class="dib">
							<img src="/data/img/banner-3.jpg" alt="活动3">
						</a>
					</div>
		    </div>
		    <div class="ctrl"></div>
			<?php
				if (isset($_SESSION['customer_id'])){
					echo '<a href="/application/views/user/personalCenter.html" class="info-link dib pos-ab"><img src="/data/img/personal-center.png" alt="个人中心" width="130" height="130"></a>';
				}else{
					echo '<div>
			    			<div class="pos-ab"></div>
							<a href="/user/login/1" class="dib login-btn pos-ab">登录</a>
						  </div>';
				}
			?>		
		</div>
    	
        <ul class="title_listul">
        	<li class="li01">
            	<a href="/jijin/Jz_account/entrance" id="fund_access" class="span01">公募</a>
            </li>
            <li class="li01 li02">
            	<a href="/application/views/commingsoon.php" id="" class="span01">私募</a>
            </li>
            <li class="li01 li03">
            	<a href="/FindPaper" id="sign_in" class="span01">发现</a>
            </li>
            <li class="li01 li04">
<!--             	<a href="/application/views/commingsoon.php" id="member" class="span01">会员专区</a> -->
            	<a href="#" id="member_apply" class="span01">会员专区</a>
            </li>
        </ul>
        <div class="text_list pos-re mt60">
          <p class="pos-ab fund-list-title">推荐基金</p>
         	<ul class="text_ul">             	
                <?php foreach ($Recommend as $val)
                	echo '<li class="clearfix">
													<p class="product-bottom">'.$val['fundname'].'</p>
													<div class="product-item1">
														<p class="product-item-num">'.$val['growthrate'].'</p>
														<p class="product-item-name">七日年化收益率</p>
													</div>
													<div class="product-item2">
														<p class="product-item-num" style="font-size:28px;">'.$val['fundtype'].'</p>
														<p class="product-item-name">基金类型</p>
													</div>
												 </li>'
                ?>
         	</ul>
        </div>
        <input id="access_type" type="hidden" name = "access_type" value ="<?php echo isset($type)?$type:0;?>"></input>      
    </article>
</section>


<script src="/data/js/RSA.min.js"></script>
<script src="/data/js/aes.js"></script>
<script src="/data/js/aes-json-format.js"></script> 
<script src="/data/js/zepto.min.js"></script>
<script src="/data/js/idangerous.swiper-2.1.min.js"></script>

<script type="text/javascript">
$(function(){
	$(".indslide").swiper({
		calculateHeight	:	true,
		pagination		:	 ".indslide .ctrl",
		mode			:	 "horizontal",
		slidesPerView 	:	 1,
		autoplay : 2000,
		speed: 500, 
		loop			:	 true,
		paginationClickable : true,
	});	
});

function RSA_encrypt(access_type) {
	var rdmString = "";
    for (; rdmString.length < 16; rdmString += Math.random().toString(36).substr(2));
    var encrypt = new JSEncrypt();
	encrypt.setPublicKey("<?php echo str_replace("\n",'', file_get_contents($this->config->item('RSA_publickey')));?>");   
    var pass_key = encrypt.encrypt(rdmString);
    var access_url = '';
    switch (true){
    	case access_type>=100 && access_type<200:
        	access_url = "/client/Fund_access/access";
    		break;
    	case access_type>=200 && access_type<300:
    		access_url = "/client/Member_access/access";
			break;
    }
	var ajaxTimeout =$.ajax({
        type: "POST",  
        timeout : 10000, //超时设置(单位毫秒)
        url: access_url, 			        
        data:{"pass_key":pass_key,"access_type":access_type},
        dataType: "json", 
        async :true, 
        success: function(json){
	        if (json.code == 'ok') {
				var encrypted_code = CryptoJS.AES.encrypt(rdmString, rdmString, {format: CryptoJSAesJson}).toString();
			    var temp = document.createElement("form");
			    temp.action = json.url;
			    temp.method = "post";        
			    temp.style.display = "none";
			    var opt = document.createElement("textarea");        
			    opt.name = 'apply_code';        
			    opt.value = json.apply_code;        
			    temp.appendChild(opt); 
			    var opt2 = document.createElement("input");
			    opt2.name = 'encrypted_code';        
			    opt2.value = encrypted_code; 
			    temp.appendChild(opt2);
			    var opt3 = document.createElement("textarea");        
			    opt3.name = 'type';        
			    opt3.value = json.type;        
			    temp.appendChild(opt3);     
			    document.body.appendChild(temp);
			    temp.submit();
			}
	        else{
		        switch (json.code){
		        	case 'e0':
			        	alert('您尚未登录，请登录！');
		        		window.location.href = json.url;
		        		break;
		        	case 'm0':
		        		window.location.href = json.url;
		        		break;
		        	default:
		        		alert('会员系统登录失败，请稍候重试！');
		        }
		    }
        },
    	complete : function(XMLHttpRequest,status){        //请求完成后最终执行参数
			if(status=='timeout'){                         //超时,status还有success,error等值的情况
				ajaxTimeout.abort();
				$("#msg_popup").text('系统超时');
// 	        	$("#div_popup").popup("open");
// 	            setTimeout(function () { $("#div_popup").popup("close"); }, 1000);
			}
			if(status=='error'){                           //超时,status还有success,error等值的情况
				ajaxTimeout.abort();
				$("#msg_popup").text('系统错误');
// 	        	$("#div_popup").popup("open");
// 	            setTimeout(function () { $("#div_popup").popup("close"); }, 1000);
			}
			if(status=='parsererror'){
				ajaxTimeout.abort();
				$("#msg_popup").text('系统异常');
// 	        	$("#div_popup").popup("open");
// 	            setTimeout(function () { $("#div_popup").popup("close"); }, 1000);
			}
		} 
    }); 
}

window.onload = function () {
	(function () {
		var access_type = document.getElementById('access_type').value;
		if (access_type != 0)
		{
			RSA_encrypt(access_type);
		}
// 		var member_access = document.getElementById('member');
// 		member_access.onclick = function() {RSA_encrypt(201)};
		var member_apply = document.getElementById('member_apply');
		member_apply.onclick = function() {RSA_encrypt(202)};
// 		var member_discount = document.getElementById('member_discount');
// 		member_discount.onclick = function() {RSA_encrypt(208)};
		var fund_access = document.getElementById('fund_access');
		fund_access.onclick = function() {RSA_encrypt(101)};
	})();
}; 
</script>
</body>
</html>
