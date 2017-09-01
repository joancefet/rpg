<div class="catched-shiny">Shiny</div>
<div class="catched">Normal</div>
<?
#Script laden zodat je nooit pagina buiten de index om kan laden
include("includes/security.php");

$query1 = mysql_fetch_assoc(mysql_query("SELECT * FROM pokemon_wild WHERE wild_id = '" . (int)$_GET['pokemon'] . "'"));
$query2 = mysql_query("SELECT * FROM gebruikers INNER JOIN pokemon_speler ON pokemon_speler.user_id = gebruikers.user_id WHERE pokemon_speler.wild_id = '" . (int)$_GET['pokemon'] . "' AND gebruikers.account_code = '1' AND gebruikers.admin = '0' GROUP BY pokemon_speler.user_id");
$query3 = mysql_num_rows(mysql_query("SELECT * FROM gebruikers INNER JOIN pokemon_speler ON pokemon_speler.user_id = gebruikers.user_id WHERE pokemon_speler.wild_id = '" . (int)$_GET['pokemon'] . "' AND gebruikers.account_code = '1' AND gebruikers.admin = '0' GROUP BY pokemon_speler.user_id"));

echo '<center><img src="images/pokemon/' . $query1['wild_id'] . '.gif" /><h3><a href="?page=information&category=pokemon-info&pokemon=' . $query1['wild_id'] . '"><u>' . $query1['naam'] . '</u></a> Al ' . $query3 . ' keer gevangen.</h3><div class="sep"></div>';
echo '<table width="720"><tr>';
while ($query = mysql_fetch_assoc($query2)) {
    $catched = mysql_fetch_assoc(mysql_query("SELECT * FROM gebruikers WHERE user_id = '" . $query['user_id'] . "' AND account_code = '1' AND admin = '0' GROUP BY user_id DESC LIMIT 1"));
    $shiny = '';
    if ($query['shiny'] == 1) $shiny = '-shiny';
    echo '<div class="catched' . $shiny . '"><a href="?page=profile&player=' . $catched['username'] . '">' . $catched['username'] . '</a></div>';
}
echo '</tr></table></center>';
?>
