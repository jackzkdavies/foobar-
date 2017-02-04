<html>
<head>
<title>My Notebook - Show </title>
    <link rel="stylesheet" href="notebook.css">
</head>

<body>

<div class ="body">
	<?php
	error_reporting(0);
	include('db.php');
	
	//Display Header
	include('header.php');
	myHeader();
	
	//start session
	session_start();{
	$check=$_SESSION['login_username'];
	if ($_POST['id'] != ""){
	$_SESSION['session_id'] = $_POST['id'];}
	$id = $_SESSION['session_id'];


	}
	
	//set content search 
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
			?>
			<h1><?php echo $title; ?></h1>
			<h2><?php echo $cont; ?></h2>
			<h3>Last edited on <?php echo $date; ?></h3>
			<?php
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