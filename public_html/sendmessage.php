<html>

<head> 

</head> 
 
<BODY>

<script language=javascript> 
	function confirmsendmessage(messagetext)
	{
		alert(messagetext);
 		location.href="clientstats.php";
	}
</script> 

<?php include "common.php"?>
<?php
	include "meniu.php";	

	$fromhost = $_GET['fromhost'];
	$toclient = $_GET['toclient'];
	$messagetext = $_GET['message'];
	$messagetext = "From: ".$fromhost.": ".$messagetext;
	$messagetext = str_replace(" ","%20",$messagetext);		

	$sendtoclient = "";
		
	$messagesent = false;
	$messageerror = false;
	
	echo "<FONT COLOR=yellow>From:</FONT><br>".$fromhost;
	echo "<br>";
	echo "<FONT COLOR=yellow>Message:</FONT><br>".str_replace("%20"," ",$messagetext);
	echo "<br><br>";
	
	if ($toclient == "ALL")
	{
		echo "<FONT COLOR=yellow>Send message to the following clients</FONT><br>";
		echo str_repeat("=",24);
	}
	else
	{
		echo "<FONT COLOR=yellow>Send message to the following client</FONT><br>";
		echo str_repeat("=",23);
	}
	echo "<br>";
	
	checkFile($clients_file);
	$clients_data = file ($clients_file);
	loadUsageData();
	
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
			$ac_ECM 				= trim($active_client[5]);
			$ac_EMM 				= trim($active_client[6]);
			$ac_Version				= trim($active_client[7]);
			$ac_LastShare			= trim($active_client[8]);
			
			$clientConectat[$ac_Username]["Info"] = array ($ac_IP,$ac_Connected,$ac_Idle,$acEcm,$acEcmOk,$acEmm,$acEmmOk,$ac_Version,$ac_LastShare,$ac_EcmTime);  
			tara($ac_IP,$ac_Username);
			
			$sendtoclient = $toclient;
			$process = false;
			if ($toclient == "ALL")
			{
				$sendtoclient = $ac_IP;
				$process = true;
			}
			elseif ($ac_IP == $toclient)
			{
				$process = true;
			}

			if ($process == true)
			{					

				$clientport = array_search($ac_Username,$clientMessagePort);
				
				if ($clientport != "" )
				{
					echo "To:&nbsp".$ac_Username."&nbsp=>&nbsp";
											
					$command = "http://".$sendtoclient.":".$clientport."/cgi-bin/xmessage?caption=Info&timeout=0&body=".$messagetext;	//Enigma1
					echo "Sending message to client, trying Enigma1 format...";

					flush();
					$var = file_get_contents("$command");
					if ($var ==true)
					{
						$messagesent = true;
						echo ":&nbsp<FONT COLOR=green>Message sent.</FONT>";
					}
					else
					{
						$command = "http://".$sendtoclient.":".$clientport."/web/message?text=".$messagetext."&type=3&timeout=0";
						echo ":&nbsp<FONT COLOR=red>Error</FONT>,&nbsptrying Enigma2 format...";
						flush();
						$var = file_get_contents("$command");
						if ($var ==true)
						{
							$messagesent = true;
							echo ":&nbsp<FONT COLOR=green>Message sent.</FONT>";
						}
						else
						{
							$messageerror = true;
							echo ":&nbsp<FONT COLOR=red>Error! Message NOT sent.</FONT>";	
						}

					}
									
					echo "<br>";					
					flush();
				}

			}
		}
	}

	$messagetext = "Message sent.";
	
	if ($messagesent == true)
	{
		if ($messageerror == true)
			$messagetext = "Message sent with errors.";
	}
	else
		$messagetext = "Message not sent.";
		
	echo "<br>";
	flush();
	echo "<script language=javascript>confirmsendmessage('$messagetext')</script>";

?>
  
<BR></BODY></HTML>


