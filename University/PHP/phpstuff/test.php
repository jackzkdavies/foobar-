<html>
<head>
<script>
function showUser(str)
{
if (str=="")
 {
 document.getElementById("txtHint").innerHTML="";
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
   document.getElementById("txtHint").innerHTML=xmlhttp.responseText;
   }
 }
xmlhttp.open("GET","phones.php?q="+str,true);
xmlhttp.send();
}
</script>
</head>
<body>

<form>
<select name="users" onchange="showUser(this.value)">
<option value="">Select a Phone:</option>
<option value="0">All</option>
<option value="1">Samsung SIII mini</option>
<option value="2">Iphone 5s</option>
</select>
</form>
<br>
<div id="txtHint"><b>Phone info will be listed here.</b></div>

</body>
</html> 