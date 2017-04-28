<!DOCTYPE html>
<html>
    <head>
    <title>发现</title>
    <?php echo file_get_contents('../Public/head.html'); ?>    

    <body>
        <section class="content">
            <ul class="fing_con">
				<li class="li10" id="li01" name="lixun">
                    <img src="/data/img/findImg/fing13.png" />
                    <a href="findDetial13.php">
                        <span>奥运表情包暗藏理财硬道理 解锁新玩法让钱变聪明</span>
                    </a>
                </li>
				 <li class="li10" id="li02" name="lixun">
                    <img src="/data/img/findImg/fing12.png" />
                    <a href="findDetial12.php">
                        <span>小牛新财富：好私募？差私募？该如何选择</span>
                    </a>
                </li>
				<li class="li10" id="li03" name="lixun">
                    <img src="/data/img/findImg/fing11.png" />
                    <a href="findDetial11.php">
                        <span>小牛新财富：当北上广深能买下半个美国，我们能做的是……</span>
                    </a>
                </li>
				 <li class="li10" id="li04" name="lixun">
                    <img src="/data/img/findImg/fing10.png" />
                    <a href="findDetial10.php">
                        <span>小牛新财富：“义无反顾”要来了，房地产税还远吗？</span>
                    </a>
                </li>
				<li class="li01" id="li05" name="lixun">
                    <img src="/data/img/findImg/fing09.png" />
                    <a href="findDetial09.php">
                        <span>你是在“赚钱”还是“挣钱”？99%的人都不懂</span>
                    </a>
                </li> 
				<li class="li01" id="li06" name="lixun">
                    <img src="/data/img/findImg/fing08.png" />
                    <a href="findDetial08.php">
                        <span>小牛新财富：教你如何炫富？</span>
                    </a>
                </li>
				<li class="li01" id="li07" name="lixun">
                    <img src="/data/img/findImg/fing07.jpg" />
                    <a href="findDetial07.php">
                        <span>小牛新财富：顺势而为，在金融改革发展中创新 </span>
                    </a>
                </li>
                <li class="li01" id="li08" name="lixun">
                    <img src="/data/img/findImg/fing06.jpg" />
                    <a href="findDetial06.php">
                        <span>五年.美好小牛.不忘初心 </span>
                    </a>
                </li>
                <li class="li01" id="li09" name="lixun">
                    <img src="/data/img/findImg/fing05.jpg" />
                    <a href="findDetial05.php">
                        <span>彭铁：在第5个年头，聊聊小牛的下一个5年 </span>
                    </a>
                </li>
                <li class="li01" id="li10" name="lixun">
                    <img src="/data/img/findImg/fing01.png" />
                    <a href="findDetial01.php">
                        <span>小牛新财富：不要让华为跑了？不要让创业热情跑了！</span>
                    </a>
                </li>
                <li class="li01" id="li11" name="lixun">
                    <img src="/data/img/findImg/fing04.png" />
                    <a href="findDetial02.php">
                        <span>彭铁：艰苦创业、勤俭节约、永葆小牛的青春活力</span>
                    </a>
                </li>
                <li class="li01" id="li12" name="lixun">
                    <img src="/data/img/findImg/fing03.png" />
                    <a href="findDetial03.php">
                        <span>易宪容：这次人民币贬值为何没有引发市场恐慌? 
                        </span>
                    </a>
                </li>
                <li class="li01" id="li13" name="lixun">
                    <img src="/data/img/findImg/fing02.png" />
                    <a href="findDetial04.php">
                        <span>儿童节将至 “宝贝经济”再升温 </span>
                    </a>
                </li>
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

