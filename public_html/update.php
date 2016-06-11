<?php

function checkUpdateFile($filename_update)
{
   global $server_offline;
   global $update_failed;
   global $downloadfacut;
   
   if (!file_exists($filename_update)) 
   {
      $server_offline = true;
      global $cccam_host;
      global $webinfo_port;
      $update_failed = "<FONT COLOR=red><B>Update failed : </B></FONT>Unable to connect to $cccam_host:$webinfo_port";
   }
   else
   {
      $filename_update_data = file ($filename_update);
      if (count($filename_update_data)<1)
      {
         $server_offline = true;
         $update_failed = "<FONT COLOR=red><B>Update failed : </B></FONT>no data";
      }
      else
      {
         $linie = $filename_update_data[0];
         
         if (!strstr($linie,"200 OK"))
         {
            $server_offline = true;
            $update_failed = "<FONT COLOR=red><B>Update failed : </B></FONT>".$linie;
         }
      }
   }
   
   
   if ($server_offline == false) 
   {
      $downloadfacut = true;
      stripdata($filename_update);
   }
   else
   if (file_exists($filename_update)) 
      unlink($filename_update);
}

function CopyUpdate($filename_source,$filename_dest)
{
   if (!file_exists($filename_source))
      return;

   if (file_exists($filename_dest)) {unlink($filename_dest);}

   copy($filename_source, $filename_dest) ;
}


$update_failed = "";
//---------------------------------------
if ($cccam_host == "")
{
   $skipUpdate = true;
   $server_offline = true;
   $update_failed = "<FONT COLOR=red><B>Update failed : </B></FONT>Server not defined in config.php !";
   
}

$TIMP_Update = "";

if (!$skipUpdate)
{
   $timp1 = timpexec();
   
   $updatelog_text = time();
   
   if (file_exists($caminfo_update))         {unlink($caminfo_update);}
   if (file_exists($servers_update))         {unlink($servers_update);}
   if (file_exists($shares_update))          {unlink($shares_update);}
   if (file_exists($clients_update))         {unlink($clients_update);}
   if (file_exists($activeclients_update)) 	{unlink($activeclients_update);}
   if (file_exists($entitlements_update))    {unlink($entitlements_update);}
   
   
   $downloadfacut = false;
   
   if (!(file_exists($caminfo_file)) || $forceupdate ||$update_info)
   {
   	$ttt1 = timpexec();
      filesuck($cccam_host, $webinfo_port, $webinfo_user, $webinfo_pass, "/", $caminfo_update);
      checkUpdateFile($caminfo_update);
      $ttt2 = timpexec();
      $difff = number_format(((substr($ttt2,0,9)) + (substr($ttt2,-10)) - (substr($ttt1,0,9)) - (substr($ttt1,-10))),4);
      if ($downloadfacut == false) $difff = "error";
      $updatelog_text = $updatelog_text."\nInfo : ".$difff." ms";
   }
   
   if (!(file_exists($clients_file)) || $forceupdate ||$update_clients)
   if ($server_offline == false)
   {     
   	$ttt1 = timpexec();
      filesuck($cccam_host, $webinfo_port, $webinfo_user, $webinfo_pass, "/clients", $clients_update);
      checkUpdateFile($clients_update);
      $ttt2 = timpexec();
      $difff = number_format(((substr($ttt2,0,9)) + (substr($ttt2,-10)) - (substr($ttt1,0,9)) - (substr($ttt1,-10))),4);
      if ($downloadfacut == false) $difff = "error";
      $updatelog_text = $updatelog_text."\nClients : ".$difff." ms";
      
      if ($downloadfacut == true)
         saveUsageData(file($clients_update));
   }
   
   if (!(file_exists($servers_file)) || $forceupdate ||$update_servers)
   if ($server_offline == false)
   {
   	$ttt1 = timpexec();
      filesuck($cccam_host, $webinfo_port, $webinfo_user, $webinfo_pass, "/servers", $servers_update);
      checkUpdateFile($servers_update);
      $ttt2 = timpexec();
      $difff = number_format(((substr($ttt2,0,9)) + (substr($ttt2,-10)) - (substr($ttt1,0,9)) - (substr($ttt1,-10))),4);
      if ($downloadfacut == false) $difff = "error";
      $updatelog_text = $updatelog_text."\nServers : ".$difff." ms";

      if ($downloadfacut == true)
         saveOnlineData(file($servers_update));
   }

   if (!(file_exists($activeclients_file)) || $forceupdate ||$update_activeclients)
   if ($server_offline == false)
   {
   	$ttt1 = timpexec();
      filesuck($cccam_host, $webinfo_port, $webinfo_user, $webinfo_pass, "/activeclients", $activeclients_update);
      checkUpdateFile($activeclients_update);
      $ttt2 = timpexec();
      $difff = number_format(((substr($ttt2,0,9)) + (substr($ttt2,-10)) - (substr($ttt1,0,9)) - (substr($ttt1,-10))),4);
      if ($downloadfacut == false) $difff = "error";
      
      $updatelog_text = $updatelog_text."\nActiveclients : ".$difff." ms";
   }  
   
   if (!(file_exists($entitlements_file)) || $forceupdate ||$update_entitlements)
   if ($server_offline == false)
   {     
   	$ttt1 = timpexec();
      filesuck($cccam_host, $webinfo_port, $webinfo_user, $webinfo_pass, "/entitlements", $entitlements_update);
      checkUpdateFile($entitlements_update);
      $ttt2 = timpexec();
      $difff = number_format(((substr($ttt2,0,9)) + (substr($ttt2,-10)) - (substr($ttt1,0,9)) - (substr($ttt1,-10))),4);
      if ($downloadfacut == false) $difff = "error";
      
      $updatelog_text = $updatelog_text."\nEntitlements : ".$difff." ms";
   }  
   
   
   if (!(file_exists($shares_file)) || $forceupdate ||$update_shares)
   if ($server_offline == false)
   {     
   	$ttt1 = timpexec();
      filesuck($cccam_host, $webinfo_port, $webinfo_user, $webinfo_pass, "/shares", $shares_update);
      checkUpdateFile($shares_update);
      $ttt2 = timpexec();
      $difff = number_format(((substr($ttt2,0,9)) + (substr($ttt2,-10)) - (substr($ttt1,0,9)) - (substr($ttt1,-10))),4);
      if ($downloadfacut == false) $difff = "error";
      
      $updatelog_text = $updatelog_text."\nShares : ".$difff." ms";
   }
   

   if ($downloadfacut == true)
   {
      CopyUpdate($caminfo_update,$caminfo_file);
      CopyUpdate($servers_update,$servers_file);
      CopyUpdate($shares_update,$shares_file);
      CopyUpdate($clients_update,$clients_file);
      CopyUpdate($activeclients_update,$activeclients_file);
      CopyUpdate($entitlements_update,$entitlements_file);
      
      UpdateClientsCountryIP();
      UpdateServersCountryIP();
      UpdateServersECM();
      
      $timp2 = timpexec();

      $diferentaTIMP1 = number_format(((substr($timp2,0,9)) + (substr($timp2,-10)) - (substr($timp1,0,9)) - (substr($timp1,-10))),4);
      $TIMP_Update = "Update time: ".$diferentaTIMP1."s";
      
      $updatelog_text = $updatelog_text."\nTOTAL : ".$diferentaTIMP1." ms";
      
      $fp = @fopen($update_log,"w");
   	fwrite($fp, $updatelog_text);
   	fclose($fp);
   }
}
if ($TIMP_Update == "" && $timp_lastupdate!="")
	$TIMP_Update = " Updated ".get_formatted_timediff($timp_lastupdate)." ago"; 

$skipUpdate = true;

?>
