<?php
$CCCamWebInfo[] = array("192.168.2.101","16001","zorg","zorg"); // for CCcam webinterface with user and pass
$CCCamWebInfo[] = array("localhost","12345");     					// for CCcam webinterface with different port than default and without user and pass
$CCCamWebInfo[] = array("localhost");												// for CCcam webinterface with all defaults
$work_path = ""; // set this if you want working folder separate // Example $work_path = "/tmp/";
$update_from_button = true; // set to true if you want Update button ( usefull if update is from remote server and takes too long)
$fullReshare = true; // shows maximum reshare if more than one route for same node // set to true to see actual reshare instead of YES/NO
$country_whois = true; // use whois for country detection
include("includes/functions.php");
$CCcam_path = "/var/etc/";
$CCcam_path1 = "/var/etc/";
$sitename = "CCcam best server";
$username  = "zorg";
$password = "zorg";
?>
