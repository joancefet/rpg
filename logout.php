<?php
#destroy session
session_destroy();

#redirect user to the index
header("Location: index.php");
exit();