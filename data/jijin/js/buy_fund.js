window.onload = function() {
  getFundType();
};

function getFundType() {
  var data;
  mui.ajax('/jijin/Jz_fund/getFundData', {
    dataType: 'json',
    type: 'post',
    async: false,
    data: {
      fundtype: 0
    },
    timeout: 10 * 1000,
    success: function(res) {
      console.log(res);
      if (!res) {
        alert('无数据！');
      } else if (res.code !== '0000' || !res.code) {
        alert('返回数据错误');
      } else {
        var filter = document.getElementById('screenFund');
        if (res.qryallfund == 1) {
          filter.innerHTML = '根据证监会适当性管理办法，产品列表已根据您的风险等级作筛选。您已主动要求查看高于您风险等级的产品。<a href="/jijin/jz_fund/viewAllFund">前往了解</a>';
        } else if (res.qryallfund == 0) {
          filter.innerHTML = '根据证监会适当性管理办法，产品列表已根据您的风险等级作筛选。<a href="/jijin/jz_fund/viewAllFund">前往了解</a>';
        } else {
          filter.style.display = 'none';
        }
        renderFundType(res.fundTypes);
        renderFundList(0);
      }
    },
    error: function(xhr) {
      alert('请求错误');
    }
  })
}

function renderFundType(data) {
  if (!data) {
    alert('无基金类型!');
    return false;
  }
  var wrapNav = document.getElementById('sliderSegmentedControl');
  var wrapCon = document.querySelector('.mui-slider-group');
  for (var i = 0; i < data.length; i++) {
    var ele = document.createElement('a');
    ele.classList.add('mui-control-item');
    ele.innerHTML = '<span>' + data[i].replace(/基金/, '') + '</span>';
    ele.href = '#fund' + i;
    wrapNav.appendChild(ele);

    var con = document.createElement('div');
    con.classList.add('mui-slider-item', 'mui-control-content');
    con.id = 'fund' + i;
    con.innerHTML = '<div class="mui-scroll-wrapper" id="scroll' + (i + 1) + '">' +
      '<div class="mui-scroll">' +
      '<ul class="mui-table-view">' +
      '<div class="mui-loading">' +
      '<div class="mui-spinner"></div>' +
      '</div>' +
      '</ul>' +
      '</div>' +
      '</div>';
    var seeHeight = document.documentElement.clientHeight;
    var navHeight = document.querySelector('.mui-bar-tab').clientHeight;
    var sliderHeight = document.getElementById('sliderSegmentedControl').clientHeight;
    var topHeight = document.getElementById('screenFund').clientHeight;
    var h = seeHeight - sliderHeight - navHeight - topHeight;
    con.style.height = h + 'px';
    wrapCon.appendChild(con);
  }
  wrapNav.firstElementChild.classList.add('mui-active');
  wrapCon.firstElementChild.classList.add('mui-active');

  initScroll(mui);
}



function renderFundList(fundtype) {
  mui.ajax('/jijin/Jz_fund/getFundData', {
    dataType: 'json',
    type: 'post',
    async: true,
    data: {
      fundtype: fundtype
    },
    timeout: 10 * 1000,
    success: function(res) {
      var fundListData = res.data[fundtype];
      var listWrap = document.getElementById('fund' + fundtype).querySelector('.mui-scroll');
      var listTitle = document.createElement('div');
      if (!listWrap.parentElement.firstElementChild.classList.contains('fundlist-title')) {
        listTitle.classList.add('fundlist-title');
        listTitle.innerHTML = '<div class="fundlist-name">基金名称</div><div class="fundlist-networth">单位净值(元)</div><select class="fundlist-chg"><option value="1">日涨幅</option><option value="2">近一月</option><option value="3">近三月</option><option value="4">近六月</option><option value="5">近一年</option></select><span></span>';
        listWrap.parentNode.insertBefore(listTitle, listWrap);
      }
      if (!res.data[fundtype]) {
        listWrap.innerHTML = '<p class="fund-list-error"><span>暂无基金</span></p>';
      } else {

        listWrap.innerHTML = '';
        var fundListData = res.data[fundtype];

        if (fundtype == 2) {
          listTitle.innerHTML = '<div class="fundlist-name">基金名称</div><div class="fundlist-networth">万分收益(元)</div><div class="fundlist-return">七日年化</div>';
          var frag = document.createElement('ul');
          frag.classList.add('mui-table-view');
          for (var i = 0; i < fundListData.length; i++) {
            var oLi = document.createElement('li');
            oLi.setAttribute('class', 'mui-table-view-cell');
            oLi.innerHTML = '<a href="' + '/jijin/Jz_fund/showprodetail?fundcode=' + fundListData[i].fundcode + '" class="fundlist-link"><div class="fundlist-name">' + fundListData[i].fundname + '</div><div class="fundlist-networth">' + fundListData[i].fundincomeunit + '</div><div class="fundlist-growthrate">' + (fundListData[i].growthrate * 100).toFixed(2) + '%</div></a>';
            frag.appendChild(oLi);
          }
          listWrap.appendChild(frag);
        } else {

          var fragDay = document.createElement('ul');
          fragDay.classList.add('mui-table-view');
          var fragOnemonth = document.createElement('ul');
          fragOnemonth.classList.add('mui-table-view');
          var fragThreemonth = document.createElement('ul');
          fragThreemonth.classList.add('mui-table-view');
          var fragSixmonth = document.createElement('ul');
          fragSixmonth.classList.add('mui-table-view');
          var fragYear = document.createElement('ul');
          fragYear.classList.add('mui-table-view');

          for (var i = 0; i < fundListData.length; i++) {
            var oLiDay = document.createElement('li');
            oLiDay.classList.add('mui-table-view-cell');
            oLiDay.innerHTML = '<a href="' + '/jijin/Jz_fund/showprodetail?fundcode=' + fundListData[i].fundcode + '" class="fundlist-link"><div class="fundlist-name">' + fundListData[i].fundname + '</div><div class="fundlist-networth">' + fundListData[i].nav + '</div><div class="fundlist-growthrate">' + (fundListData[i].growth_day * 100).toFixed(2) + '%</div></a>';
            fragDay.appendChild(oLiDay);

            var oLiOne = document.createElement('li');
            oLiOne.classList.add('mui-table-view-cell');
            oLiOne.innerHTML = '<a href="' + '/jijin/Jz_fund/showprodetail?fundcode=' + fundListData[i].fundcode + '" class="fundlist-link"><div class="fundlist-name">' + fundListData[i].fundname + '</div><div class="fundlist-networth">' + fundListData[i].nav + '</div><div class="fundlist-growthrate">' + (fundListData[i].growth_onemonth * 100).toFixed(2) + '%</div></a>';
            fragOnemonth.appendChild(oLiOne);

            var oLiThree = document.createElement('li');
            oLiThree.classList.add('mui-table-view-cell');
            oLiThree.innerHTML = '<a href="' + '/jijin/Jz_fund/showprodetail?fundcode=' + fundListData[i].fundcode + '" class="fundlist-link"><div class="fundlist-name">' + fundListData[i].fundname + '</div><div class="fundlist-networth">' + fundListData[i].nav + '</div><div class="fundlist-growthrate">' + (fundListData[i].growth_threemonth * 100).toFixed(2) + '%</div></a>';
            fragThreemonth.appendChild(oLiThree);

            var oLiSix = document.createElement('li');
            oLiSix.classList.add('mui-table-view-cell');
            oLiSix.innerHTML = '<a href="' + '/jijin/Jz_fund/showprodetail?fundcode=' + fundListData[i].fundcode + '" class="fundlist-link"><div class="fundlist-name">' + fundListData[i].fundname + '</div><div class="fundlist-networth">' + fundListData[i].nav + '</div><div class="fundlist-growthrate">' + (fundListData[i].growth_sixmonth * 100).toFixed(2) + '%</div></a>';
            fragSixmonth.appendChild(oLiSix);

            var oLiYear = document.createElement('li');
            oLiYear.classList.add('mui-table-view-cell');
            oLiYear.innerHTML = '<a href="' + '/jijin/Jz_fund/showprodetail?fundcode=' + fundListData[i].fundcode + '" class="fundlist-link"><div class="fundlist-name">' + fundListData[i].fundname + '</div><div class="fundlist-networth">' + fundListData[i].nav + '</div><div class="fundlist-growthrate">' + (fundListData[i].growth_year * 100).toFixed(2) + '%</div></a>';
            fragYear.appendChild(oLiYear);
          }
          listWrap.innerHTML = '';
          listWrap.appendChild(fragDay);
          chgIncrease(fundtype, fragDay, fragOnemonth, fragThreemonth, fragSixmonth, fragYear);
        }
      }
    },
    error: function(res) {
      alert('请求错误，请稍候重试');
    }
  })
}

function chgIncrease(type, day, one, three, six, year) {
  var wrap = document.getElementById('fund' + type).querySelector('.mui-scroll');
  var select = document.getElementById('fund' + type).querySelector('.fundlist-chg');
  select.addEventListener('change', function(e) {
    console.log(e.target.value);
    console.log(one);
    switch (e.target.value) {
      case '1':
        wrap.innerHTML = '';
        wrap.appendChild(day);
        break;
      case '2':
        wrap.innerHTML = '';
        wrap.appendChild(one);
        break;
      case '3':
        wrap.innerHTML = '';
        wrap.appendChild(three);
        break;
      case '4':
        wrap.innerHTML = '';
        wrap.appendChild(six);
        break;
      case '5':
        wrap.innerHTML = '';
        wrap.appendChild(year);
        break;
      default:
        break;
    }
  })
}


function initScroll($) {
  var gallery = mui('.mui-slider');
  gallery.slider();
  document.getElementById('slider').addEventListener('slide', function(e) {
    console.log(e.detail.slideNumber);
    switch (e.detail.slideNumber) {
      case 0:
        break;
      case 1:
        renderFundList(1);
        break;
      case 2:
        renderFundList(2);
        break;
      case 3:
        renderFundList(3);
        break;
      case 4:
        renderFundList(4);
        break;
      case 5:
        renderFundList(5);
        break;
      case 6:
        renderFundList(6);
        break;
      case 7:
        renderFundList(7);
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
};

/* var seeHeight = document.documentElement.clientHeight,
  sliderHeight = document.getElementById('sliderSegmentedControl').clientHeight,
  navHeight = document.querySelector('.mui-bar-tab').clientHeight,
  topHeight = 0;
control = document.querySelectorAll('.mui-control-content');

var h = seeHeight - sliderHeight - navHeight - 2;
for (var i = control.length - 1; i >= 0; i--) {
  control[i].style.height = h + 'px';
}


var firstPage = function() {
  mui.ajax('/jijin/Jz_fund/getFundPageData/buy', {
    data: {},
    dataType: 'json',
    type: 'post',
    timeout: 30 * 1000,
    success: function(data) {
      if (!data) {
        var fundList = document.getElementById('scroll1');
        fundList.innerHTML = '<a class="fund-list-error">查询基金失败</a>';
      } else {
        var listWrap = document.getElementById('subscribe');
        var fragment = document.createDocumentFragment();
        if (!data.buy.data) {
          listWrap.innerHTML = '<p class="fund-list-error"><span>暂无基金</span></p>';
        } else {
          listWrap.innerHTML = '';
          for (var i = data.buy.data.length - 1; i >= 0; i--) {
            var oLi = document.createElement('li');
            oLi.setAttribute('class', 'mui-table-view-cell');
            oLi.innerHTML = '<div class="mui-media-body clear">' +
              '<p>名称：<span>' + data.buy.data[i].fundname + '</span><span style="float:right;margin-right:20px;color:red;">' + data.buy.data[i].risklevel + '</span></p>' +
              '<p>代码：<span>' + data.buy.data[i].fundcode + '</span></p>' +
              '<p>净值：<span>' + data.buy.data[i].nav + '</span></p>' +
              '<p>类型：<span>' + data.buy.data[i].fundtypename + '</span></p>' +
              '<a href="' + '/jijin/Jz_fund/showprodetail?fundcode=' + data.buy.data[i].fundcode + '&tano=' + data.buy.data[i].tano + '&fundcode=' + data.buy.data[i].fundcode + '&purchasetype=认购' + ' " type="button" class="mui-btn mui-btn-success fund-btn-bk bonus-pad">详情</a>' +
              '<a href="' + '/jijin/PurchaseController/Apply?fundcode=' + data.buy.data[i].fundcode + '&purchasetype=认购' + ' " type="button" class="mui-btn mui-btn-success fund-btn-bd">认购</a>' +
              '</div>';
            fragment.appendChild(oLi);
          }
        }
        listWrap.appendChild(fragment);
      }
    },
    error: function() {
      alert('查询失败，请稍后重试！');
    }
  });
};

var item2 = document.getElementById('item2mobile'),
  item3 = document.getElementById('item3mobile'),
  item4 = document.getElementById('item4mobile');

var page2 = function() {
  mui.ajax('/jijin/Jz_fund/getFundData', {
    data: {
      fundtype: 2
    },
    dataType: 'json',
    type: 'post',
    timeout: 60 * 1000,
    success: function(data) {
      if (!data) {
        var fundList = document.getElementById('scroll2');
        fundList.innerHTML = '<p class="fund-list-error">查询基金失败</p>';
      } else {
        var nodeWrap = item2.querySelector('.mui-scroll'),
          nodeChlid = item2.querySelector('.mui-scroll').childNodes;
        var listWrap = document.getElementById('apply');
        var fragment = document.createDocumentFragment();
        if (0 === data.apply.length) {
          listWrap.innerHTML = '<p class="fund-list-error"><span>暂无基金</span></p>';
        } else {
          listWrap.innerHTML = '';

          var fundContent = document.createElement('div');
          fundContent.classList.add('mui-content');
          var fundType = document.createElement('div');
          fundType.id = 'segmentedControlApply';
          fundType.classList.add('mui-segmented-control');
          var fundListContent = document.createElement('div');
          fundListContent.classList.add('fund-list-content');

          for (var i = data.apply.data.length - 1; i >= 0; i--) {
            var oLi = document.createElement('li');
            oLi.setAttribute('class', 'mui-table-view-cell');
            oLi.innerHTML = '<div class="mui-media-body clear">' +
              '<p>名称：<span>' + data.apply.data[i].fundname + '</span><span style="float:right;margin-right:20px;color:red;">' + data.apply.data[i].risklevel + '</span></p>' +
              '<p>代码：<span>' + data.apply.data[i].fundcode + '</span></p>' +
              '<p>净值：<span>' + data.apply.data[i].nav + '</span></p>' +
              '<p>类型：<span>' + data.apply.data[i].fundtypename + '</span></p>' +
              '<a href="' + '/jijin/Jz_fund/showprodetail?fundcode=' + data.apply.data[i].fundcode + '&tano=' + data.apply.data[i].tano + '&fundcode=' + data.apply.data[i].fundcode + '&purchasetype=申购' + '" type="button" class="mui-btn mui-btn-success fund-btn-bk bonus-pad">详情</a>' +
              '<a href="' + '/jijin/PurchaseController/Apply?fundcode=' + data.apply.data[i].fundcode + '&purchasetype=申购' + ' " type="button" class="mui-btn mui-btn-success fund-btn-bd">申购</a>' +
              '</div>';
            fragment.appendChild(oLi);
          }
        }
        listWrap.appendChild(fragment);
      }
    },
    error: function() {
      alert('查询失败，请稍后重试！');
      document.getElementById('scroll2').querySelector('.mui-loading').style.display = 'none';
    }
  });
};

page2();

var page3 = function() {
  mui.ajax('/jijin/Jz_fund/getFundPageData/today', {
    data: {},
    dataType: 'json',
    type: 'post',
    timeout: 30 * 1000,
    success: function(data) {
      var nodeWrap = item3.querySelector('.mui-scroll'),
        nodeChlid = item3.querySelector('.mui-scroll').childNodes;
      nodeWrap.removeChild(nodeChlid[1]);
      var listWrap = document.getElementById('today');
      var fragment = document.createDocumentFragment();
      if (data.msg || !data.today.data) {
        if (data.msg) {
          listWrap.innerHTML = '<a class="fund-list-error" href="' + '/jijin/Jz_account/register?next_url=jz_fund&fundPageOper=today" id="errorMsg">' + data.msg + '</a>';
        } else {
          listWrap.innerHTML = '<p class="fund-list-error"><span>无记录</span></p>';
        }
      } else {
        for (var i = data.today.data.length - 1; i >= 0; i--) {
          var oLi = document.createElement('li');
          oLi.setAttribute('class', 'mui-table-view-cell query-padding');
          var paystatus = data.today.data[i].paystatus ? '/' + data.today.data[i].paystatus : '';
          oLi.innerHTML = '<div class="query-link">' +
            '<div class="mui-media-body clear delegate">' +
            '<div class="delegate-name">' + data.today.data[i].fundname + '/' + data.today.data[i].fundcode + '</div>' +
            '<div class="delegate-oprate">' + data.today.data[i].businesscode + '</div>' +
            '<div class="delegate-amount">' + data.today.data[i].applicationamount + '/' + data.today.data[i].applicationvol + '</div>' +
            '<div class="delegate-oprate">' + data.today.data[i].status + paystatus + '</div>' +
            '<div class="delegate-more"><a type="button" class="cancel-order" href="' + '/jijin/CancelApplyController/cancel?appsheetserialno=' + data.today.data[i].appsheetserialno + '">撤销</a>' +
            '</div>' +
            '</div>';
          if (data.today.data[i].cancelable === 0) {
            oLi.querySelector('.delegate-more').innerHTML = "";
          }
          fragment.appendChild(oLi);
        }
      }
      listWrap.appendChild(fragment);
    },
    error: function() {
      alert('查询失败，请稍后重试！');
      document.getElementById('scroll3').querySelector('.mui-loading').style.display = 'none';
    }
  });
};

var page4 = function(a, b) {
  mui.ajax('/jijin/Jz_fund/getFundPageData/history' + '/' + a.replace(/\-/g, "") + '/' + b.replace(/\-/g, ""), {
    data: {},
    dataType: 'json',
    type: 'get',
    timeout: 10 * 1000,
    beforeSend: function() {
      document.getElementById('scroll4').querySelector('.mui-loading').style.display = "block";
    },
    success: function(data) {
      document.getElementById('scroll4').querySelector('.mui-loading').style.display = "none";
      var listWrap = document.getElementById('history');
      var fragment = document.createDocumentFragment();
      if (data.msg || !data.history.data) {
        if (data.msg) {
          listWrap.innerHTML = '<a class="fund-list-error" href="' + '/jijin/Jz_account/register" id="errorMsg">' + data.msg + '</a>';
        } else {
          listWrap.innerHTML = '<p class="fund-list-error"><span>无记录</span></p>';
        }
      } else {
        for (var i = data.history.data.length - 1; i >= 0; i--) {
          var oLi = document.createElement('li');
          oLi.setAttribute('class', 'mui-table-view-cell query-padding');
          oLi.innerHTML = '<li class="mui-table-view-cell mui-collapse">' +
            '<a href="###" class="query-link mui-navigate-right" style="white-space:normal;">' +
            '<div class="mui-media-body clear delegate">' +
            '<div class="delegate-date">' + data.history.data[i].operdate + '</div>' +
            '<div class="delegate-name">' + data.history.data[i].fundname + '/' + data.history.data[i].fundcode + '</div>' +
            '<div class="delegate-oprate">' + data.history.data[i].businesscode + '</div>' +
            '<div class="delegate-amount">' + data.history.data[i].applicationamount + '/' + data.history.data[i].applicationvol + '</div>' +
            '<div class="delegate-more history"></div>' +
            '</div>' +
            '</a>' +
            '<div class="mui-collapse-content">' +
            '<p class="trade-num">申请单号：' + data.history.data[i].appsheetserialno + '</p>' +
            '<p class="trade-date">交易日期/状态：' + data.history.data[i].transactiondate + '/' + data.history.data[i].status + '</p>' +
            '<p class="trade-other">基金代码：</p>' +
            '</div>' +
            '</li>';
          var tradeOther = oLi.querySelector('.trade-other');
          if (data.history.data[i].paystatus) {
            tradeOther.innerHTML = '支付状态：' + data.history.data[i].paystatus;
          } else if (data.history.data[i].targetfundcode) {
            tradeOther.innerHTML = '目标基金代码：' + data.history.data[i].targetfundcode;
          } else if (data.history.data[i].defdividendmethod) {
            tradeOther.innerHTML = '分红方式：' + data.history.data[i].defdividendmethod;
          } else {
            tradeOther.innerHTML = "";
          }
          if (data.history.data[i].transactioncfmdate) {
            var addParent = oLi.querySelector('.mui-collapse-content');
            var confirmDate = document.createElement('p');
            var confirmEdvol = document.createElement('p');
            var confirmedAmount = document.createElement('p');
            var charge = document.createElement('p');
            confirmDate.setAttribute('class', 'trade-other');
            confirmEdvol.setAttribute('class', 'trade-other');
            confirmedAmount.setAttribute('class', 'trade-other');
            charge.setAttribute('class', 'trade-other');
            confirmDate.innerHTML = '确认交易日期：' + data.history.data[i].transactioncfmdate;
            confirmEdvol.innerHTML = '确认份额：' + data.history.data[i].confirmedvol;
            confirmedAmount.innerHTML = '确认金额：' + data.history.data[i].confirmedamount;
            charge.innerHTML = '手续费：' + data.history.data[i].charge;
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
    },
  });
} */

/* var active = document.getElementById('slider').querySelector('.mui-active');
switch (active.hash) {
  case '#item1mobile':
    firstPage();
    break;
  case '#item2mobile':
    page2();
    break;
  case '#item3mobile':
    page3();
    break;
  case '#item4mobile':
    page4();
    break;
  default:
    break;
} */