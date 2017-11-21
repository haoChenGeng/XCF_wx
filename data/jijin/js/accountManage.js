window.onload=function(){
	menuOp();
	getInfo();
}

function menuOp(){
	document.getElementById('buy_fund').addEventListener('tap', function() {
		//打开基金购买页		
		mui.openWindow({
			url: '/application/views/jijin/buy_fund.html',
			id: 'buy_fund'
		});
	});
	document.getElementById('my').addEventListener('tap', function() {
		//打开我的基金页
		mui.openWindow({
			url: '/jijin/Jz_my',
			id: 'my'
        });
    });
}

function getInfo(){
	mui.ajax('/jijin/Jz_my/getMyPageData/risk_test', {
        data: {},
        dataType: 'json',
        type: 'post',
        timeout: 30 * 1000,
        success: function(data) {
        	if (data.code == '9999') {
        		document.getElementById('scroll3').innerHTML = '<a class="fund-list-error" href="/user/login" id="errorMsg">' + data.msg + '</a>';
        	} else if (data.code == '8888') {
        		var fundList = document.getElementById('scroll3');
            fundList.innerHTML = '<a class="fund-list-error" href="' + '/jijin/Jz_account/register?next_url=jz_my&myPageOper=account" id="errorMsg">' + data.msg + '</a>';
        	} else {
        		var risk = document.getElementById('risk_result');
        		risk.innerHTML = '风险测试[' + data.custrisk + ':' + data.custriskname + ']';
        	}
        },
        error: function() {
        	alert('查询失败，请稍后重试！');
        }
	});
}