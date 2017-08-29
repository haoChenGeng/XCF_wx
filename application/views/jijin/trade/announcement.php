<!DOCTYPE html>
<html lang="en" style="background: #eee;">
<head>
	<meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <meta name="format-detection" content="telephone=no"/>
	<title>产品公告</title>
  <link rel="stylesheet" href="../../../../data/jijin/css/style.css">
</head>
<body>
	<div class="wrap">
		<section class="m-item-wrap m-item-5-wrap">
      <h3 class="text-center m-item-5"><span class="head-back-icon" onclick="window.history.go(-1)"></span><span style="width: 90%;display: inline-block;overflow: hidden;text-overflow: ellipsis;white-space: nowrap;margin-left: 25px;">基金公告</span></h3>      
    </section>
    <section>
    	<ul class="announcement-list">
    		<?php
    			if (empty($fundfile)){
    				echo '<li>暂无公告</li>';
    			}else{
    				foreach ($fundfile as $key=>$val){
    					echo   '<li><a href="'.$val.'">'.$key.'</a></li>';
    				}
    			}
    		?>
    		
    	</ul>
    </section>
	</div>
</body>
</html>