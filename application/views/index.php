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
            	<a href="/application/views/private/private.html" id="" class="span01">私募</a>
            </li>
            <li class="li01 li03">
            	<a href="/FindPaper" id="sign_in" class="span01">发现</a>
            </li>
            <li class="li01 li04">
            	<a href="https://neoclub.xiaoniuxcf.com/wap.php" class="span01">会员专区</a>
            </li>
        </ul>
        <div class="text_list pos-re mt60">
          <p class="pos-ab fund-list-title">推荐基金</p>
         	<ul class="text_ul">             	
                <?php 
                foreach ($Recommend as $val){
                	echo '<li class="clearfix">';
                	if(isset($val['url'])){
                		echo '<a href="'.$val['url'].'?fundcode='.$val['fundcode'].'&purchasetype='.$val['purchasetype'].'">';
                	}
					echo   '<p class="product-bottom">'.$val['fundname'].'</p>
							<div class="product-item1">
								<p class="product-item-num">'.$val['growthrate'].'</p>
								<p class="product-item-name">'.$val['growthDes'].'</p>
							</div>
							<div class="product-item2">
								<p class="product-item-num" style="font-size:28px;">'.$val['fundtype'].'</p>
								<p class="product-item-name">基金类型</p>
							</div>
						 </li>';
                }
                ?>
         	</ul>
        </div>
        <input id="access_type" type="hidden" name = "access_type" value ="<?php echo isset($type)?$type:0;?>"></input>      
    </article>
</section>

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

</script>
</body>
</html>
