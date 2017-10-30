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
				oStar.innerHTML = "(未评级)"
			}

			var oParent = byId("buy_fund");
			var a = document.createElement("a");
			a.className = "mui-btn mui-btn-block buy-btn";
			if(res.purchasetype == "申购") {
				a.innerHTML = "立即" + res.purchasetype;
			} else if(res.purchasetype == "认购") {
				a.innerHTML = "立即" + res.purchasetype;
			}
			a.setAttribute("data-fundType", res.purchasetype);
			a.href = "/jijin/PurchaseController/Apply?fundcode=" + fundcode + "&purchasetype=" + res.purchasetype;
			oParent.appendChild(a);
		}
	});
	(function queryDetail() { //基金详情信息		
		var oTab = byId("historyCont");
		mui.ajax("/jijin/jz_fund/getFundCurve", {
			data: {
				fundCode: fundcode
			},
			dataType: "json",
			type: "GET",
			success: function(res) {
				var data = res.hs_data; //沪深指数
				var prdData = res.data; //产品指数				
				renderChart(res); //产品指数
				if(res.code == 0) {
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
							divEle_1.innerHTML = "<p class='mui-text-left'>" + prdData[i].net_date + "</p>";
							divEle_2.innerHTML = "<p class='mui-text-center'>" + prdData[i].net_day_nav + "</p>";
							divEle_3.innerHTML = "<p class='mui-text-right text-warning'>" + prdData[i].net_day_growth + "</p>";
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

	function drawCharts(index, chartsDiv) { //图表渲染（index为当前时间节点，chartsDiv为图表容器）
		var _echart_width = document.body.offsetWidth; //获取屏幕宽度，根据屏幕设置图表容器的宽度
		byId("one").style.width = _echart_width + "px";
		byId("three").style.width = _echart_width + "px";
		byId("six").style.width = _echart_width + "px";
		byId("year").style.width = _echart_width + "px";
		var one = echarts.init(byId(chartsDiv));
		var shXdata = [],
			shYdata = [],
			prdXdata = [],
			prdYdata = [];
		mui.get("/jijin/jz_fund/getFundCurve", {
				fundCode: fundcode
			},
			function(res) {
				var prdData = res.data; //产品指数
				var data = res.hs_data; //沪深指数
				if(res.code == 0) {
					if(data) {
						var k = 0;
						if(index == 12) {
							k = prdData.length;
						} else {
							k = index * 30;
						}
						for(var i = 0; i < k; i++) {
							shXdata.push(prdData[i].net_date);
							shYdata.push(data[i].ValueDailyGrowthRate);
							prdYdata.push(prdData[i].net_day_growth);
						}
						one.setOption(getOption(shXdata, shYdata, prdYdata));
					}
				}
			}, "json");
	};
	//	drawCharts(1,"one");
	//	drawCharts(3, "three");
	//	drawCharts(6, "six");
	//	drawCharts(12, "year");

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

	function renderChart(data, type) {
		var prdData = data.data; //产品指数
		var hsData = data.hs_data; //沪深指数
		var oneData = [];
		var threeData = [];
		var halfData = [];

		var oneHsData = [];
		var threeHsData = [];
		var halfHsData = [];

		var today = new Date().valueOf() - 30 * 24 * 60 * 60 * 1000;	
		var three = new Date().valueOf() - 3 * 30 * 24 * 60 * 60 * 1000;
		var half = new Date().valueOf() - 6 * 30 * 24 * 60 * 60 * 1000;
		var start = new Date(prdData[0].net_date.replace(/-/g, '/')).valueOf();
		
		var startHs = new Date(hsData[0].TradingDay.replace(/-/g, '/')).valueOf();
		var kk = startHs;
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
		
		for(var i = 0; i < hsData.length; i++) {
			if(today > startHs) {
				oneHsData = [];
				break;
			} else if(today > new Date(hsData[i].TradingDay.replace(/-/g, '/')).valueOf()) {
				kk = i;
				break;
			}
		}
		if(i == hsData.length) {
			kk = i - 1;
		}
		oneHsData = hsData.slice(0, kk);
		//console.log(oneHsData);
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
		for(var i = 0; i < hsData.length; i++) {
			if(three > startHs) {
				threeHsData = [];
				break;
			} else if(three > new Date(hsData[i].TradingDay.replace(/-/g, '/')).valueOf()) {
				kk = i;
				break;
			}
		}
		if(i == hsData.length) {
			kk = i - 1;
		}
		threeHsData = hsData.slice(0, kk);

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
		for(var i = 0; i < hsData.length; i++) {
			if(half > startHs) {
				halfHsData = [];
				break;
			} else if(half > new Date(hsData[i].TradingDay.replace(/-/g, '/')).valueOf()) {
				kk = i;
				break;
			}
		}
		if(i == hsData.length) {
			kk = i - 1;
		}
		halfHsData = hsData.slice(0, kk);

		var yearHsData = hsData;
		var yearData = prdData;
		
		var Options = {
			title: {
				text: "",
				left: "center"
			},
			tooltip: {
				trigger: 'none',
				axisPointer: {
					type: "line"
				}
			},
			legend: {
				data: ['沪深指数', '产品指数'],
				bottom: "10px"
			},
			grid: {
				x: 50,
				x2: 20,
				y: 40,
				y2: 30
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
			name: '产品指数',
			type: 'line',
			data: []
		};
		var b = {
			name: '产品指数',
			type: 'line',
			data: []
		};
		var c = {
			name: '产品指数',
			type: 'line',
			data: []
		};
		var d = {
			name: '产品指数',
			type: 'line',
			data: []
		};

		var hsA = {
			name: '沪深指数',
			type: 'line',
			data: []
		}
		var hsB = {
			name: '沪深指数',
			type: 'line',
			data: []
		}
		var hsC = {
			name: '沪深指数',
			type: 'line',
			data: []
		}
		var hsD = {
			name: '沪深指数',
			type: 'line',
			data: []
		}
		var _echart_width = document.body.offsetWidth; //获取屏幕宽度，根据屏幕设置图表容器的宽度
		byId("one").style.width = _echart_width + "px";
		byId("three").style.width = _echart_width + "px";
		byId("six").style.width = _echart_width + "px";
		byId("year").style.width = _echart_width + "px";
		for(var i = 0; i < oneData.length; i++) {
			opOne.xAxis.data[i] = oneData[i].net_date; //1月x轴
			c.data[i] = oneData[i].net_day_growth;
		}

		for(var i = 0; i < oneHsData.length; i++) {
			hsC.data[i] = oneHsData[i].IndexValue;
		}

		hsC.data.reverse();
		opOne.series.push(hsC);

		opOne.xAxis.data.reverse();
		c.data.reverse();
		opOne.series.push(c);
			
		for(var i = 0; i < threeData.length; i++) {
			opThree.xAxis.data[i] = threeData[i].net_date; //三月x轴
			d.data[i] = threeData[i].net_day_growth;
		}
		for(var i = 0; i < threeHsData.length; i++) {
			hsD.data[i] = threeHsData[i].IndexValue;
		}
		opThree.xAxis.data.reverse();
		d.data.reverse();
		hsD.data.reverse();
		opThree.series.push(hsD);
		opThree.series.push(d);
		for(var i = 0; i < halfData.length; i++) {
			opSix.xAxis.data[i] = halfData[i].net_date //半年x轴
			a.data[i] = halfData[i].net_day_growth;
		}
		for(var i = 0; i < halfData.length; i++) {
			hsA.data[i] = halfData[i].IndexValue;
		}
		opSix.xAxis.data.reverse();
		a.data.reverse();
		hsA.data.reverse();
		opSix.series.push(hsA);
		opSix.series.push(a);
		for(var i = 0; i < prdData.length; i++) {
			opYear.xAxis.data[i] = prdData[i].net_date //一年x轴
			b.data[i] = prdData[i].net_day_growth;
		}
		for(var i = 0; i < hsData.length; i++) {
			hsB.data[i] = hsData[i].IndexValue;
		}
		opYear.xAxis.data.reverse();
		b.data.reverse();
		hsB.data.reverse();
		opYear.series.push(hsB);
		opYear.series.push(b);

		//  console.log(opOne);
		//  console.log(opThree);
		//  console.log(opSix);
		//  console.log(opYear);
		var n1 = echarts.init(byId("one"));
		var n2 = echarts.init(byId("three"));
		var n3 = echarts.init(byId("six"));
		var n4 = echarts.init(byId("year"));
		n1.hideLoading();
		n1.setOption(opOne);
		n2.setOption(opThree);
		n3.setOption(opSix);
		n4.setOption(opYear);
	}

}