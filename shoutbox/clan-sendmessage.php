<?php
session_start();
include_once('../includes/config.php');

if ( isset($_POST['content']) and isset($_SESSION['naam']) )
{
    $dirty_html_tekst = $_POST['content'];
    require_once '../includes/htmlpurifier/library/HTMLPurifier.auto.php';
    
    $config = HTMLPurifier_Config::createDefault();
    $purifier = new HTMLPurifier($config);
    $tekst = $purifier->purify($dirty_html_tekst);

    mysql_query("INSERT INTO shoutbox (username,content,post_time,clan)
    VALUES('".$_SESSION['naam']."','".mysql_escape_string($tekst)."',NOW(),'".$_SESSION['clan']."')") or die("Er is iets mis gegaan, contacteer de webmaster.");

    exit();
}

?>