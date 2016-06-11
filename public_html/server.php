<?php
   
   if (file_exists($servers_file))
      $servers_data = file ($servers_file);
      
   if (!isset($servers_data))
   {
      echo "<FONT COLOR=red>Server is down or updating ... Please try again later !</FONT>";
      exit;
   }
   
   if (file_exists($shares_file))
      $shares_data = file ($shares_file);
      
   if (!isset($shares_data))
   {
      echo "<FONT COLOR=red>Server is down or updating ... Please try again later !</FONT>";
      exit;
   }  
   
   loadOnlineData();
   
   //___________________________________________________________________________________________________
   
   $index = 0;
   $lastServer = "";
   $ServerSelectat = "";
   foreach ($servers_data as $currentline)
   {
      $inceput1 = substr($currentline,0,1);
      $inceput2 = substr($currentline,1,2);
      
      if ($inceput1 == "|" && $inceput2 != " H")
      {
         $server = explode("|", $currentline);
         $server_Host   = trim($server[1]);
         $server_Time   = trim($server[2]);
         $server_Type   = trim($server[3]);
         $server_Ver    = trim($server[4]);
         $server_Nodeid = trim($server[5]);
         $server_Cards  = trim($server[6]);
         $server_Idents = trim($server[7]);
         
         if ($server_Host != "")
         {
            $index++;
            $lastServer = $server_Host;
            $servers[$lastServer]["Info"] = array ($server_Time,$server_Type,$server_Ver,$server_Nodeid,$server_Cards);  
            
            if ($server_Host == $server_nodeDns) $ServerSelectat = $lastServer;

         }
         
         $servers[$lastServer]["Info"]["Idents"][] = $server_Idents;  
      }
   }
   

   //___________________________________________________________________________________________________
   $total_shares["total"] = 0;
   $maxhop = 0;
   

   foreach ($shares_data as $currentline) 
   {
      $inceput1 = substr($currentline,0,1);
      $inceput2 = substr($currentline,1,1);
      
      if ($inceput1 == "|" && $inceput2 != " ")
      {
         $share = explode("|", $currentline);
         
         $share_Host = trim($share[1]);
         $share_Type = trim($share[2]);
         $share_Caid = trim($share[3]);
         $share_System = trim($share[4]);
         
         $share_ProvidersList = trim($share[5]);
         if ($share_ProvidersList == "0,1,2,3,0") $share_ProvidersList= "0,1,2,3"; // pt premiere
         if ($share_ProvidersList == "") $share_ProvidersList = $str_empty;
         $share_Providers = explode(",", $share_ProvidersList); 
         
         list($share_Hop,$share_Reshare) = explode("   ", trim($share[6]));
         $share_Nodes = explode(",", trim($share[7]));  
         $share_Node = $share_Nodes[count($share_Nodes)-1];
         
         $share_Caid = adaug0($share_Caid,4);
         
         $share_Node = sterg0($share_Node);
         
         if ($share_Node == "")
            $share_NodeUnic = $share_Caid."|".$share_System."|".$share_ProvidersList."|".$share_Host;
         else
            $share_NodeUnic = $share_Caid."|".$share_System."|".$share_ProvidersList."|".sterg0($share_Node);
         
         $route = (isset($nodes))?count($nodes[$share_NodeUnic]):0;
         $servers[$share_Host][$share_NodeUnic]["total"] = $route;
         if ($route == 0) $servers[$share_Host][$share_NodeUnic]["total"] = 1;
         $servers[$share_Host][$share_NodeUnic][$route] = array ($share_Host,$share_Type,$share_Caid,$share_System,$share_ProvidersList,$share_Providers,$share_Hop,$share_Reshare,$share_Nodes,$share_Node);  
         
         
         if (!isset($total_shares["total"]))                $total_shares["total"] = 0;
         if (!isset($total_shares[$share_Hop]))             $total_shares[$share_Hop] = 0;
         if (!isset($share_nodes[$share_NodeUnic]))         $share_nodes[$share_NodeUnic] = 0;
         if (!isset($share_nodes_minhop[$share_NodeUnic]))  $share_nodes_minhop[$share_NodeUnic] = 9;
         
         $share_nodes[$share_NodeUnic]++;
         $share_nodes_minhop[$share_NodeUnic] = min( $share_nodes_minhop[$share_NodeUnic], $share_Hop );
         $total_shares["total"]++;
         $total_shares[$share_Hop]++;
         
         if (!isset($host_hop[$share_Host][$share_NodeUnic][$share_Hop]))  $host_hop[$share_Host][$share_NodeUnic][$share_Hop] = 0;
         if (!isset($total_host_shares[$share_Host]["total"]))             $total_host_shares[$share_Host]["total"] = 0;
         if (!isset($total_host_shares[$share_Host][$share_Hop]))          $total_host_shares[$share_Host][$share_Hop] = 0;      
         if (!isset($total_reshare[$share_Host]["total"]))                 $total_reshare[$share_Host]["total"] = 0;    
         if (!isset($re[$share_Host][$share_NodeUnic][$share_Hop]))        $re[$share_Host][$share_NodeUnic][$share_Hop] = 0;
         if (!isset($re[$share_Host][$share_NodeUnic]["reshare"]))         $re[$share_Host][$share_NodeUnic]["reshare"] = 0;     
         
         $host_hop[$share_Host][$share_NodeUnic][$share_Hop]++;
         $total_host_shares[$share_Host]["total"]++;
         $total_host_shares[$share_Host][$share_Hop]++;
         
         if (((int)$share_Reshare)>0) 
         {
            $total_reshare[$share_Host]["total"]++;
            $re[$share_Host][$share_NodeUnic][$share_Hop] = max($re[$share_Host][$share_NodeUnic][$share_Hop],((int)$share_Reshare));
            $re[$share_Host][$share_NodeUnic]["reshare"] = max($re[$share_Host][$share_NodeUnic]["reshare"],((int)$share_Reshare));          
         }
         $maxhop = max($maxhop,$share_Hop);
      } 
   }
//___________________________________________________________________________________________________
function nodeID($NodeUnic,$Host,$Total,$Hop)
{
   global $share_nodes;
   global $share_nodes_minhop;
   
   $nodSh = explode("|",$NodeUnic); 
   
   $Server = $nodSh[3];
   
   $Server2 = explode("_",$Server); 
   
   $Server_Host = "";
   $Server_Host2 = "";

   $Server = nodeIdName($Server);
   
   $extra = $share_nodes[$NodeUnic] - $Total + 1;
   
   if ($Server_Host == $Host)
   {
      $ret = "<font color=Fuchsia><B>".$Server."</B></font>";
   }
   else
   if ($extra > 1)
   {
      if ($share_nodes_minhop[$NodeUnic] == $Hop)
         $ret = "<font color=brown>".$Server."</font>";
      else
         $ret = $Server;
   }
   else
   {
      $ret = "<font color=Crimson>".$Server."</font>";
   }
   
   if ($extra > 1)
         $ret.=" (".$extra.")";
         
   global $pagina;
   global $serverindex;
   $ret = linkNod($NodeUnic,$ret,"node",false);

   return $ret;
}
//___________________________________________________________________________________________________

if ($server_nodeDns != "") 
   $sh_host = $server_nodeDns;
else
   $sh_host = $ServerSelectat;
   
if ($sh_host != "") $nodes = $servers[$sh_host];

if (isset($nodes) && count($nodes))
{ 
   $info_total = 0;  
   if (isset($total_host_shares[$sh_host]["total"]))
      $info_total = $total_host_shares[$sh_host]["total"];
      
   $info_total_unic = count($nodes) - 1;
   
   $info_uniqueIndex = 0;
   if ($info_total!=0)
      $info_uniqueIndex = (int)($info_total_unic/$info_total *100);
   //$info_total_reshare = $total_reshare[$sh_host]["total"];
   //$info_reshareIndex = (int)($info_total_reshare/$info_total_unic *100);
   
   list($host_DNS, $host_PORT) = explode(":", $sh_host);
   
   $host_IP    = trim(getHostIP($host_DNS));
   $tara_host  = taraNameSaved($sh_host);
   $IPServer   = trim($tara_host[1]); 
   $tara_code  = tara($host_IP,$sh_host);


   if ($host_IP !="" && $IPServer !="" && $host_IP != $IPServer)
   {
      loadGlobalServers();
      $globalServers[$sh_host][1] = $IPServer;
      saveGlobalServers();
   }


   if ($country_whois == true) 
      $tara_nume = taraName($tara_code["tara"]);
      
   echo "<table border=0 cellpadding=0 cellspacing=0>";
   echo "<tr>";
   echo "<td VALIGN = \"top\">";

   format1("Server",$sh_host);

	if ($host_IP=="unknown")
   	format1("IP/Clients",$host_IP);
   else
   	format1("IP/Clients",$host_IP." / ".clientIP($host_IP,$sh_host));


   if ($country_whois == true)
   {
   	if ($tara_code["tara"]=="??")
   		 format1("Country","Unresolved IP");
   	else
      if ($tara_code["tara"]=="<>")
         format1("Country","Local Private IP");
      else
         format1("Country",$tara_code["tara"]." , ".$tara_nume);
   }
   //format1("Connected from IP",clientIP($host_IP,$sh_host));
   
   if ($nodes["Info"][3] != "")
      format1("NodeID", $nodes["Info"][3]); 
      
   if ($nodes["Info"][3] != "")
      format1("Type/Ver", $nodes["Info"][1]." / ".$nodes["Info"][2] );
   else
      format1("Type", $nodes["Info"][1] ); 
      
   if ($nodes["Info"][0] != "")
   	format1("Connected",$nodes["Info"][0]);
   else
   {
   	$text_offline = "-- OFFLINE --";
		if (isset($OnlineServers[$sh_host]) && $OnlineServers[$sh_host]["time"]!="")
		{
			$last_online = $OnlineServers[$sh_host]["time"];
			$text_offline = get_formatted_timediff($last_online)." ago";
		}
   	format1("Connected","<FONT color=red>$text_offline</FONT>");
   }
   
     

   //format1("Type",$nodes["Info"][1]);
   //format1("Version",$nodes["Info"][2]);
   //format1("Shares",$info_total);
   
   echo "</td>";
   echo "<td VALIGN = \"top\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Note :&nbsp;&nbsp;";
   echo "</td>";
   echo "<td VALIGN = \"top\">";
   
   $notes_saved = "";
   $notes_save_path   = $notes_path.$host_DNS.".".$host_PORT.".note";   
   if (file_exists($notes_save_path)) 
   {
      $fh = fopen($notes_save_path, 'r');
      $notes_saved = fread($fh, filesize($notes_save_path));
      fclose($fh); 
   }
   ?>
   <FORM NAME="formular" METHOD="post" ACTION="
   <?php 
   echo $pagina;
   ?>
   "><TEXTAREA CLASS="TEXTAREANORMAL" COLS="30" name="saveNotes" ID="saveNotes" ROWS="4" TABINDEX="1" value=""><?php echo $notes_saved ?></TEXTAREA>
   <DIV ALIGN = "right"><INPUT TYPE="submit" VALUE="Save" TABINDEX="2" class="savetextbutton"></DIV>
      <input type="hidden" name="nodeDns" ID="nodeDns" value="<?php echo $server_nodeDns ?>">
   </FORM>
   <?php
   echo "</td>";
   echo "</tr>";
   echo "</table>";
   
   
   
   // online history
   $online_saveTime = $OnlineServers["savetime"]["time"];
   $online_lastseen = $OnlineServers[$sh_host]["time"];
   $online_history  = $OnlineServers[$sh_host]["log"];
  /* 
   if ($online_saveTime != $online_lastseen && $online_lastseen!="")
   {
   	$timediff = ($online_saveTime - $online_lastseen);
	   $minuteLastSave  = (int) intval($timediff / INT_MINUTE);
	   $online_history = "-".$minuteLastSave.".".$online_history;
   }
	*/
   
   $logtime = explode(".", $online_history);
   $history_off = 0;
   $history_on = 0;
   foreach ($logtime as $log)
   {
   	if (strstr($log,"-"))
   	{
   		$history_off = $history_off + substr($log,1);
   	}
   	else 	
   	{
   		$history_on = $history_on + $log;
   	}
	} 
	
	$DIV_history_off = "";
	if ($history_off >0) $DIV_history_off = "<FONT color=red>".formatted_timediff($history_off*60)."</FONT>";
	
	$DIV_history_total = formatted_timediff(($history_off + $history_on)*60);
   
   if ($history_off >0)
   	format1("Uptime",procentColor(procentOnline($sh_host))." [ ".$DIV_history_off." / ".$DIV_history_total." ]");
   else
   	format1("Uptime",procentColor(procentOnline($sh_host))." [ ".$DIV_history_total." ]");
   	
   if ($country_whois == true && $tara_code["tara"]!="<>")
   {
      format1("ISP Info",$tara_code["info"]);
   }
   echo "<BR>";
   
   ob_flush();
	flush();

	$LastPingError = "Very slow";
   $pingCount = 5;
   $pingCountOK = 0;
   $pingLast = 0;
   $pingTimeTotal = 0;
   
   //echo "<table border=0 cellpadding=0 cellspacing=0>";
   $ping_text_try = "";
   
   $stringquerry = $_SERVER['QUERY_STRING'];
   if (!strstr($stringquerry,"&checkPing=1")) 
   	$stringquerry = $stringquerry."&checkPing=1";
   $ping_text_try_check = "&nbsp;<A class=\"tabel_header\" HREF=".$pagina."?".$stringquerry.">&nbsp;PING NOW&nbsp;&nbsp;</A>";
   
   $pingTryMax = 0;
   if ($server_checkPing != "")
   {
      for ($k = 1; $k <= $pingCount; $k++) 
      {
         usleep(1000000);
         $pingTry = -1;
         $pingTry = pingDomain($host_IP,$host_PORT,3);
         
         if ($pingTryMax == 0)
      		  $pingTryMax = $pingTry;
         else
   		   if ($pingTryMax < $pingTry) $pingTryMax = $pingTry;

         if ($k>1) $ping_text_try = $ping_text_try." , ";
         if ($pingTry>0) 
         {
            $pingCountOK++;
            
            $ping_text_try = $ping_text_try.$pingTry."ms";
         }
         else
         {
            $ping_text_try = $ping_text_try."<FONT color=red>--</FONT>";
            if ($pingLast==-1)
            {
               $pingTimeTotal = -1;
               break;
            }
         }
   
            
         $pingTime[] = $pingTry;
         $pingLast = $pingTry; 
      }
      $ping_text_try = " : {".$ping_text_try."}";
   }
   
   //echo "</table>";
   
   $textPingCurent = "";

   if ($pingTimeTotal == 0)
   {
      
    
      $lost = $pingCount - $pingCountOK;
      
      $rezultatPing = $ping_text_try_check."<FONT color=gray>".$ping_text_try."</FONT>";
      
       
         		
      if ($lost == 0)
      {
      	 for ($k = 0; $k < $pingCount; $k++)
         	  if ($pingTime[$k] >0) $pingTimeTotal = $pingTimeTotal + $pingTime[$k];
      
      	 $pingTimeTotal = $pingTimeTotal - $pingTryMax;
          $pingTimeFinal = (int)($pingTimeTotal/($pingCountOK-1));
      
      	 SavePing($sh_host,$pingTimeFinal);
      	 $textPingCurent  = " = ".$pingTimeFinal."ms ";
      }
      else
      if ($server_checkPing != "")
      	$textPingCurent  = " = NOT SAVED";
	
   }
   else   
   if ($server_checkPing != "")
   {
  		$rezultatPing = $ping_text_try_check.": ".$LastPingError;
   }
   else
  	   $rezultatPing = $ping_text_try_check;
     
   
   $bestPingSaved = SavedPing($sh_host);
   
   $textPingAverage = pingResultColor($bestPingSaved[0],1);
   $textPingBest    = pingResultColor($bestPingSaved[2],1);
   
   $diffPing = $bestPingSaved[0] - $bestPingSaved[2];
   $diffMargin = (int)($bestPingSaved[2] * 50/100);
   if ($diffMargin < 50) $diffMargin  = 50;
   
   if ($diffPing > $diffMargin ) $textPingAverage = "<FONT COLOR=red><B>! </B></FONT>".$textPingAverage;
   
   format1("Best ever responce time (ping) ",$textPingBest);
   format1("Average responce time (ping) ",$textPingAverage);
   format1("Current responce time (ping) ",$rezultatPing.$textPingCurent);
   
   
   
/* 
   $webinterface = pingDomain($host_IP,"16001",1);
   
   if ($webinterface!=-1)
      format1("Webinterface default","<A class=\"tabel_header\" HREF=\"http://$host_IP:16001\" target=\"_blank\">CHECK&nbsp;</A>");
*/ 
   //format1("Unique",$info_total_unic,$info_total);
   //format1("Reshare",$info_total_reshare,$info_total_unic);

   $ecm_total = 0;
   $ecmOK_total = 0;
   foreach ($servers[$sh_host]["Info"]["Idents"] as $hit_data)
   {
      $hit_array = explode(" ",$hit_data);
      if ($hit_array[0] != "")
      {
         $hit_provider = explode(":",$hit_array[1]);
         $hit_exact = explode("(",$hit_array[2]);
         $hit_exact2 = explode(")",$hit_exact[1]);
         
         $hit_ecm = $hit_exact[0];
         $hit_ecmOK = $hit_exact2[0];
         
         $ecm_total = $ecm_total+$hit_ecm;
         $ecmOK_total = $ecmOK_total+$hit_ecmOK;
         
         $key = adaug0($hit_ecmOK,20).adaug0($hit_ecm,20).$hit_array[1];
         $ecmok_sortat[$key] = $hit_data;
         
      }
   }
   
   loadECMServers(true);
   echo "<BR>";   
   $record_ECM = $ECMservers[$sh_host]["Info"]["ECM_SAVED"]["ECM"];
   $record_ECMOK = $ECMservers[$sh_host]["Info"]["ECM_SAVED"]["ECMOK"];
   
   if (($ecm_total == $record_ECM) && ($ecmOK_total == $record_ECMOK))
      format1("Handled ECM",$record_ECMOK,$record_ECM);
   else
   {
      if ($record_ECM !="")
         format1("Handled ECM ( saved )",$record_ECMOK,$record_ECM);
         
      format1("Handled ECM ( now )",$ecmOK_total,$ecm_total);
   }
   
   if ($ecm_total)
   {
      //echo "<BR>"; 
      echo "<table border=0 cellpadding=2 cellspacing=1>";
      echo "<tr>";
      echo "<th class=\"tabel_headerc\">#</th>";
      echo "<th class=\"tabel_headerc\">Type</th>";
      echo "<th class=\"tabel_header\">ECM</th>";
      echo "<th class=\"tabel_header\">OK</th>";
      echo "<th class=\"tabel_header\">CAID/Ident</th>";
      echo "</tr>";
   }
   
  if (isset($ecmok_sortat))
  {
   	krsort($ecmok_sortat);
   
	   $counthits = 0;
	   if ($ecmok_sortat)
	   foreach ($ecmok_sortat as $key => $hit_data)
	   {
	      $hit_array = explode(" ",$hit_data);
	      if ($hit_array[0] != "")
	      {
	         $counthits++;
	         $hit_provider = explode(":",$hit_array[1]);
	         $hit_exact = explode("(",$hit_array[2]);
	         $hit_exact2 = explode(")",$hit_exact[1]);
	         
	         $hit_ecm = $hit_exact[0];
	         $hit_ecmOK = $hit_exact2[0];
	         
	         echo "<tr>";
	         echo "<td class=\"Node_Provider\">".$counthits."</td>";
	         echo "<td class=\"tabel_hop_total1\">".$hit_array[0]."</td>";
	         
	         
	         echo "<td class=\"tabel_hop_total\"><FONT COLOR=white>".$hit_ecm."</FONT></td>";
	         
	         if ($hit_ecmOK == 0)  echo "<td class=\"tabel_hop_total\"><FONT COLOR=red>".$hit_ecmOK."</FONT></td>";
	         else
	         if ($hit_ecmOK != $hit_ecm)  echo "<td class=\"tabel_hop_total\"><FONT COLOR=yellow>".$hit_ecmOK."</FONT></td>";
	         else
	            echo "<td class=\"tabel_hop_total\">".$hit_ecmOK."</td>";
	         
	         echo "<td class=\"Node_Provider\">".Providerid($hit_provider[0],$hit_provider[1],true,"Node_Provider",false)."</td>";
	         echo "</tr>";
	      }
	   }
	}
	
   if ($ecm_total)
      echo "</table>";
   echo "<BR>";
   
   $total_nodes = 0;for($k = 0; $k <= $maxhop; $k++)
   {
      if (isset($total_host_shares[$sh_host][$k]))
         $total_nodes += $total_host_shares[$sh_host][$k];
   }

if ($total_nodes>0)
{
   format1("Nodes",$info_total_unic);
   echo "<table border=0 cellpadding=2 cellspacing=1>";
   echo "<tr>";
   echo "<th class=\"tabel_headerc\">#</th>";
   echo "<th class=\"tabel_header\">NodeID/Server (extra sources)</th>";
   echo "<th class=\"tabel_header\">Shares</th>";

   for ($k = 1; $k <= $maxhop; $k++) echo "<th class=\"tabel_headerc\">hop".$k."</th>"; 
   echo "<th class=\"tabel_headerc\">Reshare</th>";
   echo "<th class=\"tabel_header\">CAID/Idents</th>";
   echo "</tr>";
   
   //$info_total = $total_host_shares[$sh_host]["total"];
   //$info_total_unic = count($nodes) - 1;
   //$info_uniqueIndex = (int)($info_total_unic/$info_total *100);
   //$info_total_reshare = $total_reshare[$sh_host]["total"];
   //$info_reshareIndex = (int)($info_total_reshare/$info_total_unic *100);
   
   echo "<tr>";
   echo "<td class=\"tabel_total\"></td>";
   echo "<td class=\"tabel_total\"></td>";
   echo "<td class=\"tabel_total\">".$total_host_shares[$sh_host]["total"]."</td>";
   for ($k = 1; $k <= $maxhop; $k++) echo "<td class=\"tabel_total\">".$total_host_shares[$sh_host][$k]."</td>"; 
   echo "<td class=\"tabel_total\">".$total_reshare[$sh_host]["total"]."</td>";
   echo "<td class=\"tabel_total\"></td>";
   echo "</tr>";
   
   $i=1;
   
   // HOP1
   if (isset($total_shares[1]))
   foreach ($nodes as $node=>$data) 
   if (!isset($host_hop[$sh_host][$node][0]) && isset($host_hop[$sh_host][$node][1]))
   {
      $nodetype = $nodes[$node][0][1];
      $caid = $nodes[$node][0][2];
      $providers = $nodes[$node][0][4];
      $nodSh = explode("|",$node);  
      
      $total = 0;for($k = 0; $k <= $maxhop; $k++) 
         if (isset($host_hop[$sh_host][$node][$k]))
            $total += $host_hop[$sh_host][$node][$k];
      
      $reshare = "<FONT COLOR=red>! NO !</FONT>";if ($re[$sh_host][$node]["reshare"] >0) if ($total==1) $reshare = $re[$sh_host][$node]["reshare"]; else $reshare = "<FONT COLOR=yellow>".$re[$sh_host][$node]["reshare"]."</FONT>";

      echo "<tr>";
      echo "<td class=\"Node_Count\">".$i."</td>";
         
      echo "<td class=\"Node_ID_hop1\">".nodeID($node,$sh_host,$total,1)."</td>";
      echo "<td class=\"tabel_total\">".$total."</td>";

      for ($k = 1; $k <= $maxhop; $k++) 
         if (isset($host_hop[$sh_host][$node][$k]))
            echo "<td class=\"tabel_hop\">".$host_hop[$sh_host][$node][$k]."</td>";
         else
            echo "<td class=\"tabel_hop\"></td>";
      
      echo "<td class=\"tabel_total\">".$reshare."</td>";
      echo "<td class=\"Node_Provider\">".providerID($caid,$providers,true,"Node_Provider",false)."</td>";
      echo "</tr>";
      $i++;
   }
   
   // HOP2
   if (isset($total_shares[2]))
   foreach ($nodes as $node=>$data) 
   if (!isset($host_hop[$sh_host][$node][0]) && !isset($host_hop[$sh_host][$node][1]) && isset($host_hop[$sh_host][$node][2]))
   {
      $nodetype = $nodes[$node][0][1];
      $caid = $nodes[$node][0][2];
      $providers = $nodes[$node][0][4];
      $nodSh = explode("|",$node);  
         
      $total = 0;for($k = 0; $k <= $maxhop; $k++) 
         if (isset($host_hop[$sh_host][$node][$k]))
            $total += $host_hop[$sh_host][$node][$k];
            
      $reshare = "<FONT COLOR=red>! NO !</FONT>";if ($re[$sh_host][$node]["reshare"] >0) if ($total==1) $reshare = $re[$sh_host][$node]["reshare"]; else $reshare = "<FONT COLOR=yellow>".$re[$sh_host][$node]["reshare"]."</FONT>";
      
      echo "<tr>";
      if (strstr($node,"*"))
         echo "<td class=\"tabel_normal_type\">".$i."</td>";
      else
         echo "<td class=\"Node_Count\">".$i."</td>";
         
      echo "<td class=\"Node_ID_hop2\">".nodeID($node,$sh_host,$total,2)."</td>";
      echo "<td class=\"tabel_total\">".$total."</td>";

      for ($k = 1; $k <= $maxhop; $k++) 
         if (isset($host_hop[$sh_host][$node][$k]))
            echo "<td class=\"tabel_hop\">".$host_hop[$sh_host][$node][$k]."</td>";
         else
            echo "<td class=\"tabel_hop\"></td>";
            
      echo "<td class=\"tabel_total\">".$reshare."</td>";
      echo "<td class=\"Node_Provider\">".providerID($caid,$providers,true,"Node_Provider",false)."</td>";
      echo "</tr>";
      $i++;
   }
   
   // HOP3
   if (isset($total_shares[3]))
   foreach ($nodes as $node=>$data) 
   if (!isset($host_hop[$sh_host][$node][0]) && !isset($host_hop[$sh_host][$node][1]) && !isset($host_hop[$sh_host][$node][2]) && isset($host_hop[$sh_host][$node][3]))
   {
      $nodetype = $nodes[$node][0][1];
      $caid = $nodes[$node][0][2];
      $providers = $nodes[$node][0][4];
      $nodSh = explode("|",$node);
      
      $total = 0;for($k = 0; $k <= $maxhop; $k++) 
         if (isset($host_hop[$sh_host][$node][$k]))
            $total += $host_hop[$sh_host][$node][$k];
            
      $reshare = "<FONT COLOR=red>! NO !</FONT>";if ($re[$sh_host][$node]["reshare"] >0) if ($total==1) $reshare = $re[$sh_host][$node]["reshare"]; else $reshare = "<FONT COLOR=yellow>".$re[$sh_host][$node]["reshare"]."</FONT>";

      echo "<tr>";
      if (strstr($node,"*"))
         echo "<td class=\"tabel_normal_type\">".$i."</td>";
      else
         echo "<td class=\"Node_Count\">".$i."</td>";
      echo "<td class=\"Node_ID_hop3\">".nodeID($node,$sh_host,$total,3)."</td>";
      echo "<td class=\"tabel_total\">".$total."</td>";

      for ($k = 1; $k <= $maxhop; $k++) 
         if (isset($host_hop[$sh_host][$node][$k]))
            echo "<td class=\"tabel_hop\">".$host_hop[$sh_host][$node][$k]."</td>";
         else
            echo "<td class=\"tabel_hop\"></td>";
            
      echo "<td class=\"tabel_total\">".$reshare."</td>";
      echo "<td class=\"Node_Provider\">".providerID($caid,$providers,true,"Node_Provider",false)."</td>";
      echo "</tr>";
      $i++;
   }
   
   
   // HOP4
   if (isset($total_shares[4]))
   foreach ($nodes as $node=>$data) 
   if (!isset($host_hop[$sh_host][$node][0]) && !isset($host_hop[$sh_host][$node][1]) && !isset($host_hop[$sh_host][$node][2]) && !isset($host_hop[$sh_host][$node][3]) && isset($host_hop[$sh_host][$node][4]))
   {
      $nodetype = $nodes[$node][0][1];
      $caid = $nodes[$node][0][2];
      $providers = $nodes[$node][0][4];
      $nodSh = explode("|",$node);
      
      $total = 0;for($k = 0; $k <= $maxhop; $k++) 
         if (isset($host_hop[$sh_host][$node][$k]))
            $total += $host_hop[$sh_host][$node][$k];
            
      $reshare = "<FONT COLOR=red>! NO !</FONT>";if ($re[$sh_host][$node]["reshare"] >0) if ($total==1) $reshare = $re[$sh_host][$node]["reshare"]; else $reshare = "<FONT COLOR=yellow>".$re[$sh_host][$node]["reshare"]."</FONT>";

      echo "<tr>";
      if (strstr($node,"*"))
         echo "<td class=\"tabel_normal_type\">".$i."</td>";
      else
         echo "<td class=\"Node_Count\">".$i."</td>";
      echo "<td class=\"Node_ID_hop4\">".nodeID($node,$sh_host,$total,4)."</td>";
      echo "<td class=\"tabel_total\">".$total."</td>";

      for ($k = 1; $k <= $maxhop; $k++) 
         if (isset($host_hop[$sh_host][$node][$k]))
            echo "<td class=\"tabel_hop\">".$host_hop[$sh_host][$node][$k]."</td>";
         else
            echo "<td class=\"tabel_hop\"></td>";
            
      echo "<td class=\"tabel_total\">".$reshare."</td>";
      echo "<td class=\"Node_Provider\">".providerID($caid,$providers,true,"Node_Provider",false)."</td>";
      echo "</tr>";
      $i++;
   }
   
   // HOP5 sau mai mult
   foreach ($nodes as $node=>$data) 
   if ($node != "Info")
   if (!isset($host_hop[$sh_host][$node][0]) && !isset($host_hop[$sh_host][$node][1]) && !isset($host_hop[$sh_host][$node][2]) && !isset($host_hop[$sh_host][$node][3]) && !isset($host_hop[$sh_host][$node][4]))
   {
      $nodetype = $nodes[$node][0][1];
      $caid = $nodes[$node][0][2];
      $providers = $nodes[$node][0][4];
      $nodSh = explode("|",$node);
      
      $total = 0;for($k = 0; $k <= $maxhop; $k++) 
         if (isset($host_hop[$sh_host][$node][$k]))
            $total += $host_hop[$sh_host][$node][$k];
            
      $reshare = "<FONT COLOR=red>! NO !</FONT>";if ($re[$sh_host][$node]["reshare"] >0) if ($total==1) $reshare = $re[$sh_host][$node]["reshare"]; else $reshare = "<FONT COLOR=yellow>".$re[$sh_host][$node]["reshare"]."</FONT>";

      echo "<tr>";
      if (strstr($node,"*"))
         echo "<td class=\"tabel_normal_type\">".$i."</td>";
      else
         echo "<td class=\"Node_Count\">".$i."</td>";
      echo "<td class=\"Node_ID_hop5\">".nodeID($node,$sh_host,$total,5)."</td>";
      echo "<td class=\"tabel_total\">".$total."</td>";

      for ($k = 1; $k <= $maxhop; $k++) 
         if (isset($host_hop[$sh_host][$node][$k]))
            echo "<td class=\"tabel_hop\">".$host_hop[$sh_host][$node][$k]."</td>";
         else
            echo "<td class=\"tabel_hop\"></td>";
            
      echo "<td class=\"tabel_total\">".$reshare."</td>";
      echo "<td class=\"Node_Provider\">".providerID($caid,$providers,true,"Node_Provider",false)."</td>";
      echo "</tr>";
      $i++;
   }
   
   echo "</table>";
}

}

ENDPage();
?>
