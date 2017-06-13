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
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-exchange"></i> <?php echo $heading_title; ?></h3>
      </div>
      <div class="panel-body">
        <form action="<?php echo $form_action?>" method="post" enctype="multipart/form-data" id="upform" class="form-horizontal">
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-import">选择导入文件</label>
            <div class="col-sm-10">
              <input type="file" name="upload" id="upload" />
            </div>
          </div>
            <?php if(isset($public_key)){
            	echo '<input type="hidden" name="uploadContent" id="uploadContent" />';
            	echo '<input type="hidden" name="EASkey" id="EASkey" />';
            }
            ?>
            <?php if(isset($forms)){
            	foreach ($forms as $key => $val){
            		if ($key == 'dates'){
            			foreach ($val as $k=>$v){
            				echo '<div class="col-sm-2">
											<div class="form-group">
												<label for="" style="display:block;">'.$v['description'].'</label>
 												<input type="date" name="'.$k.'" value="'.$v['val'].'" style="width:100%;line-height:30px;">
    	    		  						</div>
    	    		  					  </div>';
            			}
            			break;
            		}else{
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
            			echo '</div></div>';
            		}
            	}
            }?>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-import">&nbsp;</label>
            <div class="col-sm-10">
              <a class="btn btn-primary btn-lg" onclick="upload();"><span><?php echo isset($importDescription)?$importDescription:'导入';?></span></a>
              <?php	if(isset($return)) {
            			echo '<a class="btn btn-primary btn-lg" href="'.$return.'" style="margin-left:15px;"><span>返回</span></a>';
            		}    		
              ?>
            </div>

          </div>
          <input type="hidden" id="selectoper" name="selectoper" value="<?php echo isset($selectoper)?$selectoper:'default';?>"> </input>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="/data/javascript/RSA.min.js"></script>
<script src="/data/javascript/aes.js"></script>
<script src="/data/javascript/aes-json-format.js"></script> 

<script type="text/javascript">

function checkFileSize(id) {
	// See also http://stackoverflow.com/questions/3717793/javascript-file-upload-size-validation for details
	var input, file, file_size;

	if (!window.FileReader) {
		
		return true;
	}

	input = document.getElementById(id);
	if (!input) {
		
		return true;
	}
	else if (!input.files) {
		
		return true;
	}
	else if (!input.files[0]) {
		
		alert( "点击【导入】前请先选择文件" );
		return false;
	}
	else {
		file = input.files[0];
		file_size = file.size;
		<?php
			$input_max_size =  ini_get('post_max_size');
			$upload_max_filesize = ini_get('upload_max_filesize');
			$sizeUnit = array('K'=> 1024, 'M'=>1048576, 'G'=>1073741824);
			$post_max_size =  substr($input_max_size,0,-1)*$sizeUnit[substr($input_max_size,-1)];
			$post_max_size = isset($public_key) ? $post_max_size/2 : $post_max_size;
			$error_post_max_size = '文件大小超过PHP设定的【'.($post_max_size/1024).'KB】 ';
			$error_upload_max_filesize = '文件大小超过PHP设定的【'.$upload_max_filesize.'】 ';
			$upload_max_filesize =  substr($upload_max_filesize,0,-1)*$sizeUnit[substr($upload_max_filesize,-1)];
		?>
		post_max_size = <?php echo $post_max_size; ?>;
		if (file_size > post_max_size) {
			alert( "<?php echo $error_post_max_size; ?>" );
			return false;
		}
		upload_max_filesize = <?php echo $upload_max_filesize; ?>;
		if (file_size > upload_max_filesize) {
			alert( "<?php echo $error_upload_max_filesize; ?>" );
			return false;
		}
		return true;
	}
}

function jsReadFiles(e) {
	<?php 
		if(isset($public_key)){
			echo 	'file = this.files[0];
  					var reader = new FileReader();
  					reader.onload = function() {
  						var rdmString = "";
    					for (; rdmString.length < 16; rdmString += Math.random().toString(36).substr(2));
  		    			var encrypt = new JSEncrypt();
						encrypt.setPublicKey("'.str_replace("\n",'', $public_key).'");   
    					var EASkey = encrypt.encrypt(rdmString);
      					$("#EASkey").val(EASkey);
      					var encrypted_code = CryptoJS.AES.encrypt(base64encode(this.result), rdmString, {format: CryptoJSAesJson}).toString();
  						$("#uploadContent").val(encrypted_code);
            		}
  					reader.readAsBinaryString(file);';
		}
	?>
}

var base64EncodeChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
var base64DecodeChars = new Array(-1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, 62, -1, -1, -1, 63, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, -1, -1, -1, -1, -1, -1, -1, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, -1, -1, -1, -1, -1, -1, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, -1, -1, -1, -1, -1);
/**
 * base64编码
 * @param {Object} str
 */
function base64encode(str){
    var out, i, len;
    var c1, c2, c3;
    len = str.length;
    i = 0;
    out = "";
    while (i < len) {
        c1 = str.charCodeAt(i++) & 0xff;
        if (i == len) {
            out += base64EncodeChars.charAt(c1 >> 2);
            out += base64EncodeChars.charAt((c1 & 0x3) << 4);
            out += "==";
            break;
        }
        c2 = str.charCodeAt(i++);
        if (i == len) {
            out += base64EncodeChars.charAt(c1 >> 2);
            out += base64EncodeChars.charAt(((c1 & 0x3) << 4) | ((c2 & 0xF0) >> 4));
            out += base64EncodeChars.charAt((c2 & 0xF) << 2);
            out += "=";
            break;
        }
        c3 = str.charCodeAt(i++);
        out += base64EncodeChars.charAt(c1 >> 2);
        out += base64EncodeChars.charAt(((c1 & 0x3) << 4) | ((c2 & 0xF0) >> 4));
        out += base64EncodeChars.charAt(((c2 & 0xF) << 2) | ((c3 & 0xC0) >> 6));
        out += base64EncodeChars.charAt(c3 & 0x3F);
    }
    return out;
}

function upload(){
	if (checkFileSize('upload')) {
	<?php
		if(isset($public_key)){
			echo "$('#upload').attr('disabled',true)";
		}
	?>	
		$('#upform').submit();
	}
}

document.getElementById('upload').addEventListener('change', jsReadFiles, false);

</script>
