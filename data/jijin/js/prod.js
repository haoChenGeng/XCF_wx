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
/*	var oBtnP = byId("worthChart");
	var oBtn = oBtnP.children;
	for(var i = 0; i < oBtn.length; i++) {
		oBtn[i].addEventListener("tap", function() {
			this.setAttribute("class", "btn-active").nextElementSibling.removeAttribute("class", "btn-active");
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
			var oTitle = byId("fundname");
			oTitle.innerHTML = fundlist.fundname + "(" + fundlist.fundcode + ")";
			document.getElementsByClassName("date")[0].innerHTML = fundlist.navdate //日期
			document.getElementsByClassName("dayUp")[0].innerHTML = res.field1 //日涨跌幅
			document.getElementsByClassName("dayGrow")[0].innerHTML = fundlist.growth_day + "%"; //日涨跌幅
			document.getElementsByClassName("newVal")[0].innerHTML = res.field2 //最新净值
			byId("fundIndex").innerHTML = fundlist.fundtype; //基金类型
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
			var oParent = byId("buy_fund");
			var a = document.createElement("a");
			a.className = "m-footer-content ";
			if(res.purchasetype == "申购") {				
				a.innerHTML = "立即"+res.purchasetype;				
			}
			else if(res.purchasetype == "认购"){
				a.innerHTML = "立即"+res.purchasetype;
			}			
			a.setAttribute("data-fundType", res.purchasetype);
			a.href ="/jijin/PurchaseController/Apply?fundcode="+fundcode+"&purchasetype="+res.purchasetype;
			oParent.appendChild(a);			
		}
	});
	(function queryDetail(){	//基金详情信息		
		var oTab =byId("historyCont");
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
					}
					else{
						oTab.innerHTML ="<p class='text-center query-more'>暂无数据</p>"
					}						
				}
				else if(res.code ==1){
					mui.alert("暂无此数据", "温馨提示");
				}
			}
		});
	})();

	function drawCharts(index, chartsDiv){		//图表渲染（index为当前时间节点，chartsDiv为图表容器）
	  	var _echart_width =document.body.offsetWidth;	//获取屏幕宽度，根据屏幕设置图表容器的宽度
		byId("one").style.width =_echart_width+"px";
		byId("three").style.width =_echart_width+"px";
		byId("six").style.width =_echart_width+"px";
		byId("year").style.width =_echart_width+"px";		
		var one = echarts.init(byId(chartsDiv));
		var shXdata =[],shYdata=[],prdXdata=[],prdYdata=[];
		mui.get("/jijin/jz_fund/getFundCurve", 
			{
				fundCode: fundcode
			},
			function(res){
			var prdData  =res.data;			//产品指数
			var data =res.hs_data;			//沪深指数
			if(res.code ==0){
				if(data){
					var k=0;
					if(index== 12){
						k = prdData.length;
					}
					else{
						k= index*30;
					}
					for (var i=0; i<k; i++) {
						shXdata.push(data[i].TradingDay);
						shYdata.push(data[i].ValueDailyGrowthRate);							
						prdYdata.push(prdData[i].net_day_growth);
					}
					one.setOption(getOption(shXdata, shYdata, prdYdata));	
				}
			}	
		},"json");
	};
	drawCharts(1,"one");
	drawCharts(3, "three");
	drawCharts(6, "six");
	drawCharts(12, "year");

	var oBtn =document.getElementsByClassName("time-btn");
 	var nav = document.getElementById("worthChart").getElementsByTagName("div");
  	var con = document.getElementById("chartContent").getElementsByClassName('m-content-t3-chart');
  	for (i = 0; i < nav.length; i++) {
	    nav[i].index = i;
	    nav[i].addEventListener('tap', function() {		//点击查看时间图表 
	  	for (var n = 0; n < con.length; n++) {
	        con[n].style.display = "none";
	        nav[n].classList.remove('tab-active');
	  	}
	  	var chartDiv =document.getElementsByClassName("tab-active");
      	con[this.index].style.display = "block";
      	nav[this.index].classList.add('tab-active');
      	
	    });
  	}
	var getOption = function(shXdata, shYdata, prdYdata) {		//图表方法
		var chartOption ={
			tooltip:{
		        trigger: 'none',
		        axisPointer: {
		            type: 'cross'
		        }				
			},
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
	
	
	
	
	
	
	
}