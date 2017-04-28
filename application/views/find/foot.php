<p style="font-size:0.8em;padding-left: 20px;font-size: 0.8em;margin-top: 20px;">阅读 <span><?php echo $readTimes?></span></p>

<div class="qqserver">
<div class="qqserver_fold">&nbsp;
</div>
<ul class="qqserver-body">
<li class="li01"><span>分享</span></li>
<li class="li02"><a id="lunhref">评论</a></li>
<li class="li03"><a id="canghref">收藏</a></li>
<li class="qqserver_arrow"><i class="i">&nbsp;</i></li>
</ul>
</div>
<div class="qqsele_tanchu">&nbsp;</div>
<input type="text" id="addtime"  name="addtime" style="display:none" />
<script type="text/javascript">
var title = $("#title").html();
var pinglun = "<?php echo $this->base?>/application/views/find/articleComment.php?id=<?php echo $id?>";
$("#lunhref").attr('href', pinglun);

</script>
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
    $(document).ready(function() {
        $('#canghref').click(function(e) {
            e.preventDefault();
            $.ajax({
                type: 'post',
                url: '/FindPaper/collection/<?php echo $id?>',
                data: {
                },
                success: function(res) {
                    if (res) {
                        alert(res);
                    }
                }
            });                
        });
    });
	
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
</html>