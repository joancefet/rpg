<?php
//Sessie verwijderen
session_destroy();

setcookie("pa_1", "", time()-3600);
setcookie("pa_2", "", time()-3600);
      
//Terug gooien naar de index.
header("Location: index.php");
exit();
?>