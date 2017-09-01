<?php
#Script laden zodat je nooit pagina buiten de index om kan laden
include("includes/security.php");

$page = 'choose-pokemon';
#Goeie taal erbij laden voor de page
include('language/language-pages.php');

#Heeft de speler nog geen ei, dan pagina zien
if ($gebruiker['eigekregen'] == 0){
#Wil de speler een starter ei
if (isset($_POST['submit'])) {
    $whocheck = array('1', '4', '7', '152', '155', '158', '252', '255', '258', '387', '390', '393', '495', '498', '501', '16', '43', '74', '179', '194', '216', '270', '304', '363', '396', '403', '449', '535', '551', '610', '172', '173', '174', '175', '236', '238', '239', '240', '298', '360', '406', '433', '438', '439', '440', '446', '447', '458', '659', '662', '665');

    if (!isset($_POST['who'])) echo '<div class="red"><img src="images/icons/red.png"> ' . $txt['alert_no_pokemon'] . '</div>';
    elseif (!in_array($_POST['who'], $whocheck)) echo '<div class="red"><img src="images/icons/red.png"> ' . $txt['alert_pokemon_unknown'] . '</div>';
    else {
        #Willekeurige pokemon laden, en daarvan de gegevens
        $query = mysql_fetch_assoc(mysql_query("SELECT pw.wild_id, pw.naam, pw.groei, pw.attack_base, pw.defence_base, pw.speed_base, `pw`.`spc.attack_base`, `pw`.`spc.defence_base`, pw.hp_base, pw.aanval_1, pw.aanval_2, pw.aanval_3, pw.aanval_4 FROM pokemon_wild AS pw WHERE pw.wild_id = '" . $_POST['who'] . "' LIMIT 1"));
        #De willekeurige pokemon in de pokemon_speler tabel zetten

        mysql_query("INSERT INTO `pokemon_speler` (`wild_id`, `aanval_1`, `aanval_2`, `aanval_3`, `aanval_4`) SELECT `wild_id`, `aanval_1`, `aanval_2`, `aanval_3`, `aanval_4` FROM `pokemon_wild` WHERE `wild_id`='" . $query['wild_id'] . "'");
        #id opvragen van de insert hierboven
        $pokeid = mysql_insert_id();

        #Heeft speler wel pokemon gekregen??
        if (is_numeric($pokeid)) mysql_query("UPDATE `gebruikers` SET `aantalpokemon`=`aantalpokemon`+'1', `eigekregen`='1' WHERE `user_id`='" . $_SESSION['id'] . "'");

        #Karakter kiezen 
        $karakter = mysql_fetch_assoc(mysql_query("SELECT * FROM `karakters` ORDER BY rand() limit 1"));

        #Expnodig opzoeken en opslaan
        $experience = mysql_fetch_assoc(mysql_query("SELECT `punten` FROM `experience` WHERE `soort`='" . $query['groei'] . "' AND `level`='6'"));

        #Pokemon IV maken en opslaan
        #Iv willekeurig getal tussen 1,31. Ik neem 2 omdat 1 te weinig is:P
        $attack_iv = rand(2, 31);
        $defence_iv = rand(2, 31);
        $speed_iv = rand(2, 31);
        $spcattack_iv = rand(2, 31);
        $spcdefence_iv = rand(2, 31);
        $hp_iv = rand(2, 31);

        #Stats berekenen
        $attackstat = round((((($query['attack_base'] * 2 + $attack_iv) * 5 / 100) + 5) * 1) * $karakter['attack_add']);
        $defencestat = round((((($query['defence_base'] * 2 + $defence_iv) * 5 / 100) + 5) * 1) * $karakter['defence_add']);
        $speedstat = round((((($query['speed_base'] * 2 + $speed_iv) * 5 / 100) + 5) * 1) * $karakter['speed_add']);
        $spcattackstat = round((((($query['spc.attack_base'] * 2 + $spcattack_iv) * 5 / 100) + 5) * 1) * $karakter['spc.attack_add']);
        $spcdefencestat = round((((($query['spc.defence_base'] * 2 + $spcdefence_iv) * 5 / 100) + 5) * 1) * $karakter['spc.defence_add']);
        $hpstat = round(((($query['hp_base'] * 2 + $hp_iv) * 5 / 100) + 5) + 10);

        #Alle gegevens van de pokemon opslaan
        mysql_query("UPDATE `pokemon_speler` SET `level`='5', `karakter`='" . $karakter['karakter_naam'] . "', `expnodig`='" . $experience['punten'] . "', `user_id`='" . $_SESSION['id'] . "', `opzak`='ja', `opzak_nummer`='1', `gehecht` = '1', `ei`='0', `ei_tijd`= NOW(), `attack_iv`='" . $attack_iv . "',`defence_iv`='" . $defence_iv . "', `speed_iv`='" . $speed_iv . "', `spc.attack_iv`='" . $spcattack_iv . "', `spc.defence_iv`='" . $spcdefence_iv . "', `hp_iv`='" . $hp_iv . "', `attack`='" . $attackstat . "', `defence`='" . $defencestat . "', `speed`='" . $speedstat . "', `spc.attack`='" . $spcattackstat . "', `spc.defence`='" . $spcdefencestat . "', `levenmax`='" . $hpstat . "', `leven`='" . $hpstat . "' WHERE `id`='" . $pokeid . "'");

        #Tekst laten zien
        $error = '<div class="green"><img src="images/icons/green.png" width=16 height=16 /> ' . $txt['success'] . '</div>';
        #Sessie leeg maken
        unset($_SESSION['eikeuze']);
        echo "<meta http-equiv='refresh' content='3;url=?page=home'>";
    }
}


if (isset($error)) echo $error; ?><br/>
<form method="post">
    <table width="600" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td width="120" valign="top"><img src="images/oak.png"/></td>
            <td width="480" valign="top">
                <div style="padding-bottom:20px;"><?php echo $txt['title_text']; ?></div>
                <?php
                #Starter lijst
                $startersql = mysql_query("SELECT pokemon_nieuw_starter.wild_id, pokemon_wild.naam, pokemon_wild.type1, pokemon_wild.type2
											 	FROM pokemon_nieuw_starter
										   		INNER JOIN pokemon_wild ON pokemon_nieuw_starter.wild_id = pokemon_wild.wild_id
												WHERE pokemon_wild.wereld = '" . $gebruiker['wereld'] . "'
										   		ORDER BY pokemon_nieuw_starter.wild_id ASC");
                $checkStarter = mysql_num_rows($startersql);
                if ($checkStarter) {
                    echo '<h2>' . $txt['starter_pokemon'] . '</h2><div style="padding-bottom:15px;"><table width="480" border="0" cellspacing="0" cellpadding="0">
			  			<tr>
							<td class="top_first_td" width="50">' . $txt['#'] . '</td>
							<td class="top_first_td" width="50">&nbsp;</td>
							<td class="top_td" width="150">' . $txt['starter_name'] . '</td>
							<td class="top_td" width="230">' . $txt['type'] . '</td>
						</tr>';
                    while ($starter = mysql_fetch_assoc($startersql)) {
                        $starter['type1'] = strtolower($starter['type1']);
                        $starter['type2'] = strtolower($starter['type2']);
                        if (empty($starter['type2'])) $starter['type'] = '<table><tr><td><div class="type ' . $starter['type1'] . '">' . $starter['type1'] . '</div></td></tr></table>';
                        else $starter['type'] = '<table><tr><td><div class="type ' . $starter['type1'] . '">' . $starter['type1'] . '</div></td><td> <div class="type ' . $starter['type2'] . '">' . $starter['type2'] . '</div></td></tr></table>';

                        echo '<tr>
										<td class="normal_first_td"><input type="radio" name="who" value="' . $starter['wild_id'] . '" id="' . $starter['wild_id'] . '"></td>
										<td class="normal_td"><label for="' . $starter['wild_id'] . '"><img src="images/pokemon/icon/' . $starter['wild_id'] . '.gif" width="32" height="32" alt="' . $starter['naam'] . '"></label></td>
										<td class="normal_td"><label for="' . $starter['wild_id'] . '">' . $starter['naam'] . '</label></td>
										<td class="normal_td"><label for="' . $starter['wild_id'] . '">' . $starter['type'] . '</label></td>
									</tr>';
                    }
                    echo '</table></div>';
                }
                #Normal lijst
                $normalsql = mysql_query("SELECT pokemon_nieuw_gewoon.wild_id, pokemon_wild.naam, pokemon_wild.type1, pokemon_wild.type2
											 	FROM pokemon_nieuw_gewoon
										   		INNER JOIN pokemon_wild ON pokemon_nieuw_gewoon.wild_id = pokemon_wild.wild_id
												WHERE pokemon_wild.wereld = '" . $gebruiker['wereld'] . "'
										   		ORDER BY pokemon_nieuw_gewoon.wild_id ASC");
                $checkNormal = mysql_num_rows($normalsql);
                if($checkNormal) {
                    echo '<h2>' . $txt['normal_pokemon'] . '</h2><div style="padding-bottom:15px;"><table width="480" border="0" cellspacing="0" cellpadding="0">
			  			<tr>
							<td class="top_first_td" width="50">' . $txt['#'] . '</td>
							<td class="top_first_td" width="50">&nbsp;</td>
							<td class="top_td" width="150">' . $txt['normal_name'] . '</td>
							<td class="top_td" width="230">' . $txt['type'] . '</td>
						</tr>';
                    while ($normal = mysql_fetch_assoc($normalsql)) {
                        $normal['type1'] = strtolower($normal['type1']);
                        $normal['type2'] = strtolower($normal['type2']);
                        if (empty($normal['type2'])) $normal['type'] = '<table><tr><td><div class="type ' . $normal['type1'] . '">' . $normal['type1'] . '</div></td></tr></table>';
                        else $normal['type'] = '<table><tr><td><div class="type ' . $normal['type1'] . '">' . $normal['type1'] . '</div></td><td> <div class="type ' . $normal['type2'] . '">' . $normal['type2'] . '</div></td></tr></table>';

                        echo '<tr>
										<td class="normal_first_td"><input type="radio" name="who" value="' . $normal['wild_id'] . '" id="' . $normal['wild_id'] . '"></td>
										<td class="normal_td"><label for="' . $normal['wild_id'] . '"><img src="images/pokemon/icon/' . $normal['wild_id'] . '.gif" width="32" height="32" alt="' . $normal['naam'] . '"></label></td>
										<td class="normal_td"><label for="' . $normal['wild_id'] . '">' . $normal['naam'] . '</label></td>
										<td class="normal_td"><label for="' . $normal['wild_id'] . '">' . $normal['type'] . '</label></td>
									</tr>';
                    }
                    echo '</table></div>';
                }
                #baby lijst
                $babysql = mysql_query("SELECT pokemon_nieuw_baby.wild_id, pokemon_wild.naam, pokemon_wild.type1, pokemon_wild.type2
											 	FROM pokemon_nieuw_baby
										   		INNER JOIN pokemon_wild ON pokemon_nieuw_baby.wild_id = pokemon_wild.wild_id
												WHERE pokemon_wild.wereld = '" . $gebruiker['wereld'] . "'
										   		ORDER BY pokemon_nieuw_baby.wild_id ASC");
                $checkBaby = mysql_num_rows($babysql);
                if($checkBaby) {
                    echo '<h2>' . $txt['baby_pokemon'] . '</h2><div style="padding-bottom:15px;"><table width="480" border="0" cellspacing="0" cellpadding="0">
			  			<tr>
							<td class="top_first_td" width="50">' . $txt['#'] . '</td>
							<td class="top_first_td" width="50">&nbsp;</td>
							<td class="top_td" width="150">' . $txt['baby_name'] . '</td>
							<td class="top_td" width="230">' . $txt['type'] . '</td>
						</tr>';
                    if (mysql_num_rows($babysql) == 0) {
                        echo '<tr>
									<td class="normal_first_td" colspan="4">' . $txt['no_pokemon_this_world'] . '</td>
								  </tr>';
                    } else {
                        while ($baby = mysql_fetch_assoc($babysql)) {
                            $baby['type1'] = strtolower($baby['type1']);
                            $baby['type2'] = strtolower($baby['type2']);
                            if (empty($baby['type2'])) $baby['type'] = '<table><tr><td><div class="type ' . $baby['type1'] . '">' . $baby['type1'] . '</div></td></tr></table>';
                            else $baby['type'] = '<table><tr><td><div class="type ' . $baby['type1'] . '">' . $baby['type1'] . '</div></td><td> <div class="type ' . $baby['type2'] . '">' . $baby['type2'] . '</div></td></tr></table>';

                            echo '<tr>
										<td class="normal_first_td"><input type="radio" name="who" value="' . $baby['wild_id'] . '" id="' . $baby['wild_id'] . '"></td>
										<td class="normal_td"><label for="' . $baby['wild_id'] . '"><img src="images/pokemon/icon/' . $baby['wild_id'] . '.gif" width="32" height="32" alt="' . $baby['naam'] . '"></label></td>
										<td class="normal_td"><label for="' . $baby['wild_id'] . '">' . $baby['naam'] . '</label></td>
										<td class="normal_td"><label for="' . $baby['wild_id'] . '">' . $baby['type'] . '</label></td>
									</tr>';
                        }
                    }
                    echo '</table>
					</td>
                  </tr>';
                }
				  echo'<tr>
				  	<td>&nbsp;</td>
					<td class="normal_first_td"><button type="submit" name="submit" class="button">' . $txt['button'] . '</button></td>
				  </tr>
				  
                 </table></form>';
                #Anders terug naar home
                }
                else {
                    header("Location: index.php?page=home");
                }
                ?>
        