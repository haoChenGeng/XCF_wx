<!DOCTYPE html>
<html>
    <head>
        <?php echo file_get_contents('../Public/head.html'); ?> 
        <title>写评论</title>
    </head>
    <body>
        <form action="" method="post" name="form" id="form">
            <section class="content fing_par_review">
                <textarea name="comment" cols="" rows="" maxlength="100" placeholder="评论限制100字内...."></textarea>
                <div class="btn">
                    <a href="javascript:history.go(-1)" class="btn01">取消</a>
                    <input type="submit" id="send" value="发送" class="btn02"/>
                </div>
            </section>
            <input type="hidden" name="paperId" value="<?php echo $_GET['id']?>" />
        </form>
    <script>
        $(document).ready(function() {
            $('#send').click(function(e) {
                e.preventDefault();
                var content = $('textarea[name=comment]').val();
                var paperId = $('input[name=paperId]').val();
                $.ajax({
                    type: 'post',
                    url: '/findPaper/comment',
                    data: {
                        comment: content,
                        paperId: paperId
                    },
                    success: function(res) {
                        if (res) {
                            alert(res);
                            window.location.href = '/findPaper/getPaper/<?php echo $_GET['id']?>';
                        }
                    }
                });                
            });
        });
    </script>
    </body>
</html>
