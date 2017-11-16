window.onload = function() {
	var _token = "";
	var _tano = "";
	var _public_key ="";
	beforeCast();
}

function beforeCast(){
	var fundcode = getUrlParam("fundcode");
	mui.ajax("/jijin/FixedInvestmentController/beforeFixedInvestment", {	
		data: {
			fundcode: fundcode
		},
		dataType: 'json',
		type: "GET",
		success: function(res) {
			if(res.code==0){
				if(res.data.riskmatching==0){
					byId("castSurelyTip").style.display="block";
					byId("beforeCastSure").onclick=function(){						
						byId("castSurelyTip").style.display="none";
						document.getElementsByClassName("castSurely-content")[0].style.opacity=1;
					}
				}else{					
					document.getElementsByClassName("castSurely-content")[0].style.opacity=1;
				}
				
				var fundinfo = res.data.fundinfo;
				byId("fundCodeDesc").innerHTML = fundinfo.fundcode;
				byId("fundNameDesc").innerHTML = fundinfo.fundname;
				byId("castSurelyNum").max = fundinfo.per_max_39;
				byId("castSurelyNum").min = fundinfo.per_min_39;
				byId("castSurelyNum").placeholder = fundinfo.per_min_39+"元起投";
				
				_token = res.data.token;
				_public_key = res.data.public_key;
				_tano = fundinfo.tano;
				
				var bankList = res.data.bank_info;
				pickerOp(bankList);
				castOp();
			}else{
				mui.alert('暂无数据',' ', function() {
					
				});
			}
		}
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
		
		if(val==""){
			mui.alert('请输入有效的金额',' ', function() {
				
			});
			return;
		}
		if(val<parseInt(min)){
			mui.alert('最低定投'+min+'元',' ', function() {
				
			});
		}else if(val>parseInt(max)){
			mui.alert('最高定投'+max+'元',' ', function() {
				
			});
		}else{			
			document.getElementsByClassName("pass-box")[0].style.display="block";
			
			var castNext = byId("castNext");
			castNext.parentNode.removeChild(castNext);
			
			var castSubmit = document.createElement("div");
			castSubmit.innerHTML = "确认";
			castSubmit.className = "mui-btn mui-btn-block buy-btn";
			castSubmit.id = "castSubmit";
			byId("castOpBox").appendChild(castSubmit);
			submitOp();
		}
		
	}
}

function numInpOp(){
	keyupMoney("castSurelyNum");
	/*var oldVal = "";
	byId("castSurelyNum").onkeyup=function(){
		var val = byId("castSurelyNum").value;
		var max = byId("castSurelyNum").max;
		var min = byId("castSurelyNum").min;
		if(val>=parseFloat(min)&&val<=parseFloat(max)){
			byId("castSurelyNum").style.color = "#222";
		}else{
			byId("castSurelyNum").style.color = "#ff0000";			
		}
		
		var re = /^\d+(?:\.\d{0,2})?$/;
		if(val!=""){			
			if(val.match(re)==null){
				byId("castSurelyNum").value = oldVal;
			}else{
				oldVal = val;
			}
		}
	}*/
}

function submitOp(){
	byId("castSubmit").onclick=function(){
		
		var encrypt = new JSEncrypt();
		encrypt.setPublicKey(_public_key);
		var encrypted = encrypt.encrypt(byId("passwd").value+_token);
		var param = {
			fundcode:byId("fundCodeDesc").innerHTML,
			token:encrypted,
			//channelid:byId("channelid").value,
			depositacct:byId("bankSelectVal").value,
			investamount:byId("castSurelyNum").value,
			tano:_tano,
			//moneyaccount:byId("moneyaccount").value,
			investcycle:byId("castSurelyCycleVal").value,
			investcyclevalue:byId("castSurelyDateVal").value
		}
		mui.ajax("/jijin/FixedInvestmentController/FixedInvestment", {	
			data: param,
			dataType: 'json',
			type: "POST",
			success: function(res) {
				if(res.code==0){
					mui.alert('定投计划设置成功',' ', function() {
						window.location.href="/application/views/jijin/trade/castSurelyDetail.html?fundcode="+byId("fundCodeDesc").innerHTML;
					});
				}
			}
		});
	}
}

function pickerOp(bankList){
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
			if(i==1){				
				li.className = "picker-item picker-item-active";
			}else{
				li.className = "picker-item";				
			}
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
			ul.appendChild(li);
		}
		byId("castSurelyDateShow").innerHTML="1日";
		byId("castSurelyDateVal").value="1";
	}else{		
		for ( var item in list) {
			var li = document.createElement("li");
			if(item==0){
				li.className = "picker-item picker-item-active";				
			}else{				
				li.className = "picker-item";
			}
			li.innerHTML = list[item].text;
			
			(function(item){
				li.onclick=function(){
					p.callback(list[item]);
					
					var cnodes = this.parentNode.childNodes;
					for(var cnode in cnodes){
						cnodes[cnode].className = "picker-item";
					}
					this.className = "picker-item picker-item-active";
				}
			})(item);
			ul.appendChild(li);
		}
		var showid=p.id.replace(/Picker/,"")+"Show";
		var valid=p.id.replace(/Picker/,"")+"Val";
		byId(showid).innerHTML=list[0].text;
		byId(valid).value=list[0].value;
		/*if(p.id=="bankSelectPicker"){
			byId("channelid").value=list[0].channelid;
			byId("moneyaccount").value=list[0].moneyaccount;			
		}*/
	}
	box.appendChild(ul);
	document.body.appendChild(box);
	
	byId("getNextPayDate").innerHTML=getNextPayDate();
}

function createCastSurelyDate(){
	var listWeek = [{text:"周一",value:"1"},{text:"周二",value:"2"},{text:"周三",value:"3"},{text:"周四",value:"4"},{text:"周五",value:"5"}];
	var cycleType = byId("castSurelyCycleVal").value||"";
	var castSurelyDateP={
		id:"castSurelyDatePicker"
	};
	if(cycleType=="2"){
		castSurelyDateP.list=[];
		castSurelyDateP.ismoon=true;
	}else{
		castSurelyDateP.list=listWeek;
		castSurelyDateP.ismoon=false;		
	};
	castSurelyDateP.callback=function(item){
		hidePicker("castSurelyDateShow","castSurelyDateVal","castSurelyDatePicker",item)
	};
	picker(castSurelyDateP);
}

function createCastSurelyCyle(){
	var listCycle = [{text:"每周",value:"0"},{text:"每两周",value:"1"},{text:"每月",value:"2"}];
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
		id:"castSurelyCyclePicker"
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
		id:"bankSelectPicker"
	}
	picker(castSurelyCycleP);
}

function showPicker(id,pickerId){
	var castSurelyCycle = byId(id);
	castSurelyCycle.onclick=function(){
		var iPicker=byId(pickerId);
		iPicker.className="i-picker i-picker-active";
		
		var div = document.createElement("div");
		div.className = "mui-backdrop";
		document.body.appendChild(div);
		div.onclick=function(){
			var div = document.getElementsByClassName("mui-backdrop")[0];
			div.parentNode.removeChild(div);
			
			var cnodes = document.getElementsByClassName("i-picker");
			for(var cnode in cnodes){
				cnodes[cnode].className = "i-picker";
			}
		}
	}
}

function hidePicker(showId,valId,pickerId,item){
	byId(showId).innerHTML = item.text;
	byId(valId).value = item.value;
	
	var div = document.getElementsByClassName("mui-backdrop")[0];
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
	
	if(castPayType=="2"){
		
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

var byId = function(id) {
	return document.getElementById(id);
};

function getUrlParam(name) { //获取url地址参数
	var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)"); //构造一个含有目标参数的正则表达式对象
	var r = window.location.search.substr(1).match(reg); //匹配目标参数
	if(r != null) return unescape(r[2]);
	return null; //返回参数值
}

var moneyRange = function(param){
	var val = param.value;
	var max = param.max;
	var min = param.min;
	
	if(val==""){
		mui.alert('请输入有效的金额',' ', function() {});
		return false;
	}
	if(val<parseInt(min)){
		mui.alert('最低定投'+min+'元',' ', function() {});
		return false;
	}
	if(val>parseInt(max)){
		mui.alert('最高定投'+max+'元',' ', function() {});
		return false
	}
	return true;
}
var keyupMoney = function(id){
	var oldVal = "";
	byId(id).onkeyup=function(){
		var val = byId(id).value;
		var max = byId(id).max;
		var min = byId(id).min;
		if(val>=parseFloat(min)&&val<=parseFloat(max)){
			byId(id).style.color = "#222";
		}else{
			byId(id).style.color = "#ff0000";			
		}
		
		var re = /^\d+(?:\.\d{0,2})?$/;
		if(val!=""){			
			if(val.match(re)==null){
				byId(id).value = oldVal;
			}else{
				oldVal = val;
			}
		}else{
			oldVal = null;
		}
	}
}