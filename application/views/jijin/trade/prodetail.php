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
        <div id="chartContent">
          <div class="m-content-t3-chart" style="height: 280px;display: block;" id="one">1</div>
          <div class="m-content-t3-chart" style="height: 280px;" id="three">2</div>
          <div class="m-content-t3-chart" style="height: 280px;" id="six">3</div>
          <div class="m-content-t3-chart" style="height: 280px;" id="year">4</div>
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
<script src="../../../../data/jijin/js/prodetail.js"></script>
<script>

</script>
</body>
</html>