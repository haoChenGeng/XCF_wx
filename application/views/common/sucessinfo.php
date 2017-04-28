<div id="content">
	<div class="page-header">
    	<div class="container-fluid">
      		<h1><?php echo $heading_title; ?></h1>
      		<ul class="breadcrumb">
        		<?php foreach ($breadcrumbs as $breadcrumb) { ?>
        		<li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        		<?php } ?>
      		</ul>
		</div>
	</div>
	<div class="container-fluid">
		<?php if (isset($success)) { ?>
		<div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo $success; ?>
			<button type="button" class="close" data-dismiss="alert">&times;</button>
		</div>
		<?php } ?>
	</div>
</div>
