<!--SHOULD ONLY HAVE THE ABILITY TO LOG IN, AND MAYBE SOME BASIC INFO ABOUT THE SITE. THE "INDEX" PAGE OF A LOGGED-IN USER IS HIS USER PAGE.-->
<?php
session_start();
if ($_SESSION['valid'] == true) {
    include 'user_page.php';
}
else {
    include 'front.php';
}
?>