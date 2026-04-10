<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
	<title></title>
</head>

<body>
	<?php
	session_start();
	session_destroy();
	header("location:index.php");
	?>
</body>

</html>