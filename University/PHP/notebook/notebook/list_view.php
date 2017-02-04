<?php
function listView() {
include('db.php');
session_start();{

$sql="SELECT entry_id, entry_title, entry_cont, entry_date FROM entry
ORDER BY entry_title";

$result = mysqli_query($con,$sql);

//Link to homepage/new entry
?><center><br><a href="index.php" class = "listViewAdd"  >Add New Entry</a> </center><br><br> <?php

while($row = mysqli_fetch_array($result))
{
	$id = $row[0];
	$title = $row[1];
	$cont = $row[2];
	$date = $row[3];
 //echo print_r($row);
	
    echo "- "; echo $title; echo " -"; 
	?>
	<table>
	<tr>
	<td>
		<form method="post" name="list" action="show.php">
		<input type="hidden" name="id" value="<?php echo $id; ?>">
		<input type="hidden" name="title" value="<?php echo $title; ?>">
		<input type="hidden" name="cont" value="<?php echo $cont; ?>">
		<input type="hidden" name="date" value="<?php echo $date; ?>">
		<input type="submit" value="View";">
		</form>
	</td>
	
	<?php
		$check=$_SESSION['login_username'];
		if ($check != ""){
		?>
	
	<td>
		<form method="post" name="list" action="edit.php">
		<input type="hidden" name="id" value="<?php echo $id; ?>">
		<input type="hidden" name="title" value="<?php echo $title; ?>">
		<input type="hidden" name="cont" value="<?php echo $cont; ?>">
		<input type="hidden" name="date" value="<?php echo $date; ?>">
		<input type="submit" value="edit";">
		</form>
	</td>
	<td>
		<form method="post" name="list" action="delete.php">
		<input type="hidden" name="id" value="<?php echo $id; ?>">
		<input type="hidden" name="title" value="<?php echo $title; ?>">
		<input type="hidden" name="cont" value="<?php echo $cont; ?>">
		<input type="hidden" name="date" value="<?php echo $date; ?>">
		<input type="hidden" name="location" value="<?php Echo($_SERVER['PHP_SELF']); ?>">
		<input type="submit" value="delete";">
		</form>
	</td>
	<?php 
	}
	?>
	
	</tr>
	</table> 
	<hr>
	<br>
	
	<?php
	
  }

}
}
?> 
