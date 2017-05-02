<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
    <meta name="format-detection" content="telephone=no"/>
    <link href="<?php echo $base ?>/data/css/mobile.css" media="screen" rel="stylesheet" type="text/css">
    <title>交易查询</title>
</head>
<body>
<section class="wrap">
    <section class="m-item-wrap m-item-5-wrap">
        <div class="m-item-5">
            <h3 class="text-center">交易查询</h3>
        </div>
    </section>
    <section class="m-item-wrap m-item-5-wrap">
        <div class="m-item-5">
			<div class="m-item-trade-begin">
                <label for="begin" class="disb text-center">开始日期</label><input type="date" id="begin" class="trade-input">
            </div>
            <div class="m-item-trade-end">
                <label for="end" class="disb text-center">结束日期</label><input type="date" id="end" class="trade-input">
            </div>
		</div>
    </section>
    
	<section class="m-item-wrap">
        <div class="m-item">
            <div class="m-item-name-title">基金名字</div>
            <div class="m-item-code-title">操作日期/类型</div>
            <div class="m-item-code-title">单号</div>
            <div class="m-item-code-title">金额</div>
            <div class="m-item-code-title">状态</div>
		</div>
        <?php
        foreach ($fundlist as $i => $value) {
            echo "<div class='m-item' name=\"list\">";
            // echo "<i class='icon icon-lcproduct'></i>";
            echo "<div class=\"m-item-name\">" . $value['fundname'] . "</div>";
            echo "<div class='m-item-nav'>" . $value['operdate'] . "</div>";
            echo "<div class='m-item-nav'>" . $value['businesscode'] . "</div>";
            echo "<div class='m-item-code-date'>" . $value['appsheetserialno'] . "</div>";
            echo "<div class='m-item-nav'>" . $value['applicationamount'] . "</div>";
            echo "<div class='m-item-nav'>" . $value['status'] . "</div>";
            // echo "<div class='arrow-wrap'>";
            // echo  "<a href='/jijin/jijinpro/showprodetail/?fundid=".$value['fundcode']."&tano=".$value['tano']."'>";
            // echo "<i class='icon icon-arrow-right'>"."</i>";
            // echo "</a>";
            // echo "</a>";
            echo "</div>";
            echo "</div>";
        }
        ?>            
    </section>
    <div class="m-btn-wrap">
        <input class="btn btn-2" type="button" onclick="goto_main()" value="返回主页">
    </div>
</section>
</body>
<script>
    window.onload = (function() {
        var flist = document.getElementsByName('list');
        for (var i = flist.length - 1; i >= 0; i--) {
            var child1 = flist[i].childNodes[1],
                child2 = flist[i].childNodes[2],
                father = document.createElement('div');
            child1.style.cssText = "float:none;line-height:25px;";
            child2.style.cssText = "float:none;line-height:25px;";

            father.appendChild(child1);
            father.appendChild(child2);
            flist[i].insertBefore(father,flist[i].childNodes[1]);
        }
        
    })();
    function goto_main() {
        window.location.href = '<?php echo $base?>/info/goto_main';
    }
</script>
</html>