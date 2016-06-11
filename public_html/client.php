<?php
   
   checkFile($clients_file);
   
   if (file_exists($clients_file))
      $clients_data = file ($clients_file);
      
   if (!isset($clients_data))
   {
      echo "<FONT COLOR=red>Server is down or updating ... Please try again later !</FONT>";
      exit;
   }

	loadUsageData();	
   
   //___________________________________________________________________________________________________
   
   $user_shareinfo = false;
   $lastUsername = "";
   foreach ($clients_data as $currentline) 
	{
		$inceput1 = substr($currentline,0,1);
		$inceput2 = substr($currentline,1,2);
		
		
		if (strstr($currentline,"| Shareinfo")) 
			$user_shareinfo = true;

		if ($user_shareinfo == false && $inceput1 == "|" && $inceput2 != " U")
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
			
		}
		else
		if ($user_shareinfo == true && $inceput1 == "|" )
		{
			$share_client 		= explode("|", $currentline);
			$share_Username 	= trim($share_client[1]);
			$share_Idents 	   = trim($share_client[2]);
			
			if ($share_Username!="")
				$lastUsername = $share_Username;
				
			$clientConectat[$lastUsername]["Info"]["Idents"][] = $share_Idents;  
			//echo "<BR>".$lastUsername."-".$share_Idents;
			
		} 
	}
	
	//___________________________________________________________________________________________________

   if (!isset($clientConectat[$username]))
   {
   	format1("Connected","offline");
   }
   else
   {
   	
   	$username_IP = $clientConectat[$username]["Info"][0]; 
   	$tara_user  = taraNameSaved($username);
   	$tara_code  = tara($username_IP,$username);
   	if ($country_whois == true) 
      	$tara_nume = taraName($tara_code["tara"]);
   
      format1("Username",$username." (".$username_IP.")");
      if ($country_whois == true)
	   {
	      if ($tara_code["tara"]!="<>")
	         format1("Country",$tara_code["tara"]." , ".$tara_nume);
	      else
	         format1("Country","Local Private IP");
	   }
   
   	format1("Connected",$clientConectat[$username]["Info"][1]);
   	format1("Idle time",$clientConectat[$username]["Info"][2]);
   	format1("CCcam Version",$clientConectat[$username]["Info"][7]);
   	
   	if ($country_whois == true && $tara_code["tara"]!="<>")
	   {
	   	echo "<BR>";
	      format1("ISP Info",$tara_code["info"]);
	   }
	   
	   echo "<BR>";
	   
	   $lastused_Share = explode(" ", $clientConectat[$username]["Info"][8]);
	   $lastused_ShareCount = count($lastused_Share);
		$text_lastshare = "";for ($k = 0; $k <= $lastused_ShareCount-2; $k++) $text_lastshare = $text_lastshare.$lastused_Share[$k]." ";
		$text_lastshare = trim($text_lastshare);
		
		$text_lastshare_ok = "";
		
		if ($lastused_ShareCount >1)
		{
			$text_lastshare_ok = trim($lastused_Share[$lastused_ShareCount-1]);
			if ($text_lastshare_ok == "(ok)") $text_lastshare_ok = "<FONT COLOR=\"green\">".$text_lastshare_ok."</FONT>";
			else										 $text_lastshare_ok = "<FONT COLOR=\"red\">".$text_lastshare_ok."</FONT>";
		}
	   
	  $SaveIndexECM = $UsageUsers[$username]["usage"];
		list($lastIndexEcm,$averageIndexEcm) = explode(".", $SaveIndexECM,2);
	  $indexclient = $lastIndexEcm;
		
		if ($indexclient <99) $usageClient = "<FONT COLOR=gray>".$indexclient."</FONT>";
	 	else
	 	if ($indexclient <249) $usageClient = "<FONT COLOR=green>".$indexclient."</FONT>";
	 	else
	 	if ($indexclient <499) $usageClient = "<FONT COLOR=yellow>".$indexclient."</FONT>";
	 	else
	 	if ($indexclient <999) $usageClient = "<FONT COLOR=orange>".$indexclient."</FONT>";
	 	else
	 		$usageClient = "<FONT COLOR=red>".$indexclient."</FONT>";
	 		
	 	format1("Current Usage",$usageClient . " ECM/hour");
	 	
	 	if ($averageIndexEcm == "") $averageIndexEcm = 0;
		$indexclient = (int)(($lastIndexEcm + $averageIndexEcm*3)/4);
			
		if ($indexclient <99) $usageClient = "<FONT COLOR=gray>".$indexclient."</FONT>";
	 	else
	 	if ($indexclient <249) $usageClient = "<FONT COLOR=green>".$indexclient."</FONT>";
	 	else
	 	if ($indexclient <499) $usageClient = "<FONT COLOR=yellow>".$indexclient."</FONT>";
	 	else
	 	if ($indexclient <999) $usageClient = "<FONT COLOR=orange>".$indexclient."</FONT>";
	 	else
	 		$usageClient = "<FONT COLOR=red>".$indexclient."</FONT>";
	 		
	 	format1("Average Usage",$usageClient. " ECM/hour");
		echo "<BR>"; 	
			
   	format1("Last used share",$text_lastshare." ".$text_lastshare_ok);
   	format1("ECM Time",$clientConectat[$username]["Info"][9]);
	   
	   $ecm_total = 0;
	   $ecmOK_total = 0;
	   foreach ($clientConectat[$username]["Info"]["Idents"] as $hit_data)
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
	   
	   
	   if ($ecm_total)
	   {
	      echo "<BR>"; 
	     	format1("Handled ECM ( this session )",$ecmOK_total,$ecm_total);
	     	
	      echo "<table border=0 cellpadding=2 cellspacing=1>";
	      echo "<tr>";
	      echo "<th class=\"tabel_headerc\">#</th>";
	      echo "<th class=\"tabel_headerc\">Type</th>";
	      echo "<th class=\"tabel_header\">ECM</th>";
	      echo "<th class=\"tabel_header\">OK</th>";
	      echo "<th class=\"tabel_header\">CAID/Ident</th>";
	      echo "</tr>";
	   }
	   
	   krsort($ecmok_sortat);
	   
	   $counthits = 0;
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
	
	   if ($ecm_total)
	      echo "</table>";
	   echo "<BR>";
  	}

   

ENDPage();
?>
