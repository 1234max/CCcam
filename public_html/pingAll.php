<?php
   loadServersHosts();
   
   if (rand(0,1) == 0)
   	  ksort($ServerHost_Conectat);
   else
   	  krsort($ServerHost_Conectat);
   	  
   echo "<table border=0 cellpadding=1 cellspacing=1>";
   echo "<tr>";
	echo "<td width=250 class=\"Node_ID\">Server</td>";
	echo "<td width=150 class=\"Node_ID\">Ping</td>";
	echo "<td width=60 class=\"Node_IDr\">Saved</td>";
	echo "<td width=100 class=\"Node_ID\">Comment</td>";
	echo "<td width=50 class=\"Node_ID\">Average</td>";
	echo "</tr>";
   echo "</table>"; 
   
   foreach ($ServerHost_Conectat as $sh_host => $server_Time)
   {
      ob_flush();
      flush();
      ob_flush();
      flush();
      $bestPingSaved = SavedPing($sh_host);
      list($host_DNS, $host_PORT) = explode(":", $sh_host);
      $textPing = "";
      $pingTry = "";
      if ($host_PORT > 0)
      {
	      $host_IP    = trim(getHostIP($host_DNS));
	      usleep(100000);
	      $pingTry1 = pingDomain($host_IP,$host_PORT,1);
	      if ($pingTry1 > 0)
	      {
	      	 $textPing = $pingTry1."ms ";
	      	 usleep(100000);
	      	 $pingTry2 = pingDomain($host_IP,$host_PORT,1);
	      	 if ($pingTry2 > 0)
	      	 {
	      	 	  $textPing = $textPing.", ".$pingTry2."ms ";
	      	 	  usleep(100000);
	      		  $pingTry3 = pingDomain($host_IP,$host_PORT,1);
		      	  if ($pingTry3 > 0)
		      	  {
		      	  	 $textPing = $textPing.", ".$pingTry3."ms ";
		      		   $pingMax = max($pingTry1, $pingTry2, $pingTry3); 
		      			 $pingTry = (int)(($pingTry1 + $pingTry2 + $pingTry3 - $pingMax)/2);
		      			 
		             SavePing($sh_host,$pingTry);
		             $LastPingError = "";
		          }
		       }
				}
	    }
	    
	    echo "<table border=0 cellpadding=1 cellspacing=1>";
   		echo "<tr>";
	    echo "<td width=250 class=\"Node_ID\"><A HREF=".$pagina."?nodeDns=$sh_host>".$sh_host."</A></td>";	
	    
	    if ($LastPingError == "")
	    	echo "<td width=150 class=\"tabel_normal\">".$textPing."</td>";
	    else
	    	echo "<td width=150 class=\"tabel_normal\"><FONT COLOR=red>".$LastPingError."</FONT></td>";
	    
	    if ($pingTry == "")
	    	echo "<td width=60 class=\"Node_IDr\"></td>";
	    else
	    	echo "<td width=60 class=\"Node_IDr\">".$pingTry."<FONT COLOR=gray> ms</FONT></td>";
	    	
	    echo "<td width=100 class=\"tabel_normal\">".pingResultColor($pingTry)."</td>";
	    echo "<td width=50 class=\"tabel_hop_total2\"><B>".pingColor(SavedPing($sh_host))."</B></td>";
	    echo "</tr>";
	    echo "</table>";
   }
   

	 ENDPage();
?>
