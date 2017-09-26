mui.ajax('/jijin/Risk_assessment/getZNTGResult', {
  data: {

  },
  dataType: 'json',
  type: 'GET',
  timeout: 10000,
  success: function(res) {
    console.log(res);
    checkStatus(res);
  },
  error: function(xhr, type, errorThrown) {
    console.log(type);
  }
})

function checkStatus(data) {
  if (data.login !== 0) {
    window.location.href = '/user/login';
  } else if (data.riskLevel !== '') {
    window.location.href = '/application/views/roboAdvisor/showRiskResult.html'
  } else {
    document.getElementsByClassName('ra-wrap')[0].style.display = 'block';
    document.getElementsByClassName('loading')[0].style.display = 'none';
  }
}

// function jModule() {}

function a() {

}

a.prototype.a = function(par) {
  console.log(1);
}

a.prototype.ajax = function(params) {
  params = params || {};
  if (!params.url) {
    throw new Error('Necessary parameters are missing.'); //必要参数未填
  }
  var options = {
    url: params.url || '', //接口地址
    type: (params.type || 'GET').toUpperCase(), //请求方式
    timeout: params.timeout || 5000, //超时等待时间
    async: true, //是否异步
    xhrFields: {}, //设置XHR对象属性键值对。如果需要，可设置withCredentials为true的跨域请求。
    dataType: params.dataType || 'json', //请求的数据类型
    data: params.data || {}, //参数
    jsonp: 'callback',
    jsonpCallback: ('jsonp_' + Math.random()).replace('.', ''),
    error: params.error || function() {},
    success: params.success || function() {},
    complete: params.complete || function() {}
  };
  var formatParams = function(json) {
    var arr = [];
    for (var i in json) {
      arr.push(encodeURIComponent(i) + '=' + encodeURIComponent(json[i]));
    }
    return arr.join("&");
  };
  if (options.dataType == 'jsonp') {
    //插入动态脚本及回调函数
    var $head = document.getElementsByTagName('head')[0];
    var $script = document.createElement('script');
    $head.appendChild($script);
    window[options.jsonpCallback] = function(json) {
      $head.removeChild($script);
      window[options.jsonpCallback] = null;
      hander && clearTimeout(hander);
      options.success(json);
      options.complete();
    };
    //发送请求
    options.data[options.jsonp] = options.jsonpCallback;
    $script.src = options.url + '?' + formatParams(options.data);
    //超时处理
    var hander = setTimeout(function() {
      $head.removeChild($script);
      window[options.jsonpCallback] = null;
      options.error();
      options.complete();
    }, options.timeout);
  } else {
    //创建xhr对象
    var xhr = new(self.XMLHttpRequest || ActiveXObject)("Microsoft.XMLHTTP");
    if (!xhr) {
      return false;
    }
    //发送请求
    options.data = formatParams(options.data);
    if (options.type == 'POST') {
      xhr.open(options.type, options.url, options.async);
      xhr.setRequestHeader('content-type', 'application/x-www-form-urlencoded');
    } else {
      options.url += options.url.indexOf('?') > -1 ? '&' + options.data : '?' + options.data;
      options.data = null;
      xhr.open(options.type, options.url + '?' + options.data, options.async);
    }
    if (options.xhrFields) {
      for (var field in options.xhrFields) {
        xhr[field] = options.xhrFields[field];
      }
    }
    xhr.send(options.data);
    //超时处理
    var requestDone = false;
    setTimeout(function() {
      requestDone = true;
      if (xhr.readyState != 4) {
        xhr.abort();
      }
    }, options.timeout);
    //状态处理
    xhr.onreadystatechange = function() {
      if (xhr.readyState == 4 && !requestDone) {
        if (xhr.status >= 200 && xhr.status < 300) {
          var data = options.dataType == "xml" ? xhr.responseXML : xhr.responseText;
          if (options.dataType == "json") {
            try {
              data = JSON.parse(data);
            } catch (e) {
              data = eval('(' + data + ')');
            }
          }
          options.success(data);
        } else {
          options.error();
        }
        options.complete();
      }
    };
  }
}