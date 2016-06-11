<?php
	
	$provider_cautat = explode(":",$provider);
	
	if (isset($provider_cautat[1])) 
		$provider_hit = adaug0($provider_cautat[0],4).":".adaug0($provider_cautat[1],6);
	else
		$provider_hit = adaug0($provider_cautat[0],4).":";
	
	//echo $provider_hit."<BR>";
	//___________________________________________________________________________________________________
	checkFile($servers_file);
	$servers_data = file ($servers_file);
	
	$lastServer = "";
	foreach ($servers_data as $currentline) 
	{
		$inceput1 = substr($currentline,0,1);
		$inceput2 = substr($currentline,1,2);
		
		if ($inceput1 == "|" && $inceput2 != " H")
		{
			$server = explode("|", $currentline);
			$server_Host 	= trim($server[1]);
			$server_Idents = trim($server[7]);
			
			if ($server_Host != "")
			{
				$lastServer = $server_Host;
			}
						
			
			$hit_array = explode(" ",$server_Idents);
			if ($hit_array[0] != "")
			{
				$hit_caid = $hit_array[1];
				$hit_provider = explode(":",$hit_caid);
				$hit_provider_cautat = explode(":",$provider_hit);
				
				$gasit = false;
				if ($provider_hit == $hit_caid) $gasit = true;
				if ($hit_provider_cautat[1] == "" && $hit_provider_cautat[0] == $hit_provider[0]) $gasit = true;
				
				if ($gasit == true)
				{
					
					$hit_exact = explode("(",$hit_array[2]);
					$hit_exact2 = explode(")",$hit_exact[1]);
					
					$hit_ecm = $hit_exact[0];
					$hit_ecmOK = $hit_exact2[0];
					
					$server_prov = $hit_caid."|".$hit_array[0];
					
					if (!isset($ecm_hit[$server_prov][$lastServer]["Info"]["ECM"])) 			$ecm_hit[$server_prov][$lastServer]["Info"]["ECM"] = 0;
					if (!isset($ecm_hit[$server_prov][$lastServer]["Info"]["ECMOK"])) 		$ecm_hit[$server_prov][$lastServer]["Info"]["ECMOK"] = 0;
					
					$ecm_hit[$server_prov][$lastServer]["Info"]["ECM"] += $hit_ecm;
					$ecm_hit[$server_prov][$lastServer]["Info"]["ECMOK"] += $hit_ecmOK;
					
					
					if (!isset($ecm_hit_total["total"]["Info"]["ECM"])) 			$ecm_hit_total["total"]["Info"]["ECM"] = 0;
					if (!isset($ecm_hit_total["total"]["Info"]["ECMOK"])) 		$ecm_hit_total["total"]["Info"]["ECMOK"] = 0;
					
					$ecm_hit_total["total"]["Info"]["ECM"] 		+= $hit_ecm;
					$ecm_hit_total["total"]["Info"]["ECMOK"] 	+= $hit_ecmOK;
				}
			}
		}
	}
	
	//___________________________________________________________________________________________________	
	

$lastUsername = "";
if (file_exists($clients_file)) 
{  
	$clients_data = file ($clients_file);
	foreach ($clients_data as $currentline)
	{
		$inceput1 = substr($currentline,0,1);
		$inceput2 = substr($currentline,1,2);
		
		
		if (strstr($currentline,"| Shareinfo")) 
			$user_shareinfo = true;

		if ($user_shareinfo == true && $inceput1 == "|" )
		{
			$share_client 		= explode("|", $currentline);
			$share_Username 	= trim($share_client[1]);
			$share_Idents 	  = trim($share_client[2]);
			
			if ($share_Username!="")
				$lastUsername = $share_Username;
				
			$hit_array = explode(" ",$share_Idents);
      if (isset($hit_array[0]) && isset($hit_array[1]) && $hit_array[0] != "" && $hit_array[1] != "")
      {
      	 $existadeja = false;
         foreach ($identsClients[$hit_array[1]]["Clients"] as $clientProvider) 
				 {
				 		if ($clientProvider == $lastUsername)
				 			$existadeja = true;
			   }
			   
			   if ($existadeja == false)
			   {
			   }
			   $hit_exact = explode("(",$hit_array[2]);
	       	$hit_exact2 = explode(")",$hit_exact[1]);
	         
	       	$hit_ecm = $hit_exact[0];
	       	$hit_ecmOK = $hit_exact2[0];
	         
         	if ($existadeja == false)
     		 	{
            	$identsClients[$hit_array[1]]["Clients"][] = $lastUsername; 
            	$identsClientsUsage[$lastUsername][$hit_array[1]]["ecmok"] 	= $hit_ecmOK; 
            	$identsClientsUsage[$lastUsername][$hit_array[1]]["ecm"] 	= $hit_ecm; 
         	}
         	else
         	{
         		$identsClientsUsage[$lastUsername][$hit_array[1]]["ecmok"] 	= $identsClientsUsage[$lastUsername][$hit_array[1]]["ecmok"] + $hit_ecmOK; 
         	 	$identsClientsUsage[$lastUsername][$hit_array[1]]["ecm"] 	= $identsClientsUsage[$lastUsername][$hit_array[1]]["ecm"] + $hit_ecm; 
         	}
        
      	}
			
		} 
	}
}	
	//___________________________________________________________________________________________________
	$total_shares["total"] = 0;
	$maxhop = 0;

	checkFile($shares_file);
	$shares_data = file ($shares_file);
	

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
			
			if ($provider_cautat[0] == $share_Caid)
			{ 
			
				$share_ProvidersList = trim($share[5]);
				if ($share_ProvidersList == "") $share_ProvidersList = $str_empty;
				if ($share_ProvidersList == "0,1,2,3,0") $share_ProvidersList= "0,1,2,3"; // pt premiere
				if ($share_ProvidersList == "") $share_ProvidersList = $str_empty;
				$share_Providers = explode(",", $share_ProvidersList); 
				
				$ok = false;
				if (isset($provider_cautat[1])) 
				{
					foreach($share_Providers as $p)
					{
							if ($p==$provider_cautat[1]) $ok = true;
					}
				}
				else $ok = true;
				
				if ($ok)
				{
					list($share_Hop,$share_Reshare) = explode("   ", trim($share[6]));
					$share_Nodes = explode(",", trim($share[7]));  
					$share_Node = $share_Nodes[count($share_Nodes)-1];
					
					$share_Node = sterg0($share_Node);
					
					if ($share_Node == "")
						$share_NodeUnic = $share_Caid."|".$share_System."|".$share_ProvidersList."|".$share_Host;
					else
						$share_NodeUnic = $share_Caid."|".$share_System."|".$share_ProvidersList."|".$share_Node;
					
					$route = 0;
					if (isset($nodes) && isset($nodes[$share_NodeUnic])) 
						$route = count($nodes[$share_NodeUnic]);
					
					$nodes[$share_NodeUnic]["total"] = $route;
					if ($route == 0) $nodes[$share_NodeUnic]["total"] = 1;
					$nodes[$share_NodeUnic][$route] = array ($share_Host,$share_Type,$share_Caid,$share_System,$share_ProvidersList,$share_Providers,$share_Hop,$share_Reshare,$share_Nodes,$share_Node);  
					
				
					if (!isset($total_shares["total"])) 				$total_shares["total"] = 0;
					if (!isset($total_reshare["total"])) 				$total_reshare["total"] = 0;
					if (!isset($total_shares[$share_Hop]))				$total_shares[$share_Hop] = 0;
					if (!isset($hop[$share_NodeUnic][$share_Hop])) 	$hop[$share_NodeUnic][$share_Hop] = 0;
					if (!isset($re[$share_NodeUnic][$share_Hop]))	$re[$share_NodeUnic][$share_Hop] = 0;
					if (!isset($re[$share_NodeUnic]["reshare"])) 	$re[$share_NodeUnic]["reshare"] = 0;
		
					$hop[$share_NodeUnic][$share_Hop]++;
					$total_shares["total"]++;
					$total_shares[$share_Hop]++;
					
					if (((int)$share_Reshare)>0) 
					{
						$total_reshare["total"]++;
						$re[$share_NodeUnic][$share_Hop] = max($re[$share_NodeUnic][$share_Hop],((int)$share_Reshare));
						$re[$share_NodeUnic]["reshare"] = max($re[$share_NodeUnic]["reshare"],((int)$share_Reshare));
						$re_unic[$share_NodeUnic] = 1;
					}
					
					$maxhop = max($maxhop,$share_Hop);
				}
			}
		} 
	}
	
	if (!isset($nodes) || count($nodes)==0)
	{
		echo "<FONT COLOR=red>Provider $provider not found !</FONT>";
		exit;	
	}
	
	//___________________________________________________________________________________________________
	function providerEDIT($caid,$prov)
	{
		$caid = sterg0($caid);
		$prov = sterg0($prov);
 	
		global $CCcam_providersShort;
		global $str_empty;
		
		$idRecunoscut = "";
		
		$p_seek = $prov;
		if ($p_seek == $str_empty) $p_seek = "0";
		$ident_seek = $caid.":".$p_seek;
		
		if (isset($CCcam_providersShort[$ident_seek]))
		{
			$idRecunoscut = $CCcam_providersShort[$ident_seek];
		}
			
		$id = $idRecunoscut;
		
			
		return $id;
	}
	//___________________________________________________________________________________________________
	
	$total_nodes = 0;
	$total_nodes_re = 0;
	if (isset($nodes)) $total_nodes = count($nodes);
	if (isset($re_unic)) $total_nodes_re = count($re_unic);
	
	$uniqueNodeIndex = 0; if ($total_shares["total"]>0) $uniqueNodeIndex = (int)($total_nodes/$total_shares["total"] *100);
	$uniqueReshareIndex = 0; if ($total_reshare["total"]>0) $uniqueReshareIndex = (int)($total_nodes_re/$total_reshare["total"] *100);
	
	if (isset($provider_cautat[1]))
		format1("Provider",providerID($provider_cautat[0],$provider_cautat[1],true,""));
	else
		format1("Provider",$provider);

	 echo "<BR>";
	
if ( isset($ecm_hit) && count($ecm_hit)>0 )
{
	ksort($ecm_hit);
	
	$totalECM = 0;
	$ecmhit_ECMOK = 0;
	
	if (isset($ecm_hit_total["total"]["Info"]["ECM"]))  	$totalECM = $ecm_hit_total["total"]["Info"]["ECM"];
	if (isset($ecm_hit_total["total"]["Info"]["ECMOK"])) 	$ecmhit_ECMOK = $ecm_hit_total["total"]["Info"]["ECMOK"];
	
	format1("Recent ECM handled",$ecmhit_ECMOK,$totalECM);
	
	echo "<table border=0 cellpadding=2 cellspacing=1>";
	echo "<tr>";
	echo "<th class=\"tabel_headerc\">#</th>";
	echo "<th class=\"tabel_headerc\">Used</th>";
	echo "<th class=\"tabel_headerc\">Ecm</th>";
	echo "<th class=\"tabel_headerc\">Handled</th>";
	echo "<th class=\"tabel_headerc\">%</th>";	
	echo "<td class=\"tabel_header\">Server</th>";
	
	if (!isset($provider_cautat[1])) 
		echo "<td class=\"tabel_header\">Provider</th>";
	echo "<td class=\"tabel_header\">Clients</th>";	
	echo "</tr>";
		
	$lastProvider = "";	
	foreach ($ecm_hit as $provider_ecmhit => $ecmhit)
	{	
		list($provider_ecmhit,$provider_ecmhit_type) = explode("|",$provider_ecmhit);
		
		$totalECM = 0;
		$ecmhit_ECMOK = 0;
		
		if (isset($ecmhit["total"]["Info"]["ECM"]))  	$totalECM = $ecmhit["total"]["Info"]["ECM"];
		if (isset($ecmhit["total"]["Info"]["ECMOK"])) $ecmhit_ECMOK = $ecmhit["total"]["Info"]["ECMOK"];

		
	 	$procentEcm = 0; if ($totalECM > 0) $procentEcm = (int)($ecmhit_ECMOK/$totalECM *100);
	 	$procentEcmAfisat = procentColor($procentEcm);
	 		
	 	if ($totalECM == 0)
	 	{
	 		$totalECM = "<FONT COLOR=red>".$totalECM."</FONT>";
	 		$procentEcmAfisat = "-";
	 	}
	
		if ( isset($ecmhit) && count($ecmhit)>0 )
		{
		
			$i=1;
				
			foreach ($ecmhit as $server => $info) 
			if ($server != "total")
			{
				echo "<tr>";
				echo "<td class=\"Node_count\">".$i."</td>";
				echo "<td class=\"tabel_normal\">".$provider_ecmhit_type."</td>";
	
				
				$totalECM = 0;
				$ecmhit_ECMOK = 0;
				
				if (isset($ecmhit[$server]["Info"]["ECM"]))  		$totalECM = $ecmhit[$server]["Info"]["ECM"];
				if (isset($ecmhit[$server]["Info"]["ECMOK"]))  	$ecmhit_ECMOK = $ecmhit[$server]["Info"]["ECMOK"];
				
			 	$procentEcm = 0; if ($totalECM > 0) $procentEcm = (int)($ecmhit_ECMOK/$totalECM *100);
			 	$procentEcmAfisat = procentColor($procentEcm);
			 		
			 	if ($totalECM == 0)
			 	{
			 		$totalECM = "<FONT COLOR=red>-</FONT>";
			 		$procentEcmAfisat = "-";
			 	}
			 	
				echo "<td class=\"tabel_ecm\"><FONT COLOR=\"gray\">".$totalECM."</FONT></td>";
				echo "<td class=\"tabel_ecm\"><FONT COLOR=\"white\">".$ecmhit_ECMOK."</FONT></td>";
				echo "<td class=\"tabel_ecm\">".$procentEcmAfisat."</td>";
				echo "<td class=\"Node_Provider\"><A HREF=".$pagina."?nodeDns=$server>".$server."</A></td>";
				
				
						
				if ($lastProvider!=$provider_ecmhit)
				{
					$lastProvider = $provider_ecmhit;
					
					$nrprovider = 0;
					foreach ($ecm_hit as $provider_ecmhitcount => $ecmhitcount)
					{
						list($provider_ecmhitcount,$provider_ecmhit_type_count) = explode("|",$provider_ecmhitcount);
						if ( isset($ecmhitcount) && count($ecmhitcount)>0 )
						{
							foreach ($ecmhitcount as $servercount => $infocount) 
							if ($servercount != "total")
							if ($provider_ecmhitcount == $provider_ecmhit) 
							{
								 $nrprovider++;
							}
						}
					}

					if (!isset($provider_cautat[1])) 
					{
						list($hitt_caid,$hitt_providers) = explode(":",$provider_ecmhit);
						echo "<td class=\"Node_clienti\" rowspan=\"$nrprovider\" NOWRAP>".providerID($hitt_caid,$hitt_providers)."</td>";
					}
					
					
					
					unset($clienti_sortat);
					foreach ($identsClients[$provider_ecmhit]["Clients"] as $clientProvider) 
					{
						$key = adaug0($identsClientsUsage[$clientProvider][$provider_ecmhit]["ecm"],15).$clientProvider; 
						$clienti_sortat[$key]["clientProvider"] = $clientProvider;
					}	
					krsort($clienti_sortat);
					$i = 0;
					echo "<td class=\"Node_clienti\" rowspan=\"$nrprovider\">";
					foreach ($clienti_sortat as $key => $valoare) 
					{
						 $clientProvider = $clienti_sortat[$key]["clientProvider"];
						 if ($i>0) echo "&nbsp;,&nbsp; ";
						 echo "<A HREF=".$pagina."?username=$clientProvider>".$clientProvider."</A>";
						 $val = $identsClientsUsage[$clientProvider][$provider_ecmhit];
						 $valecmok = $identsClientsUsage[$clientProvider][$provider_ecmhit]["ecmok"];
			 			 $valecm = $identsClientsUsage[$clientProvider][$provider_ecmhit]["ecm"];
						 if ($valecmok > 0) 
						 	  echo "<FONT color=green>&nbsp;".$valecmok."</FONT>"; 
						 else
						 		echo "<FONT color=red>&nbsp;".$valecm."</FONT>"; 
						 $i++;
				  }
				  echo "</td>";	

				}
				
				
				echo "</tr>";
				$i++;
			}
		}
	}	
	echo "</table>";			
}
	echo "<BR>";	
	format1("Nodes",$total_nodes);
	echo "<table border=0 cellpadding=2 cellspacing=1>";
	echo "<tr>";
	echo "<th class=\"tabel_headerc\">#</th>";
	echo "<th class=\"tabel_header\">Type</th>";
	echo "<th class=\"tabel_header\">Nodes</th>";
	echo "<th class=\"tabel_headerc\">Ping</th>";
	echo "<th class=\"tabel_headerc\">Shares</th>";
	for ($k = 1; $k <= $maxhop; $k++) echo "<td class=\"tabel_headerc\">hop".$k."</th>"; 
	echo "<th class=\"tabel_header\">Reshare</th>";
	echo "<th class=\"tabel_header\">CAID/Idents</th>";
	echo "</tr>";
	
	echo "<tr>";
	echo "<td class=\"Node_Provider\" COLSPAN=4></td>";
	echo "<td class=\"tabel_total\">".$total_shares["total"]."</td>";
	for ($k = 1; $k <= $maxhop; $k++) echo "<td class=\"tabel_hop\">".$total_shares[$k]."</td>";
	echo "<td class=\"tabel_total\">".$total_reshare["total"]."</td>";
	echo "<td class=\"Node_Provider\" ></td>";
			  
	ksort($nodes);
	// CARD LOCAL
	$i=1;
	if (isset($total_shares[0]))
	foreach ($nodes as $node => $data) 
	if (isset($hop[$node][0]))
	{
		$total = $nodes[$node]["total"];
		$nodetype = $nodes[$node][0][1];
		$caid = $nodes[$node][0][2];
		$providers = $nodes[$node][0][4];
		$reshare = 0;
		if (isset($nodes[$node]["reshare"]))
			$reshare = $nodes[$node]["reshare"];
				
		if ($fullReshare) 
		{
			$reshare = "<FONT COLOR=red>! NO !</FONT>";if ($re[$node][0] >0) $reshare = $re[$node][0];
		}
		else 
		{
			$reshare = "<FONT COLOR=red>! NO !</FONT>";if ($re[$node]["reshare"] >0) $reshare = "<FONT COLOR=yellow>YES</FONT>";
		}

		echo "<tr>";
		echo "<td class=\"Node_Count\">".$i."</td>";
		echo "<td class=\"tabel_normal\">".$nodetype."</td>";
		echo "<td class=\"Node_ID_hop0\" COLSPAN=".(3+$maxhop).">".$nodes[$node][0][0]."</th>";
		
		echo "<td class=\"tabel_total\">".$reshare."</td>";
		echo "<td class=\"Node_Provider\">".providerID($caid,$providers)."</td>";
		echo "</tr>";
		$i++;
	}
	
	// HOP1

	if (isset($total_shares[1]))
	foreach ($nodes as $node => $data) 
	if (!isset($hop[$node][0]) && isset($hop[$node][1]))
	{
		$total = $nodes[$node]["total"];
		$nodetype = $nodes[$node][0][1];
		$caid = $nodes[$node][0][2];
		$providers = $nodes[$node][0][4];
		$textNod = $nodes[$node][0][0];	
		$share_host = $nodes[$node][0][0];
		
	
		if ($fullReshare) 
		{
			$reshare = "<FONT COLOR=red>! NO !</FONT>";if ($re[$node][1] >0) $reshare = $re[$node][1];
		}
		else
		{
			$reshare = "<FONT COLOR=red>! NO !</FONT>";if ($re[$node]["reshare"] >0) $reshare = "<FONT COLOR=yellow>YES</FONT>";
		}

		echo "<tr>";
		echo "<td class=\"Node_Count\">".$i."</td>";
		
		if ($nodes[$node][0][1]!="CCcam-s2s")
			echo "<td class=\"Node_ID_hopCS\">".$nodetype."</td>";
		else
			echo "<td class=\"tabel_normal\">".$nodetype."</td>";

		if (strstr($node,"*")) $textNod = $textNod."*";	
		$textNod = linkNod($node,$textNod,"node",false);
		
		echo "<td class=\"Node_ID_hop1\">".$textNod."</td>";
		echo "<td class=\"tabel_hop_total2\"><A HREF=".$pagina."?nodeDns=".$share_host."&checkPing=1>".pingColor(SavedPing($share_host))."</A></td>";

		echo "<td class=\"tabel_total\">".$total."</td>";
			
		if ($nodes[$node][0][1]!="CCcam-s2s")
		{
			if (isset($hop[$node][1]))
			{
				$textnod = $hop[$node][1];
				echo "<td class=\"tabel_hop\">".$textnod."</td>";
				if ($maxhop>1) echo "<td class=\"tabel_hop\" COLSPAN=".($maxhop-1)."></td>";
			}
			else
				echo "<td class=\"tabel_hop\" COLSPAN=".($maxhop)."></td>";
		}
		else
		{
			for ($k = 1; $k <= $maxhop; $k++) 
			{
				$textnod = ""; 
				if (isset($hop[$node][$k])) $textnod = $hop[$node][$k];
				echo "<td class=\"tabel_hop\">".$textnod."</td>";
			}
		}
		
		echo "<td class=\"tabel_total\">".$reshare."</td>";
		echo "<td class=\"Node_Provider\">".providerID($caid,$providers)."</td>";
		echo "</tr>";
		$i++;
	}
	
	// HOP2
	if (isset($total_shares[2]))
	foreach ($nodes as $node => $data) 
	if (!isset($hop[$node][0]) && !isset($hop[$node][1]) && isset($hop[$node][2]))
	{
		$total = $nodes[$node]["total"];
		$nodetype = $nodes[$node][0][1];
		$caid = $nodes[$node][0][2];
		$providers = $nodes[$node][0][4];
		
		$nodSh = explode("|",$node);
		$textNod = $nodSh[3];
		if ($total==1) 
		{	
			$textNod = $nodes[$node][0][0];
			if (strstr($node,"*")) $textNod = $textNod."*";		
		}
		$textNod = linkNod($node,$textNod,"node",false);
		$share_host = $nodes[$node][0][0];
		
		if ($fullReshare) 
		{
			$reshare = "<FONT COLOR=red>! NO !</FONT>";if ($re[$node]["reshare"] >0) if ($total==1) $reshare = $re[$node]["reshare"]; else $reshare = "<FONT COLOR=yellow>".$re[$node]["reshare"]."</FONT>";
		}
		else
		{
			$reshare = "<FONT COLOR=red>! NO !</FONT>";if ($re[$node]["reshare"] >0) $reshare = "<FONT COLOR=yellow>YES</FONT>";
		}
		
		echo "<tr>";
		echo "<td class=\"Node_Count\">".$i."</td>";
		
		if (strstr($node,"*"))
			echo "<td class=\"tabel_normal_type\">".$nodetype."</td>";
		else
			echo "<td class=\"tabel_normal\">".$nodetype."</td>";
			
		echo "<td class=\"Node_ID_hop2\">".$textNod."</td>";
		if ($total==1) 
			echo "<td class=\"tabel_hop_total2\"><A HREF=".$pagina."?nodeDns=".$share_host."&checkPing=1>".pingColor(SavedPing($share_host))."</A></td>";
		else
			echo "<td class=\"tabel_hop_total2\"></td>";
		echo "<td class=\"tabel_total\">".$total."</td>";
		
		for ($k = 1; $k <= $maxhop; $k++) 
		{
			$textnod = ""; 
			if (isset($hop[$node][$k])) $textnod = $hop[$node][$k];
			echo "<td class=\"tabel_hop\">".$textnod."</td>";
		}
				
		echo "<td class=\"tabel_total\">".$reshare."</td>";
		echo "<td class=\"Node_Provider\">".providerID($caid,$providers)."</td>";
		echo "</tr>";
		$i++;
	}
	
	// HOP3
	if (isset($total_shares[3]))
	foreach ($nodes as $node => $data) 
	if (!isset($hop[$node][0]) && !isset($hop[$node][1]) && !isset($hop[$node][2]) && isset($hop[$node][3]))
	{
		$total = $nodes[$node]["total"];
		$nodetype = $nodes[$node][0][1];
		$caid = $nodes[$node][0][2];
		$providers = $nodes[$node][0][4];
		
		$nodSh = explode("|",$node);
		$textNod = $nodSh[3];
		if ($total==1) 
		{	
			$textNod = $nodes[$node][0][0];
			if (strstr($node,"*")) $textNod = $textNod."*";		
		}

		$textNod = linkNod($node,$textNod,"node",false);
		$share_host = $nodes[$node][0][0];
		
		if ($fullReshare) 
		{
			$reshare = "<FONT COLOR=red>! NO !</FONT>";if ($re[$node]["reshare"] >0) if ($total==1) $reshare = $re[$node]["reshare"]; else $reshare = "<FONT COLOR=yellow>".$re[$node]["reshare"]."</FONT>";
		}		
		else		
		{
			$reshare = "<FONT COLOR=red>! NO !</FONT>";if ($re[$node]["reshare"] >0) $reshare = "<FONT COLOR=yellow>YES</FONT>";
		}

		echo "<tr>";
		echo "<td class=\"Node_Count\">".$i."</td>";
		if (strstr($node,"*"))
			echo "<td class=\"tabel_normal_type\">".$nodetype."</td>";
		else
			echo "<td class=\"tabel_normal\">".$nodetype."</td>";
		echo "<td class=\"Node_ID_hop3\">".$textNod."</td>";
		if ($total==1) 
			echo "<td class=\"tabel_hop_total2\"><A HREF=".$pagina."?nodeDns=".$share_host."&checkPing=1>".pingColor(SavedPing($share_host))."</A></td>";
		else
			echo "<td class=\"tabel_hop_total2\"></td>";
		echo "<td class=\"tabel_total\">".$total."</td>";
		
		for ($k = 1; $k <= $maxhop; $k++) 
		{
			$textnod = ""; 
			if (isset($hop[$node][$k])) $textnod = $hop[$node][$k];
			echo "<td class=\"tabel_hop\">".$textnod."</td>";
		}
				
		echo "<td class=\"tabel_total\">".$reshare."</td>";
		echo "<td class=\"Node_Provider\">".providerID($caid,$providers)."</td>";
		echo "</tr>";
		$i++;
	}
	
	
	// HOP4
	if (isset($total_shares[4]))
	foreach ($nodes as $node => $data) 
	if (!isset($hop[$node][0]) && !isset($hop[$node][1]) && !isset($hop[$node][2]) && !isset($hop[$node][3]) && isset($hop[$node][4]))
	{
		$total = $nodes[$node]["total"];
		$nodetype = $nodes[$node][0][1];
		$caid = $nodes[$node][0][2];
		$providers = $nodes[$node][0][4];
		
		$nodSh = explode("|",$node);
		$textNod = $nodSh[3];
		if ($total==1) 
		{	
			$textNod = $nodes[$node][0][0];
			if (strstr($node,"*")) $textNod = $textNod."*";		
		}
		$textNod = linkNod($node,$textNod,"node",false);
		$share_host = $nodes[$node][0][0];
		
		if ($fullReshare) 
		{
			$reshare = "<FONT COLOR=red>! NO !</FONT>";if ($re[$node]["reshare"] >0) if ($total==1) $reshare = $re[$node]["reshare"]; else $reshare = "<FONT COLOR=yellow>".$re[$node]["reshare"]."</FONT>";
		}
		else		
		{
			$reshare = "<FONT COLOR=red>! NO !</FONT>";if ($re[$node]["reshare"] >0) $reshare = "<FONT COLOR=yellow>YES</FONT>";
		}
		
		echo "<tr>";
		echo "<td class=\"Node_Count\">".$i."</td>";
		if (strstr($node,"*"))
			echo "<td class=\"tabel_normal_type\">".$nodetype."</td>";
		else
			echo "<td class=\"tabel_normal\">".$nodetype."</td>";
		echo "<td class=\"Node_ID_hop4\">".$textNod."</td>";
		if ($total==1) 
			echo "<td class=\"tabel_hop_total2\"><A HREF=".$pagina."?nodeDns=".$share_host."&checkPing=1>".pingColor(SavedPing($share_host))."</A></td>";
		else
			echo "<td class=\"tabel_hop_total2\"></td>";
		echo "<td class=\"tabel_total\">".$total."</td>";
		
		for ($k = 1; $k <= $maxhop; $k++) 
		{
			$textnod = ""; 
			if (isset($hop[$node][$k])) $textnod = $hop[$node][$k];
			echo "<td class=\"tabel_hop\">".$textnod."</td>";
		}
		
		echo "<td class=\"tabel_total\">".$reshare."</td>";
		echo "<td class=\"Node_Provider\">".providerID($caid,$providers)."</td>";
		echo "</tr>";
		$i++;
	}
	
	// HOP5 sau mai mult
	foreach ($nodes as $node => $data) 
	if (!isset($hop[$node][0]) && !isset($hop[$node][1]) && !isset($hop[$node][2]) && !isset($hop[$node][3]) && !isset($hop[$node][4]))
	{
		$total = $nodes[$node]["total"];
		$nodetype = $nodes[$node][0][1];
		$caid = $nodes[$node][0][2];
		$providers = $nodes[$node][0][4];
		
		$nodSh = explode("|",$node);
		$textNod = $nodSh[3];
		if ($total==1) 
		{	
			$textNod = $nodes[$node][0][0];
			if (strstr($node,"*")) $textNod = $textNod."*";		
		}
		$textNod = linkNod($node,$textNod,"node",false);
		$share_host = $nodes[$node][0][0];

		if ($fullReshare)  
		{
			$reshare = "<FONT COLOR=red>! NO !</FONT>";if ($re[$node]["reshare"] >0) if ($total==1) $reshare = $re[$node]["reshare"]; else $reshare = "<FONT COLOR=yellow>".$re[$node]["reshare"]."</FONT>";
		}
		else
		{
			$reshare = "<FONT COLOR=red>! NO !</FONT>";if ($re[$node]["reshare"] >0) $reshare = "<FONT COLOR=yellow>YES</FONT>";
		}

		echo "<tr>";
		echo "<td class=\"Node_Count\">".$i."</td>";
		if (strstr($node,"*"))
			echo "<td class=\"tabel_normal_type\">".$nodetype."</td>";
		else
			echo "<td class=\"tabel_normal\">".$nodetype."</td>";
		echo "<td class=\"Node_ID_hop5\">".$textNod."</td>";
		if ($total==1) 
			echo "<td class=\"tabel_hop_total2\"><A HREF=".$pagina."?nodeDns=".$share_host."&checkPing=1>".pingColor(SavedPing($share_host))."</A></td>";
		else
			echo "<td class=\"tabel_hop_total2\"></td>";
		echo "<td class=\"tabel_total\">".$total."</td>";
		
		for ($k = 1; $k <= $maxhop; $k++) 
		{
			$textnod = ""; 
			if (isset($hop[$node][$k])) $textnod = $hop[$node][$k];
			echo "<td class=\"tabel_hop\">".$textnod."</td>";
		}
				
		echo "<td class=\"tabel_total\">".$reshare."</td>";
		echo "<td class=\"Node_Provider\">".providerID($caid,$providers)."</td>";
		echo "</tr>";
		$i++;
	}

	
	echo "</table><BR>";
?>
</BODY></HTML>
