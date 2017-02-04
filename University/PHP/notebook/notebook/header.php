<?php
function myHeader() {
?>
<div class="header">
		<div class="logo">
			<img class="logoImage" src="images/notebookwhite.png" alt="My Notebook" height="100%" width="80%">
		</div>
		
		<div class="loginScipt">
		<?php
		error_reporting(0);
		session_start();{
		
		$check=$_SESSION['login_username'];
		if ($check != ""){
			?>
			<?php
				echo "<b> Username: $check  </b>";
				?>
				<form method="post" name="logout" action="logout.php">
				<input type="submit" name="submit" id="submit"  value="Logout" style="position:relative; top:10px; left: 50%;" />
				</form>
			<?php
		}
			
		else{
			?>
				<form method="post" name="login" action="login.php">
				<label for="name" class="labelname"> Username </label>
				<input type="text" name="username" id="userid" required="required" /><br />
				<label for="name" class="labelname"> Password </label>
				<input type="password" name="password" id="passid" required="required"  /><br />
				<input type="hidden" name="location" value="<?php Echo($_SERVER['PHP_SELF']); ?>">
				<input type="submit" name="submit" id="submit"  value="Login" style="position:relative; top:10px; left: 50%;" />
				</form>
		
			<?php
		}
		}
		?>
		</div>
	</div>
<?php
}
?>