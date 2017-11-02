window.onload = function() {
  var byId = function(id) { //获取id对象
    return document.getElementById(id);
  };

  function getUrlParam(name) { //获取url地址参数
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)"); //构造一个含有目标参数的正则表达式对象
    var r = window.location.search.substr(1).match(reg); //匹配目标参数
    if (r != null) return unescape(r[2]);
    return null; //返回参数值
  }
  var fundcode = getUrlParam("fundcode"); //获取基金代码
  (function queryMange() {
    mui.ajax({
      url: "/jijin/jz_fund/tradeNote/",
      type: "GET",
      dataType: "json",
      data: {
        fundcode: fundcode
      },
      success: function(res) {
        var buyRata = res.data.rate_20_22; //认申购费用
        var costData = res.data.cost; //管理费用
        var feeData = res.data.rate_24; //赎回费用
        var rate = feeData.rate; //赎回费率
        byId("costManage").innerHTML = costData.cost_manage + "%"; //管理费
        byId("costTrustee").innerHTML = costData.cost_trustee + "%"; //托管费
        byId("perMin").innerHTML = feeData.per_min_24 + "份"; //赎回份额
        if (!feeData.date_payment) {
          byId('dayPay').innerHTML = '无赎回';
          byId('rateCont').innerHTML = '无赎回';
          byId('rateCont').style.textAlign = 'center';
        } else {
          byId("dayPay").innerHTML = "T+" + feeData.date_payment //预计赎回到账时间
        }
        var oRateDiv = byId("rateCont");
        var oBuyDiv = byId("buyRate");
        var minBuy = byId("minBuy");
        var buyTitle = document.getElementsByClassName("buyTitle");
        buyTitle[0].innerHTML = buyRata.businesscode + "费用";
        buyTitle[1].innerHTML = buyRata.businesscode + "确认时间";
        buyTitle[2].innerHTML = buyRata.businesscode + "费率";
        minBuy.innerHTML = buyRata.first_per_min + "元";
        byId("buyTime").innerHTML = "T+" + buyRata.confirmed;
        var aRate = buyRata.rate //认申购费率
        var buyRate = byId(buyRate);
        for (var k = 0; k < aRate.length; k++) { //认申购费率
          var len = aRate.length - 1;
          var rateType = "";
          if (aRate[k].rate_type == "0") {
            rateType = "%";
          } else if (aRate[k].rate_type == "1") {
            rateType = "元";
          }
          var rowDiv = document.createElement("div");
          rowDiv.className = "mui-row";
          var div_1 = document.createElement("div");
          var div_2 = document.createElement("div");
          var div_3 = document.createElement("div");
          div_1.className = "mui-col-xs-6 mui-text-center";
          div_2.className = "mui-col-xs-3";
          div_3.className = "mui-col-xs-3 mui-text-right";
          if (k == 0) {
            div_1.innerHTML = aRate[k].downamount + "<=" + buyRata.businesscode + "金额";
            div_2.innerHTML = aRate[k].ratevalue + rateType;
          } else if (k == len) {
            div_1.innerHTML = aRate[k].downamount + "万<=" + buyRata.businesscode + "金额";
            div_2.innerHTML = aRate[k].ratevalue + rateType;
          } else {
            div_1.innerHTML = aRate[k].downamount + "万<=" + buyRata.businesscode + "金额<" + aRate[k].upamount + "万";
            div_2.innerHTML = aRate[k].ratevalue + rateType;
          }

          if (aRate[k].rate_type == 1) {
            div_3.innerHTML = aRate[k].executionrate + "元";
          } else {
            div_3.innerHTML = aRate[k].executionrate + "%";
          }
          rowDiv.appendChild(div_1);
          rowDiv.appendChild(div_2);
          rowDiv.appendChild(div_3);
          oBuyDiv.appendChild(rowDiv);
        }
        for (var i = 0; i < rate.length; i++) { //赎回费用
          if (!rate[i].feepolicy) { //无赎回费率

          } else { //有赎回费率
            var len = rate.length - 1;
            var data = ""
            if (rate[i].feepolicy == "3") {
              data = "日";
            } else if (rate[i].feepolicy == "4") {
              data = "月";
            }
            var rowDiv = document.createElement("div");
            rowDiv.className = "mui-row";
            var div_1 = document.createElement("div");
            var div_2 = document.createElement("div");
            div_1.className = "mui-col-xs-6 mui-text-center";
            div_2.className = "mui-col-xs-6 mui-text-center";
            if (i == 0) {
              div_1.innerHTML = rate[i].downhold + "<持有天数<=" + rate[i].uphold + data;
            } else if (i == len) {
              div_1.innerHTML = rate[i].downhold + data + "<=持有天数";
            } else {
              div_1.innerHTML = rate[i].downhold + data + "<持有天数<=" + rate[i].uphold + data;
            }

            div_2.innerHTML = rate[i].feerate + "%";
            rowDiv.appendChild(div_1);
            rowDiv.appendChild(div_2);
            oRateDiv.appendChild(rowDiv);
          }
        }
      }
    });
  })();
}