<html>
<body>
<?php
$q = intval($_GET['id']);

$con = mysqli_connect("localhost", "root", "");
if (!$con)
  {
  die('Could not connect: ' . mysqli_error($con));
  }

mysqli_select_db($con,"database_for_252");
$sql="SELECT * FROM phones WHERE id = '".$q."'";

$result = mysqli_query($con,$sql);


echo "<table border='1'>
<tr>
<th>ID</th>
<th>Product Name</th>
<th>Price</th>
<th>Desscription</th>
</tr>";
echo "<br>";
while($row = mysqli_fetch_array($result))
{
  echo "<tr>";
  echo "<td>" . $row['id'] . "</td>";
  echo "<td>" . $row['phoneName'] . "</td>";
  echo "<td>" . $row['price'] . "</td>";
  echo "<td>" . $row['description'] . "</td>";
  echo "</tr>";
  }

echo "</table>";
echo "<br>";
####
$con = mysqli_connect("localhost", "root", "");
if (!$con)
  {
  die('Could not connect: ' . mysqli_error($con));
  }

mysqli_select_db($con,"database_for_252");
$sql="SELECT * FROM comments WHERE item = '".$q."'";

$result = mysqli_query($con,$sql);


echo "<table border='1'>
<tr>
<th>ID</th>
<th>Name</th>
<th>Comment</th>
</tr>";

while($row = mysqli_fetch_array($result))
{
  echo "<tr>";
  echo "<td>" . $row['id'] . "</td>";
  echo "<td>" . $row['name'] . "</td>";
  echo "<td>" . $row['comment'] . "</td>";
  echo "</tr>";
  }
  
echo "</table>";

echo" <form action='insert.php' method='post'>";
echo" <input type='hidden' name='item' value=$q>";
echo" name: <input type='text' name='name'>";
echo" comment: <input type='text' name='comment'>";
echo" <input type='submit'>";
echo" </form>";



mysqli_close($con);
echo" <a href=index.php?id=$q>Home</a>";
?> 


</body>
</html> 