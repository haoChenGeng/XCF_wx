window.onload = function() {
	var byId = function(id) {
		return document.getElementById(id);
	};
	function getUrlParam(name) {		//获取url地址参数
		var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)"); //构造一个含有目标参数的正则表达式对象
		var r = window.location.search.substr(1).match(reg); //匹配目标参数
		if(r != null) return unescape(r[2]);
		return null; //返回参数值
	}
	var fundcode = getUrlParam("fundcode");		//获取基金代码
	var oBtnP = document.getElementById("worthChart");
	var oBtn = oBtnP.children;
	for(var i = 0; i < oBtn.length; i++) {
		oBtn[i].addEventListener("tap", function() {
			this.setAttribute("class", "btn-active").nextElementSibling.removeAttribute("class", "btn-active");
		});
	}
		
//			extras:{
//			fundcode: fundcode	
//			}
//	function loadPages(url) { //用于页面跳转
//		mui.openWindow({
//			url: url,
//			id: url
//		});
//	}

/*	var oLi = document.getElementById("fund-list"); //获取绑定事件对象
	var oLiChlid = oLi.childNodes;
	for(var i = 0; i < oLiChlid.length; i++) {
		oLiChlid[i].addEventListener("tap", function() {
			var strA = this.children[0].innerHTML;
			if(strA == "基金概括") { //跳转到基金概括
				var url = "summ/"
				loadPages(url);
			} else if(strA == "交易须知") { //跳转到交易须知							
				var url = "tradelist/"
				loadPages(url);
			}
		});
	}*/
	
	mui.ajax("/jijin/Jz_fund/fundDetail", {		//基金详情信息
		data: {
			fundcode: fundcode
		},
		dataType: 'json',
		type: "GET",
		success: function(res) {
			var fundlist = res.fundlist //基金详情
			var stars = parseInt(fundlist.star);
			var oTitle = document.getElementById("fundname");
			oTitle.innerHTML = fundlist.fundname + "(" + fundlist.fundcode + ")";
			document.getElementsByClassName("date")[0].innerHTML = fundlist.navdate //日期
			document.getElementsByClassName("dayUp")[0].innerHTML = res.field1 //日涨跌幅
			document.getElementsByClassName("dayGrow")[0].innerHTML = fundlist.growth_day + "%"; //日涨跌幅
			document.getElementsByClassName("newVal")[0].innerHTML = res.field2 //最新净值
			document.getElementById("fundIndex").innerHTML = fundlist.fundtype; //基金类型
			document.getElementsByClassName("fund-msg")[0].innerHTML = fundlist.risklevel; //风险
			document.getElementsByClassName("dayNew")[0].innerHTML = fundlist.nav
			var starParent = document.getElementsByClassName("level");
			if(stars <10){
				for(var i = 0; i < stars; i++) { //评级							
					var oStar = document.createElement("span");
					oStar.className = "mui-icon mui-icon-star active-star";
				}				
			}
			else{
				var oStar = document.createElement("span");
				oStar.style.fontSize ="12px";
				oStar.innerHTML ="(未评级)"
			}
			starParent[0].appendChild(oStar);

			if(res.purchasetype == "申购") {
				var p = document.createElement("p");
				p.className = "m-footer-content buySubmit";
				p.innerHTML = "立即申购"
				var oParent = document.getElementById("buy_fund");
				oParent.appendChild(p)
			}
		}
	});
	
	var shXdata =[],shYdata=[],prdXdata=[],prdYdata=[];
	function queryDetail(dIndex){	//基金详情信息
		var dy =dIndex;
		var oTab =document.getElementById("historyCont");
		mui.ajax("/jijin/jz_fund/getFundCurve", {
			data: {
				fundCode: fundcode
			},
			dataType: "json",
			type: "GET",
			success: function(res){
				var data =res.hs_data;		//沪深指数
				var prdData =res.data;		//产品指数
				if(res.code ==0){					
					if(data){
						for (var i=0 ;i<7; i++) {
							var newEle =document.createElement("div");
							newEle.className ="mui-row";
							var divEle_1 = document.createElement("div");
							var divEle_2 = document.createElement("div");
							var divEle_3 = document.createElement("div");
							divEle_1.className ="mui-col-xs-4";
							divEle_2.className ="mui-col-xs-4";
							divEle_3.className ="mui-col-xs-4";						
							divEle_1.innerHTML= "<p class='text-left'>"+data[i].TradingDay+"</p>";
							divEle_2.innerHTML= "<p class='text-center'>"+data[i].IndexValue+"</p>";
							divEle_3.innerHTML= "<p class='text-right text-warning'>"+data[i].ValueDailyGrowthRate+"</p>";
							newEle.appendChild(divEle_1);
							newEle.appendChild(divEle_2);
							newEle.appendChild(divEle_3);
							oTab.appendChild(newEle);
						}
						var pEle = document.createElement("a");
						pEle.className="text-center query-more";
						pEle.setAttribute("id","queryMore");
						pEle.href= "history?fundCode="+fundcode;
						pEle.innerHTML="查看更多";						
						oTab.appendChild(pEle);
						//var shXdata =[],shYdata=[],prdXdata=[],prdYdata=[];
						var k= dy*30;
						if(dy== 12){
							k = prdData.length;
						}
						for (var i=0; i<k; i++) {							
							shXdata.push(prdData[i].net_date);
							shYdata.push(data[i].ValueDailyGrowthRate);							
							prdYdata.push(prdData[i].net_day_growth);
						}
					}
					else{
						oTab.innerHTML ="<p class='text-center query-more'>暂无数据</p>"
					}					
					lineChart.setOption(getOption(shXdata, shYdata, prdYdata));	
				}
				else if(res.code ==1){
					mui.alert("暂无此数据", "温馨提示");
				}
			}
		});
	};
	queryDetail(1);

	var oBtn =document.getElementsByClassName("time-btn");
	for (var i=0; i<oBtn.length; i++) {
		oBtn[i].addEventListener("tap", function(){
			var dIdnex =this.getAttribute("data-day");
			queryDetail(dIdnex);
		});
	}


	var getOption = function(shXdata, shYdata, prdYdata) {
		var chartOption ={
			legend: {
				data: ['沪深指数', '产品指数']
			},
			grid: {
				x: 35,
				x2: 10,
				y: 30,
				y2: 25
			},
			toolbox: {
				show: false,
				feature: {
					mark: {
						show: true
					},
					dataView: {
						show: true,
						readOnly: false
					},
					magicType: {
						show: true,
						type: ['line', 'bar']
					},
					restore: {
						show: true
					},
					saveAsImage: {
						show: true
					}
				}
			},
			calculable: false,
			xAxis: [{
				type: 'category',
				data: shXdata
			}],
			yAxis: [{
				type: 'value',
				axisLabel: {
		            formatter: '{value}%'
		        },
				splitArea: {
					show: true
				}
			}],
			series: [
				{
					name: '沪深指数',
					type: 'line',
					data: shYdata
				}, 
				{
					name: '产品指数',
					type: 'line',
					data: prdYdata
				}
			]
		};
		return chartOption;
	};				
	var lineChart = echarts.init(byId('chartContent'));
		




//	var more =document.getElementById("queryMore"); //历史净值查看更多
//	more.addEventListener("tap", function() {
//		alert(1);
//		return false;
//		var url = "history/"
//		loadPages(url);
//	});
//
//	var nn =document.getElementById("text");
//	nn.addEventListener("tap", function(){
//		console.log(1);
//	})
//	document.getElementById("buySubmit").on("tap", function(){
//		alert(1);
//	});
//	
	
	
	
	
	
	
	
}