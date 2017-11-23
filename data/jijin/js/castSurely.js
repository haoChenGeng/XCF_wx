window.onload = function() {
	var _token = "";
	var _tano = "";
	var _public_key ="";
	var _isEdit = getUrlParam("isEdit");
	var _buyplanno = getUrlParam("buyplanno");
	
	beforeCast();
}

function beforeCast(){
	var fundcode = getUrlParam("fundcode");
	
	muiAjax("/jijin/FixedInvestmentController/beforeFixedInvestment",{fundcode: fundcode},"GET",function(res){

		if(getUrlParam("isEdit")==1){
			byClass("castSurely-content").style.opacity=1;
		}else{					
			if(res.data.riskmatching==0){
				mui.ajax("/application/views/jijin/trade/riskTip/riskTip.html",{
					success:function(rest){
						byId("riskmatching0").innerHTML=rest;
						byId("riskmatching0").className="castSurelyTip";
						byId("ensureSubmit").innerHTML = "我已悉知并确认购买";
						byId("custRisk").innerHTML = res.data.my_risklevel;
						byId("ensureSubmit").onclick=function(){						
							remove(byId("riskmatching0"));
							byClass("castSurely-content").style.opacity=1;
						}
					}
				})
			}else if(res.data.riskmatching==2){	
				mui.ajax("/application/views/jijin/trade/riskTip/riskTip.html",{
					success:function(rest){
						byId("riskmatching2").innerHTML = rest;
						byId("riskmatching2").className="castSurelyTip";
						byId("ensureSubmit").innerHTML = "重新测评";
						byId("custRisk").innerHTML = res.data.my_risklevel;
						byId("ensureSubmit").href="/jijin/Risk_assessment";
					}
				})
				
			}else if(res.data.riskmatching==3){
				mui.ajax("/application/views/jijin/trade/riskTip/riskTestTip.html",{
					success:function(rest){
						byId("riskmatching3").innerHTML = rest;
						byId("riskmatching3").className="castSurelyTip";
						document.getElementById('sub').addEventListener('click', function() {
							console.log("a");
							document.getElementById('login_form').submit();
						});
					}
				})
			}else{
				byClass("castSurely-content").style.opacity=1;					
			}
		}
		
		var fundinfo = res.data.fundinfo;
		byId("fundCodeDesc").innerHTML = fundinfo.fundcode;
		byId("fundNameDesc").innerHTML = fundinfo.fundname;
		byId("castSurelyNum").max = fundinfo.per_max_39;
		byId("castSurelyNum").min = fundinfo.per_min_39;
		byId("castSurelyNum").placeholder = fundinfo.per_min_39+"元起投";
		
		_token = res.data.token;
		if(getUrlParam("isEdit")!=1){					
			_public_key = res.data.public_key;
		}
		_tano = fundinfo.tano;
		
		var bankList = res.data.bank_info;
		pickerOp(bankList);
		castOp();
	});
}

function castOp(){
	castNextOp();
	numInpOp();
}

function castNextOp(){
	byId("castNext").onclick=function(){
		var val = byId("castSurelyNum").value;
		var max = byId("castSurelyNum").max;
		var min = byId("castSurelyNum").min;
		
		if(!moneyRange({
			val:val,
			max:max,
			min:min
		})) return;
		
		byClass("pass-box").style.display="block";
		
		var castNext = byId("castNext");
		castNext.parentNode.removeChild(castNext);
		
		var castSubmit = document.createElement("div");
		castSubmit.innerHTML = "确认";
		castSubmit.className = "mui-btn mui-btn-block buy-btn";
		castSubmit.id = "castSubmit";			
		byId("castOpBox").appendChild(castSubmit);
		byId("castSurelyNum").disabled=true;
		
		submitOp();
		
	}
}

function numInpOp(){
	keyupMoney("castSurelyNum");
}

function submitOp(){
	byId("castSubmit").onclick=function(){
		
		/*var encrypt = new JSEncrypt();
		encrypt.setPublicKey(_public_key);
		var encrypted = encrypt.encrypt(byId("passwd").value+_token);*/
		
		var param = {
			fundcode:byId("fundCodeDesc").innerHTML,
			//channelid:byId("channelid").value,
			depositacct:byId("bankSelectVal").value,
			investamount:byId("castSurelyNum").value,
			
			//moneyaccount:byId("moneyaccount").value,
			investcycle:byId("castSurelyCycleVal").value,
			investcyclevalue:byId("castSurelyDateVal").value
		}
		
		if(getUrlParam("isEdit")==1){
			var encrypted = encryptPass(_public_key,byId("passwd").value,"");
			param.buyplanno=getUrlParam("buyplanno");
			param.tpasswd=encrypted;
			mui.ajax("/jijin/FixedInvestmentController/FixedInvestmentUpdate", {	
				data: param,
				dataType: 'json',
				type: "POST",
				success: function(res) {
					if(res.code==0){
						mui.alert('定投计划修改成功',' ', function() {
							window.location.href="/application/views/jijin/trade/castSurelyDetail.html?buyplanno="+res.data[0].buyplanno;
						});
					}
				}
			});
		}else{
			var encrypted = encryptPass(_public_key,byId("passwd").value,_token);
			param.tano=_tano;
			param.token=encrypted,
			mui.ajax("/jijin/FixedInvestmentController/FixedInvestment", {	
				data: param,
				dataType: 'json',
				type: "POST",
				success: function(res) {
					if(res.code==0){
						mui.alert('定投计划设置成功',' ', function() {
							window.location.href="/application/views/jijin/trade/castSurelyDetail.html?buyplanno="+res.data[0].buyplanno;
							//window.location.href="/jijin/Jz_my?activePage=fixed";
						});
					}
				}
			});
		}
		
	}
}

function pickerOp(bankList){
	if(getUrlParam("isEdit")==1){
		editCast();
	}else{		
		//生成定投周期
		createCastSurelyCyle();
		//展示定投周期
		showPicker("castSurelyCycle","castSurelyCyclePicker");
		
		//生成定投日
		createCastSurelyDate();
		//展示定投日
		showPicker("castSurelyDate","castSurelyDatePicker");
		
		//生成银行列表
		createBankSelect(bankList);
		//展示银行列表
		showPicker("bankSelect","bankSelectPicker");
	}
}

function picker(p){
	var list = p.list;
	var box = document.createElement("div");
	box.className = "i-picker";
	box.id = p.id;
	
	var ul = document.createElement("ul");
	ul.className = "picker-list";
	if(p.ismoon){		
		for ( var i=1;i<=28;i++) {
			var li = document.createElement("li");
			/*if(i==1){				
				li.className = "picker-item picker-item-active";
			}else{
				li.className = "picker-item";				
			}*/
			li.innerHTML = i+"日";
			
			(function(i){
				li.onclick=function(){
					p.callback({text:i+"日",value:i});
					
					var cnodes = this.parentNode.childNodes;
					for(var cnode in cnodes){
						cnodes[cnode].className = "picker-item";
					}
					this.className = "picker-item picker-item-active";
				}
			})(i);
			
			if(i==parseInt(p.active, 10)){	
				byId("castSurelyDateShow").innerHTML=i+"日";
				byId("castSurelyDateVal").value=i;
				li.className = "picker-item picker-item-active";
			}else{
				li.className = "picker-item";	
			}
			ul.appendChild(li);
		}
	}else{		
		for ( var item in list) {
			var li = document.createElement("li");
			/*if(item==0){
				li.className = "picker-item picker-item-active";				
			}else{				
				li.className = "picker-item";
			}*/
			li.innerHTML = list[item].text;
			
			(function(item){
				li.onclick = function(){
					p.callback(list[item]);
					
					var cnodes = this.parentNode.childNodes;
					for(var cnode in cnodes){
						cnodes[cnode].className = "picker-item";
					}
					this.className = "picker-item picker-item-active";
				}
			})(item);
			
			if(list[item].value==p.active){				
				var showid=p.id.replace(/Picker/,"")+"Show";
				var valid=p.id.replace(/Picker/,"")+"Val";
				byId(showid).innerHTML=list[item].text;
				byId(valid).value=list[item].value
				li.className = "picker-item picker-item-active";
			}else{
				li.className = "picker-item";
			}
			
			if(p.active=="active"&&item==0){
				var showid=p.id.replace(/Picker/,"")+"Show";
				var valid=p.id.replace(/Picker/,"")+"Val";
				byId(showid).innerHTML=list[item].text;
				byId(valid).value=list[item].value
				li.className = "picker-item picker-item-active";
			}
			
			ul.appendChild(li);
		}
		if(p.id=="bankSelectPicker"){
			
			/*byId("channelid").value=list[0].channelid;
			byId("moneyaccount").value=list[0].moneyaccount;*/			
		}
	}
	box.appendChild(ul);
	document.body.appendChild(box);
	
	if(getUrlParam("isEdit")!=1){		
		byId("getNextPayDate").innerHTML=getNextPayDate();
	}
}

function createCastSurelyDate(active){
	var listWeek = [{text:"周一",value:"1"},{text:"周二",value:"2"},{text:"周三",value:"3"},{text:"周四",value:"4"},{text:"周五",value:"5"}];
	var cycleType = byId("castSurelyCycleVal").value||"";
	var castSurelyDateP={
		id:"castSurelyDatePicker"
	};
	if(cycleType=="0"){
		castSurelyDateP.list=[];
		castSurelyDateP.ismoon=true;
		castSurelyDateP.active=active||"01";
	}else{
		castSurelyDateP.list=listWeek;
		castSurelyDateP.ismoon=false;		
		castSurelyDateP.active=active||"1";
	};
	castSurelyDateP.callback=function(item){
		hidePicker("castSurelyDateShow","castSurelyDateVal","castSurelyDatePicker",item)
	};
	picker(castSurelyDateP);
}

function createCastSurelyCyle(active){
	var active = active||"1";
	var listCycle = [{text:"每周",value:"1"},{text:"每两周",value:"2"},{text:"每月",value:"0"}];
	var castSurelyCycleP={
		list:listCycle,
		callback:function(item){
			var oldval = byId("castSurelyCycleVal").value;
			hidePicker("castSurelyCycleShow","castSurelyCycleVal","castSurelyCyclePicker",item);
			
			if(oldval!=item.value){
				var iPicker=byId("castSurelyDatePicker");
				iPicker.parentNode.removeChild(iPicker);
				//重新生成定投日
				createCastSurelyDate();
			}
		},
		ismoon:false,
		id:"castSurelyCyclePicker",
		active:active
	}
	picker(castSurelyCycleP);
}

function createBankSelect(bankList){
	var listCycle = getBankList(bankList);
	var castSurelyCycleP={
		list:listCycle,
		callback:function(item){
			hidePicker("bankSelectShow","bankSelectVal","bankSelectPicker",item);
			/*byId("channelid").value = item.channelid;
			byId("moneyaccount").value = item.moneyaccount;*/
		},
		ismoon:false,
		id:"bankSelectPicker",
		active:"active"
	}
	picker(castSurelyCycleP);
}

function showPicker(id,pickerId){
	if(id=="bankSelect"&&getUrlParam("isEdit")==1) return;
	var castSurelyCycle = byId(id);
	castSurelyCycle.onclick=function(){
		var iPicker=byId(pickerId);
		iPicker.className="i-picker i-picker-active";
		
		var div = document.createElement("div");
		div.className = "mui-backdrop";
		document.body.appendChild(div);
		div.onclick=function(){
			var div = byClass("mui-backdrop");
			div.parentNode.removeChild(div);
			
			var cnodes = byClass("i-picker");
			for(var cnode in cnodes){
				cnodes[cnode].className = "i-picker";
			}
		}
	}
}

function hidePicker(showId,valId,pickerId,item){
	byId(showId).innerHTML = item.text;
	byId(valId).value = item.value;
	
	var div = byClass("mui-backdrop");
	div.parentNode.removeChild(div); 
	var iPicker=byId(pickerId);
	iPicker.className="i-picker";
	
	byId("getNextPayDate").innerHTML=getNextPayDate();
}

function getNextPayDate(){
	var castPayType = byId("castSurelyCycleVal").value;
	var castPayDate = parseInt(byId("castSurelyDateVal").value, 10);
	var date = new Date();
	
	var pYear = date.getFullYear();
	var pMoon = date.getMonth()+1;
	var pDate = date.getDate();
	var cDate = date.getDate();
	
	if(castPayType=="0"){
		
		if(cDate>=castPayDate){
			if(pMoon == 12){
				pMoon = 1;
				pYear +=1;
			}else{
				pMoon +=1;
			}
		}
	
		pDate = castPayDate>=10?castPayDate:"0"+castPayDate;
		pMoon = pMoon>=10?pMoon:"0"+pMoon;
		return pYear+"-"+pMoon+"-"+pDate;
	}else{
		var cDay = date.getDay();
		var disDate = 0;
		if(cDay>=castPayDate){
			disDate = 7-cDay+castPayDate;
		}else{
			disDate = castPayDate - cDay;
		}
		return addDate(date,disDate);
	}
}

function addDate(date,days){
	var d=new Date(date); 
	d.setDate(d.getDate()+days); 
	var month=d.getMonth()+1; 
	var day = d.getDate(); 
	if(month<10){ 
		month = "0"+month; 
	} 
	if(day<10){ 
		day = "0"+day; 
	} 
	var val = d.getFullYear()+"-"+month+"-"+day; 
	return val; 
}

function getBankList(list){
	var blist = new Array();
	for ( var i in list) {
		var obj = {};
		var item = list[i];
		var numLength = item.depositacct.length;
		var lastNum = item.depositacct.substring(numLength-3,numLength);
		obj.text = item.channelname + "(尾号"+lastNum+")";
		obj.value = item.depositacct;
		/*obj.channelid = item.channelid;
		obj.moneyaccount = item.moneyaccount;*/
		blist.push(obj);
	}
	return blist;
}


function editCast(){
	//添加loading
	loadingShow();
	var buyplanno = getUrlParam("buyplanno");
	
	muiAjax("/jijin/FixedInvestmentController/FixedInvestmentQuery",{buyplanno:buyplanno},"GET",function(res){
		var castList = res.data.fixed;
		_public_key = res.data.public_key;
		if(castList.length==1){
			var item = castList[0];
			var numLength = item.depositacct.length;
			var lastNum = item.depositacct.substring(numLength-3,numLength);
			
			byId("castSurelyNum").value=item.continueinvestamount;
			byId("bankSelectShow").innerHTML=item.channelname+'（尾号'+lastNum+'）';
			byId("bankSelectVal").value=item.depositacct;
			byId("getNextPayDate").innerHTML=item.nextinvestdate;
			
			//生成定投周期
			createCastSurelyCyle(item.investcycle);
			//展示定投周期
			showPicker("castSurelyCycle","castSurelyCyclePicker");
			
			//生成定投日
			createCastSurelyDate(item.investcyclevalue);
			//展示定投日
			showPicker("castSurelyDate","castSurelyDatePicker");
			//移除loading
			loadingRemove();
		}
	});
}