<html>
<head>
<script>
function category(str)
{
if (str=="")
 {
 document.getElementById("txtHint2").innerHTML="";
 return;
 }
if (window.XMLHttpRequest)
 {// code for IE7+, Firefox, Chrome, Opera, Safari
 xmlhttp=new XMLHttpRequest();
 }
else
 {// code for IE6, IE5
 xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
 }
xmlhttp.onreadystatechange=function()
 {
 if (xmlhttp.readyState==4 && xmlhttp.status==200)
   {
   document.getElementById("txtHint2").innerHTML=xmlhttp.responseText;
   }
 }
xmlhttp.open("GET","phones.php?q="+str,true);
xmlhttp.send();
}
</script>
<script>

function search()
{
category(document.getElementById("mytextarea").value);
}


</script>
</head>
<body>



<textarea id="mytextarea" cols="10" rows="1" ></textarea>
<br>
<button type="button" onclick="search()"> Add to Search</button>
<br>
<br>
<div id= "test">
	<form>
	<select name="users" onchange="category(this.value)">
	<option value="">Select a Phone:</option>
	<option value="0">All</option>
	<option value="1">Samsung SIII mini</option>
	<option value="2">Iphone 5s</option>
	</select>
	</form>
</div>
<br>
<div id="txtHint2"><b>Phone info will be listed here.</b></div>


</body>
</html> 