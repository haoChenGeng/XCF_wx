<!DOCTYPE html>
<html>
    <head>
    <title>发现</title>
    <body>
        <section class="content">
            <ul class="fing_con">
            	<?php
            		foreach ($papers as $key=>$val){
            			$classType = ($key < 5) ? "li10" : "li01";
            			$idVal = substr(101+$key,0,2);
            			echo '<li class="'.$classType.'" id="'.$idVal.'" name="lixun">
                    			<img src="'.'/application/views/find/'.$val['id'].'/'.$val['img'].'" />
                    			<a href="'.$this->base.'/FindPaper/getPaper/'.$val['id'].'">
                        			<span>'.$val['title'].'</span>
                    			</a>
                			</li>';
            		}
            	?>
            </ul>
        </section>       
    </head>
    <script>
        /*首页*/
        $("#li01").animate({left: "0", opacity: 1}, 200, "swing");
        $("#li02").animate({right: "0", opacity: 1}, 300, "swing");
        $("#li03").animate({left: "0", opacity: 1}, 400, "swing");
        $("#li04").animate({right: "0", opacity: 1}, 500, "swing");

        $(document).ready(function () {
            $(window).scroll(function () {
                if ($(window).scrollTop() > 80) {
                    $("#li05").animate({left: "0", opacity: 1}, 600, "swing");
                }
                if ($(window).scrollTop() > 250) {
                    $("#li06").animate({right: "0", opacity: 1}, 700, "swing");
                }
                if ($(window).scrollTop() > 500) {
                    $("#li07").animate({left: "0", opacity: 1}, 800, "swing");
                }
                if ($(window).scrollTop() > 750) {
                    $("#li08").animate({right: "0", opacity: 1}, 900, "swing");
                }
				if ($(window).scrollTop() > 1000) {
                    $("#li09").animate({right: "0", opacity: 1}, 1000, "swing");
                }
				if ($(window).scrollTop() > 1250) {
                    $("#li10").animate({right: "0", opacity: 1}, 1100, "swing");
                }
				if ($(window).scrollTop() > 1500) {
                    $("#li11").animate({right: "0", opacity: 1}, 1200, "swing");
                }
				if ($(window).scrollTop() > 1750) {
                    $("#li12").animate({right: "0", opacity: 1}, 1300, "swing");
                }
				if ($(window).scrollTop() > 1900) {
                    $("#li13").animate({right: "0", opacity: 1}, 1400, "swing");
                }
            });
        });
    </script> 
</body>
</html>

