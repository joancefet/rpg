<?php
session_start();

$page = 'register';
#Goeie taal erbij laden voor de page
include_once('language/language-pages.php');

if (isset($_POST['registreer'])) {
    $voornaam = $_POST['voornaam'];
    $achternaam = $_POST['achternaam'];
    $land = "Nederland"; //$_POST['land'];
    $gebdate = $_POST['year'] . '-' . $_POST['month'] . '-' . $_POST['day'];
    $inlognaam = $_POST['inlognaam'];
    $wachtwoord = $_POST['wachtwoord'];
    $wachtwoord_nogmaals = $_POST['wachtwoord_nogmaals'];
    $wachtwoordmd5 = md5($wachtwoord);
    $email = $_POST['email'];
    $wereld = $_POST['wereld'];
    $gotarefer = $_POST['gotarefer'];
    /*$schelden            	= $_POST['agreecheck2'];*/
    $captcha = $_POST['captcha'];
    $ip = $_SERVER['REMOTE_ADDR'];
    $date = date("Y-m-d H:i:s");
    $character = $_POST['character'];
    $referer = $_POST['referer'];
    $check = mysql_fetch_assoc(mysql_query("SELECT `ip_aangemeld`, `aanmeld_datum` FROM `gebruikers` WHERE `ip_aangemeld`='" . $ip . "' ORDER BY `user_id` DESC"));
    $registerdate = strtotime($check['aanmeld_datum']);
    $current_time = strtotime(date('Y-m-d H:i:s'));
    $countdown_time = 604800 - ($current_time - $registerdate);

    if (isset($_POST['g-recaptcha-response'])) {
        $captcha = $_POST['g-recaptcha-response'];
    }
    if (!$captcha) {
        $foutje12 = '<span class="error_red">*</span>';
        $alert = '<div class="red">' . $txt['alert_guardcore_invalid'] . '</div>';
    }
    #define your secret key
    $secretKey = "";
    $ip = $_SERVER['REMOTE_ADDR'];
    $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . $secretKey . "&response=" . $captcha . "&remoteip=" . $ip);
    $responseKeys = json_decode($response, true);
    if (intval($responseKeys["success"]) !== 1) {
        $foutje12 = '<span class="error_red">*</span>';
        $alert = '<div class="red">' . $txt['alert_guardcore_invalid'] . '</div>';
    } else {

        #inlognaam
        if (empty($inlognaam)) {
            $foutje5 = '<span class="error_red">*</span>';
            $alert = '<div class="red">' . $txt['alert_no_username'] . '</div>';
        } elseif (strlen($inlognaam) < 3) {
            $foutje5 = '<span class="error_red">*</span>';
            $alert = '<div class="red">' . $txt['alert_username_too_short'] . '</div>';
        } #Is de inlognaam wel korter dan 10 tekens
        elseif (strlen($inlognaam) > 10) {
            $foutje5 = '<span class="error_red">*</span>';
            $alert = '<div class="red">' . $txt['alert_username_too_long'] . '</div>';
        } #Bestaat de gebruiker al.
        elseif (mysql_num_rows(mysql_query("SELECT `username` FROM `gebruikers` WHERE `username`='" . $inlognaam . "'")) >= 1) {
            $foutje5 = '<span class="error_red">*</span>';
            $alert = '<div class="red">' . $txt['alert_username_exists'] . '</div>';
        } #Kijken als het geen speciale tekens bevat
        elseif (!preg_match('/^([a-zA-Z0-9]+)$/is', $inlognaam)) {
            $foutje5 = '<span class="error_red">*</span>';
            $alert = '<div class="red">' . $txt['alert_username_incorrect_signs'] . '</div>';
        } #wachtwoord
        elseif (empty($wachtwoord)) {
            $foutje6 = '<span class="error_red">*</span>';
            $alert = '<div class="red">' . $txt['alert_no_password'] . '</div>';
        } #Komen de wachtwoorden niet overeen
        elseif ($wachtwoord <> $wachtwoord_nogmaals) {
            $foutje6 = '<span class="error_red">*</span>';
            $foutje7 = '<span class="error_red">*</span>';
            $alert = '<div class="red">' . $txt['alert_passwords_dont_match'] . '</div>';
        } #email
        elseif (empty($email)) {
            $foutje8 = '<span class="error_red">*</span>';
            $alert = '<div class="red">' . $txt['alert_no_email'] . '</div>';
        } #Is email wel goed?
        elseif (!preg_match("/^[A-Z0-9._%-]+@[A-Z0-9][A-Z0-9.-]{0,61}[A-Z0-9]\.[A-Z]{2,6}$/i", $email)) {
            $foutje8 = '<span class="error_red">*</span>';
            $alert = '<div class="red">' . $txt['alert_email_incorrect_signs'] . '</div>';
        } #Bestaat e-mail al.
        elseif (mysql_num_rows(mysql_query("SELECT `email` FROM `gebruikers` WHERE `email`='" . $email . "'")) >= 1) {
            $foutje8 = '<span class="error_red">*</span>';
            $alert = '<div class="red">' . $txt['alert_email_exists'] . '</div>';
        }
        /*#character
        elseif($character != 'Ash-red' && $character != 'Leaf' && $character != 'Ethan' && $character != 'Lyra' && $character != 'Brendan' && $character != 'May' && $character != 'Lucas' && $character != 'Dawn' && $character != 'Lunick' && $character != 'Solana' && $character != 'Ash' && $character != 'Blue' && $character != 'Brock' && $character != 'Misty' && $character != 'Tracey' && $character != 'Max' && $character != 'Paul' && $character != 'J' && $character != 'Hilda' && $character != 'Hilbert' && $character != 'N'&& $character != 'Akuroma'&& $character != 'fem-bw2'&& $character != 'mal-bw2'){
            $foutje9	    = '<span class="error_red">*</span>';
            $alert  		= '<div class="red">'.$txt['alert_character_invalid'].'</div>';
        }*/
        #Is de wereld wel geselecteerd
        elseif (empty($wereld)) {
            $foutje10 = '<span class="error_red">*</span>';
            $alert = '<div class="red">' . $txt['alert_no_beginworld'] . '</div>';
        } #Is de wereld wel geselecteerd
        elseif ($wereld != 'Kanto' && $wereld != 'Johto' && $wereld != 'Hoenn' && $wereld != 'Sinnoh' && $wereld != 'Unova') {
            $foutje10 = '<span class="error_red">*</span>';
            $alert = '<div class="red">' . $txt['alert_world_invalid'] . '</div>';
        } else {
            #Genereer activatiecode
            //$activatiecode = 1;
            $desired_length = 10; //or whatever length you want
            $unique = uniqid();
            $activatiecode = substr($unique, 0, $desired_length);

            $referaldata = 'Activeer je account met deze link of klik <a href="' . GLOBALDEF_PROTOCOL . '://' . GLOBALDEF_SITEDOMAIN . 'index.php?page=activate&player=' . $inlognaam . '&code=' . $activatiecode . '">hier</a>.<br>
                                <small>' . GLOBALDEF_PROTOCOL . '://' . GLOBALDEF_SITEDOMAIN . '/index.php?page=activate&player=' . $inlognaam . '&code=' . $activatiecode . '</small><br/><br/>';
            if (!isset($gotarefer)) {
                $referer = "";
                $activatiecode = 1;
                $referaldata = "";
            }

            $character = 'images/you/' . $character . '.png';

            #Gebruiker in de database
            mysql_query("INSERT INTO `gebruikers` (`account_code`, `voornaam`, `achternaam`, `land`, `profielfoto`, `username`, `geb_datum`, `datum`, `aanmeld_datum`, `wachtwoord`, `email`, `ip_aangemeld`, `wereld`, `referer`)
          VALUES ('" . $activatiecode . "', '" . $voornaam . "', '" . $achternaam . "', '" . $land . "', '" . $character . "', '" . $inlognaam . "', '" . $gebdate . "', '" . $date . "', '" . $date . "', '" . $wachtwoordmd5 . "', '" . $email . "', '" . $ip . "' , '" . $wereld . "', '" . $referer . "')");

            #id opvragen van de gebruiker tabel van de gebruiker
            $id = mysql_insert_id();

            #Speler opslaan in de gebruikers_item tabel
            mysql_query("INSERT INTO `gebruikers_item` (`user_id`)
          VALUES ('" . $id . "')");

            #Speler opslaan in de gebruikers_item tabel
            mysql_query("INSERT INTO `gebruikers_badges` (`user_id`)
          VALUES ('" . $id . "')");

            #Speler opslaan in de gebruikers_tmhm tabel
            mysql_query("INSERT INTO `gebruikers_tmhm` (`user_id`)
          VALUES ('" . $id . "')");

            #Bestaat de referer wel.
            if (mysql_num_rows(mysql_query("SELECT `username` FROM `gebruikers` WHERE `username`='" . $referer . "'")) >= 1) {
                mysql_query("INSERT INTO `referer_logs` (`gebruiker`,`nieuwe_gebruiker`)
                        VALUES ('" . $referer . "','" . $inlognaam . "')");
            }

            ### Headers
            $headers = "From: " . GLOBALDEF_ADMINEMAIL . "\r\n";
            $headers .= "Return-pathSender: " . GLOBALDEF_ADMINEMAIL . "\r\n";
            $headers .= "X-Sender: \"" . GLOBALDEF_ADMINEMAIL . "\" \n";
            $headers .= "X-Mailer: PHP\n";
            $headers .= "Bcc: " . GLOBALDEF_ADMINEMAIL . "\r\n";
            $headers .= "Content-Type: text/html; charset=iso-8859-1\n";

            $page = 'register';
            #Goeie taal erbij laden voor de mail
            include_once('language/language-mail.php');

            mail($email, $txt['mail_register_title'],
                '<html dir="rtl">
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <style>
    .flip { -moz-transform: scaleX(-1); -o-transform: scaleX(-1); -webkit-transform: scaleX(-1); transform: scaleX(-1); filter: FlipH; -ms-filter: "FlipH"; }
    </style>
    <body>
    <center>
      <table width="80%" border="0" cellspacing="0" cellpadding="0">
        <tr>
      <td background="'.GLOBALDEF_PROTOCOL.'://www.'.GLOBALDEF_SITEDOMAIN.'/images/mail/linksboven.gif" width="11" height="11"></td>
      <td height="11" background="'.GLOBALDEF_PROTOCOL.'://www.'.GLOBALDEF_SITEDOMAIN.'/images/mail/bovenbalk.gif" class="flip"></td>
      <td background="'.GLOBALDEF_PROTOCOL.'://www.'.GLOBALDEF_SITEDOMAIN.'/images/mail/rechtsboven.gif" width="11" height="11"></td>
        </tr>

        <tr>
      <td width="11" rowspan="2" background="'.GLOBALDEF_PROTOCOL.'://www.'.GLOBALDEF_SITEDOMAIN.'/images/mail/linksbalk.gif"></td>
      <td align="center" bgcolor="#D3E9F5"><img src="'.GLOBALDEF_PROTOCOL.'://www.'.GLOBALDEF_SITEDOMAIN.'/images/mail/headermail.png" width="350" ></td>
      <td width="11" rowspan="2" background="'.GLOBALDEF_PROTOCOL.'://www.'.GLOBALDEF_SITEDOMAIN.'/images/mail/rechtsbalk.gif"></td>
        </tr>
        <tr>
          <td align="center" valign="top" bgcolor="#D3E9F5">Welkom, ' . $inlognaam . ',<br/><br/>
                                Welkom bij het leukste online pokemon spel.<br/><br/>
                                Uw gebruikersnaam: <b>' . $inlognaam . '</b><br/>
                                Uw wachtwoord: <b>' . $wachtwoord . '</b><br/>
                                <small>*Let op: Hou je wachtwoord prive.</small><br/><br/>

                                ' . $referaldata . '
                                Veel plezier op '.GLOBALDEF_SITENAME.'!<br/><br/>
                                Met vriendelijke groet,<br/>
                                <b>'.GLOBALDEF_SITENAME.'</b>.</td>
        </tr>
        <tr>
      <td background="'.GLOBALDEF_PROTOCOL.'://www.'.GLOBALDEF_SITEDOMAIN.'/images/mail/linksonder.gif" width="11" height="11"></td>
      <td background="'.GLOBALDEF_PROTOCOL.'://www.'.GLOBALDEF_SITEDOMAIN.'/images/mail/onderbalk.gif" height="11" class="flip"></td>
      <td background="'.GLOBALDEF_PROTOCOL.'://www.'.GLOBALDEF_SITEDOMAIN.'/images/mail/rechtsonder.gif" width="11" height="11"></td>

        </tr>
      </table>
      &copy; '.GLOBALDEF_SITENAME.' - '.date('Y').'
    </center>
    </body>
      </html>',
                $headers
            );

            #Bericht opstellen
            if ($gotarefer) {
                $alert = '<div class="green">' . $txt['success_register'] . '</div>';
            } else {
                $alert = '<div class="green">' . $txt['success_register2'] . '</div>';
                // Gegevens laden om te kijken voor de gebruiker
                $naam = $inlognaam;
                $gegevens_sql = mysql_query("SELECT `user_id`, `username`, `wachtwoord`, `premiumaccount`, `account_code` FROM `gebruikers` WHERE `username`='" . $naam . "'");
                // Gegevens laden om te kijken voor de gebruiker
                $gegeven_sql = mysql_query("SELECT `username`, `wachtwoord`, `account_code` FROM `gebruikers` WHERE `username`='" . $naam . "'");
                $gegeven = mysql_fetch_array($gegevens_sql);

                //zet naam in variabele, zodat het later nog gebruikt kan worden
                $_SESSION['id'] = $gegeven['user_id'];
                $_SESSION['naam'] = $gegeven['username'];
                $_SESSION['userid'] = "";
                //Hash opslaan
                $_SESSION['hash'] = md5($_SERVER['REMOTE_ADDR'] . "," . $gegeven['username']);
                //Ben je wel premium
                if ($gegeven['premiumaccount'] > 0)
                    $_SESSION['userid'] = $gegeven['id'];
                //naar de ingame pagina sturen
                header('location: ?page=home');
                exit;
            }

        }
    }
}

?>
    <script src='https://www.google.com/recaptcha/api.js'></script>
    <script>
        $(document).ready(function () {
            <?
            if(empty($_GET['referer'])){
            ?>
            $("#referer").toggle();
            $("#referer1").toggle();
            $("#referer2").toggle();
            <?
            }
            ?>
            $("#gotarefer").change(function () {
                $("#referer").toggle();
                $("#referer1").toggle();
                $("#referer2").toggle();
            });
        });
    </script>

    <form method="post" action="?page=register" name="register">
        <center><p> <?php echo $txt['title_text']; ?> </p></center>
        <?php if ($alert != '') echo $alert; ?>
        <table width="660" cellspacing="0" cellpadding="0">
            <tr>
                <td colspan="2" class="top_first_td"><? echo $txt['register_game_data']; ?></td>
            </tr>
            <tr>
                <td colspan="2" style="padding-bottom:10px;"></td>
            </tr>
            <tr>
                <td class="normal_first_td" width="150px"><? echo $txt['username'] . ' ' . $foutje5; ?></td>
                <td class="normal_td"><input name="inlognaam" type="text" class="text_long"
                                             value="<?php if (isset($_POST ['inlognaam']) && !empty($_POST ['inlognaam'])) {
                                                 echo $_POST ['inlognaam'];
                                             } ?>" maxlength="10"/></td>
            </tr>
            <tr>
                <td class="normal_first_td"><? echo $txt['password'] . ' ' . $foutje6; ?></td>
                <td class="normal_td"><input type="password" name="wachtwoord"
                                             value="<?php if (isset($_POST ['wachtwoord']) && !empty($_POST ['wachtwoord'])) {
                                                 echo $_POST ['wachtwoord'];
                                             } ?>" class="text_long"/></td>
            </tr>
            <tr>
                <td class="normal_first_td"><? echo $txt['password_again'] . ' ' . $foutje7; ?></td>
                <td class="normal_td"><input type="password" name="wachtwoord_nogmaals"
                                             value="<?php if (isset($_POST ['wachtwoord_nogmaals']) && !empty($_POST ['wachtwoord_nogmaals'])) {
                                                 echo $_POST ['wachtwoord_nogmaals'];
                                             } ?>" class="text_long"/></td>
            </tr>
            <tr>
                <td class="normal_first_td"><?php echo $txt['email'] . ' ' . $foutje8; ?></td>
                <td class="normal_td"><input type="text" name="email"
                                             value="<?php if (isset($_POST ['email']) && !empty($_POST ['email'])) {
                                                 echo $_POST ['email'];
                                             } ?>" class="text_long"/> <span id="referer2">*Activatie is vereist, voer een geldig email adres in.</span>
                </td>
            </tr>
            <tr>
                <td class="normal_first_td"><?php echo $txt['character'] . ' ' . $foutje9; ?></td>
                <td class="normal_td"><select name="character"
                                              value="<?php if (isset($_POST ['character']) && !empty($_POST ['character'])) {
                                                  echo $_POST ['character'];
                                              } ?>" class="text_select">
                        <?
                        $charactersql = mysql_query("SELECT naam FROM characters ORDER BY id ASC");

                        if (isset($_POST['character'])) {
                            $characterr = $_POST['character'];
                        } else {
                            $characterr = 'Red';
                        }

                        while ($character = mysql_fetch_assoc($charactersql)) {
                            if ($character['naam'] == $characterr) {
                                $selected = 'selected';
                            } else {
                                $selected = '';
                            }
                            echo '<option value="' . $character['naam'] . '" ' . $selected . '>' . $character['naam'] . '</option>';
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="normal_first_td"><? echo $txt['beginworld'] . ' ' . $foutje10; ?></td>
                <td class="normal_td"><select name="wereld" class="text_select">
                        <option <?php if (isset($_POST['wereld']) && $_POST['wereld'] == "Kanto") {
                            echo 'checked';
                        } ?>>Kanto
                        </option>
                        <option> <?php if (isset($_POST['wereld']) && $_POST['wereld'] == "Kanto") {
                                echo 'checked';
                            } ?>Johto
                        </option>
                        <option <?php if (isset($_POST['wereld']) && $_POST['wereld'] == "Kanto") {
                            echo 'checked';
                        } ?>>Hoenn
                        </option>
                        <option <?php if (isset($_POST['wereld']) && $_POST['wereld'] == "Kanto") {
                            echo 'checked';
                        } ?>>Sinnoh
                        </option>
                        <option <?php if (isset($_POST['wereld']) && $_POST['wereld'] == "Unova") {
                            echo 'checked';
                        } ?>>Unova
                        </option>
                        <option <?php if (isset($_POST['wereld']) && $_POST['wereld'] == "Kalos") {
                            echo 'checked';
                        } ?>>Kalos
                        </option>

                    </select></td>
            </tr>
            <tr>
                <td class="normal_first_td"><label
                        for="gotarefer"><? echo $txt['1account_rule']; ?></label><?php echo $gotarefer; ?></td>
                <td class="normal_td"><input name="gotarefer" id="gotarefer" class="gotarefer" value="yes"
                                             type="checkbox" <? if (!empty($_GET['referer'])) {
                        echo "checked";
                    } ?>></td>
            </tr>
            <tr>
                <td class="normal_first_td">
                    <div id="referer"><?php echo $txt['referer']; ?></div>
                </td>
                <td class="normal_td">
                    <div id="referer1"><input type="text" name="referer" value="<?php echo $_GET['referer']; ?>"
                                              class="text_long"/> <span
                            style="padding-left:5px;"><?php echo $txt['not_oblige']; ?></span></div>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="padding-bottom:10px;"></td>
            </tr>
            <tr>
                <td colspan="2" class="top_first_td"><? echo $txt['register_security']; ?></td>
            </tr>
            <tr>
                <td colspan="2" style="padding-bottom:10px;"></td>
            </tr>
            <tr>
                <td class="normal_first_td">&nbsp;</td>
                <td class="normal_td">
                    <div class="g-recaptcha" data-sitekey=""></div>
                </td>
            </tr>
            <tr>
                <td class="normal_first_td">&nbsp;</td>
                <td class="normal_td">
                    <button type="submit" name="registreer" class="button"><? echo $txt['button']; ?></button>
                </td>
            </tr>
        </table>
    </form>
<?php session_destroy(); ?>