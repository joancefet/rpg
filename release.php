<?php
#include dit script als je de pagina alleen kunt zien als je ingelogd bent.
include('includes/security.php');

#Als je geen pokemon bij je hebt, terug naar index.
if($gebruiker['in_hand'] == 0) header('Location: index.php');

$page = 'release';
#Goeie taal erbij laden voor de page
include_once('language/language-pages.php');

if($gebruiker['item_over'] < 1){
	echo '<div class="blue">'.$txt['alert_itemplace'].'</div>';
}

if(isset($_POST['submit'])){
	$update = mysql_fetch_assoc(mysql_query("SELECT wild_id, user_id, gehecht, gevongenmet FROM pokemon_speler WHERE id = '".$_POST['release']."'"));
	if(empty($_POST['release']))
		echo '<div class="red">'.$txt['alert_no_pokemon_selected'].'</div>';
	elseif($update['user_id'] != $_SESSION['id'])
    	echo' <div class="red">'.$txt['alert_not_your_pokemon'].'</div>';
	elseif($update['gehecht'] == 1)
		echo' <div class="red">'.$txt['alert_beginpokemon'].'</div>';
	else{
		#Ball teruggeven als er nog een itemplek over is
		if($gebruiker['item_over'] > 0)
		  mysql_query("UPDATE gebruikers_item SET `".$update['gevongenmet']."`=`".$update['gevongenmet']."`+'1' WHERE `user_id`='".$_SESSION['id']."'");
	  if(mysql_num_rows(mysql_query("SELECT id FROM pokemon_speler WHERE wild_id='".$update['wild_id']."'")) == 1)
		  update_pokedex($pokemon['wild_id'],'','release');
		#Alles verwijderen
		mysql_query("DELETE FROM pokemon_speler WHERE id = '".$_POST['release']."'");
		mysql_query("DELETE FROM transferlijst WHERE id = '".$_POST['release']."'");
		mysql_query("UPDATE `gebruikers` SET `aantalpokemon`=`aantalpokemon`-'1' WHERE `user_id` = '".$_SESSION['id']."'");

		echo '<div class="green">'.$txt['success_release'].'</div>';
	}
}


if(empty($_GET['subpage'])) $subpage = 1; 
else $subpage = $_GET['subpage']; 
#Max aantal leden per pagina
$max = 50; 
$aantal = mysql_num_rows(mysql_query("SELECT `id` FROM `pokemon_speler` WHERE `user_id`='".$_SESSION['id']."' AND `opzak`='nee'"));
$aantal_paginas = ceil($aantal/$max); 
if($aantal_paginas == 0) $aantal_paginas = 1;
$pagina = $subpage*$max-$max; 

?>
<form method="post">
  <center>
  <?php echo $txt['title_text']; ?><br /><br />
    <table width="390" cellpadding="0" cellspacing="0">
      <tr> 
        <td colspan="5"><center><strong><?php echo $txt['pokemon_team']; ?></strong></center><br /></td>
      </tr>
      <tr>
        <td width="50" class="top_first_td"><?php echo $txt['#']; ?></td>
        <td width="90" class="top_td"><?php echo $txt['pokemon']; ?></td>
        <td width="120" class="top_td"><?php echo $txt['clamour_name']; ?></td>
        <td width="60" class="top_td"><?php echo $txt['level']; ?></td>
        <td width="70" class="top_td"><center><?php echo $txt['release']; ?></center></td>
      </tr>
      <?php
 	  $tellerteam=0;
	  
      #Pokemon query ophalen	  
      
      while($pokemon = mysql_fetch_assoc($pokemon_sql)){
	  
	  $tellerteam++;
	  
        #Gegevens juist laden voor de pokemon
        $pokemon = pokemonei($pokemon);
        $pokemon['naam'] = pokemon_naam($pokemon['naam'],$pokemon['roepnaam']);
        $popup = pokemon_popup($pokemon, $txt);
		
        echo '<tr>
				<td class="normal_first_td">'.$tellerteam.'.</td>
				<td class="normal_td"><a href="#" class="tooltip" onMouseover="showhint(\''.$popup.'\', this)"><img src="'.$pokemon['animatie'].'" width=32 height=32></a></td>
				<td class="normal_td">'.$pokemon['naam'].'</td>
				<td class="normal_td">'.$pokemon['level'].'</td>
				<td class="normal_td"><center><input type="radio" name="release" value="'.$pokemon['id'].'"></center></td>
			  </tr>';
      }
      mysql_data_seek($pokemon_sql, 0);
	  if($tellerteam == 0) 
	  	echo '<tr>
				<td colspan="5" class="normal_td">'.$txt['alert_no_pokemon_in_hand'].'</td>
			  </tr>';
      ?>
      <tr>
      	<td colspan="9"><button type="submit" name="submit" class="button_mini" style="float:right;" ><?php echo $txt['button']; ?></button></td>
      </tr>
    </table>
        
    <br /><br />
        
    <table width="390" border="0" cellpadding="0" cellspacing="0">
      <tr> 
        <td colspan="5"><center><strong><?php echo $txt['pokemon_at_home']; ?></strong></center><br /></td>
      </tr>
      <tr>
        <td width="50" class="top_first_td"><?php echo $txt['#']; ?></td>
        <td width="90" class="top_td"><?php echo $txt['pokemon']; ?></td>
        <td width="120" class="top_td"><?php echo $txt['clamour_name']; ?></td>
        <td width="60" class="top_td"><?php echo $txt['level']; ?></td>
        <td width="70" class="top_td"><center><?php echo $txt['release']; ?></center></td>
      </tr>
      <?
      $poke = mysql_query("SELECT pokemon_speler.*, pokemon_wild.naam, pokemon_wild.type1, pokemon_wild.type2
							   FROM pokemon_speler
							   INNER JOIN pokemon_wild
							   ON pokemon_speler.wild_id = pokemon_wild.wild_id 
							   WHERE pokemon_speler.user_id='".$_SESSION['id']."' AND pokemon_speler.opzak='nee' 
							   ORDER BY pokemon_speler.opzak ASC , pokemon_speler.opzak_nummer
							   ASC LIMIT ".$pagina.", ".$max."");
    
      #Teller op 0 zetten
      $tellerhuis = 0;
      
      for($j=$pagina+1; $pokemon = mysql_fetch_assoc($poke); $j++){
        #Alle pokemons tellen
        $tellerhuis++;
        
        #Gegevens juist laden voor de pokemon
        $pokemon = pokemonei($pokemon);
        $pokemon['naam'] = pokemon_naam($pokemon['naam'],$pokemon['roepnaam']);
        $popup = pokemon_popup($pokemon, $txt);
        
        #Als pokemon geen baby is
        echo '<tr>
				<td class="normal_first_td">'.$tellerhuis.'.</td>
				<td class="normal_td"><a href="#" class="tooltip" onMouseover="showhint(\''.$popup.'\', this)"><img src="'.$pokemon['animatie'].'" width=32 height=32></a></td>
				<td class="normal_td">'.$pokemon['naam'].'</td>
				<td class="normal_td">'.$pokemon['level'].'</td>
				<td class="normal_td"><center><input type="radio" name="release" value="'.$pokemon['id'].'"></center></td>
			  </tr>';
	  }
	  if($tellerhuis == 0) 
	  	echo '<tr>
				<td colspan="5" class="normal_td">'.$txt['alert_no_pokemon_at_home'].'</td>
			  </tr>';
			?>
			<tr>
      	<td colspan="9"><button type="submit" name="submit" class="button_mini" style="float:right;" ><?php echo $txt['button']; ?></button></td>
      </tr>
		  <?	           
      #Pagina systeem
      $links = false;
      $rechts = false;
      echo '<tr><td colspan=5><center><br /><div class="sabrosus">';
      if($subpage == 1)
        echo '<span class="disabled"> &lt; </span>';
      else{
        $back = $subpage-1;
        echo '<a href="'.$_SERVER['PHP_SELF'].'?page='.$_GET['page'].'&subpage='.$back.'"> &lt; </a>';
      }
      for($i = 1; $i <= $aantal_paginas; $i++) { 
        if((2 >= $i) && ($subpage == $i))
          echo '<span class="current">'.$i.'</span>';
        elseif((2 >= $i) && ($subpage != $i))
          echo '<a href="'.$_SERVER['PHP_SELF'].'?page='.$_GET['page'].'&subpage='.$i.'">'.$i.'</a>';
        elseif(($aantal_paginas-2 < $i) && ($subpage == $i))
          echo '<span class="current">'.$i.'</span>';
        elseif(($aantal_paginas-2 < $i) && ($subpage != $i))
          echo '<a href="'.$_SERVER['PHP_SELF'].'?page='.$_GET['page'].'&subpage='.$i.'">'.$i.'</a>';
        else{
          $max = $subpage+3;
          $min = $subpage-3;  
          if($subpage == $i)
            echo '<span class="current">'.$i.'</span>';
          elseif(($min < $i) && ($max > $i))
          	echo '<a href="'.$_SERVER['PHP_SELF'].'?page='.$_GET['page'].'&subpage='.$i.'">'.$i.'</a>';
          else{
            if($i < $subpage){
              if(!$links){
                echo '...';
                $links = True;
              }
            }
            else{
              if(!$rechts){
                echo '...';
                $rechts = True;
              }
            }
          }
        }
      } 
      if($aantal_paginas == $subpage)
        echo '<span class="disabled"> &gt; </span>';
      else{
        $next = $subpage+1;
        echo '<a href="'.$_SERVER['PHP_SELF'].'?page='.$_GET['page'].'&subpage='.$next.'"> &gt; </a>';
      }
      echo "</div></center></td>";

      ?>
    </table>
  </center>
</form>