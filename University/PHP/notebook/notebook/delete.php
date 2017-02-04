<?php

include('db.php');
$id=mysqli_real_escape_string($con, $_POST['id']);


mysqli_query($con,"DELETE FROM entry WHERE entry_id='$id'");

$location = "Location:" . $_POST['location'];
header($location);
//header("Location:index.php");
//header("Location: ".$_SERVER['PHP_SELF']);
?>