window.onload = function() {
	function getUrlParam(name) { //获取url地址参数
		var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)"); //构造一个含有目标参数的正则表达式对象
		var r = window.location.search.substr(1).match(reg); //匹配目标参数
		if(r != null) return unescape(r[2]);
		return null; //返回参数值
	}
	var fundCode = getUrlParam("fundcode");
	(function fundInfo() { //基金信息
		var legend = [],
			dataArr = [];
		var setObj = new Object();
		var dc = document;
		mui.ajax("/jijin/jz_fund/fundInfo", {
			data: {
				fundcode: fundCode,
			},
			dataType: "json",
			type: "GET",
			success: function(res) {
				if(res.code == 0) { //有数据								
					var basicData = res.data.basic_info; //基本信息
					dc.getElementById("fundName").innerHTML = basicData.fundname;
					dc.getElementById("fundCode").innerHTML = basicData.fundcode;
					dc.getElementById("buildDay").innerHTML = basicData.build_date;
					dc.getElementById("riskRes").innerHTML = basicData.risklevel;
					dc.getElementById("fundMage").innerHTML = basicData.investadvisor;
					dc.getElementById("fundTurst").innerHTML = basicData.trustee;
					dc.getElementById("asets").innerHTML = basicData.total_assets + "亿元";
					dc.getElementById("totalAsets").innerHTML = basicData.total_scale + "亿元";
					var aManage = res.data.manager; //基金经理
					var ul = dc.getElementById("ulManage");
					for(var i = 0; i < aManage.length; i++) {
						var p = dc.createElement("p");
						p.className = "mui-text-justify"
						p.innerHTML = aManage[i].MangerName + "：" + aManage[i].MangerResume;
						ul.appendChild(p);
					}
					var news = res.data.file; //基金公告
					var fundnews = dc.getElementsByClassName("fund-news")[0];
					for(var i = 0; i < news.length; i++) {
						var li = dc.createElement("li");
						li.className = "mui-table-view-cell";
						var a = dc.createElement("a");
						a.innerHTML = news[i].filename;
						a.href = news[i].url;
						li.appendChild(a);
						fundnews.appendChild(li);
					}
					var fundAllocat = res.data.asset_allocation; //资金配置
					console.log(fundAllocat);
					var div = dc.getElementById("fundSets");
					for(var funds in fundAllocat) {
						var fundnames = "";
						switch(funds) {
							case "bond":
								fundnames = "债券";
								break;
							case "cash":
								fundnames = "现金";
								break;
							case "stock":
								fundnames = "股票";
								break;
							case "other":
								fundnames = "其他";
								break;
							case "total_assets":
								fundnames = "总资产";
						}
						legend.push(fundnames); //用于饼图
						setObj = {
							"name": fundnames,
							"value": fundAllocat[funds]
						};
						dataArr.push(setObj);
						var ul = dc.createElement("ul");
						ul.className = "mui-table-view ul-info";
						var li_1 = dc.createElement("li");
						var li_2 = dc.createElement("li");
						li_1.innerHTML = fundnames;
						li_2.innerHTML = fundAllocat[funds] + "%";
						if(fundnames == "总资产") {
							li_2.innerHTML = fundAllocat[funds] + "亿";
						}
						ul.appendChild(li_1);
						ul.appendChild(li_2);
						div.appendChild(ul);
					}

					legend.pop();
					dataArr.pop();
					var totalAset = fundAllocat.total_assets;
					drawCharts(legend, dataArr, totalAset);
				} else if(res.code == 1) {
					mui.alert("暂无此数据", "温馨提示");
					dc.getElementById("fundName").innerHTML = "暂无此数据";
					dc.getElementById("fundCode").innerHTML = "暂无此数据";
					dc.getElementById("buildDay").innerHTML = "暂无此数据";
					dc.getElementById("riskRes").innerHTML = "暂无此数据";
					dc.getElementById("fundMage").innerHTML = "暂无此数据";
					dc.getElementById("fundTurst").innerHTML = "暂无此数据";
					dc.getElementById("asets").innerHTML = "暂无此数据";
					dc.getElementById("totalAsets").innerHTML = "暂无此数据";
				}

			}
		});
	})();

	function drawCharts(legend, dataArr, totalAset) { //画图表的方法（legend为图例数据，dataArr为主体数据，totalAset为资产规模）
		var _echart_width = document.body.offsetWidth; //获取屏幕宽度，根据屏幕设置图表容器的宽度
		document.getElementById("fundChart").style.width = _echart_width + "px";
		var chartOption = {
			graphic: {
				type: 'text',
				left: "41%",
				top: 75,
				z: 5,
				zlevel: 100,
				style: {
					text: totalAset + "亿\n资产规模",
					textAlign: 'center',
					fill: '#000',
					with: 60,
					height: 50,
					fontSize: 16
				}
			},
			color: ['#ff7f50', '#87cefa', '#da70d6', '#32cd32', '#6495ed'],
			legend: {
				orient: 'vertical',
				x: 'left',
				formatter: "{name}",
				data: legend
			},
			series: [{
				type: 'pie',
				radius: ['50%', '70%'],
				label: {
					normal: {
						show: false,
						position: 'center'
					}
				},
				data: dataArr
			}]
		};
		var pieChart = echarts.init(document.getElementById('fundChart'));
		pieChart.setOption(chartOption);
	}

}