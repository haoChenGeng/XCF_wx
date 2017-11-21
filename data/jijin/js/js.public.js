var byId = function(id) {
	return document.getElementById(id);
};

var byClass = function(classname) {
	var dom = document.getElementsByClassName(classname);
	return dom.length>1?dom:dom[0];
};

var getUrlParam= function(name) { //获取url地址参数
	var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)"); //构造一个含有目标参数的正则表达式对象
	var r = window.location.search.substr(1).match(reg); //匹配目标参数
	if(r != null) return unescape(r[2]);
	return null; //返回参数值
}

//校验金额范围
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

//keyup 校验输入金额
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

var loadingShow=function(){
	var div = document.createElement("div");
	div.className = "mui-backdrop loading";
	div.id="loadId";
	div.innerHTML = '<img src="/data/jijin/img/loading.gif">';
	document.body.appendChild(div);
}
var loadingRemove=function(){
	remove(byId("loadId"));
}
//移除节点
var remove = function(dom){
	dom.parentNode.removeChild(dom);
}

var encryptPass=function(public_key,pass,token){
	console.log(public_key+"-"+pass);
	var encrypt = new JSEncrypt();
	encrypt.setPublicKey(public_key);
	return encrypted = encrypt.encrypt(pass+token);
}

var stopBubble=function(e) { 
	if(e && e.stopPropagation) { //非IE 
    	e.stopPropagation(); 
	} else { //IE 
		window.event.cancelBubble = true; 
	} 
} 

var muiAjax = function(url, params,type, success, error) {
	mui.ajax(url,{
		data:params,
		dataType: 'json',
		type: type,
		success:function(res){
			if (res.code == 0) {
				success(res);
			}
			else if(res.code == -1){
				console.log("aa");
				if (error) {
					error(res);
				} else {
					errorOut();
				}
			}
			else if(res.code == -2){
				mui.alert('请开户后后在进行操作', '您未开户', function() {
					window.location.href="/jijin/Jz_account/register";
				});
			}
		}
	});
}
function errorOut(){
	mui.alert('请登陆后在进行操作', '您未登陆', function() {
		window.location.href="/User/logout";
	});
}