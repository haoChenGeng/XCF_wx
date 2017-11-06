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
// console.log(res);
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
  });
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
    ele.innerHTML = '<span>' + data[i].name.replace(/基金/, '') + '</span>';
    ele.href = '#fund' + data[i].type;
    ele.dataset.type = data[i].type;
    wrapNav.appendChild(ele);

    var con = document.createElement('div');
    con.classList.add('mui-slider-item', 'mui-control-content');
    con.id = 'fund' + data[i].type;
    con.innerHTML = '<div class="mui-scroll-wrapper" id="scroll' + data[i].type + '">' +
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

        if (fundtype == 2) {
          listTitle.innerHTML = '<div class="fundlist-name">基金名称</div><div class="fundlist-networth">万分收益(元)</div><div class="fundlist-return">七日年化</div>';
          var frag = document.createElement('ul');
          frag.classList.add('mui-table-view');
          for (var i = 0; i < fundListData.length; i++) {
            var oLi = document.createElement('li');
            oLi.setAttribute('class', 'mui-table-view-cell');
            oLi.innerHTML = '<a href="' + '/application/views/jijin/trade/prodetail.html?fundcode=' + fundListData[i].fundcode + '" class="fundlist-link"><div class="fundlist-name">' + fundListData[i].fundname + '</div><div class="fundlist-networth">' + fundListData[i].fundincomeunit + '</div><div class="fundlist-growthrate">' + (fundListData[i].growthrate) + '%</div></a>';
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

          var dayData = quickSort(fundListData,0,(fundListData.length - 1),'growth_day');
          for (var i = 0; i < dayData.length; i++) {
            var oLiDay = document.createElement('li');
            oLiDay.classList.add('mui-table-view-cell');
            oLiDay.innerHTML = '<a href="' + '/application/views/jijin/trade/prodetail.html?fundcode=' + dayData[i].fundcode + '" class="fundlist-link"><div class="fundlist-name">' + dayData[i].fundname + '</div><div class="fundlist-networth">' + dayData[i].nav + '</div><div class="fundlist-growthrate">' + (dayData[i].growth_day) + '%</div></a>';
            fragDay.appendChild(oLiDay);            
          }
          var oneData = quickSort(fundListData,0,(fundListData.length - 1),'growth_onemonth');
          for (var i = 0; i < oneData.length; i++) {
            var oLiOne = document.createElement('li');
            oLiOne.classList.add('mui-table-view-cell');
            oLiOne.innerHTML = '<a href="' + '/application/views/jijin/trade/prodetail.html?fundcode=' + oneData[i].fundcode + '" class="fundlist-link"><div class="fundlist-name">' + oneData[i].fundname + '</div><div class="fundlist-networth">' + oneData[i].nav + '</div><div class="fundlist-growthrate">' + (oneData[i].growth_onemonth) + '%</div></a>';
            fragOnemonth.appendChild(oLiOne);            
          }
          var threeData = quickSort(fundListData,0,(fundListData.length - 1),'growth_threemonth');
          for (var i = 0; i < threeData.length; i++) {
            var oLiThree = document.createElement('li');
            oLiThree.classList.add('mui-table-view-cell');
            oLiThree.innerHTML = '<a href="' + '/application/views/jijin/trade/prodetail.html?fundcode=' + threeData[i].fundcode + '" class="fundlist-link"><div class="fundlist-name">' + threeData[i].fundname + '</div><div class="fundlist-networth">' + threeData[i].nav + '</div><div class="fundlist-growthrate">' + (threeData[i].growth_threemonth) + '%</div></a>';
            fragThreemonth.appendChild(oLiThree);            
          }
          var sixData = quickSort(fundListData,0,(fundListData.length - 1),'growth_sixmonth');
          for (var i = 0; i < sixData.length; i++) {
            var oLiSix = document.createElement('li');
            oLiSix.classList.add('mui-table-view-cell');
            oLiSix.innerHTML = '<a href="' + '/application/views/jijin/trade/prodetail.html?fundcode=' + sixData[i].fundcode + '" class="fundlist-link"><div class="fundlist-name">' + sixData[i].fundname + '</div><div class="fundlist-networth">' + sixData[i].nav + '</div><div class="fundlist-growthrate">' + (sixData[i].growth_sixmonth) + '%</div></a>';
            fragSixmonth.appendChild(oLiSix);            
          }
          var yearData = quickSort(fundListData,0,(fundListData.length - 1),'growth_year');
          for (var i = 0; i < yearData.length; i++) {
            var oLiYear = document.createElement('li');
            oLiYear.classList.add('mui-table-view-cell');
            oLiYear.innerHTML = '<a href="' + '/application/views/jijin/trade/prodetail.html?fundcode=' + yearData[i].fundcode + '" class="fundlist-link"><div class="fundlist-name">' + yearData[i].fundname + '</div><div class="fundlist-networth">' + yearData[i].nav + '</div><div class="fundlist-growthrate">' + (yearData[i].growth_year) + '%</div></a>';
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
  });
}

function chgIncrease(type, day, one, three, six, year) {
  var wrap = document.getElementById('fund' + type).querySelector('.mui-scroll');
  var select = document.getElementById('fund' + type).querySelector('.fundlist-chg');
  select.addEventListener('change', function(e) {
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
  });
}


function initScroll($) {
  var gallery = mui('.mui-slider');
  gallery.slider();
  document.getElementById('slider').addEventListener('slide', function(e) {
    var con = e.target.querySelector('#sliderSegmentedControl');
    var tar = con.querySelector('.mui-active');
    var load = e.target.querySelector('.mui-slider-group');
    if (load.querySelector('.mui-loading')) {
      renderFundList(tar.dataset.type);
    }
  });
  var sliderSegmentedControl = document.getElementById('sliderSegmentedControl');
  sliderSegmentedControl.addEventListener('tap', function(e) {
    });
}


function quickSort(arr, left, right,a) {
    var len = arr.length,
        partitionIndex,
        left = typeof left !== 'number' ? 0 : left,
        right = typeof right !== 'number' ? len - 1 : right;

    if (left < right) {

        partitionIndex = partition(arr, left, right,a);

        quickSort(arr, left, partitionIndex - 1,a);

        quickSort(arr, partitionIndex + 1, right,a);
    }
    return arr;
}

function partition(arr, left, right,a) {
    var pivot = left,
        index = pivot + 1;
    for (var i = index; i <= right; i++) {
        if (Math.abs(parseFloat(arr[i][a])) > Math.abs(parseFloat(arr[pivot][a]))) {
            swap(arr, i, index);
            index++;
        }
    }
    swap(arr, pivot, index - 1);

    return index - 1;
}

function swap(arr, i, j) {
    var temp = arr[i];
    arr[i] = arr[j];
    arr[j] = temp;
}
