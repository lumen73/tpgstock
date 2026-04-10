<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="../bootstrap-5.3.0/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
	<title>Déconnexion</title>
</head>

<body>
	<div class="container">
		<div class="row">
			<div class="col-md-3">
				<img src="img/images.png" width="150" height="150" />
				<?php
				if (isset($_SESSION['nomuser'])) {
					echo $_SESSION['nomuser'];
				}
				?>
				<h4>MENU</h4>
				<?php include "les menus.php" ?>
			</div>
			<div class="col-md-9">
				<br>
				<div style="float: right"><a href="deconnexion.php" class="btn btn-danger">Déconnexion</a></div>
				<br>
				<h2 style="text-align:center">SYSTEME DE GESTION DE STOCK</h2>
			</div>
		</div>
	</div>
</body>

</html>