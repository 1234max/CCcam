<?php include "common.php"?>
<?php
   
   if (!$update_from_button)
   {
      $update_info = true;
      $update_shares = true;
      $update_servers = true;
   }
   
   include "meniu.php";
   

   checkFile($servers_file);
   $servers_data = file ($servers_file);

   checkFile($shares_file);
   $shares_data = file ($shares_file);
   
   //___________________________________________________________________________________________________

   initClients();
   initPairs();
   loadGlobalServers();
   UpdateServersECM();
   loadOnlineData();

   if (!isset($ServerHost_Conectat)) loadServersHosts();

   $servers = $ECMservers;
   
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
         
         if (isset($servers[$share_Host]))
         {
         
            $share_ProvidersList = trim($share[5]);
            if ($share_ProvidersList == "0,1,2,3,0") $share_ProvidersList= "0,1,2,3"; // pt premiere
            if ($share_ProvidersList == "") $share_ProvidersList = $str_empty;
            $share_Providers = explode(",", $share_ProvidersList); 
            
            list($share_Hop,$share_Reshare) = explode("   ", trim($share[6]));
            $share_Nodes = explode(",", trim($share[7]));  
            $share_Node = $share_Nodes[count($share_Nodes)-1];
            
            $share_Node = sterg0($share_Node);
            
            if ($share_Node == "")
               $share_NodeUnic = $share_Caid."|".$share_System."|".$share_ProvidersList."|".$share_Host;
            else
               $share_NodeUnic = $share_Caid."|".$share_System."|".$share_ProvidersList."|".$share_Node;
               
            $route = (isset($nodes))?count($nodes[$share_NodeUnic]):0;
            $servers[$share_Host][$share_NodeUnic]["total"] = $route;
            if ($route == 0) $host_nodes[$share_Host][$share_NodeUnic]["total"] = 1;
            $servers[$share_Host][$share_NodeUnic][$route] = array ($share_Host,$share_Type,$share_Caid,$share_System,$share_ProvidersList,$share_Providers,$share_Hop,$share_Reshare,$share_Nodes,$share_Node);  
            
            
            if (!isset($total_shares["total"]))                $total_shares["total"] = 0;
            if (!isset($total_shares[$share_Hop]))             $total_shares[$share_Hop] = 0;
            if (!isset($share_nodes[$share_NodeUnic]))         $share_nodes[$share_NodeUnic] = 0;
            if (!isset($share_nodes_minhop[$share_NodeUnic]))  $share_nodes_minhop[$share_NodeUnic] = 9;
            
            $share_nodes[$share_NodeUnic]++;
            $share_nodes_minhop[$share_NodeUnic] = min( $share_nodes_minhop[$share_NodeUnic], $share_Hop );
            $total_shares["total"]++;
            $total_shares[$share_Hop]++;
            
            if ($share_Node!="" && $share_Hop == 1)
               $Server_Cunoscut[$share_Node] = $share_Host; 
            
            if (!isset($host_hop[$share_Host][$share_NodeUnic][$share_Hop]))  $host_hop[$share_Host][$share_NodeUnic][$share_Hop] = 0;
            if (!isset($total_host_shares[$share_Host]["total"]))             $total_host_shares[$share_Host]["total"] = 0;
            if (!isset($total_host_shares[$share_Host][$share_Hop]))          $total_host_shares[$share_Host][$share_Hop] = 0;      
            if (!isset($total_reshare[$share_Host]["total"]))                 $total_reshare[$share_Host]["total"] = 0;    
            if (!isset($total_reshare[$share_Host]["maxim"]))                 $total_reshare[$share_Host]["maxim"] = 0;    
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
               
               $total_reshare[$share_Host]["maxim"] = max($total_reshare[$share_Host]["maxim"],((int)$share_Reshare));        
            }
            
            $maxhop = max($maxhop,$share_Hop);
         }
      } 
   }
   
   
   
   if (file_exists($country_file))
   {
      $country_data = file ($country_file);
      foreach ($country_data as $currentline) 
      {
         list($IP_CACHE, $TARA_CACHE, $SERVER_USER) = explode("|", $currentline);
         $IP_CACHE = trim($IP_CACHE);
         $TARA_CACHE = trim($TARA_CACHE);
         $SERVER_USER = trim($SERVER_USER);
         
         $SERVER_IP[$IP_CACHE]["TARA"] = $IP_CACHE;
         
         if (strstr($SERVER_USER,":")) 
         {
            $SERVER_IP[$IP_CACHE][] = $SERVER_USER;
            
            $SERVER_CLIENT[$SERVER_USER]["NAME"] = $SERVER_USER;
            $SERVER_CLIENT[$SERVER_USER]["IP"]   = $IP_CACHE;
            $SERVER_CLIENT[$SERVER_USER]["TARA"] = $TARA_CACHE;
         }
         else
         {
            $CLIENT_IP[$IP_CACHE][] = $SERVER_USER;
         }
      }
   }
   
   $newPairfound = false;
   foreach ($SERVER_CLIENT as $SERVER_GASIT) 
   {
      $SERVER_GASIT_IP = $SERVER_CLIENT[$SERVER_GASIT["NAME"]]["IP"];
   
      if (isset($CLIENT_IP[$SERVER_GASIT_IP]))
      foreach ($CLIENT_IP[$SERVER_GASIT_IP] as $CLIENT_GASIT) 
      {
         $existadaja = false;
         foreach ($SERVER_PAIR[$SERVER_GASIT["NAME"]] as $nr => $CLIENT_EXISTENT) 
         {
            //echo $CLIENT_EXISTENT."<BR>";
            if ($CLIENT_GASIT == $CLIENT_EXISTENT)
            {
               $existadaja = true;
               break;
            }
         }
         if ($existadaja == false)
         {
            
            echo "New pair found : ".$CLIENT_GASIT." = ".$SERVER_GASIT["NAME"]." (".$SERVER_GASIT_IP.")<BR>";
            $SERVER_PAIR[$SERVER_GASIT["NAME"]][] = $CLIENT_GASIT;
            $newPairfound = true;
         }
      }
   }
   
   if ($newPairfound == true) $fp = @fopen($pair_file,"w");
   foreach ($SERVER_PAIR as $SERVER_GASIT => $SERVER_CLIENTI) 
   {
      
      $text = $SERVER_GASIT."/";
      $i = 0;

      foreach ($SERVER_CLIENTI as $SERVER_CLIENT_SAVE)
      if (trim($SERVER_CLIENT_SAVE) != "")
      {
         
         $i++;
         if ($i!=1) $text = $text.";";
         $text = $text.$SERVER_CLIENT_SAVE;
         if (isset($ServerHost_Conectat[$SERVER_GASIT]))
            $client_pereche[$SERVER_CLIENT_SAVE] = "ok";
         //else
            //$client_pereche[$SERVER_CLIENT_SAVE] = "no";
      }
      if ($newPairfound == true) fwrite($fp, $text."\n");
   }
   if ($newPairfound == true) fclose($fp);

//___________________________________________________________________________________________________

$total_servers = 0; 
$total_connected_servers = 0; 

if (isset($servers) && count($servers)>0)
{
   foreach ($servers as $sh_host => $nodes)
   {
      $total_servers++;
      if ($nodes["Info"][0] != "") 
         $total_connected_servers++;
   }
}

$textClientiPierduti = "";

foreach ($clientConectat as $client_conectat_gasit => $info)
{
   
   if (!isset($client_pereche[$client_conectat_gasit]))
   {
      if ($textClientiPierduti !="") $textClientiPierduti = $textClientiPierduti." , ";
      $textClientiPierduti = $textClientiPierduti."<A HREF=".$pagina."?username=".$client_conectat_gasit.">".$client_conectat_gasit."</A>";

      $servers[$client_conectat_gasit]["client"] = "client";
   }
}

if ($textClientiPierduti !="")
   format1("Connected Clients with no pair",$textClientiPierduti);

//format1("Servers connected",$total_connected_servers,$total_servers);

echo "<BR>";
echo "<table width=100% border=0 cellpadding=2 cellspacing=1";
echo "<tr>";
echo "<th class=\"tabel_headerc\">#</th>";
//echo "<th class=\"tabel_headerc\"><A class=\"header\" HREF=".$pagina."?sort=rating>Rating</A></th>";
echo "<th class=\"tabel_headerc\"><A class=\"header\" HREF=".$pagina."?sort=type>Type</A></th>";
echo "<th class=\"tabel_headerr\"><A class=\"header\" HREF=".$pagina."?sort=client>Client</A></th>";
echo "<th class=\"tabel_header\"><A class=\"header\" HREF=".$pagina."?sort=server>Server</A></th>";
if ($country_whois == true) 
   echo "<th class=\"tabel_headerc\"><A class=\"header\" HREF=".$pagina."?sort=country>Cnt</A></th>";
echo "<th class=\"tabel_headerc\"><A class=\"header\" HREF=".$pagina."?sort=ping>Ping</A></th>";
//echo "<th class=\"tabel_headerc\"><A class=\"header\" HREF=".$pagina."?sort=pingbest>Best</A></th>";
echo "<th class=\"tabel_headerc\"><A class=\"header\" HREF=".$pagina."?sort=reshare>RE</A></th>";
//echo "<th class=\"tabel_headerc\"><A class=\"header\" HREF=".$pagina."?sort=connected>Connected</A></th>";
//echo "<th class=\"tabel_headerc\"><A class=\"header\" HREF=".$pagina."?sort=ver>Ver</A></th>";
//echo "<th class=\"tabel_headerc\">NodeID</th>";
echo "<th class=\"tabel_headerc\"><A class=\"header\" HREF=".$pagina."?sort=shares>Shares</A></th>";
if ($maxhop>1)
for ($k = 1; $k <= $maxhop; $k++) echo "<th class=\"tabel_headerc\"><A class=\"header\" HREF=".$pagina."?sort=hop_".$k.">hop".$k."</A></th>"; 
echo "<th class=\"tabel_headerc\" COLSPAN=2><A class=\"header\" HREF=".$pagina."?sort=ecmok>EcmOK</A>";
echo " - <A class=\"header\" HREF=".$pagina."?sort=ecmokpercent>%</A></th>";
echo "<th class=\"tabel_header\"><A class=\"header\" HREF=".$pagina."?sort=note>Note</A></th>";
echo "<th class=\"tabel_header\" width=100%>CAID/Idents LOCAL</th></tr>";
$sort_c = 1;

if ($total_servers>0)
{
   
   foreach ($servers as $sh_host => $nodes)
   { 
      $client_host = "";
      if (isset($nodes["client"]) && $nodes["client"] == "client")
      {
         $client_host = $sh_host;   
         $sh_host = "!client_".$client_host;
      }
      $key = $sh_host;
      $ordine = 1;
      
      //--------------------------------
      
      $client_sort = ""; 
      if ($client_host != "")
         $client_sort = $client_host;
      else
      if (isset($SERVER_PAIR[$sh_host][0])) 
         $client_sort = $SERVER_PAIR[$sh_host][0]."_".$sh_host;
         
      if ($client_sort == "") $client_sort = "!".$sh_host;
         
      if ($sort == "client") 
      {
      	$key = $client_sort;
      	$ordine = 2;
      }
      
      //--------------------------------
      
      if ($sort == "server") 
      {
      	$key = $sh_host;
      	$ordine = 2;
      }
      
      //--------------------------------
      
      $tara_sort = taraNameSaved($sh_host);
      if ($sort == "country") 
      {
      	$key = adaug0($tara_sort[0]["tara"],20).$sh_host;
      	$ordine = 2;
      }
      
      //--------------------------------
      
      $ping_salvat = SavedPing($sh_host);
      if ($sort == "ping") 
      {
      	$ping_sort = 5000 - $ping_salvat[0];
      	if ($nodes["client"] == "client")
      		$key = "-".$sh_host;
      	else
      		$key = adaug0($ping_sort,20).$sh_host;
      }
      
      
      if ($sort == "pingbest") 
      {
      	$ping_sort = 5000 - $ping_salvat[2];
      	if ($nodes["client"] == "client")
      		$key = "-".$sh_host;
      	else
      		$key = adaug0($ping_sort,20).$sh_host;
      }
      
      //--------------------------------
      
      $reshare_maxim = 0;
      if (isset($total_reshare[$sh_host]["maxim"]) )
         $reshare_maxim = $total_reshare[$sh_host]["maxim"];
      if ($sort == "reshare") $key = adaug0($reshare_maxim,20).$sh_host;
      
      //--------------------------------
      
      $timp_connected = $nodes["Info"][0];
      if ($sort == "connected") $key = adaug0($timp_connected,20).$sh_host;
      
      //--------------------------------
      
      $type_sort = $nodes["Info"][1];
      if ($sort == "type") $key = adaug0($type_sort,20).$sh_host;
      
      //--------------------------------
      
      $ver_connected = $nodes["Info"][2];
      if ($sort == "ver") $key = adaug0($ver_connected,20).$sh_host;
      
      //--------------------------------
      
      $nodeid_connected = $nodes["Info"][3];
      if ($sort == "nodeid") 
      {
      	$key = adaug0($nodeid_connected,20).$sh_host;
      	$ordine = 2;
      }
      
      if ($sort == "note")
      {
	      $notes_saved[0] = "";
	      list($host_DNS, $host_PORT) = explode(":", $sh_host);	
			$notes_save_path   = $notes_path.$host_DNS.".".$host_PORT.".note";	
			if (file_exists($notes_save_path)) 
			{
				$notes_saved = file ($notes_save_path);
		   }
		   $key = $notes_saved[0]."xxx".$sh_host;
		   $ordine = 2;
		}
      
      //--------------------------------
      
      $total_nodes = 0;for($k = 0; $k <= $maxhop; $k++)
      {
         if (isset($total_host_shares[$sh_host][$k]))
            $total_nodes += $total_host_shares[$sh_host][$k];
      }
      if ($sort == "shares") $key = adaug0($total_nodes,20).$sh_host;
      
      //--------------------------------
      
      if (strstr($sort,"hop_")) 	
		{
	 		list($temp, $hop_sort) = explode("_", $sort);
	 		if (isset($hop_sort) && $hop_sort!="")
	 		{
	 			$hop_sort_val = "";
	 			for ($k = $hop_sort; $k <= $maxhop; $k++) 
	 			{
	 				if (isset($total_host_shares[$sh_host][$k]))
	 					$hop_sort_valX = $total_host_shares[$sh_host][$k];
	 				else
	 					$hop_sort_valX = 0;
	 					
	 				$hop_sort_val = $hop_sort_val.adaug0($hop_sort_valX,6);
	 			}
	 			
	 			for ($k = 0; $k < $hop_sort; $k++) 
	 			{
	 				if (isset($total_host_shares[$sh_host][$k]))
	 					$hop_sort_valX = $total_host_shares[$sh_host][$k];
	 				else
	 					$hop_sort_valX = 0;
	 					
	 				$hop_sort_val = $hop_sort_val.adaug0($hop_sort_valX,6);
	 			}
		
	 			
	 			$key = $hop_sort_val.$sh_host;
	 		}
	 	}
      
      $totalECM = "";
      if (isset($nodes["Info"]["ECM_SAVED"]["ECM"]))
         $totalECM = $nodes["Info"]["ECM_SAVED"]["ECM"];
      
      $totalECMOK = "";
      if (isset($nodes["Info"]["ECM_SAVED"]["ECMOK"]))
         $totalECMOK = $nodes["Info"]["ECM_SAVED"]["ECMOK"];
      
      $totalECMNow = "";
      if (isset($nodes["Info"]["ECM_NOW"]["ECM"]))
         $totalECMNow = $nodes["Info"]["ECM_NOW"]["ECM"];
      
      $totalECMOKNow = "";
      if (isset($nodes["Info"]["ECM_NOW"]["ECMOK"]))
         $totalECMOKNow = $nodes["Info"]["ECM_NOW"]["ECMOK"];

      if ($totalECMNow < $totalECM)
      {
         $totalECM = $totalECM + $totalECMNow;
         $totalECMOK = $totalECMOK + $totalECMOKNow;
      }
      else
      if ($totalECMOK == 0 && $totalECM != $totalECMNow)
      {
         $totalECM = $totalECM + $totalECMNow;    
      }

      $procentEcm = 0;
      if ($totalECM > 0)
         $procentEcm = (int)($totalECMOK/$totalECM *100);
         
      if ($sort == "ecmok")
      {
         if ($totalECM > 0)
         {
            if ($totalECMOK > 0)
               $key = adaug0($totalECMOK,10).adaug0($procentEcm,3).$sh_host;
            else
               $key = adaug0($totalECM,20).adaug0($procentEcm,3).$sh_host;
            
         }
         else
            $key = "-".adaug0($procentEcm,3).$sh_host;
      }
      if ($sort == "ecmokpercent") 
      {
         if ($totalECM > 0)
         {
            if ($totalECMOK > 0)
               $key = adaug0($procentEcm,3).adaug0($totalECMOK,10).$sh_host;
            else
               $key = adaug0($procentEcm,3).adaug0($totalECM,10).$sh_host;
         }
         else
            $key = "-".adaug0($totalECMOK,5).$sh_host;
      }

         
      //echo $sh_host."<BR>"; 
         
      //================================
      $servers_sortat[$key] = $sh_host;
      $servers_sortat_value[$key]["client_host"] = $client_host;
      $servers_sortat_value[$key]["total_nodes"] = $total_nodes;
      $servers_sortat_value[$key]["totalECM"]   = $totalECM;
      $servers_sortat_value[$key]["totalECMOK"] = $totalECMOK;
      $servers_sortat_value[$key]["procentEcm"] = $procentEcm;
      
   }
   
   if ($sort!="")
	{
		if ($ordine == 1)
			krsort($servers_sortat);
		else
		if ($ordine == 2)
			ksort($servers_sortat);
	}
   
   echo "<tr>";
	echo "<td class=\"tabel_total\" COLSPAN=5></td>";
	echo "<td class=\"tabel_normal\" COLSPAN=2><A HREF=$pagina?sort=$sort&pingAll=1>Ping ALL</A></td>";
		
	echo "<td class=\"tabel_total\">".$total_shares["total"]."</td>";
	if ($maxhop>1)
		for ($k = 1; $k <= $maxhop; $k++) echo "<td class=\"tabel_total\">".$total_shares[$k]."</td>"; 
	echo "<td class=\"tabel_total\" COLSPAN=4></td>";
	echo "</tr>";
   
   $i=1;
   foreach ($servers_sortat as $key => $sh_host)
   { 
      $nodes = $servers[$sh_host];
      
      $client_host = $servers_sortat_value[$key]["client_host"];
      $total_nodes = $servers_sortat_value[$key]["total_nodes"];
      $totalECM   = $servers_sortat_value[$key]["totalECM"];
      $totalECMOK = $servers_sortat_value[$key]["totalECMOK"];
      $procentEcm = $servers_sortat_value[$key]["procentEcm"];
      
      $total_unic = count($nodes) - 1;
      
      $sh_host_afisat = $sh_host;
      if ($client_host != "") $sh_host_afisat = "no server";
      $nodeid_afisat = $nodes["Info"][3];
      
      list($host_DNS, $host_PORT) = explode(":", $sh_host);
      
      $host_IP = $SERVER_CLIENT[$sh_host]["IP"];
      $tara_DNS = $SERVER_CLIENT[$sh_host]["TARA"];
   
      echo "<tr>";
      
      $uniqueIndex = 0;
      if ($total_nodes >0)
         $uniqueIndex = (int)($total_unic/$total_nodes *100);
      
      
      
      
      //------------- INDEX
      
      echo "<td class=\"Node_count\">".$i."</td>";
      if ($nodes["Info"][1] == "CCcam-s2s")
         echo "<td class=\"tabel_hop_total2\">".$nodes["Info"][1]."</td>";
      else
         echo "<td class=\"tabel_hop_total\">".$nodes["Info"][1]."</td>";
      
      //------------- SERVER
      
      echo "<td class=\"Node_IDr\">";
      
      if ($client_host != "")
      {
         echo "<A HREF=".$pagina."?username=$client_host>".$client_host."</A>"."<BR>";
      }
      else
      if (isset($SERVER_PAIR[$sh_host]))
      foreach ($SERVER_PAIR[$sh_host] as $Client_afisat) 
      {
         $IPdiferit = false;
         $IPClient = trim($clientConectat[$Client_afisat]["Info"][0]) ;
         $tara_host = taraNameSaved($sh_host);
         $IPServer = trim($tara_host[1]); 
         
         /*
         if ($IPServer == "" || $IPClient != $IPServer)
         {
            list($host_DNS, $host_PORT) = explode(":", $sh_host);
            $newIPServer = getHostIP($host_DNS);
            
            if ($newIPServer != $IPServer)
            {
               $IPServer = $newIPServer;
               tara($IPServer,$sh_host);
               
               $globalServers[$server_Host][1] = $IPServer; 
               saveGlobalServers();
            }
         }
         */
         
         
         
         
         
         if ($IPClient != "" && $IPServer != "" && $IPClient != $IPServer)
         {
            $IPdiferit = true;
         }
         
         if ($IPdiferit == true)
         
            echo "<A HREF=".$pagina."?username=$Client_afisat><FONT COLOR=fuchsia>".$Client_afisat."</FONT></A><BR>";
         else
         if (isset($clientConectat[$Client_afisat]))
            echo "<A HREF=".$pagina."?username=$Client_afisat>".$Client_afisat."</A>"."<BR>";
         else
            echo "<FONT COLOR=red>".$Client_afisat."</FONT><BR>";
            
      }
      echo "</td>";  
      
      echo "<td class=\"Node_ID\">";
         
      if ($client_host != "")
         echo "<FONT COLOR=gray>".$sh_host_afisat."</FONT>";
      else
      if ($nodes["Info"][0] == "")
         echo "<A HREF=".$pagina."?nodeDns=$sh_host_afisat><FONT COLOR=red>".$sh_host_afisat."</FONT></A>";
      else
         echo "<A HREF=".$pagina."?nodeDns=$sh_host_afisat>".$sh_host_afisat."</A>";   
      echo "</td>";  
      
         
      if ($country_whois == true)      
         echo "<td class=\"tabel_hop_total2\">".$tara_DNS."</td>";
      
      echo "<td class=\"tabel_hop_total2\"><A HREF=".$pagina."?nodeDns=".$sh_host."&checkPing=1>".pingColor(SavedPing($sh_host))."</A></td>";
      //echo "<td class=\"tabel_hop_total2\"><A HREF=".$pagina."?nodeDns=".$sh_host."&checkPing=1>".pingColorBest(SavedPing($sh_host))."</A></td>";
      
      //------------- RESHARE
      
      $reshare = "<FONT COLOR=red>! NO !</FONT>";
      
      if (!isset($total_reshare[$sh_host]["maxim"]))
      {
         $reshare = "-";
      }
      else
      if ($fullReshare) 
      {
         if ($total_reshare[$sh_host]["maxim"] >0) $reshare = $total_reshare[$sh_host]["maxim"];
      }
      else 
      {
         if ($total_reshare[$sh_host]["maxim"] >0) $reshare = "<FONT COLOR=yellow>YES</FONT>";
      }
           
      if ($nodes["Info"][0] != "")
      {  
         echo "<td class=\"tabel_hop_total\">".$reshare."</td>";
         
         if ($nodes["Info"][2] != "") 
         {
            //echo "<td class=\"tabel_hop_total2\">".$nodes["Info"][2]."</td>";
            //echo "<td class=\"tabel_hop_total2\">".$nodeid_afisat."</td>";
            
            if ($uniqueIndex == 0)
               echo "<td class=\"tabel_total\"><FONT COLOR=red>".$total_nodes."</FONT></td>";
            else
            if ($uniqueIndex != 100)
               echo "<td class=\"tabel_total\">".$total_nodes."<SPAN class=\"tabel_hop_total2\"> (".$uniqueIndex."%)</SPAN></td>";
            else
               echo "<td class=\"tabel_total\">".$total_nodes."</td>";
         }
         else
         {
            //echo "<td class=\"tabel_hop_total\" COLSPAN=\"2\">".$nodes["Info"][1]."</td>";
            echo "<td class=\"tabel_total\">".$total_nodes."</td>";
         }
         
			if ($maxhop>1)
         for ($k = 1; $k <= $maxhop; $k++) 
         {
            if (isset($total_host_shares[$sh_host][$k]))
            {
               if ($k==1)
                  echo "<td class=\"tabel_hop_total\">".$total_host_shares[$sh_host][$k]."</td>";
               else
                  echo "<td class=\"tabel_hop_total1\">".$total_host_shares[$sh_host][$k]."</td>";
            }
            else
               echo "<td class=\"tabel_hop_total\"></td>";
         }
      }
      else
      if ($client_host != "") 
      {
			if ($maxhop>1)
         	echo "<td class=\"tabel_hop_total\" COLSPAN=\"".($maxhop+2)."\"></td>";
         else
         	echo "<td class=\"tabel_hop_total\" COLSPAN=\"".($maxhop+1)."\"></td>";
      }
      else
      {
         $text_offline = "-- OFFLINE --";
			if (isset($OnlineServers[$sh_host]) && $OnlineServers[$sh_host]["time"]!="")
			{
				$last_online = $OnlineServers[$sh_host]["time"];
				$text_offline = get_formatted_timediff($last_online);
			}
			if ($maxhop>1)
				echo "<td class=\"tabel_hop_total\" COLSPAN=\"".($maxhop+2)."\"><FONT COLOR=red>".$text_offline."</FONT></td>";
			else
				echo "<td class=\"tabel_hop_total\" COLSPAN=\"".($maxhop+1)."\"><FONT COLOR=red>".$text_offline."</FONT></td>";
      }
      
      
      //------------- ECM
      
      
      $totalECMNow = "";
      if (isset($nodes["Info"]["ECM_NOW"]["ECM"]))
         $totalECMNow = $nodes["Info"]["ECM_NOW"]["ECM"];
         
      $totalECMSaved = "";
      if (isset($nodes["Info"]["ECM_SAVED"]["ECM"]))
         $totalECMSaved = $nodes["Info"]["ECM_SAVED"]["ECM"];
      
       
      $procentEcmAfisat = procentColor($procentEcm);
         
      if ($totalECMOK == 0 && $totalECM == 0)
      {
         $totalECMOK = "<FONT COLOR=red>-</FONT>";
         $procentEcmAfisat = "-";
      }
      else
      if ($totalECMOK == 0 && $totalECM >0)
      {
         $totalECMOK = "<FONT COLOR=red>".$totalECM."</FONT>";
      }
      else
      if ($totalECMNow < $totalECMSaved )
      	$totalECMOK = "<FONT COLOR=gray>".$totalECMOK."</FONT>";
      
      
      
    	echo "<td class=\"tabel_ecm\">".$totalECMOK."</td>";
      echo "<td class=\"tabel_ecm\">".$procentEcmAfisat."</td>";
      
      
    $notes_saved[0] = "";	
		$notes_save_path   = $notes_path.$host_DNS.".".$host_PORT.".note";	
		if (file_exists($notes_save_path)) 
		{
			$notes_saved = file ($notes_save_path);
	   }
      echo "<td class=\"Node_ID\">".$notes_saved[0]."</td>";
      
      //------------- LOCAL CARDS
      
      echo "<td class=\"Server_Local\">";
      
      if (isset($total_shares[1]) && $total_shares[1] > 0) 
         foreach ($nodes as $node=>$data) 
            if ( !isset($host_hop[$sh_host][$node][0]) && isset($host_hop[$sh_host][$node][1]))
            {  
               $caid = $nodes[$node][0][2];
               $providers = $nodes[$node][0][4];
               echo providerID($caid,$providers)."<BR>";
            }
      
      echo "</td>";
      
      echo "</tr>";
      
      $i++;
   }
}
echo "</table>";
ENDPage();
?>
