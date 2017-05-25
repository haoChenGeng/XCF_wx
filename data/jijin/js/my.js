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
	 			if (!data.fund_list.data.length) {
	 				var oLi = document.createElement('li');	 			
	 				oLi.setAttribute('class','mui-table-view-cell');
	 				oLi.innerHTML = '<p class="fund-list-error"><span>未购买任何基金</span></p>';								 	
					fragment.appendChild(oLi);
	 			} else {	 			
		 			for (var i = data.fund_list.data.length - 1; i >= 0; i--) {
		 				if (0 === data.fund_list.data[i].length) continue;
		 				var oLi = document.createElement('li');
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

	(function($) {
		$('.mui-scroll-wrapper').scroll({
			indicators: true //是否显示滚动条
		});					
		var item2 = document.getElementById('item2mobile'),
			item3 = document.getElementById('item3mobile');
			// item4 = document.getElementById('item4mobile');
		document.getElementById('slider').addEventListener('slide', function(e) {
			switch (e.detail.slideNumber) {
				case 0:
					break;
				case 1:
					if (item2.querySelector('.mui-loading')) {							
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
			 				 		if (0 == data.bonus_change.data.length) {
							 			var oLi = document.createElement('li');
							 			oLi.setAttribute('class','mui-table-view-cell');
							 			oLi.innerHTML = '<p class="fund-list-error"><span>未购买任何基金</span></p>';														 	
										fragment.appendChild(oLi);
							 		} else {
			 				 			for (var i = data.bonus_change.data.length - 1; i >= 0; i--) {
			 				 				var oLi = document.createElement('li');
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
					break;
				case 2:
					if (item3.querySelector('.mui-loading')) {			
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
					break;
				// case 3:
				// var search = document.getElementById('search');
				// search.addEventListener('tap',function() {
				// 	var startDate = document.getElementById('begin').innerHTML,
				// 		endDate = document.getElementById('end').innerHTML;
				// 	if (startDate == '开始日期') {
				// 		alert('请选择开始日期');
				// 	}else if (endDate == '结束日期') {
				// 		alert('请选择结束日期');
				// 	}else if (parseInt(endDate.replace(/\-/g,""),10)-parseInt(startDate.replace(/\-/g,""),10) < 0) {
				// 		alert('日期选择错误，请重新选择');
				// 	}else {
				// 		mui.ajax(aa+'/jijin/Jz_my/getHistoryTran'+'/'+startDate.replace(/\-/g,"")+'/'+endDate.replace(/\-/g,""),{
				// 			data:{},
				// 			dataType:'json',
				// 			type:'get',
				// 			timeout:10*1000,
				// 			beforeSend:function() {
				// 				document.getElementById('scroll4').querySelector('.mui-loading').style.display = "block";
				// 			},
				// 			success:function(data) {						 		
				// 	 			document.getElementById('scroll4').querySelector('.mui-loading').style.display = "none";
				// 	 			var listWrap = document.getElementById('historyTran');
				// 	 			var fragment = document.createDocumentFragment();
				// 	 			if (1 == data.data.length && 0 === data.data[0].length) {
				// 		 			listWrap.innerHTML = '<p class="fund-list-error"><span>未购买任何基金</span></p>';														 	
				// 		 		} else {
				// 		 			for (var i = data.data.length - 1; i >= 0; i--) {
				// 			 			var oLi = document.createElement('li');
				// 		 				oLi.setAttribute('class','mui-table-view-cell query-padding');
				// 		 				oLi.innerHTML = '<a href="###" class="query-link">'+
				// 		 									'<div class="mui-media-body clear delegate">'+
				// 												'<div class="delegate-date">'+data.data[i].transactioncfmdate+'</div>'+
				// 												'<div class="delegate-name">'+data.data[i].fundname+'/'+data.data[i].businesscode+'</div>'+
				// 												'<div class="delegate-amount">'+data.data[i].confirmedvol+'/'+data.data[i].confirmedamount+'</div>'+
				// 												'<div class="delegate-oprate">申购</div>'+
				// 												'<div class="delegate-more"></div>'+										
				// 									 		'</div>'+
				// 									 	'</a>';
				// 						fragment.appendChild(oLi);
				// 		 			}
				// 		 		listWrap.innerHTML = "";
				// 	 			listWrap.appendChild(fragment);
				// 		 		}						 		
				// 			},
				// 			error:function () {
				// 		 		alert('查询失败，请稍后重试！');
				// 		 	}
				// 		});
				// 	}
				// });
					// break;				
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