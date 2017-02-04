<html>
<body>
<?php
include('db.php');
session_start();{
//$location = "Location:" . $_POST['location'];
$user = $_SESSION['login_username'];
$id = $_POST["id"];
$title = $_POST["title"];
$cont = $_POST["cont"];

date_default_timezone_set('UTC');
$date = date('l jS \of F Y h:i:s A');
//$date = getdate();

$title=str_replace("'","''",$title);
$cont=str_replace("'","''",$cont);

$sql="UPDATE entry
SET entry_owner='$user', entry_title='$title', entry_cont='$cont', entry_date='$date'
WHERE entry_id='$id'";


if (!mysqli_query($con,$sql))
  {
  die('Error: ' . mysqli_error($con));
  }
echo "1 entry added";

mysqli_close($con);
header("Location:show.php");
}


?> 

</body>
</html> 