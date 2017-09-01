<?php
	if(empty($_COOKIE['pa_language'])) $_COOKIE['pa_language'] = 'nl';
	
	$home = mysql_fetch_array(mysql_query("SELECT text_".$_COOKIE['pa_language']." FROM home"));
	echo (nl2br($home['text_'.$_COOKIE['pa_language']]));
?>