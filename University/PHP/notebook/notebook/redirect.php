<html>
<body>
<?php
if ($_GET['action'] == 'succeed') {
  $msg = 'Logged successfully...';
  echo '
' . $msg . '
';
  header('Refresh: 2; URL=index.php');
}

else if ($_GET['action'] == 'logout') {
  session_unset();
  session_destroy();
  $msg = 'Logged out. Now come back to homepage';
  echo '
' . $msg . '
';
  header('Refresh: 2; URL=index.php');
}

?> 
</body>
</html> 