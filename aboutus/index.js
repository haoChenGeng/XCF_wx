$(function(){
	var slider = mui("#slider");
		setTimeout(function(){
			slider.slider({
			interval: 3000
		});
	},1000)
	

	mui.init();
	//初始化单页view
	var viewApi = mui('#app').view({
		defaultPage: '#mainView'
	});
	//初始化单页的区域滚动
	mui('.mui-scroll-wrapper').scroll();

	var client = document.documentElement.clientHeight;
	var b = document.body.clientHeight;
	var o = document.querySelector('.mw').offsetHeight;
	var bg = document.getElementById('bg');
	bg.style.height = client > o ? client : o + 'px';
	// alert(client);
	// alert(b);
	// alert(o);

	var view = viewApi.view;
	(function($) {
		//处理view的后退与webview后退
		var oldBack = $.back;
		$.back = function() {
			if (viewApi.canBack()) { //如果view可以后退，则执行view的后退
				viewApi.back();
			} else { //执行webview后退
				oldBack();
			}
		};
		//监听页面切换事件方案1,通过view元素监听所有页面切换事件，目前提供pageBeforeShow|pageShow|pageBeforeBack|pageBack四种事件(before事件为动画开始前触发)
		//第一个参数为事件名称，第二个参数为事件回调，其中e.detail.page为当前页面的html对象
		view.addEventListener('pageBeforeShow', function(e) {
			if(e.detail.page.id=="mainView"){
				showMain();
				loadimgF(10);
			}
							//console.log(e.detail.page.id + ' beforeShow');
		});
		view.addEventListener('pageShow', function(e) {
			if(e.detail.page.id!="mainView"){
				moveImgLoag();
				hideMain();
			}
							//console.log(e.detail.page.id + ' show');
		});
		view.addEventListener('pageBeforeBack', function(e) {
							//console.log(e.detail.page.id + ' beforeBack');
		});
		view.addEventListener('pageBack', function(e) {
							//console.log(e.detail.page.id + ' back');
		});
	})(mui);

	if(mui.os.stream){
		document.getElementById("check_update").display = "none";
	}
	lazyImg();
})
function lazyImg(){
	setTimeout(function(){
		loadimgF();
	},50);
}

function loadimgF(t){
	if($(".jz-img.imgLoad").length<$(".jz-img").length){
		setTimeout(function(){
			var i=$(".jz-img.imgLoad").length;
			$($(".jz-img")[i]).addClass("imgLoad");
			if($(".jz-img.imgLoad").length<$(".jz-img").length){
				loadimgF(500);
			}
		},t);
	}
}

function moveImgLoag(){
	$(".jz-img").removeClass("imgLoad");
}

function hideMain(){
	$(".main-wrapper").hide();
}
function showMain(){
	$(".main-wrapper").show();
}

