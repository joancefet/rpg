<?
#include dit script als je de pagina alleen kunt zien als je ingelogd bent.
include('includes/security.php');

#Als je geen pokemon bij je hebt, terug naar index.
if($gebruiker['in_hand'] == 0) header('Location: index.php');

$page = 'name-specialist';
#Goeie taal erbij laden voor de page
include_once('language/language-pages.php');

if(isset($_POST['namenspecialist'])){
	if(empty($_POST['nummer'])) echo '<div class="red">'.$txt['alert_nothing_selected'].'</div>';
	else{
		foreach($_POST['nummer'] AS $nummer){
		#heeft Speler wel genoeg silver?
		if($gebruiker['silver'] < 40){
			echo '<div class="red">'.$txt['alert_not_enough_silver'].'</div>';
	  	}
      	#Naam lengte check?
	  	elseif(strlen($_POST['naam'.$nummer] > 12)){
			echo '<div class="red">'.$txt['alert_name_too_long'].'</div>';
	  	}
	  	else{
	  		#Check of pokemon wel van de betreffende speler is
 	 		$update = mysql_fetch_assoc(mysql_query("SELECT user_id FROM pokemon_speler WHERE id = '".$_POST['pokemonid'.$nummer]."'"));
  
			if($update['user_id'] != $_SESSION['id'])
    		echo ' <div class="red"><img src="images/icons/red.png"> '.$txt['alert_not_your_pokemon'].'</div>';
      		#Nieuwe naam opslaan
      	else{
        	mysql_query("UPDATE `pokemon_speler` SET `roepnaam`='".$_POST['naam'.$nummer]."' WHERE `id`='".$_POST['pokemonid'.$nummer]."'");
        	mysql_query("UPDATE `gebruikers` SET `silver`=`silver`-'40' WHERE `user_id`='".$_SESSION['id']."'");
        	echo '<div class="green">'.$txt['success_namespecialist'].' '.$_POST['naam'.$nummer].'</div>';
	  	}
      }
    }
  }
}
?>
<center>
  <p><?php echo $txt['title_text']; ?> <img src="images/icons/silver.png"title="Silver" /> 40.</p></center>
  
  <center>
   <table width="260" cellpadding="0" cellspacing="0">
    <form method="post">
      <tr>
        <td width="50" class="top_first_td"><?php echo $txt['#']; ?></td>
        <td width="60" class="top_td">&nbsp;</td>
        <td width="150" class="top_td"><?php echo $txt['name_now']; ?></td>
      </tr>
      <?php
      
      mysql_data_seek($pokemon_sql, 0);
      for($teller=0; $pokemon = mysql_fetch_assoc($pokemon_sql); $teller++){
        $pokemon = pokemonei($pokemon);
        $pokemon['naam'] = pokemon_naam($pokemon['naam'],$pokemon['roepnaam']);
		$popup = pokemon_popup($pokemon, $txt);
                      
        echo "<tr>";
          #Als pokemon geen baby is
          if($pokemon['ei'] != 1)
            echo '<td class="normal_first_td"><input type="radio" name="nummer[]" value="'.$teller.'"/></td>';              
          else
            echo '<td class="normal_first_td"><input type="radio" name="pokemonid" disabled/></td>';

        echo '
          <td class="normal_td"><a href="#" class="tooltip" onMouseover="showhint(\''.$popup.'\', this)"><img src="'.$pokemon['animatie'].'"></a></td>
          <td class="normal_td">
            <input type="text" name="naam'.$teller.'" value="'.$pokemon['naam'].'" class="text_long" maxlength="12" />
            <input type="hidden" name="pokemonid'.$teller.'" value="'.$pokemon['id'].'" />
          </td>
        </tr>';
      }
	  mysql_data_seek($pokemon_sql, 0);
      ?>
      <tr>
        <td colspan="2"></td>
        <td><button type="submit" name="namenspecialist" class="button" ><?php echo $txt['button']; ?>shin</button></td>
      </tr>
  </form>
</table>
</center>