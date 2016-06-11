<?php

	if (file_exists($shares_file))
		$shares_data = file ($shares_file);
		
	if (!isset($shares_data))
	{
		echo "<FONT COLOR=red>Server is down or updating ... Please try again later !</FONT>";
		exit;
	}
	//___________________________________________________________________________________________________
	
	if (!isset($shares_data))
	{
		echo "<FONT COLOR=red>Server is down or updating ... Please try again later !</FONT>";
		exit;
	}
	
	$nodSh = explode("|",$node);
	
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
			
			$share_Node = sterg0($share_Node);
			
			if ($share_Node == "")
					$share_NodeUnic = $share_Caid."|".$share_System."|".$share_ProvidersList."|".$share_Host;
			else
					$share_NodeUnic = $share_Caid."|".$share_System."|".$share_ProvidersList."|".$share_Node;
			
			if ($share_NodeUnic == $node)
			{
				$nodes[] = array ($share_Host,$share_Type,$share_Caid,$share_System,$share_ProvidersList,$share_Providers,$share_Hop,$share_Reshare,$share_Nodes,$share_Node,trim($share[7]));  
				
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
			
			if (strstr($SERVER_USER,":")) 
			{
				$SERVER_IP[$IP_CACHE][] = $SERVER_USER;
				
				$SERVER_CLIENT[$SERVER_USER]["NAME"] = $SERVER_USER;
				$SERVER_CLIENT[$SERVER_USER]["IP"] 	 = $IP_CACHE;
				$SERVER_CLIENT[$SERVER_USER]["TARA"] = $TARA_CACHE;
			}
		}
	}
	
if (count($nodes)>0)
{
	
	format1("NodeID",$nodSh[3]);
	$ServerNodeIDDNS = nodeIdName($nodSh[3]);
	if ($ServerNodeIDDNS != $nodSh[3])
		format1("NodeDNS",$ServerNodeIDDNS);
	format1("Providers",providerID($nodes[0][2],$nodes[0][4],true,""));

	
	echo "<BR><table border=0 cellpadding=2 cellspacing=1>";
	echo "<tr>";
	echo "<th class=\"tabel_headerc\">#</th>";
	echo "<th class=\"tabel_header\">Type</th>";
	echo "<th class=\"tabel_headerc\">hop</th>";
	echo "<th class=\"tabel_header\">Server</th>";	
	echo "<th class=\"tabel_headerc\"><A class=\"header\" HREF=".$pagina."?sort=country>Cnt</A></th>";
	echo "<th class=\"tabel_headerc\"><A class=\"header\" HREF=".$pagina."?sort=ping>Ping</A></th>";
	echo "<th class=\"tabel_headerc\">Reshare</th>";	
	echo "</tr>";
	
	$i = 0;
	foreach ($nodes as $node => $data) 
	{
		$i++;
		$sh_host = $data[0];
		echo "<tr>";
		echo "<td class=\"Node_count\">".$i."</td>";

		if ($data[1] == "CCcam-s2s")
			echo "<td class=\"tabel_hop_total2\">".$data[1]."</td>";
		else
			echo "<td class=\"tabel_hop_total\">".$data[1]."</td>";
			
		echo "<td class=\"tabel_hop_total\">".$data[6]."</td>";
		
		echo "<td class=\"Node_ID\"><A HREF=".$pagina."?nodeDns=$sh_host>".$sh_host."</A></td>";
		
		list($host_DNS, $host_PORT) = explode(":", $sh_host);
		
		$host_IP = $SERVER_CLIENT[$sh_host]["IP"];
		$tara_DNS = $SERVER_CLIENT[$sh_host]["TARA"];
		
		echo "<td class=\"tabel_hop_total2\">".$tara_DNS."</td>";
		
		$pingSalvat = SavedPing($sh_host);
		if ($pingSalvat[1]>10)
			echo "<td class=\"tabel_hop_total2\">".$pingSalvat[0]."</td>";
		else
			echo "<td class=\"tabel_hop_total2\"><FONT COLOR=#555555>".$pingSalvat[0]."</FONT></td>";
			
			
		$reshare = "<FONT COLOR=red>! NO !</FONT>";
		
		if ($fullReshare) 
		{
			if ($data[7] >0) $reshare = $data[7];
		}
		else 
		{
			if ($data[7] >0) $reshare = "<FONT COLOR=yellow>YES</FONT>";
		}
		echo "<td class=\"tabel_hop_total\">".$reshare."</td>";
		
		echo "</tr>";
	}
	
	echo "</table>";
}
?>
</BODY></HTML>