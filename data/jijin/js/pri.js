mui.init();

function getFundList(type) {
	mui.ajax('/admin/PrivateFund/fund_list/'+type,{
		data: {},
		dataType: 'json',
		type: 'get',
		beforeSend: function() {
			
		},
		success: function(res) {
			renderList(res);
		},
		error: function(res) {
			alert('查询基金失败！');
		}
	});
}

(function getLoginStatus() {
	mui.ajax('/Pf_assessment/accessmentstatus',{
		data: {},
		dataType: 'json',
		type: 'get',
		async: false,
		success: function(res) {
			if (res.code === 0) {
				needModal(res.data);
			}else {
				alert('读取信息错误！');
			}
		},
		error: function(xhr) {
			alert('网络错误!');
		}
	});
})();

function needModal(data) {
	var prompt = '根据《私募投资基金募集行为管理办法》及《证券期货投资者适当性管理办法》规定，小牛投资咨询只向特定的合格投资者宣传推介相关私募投资产品。 阁下如有意向进行私募类相关产品投资且满足《私募投资基金管理募集行为管理办法》关于“合格投资者”之标准规定，具备相应风险识别能力和风险承担能力，愿意完成投资者风险测评，请阁下详细阅读本提示，完成注册投资者风险测评，方可获得小牛投资咨询私募投资基金产品宣传推介服务';
	var btn = ['暂不测评','立即测评'];
	var riskTip = '您还未完成风险承受能力评测,请点击按钮开始进行风险承受能力评测,谢谢!';
	var type = document.getElementById('info').querySelector('.mui-active').dataset.type;

	if (data.readpfmsg === 0) {
		mui.alert(prompt,'温馨提示', function() {
		  updataLoginStatus();
			mui.confirm(riskTip,'温馨提示',btn,function(e) {
				if (e.index == 1) {
					window.location.href = '/application/views/privateFund/pfRiskTest.html';
				}else {
					window.location.href = '/index.php';
				}
			});		  
		});
	}else	if(data.readpfmsg === 1 && data.pflevel === 0) {
		mui.confirm(riskTip,'温馨提示',btn,function(e) {
			if (e.index == 1) {
				window.location.href = '/application/views/privateFund/pfRiskTest.html';
			}else {
				window.location.href = '/index.php';
			}
		});
	}else if(data.readpfmsg === 1 && data.pflevel === 1) {
		getFundList(type);
	}else {
		alert('系统错误！');
	}
}

function updataLoginStatus() {
	mui.ajax('/Pf_assessment/updateReadPfMsg',{
		data: {
			donereadmsg: 1
		},
		dataType: 'json',
		type: 'post',
		success: function(res) {
			if (res.code !== 0) {
				alert('更新错误，请稍后重试!');
			}
		},
		error: function(res) {
			alert('请求错误!');
		}
	});
}

function consultFund(id,name,cust,phone) {
	mui.ajax('/admin/OrderInfo/order_add',{
		data: {
			fundid: id,
			fundname: name,
			custname: cust,
			custphone: phone
		},
		dataType: 'json',
		type: 'post',
		success: function(res) {
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
	var content = document.getElementById('info').querySelector('.mui-active');
	if (!data) {
		content.querySelector('.mui-scroll').innerHTML = '<p class="fund-list-error"><span>暂无基金</span></p>';
	}else {
		var oL = document.createElement('ul');
		oL.classList.add('mui-table-view');
		for (var i = 0; i < data.length; i++) {
			var labelArr = data[i].label.split('、');
			data[i].label = '<span>' + labelArr.join('</span><span>') + '</span>';
			var oLi = document.createElement('li');
			oLi.classList.add('mui-table-view-cell');
			oLi.innerHTML = '<div class="mui-media-body info-list">'+
												'<div class="info-list-left">'+
													'<p class="info-left-adv order">'+data[i].strategy+'</p>'+
													'<p class="info-left-title order">'+data[i].advantage+'</p>'+
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
	var order = document.getElementById('order');
	var mask = mui.createMask(function() {
		order.style.display = 'none';
		
	});
	document.getElementById('info').addEventListener('tap',function(e) {
		if (e.target.classList.contains('info-list-left')) {
			var a = e.target.nextSibling.firstElementChild;
			name = a.innerHTML;
			id = a.dataset.id;
			mask.show();
			order.style.display = 'block';
		}else if (e.target.innerHTML.trim() == '预约咨询' || e.target.classList.contains('order')) {
			var a = e.target.parentNode.nextSibling.firstElementChild;
			name = a.innerHTML;
			id = a.dataset.id;
			mask.show();
			order.style.display = 'block';
		}else {
			order.style.display = 'none';
			mask.close();
		}
	});
	document.getElementById('cancel').addEventListener('tap', function(e) {
		order.style.display = 'none';
		mask.close();
	});
	document.getElementById('confirm').addEventListener('tap', function(e) {
		var custName = document.getElementById('custName').value;
		var custPhone = document.getElementById('custPhone').value;
		var validate = /^[1][34578][0-9]{9}$/;
		if (custName === '' || custPhone === '') {
			alert('请填写姓名和电话，谢谢！');
		}else if (!validate.test(custPhone)) {
			alert('请正确填写电话!');
		}else {
			mask.close();
			order.style.display = 'none';
			consultFund(id,name,custName,custPhone);				
		}
	});		
})();

