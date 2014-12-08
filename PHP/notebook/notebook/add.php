<html>
<head>
<title>My Notebook-Add</title>
    <link rel="stylesheet" href="notebook.css">
</head>

<body>

<div class ="body">
	<?php
	error_reporting(0);
	session_start();{
	$check=$_SESSION['login_username'];
	include('header.php');
	myHeader();
	?>

	<div class = "notes">
		<div class="binder">

		</div>
			
		<div class="note">
		<?php
		if ($check != ""){
		?>
			<form method="post" name="login" action="insert.php">
			<input type="hidden" name="location" value="<?php Echo($_SERVER['PHP_SELF']); ?>">
			<input class="inputTitle" type="text" name="title" id="titleid" required="required" maxlength="30" placeholder="TITLE" /><br /><br />
			<textarea class="inputCont" name="cont" col="40" rows="5" placeholder="enter text here" maxlength="2000"></textarea>
			
			<br />
			
			<input class="inputSubmit" type="submit" name="submit" id="submit"  value="submit" style="position:relative;" />
			</form>
		<?php
			}
		
		else{
		?>
			<form method="post" name="login" action="insert.php">
			
			<input class="inputTitle" type="text" name="title" id="titleid" required="required" maxlength="30" placeholder="Please login" /><br /><br />
			<textarea class="inputCont" name="cont" col="40" rows="5" placeholder="You must login to make a new entry" maxlength="2000"></textarea>
			
			<br />
			
			</form>
			
		<?php
			}
			}
		?>
		</div>
		
	</div>
	
		<div class="listView">
		<?php
		include('list_view.php');
		listView();
		?>
	</div>

</div>
</body>
</html>