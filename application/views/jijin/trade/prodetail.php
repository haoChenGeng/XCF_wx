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
      <h3 class="text-center m-item-5"><span class="head-back-icon" onclick="window.history.go(-1)"></span><span style="width: 90%;display: inline-block;overflow: hidden;text-overflow: ellipsis;white-space: nowrap;margin-left: 25px;"><?php echo $fundlist['fundname'].'('.$fundlist['fundcode'].')';?></span></h3>      
    </section>
    <section class="m-content">
      <div class="m-content-t1">
        <div class="m-content-t1-detail">
          <p><?php echo $fundlist['growth_day'].'%';?></p>
          <p><?php echo $field1;?></p>
        </div>
        <div class="m-content-t1-detail">
          <p><?php echo $fundlist['nav'];?></p>
          <p><?php echo $field2;?></p>
        </div>
        <div class="m-content-date">净值日期：<?php echo $fundlist['navdate'];?></div>
      </div>
      <div class="m-content-t2">
        <div class="m-content-t2-detail">
          <p>起投金额(元)</p>
          <p><?php echo $fundlist['firstMin']?></p>
        </div>
        <div class="m-content-t2-detail">
          <p>基金类型</p>
          <p><?php echo $fundlist['sharetype']?></p>
        </div>
        <div class="m-content-t2-detail">
          <p>风险等级</p>
          <p><?php echo $fundlist['risklevel']?></p>
        </div>
      </div>
      <div class="m-content-t3">
        <p><?php echo $field3;?></p>
        <div id="chartContent">
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
          申购：T日购买，T+1日确认份额，T+2日收益到账（QDII一般为T+2确认份额）
        </p>
        <p class="m-content-t4-detail">
          赎回：由于银行划款时间原因 ，货币基金预计T+2个交易日到账，股票、混合、债券等一般需T+3个交易日到账，QDII一般需要T+7个交易日到账，T为交易日
        </p>
      </div>
      <div class="m-content-t">
        <a href="/jijin/PurchaseController/fundfile?fundcode=<?php echo $fundlist['fundcode']?>" class="announcement-link">产品公告</a>
      </div>
      <div class="m-content-t">
        <p>基金过往业绩不预示其未来表现，市场有风险，投资需谨慎。</p>
      </div>
    </section>
    <section class="m-footer">
      <p class="m-footer-content" onclick="goto_buyfund()">立即<?php echo $purchasetype;?></p>
    </section>
  </div>
<script src="../../../../data/jijin/js/echarts.min.js"></script>
<script src="../../../../data/jijin/js/zepto.min.js"></script>
<script src="../../../../data/jijin/js/prodetail.js"></script>
<script>

function goto_buyfund() {
    window.location.href = "<?php echo "/jijin/PurchaseController/Apply?fundcode=".$fundlist['fundcode'].'&purchasetype='.$purchasetype;?>"
}

</script>
</script>
</body>
</html>