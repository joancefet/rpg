<?php
//Security laden
include('includes/security.php');

if(($gebruiker['clan'] == "") ) echo '<div class="red">Du bist in keinem Clan!</div>';
else {
#Load language
$page = 'clan-fights';

$uid = (int) $_SESSION['id'];

$date = date("d.m.Y");
$expire = date("d.m.Y")+1;

#playerstrength
$playerstrength_query = mysql_query("SELECT `attack`, `defence`, `spc.attack`, `spc.defence`, `speed`, `opzak`, `user_id`, SUM(`attack` + `defence` + `spc.attack` + `spc.defence` + `speed`) as `endstaerke` FROM `pokemon_speler` WHERE `user_id`='{$uid}' AND `opzak`='ja'");
$playerstrength = mysql_fetch_assoc($playerstrength_query);
mysql_query("UPDATE `gebruikers` SET `playerstrength`='".$playerstrength['endstaerke']."' WHERE `user_id`='{$uid}'");

#clanplayercheck
$clanplayercheck_query = mysql_query("SELECT `clan`, `user_id` FROM `gebruikers` WHERE `user_id`='{$uid}'");
$clanplayercheck = mysql_fetch_assoc($clanplayercheck_query);

#clanstrength
$clanstrength_query = mysql_query("SELECT `clan`, `playerstrength`, SUM(`playerstrength`) as `clanstrength` FROM `gebruikers` WHERE `clan`='".$clanplayercheck['clan']."' AND `account_code`='1'");
$clanstrength = mysql_fetch_assoc($clanstrength_query);

$clan_sql = mysql_query("SELECT * FROM `clans` WHERE `clan_naam`='".$clanplayercheck['clan']."'");
$clan_check = mysql_fetch_assoc($clan_sql);

$text1 = "Berechnet sich aus der Gesamstärke aller Pokémon der Spieler im jeweiligen Clan";
$text2 = "";

#Wieviele Clan Mitglieder sind bereit?
$clan_rdy_members_query = mysql_query("SELECT `clan`, `clan_fight-ready` FROM `gebruikers` WHERE `clan`='".$clanplayercheck['clan']."' AND `clan_fight-ready`='1'");
$clan_rdy_members = mysql_num_rows($clan_rdy_members_query);
#Wieviele Clan Mitglieder existieren?
$clan_members_query = mysql_query("SELECT `clan` FROM `gebruikers` WHERE `clan`='".$clanplayercheck['clan']."'");
$clan_members = mysql_num_rows($clan_members_query);

//Ende
if((isset($_POST['accept']))){
echo '<center><div class="green">Du nimmst am Angriff teil.</div></center>';
mysql_query("UPDATE `gebruikers` SET `clan_fight-ready`='1' WHERE `user_id`='{$uid}'");
}

if((isset($_POST['decline']))){
echo '<center><div class="red">Du hast den Angriff abgelehnt.</div></center>';
mysql_query("UPDATE `gebruikers` SET `clan_fight-ready`='0' WHERE `user_id`='{$uid}'");
}

if((isset($_POST['submit']))){
//Wieviel Geld klauen?
$random1 = rand(1, 4);
if($random1 == 1) $abziehen = 7;
elseif($random1 == 2) $abziehen = 8;
elseif($random1 == 3) $abziehen = 9;
elseif($random1 == 4) $abziehen = 10;

$clan_name = mysql_real_escape_string($_POST['naam']);
$clan_level_check_query = mysql_query("SELECT `clan_naam`, `clan_level`, `daily-fights` FROM `clans` WHERE `clan_naam`='".$clan_name."'");
$clan_level_check = mysql_fetch_assoc($clan_level_check_query);
$clan_exist_check_query = mysql_query("SELECT `clan_naam`, `clan_silver`, `bank`, `battle_points` FROM `clans` WHERE `clan_naam`='".$clan_name."'");
$clan_exist_check = mysql_fetch_assoc($clan_exist_check_query);
$clan_exist2 = mysql_query("SELECT `clan` FROM `gebruikers` WHERE `clan`='".$clan_name."'");
$clan_geld_abziehen = round($clan_exist_check['battle_points']/$abziehen);
$gives = htmlspecialchars($_POST['gives']);
#clanstrength opponents
$clanstrength_opponents_query = mysql_query("SELECT `clan`, `playerstrength`, SUM(`playerstrength`) as `clanstrength` FROM `gebruikers` WHERE `clan`='".$clan_name."'");
$clanstrength_opponents = mysql_fetch_assoc($clanstrength_opponents_query);
		
		//echo $clanstrength_opponents['clanstrength'];
		//echo '<br>';
		//echo $clanstrength['clanstrength'];
        //echo $clan_check['daily-fights'];
	    //echo '<br>';
		//echo $clan_geld_abziehen_silver;
		//echo '<br>';
        //echo $clan_geld_abziehen_gold;
		//echo '<br>';
		//echo $clan_exist_check['bank'];
		//echo '<br>';
		//echo $clan_exist_check['clan_gold'];

		#Clan Gründer?
		if($_SESSION['naam'] != $clan_check['clan_owner']){ 
		echo '<div class="red">Du bist nicht der Clan Gründer.</div>';
		}
		#Irgendwas abgeschickt?
		else if(empty($clan_name)){
			echo '<div class="red">Bitte gib einen Clan Namen ein.</div>';
		}
		else if(mysql_num_rows($clan_exist2) == 0){
			echo '<div class="red">Dieser Clan existiert nicht.</div>';
		}
		else if($clan_check['daily-fights'] == 0){
			echo '<div class="red">Du kannst heute keine weiteren Angriffe ausführen.</div>';
		}
		else if($clan_rdy_members != $clan_members){
			echo '<div class="red">Es sind nicht alle Spieler bereit für einen Angriff.</div>';
		}
		else if($clan_check['clan_level'] > $clan_level_check['clan_level']+1){
		    echo '<div class="red">Das Level des anderen Clans ist zu niedrig.</div>';
		}
		else if($clan_members < 2){
		   echo '<div class="red">Dein Clan muss mindestens 5 Mitglieder haben!</div>';
		}
		else if($clan_exist_check['battle_points'] < 500){
		   echo '<div class="red">Der Clan hat zu wenig Battle Points<img src="images/battlepoints.png" /></div>';
		}
		
        //All right?
		
		else {
		// + gold
		mysql_query("UPDATE `clans` SET `battle_points`=`battle_points`+'".$clan_geld_abziehen."' WHERE `clan_naam`='".$clanplayercheck['clan']."'");
		// - daily clan fights
		mysql_query("UPDATE `clans` SET `daily-fights`=`daily-fights`-'1' WHERE `clan_naam`='".$clanplayercheck['clan']."'");
		// - gold
		mysql_query("UPDATE `clans` SET `battle_points`=`battle_points`-'".$clan_geld_abziehen."' WHERE `clan_naam`='".$clan_name."'");
		echo "<div class='green'>Der Angriff war erfolgreich.</div>";
		}
		}
		
$checkitout_query = mysql_query("SELECT * FROM `clans` WHERE `clan_naam`='".$clanplayercheck['clan']."'");
$checkitout = mysql_fetch_assoc($checkitout_query);

mysql_query("INSERT INTO `clan_items` (clanname, expire) VALUES ('".$checkitout['clan_naam']."', '".$expire."')");		
?>
<?php if($uid == $clan_check['clan_ownerid']) { ?>

<form method="post">
  <center>
    <p><strong>Greife andere Clans an.</strong><br/><br/></p>
	<p>Du und die Mitglieder Deines Clans, können andere Clans angreifen, sobald alle Mitglieder dem Angriff zugestimmt haben!</p>
	<p>Hierbei gewinnt der Clan mit der höchsten <a onmouseover="showhint('<?php echo $text1; ?>.', this)">Gesamtstärke</a></p>
	<p>Du kannst heute noch <b><?php echo $clan_check['daily-fights']; ?></b> Angriffe starten.</p>
	<p>Die Gesamtstärke Deines Clans beträgt: <b><?php echo $clanstrength['clanstrength']; ?></b> <img src="https://pogs.free.fr/WoT/WoT/Image/image%2014%20(BattleResultIcon).png" width="12" height="12" /></p>
    <table width="300">
      <tr>
        <td><label for="naam"><img src="images/icons/clan.png" width="16" height="16" alt="Player" class="imglower" /> Clan:</label></td>
        <td colspan="2"><input type="text" name="naam" id="naam" class="text_long" value="<?php echo $getname; ?>" /></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td colspan="2"><input type="submit" name="submit" value="Angreifen" class="button"></td>
      </tr>
  </table>
  </form>
  </center>
<hr>
<?php } ?>
<center>

<div class="clanfightz">
<h2>Clan Kämpfe</h2>
<div class="info">
Du hast die Möglichkeit, an Clan Angriffen teilzunehmen!
Trage Dich und Deine Pokémon, welche Du dabei hast ein, um an einem Angriff teilzunehmen.<br>
</div>
</div>

	<table width="300" border="0">
        <tr>
        	<td width="50"><strong>#</strong></td>
            <td width="120"><strong>Spieler</strong></td>
			<td width="150"><strong>Stärke</strong></td>
            <td width="120"><strong>Bereit?</strong></td>
        </tr>
<?php
$clan_ready_query = mysql_query("SELECT `username`, `clan_fight-ready`, `playerstrength` FROM `gebruikers` WHERE `clan`='".$clanplayercheck['clan']."'");

while($clan_ready = mysql_fetch_array($clan_ready_query)){
if($uid == $clan_check['clan_ownerid']) {
$clan_playerstrength = $clan_ready['playerstrength'];
}
else {
$clan_playerstrength = '****';
}
	
	if($clan_ready['clan_fight-ready'] == 0){ $ready = '<img src="images/icons/alert_red.png" border="0" style="margin-bottom:-3px;">'; }
	else { $ready = '<img src="images/icons/alert_green.png" border="0" style="margin-bottom:-3px;">'; }
	$number ++;

  echo '<tr>
  			<td>'.$number.'.</td>
			<td><a href="index.php?page=profile&player='.$clan_ready['username'].'">'.$clan_ready['username'].'</a></td>
			<td><font color="red"><b>'.$clan_playerstrength.'</b></font></td>
			<td>'.$ready.'</td>
		</tr>';
}		
?>

</table>
<?php if($clan_rdy_members == $clan_members){
echo "<div class='green'>Es haben ".$clan_rdy_members."/".$clan_members." Clan-Mitgliedern dem Angriff zugesagt!</div>";
}
else{
echo "<div class='red'>Es haben ".$clan_rdy_members."/".$clan_members." Clan-Mitgliedern dem Angriff zugesagt!</div>";
}
?>
<br>
<?php if($gebruiker['clan_fight-ready'] == 0) { ?>
Möchtest Du an einem Angriff teilnehmen?<br>
<form method="post">
<input type="submit" name="accept" value="Teilnehmen" class="button_mini" style="width:85px;background-color:#cbed91;"> 
</form>
<br>
<?php } ?>
<font color="red">Deine Stärke errechnet sich aus der Gesamtstärken Deiner Pokémon, welche Du bei Dir trägst!</font><br>
Deine Stärke: <font color="red"><b><?php echo $playerstrength['endstaerke']; ?></b></font> <img src="https://pogs.free.fr/WoT/WoT/Image/image%2014%20(BattleResultIcon).png" width="12" height="12" /><br>
Die Gesamtstärke des Clans beträgt: <b><?php echo $clanstrength['clanstrength']; ?></b> <img src="https://pogs.free.fr/WoT/WoT/Image/image%2014%20(BattleResultIcon).png" width="12" height="12" />
<style>
.clanfightz {
width: 750px;
height: 120px;
line-height: 34px;
text-align: center;
display: inline-block;
margin-bottom: 4px;
border: 1px solid #ccc;
border-left: 1px dashed #ccc;
border-right: 1px dashed #ccc;
background: #fff url(https://th04.deviantart.net/fs70/PRE/f/2012/126/6/b/elemental_clash___harvey_vs__grace_by_arkeis_pokemon-d4ysoqg.jpg) center  fixed;
-webkit-background-size: cover;
-moz-background-size: cover;
-o-background-size: cover;
background-size: cover;
font-size: 12px;
}
.info{margin-top:20px;background:#fff;box-shadow:0 0 10px #333;border-bottom:1px solid #ccc;border-top:1px solid #ccc;font-weight:700}
h2{background:#fff;width:220px;margin-left:auto;margin-right:auto;border:1px solid #ccc;border-top:0;border-radius:0 0 8px 8px;box-shadow:0 0 10px #333}
</style>
<?php } ?>
