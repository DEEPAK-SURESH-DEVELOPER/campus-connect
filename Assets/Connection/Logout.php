<?php
session_start();
session_destroy();
header("Location: ../../Guest/Login.php");
exit;
?>
