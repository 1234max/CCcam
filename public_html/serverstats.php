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
	
	loadOnlineData();
	

	//___________________________________________________________________________________________________
	
	$lastServer = "";
	foreach ($servers_data as $currentline) 
	{
		$inceput1 = substr($currentline,0,1);
		$inceput2 = substr($currentline,1,2);
		
		if ($inceput1 == "|" && $inceput2 != " H")
		{
			$server = explode("|", $currentline);
			$server_Host 	= trim($server[1]);
			$server_Time 	= trim($server[2]);
			$server_Type 	= trim($server[3]);
			$server_Ver  	= trim($server[4]);
			$server_Nodeid = trim($server[5]);
			$server_Cards  = trim($server[6]);
			$server_Idents = trim($server[7]);
			
			if ($server_Host != "")
			{
				$lastServer = $server_Host;
				$servers[$lastServer]["Info"] = array ($server_Time,$server_Type,$server_Ver,$server_Nodeid,$server_Cards,0);  
			}
			
			$servers[$lastServer]["Info"]["Idents"][] = $server_Idents;  
			
			$hit_array = explode(" ",$server_Idents);
			if ($hit_array[0] != "")
			{
				$hit_provider = explode(":",$hit_array[1]);
				$hit_exact = explode("(",$hit_array[2]);
				$hit_exact2 = explode(")",$hit_exact[1]);
				
				$hit_ecm = $hit_exact[0];
				$hit_ecmOK = $hit_exact2[0];
				
				if (!isset($servers[$lastServer]["Info"]["ECM"])) 					$servers[$lastServer]["Info"]["ECM"] = 0;
				if (!isset($servers[$lastServer]["Info"]["ECMOK"])) 				$servers[$lastServer]["Info"]["ECMOK"] = 0;
				
				$servers[$lastServer]["Info"]["ECM"]+= $hit_ecm;
				$servers[$lastServer]["Info"]["ECMOK"]+= $hit_ecmOK;
			}
		}
	}
	
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
				
				
				if (!isset($total_shares["total"])) 					$total_shares["total"] = 0;
				if (!isset($total_shares[$share_Hop]))					$total_shares[$share_Hop] = 0;
				if (!isset($share_nodes[$share_NodeUnic]))			$share_nodes[$share_NodeUnic] = 0;
				if (!isset($share_nodes_minhop[$share_NodeUnic]))	$share_nodes_minhop[$share_NodeUnic] = 9;
				
				$share_nodes[$share_NodeUnic]++;
				$share_nodes_minhop[$share_NodeUnic] = min( $share_nodes_minhop[$share_NodeUnic], $share_Hop );
				$total_shares["total"]++;
				$total_shares[$share_Hop]++;
				
				if ($share_Node!="" && $share_Hop == 1)
					$Server_Cunoscut[$share_Node] = $share_Host; 
				
				if (!isset($host_hop[$share_Host][$share_NodeUnic][$share_Hop])) 	$host_hop[$share_Host][$share_NodeUnic][$share_Hop] = 0;
				if (!isset($total_host_shares[$share_Host]["total"])) 				$total_host_shares[$share_Host]["total"] = 0;
				if (!isset($total_host_shares[$share_Host][$share_Hop]))				$total_host_shares[$share_Host][$share_Hop] = 0;		
				if (!isset($total_reshare[$share_Host]["total"])) 						$total_reshare[$share_Host]["total"] = 0;		
				if (!isset($total_reshare[$share_Host]["maxim"])) 						$total_reshare[$share_Host]["maxim"] = 0;		
				if (!isset($re[$share_Host][$share_NodeUnic][$share_Hop]))			$re[$share_Host][$share_NodeUnic][$share_Hop] = 0;
				if (!isset($re[$share_Host][$share_NodeUnic]["reshare"]))			$re[$share_Host][$share_NodeUnic]["reshare"] = 0;

				
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

format1("Servers connected",$total_connected_servers,$total_servers);

echo "<BR>";
echo "<table width=100% border=0 cellpadding=2 cellspacing=1";
echo "<tr>";
echo "<th class=\"tabel_headerc\">#</th>";
if ($maxhop>1)
echo "<th class=\"tabel_headerc\"><A class=\"header\" HREF=".$pagina."?sort=rating>Rating</A></th>";
echo "<th class=\"tabel_headerc\"><A class=\"header\" HREF=".$pagina."?sort=online>Uptime</A></th>";
echo "<th class=\"tabel_header\"><A class=\"header\" HREF=".$pagina."?sort=server>Server</A></th>";
if ($country_whois == true) 
	echo "<th class=\"tabel_headerc\"><A class=\"header\" HREF=".$pagina."?sort=country>Cnt</A></th>";
echo "<th class=\"tabel_headerc\"><A class=\"header\" HREF=".$pagina."?sort=ping>Ping</A></th>";
//echo "<th class=\"tabel_headerc\"><A class=\"header\" HREF=".$pagina."?sort=pingbest>Best</A></th>";
echo "<th class=\"tabel_headerc\"><A class=\"header\" HREF=".$pagina."?sort=reshare>RE</A></th>";
echo "<th class=\"tabel_headerc\"><A class=\"header\" HREF=".$pagina."?sort=connected>Connected</A></th>";
echo "<th class=\"tabel_headerc\"><A class=\"header\" HREF=".$pagina."?sort=ver>Ver</A></th>";
echo "<th class=\"tabel_headerc\"><A class=\"header\" HREF=".$pagina."?sort=nodeid>NodeID</A></th>";
echo "<th class=\"tabel_headerc\"><A class=\"header\" HREF=".$pagina."?sort=shares>Shares</A></th>";
if ($maxhop>1)
for ($k = 1; $k <= $maxhop; $k++) echo "<th class=\"tabel_headerc\"><A class=\"header\" HREF=".$pagina."?sort=hop_".$k.">hop".$k."</A></th>"; 
echo "<th class=\"tabel_headerc\" COLSPAN=2><A class=\"header\" HREF=".$pagina."?sort=ecmok>EcmOK</A>";
echo " - <A class=\"header\" HREF=".$pagina."?sort=ecmokpercent>%</A></th>";
echo "<th class=\"tabel_header\" width=100%>CAID/Idents LOCAL</th></tr>";

echo "<tr>";
	echo "<td class=\"tabel_total\" COLSPAN=5></td>";
	if ($maxhop>1)
		echo "<td class=\"tabel_normal\" COLSPAN=5><A HREF=$pagina?sort=$sort&pingAll=1>Ping ALL</A></td>";
	else
		echo "<td class=\"tabel_normal\" COLSPAN=4><A HREF=$pagina?sort=$sort&pingAll=1>Ping ALL</A></td>";
	echo "<td class=\"tabel_total\">".$total_shares["total"]."</td>";
	if ($maxhop>1)
	for ($k = 1; $k <= $maxhop; $k++) echo "<td class=\"tabel_total\">".$total_shares[$k]."</td>"; 
	echo "<td class=\"tabel_total\" COLSPAN=3></td>";
	echo "</tr>";

if ($total_servers>0)
{
	
	foreach ($servers as $sh_host => $nodes)
	{ 
		$key = $sh_host;
		$ordine = 1;

		//--------------------------------
		$indexServer = 0;
		
		$total_unic = count($nodes) - 1;
		$total_nodes = 0;for($k = 0; $k <= $maxhop; $k++)
		{
			if (isset($total_host_shares[$sh_host][$k]))
				$total_nodes += $total_host_shares[$sh_host][$k];
		}
		
		$uniqueIndex = 0;
	 	if ($total_nodes >0)
	 		$uniqueIndex = (int)($total_unic/$total_nodes *100);
	 	
 	
 		if ($total_nodes==0)
 			$indexServer = 0;
 		else
	 	if (!isset($total_host_shares[$sh_host][2]) && !isset($total_host_shares[$sh_host][3]))
	 		$indexServer = $total_host_shares[$sh_host][1];
	 	else
	 	{
	 		if (isset($total_host_shares[$sh_host][1])) $indexServer = $indexServer + 50*$total_host_shares[$sh_host][1];
	 		if (isset($total_host_shares[$sh_host][2])) $indexServer = $indexServer + 10*$total_host_shares[$sh_host][2];
	 		if (isset($total_host_shares[$sh_host][3])) $indexServer = $indexServer + 1* (int)($total_host_shares[$sh_host][3] * $uniqueIndex/100);
		}

	 	
		if ($sort == "rating") 
		{
			$key = adaug0($indexServer,20).$sh_host;
			$ordine = 1;
			//echo $key."<BR>";
		}
		if ($sort == "online") 
		{
			$procent = procentOnline($sh_host);
			if ($procent == round($procent))
				$procent = $procent.".0";
			
			$key = adaug0($procent,20).adaug0($nodes["Info"][0],20).$sh_host;
		}
		//if ($sort == "usage") $key = adaug0($nodes["Info"][5],20).$sh_host;
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
	 	if ($sort == "connected") 
	 	{
	 		if (isset($OnlineServers[$sh_host]) && $OnlineServers[$sh_host]["time"]!="")
			{
				$last_online = $OnlineServers[$sh_host]["time"];
			}
			
			if ($timp_connected!="")
	 			$key = adaug0($timp_connected,20).$sh_host;
	 		else
	 			$key = adaug0($last_online,20).$sh_host;
	 	}
	 	
	 	//--------------------------------
	 	
	 	$ver_connected = $nodes["Info"][2];
	 	if ($sort == "ver") 
	 	{
	 		if ($ver_connected!="" && $timp_connected!="")
	 			$key = adaug0($ver_connected,20).$sh_host;
	 		else
	 		if ($timp_connected!="")
	 			$key = "-".$sh_host;
	 		else
	 		{
	 			$last_online = "";
	 			if (isset($OnlineServers[$sh_host]) && $OnlineServers[$sh_host]["time"]!="")
				{
					$last_online = $OnlineServers[$sh_host]["time"];
				}
			
	 			$key = "--".$last_online.$sh_host;
	 		}
	 			
	 		
	 	}
	 	
	 	//--------------------------------
	 	
	 	$nodeid_connected = $nodes["Info"][3];
	 	if ($sort == "nodeid") 
	 	{
	 		if ($ver_connected!="" && $timp_connected!="")
	 			$key = adaug0($nodeid_connected,20).$sh_host;
	 		else
	 		if ($timp_connected!="")
	 			$key = "-".$sh_host;
	 		else
	 		{
	 			$key = $sh_host;
	 		}
	 			
	 		$ordine = 2;
	 		//echo $key."<BR>";
	 	}
	 	
	 	//--------------------------------
	 	
		if ($sort == "shares") $key = adaug0($total_nodes,20).$sh_host;
		
		//--------------------------------
	 	/*
	 	if (strstr($sort,"hop_")) 	
	 	{
	 		list($temp, $hop_sort) = explode("_", $sort);
	 		if (isset($hop_sort) && $hop_sort!="")
	 		{
	 			if (isset($total_host_shares[$sh_host][$hop_sort]))
	 				$hop_sort_val = $total_host_shares[$sh_host][$hop_sort];
	 			else
	 				$hop_sort_val = -1;
	 			
	 			$key = adaug0($hop_sort_val,20).$sh_host;
	 		}
	 	}
	 	*/
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
	 	
	 	//--------------------------------
	 	
	 	$totalECM = 0;
	 	if (isset($nodes["Info"]["ECM"]) && $nodes["Info"]["ECM"] > 0)
	 		$totalECM = $nodes["Info"]["ECM"];
	 	
	 	$totalECMOK = 0;
	 	if (isset($nodes["Info"]["ECMOK"]) && $nodes["Info"]["ECMOK"] > 0)
	 		$totalECMOK = $nodes["Info"]["ECMOK"];
	 		
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

	 		
			
			
		//================================
		$servers_sortat[$key] = $sh_host;
		$servers_sortat_value[$key]["indexServer"] 	= $indexServer;
		$servers_sortat_value[$key]["total_nodes"] 	= $total_nodes;
		$servers_sortat_value[$key]["totalECM"] 		= $totalECM;
		$servers_sortat_value[$key]["totalECMOK"] 	= $totalECMOK;
		$servers_sortat_value[$key]["procentEcm"]		= $procentEcm;
		
		//echo $key."<BR>";
		
	}
	
	if ($sort!="")
	{
		if ($ordine == 1)
			krsort($servers_sortat);
		else
		if ($ordine == 2)
			ksort($servers_sortat);
	}
		

	$i=1;
	foreach ($servers_sortat as $key => $sh_host)
	{ 
		$nodes = $servers[$sh_host];
		
		$indexServer = $servers_sortat_value[$key]["indexServer"];
		$total_nodes = $servers_sortat_value[$key]["total_nodes"];
		$totalECM 	= $servers_sortat_value[$key]["totalECM"];
		$totalECMOK = $servers_sortat_value[$key]["totalECMOK"];
		$procentEcm = $servers_sortat_value[$key]["procentEcm"];
		
		$total_unic = count($nodes) - 1;
		
		$sh_host_afisat = $sh_host;
		$nodeid_afisat = $nodes["Info"][3];
		
		list($host_DNS, $host_PORT) = explode(":", $sh_host);
			
	 	echo "<tr>";
	 	
	 	$uniqueIndex = 0;
	 	if ($total_nodes >0)
	 		$uniqueIndex = (int)($total_unic/$total_nodes *100);
	 	
	 	
	 	//$indexServer = $nodes["Info"][5];
	 	
	 	if ($indexServer <99) $qualityServer = "<FONT COLOR=red>".$indexServer."</FONT>";
	 	else
	 	if ($indexServer <249) $qualityServer = "<FONT COLOR=gray>".$indexServer."</FONT>";
	 	else
	 	if ($indexServer <499) $qualityServer = "<FONT COLOR=white>".$indexServer."</FONT>";
	 	else
	 	if ($indexServer <999) $qualityServer = "<FONT COLOR=yellow>".$indexServer."</FONT>";
	 	else
	 		$qualityServer = $indexServer;
	 		
	 	if ($nodes["Info"][1] != "CCcam-s2s") $qualityServer = "-"; 
	 	else
	 	if (!isset($total_host_shares[$sh_host][2]) && !isset($total_host_shares[$sh_host][3])) $qualityServer = "-"; 
	 	
	 	//------------- INDEX , RATING
	 	
	 	echo "<td class=\"Node_count\">".$i."</td>";
	 	if ($maxhop>1)
		echo "<td class=\"tabel_hop_total\">".$qualityServer."</td>";
		
		$procent = procentOnline($sh_host);
		if ($procent == round($procent) && $procent!="100")
			$procent = $procent.".0";
				
		if ($procent=="100")
			echo "<td class=\"tabel_hop_total2\"><B>".procentColor($procent)."</B></td>";
		else
			echo "<td class=\"tabel_hop_total2\">".procentColor($procent)."</td>";
		
		
		//------------- SERVER
		
		$tara_DNS = taraNameSaved($sh_host);
		
		if ($nodes["Info"][0] == "")
			echo "<td class=\"Node_ID\"><A HREF=".$pagina."?nodeDns=$sh_host_afisat><FONT COLOR=red>".$sh_host_afisat."</FONT></A></td>";
		else
			echo "<td class=\"Node_ID\"><A HREF=".$pagina."?nodeDns=$sh_host_afisat>".$sh_host_afisat."</A></td>";	
			
		if ($country_whois == true) 
			echo "<td class=\"tabel_hop_total2\">".$tara_DNS[0]["tara"]."</td>";
		
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
			echo "<td class=\"tabel_hop_total2\">".$nodes["Info"][0]."</td>";
			
			if ($nodes["Info"][2] != "") 
			{
				echo "<td class=\"tabel_hop_total2\">".$nodes["Info"][2]."</td>";
				echo "<td class=\"tabel_hop_total2\">".$nodeid_afisat."</td>";
				
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
				echo "<td class=\"tabel_hop_total\" COLSPAN=\"2\">".$nodes["Info"][1]."</td>";
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
		{
			$text_offline = "-- OFFLINE --";
			if (isset($OnlineServers[$sh_host]) && $OnlineServers[$sh_host]["time"]!="")
			{
				$last_online = $OnlineServers[$sh_host]["time"];
				$text_offline = "Last seen online : ". get_formatted_timediff($last_online). " ago";
			}
			if ($maxhop>1)
				echo "<td class=\"tabel_hop_total\" COLSPAN=\"".($maxhop+5)."\"><FONT COLOR=red>".$text_offline."</FONT></td>";
			else
				echo "<td class=\"tabel_hop_total\" COLSPAN=\"".($maxhop+4)."\"><FONT COLOR=red>".$text_offline."</FONT></td>";
		}
		
		
		//------------- ECM
	 		
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
	 	
		echo "<td class=\"tabel_ecm\">".$totalECMOK."</td>";
		echo "<td class=\"tabel_ecm\">".$procentEcmAfisat."</td>";
		
		
		//------------- LOCAL CARDS
		
		echo "<td class=\"Server_Local\">";
		
		if ( isset($total_shares[0]) && $total_shares[0] > 0) 
			foreach ($nodes as $node=>$data) 
				if (isset($host_hop[$sh_host][$node][0]))
				{	
					$caid = $nodes[$node][0][2];
					$providers = $nodes[$node][0][4];
					echo providerID($caid,$providers)."<BR>";
				}
	
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
