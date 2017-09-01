<?
session_start();

include_once('includes/config.php');
include_once('includes/ingame.inc.php');
include_once('includes/globaldefs.php');

$error = "<center>
<h2>Arceus Form</h2>
Kies een Pokemon dat de <b><img src='images/items/".$_GET['name'].".png'> ".$_GET['name']."</b> moeten krijgen.";
$gebruiker_item = mysql_fetch_array(mysql_query("SELECT * FROM `gebruikers_item` WHERE `user_id`='".$_SESSION['id']."'"));
if($gebruiker_item[$_GET['name']] <= 0){
	header("Location: index.php?page=home");
	?>
  <script>  
  	parent.$.fn.colorbox.close();
  </script>
  <?
}

$button = true;

//Afbreken
if(isset($_POST['nee'])){
  ?>
  <script>  
  	parent.$.fn.colorbox.close();
  </script>
  <?
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "https://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="https://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta http-equiv="content-style-type" content="text/css" />
    <meta http-equiv="content-language" content="nl" />
    <meta name="description" content="" />
    <meta name="abstract" content="" />
    <meta name="keywords" content="" />
    <meta name="robots" content="index, follow" />
    <meta name="copyright" content="(c) 2010" />
    <meta name="language" content="nl" />
    <title><?=GLOBALDEF_SITETITLE?></title>
    <link rel="stylesheet" type="text/css" href="../stylesheets/main.css" />
  </head>
  
  <body style="background:#fff;">
  <?
//Als een pokemon moet evolueren met de steen
if(isset($_POST['zeker'])){
  //Gegevens laden van de des betreffende pokemon
  $pokemon = mysql_fetch_array(mysql_query("SELECT pokemon_wild.* ,pokemon_speler.*, karakters.* FROM pokemon_wild INNER JOIN pokemon_speler ON pokemon_speler.wild_id = pokemon_wild.wild_id INNER JOIN karakters ON pokemon_speler.karakter = karakters.karakter_naam WHERE pokemon_speler.id='".$_POST['pokemonid']."'"));
  //Gegevens halen uit de levelen tabel
  $levelensql = mysql_query("SELECT nieuw_id FROM `levelen` WHERE `id`='".$_POST['levelenid']."'");
  $levelen = mysql_fetch_array($levelensql);
  if(empty($_POST['pokemonid']))
    $error = 'FOUT 2!<br /> Code: '.$_POST['pokemonid'].'<br />Fehler, bitte an Spooky melden!';
  elseif(mysql_num_rows($levelensql) == 1){
    $update = mysql_fetch_array(mysql_query("SELECT * FROM `pokemon_wild` WHERE `wild_id`='".$levelen['nieuw_id']."'"));
	
    //Formule Stats = int((int(int(A*2+B+int(C/4))*D/100)+5)*E)    
    $attackstat     = round(((((($update['attack_base']*2+$pokemon['attack_iv']+floor($pokemon['attack_ev']/4))*$pokemon['level']/100)+5)*1)+$pokemon['attack_up'])*$pokemon['attack_add']);
    $defencestat    = round(((((($update['defence_base']*2+$pokemon['defence_iv']+floor($pokemon['defence_ev']/4))*$pokemon['level']/100)+5)*1)+$pokemon['defence_up'])*$pokemon['defence_add']);
    $speedstat      = round(((((($update['speed_base']*2+$pokemon['speed_iv']+floor($pokemon['speed_ev']/4))*$pokemon['level']/100)+5)*1)+$pokemon['speed_up'])*$pokemon['speed_add']);
    $spcattackstat  = round(((((($update['spc.attack_base']*2+$pokemon['spc.attack_iv']+floor($pokemon['spc.attack_ev']/4))*$pokemon['level']/100)+5)*1)+$pokemon['spc_up'])*$pokemon['spc.attack_add']);
    $spcdefencestat = round(((((($update['spc.defence_base']*2+$pokemon['spc.defence_iv']+floor($pokemon['spc.defence_ev']/4))*$pokemon['level']/100)+5)*1)+$pokemon['spc_up'])*$pokemon['spc.defence_add']);
    $hpstat         = round((((($update['hp_base']*2+$pokemon['hp_iv']+floor($pokemon['hp_ev']/4))*$pokemon['level']/100)+$pokemon['level'])+10)+$pokemon['hp_up']);
      
    //Pokemon gegevens en nieuwe Stats opslaan
    mysql_query("UPDATE `pokemon_speler` SET `wild_id`='".$levelen['nieuw_id']."', `attack`='".$attackstat."', `defence`='".$defencestat."', `speed`='".$speedstat."', `spc.attack`='".$spcattackstat."', `spc.defence`='".$spcdefencestat."', `levenmax`='".$hpstat."', `leven`='".$hpstat."' WHERE `id`='".$pokemon['id']."'");
    //Pokemon opslaan als in bezit
    update_pokedex($update['wild_id'],$pokemon['wild_id'],'evo');
    //Stone weg
    mysql_query("UPDATE `gebruikers_item` SET `".$_POST['item']."`=`".$_POST['item']."`-'1' WHERE `user_id`='".$_SESSION['id']."'");
    //Post leeg maken.
    unset($_POST['zeker']);
    
    $error = '<div class="green"><img src="../images/icons/green.png"> Gefeliciteerd, je hebt <strong>'.$pokemon['naam'].'</strong> heeft met de'.$_POST['item'].' in een <strong>'.$update['naam'].'</strong> ontwikkeld!</div>';
  }
  else{
    $error = 'FOUT 1!<br /> Code: '.$_POST['levelenid'].'<br /> Neem contact op met de beheerder';
  }
  ?>
  <center>
  <table width="500" border="0">
  	<tr>
  		<td colspan="3"><? if($error) echo $error; else echo "&nbsp"; ?></td>
  	</tr>
  	<tr>
  		<td width="200"><center><img src="../images/<?php if($pokemon['shiny'] == 1) echo 'shiny'; else echo 'pokemon'; ?>/<? echo $pokemon['wild_id']; ?>.png" width="130" height="120" /></center></td>
  		<td width="86"><center><img src="../images/icons/pijl_rechts.png" /></center></td>
  		<td width="200"><center><img src="../images/<?php if($update['shiny'] == 1) echo 'shiny'; else echo 'pokemon'; ?>/<? echo $update['wild_id']; ?>.png" width="130" height="120" /></center></td>
  	</tr>
  </table>
  </center>
  <?
}
else{
	if(isset($_POST['evolve'])){
  	list ($pokemonid, $pokemonnaam, $wildid, $pokelvl) = split ('[/]', $_POST['pokemonid']);
########
if($_POST['plate']=='Wiesentafel') $vorm='Pflanze';
if($_POST['plate']=='Feuertafel') $vorm='Feuer';
if($_POST['plate']=='Wassertafel') $vorm='Wasser';
if($_POST['plate']=='Wolkentafel') $vorm='Flug';
if($_POST['plate']=='Kaefertafel') $vorm='K&auml;fer';
if($_POST['plate']=='Gifttafel') $vorm='Gift';
if($_POST['plate']=='Blitztafel') $vorm='Elektro';
if($_POST['plate']=='Hirntafel') $vorm='Psycho';
if($_POST['plate']=='Steintafel') $vorm='Gestein';
if($_POST['plate']=='Erdtafel') $vorm='Boden';
if($_POST['plate']=='Furchttafel') $vorm='Unlicht';
if($_POST['plate']=='Spuktafel') $vorm='Geist';
if($_POST['plate']=='Eisentafel') $vorm='Stahl';
if($_POST['plate']=='Fausttafel') $vorm='Kampf';
if($_POST['plate']=='Frosttafel') $vorm='Eis';
if($_POST['plate']=='Dracotafel') $vorm='Drache';
########
    if(empty($_POST['coin'])) echo '<div class="red">Kies een betaalmethode ( goud / zilver )</div>';
    elseif(empty($_POST['pokemonid'])) echo '<div class="red">Je hebt geen Pokemon gekozen!</div>';
    else {
    mysql_query("UPDATE `gebruikers_item` SET `".$_POST['plate']."`=`".$_POST['plate']."`-'1' WHERE `user_id`='".$_SESSION['id']."'");
    mysql_query("UPDATE `pokemon_speler` SET `vorm`='".$vorm."' WHERE `id`='".$pokemonid."'");
    if($_POST['coin']=='gold') {
    $msgg = '85 Gold';
    mysql_query("UPDATE `gebruikers` SET `gold`=`gold`-'85' WHERE `user_id`='".$_SESSION['id']."'");
    }
    elseif($_POST['coin']=='silver') {
     $msgg = '120,000 Silber';
    mysql_query("UPDATE `gebruikers` SET `silver`=`silver`-'120000' WHERE `user_id`='".$_SESSION['id']."'");
 	}
    echo '<div class="green">Gefeliciteerd, je hebt je Pokemon met behulp van de '.$_POST['plate'].' en '.$msgg.' in de '.ucfirst($vorm).' ontwikkeld.</div>';
		}
  }
  else{
  ?>

    <form method="post" name="useitem">
    <center>
<table width="500" border="0" cellspacing="0" cellpadding="0">
    	<tr> 
    		<td colspan="5"><? if($error) echo $error; else echo "&nbsp"; ?><br />
    		 <b>Kies een betalingsmethode: </b><br />
    		<input type="radio" name="coin" value="gold" id="gold" /><label for="gold"> <img src="images/icons/gold.png" /> 85 Gold <b>of</b></label><br />
    		<input type="radio" name="coin" value="silver" id="silver" /><label for="silver"> <img src="images/icons/silver.png" /> 120,000 Silver</label>
    		</center>
    		</td>
    	</tr>
    	<tr> 
        <td width="50" class="top_first_td" style="background:#eee;"><center><strong>&raquo;</strong></center></td>
		<td width="100" align="center" class="top_td" style="background:#eee;"><strong>Pokémon</strong></td>
		<td width="150" align="center" class="top_td" style="background:#eee;"><strong>Naam</strong></td>
    	<td width="100" align="center" class="top_td" style="background:#eee;"><strong>Level</strong></td>
            	<td width="100" align="center" class="top_td" style="background:#eee;"><strong>Kiezen</strong></td>
    	</tr>
    	<tr>
    <?
    //Pokemon laden van de gebruiker die hij opzak heeft
    $poke = mysql_query("SELECT pokemon_wild.* ,pokemon_speler.* FROM pokemon_wild INNER JOIN pokemon_speler ON pokemon_speler.wild_id = pokemon_wild.wild_id WHERE user_id='".$_SESSION['id']."' AND `opzak`='ja' ORDER BY `opzak_nummer` ASC");
    
    //Pokemons die hij opzak heeft weergeven  
    for($teller=0; $pokemon = mysql_fetch_array($poke); $teller++){
      $kan = "<img src='../images/icons/red.png' alt='Funktioniert nicht'>";
      $disabled = 'disabled';   
      //Als er een result is kan pokemon evolueren.
      $stoneevolvesql = mysql_query("SELECT `id`, `stone`, `nieuw_id` FROM `levelen` WHERE `wild_id`='".$pokemon['wild_id']."' AND `stone`='".$_GET['name']."'");
      $stoneevolve = mysql_fetch_array($stoneevolvesql);
      
      //Heeft de stone werking?
      if($pokemon['wild_id']==493){
      	$kan = "<img src='../images/icons/green.png' alt='Funktioniert'>";
      	$disabled = '';
      }
    
      //Als pokemon geen baby is
      if($pokemon['ei'] != 1){
        echo '
          <td><center><input type="hidden" name="plate" value="'.$_GET['name'].'">
          <input type="radio" name="pokemonid" value="'.$pokemon['id'].'/'.$pokemon['naam'].'/'.$pokemon['wild_id'].'/'.$pokemon['level'].'" '.$disabled.'/>
          <input type="hidden" name="pokemonnaam" value="'.$pokemon['naam'].'"></center></td>
        ';             
      }
      else
        echo '<td><center><input type="radio" id="niet'.$i.'" name="niet" disabled/></center></td></td>';
      
      $pokemon = pokemonei($pokemon);
      $pokemon['naam_goed'] = pokemon_naam($pokemon['naam'],$pokemon['roepnaam']);
                          $popup = pokemon_popup($pokemon, $txt);
                          if($pokemon['vorm']=='') { $pokemon['vorm'] = ''; }
                          else { $pokemon['vorm']=' ('.ucfirst($pokemon['vorm']).')'; }
      echo '
        <td><center><img src="../'.$pokemon['animatie'].'" width="32" height="32" onMouseover="showhint(\''.$popup.'\', this)"></center></td>
        <td dir="ltr">'.$pokemon['naam_goed'].$pokemon['vorm'].'</td>
        <td align="center">'.$pokemon['level'].'</td>
      ';
      
      //Als pokemon geen baby is
      if($pokemon['ei'] != 1) echo '<td align="center">'.$kan.'</td>';
      else echo '<td>Fout</td>';
      	
      echo '</tr>';
    }
    
    if($button){
      ?>
      <tr> 
        <td colspan="5"><input type="hidden" name="item" value="<? echo $_GET['name']; ?>">
        <input type="submit" name="evolve" value="Ontwikkelen!" class="button"></td>
      </tr>
      <?
    }
    ?>
    </table>
    </center>
    </form>
    <?
    }
  }
?>
</body></html>