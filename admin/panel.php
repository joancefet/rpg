<?php
//Script laden zodat je nooit pagina buiten de index om kan laden
include("includes/security.php");

//Admin controle
if ($gebruiker['admin'] < 1) {
    header('location: index.php?page=home');
}
?>
<center><img src="<?=GLOBALDEF_SITELOGO?>" width="350px"><br/><br/>
    <h2>Admin Panel</h2><br/>
    <hr>
    <table width="500" border="0">
        <?
        if ($gebruiker['admin'] >= 3) {
            ?>
            <tr>
                <td width="50">
                    <center><img src="images/icons/alert_red.png"/></center>
                </td>
                <td width="130"><a href="index.php?page=admin/reset">Reset het spel</a></td>
            </tr>
            <?
        }
        ?>
        <?
        if ($gebruiker['admin'] >= 3) {
            ?>
            <tr>
                <td width="50">
                    <center><img src="images/icons/gear.gif"/></center>
                </td>
                <td width="130"><a href="index.php?page=admin/settings">Instellingen</a></td>
            </tr>
            <?
        }
        if ($gebruiker['admin'] >= 3) {
            ?>
            <tr>
                <td width="50">
                    <center><img src="images/icons/user_admin.png"/></center>
                </td>
                <td width="130"><a href="index.php?page=admin/admins">Administrator</a></td>
            </tr>
            <?
        }
        if ($gebruiker['admin'] >= 2) {
            ?>
            <tr>
                <td>
                    <center><img src="images/icons/user_ban.png"/></center>
                </td>
                <td><a href="index.php?page=admin/ban-ip">Ban IP</a></td>
            </tr>
            <?
        }
        ?>
        <tr>
            <td>
                <center><img src="images/icons/user_view.png"/></center>
            </td>
            <td><a href="index.php?page=admin/search-on-ip">Zoek op IP</a></td>
        </tr>
        <tr>
            <td>
                <center><img src="images/icons/groep_magnify.png"/></center>
            </td>
            <td><a href="index.php?page=admin/more-accounts">Multi accounts chekken</a></td>
        </tr>
        <tr>
            <td>
                <center><img src="images/icons/key_delete.png"/></center>
            </td>
            <td><a href="index.php?page=admin/wrong-login">Verkeerde inlog</a></td>
        </tr>
        <tr>
            <td colspan="2">
                <div style="padding-top:10px;">
                    <HR>
                </div>
            </td>
        </tr>
        <?
        if ($gebruiker['admin'] >= 3) {
            ?>
            <tr>
                <td>
                    <center><img src="images/icons/gebeurtenis.png" alt=""/></center>
                </td>
                <td><a href="index.php?page=admin/change-homepage">Homepage</a></td>
            </tr>
            <tr>
                <td>
                    <center><img src="images/icons/gebeurtenis.png" alt=""/></center>
                </td>
                <td><a href="index.php?page=admin/change-newspage">Nieuws</a></td>
            </tr>
            <tr>
                <td>
                    <center><img src="images/icons/gebeurtenis.png" alt=""/></center>
                </td>
                <td><a href="index.php?page=admin/change-bovenstuk">Bovenkant</a></td>
            </tr>
            <tr>
                <td>
                    <center><img src="images/icons/tekst_add.png"/></center>
                </td>
                <td><a href="index.php?page=admin/mass-message">Massa bericht</a></td>
            </tr>
            <?
        }
        ?>
        <tr>
            <td>
                <center><img src="images/icons/comments.png"/></center>
            </td>
            <td><a href="index.php?page=admin/messages">Berichten</a></td>
        </tr>
        <?
        if ($gebruiker['admin'] >= 3) {
            ?>
            <tr>
                <td>
                    <center><img src="images/icons/email.png"/></center>
                </td>
                <td><a href="index.php?page=admin/mass-mail">Massa e-mail</a></td>
            </tr>
            <?
        }
        ?>
        <tr>
            <td colspan="2">
                <div style="padding-top:10px;">
                    <HR>
                </div>
            </td>
        </tr>
        <?
        if ($gebruiker['admin'] >= 3) {
            ?>
            <tr>
                <td>
                    <center><img src="images/icons/doneer.png"/></center>
                </td>
                <td><a href="index.php?page=admin/pay-list">Betaal lijst</a></td>
            </tr>
            <?
        }
        if ($gebruiker['admin'] >= 2) {
            ?>
            <tr>
                <td>
                    <center><img src="images/icons/pokeball.gif"/></center>
                </td>
                <td><a href="index.php?page=admin/new-starter">Nieuwe starter</a></td>
            </tr>
            <tr>
                <td>
                    <center><img src="images/icons/egg2.gif"/></center>
                </td>
                <td><a href="index.php?page=admin/give-egg">Geef een baby egg</a></td>
            </tr>
            <tr>
                <td>
                    <center><img src="images/icons/pokeball.gif"/></center>
                </td>
                <td><a href="index.php?page=admin/give-pokemon">Geef een pokémon</a></td>
            </tr>
            <tr>
                <td>
                    <center><img src="images/icons/basket_put.png"/></center>
                </td>
                <td><a href="index.php?page=admin/give-pack">Geef een Pakket</a></td>
            </tr>
            <?
        }
        if ($gebruiker['admin'] >= 3) {
            ?>
            <tr>
                <td>
                    <center><img src="images/icons/gold.png"/></center>
                </td>
                <td><a href="index.php?page=admin/massa-gold">Geef iedereen gold</a></td>
            </tr>
            <tr>
                <td>
                    <center><img src="images/icons/boy.gif"/></center>
                </td>
                <td><a href="index.php?page=admin/massa-premium">Iedereen premium acc</a></td>
            </tr>
            <?
        }
        if ($gebruiker['admin'] >= 2) {
            ?>
            <tr>
                <td>
                    <center><img src="images/icons/options.png"/></center>
                </td>
                <td><a href="index.php?page=admin/item-add">Items toevoegen</a></td>
            </tr>
            <td colspan="2">
                <div style="padding-top:10px;">
                    <HR>
                </div>
            </td>
            </tr>
            <tr>
                <td>
                    <center><img src="images/icons/on-transferlist.gif"/></center>
                </td>
                <td><a href="index.php?page=admin/tournament">Tournament</a></td>
            </tr>
            <?
        }
        ?>
    </table>
</center>
