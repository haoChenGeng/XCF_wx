<div id="content">
	<div class="page-header">
    	<div class="container-fluid">
			<div class="pull-right">
			<button type="submit" class="btn btn-primary" form="form-user" data-toggle="tooltip" title="保存" ><i class="fa fa-save"></i></button>
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
							switch ($val['type']) {
								case 'cascade':
									$width = ((79-$val['num'])/$val['num']).'%';
									echo '<div class="row" id="selectCx">
        							<div class="form-group required">
        								<label class="col-sm-2 control-label" style="margin-left:10px;">'.$val['description'].'</label>
										<div><select class="cascade1 col-sm-2" name="cascade1" value="1" style="height:35px;width:'.$width.';margin-left:15px;border-radius:3px;"></select>';
									for ( $i=2; $i<=$val['num']; $i++){
										echo '<select class="cascade'.$i.' col-sm-2" name="cascade'.$i.'" style="height:35px;width:'.$width.';margin-left:1%;border-radius:3px;"></select>';
									}
									echo '</div></div></div>';
									break;
								case 'select' :
									echo '<div class="form-group';
									if (isset($val['required'])){
										echo ' required';
									}
									echo '"><label class="col-sm-2 control-label" for="input-user-group">'.$val['description'].'</label>
											<div class="col-sm-10"> 
												<select name="'.$val['name'].'" id="input-user-group" class="form-control">';
									foreach ($val['items'] as $item){
										echo '<option value="'.$item['val'].'"';
										if ($val['val'] == $item['val']){
											echo ' selected="selected"';
										}
										echo '>'.$item['name'].'</option>';
									}
									echo '</select></div></div>';
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
					<div class="form-group">
					<label class="col-sm-2 control-label" for="input-user-group"><?php echo $table_description;?></label>
					<div class="table-responsive" >
     				<table class="table table-bordered table-hover" style="width:96.5%;margin-left:15px;">
						<thead>
							<tr>
								<td style="width: 1px;" class="text-center"><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', this.checked);" /></td>
								<?php 
								foreach ($table_field as $key => $val){
									echo '<td class="text-left">'.$val['description'].'</td>';
     	             			}
								?>
							</tr>
						</thead>
						<tbody>
							<?php
							if (isset($table_content)){
								foreach ($table_content as $key =>$val){
									echo '<tr> 
											<td class="text-left">';
									if (in_array($val[$selcet_key], $selected)){
										echo '<input type="checkbox" name="selected[]" value="'.$val[$selcet_key].'" checked="checked" />';
									}else{
										echo '<input type="checkbox" name="selected[]" value="'.$val[$selcet_key].'" />' ;
									}
									echo '</td>';
									foreach ($val as $k => $v){
										if ($k != 'id')
											echo '<td class="text-left">'.$v.'</td>';
									}
									echo '</tr>';
								}
							}else{
								echo '<tr>  
										<td class="text-center" colspan="'.(count($table_field)+1).'">未找到相关记录</td>
									  </tr>';
							}
							?>
						</tbody>
					</table>
					</div>
					</div>
					<input type="hidden" name="selectoper" value="<?php echo $selectoper;?>"  />
					<?php if(isset($rand_code)){
						echo '<input type="hidden" name="rand_code" value="'.$rand_code.'"  />';
					}?>
				</form>
			</div>
		</div>
	</div>
</div>

