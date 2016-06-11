<?php include "common.php"?>
<?php
	
	if (!$update_from_button)
	{
		$update_info = true;
		$update_shares = true;
	}
	
	include "meniu.php";
	
	
	checkFile($shares_file);
	$shares_data = file ($shares_file);
	
	//___________________________________________________________________________________________________
	$total_shares["total"] = 0;
	$maxhop = 0;
	
	if (!isset($shares_data))
	{
		echo "<FONT COLOR=red>Server is down or updating ... Please try again later !</FONT>";
		exit;
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
	
	if (!isset($nodes))
	{
		echo "<FONT COLOR=red>CCCam server is down. Please try again later !</FONT>";
		exit;	
	}
	//___________________________________________________________________________________________________
	
	$total_nodes = 0;
	$total_nodes_re = 0;
	if (isset($nodes)) $total_nodes = count($nodes);
	if (isset($re_unic)) $total_nodes_re = count($re_unic);
	
	$uniqueNodeIndex = (int)($total_nodes/$total_shares["total"] *100);
	$uniqueReshareIndex = (int)($total_nodes_re/$total_nodes *100);
	
	format1("Shares",$total_shares["total"]);
	format1("Nodes",$total_nodes,$total_shares["total"]);
	format1("Reshare",$total_reshare["total"],$total_shares["total"]);
	format1("Reshare Nodes",$total_nodes_re,$total_nodes);
	

	echo "<BR>";
	echo "<table width=100% border=0 cellpadding=2 cellspacing=1>";
	echo "<tr>";
	echo "<th class=\"tabel_headerc\">#</th>";
	echo "<th class=\"tabel_header\">Type</th>";
	echo "<th class=\"tabel_header\">Nodes</th>";
	echo "<th class=\"tabel_headerc\">Ping</th>";
	echo "<th class=\"tabel_headerc\">Shares</th>";
	for ($k = 1; $k <= $maxhop; $k++) echo "<td class=\"tabel_headerc\">hop".$k."</th>"; 
	echo "<th class=\"tabel_header\">Reshare</th>";
	echo "<th class=\"tabel_header\" width=100%>CAID/Idents</th>";
	echo "</tr>";
	
	echo "<tr>";
	echo "<td class=\"Node_Provider\" COLSPAN=3></td>";
	echo "<td class=\"tabel_total\"></td>";
	echo "<td class=\"tabel_total\">".$total_shares["total"]."</td>";
	for ($k = 1; $k <= $maxhop; $k++) echo "<td class=\"tabel_total\">".$total_shares[$k]."</td>"; 
	echo "<td class=\"tabel_total\">".$total_reshare["total"]."</td>";
	echo "<td class=\"tabel_total\"></td>";
	echo "</tr>";
	
			  
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
		{
			echo "<td class=\"Node_ID_hopCS\">".$nodetype."</td>";
			echo "<td class=\"Node_ID_hop1\"><A HREF=".$pagina."?nodeDns=$share_host><FONT COLOR=black>".$share_host."</FONT></A></td>";
			echo "<td class=\"tabel_hop_total2\"><A HREF=".$pagina."?nodeDns=".$share_host."&checkPing=1>".pingColor(SavedPing($share_host))."</A></td>";
			echo "<td class=\"tabel_total\">".$total."</td>";
			echo "<td class=\"tabel_hop\" COLSPAN=".($maxhop)."></td>";
		}
		else
		{
			$textNod = linkNod($node,$textNod,"node");
			echo "<td class=\"tabel_normal\">".$nodetype."</td>";
			echo "<td class=\"Node_ID_hop1\">".$textNod."</td>";
			echo "<td class=\"tabel_hop_total2\"><A HREF=".$pagina."?nodeDns=".$share_host."&checkPing=1>".pingColor(SavedPing($share_host))."</A></td>";
			echo "<td class=\"tabel_total\">".$total."</td>";
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
		$textNod = linkNod($node,$textNod,"node");
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
		$textNod = linkNod($node,$textNod,"node");
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
		$textNod = linkNod($node,$textNod,"node");
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
		$textNod = linkNod($node,$textNod,"node");
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

	
	echo "</table>";
	ENDPage();
?>
