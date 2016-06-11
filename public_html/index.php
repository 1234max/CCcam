<?php include "common.php"?>
<?php

	if (!$update_from_button)
	{
		$update_info = true;
		$update_servers = true;
		$update_activeclients = true;
		$update_entitlements = true;
	}

	include "meniu.php";

	if (file_exists($entitlements_file))
		$entitlements_data = file ($entitlements_file);
		
	$localecm = 0;
	$localecmOK= 0;
	foreach ($entitlements_data as $currentline) 
	if (!strstr($currentline,"</H2>")) 		
	{
		if (strstr($currentline,"handled ")) 
		{
			
			$liniesplit = explode(" ", $currentline);
			list($xlocalecm,$xlocalecmOK) = explode("(", substr($liniesplit[1],0,-1),2);
			
			$localecm   = $localecm+$xlocalecm;
			$localecmOK = $localecmOK+$xlocalecmOK;
		}	
	}
	
	if (isset($caminfo_data) && count($caminfo_data)>0)
	foreach ($caminfo_data as $currentline) 
	{
		$liniesplit = explode("<BR>", $currentline);
		foreach ($liniesplit as $linie) 
		{
			if (strstr($linie,"Current time")) 
			{
				$temp = explode(" ",$linie);
				format1("Current time",$temp[2]);
			}
			else
			if (strstr($linie,"Uptime"))
			{
				$temp = explode(" ",$linie);
				format1("Uptime",$temp[1]." ".$temp[2]);
			}
			else
			{
				$linieafis = explode(":",$linie);
				
				if (strstr($linie,"NodeID")) 								format1($linieafis[0],$linieafis[1]);
				if (strstr($linie,"Connected clients")) 				format1($linieafis[0],$linieafis[1]);
				//if (strstr($linie,"Active clients")) 					format1($linieafis[0],$linieafis[1]);
				if (strstr($linie,"Total handled client ecm's")) 	format1($linieafis[0],$linieafis[1]);
				if (strstr($linie,"Total handled client emm's")) 	
				{
					format1($linieafis[0],$linieafis[1]);
					format1("Total handled LOCAL ecm's",$localecmOK,$localecm);
				}
				if (strstr($linie,"Peak load")) 							format1($linieafis[0],$linieafis[1]);
			}

			
		}
	}
	

	checkFile($activeclients_file);
	$activeclients_data = file ($activeclients_file);
	
	foreach ($activeclients_data as $currentline) 
	{
		$inceput1 = substr($currentline,0,1);
		$inceput2 = substr($currentline,1,2);
		if (strstr($currentline,"| Shareinfo")) break; 	

		if ($inceput1 == "|" && $inceput2 != " U")
		{
			$active_client = explode("|", $currentline);
			$ac_Username 			= trim($active_client[1]);
			$ac_IP 					= trim($active_client[2]);
			$ac_Connected 			= trim($active_client[3]);
			$ac_Idle 				= trim($active_client[4]);
			$ac_ECM 					= trim($active_client[5]);
			$ac_EMM 					= trim($active_client[6]);
			$ac_Version				= trim($active_client[7]);
			$ac_LastShare			= trim($active_client[8]);
			
			$ac_EcmTime	= "";  
			if (isset($active_client[9]))	
				$ac_EcmTime	= trim($active_client[9]);
			
			$clientActiv[$ac_Username]["Info"] = array ($ac_IP,$ac_Connected,$ac_Idle,$ac_ECM,$ac_EMM,$ac_Version,$ac_LastShare,$ac_EcmTime);  
			
		}
	}
	
	echo "<br>";
	
if ( isset($clientActiv) && count($clientActiv)>0 )
{	
	format1("Active Clients",count($clientActiv));
	echo "<table border=0 cellpadding=2 cellspacing=1>";
	echo "<tr>";
	echo "<th class=\"tabel_headerc\">#</th>";
	echo "<th class=\"tabel_headerc\">Username</th>";
	if ($country_whois == true) 
		echo "<th class=\"tabel_headerc\" COLSPAN=\"2\">Host</th>";	
	else
		echo "<th class=\"tabel_headerc\">Host</th>";	

	echo "<th class=\"tabel_headerc\">Connected</th>";	
	echo "<th class=\"tabel_headerc\">Idle time</th>";	
	echo "<th class=\"tabel_headerc\">ECM</th>";	
	echo "<th class=\"tabel_headerc\">EMM</th>";	
	echo "<th class=\"tabel_headerc\">Ver</th>";	
	echo "<th class=\"tabel_headerc\" COLSPAN=\"2\">Last used share</th>";	
	echo "<th class=\"tabel_headerc\">Ecm time</th>";	
	echo "</tr>";
	
	$i=0;
	foreach ($clientActiv as $username => $client) 
	{
		$i++;
		echo "<tr>";
		echo "<td class=\"Node_count\">".$i."</td>";
		echo "<td class=\"tabel_ecm\"><A HREF=".$pagina."?username=$username>".$username."</A></td>";
		if ($country_whois == true) 
		{
			$tara = tara($client["Info"][0],$username);
			echo "<td class=\"tabel_ecm\">".$tara["tara"]."</td>";		
		}
		echo "<td class=\"tabel_hop_total2\">".$client["Info"][0]."</td>";
		echo "<td class=\"tabel_hop_total2\">".$client["Info"][1]."</td>";
		echo "<td class=\"tabel_hop_total2\">".$client["Info"][2]."</td>";
		echo "<td class=\"tabel_hop_total2\">".$client["Info"][3]."</td>";
		echo "<td class=\"tabel_hop_total2\">".$client["Info"][4]."</td>";
		echo "<td class=\"tabel_hop_total2\">".$client["Info"][5]."</td>";
		
		$lastused_Share = explode(" ", $client["Info"][6]);
		$lastused_ShareCount = count($lastused_Share);
		$text_lastshare = "";for ($k = 0; $k <= $lastused_ShareCount-2; $k++) $text_lastshare = $text_lastshare.$lastused_Share[$k]." ";

		echo "<td class=\"tabel_normal\">".trim($text_lastshare)."</td>";		
		
		if ($lastused_ShareCount >1)
		{
			$text_ok = trim($lastused_Share[$lastused_ShareCount-1]);
			if ($text_ok == "(ok)") echo "<td class=\"tabel_hop_total2\"><FONT COLOR=\"green\">".$text_ok."</FONT></td>";
			else							echo "<td class=\"tabel_hop_total2\"><FONT COLOR=\"red\">".$text_ok."</FONT></td>";
		}
		else
			echo "<td class=\"tabel_hop_total2\"></td>";

		echo "<td class=\"tabel_hop_total2\">".$client["Info"][7]."</td>";
		echo "</tr>";
	}
}	
	echo "</table>";
	
	checkFile($servers_file);
	$servers_data = file ($servers_file);
	
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
				
				if (!isset($ecm_hit[$hit_array[0]][$hit_caid]["Info"]["ECM"])) 		$ecm_hit[$hit_array[0]][$hit_caid]["Info"]["ECM"] = 0;
				if (!isset($ecm_hit[$hit_array[0]][$hit_caid]["Info"]["ECMOK"])) 		$ecm_hit[$hit_array[0]][$hit_caid]["Info"]["ECMOK"] = 0;
				
				$ecm_hit[$hit_array[0]][$hit_caid]["Info"]["ECM"] 		+= $hit_ecm;
				$ecm_hit[$hit_array[0]][$hit_caid]["Info"]["ECMOK"] 	+= $hit_ecmOK;
				
				
				if (!isset($ecm_hit[$hit_array[0]]["total"]["Info"]["ECM"])) 			$ecm_hit[$hit_array[0]]["total"]["Info"]["ECM"] = 0;
				if (!isset($ecm_hit[$hit_array[0]]["total"]["Info"]["ECMOK"])) 		$ecm_hit[$hit_array[0]]["total"]["Info"]["ECMOK"] = 0;
				
				$ecm_hit[$hit_array[0]]["total"]["Info"]["ECM"] 		+= $hit_ecm;
				$ecm_hit[$hit_array[0]]["total"]["Info"]["ECMOK"] 		+= $hit_ecmOK;

			}
		}
	}
	echo "<BR>";	

if ( isset($ecm_hit) && count($ecm_hit)>0 )
{
	ksort($ecm_hit);
	
	foreach ($ecm_hit as $tip_ecmhit => $ecmhit)
	{
		$totalECM = 0;
		$ecmhit_ECMOK = 0;
		
		if (isset($ecmhit["total"]["Info"]["ECM"]))  	$totalECM = $ecmhit["total"]["Info"]["ECM"];
		if (isset($ecmhit["total"]["Info"]["ECMOK"]))  	$ecmhit_ECMOK = $ecmhit["total"]["Info"]["ECMOK"];
		
	 	$procentEcm = 0; if ($totalECM > 0) $procentEcm = (int)($ecmhit_ECMOK/$totalECM *100);
	 	$procentEcmAfisat = procentColor($procentEcm);
	 		
	 	if ($totalECM == 0)
	 	{
	 		$totalECM = "<FONT COLOR=red>".$totalECM."</FONT>";
	 		$procentEcmAfisat = "-";
	 	}
	 	
	 	echo "Recent <B><FONT COLOR=\"white\">".$tip_ecmhit."</FONT></b> ECM handled :<B> ";
	 	echo "<FONT COLOR=\"white\">".$ecmhit_ECMOK."</FONT>/".$totalECM." (".$procentEcmAfisat.")";
		echo "</b><BR>";
	
		
		echo "<table border=0 cellpadding=2 cellspacing=1>";
		echo "<tr>";
		echo "<th class=\"tabel_headerc\">#</th>";
		echo "<th class=\"tabel_headerc\">Ecm</th>";
		echo "<th class=\"tabel_headerc\">Handled</th>";	
		echo "<th class=\"tabel_headerc\">%</th>";	
		echo "<td class=\"tabel_header\">Provider Name</th>";
		echo "</tr>";
		
		$i=1;
		if (count($ecmhit)>0)
		{
			//ksort($ecmhit);
			
			foreach ($ecmhit as $caid => $info) 
			if ($caid != "total")
			{
				
		
				$ecmhit_ECMOK = 0;
				if (isset($ecmhit[$caid]["Info"]["ECMOK"]))  	$ecmhit_ECMOK = $ecmhit[$caid]["Info"]["ECMOK"];
				
				$key = adaug0($ecmhit_ECMOK,20)."_".$caid;
				
				$servers_sortat[$tip_ecmhit][$key] = $caid;
				
				//echo $key."<BR>";
			}
			
			krsort($servers_sortat[$tip_ecmhit]);
			
	
			foreach ($servers_sortat[$tip_ecmhit] as $key => $caid) 
			//if ($caid != "total")
			{
				$info = $ecmhit[$caid];
				echo "<tr>";
				
				$hit_provider = explode(":",$caid);
				$caidECM = $hit_provider[0];
				$pECM    = $hit_provider[1];
				
				echo "<td class=\"Node_count\">".$i."</td>";
				
				//------------- ECM
					
		
				$totalECM = 0;
				$ecmhit_ECMOK = 0;
				
				if (isset($ecmhit[$caid]["Info"]["ECM"]))  		$totalECM = $ecmhit[$caid]["Info"]["ECM"];
				if (isset($ecmhit[$caid]["Info"]["ECMOK"]))  	$ecmhit_ECMOK = $ecmhit[$caid]["Info"]["ECMOK"];
				
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
		
				echo "<td class=\"Node_Provider\">".providerID($caidECM,$pECM)."</td>";
				echo "</tr>";
				$i++;
			}
			echo "</table>";
			echo "<BR>";
		}
	}
}
	
ENDPage();
?>