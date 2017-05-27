mui.init();
	window.onload = function() {
		var type = document.getElementById('info').querySelector('.mui-active').dataset.type;
		
		getFundList(type);

		function getFundList(type) {
			mui.ajax('http://localhost:8009/systemsetup/PrivateFund/fund_list/'+type,{
				data: {},
				dataType: 'json',
				type: 'get',
				beforeSend: function() {
					
				},
				success: function(res) {
// console.log(res);
					renderList(res);					
				},
				error: function(res) {
					alert('查询基金失败！');
				}
			});
		}

		function consultFund(id,name,cust,phone) {
			mui.ajax('http://localhost:8009//systemsetup/OrderInfo/order_add',{
				data: {
					fundid: id,
					fundname: name,
					custname: cust,
					custphone: phone
				},
				dataType: 'json',
				type: 'post',
				success: function(res) {
					console.log(res);
					if (res.code === '0000') {
						mui.alert(res.msg);
					}else {
						mui.alert(res.msg);
					}
				},
				error: function() {
					alert('预约失败，请联系客服!');
				}
			});
		}

		function renderList(data) {
// console.log(data);
			var content = document.getElementById('info').querySelector('.mui-active');
			if (!data) {
				content.querySelector('.mui-scroll').innerHTML = '<p class="fund-list-error"><span>暂无基金</span></p>';
			}else {
				var oL = document.createElement('ul');
				oL.classList.add('mui-table-view');
				for (var i = 0; i < data.length; i++) {
					var labelArr = data[i].label.split('、');
					data[i].label = '<span>' + labelArr.join('</span><span>') + '</span>';
// console.log(data[i].label);
					var oLi = document.createElement('li');
					oLi.classList.add('mui-table-view-cell');
					oLi.innerHTML = '<div class="mui-media-body info-list">'+
														'<div class="info-list-left">'+
															'<p class="info-left-adv">'+data[i].strategy+'</p>'+
															'<p class="info-left-title">'+data[i].advantage+'</p>'+
															'<button class="info-order">'+
																'预约咨询'+
															'</button>'+
														'</div>'+
														'<div class="info-list-right">'+
															'<p class="info-right-title" data-id="'+data[i].id+'">'+data[i].name+'</p>'+
															'<p class="info-tag">'+data[i].label+'</p>'+
															'<p class="info-desc"><span class="mui-icon mui-icon-chatboxes-filled"></span>'+data[i].evaluate+'</p>'+
														'</div>'+
													'</div>';
					oL.appendChild(oLi);					
				}
				content.querySelector('.mui-scroll').innerHTML = '';
				content.querySelector('.mui-scroll').appendChild(oL);
			}
		}

		(function($) {
				$('.mui-scroll-wrapper').scroll({
					indicators: true //是否显示滚动条
				});
				
				var item2 = document.getElementById('item2mobile');
				var item3 = document.getElementById('item3mobile');
				var item4 = document.getElementById('item4mobile');
				var item5 = document.getElementById('item5mobile');
				document.getElementById('slider').addEventListener('slide', function(e) {
					switch (e.detail.slideNumber + 1) {
						case 1:
							getFundList(1);
							break;
						case 2: 
							if (item2.querySelector('.mui-loading')) {
								getFundList(2);
							}
							break;
						case 3:
							if (item3.querySelector('.mui-loading')) {
								getFundList(3);
							}
							break;
						case 4: 
							if (item4.querySelector('.mui-loading')) {
								getFundList(4);
							}
							break;
						case 5: 
							if (item5.querySelector('.mui-loading')) {
								getFundList(5);
							}
						break;
						default:
							// statements_def
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

	(function() {
		var name;
		var id;
		var mask = mui.createMask();
		document.getElementById('info').addEventListener('tap',function(e) {
			if (e.target.innerHTML.trim() == '预约咨询') {
				var a = e.target.parentNode.nextSibling.firstElementChild;
				name = a.innerHTML;
				id = a.dataset.id;
				mask.show();
				document.getElementById('order').style.display = 'block';
			}else {
				document.getElementById('order').style.display = 'none';
				mask.close();
			}
		});
		document.getElementById('cancel').addEventListener('click', function(e) {
			document.getElementById('order').style.display = 'none';
			mask.close();
		});
		document.getElementById('confirm').addEventListener('tap', function(e) {
			var custName = document.getElementById('custName').value;
			var custPhone = document.getElementById('custPhone').value;
			if (custName === '' || custPhone === '') {
				alert('请填写姓名和电话，谢谢！');
			}else {
				mask.close();
				document.getElementById('order').style.display = 'none';
				consultFund(id,name,custName,custPhone);				
			}
		});		
	})();

};