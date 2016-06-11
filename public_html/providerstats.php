<?php include "common.php"?>

<?php
	
	if (!$update_from_button)
	{
		$update_servers = true;
		$update_shares = true;
	}

	include "meniu.php";
	
	
	$providerslist = "";  if(isset($_GET['providerslist'])) $providerslist = $_GET['providerslist'];
	
	
	checkFile($shares_file);
	$shares_data = file ($shares_file);
	
	checkFile($servers_file);
	$servers_data = file ($servers_file);
	
	//___________________________________________________________________________________________________
	$maxhop = 0;


	foreach ($servers_data as $currentline) 
	{
		$inceput1 = substr($currentline,0,1);
		$inceput2 = substr($currentline,1,2);
		
		if ($inceput1 == "|" && $inceput2 != " H")
		{
			$server = explode("|", $currentline);
			$server_Idents = trim($server[7]);
			
			$hit_array = explode(" ",$server_Idents);
			if ($hit_array[0] != "")
			{
				$hit_caid = $hit_array[1];
				$hit_provider = explode(":",$hit_caid);
				$hit_exact = explode("(",$hit_array[2]);
				$hit_exact2 = explode(")",$hit_exact[1]);
				
				$hit_ecm = $hit_exact[0];
				$hit_ecmOK = $hit_exact2[0];
				
				if (!isset($ecmhit[$hit_caid]["Info"]["ECM"])) 			$ecmhit[$hit_caid]["Info"]["ECM"] = 0;
				if (!isset($ecmhit[$hit_caid]["Info"]["ECMOK"])) 		$ecmhit[$hit_caid]["Info"]["ECMOK"] = 0;
				
				$ecmhit[$hit_caid]["Info"]["ECM"] += $hit_ecm;
				$ecmhit[$hit_caid]["Info"]["ECMOK"] += $hit_ecmOK;
				
				
				if (!isset($ecmhit["total"]["Info"]["ECM"])) 			$ecmhit["total"]["Info"]["ECM"] = 0;
				if (!isset($ecmhit["total"]["Info"]["ECMOK"])) 			$ecmhit["total"]["Info"]["ECMOK"] = 0;
				
				$ecmhit["total"]["Info"]["ECM"] 		+= $hit_ecm;
				$ecmhit["total"]["Info"]["ECMOK"] 	+= $hit_ecmOK;

			}
		}
	}
	
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
			if ($share_ProvidersList == "") $share_ProvidersList = $str_empty;
			if ($share_ProvidersList == "0,1,2,3,0") $share_ProvidersList= "0,1,2,3"; // pt premiere
			
			$share_Providers = explode(",", $share_ProvidersList); 
			
			list($share_Hop,$share_Reshare) = explode("   ", trim($share[6]));
			$share_Nodes = explode(",", trim($share[7])); 
			$share_Node = $share_Nodes[count($share_Nodes)-1];
			
			$share_Node = sterg0($share_Node);
			
			if ($share_Node == "")
				$share_NodeUnic = $share_Host;
			else
				$share_NodeUnic = $share_Node."|".$share_System.$share_ProvidersList;
				
			if (isset($Unique_nodes[$share_NodeUnic]))
				$Unique_nodes[$share_NodeUnic]++;
			else
				$Unique_nodes[$share_NodeUnic] = 1;
		
			foreach ($share_Providers as $share_Provider)
			{
				if ($share_Provider == "") $share_Provider = $str_empty;
				if (isset($share_caident[$share_Caid][$share_Provider][$share_Hop]))
					$share_caident[$share_Caid][$share_Provider][$share_Hop]++;
				else
					$share_caident[$share_Caid][$share_Provider][$share_Hop] = 1;
				
				if (isset($share_caident_unic[$share_Caid][$share_Provider][$share_NodeUnic]))
					$share_caident_unic[$share_Caid][$share_Provider][$share_NodeUnic]++;
				else
					$share_caident_unic[$share_Caid][$share_Provider][$share_NodeUnic] = 1;
					
				if (!isset($share_caident_reshare[$share_Caid][$share_Provider]["reshare"]))				
					$share_caident_reshare[$share_Caid][$share_Provider]["reshare"] = 0;
				if (((int)$share_Reshare)>0) 
					$share_caident_reshare[$share_Caid][$share_Provider]["reshare"] = max($share_caident_reshare[$share_Caid][$share_Provider]["reshare"],((int)$share_Reshare));

			}
			
			$maxhop = max($maxhop,$share_Hop);
			
			if (!isset($total_shares["total"])) 				$total_shares["total"] = 0;
			if (!isset($total_reshare["total"])) 				$total_reshare["total"] = 0;
			if (!isset($total_shares[$share_Hop]))				$total_shares[$share_Hop] = 0;
			
			$total_shares["total"]++;
			$total_shares[$share_Hop]++;
			if (((int)$share_Reshare)>0) $total_reshare["total"]++;


		} 
	}
	if (!isset($share_caident))
	{
		echo "<FONT COLOR=red>Server is down or updating ... Please try again later !</FONT>";
		exit;	
	}
	
	//___________________________________________________________________________________________________	
	

$lastUsername = "";
if (file_exists($clients_file)) 
{  
	$clients_data = file ($clients_file);
	$user_shareinfo = false;
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
      	 	if (isset($identsClients[$hit_array[1]]))
         	foreach ($identsClients[$hit_array[1]]["Clients"] as $clientProvider) 
				{
					if ($clientProvider == $lastUsername)
				 		$existadeja = true;
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
	
	$total_Nodes = count($Unique_nodes);
	
	$total_shares_unic = 0;
	$total_providers = 0;
	foreach ($share_caident_unic as $caid => $prov) 
	{
		foreach ($prov as $p => $nodes) 
		{
			$total_providers++;
			$total_shares_unic += count($share_caident_unic[$caid][$p]);
		}
	}
	
	$totalECM = 0;
	$ecmhit_ECMOK = 0;
	if (isset($ecmhit["total"]["Info"]["ECM"]))  	$totalECM = $ecmhit["total"]["Info"]["ECM"];
	if (isset($ecmhit["total"]["Info"]["ECMOK"]))  	$ecmhit_ECMOK = $ecmhit["total"]["Info"]["ECMOK"];
	
	
	format1("Providers",$total_providers);
	format1("Recent ECM handled",$ecmhit_ECMOK,$totalECM);
if ($providerslist =="")
{			
	echo "<BR>";
	echo "<table width=100% border=0 cellpadding=2 cellspacing=1>";
	echo "<tr>";
	echo "<td class=\"tabel_headerc\">#</th>";
	echo "<td class=\"tabel_header\"><A class=\"header\" HREF=".$pagina."?sort=caid>CAID</A></th>";
	echo "<td class=\"tabel_header\"><A class=\"header\" HREF=".$pagina."?sort=caid>Ident</A></th>";
	echo "<td class=\"tabel_header\"><A class=\"header\" HREF=".$pagina."?sort=nodes>Nodes</A></th>";
	if ($maxhop>1)
	echo "<td class=\"tabel_headerc\"><A class=\"header\" HREF=".$pagina."?sort=shares>Shares</A></th>";
	if ( isset($total_shares[0]) && $total_shares[0] > 0) echo "<td class=\"tabel_header\">Local</th>";
	if ($maxhop>1)
	for ($k = 1; $k <= $maxhop; $k++) echo "<td class=\"tabel_headerc\"><A class=\"header\" HREF=".$pagina."?sort=hop_".$k.">hop".$k."</A></th>";
	echo "<th class=\"tabel_header\">Reshare</th>";
	echo "<th class=\"tabel_headerc\"><A class=\"header\" HREF=".$pagina."?sort=ecm>Ecm</A></th>";
	echo "<th class=\"tabel_headerc\"><A class=\"header\" HREF=".$pagina."?sort=ecmok>EcmOK</A></th>";	
	echo "<td class=\"tabel_header\"><A class=\"header\" HREF=".$pagina."?sort=caid>CAID : Provider</A></th>";
	echo "<td class=\"tabel_header\" width=\"100%\">Clients</th>";	
	echo "</tr>";
	
	echo "<tr>";
	echo "<td class=\"tabel_total\"></td>";
	echo "<td class=\"tabel_total\"></td>";
	echo "<td class=\"tabel_total\"></td>";
	echo "<td class=\"tabel_total\"><FONT COLOR=red>".$total_Nodes."</FONT></td>";
	if ($maxhop>1)
	echo "<td class=\"tabel_total\">".$total_shares["total"]."</td>";
	if ( isset($total_shares[0]) && $total_shares[0] > 0) echo "<td class=\"tabel_total\">".$total_shares[0]."</th>";
	if ($maxhop>1)
	for ($k = 1; $k <= $maxhop; $k++) echo "<td class=\"tabel_total\">".$total_shares[$k]."</td>"; 
	echo "<td class=\"tabel_total\">".$total_reshare["total"]."</td>";
	echo "<td class=\"tabel_total\">".$totalECM."</td>";
	echo "<td class=\"tabel_total\">".$ecmhit_ECMOK."</td>";
	echo "<td class=\"tabel_header\"><A class=\"header\" HREF=".$pagina."?providerslist=1>Used Providers List</A></td>";
	echo "<td class=\"tabel_total\"></td>";
	echo "</tr>";
}	
	foreach ($share_caident as $caid => $prov) 
	{
		foreach ($prov as $p => $hops) 
		{
			$ident = $caid.":".$p;
			$total=0;foreach ($hops as $h) $total+=$h;
			$total_unic = count($share_caident_unic[$caid][$p]);
			$total_sort = $total_shares["total"] - $total;
			$total_unic_sort = $total_Nodes - $total_unic;
			
			$caidECM = $caid;
			$pECM    = $p;
			
			$caidECM = adaug0($caidECM,4);
			if ($pECM == "") $pECM = "0";
			$pECM 	= adaug0($pECM,6);
			 
			$identECM = $caidECM.":".$pECM;
			
			$p_total_ECM = 0;
			$p_ecmhit_ECMOK = 0;
			
			if (isset($ecmhit[$identECM]["Info"]["ECM"]))  		$p_total_ECM = $ecmhit[$identECM]["Info"]["ECM"];
			if (isset($ecmhit[$identECM]["Info"]["ECMOK"]))  	$p_ecmhit_ECMOK = $ecmhit[$identECM]["Info"]["ECMOK"];
			
		 	$p_procentECM = 0; if ($p_total_ECM > 0) $p_procentECM = (int)($p_ecmhit_ECMOK/$p_total_ECM *100);
		 	
		 	$totalECM_sort = $ecmhit["total"]["Info"]["ECM"] - $p_total_ECM;
		 	$procentECM_sort = 100 - $p_procentECM;
			
			if ($sort == "nodes")  
				$key = adaug0($total_unic_sort,5).adaug0($caid,4).":".adaug0($p,6);
			else
			if ($sort == "shares")  
				$key = adaug0($total_sort,5).adaug0($caid,4).":".adaug0($p,6);
			else
			if (strstr($sort,"hop_")) 	
		 	{
		 		list($temp, $hop_sort) = explode("_", $sort);
		 		if (isset($hop_sort) && $hop_sort!="")
		 		{
		 			$hop_sort_val = "";
		 			for ($k = $hop_sort; $k <= $maxhop; $k++) 
		 			{
		 				if (isset($hops[$k]))
		 					$hop_sort_valX = $total_shares[$k] - $hops[$k];
		 				else
		 					$hop_sort_valX = $total_shares[$k];
		 					
		 				$hop_sort_val = $hop_sort_val."_".adaug0($hop_sort_valX,8);
		 			}
		 			for ($k = 0; $k < $hop_sort; $k++) 
		 			{
		 				if (isset($hops[$k]))
		 					$hop_sort_valX = $total_shares[$k] - $hops[$k];
		 				else
		 					$hop_sort_valX = $total_shares[$k];
		 					
		 				$hop_sort_val = $hop_sort_val."_".adaug0($hop_sort_valX,8);
		 			}
		 				
		 			
		 			$key = adaug0($hop_sort_val,10)."_".adaug0($caid,4).":".adaug0($p,6);
		 		}
		 	}
			else
			if ($sort == "ecm")  
				$key = adaug0($totalECM_sort,5)."--".adaug0($procentECM_sort,5).adaug0($caid,4).":".adaug0($p,6);
			else
			if ($sort == "ecmok")  
				$key = adaug0($procentECM_sort,5).adaug0($totalECM_sort,5).adaug0($caid,4).":".adaug0($p,6);
			else
				$key = adaug0($caid,4).":".adaug0($p,6);
			
			$caident_sortat[$key] = $ident;
		}
	}
	ksort($caident_sortat);
			  
	$i=1;
	
	foreach ($caident_sortat as $key => $ident) 
	{
		list($caid,$p) = explode(":",$ident);

		
		$prov = $share_caident[$caid];
		$hops = $prov[$p];

		$nume = "";
		$p_seek = $p;
		if ($p_seek == $str_empty) $p_seek = "0";
		
		$ident_seek = $caid.":".$p_seek;
		if (isset($CCcam_providersShort[$ident_seek]))
			$nume = $CCcam_providersShort[$ident_seek]."<font color=white>[".$p."]</font>";

			
		$total=0;foreach ($hops as $h) $total+=$h;
		$total_unic = count($share_caident_unic[$caid][$p]);
		$reshare = "<FONT COLOR=red>! NO !</FONT>";
		if ($share_caident_reshare[$caid][$p]["reshare"] >0) 
			$reshare = "<FONT COLOR=yellow>YES</FONT>";
		
		$total_provider_gasit[$caid][$p] = $total_unic; 
		
if ($providerslist =="")
{		
		echo "<tr>";
		echo "<td class=\"Node_count\">".$i."</td>";
		
		echo "<td class=\"Node_IDr\"><A HREF=".$pagina."?provider=$caid>".$caid."</A></td>";
		echo "<td class=\"Node_ID\"><A HREF=".$pagina."?provider=$ident>".$p."</A></td>";
		
		echo "<td class=\"tabel_hop\"><A HREF=".$pagina."?provider=$ident><SPAN class=\"Node_Unic\">".$total_unic."</SPAN></A></td>";
		

				
		if ($maxhop>1)
		echo "<td class=\"tabel_total\">".$total."</td>";


		if (isset($total_shares[0])) 
			if (isset($hops[0]))
				echo "<td class=\"tabel_hop\">".$hops[0]."</td>";
			else
				echo "<td class=\"tabel_hop\"></td>";
				
		if ($maxhop>1)
		for ($k = 1; $k <= $maxhop; $k++)
			if (isset($hops[$k])) 
				echo "<td class=\"tabel_hop\">".$hops[$k]."</td>";
			else	
				echo "<td class=\"tabel_hop\"></td>";
		
		echo "<td class=\"tabel_total\">".$reshare."</td>";
		
		//------------- ECM
		
		$caidECM = $caid;
		$pECM    = $p;
		
		$caidECM = adaug0($caidECM,4);
		if ($pECM == "") $pECM = "0";
		$pECM 	= adaug0($pECM,6);
		 
		$identECM = $caidECM.":".$pECM;
		
		$totalECM = 0;
		$ecmhit_ECMOK = 0;
		
		if (isset($ecmhit[$identECM]["Info"]["ECM"]))  		$totalECM = $ecmhit[$identECM]["Info"]["ECM"];
		if (isset($ecmhit[$identECM]["Info"]["ECMOK"]))  	$ecmhit_ECMOK = $ecmhit[$identECM]["Info"]["ECMOK"];
		
	 	$procentEcm = 0; if ($totalECM > 0) $procentEcm = (int)($ecmhit_ECMOK/$totalECM *100);
	 	$procentEcmAfisat = procentColor($procentEcm);
	 		
	 	if ($totalECM == 0)
	 	{
	 		$totalECM = "<FONT COLOR=red>-</FONT>";
	 		$procentEcmAfisat = "-";
	 	}
	 	
		echo "<td class=\"tabel_ecm\">".$totalECM."</td>";
		echo "<td class=\"tabel_ecm\">".$procentEcmAfisat."</td>";

		echo "<td class=\"Node_Provider\" NOWRAP>".providerID($caid,$p,true)."</td>";
		
		unset($clienti_sortat);
		
		if (isset($identsClients[$identECM]["Clients"]))
		{
			foreach ($identsClients[$identECM]["Clients"] as $clientProvider) 
			{
				$key = adaug0($identsClientsUsage[$clientProvider][$identECM]["ecm"],15).$clientProvider; 
				$clienti_sortat[$key]["clientProvider"] = $clientProvider;
			}	
			krsort($clienti_sortat);
	
			$j = 0;
			echo "<td class=\"Node_clienti\">";
			foreach ($clienti_sortat as $key => $valoare) 
			{
				 $clientProvider = $clienti_sortat[$key]["clientProvider"];
				 if ($j>0) echo "&nbsp;,&nbsp; ";
				 echo "<A HREF=".$pagina."?username=$clientProvider>".$clientProvider."</A>";
				 $valecmok = $identsClientsUsage[$clientProvider][$identECM]["ecmok"];
				 $valecm = $identsClientsUsage[$clientProvider][$identECM]["ecm"];
				 if ($valecmok > 0) 
				 	  echo "<FONT color=green>&nbsp;".$valecmok."</FONT>"; 
				 else
				 		echo "<FONT color=red>&nbsp;".$valecm."</FONT>"; 
				 $j++;
		  	}
		  	echo "</td>";	
		}
		else
			echo "<td class=\"Node_clienti\"></td>";
				  
	   echo "</tr>";
}		
	   $i++;
	}
	
	echo "</table><BR>";
	
	
if ($providerslist !="")
{
	echo "<table border=0 cellpadding=2 cellspacing=1>";
	echo "<tr>";
		echo "<td class=\"tabel_header\" colspan=3>Used & known providers List</td>";
	echo "</tr>";
	
	$i=1;
	foreach ($usedProviders as $used_caid => $used_caid_nr) 
	{
		list($caid,$p) = explode(":",$used_caid);
		$caid = sterg0($caid);
		$p = sterg0($p);
		
		$este = isset($total_provider_gasit[$caid][$p]);
		
		if (!$este)
		{
			if (substr($caid,0,1) == "d")
				$este = isset($total_provider_gasit[$caid]); 
		}
		
		if (!$este)
		{
			echo "<tr>";
			echo "<td class=\"Node_count\">".$i."</td>";
			echo "<td class=\"Node_ProviderMissing\" NOWRAP>".providerID($caid,$p,false)."</td>";
			echo "<td class=\"Node_ProviderMissing\"></td>";
			echo "</tr>";
			$i++;
		}
		else
		{
			echo "<tr>";
			echo "<td class=\"Node_count\">".$i."</td>";
			echo "<td class=\"Node_Provider\" NOWRAP>".providerID($caid,$p)."</td>";
			echo "<td class=\"Node_Provider\">OK</td>";
			echo "</tr>";
			$i++;
		}
		
		
	}
	
	echo "</table>";
}	
	
	ENDPage();
?>
