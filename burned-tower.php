<?php
//Script laden zodat je nooit pagina buiten de index om kan laden
include("includes/security.php");

//Kijken of je wel pokemon bij je hebt
if($gebruiker['in_hand'] == 0) header('location: index.php');

$page = 'attack/attack_map';
//Goeie taal erbij laden voor de page
include_once('language/language-pages.php');

//Are player pokemon alive?
$kill_query = mysql_query("SELECT `wild_id`, `leven` FROM `pokemon_speler` WHERE `user_id`='".$_SESSION['id']."' AND `opzak`='ja' ORDER BY `opzak_nummer` LIMIT 6");
while ($poke_live = mysql_fetch_assoc($kill_query)) {
if($poke_live['leven'] <= 0){
header('location: index.php?page=pokemoncenter');
}
}

if(mysql_num_rows(mysql_query("SELECT `id` FROM `pokemon_speler` WHERE `user_id`='".$_SESSION['id']."' AND `ei`='0' AND `opzak`='ja'")) > 0){
if((isset($_POST['gebied'])) && (is_numeric($_POST['gebied']))){
if($_POST['gebied'] == 1) $gebied = 'Lavagrot';
elseif($_POST['gebied'] == 2) $gebied = 'Lavagrot';

if($gebruiker['in_hand'] == 0)
echo '<div class="blue"><img src="images/icons/blue.png"> '.$txt['alert_no_pokemon'].'</div>';
elseif($gebruiker['trainerb'] == 0)
echo '<div class="red">Die maximale Anzahl an Kämpfen ist für heute erreicht.</div>'; 
else{
//Zeldzaamheid bepalen
$zeldzaam = rand(1,400);
if($zeldzaam <= 399) $trainer = 1;// Team Rocket
elseif($zeldzaam == 400) $zeldzaamheid = 3;

if($trainer == 1){
$query = mysql_fetch_assoc(mysql_query("SELECT `naam` FROM `trainer` WHERE `badge`='' AND (`gebied`='Grot' OR `gebied`='Lavagrot') ORDER BY rand() limit 1"));
include('attack/trainer/trainer-start.php');
mysql_data_seek($pokemon_sql, 0);
$opzak = mysql_num_rows($pokemon_sql);
$level = 0;
while($pokemon = mysql_fetch_assoc($pokemon_sql)) $level += $pokemon['level'];
$trainer_ave_level = $level/$opzak;
//Make Fight
$info = create_new_trainer_attack($query['naam'],$trainer_ave_level,$gebied);
if(empty($info['bericht'])) header("Location: ?page=attack/trainer/trainer-attack");
else echo "<div class='red'>".$txt['alert_no_pokemon']."</div>";
            mysql_query("UPDATE `gebruikers` SET `trainerb`=`trainerb`-'1' WHERE `user_id`='".$_SESSION['id']."'"); 
}
else{
if(($gebruiker['rank'] > 17) && (!empty($gebruiker['lvl_choose']))){
$level = explode("-", $gebruiker['lvl_choose']);
$leveltegenstander = rand($level[0],$level[1]);
}
else $leveltegenstander = rankpokemon($gebruiker['rank']);
$tower_id = rand(243,245);
$query = mysql_fetch_assoc(mysql_query("SELECT wild_id FROM `pokemon_wild` WHERE `wild_id`='".$tower_id."' ORDER BY rand() limit 1"));
//Geen pokemon geen gekozen
if(empty($query['wild_id']))
$query = mysql_fetch_assoc(mysql_query("SELECT wild_id FROM `pokemon_wild` WHERE `wild_id`='245' ORDER BY rand() limit 1"));
//echo "<div class='red'>".$txt['alert_error']." 100".$zeldzaamheid.".</div>";
//else{
include("attack/wild/wild-start.php");
$info = create_new_attack($query['wild_id'],$leveltegenstander,$gebied);
if(empty($info['bericht'])) header("Location: ?page=attack/wild/wild-attack");
else echo "<div class='red'>".$txt['alert_no_pokemon']."</div>";
            mysql_query("UPDATE `gebruikers` SET `trainerb`=`trainerb`-'1' WHERE `user_id`='".$_SESSION['id']."'"); 
//}
}
}
}

echo $error; ?>
<style>
.burned {
border: 1px solid #000000;border-bottom:0;
border-radius:10px 10px 0 0;
box-shadow:0 0 5px #333;
background: rgb(255,255,255); /* Old browsers */
background: -moz-linear-gradient(top, rgba(255,255,255,1) 0%, rgba(255,255,255,1) 75%, rgba(237,237,237,1) 100%); /* FF3.6+ */
background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(255,255,255,1)), color-stop(75%,rgba(255,255,255,1)), color-stop(100%,rgba(237,237,237,1))); /* Chrome,Safari4+ */
background: -webkit-linear-gradient(top, rgba(255,255,255,1) 0%,rgba(255,255,255,1) 75%,rgba(237,237,237,1) 100%); /* Chrome10+,Safari5.1+ */
background: -o-linear-gradient(top, rgba(255,255,255,1) 0%,rgba(255,255,255,1) 75%,rgba(237,237,237,1) 100%); /* Opera 11.10+ */
background: -ms-linear-gradient(top, rgba(255,255,255,1) 0%,rgba(255,255,255,1) 75%,rgba(237,237,237,1) 100%); /* IE10+ */
background: linear-gradient(to bottom, rgba(255,255,255,1) 0%,rgba(255,255,255,1) 75%,rgba(237,237,237,1) 100%); /* W3C */
filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ffffff', endColorstr='#ededed',GradientType=0 ); /* IE6-9 */
text-align:center;
}
.burned-times { 
background: rgb(255,255,255); /* Old browsers */
background: -moz-linear-gradient(top, rgba(255,255,255,1) 0%, rgba(241,241,241,1) 50%, rgba(225,225,225,1) 51%, rgba(246,246,246,1) 100%); /* FF3.6+ */
background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(255,255,255,1)), color-stop(50%,rgba(241,241,241,1)), color-stop(51%,rgba(225,225,225,1)), color-stop(100%,rgba(246,246,246,1))); /* Chrome,Safari4+ */
background: -webkit-linear-gradient(top, rgba(255,255,255,1) 0%,rgba(241,241,241,1) 50%,rgba(225,225,225,1) 51%,rgba(246,246,246,1) 100%); /* Chrome10+,Safari5.1+ */
background: -o-linear-gradient(top, rgba(255,255,255,1) 0%,rgba(241,241,241,1) 50%,rgba(225,225,225,1) 51%,rgba(246,246,246,1) 100%); /* Opera 11.10+ */
background: -ms-linear-gradient(top, rgba(255,255,255,1) 0%,rgba(241,241,241,1) 50%,rgba(225,225,225,1) 51%,rgba(246,246,246,1) 100%); /* IE10+ */
background: linear-gradient(to bottom, rgba(255,255,255,1) 0%,rgba(241,241,241,1) 50%,rgba(225,225,225,1) 51%,rgba(246,246,246,1) 100%); /* W3C */
filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ffffff', endColorstr='#f6f6f6',GradientType=0 ); /* IE6-9 */;margin-right:auto;margin-bottom:10px;margin-left:auto;text-align:center;width:300px;padding:5px;border:2px solid #ccc;border-radius:6px; }

</style>
<center>
<table width="634" class="burned" cellspacing="0" cellpadding="0"><tr><td>
<center><img src="images/burned_tower.jpg" width="350"><br />
<b>Etwas mystisches liegt in der Luft.</b><br />
Denkst du, dass du eines der 3 legendären Pokémon einfangen kannst?<br />
Glaube nicht, dass es einfach wird. Es werden eine Menge Trainer erscheinen, bis du auf eines der 3 mystischen Pokémon treffen wirst! <br />
Mal sehen was du drauf hast.
<br /><br />
<div class="burned-times">
Du hast heute noch <b><? echo $gebruiker['trainerb'] ?></b> Versuche.
</div>
</td></tr></table>
</center>
<center>
<table width="632" style="border: 1px solid #000000;box-shadow:0 0 5px #333;" cellspacing="0" cellpadding="0">
<tr>
<td><table width="632" border="0" cellspacing="0" cellpadding="0">
<tr>
<td><form method="post" name="Lavagrot"><input type="image" onClick="Lavagrot.submit();" src="images/burned/1.png" alt="Firecave" /><input type="hidden" value="1" name="gebied"></form></td>
<td><form method="post" name="Vechtschool"><input type="image" onClick="Vechtschool.submit();" src="images/burned/2.png" alt="Fighting gym" /><input type="hidden" value="2" name="gebied"></form></td>
</tr>
</table></td>
</tr>
</table>
</center>
<?
}
else{
echo '<div style="padding-top:10px;"><div class="blue"><img src="images/icons/blue.png" width="16" height="16" /> '.$txt['alert_no_pokemon'].'</div></div>';
}
?>
