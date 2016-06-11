<?php
   $cron_p= ""; if(isset($_GET['p'])) $cron_p = $_GET['p'];
   $cron_profil = (int)(trim($cron_p));
   $cron_ping = true;
   include "common.php";
   
   loadServersHosts();
   
   $bestPingSaved = SavedPing($sh_host);
   
   if (rand(0,1) == 0)
   	  ksort($ServerHost_Conectat);
   else
   	  krsort($ServerHost_Conectat);
   
   foreach ($ServerHost_Conectat as $sh_host => $server_Time)
   {
   		$bestPingSaved = SavedPing($sh_host);
      list($host_DNS, $host_PORT) = explode(":", $sh_host);
      if ($host_PORT > 0)
      {
	      $host_IP    = trim(getHostIP($host_DNS));
	      usleep(100000);
	      $pingTry1 = pingDomain($host_IP,$host_PORT,1);
	      if ($pingTry1 > 0)
	      {
	      	 usleep(100000);
	      	 $pingTry2 = pingDomain($host_IP,$host_PORT,1);
	      	 if ($pingTry2 > 0)
	      	 {
	      	 	  usleep(100000);
	      		  $pingTry3 = pingDomain($host_IP,$host_PORT,1);
		      	  if ($pingTry3 > 0)
		      	  {
		      		   $pingMax = max($pingTry1, $pingTry2, $pingTry3); 
		      			 $pingTry = (int)(($pingTry1 + $pingTry2 + $pingTry3 - $pingMax)/2);
		      			 
		             SavePing($sh_host,$pingTry);
		          }
		       }
				}
	   }
   }
?>
