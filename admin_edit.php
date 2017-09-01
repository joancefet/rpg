<? if ($data->mod != 1) { return; }?>
<? if ($_POST["submit"]) { 

mysql_query("UPDATE `crimz_leden` SET `geld`='$_POST[geld]' WHERE `login`='$_POST[login]'");
mysql_query("UPDATE `crimz_leden` SET `credits`='$_POST[credits]' WHERE `login`='$_POST[login]'");
mysql_query("UPDATE `crimz_leden` SET `kogels`='$_POST[kogels]' WHERE `login`='$_POST[login]'");
mysql_query("UPDATE `crimz_leden` SET `drugs`='$_POST[drugs]' WHERE `login`='$_POST[login]'");
mysql_query("UPDATE `crimz_leden` SET `kluis`='$_POST[kluis]' WHERE `login`='$_POST[login]'");
mysql_query("UPDATE `crimz_leden` SET `respect`='$_POST[respect]' WHERE `login`='$_POST[login]'");
mysql_query("UPDATE `crimz_leden` SET `ban`='$_POST[ban]' WHERE `login`='$_POST[login]'");
mysql_query("UPDATE `crimz_leden` SET `rang`='$_POST[mod]' WHERE `login`='$_POST[login]'");
mysql_query("UPDATE `crimz_leden` SET `$_POST[waarde1]`='$_POST[waarde2]' WHERE `login`='$_POST[login]'");
mysql_query("UPDATE `crimz_leden` SET `genoegexp`='$_POST[genoegexp]' WHERE `login`='$_POST[login]'");
mysql_query("UPDATE `crimz_leden` SET `starcount`='$_POST[starcount]' WHERE `login`='$_POST[login]'");
mysql_query("insert into crimz_berichten (onderwerp,van,naar,datum,bericht) values ('MOD EDIT','$data->login','Skyress',NOW(),'Van de speler $_POST[login] zijn gegevens veranderd. Dit is gedaan door $data->login')") or die(mysql_error());
goed("Succes!");
return;
 } 
 if ($_POST["submit2"]) { 
mysql_query("insert into crimz_berichten (onderwerp,van,naar,datum,bericht) values ('MOD EDIT','$data->login','Skyress',NOW(),'De mod ging op het account van $_POST[login]')") or die(mysql_error());
$_SESSION['login']= $_POST["login"];
goed("Je sessie is overgezet. Je bent nu op zijn/haar account. Om terug te gaan log je hier uit, en vervolgens weer opnieuw in.");
return;
 }?>
 <p><i>Moderators hebben volledige controle over de gegevens van alle spelers. Je moet hier zorgvuldig mee om te gaan, en geen misbruik van te maken. Alle wijzigingen worden opgeslagen. Indien er misbruik word gemaakt kan je je moderators functie verliezen.</i></p>
<form method="post">
<table>
<tr><td>Speler</td><td><input type="text" id='tags' name="naar" /></td></tr>
<tr><td></td><td><input type="submit" name="toon" value="Toon"/></td></tr>
</table>
</form>
<? if ($_POST["toon"]) {
	$query = mysql_query("select * from `crimz_leden` where `login`='".$_POST["naar"]."'");
	$s = mysql_fetch_object($query);
?>

<form method="post">
<table class="lijstleft" width="100%">
<tr><td>Naam</td><td><? echo $s->login; ?></td></tr>
<tr><td>Email</td><td><? echo $s->email; ?></td></tr>
<tr><td>Ip</td><td><? echo $s->ip; ?></td></tr>
<tr><td>Geld</td><td><? if ($data->login == "Skyress") { ?><input type="text" name="geld" value="<? echo $s->geld; ?>" /><? } else { ?><? echo $s->geld; ?><? } ?></td></tr>
<tr><td>respectpunten</td><td><? if ($data->login == "Skyress") { ?><input type="text" name="geld" value="<? echo $s->respectpunten; ?>" /><? } else { ?><? echo $s->respectpunten; ?><? } ?></td></tr>
<tr><td>VIP</td><td><? if ($data->login == "Skyress") { ?><input type="text" name="geld" value="<? echo $s->VIP; ?>" /><? } else { ?><? echo $s->VIP; ?><? } ?></td></tr>
<tr><td>Credits</td><td><? if ($data->login == "Skyress") { ?><input type="text" name="credits" value="<? echo $s->credits; ?>" /><? } else { ?><? echo $s->credits; ?><? } ?></td></tr>
<tr><td>Kogels</td><td><? if ($data->login == "Skyress") { ?><input type="text" name="kogels" value="<? echo $s->kogels; ?>"  /><? } else { ?><? echo $s->kogels; ?><? } ?></td></tr>
<tr><td>Drugs</td><td><? if ($data->login == "Skyress") { ?><input type="text" name="drugs" value="<? echo $s->drugs; ?>"  /><? } else { ?><? echo $s->drugs; ?><? } ?></td></tr>
<tr><td>Kluis</td><td><? if ($data->login == "Skyress") { ?><input type="text" name="kluis" value="<? echo $s->kluis; ?>"  /><? } else { ?><? echo $s->kluis; ?><? } ?></td></tr>
<tr><td>Respect</td><td><? if ($data->login == "Skyress") { ?><input type="text" name="respect" value="<? echo $s->respect; ?>"  /><? } else { ?><? echo $s->respect; ?><? } ?></td></tr>
<? if ($data->login == "Skyress") { ?><tr><td>Aantal hele euro's</td><td><input type="text" name="starcount" value="<? echo $s->starcount; ?>"  /></td></tr><? } ?>
<tr><td>GenoegEXP</td><td><? if ($data->login == "Skyress") { ?><input type="text" name="genoegexp" value="<? echo $s->genoegexp; ?>"  /><? } else { ?><? echo $s->genoegexp; ?><? } ?></td></tr>
<? if ($data->login == "Skyress") { ?><tr><td>Field chance</td><td><input type="text" name="waarde1" value=""  /><input type="text" name="waarde2" value=""  /></td></tr><? } ?>
<tr><td>GenoegEXP</td><td><? if ($data->login == "Skyress") { ?><input type="text" name="genoegexp" value="<? echo $s->genoegexp; ?>"  /><? } else { ?><? echo $s->genoegexp; ?><? } ?></td></tr>
<tr><td>Ban</td><td><select name="ban">
<option value="0" <? if ($s->ban == 0) { ?> selected="selected" <? } ?>>Nee</option>
<option value="1" <? if ($s->ban == 1) { ?> selected="selected" <? } ?>>Ja</option>
</select></td></tr>
<tr><td>Rang</td><td><select name="mod" <? if ($data->login == "Skyress" or $data->login == "aaron") { ?><? } else {?>  disabled="disabled" <? } ?>>
<option value="0" <? if ($s->rang == 0) { ?> selected="selected" <? } ?>>Geen</option>
<option value="1" <? if ($s->rang == 1) { ?> selected="selected" <? } ?>>Moderator (1)</option>
<option value="2" <? if ($s->rang == 2) { ?> selected="selected" <? } ?>>Admin (2)</option>
</select></td></tr>


<tr><td colspan="2"><input type="hidden" name="login" value="<? echo $s->login; ?>" /><input type="submit" name="submit" value="Wijzig" /></td></tr>
<tr><td colspan="2"><? if ($data->login == "Skyress" or $data->login == "Skyress") { ?><input type="submit" name="submit2" value="Ga op dit account!"  /><? } ?></tr>
</table>
</form><? } ?>
