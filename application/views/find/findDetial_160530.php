<!DOCTYPE html>
<html>
    <head>
        <?php echo file_get_contents('../Public/head.html'); ?> 
        <title>发现</title>
    </head>
    <body>
<!--        <header class="fing_header">
            <div class="fin_header_ico">
                <a href="fing.html">&nbsp;</a>
            </div>
        </header>-->
        <section class="content fing_content">
            <div class="title">
                <span class="name">中国首个PE指数发布股权投资迎来风向标</span>
                <span class="text"><span class="date">2016-03-30</span>&nbsp;&nbsp;&nbsp;小牛新财富</span>
                <i class="ico">&nbsp;</i>
            </div>
            <i class="bianji">&nbsp;</i> 
            <div class="text01">
                <i class="top_ico"></i>
                <i class="left_ico"></i>
                <img src="/data/img/fing_par(01).png" />
                <p>
                    在银行买过理财产品的人都知道，在购买之前都需要填一份风险评估报告，可以在银行网点也可以在网银上做。根据你的评估结果，银行工作人员将向你推荐适合你风险承受能力的理财产品。不过实际上，有些情况下，这些评估结果并不能真正拦截超出你风险承受能力的产品，投资者不能对其完全信任。</p>
                <p>
            </div>
            <div class="text01 text02">
                <i class="top_ico"></i>
                <i class="left_ico"></i>
                <img src="/data/img/fing_par(02).png" />
                <p>
                    在银行买过理财产品的人都知道，在购买之前都需要填一份风险评估报告，可以在银行网点也可以在网银上做。根据你的评估结果，银行工作人员将向你推荐适合你风险承受能力的理财产品。不过实际上，有些情况下，这些评估结果并不能真正拦截超出你风险承受能力的产品，投资者不能对其完全信任。</p>
            </div>
        </section>
        <section class="pinglun_content">
            <ul class="title">
                <li class="cur">
                    <span>评论 1</span>
                </li>
<!--                <li>
                    <span>收藏 10</span>
                </li>-->
            </ul>
            <div class="matter_zong">
                <ul class="matter">
                    <li class="li01">
                        <span class="img"><img src="/data/img/ping_01.png" /></span>
                        <span class="text_right">
                            <span class="name">wanwan</span>
                            <span class="date">05-11  14:23</span>
                            <span class="text">文章写得不错</span>
                        </span>
                    </li>
                </ul>
            </div>
        </section>
        <div class="qqserver">
            <div class="qqserver_fold">&nbsp;
            </div>
            <ul class="qqserver-body">
                <li class="li01"><span>分享</span></li>
                <li class="li02"><a href="articleComment.php?url=&title=">评论</a></li>
                <li class="li03"><span>收藏</span></li>
                <li class="qqserver_arrow"><i class="i">&nbsp;</i></li>
            </ul>
        </div>
        <div class="qqsele_tanchu">&nbsp;</div>
        <input type="text" id="addtime"  name="addtime" style="display:none" />
        <script language=javascript>
            var int = self.setInterval("clock()", 1000)
            var time = 0;
            function clock()
            {
                time++;
                document.getElementById("addtime").value = time;
            }
        </script>
        <script type="text/javascript">
            $(function () {
                $('.pinglun_content .title li').each(function (i) {
                    $(this).click(function () {
                        $('.pinglun_content .title li').eq(i).addClass('cur').siblings().removeClass('cur');
                        $('.matter').eq(i).show().siblings().hide();
                        pageHeight();
                        return false;
                    });
                }).focus(function () {
                    this.blur();
                });
            });
        </script>
        <script type="text/javascript">
            $(function () {
                var $qqServer = $('.qqserver');
                var $qqserverFold = $('.qqserver_fold');
                var $qqserverUnfold = $('.qqserver_arrow');
                var $qqserverbody = $('.qqserver-body');
                $qqserverFold.click(function () {
                    $qqserverFold.hide(500);
                    $qqServer.addClass('unfold');
                });
                $qqserverUnfold.click(function () {
                    $qqServer.removeClass('unfold');
                    $qqserverFold.show(500);
                });
                $('.qqserver-body .li01').click(function () {
                    $('.qqsele_tanchu').show();
                });
                $('.qqsele_tanchu').click(function () {
                    $(this).hide();
                });
            });
        </script>
    </body>
</html>
