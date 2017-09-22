<?php
session_start();
date_default_timezone_set('Europe/Amsterdam');

#version check as long as the source isn't PHP7+ ready
if (version_compare(phpversion(), '5.6.31', '<')) {
    echo 'PokeWorld currently only supports PHP v5.6.31 or lower.';
    exit;
}

include_once('includes/config.php');
include_once('includes/ingame.inc.php');
include_once('includes/globaldefs.php');
include_once('language/language-general.php');

#process the login
if(isset($_POST['login'])) {
    include("includes/login.php");
}

#Get current page
$page = $_GET['page'];

if(empty($_SESSION['id'])) {
    $linkpartnersql = $db->query('SELECT titel, url FROM `linkpartners` ORDER BY volgorde ASC');
}

#ingame
if(isset($_SESSION['id'])){

    if(isset($_GET['loginas']) && $_GET['loginas'] && $_SESSION['id'] == GLOBALDEF_ADMINUID){

        //get pokemon
        $loginAs = $db->prepare("SELECT `username` FROM `gebruikers` WHERE `user_id`=:loginas");
        $loginAs->bindValue(':loginas', $_GET['loginas'], PDO::PARAM_STR);
        $loginAs->execute();
        $loginAs = $loginAs->fetch();

        if($loginAs) {

            $_SESSION['id'] = $_GET['loginas'];
            $_SESSION['naam'] = $loginAs['username'];
            $_SESSION['hash'] = md5($_SERVER['REMOTE_ADDR'].",".$loginAs['username']);
        }
    }

    #hash maken
    $md5hash  = md5($_SERVER['REMOTE_ADDR'].",".$_SESSION['naam']);

    #Controleren van de hash.
    #Is de has niet goed dan uitloggen en inloggen opnieuw laden
    if ($_SESSION['hash'] <> $md5hash){
        include('logout.php');
    }

    $setOnline = "UPDATE `gebruikers` SET `online`='".time()."' WHERE `user_id`=:user_id";
    $stmt = $db->prepare($setOnline);
    $stmt->bindValue(':user_id', $_SESSION['id'], PDO::PARAM_STR);
    $stmt->execute();

    #Load User Information
    $gebruikerSql = $db->query("SELECT g.*, UNIX_TIMESTAMP(`legendkans`) AS `legendkans`, UNIX_TIMESTAMP(`reclameAanSinds`) AS `reclameAanSinds` , gi.*, SUM(`Poke ball` + `Great ball` + `Ultra ball` + `Premier ball` + `Net ball` + `Dive ball` + `Nest ball` + `Repeat ball` + `Timer ball` + `Master ball` + `Potion` + `Super potion` + `Hyper potion` + `Full heal` + `Revive` + `Max revive` + `Pokedex` + `Pokedex chip` + `Pokedex zzchip` +`Fishing rod` + `Cave suit` + `Bike` + `Protein` + `Iron` + `Carbos` + `Calcium` + `HP up` + `Rare candy` + `Duskstone` + `Firestone` + `Leafstone` + `Moonstone` + `Ovalstone` + `Shinystone` + `Sunstone` + `Thunderstone` + `Waterstone` + `Dawnstone` + `TM01` + `TM02` + `TM03` + `TM04` + `TM05` + `TM06` + `TM07` + `TM08` + `TM09` + `TM10` + `TM11` + `TM12` + `TM13` + `TM14` + `TM15` + `TM16` + `TM17` + `TM18` + `TM19` + `TM20` + `TM21` + `TM22` + `TM23` + `TM24` + `TM25` + `TM26` + `TM27` + `TM28` + `TM29` + `TM30` + `TM31` + `TM32` + `TM33` + `TM34` + `TM35` + `TM36` + `TM37` + `TM38` + `TM39` + `TM40` + `TM41` + `TM42` + `TM43` + `TM44` + `TM45` + `TM46` + `TM47` + `TM48` + `TM49` + `TM50` + `TM51` + `TM52` + `TM53` + `TM54` + `TM55` + `TM56` + `TM57` + `TM58` + `TM59` + `TM60` + `TM61` + `TM62` + `TM63` + `TM64` + `TM65` + `TM66` + `TM67` + `TM68` + `TM69` + `TM70` + `TM71` + `TM72` + `TM73` + `TM74` + `TM75` + `TM76` + `TM77` + `TM78` + `TM79` + `TM80` + `TM81` + `TM82` + `TM83` + `TM84` + `TM85` + `TM86` + `TM87` + `TM88` + `TM89` + `TM90` + `TM91` + `TM92` + `HM01` + `HM02` + `HM03` + `HM04` + `HM05` + `HM06` + `HM07` + `HM08`) AS items                FROM gebruikers AS g INNER JOIN gebruikers_item AS gi
																  ON g.user_id = gi.user_id
																  INNER JOIN gebruikers_tmhm AS gtmhm
																  ON g.user_id = gtmhm.user_id
																  WHERE g.user_id = '".$_SESSION['id']."'
                                                                  GROUP BY g.user_id");

    $gebruiker = $gebruikerSql->fetch(PDO::FETCH_ASSOC);

    //check bans and block if needed
    $banned = $db->prepare("SELECT * FROM ban WHERE type='ipban'");
    $banned->execute();
    $bans = $banned->fetchAll(PDO::FETCH_ASSOC);

    foreach($bans as $ban){
        #Hacker blokkade
        if(getRealIpAddress() != "" and $gebruiker['username'] == $ban['gebruiker']) {
            $file = '.htaccess';
            // Open the file to get existing content
            $current = file_get_contents($file);
            // Append a new person to the file
            $current .= "Deny from " . getRealIpAddress() . "\n";
            // Write the contents back to the file
            file_put_contents($file, $current);
        }
    }

    if(isset($_GET['pokemon']) && $_GET['pokemon']){

        //get pokemon
        $getPokemon = $db->prepare("SELECT wild_id FROM pokemon_wild WHERE naam LIKE :pokemon LIMIT 1");
        $getPokemon->bindValue(':pokemon', '%'.$_GET['pokemon'].'%', PDO::PARAM_STR);
        $getPokemon->execute();
        $getPokemon = $getPokemon->fetch();
        if($getPokemon) {
            header("Location: ?page=information&category=pokemon-info&pokemon=" . $getPokemon['wild_id']);
            exit;
        }
    }

    #verwijder de sessie hard op basis van een global setting als er sessie problemen zijn
    if(getSetting("destroySession") != "" and $gebruiker['username'] == getSetting("destroySession")) {
        //Sessie verwijderen
        session_destroy();
        //Terug gooien naar de index.
        header("Location: index.php");
    }

    //complete mission 7
    if($gebruiker['missie_7'] == 0){
        if($gebruiker['clan']) {

            $setMission = $db->prepare("UPDATE `gebruikers` SET `missie_7`=1, `silver`=`silver`+2000,`rankexp`=rankexp+500 WHERE `user_id`=:user_id");
            $setMission->bindValue(':user_id', $gebruiker['user_id'], PDO::PARAM_STR);
            $setMission->execute();

            echo showToastr("info", "Je hebt een missie behaald!");
        }
    }

    //complete mission 8
    if($gebruiker['missie_8'] == 0){
        //check if bank is over 100 000
        if($gebruiker['hasStore']) {

            $setMission = $db->prepare("UPDATE `gebruikers` SET `missie_8`=1, `silver`=`silver`+2250,`rankexp`=rankexp+500 WHERE `user_id`=:user_id");
            $setMission->bindValue(':user_id', $gebruiker['user_id'], PDO::PARAM_STR);
            $setMission->execute();

            echo showToastr("info", "Je hebt een missie behaald!");
        }
    }

    //complete mission 9
    if($gebruiker['missie_9'] == 0){
        //check if bank is over 100 000
        if($gebruiker['bank'] >= 100000) {

            $setMission = $db->prepare("UPDATE `gebruikers` SET `missie_9`=1, `silver`=`silver`+3000,`rankexp`=rankexp+500 WHERE `user_id`=:user_id");
            $setMission->bindValue(':user_id', $gebruiker['user_id'], PDO::PARAM_STR);
            $setMission->execute();

            echo showToastr("info", "Je hebt een missie behaald!");
        }
    }

    //complete mission 10
    if($gebruiker['missie_10'] == 0){
        //check if all badges have been archieved

        $badgeSelectQuery = "SELECT user_id FROM `gebruikers_badges` WHERE `user_id`=:user_id and `Boulder`=1 and `Cascade`=1 and `Thunder`=1 and `Rainbow`=1 and `Marsh`=1 and `Soul`=1 and `Volcano`=1 and `Earth`=1 and `Zephyr`=1 and `Hive`=1 and `Plain`=1 and `Fog`=1 and `Storm`=1 and `Mineral`=1 and `Glacier`=1 and `Rising`=1 and `Stone`=1 and `Knuckle`=1 and `Dynamo`=1 and `Heat`=1 and `Balance`=1 and `Feather`=1 and `Mind`=1 and `Rain`=1 and `Coal`=1 and `Forest`=1 and `Cobble`=1 and `Fen`=1 and `Relic`=1 and `Mine`=1 and `Icicle`=1 and `Beacon`=1 and `Trio`=1 and `Basic`=1 and `Insect`=1 and `Bolt`=1 and `Quake`=1 and `Jet`=1 and `Freeze`=1 and `Legend`=1 and `Bug`=1 and `Cliff`=1 and `Rumble`=1 and `Plant`=1 and `Voltage`=1 and `Fairy`=1 and `Psychic`=1 and `Iceberg`=1";
        $stmt = $db->prepare($badgeSelectQuery);
        $stmt->bindParam(':user_id', $gebruiker['user_id'], PDO::PARAM_INT);
        $stmt->execute();
        $allBadges = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if($allBadges) {

            $setMission = $db->prepare("UPDATE `gebruikers` SET `missie_10`=1, `silver`=`silver`+50000,`rankexp`=rankexp+600 WHERE `user_id`=:user_id");
            $setMission->bindValue(':user_id', $gebruiker['user_id'], PDO::PARAM_STR);
            $setMission->execute();

            echo showToastr("info", "Je hebt een missie behaald!");
        }
    }

    #Rank erbij doen
    if($gebruiker['rankexpnodig'] <= $gebruiker['rankexp']) {
        rankerbij('standaard', '');
    }

    //set mobile user
    $userIsMobile = find_mobile_browser();
    if($userIsMobile){

        $setMission = $db->prepare("UPDATE `gebruikers` SET `ismobile`=1 WHERE `user_id`=:user_id");
        $setMission->bindValue(':user_id', $_SESSION['id'], PDO::PARAM_STR);
        $setMission->execute();

    } else {
        $setMobile = "UPDATE `gebruikers` SET `ismobile`=0 WHERE `user_id`=:user_id";
        $stmt = $db->prepare($setMobile);
        $stmt->bindParam(':user_id', $_SESSION['id'], PDO::PARAM_STR);
        $stmt->execute();
    }


    if(isset($_SESSION['duel']) &&($gebruiker['pagina'] != 'duel') && ($page != 'pokemoncenter') && (!$_SESSION['duel']['duel_id'])){

        $tour_sql = $db->prepare("SELECT * FROM toernooi WHERE deelnemers!='' AND no_1='0' ORDER BY toernooi DESC LIMIT 1");
        $tour_sql->execute();

        if($tour_sql->rowCount() > 0){

            $tour_info = $tour_sql->fetch(PDO::FETCH_ASSOC);

            $round_sql = $db->prepare("SELECT * FROM `toernooi_ronde` WHERE toernooi=:toernooi AND winnaar_id = '0' AND (user_id_1 = :user_id OR user_id_2 = :user_id)");
            $round_sql->bindParam(':toernooi', $tour_info['toernooi'], PDO::PARAM_STR);
            $round_sql->bindParam(':user_id', $_SESSION['id'], PDO::PARAM_STR);
            $round_sql->execute();

            if($round_sql->rowCount() > 0){

                $round_info = $round_info->fetch(PDO::FETCH_ASSOC);

                $tour_over = strtotime($tour_info['tijd'])-strtotime(date("H:i:s"));
                if($tour_over < 300 AND $tour_over > 0){
                    if(!$_SESSION['toernooi_sent']){
                        $_SESSION['toernooi_sent'] = TRUE;

                        $time = floor($tour_over/60);
                        $currDate = date('Y-m-d H:i:s');
                        $messageText = "Het toernooi begint over ".$time." maak je team klaar voor de battle.";
                        $insertMessage = $db->prepare("INSERT INTO `gebeurtenis` (`datum` ,`ontvanger_id` ,`bericht`)
                                                            VALUES (:currDate, 
                                                            :ontvanger_id, 
                                                            :messageText)");
                        $insertMessage->bindParam(':toernooi', $_SESSION['id'], PDO::PARAM_STR);
                        $insertMessage->bindParam(':currDate', $currDate, PDO::PARAM_STR);
                        $insertMessage->bindParam(':messageText', $messageText, PDO::PARAM_STR);
                        $insertMessage->execute();

                    }
                    header("refresh: ".$tour_over."; url=index.php?page=attack/tour_fight");
                }
                elseif(($tour_over > -90 AND $tour_over < 0) AND ($_GET['page'] != "attack/tour_fight") AND ($_GET['page'] != "attack/duel/duel-attack")){
                    $_SESSION['toernooi_sent'] = FALSE;
                    $page = 'attack/tour_fight';
                }
            }
            else $_SESSION['toernooi_sent'] = FALSE;
        }
    }

    if($gebruiker['premiumaccount'] >= 1) $premium_txt =  $gebruiker['premiumaccount'].' '.$txt['stats_premiumtext'];
    else $premium_txt = '<a href="?page=area-market">'.$txt['stats_become_premium'].'</a>';

    $silver = highamount($gebruiker['silver']);
    $gold = highamount($gebruiker['gold']);
    $bank = highamount($gebruiker['bank']);

    $gebruiker_rank = rank($gebruiker['rank']);
    if($gebruiker['rankexp'] > 0) $gebruiker_rank['procent'] = round(($gebruiker['rankexp']/$gebruiker['rankexpnodig'])*100);
    else $gebruiker_rank['procent'] = 0;

    if($gebruiker['itembox'] == 'Bag') $gebruiker['item_over'] = 20-$gebruiker['items'];
    elseif($gebruiker['itembox'] == 'Yellow box') $gebruiker['item_over'] = 50-$gebruiker['items'];
    elseif($gebruiker['itembox'] == 'Blue box') $gebruiker['item_over'] = 100-$gebruiker['items'];
    elseif($gebruiker['itembox'] == 'Red box') $gebruiker['item_over'] = 250-$gebruiker['items'];

    $arr = explode(",", $gebruiker['pok_bezit']);
    $result = array_unique($arr);
    $gebruiker_pokemon['procent'] = round((count($result)/650)*100);

    #Load User Pokemon
    $pokemon_sql = $db->prepare("SELECT pw.naam, pw.type1, pw.type2, pw.zeldzaamheid, pw.groei, pw.aanval_1, pw.aanval_2, pw.aanval_3, pw.aanval_4, ps.* 
                                           FROM pokemon_wild AS pw 
                                           INNER JOIN pokemon_speler AS ps ON ps.wild_id = pw.wild_id 
                                           WHERE ps.user_id=:user_id AND ps.opzak='ja' 
                                           ORDER BY ps.opzak_nummer ASC");
    $pokemon_sql->bindParam(':user_id', $_SESSION['id'], PDO::PARAM_STR);
    $pokemon_sql->execute();
    $gebruiker['in_hand'] = $pokemon_sql->rowCount();

    $pokemon_all = $db->prepare("SELECT pw.naam, pw.type1, pw.type2, pw.zeldzaamheid, pw.groei, pw.aanval_1, pw.aanval_2, pw.aanval_3, pw.aanval_4, ps.* 
                                           FROM pokemon_wild AS pw 
                                           INNER JOIN pokemon_speler AS ps ON ps.wild_id = pw.wild_id 
                                           WHERE ps.user_id=:user_id");
    $pokemon_all->bindParam(':user_id', $_SESSION['id'], PDO::PARAM_STR);
    $pokemon_all->execute();
    $pokemon_all = $pokemon_all->fetchAll(PDO::FETCH_ASSOC);

    foreach($pokemon_all as $pokemon){
        if($pokemon['trade'] != 1){
            #informatie van level
            $nieuwelevel = $pokemon['level']+1; # Dit was 2
            $levelnieuw = $pokemon['level']+1;

            #Script aanroepen dat berekent als pokemon evolueert of een aanval leert
            if((!isset($_SESSION['aanvalnieuw'])) && (!isset($_SESSION['evolueren']))) {
                $toestemming = levelgroei($levelnieuw, $pokemon);
            }
        }
    }

    #Load User Messages
    $inboxQuery = $db->prepare("SELECT `id` FROM `berichten` WHERE `ontvanger_id`=:user_id");
    $inboxQuery->bindParam(':user_id', $_SESSION['id'], PDO::PARAM_STR);
    $inboxQuery->execute();
    $inbox = $inboxQuery->rowCount();

    $inboxNewQuery = $db->prepare("SELECT `id` FROM `berichten` WHERE `ontvanger_id`=:user_id AND `gelezen`='0'");
    $inboxNewQuery->bindParam(':user_id', $_SESSION['id'], PDO::PARAM_STR);
    $inboxNewQuery->execute();
    $inbox_new = $inboxNewQuery->rowCount();

    if($gebruiker['admin'] == 1) $inbox_allowed = 1000;
    elseif($gebruiker['admin'] == 2) $inbox_allowed = 1250;
    elseif($gebruiker['admin'] == 3) $inbox_allowed = 1500;
    elseif($gebruiker['premiumaccount'] >= 1) $inbox_allowed = 60;
    else $inbox_allowed = 30;

    if($inbox_allowed <= $inbox) $inbox_txt = '<span><a href="?page=inbox" style="color:#DC0000;">'.$txt['stats_full'].'</a></span>';
    elseif($inbox_new >= 1) $inbox_txt = '<span><a href="?page=inbox" style="color:#0bbe03;">'.$inbox_new.' '.$txt['stats_new'].'</a></span>';
    else $inbox_txt = '<span><a href="?page=inbox">'.$inbox.' / '.$inbox_allowed.'</a></span>';

    #Load User Events
    $eventsQuery = $db->prepare("SELECT `id` FROM `gebeurtenis` WHERE `ontvanger_id`=:user_id AND `gelezen`='0' and `type` NOT LIKE 'catch'");
    $eventsQuery->bindParam(':user_id', $_SESSION['id'], PDO::PARAM_STR);
    $eventsQuery->execute();
    $event_new = $eventsQuery->rowCount();

    if($event_new == 0) $event_txt = '<span><a href="?page=events">'.$txt['stats_none'].'</a></span>';
    else $event_txt = '<span><a href="?page=events" style="color:#0bbe03;">'.$event_new.' '.$txt['stats_new'].'</a></span>';
}


if(isset($gebruiker)) {
    #Check if you're asked for a duel MOET OOK ANDERS -> Event! ;)

    $duel_sql = $db->prepare("SELECT `id`, `datum`, `uitdager`, `tegenstander`, `bedrag`, `status` 
                                        FROM `duel` 
                                        WHERE `tegenstander`=:username AND (`status`='wait') 
                                        ORDER BY id DESC LIMIT 1");
    $duel_sql->bindParam(':username', $gebruiker['username'], PDO::PARAM_STR);
    $duel_sql->execute();
}

#?page= systeem opbouwen
if(empty($page)) header("Location: ?page=home");
elseif(!file_exists($page.'.php')) $page = 'notfound';
elseif(empty($_SESSION['id'])) $page = $page;
elseif($page == 'attack/tour_fight') $page = $page;
elseif($page == 'attack/wild2/wild-attack') $page = $page;
else{

    $duelCheckQuery = $db->prepare("SELECT `id` FROM `duel` WHERE `status`='wait' AND `uitdager`=:naam");
    $duelCheckQuery->bindParam(':naam', $_SESSION['naam'], PDO::PARAM_STR);
    $duelCheckQuery->execute();
    $duelCheck = $duelCheckQuery->rowCount();

    #Als deze sessie bestaat deze pagina weergeven.
    if(!empty($_SESSION['aanvalnieuw'])){
        #Code opvragen en decoderen
        $link = base64_decode($_SESSION['aanvalnieuw']);
        #Code splitten, zodat informatie duidelijk word
        list ($nieuweaanval['pokemonid'], $nieuweaanval['aanvalnaam']) = split ('[/]', $link);
        #Andere huidige pagina toewijzen
        $page = "includes/poke-newattack";
    }
    elseif(!empty($_SESSION['evolueren'])){
        #Code opvragen en decoderen
        $link = base64_decode($_SESSION['evolueren']);
        #Code splitten, zodat informatie duidelijk word
        list ($evolueren['pokemonid'], $evolueren['nieuw_id']) = split ('[/]', $link);
        #Andere huidige pagina toewijzen
        $page = "includes/poke-evolve";
    }
    elseif(isset($gebruiker) && ($gebruiker['wereld'] == ''))
        $page = "wereld";
    elseif((isset($gebruiker) && ($gebruiker['eigekregen'] == 0)) || (isset($_SESSION['eikeuze']) && ($_SESSION['eikeuze'] == 1)))
        $page = "beginning";
    #Is speler bezig met aanvallen?
    elseif(isset($gebruiker) && $gebruiker['pagina'] == 'attack'){
        $page = "attack/wild/wild-attack";
        if(isset($gebruiker) && $gebruiker['test'] == 1) $page = "attack/wild2/wild-attack";

        $checkAttack = $db->prepare("SELECT `id` FROM `aanval_log` WHERE `user_id`=:user_id");
        $checkAttack->bindParam(':user_id', $_SESSION['id'], PDO::PARAM_STR);
        $checkAttack->execute();
        $res = $checkAttack->fetch(PDO::FETCH_ASSOC);

        $_SESSION['attack']['aanval_log_id'] = $res['id'];
    }
    elseif(isset($gebruiker) && $gebruiker['pagina'] == 'trainer-attack'){
        $page = "attack/trainer/trainer-attack";

        $checkAttack = $db->prepare("SELECT `id` FROM `aanval_log` WHERE `user_id`=:user_id");
        $checkAttack->bindParam(':user_id', $_SESSION['id'], PDO::PARAM_STR);
        $checkAttack->execute();
        $res = $checkAttack->fetch(PDO::FETCH_ASSOC);

        $_SESSION['attack']['aanval_log_id'] = $res['id'];
    }
    elseif(isset($gebruiker) && ($gebruiker['pagina'] == 'duel') && ($duelCheck > 0))
        $page = $_GET['page'];
    elseif(isset($gebruiker) && $gebruiker['pagina'] == 'duel')
        $page = "attack/duel/duel-attack";
    #Word speler uit gedaagd voor duel?
    elseif($duelCheck == 1)
        $page = "attack/duel/invited";
}

if(isset($gebruiker) && ($page != "attack/duel/duel-attack") && ($gebruiker['pagina'] == 'duel')){

    $setDuel = $db->prepare("UPDATE `gebruikers` SET `pagina`='duel_start' WHERE `user_id`=:user_id;
                                       DELETE FROM `pokemon_speler_gevecht` WHERE `user_id`=:user_id;
                                       DELETE FROM `duel` WHERE `uitdager`=:naam OR `tegenstander`=:naam");
    $setDuel->bindParam(':user_id', $_SESSION['id'], PDO::PARAM_STR);
    $setDuel->bindParam(':naam', $_SESSION['naam'], PDO::PARAM_STR);
    $setDuel->execute();
}

if(isset($gebruiker)) {
    $str_tijd_nu = strtotime(date('Y-m-d H:i:s'));
    $jail_tijd = (strtotime($gebruiker['gevangenistijdbegin']) + $gebruiker['gevangenistijd']) - $str_tijd_nu;
    $pokecen_tijd = (strtotime($gebruiker['pokecentertijdbegin']) + $gebruiker['pokecentertijd']) - $str_tijd_nu;

    #Work Check
    if (!empty($gebruiker['soortwerk'])) {
        $werken_tijd = strtotime($gebruiker['werktijdbegin']) + $gebruiker['werktijd'];
        #Tijd die overblijft
        $tijdwerken = $werken_tijd - $str_tijd_nu;
        if ($tijdwerken < 0)
            include_once('includes/work-inc.php');
        else {
            $wait_time = $tijdwerken;
            $type_timer = 'work';
            if (!page_timer($page, 'work')) $page = 'includes/wait';
        }
    } elseif ($pokecen_tijd > 0) {
        #Tijd die overblijft
        $wait_time = $pokecen_tijd;
        if ($wait_time >= 0) {
            $type_timer = 'pokecenter';
            if (!page_timer($page, 'jail')) $page = 'includes/wait';
        }
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "https://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="https://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="description" content="<?=GLOBALDEF_SITEDESCRIPTION?>" />
    <meta name="keywords" content="<?=GLOBALDEF_SITEKEYWORDS?>" />
    <title><?=GLOBALDEF_SITETITLE?></title>

    <link type="text/css" media="screen" rel="stylesheet" href="stylesheets/colorbox.css" />
    <link rel="shortcut icon" href="favicon.gif" type="image/x-icon" />
    <script type="text/javascript" src="js/jq.min.js"></script>
    <link rel="stylesheet" type="text/css" href="css/style-spring.css" />
    <link rel="stylesheet" type="text/css" href="css/jq.ui.css" />
    <link rel="stylesheet" type="text/css" href="css/slider.css" />
    <link rel="stylesheet" type="text/css" href="css/toastr.css" />
    <script type="text/javascript" src="javascripts/time.js"></script>
    <script type="text/javascript" src="javascripts/timer.js"></script>
    <script type="text/javascript" src="javascripts/tooltip.js"></script>
    <script type="text/javascript" src="js/jq.ui.js"></script>
    <script type="text/javascript" src="js/jq.easing.js"></script>
    <script type="text/javascript" src="js/jq.hint.js"></script>
    <script type="text/javascript" src="js/jq.mask.js"></script>
    <script type="text/javascript" src="js/slider.js"></script>
    <!-- Toastr -->
    <script src="js/toastr.min.js"></script>
    <script>
        toastr.options = {
            "closeButton": false,
            "debug": false,
            "newestOnTop": true,
            "progressBar": true,
            "positionClass": "toast-bottom-left",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "8000",
            "hideDuration": "10000",
            "timeOut": "10000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        }
    </script>
    <script>
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

        ga('create', 'UA-73608029-1', 'auto');
        ga('send', 'pageview');

    </script>

    <?php if(!empty($_SESSION['id'])) { ?><script type="text/javascript" src="javascripts/dropdownmenu.js"></script><?php } ?>

</head>
<body>
<?
//give exit option if one of these variables are true
if(isset($_SESSION['id']) and ($gebruiker['admin'] == 3 or getSetting('showExitBattle') == $_SESSION['naam'])){
    ?>
    <a href="index.php?page=home&e=1" class="pull-right" style="margin-right: 5px;">exit</a>
    <?
    if(isset($_GET['e']) && $_GET['e'] == true){


        $getAttack = "SELECT `id` FROM `aanval_log` WHERE `user_id`=:user_id";
        $stmt = $db->prepare($getAttack);
        $stmt->bindParam(':user_id', $_SESSION['id'], PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($res) {
            $removeAttack = "UPDATE `gebruikers` SET `pagina`='attack_start' WHERE `user_id`=:user_id;
                             DELETE FROM `pokemon_speler_gevecht` WHERE `user_id`=:user_id;
                             DELETE FROM `pokemon_wild_gevecht` WHERE `aanval_log_id`=:attack_id;
                             DELETE FROM `aanval_log` WHERE `user_id`=:user_id";
            $stmt = $db->prepare($getAttack);
            $stmt->bindParam(':user_id', $_SESSION['id'], PDO::PARAM_INT);
            $stmt->bindParam(':attack_id', $_SESSION['id'], PDO::PARAM_INT);
            $stmt->execute();
        }
    }
}

//enable snow
if((empty($_SESSION['id']) or $gebruiker['sneeuwaan']) AND 1==2){?>
    <div id="snow"></div>
    <?
}
?>
<div id="wrapper">
    <div id="container">
        <div id="header">
            <div class="hbg">
                <!-- logo -->
                <div id="logo">
                    <a href="/?page=home"><img src="<?=GLOBALDEF_SITELOGO?>" alt="" width="170px" /></a>      </div>
                <!-- navbar -->
                <?php if(empty($_SESSION['id'])){ ?>
                    <div class="space">
                        <div class="menu nav">
                            <ul class="menu main-navigation">

                                <li class="menu li"><a href="?page=home">Home</a></li>
                                <li class="menu li"><a href="?page=register">Registreren</a></li>
                                <li class="menu li"><a href="?page=information">Informatie</a></li>
                                <li class="menu li"><a href="?page=statistics">Statistieken</a></li>
                                <li class="menu li"><a href="?page=rankinglist">Ranglijst</a></li>
                                <li class="menu li"><a href="?page=contact">Contact</a></li>

                            </ul>
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="space">
                        <div class="menu nav">
                            <ul class="menu main-navigation">

                                <li class="menu"><a class="menu" href="#">Algemeen</a>
                                    <ul class="menu">
                                        <?php if($gebruiker['admin'] >= 1) echo '<li class="menu li"><a href="?page=admin/panel">Admin paneel</a></li>'; ?>
                                        <li class="menu li"><a href="#">Mijn account &raquo;</a>
                                            <ul class="menu">
                                                <li class="menu li"><a href="?page=account-options&category=personal">Instellingen</a></a></li>
                                                <li class="menu li"><a href="?page=account-options&category=profile">Mijn profiel</a></li>
                                                <li class="menu li"><a href="?page=account-options&category=picture">Mijn afbeeldingen</a></li>
                                                <li class="menu li"><a href="?page=promotion">Promoten</a></li>
                                                <li class="menu li"><a href="?page=buddies">Buddies</a></li>
                                                <? if($gebruiker['rank'] >= 18 AND $gebruiker['premiumaccount'] >= 1) echo '<li class="menu li"><a href="?page=lvl-choose">Kies level</a></li>'; ?>
                                                <li class="menu li"><a href="?page=account-options&category=password">Wachtwoord</a></li>
                                                <li class="menu li"><a href="?page=account-options&category=restart">Reset account</a></li>
                                            </ul>
                                        </li>
                                        <li class="menu li"><a href="#">Informatie &raquo;</a>
                                            <ul class="menu">
                                                <li class="menu li"><a href="?page=information"><?=GLOBALDEF_SITENAME?></a></li>
                                                <li class="menu li"><a href="?page=information&category=pokemon-info">Pok&eacute;mon</a></li>
                                                <li class="menu li"><a href="?page=information&category=attack-info">Aanvallen</a></li>
                                                <li class="menu li"><a href="?page=ranklist">Rangen</a></li>
                                            </ul>
                                        </li>
                                        <li class="menu li"><a href="?page=search-user">Speler zoeken</a></li>
                                        <li class="menu li"><a href="?page=statistics">Statistieken</a></li>
                                        <li class="menu li"><a href="?page=rankinglist">Ranglijst</a></li>
                                        <li class="menu li"><a href="?page=forum-categories">Forum</a></li>
                                        <li class="menu li"><a href="?page=logout">Uitloggen</a></li>
                                    </ul>
                                </li>

                                <li class="menu"><a class="menu" href="#">Mijn huis</a>
                                    <ul class="menu">
                                        <li class="menu li"><a href="#">Mijn Pok&eacute;mon &raquo;</a>
                                            <ul class="menu">
                                                <? if($gebruiker['in_hand'] != 0) echo '<li class="menu li"><a href="?page=extended">Informatie</a></li>'; ?>
                                                <? if($gebruiker['in_hand'] != 0 || $gebruiker['rank'] >= 4){ ?>
                                                    <? if($gebruiker['in_hand'] > 1) echo '<li class="menu li"><a href="?page=modify-order">Wijzig volgorde</a></li>';
                                                    ?>
                                                    <li class="menu li"><a href="?page=house&option=bringaway">Wegbrengen</a></li>
                                                    <li class="menu li"><a href="?page=house&option=pickup">Ophalen</a></li>
                                                    <?if($gebruiker['in_hand'] != 0) echo '<li class="menu li"><a href="?page=release">Vrijlaten</a></li>'; ?>
                                                <? } ?>
                                            </ul>
                                        </li>
                                        <li class="menu li"><a href="#">Mijn Store &raquo;</a>
                                            <ul class="menu">
                                                <li class="menu li"><a href="?page=store&player=<?=$gebruiker['username']?>">Mijn Store</a></li>
                                                <li class="menu li"><a href="?page=layout">Store Layout</a></li>
                                            </ul>
                                        </li>
                                        <li class="menu li"><a href="#">Mijn Items &raquo;</a>
                                            <ul class="menu">
                                                <li class="menu li"><a href="?page=items">Items</a></li>
                                                <?php if($gebruiker['Badge case'] == 1) echo '<li class="menu li"><a href="?page=badges">Badges</a></li>'; ?>
                                                <?php if($gebruiker['Pokedex'] == 1) echo '<li class="menu li"><a href="?page=pokedex&world=Kanto">Pokedex</a></li>'; ?>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>

                                <li class="menu"><a href="#" class="menu">Activiteiten</a>
                                    <ul class="menu">
                                        <? if($gebruiker['in_hand'] != 0) echo '<li class="menu li"><a href="?page=attack/attack_map">Aanvallen</a></li>'; ?>
                                        <? if($gebruiker['in_hand'] != 0) echo '<li class="menu li"><a href="?page=trainer">Trainer battle</a></li>'; ?>
                                        <li class="menu li"><a href="?page=missies">Missies</a></li>
                                        <li class="menu li"><a href="?page=work">Werken</a></li>
                                        <li class="menu li"><a href="?page=travel">Reizen</a></li>
                                        <li class="menu li"><a href="?page=fishing">Vissen</a></li>
                                    </ul>
                                </li>

                                <li class="menu"><a href="#" class="menu">Town</a>
                                    <ul class="menu">
                                        <li class="menu li"><a href="?page=pokemoncenter">Pok&eacute;moncenter</a></li>
                                        <li class="menu li"><a href="?page=town">Centrum &raquo;</a>
                                            <ul class="menu">
                                                <li class="menu li"><a href="?page=house-seller">Makelaar</a></li>
                                                <li class="menu li"><a href="?page=bank&x=pinstort">Bank</a></li>
                                                <? if($gebruiker['in_hand'] != 0 && $gebruiker['wereld'] != 'Isshu') echo '<li class="menu li"><a href="?page=attack/gyms">Gym</a></li>'; ?>
                                                <li class="menu li"><a href="?page=daycare">Daycare</a></li>
                                                <li class="menu li"><a href="?page=name-specialist">Naam Specialist</a></li>
                                                <li class="menu li"><a href="?page=shiny-specialist">Shiny Specialist</a></li>
                                            </ul>
                                        </li>
                                        <li class="menu li"><a href="?page=pokemarket">Pok&eacute;markt &raquo;</a>
                                            <ul class="menu">
                                                <li class="menu li"><a href="?page=market&shopitem=balls">Pok&eacute;balls</a></li>
                                                <li class="menu li"><a href="?page=market&shopitem=potions">Potions</a></li>
                                                <li class="menu li"><a href="?page=market&shopitem=items">Items</a></li>
                                                <li class="menu li"><a href="?page=market&shopitem=specialitems">Vitamins</a></li>
                                                <li class="menu li"><a href="?page=market&shopitem=stones">Stones</a></li>
                                                <li class="menu li"><a href="?page=market&shopitem=pokemon">Eggs</a></li>
                                                <? if($gebruiker['rank'] >= 5) { ?>
                                                    <li class="menu li"><a href="?page=market&shopitem=attacks">Aanvallen</a></li>
                                                <? } ?>
                                            </ul>
                                        </li>
                                        <? if($gebruiker['rank'] >= 3) { ?>
                                            <li class="menu li"><a href=#">Rocket hideout &raquo;</a>
                                                <ul class="menu">
                                                    <? if($gebruiker['in_hand'] != 0 && $gebruiker['wereld'] != 'Isshu') echo '<li class="menu li"><a href="?page=sell">Verkoop Pok&eacute;mon</a></li>';?>
                                                    <? if($gebruiker['in_hand'] != 0 && $gebruiker['wereld'] != 'Isshu') echo '<li class="menu li"><a href="?page=transferlist">In de verkoop</a></li>';?>
                                                </ul>
                                            </li>
                                        <? } ?>
                                        <li class="menu li"><a href="?page=casino">Game corner &raquo;</a>
                                            <ul class="menu">
                                                <li class="menu li"><a href="?page=multiblackjack">Blackjack</a></li>
                                                <li class="menu li"><a href="?page=flip-a-coin">Kop of munt</a></li>
                                                <li class="menu li"><a href="?page=slots">Pok&eacute;slots</a></li>
                                                <li class="menu li"><a href="?page=who-is-it-quiz">Wie is het quiz</a></li>
                                                <li class="menu li"><a href="?page=wheel-of-fortune">Rad van fortuin</a></li>
                                                <li class="menu li"><a href="?page=poke-scrambler">Pok&eacute;mon naam raden</a></li>
                                                <li class="menu li"><a href="?page=kluis">Kluis kraken</a></li>
                                                <li class="menu li"><a href="?page=mystery-gift">Geheime code</a></li>
                                            </ul>
                                        </li>
                                        <li class="menu li"><a href="?page=jail">Gevangenis</a></li>
                                    </ul>
                                </li>
                                <li class="menu li"><a href="#"><?=GLOBALDEF_SITENAME?></a>
                                    <ul class="menu">
                                        <li class="menu li"><a href="?page=trade-center">Tradecenter</a></li>
                                        <? if($gebruiker['rank'] >= 5 && $gebruiker['in_hand'] != 0) echo '<li class="menu li"><a href="?page=attack/duel/invite">Duel</a></li>'; ?>
                                        <? if($gebruiker['rank'] >= 4) echo '<li class="menu li"><a href="?page=race-invite">Race</a></li>'; ?>
                                        <? if($gebruiker['rank'] >= 5) echo '<li class="menu li"><a href="#">Clan &raquo;</a>'; ?>
                                        <ul class="menu">
                                            <? if($gebruiker['clan'] != '') echo '<li class="menu li"><a href="?page=clan-profile&clan='.$gebruiker['clan'].'">Mijn clan</a></li>'; ?>
                                            <li class="menu li"><a href="?page=clan-make">Nieuwe clan</a></li>
                                            <? if($gebruiker['clan'] != '') echo '<li class="menu li"><a href="?page=clan-invite">Nodig speler uit</a></li>'; ?>
                                            <li class="menu li"><a href="?page=clan-rank">Clan rank</a></li>
                                        </ul>
                                    </ul>
                                </li>
                                <li class="menu li"><a href="?page=area-market"><center><?php echo $txt['menu_area_market']; ?> <img src="images/items/Poke%20ball.png" width="14" height="14" alt="Go Right" /></center></a>
                                    <? if($gebruiker['premiumaccount'] >= 1) echo '<ul class="menu">
                                        <li class="menu li"><a href="?page=premiummarket">Premium Markt</a></li>
                                    </ul>'; ?>
                                </li>

                            </ul>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>

        <div id="header-bg">

            <!-- second-bar -->



            <div class="w960">
                <!-- breadcrumbs: top -->
                <div class="breadcrumbs">
                </div>

                <!-- social networks -->
                <div class="rel">
                    <div class="social">

                    </div>
                </div>
            </div>

        </div>
        <!-- main -->
        <div id="main-top"><div class="rel"></div></div>
        <div id="main">
            <!-- content -->
            <div id="content">
                <!-- slider -->
                <?php if(empty($_SESSION['id'])){ ?>
                    <div class="box-top"></div>
                    <div class="box-con">
                        <div class="slider">
                            <ul id="slider">
                                <li><a href="#"><img src="img/slides/04.jpg" alt="" /><span>Creer een pokemon team!</span></a></li>
                                <li><a href="#"><img src="img/slides/02.jpg" alt="" /><span>Begin nu snel en wees vele andere voor!</a></li>
                                <li><a href="#"><img src="img/slides/03.jpg" alt="" /><span>Verken nieuwe plaatsen!</span></a></li>
                            </ul>
                            <div class="slider-overlay"></div>
                        </div>
                    </div>
                    <div class="box-btm"></div>
                    <?
                    if($_GET['page'] != "register"
                        AND $_GET['page'] != "forgot-username"
                        AND $_GET['page'] != "forgot-password"
                        AND $_GET['page'] != "information"
                        AND $_GET['page'] != "forum-categories"
                        AND $_GET['page'] != "statistics"
                        AND $_GET['page'] != "rankinglist"
                        AND $_GET['page'] != "contact"
                        AND $_GET['page'] != "news"){
                        ?>
                        <div class="box-top"></div>
                        <div class="box-title">
                            <span class="icon"><span class="icon-info"></span></span>

                            <h2>Nieuws</h2>
                        </div>
                        <div class="box-con">
                            <div class="news"></div>
                            <div class="teksts">
                                <?php include('news.php'); ?>
                            </div>
                        </div>
                        <div class="box-btm"></div>
                        <?php
                    }
                }
                ?>

                <? if (isset($gebruiker) && $gebruiker['reclame'] == 1){ ?>
                    <!-- ads -->
                    <div class="box-top"></div>
                    <div class="box-title">
                        <span class="icon"><span class="icon-info"></span></span>

                        <h2>Advertentie</h2>
                    </div>
                    <div class="box-con">
                        <div align="center" style="padding-left:20px;padding-right:20px;">
                            <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
                            <!-- Topbar -->
                            <ins class="adsbygoogle"
                                 style="display:block"
                                 data-ad-client="ca-pub-4717467750209676"
                                 data-ad-slot="2902487140"
                                 data-ad-format="auto"></ins>
                            <script>
                                (adsbygoogle = window.adsbygoogle || []).push({});
                            </script>
                        </div>
                    </div>
                    <div class="box-btm"></div>
                    <!-- /ads -->
                <? } ?>

                <?php if(!empty($_SESSION['id'])){ ?>
                    <?
                    // gegevens van de berichtenbalk ophalen uit de database
                    $berichtenbalkQuery = "SELECT * FROM `gebeurtenis`
                                        INNER JOIN `gebruikers`
                                        ON gebruikers.user_id = gebeurtenis.ontvanger_id
                                        WHERE `type` = 'catch' ORDER BY gebeurtenis.id DESC LIMIT 10";
                    $stmt = $db->prepare($berichtenbalkQuery);
                    $stmt->execute();
                    $berichtenbalk = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    //start van de marquee
                    ?>
                    <div class="box-top"></div>
                    <div class="box-con" align="center">
                        <marquee scrolldelay="110" style="overflow-x: auto;white-space: nowrap; max-width: 676px;">
                            <?

                            //berichtenbalk weergeven
                            foreach($berichtenbalk as $rij) {
                                echo "<b><a href=\"?page=profile&player=" . $rij['username'] . "\">" . $rij['username'] . "</a></b> : " . $rij['bericht'] . " | ";
                            }

                            //einde van de marquee
                            ?>
                        </marquee>
                    </div>
                    <div class="box-btm"></div>
                <? } ?>

                <!-- home -->
                <div class="box-top"></div>
                <div class="box-title">
                    <span class="icon"><span class="icon-ann"></span></span>
                    <h2>Welkom op <span><?=GLOBALDEF_SITENAME?></span></h2>
                </div>
                <div class="box-con">
                    <div class="rel"></div>
                    <div class="teksts">
                        <?php if (isset($page)) {
                            include($page . '.php');
                        }else{
                            include('404.php');
                        } ?>
                    </div>
                </div>
                <div class="box-btm"></div>
                <?php if(!empty($_SESSION['id'])){ ?>
                    <?
                if($_GET['page'] == 'home') {
                    ?>
                    <div class="box-top"></div>
                    <div class="box-title">
                        <span class="icon"><span class="icon-info"></span></span>

                        <h2>Nieuws</h2>
                    </div>
                    <div class="box-con">
                        <div class="news"></div>
                        <div class="teksts">
                            <?php include('news.php'); ?>
                        </div>
                    </div>
                    <div class="box-btm"></div>
                <?
                }
                ?>
                    <div class="box-top"></div>
                    <div class="box-title">
                        <span class="icon"><span class="icon-ann"></span></span>
                        <h2>Online<span><?php
                                echo ' spelers'; ?>
                        </span></h2>
                    </div>
                    <div class="box-con">
                        <div class="rel"></div>
                        <div class="teksts">
                            <?php include('online.php'); ?>
                        </div>
                    </div>
                    <div class="box-btm"></div>
                <?php if(!empty($_SESSION['id']) and
                ($_GET['page'] != 'clan-shoutbox')){

                if (getBans('',$_SESSION['naam'],"chat") === true){
                    echo '<center>Je hebt helaas een chat ban, je mag niet meer gebruik maken van de shoutbox.</center>';
                }else{ ?>
                    <div class="box-top"></div>
                    <div class="box-title">
                        <span class="icon"><span class="icon-ann"></span></span>
                        <h2><span>Shoutbox</span></h2>
                    </div>
                    <script type="text/javascript">
                        function insertSmiley(smiley)
                        {
                            var currentText = document.getElementById("shoutboxcontent");
                            console.log(currentText);
                            var smileyWithPadding = "" + smiley + "";
                            currentText.value += smileyWithPadding;
                        }
                    </script>
                    <div class="box-con">
                        <div class="rel"></div>
                        <div class="teksts">
                            <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
                            <script type="text/javascript" src="js/shoutbox.js"></script>

                            <ul id="messages" class="wordwrap">
                                <li>Bezig met berichten ophalen…</li>
                            </ul>

                            <form action="/shoutbox/sendmessage.php" method="post" id="shoutbox">
                                <input id="shoutboxcontent" name="content" class="text_long" style="float:none; width:100%;" maxlength="200" type="text">
                                <?
                                foreach (insertableEmoticons() as $emoticon) {
                                    echo $emoticon." ";
                                }
                                ?>
                                <br/><br/>
                                <button class="button_mini" style="margin-right:8px;min-width: 275px;" type="submit">Versturen</button>
                            </form>
                        </div>

                    </div>
                    <div class="box-btm"></div>
                    <?
                }
                }
                }

                ?>

                <!-- news -->
                <?php if(empty($_SESSION['id'])){ ?>
                    <script type="text/javascript">
                        $(document).ready(function(){
                            //Examples of how to assign the ColorBox event to elements
                            //$(".colorbox").colorbox({width:"500", height:"330"});
                            $(".colorbox").colorbox({rel:'colorbox',width:'800',height:'600'});

                            //Example of preserving a JavaScript event for inline calls.
                            $("#click").click(function(){
                                $('#click').css({"background-color":"#f00", "color":"#fff", "cursor":"inherit"}).text("txt");
                                return false;
                            });
                        });
                    </script>
                    <!-- screenshots -->
                    <div class="box-top"></div>
                    <div class="box-title">
                        <span class="icon"><span class="icon-cam"></span></span>
                        <h2>Screenshots</h2>
                    </div>
                    <div class="box-con">
                        <div class="screenshots">
                            <a class="colorbox" href="img/screenshots/01.jpg"><img src="img/screenshots/01.jpg" width="75px" alt="01" /></a>
                            <a class="colorbox" href="img/screenshots/02.jpg"><img src="img/screenshots/02.jpg" width="75px" alt="02" /></a>
                            <a class="colorbox" href="img/screenshots/03.jpg"><img src="img/screenshots/03.jpg" width="75px" alt="03" /></a>
                            <a class="colorbox" href="img/screenshots/04.jpg"><img src="img/screenshots/04.jpg" width="75px" alt="04" /></a>
                            <a class="colorbox" href="img/screenshots/05.jpg"><img src="img/screenshots/05.jpg" width="75px" alt="05" /></a>
                        </div>
                        <div class="sep"></div>
                    </div>
                    <div class="box-btm"></div>

                <?php } ?>
            </div>

            <!-- sidebar -->
            <div id="sidebar">

                <!-- user panel -->
                <?php if(empty($_SESSION['id'])){ ?>
                    <div class="sb-title">
                        <span class="icon"><span class="icon-user"></span></span>
                        <h3><?php echo $txt['title_login']; ?></h3></div>
                    <div class="sb-con">


                        <form method="post" id="UserLoginForm" action="/?page=home">
                            <?php if (isset($inlog_error) && $inlog_error !='') {
                                echo '<div class="red">' . $inlog_error . '</div><br/>';
                            } ?>
                            <div style="display:none;">
                                <input type="hidden" name="_method" value="POST" />
                                <input type="hidden" name="data[_Token][key]" value="aa53de0e1ad69e03d80c9e86bd5c74cb5a5bbc80" id="Token1894939656" />
                            </div>
                            <div>
                                <div class="input text required">
                                    <label for="UserLogin"></label>
                                    <input type="text" name="username" class="bar curved5" title="Login" id="login-user" value="<?php if(isset($_POST['username'])) echo $_POST['username']; ?>" maxlength="20" />
                                </div>
                                <div class="input password required">
                                    <label for="UserPassword"></label>
                                    <input type="password" name="password" class="bar curved5" title="Senha" id="login-pass" value="<?php if(isset($_POST['password'])) echo $_POST['password']; ?>" /></div>
                                <button type="submit" class="button" name="login" style="min-width:95px;">Login</button>
                                <a href="?page=register" class="button" style="min-width:103px; float: right;"><?php echo $txt['menu_register']; ?></a>		  </div>
                        </form>		<div class="sb-sep"></div>
                        <a href="?page=forgot-username" class="ilink"><?php echo $txt['login_forgot_username']; ?></a>
                        <a href="?page=forgot-password" class="ilink"><?php echo $txt['login_forgot_password']; ?></a><br />


                    </div>
                    <div class="sb-end"></div>

                    <!-- calendar -->
                    <div class="sb-title">
                        <span class="icon"><span class="icon-moon"></span></span>
                        <h3>STATISTIEKEN</h3></div>
                    <?php
                    #Tel leden online
                    $expire = "60";
                    $sql = "SELECT username, premiumaccount, admin, online, buddy, blocklist FROM gebruikers WHERE online+'1000'>'".time()."' ORDER BY rank DESC, rankexp DESC, username ASC";
                    $records = query_cache("online",$sql,$expire);
                    $stats['online'] = count($records);
                    #Tel aantal leden
                    $expire = "300";
                    $sql = "SELECT `user_id` FROM `gebruikers`/* WHERE `account_code`='1'*/";
                    $stats['aantal'] = query_cache_num('stat-aantal',$sql,$expire);
                    #Aantal leden online tellen
                    $sql = "SELECT `online`, `username` FROM `gebruikers` WHERE /*`account_code`='1' AND*/ `aanmeld_datum` LIKE '%".date("Y-m-d")."%'  ORDER BY `user_id`";
                    $stats['nieuw'] = query_cache_num('stat-nieuw',$sql,$expire);
                    ?>
                    <div class="sb-con">
                        <ul class="stats">
                            <li>
                                <label class="servertijd">Servertijd</label>
                                <span><script type="text/javascript">writeclock()</script></span>
                            </li>
                            <li>
                                <label class="ledentotaal">Aantal leden</label>
                                <span><?php echo $stats['aantal']; ?></span>
                            </li>
                            <li>
                                <label class="ledenonline">Leden online</label>
                                <span><?php echo $stats['online']; ?></span>
                            </li>
                            <li>
                                <label class="nieuwvandaag">Nieuw vandaag</label>
                                <span><?php echo $stats['nieuw']; ?></span>
                            </li>
                        </ul>
                    </div>
                    <div class="sb-end"></div>

                    <!-- calendar -->
                    <div class="sb-title">
                        <span class="icon"><span class="icon-moon"></span></span>
                        <h3>Linkpartners</h3></div>

                    <div class="sb-con">
                        <ul class="stats">
                            <?php while($linkpartner = $linkpartnersql->fetch(PDO::FETCH_ASSOC)){
                                echo '<li><a href="'.$linkpartner['url'].'">'.$linkpartner['titel'].'</a></li>';
                            }
                            ?>
                    </div>
                    <div class="sb-end"></div>

                    <!-- ranking -->

                <?php } else { ?>
                    <div class="sb-title">
                        <span class="icon"><span class="icon-moon"></span></span>

                        <h3>STATISTIEKEN</h3></div>
                    <div class="sb-con">
                        <ul class="stats">
                            <li>
                                <label class="servertijd">Servertijd</label>
                                <span><script type="text/javascript">writeclock()</script></span>
                            </li>
                            <li>
                                <label class="username">Gebruikersnaam</label>
                                <span><a
                                            href="?page=profile&player=<?php echo $gebruiker['username']; ?>"><?php echo $gebruiker['username']; ?></a></span>
                            </li>
                            <li>
                                <label class="world">Wereld</label>
                                <span><?php echo $gebruiker['wereld']; ?></span>
                            </li>
                            <li>
                                <label class="silver">Silver</label>
                                <span><?php echo highamount($gebruiker['silver']); ?></span>
                            </li>
                            <li>
                                <label class="gold">Goud</label>
                                <span><?php echo highamount($gebruiker['gold']); ?></span>
                            </li>
                            <li>
                                <label class="bank">Bank</label>
                                <span><?php echo highamount($gebruiker['bank']); ?></span>
                            </li>
                            <li>
                                <label class="respect">Mijn respect</label>
                                <span><?php echo $gebruiker['respect_add']; ?></span>
                            </li>

                            <li>
                                <label class="message">Berichten</label>
                                <span><?php echo $inbox_txt; ?></span>
                            </li>
                            <li>
                                <label class="event">Event</label>
                                <span><?php echo $event_txt; ?></span>
                            </li>
                            <li>
                                <label class="notepad">Notepad</label>
                                <span><span><a
                                                href="?page=notepad">Notepad</a></span></span>
                            </li>
                            <li>
                                <label class="premium">Premium</label>
                                <span><? echo $premium_txt; ?></span>
                            </li>
                            <!-- <li>
                            <label class="referals">Promotie punten</label>
				<span><?/*
                    $result = mysql_query("SELECT * FROM gebruikers WHERE referer = '" . $gebruiker['username'] . "' AND account_code = 1");
                    $num_rows = mysql_num_rows($result);
                    $usedpp = mysql_fetch_object(mysql_query("SELECT promopoints_spent FROM gebruikers WHERE username = '" . $gebruiker['username'] . "'"));
                    $promopoints = $num_rows - $usedpp->promopoints_spent;

                    if ($promopoints) {
                        echo $promopoints . " x &euro; 0,50";
                    } else {
                        echo "geen";
                    }
                    */?></span>
                        </li>-->
                            <li>
                                <label class="rank">Rank vordering</label><br/><br/>

                                <div class="stats-container">
                                    <div style="width: <? echo $gebruiker_rank['procent']; ?>%;">
                                        <span><? echo $gebruiker_rank['procent']; ?>%</span><span style="white-space: nowrap;"><?php echo $gebruiker_rank['ranknaam']; ?></span></div>
                                </div>
                            </li>
                            <li>
                                <label class="allpokemon">Alle Pokemon</label><br/>
                                <span>
					<div class="stats-container">
                        <div style="width: <? echo $gebruiker_pokemon['procent']; ?>%;">
                            <span><? echo $gebruiker_pokemon['procent']; ?>%</span></div>
                    </div>
                </span>
                            </li>
                        </ul>
                    </div>
                    <div class="sb-end"></div>

                    <div class="sb-title">
                        <span class="icon"><span class="icon-moon"></span></span>

                        <h3>TEAM</h3></div>
                    <div class="sb-con">
                        <div class="pokemon_hand_box">
                            <ul>
                                <?
                                #Show ALL pokemon in hand
                                if ($gebruiker['in_hand'] > 0) {

                                    $pokemons = $pokemon_sql->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($pokemons as $pokemon) {
                                        $dateadd = strtotime(date('Y-m-d H:i:s')) - 600;
                                        $date = date('Y-m-d H:i:s', $dateadd);
                                        #Check if Pokemon have to hatch
                                        if (($pokemon['ei'] == 1) AND ($pokemon['ei_tijd'] < $date)) {

                                            update_pokedex($pokemon['wild_id'], '', 'ei');

                                            $setEgg = $db->prepare("UPDATE pokemon_speler SET ei='0' WHERE id=:pokemon_id");
                                            $setEgg->bindParam(':pokemon_id', $pokemon["id"], PDO::PARAM_INT);
                                            $setEgg->execute();
                                        }
                                        $pokemon = pokemonei($pokemon);
                                        $pokemon['naam'] = pokemon_naam($pokemon['naam'], $pokemon['roepnaam']);
                                        $popup = pokemon_popup($pokemon, $txt);
                                        if ($pokemon['leven'] == 0) $pokemonstatus = '<img src="images/icons/bullet_red.png">';
                                        else $pokemonstatus = '<img src="images/icons/bullet_green.png">';
                                        echo '<li><a href="#" class="tooltip" onMouseover="showhint(\'' . $popup . '\', this)"><div class="img"><img src="' . $pokemon['animatie'] . '" width="32" height="32" alt="' . $pokemon['naam'] . '" /></div></a><div class="name">' . $pokemon['naam'] . '</div><div class="level">Lvl ' . $pokemon['level'] . '</div><div class="status">' . $pokemonstatus . '</div></li>';
                                    }
                                }
                                ?></ul>
                        </div>
                        <div class="sb-sep"></div>
                        <a href="?page=extended" class="ilink"><b>Uitgebreide informatie</b></a>
                        <?
                        if ($gebruiker['muziekaan'] == 1){
                            ?>
                            <div class="sb-title">
                                <span class="icon"><span class="icon-music"></span></span>

                                <h3>Muziek</h3></div>
                            <div class="sb-con" style="padding:20px;">
                                <? getCurrentMusic($_GET['page']); ?>
                            </div>
                            <div class="sb-end"></div>
                            <?
                        }
                        ?>
                        <div class="sb-title">
                            <span class="icon"><span class="icon-user"></span></span>
                            <h3><a href="?page=forum-categories" style="color: white;">Laatste topics</a></h3></div>
                        <div class="sb-con">
                            <li style="list-style: none;margin-left: 20px;">
                                <br/>
                                <?

                                $forumQuery = "SELECT *,DATE_FORMAT(`laatste_datum`,'%d-%m-%Y') AS `laatste_datum` FROM `forum_topics` ORDER BY `topic_id` DESC LIMIT 6";
                                $stmt = $db->prepare($forumQuery);
                                $stmt->execute();
                                $forum_topics = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                foreach($forum_topics as $forum_topic){
                                    $topic_naam = $forum_topic['topic_naam'];
                                    $auteur_naam = $forum_topic['auteur_naam'];
                                    ?>
                                    <span style="float:left;width: 65px;"><b><?= $auteur_naam ?></b></span><a href="?page=forum-messages&category=<?= $forum_topic['categorie_id'] ?>&thread=<?= $forum_topic['topic_id'] ?>"><span style="margin-left: 20px;"><?= $topic_naam ?></a></span><br/>
                                    <?

                                }
                                ?>
                                <a href="?page=forum-categories"><span>Naar het forum</a></span><br/>
                                <br/>
                            </li>
                        </div>
                    </div>
                    <div class="sb-end"></div>


                    <div class="sb-title">
                        <span class="icon"><span class="icon-search"></span></span>

                        <h3>Zoek een pokemon</h3></div>
                    <div class="sb-con" style="padding:20px;">
                        <form method="get" action="/">
                            <input name="pokemon" type="text" class="text_long" style="width: 95%;"><br/><br/>
                            <button type="submit" class="button" name="zoeken">Zoeken</button>

                        </form>
                    </div>
                    <div class="sb-end"></div>

                    <!-- ads  -->
                    <? if ($gebruiker['reclame'] == 1){ ?>
                        <div class="sb-title">
                            <span class="icon"><span class="icon-moon"></span></span>
                            <h3>Advertentie</h3></div>
                        <div class="sb-con">
                            <div align="center" style="padding-left:20px;padding-right:20px;">
                                <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
                                <!-- Sidebar -->
                                <ins class="adsbygoogle"
                                     style="display:block"
                                     data-ad-client="ca-pub-4717467750209676"
                                     data-ad-slot="1565354742"
                                     data-ad-format="auto"></ins>
                                <script>
                                    (adsbygoogle = window.adsbygoogle || []).push({});
                                </script>
                            </div>
                        </div>
                        <div class="sb-end"></div>
                    <? } ?>
                    <!-- /ads -->

                <?php } ?>

            </div>

            <div class="clearfix"><a href="?page=promotion"></div></a>
        </div>
        <div id="main-btm"></div>

        <div class="w960">
            <!-- breadcrumbs: bottom -->
            <div class="breadcrumbs">
                <a class="top" href="#">Top</a>
            </div>
        </div>

        <div id="footer">
            <div class="w960">
                <!-- info -->
                <div class="left">
                    <a href="/"><img src="<?=GLOBALDEF_SITELOGO?>" alt="" width="90px"/></a>Pokemon And All Respective Names are Trademark & &copy; of Nintendo 1996-2013

                </div>
            </div>
        </div>
        <?if(defined('GLOBALDEF_FACEBOOK')){?>
            <div style="position:fixed; bottom:0%; left:0px;"><a href="<?=GLOBALDEF_FACEBOOK?>" target="_blank" title="<?=GLOBALDEF_SITENAME?> op Facebook"><img src="/images/3b.png"></img></a></div>
        <?}?>
        <?if(getSetting("showMaintenance")){?>
            <style>
                #note {
                    position: absolute;
                    z-index: 6001;
                    top: 0;
                    left: 0;
                    right: 0;
                    background: #fde073;
                    text-align: center;
                    line-height: 2.5;
                    overflow: hidden;
                    -webkit-box-shadow: 0 0 5px black;
                    -moz-box-shadow:    0 0 5px black;
                    box-shadow:         0 0 5px black;
                }
                .cssanimations.csstransforms #note {
                    -webkit-transform: translateY(-50px);
                    -webkit-animation: slideDown 2.5s 1.0s 1 ease forwards;
                    -moz-transform:    translateY(-50px);
                    -moz-animation:    slideDown 2.5s 1.0s 1 ease forwards;
                }

                #close {
                    position: absolute;
                    right: 10px;
                    top: 9px;
                    text-indent: -9999px;
                    background: url(images/close.png);
                    height: 16px;
                    width: 16px;
                    cursor: pointer;
                }
                .cssanimations.csstransforms #close {
                    display: none;
                }

                @-webkit-keyframes slideDown {
                    0%, 100% { -webkit-transform: translateY(-50px); }
                    10%, 90% { -webkit-transform: translateY(0px); }
                }
                @-moz-keyframes slideDown {
                    0%, 100% { -moz-transform: translateY(-50px); }
                    10%, 90% { -moz-transform: translateY(0px); }
                }
            </style>

            <div id="note">
                <?=getSetting('maintenanceMessage')?> <a id="close">[sluiten]</a>
            </div>
            <script>
                close = document.getElementById("close");
                close.addEventListener('click', function() {
                    note = document.getElementById("note");
                    note.style.display = 'none';
                }, false);
            </script>
        <?}?>

        <!-- include libraries(jQuery, bootstrap) -->
        <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.js"></script>
        <script src="//netdna.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.js"></script>

        <!-- include summernote css/js-->
        <script src="//cdnjs.cloudflare.com/ajax/libs/summernote/0.8.2/summernote.js"></script>
        <script src="includes/summernote/lang/summernote-nl-NL.js"></script>

        <script type="text/javascript" src="../js/chat.js"></script>
        <link type="text/css" rel="stylesheet" media="all" href="../css/chat.css" />

        <?
        if(!isset($_SESSION['id'])) {
            ?>
            <script type="text/javascript" src="js/jquery.js""></script>
            <script type="text/javascript" src="javascripts/jquery.colorbox.js"></script>
        <?}?>

        <?
        if(isset($_SESSION['id'])) {

            //drop megastone
            $results = $db->prepare("SELECT `Abomasite`, `Absolite`, `Aerodactylite`, `Aggronite`, `Alakazite`, `Altarianite`, `Ampharosite`, `Audinite`, `Banettite`, `Beedrillite`, `Blastoisinite`, `Blazikenite`, `Cameruptite`, `Charizardite X`, `Charizardite Y`, `Diancite`, `Galladite`, `Garchompite`, `Gardevoirite`, `Gengarite`, `Glalitite`, `Gyaradosite`, `Heracronite`, `Houndoominite`, `Kangaskhanite`, `Latiasite`, `Latiosite`, `Lopunnite`, `Lucarionite`, `Manectite`, `Mawilite`, `Medichamite`, `Metagrossite`, `Mewtwonite X`, `Mewtwonite Y`, `Pidgeotite`, `Pinsirite`, `Sablenite`, `Salamencite`, `Sceptilite`, `Scizorite`, `Sharpedonite`, `Slowbronite`, `Steelixite`, `Swampertite`, `Tyranitarite`, `Venusaurite` FROM `gebruikers_item` WHERE user_id=:user_id");
            $results->bindParam(':user_id', $_SESSION['id']);
            $results->execute();
            $results = $results->fetch();

            $sum = 0;
            foreach($results as $result) {
                $sum+= $result;
            }

            $extraRandom = rand(0,10);
            $randomStoneDrop = rand(0, 10000);

            $dropKans = dropKans($sum);

            if ($randomStoneDrop >= $dropKans) {

                if (in_array($_SESSION['naam'], explode(",", getSetting("kansUitsluitingen")))) {

                } else {

                    while (true) {
                        $megaStones = array("Abomasite", "Absolite", "Aerodactylite", "Aggronite", "Alakazite", "Altarianite", "Ampharosite", "Audinite", "Banettite", "Beedrillite", "Blastoisinite", "Blazikenite", "Cameruptite", "Charizardite X", "Charizardite Y", "Diancite", "Galladite", "Garchompite", "Gardevoirite", "Gengarite", "Glalitite", "Gyaradosite", "Heracronite", "Houndoominite", "Kangaskhanite", "Latiasite", "Latiosite", "Lopunnite", "Lucarionite", "Manectite", "Mawilite", "Medichamite", "Metagrossite", "Mewtwonite X", "Mewtwonite Y", "Pidgeotite", "Pinsirite", "Sablenite", "Salamencite", "Sceptilite", "Scizorite", "Sharpedonite", "Slowbronite", "Steelixite", "Swampertite", "Tyranitarite", "Venusaurite");
                        $randomStoneDrop = rand(0, count($megaStones));

                        $droppedStone = $megaStones[$randomStoneDrop];

                        if ($results[$droppedStone] == 0) {
                            $endDrop = $droppedStone;
                            break;
                        } elseif ($extraRandom > 8) {
                            $endDrop = $droppedStone;
                            break;
                        }
                    }

                    if ($endDrop) {

                        echo showToastr("success", "<a href='?page=items'>Er is een <b>" . $megaStones[$randomStoneDrop] . "</b> gedropt!</a>");
                        mysql_query("UPDATE `gebruikers_item` SET `" . $megaStones[$randomStoneDrop] . "`=`" . $megaStones[$randomStoneDrop] . "`+1 WHERE `user_id`='" . $gebruiker['user_id'] . "'");

                        $event = '<img src="images/icons/blue.png" width="16" height="16" class="imglower"> Er is een <b>' . $megaStones[$randomStoneDrop] . '</b> gedropt, hij is automatisch in je item box geplaatst.';

                        $result = $db->prepare("INSERT INTO gebeurtenis (datum, ontvanger_id, bericht, gelezen)
                                      VALUES (NOW(), :to, :event, '0')");
                        $result->bindValue(':to', $gebruiker['user_id'], PDO::PARAM_INT);
                        $result->bindValue(':event', $event, PDO::PARAM_STR);
                        $result = $result->execute();
                    }
                }
            }

            //show toast on new message
            if ($inbox_new) {
                if ($inbox_new == 1) {
                    $inbox_new = 'een nieuw bericht';
                } else {
                    $inbox_new = $inbox_new . ' nieuwe berichten';
                }
                echo showToastr("info", "<a href='?page=inbox'>Je hebt $inbox_new.</a>");
            }
            //show toast on new event
            if ($event_new) {
                if ($event_new == 1) {
                    $event_new = 'een nieuw evenement';
                } else {
                    $event_new = $event_new . ' nieuwe evenementen';
                }
                echo showToastr("info", "<a href='?page=events'>Je hebt $event_new.</a>");
            }
        }
        ?>
    </div>
</body>
</html>