function getQueryString(e) {
  var t = new RegExp("(^|&)" + e + "=([^&]*)(&|$)", "i"),
    o = window.location.search.substr(1).match(t);
  return null != o ? unescape(o[2]) : null;
}

var date = new Date();
date.toLocaleDateString();

var nav = document.getElementById("worthChart").getElementsByTagName("div");  
var con = document.getElementById("chartContent").getElementsByClassName('m-content-t3-chart');
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
  var _echart_width = $(document.body).width() - 20;
  (function() {
    var fundCode = getQueryString('fundcode');
    $.ajax({
      url: '/jijin/jz_fund/getFundCurve',
      data: {
        fundCode: fundCode
      },
      dataType: 'json',
      type: 'get',
      async: true,
      success: function(res) {
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
    
    var oneData = [];
    var threeData = [];
    var halfData = [];
    var today = new Date().valueOf() - 30*24*60*60*1000;
    var three = new Date().valueOf() - 3*30*24*60*60*1000;
    var half = new Date().valueOf() - 6*30*24*60*60*1000;
    var start = new Date(data[0].net_date.replace(/-/g, '/')).valueOf();
    var ii = start;
    for (var i = 0; i < data.length; i++) {
      if (today > start) {
        oneData = [];
        break;
      }else if(today > new Date(data[i].net_date.replace(/-/g, '/')).valueOf()) {
        ii = i;
        break;
      }
    }
    if (i == data.length){
    	ii = i-1; 
    }
    oneData = data.slice(0,ii);
    for (; i < data.length; i++) {
      if (three > start) {
        threeData = [];
        break;
      }else if (three > new Date(data[i].net_date.replace(/-/g, '/')).valueOf()) {
        ii = i;
        break;
      }
    }
    if (i == data.length){
    	ii = i-1; 
    }
    threeData = data.slice(0, ii);
    for (; i < data.length; i++) {
      if (half > start) {
        halfData = [];
        break;
      }else if (half > new Date(data[i].net_date.replace(/-/g, '/')).valueOf()) {
        ii = i;
        break;
      }
    }
    if (i == data.length){
    	ii = i-1; 
    }
    halfData = data.slice(0, ii);
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
        data: [],
        bottom: "10px"
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
        data: []
      },
      yAxis: {
        type: "value"
      },
      series: []
    };

    var opOne = clone(Options);
    var opThree = clone(Options);
    var opSix = clone(Options);
    var opYear = clone(Options);

    opOne.legend.data.push('1个月净值走势');
    opThree.legend.data.push('3个月净值走势');
    opSix.legend.data.push('6个月净值走势');
    opYear.legend.data.push('1年净值走势');

    var a = {
      name: '6个月净值走势',
      type: 'line',
      data: []
    };
    var b = {
      name: '1年净值走势',
      type: 'line',
      data: []
    };
    var c = {
      name: '1个月净值走势',
      type: 'line',
      data: []
    };
    var d = {
      name: '3个月净值走势',
      type: 'line',
      data: []
    };
    for (var i = 0; i < oneData.length; i++) {
      opOne.xAxis.data[i] = oneData[i].net_date.replace(/-/g, '');
      c.data[i] = oneData[i].net_day_growth;
    }
    opOne.xAxis.data.reverse();
    opOne.series.push(c);
    for (var i = 0; i < threeData.length; i++) {
      opThree.xAxis.data[i] = threeData[i].net_date.replace(/-/g, '');
      d.data[i] = threeData[i].net_day_growth;
    }
    opThree.xAxis.data.reverse();
    opThree.series.push(d);
    for (var i = 0; i < halfData.length; i++) {
      opSix.xAxis.data[i] = halfData[i].net_date.replace(/-/g, '');
      a.data[i] = halfData[i].net_day_growth;
    }
    opSix.xAxis.data.reverse();
    opSix.series.push(a);
    for (var i = 0; i < data.length; i++) {
      opYear.xAxis.data[i] = data[i].net_date.replace(/-/g, '');
      b.data[i] = data[i].net_day_growth;
    }
    opYear.xAxis.data.reverse();
    opYear.series.push(b);

    var n1 = echarts.init(document.getElementById("one"));
    var n2 = echarts.init(document.getElementById("three"));
    var n3 = echarts.init(document.getElementById("six"));
    var n4 = echarts.init(document.getElementById("year"));
    $("#one").css({ height: "280px",width: _echart_width}), n1.resize(), n1.setOption(opOne);
    $("#three").css({ height: "280px",width: _echart_width}), n2.resize(), n2.setOption(opThree);
    $("#six").css({ height: "280px",width: _echart_width}), n3.resize(), n3.setOption(opSix);
    $("#year").css({ height: "280px",width: _echart_width}), n4.resize(), n4.setOption(opYear);
  }
});