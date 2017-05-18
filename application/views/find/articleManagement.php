<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <link rel="Shortcut Icon" href="/favicon.ico?v1" type="image/x-icon" />
        <meta name="Keywords" content="小牛新财富" />
        <meta name="Description" content="小牛新财富" />
        <meta name="robots" content="index,follow,noodp,noydir" />
        <meta name="viewport" content="width=640px, maximum-scale=1.0, user-scalable=no, target-densitydpi=320" />
        <title>文章管理</title>
        <link rel="stylesheet" href="/data/css/style.css" />
        <script type="text/javascript" src="/data/js/jquery.min.js"></script>
    </head>
    <body>
        <section class="pinglun_content">
            <ul class="title title2">
                <li class="cur">
                    <span>评论</span>
                </li>
                <li>
                    <span>收藏</span>
                </li>
            </ul>
            
             <div class="matter_zong matter_zong2" 我的评论>
                <ul class="matter">
                    <?php foreach($comment_data as $val){?>
                    <li class="li01">
                        <span class="text_right">
                        	<span><a class="yonghu_name" href="/<?php echo $val['url'];?>"><?php echo $val['title'];?></a></span>
                        	<?php foreach ($val['comment'] as $v){?>
                            	<span class="date"><?php echo substr($v['comment_time'], 5, 11);?></span>
                            	<span class="text"><?php echo $v['content'];?></span>
                            <?php } ?>
                        </span>
                    </li>
                    <?php } ?>
                </ul>
                <ul class="matter matter2"  style="display:none;">
                    <?php foreach($collect_data as $k => $v):?>
                    <li class="li01">
                        <span class="text text2">
                            <span class="text2_right">
                                <span><a href="/<?php echo $v['url'];?>"><?php echo $v['title'];?></a></span>
                                <span class="title_text">小牛财富</span>
                            </span>
                        </span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </section>
    </body>
</html>
<script type="text/javascript">
    $(function () {
        $('.pinglun_content .title li').each(function (i) {
            $(this).click(function () {
                $('.pinglun_content .title li').eq(i).addClass('cur').siblings().removeClass('cur');
                $('.matter').eq(i).show().siblings().hide();
                return false;
            });
        }).focus(function () {
            this.blur();
        });
    })
</script>