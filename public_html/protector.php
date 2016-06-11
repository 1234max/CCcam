<?php
require_once('config.php');
session_start();
//if ($_SESSION['logged']!='true')
if (isset($_POST['login'])) {
  $p = $_POST['password'];
  $u = $_POST['username'];
  if (($p == $password) && ($u == $username)) {
    $_SESSION['logged']='true';
    header('Location: '.$_SERVER['PHP_SELF']);
    //header('Status: 404 Not Found');
    exit;
}}

 if (($_SESSION['logged'] == 'false') || (!isset($_SESSION['logged']))){
header('Location: login.php');
}

?>
