<html>
<body>
<?php
$item = $_POST["item"];
$con=mysqli_connect("localhost", "root", "");
// Check connection

if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }
  
mysqli_select_db($con,"database_for_252");
$sql="INSERT INTO comments (item, name, comment)

VALUES
('$_POST[item]','$_POST[name]','$_POST[comment]')";

if (!mysqli_query($con,$sql))
  {
  die('Error: ' . mysqli_error($con));
  }
echo "1 record added";

mysqli_close($con);
echo" <a href=comments.php?id=$item>Back to Comments</a>";
?> 
</body>
</html> 