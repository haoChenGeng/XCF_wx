window.onload = function () {
	var seeHeight = document.documentElement.clientHeight,
		sliderHeight = document.getElementById('sliderSegmentedControl').clientHeight,
		navHeight = document.querySelector('.mui-bar-tab').clientHeight,
		topHeight = document.getElementById('header').clientHeight,
		control = document.querySelectorAll('.mui-control-content');

	var h = seeHeight-sliderHeight-navHeight-topHeight-2;
	for (var i = control.length - 1; i >= 0; i--) {
		control[i].style.height = h + 'px';
	}

  var aa = document.getElementById('url').value;

  page1();
	function page1() {
		mui.ajax(aa+'/jijin/Jz_my/getMyPageData/fund',{
		 	data:{},
		 	dataType:'json',
		 	type:'post',
		 	timeout:30*1000,
		 	success:function (data) {
		 		if (data.error) {
		 			var fundList = document.getElementById('scroll1');
		 			fundList.innerHTML = '<a class="fund-list-error" href="'+aa+'/jijin/Jz_account/register?next_url=jz_my&myPageOper=asset" id="errorMsg">'+data.errorMsg+'</a>';	 			
		 		}else {
		 			document.getElementById('totalBalance').innerHTML = data.totalfundvolbalance || 0;
		 			var listWrap = document.getElementById('buyFundList');
		 			var fragment = document.createDocumentFragment();
		 				var oLi = document.createElement('li');
		 			if (!data.fund_list.data.length) {
		 				oLi.setAttribute('class','mui-table-view-cell');
		 				oLi.innerHTML = '<p class="fund-list-error"><span>未购买任何基金</span></p>';								 	
						fragment.appendChild(oLi);
		 			} else {	 			
			 			for (var i = data.fund_list.data.length - 1; i >= 0; i--) {
			 				if (0 === data.fund_list.data[i].length) continue;
			 				oLi.setAttribute('class','mui-table-view-cell');
			 				oLi.innerHTML = '<div class="mui-media-body clear">'+
																'<a type="button" href="'+aa+'/jijin/RedeemFundController/Redeem?json='+data.fund_list.data[i].json+'" class="mui-btn mui-btn-success fund-btn-redeem">赎回</a>'+
																'<p>名称：<span>'+data.fund_list.data[i].fundname+'</span></p>'+
																'<p>代码：<span>'+data.fund_list.data[i].fundcode+'</span></p>'+
																'<p>净值/份额：<span>'+data.fund_list.data[i].nav+'/'+data.fund_list.data[i].fundvolbalance+'</span></p>'+
																'<p>类型：<span>'+data.fund_list.data[i].fundtypename+'</span></p>'+
														 	'</div>';
							if (data.fund_list.data[i].redeem != 'Y') {
								oLi.querySelector('.fund-btn-redeem').href = "javascript:;";
								oLi.querySelector('.fund-btn-redeem').style.cssText = "border-color:#ccc;color:#ccc;";
							}
							fragment.appendChild(oLi);
			 			}
		 			}
		 			listWrap.innerHTML = "";
		 			listWrap.appendChild(fragment);
		 		}
		 	},
		 	error:function () {
		 		alert('查询失败，请稍后重试！');
		 	}
		});
	}

	var item2 = document.getElementById('item2mobile');
	var	item3 = document.getElementById('item3mobile');

	function page2() {
		mui.ajax(aa+'/jijin/Jz_my/getMyPageData/bonus_change',{
		 	data:{},
		 	dataType:'json',
		 	type:'post',
		 	timeout:30*1000,
		 	success:function (data) {
		 		if (data.error) {
		 			var fundList = document.getElementById('scroll2');
		 			fundList.innerHTML = '<a class="fund-list-error" href="'+aa+'/jijin/Jz_account/register?next_url=jz_my&myPageOper=bonus" id="errorMsg">'+data.errorMsg+'</a>';						 			
		 		}else {
			 		var listWrap = document.getElementById('bonus-mod');
				 		var fragment = document.createDocumentFragment();
		 			var oLi = document.createElement('li');
				 		if (0 === data.bonus_change.data.length) {
			 			oLi.setAttribute('class','mui-table-view-cell');
			 			oLi.innerHTML = '<p class="fund-list-error"><span>未购买任何基金</span></p>';														 	
						fragment.appendChild(oLi);
			 		} else {
				 			for (var i = data.bonus_change.data.length - 1; i >= 0; i--) {
				 				oLi.setAttribute('class','mui-table-view-cell');
				 				oLi.innerHTML = '<div class="mui-media-body clear">'+
				 													'<a type="button" href="'+aa+'/jijin/ModifyBonusController/Modify?json='+data.bonus_change.data[i].json+'" class="mui-btn mui-btn-success fund-btn-bc">修改分红</a>'+
				 													'<p>名称：<span>'+data.bonus_change.data[i].fundname+'</span></p>'+
				 													'<p>代码：<span>'+data.bonus_change.data[i].fundcode+'</span></p>'+
				 													'<p>净值：<span>'+data.bonus_change.data[i].nav+'</span></p>'+
				 													'<p>分红方式：<span>'+data.bonus_change.data[i].dividendmethodname+'</span></p>'+
				 											 	'</div>';
								fragment.appendChild(oLi);
				 			}
		 			}
			 			listWrap.innerHTML = "";	 				 			
			 			listWrap.appendChild(fragment);
			 		}						 		
		 	},
		 	error:function () {
		 		alert('查询失败，请稍后重试！');
		 	}
		});	
	}

	function page3() {
		mui.ajax(aa+'/jijin/Jz_my/getMyPageData/risk_test',{
		 	data:{},
		 	dataType:'json',
		 	type:'post',
		 	timeout:30*1000,
		 	success:function (data) {
	 			var nodeWrap = item3.querySelector('.mui-scroll'),
	 				nodeChlid = item3.querySelector('.mui-scroll').childNodes;
	 			nodeWrap.removeChild(nodeChlid[1]);
		 		if (data.error) {
		 			var fundList = document.getElementById('scroll3');
		 			fundList.innerHTML = '<a class="fund-list-error" href="'+aa+'/jijin/Jz_account/register?next_url=jz_my&myPageOper=account" id="errorMsg">'+data.errorMsg+'</a>';						 			
		 		} else {
		 			var risk = document.getElementById('risk_result');
		 			risk.innerHTML = '风险测试['+data.custrisk+':'+data.custriskname+']';						 			
		 		}
		 	},
		 	error:function () {
		 		alert('查询失败，请稍后重试！');
		 	}
		});	
	}

	var active = document.getElementById('slider').querySelector('.mui-active');
  switch (active.hash) {
    case '#item1mobile':
      page1();
      break;
    case '#item2mobile':
      page2();
      break;
    case '#item3mobile': 
      page3();
      break;
    default:
      break;
  }

	(function($) {
		$('.mui-scroll-wrapper').scroll({
			indicators: true //是否显示滚动条
		});					
		document.getElementById('slider').addEventListener('slide', function(e) {
			switch (e.detail.slideNumber) {
				case 0:
					break;
				case 1:
					if (item2.querySelector('.mui-loading')) {							
						page2();
					}
					break;
				case 2:
					if (item3.querySelector('.mui-loading')) {			
						page3();
					}
					break;
				default:
					break;
			}
		});
		var sliderSegmentedControl = document.getElementById('sliderSegmentedControl');
		$('.mui-input-group').on('change', 'input', function() {
			if (this.checked) {
				sliderSegmentedControl.className = 'mui-slider-indicator mui-segmented-control mui-segmented-control-inverted mui-segmented-control-' + this.value;
				//force repaint
				sliderProgressBar.setAttribute('style', sliderProgressBar.getAttribute('style'));
			}
		});
	})(mui);
};

(function($) {
	$.init();	
	var btns = $('.btn');
	btns.each(function(i, btn) {
		btn.addEventListener('tap', function() {
			var optionsJson = this.getAttribute('data-options') || '{}';
			var options = JSON.parse(optionsJson);
			var id = this.getAttribute('id');
			var showRs = this;
			/*
			 * 首次显示时实例化组件
			 * 示例为了简洁，将 options 放在了按钮的 dom 上
			 * 也可以直接通过代码声明 optinos 用于实例化 DtPicker
			 */
			var picker = new $.DtPicker(options);
			picker.show(function(rs) {
				/*
				 * rs.value 拼合后的 value
				 * rs.text 拼合后的 text
				 * rs.y 年，可以通过 rs.y.vaue 和 rs.y.text 获取值和文本
				 * rs.m 月，用法同年
				 * rs.d 日，用法同年
				 * rs.h 时，用法同年
				 * rs.i 分（minutes 的第二个字母），用法同年
				 */
				showRs.innerHTML = rs.text;

				/* 
				 * 返回 false 可以阻止选择框的关闭
				 * return false;
				 */
				/*
				 * 释放组件资源，释放后将将不能再操作组件
				 * 通常情况下，不需要示放组件，new DtPicker(options) 后，可以一直使用。
				 * 当前示例，因为内容较多，如不进行资原释放，在某些设备上会较慢。
				 * 所以每次用完便立即调用 dispose 进行释放，下次用时再创建新实例。
				 */
				picker.dispose();
			});
		}, false);
	});
})(mui);