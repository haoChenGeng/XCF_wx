<div id="content">
	<div class="page-header">
    	<div class="container-fluid">
			<div class="pull-right">
			<button type="button" class="btn btn-primary" data-toggle="tooltip" title="保存" ><i class="fa fa-save"></i></button>
        	<a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="取消" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
      		<h1><?php echo $heading_title; ?></h1>
      		<ul class="breadcrumb">
        		<?php foreach ($breadcrumbs as $breadcrumb) { ?>
        		<li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        		<?php } ?>
      		</ul>
		</div>
	</div>
	<div class="container-fluid">
		<?php if (isset($error_warning)) { ?>
		<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
			<button type="button" class="close" data-dismiss="alert">&times;</button>
		</div>
    	<?php } ?>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_form; ?></h3>
			</div>
			<div class="panel-body">
				<form action="<?php echo $form_action; ?>" method="post" enctype="multipart/form-data" id="form-user" class="form-horizontal">
					<?php
						foreach ($forms as $key => $val){
							echo '<div class="form-group';
							if (isset($val['required'])){
								echo ' required';
							}
							echo '"><label class="col-sm-2 control-label" >'.$val['description'].'</label>'.
									'<div class="col-sm-10">
										<input '.$val['content'].'" class="form-control"/>';
							if (isset($val['error'])){
								echo '<div class="text-danger">'.$val['error'].'</div>';
							}
							echo '</div></div>';
						}
					?>
				</form>
			</div>
		</div>
	</div>
</div>

<script src="/data/javascript/RSA.min.js"></script>
<script type="text/javascript">
	$(".btn").on("click", function (event) {
		var newpassword = $('#newpassword').val();
		var confirmpassword = $('#confirmpassword').val();
		var oldpassword = $("#oldpassword").val();
		if (oldpassword=='' || newpassword==''){
			alert('输入的密码不能为空！');
		}else{
			if (newpassword === confirmpassword){
				var encrypt = new JSEncrypt();
				encrypt.setPublicKey("<?php echo $public_key;?>");
				var encrypted = encrypt.encrypt($("#oldpassword").val()+"<?php echo strval($rand_code)?>" + $("#newpassword").val());
				$("#oldpassword").val(encrypted);
				$("#newpassword").val('');
				$("#confirmpassword").val('');
				$("#form-user").submit();
	        }else{
	        	alert('两次输入的新密码不一致！');
	        }
		}
	});
</script>
