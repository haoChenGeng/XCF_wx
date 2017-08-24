<div id="content">
	<div class="page-header">
    	<div class="container-fluid">
			<div class="pull-right">
			<button type="submit" class="btn btn-primary" <?php if(!isset($public_key)){ echo 'form="form-user"';};?> data-toggle="tooltip" title="保存" ><i class="fa fa-save"></i></button>
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
				<form action="<?php echo $form_action; ?>" method="post" enctype="multipart/form-data" id="form-submit" class="form-horizontal">
					<?php
						foreach ($forms as $key => $val){
							switch ($val['type']) {
								case 'upload' :
									echo '<div class="form-group required">';
									
									echo '  <label class="col-sm-2 control-label" for="input-import">'.$val['description'].'</label>
											<div class="col-sm-10">
												<input type="file" style="padding:6px" name="'.$val['name'].'" '.(isset($val['content'])?$val['content']:'').'/>
											</div> </div>';
									break;
								default :
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
									break;
							}
						}
					?>
					<input type="hidden" name="selectoper" value="<?php echo $selectoper;?>"  />
					<input type="hidden" id="fundInfo" value="<?php echo $fundInfo;?>"  />
					<?php if(isset($rand_code)){
						echo '<input type="hidden" name="rand_code" value="'.$rand_code.'"  />';
					}?>
				</form>
			</div>
		</div>
	</div>
	<?php if (isset($cascade_select)){
			echo '<input type="hidden" id="cascade_select" value='."'".$cascade_select."'".' />';
		}
	?>
</div>

<script type="text/javascript">
$('button').on('click',function(){
	var $btn = $(this);
	$("#form-submit").submit();
});
</script>