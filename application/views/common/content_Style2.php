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
				<form action="<?php echo $form_action; ?>" method="post" enctype="multipart/form-data" id="form-user" class="form-horizontal">
					<?php
						foreach ($forms as $key => $val){
							switch ($val['type']) {
								case 'cascade':
									$width = ((79-$val['num'])/$val['num']).'%';
									for ($i = 0; $i<$val['num']; $i++){
										if (!isset($val['default'][$i+1])){
											$defaultVal = $val['default'][$i];
											break;
										}
									}
// 									echo '<input type="hidden" id="'.$val['name'].'" name="'.$val['name'].'" value="'.$defaultVal.'"  />';
									echo '<div class="form-group">
      										<input type="hidden" id="'.$val['name'].'" name="'.$val['name'].'" value="'.$defaultVal.'"  />
        									<div class="row">
        										<label class="col-sm-2 control-label" style="margin-left:10px;">'.$val['description'].'</label>
												<div'.' name="'.$val['name'].'">';
									$cascadeVar = 'cascade_'.$val['name'];
									$cascadeVar = &$$cascadeVar;
									for ($i = 0; $i<$val['num']; $i++){
										$marginLeft = ($i == 0) ? ';margin-left:0.75%' : ';margin-left:1.6%';
										echo '<select class="cascadeClass col-sm-2" style="height:35px;width:'.$width.$marginLeft.';border-radius:3px;">';
										if (isset($val['cascadeItems'][$i])){
											if (isset($cascadeVar[$val['cascadeItems'][$i]])){
												foreach ($cascadeVar[$val['cascadeItems'][$i]] as $k=>$v){
													echo '<option value="'.$k.'"';
													if (isset($val['default'][$i]) && $k == $val['default'][$i]){
														echo ' selected="selected"';
													}
													echo '>'.$v."</option>\r\n";
												}
											}
										}
										echo '</select>';
									}
									echo "</div></div></div>\r\n";
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
									echo "</select></div></div>\r\n";
									break;
								case 'tree' :
									echo '<div class="form-group';
									if (isset($val['required'])){
										echo ' required';
									}
									echo '"><label class="col-sm-2 control-label" for="input-user-group">'.$val['description'].'</label>
        									<div class="col-sm-10">';
									$this->load->helper( array("webpagtools"));
									echo '<ul id="tree" style="margin-left:-25px;margin-top:7px;margin-bottom:-28px;border-radius:3px;">';
									echo getTreeHtml($val['content'],$selected);
									echo "</ul></div></div>\r\n";
									break;
								default :
									echo '<div class="form-group';
									if (isset($val['required'])){
										echo ' required';
									}
									echo '"><label class="col-sm-2 control-label" >'.$val['description'].'</label>'.
											'<div class="col-sm-10">
										<input '.$val['content'].' class="form-control"/>';
									if (isset($val['error'])){
										echo '<div class="text-danger">'.$val['error'].'</div>';
									}
									echo "</div></div>\r\n";
									break;
							}
						}
					?>
					<input type="hidden" name="selectoper" value="<?php echo $selectoper;?>"  />
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

<?php 

if (isset($cascade) && is_array($cascade)){
	echo "<script type='text/javascript'>\r\nvar subDatas = {};\r\n";
	foreach ($cascade as $key => $val){
		echo "subDatas['".$key."'] = $.parseJSON('".$val."');\r\n";
	}
	echo "$('.cascadeClass').change(function(){
			cascadeName = $(this).parent().attr('name');
			var itemKey = '#'+cascadeName;
			$(itemKey).attr('value',$(this).val());
			var nextSub = $(this).next();
			while (nextSub.attr('class') == $(this).attr('class')){
				nextSub.html('');
				nextSub = nextSub.next();
			}
			nextSub = $(this).next();
			var allSubItems = subDatas[cascadeName];
      		var selectHtml = $('option[value='+$(this).val()+']',this).html();
			if (allSubItems[$(this).val()] && selectHtml !=''){
				parentItem = allSubItems[$(this).val()];
				for(var key in parentItem){
					var selected = (parentItem[key] == '') ? 'selected=\"selected\"' :\"\";
					nextSub.append('<option value =\"' + key +'\"'  + selected +'>'+parentItem[key]+'</option>');
				}
			}
	});";
	echo '</script>';
}

if (isset($public_key)){
	echo '<script src="/data/javascript/RSA.min.js"></script>
        	<script type="text/javascript">';
	echo '$(".btn").on("click", function () {
			var encrypt = new JSEncrypt();
			encrypt.setPublicKey("'.$public_key.'");
			var encrypted = encrypt.encrypt($("#password").val()+"'.$rand_code.'");
			$("#password").val(encrypted);
			$("#form-user").submit();
			});';
	echo '</script>';
};
?>

<script type="text/javascript">
(function(){
    $.fn.extend({
        checktree: function(){
            $(this)
                .addClass('checktree-root')
                .on('change', 'input[type="checkbox"]', function(e){
                    e.stopPropagation();
                    e.preventDefault();
                    checkParents($(this));
                    checkChildren($(this));
                })
            ;

            var checkParents = function (c)
            {
                var parentLi = c.parents('ul:eq(0)').parents('li:eq(0)');

                if (parentLi.length)
                {
                    var siblingsChecked = parseInt($('input[type="checkbox"]:checked', c.parents('ul:eq(0)')).length),
                        rootCheckbox = parentLi.find('input[type="checkbox"]:eq(0)')
                    ;

                    if (c.is(':checked'))
                        rootCheckbox.prop('checked', true)
//                     else if (siblingsChecked === 0)
//                         rootCheckbox.prop('checked', false);
                    checkParents(rootCheckbox);
                }
            }

            var checkChildren = function (c)
            {
                var childLi = $('ul li input[type="checkbox"]', c.parents('li:eq(0)'));

                if (childLi.length)
                    childLi.prop('checked', c.is(':checked'));
            }
        }

    });
})();

$('#tree').checktree();
</script>

