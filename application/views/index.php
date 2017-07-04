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
					$headUrl = "/application/views/user/personalCenter.html";
					$headHtml = '我的';
				}else{
					$headUrl = "/user/login/1";
					$headHtml = '登录';
				}
				echo '<div><div class="pos-ab"></div><a href='.$headUrl.' style=" ';
				if (isset($_SESSION['headimgurl'])){
					echo 'background:url('.$_SESSION['headimgurl'].') no-repeat;background-size:100%;';
				}
				echo '" class="dib login-btn pos-ab">'.$headHtml.'</a></div>';
/* 				if (isset($_SESSION['customer_id'])){
					if(ISTESTING)
						echo '<a href="/application/views/user/personalCenter.html" class="info-link dib pos-ab" ><img src="/data/img/personal-center.png" alt="个人中心" width="130" height="130"></a>';
						else
							echo '<a href="/application/views/user/personalCenter.html" style="left:25px;top:25px;width:130px;height:130px;background-color:yellow;background:url('.$_SESSION['headimgurl'].') no-repeat;background-size:100%;" class="info-link dib login-btn pos-ab" >我的</a>';
				}else{
					if(ISTESTING)
						echo '<div>
			    			<div class="pos-ab"></div>
							<a href="/user/login/1" class="dib login-btn pos-ab">登录</a>
							</div>';
					else
						echo '<div><div class="pos-ab"></div><a href="/user/login/1" style="background-color:;';
						if (isset($_SESSION['headimgurl'])){
							echo 'background:url('.$_SESSION['headimgurl'].') no-repeat;background-size:100%;';
						}
						echo '" class="dib login-btn pos-ab">登录</a>
						  </div>'; 
				} */
			?>		
		</div>
    	
        <ul class="title_listul">
        	<li class="li01">
            	<a href="/jijin/Jz_account/entrance" id="fund_access" class="span01">公募</a>
            </li>
            <li class="li01 li02">
            	<a href="/application/views/privateFund/private.html" id="" class="span01">私募</a>
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
                		echo '<a href="'.$val['url'].'?fundcode='.$val['fundcode'].'&purchasetype='.$val['purchasetype'].'&next_url=">';
                	}
					echo   '<p class="product-bottom">'.$val['fundname'].'('.$val['fundtype'].')</p>
							<div class="product-item1">
								<p class="product-item-num">'.$val['growthrate'].'</p>
								<p class="product-item-name">'.$val['growthDes'].'</p>
							</div>
							<div class="product-item2">
								<p class="product-item-num" style="font-size:28px;">'.$val['nav'].'</p>
								<p class="product-item-name">基金净值</p>
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
