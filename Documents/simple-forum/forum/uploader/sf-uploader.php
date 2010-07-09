<?php
/*
Simple:Press Forum
Image Uploader add-on to TinyMCE
Author: Phillip Winn
$LastChangedDate: 2009-06-04 02:51:02 +0100 (Thu, 04 Jun 2009) $
$Rev: 1986 $
*/

require_once("../../sf-config.php");

global $current_user, $fid;

$fid = sf_syscheckint($_GET['id']);

sf_initialise_globals($fid);

if (!$current_user->sfuploads)
{
	echo(__("Access Denied", "sforum"));
	die();
}

$uppath = get_option('sfuppath');
if($uppath == NULL) { die(); }
$lenpath = strlen($uppath);

ini_set("upload_max_filesize", "1048576");
ini_set("post_max_size", "1048576");
$imageTypes='';
if(imagetypes() & IMG_GIF) $imageTypes.=' GIF ';
if(imagetypes() & IMG_JPG) $imageTypes.=' JPG ';
if(imagetypes() & IMG_PNG) $imageTypes.=' PNG ';
if(imagetypes() & IMG_WBMP) $imageTypes.=' BMP ';

$abspath = trailingslashit(WP_CONTENT_DIR);
$absurl  = trailingslashit(WP_CONTENT_URL);

$path=sf_syscheckstr($_GET['folder']); if(substr($path,0, $lenpath)!=$uppath){$path=$uppath.'/'.$path;} $path=trim($path,'/');
$sfile=sf_syscheckstr($_GET['file']); $sfile=trim($sfile,'/');
$action=$_POST['action'];
$dname=$_POST['dname'];
$fname=$_FILES['fname'];

if($action=='create' && $dname) {
  $old=umask(0);
  mkdir($abspath.$path.'/'.$dname, 0755);
  umask($old);
  touch($abspath.$path.'/'.$dname.'/index.html');
  $path=$path.'/'.$dname;
}
if($action=='upload' && $fname['name']){
  $old=umask(0);
  $size=getimagesize($fname['tmp_name']);
  if ($size[mime] == 'image/jpeg' || $size[mime] == 'image/gif' || $size[mime] == 'image/png' || $size[mime] == 'image/bmp')
  {
     if(move_uploaded_file($fname['tmp_name'],$abspath.$path.'/'.$fname['name'])){
       $sfile=trim($fname['name'],'/');
       chmod($abspath.$path.'/'.$fname['name'],0644);
     } else { echo __("Error - Upload failed!", "sforum"); }
  } else { echo __("Error - File is not an image file!", "sforum"); }
  umask($old);
}

function compare_first($a, $b){ return strnatcasecmp($a[0],$b[0]); }

function getFiles($path) {
  global $abspath;
  # $path should be in the form images/whatever. We need the real system-level path
  $diskpath = $abspath.$path;
  # May someday want to track folders separately, for sorting Windows-style. Maybe not.
  $files = array();
  $folder = opendir($diskpath);
  while($filehandle = readdir($folder)){
    if(is_dir($diskpath.'/'.$filehandle) && $filehandle != '.' && $filehandle != '..'){
      $files[]=array($filehandle,'d');
    }elseif($filehandle != 'index.html' && !is_dir($diskpath.'/'.$filehandle)) {
      $filesize=filesize($diskpath.'/'.$filehandle);
      if($filesize > 0) {
        $filesize=round($filesize/1024);
        if($filesize < 1) {$filesize=1;}
      }
      $files[]=array($filehandle,'f',$filesize);
    }
  }
  usort($files,'compare_first');
  return $files;
}

function linkPath($path){
  global $urlpart;
  $url='';
  $urlpart='';
  $path_array=explode('/',$path);
  foreach($path_array as $path_element){
    $urlpart.="$path_element/";
    $url.="<a href='?folder=$urlpart'>$path_element</a>/";
  }
  return $url;
}
?>

<html><head><title>Browse and Upload Images</title>
<link rel="stylesheet" type="text/css" href="uploader.css" />
<style type="text/css">
form { margin:0; }
#tiny-uploader { width: 320px; height: 500px; margin: 0px; padding: 0px; background-image: url(images/uploader.gif); font-family: Verdana, Helvetica, Arial, sans-serif; font-size: 13px; }
#path { height: 19px; margin: 5px 0 0 5px; overflow: hidden; bborder: 1px solid orange; }
#list { height: 250px; margin: 0 0 0 5px; overflow: hidden; overflow-y: auto; bborder: 1px solid yellow; }
#action { height: 60px; margin: 5px 0 5px 5px; overflow: hidden; bborder: 1px solid blue; }
#preview { height: 160px; margin: 5px; overflow: hidden; bborder: 1px solid green; }

#list ul { list-style: none; margin: 0; padding: .5em; }
#list ul li { border: 1px solid white; border-bottom: 1px dotted #eee; }
#list ul li div.filename { float: left; }
#list ul li div.details { text-align: right; }

#previewimage { float: left; width: 150px; height: 150px; margin: 0 10px 0 5px; text-align: right; }
#preview { font-size: small; }
#preview span { font-weight: bold; display: block; margin-top: .5em; }

.sfcontrol { background: #E5E1FC; border: 1px solid #5364AE; font-family: Verdana, SanSerif; font-size: 12px; margin: 4px; }

</style>
<script>

function fileSelected(filename) {
        window.top.opener.plw_win.document.getElementById(window.top.opener.plw_field).value = "<?php echo $absurl.$path; ?>/" + filename;
        window.top.opener.plw_win.document.getElementById(window.top.opener.plw_field).onchange();
        window.close();
}

function checkUpload() {
  if(document.getElementById('fname').value.length == 0) {
    alert('Please select a file before clicking upload');
    return false;
  }
  document.getElementById('uploadButton').disabled = true;
  return true;
}

function uploadForm(action) {
  actionForm=document.getElementById('action');
  if(action=='newfolder') { actionForm.innerHTML='<form id="newfolder" method="post" action="<?php echo $PHP_SELF; ?>?folder=<?php echo $path; ?>&id=<?php echo $fid; ?>"><input type="hidden" name="action" value="create"/><?php _e('New Folder', 'sforum'); ?>: <input class="sfcontrol" type="text" name="dname" value=""/><br/><input class="sfcontrol" type="submit" value="<?php _e('Create', 'sforum'); ?>" /> <input class="sfcontrol" type="button" value="Cancel" onclick="javascript:uploadForm(\'cancel\'); return false;" /></form>'; }
  if(action=='upload') { actionForm.innerHTML='<form id="newfile" enctype="multipart/form-data" method="post" action="<?php echo $PHP_SELF; ?>?folder=<?php echo $path; ?>&id=<?php echo $fid; ?>" onsubmit="return checkUpload();"><input type="hidden" name="MAX_FILE_SIZE" value="1048576" /><input type="hidden" name="action" value="upload"/> <input class="sfcontrol" id="fname" name="fname" type="file"><br/><input class="sfcontrol" id="uploadButton" type="submit" value="<?php _e('Upload', 'sforum'); ?>" /> <input class="sfcontrol" type="button" value="Cancel" onclick="javascript:uploadForm(\'cancel\'); return false;" /><?php echo $imageTypes; ?></form>'; }
  if(action=='cancel') { actionForm.innerHTML='<form><b>Actions:</b> <input class="sfcontrol" type="button" value="<?php _e('New Folder', 'sforum'); ?>" onclick="javascript:uploadForm(\'newfolder\'); return false;" /> <input class="sfcontrol" type="button" value="<?php _e('Upload Image', 'sforum'); ?>" onclick="javascript:uploadForm(\'upload\'); return false;" /></form>'; }
}
</script>
</head><body id="tiny-uploader">
<div id="path"><b><?php _e('Folder', 'sforum'); ?>:</b> <?php echo linkPath($path); ?></div>
<div id="list"><b><?php _e('Images', 'sforum'); ?>:</b>
<?php $files=getFiles($path);
if(count($files)){
  echo "<ul>";
  foreach($files as $file)
  {
    if($file[1]=='d'){echo "<li><div class='filename'><a href='?id=$fid&folder=$urlpart$file[0]'>$file[0]</a> </div><div class='details'>[folder]</div></li>\n";}
    if($file[0] != 'index.php') {
      if($file[1]=='f'){echo "<li><div class='filename'><a href='?id=$fid&folder=$urlpart&file=$file[0]'>$file[0]</a></div> <div class='details'>$file[2] KB</div></li>\n";}
    }
  }
  echo "</ul>";
}
?>
</div>
<div id="action"><form><b><?php _e('Actions', 'sforum'); ?>:</b> <input class="sfcontrol" type="button" value="<?php _e('New Folder', 'sforum'); ?>" onclick="javascript:uploadForm('newfolder'); return false;" /> <input class="sfcontrol" type="button" value="<?php _e('Upload Image', 'sforum'); ?>" onclick="javascript:uploadForm('upload'); return false;" />
</form></div>
<div id="preview">
<?php if($sfile){
  $fsize=filesize("$abspath$path/$sfile");
  $ftime=Date('Y-m-d H:i:s',filemtime("$abspath$path/$sfile"));
  $isize=getimagesize("$abspath$path/$sfile");
  if($isize[1] > $isize[0]){$idim='height';}else{$idim='width';}
  echo "<div id='previewimage'><small>".__('Click on image to insert', 'sforum')."</small><a href='#' onclick='fileSelected(\"$sfile\"); return false;'><img src='".$absurl."$path/$sfile' $idim='150' /></a></div>";
  echo "<span class='label'>".__('Dimensions', 'sforum').":</span> $isize[0] x $isize[1]<br/>\n<span class='label'>".__('Size', 'sforum').":</span> $fsize bytes<br/>\n<span class='label'>".__('Uploaded', 'sforum').":</span>$ftime<br/>\n<span>".__('Type', 'sforum').":</span> $isize[mime]";
} ?>
</div>
</body>
</html>
