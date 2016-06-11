<?php
   $cron_p= ""; if(isset($_GET['p'])) $cron_p = $_GET['p'];
   $cron_profil = (int)(trim($cron_p));
   $cron_update = true;
   include "common.php";

   $forceupdate = 1;
   include "update.php";

?>
