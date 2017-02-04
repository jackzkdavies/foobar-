<?php
$q = intval($_GET['q']);
$q2 = $_GET['q'];

$con = mysqli_connect("localhost", "root", "");
if (!$con)
  {
  die('Could not connect: ' . mysqli_error($con));
  }

mysqli_select_db($con,"database_for_252");
$sql="SELECT * FROM phones WHERE id = '".$q."'";
$sql2="SELECT * FROM phones WHERE phoneName = '".$q2."'";

$result = mysqli_query($con,$sql);
$result2 = mysqli_query($con,$sql2);


//FULL LIST STUFF
mysql_connect("localhost", "root", "") or die (mysql_error());
mysql_select_db("database_for_252") or die(mysql_error());
$strSQL = "SELECT * FROM phones";
$rs = mysql_query($strSQL);


echo "<table border='1'>
<tr>
<th>ID</th>
<th>Product Name</th>
<th>Price</th>
<th>Desscription</th>
<th>Comments</th>
</tr>";

while($row = mysqli_fetch_array($result))
{
  echo "<tr>";
  echo "<td>" . $row['id'] . "</td>";
  echo "<td>" . $row['phoneName'] . "</td>";
  echo "<td>" . $row['price'] . "</td>";
  echo "<td>" . $row['description'] . "</td>";
  echo "<td><a href=comments.php?id=$q>Click To View</a></td>";
  echo "</tr>";
  }

  
 if (($q)<"1"){
	 while($row = mysql_fetch_array($rs)) {
	 $test= $row['id'];

	   // Write the value of the column FirstName (which is now in the array $row)
	  echo "<tr>";
	  echo "<td>" . $row['id'] . "</td>";
	  echo "<td>" . $row['phoneName'] . "</td>";
	  echo "<td>" . $row['price'] . "</td>";
	  echo "<td>" . $row['description'] . "</td>";
	  echo "<td><a href=comments.php?id=$test>Click To View</a></td>";
	  echo "</tr>";
	  }
 }
 
echo "</table>";

mysqli_close($con);
?> 