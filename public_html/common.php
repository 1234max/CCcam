<?php
//___________________________________________________________________________________________________   
function timpexec()
{
   $time = microtime();
   return $time;
}

$timpSTART = timpexec();

//___________________________________________________________________________________________________
function ENDPage()
{
   global $timpSTART;
   $timpEND = timpexec();
   $diferentaTIMP = number_format(((substr($timpEND,0,9)) + (substr($timpEND,-10)) - (substr($timpSTART,0,9)) - (substr($timpSTART,-10))),4);
   $TIMP_Page = "<BR>Page Loading Time : <B>".$diferentaTIMP."</B>s";
   echo '<font color="#494949">'.$TIMP_Page.'</font>';
   echo "<BR></BODY></HTML>";
}


error_reporting(E_ERROR | E_PARSE);
//error_reporting(E_ALL);


//-------------------------------------
// UPDATE - REDIRECT
//-------------------------------------
$forceupdate = "";   if(isset($_GET['forceupdate']))  $forceupdate = $_GET['forceupdate'];
$page = "";          if(isset($_GET['page']))         $page = $_GET['page'];

if($forceupdate != "" && $page != "")
{
   $pageRedirect = $page;
   $pageRedirect = $pageRedirect."?forceupdate=1";
   if ($sort != "") $pageRedirect = $pageRedirect."&sort=".$sort;
   header('Location: '.$pageRedirect);
   exit;
}
//-------------------------------------
// DEFAULTS - CONFIGURED in config.php
//-------------------------------------
$work_path = "";
$update_from_button = true;
$fullReshare = true;
$country_whois = true;
if (file_exists("config.php")) include "config.php";

//-------------------------------------
// ACTIVE PROFLE
//-------------------------------------

$active_profie = 0;
$filename_profile = "profile.ini";
$setProfil = "";  if(isset($_GET['setProfil'])) $setProfil = $_GET['setProfil'];

if ($setProfil != "")
{
   $active_profie = $setProfil;
   $fp = @fopen($filename_profile,"w");
   fwrite($fp, $setProfil."\n");
   fclose($fp);
}
else
if (isset($cron_profil) && isset($CCCamWebInfo[$cron_profil]))
{
   $active_profie = $cron_profil;
}
else
if (file_exists($filename_profile))
{
   $filename_profile_data = file ($filename_profile);
   $active_profie = (int)(trim($filename_profile_data[0]));

   if (!isset($CCCamWebInfo[$active_profie]))
   {
      $active_profie = 0;
      $fp = @fopen($filename_profile,"w");
      fwrite($fp, $active_profie."\n");
      fclose($fp);
   }
}

//-------------------------------------
// CCCAM SERVER
//-------------------------------------

$cccam_host   = "";
$webinfo_port = "";
$webinfo_user = "";
$webinfo_pass = "";

if (isset($CCCamWebInfo[$active_profie]))
{
   $cccam_host = $CCCamWebInfo[$active_profie][0];

   $webinfo_port = "16001";
   $webinfo_user = "";
   $webinfo_pass = "";

   if (isset($CCCamWebInfo[$active_profie][1])) $webinfo_port      = $CCCamWebInfo[$active_profie][1];
   if (isset($CCCamWebInfo[$active_profie][1])) $webinfo_port      = $CCCamWebInfo[$active_profie][1];
   if (isset($CCCamWebInfo[$active_profie][2])) $webinfo_user      = $CCCamWebInfo[$active_profie][2];
   if (isset($CCCamWebInfo[$active_profie][3])) $webinfo_pass      = $CCCamWebInfo[$active_profie][3];
}

//-------------------------------------
// 
//-------------------------------------

$update_info = false;
$update_shares = false;
$update_servers = false;
$update_clients = false;
$update_activeclients = false;
$update_entitlements = false;

$skipUpdate = false;
$server_offline = false;

$str_empty = "0";

//-------------------------------------
// PARAMS
//-------------------------------------

$pagina = $_SERVER['PHP_SELF'];

$sort = "";             if(isset($_GET['sort']))         $sort = $_GET['sort'];
$provider = "";         if(isset($_GET['provider']))     $provider = $_GET['provider'];
$node = "";             if(isset($_GET['node']))         $node = $_GET['node'];
$server_nodeDns = "";   if(isset($_GET['nodeDns']))      $server_nodeDns = $_GET['nodeDns'];
$username = "";   		  if(isset($_GET['username']))     $username = $_GET['username'];
$server_checkPing = ""; if(isset($_GET['checkPing']))    $server_checkPing = $_GET['checkPing'];
$pingAll = "";          if(isset($_GET['pingAll']))      $pingAll = $_GET['pingAll'];

if(isset($_POST['nodeDns'])) $server_nodeDns = $_POST['nodeDns'];
$NotesToSave = "__NOTDEFINDED__"; if(isset($_POST['saveNotes']))    $NotesToSave = $_POST['saveNotes'];

//-------------------------------------
// PATHS & FILES
//-------------------------------------

$save_path        = $work_path.$cccam_host."/";
$global_save_path = $work_path."!global/";
$download_path    = $work_path.$cccam_host."/download/";
$notes_path       = $work_path.$cccam_host."/notes/";
//$online_path      = $work_path.$cccam_host."/online/";

if (!is_dir($global_save_path)) mkdir($global_save_path, 0777);
if ($cccam_host!="")
{
   if (!is_dir($save_path))        mkdir($save_path, 0777);
   if (!is_dir($download_path))    mkdir($download_path, 0777);
   if (!is_dir($notes_path))       mkdir($notes_path, 0777);
//   if (!is_dir($online_path))      mkdir($online_path, 0777);
}

$globalServersfile      = $global_save_path."servers.data";
$globalNodeIDfile       = $global_save_path."nodeid.data";
$ping_file              = $global_save_path."ping.data";

$update_log             = $save_path."update.log";
$country_file           = $save_path."country.data";
$pair_file              = $save_path."pair.data";
$ECM_file               = $save_path."ECM.data";
$online_file            = $save_path."online.data";
$usage_file             = $save_path."usage.data";

$usedProvidersFile      = "CCcam_used.providers";
$fakeProvidersFile      = "CCcam_fake.providers";
$countrycode_file       = "country.code";

$caminfo_update         = $download_path."caminfo.data";
$servers_update         = $download_path."servers.data";
$shares_update          = $download_path."shares.data";
$clients_update         = $download_path."clients.data";
$activeclients_update   = $download_path."activeclients.data";
$entitlements_update    = $download_path."entitlements.data";

$caminfo_file           = $save_path."caminfo.data";
$servers_file           = $save_path."servers.data";
$shares_file            = $save_path."shares.data";
$clients_file           = $save_path."clients.data";
$activeclients_file     = $save_path."activeclients.data";
$entitlements_file      = $save_path."entitlements.data";


if ($NotesToSave != "__NOTDEFINDED__")
{
   list($notessave_DNS, $notessave_PORT) = explode(":", $server_nodeDns);
   $notes_save_path   = $notes_path.$notessave_DNS.".".$notessave_PORT.".note";  
   $fp = @fopen($notes_save_path,"w");
   fwrite($fp, $NotesToSave);
   fclose($fp);
}



if (file_exists($countrycode_file)) $countrycode_data = file ($countrycode_file);
if (file_exists($country_file))     $country_data     = file ($country_file);

define('INT_SECOND', 1);
define('INT_MINUTE', 60);
define('INT_HOUR', 3600);
define('INT_DAY', 86400);
define('INT_WEEK', 604800);

//-------------------------------------
// FUNCTIONS
//___________________________________________________________________________________________________
function formula_usage($timp, $ecm)
{
	list($zile,$rest) =  explode("d ",$timp);
	list($ore,$minute,$secunde) =  explode(":",$rest);
	$timpconectat = $zile*86400 + $ore*3600 + $minute*60 + $secunde;
	
	$indexECM = ((int)((3600*$ecm)/($timpconectat)));
	 
	//if ($timpconectat<60) $indexECM = (int)($indexECM/2); 
	
	if ($ecm>0) $indexECM = $indexECM + 1;
	
	return $indexECM;
}
//___________________________________________________________________________________________________
function formatted_timediff($timediff)
{
	$weeks    = (int) intval($timediff / INT_WEEK);
	$timediff = (int) intval($timediff - (INT_WEEK * $weeks));
	$days     = (int) intval($timediff / INT_DAY);
	$timediff = (int) intval($timediff - (INT_DAY * $days));
	$hours    = (int) intval($timediff / INT_HOUR);
	$timediff = (int) intval($timediff - (INT_HOUR * $hours));
	$mins     = (int) intval($timediff / INT_MINUTE);
	$timediff = (int) intval($timediff - (INT_MINUTE * $mins));
	$sec      = (int) intval($timediff / INT_SECOND);
	$timediff = (int) intval($timediff - ($sec * INT_SECOND));
	
	$str = '';
	if ( $weeks )
	{
	  $str .= intval($weeks);
	  $str .= ($weeks > 1) ? ' weeks' : ' week';
	}
	
	if ( $days )
	{
	  $str .= ($str) ? ', ' : '';
	  $str .= intval($days);
	  $str .= ($days > 1) ? ' days' : ' day';
	}
	
	if ( $hours )
	{
	  $str .= ($str) ? ', ' : '';
	  $str .= intval($hours);
	  $str .= ($hours > 1) ? ' hours' : ' hour';
	}
	
	if ( $str == "" && $mins )
	{
	  $str .= ($str) ? ', ' : '';
	  $str .= intval($mins);
	  $str .= ($mins > 1) ? ' min' : ' min';
	}
	
	if ( $str == "" && $sec )
	{
	  $str .= ($str) ? ', ' : '';
	  $str .= intval($sec);
	  $str .= ($sec > 1) ? ' sec' : ' sec';
	}
	
	return $str;
}
//___________________________________________________________________________________________________
function get_formatted_timediff($then, $now = false)
{
    $now      = (!$now) ? time() : $now;
    $timediff = ($now - $then);
    $str = formatted_timediff($timediff);
    
    return $str;
}
//___________________________________________________________________________________________________
function filesuck ($dnsadd, $dnsport, $dnsuser, $dnspass, $dnspath, $filename="", $timeout=10 )
{
   $out  = "GET $dnspath HTTP/1.1\r\n";
   $out .= "Host: ".$dnsadd."t\r\n";
   $out .= "Connection: Close\r\n";
   $out .= "Authorization: Basic ".base64_encode($dnsuser.":".$dnspass)."\r\n";
   $out .= "\r\n";

   if (!$conex = @fsockopen($dnsadd, $dnsport, $errno, $errstr, $timeout))
       return 0;
   fwrite($conex, $out);
   $data = '';
   while (!feof($conex)) {
       $data .= fgets($conex, 512);
   }
   fclose($conex);
   $somecontent = $data;


   if ($filename=="")
   {
      return $somecontent;
   }
   else
   {
      if (file_exists($filename)) unlink($filename);
      touch ($filename);
      if (is_writable($filename))
      {
         if (!$handle = fopen($filename, 'a'))
         {
            echo "Cannot open file ($filename)";
            exit;
         }
         fwrite($handle, $somecontent);
         fclose($handle);
      }
      else {echo "The file $filename is not writable";}
   }
}
//___________________________________________________________________________________________________
function fstripos($string,$word)
{
   $retval = -1;
   for($i=0;$i<=strlen($string);$i++)
   {
       if (strtolower(substr($string,$i,strlen($word))) == strtolower($word))
       {
           $retval = $i;
       }
   }
   return $retval;
}
//___________________________________________________________________________________________________
function stripcontents($contents)
{
   $strip_contents = strip_tags($contents,'<BR><H2>');

   $pos=fstripos($strip_contents, "<H2>");
   if ($pos!=-1)
   {
      $strip_contents = substr($strip_contents, $pos);
      $strip_contents = str_replace("</H2>","</H2>\n",$strip_contents);
   }

   return $strip_contents;
}
//___________________________________________________________________________________________________
function stripdata($filename)
{
   $contents=file_get_contents($filename);
   $strip_contents = stripcontents($contents);

   $f=@fopen($filename,"w");
   if ($f)
   {
      fwrite($f,$strip_contents);
     fclose($f);
   }
}
//___________________________________________________________________________________________________
function loadUsageData()
{
   global $usage_file;
   global $UsageUsers;
   
   $usage_data = file ($usage_file);
   foreach ($usage_data as $currentline)
   {
      list($DNS, $TIME, $USAGE) = explode("|", $currentline);
      $DNS = trim($DNS);
      $USAGE = trim($USAGE);

      if (isset($TIME))
      {
     		$TIME = trim($TIME);
         $UsageUsers[$DNS]["time"] = $TIME;
         $UsageUsers[$DNS]["usage"] = $USAGE;
      }
   }
}
//___________________________________________________________________________________________________
function saveUsageData($clients_data)
{
	$saveTime = time();
	   
   global $usage_file;
   global $UsageUsers;
   
   loadUsageData();

   $modificat = false;
  
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
			
			$indexECM = formula_usage($ac_Connected, $ac_ECM);
			
			
			list($zile,$rest) =  explode("d ",$ac_Connected);
			list($ore,$minute,$secunde) =  explode(":",$rest);
			$minuteConectat = 1 + $zile*24*60 + $ore*60 + $minute;
			
			$timediff = 0;
			if ($UsageUsers[$ac_Username]["time"] != "")
				$timediff = ($saveTime - $UsageUsers[$ac_Username]["time"]);
				
			$minuteDiff  = (int) intval($timediff / INT_MINUTE);
			
			
			$SaveIndexECM = $UsageUsers[$ac_Username]["usage"];
			list($lastIndexEcm,$averageIndexEcm) = explode(".", $SaveIndexECM,2);
			
			if ($minuteConectat < $minuteDiff) // fac media
			{
				if (isset($averageIndexEcm) && $averageIndexEcm!="")
					$averageIndexEcm = (int)(( $lastIndexEcm + $averageIndexEcm*3)/4);
				else
				if (isset($lastIndexEcm) && $lastIndexEcm!="")
					$averageIndexEcm = $indexECM;
				
			}

			$lastIndexEcm = $indexECM;
			
			
			$UsageUsers[$ac_Username]["usage"]  = $lastIndexEcm.".".$averageIndexEcm;
			$UsageUsers[$ac_Username]["time"] = $saveTime;
         $modificat = true;
		}
	}

   if ($modificat == true)
   {
   	
      $fp = @fopen($usage_file,"w");
      
      fwrite($fp, "savetime|".$saveTime."|"."\n");
      foreach ($UsageUsers as $User => $Usage)
      {
      	if ($User != "savetime")
         	fwrite($fp, $User."|".$Usage["time"]."|".$Usage["usage"]."\n");
      }
      fclose($fp);
   }
}
//___________________________________________________________________________________________________
function VerificLog($DNS)
{
	global $OnlineServers;
	$online_history = $OnlineServers[$DNS]["log"];
	
	$logtime = explode(".", $online_history);
	
	$maxim = 40320; // 28 zile
	$tot = 0;
	$str = "";
	
	foreach ($logtime as $log)
   {
   	$v = $log;
   	$semn = "";
   	if (strstr($log,"-"))
   	{
   		$semn = "-";
   		$v = substr($log,1);
   	}
   		
   	if ($str!="") $str = $str.".";
   	$tot = $tot + $v;
  	
   	
   	if ($tot > $maxim) // am depasit
   	{
   		$ramaslog = $v - ($tot - $maxim);
   		$str = $str.$semn.$ramaslog;
   		break;
   	}
   	else
   	{
   		$str = $str.$log;
   	}
 	
	}
	
	if ($str != $online_history)
	{
		$OnlineServers[$DNS]["log"] = $str;
	}
}
//___________________________________________________________________________________________________
function saveOnlineData($servers_data)
{
	$saveTime = time();
	   
   global $online_file;
   global $OnlineServers;
   
   loadOnlineData();

   $modificat = false;
   
   $minuteConectatMaxim = 0;
	foreach ($servers_data as $currentline)
   {
      $inceput1 = substr($currentline,0,1);
      $inceput2 = substr($currentline,1,2);

      if ($inceput1 == "|" && $inceput2 != " H")
      {
         $server = explode("|", $currentline);
         $server_Host      = trim($server[1]);
         $server_Time      = trim($server[2]);
         if ($server_Host != "" && $server_Time != "")
         {
         	list($zile,$rest) =  explode("d ",$server_Time);
				list($ore,$minute,$secunde) =  explode(":",$rest);
				$minuteConectat = 1 + $zile*24*60 + $ore*60 + $minute;
				
				if ($minuteConectatMaxim < $minuteConectat && $minuteConectat < 1000000)
					$minuteConectatMaxim = $minuteConectat;
			}	
      }
   }
	
	if ($minuteConectatMaxim < 2)
		return;
		
   foreach ($servers_data as $currentline)
   {
      $inceput1 = substr($currentline,0,1);
      $inceput2 = substr($currentline,1,2);

      if ($inceput1 == "|" && $inceput2 != " H")
      {
         $server = explode("|", $currentline);
         $server_Host      = trim($server[1]);
         $server_Time      = trim($server[2]);
         
         $logtime = $OnlineServers[$server_Host]["log"];
         
         if ($server_Host != "" && $server_Time == "") // offline
         {
         	$timediff = 0;
				if ($OnlineServers[$server_Host]["time"] != "")
					$timediff = ($saveTime - $OnlineServers[$server_Host]["time"]);
				
			   $minuteOffline  = (int) intval($timediff / INT_MINUTE);
			   
			   if ($logtime!="")
			   {
	         	list($lastlogtime,$logtimenew) = explode(".", $logtime,2);
	         	if (isset($lastlogtime) && $lastlogtime!="")
				   {
				   	if ($lastlogtime > 0)
				   		$logtime = "-".$minuteOffline.".".$logtime;
				   	else
				   	if ($logtimenew=="")
				   		$logtime = "-".$minuteOffline;
				   	else
				   		$logtime = "-".$minuteOffline.".".$logtimenew;
				   }
				}
			   
            $OnlineServers[$server_Host]["log"]  = $logtime;
            $modificat = true;
      	}
      	else
         if ($server_Host != "" && $server_Time != "") // online
         {
         	list($zile,$rest) =  explode("d ",$server_Time);
				list($ore,$minute,$secunde) =  explode(":",$rest);
				$minuteConectat = 1 + $zile*24*60 + $ore*60 + $minute;
				
				if ($minuteConectat > 1000000)
					$minuteConectat = $minuteConectatMaxim;
				
				$timediff = 0;
				if ($OnlineServers[$server_Host]["time"] != "")
					$timediff = ($saveTime - $OnlineServers[$server_Host]["time"]);
				
			   $minuteLastSave  = (int) intval($timediff / INT_MINUTE);
		   
			    
			   if ($logtime=="")
			  		$logtime = $minuteConectat;
			  	else
			   {
			   	if ($minuteConectat > $minuteLastSave)
			      	$minuteConectatADD = $minuteLastSave;
			      else
			      	$minuteConectatADD = $minuteConectat;
			      
			      list($lastlogtime,$logtimenew) = explode(".", $logtime,2);
			      
			      if (isset($lastlogtime) && $lastlogtime!="")
			      {
			      	if ($lastlogtime > 0)
			      		$minuteConectat = $minuteConectatADD+$lastlogtime;
			      	else
			      	{
			      		$logtimenew = $logtime;
			      	}
			      }
			      
			      if ($logtimenew == "")
		      		$logtime = $minuteConectat;
		      	else
		      		$logtime = $minuteConectat.".".$logtimenew;
			   }

				$OnlineServers[$server_Host]["time"] = $saveTime;
            $OnlineServers[$server_Host]["log"]  = $logtime;
            $modificat = true;
         }
         
         
         VerificLog($server_Host);
      }
   }

   if ($modificat == true)
   {
   	
      $fp = @fopen($online_file,"w");
      
      fwrite($fp, "savetime|".$saveTime."|"."\n");
      foreach ($OnlineServers as $OnlineHost => $OnlineTime)
      {
      	if ($OnlineHost != "savetime")
         	fwrite($fp, $OnlineHost."|".$OnlineTime["time"]."|".$OnlineTime["log"]."\n");
      }
      fclose($fp);
   }
}
//___________________________________________________________________________________________________
function procentOnline($DNS)
{
	global $OnlineServers;
	
	if (!isset($OnlineServers))
		loadOnlineData();
		
	// online history
   //$online_saveTime = $OnlineServers["savetime"]["time"];
   //$online_lastseen = $OnlineServers[$DNS]["time"];
   $online_history  = $OnlineServers[$DNS]["log"];
   
   $minute_off = 0;
   $minute_on = 0;
   /*
   if ($online_saveTime != $online_lastseen)
   {
   	$timediff = ($online_saveTime - $online_lastseen);
	   $minute_off  = (int) intval($timediff / INT_MINUTE);
   }
   */
   
   $logtime = explode(".", $online_history);
   foreach ($logtime as $log)
   {
   	if (strstr($log,"-"))
   	{
   		$minute_off = $minute_off + substr($log,1);
   	}
   	else 	
   	{
   		$minute_on = $minute_on + $log;
   	}
	}
	
   
   $procent = round(($minute_on/($minute_off +$minute_on) *100),1);
	return $procent;
}
//___________________________________________________________________________________________________
function loadOnlineData()
{
   global $online_file;
   global $OnlineServers;
   
   $online_data = file ($online_file);
   foreach ($online_data as $currentline)
   {
      list($DNS, $TIME, $LOG) = explode("|", $currentline);
      $DNS = trim($DNS);
      $LOG = trim($LOG);

      if (isset($TIME))
      {
     		$TIME = trim($TIME);
         $OnlineServers[$DNS]["time"] = $TIME;
         $OnlineServers[$DNS]["log"] = $LOG;
      }
   }
}
//___________________________________________________________________________________________________
function checkFile($File)
{
   if (file_exists($File))
   {
      $servers_data = file ($File);

      if (count($servers_data) <1)
      {
         echo "<FONT COLOR=red>Server is down or updating ... Please try again later !</FONT>";
         exit;
      }

      if (count($servers_data) <6)
      {
         echo "<FONT COLOR=red>".$servers_data[0]."</FONT>";
         exit;
      }

   }
   else
   {
      exit;
   }
}
//___________________________________________________________________________________________________
function pingResultColor($ping,$showping = 0)
{
	$textPing = "";
	if ($showping ==1 && $ping != "" && $ping>0)
		$textPing = "<FONT COLOR=white>".$ping."</FONT><FONT COLOR=gray>ms </FONT>";
	
   if     ($ping>300)   $textPing = $textPing."<FONT color=red>[very bad]</FONT>";
   elseif ($ping>200)   $textPing = $textPing."<FONT color=\"#C11B17\">[very slow]</FONT>";
   elseif ($ping>130)   $textPing = $textPing."<FONT color=orange>[slow]</FONT>";
   elseif ($ping>80)    $textPing = $textPing."<FONT color=yellow>[good]</FONT>";
   elseif ($ping>40)    $textPing = $textPing."<FONT color=green>[very good]</FONT>";
   else                 $textPing = $textPing."<FONT color=\"#5EFB6E\">[excellent]</FONT>";
   if     ($ping == 0)  $textPing = "not saved";
 
 	return $textPing;  
}
//___________________________________________________________________________________________________
function procentColor($procent)
{
   $ret = "";
   
   if      ($procent <20) $ret = "<FONT COLOR=red>".$procent."%</FONT>";
   else if ($procent <50) $ret = "<FONT COLOR=orange>".$procent."%</FONT>";
   else if ($procent <80) $ret = "<FONT COLOR=yellow>".$procent."%</FONT>";
   else if ($procent <90) $ret = "<FONT COLOR=green>".$procent."%</FONT>";
   else                   $ret = "<FONT COLOR=#10f180>".$procent."%</FONT>";
   
   return $ret;
}
//___________________________________________________________________________________________________
function pingColor($pingSalvat)
{
   $ret = "";
   if       ($pingSalvat[0] == "") $ret = "&nbsp;?&nbsp;";
   else if  ($pingSalvat[0]<40)    $ret = "<FONT COLOR=\"#5EFB6E\">".$pingSalvat[0]."</FONT>";
   else if  ($pingSalvat[0]<80)    $ret = "<FONT COLOR=green>".$pingSalvat[0]."</FONT>";
   else if  ($pingSalvat[0]<130)   $ret = "<FONT COLOR=yellow>".$pingSalvat[0]."</FONT>";
   else if  ($pingSalvat[0]<200)   $ret = "<FONT COLOR=orange>".$pingSalvat[0]."</FONT>";
   else if  ($pingSalvat[0]<300)   $ret = "<FONT COLOR=\"#C11B17\">".$pingSalvat[0]."</FONT>";
   else                            $ret = "<FONT COLOR=red>".$pingSalvat[0]."</FONT>";
   
   if (isset($pingSalvat[2]))
   {
   	$diffPing = $pingSalvat[0] - $pingSalvat[2];
   	$diffMargin = (int)($pingSalvat[2] * 50/100);
   	if ($diffMargin < 50) $diffMargin  = 50;
   	if ($diffPing > $diffMargin ) $ret = "<FONT COLOR=red><B>! </B></FONT>".$ret."&nbsp;&nbsp;";
   }
		
   return $ret;
}    
//___________________________________________________________________________________________________
function pingColorBest($pingSalvat)
{
   $ret = "";
   if       ($pingSalvat[0] == "") $ret = "&nbsp;?&nbsp;";
   else if  ($pingSalvat[2]<40)  $ret = "<FONT COLOR=\"#5EFB6E\">".$pingSalvat[2]."</FONT>";
   else if  ($pingSalvat[2]<80) 	$ret = "<FONT COLOR=green>".$pingSalvat[2]."</FONT>";
   else if  ($pingSalvat[2]<130) $ret = "<FONT COLOR=yellow>".$pingSalvat[2]."</FONT>";
   else if  ($pingSalvat[2]<200) $ret = "<FONT COLOR=orange>".$pingSalvat[2]."</FONT>";
   else if  ($pingSalvat[2]<300) $ret = "<FONT COLOR=\"#C11B17\">".$pingSalvat[2]."</FONT>";
   else                          $ret = "<FONT COLOR=red>".$pingSalvat[2]."</FONT>";
   
   return $ret;
}                      
//___________________________________________________________________________________________________
function format1($titlu,$v1="",$v2=-1)
{
       $ret = "";
       
       $ret = "".$titlu." : ";
       $ret.="<B><FONT COLOR=white>".$v1."</FONT></B>";
       if ($v2>0)
       {
               $procent = (int)($v1/$v2 *100);
               $procentAfisat = procentColor($procent);
               $ret.=" / ".$v2." (<B>".$procentAfisat."</B>)";
       }
       $ret.="<BR>";
       
       echo $ret;
}
//___________________________________________________________________________________________________
function sterg0($text)
{
       $inceput = substr($text,0,1);
       while($inceput == "0" && strlen($text)>1)
       {
               $text = substr($text,1,strlen($text)-1);
               $inceput = substr($text,0,1);   
       }
       return $text;
}
//___________________________________________________________________________________________________
function adaug0($text,$count)
{
       if (strlen($text) == 0)
               return $text;
       /*
       $dif = $count - strlen($text);
       
       echo $dif." ";
 for ($k = 1; $k <= $dif; $k++) 
               $ret = "0".$ret;
       */
       
       while( strlen($text) < $count)
       {
               $text = "0".$text;
       }
       return $text;
}
//___________________________________________________________________________________________________
function linkNod($node,$text,$clasa="",$cuQuerry = true)
{
       global $pagina;
       
       if ($clasa!="")
               $ret = "<A CLASS=\"".$clasa."\" HREF=".$pagina."?";
       else
               $ret = "<A HREF=".$pagina."?";
               
       $node = sterg0($node);
       if ($cuQuerry)
               $ret = $ret.$_SERVER['QUERY_STRING'];
       
       $ret = $ret."&node=".$node.">".$text."</A>";
       return $ret;
}
//___________________________________________________________________________________________________
function linkProvider($provider,$text,$clasa="",$cuQuerry = false)
{
       global $pagina;
       if ($clasa!="")
               $ret = "<A CLASS=\"".$clasa."\" HREF=".$pagina."?";
       else
               $ret = "<A HREF=".$pagina."?";
                               
       if ($_SERVER['QUERY_STRING'] == "" || !$cuQuerry)
       {
               $ret = $ret."provider=".$provider.">".$text."</A>";
       }
       else
               $ret = $ret.$_SERVER['QUERY_STRING']."&provider=".$provider.">".$text."</A>";
       
       return $ret;
}
//___________________________________________________________________________________________________
function providerID($caid,$prov,$link=true,$clasa="Node_Provider",$cuQuerry = false)
{
       $caid = sterg0($caid);
       $prov_seek = $caid.":".$prov;   
       $provUsed_seek = strtolower(adaug0($caid,4).":".adaug0($prov,6));
       
       global $usedProviders;
       if (!isset($usedProviders)) LoadUsedProviders();
       global $fakeProviders;
       if (!isset($fakeProviders)) LoadFakeProviders();
               
               
       $text_provider = "";
       if (IsUsedProvider($provUsed_seek)) 
               $text_provider = "<font color=#1F0FA00>".$prov." </font>";
       else
       if (IsFakeProvider($provUsed_seek)) 
               $text_provider = "<font color=red>".$prov." </font>";
       else
               $text_provider = "<font color=#F0FA00>".$prov." </font>";
               
       global $CCcam_providersShort;
       
       $caidcolor = "white";
       
       if (strstr($provUsed_seek,"0501:")) $caidcolor = "red";
       if (strstr($provUsed_seek,"0502:")) $caidcolor = "red";
       
       if ($link == true)
               $caidLink = linkProvider($caid,"<font color=$caidcolor>".$caid." : </font>",$clasa,$cuQuerry);
       else
               $caidLink = "<font color=$caidcolor>".$caid." : </font>";
       
       $idRecunoscut = "";
       if (isset($CCcam_providersShort[$prov_seek]))
       {
               if ($link == true)
                       $idRecunoscut = linkProvider($caid.":".$prov, $text_provider.$CCcam_providersShort[$prov_seek] , $clasa, $cuQuerry);
               else
                       $idRecunoscut = $text_provider.$CCcam_providersShort[$prov_seek];
       }
               
       $id = "<font color=orange>".$idRecunoscut."</font>";
       
       if ($idRecunoscut == "")
       {
               $providers = explode(",", $prov);
               
               foreach ($providers as $provider)
               {
                       $provider = sterg0($provider);
                       
                       $text_provider = "";
                       $provUsed_seek = strtolower(adaug0($caid,4).":".adaug0($provider,6));
                       
                       if (IsUsedProvider($provUsed_seek)) 
                               $text_provider = linkProvider($caid.":".$provider, "<font color=#1F0FA00>".$provider." </font>", $clasa, $cuQuerry);
                       else
                       if (IsFakeProvider($provUsed_seek)) 
                               $text_provider = linkProvider($caid.":".$provider, "<font color=red>".$provider." </font>", $clasa, $cuQuerry);
                       else
                               $text_provider = linkProvider($caid.":".$provider, "<font color=#F0FA00>".$provider." </font>", $clasa, $cuQuerry);

                       $idTemp = "";
                       if (isset($CCcam_providersShort[$caid.":".$provider]))
                       {
                               if ($link == true)
                                       $idTemp = $text_provider.linkProvider($caid.":".$provider, $CCcam_providersShort[$caid.":".$provider], $clasa, $cuQuerry);
                               else
                                       $idTemp = $text_provider.$CCcam_providersShort[$caid.":".$provider];
                       }
                               
                       if ($idTemp == "")  
                       {
                               if ($link == true)
                                       $idTemp = linkProvider($caid.":".$provider, $text_provider, $clasa, $cuQuerry);
                               else
                                       $idTemp = $text_provider;
                       }

                       $idents[$idTemp] = $provider ;
               }

               $k=0;
               foreach ($idents as $pName => $pID)
               {
                       if ($k!=0) $id = $id."<font color=white><B> | </B></font>";
                       $id = $id."<font color=orange>".$pName."</font>";
                       $k++;
                       
                       
               }
       }
       
       $id = $caidLink.$id;

       return $id;
}
//___________________________________________________________________________________________________
function IsUsedProvider($provider)
{
	global $usedProviders;
  if (!isset($usedProviders)) LoadUsedProviders();

	if (isset($usedProviders[$provider])) 
  	return true;
               
  return false;
}
//___________________________________________________________________________________________________
function IsFakeProvider($provider)
{
	
  global $fakeProviders;
	if (!isset($fakeProviders)) LoadFakeProviders();
  
  if (isset($fakeProviders[$provider])) 
     return true;
               
  if (strstr($provider,"0501:")) return true;
  if (strstr($provider,"0502:")) return true;
       
   return false;
}
//___________________________________________________________________________________________________
function LoadFakeProviders()
{
	global $fakeProviders;
	global $fakeProvidersFile;
	
	$globalProviders_data = file ($fakeProvidersFile);
	foreach ($globalProviders_data as $uProvider) 
	{
		$uProvider = strtolower(trim($uProvider));
		$fakeProviders[$uProvider] = 1;
	}
}

//___________________________________________________________________________________________________
function LoadUsedProviders()
{
	global $usedProviders;
	global $usedProvidersFile;
	$globalProviders_data = file ($usedProvidersFile);
	foreach ($globalProviders_data as $uProvider) 
	{
		$uProvider = explode(" ", strtolower(trim($uProvider)));
	   $usedProviders[$uProvider[0]] = 1;
	}
}
//___________________________________________________________________________________________________
function UpdateHitProviders()
{
       
	global $usedProviders;
	global $usedProvidersFile;
	LoadUsedProviders();
	
	global $servers_file;
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
				
				if (!isset($ecm_hit[$hit_caid]))        
				$ecm_hit[$hit_caid] = 0;
				
				$ecm_hit[$hit_caid] += $hit_ecmOK;
			
			}
		}
	}
	
	foreach ($ecm_hit as $hit_caid => $hit_info)
	{
		if ($hit_info > 0 )
		{
			$hit_provider = explode(":",$hit_caid);
			$hit_caid = adaug0($hit_provider[0],4).":".adaug0($hit_provider[1],6);
			$usedProviders[$hit_caid] = $hit_info;
		}
	}
	
	
	
	ksort($usedProviders);
	$fp = @fopen($usedProvidersFile,"w");
	global $CCcam_providers;
	foreach ($usedProviders as $S_CAID => $data) 
	{
		if (!IsFakeProvider($S_CAID))
		{
			fwrite($fp, $S_CAID." ".$CCcam_providers[$S_CAID]."\n");
		}
	}
	fclose($fp);
       
}
//___________________________________________________________________________________________________
function UpdateClientsCountryIP()
{
       global $clients_file;
       $clients_data = file ($clients_file);
       foreach ($clients_data as $currentline) 
       {
               $inceput1 = substr($currentline,0,1);
               $inceput2 = substr($currentline,1,2);
               if (strstr($currentline,"| Shareinfo")) break;  

               if ($inceput1 == "|" && $inceput2 != " U")
               {
                       $active_client          = explode("|", $currentline);
                       $ac_Username                    = trim($active_client[1]);
                       $ac_IP                                          = trim($active_client[2]);
                       tara($ac_IP,$ac_Username);

               }
       }
}
//___________________________________________________________________________________________________
function initPairs()
{   
   global $pair_file;
   global $SERVER_PAIR;
   
   if (!isset($SERVER_PAIR) && file_exists($pair_file))
   {
      $pair_data = file ($pair_file);
      foreach ($pair_data as $currentline) 
      {  
         list($S_SERVER, $S_CLIENTS) = explode("/", $currentline);
         $S_SERVER = trim($S_SERVER);
         $S_CLIENTS = trim($S_CLIENTS);
         if (strstr($S_CLIENTS,";"))
         {
            $S_CLIENTS = explode(";", $S_CLIENTS); 
            foreach ($S_CLIENTS as $S_CLIENT) 
               $SERVER_PAIR[$S_SERVER][] = $S_CLIENT;
         }
         else
         {
            $SERVER_PAIR[$S_SERVER][] = $S_CLIENTS;
         }
      }
   }
}
//___________________________________________________________________________________________________
function initClients()
{   
   global $clients_file;
   global $clientConectat;
   
   if (!isset($clientConectat) && file_exists($clients_file))
   {
      $clients_data = file ($clients_file);
      foreach ($clients_data as $currentline) 
      {
         $inceput1 = substr($currentline,0,1);
         $inceput2 = substr($currentline,1,2);
         if (strstr($currentline,"| Shareinfo")) break;  
   
         if ($inceput1 == "|" && $inceput2 != " U")
         {
            $active_client       = explode("|", $currentline);
            $ac_Username         = trim($active_client[1]);
            $ac_IP               = trim($active_client[2]);
            $ac_Connected        = trim($active_client[3]);
            $ac_Idle             = trim($active_client[4]);
            $ac_ECM              = trim($active_client[5]);
            $ac_EMM              = trim($active_client[6]);
            $ac_Version          = trim($active_client[7]);
            $ac_LastShare        = trim($active_client[8]);
            
            $ac_EcmTime = "";
            if (isset($active_client[9])) 
               $ac_EcmTime = trim($active_client[9]);
            
            $clientConectat[$ac_Username]["Info"] = array ($ac_IP,$ac_Connected,$ac_Idle,$ac_ECM,$ac_EMM,$ac_Version,$ac_LastShare,$ac_EcmTime);
            tara($ac_IP,$ac_Username);
            
         }
      }
   }
}
//___________________________________________________________________________________________________
function getHostIP($host_DNS)
{
   $eDNSIP = ereg("^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$",$host_DNS);
   
   if ($eDNSIP)
      $host_IP  = $host_DNS;
   else
   {
      $host_IP = gethostbyname(trim($host_DNS));
      if ($host_IP == $host_DNS )
         $host_IP = "unknown";
   }
   return $host_IP;
}
//___________________________________________________________________________________________________
function UpdateServersCountryIP()
{
   loadGlobalServers();
   loadECMServers();

   global $servers_file;
   global $globalServers;
   global $Server_Conectat_Local;
   
   global $SERVER_PAIR;
   global $clientConectat;
   initPairs();
   initClients();

   $servers_data = file ($servers_file);
   foreach ($servers_data as $currentline)
   {
      $inceput1 = substr($currentline,0,1);
      $inceput2 = substr($currentline,1,2);

      if ($inceput1 == "|" && $inceput2 != " H")
      {
         $server = explode("|", $currentline);
         $server_Host      = trim($server[1]);
         $server_Time      = trim($server[2]);
         $server_Type      = trim($server[3]);
         $server_Ver       = trim($server[4]);
         $server_Nodeid    = trim($server[5]);
         $server_Cards     = trim($server[6]);
         $server_Idents    = trim($server[7]);

         if ($server_Host != "")
         {
            list($host_DNS, $host_PORT) = explode(":", $server_Host);
            $taraSaved = taraNameSaved($server_Host);
            
            $checkIP = true;
            if (isset($SERVER_PAIR[$server_Host]))
            {
               
               foreach ($SERVER_PAIR[$server_Host] as $Client_afisat) 
               {
                  $IPdiferit = false;
                  $IPClient = trim($clientConectat[$Client_afisat]["Info"][0]);
                  $IPServer = trim($taraSaved[1]);
                  if ($IPClient == $IPServer)
                  {
                     $checkIP = false;
                     break;
                  }
               }
            }
            else
               $checkIP = false;
               
            if ($checkIP == true || $taraSaved[0]  == "" || $taraSaved[1] == "")
            {
               $host_IP = getHostIP($host_DNS);
               tara($host_IP,$server_Host);
               $globalServers[$server_Host] = array( $server_Nodeid,$host_IP,$taraSaved[0]["tara"]);
            }
            else
            {
               $globalServers[$server_Host] = array( $server_Nodeid,$taraSaved[1],$taraSaved[0]["tara"]);
            }

            $Server_Conectat_Local[$server_Nodeid] = $server_Host;

            $lastServer = $server_Host;
         }
      }
   }

   saveGlobalServers();
   saveGlobalNodeID();
}
//___________________________________________________________________________________________________
function saveGlobalServers()
{
   global $globalServers;
   global $globalServersfile;

   ksort($globalServers);
   $fp = @fopen($globalServersfile,"w");
   foreach ($globalServers as $S_NAME => $S_INFO)
   {
      if (strstr($S_INFO[1],"192.168.") || strstr($S_INFO[1],"127.0.0.1")) continue;
      fwrite($fp, $S_NAME."|".$S_INFO[0]."|".$S_INFO[1]."|".$S_INFO[2]."\n");
   }
   fclose($fp);
}
//___________________________________________________________________________________________________
function saveGlobalNodeID()
{
   global $globalNodeIDfile;
   global $Server_Conectat_Local;
   global $Server_Conectat_Global;
   global $globalNodeIDNames;

   $globalNodeID_data = file ($globalNodeIDfile);
   foreach ($globalNodeID_data as $currentline)
   {
      list($S_node, $S_host) = explode("|", $currentline);
      $S_node = trim($S_node);
      $S_host = trim($S_host);

      $globalNodeIDNames[$S_node] = $S_host;
   }

   foreach ($Server_Conectat_Global as $S_node => $S_host)
      if ($S_node!="") $globalNodeIDNames[$S_node] = $S_host;

   foreach ($Server_Conectat_Local as $S_node => $S_host)
      if ($S_node!="") $globalNodeIDNames[$S_node] = $S_host;

   $fp = @fopen($globalNodeIDfile,"w");
   foreach ($globalNodeIDNames as $S_node => $S_host)
      fwrite($fp, $S_node."|".$S_host."\n");
   fclose($fp);
}

//___________________________________________________________________________________________________
function UpdateServersECM()
{
   global $ECMservers;
   global $servers_file;

   $servers_data = file ($servers_file);
   $lastServer = "";
   foreach ($servers_data as $currentline)
   {
      $inceput1 = substr($currentline,0,1);
      $inceput2 = substr($currentline,1,2);

      if ($inceput1 == "|" && $inceput2 != " H")
      {
         $server = explode("|", $currentline);
         $server_Host      = trim($server[1]);
         $server_Time      = trim($server[2]);
         $server_Type      = trim($server[3]);
         $server_Ver       = trim($server[4]);
         $server_Nodeid    = trim($server[5]);
         $server_Cards     = trim($server[6]);
         $server_Idents    = trim($server[7]);

         if ($server_Host != "")
         {
            $lastServer = $server_Host;
            $ECMservers[$server_Host]["Info"] = array ($server_Time,$server_Type,$server_Ver,$server_Nodeid,$server_Cards);  
         }
         
         $hit_array = explode(" ",$server_Idents);
         if ($hit_array[0] != "")
         {
            $hit_provider  = explode(":",$hit_array[1]);
            $hit_exact     = explode("(",$hit_array[2]);
            $hit_exact2    = explode(")",$hit_exact[1]);

            $hit_ecm    = $hit_exact[0];
            $hit_ecmOK  = $hit_exact2[0];

            if (!isset($ECMservers[$lastServer]["Info"]["ECM_NOW"]["ECM"]))
               $ECMservers[$lastServer]["Info"]["ECM_NOW"]["ECM"] = 0;
            if (!isset($ECMservers[$lastServer]["Info"]["ECM_NOW"]["ECMOK"]))
               $ECMservers[$lastServer]["Info"]["ECM_NOW"]["ECMOK"] = 0;

            $ECMservers[$lastServer]["Info"]["ECM_NOW"]["ECM"]+= $hit_ecm;
            $ECMservers[$lastServer]["Info"]["ECM_NOW"]["ECMOK"]+= $hit_ecmOK;
         }
      }
   }

   loadECMServers();
   $modificat = 0;
   foreach ($ECMservers as $ECMserver => $ECMData)
   if (isset($ECMservers[$ECMserver]["Info"]["ECM_NOW"]))
   {
      $ecm_salvat_ecmok = $ECMservers[$ECMserver]["Info"]["ECM_SAVED"]["ECMOK"];
      $ecm_salvat_ecm   = $ECMservers[$ECMserver]["Info"]["ECM_SAVED"]["ECM"];
      $ecm_now_ecmok    = $ECMservers[$ECMserver]["Info"]["ECM_NOW"]["ECMOK"];
      $ecm_now_ecm      = $ECMservers[$ECMserver]["Info"]["ECM_NOW"]["ECM"];
      
      if ($ecm_now_ecmok != "" || $ecm_now_ecm !="")
      {
         if ($ecm_now_ecm > 500 || $ecm_salvat_ecmok == "" || $ecm_now_ecmok > $ecm_salvat_ecmok)
         {
            $ECMservers[$ECMserver]["Info"]["ECM_SAVED"]["ECM"]   = $ecm_now_ecm;
            $ECMservers[$ECMserver]["Info"]["ECM_SAVED"]["ECMOK"] = $ecm_now_ecmok;

            $modificat = 1;
         }
         else
         if ($ecm_salvat_ecmok == $ecm_now_ecmok && $ecm_now_ecm > $ecm_salvat_ecm)
         {
            $ECMservers[$ECMserver]["Info"]["ECM_SAVED"]["ECM"]   = $ecm_now_ecm;
            $ECMservers[$ECMserver]["Info"]["ECM_SAVED"]["ECMOK"] = $ecm_now_ecmok;

            $modificat = 1;
         }
      }
   }

   if ($modificat == 1)
      saveECMServers();
}
//___________________________________________________________________________________________________
function saveECMServers()
{
   global $ECM_file;
   global $ECMservers;

   $fp = @fopen($ECM_file,"w");
   foreach ($ECMservers as $ECMserver => $ECMData)
   {
      fwrite($fp, $ECMserver."|".$ECMData["Info"]["ECM_SAVED"]["ECM"]."|".$ECMData["Info"]["ECM_SAVED"]["ECMOK"]."\n");
   }
   fclose($fp);
}
//___________________________________________________________________________________________________
function loadECMServers($all = false)
{
   global $ECM_file;
   global $ECMservers;


   $ECM_data = file ($ECM_file);
   foreach ($ECM_data as $currentline)
   {
      list($DNS_SAVED, $ECM_SAVED, $ECMOK_SAVED) = explode("|", $currentline);
      $DNS_SAVED     = trim($DNS_SAVED);
      $ECM_SAVED     = trim($ECM_SAVED);
      $ECMOK_SAVED   = trim($ECMOK_SAVED);

      if (isset($ECMservers[$DNS_SAVED]) || $all == true)
      {
          $ECMservers[$DNS_SAVED]["Info"]["ECM_SAVED"]["ECM"]     = $ECM_SAVED;
          $ECMservers[$DNS_SAVED]["Info"]["ECM_SAVED"]["ECMOK"]   = $ECMOK_SAVED;
      }
   }
}
//___________________________________________________________________________________________________
function loadGlobalServers()
{
   global $globalServers;
   global $globalServersfile;
   global $Server_Conectat_Global;

   $globalServers_data = file ($globalServersfile);
   foreach ($globalServers_data as $currentline)
   {
      list($DNS_SAVED, $NODE_SAVED, $IP_SAVED, $TARA_SAVED) = explode("|", $currentline);
      $DNS_SAVED  = trim($DNS_SAVED);
      $NODE_SAVED = trim($NODE_SAVED);
      $IP_SAVED   = trim($IP_SAVED);
      $TARA_SAVED = trim($TARA_SAVED);
      $globalServers[$DNS_SAVED] = array( $NODE_SAVED,$IP_SAVED,$TARA_SAVED);

      if ($DNS_SAVED !="")
         $Server_Conectat_Global[$NODE_SAVED] = $DNS_SAVED;
   }
}
        
function loadLocalServers()
{
   global $servers_file;
   global $Server_Conectat_Local;

   $servers_data = file ($servers_file);
   foreach ($servers_data as $currentline)
   {
      $inceput1 = substr($currentline,0,1);
      $inceput2 = substr($currentline,1,2);

      if ($inceput1 == "|" && $inceput2 != " H")
      {
         $server = explode("|", $currentline);
         $server_Host    = trim($server[1]);
         $server_Nodeid = trim($server[5]);
         if ($server_Host != "")
         {
            $Server_Conectat_Local[$server_Nodeid] = $server_Host;
         }
      }
   }
}
function loadServersHosts()
{
   global $servers_file;
   global $ServerHost_Conectat;

   $servers_data = file ($servers_file);
   foreach ($servers_data as $currentline)
   {
      $inceput1 = substr($currentline,0,1);
      $inceput2 = substr($currentline,1,2);

      if ($inceput1 == "|" && $inceput2 != " H")
      {
         $server = explode("|", $currentline);
         $server_Host    = trim($server[1]);
         $server_Time   = trim($server[2]);
         if ($server_Host != "")
         {
           $ServerHost_Conectat[$server_Host] = $server_Time;
         }
      }
   }
}
//___________________________________________________________________________________________________
function nodeIdName($nodeid)
{
	global $Server_Conectat_Global;
	global $Server_Conectat_Local;
	
	if (!isset($Server_Conectat_Global)) loadGlobalServers();
	if (!isset($Server_Conectat_Local)) loadLocalServers();
	
	$node = explode("_",$nodeid);   
	
	$ret = $nodeid;
	if (isset($Server_Conectat_Local[$node[0]]))
	{
		$Server_Host = $Server_Conectat_Local[$node[0]];
		$ret = $Server_Host."_".$node[1];
	}
	else
	if (isset($Server_Conectat_Global[$node[0]]))   
	{
		$Server_Host = $Server_Conectat_Global[$node[0]];
		$ret = "[".$Server_Host."]_".$node[1];
	}
	return $ret;

}
//___________________________________________________________________________________________________
function pingDomain($domain,$port,$timeout=5)
{
	global $LastPingError;
   $starttime = microtime(true);
   $conex      = fsockopen ($domain, $port, $errno, $errstr, $timeout);
   $stoptime = microtime(true);
   $status    = 1;
   
   

   if (!$conex)
   {
      $status = -1;  // Site is down
      $LastPingError = $errstr;
   }
   else
   {
		fclose($conex);
      
      $status = ($stoptime - $starttime) * 1000;
      $status = floor($status);
      
      if ($status == 0) $status = 1;
   }

   return $status;
}
//___________________________________________________________________________________________________
function SendMessage($HOST)
{
	return $OUT;
}
//___________________________________________________________________________________________________
function SavedPing($NAME)
{
	global $ping_file;
	
	$ping_data = file ($ping_file);
	foreach ($ping_data as $currentline) 
	{
		list($S_NAME,$S_PING, $S_COUNT,$S_BEST) = explode("|", $currentline);
		$S_NAME  = trim($S_NAME);
		$S_PING  = trim($S_PING);
		$S_COUNT = trim($S_COUNT);
		$S_BEST  = trim($S_BEST);
		
		if ($S_BEST == "") $S_BEST = $S_PING;
		
		if ($S_NAME == $NAME)
			return array($S_PING,$S_COUNT,$S_BEST);
	}
	
	return array("","");
}
//___________________________________________________________________________________________________
function SavePing($NAME,$PING)
{
	global $ping_file;
	$NAME = trim($NAME);
	$PING = trim($PING);
	
	$ping_data = file ($ping_file);
	foreach ($ping_data as $currentline) 
	{
		list($S_NAME, $S_PING, $S_COUNT,$S_BEST) = explode("|", $currentline);
		$S_NAME  = trim($S_NAME);
		$S_PING  = trim($S_PING);
		$S_COUNT = trim($S_COUNT);
		$S_BEST  = trim($S_BEST);
		
		$ping_file_data[$S_NAME] = array ($S_PING,$S_COUNT,$S_BEST);
	}
	
	$OLD_PING  = 0;if (isset($ping_file_data[$NAME][0])) $OLD_PING  = (int)$ping_file_data[$NAME][0];               
	$OLD_COUNT = 0;if (isset($ping_file_data[$NAME][1])) $OLD_COUNT = (int)$ping_file_data[$NAME][1];
	$OLD_BEST  = 0;if (isset($ping_file_data[$NAME][2])) $OLD_BEST  = (int)$ping_file_data[$NAME][2];
	
	$NEW_COUNT = $OLD_COUNT+1;
	if ($NEW_COUNT > 31) 
	{
	   $NEW_COUNT = 31;
	}
	
	$NEW_PING =$PING;
	
	if ($OLD_PING != 0)  
	{
      $NEW_PING_FACTOR = 1;
      if ($NEW_PING > $OLD_PING) $NEW_PING_FACTOR = 3;

      $NEW_PING = (int)( ($PING*$NEW_PING_FACTOR + $OLD_PING*($OLD_COUNT)) / ($OLD_COUNT+$NEW_PING_FACTOR));
	}
	
	$NEW_BEST = $OLD_BEST;
	if ($OLD_BEST == 0 || $PING < $OLD_BEST)
		$NEW_BEST = $PING;
		
	if ($NEW_PING < $NEW_BEST)
		$NEW_BEST = $NEW_PING;
	      
	$ping_file_data[$NAME] = array ($NEW_PING,$NEW_COUNT,$NEW_BEST);
	
	$fp = @fopen($ping_file,"w");
	foreach ($ping_file_data as $S_NAME => $S_INFO) 
	{
		fwrite($fp, $S_NAME."|".$S_INFO[0]."|".$S_INFO[1]."|".$S_INFO[2]."\n");
	}
	fclose($fp);
       
}
//___________________________________________________________________________________________________
function whois($IP)
{
	$SERVER = "whois.ripe.net";
	
	@$CON=fsockopen($SERVER,43, $errno, $errstr, 10);
	if (!$CON) 
	{
	return "";
	}
	@fputs($CON,$IP."\r\n");
	unset($OUT);
	
	while(!feof($CON))
	{
		$OUT.=fread($CON,1000);
	}
	if (!(strpos($OUT,"ERROR")===false && strpos($OUT,"NOT FOUND")===false && strpos($OUT,"No data found")===false && strpos($OUT,"No match for")===false))
		$OUT="?";
	else
	{
		foreach($OUT as $linie)
	   	echo $linie."<BR>";
	}
	return $OUT;
}
//___________________________________________________________________________________________________
function clientIP($IP,$exclude="")
{
   global $country_data;
   global $pagina;
   
   $exclude = trim($exclude);
   $CLIENTI="";
   $i=0;
   foreach ($country_data as $currentline) 
   {
      list($IP_SAVED, $TARA_CODE, $CLIENT) = explode("|", $currentline);
      $IP_SAVED = trim($IP_SAVED);
      $TARA_CODE = trim($TARA_CODE);
      $CLIENT = trim($CLIENT);
      if ($IP == $IP_SAVED && !strstr($CLIENT,":") )
      {
         if ($exclude != $CLIENT)
         {
            if ($i==0)  $CLIENTI = $CLIENTI." "."<A HREF=".$pagina."?username=".$CLIENT.">".$CLIENT."</A>";
            else        $CLIENTI = $CLIENTI.", "."<A HREF=".$pagina."?username=".$CLIENT.">".$CLIENT."</A>";
            
            //"<A HREF=".$pagina."?username=".$CLIENT.">".$CLIENT."</A>"

            $i++;
         }
      }
   }
   return trim($CLIENTI);
}
//___________________________________________________________________________________________________
function taraName($NAME)
{
   global $country_whois;
   if ($country_whois == false)
      return "";
         
   global $countrycode_file;
   global $countrycode_data;
   if (!isset($countrycode_data))
      return "";      
   
   $NAME = strtoupper(trim($NAME));
   $TARA="";
   foreach ($countrycode_data as $currentline) 
   {
      list($TARA_CODE, $TARA_NAME) = explode("     ", $currentline);
      $TARA_CODE = strtoupper(trim($TARA_CODE));
      
      if ($TARA_CODE == $NAME)
      {
         $TARA = $TARA_NAME;
         break; 
      }
   }
   return $TARA;
}
//___________________________________________________________________________________________________
function taraNameSaved($NAME)
{
   global $country_whois;
   if ($country_whois == false)
      return array("","");
         
   global $country_data;
   
   $NAME == trim($NAME);
   $TARA["tara"]="";
   $TARA["info"]="";
   $IP="";
   
   foreach ($country_data as $currentline) 
   {
      list($IP_CACHE, $TARA_CACHE, $TARA_USER, $TARA_ORAS) = explode("|", $currentline);
      $IP_CACHE   = trim($IP_CACHE);
      $TARA_CACHE = trim($TARA_CACHE);
      $TARA_USER  = trim($TARA_USER);
      $TARA_ORAS  = trim($TARA_ORAS);
         
      if ($NAME == $TARA_USER)
      {
         $TARA["tara"] = $TARA_CACHE;
         $TARA["info"] = $TARA_ORAS;
         $IP = $IP_CACHE;
         break; 
      }
   }
   return array($TARA,$IP);
}
//___________________________________________________________________________________________________
function taraSaved($IP,$NAME="")
{
   global $country_whois;
   if ($country_whois == false)
      return "";
   
   global $country_data;
   
   $NAME == trim($NAME);
   $TARA["tara"]="";
   $TARA["info"]="";
   foreach ($country_data as $currentline) 
   {
      list($IP_CACHE, $TARA_CACHE, $TARA_USER, $TARA_ORAS) = explode("|", $currentline);
      $IP_CACHE   = trim($IP_CACHE);
      $TARA_CACHE = trim($TARA_CACHE);
      $TARA_USER  = trim($TARA_USER);
      $TARA_ORAS  = trim($TARA_ORAS);
      
      if ($IP == $IP_CACHE)
      {
         if ($NAME == "" || $NAME == $TARA_USER)
         {
            $TARA["tara"] = $TARA_CACHE;
            $TARA["info"] = $TARA_ORAS;
            break; 
         }
      }
   }
   return $TARA;
}

//___________________________________________________________________________________________________
function saveIPTaraName($IP,$TARA,$NAME)
{      
   if ($IP == "unknown")
      return; 
   if ($NAME == "")
      return;

   global $country_file;
   global $country_data;
       
       
   foreach ($country_data as $currentline)
   {
      list($S_IP, $S_TARA, $S_NAME, $S_ORAS) = explode("|", $currentline);
      $S_IP = trim($S_IP);
      $S_TARA = trim($S_TARA);
      $S_NAME = trim($S_NAME);
      $S_ORAS = trim($S_ORAS);
      $country_file_data[$S_NAME] = array ($S_IP,$S_TARA,$S_ORAS);
   }
       
   if (!isset($country_file_data[$NAME]) ||
              $country_file_data[$NAME][0] != $IP ||
              $country_file_data[$NAME][1] != $TARA["tara"] ||
              $country_file_data[$NAME][2] != $TARA["info"] )
   {
      if (! (isset($country_file_data[$NAME]) && $TARA["tara"] =="") )
      {
         $country_file_data[$NAME] = array ($IP,$TARA["tara"],$TARA["info"]);

         $fp = @fopen($country_file,"w");
         foreach ($country_file_data as $S_NAME => $S_INFO)
         {
            fwrite($fp, $S_INFO[0]."|".$S_INFO[1]."|".$S_NAME."|".$S_INFO[2]."\n");
         }
         fclose($fp);

         $country_data = file ($country_file);
      }
   }
}
//___________________________________________________________________________________________________
function tara($IP,$NAME="")
{
	if ($IP == "unknown")
	{
		$TARA["tara"]="??";
   	$TARA["info"]="UNKNOWN";
      return $TARA;
   }
      
   $NAME == trim($NAME);
   $TARA=taraSaved($IP);
   if ($TARA["tara"] !="")
   {
      saveIPTaraName($IP,$TARA,$NAME);
      return $TARA;
   }       

   $TARA["tara"]="";
   $TARA["info"]="";
   
   global $country_whois;
   if ($IP =="127.0.0.1")
	{
   	$TARA["tara"]="<>";
   	$TARA["info"]="";
   }
   else
   if ($country_whois == true)
   {
      
      // apnic.net> - Asia pacific
      // ripe.net> - Europe
      // arin.net> - North America
      // lacnic.net> - Latin America
      // afrinic.net> - Africa and Indian Ocean


      $SERVERE=array("whois.ripe.net","whois.afrinic.net","whois.arin.net","whois.lacnic.net","whois.apnic.net");
      foreach($SERVERE as $SERVER)
      {
         @$CON=fsockopen($SERVER,43, $errno, $errstr, 10);
         if (!$CON)
         {
            $TARA["tara"]="?";
            $TARA["info"]="";
            continue;
         }

         @fputs($CON,$IP."\r\n");
         unset($OUT);
         $OUT = "";
         while(!feof($CON))
         {
            $OUT.=fread($CON,1000);
         }
         
         $TARA["tara"]="?";
         $TARA["info"]="";

         if (strpos($OUT,"ERROR")===false && strpos($OUT,"NOT FOUND")===false && strpos($OUT,"No data found")===false && strpos($OUT,"No match for")===false)
         {
         	$netname = "";
         	$TARA["info"]="";
         	
            $OUT_LIST = explode("\n",htmlspecialchars($OUT));
            foreach($OUT_LIST as $linie)
            {
            	$linie = trim($linie);
            	if ($netname!="" && $linie == "")
            		break;
            		
               if ($TARA["tara"] == "?" && strstr(strtoupper($linie),"COUNTRY:"))
               {
                  list($temp, $rest) = explode(":", $linie);
                  $TARA["tara"] = trim(strtoupper($rest));
               }
               
               if (strstr(strtoupper($linie),"NETNAME:"))
               {
               	$netname = $linie;
                  list($temp, $rest) = explode(":", $linie);
                  if ($TARA["info"]!="") $TARA["info"] = $TARA["info"].";";
                  $TARA["info"] = $TARA["info"]."[NETNAME] ".trim($rest)." ";
               }

               if (strstr(strtoupper($linie),"DESCR:"))
               {
                  list($temp, $DESCRIERE) = explode(":", $linie);

                  $TARA["info"] = $TARA["info"]."; ".trim($DESCRIERE);

                  if (strstr($DESCRIERE,"for private"))
                  {
                     $TARA["tara"]="<>";
                  }

               }
            }
            
            if (strlen($TARA["tara"])==2)
               break;
         }
      }
   }
   
   saveIPTaraName($IP,$TARA,$NAME);

   return $TARA;
}
        
//___________________________________________________________________________________________________

if (file_exists("CCcam.providers"))     
        $providers_ident = file ("CCcam.providers");
else
if (file_exists("ident.info"))  
        $providers_ident = file ("ident.info");
        
        
if (isset($providers_ident))
{
        foreach ($providers_ident as $currentline) 
        {
                $inceput = substr($currentline,0,1);
                if ($inceput != ";" && $inceput != "#" && $currentline!="")
                {
                        list($caid,$nume) = explode(" ", $currentline,2);
                        if ( isset($caid) && $caid!="" )
                        {
                                if (strstr($caid,";")) 
                                        list($caid,$caid2) = explode(";", $caid);
                                
                                $caid = trim($caid);
                                $nume = trim($nume);
                                
                                if (strstr($nume,"\"")) 
                                {
                                        $numeexplode = explode("\"", $nume);
                                        $nume = $numeexplode[1];
                                }
                                
                                $inceput_caid = substr($caid,0,2);
                                
                                //- daca incepe cu 01 sau 05 se insereaza "00:" pe pozitia 3
                                //- daca incepe cu 0D se taie ultimile 2 caractere si se insereaza ":0000" pe pozitia 5
                                //- in rest se insereaza ":00" pe pozitia 5 
                                
                                if ($inceput_caid == "01") $caid_new = substr($caid,0,2)."00:".substr($caid,2,6);
                                else
                                if ($inceput_caid == "05") $caid_new = substr($caid,0,2)."00:".substr($caid,2,6);
                                else
                                if ($inceput_caid == "0D") 
                                {
                                        if (substr($caid,6,2) == "00")
                                                $caid_new = substr($caid,0,4).":0000".substr($caid,4,2);
                                        else
                                                $caid_new = substr($caid,0,4).":0000".substr($caid,6,2);
                                }
                                else
                                        $caid_new = substr($caid,0,4).":00".substr($caid,4,4);
                                        
                        
                        
                                list($ca,$id) = explode(":", $caid_new);
        
                                $ca = sterg0($ca);
                                $id = sterg0($id);
        
                                $caid_new = strtolower($caid_new);
                                $caidstrip = $ca.":".$id;
                                $caidstrip = strtolower($caidstrip);
                                
                                $CCcam_providers[$caid_new] = $nume;
                                $CCcam_providersShort[$caidstrip] = $nume;
                                $CCcam_providers2[$caid] = $nume;
                        } 
                }
        }
        
}
/*
//save CCcam.providers 
$fp = @fopen("CCcam.providers.save","w");
foreach ($CCcam_providers2 as $caid => $nume) 
{
	
	if ($nume != "fake ID")
	{
		//echo $nume;
      fwrite($fp, $caid ." \"".$nume."\"\n");
   }
}
fclose($fp);
*/

   $timp_lastupdate= "";
   
   if (file_exists($update_log)) 
   {
      $update_log_data = file ($update_log);
      $timp_lastupdate = $update_log_data[0];
   }

	if ($username != "")
   {
      $skipUpdate = true;
      
      include "meniu.php";
      include "client.php";
      exit;
   }

   if ($server_nodeDns != "")
   {
      $skipUpdate = true;
      
      include "meniu.php";
      include "server.php";
      exit;
   }
   
   if ($node!="")
   {
      $skipUpdate = true;
      
      include "meniu.php";
      include "node.php";
      exit;
   }
   
   if ($provider!="")
   {
      $skipUpdate = true;
      
      include "meniu.php";
      include "provider.php";
      exit;
   }
   
   if ($pingAll!="")
   {
      $skipUpdate = true;
      
      include "meniu.php";
      include "pingAll.php";
      exit;
   }

?>
