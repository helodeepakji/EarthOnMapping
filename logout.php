<?php
session_start();
session_unset();
session_destroy();

setcookie('userId',$_SESSION['userId'],time() - 3600*720);
unset($_COOKIE['userId']);

header("location: login.php");
die();

?>