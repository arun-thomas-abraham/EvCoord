<?php
	//redirect to index after 3 seconds.
	header('Refresh: 3;url=index.php');
	session_start();
	session_unset();
	session_destroy();
	print("you are now logged out. redirecting in three (3) seconds.");
?>