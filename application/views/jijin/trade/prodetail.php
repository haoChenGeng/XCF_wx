<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <meta name="format-detection" content="telephone=no"/>
	<title>产品详情</title>
  <link rel="stylesheet" href="../../../../data/jijin/css/style.css">
</head>
<body>
	<div class="wrap">
    <section class="m-item-wrap m-item-5-wrap">
      <h3 class="text-center m-item-5"><span class="head-back-icon" onclick="window.history.go(-1)"></span><?php echo $fundlist['fundname'].'('.$fundlist['fundcode'].')';?></h3>      
    </section>
    <section class="m-content">
      <div class="m-content-t1">
        <div class="m-content-t1-detail">
          <p><?php echo $fundlist['growth_day'].'%';?></p>
          <p>日涨跌幅</p>
        </div>
        <div class="m-content-t1-detail">
          <p><?php echo $fundlist['nav'];?></p>
          <p>最新净值(元)</p>
        </div>
        <div class="m-content-date">净值日期：<?php echo $fundlist['navdate'];?></div>
      </div>
      <div class="m-content-t2">
        <div class="m-content-t2-detail">
          <p>起投金额(元)</p>
          <p>100.00</p>
        </div>
        <div class="m-content-t2-detail">
          <p>基金类型</p>
          <p>货币型</p>
        </div>
        <div class="m-content-t2-detail">
          <p>风险等级</p>
          <p>积极型</p>
        </div>
      </div>
      <div class="m-content-t3">
        <p>净值走势</p>
        <div  id="chartContent">
          <div class="m-content-t3-chart" style="height: 280px;display: block;" id="one"></div>
          <div class="m-content-t3-chart" style="height: 280px;" id="three"></div>
          <div class="m-content-t3-chart" style="height: 280px;" id="six"></div>
          <div class="m-content-t3-chart" style="height: 280px;" id="year"></div>
        </div>
        <div class="m-content-t3-tab" id="worthChart">
          <div class="tabItem tab-active">1月</div>
          <div class="tabItem">3月</div>
          <div class="tabItem">6月</div>
          <div class="tabItem">1年</div>
        </div>
      </div>
      <div class="m-content-t4">
        <p>交易规则：</p>
        <p class="m-content-t4-detail">
          申购：T日购买，T+1日确认份额，T+2日收益到账
        （QDII一般为T+2确认份额）
        </p>
        <p class="m-content-t4-detail">
          赎回：货币基金一般T+1个交易日到账，股票、混合、债券
          等一般需T+3个交易日到账，QDII一般需要T+7个交
          易日到账，T为交易日
        </p>
      </div>
    </section>
    <section class="m-footer">
      <p class="m-footer-content">立即申购</p>
    </section>
  </div>
<script src="../../../../data/jijin/js/echarts.js"></script>
<script src="../../../../data/jijin/js/zepto.min.js"></script>
<script>
function getQueryString(e) {
  var t = new RegExp("(^|&)" + e + "=([^&]*)(&|$)", "i"),
    o = window.location.search.substr(1).match(t);
  return null != o ? unescape(o[2]) : null;
}

var date = new Date();
date.toLocaleDateString();

var nav=document.getElementById("worthChart").getElementsByTagName("div");  
var con=document.getElementById("chartContent").getElementsByTagName("div");
console.log(nav);
for(i=0;i<nav.length;i++){
    nav[i].index = i;
    nav[i].addEventListener('click', function() {
      for(var n = 0; n < con.length; n++) {
          con[n].style.display = "none";
          nav[n].classList.remove('tab-active');
      }
      con[this.index].style.display = "block";
      nav[this.index].classList.add('tab-active');
      
      
    });
}


$(document).ready(function() {
  

// console.log(fundCode);
  (function() {
    var fundCode = getQueryString('fundcode');
    $.ajax({
      url: 'http://localhost:8009/jijin/jz_fund/getFundCurve',
      data: {
        fundCode: fundCode
      },
      dataType: 'json',
      type: 'get',
      async: true,
      success: function(res) {
  // console.log(res);
        renderChart(res.data);
      },
      error: function() {
        alert('请求错误');
      }
    });    
  })();

  function clone(e) {
    var t;
    if (null == e || "object" != typeof e) return e;
    if (e instanceof Date) return t = new Date, t.setTime(e.getTime()), t;
    if (e instanceof Array) { t = [];
      for (var o = 0, n = e.length; o < n; o++) t[o] = clone(e[o]);
      return t }
    if (e instanceof Object) { t = {};
      for (var a in e) e.hasOwnProperty(a) && (t[a] = clone(e[a]));
      return t }
    throw new Error("Unable to copy obj! Its type isn't supported.") }

  function renderChart(data) {
    /*var oneData = clone(data);
    var threeData = clone(data);
    var sixData = clone(data);
    var yearData = clone(data);var one = oneData.splice(0, oneData.length - 30);
    var three = threeData.splice(0, threeData.length - 90);
    var six = sixData.splice(0,sixData.length - 180);
    var year = yearData.splice(0, yearData.length - 360);*/
    var oneData;
    var threeData;
    var halfData;
    var today = new Date().valueOf() - 30*24*60*60*1000;
    var three = new Date().valueOf() - 3*30*24*60*60*1000;
    var half = new Date().valueOf() - 6*30*24*60*60*1000;
    for (var i = 0; i < data.length; i++) {
console.log(today);
console.log(new Date(data[i].net_date.replace(/-/g, '/')).valueOf());      
      if (today > new Date(data[i].net_date.replace(/-/g, '/')).valueOf()) {
        oneData = data.slice(0, i);
      }
      if (three > new Date(data[i].net_date.replace(/-/g, '/')).valueOf()) {
        threeData = data.slice(0, i);
      }
      if (half > new Date(data[i].net_date.replace(/-/g, '/')).valueOf()) {
        halfData = data.slice(0, i);
      }
    }
console.log(oneData);
console.log(threeData);
console.log(halfData);
    var yearData = data;

    var Options = {
      title: {
        text: "",
        left: "center"
      },
      tooltip: {
        trigger: "axis",
        axisPointer: {
          type: "line"
        }
      },
      legend: {
        data: ["净值走势"],
        bottom: "1px"
      },
      toolbox: {
        show: !1
      },
      grid: {
        left: "3%",
        right: "4%",
        bottom: "18%",
        top: "5%",
        containLabel: !0
      },
      xAxis: {
        type: "category",
        boundaryGap: !1,
        data: ["周一", "周二", "周三", "周四", "周五", "周六", "周日"]
      },
      yAxis: {
        type: "value"
      },
      series: [{
        name: "邮件营销",
        type: "line",
        stack: "总量",
        data: [120, 132, 101, 134, 90, 230, 210]
      }, {
        name: "联盟广告",
        type: "line",
        stack: "总量",
        data: [120, 132, 101, 134, 190, 230, 210]
      }, {
        name: "视频广告",
        type: "line",
        stack: "总量",
        data: [120, 132, 101, 134, 90, 200, 210]
      }, {
        name: "直接访问",
        type: "line",
        stack: "总量",
        data: [120, 132, 201, 134, 90, 230, 210]
      }, {
        name: "搜索引擎",
        type: "line",
        stack: "总量",
        data: [120, 182, 101, 134, 90, 230, 210]
      }]
    };

    var opOne = clone(Options);
    var opThree = clone(Options);
    var opSix = clone(Options);
    var opYear = clone(Options);

    for (var i = 0; i < one.length; i++) {
      opOne.xAxis.data[i] = one[i].net_date.replace(/-/g, '');
      opOne.series.data[i] = one[i].net_day_growth;
    }
    for (var i = 0; i < three.length; i++) {
      opThree.xAxis.data[i] = three[i].net_date.replace(/-/g, '');
      opThree.series.data[i] = three[i].net_day_growth;
    }
    for (var i = 0; i < six.length; i++) {
      opSix.xAxis.data[i] = six[i].net_date.replace(/-/g, '');
      opSix.series.data[i] = six[i].net_day_growth;
    }
    for (var i = 0; i < year.length; i++) {
      opYear.xAxis.data[i] = year[i].net_date.replace(/-/g, '');
      opYear.series.data[i] = year[i].net_day_growth;
    }

    var n1 = echarts.init(document.getElementById("one"));
    var n2 = echarts.init(document.getElementById("three"));
    var n3 = echarts.init(document.getElementById("six"));
    var n4 = echarts.init(document.getElementById("year"));
    $("#one").css({ height: "280px"}), n1.resize(), n1.setOption(opOne);
    $("#three").css({ height: "280px"}), n1.resize(), n1.setOption(opThree);
    $("#six").css({ height: "280px"}), n1.resize(), n1.setOption(opSix);
    $("#year").css({ height: "280px"}), n1.resize(), n1.setOption(opYear);
  }
});
</script>
</body>
</html>