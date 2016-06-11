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
	
	function setFocus()
	{
		document.configform.server.focus();
	}
	
	function SaveConfigFile()
	{
		server = trim(document.all("server").value);
		port = trim(document.all("port").value);
		user = trim(document.all("user").value);
		password = trim(document.all("password").value);
		
		if (server == "" )
		{
			alert("Server is mandatory");
			return;
		}
		
		if ((port == "") && (user != "" ) && (password != ""))
		{
			alert("Port is mandatory if user and password are defined");
			return;
		}
		
		if (user == "" )
		{
			if (password != "")
			{
				alert("User is mandatory if password is defined");
				return;
			}
		}
		
		if (user != "")
		{
			if (password == "")
			{
				alert("Password is mandatory if user is defined");
				return;
			}
		}
		
		location.href="configserver.php?profile=new&server=" + server + "&port=" + port + "&user=" + user + "&pass=" + password;
	}
</script>

<?php
	echo "
	<form name='configform' method='post' action=''> 
	<table border=0 cellpadding=2 cellspacing=1 style=background-color:#363636>
	<tr>
	<td class=\"tabel_param\"><A class=\"header\" >Server:</A></td>
	<td><input type='text' name='server' size 30><br></td>
	</tr>
	<tr>
	<td class=\"tabel_param\"><A class=\"header\" >Port:</A></td>
	<td><input type='text' name='port' size 5><br></td>
	</tr>
	<tr>
	<td class=\"tabel_param\"><A class=\"header\" >User:</A></td>
	<td><input type='text' name='user' size 20><br></td>
	</tr>
	<tr>
	<td class=\"tabel_param\"><A class=\"header\" >Password:</A></td>
	<td><input type='text' name='password' size 20><br><br></td>
	</tr>
	<tr>
	<td></td>
	<td class=\"tabel_param\"><input type='button' name='Save' value='Save Configuration' style='width:120px;height:18px;font-family: Tahoma;font-size : 9px' onclick=\"SaveConfigFile()\"</A></td>
	</tr>
	</form>"; 	
	echo "<script>setFocus();</script>";
	exit;

?>
  
<BR></BODY></HTML>


