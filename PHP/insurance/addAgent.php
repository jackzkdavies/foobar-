<html>
<body>
<?php
include('dataBase.php');


   
    $sql="INSERT INTO AGENT VALUES
	('504','Arataki','Rapana','T','677','123-5590','Colombo Drive','Massey University',
	'PN','36155','5/14/2014','23498.29','5798.57','1895.86','124093.45','23')";
    


if (!mysqli_query($con,$sql))
  {
  die('Error: ' . mysqli_error($con));
  }
echo "1 entry added";
    
mysqli_close($con);


?>
</body></html>


