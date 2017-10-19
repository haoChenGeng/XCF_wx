window.onload = function() {
  var seeHeight = document.documentElement.clientHeight,
    sliderHeight = document.getElementById('sliderSegmentedControl').clientHeight,
    navHeight = document.querySelector('.mui-bar-tab').clientHeight,
    topHeight = document.getElementById('header').clientHeight,
    control = document.querySelectorAll('.mui-control-content');

  var h = seeHeight - sliderHeight - navHeight - topHeight - 2;
  for (var i = control.length - 1; i >= 0; i--) {
    control[i].style.height = h + 'px';
  }

  getUserInfo();

  function getUserInfo() {
    mui.ajax('/jijin/jz_my/myFundInfo', {
      data: {

      },
      dataType: 'json',
      type: 'get',
      timeout: 10 * 1000,
      success: function(res) {
        if (!res.code) {
          alert('获取数据错误');
        } else {
          document.getElementById('totalBalance').innerHTML = res.totalfundvolbalance || 0;
          document.getElementById('yesterDayIncome').innerHTML = res.yestincomesum || 0;
          document.getElementById('totalIncome').innerHTML = res.addincomesum || 0;
          document.getElementById('customerName').innerHTML = res.customerName || '未登录';

          switch (res.activePage) {
            case 'asset':
              document.getElementsByClassName('mui-control-item')[0].classList.add('mui-active');
              document.getElementById('item1mobile').classList.add('mui-active');
              page1();
              break;
            case 'bonus':
              document.getElementById('item2mobile').classList.add('mui-active');
              document.getElementsByClassName('mui-control-item')[1].classList.add('mui-active');
              page2();
              break;
            case 'account':
              document.getElementById('item3mobile').classList.add('mui-active');
              document.getElementsByClassName('mui-control-item')[2].classList.add('mui-active');
              page3();
              break;
            case 'history':
              document.getElementById('item4mobile').classList.add('mui-active');
              document.getElementsByClassName('mui-control-item')[3].classList.add('mui-active');
              page4();
              break;
            default:
              break;
          }
        }
      },
      error: function(xhr) {
        alert('请求错误，请稍后重试');
      }
    })
  }

  function page1() {
    mui.ajax('/jijin/Jz_my/getMyPageData/fund', {
      data: {},
      dataType: 'json',
      type: 'post',
      timeout: 30 * 1000,
      success: function(data) {
        if (data.code == '9999') {
          document.getElementById('scroll1').innerHTML = '<a class="fund-list-error" href="/user/login" id="errorMsg">' + data.msg + '</a>';
        } else if (data.code == '8888') {
          var fundList = document.getElementById('scroll1');
          fundList.innerHTML = '<a class="fund-list-error" href="/jijin/Jz_account/register?next_url=jz_my&myPageOper=asset" id="errorMsg">' + data.msg + '</a>';
        } else {
          var listWrap = document.getElementById('buyFundList');
          var fragment = document.createDocumentFragment();
          if (!data.fund_list.data.length) {
            var oLi = document.createElement('li');
            oLi.setAttribute('class', 'mui-table-view-cell');
            oLi.innerHTML = '<p class="fund-list-error"><span>未购买任何基金</span></p>';
            fragment.appendChild(oLi);
          } else {
            for (var i = data.fund_list.data.length - 1; i >= 0; i--) {
              if (0 === data.fund_list.data[i].length) continue;
              var oLi = document.createElement('li');
              oLi.setAttribute('class', 'mui-table-view-cell');
              oLi.innerHTML = '<div class="mui-media-body clear">' +
                '<a type="button" href="' + '/jijin/RedeemFundController/Redeem?json=' + data.fund_list.data[i].json + '" class="mui-btn mui-btn-success fund-btn-redeem">赎回</a>' +
                '<p class="clear" style="height:21px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">名称:' + data.fund_list.data[i].fundname + '(' + data.fund_list.data[i].fundcode + ')<span style="float:right;color:red;margin-right:-75px;">' + data.fund_list.data[i].riskDes + '</span></p>' +
                '<p>净值/份额：<span>' + data.fund_list.data[i].nav + '/' + data.fund_list.data[i].fundvolbalance + '</span></p>' +
                '<p>昨日收益：<span>' + data.fund_list.data[i].yestincome + '</span></p>' +
                '<p>累计收益：<span>' + data.fund_list.data[i].addincome + '</span></p>' +
                '</div>';
              if (data.fund_list.data[i].redeem != 'Y') {
                oLi.querySelector('.fund-btn-redeem').href = "javascript:;";
                oLi.querySelector('.fund-btn-redeem').style.cssText = "border-color:#ccc;color:#ccc;";
              }
              fragment.appendChild(oLi);
            }
          }
          listWrap.innerHTML = "";
          listWrap.appendChild(fragment);
        }
      },
      error: function() {
        alert('查询失败，请稍后重试！');
      }
    });
  }

  function page2() {
    mui.ajax('/jijin/Jz_my/getMyPageData/bonus_change', {
      data: {},
      dataType: 'json',
      type: 'post',
      timeout: 30 * 1000,
      success: function(data) {
        if (data.code == '9999') {
          document.getElementById('scroll2').innerHTML = '<a class="fund-list-error" href="/user/login" id="errorMsg">' + data.msg + '</a>';
        } else if (data.code == '8888') {
          var fundList = document.getElementById('scroll2');
          fundList.innerHTML = '<a class="fund-list-error" href="' + '/jijin/Jz_account/register?next_url=jz_my&myPageOper=bonus" id="errorMsg">' + data.msg + '</a>';
        } else {
          var listWrap = document.getElementById('bonus-mod');
          var fragment = document.createDocumentFragment();
          var oLi = document.createElement('li');
          if (0 === data.bonus_change.data.length) {
            oLi.setAttribute('class', 'mui-table-view-cell');
            oLi.innerHTML = '<p class="fund-list-error"><span>未购买任何基金</span></p>';
            fragment.appendChild(oLi);
          } else {
            for (var i = data.bonus_change.data.length - 1; i >= 0; i--) {
              oLi.setAttribute('class', 'mui-table-view-cell');
              oLi.innerHTML = '<div class="mui-media-body clear">' +
                '<a type="button" href="' + '/jijin/ModifyBonusController/Modify?json=' + data.bonus_change.data[i].json + '" class="mui-btn mui-btn-success fund-btn-bc">修改分红</a>' +
                '<p style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">名称：<span>' + data.bonus_change.data[i].fundname + '</span></p>' +
                '<p>代码：<span>' + data.bonus_change.data[i].fundcode + '</span></p>' +
                '<p>净值：<span>' + data.bonus_change.data[i].nav + '</span></p>' +
                '<p>分红方式：<span>' + data.bonus_change.data[i].dividendmethodname + '</span></p>' +
                '</div>';
              fragment.appendChild(oLi);
            }
          }
          listWrap.innerHTML = "";
          listWrap.appendChild(fragment);
        }
      },
      error: function() {
        alert('查询失败，请稍后重试！');
      }
    });
  }

  function page3() {
    mui.ajax('/jijin/Jz_my/getMyPageData/risk_test', {
      data: {},
      dataType: 'json',
      type: 'post',
      timeout: 30 * 1000,
      success: function(data) {
        var nodeWrap = item3.querySelector('.mui-scroll'),
          nodeChlid = item3.querySelector('.mui-scroll').childNodes;
        nodeWrap.removeChild(nodeChlid[1]);
        if (data.code == '9999') {
          document.getElementById('scroll3').innerHTML = '<a class="fund-list-error" href="/user/login" id="errorMsg">' + data.msg + '</a>';
        } else if (data.code == '8888') {
          var fundList = document.getElementById('scroll3');
          fundList.innerHTML = '<a class="fund-list-error" href="' + '/jijin/Jz_account/register?next_url=jz_my&myPageOper=account" id="errorMsg">' + data.msg + '</a>';
        } else {
          var risk = document.getElementById('risk_result');
          risk.innerHTML = '风险测试[' + data.custrisk + ':' + data.custriskname + ']';
        }
      },
      error: function() {
        alert('查询失败，请稍后重试！');
      }
    });
  }

  function page4(a, b) {
    mui.ajax('/jijin/Jz_my/getHistoryApply', {
      data: {
        startDate: a.replace(/\-/g, ""),
        endDate: b.replace(/\-/g, "")
      },
      dataType: 'json',
      type: 'post',
      timeout: 10 * 1000,
      beforeSend: function() {
        document.getElementById('scroll4').querySelector('.mui-loading').style.display = "block";
      },
      success: function(res) {
        // res = mockData;
        // console.log(res);
        document.getElementById('scroll4').querySelector('.mui-loading').style.display = "none";
        var listWrap = document.getElementById('history');
        var fragment = document.createDocumentFragment();
        if (res.code == '9999') {
          listWrap.innerHTML = '<a class="fund-list-error" href="/user/login" id="errorMsg">' + res.msg + '</a>';
        } else if (res.code == '8888') {
          listWrap.innerHTML = '<a class="fund-list-error" href="' + '/jijin/Jz_account/register" id="errorMsg">' + res.msg + '</a>';
        } else {
          if (!res.data.length) {
            listWrap.innerHTML = '<p class="fund-list-error"><span>无记录</span></p>';
          } else {
            for (var i = res.data.length - 1; i >= 0; i--) {
              var oLi = document.createElement('li');
              oLi.setAttribute('class', 'mui-table-view-cell query-padding mui-collapse');
              oLi.innerHTML = '<a class="mui-navigate-right history-arrow" href="###">' +
                '<div class="mui-media-body clear history-detail-title">' +
                '<div class="delegate-name">' + res.data[i].fundname + '<br>' + res.data[i].operdate + ' ' + res.data[i].opertime + '</div>' +
                '<div class="delegate-oprate">' + res.data[i].businesscode + '<br><span class="cancel-status">可撤单</span></div>' +
                '<div class="delegate-amount">' + res.data[i].applicationamount + '/' + res.data[i].applicationvol + '</div>' +
                '</div>' +
                '</a>' +
                '<div class="mui-collapse-content history-detail-content">' +
                '<p class="trade-num">申请单号：' + res.data[i].appsheetserialno + '</p>' +
                '<p class="trade-date">交易日期/状态：' + res.data[i].transactiondate + '/' + res.data[i].status + '/<a type="button" class="cancel-order" href="' + '/jijin/CancelApplyController/cancel?appsheetserialno=' + res.data[i].appsheetserialno + '">撤单</a></p>' +
                '<p class="trade-other">基金代码：</p>' +
                '</div>';
              if (res.data[i].cancelable !== 1) {
                oLi.querySelector('.cancel-status').style.display = 'none';
                oLi.querySelector('.cancel-order').style.display = 'none';
              }
              var tradeOther = oLi.querySelector('.trade-other');
              if (res.data[i].paystatus) {
                tradeOther.innerHTML = '支付状态：' + res.data[i].paystatus;
              } else if (res.data[i].targetfundcode) {
                tradeOther.innerHTML = '目标基金代码：' + res.data[i].targetfundcode;
              } else if (res.data[i].defdividendmethod) {
                tradeOther.innerHTML = '分红方式：' + res.data[i].defdividendmethod;
              } else {
                tradeOther.innerHTML = "";
              }
              if (res.data[i].transactioncfmdate) {
                var addParent = oLi.querySelector('.mui-collapse-content');
                var confirmDate = document.createElement('p');
                var confirmEdvol = document.createElement('p');
                var confirmedAmount = document.createElement('p');
                var charge = document.createElement('p');
                confirmDate.setAttribute('class', 'trade-other');
                confirmEdvol.setAttribute('class', 'trade-other');
                confirmedAmount.setAttribute('class', 'trade-other');
                charge.setAttribute('class', 'trade-other');
                confirmDate.innerHTML = '确认交易日期：' + res.data[i].transactioncfmdate;
                confirmEdvol.innerHTML = '确认份额：' + res.data[i].confirmedvol;
                confirmedAmount.innerHTML = '确认金额：' + res.data[i].confirmedamount;
                charge.innerHTML = '手续费：' + res.data[i].charge;
                addParent.appendChild(confirmDate);
                addParent.appendChild(confirmEdvol);
                addParent.appendChild(confirmedAmount);
                addParent.appendChild(charge);
              }
              fragment.appendChild(oLi);
            }
            listWrap.innerHTML = "";
            listWrap.appendChild(fragment);
          }
        }
      },
    });
  }

  var item1 = document.getElementById('item1mobile');
  var item2 = document.getElementById('item2mobile');
  var item3 = document.getElementById('item3mobile');
  (function($) {
    document.getElementById('slider').addEventListener('slide', function(e) {
      switch (e.detail.slideNumber) {
        case 0:
          if (item1.querySelector('.mui-loading')) {
            page1();
          }
          break;
        case 1:
          if (item2.querySelector('.mui-loading')) {
            page2();
          }
          break;
        case 2:
          if (item3.querySelector('.mui-loading')) {
            page3();
          }
          break;
        case 3:
          var picker = new $.DtPicker({ type: 'date' });
          var now = picker.getSelected().value;
          var startDate = document.getElementById('begin');
          var endDate = document.getElementById('end');
          startDate.innerHTML = now;
          endDate.innerHTML = now;
          page4(now, now);
          var search = document.getElementById('search');
          search.addEventListener('tap', function() {
            if (parseInt(endDate.innerHTML.replace(/\-/g, ""), 10) - parseInt(startDate.innerHTML.replace(/\-/g, ""), 10) < 0) {
              alert('日期选择错误，请重新选择');
            } else {
              document.getElementById('scroll4').querySelector('.mui-loading').style.display = "block";
              page4(startDate.innerHTML, endDate.innerHTML);
            }
          });
          break;
        default:
          break;
      }
    });
    var sliderSegmentedControl = document.getElementById('sliderSegmentedControl');
    $('.mui-input-group').on('change', 'input', function() {
      if (this.checked) {
        sliderSegmentedControl.className = 'mui-slider-indicator mui-segmented-control mui-segmented-control-inverted mui-segmented-control-' + this.value;
        //force repaint
        sliderProgressBar.setAttribute('style', sliderProgressBar.getAttribute('style'));
      }
    });
  })(mui);
};

(function($) {
  $.init();
  var btns = $('.btn');
  btns.each(function(i, btn) {
    btn.addEventListener('tap', function() {
      var optionsJson = this.getAttribute('data-options') || '{}';
      var options = JSON.parse(optionsJson);
      var id = this.getAttribute('id');
      var showRs = this;
      /*
       * 首次显示时实例化组件
       * 示例为了简洁，将 options 放在了按钮的 dom 上
       * 也可以直接通过代码声明 optinos 用于实例化 DtPicker
       */
      var picker = new $.DtPicker(options);
      // console.log(picker.getSelected())
      picker.show(function(rs) {
        /*
         * rs.value 拼合后的 value
         * rs.text 拼合后的 text
         * rs.y 年，可以通过 rs.y.vaue 和 rs.y.text 获取值和文本
         * rs.m 月，用法同年
         * rs.d 日，用法同年
         * rs.h 时，用法同年
         * rs.i 分（minutes 的第二个字母），用法同年
         */
        showRs.innerHTML = rs.text;

        /* 
         * 返回 false 可以阻止选择框的关闭
         * return false;
         */
        /*
         * 释放组件资源，释放后将将不能再操作组件
         * 通常情况下，不需要示放组件，new DtPicker(options) 后，可以一直使用。
         * 当前示例，因为内容较多，如不进行资原释放，在某些设备上会较慢。
         * 所以每次用完便立即调用 dispose 进行释放，下次用时再创建新实例。
         */
        picker.dispose();
      });
    }, false);
  });
})(mui);


var mockData = {
  "data": [{
      "operdate": "2017-10-12",
      "opertime": "17:22:46",
      "fundname": "大成标普500指数基金",
      "fundcode": "096001",
      "applicationamount": "111.00",
      "applicationvol": "--",
      "appsheetserialno": "201708290000000006438622",
      "businesscode": "申购申请",
      "cancelable": 1,
      "transactiondate": "2017-08-29",
      "status": "待报",
      "paystatus": "支付成功"
    },
    {
      "operdate": "2017-10-12",
      "opertime": "17:21:41",
      "fundname": "大成标普500指数基金",
      "fundcode": "096001",
      "applicationamount": "111.00",
      "applicationvol": "--",
      "appsheetserialno": "201708290000000006338622",
      "businesscode": "申购申请",
      "cancelable": 1,
      "transactiondate": "2017-08-29",
      "status": "待报",
      "paystatus": "支付成功"
    },
    {
      "operdate": "2017-10-12",
      "opertime": "17:11:30",
      "fundname": "大成债券基金C级",
      "fundcode": "092002",
      "applicationamount": "111.00",
      "applicationvol": "--",
      "appsheetserialno": "201708290000000006238622",
      "businesscode": "申购申请",
      "cancelable": 1,
      "transactiondate": "2017-08-29",
      "status": "待报",
      "paystatus": "支付成功"
    },
    {
      "operdate": "2017-10-12",
      "opertime": "17:10:52",
      "fundname": "大成标普500指数基金",
      "fundcode": "096001",
      "applicationamount": "111.00",
      "applicationvol": "--",
      "appsheetserialno": "201708290000000006138622",
      "businesscode": "申购申请",
      "cancelable": 1,
      "transactiondate": "2017-08-29",
      "status": "待报",
      "paystatus": "支付成功"
    },
    {
      "operdate": "2017-10-12",
      "opertime": "17:10:52",
      "fundname": "大成标普500指数基金",
      "fundcode": "096001",
      "applicationamount": "111.00",
      "applicationvol": "--",
      "appsheetserialno": "201708290000000006138622",
      "businesscode": "申购申请",
      "cancelable": 1,
      "transactiondate": "2017-08-29",
      "status": "待报",
      "paystatus": "支付成功"
    }
  ],
  "code": "0000",
  "msg": "查询交易明细成功"
};