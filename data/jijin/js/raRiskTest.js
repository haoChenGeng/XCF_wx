window.onload = function() {
  mui.ajax('/jijin/Risk_assessment/getRiskQuestion', {
    data: {

    },
    dataType: 'json',
    type: 'GET',
    timeout: 10000,
    async: false,
    success: function(res) {
      // console.log(res);
      if (!res || res.code !== '0000') {
        alert('获取题目出错，请稍后再试！')
      } else {
        renderTest(res.data);
      }
    },
    error: function(xhr) {
      console.log('error');
    }
  })
}

function renderTest(data) {
  if (!data) {
    alert('无题目！')
    return false;
  }
  var frag = document.createDocumentFragment();
  for (var i = 0; i < data.length; i++) {
    var element = document.createElement('div');
    element.id = 'ques-' + data[i].id;
    element.classList.add('mui-page');
    var oLi = [];
    for (var j = 0; j < data[i].result.length; j++) {
      oLi.push('<li class="mui-table-view-cell test-li">' +
        '<a href="#ques-' + (i + 2) + '" class="test-item">' +
        '<label class="test-label">' + data[i].result[j].resultcontent + '</label><input type="radio" name="q-' + (i + 1) + '" data-num="' + data[i].questioncode + '" value="' + data[i].result[j].result + '" data-point="' + data[i].result[j].resultpoint + '">' +
        '</a>' +
        '</li>');

    }
    // oLi.reverse();
    var item = '<div class="mui-page-content">' +
      '<div class="mui-scroll-wrapper">' +
      '<p class="test-num"><span>' + (i + 1) + '</span>/' + data.length + '</p>' +
      '<div class="mui-scroll">' +
      '<p class="test-ques">' + data[i].questionname + '</p>' +
      '<ul class="mui-table-view">' + oLi.join('') + '</ul>' +
      '<a href="javascript:;" class="start-btn mui-action-back">上一题</a>' +
      '</div>' +
      '</div>' +
      '</div>';
    // console.log(i);
    if (i == 0) {
      item = '<div class="mui-page-content">' +
        '<div class="mui-scroll-wrapper">' +
        '<p class="test-num"><span>' + (i + 1) + '</span>/' + data.length + '</p>' +
        '<div class="mui-scroll">' +
        '<p class="test-ques">' + data[i].questionname + '</p>' +
        '<ul class="mui-table-view">' + oLi.join('') + '</ul>' +
        '<a href="roboAdvisor.html" class="start-btn mui-action-back">返回</a>' +
        '</div>' +
        '</div>' +
        '</div>';
    }
    if (i == (data.length - 1)) {
      item = '<div class="mui-page-content">' +
        '<div class="mui-scroll-wrapper">' +
        '<p class="test-num"><span>' + (i + 1) + '</span>/' + data.length + '</p>' +
        '<div class="mui-scroll">' +
        '<p class="test-ques">' + data[i].questionname + '</p>' +
        '<ul class="mui-table-view">' + oLi.join('') + '</ul>' +
        '<a href="javascript:;" class="start-btn" id="testSubmit">提交</a>' +
        '</div>' +
        '<a href="javascript:;" class="mui-action-back last-btn">上一题</a>'
      '</div>' +
      '</div>';
    }
    element.innerHTML = item;
    // console.log(item);
    frag.appendChild(element);
  }
  // console.log(frag);
  var app = document.getElementById('app');
  app.parentNode.insertBefore(frag, app.nextSibling);
  initView();
}

function initView() {
  //初始化单页view
  var viewApi = mui('#app').view({
    defaultPage: '#ques-1'
  });

  var res = [];
  document.getElementById('app').addEventListener('tap', function(e) {
    console.log(e.target);
    var self = e.target;

    if (self.classList.contains('test-item')) {
      var ret = self.parentNode.parentNode.querySelector('.test-active');
      if (ret) {
        ret.classList.remove('test-active');
      }
      self.parentNode.classList.add('test-active');
      var targetInput = self.firstElementChild.nextSibling;
      targetInput.checked = 'checked';

      var repeat = false;
      for (var i = 0; i < res.length; i++) {
        if (res[i].num === targetInput.dataset.num) {
          res[i].num = targetInput.dataset.num;
          res[i].result = targetInput.value;
          res[i].point = targetInput.dataset.point;
          repeat = true;
        }
      }
      if (!repeat) {
        res.push({
          num: targetInput.dataset.num,
          result: targetInput.value,
          point: targetInput.dataset.point
        });
      }
    } else if (self.parentNode.classList.contains('test-item')) {
      var ret2 = self.parentNode.parentNode.parentNode.querySelector('.test-active');
      if (ret2) {
        ret2.classList.remove('test-active');
      }
      self.parentNode.parentNode.classList.add('test-active');
      if (self.nodeName == 'LABEL') {
        self.nextSibling.checked = 'checked';

        var repeat2 = false;
        for (var i = 0; i < res.length; i++) {
          if (res[i].num === self.nextSibling.dataset.num) {
            res[i].num = self.nextSibling.dataset.num;
            res[i].result = self.nextSibling.value;
            res[i].point = self.nextSibling.dataset.point;
            repeat2 = true;
          }
        }
        if (!repeat2) {
          res.push({
            num: self.nextSibling.dataset.num,
            result: self.nextSibling.value,
            point: self.nextSibling.dataset.point
          });
        }
      } else if (self.nodeName == 'INPUT') {
        self.checked = 'checked';

        var repeat3 = false;
        for (var i = 0; i < res.length; i++) {
          if (res[i].num === self.dataset.num) {
            res[i].num = self.dataset.num;
            res[i].result = self.value;
            res[i].point = self.dataset.point;
            repeat3 = true;
          }
        }
        if (!repeat3) {
          res.push({
            num: self.dataset.num,
            result: self.value,
            point: self.dataset.point
          });
        }
      }
    }
  })

  document.getElementById('testSubmit').addEventListener('tap', function(e) {
    getTestResult(res);
  })

  mui('.mui-scroll-wrapper').scroll({
    deceleration: 0.0005 //flick 减速系数，系数越大，滚动速度越慢，滚动距离越小，默认值0.0006
  });

  var view = viewApi.view;
  (function($) {
    //处理view的后退与webview后退
    var oldBack = $.back;
    $.back = function() {
      if (viewApi.canBack()) { //如果view可以后退，则执行view的后退
        viewApi.back();
      } else { //执行webview后退
        oldBack();
      }
    };
    //监听页面切换事件方案1,通过view元素监听所有页面切换事件，目前提供pageBeforeShow|pageShow|pageBeforeBack|pageBack四种事件(before事件为动画开始前触发)
    //第一个参数为事件名称，第二个参数为事件回调，其中e.detail.page为当前页面的html对象
    view.addEventListener('pageBeforeShow', function(e) {
      // console.log(e.detail.page.id + ' beforeShow');
      // console.log(e);
    });
    view.addEventListener('pageShow', function(e) {
      // console.log(e.detail.page.id + ' show');
    });
    view.addEventListener('pageBeforeBack', function(e) {
      // console.log(e.detail.page.id + ' beforeBack');
    });
    view.addEventListener('pageBack', function(e) {
      // console.log(e.detail.page.id + ' back');
    });
  })(mui);
}

function getTestResult(data) {
  var totalQues = document.getElementsByClassName('mui-page').length;
  console.log(totalQues);
  console.log(data);
  if (!data) {
    alert('无数据!');
    return false;
  } else if (data.length !== totalQues) {
    alert('您还有题没做，请完成后提交！');
    return false;
  }
  mui.ajax('/jijin/Risk_assessment/getZNTGResult', {
    data: {
      res: JSON.stringify(data)
    },
    type: 'POST',
    dataType: 'json',
    timeout: 1000,
    success: function(res) {
      // console.log(res);
      if (res.code == '0000') {
        window.location.href = '/application/views/roboAdvisor/showRiskResult.html';
      } else {
        alert(res.msg);
      }
    },
    error: function(xhr) {
      alert('网络错误');
    }
  })
}