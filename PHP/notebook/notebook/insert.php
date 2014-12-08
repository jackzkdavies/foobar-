<html>
<body>
<?php
include('db.php');
session_start();{
$location = "Location:" . $_POST['location'];
$user = $_SESSION['login_username'];
$title = $_POST["title"];
$cont = $_POST["cont"];

date_default_timezone_set('UTC');
$date = date('l jS \of F Y h:i:s A');
//$date = getdate();

$title=str_replace("'","''",$title);
$cont=str_replace("'","''",$cont);


$sql="INSERT INTO entry (entry_owner, entry_title, entry_cont, entry_date) 
VALUES
('$user ','$title','$cont', '$date')";

if (!mysqli_query($con,$sql))
  {
  die('Error: ' . mysqli_error($con));
  }
echo "1 entry added";

mysqli_close($con);
header($location);
}


?> 

</body>
</html> 