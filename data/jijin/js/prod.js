window.onload = function() {
	var byId = function(id) {
		return document.getElementById(id);
	};

	function getUrlParam(name) { //获取url地址参数
		var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)"); //构造一个含有目标参数的正则表达式对象
		var r = window.location.search.substr(1).match(reg); //匹配目标参数
		if(r != null) return unescape(r[2]);
		return null; //返回参数值
	}
	var fundcode = getUrlParam("fundcode"); //获取基金代码

	var oLi = byId("fund-list"); //获取绑定事件对象
	var oLiChlid = oLi.childNodes;
	for(var i = 0; i < oLiChlid.length; i++) {
		oLiChlid[i].addEventListener("tap", function() {
			var strA = this.children[0].innerHTML;
			if(strA == "基金概括") { //跳转到基金概括
				var url = "summ/?fundcode=" + fundcode;
				this.childNodes[1].href = url;
			} else if(strA == "交易须知") { //跳转到交易须知							
				var url = "tradelist/?fundcode=" + fundcode;
				this.childNodes[1].href = url;
			}
		});
	}

	mui.ajax("/jijin/Jz_fund/fundDetail", { //基金详情信息		
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
			if(stars < 10) {
				for(var i = 0; i < stars; i++) { //评级	
					var oStar = document.createElement("span");
					oStar.className = "mui-icon mui-icon-star active-star";
					starParent[0].appendChild(oStar);
				}
			} else {
				var oStar = document.createElement("span");
				oStar.style.fontSize = "12px";
				oStar.innerHTML = "(未评级)";
			}

			var oParent = byId("buy_fund");
			var a = document.createElement("a");
			a.className = "mui-btn mui-btn-block buy-btn";
			if(res.purchasetype == "申购") {
				a.innerHTML = "" + res.purchasetype;
			} else if(res.purchasetype == "认购") {
				a.innerHTML = "" + res.purchasetype;
			}
			a.setAttribute("data-fundType", res.purchasetype);
			a.href = "/jijin/PurchaseController/Apply?fundcode=" + fundcode + "&purchasetype=" + res.purchasetype;
			
			oParent.appendChild(a);
			
			if(fundlist.business_flag==1){
				var castSurely = document.createElement("a");
				castSurely.innerHTML = "定投";
				castSurely.className = "mui-btn mui-btn-block buy-btn";
				castSurely.setAttribute("data-fundType", res.purchasetype);
				castSurely.href = "/application/views/jijin/trade/castSurely.html?fundcode=" + fundcode;
				
				oParent.appendChild(castSurely);
			}
		}
	});

	function getXdata(data) { //获取各时间点的X轴
		var XdataArr = []
		for(var i = 0; i < data.length; i++) {
			XdataArr.push(data[i].net_date);
		}
		XdataArr = XdataArr.reverse();
		return XdataArr;
	}

	function getPrdYdata(data) { //获取各时间点的Y轴
		var YdataArr = []
		for(var i = 0; i < data.length; i++) {
			YdataArr.push(data[i].value);
		}
		YdataArr = YdataArr.reverse();
		return YdataArr;
	}

	function getHsYdata(data) { //获取各时间点的Y轴
		var YdataArr = []
		for(var i = 0; i < data.length; i++) {
			YdataArr.push(data[i].hs_value);
		}
		YdataArr = YdataArr.reverse();
		return YdataArr;
	}

	(function queryDetail() { //基金详情信息		
		var oTab = byId("historyCont");
		mui.ajax("/jijin/jz_fund/getFundCurve", {
			data: {
				fundCode: fundcode
			},
			dataType: "json",
			type: "GET",
			success: function(res) {
				var data = res.data.fundCure; //产品指数
				if(res.code == 0) {
					var _echart_width = document.body.offsetWidth; //获取屏幕宽度，根据屏幕设置图表容器的宽度
					byId("one").style.width = _echart_width + "px";
					byId("three").style.width = _echart_width + "px";
					byId("six").style.width = _echart_width + "px";
					byId("year").style.width = _echart_width + "px";
					if(res.data.fundtype != 2) {
						byId("netVal").innerHTML = "单位净值(元)";
						byId("dayGrowUp").innerHTML = "日涨跌幅(%)";
						var onemonth = res.data.onemonth; //产品一月数据
						var threemonth = res.data.threemonth; //产品三月数据
						var sixmonth = res.data.sixmonth; //产品六月数据
						var oneyear = res.data.oneyear; //产品一年数据
						var oneXdata = getXdata(onemonth); //一月x轴
						var onePrdYdata = getPrdYdata(onemonth); //一月产品指数Y轴
						var oneHsYdata = getHsYdata(onemonth); //一月沪深指数Y轴

						var threeXdata = getXdata(threemonth); //三月x轴
						var threePrdYdata = getPrdYdata(threemonth); //三月产品指数Y轴
						var threeHsYdata = getHsYdata(threemonth); //三月沪深指数Y轴

						var sixXdata = getXdata(sixmonth); //六月x轴
						var sixPrdYdata = getPrdYdata(sixmonth); //三月产品指数Y轴
						var sixHsYdata = getHsYdata(sixmonth); //三月沪深指数Y轴

						var yearXdata = getXdata(oneyear); //一年x轴
						var yearPrdYdata = getPrdYdata(oneyear); //三月产品指数Y轴
						var yearHsYdata = getHsYdata(oneyear); //三月沪深指数Y轴

						var oneCont = byId("one");
						var oneChart = echarts.init(oneCont);
						oneChart.setOption(getOption(oneXdata, onePrdYdata, oneHsYdata));

						var threeCont = byId("three");
						var threeChart = echarts.init(threeCont);
						threeChart.setOption(getOption(threeXdata, threePrdYdata, threeHsYdata));

						var sixCont = byId("six");
						var sixChart = echarts.init(sixCont);
						sixChart.setOption(getOption(sixXdata, sixPrdYdata, sixHsYdata));

						var yearCont = byId("year");
						var yearChart = echarts.init(yearCont);
						yearChart.setOption(getOption(yearXdata, yearPrdYdata, yearHsYdata));

					} else if(res.data.fundtype == "2") {						
						byId("netVal").innerHTML = "万份收益(元)";
						byId("dayGrowUp").innerHTML = "七日年化(%)";
						renderChart(data);
					}
					
					if(data) {
						for(var i = 0; i < 7; i++) { //历史净值
							var newEle = document.createElement("div");
							newEle.className = "mui-row";
							var divEle_1 = document.createElement("div");
							var divEle_2 = document.createElement("div");
							var divEle_3 = document.createElement("div");
							divEle_1.className = "mui-col-xs-4";
							divEle_2.className = "mui-col-xs-4";
							divEle_3.className = "mui-col-xs-4";
							divEle_1.innerHTML = "<p class='mui-text-left'>" + data[i].net_date + "</p>";
							divEle_2.innerHTML = "<p class='mui-text-center'>" + data[i].net_day_nav + "</p>";
							divEle_3.innerHTML = "<p class='mui-text-right text-warning'>" + data[i].net_day_growth + "</p>";
							newEle.appendChild(divEle_1);
							newEle.appendChild(divEle_2);
							newEle.appendChild(divEle_3);
							oTab.appendChild(newEle);
						}
						var pEle = document.createElement("a");
						pEle.className = "mui-text-center query-more";
						pEle.setAttribute("id", "queryMore");
						pEle.href = "history?fundCode=" + fundcode;
						pEle.innerHTML = "查看更多";
						oTab.appendChild(pEle);
					} else {
						oTab.innerHTML = "<p class='mui-text-center query-more'>暂无数据</p>"
					}
				} else if(res.code == 1) {
					mui.alert("暂无此数据", "温馨提示");
				}
			}
		});
	})();

	var oBtn = document.getElementsByClassName("time-btn");
	var nav = byId("worthChart").getElementsByTagName("div");
	var con = byId("chartContent").getElementsByClassName('m-content-t3-chart');
	for(i = 0; i < nav.length; i++) {
		nav[i].index = i;
		nav[i].addEventListener('tap', function() { //点击查看时间图表 
			for(var n = 0; n < con.length; n++) {
				con[n].style.display = "none";
				nav[n].classList.remove('tab-active');
			}
			var chartDiv = document.getElementsByClassName("tab-active");
			con[this.index].style.display = "block";
			nav[this.index].classList.add('tab-active');

		});
	}
	var getOption = function(xdata, prdYdata, hsYdata) {		//
		var chartOption = {			
			legend: {
				data: ['产品指数', '沪深指数']
			},
			grid: {
				x: 40,
				x2: 20,
				y: 30,
				y2: 25
			},			
			xAxis: [{
				type: 'category',
				data: xdata,
				boundaryGap: false
			}],
			yAxis: [{
				type: 'value',
				axisLabel: {
					formatter: '{value}%'
				},
			}],
			series: [{
				name: '产品指数',
				type: "line",
				data: prdYdata,
			}, {
				name: '沪深指数',
				type: "line",
				data: hsYdata,
			}]
		};
		return chartOption;
	};

    function clone(e) {
        var t;
        if(null == e || "object" != typeof e) return e;
        if(e instanceof Date) return t = new Date, t.setTime(e.getTime()), t;
        if(e instanceof Array) {
            t = [];
            for(var o = 0, n = e.length; o < n; o++) t[o] = clone(e[o]);
            return t
        }
        if(e instanceof Object) {
            t = {};
            for(var a in e) e.hasOwnProperty(a) && (t[a] = clone(e[a]));
            return t
        }
        throw new Error("Unable to copy obj! Its type isn't supported.")
    }
    
	function renderChart(prdDataArr, type) {
		if(!prdDataArr || !prdDataArr.length) {
			alert('无数据');
			return false;
		}
		var prdData =[];
		for(var i =0,j=0 ;i<prdDataArr.length; i++){
			if(prdDataArr[i].net_day_nav !=null){		//若net_day_nav为null则该记录废弃
				prdData[j++] =prdDataArr[i];
			}
		}
		var oneData = [];
		var threeData = [];
		var halfData = [];
		
		var today = new Date().valueOf() - 30 * 24 * 60 * 60 * 1000;
		var three = new Date().valueOf() - 3 * 30 * 24 * 60 * 60 * 1000;
		var half = new Date().valueOf() - 6 * 30 * 24 * 60 * 60 * 1000;
		var start = new Date(prdData[0].net_date.replace(/-/g, '/')).valueOf();
		var ii = start;
		for(var i = 0; i < prdData.length; i++) {
			if(today > start) {
				oneData = [];
				break;
			} else if(today > new Date(prdData[i].net_date.replace(/-/g, '/')).valueOf()) {
				ii = i;
				break;
			}
		}
		if(i == prdData.length) {
			ii = i - 1;
		}
		oneData = prdData.slice(0, ii);
		for(; i < prdData.length; i++) {
			if(three > start) {
				threeData = [];
				break;
			} else if(three > new Date(prdData[i].net_date.replace(/-/g, '/')).valueOf()) {
				ii = i;
				break;
			}
		}
		if(i == prdData.length) {
			ii = i - 1;
		}
		threeData = prdData.slice(0, ii);
		for(; i < prdData.length; i++) {
			if(half > start) {
				halfData = [];
				break;
			} else if(half > new Date(prdData[i].net_date.replace(/-/g, '/')).valueOf()) {
				ii = i;
				break;
			}
		}
		if(i == prdData.length) {
			ii = i - 1;
		}
		halfData = prdData.slice(0, ii);		
		var yearData = prdData;
		var Options = {
			color: ["#98d4f9"],
			title: {
				text: "七日年化",
				textStyle: {
					color: "#98d4f9",
					fontWeight: "normal",
					fontSize: 14,
					
				},
				left:	300
			},
			tooltip: {
				trigger: 'none',
				axisPointer: {
					type: "line"
				}
			},
			grid: {
				x: 40,
				x2: 20,
				y: 30,
				y2: 25
			},
			toolbox: {
				show: false,
			},

			xAxis: {
				type: "category",
				boundaryGap: !1,
				data: []
			},
			yAxis: {
				type: "value",
				axisLabel: {
					formatter: '{value}%'
				},
			},
			series: []
		};

		var opOne = clone(Options);
		var opThree = clone(Options);
		var opSix = clone(Options);
		var opYear = clone(Options);

		var a = {
			// name: '6个月净值走势',
			type: 'line',
			data: []
		};
		var b = {
			// name: '1年净值走势',
			type: 'line',
			data: []
		};
		var c = {
			// name: '1个月净值走势',
			type: 'line',
			data: []
		};
		var d = {
			// name: '3个月净值走势',
			type: 'line',
			data: []
		};

		var _echart_width = document.body.offsetWidth; //获取屏幕宽度，根据屏幕设置图表容器的宽度
		byId("one").style.width = _echart_width + "px";
		byId("three").style.width = _echart_width + "px";
		byId("six").style.width = _echart_width + "px";
		byId("year").style.width = _echart_width + "px";
		for(var i = 0; i < oneData.length; i++) {			
			opOne.xAxis.data[i] = oneData[i].net_date;
			c.data[i] = oneData[i].net_day_growth;
		}
		opOne.xAxis.data.reverse();
		c.data.reverse();
		opOne.series.push(c);
		
		for(var i = 0; i < threeData.length; i++) {			
			opThree.xAxis.data[i] = threeData[i].net_date;
			d.data[i] = threeData[i].net_day_growth;
		}
		
		opThree.xAxis.data.reverse();
		d.data.reverse();
		opThree.series.push(d);
		for(var i = 0; i < halfData.length; i++) {			
			opSix.xAxis.data[i] = halfData[i].net_date
			a.data[i] = halfData[i].net_day_growth;	
		}
		opSix.xAxis.data.reverse();
		a.data.reverse();
		opSix.series.push(a);
		for(var i = 0; i < prdData.length; i++) {			
			opYear.xAxis.data[i] = prdData[i].net_date
			b.data[i] = prdData[i].net_day_growth;
		}
		opYear.xAxis.data.reverse();
		b.data.reverse();
		opYear.series.push(b);
		
		var n1 = echarts.init(document.getElementById("one"));
		var n2 = echarts.init(document.getElementById("three"));
		var n3 = echarts.init(document.getElementById("six"));
		var n4 = echarts.init(document.getElementById("year"));
		n1.hideLoading();
		n1.setOption(opOne);
		n2.setOption(opThree);
		n3.setOption(opSix);
		n4.setOption(opYear);
	}
}