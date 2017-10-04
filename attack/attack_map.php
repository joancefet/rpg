<?php
//Script laden zodat je nooit pagina buiten de index om kan laden
include("includes/security.php");

//Kijken of je wel pokemon bij je hebt
if ($gebruiker['in_hand'] == 0) header('location: index.php');

$page = 'attack/attack_map';
//Goeie taal erbij laden voor de page
include_once('language/language-pages.php');
include_once("attack/wild/wild-start.php");

$countPokemon = $db->prepare("SELECT `id` FROM `pokemon_speler` WHERE `user_id`=:user_id AND `ei`='0' AND `opzak`='ja'");
$countPokemon->bindValue(':user_id', $_SESSION['id'], PDO::PARAM_INT);
$countPokemon->execute();
$countPokemon = $countPokemon->fetchAll();

if ($countPokemon) {
    if ((isset($_POST['gebied'])) && (is_numeric($_POST['gebied']))) {
        if ($_POST['gebied'] == 1) $gebied = 'Lavagrot';
        elseif ($_POST['gebied'] == 2) $gebied = 'Vechtschool';
        elseif ($_POST['gebied'] == 3) $gebied = 'Gras';
        elseif ($_POST['gebied'] == 4) $gebied = 'Spookhuis';
        elseif ($_POST['gebied'] == 5) $gebied = 'Grot';
        elseif ($_POST['gebied'] == 6) $gebied = 'Water';
        elseif ($_POST['gebied'] == 7) $gebied = 'Strand';


        if ($gebruiker['in_hand'] == 0)
            echo '<div class="blue"><img src="images/icons/blue.png"> ' . $txt['alert_no_pokemon'] . '</div>';
        elseif (($gebied == 'Water') AND ($gebruiker['Fishing rod'] == 0))
            $error = '<div class="red">' . $txt['alert_no_fishing_rod'] . '</div>';
        elseif (($gebied == 'Grot' || $_POST['gebied'] == 'Lavagrot') AND ($gebruiker['Cave suit'] == 0))
            $error = '<div class="red">' . $txt['alert_no_cave_suit'] . '</div>';
        else {
            //Zeldzaamheid bepalen
            $zeldzaam = rand(1, 1000);
            $trainer = 0;

            //tussen 0 en 50 een trainer = 50:1000 kans
            if (($zeldzaam > 0 && $zeldzaam < 50)){
            
                $trainer = 1;
                
            //tussen 50 en 53 een zeer zeldzaam = 3:1000 kans
            } elseif (($zeldzaam > 50 && $zeldzaam < 53)){
            
                $zeldzaamheid = 3;
                
            //tussen 53 en 253 een zeldzaam = 200:1000 kans
            } elseif (($zeldzaam > 53 && $zeldzaam < 253)){
            
                $zeldzaamheid = 2;
                
            //tussen 253 en 1000 een normale = 747:1000 kans
            } else{
            
                $zeldzaamheid = 1;
                
            }
            //for logging
            $legend = "Nee";

            //legendkans vergroter actief
            if ((3 * 3600) + $gebruiker['legendkans'] >= time()) {
            
                //tussen 0 en 50 een trainer = 50:1000 kans
                if (($zeldzaam > 0 && $zeldzaam < 50)){
            
                    $trainer = 1;
                
                //tussen 50 en 60 een zeer zeldzaam = 10:1000 kans
                } elseif (($zeldzaam > 50 && $zeldzaam < 60)){
                
                    $zeldzaamheid = 3;
                
                //tussen 60 en 260 een zeldzaam = 200:1000 kans
                } elseif (($zeldzaam > 60 && $zeldzaam < 260)){
                
                    $zeldzaamheid = 2;
                    
                //tussen 260 en 1000 een normale = 725:1000 kans
                } else{
                
                    $zeldzaamheid = 1;
                    
                }
                $legend = "Ja";
            }
            
            if($zeldzaamheid == ''){
                $zeldzaamheid = 1;
            }

            if ($trainer == 1) {

                $getTrainer = $db->prepare("SELECT `naam` FROM `trainer` WHERE `badge`='' AND (`gebied`=:gebied OR `gebied`='All') ORDER BY rand() limit 1");
                $getTrainer->bindValue(':gebied', $gebied, PDO::PARAM_STR);
                $getTrainer->execute();
                $getTrainer = $getTrainer->fetchAll();

                include('attack/trainer/trainer-start.php');

                $pokemonSelect = $pokemon_sql->fetchAll();

                $opzak = count($pokemonSelect);
                $level = 0;
                foreach ($pokemonSelect as $pokemon) {
                    $level += $pokemon['level'];
                }
                $trainer_ave_level = $level / $opzak;
                //Make Fight
                $info = create_new_trainer_attack($getTrainer['naam'], $trainer_ave_level, $gebied);
                if (empty($info['bericht'])) header("Location: ?page=attack/trainer/trainer-attack");
                else echo "<div class='red'>" . $txt['alert_no_pokemon'] . "</div>";
            } else {
                if (($gebruiker['rank'] > 15) && (!empty($gebruiker['lvl_choose']))) {
                    $level = explode("-", $gebruiker['lvl_choose']);
                    $leveltegenstander = rand($level[0], $level[1]);
                } else $leveltegenstander = rankpokemon($gebruiker['rank']);

                function getPokemon($zeldzaamheid, $wereld, $gebied) {
                    global $db;

                    $getPokemon = $db->prepare("SELECT wild_id,zeldzaamheid FROM `pokemon_wild` 
                    WHERE `wereld`=:wereld
                    AND `zeldzaamheid`=:zeldzaamheid
                    AND `zeldzaamheid` != 4
                    AND (`gebied`=:gebied OR `gebied`='')");
                    $getPokemon->bindValue(':zeldzaamheid', $zeldzaamheid, PDO::PARAM_INT);
                    $getPokemon->bindValue(':wereld', $wereld, PDO::PARAM_STR);
                    $getPokemon->bindValue(':gebied', $gebied, PDO::PARAM_STR);
                    $getPokemon->execute();
                    $query = $getPokemon->fetchAll();

                    return $query;
                }

                while(true){

                    $query = getPokemon($zeldzaamheid, $gebruiker['wereld'], $gebied);

                    if($query) {
                        break;
                    }
                    if(empty($query) and $zeldzaamheid == 1) {
                        $zeldzaamheid = rand(2,3);
                        $query = getPokemon($zeldzaamheid, $gebruiker['wereld'], $gebied);
                        if($query){
                            break;
                        }
                    }
                    if(empty($query) and $zeldzaamheid == 2) {
                        $random = rand(1,2);
                        if($random == 1){
                            $zeldzaamheid = 3;
                        }else{
                            $zeldzaamheid = 1;
                        }
                        $query = getPokemon($zeldzaamheid, $gebruiker['wereld'], $gebied);
                        if($query){
                            break;
                        }
                    }
                    if(empty($query) and $zeldzaamheid == 3) {
                        $zeldzaamheid = rand(1,2);
                        $query = getPokemon($zeldzaamheid, $gebruiker['wereld'], $gebied);
                        if($query){
                            break;
                        }
                    }
                }

                $query = $query[array_rand($query)];

                if ($zeldzaamheid == 3) {
                    $zzchip = 'Zeer zeldzaam';
                } elseif ($zeldzaamheid == 2) {
                    $zzchip = 'Beetje zeldzaam';
                } else {
                    $zzchip = 'Niet zeldzaam';
                }
                if (($gebruiker['Pokedex zzchip'] == 1) AND ($gebruiker['Pokedex'] == 1)) {
                    $_SESSION['zzchip'] = $zzchip;
                } else {
                    $_SESSION['zzchip'] = "??";
                }


                $info = create_new_attack($query['wild_id'], $leveltegenstander, $gebied);
                if (empty($info['bericht'])) {
                       echo '<script type="text/javascript">
                            window.location.href = \'?page=attack/wild/wild-attack\';
                            </script>';
                } else {
                    echo "<div class='red'>" . $txt['alert_no_pokemon'] . "</div>";
                }

            }
        }
    }
    if (isset($error)) {
        echo $error;
    }
    ?>
    <center>
        <table width="600" border="0">
            <tr>
                <td>
                    <center><?php echo $txt['title_text']; ?><br/><br/><a href='?page=pokemoncenter'><img
                                src='/images/pokemoncenter.gif' title='Pokemoncenter'><br/>Naar het Pok&eacute;moncenter.</a><br/><br/>Zeer
                        zeldzame Pok&eacute;mon kans: <b>3:1000</b><br/><br/></center>
                </td>
            </tr>
        </table>
    </center>
    <?php
    if ($gebruiker['wereld'] == "Kanto") {
        echo "<center>
    <table width='580' style='border: 1px solid #000000;' cellspacing='0' cellpadding='0'>
      <tr>
        <td><table width='580' border='0' cellspacing='0' cellpadding='0'>
          <tr>
            <td width='346' height='179'><form method='post' name='Lavagrot'><input type='image' onClick='Lavagrot.submit();' src='images/attackmap/kanto/lavagrot.gif' width='346' height='179' alt='Firecave' /><input type='hidden' value='1' name='gebied'></form></td>
            <td width='234' height='179'><form method='post' name='Vechtschool'><input type='image' onClick='Vechtschool.submit();' src='images/attackmap/kanto/vechtschool.gif' width='234' height='179' alt='Fighting gym' /><input type='hidden' value='2' name='gebied'></form></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table width='580' border='0' cellspacing='0' cellpadding='0'>
          <tr>
            <td width='216' height='249'><form method='post' name='Gras'><input type='image' onClick='Gras.submit();' src='images/attackmap/kanto/grasveld.gif' width='216' height='249' alt='Grass field' /><input type='hidden' value='3' name='gebied'></form></td>
            <td width='123' height='249'><form method='post' name='Spookhuis'><input type='image' onClick='Spookhuis.submit();' src='images/attackmap/kanto/spookhuis.gif' width='123' height='249' alt='Ghosthouse' /><input type='hidden' value='4' name='gebied'></form></td>
            <td width='241' height='249'><form method='post' name='Grot'><input type='image' onClick='Grot.submit();' src='images/attackmap/kanto/grot.gif' width='241' height='249' alt='Cave' /><input type='hidden' value='5' name='gebied'></form></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table width='580' border='0' cellspacing='0' cellpadding='0'>
          <tr>
            <td width='305'height='172'><form method='post' name='Water'><input type='image' onClick='Water.submit();' src='images/attackmap/kanto/water.gif' width='305' height='172' alt='Water' /><input type='hidden' value='6' name='gebied'></form></td>
            <td width='275' height='172'><form method='post' name='Strand'><input type='image' onClick='Strand.submit();' src='images/attackmap/kanto/strand.gif' width='275' height='172' alt='Beach'/><input type='hidden' value='7' name='gebied'></form></td>
          </tr>
        </table></td>
      </tr>
    </table>
	</center>";
    } elseif ($gebruiker['wereld'] == "Johto") {
        echo "<center>
    <table width='580' style='border: 1px solid #000000;' cellspacing='0' cellpadding='0'>
      <tr>
        <td><table width='580' border='0' cellspacing='0' cellpadding='0'>
          <tr>
            <td width='346' height='179'><form method='post' name='Lavagrot'><input type='image' onClick='Lavagrot.submit();' src='images/attackmap/johto/lavagrot.gif' width='346' height='179' alt='Firecave' /><input type='hidden' value='1' name='gebied'></form></td>
            <td width='234' height='179'><form method='post' name='Vechtschool'><input type='image' onClick='Vechtschool.submit();' src='images/attackmap/johto/vechtschool.gif' width='234' height='179' alt='Fighting gym' /><input type='hidden' value='2' name='gebied'></form></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table width='580' border='0' cellspacing='0' cellpadding='0'>
          <tr>
            <td width='216' height='249'><form method='post' name='Gras'><input type='image' onClick='Gras.submit();' src='images/attackmap/johto/grasveld.gif' width='216' height='249' alt='Grass field' /><input type='hidden' value='3' name='gebied'></form></td>
            <td width='123' height='249'><form method='post' name='Spookhuis'><input type='image' onClick='Spookhuis.submit();' src='images/attackmap/johto/spookhuis.gif' width='123' height='249' alt='Ghosthouse' /><input type='hidden' value='4' name='gebied'></form></td>
            <td width='241' height='249'><form method='post' name='Grot'><input type='image' onClick='Grot.submit();' src='images/attackmap/johto/grot.gif' width='241' height='249' alt='Cave' /><input type='hidden' value='5' name='gebied'></form></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table width='580' border='0' cellspacing='0' cellpadding='0'>
          <tr>
            <td width='305'height='172'><form method='post' name='Water'><input type='image' onClick='Water.submit();' src='images/attackmap/johto/water.gif' width='305' height='172' alt='Water' /><input type='hidden' value='6' name='gebied'></form></td>
            <td width='275' height='172'><form method='post' name='Strand'><input type='image' onClick='Strand.submit();' src='images/attackmap/johto/strand.gif' width='275' height='172' alt='Beach'/><input type='hidden' value='7' name='gebied'></form></td>
          </tr>
        </table></td>
      </tr>
    </table>
	</center>";
    } elseif ($gebruiker['wereld'] == "Hoenn") {
        echo "<center>
    <table width='580' style='border: 1px solid #000000;' cellspacing='0' cellpadding='0'>
      <tr>
        <td><table width='580' border='0' cellspacing='0' cellpadding='0'>
          <tr>
            <td width='346' height='179'><form method='post' name='Lavagrot'><input type='image' onClick='Lavagrot.submit();' src='images/attackmap/hoenn/lavagrot.gif' width='346' height='179' alt='Firecave' /><input type='hidden' value='1' name='gebied'></form></td>
            <td width='234' height='179'><form method='post' name='Vechtschool'><input type='image' onClick='Vechtschool.submit();' src='images/attackmap/hoenn/vechtschool.gif' width='234' height='179' alt='Fighting gym' /><input type='hidden' value='2' name='gebied'></form></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table width='580' border='0' cellspacing='0' cellpadding='0'>
          <tr>
            <td width='216' height='249'><form method='post' name='Gras'><input type='image' onClick='Gras.submit();' src='images/attackmap/hoenn/grasveld.gif' width='216' height='249' alt='Grass field' /><input type='hidden' value='3' name='gebied'></form></td>
            <td width='123' height='249'><form method='post' name='Spookhuis'><input type='image' onClick='Spookhuis.submit();' src='images/attackmap/hoenn/spookhuis.gif' width='123' height='249' alt='Ghosthouse' /><input type='hidden' value='4' name='gebied'></form></td>
            <td width='241' height='249'><form method='post' name='Grot'><input type='image' onClick='Grot.submit();' src='images/attackmap/hoenn/grot.gif' width='241' height='249' alt='Cave' /><input type='hidden' value='5' name='gebied'></form></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table width='580' border='0' cellspacing='0' cellpadding='0'>
          <tr>
            <td width='305'height='172'><form method='post' name='Water'><input type='image' onClick='Water.submit();' src='images/attackmap/hoenn/water.gif' width='305' height='172' alt='Water' /><input type='hidden' value='6' name='gebied'></form></td>
            <td width='275' height='172'><form method='post' name='Strand'><input type='image' onClick='Strand.submit();' src='images/attackmap/hoenn/strand.gif' width='275' height='172' alt='Beach'/><input type='hidden' value='7' name='gebied'></form></td>
          </tr>
        </table></td>
      </tr>
    </table>
	</center>";
    } elseif ($gebruiker['wereld'] == "Sinnoh") {
        echo "<center>
    <table width='580' style='border: 1px solid #000000;' cellspacing='0' cellpadding='0'>
      <tr>
        <td><table width='580' border='0' cellspacing='0' cellpadding='0'>
          <tr>
            <td width='346' height='179'><form method='post' name='Lavagrot'><input type='image' onClick='Lavagrot.submit();' src='images/attackmap/sinnoh/lavagrot.gif' width='346' height='179' alt='Firecave' /><input type='hidden' value='1' name='gebied'></form></td>
            <td width='234' height='179'><form method='post' name='Vechtschool'><input type='image' onClick='Vechtschool.submit();' src='images/attackmap/sinnoh/vechtschool.gif' width='234' height='179' alt='Fighting gym' /><input type='hidden' value='2' name='gebied'></form></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table width='580' border='0' cellspacing='0' cellpadding='0'>
          <tr>
            <td width='216' height='249'><form method='post' name='Gras'><input type='image' onClick='Gras.submit();' src='images/attackmap/sinnoh/grasveld.gif' width='216' height='249' alt='Grass field' /><input type='hidden' value='3' name='gebied'></form></td>
            <td width='123' height='249'><form method='post' name='Spookhuis'><input type='image' onClick='Spookhuis.submit();' src='images/attackmap/sinnoh/spookhuis.gif' width='123' height='249' alt='Ghosthouse' /><input type='hidden' value='4' name='gebied'></form></td>
            <td width='241' height='249'><form method='post' name='Grot'><input type='image' onClick='Grot.submit();' src='images/attackmap/sinnoh/grot.gif' width='241' height='249' alt='Cave' /><input type='hidden' value='5' name='gebied'></form></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table width='580' border='0' cellspacing='0' cellpadding='0'>
          <tr>
            <td width='305'height='172'><form method='post' name='Water'><input type='image' onClick='Water.submit();' src='images/attackmap/sinnoh/water.gif' width='305' height='172' alt='Water' /><input type='hidden' value='6' name='gebied'></form></td>
            <td width='275' height='172'><form method='post' name='Strand'><input type='image' onClick='Strand.submit();' src='images/attackmap/sinnoh/strand.gif' width='275' height='172' alt='Beach'/><input type='hidden' value='7' name='gebied'></form></td>
          </tr>
        </table></td>
      </tr>
    </table>
	</center>";
    } elseif ($gebruiker['wereld'] == "Unova") {
        echo "<center>
    <table width='580' style='border: 1px solid #000000;' cellspacing='0' cellpadding='0'>
      <tr>
        <td><table width='580' border='0' cellspacing='0' cellpadding='0'>
          <tr>
            <td width='346' height='179'><form method='post' name='Lavagrot'><input type='image' onClick='Lavagrot.submit();' src='images/attackmap/unova/lavagrot.gif' width='346' height='179' alt='Firecave' /><input type='hidden' value='1' name='gebied'></form></td>
            <td width='234' height='179'><form method='post' name='Vechtschool'><input type='image' onClick='Vechtschool.submit();' src='images/attackmap/unova/vechtschool.gif' width='234' height='179' alt='Fighting gym' /><input type='hidden' value='2' name='gebied'></form></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table width='580' border='0' cellspacing='0' cellpadding='0'>
          <tr>
            <td width='216' height='249'><form method='post' name='Gras'><input type='image' onClick='Gras.submit();' src='images/attackmap/unova/grasveld.gif' width='216' height='249' alt='Grass field' /><input type='hidden' value='3' name='gebied'></form></td>
            <td width='123' height='249'><form method='post' name='Spookhuis'><input type='image' onClick='Spookhuis.submit();' src='images/attackmap/unova/spookhuis.gif' width='123' height='249' alt='Ghosthouse' /><input type='hidden' value='4' name='gebied'></form></td>
            <td width='241' height='249'><form method='post' name='Grot'><input type='image' onClick='Grot.submit();' src='images/attackmap/unova/grot.gif' width='241' height='249' alt='Cave' /><input type='hidden' value='5' name='gebied'></form></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table width='580' border='0' cellspacing='0' cellpadding='0'>
          <tr>
            <td width='305'height='172'><form method='post' name='Water'><input type='image' onClick='Water.submit();' src='images/attackmap/unova/water.gif' width='305' height='172' alt='Water' /><input type='hidden' value='6' name='gebied'></form></td>
            <td width='275' height='172'><form method='post' name='Strand'><input type='image' onClick='Strand.submit();' src='images/attackmap/unova/strand.gif' width='275' height='172' alt='Beach'/><input type='hidden' value='7' name='gebied'></form></td>
          </tr>
        </table></td>
      </tr>
    </table>
	</center>";
    } elseif ($gebruiker['wereld'] == "Kalos") {
        echo "<center>
    <table width='580' style='border: 1px solid #000000;' cellspacing='0' cellpadding='0'>
      <tr>
        <td><table width='580' border='0' cellspacing='0' cellpadding='0'>
          <tr>
            <td width='346' height='179'><form method='post' name='Lavagrot'><input type='image' onClick='Lavagrot.submit();' src='images/attackmap/kalos/lavagrot.gif' width='346' height='179' alt='Firecave' /><input type='hidden' value='1' name='gebied'></form></td>
            <td width='234' height='179'><form method='post' name='Vechtschool'><input type='image' onClick='Vechtschool.submit();' src='images/attackmap/kalos/vechtschool.gif' width='234' height='179' alt='Fighting gym' /><input type='hidden' value='2' name='gebied'></form></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table width='580' border='0' cellspacing='0' cellpadding='0'>
          <tr>
            <td width='216' height='249'><form method='post' name='Gras'><input type='image' onClick='Gras.submit();' src='images/attackmap/kalos/grasveld.gif' width='216' height='249' alt='Grass field' /><input type='hidden' value='3' name='gebied'></form></td>
            <td width='123' height='249'><form method='post' name='Spookhuis'><input type='image' onClick='Spookhuis.submit();' src='images/attackmap/kalos/spookhuis.gif' width='123' height='249' alt='Ghosthouse' /><input type='hidden' value='4' name='gebied'></form></td>
            <td width='241' height='249'><form method='post' name='Grot'><input type='image' onClick='Grot.submit();' src='images/attackmap/kalos/grot.gif' width='241' height='249' alt='Cave' /><input type='hidden' value='5' name='gebied'></form></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table width='580' border='0' cellspacing='0' cellpadding='0'>
          <tr>
            <td width='305'height='172'><form method='post' name='Water'><input type='image' onClick='Water.submit();' src='images/attackmap/kalos/water.gif' width='305' height='172' alt='Water' /><input type='hidden' value='6' name='gebied'></form></td>
            <td width='275' height='172'><form method='post' name='Strand'><input type='image' onClick='Strand.submit();' src='images/attackmap/kalos/strand.gif' width='275' height='172' alt='Beach'/><input type='hidden' value='7' name='gebied'></form></td>
          </tr>
        </table></td>
      </tr>
    </table>
	</center>";
    }
} else {
    echo '<div style="padding-top:10px;"><div class="blue"><img src="images/icons/blue.png" width="16" height="16" /> ' . $txt['alert_no_pokemon'] . '</div></div>';
}