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
  var a = {
  "code": 0,
  "data": [
    {
      "net_date": "2016-06-13",
      "net_day_growth": "-0.0108540177"
    },
    {
      "net_date": "2016-06-14",
      "net_day_growth": "-0.0003402518"
    },
    {
      "net_date": "2016-06-15",
      "net_day_growth": "0.0061266167"
    },
    {
      "net_date": "2016-06-16",
      "net_day_growth": "-0.0015223275"
    },
    {
      "net_date": "2016-06-17",
      "net_day_growth": "0.0025410808"
    },
    {
      "net_date": "2016-06-20",
      "net_day_growth": "0.0007603920"
    },
    {
      "net_date": "2016-06-21",
      "net_day_growth": "-0.0009286619"
    },
    {
      "net_date": "2016-06-22",
      "net_day_growth": "0.0032110867"
    },
    {
      "net_date": "2016-06-23",
      "net_day_growth": "-0.0026111860"
    },
    {
      "net_date": "2016-06-24",
      "net_day_growth": "-0.0043915210"
    },
    {
      "net_date": "2016-06-27",
      "net_day_growth": "0.0052591399"
    },
    {
      "net_date": "2016-06-28",
      "net_day_growth": "0.0008438106"
    },
    {
      "net_date": "2016-06-29",
      "net_day_growth": "0.0018548183"
    },
    {
      "net_date": "2016-06-30",
      "net_day_growth": "0.0018513843"
    },
    {
      "net_date": "2016-07-01",
      "net_day_growth": "0.0006719866"
    },
    {
      "net_date": "2016-07-04",
      "net_day_growth": "0.0057919919"
    },
    {
      "net_date": "2016-07-05",
      "net_day_growth": "0.0025037556"
    },
    {
      "net_date": "2016-07-06",
      "net_day_growth": "0.0031635032"
    },
    {
      "net_date": "2016-07-07",
      "net_day_growth": "0.0017427386"
    },
    {
      "net_date": "2016-07-08",
      "net_day_growth": "-0.0007455886"
    },
    {
      "net_date": "2016-07-11",
      "net_day_growth": "0.0005803349"
    },
    {
      "net_date": "2016-07-12",
      "net_day_growth": "0.0061314111"
    },
    {
      "net_date": "2016-07-13",
      "net_day_growth": "0.0018940954"
    },
    {
      "net_date": "2016-07-14",
      "net_day_growth": "-0.0006575703"
    },
    {
      "net_date": "2016-07-15",
      "net_day_growth": "-0.0009870044"
    },
    {
      "net_date": "2016-07-18",
      "net_day_growth": "0.0019759592"
    },
    {
      "net_date": "2016-07-19",
      "net_day_growth": "-0.0005751849"
    },
    {
      "net_date": "2016-07-20",
      "net_day_growth": "0.0009865987"
    },
    {
      "net_date": "2016-07-21",
      "net_day_growth": "0.0015605749"
    },
    {
      "net_date": "2016-07-22",
      "net_day_growth": "-0.0034443169"
    },
    {
      "net_date": "2016-07-25",
      "net_day_growth": "0.0020572745"
    },
    {
      "net_date": "2016-07-26",
      "net_day_growth": "0.0029563932"
    },
    {
      "net_date": "2016-07-27",
      "net_day_growth": "-0.0090886760"
    },
    {
      "net_date": "2016-07-28",
      "net_day_growth": "0.0010742026"
    },
    {
      "net_date": "2016-07-29",
      "net_day_growth": "-0.0023937268"
    },
    {
      "net_date": "2016-08-01",
      "net_day_growth": "-0.0021512494"
    },
    {
      "net_date": "2016-08-02",
      "net_day_growth": "0.0019071310"
    },
    {
      "net_date": "2016-08-03",
      "net_day_growth": "0.0004138045"
    },
    {
      "net_date": "2016-08-04",
      "net_day_growth": "-0.0011581734"
    },
    {
      "net_date": "2016-08-05",
      "net_day_growth": "-0.0015736293"
    },
    {
      "net_date": "2016-08-08",
      "net_day_growth": "0.0042306097"
    },
    {
      "net_date": "2016-08-09",
      "net_day_growth": "0.0015694697"
    },
    {
      "net_date": "2016-08-10",
      "net_day_growth": "0.0003298969"
    },
    {
      "net_date": "2016-08-11",
      "net_day_growth": "-0.0020611757"
    },
    {
      "net_date": "2016-08-12",
      "net_day_growth": "0.0041308658"
    },
    {
      "net_date": "2016-08-15",
      "net_day_growth": "0.0060885305"
    },
    {
      "net_date": "2016-08-16",
      "net_day_growth": "-0.0004906771"
    },
    {
      "net_date": "2016-08-17",
      "net_day_growth": "0.0013909344"
    },
    {
      "net_date": "2016-08-18",
      "net_day_growth": "-0.0004902361"
    },
    {
      "net_date": "2016-08-19",
      "net_day_growth": "0.0008992071"
    },
    {
      "net_date": "2016-08-22",
      "net_day_growth": "-0.0038386148"
    },
    {
      "net_date": "2016-08-23",
      "net_day_growth": "0.0011478232"
    },
    {
      "net_date": "2016-08-24",
      "net_day_growth": "0.0006551470"
    },
    {
      "net_date": "2016-08-25",
      "net_day_growth": "-0.0020459939"
    },
    {
      "net_date": "2016-08-26",
      "net_day_growth": "0.0007380679"
    },
    {
      "net_date": "2016-08-29",
      "net_day_growth": "-0.0000819471"
    },
    {
      "net_date": "2016-08-30",
      "net_day_growth": "0.0014751680"
    },
    {
      "net_date": "2016-08-31",
      "net_day_growth": "0.0009819967"
    },
    {
      "net_date": "2016-09-01",
      "net_day_growth": "-0.0009810334"
    },
    {
      "net_date": "2016-09-02",
      "net_day_growth": "-0.0005728314"
    },
    {
      "net_date": "2016-09-05",
      "net_day_growth": "0.0009825596"
    },
    {
      "net_date": "2016-09-06",
      "net_day_growth": "0.0016359918"
    },
    {
      "net_date": "2016-09-07",
      "net_day_growth": "-0.0001633320"
    },
    {
      "net_date": "2016-09-08",
      "net_day_growth": "0.0016335865"
    },
    {
      "net_date": "2016-09-09",
      "net_day_growth": "-0.0012231917"
    },
    {
      "net_date": "2016-09-12",
      "net_day_growth": "-0.0097975180"
    },
    {
      "net_date": "2016-09-13",
      "net_day_growth": "0.0031332454"
    },
    {
      "net_date": "2016-09-14",
      "net_day_growth": "-0.0009041591"
    },
    {
      "net_date": "2016-09-19",
      "net_day_growth": "0.0043603455"
    },
    {
      "net_date": "2016-09-20",
      "net_day_growth": "-0.0011467890"
    },
    {
      "net_date": "2016-09-21",
      "net_day_growth": "0.0001640151"
    },
    {
      "net_date": "2016-09-22",
      "net_day_growth": "0.0013938996"
    },
    {
      "net_date": "2016-09-23",
      "net_day_growth": "-0.0009825596"
    },
    {
      "net_date": "2016-09-26",
      "net_day_growth": "-0.0054093927"
    },
    {
      "net_date": "2016-09-27",
      "net_day_growth": "0.0011536877"
    },
    {
      "net_date": "2016-09-28",
      "net_day_growth": "-0.0004938678"
    },
    {
      "net_date": "2016-09-29",
      "net_day_growth": "0.0014823355"
    },
    {
      "net_date": "2016-09-30",
      "net_day_growth": "0.0011512211"
    },
    {
      "net_date": "2016-10-10",
      "net_day_growth": "0.0047638604"
    },
    {
      "net_date": "2016-10-11",
      "net_day_growth": "0.0012261914"
    },
    {
      "net_date": "2016-10-12",
      "net_day_growth": "0.0004082299"
    },
    {
      "net_date": "2016-10-13",
      "net_day_growth": "0.0003264507"
    },
    {
      "net_date": "2016-10-14",
      "net_day_growth": "0.0012237905"
    },
    {
      "net_date": "2016-10-17",
      "net_day_growth": "-0.0029335072"
    },
    {
      "net_date": "2016-10-18",
      "net_day_growth": "0.0054756456"
    },
    {
      "net_date": "2016-10-19",
      "net_day_growth": "0.0020320247"
    },
    {
      "net_date": "2016-10-20",
      "net_day_growth": "0.0001622323"
    },
    {
      "net_date": "2016-10-21",
      "net_day_growth": "-0.0021086780"
    },
    {
      "net_date": "2016-10-24",
      "net_day_growth": "0.0033322497"
    },
    {
      "net_date": "2016-10-25",
      "net_day_growth": "0.0004050223"
    },
    {
      "net_date": "2016-10-26",
      "net_day_growth": "-0.0003238866"
    },
    {
      "net_date": "2016-10-27",
      "net_day_growth": "0.0004049895"
    },
    {
      "net_date": "2016-10-28",
      "net_day_growth": "-0.0023479880"
    },
    {
      "net_date": "2016-10-31",
      "net_day_growth": "0.0019477358"
    },
    {
      "net_date": "2016-11-01",
      "net_day_growth": "0.0038069010"
    },
    {
      "net_date": "2016-11-02",
      "net_day_growth": "-0.0012103607"
    },
    {
      "net_date": "2016-11-03",
      "net_day_growth": "0.0029083858"
    },
    {
      "net_date": "2016-11-04",
      "net_day_growth": "0.0010472048"
    },
    {
      "net_date": "2016-11-07",
      "net_day_growth": "0.0030578579"
    },
    {
      "net_date": "2016-11-08",
      "net_day_growth": "0.0034496590"
    },
    {
      "net_date": "2016-11-09",
      "net_day_growth": "-0.0007994883"
    },
    {
      "net_date": "2016-11-10",
      "net_day_growth": "0.0052008321"
    },
    {
      "net_date": "2016-11-11",
      "net_day_growth": "0.0045371329"
    },
    {
      "net_date": "2016-11-14",
      "net_day_growth": "0.0007923930"
    },
    {
      "net_date": "2016-11-15",
      "net_day_growth": "0.0012668250"
    },
    {
      "net_date": "2016-11-16",
      "net_day_growth": "-0.0002372292"
    },
    {
      "net_date": "2016-11-17",
      "net_day_growth": "0.0000000000"
    },
    {
      "net_date": "2016-11-18",
      "net_day_growth": "-0.0015819030"
    },
    {
      "net_date": "2016-11-21",
      "net_day_growth": "0.0001584409"
    },
    {
      "net_date": "2016-11-22",
      "net_day_growth": "0.0039603960"
    },
    {
      "net_date": "2016-11-23",
      "net_day_growth": "-0.0004733728"
    },
    {
      "net_date": "2016-11-24",
      "net_day_growth": "0.0022101192"
    },
    {
      "net_date": "2016-11-25",
      "net_day_growth": "0.0000000000"
    },
    {
      "net_date": "2016-11-28",
      "net_day_growth": "0.0047255257"
    },
    {
      "net_date": "2016-11-29",
      "net_day_growth": "-0.0057223485"
    },
    {
      "net_date": "2016-11-30",
      "net_day_growth": "-0.0026805424"
    },
    {
      "net_date": "2016-12-01",
      "net_day_growth": "0.0012648221"
    },
    {
      "net_date": "2016-12-02",
      "net_day_growth": "-0.0037107216"
    },
    {
      "net_date": "2016-12-05",
      "net_day_growth": "-0.0019811396"
    },
    {
      "net_date": "2016-12-06",
      "net_day_growth": "0.0007146260"
    },
    {
      "net_date": "2016-12-07",
      "net_day_growth": "0.0029358089"
    },
    {
      "net_date": "2016-12-08",
      "net_day_growth": "-0.0003164557"
    },
    {
      "net_date": "2016-12-09",
      "net_day_growth": "0.0005539728"
    },
    {
      "net_date": "2016-12-12",
      "net_day_growth": "-0.0102823697"
    },
    {
      "net_date": "2016-12-13",
      "net_day_growth": "0.0054343483"
    },
    {
      "net_date": "2016-12-14",
      "net_day_growth": "-0.0047690963"
    },
    {
      "net_date": "2016-12-15",
      "net_day_growth": "0.0027154381"
    },
    {
      "net_date": "2016-12-16",
      "net_day_growth": "0.0011150936"
    },
    {
      "net_date": "2016-12-19",
      "net_day_growth": "0.0031028721"
    },
    {
      "net_date": "2016-12-20",
      "net_day_growth": "-0.0005552030"
    },
    {
      "net_date": "2016-12-21",
      "net_day_growth": "0.0015078168"
    },
    {
      "net_date": "2016-12-22",
      "net_day_growth": "-0.0010301109"
    },
    {
      "net_date": "2016-12-23",
      "net_day_growth": "-0.0049972238"
    },
    {
      "net_date": "2016-12-26",
      "net_day_growth": "0.0030293367"
    },
    {
      "net_date": "2016-12-27",
      "net_day_growth": "-0.0015100938"
    },
    {
      "net_date": "2016-12-28",
      "net_day_growth": "-0.0017511741"
    },
    {
      "net_date": "2016-12-29",
      "net_day_growth": "-0.0011960769"
    },
    {
      "net_date": "2016-12-30",
      "net_day_growth": "0.0006386716"
    },
    {
      "net_date": "2016-12-31",
      "net_day_growth": "0.0000797830"
    },
    {
      "net_date": "2017-01-03",
      "net_day_growth": "0.0048663742"
    }
  ]
};
  /*var fundCode = getQueryString('fundcode');
  $.ajax('http://localhost:8009/jijin/jz_fund/getFundCurve',{
    data: {
      fundCode: fundCode
    },
    dataType: 'json',
    type: 'get',
    async: true,
    success: function(res) {
      console.log(res);
      renderChart(res);
    },
    error: function() {
      alert('请求错误');
    }
  });*/

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

  renderChart(a.data);
  function renderChart(data) {
    var oneData = clone(data);
    var threeData = clone(data);
    var sixData = clone(data);
    var yearData = clone(data);
    var one = oneData.splice(0, oneData.length - 30);
    var three = threeData.splice(0, threeData.length - 90);
    var six = sixData.splice(0,sixData.length - 180);
    var year = yearData.splice(0, yearData.length - 360);
console.log(sixData.length);
    
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