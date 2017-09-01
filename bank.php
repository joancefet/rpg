<table width="100%">
<tr>
  <td align="left">
    <center><a href="index.php?page=bank&x=pinstort"><img src="images/pin.png"></center><br>
    <center>Pinnen &amp; Storten</a><center>
  </td>
    <?php
    $clanquery = mysql_query("SELECT * FROM clans WHERE clan_naam='".$gebruiker['clan']."'");
    $profiel = mysql_fetch_array($clanquery);
    if($gebruiker['clan'] != ""){
    ?>
    <td align="center">
        <center><a href="index.php?page=bank&x=stortennaarclan"><img src="images/type/<? echo $profiel['clan_type']; ?>.png" width="72px"></center><br>
        <center>Overschrijven naar Clan</a><center>
    </td>
    <?php
    }
    ?>
  <td align="right">
    <center><a href="index.php?page=bank&x=overschrijven"><img src="images/overschrijven.png"></center><br>
    <center>Overschrijven</a></center>
  </td>
</tr>
</table>
<br/><br/>
<?php 
$bankmax = "9999999999";
$silver = $gebruiker['silver'];
$gold = $gebruiker['gold'];
$bank = $gebruiker['bank'];
$data->bankleft = $gebruiker['storten'];
$amount = $_POST['amount'];

  if(isset($_POST['out']) && preg_match('/^[0-9]+$/',$_POST['amount'])) {
    if($_POST['amount'] <= $bank) {
      $data->silver	= mysql_escape_string($_POST['amount']);
      $data->bank	= mysql_escape_string($_POST['amount']);
      mysql_query("UPDATE `gebruikers` SET `bank`=`bank`-{$data->bank},`silver`=`silver`+{$data->silver} WHERE `username`='{$gebruiker['username']}'");
        mysql_query("INSERT INTO bank_logs (date, sender, receiver, amount, what,type)
                    VALUES (NOW(),'".$gebruiker['username']."','".$gebruiker['username']."','{$data->silver}','withdraw','silver')");
        print "<div class=\"green\">Er is <img src=\"images/icons/silver.png\" /> $amount silver afgeschreven van je bankrekening!</div>";
    }
    else
      print "<div class=\"red\">Zoveel silver staat er niet op je bankrekening.</div>";
  }
  else if(isset($_POST['in']) && preg_match('/^[0-9]+$/',$_POST['amount'])) {
    if($_POST['amount'] <= $silver) {
      if($_POST['amount'] <= $bankmax) {
        if($data->bankleft > 0) {
          $data->silver	= mysql_escape_string($_POST['amount']);
          $data->bank	= mysql_escape_string($_POST['amount']);
          mysql_query("UPDATE `gebruikers` SET `bank`=`bank`+{$data->bank},`silver`=`silver`-{$data->silver},`storten`=`storten`-1 WHERE `username`='{$gebruiker['username']}'");
            mysql_query("INSERT INTO bank_logs (date, sender, receiver, amount, what, type)
                    VALUES (NOW(),'".$gebruiker['username']."','".$gebruiker['username']."','{$data->silver}','deposit','silver')");
        print "<div class=\"green\">Er is <img src=\"images/icons/silver.png\" /> $amount bijgeschreven bij je bankrekening!</div>";
        }
        else
          print "<div class=\"blue\">Je kan niet meer storten vandaag</div>";
      }
      else
        print "<div class=\"red\">Je mag maar <img src=\"images/icons/silver.png\" /> {$bankmax} per keer storten</div>";
    }
    else
      print "<div class=\"red\">Zoveel silver heb je niet</div>";
  }
if($_GET['x'] == "pinstort") {
  print '<br/>Je mag nog '.$gebruiker['storten'].'x silver storten.<br/><br/>
  <table width="60%">
    <tr>
      <td width=100>Contant:</td>
      <td align="right"><img src="images/icons/silver.png" /> '.highamount($gebruiker['silver']).'</td>
    </tr>
    <tr>
      <td width=100>Op de bank:</td>
      <td align="right"><img src="images/icons/silver.png" /> '.highamount($gebruiker['bank']).'</td>
    </tr>
  </table>
  <form method="post"><table width="60%" align="center">
    <tr>
    <td align="left">
      &euro; <input type="text" class="bar curved5" name="amount" maxlength="10"> ,- <br/><br/>
      <button type="submit" name="out" class="button">pin</button>
      <button type="submit" name="in" class="button">stort</button>
    </td>
    </tr>
  </table></form>';
  
} else if($_GET['x'] == "overschrijven") {

    if(isset($_POST['to'])) {
        if ($_POST['silver']) {
            $to = mysql_escape_string($_POST['to']);
            $amount = mysql_escape_string($_POST['amount']);
            $ontvanger1 = mysql_query("SELECT * FROM `gebruikers` WHERE `username`='{$to}'");
            $ontvanger = mysql_fetch_assoc($ontvanger1);
            if ($ontvanger < 1) {
                print "<div class=\"red\">De naam die je hebt ingevuld klopt niet!</div>";
            } else if ($_POST['silver'] <= $silver) {
                $data->silver = mysql_escape_string($_POST['silver']);
                $data->to = mysql_escape_string($_POST['to']);
                mysql_query("UPDATE `gebruikers` SET `silver`=`silver`-{$data->silver} WHERE `username`='{$gebruiker['username']}'");
                if ($member = mysql_fetch_object(mysql_query("SELECT `username` FROM `gebruikers` WHERE `username`='{$data->to}'"))) {
                    mysql_query("UPDATE `gebruikers` SET `silver`=`silver`+{$data->silver} WHERE `username`='{$ontvanger['username']}'");
                    mysql_query("INSERT INTO bank_logs (date, sender, receiver, amount, what,type)
                    VALUES (NOW(),'".$gebruiker['username']."','".$data->to."','{$data->silver}','transfer','silver')");

                    print "<div class=\"green\">Er is <img src=\"images/icons/silver.png\" /> {$data->silver} aan {$ontvanger['username']} overgemaakt</div>";
                }
                } else {
                    print "<div class=\"red\">Je hebt niet genoeg silver contant staan.</div>";
            }
        }else{
            print "<div class=\"red\">Er is geen silver opgegeven.</div>";
        }
    }
    print <<<ENDHTML
    <br/><br/>
  <form method="post"><table width="60%">
  <tr>
    <td><img src="images/icons/user.png" /> Aan:</td>
    <td><input type="text" class="bar curved5" name="to" value="{$_REQUEST['to']}"></td>
  </tr>
  <tr>
    <td><img src="images/icons/silver.png" />  Silver:</td>
    <td><input type="text" class="bar curved5" name="silver" maxlength="7"  value="{$_REQUEST['silver']}"></td>
  </tr>
  <tr>
    <td ><button type="submit" class="button">Overmaken</button></td>
  </tr>
  </table></form>
ENDHTML;

} else if($_GET['x'] == "stortennaarclan") {

    if(isset($_POST['to'])) {
        if ($_POST['silver']) {
            $to = mysql_escape_string($_POST['to']);
            $amount = mysql_escape_string($_POST['amount']);
            $ontvanger1 = mysql_query("SELECT * FROM `clans` WHERE `clan_naam`='{$to}'");
            $ontvanger = mysql_fetch_assoc($ontvanger1);
            if ($ontvanger < 1) {
                print "<div class=\"red\">De clan die je hebt ingevuld klopt niet!</div>";
            } else if ($_POST['silver'] <= $silver) {
                $data->silver = mysql_escape_string($_POST['silver']);
                $data->to = mysql_escape_string($_POST['to']);
                mysql_query("UPDATE `gebruikers` SET `silver`=`silver`-{$data->silver} WHERE `username`='{$gebruiker['username']}'");
                    mysql_query("UPDATE `clans` SET `clan_silver`=`clan_silver`+{$data->silver} WHERE `clan_naam`='{$ontvanger['clan_naam']}'");
                    mysql_query("INSERT INTO bank_logs (date, sender, receiver, amount, what,ype)
                        VALUES (NOW(),'".$gebruiker['username']."','".$ontvanger['clan_naam']."','{$data->silver}','transfer to clan','silver')");
                    print "<div class=\"green\">Er is <img src=\"images/icons/silver.png\" /> {$data->silver} aan de clan {$ontvanger['clan_naam']} overgemaakt</div>";
            } else {
                print "<div class=\"red\">Je hebt niet genoeg silver contant staan.</div>";
            }
        }elseif ($_POST['gold']) {
            $to = mysql_escape_string($_POST['to']);
            $amount = mysql_escape_string($_POST['amount']);
            $ontvanger1 = mysql_query("SELECT * FROM `clans` WHERE `clan_naam`='{$to}'");
            $ontvanger = mysql_fetch_assoc($ontvanger1);
            if ($ontvanger < 1) {
                print "<div class=\"red\">De clan die je hebt ingevuld klopt niet!</div>";
            } else if ($_POST['gold'] <= $gold) {
                $data->gold = mysql_escape_string($_POST['gold']);
                $data->to = $_POST['to'];
                mysql_query("UPDATE `gebruikers` SET `gold`=`gold`-{$data->gold} WHERE `username`='{$gebruiker['username']}'");
                    mysql_query("UPDATE `clans` SET `clan_gold`=`clan_gold`+{$data->gold} WHERE `clan_naam`='{$ontvanger['clan_naam']}'");
                    mysql_query("INSERT INTO bank_logs (date, sender, receiver, amount, what,type)
                            VALUES (NOW(),'".$gebruiker['username']."','".$ontvanger['clan_naam']."','{$data->gold}','transfer to clan','gold')");
                    print "<div class=\"green\">Er is <img src=\"images/icons/gold.png\" /> {$data->gold} aan de clan {$ontvanger['clan_naam']} overgemaakt</div>";
            } else {
                print "<div class=\"red\">Je hebt niet genoeg gold.</div>";
            }
        }else{
            print "<div class=\"red\">Er is geen silver of gold opgegeven.</div>";
        }
    }
    print <<<ENDHTML
    <br/><br/>
  <form method="post"><table width="60%">
  <tr>
    <td><img src="images/icons/user.png" /> Aan:</td>
    <td><input type="text" class="bar curved5" name="to" value="{$_REQUEST['to']}"></td>
  </tr>
  <tr>
    <td><img src="images/icons/silver.png" />  Silver:</td>
    <td><input type="text" class="bar curved5" name="silver" maxlength="7"  value="{$_REQUEST['silver']}"></td>
  </tr>
  <tr>
    <td><img src="images/icons/gold.png" />  Gold:</td>
    <td><input type="text" class="bar curved5" name="gold" maxlength="7"  value="{$_REQUEST['gold']}"></td>
  </tr>
  <tr>
    <td ><button type="submit" class="button">Overmaken</button></td>
  </tr>
  </table></form>
ENDHTML;

}

?>
