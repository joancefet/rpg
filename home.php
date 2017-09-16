<?php
	if(empty($_COOKIE['pa_language'])) $_COOKIE['pa_language'] = 'nl';

    $newsQuery = "SELECT text_".$_COOKIE['pa_language']." FROM home";
    $stmt = $db->prepare($newsQuery);
    $stmt->execute();
    $home = $stmt->fetch(PDO::FETCH_ASSOC);
	echo (nl2br($home['text_'.$_COOKIE['pa_language']]));