<html>
<body>
<table  border="1">
	<tr>
	<td>Code</td>
	<td>Lname</td>
	<td>Fname</td>
	<td>Initial</td>
	<td>Areacode</td>
	<td>Phone</td>
	<td>Renew_date</td>
	<td>Agent_code</td>

	<tr>
<?php
include('dataBase.php');

    
    $result=mysqli_query($con, "SELECT * FROM CUSTOMER WHERE AGENT_CODE ='503' ");
    $count=mysqli_num_rows($result);
	
    if( $count != '')
    {

            while($row = mysqli_fetch_array($result))
            {
            $code = $row[0];
            $lname = $row[1];
            $fname = $row[2];
            $initial = $row[3];            
            $areacode = $row[4];
            $phone = $row[5];
            $cus_renew_date = $row[6];
            $agent_code = $row[7];
            ?>
			
			<tr>
			<td><?php echo $code; ?></td>
            <td><?php echo $lname; ?></td>
			<td><?php echo $fname; ?></td>
            <td><?php echo $initial; ?></td>
			<td><?php echo $areacode; ?></td>
            <td><?php echo $phone; ?></td>
			<td><?php echo $cus_renew_date; ?></td>
            <td><?php echo $agent_code; ?></td>

			<tr>
			
			
    
            <?php
            }
            ?>
        
        <?php
    }
    else
    {
       ?>
        <h1>failed</h1>
    <?php
    }
    
mysqli_close($con);


?>
</table></body></html>


