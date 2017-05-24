/**
 * Created by Jensen on 2015/10/22.
 */
function sendSms(inputTel, sendSmsBtn) {
  var str = inputTel.val();
  var regstr = inputTel.attr('data-reg');
  var reg = new RegExp(regstr, 'g');
  if (reg != null && !reg.test(str)) {
    M.alert({
      title: '提示',
      message: inputTel.attr('data-error')
    });
  } else {
    $.post("/user/send_sms", {
      tel: str
    }, function(res) {
      if (res == 1) {
        alert("发送成功");
        var timer = null;
        var times = 60;
        var oldStr = sendSmsBtn.html();
        sendSmsBtn.html('倒计时 ' + times + ' 秒');
        sendSmsBtn.attr('disabled', 'disabled').addClass('disabled');
        timer = setInterval(function() {
          if (times == 0) {
            clearInterval(timer);
            sendSmsBtn.html(oldStr);
            sendSmsBtn.removeAttr('disabled').removeClass('disabled');
          } else {
            times--;
            sendSmsBtn.html('倒计时 ' + times + ' 秒');
          }
        }, 1000);
      } else {
        M.alert({
          title: '提示',
          message: res == null || res == '' || res == undefined ? '发送失败' : res
        });
      }
    })
  }

}

function sendPlannerSmsCode(plannerNum) {
  $.post("/user/sendSmsByPlannerNum", {
    plannerNum: plannerNum
  }, function(res) {
    if (res == 1) {
      alert("发送成功");
    } else {
      alert(res == null || res == '' || res == undefined ? '发送失败' : res);
    }
  });
}

function goUrl(url) {
  window.location.href = url;
}