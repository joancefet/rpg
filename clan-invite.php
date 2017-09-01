<?
//Security laden
include('includes/security.php');

if(($gebruiker['rank'] < 5) ) header('Location: index.php');

#Load language
$page = 'clan-invite';
#Goeie taal erbij laden voor de page
include_once('language/language-pages.php');

$getname = $_GET['player'];

#gebruiker
$clanquery2 = mysql_query("SELECT clan FROM gebruikers WHERE username='".$_SESSION['naam']."'");
$clan = mysql_fetch_array($clanquery2);
#clan laden
$clanquery = mysql_query("SELECT * FROM clans WHERE clan_naam='".$clan['clan']."'");
$profiel = mysql_fetch_array($clanquery);
if(!empty($profiel)){
#clan aantal members bepalen
$clanmembers = 10*$profiel['clan_level'];
#invite left
$claninvites = $clanmembers - $profiel['clan_spelersaantal'];


if((isset($_POST['submit']))){
	
	$query ="SELECT max(invite_id) FROM clan_invites";
	$result = mysql_query($query) or die ("Error in query: $query. " .mysql_error());
	$row = mysql_fetch_row($result);
	$row2 = $row[0];
	$row3 = 1 + $row2;
	
	$invite_id				= $row3;
	$getname 				= $_POST['naam'];
	$time 					= time();
	$code 					= rand(100000,999999);
	
	$clanquery3 = mysql_query("SELECT clan FROM gebruikers WHERE username='".$_POST['naam']."'");
	$clan3 = mysql_fetch_array($clanquery3);
  
		#check of clanleader.
		if($_SESSION['naam'] != $profiel['clan_owner']){ 
			echo '<div class="red">Je bent geen clan leider.</div>';
		}
		#check of ingevuld
		elseif(empty($getname)){
			echo '<div class="red">Vul een naam in.</div>';
		}
		#check of de clan vol is.
		elseif($claninvites == 0){
			echo '<div class="red">Je clan is vol, Je moet je clan upgraden om voor meer leden.</div>';
		}
		#check of al in clan
		elseif($clan3['clan'] != ""){
			echo '<div class="red">De speler heeft al een clan.</div>';
		}
		#check of user bestaat
		elseif(mysql_num_rows($clanquery3) == 0){
			echo '<div class="red">De speler bestaat niet.</div>';
		}
		
		else{
		
		#opslaan in clan_invites
		mysql_query("INSERT INTO `clan_invites` (`invite_id`, `invite_clannaam`, `invite_usernaam`, `time`, `code`)
          VALUES ('".$invite_id."', '".$clan['clan']."', '".$getname."', '".$time."', '".$code."')");
		$claninputid = mysql_insert_id();
		
		#bericht sturen
		$event = '<img src="images/icons/blue.png" width="16" height="16" class="imglower"> '.$gebruiker['username'].' heeft jou uitgenodigd voor clan <strong>'.$clan['clan'].'</strong>.<a href="?page=clan-invite2&id='.$claninputid.'&code='.$code.'&accept=1">Accepteren</a>, <a href="?page=clan-invite2&id='.$claninputid.'&code='.$code.'&accept=0">Weigeren</a>.';
		#user laden
		$sql2 = mysql_query("SELECT user_id, wereld, land, rank, admin FROM gebruikers WHERE username='".$_POST['naam']."'");
		$select = mysql_fetch_assoc($sql2);
		#updaten
		mysql_query("INSERT INTO gebeurtenis (id, datum, ontvanger_id, bericht, gelezen)
			VALUES (NULL, NOW(), '".$select['user_id']."', '".$event."', '0')");
		
		echo '<div class="green">Uitnodiging verzonden naar '.$getname.'</div>';
		}
		

    }
  

?>

<form method="post">
  <center>
    <p><strong>Nodig een speler uit voor <?php echo $clan['clan']; ?>.</strong><br/><br/></p>
	<p>Een level <?php echo $profiel['clan_level']; ?> clan kan <?php echo (string)$clanmembers; ?> leden hebben.</p>
	<p>Je kan nog <?php echo (string)$claninvites; ?> leden uitnodigen.</p>
    <table width="300">
      <tr>
        <td><label for="naam"><img src="images/icons/user.png" width="16" height="16" alt="Player" class="imglower" /> <?php echo $txt['player']; ?></label></td>
        <td colspan="2"><input type="text" name="naam" id="naam" class="text_long" value="<?php echo $getname; ?>" /></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td colspan="2"><button type="submit" name="submit" class="button">Invite</button></td>
      </tr>
  </table>
  </form>
  </center>
  <center>
  </br>
  <?php 
  $number = 0;
$clanquery = mysql_query("SELECT * FROM clan_invites WHERE invite_clannaam = '".$clan['clan']."'  ORDER BY invite_id DESC") or die(mysql_error());
$tel = mysql_num_rows($clanquery);
  if($tel == 0 )
{ 
echo "<div class='red'>Geen uitstaande uitnodigingen.</div>";
}
else {
	?>
	<p><?php echo $txt['title_text']; ?></p>

	<table width="350" cellpadding="0" cellspacing="0">
		<tr>
			<td class="top_first_td" width="50"><?php echo '#'; ?></td>
			<td class="top_td" class="150"><?php echo 'Name'; ?></td>
			<td class="top_td" class="150"><?php echo 'Date'; ?></td>
		</tr>
		<?php
		while ($invite = mysql_fetch_array($clanquery)) {
//Kijken als je er zelf tussen zit, dan moet het dik gedrukt zijn.

			$number++;
			if ($clan['premiumaccount'] > 0) $premiumimg = '<img src="images/icons/lidbetaald.png" width="16" height="16" border="0" alt="Premiumlid" title="Premiumlid" style="margin-bottom:-3px;">';
			echo '<tr>
				<td class="normal_first_td">' . $number . '.</td>
				<td class="normal_td"><a href="?page=profile&player=' . $invite['invite_usernaam'] . '">' . $invite['invite_usernaam'] . '</a></td>
				<td class="normal_td">' . date("d-m-Y H:i", $invite['time']) . '</td>
			  </tr>';
		}
		?>
	</table>
	</center>
<?
	}
} else {
	echo "<center>Je hebt geen clan, maak <a href='?page=clan-make'>hier</a> een clan aan.</center>";
}
?>