<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
	    <meta name="viewport" content=" initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	    <title>我的资产</title>
	    <meta name="keywords" content="小牛资本，小牛新财富，公募基金，风险评测" />
	    <meta name="description" content="购买小牛新财富发行的公募基金前需要做的风险等级评测" />
	    <meta name="format-detection" content="telephone=no" />
	    <meta name="apple-mobile-web-app-capable" content="no" />
	    <link href="<?php echo $base?>/data/css/mobile.css" media="screen" rel="stylesheet" type="text/css">
		<script src="http://code.jquery.com/jquery-1.8.3.min.js"></script>
	</head>
	<body>
		<div>
			<!-- header-->
			<section class="m-item-wrap m-item-5-wrap">
		        <div class="m-item-5 text-align:center">
		            <h3 class="text-center">我的资产</h3>
		        </div>
		    </section>
			<div style="display:flex">
				<h3 style="flex:1;text-align:center;">总资产:<span>   <?php echo $totalfundvolbalance?></span></h3>
				<h3 style="flex:1;text-align:center;">总市值:<span>   <?php echo $totalfundmarketvalue?></span></h3>
				<ul data-role="listview" id="list" data-inset="true">
				
				</ul>		
			</div>
			<div class="ui-grid-b">
				<div class="ui-block-a" ></div>
				<div class="ui-block-b" ><h3 style="text-align: center;margin-top:50px;">我的基金</h3></div>
				<div class="ui-block-c" ></div>
			</div>
			<section class="m-item-wrap"> 
		        <div class="m-item">
		            <div class="m-item-name-title">名称</div>
		            <div class="m-item-code-title">代码</div>
		            <div class="m-item-nav-title">净值</div>
		            <div class="m-item-nav-title">当前份额</div>
		        </div>
				<div class="m-item">
					<?php for($i=0;$i<count($fundInfoArray);$i++) {?>
					     <div class="m-item-name text-center"  ><span><?php echo $fundInfoArray[$i]['fundname']?></span></div>
					     <div class="m-item-code"  ><span><?php echo $fundInfoArray[$i]['fundcode']?></span></div>
					     <div class="m-item-nav"  ><span><?php echo $fundInfoArray[$i]['last_fundvol']?></span></div>
					     <div class="m-item-nav"  ><span><?php echo $fundInfoArray[$i]['fundmarketvalue']?></span></div>
					<?php }?>
				</div>
			</section>
			<div class="m-btn-wrap">
			    <input class="btn btn-2" type="button" onclick="history.go(-1)" value="返回上一页">
			</div>
			
			<!-- /content -->
			<div data-role="footer" class="ui-bar-b">
<!-- 				<a href="#" data-role="button" id="more" data-corners="false" data-theme="b">点击加载更多</a> -->
			</div>
		</div>
		<!-- /page -->
	</body>
			<script type="text/javascript">
				var pageNum = 1;//设置页码
	// 			window.onload = callAjax;
				$(function() {
	// 				callAjax();
				});	
						
				function callAjax() {
					$("#more").prop('disabled',true).addClass("ui-disabled");//禁用按钮
					var ajaxTimeout =$.ajax({
				        type: "POST",  
				        timeout : 10000, //超时时间设置，单位毫秒
				        url: "/jijin/ListTestController/getList",  
				        data:{"page":pageNum},
				        dataType: "json", 
				        async :true, 
				        beforeSend: function(){
				            //$('<div id="msg" />').addClass("loading").html("正在登录...").css("color","#999").appendTo('.ui-bar'); 
				        	$.mobile.loadingMessageTextVisible = true;
				            $.mobile.loadingMessageTheme = 'a';
				            $.mobile.showPageLoadingMsg();
				        },
				        success: function(json){
					        if (json.code == 'ok') {
					            var i = 0;
								for( i = 0; i < eval(json.listData).length; i++) {
									var list = $("<li><a href='"+json.listData[i].url+"'><img src='/data/img/sess.png'><h2>名称</h2><p>说明" + json.listData[i].content + "</p></a></li>");
									$("#list").append(list);
									$('ul').listview('refresh');
									$("#list").find("li:last").slideDown(300);
								}
								$("#msg").remove();
								$.mobile.hidePageLoadingMsg();
								$("#more").prop('disabled',false).removeClass("ui-disabled");//启用按钮
								pageNum = pageNum + 1;
					        } else if (json.code=='none'){
						        $("#msg_popup").text('没有更多数据了');
					        	$("#div_popup").popup("open");
					            setTimeout(function () { $("#div_popup").popup("close"); }, 1000);
					        } else {
					        	$("#msg_popup").text('系统异常['+json.msg+']');
					        	$("#div_popup").popup("open");
					            setTimeout(function () { $("#div_popup").popup("close"); }, 1000);
					        }					        
				        },
	                	complete : function(XMLHttpRequest,status){ //请求完成后最终执行参数
							if(status=='timeout'){//超时,status还有success,error等值的情况
								ajaxTimeout.abort();
								$("#msg_popup").text('系统超时');
					        	$("#div_popup").popup("open");
					            setTimeout(function () { $("#div_popup").popup("close"); }, 1000);
							}
							if(status=='error'){//超时,status还有success,error等值的情况
								ajaxTimeout.abort();
								$("#msg_popup").text('系统错误');
					        	$("#div_popup").popup("open");
					            setTimeout(function () { $("#div_popup").popup("close"); }, 1000);
							}
							if(status=='parsererror'){
								ajaxTimeout.abort();
								$("#msg_popup").text('系统异常');
					        	$("#div_popup").popup("open");
					            setTimeout(function () { $("#div_popup").popup("close"); }, 1000);
							}
							$("#msg").remove();
							$.mobile.hidePageLoadingMsg();
							$("#more").prop('disabled',false).removeClass("ui-disabled");//启用按钮						
							return false;
						} 
				    }); 
				}

				$("#more").live("click", function() {
					callAjax();
				});
			</script>
</html>