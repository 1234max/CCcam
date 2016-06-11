<html>

<head> 

</head> 
 
<BODY>

<script language=javascript> 
	function trim(string)
	{
		while(string.substr(0,1)==" ")
			string = string.substring(1,string.length) ;
		
		while(string.substr(string.length-1,1)==" ")
			string = string.substring(0,string.length-2) ;
		
		return string;
	}
	
	function SendMessageTo(fromhost,toclient)
	{
		messagetext = trim(document.all("messagetext").value);
		
		if (messagetext == "" ) {
			alert("Message text is empty");
			return;
		}

 		location.href="SendMessage.php?fromhost=" + fromhost + "&toclient=" + toclient + "&message=" + messagetext;
		
	}
</script> 

<?php include "common.php"?>
<?php
	if (!$update_from_button)
	{
		$update_clients = true;
	}

	include "meniu.php";
	
	checkFile($clients_file);
	$clients_data = file ($clients_file);
	
	loadUsageData();
	
	$totalUsage = 0;
	$totalAVU = 0;
	$totalport = 0;

	foreach ($clients_data as $currentline) 
	{
		$inceput1 = substr($currentline,0,1);
		$inceput2 = substr($currentline,1,2);
		if (strstr($currentline,"| Shareinfo")) break; 	

		if ($inceput1 == "|" && $inceput2 != " U")
		{
			$active_client 		= explode("|", $currentline);
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
			
			list($acEcm,$acEcmOk) = explode("(", $ac_ECM);
			list($acEcmOk,$temp) = explode(")", $acEcmOk);
			
			list($acEmm,$acEmmOk) = explode("(", $ac_EMM);
			list($acEmmOk,$temp) = explode(")", $acEmmOk);

			$clientConectat[$ac_Username]["Info"] = array ($ac_IP,$ac_Connected,$ac_Idle,$acEcm,$acEcmOk,$acEmm,$acEmmOk,$ac_Version,$ac_LastShare,$ac_EcmTime);  
			tara($ac_IP,$ac_Username);
			
			$SaveIndexECM = $UsageUsers[$ac_Username]["usage"];
			list($lastIndexEcm,$averageIndexEcm) = explode(".", $SaveIndexECM,2);
			$averageIndex = (int)(($lastIndexEcm + $averageIndexEcm*3)/4);
			
			$totalUsage = $totalUsage + $lastIndexEcm;
			$totalAVU = $totalAVU + $averageIndex;
			
			if (array_search($ac_Username,$clientMessagePort) != "")
				$totalport++;
		}
	}


	//___________________________________________________________________________________________________
	

	format1("Connected clients",count($clientConectat));
	format1("Current Usage (ECM requests/h)",$totalUsage);
	format1("Average Usage (ECM requests/h)",$totalAVU);
	
	if ($totalport > 0)
	{
	echo "
	<form method='post' action=''> 
	<th class=\"tabel_headerc\"><A class=\"header\" >Message Text</A></th>
	<input type='text' name='messagetext' size 20>
	<td class=\"tabel_hop_total2\"><input type='button' name='buttonsendall' value='Send to All' style='width:70px;height:18px;font-family: Tahoma;font-size : 9px' onclick=\"SendMessageTo('$cccam_host','ALL')\"</A></td>
	</form>"; 	
	};
		
	echo "<br>";
	echo "<table border=0 cellpadding=2 cellspacing=1>";
	echo "<tr>";
	echo "<th class=\"tabel_headerc\">#</th>";
	echo "<th class=\"tabel_headerc\"><A class=\"header\" HREF=".$pagina."?sort=average>AVU</A></th>";
	echo "<th class=\"tabel_headerr\"><A class=\"header\" HREF=".$pagina."?sort=username>Username</A></th>";
	if ($country_whois == true) 
		echo "<th class=\"tabel_header\" COLSPAN=\"2\"><A class=\"header\" HREF=".$pagina."?sort=country>Country</A> / <A class=\"header\" HREF=".$pagina."?sort=host>IP</A></th>";	
	else
		echo "<th class=\"tabel_headerc\"><A class=\"header\" HREF=".$pagina."?sort=host>Host</A></th>";	
	echo "<th class=\"tabel_headerc\"><A class=\"header\" HREF=".$pagina."?sort=usage>Usage</A></th>";
	echo "<th class=\"tabel_headerc\"><A class=\"header\" HREF=".$pagina."?sort=connected>Connected</A></th>";	
	echo "<th class=\"tabel_headerc\"><A class=\"header\" HREF=".$pagina."?sort=idle>Idle time</A></th>";	
	echo "<th class=\"tabel_headerc\" COLSPAN=\"2\"><A class=\"header\" HREF=".$pagina."?sort=ecm>Ecm</A> / <A class=\"header\" HREF=".$pagina."?sort=ecmok>Ok</A></th>";	
	echo "<th class=\"tabel_headerc\"><A class=\"header\" HREF=".$pagina."?sort=ecmprocent> OK% </A></th>";	
	echo "<th class=\"tabel_headerc\" COLSPAN=\"2\"><A class=\"header\" HREF=".$pagina."?sort=emm>Emm</A> / <A class=\"header\" HREF=".$pagina."?sort=emmok>Ok</A></th>";	
	echo "<th class=\"tabel_headerc\"><A class=\"header\" HREF=".$pagina."?sort=ver>Ver</A></th>";	
	echo "<th class=\"tabel_header\" COLSPAN=\"2\"><A class=\"header\" HREF=".$pagina."?sort=lastshare>Last used share</A></th>";	
	echo "<th class=\"tabel_headerc\"><A class=\"header\" HREF=".$pagina."?sort=ecmtime>Ecm time</A></th>";	
	echo "<th class=\"tabel_headerc\"><A class=\"header\">Send Message</A></th>";	
	echo "</tr>";
	
	$ordine = 0;
	foreach ($clientConectat as $username => $client) 
	{
		$key = "";
		$ordine = 1;
		
		if ($sort == "usage") 
		{
			$SaveIndexECM = $UsageUsers[$username]["usage"];
			list($lastIndexEcm,$averageIndexEcm) = explode(".", $SaveIndexECM,2);
			
			if ($lastIndexEcm == 0)
				$key = adaug0("0",20).$username; 
			else
			{
				$key = adaug0($lastIndexEcm,10).adaug0($client["Info"][3],10).$username; 
			}
			
			$ordine = 1;
		}
		else
		if ($sort == "average" || $sort == "") 
		{
			$SaveIndexECM = $UsageUsers[$username]["usage"];
			list($lastIndexEcm,$averageIndexEcm) = explode(".", $SaveIndexECM,2);
			
			$averageIndex = 0;
			if ($averageIndexEcm == "") $averageIndexEcm = 0;
			
			$averageIndex = (int)(($lastIndexEcm + $averageIndexEcm*3)/4);
			
			$key = adaug0($averageIndex,10).adaug0($lastIndexEcm,10).adaug0($client["Info"][3],10).$username; 

			//echo $key."<BR>";
			$ordine = 1;
		}
		else
		if ($sort == "username") 
		{
			$key = $username; 
			$ordine = 2;
		}
		else
		if ($sort == "country") 
		{
			if ($country_whois == true) 
			{
				$tara = tara($client["Info"][0],$username);
				$key = $tara["tara"].$username; 
				$ordine = 2;
			}
		}
		else
		if ($sort == "host") 
		{
			$key = adaug0($client["Info"][0],15).$username; 
			$ordine = 2;
		}
		else
		if ($sort == "connected") 
		{
			$key = adaug0($client["Info"][1],15).$username; 
			$ordine = 1;
		}
		else
		if ($sort == "idle") 
		{
			$key = adaug0($client["Info"][2],15).$username; 
			$ordine = 1;
		}
		else
		if ($sort == "ecm") 
		{
			$key = adaug0($client["Info"][3],10).$username; 
			$ordine = 1;
		}
		else
		if ($sort == "ecmok") 
		{
			$key = adaug0($client["Info"][4],10).$username; 
			$ordine = 1;
		}
		else
		if ($sort == "ecmprocent") 
		{
			$procentEcm = (int)($client["Info"][4]*100/$client["Info"][3]);
			$key = adaug0($procentEcm,10).adaug0($client["Info"][3],10).$username; 
			$ordine = 1;
		}
		else
		if ($sort == "emm") 
		{
			$key = adaug0($client["Info"][5],10).$username; 
			$ordine = 1;
		}
		else
		if ($sort == "emmok") 
		{
			$key = adaug0($client["Info"][6],10).$username; 
			$ordine = 1;
		}
		else
		if ($sort == "ver") 
		{
			$key = adaug0($client["Info"][7],10).$username; 
			$ordine = 1;
		}
		else
		if ($sort == "lastshare") 
		{
			$key = adaug0($client["Info"][8],10).$username; 
			$ordine = 2;
		}
		else
		if ($sort == "ecmtime") 
		{
			$key = adaug0($client["Info"][9],10).$username; 
			$ordine = 2;
		}
	 	
		//echo $key."<BR>";
		$servers_sortat[$key] = $username;
		//$servers_sortat_value[$key]["indexECM"] 	= $indexServer;
	}
	if ($ordine == 1)
		krsort($servers_sortat);
	else
	if ($ordine == 2)
		ksort($servers_sortat);

	$i=0;
	foreach ($servers_sortat as $key => $username)
	{ 
		$client = $clientConectat[$username];
		
		$i++;
		echo "<tr>";
		echo "<td class=\"Node_count\">".$i."</td>";
		
		$SaveIndexECM = $UsageUsers[$username]["usage"];
		list($lastIndexEcm,$averageIndexEcm) = explode(".", $SaveIndexECM,2);
		if ($averageIndexEcm == "") $averageIndexEcm = 0; 
		$indexclient = (int)(($lastIndexEcm + $averageIndexEcm*3)/4);
		
		if ($indexclient <100) $usageClient = "<FONT COLOR=gray>".$indexclient."</FONT>";
	 	else
	 	if ($indexclient <250) $usageClient = "<FONT COLOR=green>".$indexclient."</FONT>";
	 	else
	 	if ($indexclient <500) $usageClient = "<FONT COLOR=yellow>".$indexclient."</FONT>";
	 	else
	 	if ($indexclient <1000) $usageClient = "<FONT COLOR=orange>".$indexclient."</FONT>";
	 	else
	 		$usageClient = "<FONT COLOR=red>".$indexclient."</FONT>";
	 		
		echo "<td class=\"tabel_hop_total2\">".$usageClient."</td>";
		
		echo "<td class=\"Node_IDr\"><A HREF=".$pagina."?username=$username>".$username."</A></td>";
		if ($country_whois == true) 
		{
			$tara = tara($client["Info"][0],$username);
			echo "<td class=\"tabel_ecm\">".$tara["tara"]."</td>";		
		}
		
		echo "<td class=\"tabel_normal\">".$client["Info"][0]."</td>";
		
		$indexclient = $lastIndexEcm;
		if ($indexclient <100) $usageClient = "<FONT COLOR=gray>".$indexclient."</FONT>";
	 	else
	 	if ($indexclient <250) $usageClient = "<FONT COLOR=green>".$indexclient."</FONT>";
	 	else
	 	if ($indexclient <500) $usageClient = "<FONT COLOR=yellow>".$indexclient."</FONT>";
	 	else
	 	if ($indexclient <1000) $usageClient = "<FONT COLOR=orange>".$indexclient."</FONT>";
	 	else
	 		$usageClient = "<FONT COLOR=red>".$indexclient."</FONT>";
	 		
		echo "<td class=\"tabel_hop_total2\">".$usageClient."</td>";
		
		echo "<td class=\"tabel_hop_total2\">".$client["Info"][1]."</td>";
		echo "<td class=\"tabel_hop_total2\">".$client["Info"][2]."</td>";
		echo "<td class=\"tabel_hop_total2\">".$client["Info"][3]."</td>";
		echo "<td class=\"tabel_hop_total2\">".$client["Info"][4]."</td>";
		
		if ($client["Info"][3] == 0)
			$procentEcm = 0;
		else	
			$procentEcm = (int)($client["Info"][4]*100/$client["Info"][3]);
		if ($client["Info"][3] == 0)
			echo "<td class=\"tabel_hop_total2\"></td>";
		else
			echo "<td class=\"tabel_hop_total2\">".procentColor($procentEcm)."</td>";
		
		echo "<td class=\"tabel_hop_total2\">".$client["Info"][5]."</td>";
		echo "<td class=\"tabel_hop_total2\">".$client["Info"][6]."</td>";
		echo "<td class=\"tabel_hop_total2\">".$client["Info"][7]."</td>";		
		
		$lastused_Share = explode(" ", $client["Info"][8]);
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

		echo "<td class=\"tabel_hop_total2\">".$client["Info"][9]."</td>";

		
		if (array_search($username,$clientMessagePort) == "")
			echo "<td class=\"tabel_hop_total2\">"."no port defined"."</td>";
		else
		{
			$hostname = $client["Info"][0];
			echo "<td class=\"tabel_hop_total2\"><input type='button' name='buttonsend' value='Send' style='width:40px;height:18px;font-family: Tahoma;font-size : 9px' onclick=\"SendMessageTo('$cccam_host','$hostname')\"</A></td>";
		};

		echo "</tr>";
	}
	
	echo "</table>";
	

ENDPage();
?>

<BR></BODY></HTML>
