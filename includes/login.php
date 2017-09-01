<?php 
//Als er op de inlog knop gedrukt word
if(isset($_POST['login'])){
  //Input filteren
  $name = htmlspecialchars(addslashes($_POST['username'])); 
  $ww = htmlspecialchars(addslashes($_POST['password']));
  //Wachtwoord omzetten naar md5 
  $wwmd5 = md5($_POST['password']);
   
  //Gegevens laden voor het foute inloggen.
  $inlog_fout_sql = mysql_query("SELECT `datum`, `ip`, `spelernaam` FROM `inlog_fout` WHERE `ip`='".$_SERVER['REMOTE_ADDR']."' ORDER BY `id` DESC");
  $inlog_fout = mysql_fetch_array($inlog_fout_sql); 
  $aftellen = 1200-(time()-strtotime($inlog_fout['datum']));
   
  //Geen inlognaam ingevuld
  if($_POST['username'] == '')
    $inlog_error = $txt['alert_no_username'];
  //Geen wachtwoord ingevoerd
  elseif($ww == '')
    $inlog_error = $txt['alert_no_password'];
  //Is het wel drie keer mis gegaan
  elseif((mysql_num_rows($inlog_fout_sql) >= 3) AND ($inlog_fout['ip'] === $_SERVER['REMOTE_ADDR']) AND ($aftellen > 0)) {
    $inlog_error = $txt['alert_time_sentence'].' <span><script type="text/javascript">writetimer("'.$aftellen.'")</script></span>';
  }
  else{
    if($aftellen < 0)
      mysql_query("DELETE FROM `inlog_fout` WHERE `ip`='".$_SERVER['REMOTE_ADDR']."'");

    // Gegevens laden om te kijken voor de gebruiker
	$naam = $_POST['username'];
    $gegevens_sql = mysql_query("SELECT `user_id`, `username`, `wachtwoord`, `premiumaccount`, `account_code` FROM `gebruikers` WHERE `username`='".$naam."'"); 
    // Gegevens laden om te kijken voor de gebruiker
    $gegeven_sql  = mysql_query("SELECT `username`, `wachtwoord`, `account_code` FROM `gebruikers` WHERE `wachtwoord`='".$wwmd5."' AND `username`='".$naam."'");
    $gegeven = mysql_fetch_array($gegevens_sql);

    if(mysql_num_rows($gegevens_sql) == 0)
      $inlog_error = $txt['alert_unknown_username'];
    elseif($gegeven['username'] != $naam)
      $inlog_error = $txt['alert_unknown_username'];
    //Kijken of account niet is verbannen
    elseif(mysql_num_rows(mysql_query("SELECT user_id FROM ban WHERE user_id = '".$gegeven['user_id']."'")) > 0)
  	  $inlog_error = $txt['alert_account_banned'];
    elseif(mysql_num_rows($gegeven_sql) == 0){
      $datum = date("Y-m-d H:i:s");
      mysql_query("INSERT INTO `inlog_fout` (`datum`, `ip`, `spelernaam`, `wachtwoord`) 
        VALUES ('".$datum."', '".$_SERVER['REMOTE_ADDR']."', '".$naam."', '".$ww."')");

      if((mysql_num_rows($inlog_fout_sql) == 2) AND ($gegeven['wachtwoord'] != $wwmd5))
        $inlog_error = $txt['alert_timepenalty'];
      elseif((mysql_num_rows($inlog_fout_sql) == 1) AND ($gegeven['wachtwoord'] != $wwmd5))
        $inlog_error = $txt['alert_trys_left_1'];
      elseif((mysql_num_rows($inlog_fout_sql) == 0) AND ($gegeven['wachtwoord'] != $wwmd5))
        $inlog_error = $txt['alert_trys_left_2'];
    }
    elseif($gegeven['account_code'] != 1)
      $inlog_error = $txt['alert_account_not_activated'];
    else{
      //If Onthoud Check box is checked save cookie
      if($_POST['remember'] == "on"){
        setcookie("pa_1", $gegeven['username'], time()+(60*60*24*365));
        setcookie("pa_2", $_POST['password'], time()+(60*60*24*365));
      }
      
      //Zorgen dat gebruiker weer 3 pogingen heeft.
      mysql_query("DELETE FROM `inlog_fout` WHERE `ip`='".$_SERVER['REMOTE_ADDR']."'");
      
      //tijd opslaan dat het lid inlogt, zodat de site weet dat hij online is.
      $tijd = time();
      mysql_query("UPDATE `gebruikers` SET `ip_ingelogd`='".$_SERVER['REMOTE_ADDR']."', `online`='".$tijd."' WHERE `username`='".$gegeven['username']."'");
      
      //Datum opvragen
      $date = date("Y-m-d H:i:s");
      //Opslaan in de inlog_logs tabel
      $queryloginlogs = mysql_query("SELECT `id` FROM `inlog_logs` WHERE `ip`='".$_SERVER['REMOTE_ADDR']."' AND `speler`='".$gegeven['username']."'");
      if(mysql_num_rows($queryloginlogs) == "0"){
        mysql_query("INSERT INTO `inlog_logs` (`ip`, `datum`, `speler`) 
          VALUES ('".$_SERVER['REMOTE_ADDR']."', '".$date."', '".$naam."')");
      }
      else
        mysql_query("UPDATE `inlog_logs` SET `datum`='".$date."' WHERE `speler`='".$gegeven['username']."' AND `ip`='".$_SERVER['REMOTE_ADDR']."'");
      
      //zet naam in variabele, zodat het later nog gebruikt kan worden
      $_SESSION['id'] = $gegeven['user_id'];
      $_SESSION['naam'] = $gegeven['username'];
      //Hash opslaan
      $_SESSION['hash'] = md5($_SERVER['REMOTE_ADDR'].",".$gegeven['username']);
      //Ben je wel premium
      if($gegeven['premiumaccount'] > 0)
        $_SESSION['userid'] = $gegeven['id'];
      //naar de ingame pagina sturen
      header('location: index.php?page=home');
    }
  }
}
?>