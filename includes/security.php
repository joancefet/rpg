<?php
	if(empty($_SESSION['naam'])){
		header('Location: ?page=error');
	}
?>