<?php 
include('header.php');
?>
<style type="text/css">
<!--
.style4 {font-size: 14px; }
.style9 {font-size: 14px; font-weight: bold; }
-->
</style>

<blockquote>
  
</blockquote>
<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td width="20%" valign="top">

<h4 align="left" class="style4">CCcam Config </h4>
<h5 align="left" class="style4">
<a href="editor.php?file=CCcam.cfg" target="_self">- CCcam.cfg</a><br>
</h5>
</td>
    <td width="5%">&nbsp;</td>
  </tr>
</table>

<div align="center">
  <blockquote>
    <p>
      <?php
 if (isset($_POST['Submit2'])) {
    $data = $_POST['text1'];
	$file_np = $_POST['filename'];
	$file = $CCcam_path.$_POST['filename'];  
   if (isset($_POST['site']) && $_POST['site']==1) {	$file = '../'.$_POST['filename'];  };
    if (!$file_handle = fopen($file,"w")) { echo "<h6>Erro opening file $file_np. Check permissions.\n</h6>"; }  
    if (!fwrite($file_handle, $data)) { echo "<h6>Error writing file $file_np. Check permissions.\n</h6>"; }  
     echo "<font color=\"#FF0000\" face=\"Arial\" size=\"4\">$file_np saved with success!</font>";   
    fclose($file_handle);  
 }
 if (isset($_GET['file'])){
   $file = $_GET['file'];
   
   {
   if (file_exists($CCcam_path.$file)) {
   $cw = file_get_contents($CCcam_path.$file);
   print "<h4 align=\"center\">".$file."</h4>";}
   else {   print "<h4 align=\"center\">File not found - ".$file."</h4>"; $cw='';}
   }
?>
    </p>
  </blockquote>
  <form id="form1" name="form1" method="post" action="<?=$_SERVER['PHP_SELF']?>">
  <div align="center">
    <p>
      <textarea name="text1" cols="100" rows="20"><?=$cw?></textarea>
    </p>
    <p>
      <input name="site" type="hidden" value="<?=isset($_GET['site'])?>">
      <input name="filename" type="hidden" value="<?=$file?>">
	  <input type="submit" name="Submit2" value="Save" />
    </p>
  </div>
</form>
<p>&nbsp;</p>
</div>
<?php
} 
include('footer.php');
?>
