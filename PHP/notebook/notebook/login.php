<?php 
include('db.php');
session_start();
{
	$location = "Location:" . $_POST['location'];
    $user=mysqli_real_escape_string($con, $_POST['username']);
    $pass=mysqli_real_escape_string($con, $_POST['password']);
	
	
	
    $fetch=mysqli_query($con, "SELECT * FROM users WHERE 
                         username='$user' and password='$pass'");
    $count=mysqli_num_rows($fetch);
    if( $count != '')
    {
    
    $_SESSION['login_username']=$user;
   // header("Location:index.php"); 
	header($location);
    }
    else
    {
       header('Location:failed.php');
    }
	
mysqli_close($con);

}
?>

