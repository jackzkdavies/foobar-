<html>
<head>
<title>My Notebook</title>
    <link rel="stylesheet" href="notebook.css">
</head>

<body>

	<?php
		session_start();

		error_reporting(0);
		include('header.php');
		myHeader();
	?>

	<div class = "failed">
		
		<h2 class ="failed"> Login Failed: Please try again. </h2>
		<a class ="failed" href="index.php" >return</a> 
		
	</div>
</body>

</html>