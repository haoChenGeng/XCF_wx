<div id="content">
	<div class="page-header">
    	<div class="container-fluid">
    		<h1><?php echo $heading_title;?></h1>
			<ul class="breadcrumb">
				<?php 
				foreach ($breadcrumbs as $breadcrumb) {
					echo '<li><a href="'.$breadcrumb['href'].'">'.$breadcrumb['text'].'</a></li>';
				} 
				?>
			</ul>
			<?php
			if(isset($buttons) && !empty($buttons)){
    			echo '<div class="pull-right">';
    			foreach ($buttons as $key => $val){
    				if (isset($val['url'])){
    					echo '<a href="'.$val['url'].'" data-toggle="tooltip" class="btn btn-primary">'.$val['description'].'</a>&nbsp&nbsp';
    				}else{
    					echo '<button type="button" class="btn btn-primary pull-right" style="margin-left:35px;" data-oper="'.$val['operation'].'"><i class="fa"></i>'.$val['description'].'</button>';
    				};
    			}
			}
    		?>
		</div>
	</div>
		<div class="container-fluid">
			<?php if (!empty($error_warning)) { ?>
			<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
				<button type="button" class="close" data-dismiss="alert">&times;</button>
			</div>
			<?php } ?>
			<?php if (!empty($success)) { ?>
			<div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo $success; ?>
				<button type="button" class="close" data-dismiss="alert">&times;</button>
			</div>
			<?php } ?>
		
			<div class="panel panel-default">
			<form action="<?php echo $form_action?>" method="post" enctype="multipart/form-data" id="form-submit">
				<?php
				if(!isset($selectoper)) {$selectoper = 'default';}
				if(isset($filters)){
					echo '<div class="panel-body">
    	    		  		<div class="well clearfix" style="margin-bottom:5px;padding:10px;">';           			  			;
					foreach ($filters as $key =>$val){
						switch ($key){
// 							case 'cascade_select':
// 								echo   '<div class="row" id="selectCx" style="margin-left:0px">
// 											<div style="height:25px;margin-left:15px;font-weight:bold">'.$val['description'].'</div>';
// 								$width = intval(95/$val['num']).'%';
// 								echo '<div><select class="cascade1 col-sm-2" name="cascade1" style="height:35px;width:'.$width.';margin-left:15px;border-radius:3px;"></select>';
// 								for ($i=2; $i<=$val['num']; $i++)
// 									echo '<select class="cascade'.$i.'"col-sm-2" name="cascade'.$i.'" style="height:35px;width:'.$width.';border-radius:3px;"></select>';
// 								echo '</div></div>';
// 								break;
							case 'cascade':
								$cascadeVar = &$cascadeDatas[$v['name']];
								echo '<select class="table_cascade" name="'.$v['name'].'" style="border:0">';
								if (isset($val[$k]['parentId']) && isset($cascadeVar[$val[$k]['parentId']])){
									foreach ($cascadeVar[$val[$k]['parentId']] as $k1=>$v1){
										echo '<option value="'.$k1.'"';
										if (isset($val[$k]['default']) && $k1== $val[$k]['default']){
											echo ' selected="selected"';
										}
										echo '>'.$v1."</option>\r\n";
									}
								}
								echo '</select>';
								break;
							case 'fields':
								foreach ($val as $k => $v){
									echo   '<div class="col-sm-2">
              							<div class="form-group">
                							<label class="control-label" for="input-dept-id" >'.$v['description'].'</label>
                							<input type="text" name="'.$v['name'].'" value="'.$v['val'].'" placeholder="'.$v['description'].'" class="form-control" />
              							</div>
            			  			</div>';
								}
								break;
							case 'dates':
								foreach ($val as $k=>$v){
									echo '<div class="col-sm-2">
											<div class="form-group">
												<label for="" style="display:block;">'.$v['description'].'</label>
 												<input type="date" name="'.$k.'" value="'.$v['val'].'" style="width:100%;line-height:30px;">
    	    		  						</div>
    	    		  					  </div>';
								}
								break;
							case 'select' :
								foreach ($val as $k=>$v){
									echo '<div class="form-group col-sm-2">
											<label for="" style="display:block;">'.$v['description'].'</label>
											<div>
												<select name="'.$v['name'].'" id="input-user-group" class="form-control">';
												foreach ($v['items'] as $item){
													echo '<option value="'.$item['val'].'"';
													if ($v['val'] === $item['val']){
														echo ' selected="selected"';
													}
													echo '>'.$item['name'].'</option>';
												}
									echo '</select></div></div>';
								}
								break;
						}
					}
					echo '<div class="col-sm-1">
         					<button type="button" id="button-filter" class="btn btn-primary pull-right" style="margin-top:35px;margin-left:35px;"><i class="fa fa-search"></i> 查询</button>
              	  	  	</div>';
					echo '</div> </div> </div>';
				}
				?>
				<input type="hidden" id="selectoper" name="selectoper" value="<?php echo $selectoper;?>"> </input>
				<input type="hidden" id="sortField" name="sortField" value="<?php echo isset($sortField) ? $sortField : '';?>"> </input>
				<input type="hidden" id = "order" name="order" value="<?php echo isset($order) ? $order : '';?>"> </input>
				<input type="hidden" id = "editItem" name="editItem" value=""> </input>
				<?php 
				if (isset($rand_code)){
					echo '<input type="hidden" name="rand_code" value="'.$rand_code.'"> </input>';
				}
				if (isset($cascade_select)){
					echo '<input type="hidden" id="cascade_select" value='."'".$cascade_select."'".'/>';
				}
				if (isset($tableEdit)){
					echo '<input type="hidden" id = "tableEditContent" name="tableEditContent" value=""></input>';
				}
				?>
				<div class="table-responsive">
     				<table class="table table-bordered table-hover">
						<thead>
							<tr>
								<?php if(isset($selcet_key)){
									echo '<td style="width: 1px;" class="text-center"><input type="checkbox" onclick="$('."'input[name*=\'selected\']').prop('checked', this.checked);".'"/></td>';
								}
								foreach ($table_field as $key => $val){
									$padding = isset($val['padding']) ? ' style="padding:0 '.$val['padding'].'px;"' : '';
									if (isset($val['sort'])){
										echo '<td  value="'.$key.'" class="text-center sortField"> <a ';
										if ($sortField == $key){
											echo 'class="'.strtolower($order).'" ';
										}
										echo '>'.$val['description'].'</a>';
									}else{
										echo '<td class="text-center"'.$padding.'>'.$val['description'].'</td>';
									}
     	             			}
								?>
							</tr>
						</thead>
						<tbody>
							<?php
							if (!empty($table_content) ){
								foreach ($table_content as $key =>$val){
									echo '<tr>';
									if (isset($selcet_key)){
										echo '<td class="text-center">';
										if (in_array($val[$selcet_key], $selected)){
											echo '<input type="checkbox" name="selected[]" value="'.$val[$selcet_key].'" checked="checked" />';
										}else{
											echo '<input type="checkbox" name="selected[]" value="'.$val[$selcet_key].'" />' ;
										}
									}
									echo '</td>';
									foreach ($table_field as $k => $v){
										$align = isset($v['align']) ? $v['align'] : "text-center";
										if ($k === 'operButton'){
											echo '<td class="'.$align.'">';
											foreach ($val['operButton'] as $k3 =>$v3){
												echo '<i class="btn editItem fa '.$v3['iconType'].'" title="'.$v3['description'].'" oper="'.$k3.'"></i>';
											}
											echo '</td>';
										}else{
											if (isset($v['type'])){
												echo '<td class="'.$align.'">';
												switch ($v['type']){
													case 'select':
														echo '<select class="table_select" name="'.$k.'" style="border:0" id="">';
														$selectContnet = isset($val[$k.'SelIndex']) ? $v['items'][$val[$k.'SelIndex']] : $v['items'];
														foreach ($selectContnet as $item){
															echo '<option value="'.$item['val'].'"';
															if ($val[$k] === $item['val']){
																echo ' selected="selected"';
															}
															echo '>'.$item['name'].'</option>';
														}
														break;
													case 'input':
														echo '<input class="table_input" name="'.$k.'" style="border:0" value = "'.(isset($val[$k]) ? $val[$k] : '').'"></input>';
														break;
													case 'cascade':
														$cascadeVar = &$cascadeDatas[$v['name']];
														echo '<select class="table_cascade" name="'.$v['name'].'" style="border:0">';
														if (isset($val[$k]['parentId']) && isset($cascadeVar[$val[$k]['parentId']])){
															foreach ($cascadeVar[$val[$k]['parentId']] as $k1=>$v1){
																echo '<option value="'.$k1.'"';
																if (isset($val[$k]['default']) && $k1== $val[$k]['default']){
																	echo ' selected="selected"';
																}
																echo '>'.$v1."</option>\r\n";
															}
														}
														echo '</select>';
														break;
												}
												echo '</td>';
											}else{
												if (isset($val[$k])){
													if (is_array($val[$k])){
														if (!key_exists('del', $val[$k])){
															echo '<td class="'.$align;
															if (isset($val[$k]['row'])){
																echo '" rowspan="'.$val[$k]['row'];
															}
															if (isset($val[$k]['col'])){
																echo '" colspan="'.$val[$k]['col'];
															}
															echo '">'.(isset($val[$k]['val'])?$val[$k]['val']:'').'</td>';
															if (key_exists('break', $val[$k])){
																break;
															}
														}
													}else{
														echo '<td class="'.$align.'">'.$val[$k].'</td>';
													}
												}else{
													echo '<td> </td>';
												}
											}
										}
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
        		<div class="row pagSetJump">
					<div class="col-sm-6 text-left"><?php if(isset($pagination[1])){echo $pagination[1];}?></div>
					<div class="col-sm-6 text-right"><?php if(isset($pagination[2])){echo $pagination[2];} ?></div>
				</div>
			</form>
		</div>
    </div>
</div>

<?php if (isset($cascadeDatas)){ 		//获取级联选择的选择数据 
echo "<script type='text/javascript'>
var cascadeDatas = {};\r\n";
foreach ($cascadeDatas as $key => $val){
	echo "cascadeDatas['".$key."'] = $.parseJSON('".json_encode($val)."');\r\n";
}
echo "function renewCascadeSelect(currentSelect,cascadeName,depth){
	var nextSub = currentSelect;
	for (i = 0; i < depth; i ++){
		nextSub = nextSub.parent();
	}
	nextSub = nextSub.next();
	var compareSub = nextSub;
	for (i = 0; i < depth; i ++){
		compareSub = compareSub.children();
	}
	var renewSub = compareSub;
	while (compareSub.attr('class') == currentSelect.attr('class')){
		compareSub.html('');
		nextSub = compareSub = nextSub.next();
		for (i = 0; i < depth; i ++){
			compareSub = compareSub.children();
		}
	}
	var allSubItems = cascadeDatas[cascadeName];
	var selectHtml = $('option[value='+currentSelect.val()+']',currentSelect).html();
	if (allSubItems[currentSelect.val()] && selectHtml !=''){
		parentItem = allSubItems[currentSelect.val()];
		for(var key in parentItem){
			var selected = (parentItem[key] == '') ? 'selected=\"selected\"' :\"\";
			renewSub.append('<option value =\"' + key +'\"'  + selected +'>'+parentItem[key]+'</option>');
		}
	}
}";
}
echo "\r\n</script>";
?>

<?php
if (isset($tableEdit)){
	echo '<script type="text/javascript">'."\r\n";
	echo "\r\nvar hash = {};\r\n";
	$editTypes = array_column($tableEdit, 'type','type');
	foreach ($editTypes as $val){
		echo "$('.table_".$val."').change(function(){";
		switch($val){
			case 'input':
				echo 'var hashVal = $(this).val();';
			break;
			case 'select':
				echo 'var hashVal = $(this).find("option:selected").val();';
    		break;
    		case 'cascade':
    			echo "var hashVal = $(this).find('option:selected').val();";
    			echo "renewCascadeSelect($(this),$(this).attr('name'),1);";
    		break;
		}
		echo 'var hashKey = $(this).parent().parent().children().eq(0).children().val();
				if (!hash.hasOwnProperty(hashKey)){
					hash[hashKey] = {};
				}
				hash[hashKey][$(this).attr("name")] = hashVal;'."\r\n})\r\n";
	}
	echo "\r\n".'</script>';
}
?>

<script type="text/javascript">
//通过异步方式提交修改信息
// $('#btnSaveSel').on('click',function() {                  
// 	$.ajax({
// 		type: 'post',
// 		data: hash,//{aaa:selVal},
//		url: '',                                 //businessData/Area/getdata
// 		success: function(data) {
// 			alert('保存成功');
// 		},
// 		error: function() {
// 			alert('保存失败');
// 		}
// 	});
// });

$('button').on('click',function(){
	var $btn = $(this);
	switch ($btn.attr('id')){
		case 'button-filter':
			$('#page').val(1);
			$('#sortField').val('');
		break;
	}
	if ($btn.attr('data-oper')){
		$("#selectoper").val($btn.attr('data-oper'));
		if ($btn.attr('data-oper') == 'oper_save'){
			if (confirm('保存修改的数据')){
				$("#tableEditContent").val(JSON.stringify(hash));
			}else{
				return;
			}
		}
	}else{
		$("#selectoper").val("<?php echo $selectoper;?>");
	}
	$("#form-submit").submit();
});

$('li').on('click',function(){
	var $li = $(this);
	var $idOfLi = $li.attr('id');
	if ($idOfLi && $idOfLi.indexOf("jumpPage") != -1){
		$("#page").val($li.attr('value'));
// 		$("#selectoper").val("");
		$("#form-submit").submit();
	}
});

$('.sortField').on('click',function(){
	$("#page").val(1);
	var $sortField = $(this);
	if ($sortField.attr("value") == "<?php echo $sortField;?>"){
		if ($("#order").val()=='ASC'){
			$("#order").val('DESC');
		}else{
			$("#order").val('ASC');
		}
	}else{
		$("#order").val('ASC');
	}
	$("#sortField").val($sortField.attr("value"));
	$("#selectoper").val("<?php echo $selectoper;?>");
	$("#form-submit").submit();
});

$('.editItem').on('click',function(){
	var $editItem = $(this);
	var $selectItem = $(this).parent().parent().find("input[type=checkbox]").val();
	$("#editItem").val($selectItem);
	$("#selectoper").val($editItem.attr("oper"));
	$("#form-submit").submit();
});


$('#form-submit').on('keydown',function(e) {	
	//IE浏览器
	if(CheckBrowserIsIE()){
	 	keycode = e.keyCode;
	}else{
	//火狐浏览器
	keycode = e.which;
	}
	if (keycode == 13 ) //回车键是13
	{
		$('#page').val(1);
		$('#sortField').val('');
		$("#selectoper").val("<?php echo $selectoper;?>");
		$('#form-submit').submit();
	}
});

$('.pagSetJump').on('keydown',function(e) {	
	//IE浏览器
	if(CheckBrowserIsIE()){
	 	keycode = e.keyCode;
	}else{
	//火狐浏览器
	keycode = e.which;
	}
	if (keycode == 13 ) //回车键是13
	{
		$("#selectoper").val("<?php echo $selectoper;?>");
		$('#form-submit').submit();
	}
});
//判断访问者的浏览器是否是IE
function CheckBrowserIsIE(){
 	var result = false;
 	var browser = navigator.appName;
 	if(browser == "Microsoft Internet Explorer"){
  		result = true;
 	}
 	return result;
}
</script>

<?php 
if (isset($cascade_select)){
	echo '<script src="data/javascript/jquery/jquery.cxselect.js"></script>';
	echo '<script type="text/javascript">';
	echo '
		var deptsData = $.parseJSON($("#cascade_select").val()).menu;
		$("#selectCx").cxSelect({
			selects: ["cascade1"';
	for ($i=2; $i<=$filters['cascade_select']['num']; $i++){
		echo ',"cascade'.$i.'"';
	}
	echo	'],
    		jsonName: "name",
		  	jsonValue: "val",
		 	jsonSub: "menu",
		 	data: deptsData,
 		 });';
	echo '</script>';
};
?>
