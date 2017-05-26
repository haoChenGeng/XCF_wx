window.onload = function () {
	
	var seeHeight = document.documentElement.clientHeight,
		sliderHeight = document.getElementById('sliderSegmentedControl').clientHeight,
		navHeight = document.querySelector('.mui-bar-tab').clientHeight,
//		topHeight = document.getElementById('header').clientHeight,
		topHeight = 0;
		control = document.querySelectorAll('.mui-control-content');

	var h = seeHeight-sliderHeight-navHeight-topHeight-2;
	for (var i = control.length - 1; i >= 0; i--) {
		control[i].style.height = h + 'px';
	}

	var aa = document.getElementById('url').value;
	
	var firstPage = function() {
		mui.ajax(aa+'/jijin/Jz_fund/getFundPageData/buy',{
		 	data:{},
		 	dataType:'json',
		 	type:'post',
		 	timeout:30*1000,
		 	success:function (data) {
		 		if (!data) {
		 			var fundList = document.getElementById('scroll1');
		 			fundList.innerHTML = '<a class="fund-list-error">查询基金失败</a>';
		 		}else {	 			
		 			var listWrap = document.getElementById('subscribe');
		 			var fragment = document.createDocumentFragment();
		 			if (!data.buy.data) {
			 			listWrap.innerHTML = '<p class="fund-list-error"><span>暂无基金</span></p>';									 	
			 		} else {
			 			listWrap.innerHTML = '';
			 			for (var i = data.buy.data.length - 1; i >= 0; i--) {
			 				var oLi =document.createElement('li');
			 				oLi.setAttribute('class','mui-table-view-cell');
			 				oLi.innerHTML = '<div class="mui-media-body clear">'+
																'<p>名称：<span>'+data.buy.data[i].fundname+'</span></p>'+
																'<p>代码：<span>'+data.buy.data[i].fundcode+'</span></p>'+
																'<p>净值：<span>'+data.buy.data[i].nav+'</span></p>'+
																'<p>类型：<span>'+data.buy.data[i].fundtypename+'</span></p>'+
																'<a href="'+aa+'/jijin/Jz_fund/showprodetail?fundcode='+data.buy.data[i].fundcode+'&tano='+data.buy.data[i].tano+'&fundcode='+data.buy.data[i].fundcode+'&purchasetype=认购'+' " type="button" class="mui-btn mui-btn-success fund-btn-bk bonus-pad">详情</a>'+
																'<a href="'+aa+'/jijin/PurchaseController/Apply?fundcode='+data.buy.data[i].fundcode+'&purchasetype=认购'+' " type="button" class="mui-btn mui-btn-success fund-btn-bd">认购</a>'+
													 		'</div>';								 	
							fragment.appendChild(oLi);
			 			}
			 		}
		 			listWrap.appendChild(fragment);
		 		}
		 	},
		 	error:function () {
		 		alert('查询失败，请稍后重试！');
		 	}
		});
	};
	firstPage();

		var item2 = document.getElementById('item2mobile'),
			item3 = document.getElementById('item3mobile'),
			item4 = document.getElementById('item4mobile');

		var page2 = function() {
			mui.ajax(aa+'/jijin/Jz_fund/getFundPageData/apply',{
			 	data:{},
			 	dataType:'json',
			 	type:'post',
			 	timeout:60*1000,
			 	success:function (data) {
			 		if (!data) {
			 			var fundList = document.getElementById('scroll2');
			 			fundList.innerHTML = '<p class="fund-list-error">查询基金失败</p>';
			 		}else {
			 			var nodeWrap = item2.querySelector('.mui-scroll'),
			 				nodeChlid = item2.querySelector('.mui-scroll').childNodes;				 						 				
			 			nodeWrap.removeChild(nodeChlid[1]);
			 			var listWrap = document.getElementById('apply');
			 			var fragment = document.createDocumentFragment();
			 			if (0 === data.apply.length) {
				 			listWrap.innerHTML = '<p class="fund-list-error"><span>暂无基金</span></p>';														 	
				 		} else {
				 			for (var i = data.apply.data.length - 1; i >= 0; i--) {
				 				var oLi = document.createElement('li');
				 				oLi.setAttribute('class','mui-table-view-cell');
				 				oLi.innerHTML = '<div class="mui-media-body clear">'+
																	'<p>名称：<span>'+data.apply.data[i].fundname+'</span></p>'+
																	'<p>代码：<span>'+data.apply.data[i].fundcode+'</span></p>'+
																	'<p>净值：<span>'+data.apply.data[i].nav+'</span></p>'+
																	'<p>类型：<span>'+data.apply.data[i].fundtypename+'</span></p>'+
																	'<a href="'+aa+'/jijin/Jz_fund/showprodetail?fundcode='+data.apply.data[i].fundcode+'&tano='+data.apply.data[i].tano+'&fundcode='+data.apply.data[i].fundcode+'&purchasetype=申购'+'" type="button" class="mui-btn mui-btn-success fund-btn-bk bonus-pad">详情</a>'+
																	'<a href="'+aa+'/jijin/PurchaseController/Apply?fundcode='+data.apply.data[i].fundcode+'&purchasetype=申购'+' " type="button" class="mui-btn mui-btn-success fund-btn-bd">申购</a>'+
														 		'</div>';
								fragment.appendChild(oLi);
				 			}
				 		}
			 			listWrap.appendChild(fragment);
			 		}
			 	},
			 	error:function () {
			 		alert('查询失败，请稍后重试！');
			 		document.getElementById('scroll2').querySelector('.mui-loading').style.display = 'none';
			 	}
			});
		};

		var page3 = function() {
			mui.ajax(aa+'/jijin/Jz_fund/getFundPageData/today',{								
			 	data:{},
			 	dataType:'json',
			 	type:'post',
			 	timeout:30*1000,
			 	success:function (data) {						 		
		 			var nodeWrap = item3.querySelector('.mui-scroll'),
		 				nodeChlid = item3.querySelector('.mui-scroll').childNodes;						 						 				
		 			nodeWrap.removeChild(nodeChlid[1]);
		 			var listWrap = document.getElementById('today');
		 			var fragment = document.createDocumentFragment();
		 			if ( data.msg || !data.today.data) {
		 				if (data.msg){
		 					listWrap.innerHTML = '<a class="fund-list-error" href="'+aa+'/jijin/Jz_account/register?next_url=jz_fund&fundPageOper=today" id="errorMsg">'+data.msg+'</a>';
		 				}else{
		 					listWrap.innerHTML = '<p class="fund-list-error"><span>'+data.today.msg+'</span></p>';	
		 				}
			 		}else {
			 			for (var i = data.today.data.length - 1; i >= 0; i--) {
				 			var oLi = document.createElement('li');							 			
			 				oLi.setAttribute('class','mui-table-view-cell query-padding');
			 				var paystatus = data.today.data[i].paystatus ? '/'+data.today.data[i].paystatus : '';
			 				oLi.innerHTML = '<div class="query-link">'+
							 									'<div class="mui-media-body clear delegate">'+
																	'<div class="delegate-name">'+data.today.data[i].fundname+'/'+data.today.data[i].fundcode+'</div>'+
																	'<div class="delegate-oprate">'+data.today.data[i].businesscode+'</div>'+
																	'<div class="delegate-amount">'+data.today.data[i].applicationamount+'/'+data.today.data[i].applicationvol+'</div>'+
																	'<div class="delegate-oprate">'+data.today.data[i].status+paystatus+'</div>'+
																	'<div class="delegate-more"><a type="button" class="cancel-order" href="'+aa+'/jijin/CancelApplyController/cancel?appsheetserialno='+data.today.data[i].appsheetserialno+'">撤销</a>'+									
														 		'</div>'+
														 	'</div>';
							if (data.today.data[i].cancelable === 0) {
								oLi.querySelector('.delegate-more').innerHTML = "";
							}
							fragment.appendChild(oLi);
			 			}
			 		}
		 			listWrap.appendChild(fragment);						 		
			 	},
			 	error:function () {
			 		alert('查询失败，请稍后重试！');
			 		document.getElementById('scroll3').querySelector('.mui-loading').style.display = 'none';
			 	}
			});
		};

		function page4(a,b) {
			mui.ajax(aa+'/jijin/Jz_fund/getFundPageData/history'+'/'+a.replace(/\-/g,"")+'/'+b.replace(/\-/g,""),{
				data:{},
				dataType:'json',
				type:'get',
				timeout:10*1000,
				beforeSend:function() {
					document.getElementById('scroll4').querySelector('.mui-loading').style.display = "block";
				},
				success:function(data) {
		 			document.getElementById('scroll4').querySelector('.mui-loading').style.display = "none";
		 			var listWrap = document.getElementById('history');
		 			var fragment = document.createDocumentFragment();
		 			if (data.msg || !data.history.data) {
		 				if (data.msg){
		 					listWrap.innerHTML = '<a class="fund-list-error" href="'+aa+'/jijin/Jz_account/register" id="errorMsg">'+data.msg+'</a>';
		 				}else{
		 					listWrap.innerHTML = '<p class="fund-list-error"><span>'+data.history.msg+'</span></p>';
		 				}
			 		} else {
			 			for (var i = data.history.data.length - 1; i >= 0; i--) {
			 				var oLi = document.createElement('li');
			 				oLi.setAttribute('class','mui-table-view-cell query-padding');
			 				oLi.innerHTML = '<li class="mui-table-view-cell mui-collapse">'+
								 								'<a href="###" class="query-link mui-navigate-right" style="white-space:normal;">'+
								 									'<div class="mui-media-body clear delegate">'+
																		'<div class="delegate-date">'+data.history.data[i].operdate+'</div>'+
																		'<div class="delegate-name">'+data.history.data[i].fundname+'/'+data.history.data[i].fundcode+'</div>'+
																		'<div class="delegate-oprate">'+data.history.data[i].businesscode+'</div>'+
																		'<div class="delegate-amount">'+data.history.data[i].applicationamount+'/'+data.history.data[i].applicationvol+'</div>'+
																		'<div class="delegate-more history"></div>'+								
															 		'</div>'+
															 	'</a>'+
															 	'<div class="mui-collapse-content">'+
															 		'<p class="trade-num">申请单号：'+data.history.data[i].appsheetserialno+'</p>'+
															 		'<p class="trade-date">交易日期/状态：'+data.history.data[i].transactiondate+'/'+data.history.data[i].status+'</p>'+
															 		'<p class="trade-other">基金代码：</p>'+													 		
															 	'</div>'+
															'</li>';
							var tradeOther = oLi.querySelector('.trade-other');
							if (data.history.data[i].paystatus) {
								tradeOther.innerHTML = '支付状态：'+data.history.data[i].paystatus;
							}else if (data.history.data[i].targetfundcode) {
								tradeOther.innerHTML = '目标基金代码：'+data.history.data[i].targetfundcode;
							}else if (data.history.data[i].defdividendmethod) {
								tradeOther.innerHTML = '分红方式：'+data.history.data[i].defdividendmethod;
							}else {
								tradeOther.innerHTML = "";
							}
							if (data.history.data[i].transactioncfmdate) {
								var addParent =	oLi.querySelector('.mui-collapse-content');
								var confirmDate = document.createElement('p');
								var confirmEdvol = document.createElement('p');
								var confirmedAmount = document.createElement('p');
								var charge = document.createElement('p');
								confirmDate.setAttribute('class', 'trade-other');
								confirmEdvol.setAttribute('class', 'trade-other');
								confirmedAmount.setAttribute('class', 'trade-other');
								charge.setAttribute('class', 'trade-other');
								confirmDate.innerHTML = '确认交易日期：'+data.history.data[i].transactioncfmdate;
								confirmEdvol.innerHTML = '确认份额：'+data.history.data[i].confirmedvol;
								confirmedAmount.innerHTML = '确认金额：'+data.history.data[i].confirmedamount;
								charge.innerHTML = '手续费：'+data.history.data[i].charge;
								addParent.appendChild(confirmDate);
								addParent.appendChild(confirmEdvol);
								addParent.appendChild(confirmedAmount);
								addParent.appendChild(charge); 
							}
							fragment.appendChild(oLi);
			 			}
			 		listWrap.innerHTML = "";
		 			listWrap.appendChild(fragment);						 		
			 		}
				},
			});
		}

  var active = document.getElementById('slider').querySelector('.mui-active');
console.dir(active.hash);
  switch (active.hash) {
    case '#item1mobile':
      firstPage();
      break;
    case '#item2mobile':
      page2();
      break;
    case '#item3mobile': 
      page3();
      break;
    case '#item4mobile':
      page4();
      break;
    default:
      break;
  }

	(function($) {
		$('.mui-scroll-wrapper').scroll({
			indicators: true //是否显示滚动条
		});		
		document.getElementById('slider').addEventListener('slide', function(e) {
console.log(e);
			switch (e.detail.slideNumber) {
				case 0:
					firstPage();
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
				case 3:
					var search = document.getElementById('search');
					search.addEventListener('tap',function() {
						var startDate = document.getElementById('begin').innerHTML,
							endDate = document.getElementById('end').innerHTML;
						if (startDate == '开始日期') {
							alert('请选择开始日期');
						}else if (endDate == '结束日期') {
							alert('请选择结束日期');
						}else if (parseInt(endDate.replace(/\-/g,""),10)-parseInt(startDate.replace(/\-/g,""),10) < 0) {
							alert('日期选择错误，请重新选择');
						}else {
console.log(startDate);
							page4(startDate,endDate);
						}
					});
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
};

