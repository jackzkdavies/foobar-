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
	<td>Address</td>
	<td>City</td>
	<td>State</td>
	<td>Zip</td>
	<td>Date hired</td>
	<td>ytd_pay</td>
	<td>ytd_fit</td>
	<td>ytd_fica</td>
	<td>ytd_sls</td>
	<td>dep</td>
	<tr>
<?php
include('dataBase.php');


    
    $result=mysqli_query($con, "SELECT * FROM AGENT ");
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
            $address = $row[6];
            $city = $row[7];
            $state = $row[8];
            $zip = $row[9];
            $date_hired = $row[10];
            $ytd_pay = $row[11];
            $ytd_fit = $row[12];
            $ytd_fica = $row[13];
            $ytd_sls = $row[14];
            $dep = $row[15];
            ?>
			
			<tr>
			<td><?php echo $code; ?></td>
            <td><?php echo $lname; ?></td>
			<td><?php echo $fname; ?></td>
            <td><?php echo $initial; ?></td>
			<td><?php echo $areacode; ?></td>
            <td><?php echo $phone; ?></td>
			<td><?php echo $address; ?></td>
            <td><?php echo $city; ?></td>
			<td><?php echo $state; ?></td>
            <td><?php echo $zip; ?></td>
			<td><?php echo $date_hired; ?></td>
            <td><?php echo $ytd_pay; ?></td>
			<td><?php echo $ytd_fit; ?></td>
            <td><?php echo $ytd_fica; ?></td>
			<td><?php echo $ytd_sls; ?></td>
            <td><?php echo $dep; ?></td>
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


