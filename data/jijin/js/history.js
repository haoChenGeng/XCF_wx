(function($) {
	window.onload =function(){
		
	
		function getUrlParam(name) { //获取url地址参数
			var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)"); //构造一个含有目标参数的正则表达式对象
			var r = window.location.search.substr(1).match(reg); //匹配目标参数
			if(r != null) return unescape(r[2]);
			return null; //返回参数值
		}
		
		var fundCode = getUrlParam("fundCode");
		function querymore(index) {
			var ul = document.getElementsByClassName("mui-table-view")[0];
			var fragment = document.createDocumentFragment();
			mui.ajax({
				url: "/jijin/jz_fund/getFundCurve",
				dataType: 'json',
				data: {
					fundCode: fundCode
				},
				success: function(res) {
					var data = res.hs_data;
					if(index == 30) {
						index = 30;
					} else if(!index) {
						index = data.length;
					}
					for(var i = 0; i < index; i++) {
						li_1 = document.createElement('li');
						li_2 = document.createElement('li');
						li_3 = document.createElement('li');
						li_2.className = "mui-text-center";
						li_3.className = "mui-text-right text-warning";
						li_1.innerHTML = data[i].TradingDay;
						li_2.innerHTML = data[i].IndexValue;
						li_3.innerHTML = data[i].ValueDailyGrowthRate;
						fragment.appendChild(li_1);
						fragment.appendChild(li_2);
						fragment.appendChild(li_3);
						ul.appendChild(fragment);
					}
				}
			});
		}
		querymore(30);
		//阻尼系数
		var deceleration = mui.os.ios ? 0.003 : 0.0009;
		$('.mui-scroll-wrapper').scroll({
			bounce: true,
			indicators: false, //是否显示滚动条
			deceleration: deceleration
		});
		$.each(document.querySelectorAll('.mui-slider-group .mui-scroll'), function(index, pullRefreshEl) {
			$(pullRefreshEl).pullToRefresh({
				up: {
					callback: function() {
						setTimeout(function() {
						querymore()
						var loadTxt =document.getElementsByClassName("mui-pull-bottom-tips")[0];
						loadTxt.style.display = 'none';
					}, 1000);
				}
			}
		});
	});
	}
})(mui);