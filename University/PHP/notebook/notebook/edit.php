<html>
<head>
<title>My Notebook</title>
    <link rel="stylesheet" href="notebook.css">
</head>

<body>

<div class ="body">
	<?php
	error_reporting(0);
	include('db.php');
	include('header.php');
	myHeader();
	
	//set last viewed id
	if ($_POST['id'] != ""){
	$_SESSION['session_id'] = $_POST['id'];}
	
	$id = $_POST['id'];
	$sql="SELECT entry_id, entry_title, entry_cont, entry_date FROM entry
	WHERE entry_id = '$id'";
	$result = mysqli_query($con,$sql);
	?>





	<div class = "notes">
		<div class="binder">

		</div>
			
		<div class="note">
			<?php 	
			while($row = mysqli_fetch_array($result))
			{
			$id = $row[0];
			$title = $row[1];
			$cont = $row[2];
			$date = $row[3];
			}
			?>
			

		
			<form method="post" name="login" action="update.php">
			<input type="hidden" name="location" value="<?php Echo($_SERVER['PHP_SELF']); ?>">
			<input type="hidden" name="id" value="<?php echo $id; ?>">
			<input class="inputTitle" type="text" name="title" id="titleid" required="required" maxlength="30" value="<?php Echo $title; ?> "/><br /><br />
			<textarea class="inputCont" name="cont" col="40" rows="5" maxlength="2000"><?php Echo $cont; ?></textarea>
			
			<br />
			
			<input class="inputSubmit" type="submit" name="submit" id="submit"  value="update" style="position:relative;" />
			</form>
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