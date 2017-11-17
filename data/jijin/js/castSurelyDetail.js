window.onload = function() {
	var _public_key = "";
	var _buyplanno = "";
	var _depositacct = "";
	var _fundcode = "";
	getCastDetail();
}

function getCastDetail(){
	var buyplanno = getUrlParam("buyplanno");
	_buyplanno = buyplanno;
	mui.ajax("/jijin/FixedInvestmentController/FixedInvestmentQuery",{
		data:{
			buyplanno:buyplanno
		},
		dataType: 'json',
		type: "GET",
		success:function(res){
			if(res.code==0){
				var castList = res.data.fixed;
				if(castList.length==1){
					var item = castList[0];
					var numLength = item.depositacct.length;
					var lastNum = item.depositacct.substring(numLength-3,numLength);
					
					byId("fundName").innerHTML=item.fundname+'（'+item.fundcode+'）';
					byId("fundName").title=item.fundname+'（'+item.fundcode+'）';
					byId("debitWay").innerHTML=item.channelname+'（尾号'+lastNum+'）';
					byId("castStatue").innerHTML=item.status=="N"?"正常":item.status=="C"?"终止":"未知";;
					byId("castType").innerHTML=item.periodremark+'定投：'+item.continueinvestamount+'元';
					byId("nextDebitTime").innerHTML=item.nextinvestdate;
					byId("castTotal").innerHTML=item.totalsuccamount;
					byId("castTimes").innerHTML=item.totalexecutetimes;
					
					_depositacct = item.depositacct;
					_fundcode =item.fundcode;
				}
				_public_key = res.data.public_key;
				
				var order = res.data.order;
				var ohtml = "";
				for (var i in order){
					var record = order[i];
					ohtml +='<li>\
						<span class="record-time">'+record.operdate+'</span>\
						<span class="record-num">'+record.applicationamount+'元</span>\
						<span class="record-result">'+record.status+'</span>\
					</li>';
				}
				byId("orderList").innerHTML = ohtml||"<div class='norecord'>暂无记录</div>";
				
				var status = item.status;
				if(status=="D"||status=="C"){
					/*var btn = document.createElement("div");
					btn.innerHTML = "定投计划已终止";
					btn.className = "mui-btn mui-btn-block buy-btn disabled";
					byId("castSurelyBtn").appendChild(btn);*/
					castCatch("定投计划已终止");
				}else if(status=="N"){
					upCast();
					stopCast();	
				}else{
					/*var btn = document.createElement("div");
					btn.innerHTML = "定投计划状态异常";
					btn.className = "mui-btn mui-btn-block buy-btn disabled";
					byId("castSurelyBtn").appendChild(btn);*/
					castCatch("定投计划状态异常");
				}
				
				
			}
		}//success end
	});
}

function stopCast(){
	var stopBtn = document.createElement("div");
	stopBtn.innerHTML = "停止";
	stopBtn.className = "mui-btn mui-btn-block buy-btn";
	
	stopBtn.onclick=function(){
		var btnArray = ['取消', '确定'];
		mui.confirm( '','确定要终止定投吗？提交终止后将不能恢复执行。', btnArray, function(e) {
			if (e.index == 1) {//true
				var btnArray2 = ['取消', '确定'];
				mui.prompt('', '请输入交易密码', '请输入交易密码', btnArray2, function(e) {
					if (e.index == 1) {
						/*var encrypted = encryptPass(_public_key,e.value,"");
						mui.ajax("/jijin/FixedInvestmentController/FixedInvestmentEnd",{
							data:{
								tpasswd:encrypted,
								buyplanno:_buyplanno,
								depositacct:_depositacct
							},
							dataType: 'json',
							type: "GET",
							success:function(res){
								castCatch("定投计划已终止");
							}
						});*/
						castCatch("定投计划已终止");
					} else {}//取消输入密码
				})
			} else {}//取消终止
		});
	}
	
	byId("castSurelyBtn").appendChild(stopBtn);
}

function upCast(){
	var upBtn = document.createElement("div");
	upBtn.innerHTML = "修改";
	upBtn.className = "mui-btn mui-btn-block buy-btn";
	upBtn.onclick=function(){
		window.location.href="/application/views/jijin/trade/castSurely.html?fundcode="+_fundcode+"&buyplanno="+_buyplanno+"&isEdit=1";
	}

	byId("castSurelyBtn").appendChild(upBtn);
}

function castCatch(text){
	var btn = document.createElement("div");
	btn.innerHTML = text;
	btn.className = "mui-btn mui-btn-block buy-btn disabled";
	byId("castSurelyBtn").innerHTML="";
	byId("castSurelyBtn").appendChild(btn);
}
/*function getStatus(statu){
	var html = "";
	switch (statu) {
	case "00":
		html = "待复核";
		break;
	case "01":
		html = "待勾兑";
		break;
	case "02":
		html = "待报";
		break;
	case "04":
		html = "";
		break;
	case "01":
		html = "待复核";
		break;
	case "01":
		html = "待复核";
		break;
	case "01":
		html = "待复核";
		break;
	case "01":
		html = "待复核";
		break;

	default:
		break;
	}
}*/