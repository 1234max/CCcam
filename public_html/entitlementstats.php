<?php include "common.php"?>
<?php

	if (!$update_from_button)
	{
		$update_entitlements = true;
	}
	include "meniu.php";
	
	if (file_exists($entitlements_file))
		$entitlements_data = file ($entitlements_file);
		
	foreach ($entitlements_data as $currentline) 
	if (!strstr($currentline,"</H2>")) 		
	{
		echo $currentline."<BR>";
	}
	
	//___________________________________________________________________________________________________
	


?>
<BR></BODY></HTML>
