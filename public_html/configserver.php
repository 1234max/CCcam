<html>

<head> 

</head> 
 
<BODY>
<script language=javascript> 

    function InitialPage()
    {
        location.href="index.php";
    }
</script>

<?php

	if (file_exists("config.php"))
	{
		$config_data = file ("config.php");

		$profile = $_GET['profile'];
		$server = $_GET['server'];
		$port = $_GET['port'];
		$user = $_GET['user'];
		$pass = $_GET['pass'];
				
		$config_line = "";
		$seek_line = "";
		
		if ($profile == "new")
		{
			$seek_line = "\$work_path";
			if (($server != "") && ($port != "") && ($user != "") && ($pass != ""))
			{
				$config_line = "\$CCCamWebInfo[] = array(\"".$server."\",\"".$port."\",\"".$user."\",\"".$pass."\");";
				$config_line = $config_line."\n";
			}
			elseif (($server != "") && ($port != "") && ($user == "") && ($pass == ""))
			{
				$config_line = "\$CCCamWebInfo[] = array(\"".$server."\",\"".$port."\");";
				$config_line = $config_line."\n";
			}
			elseif (($server != "") && ($port == "") && ($user == "") && ($pass == ""))
			{
				$config_line = "\$CCCamWebInfo[] = array(\"".$server."\");";
				$config_line = $config_line."\n";
			}
		}
		elseif ($profile == "delete")
		{
			if (($server != "") && ($port != "") && ($user != "") && ($pass != ""))
			{
				$seek_line = "\$CCCamWebInfo[] = array(\"".$server."\",\"".$port."\",\"".$user."\",\"".$pass."\");";
			}
			elseif (($server != "") && ($port != "") && ($user == "") && ($pass == ""))
			{
				$seek_line = "\$CCCamWebInfo[] = array(\"".$server."\",\"".$port."\");";
			}
			elseif (($server != "") && ($port == "") && ($user == "") && ($pass == ""))
			{
				$seek_line = "\$CCCamWebInfo[] = array(\"".$server."\");";
			}
			
		}
			
		$index_server = -1;
 		$index = -1;
		foreach ($config_data as $currentline) 
		{
			$index++;
			if (strstr($currentline,$seek_line))
			{
				if ($index_server == -1)
					$index_server = $index;
			}
		}
		
		if ($index_server == -1)
			$index_server = $index;

		$myFile = "config.php";
		$fh = fopen($myFile, 'w') or die("can't open file");

		$index = -1;
		foreach ($config_data as $currentline) 
		{
			$index++;
			if ($profile == "new")
			{
				if ($index == $index_server)
				{
					fwrite($fh, $config_line);	
				}
				fwrite($fh, $currentline);	
			}
			elseif ($profile == "delete")
			{
				if ($index != $index_server)
				{
					fwrite($fh, $currentline);
				}
			}

		}
		fclose($fh);
		
		echo "<script>InitialPage();</script>";		
	}

?>
  
<BR></BODY></HTML>


