<?php
$Server="localhost";
$User="root";
$Password="";
$Db="campus_connect";
$con=mysqli_connect($Server,$User,$Password,$Db);
if(!$con)
{
		echo"Connection Error";
}
?>