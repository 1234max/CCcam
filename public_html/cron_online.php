<?php
   $cron_p= ""; if(isset($_GET['p'])) $cron_p = $_GET['p'];
   $cron_profil = (int)(trim($cron_p));
   $cron_update = true;
   include "common.php";

   $servers_data = filesuck($cccam_host, $webinfo_port, $webinfo_user, $webinfo_pass, "/servers", "");
   $servers_data = stripcontents($servers_data);
   saveOnlineData($servers_data);
   
   $clients_data = filesuck($cccam_host, $webinfo_port, $webinfo_user, $webinfo_pass, "/clients", "");
   $clients_data = stripcontents($clients_data);
   saveUsageData($clients_data);
?>
