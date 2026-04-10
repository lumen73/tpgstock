<?php
require "connexion.php";
if (isset($_POST['connexion'])) {

	if (!empty($_POST['login']) and !empty($_POST['mdp'])) {

		$login = $_POST['login'];
		$mdp = $_POST['mdp'];
		$q = $bd->prepare("select iduser,nomuser from utilisateur where login=? and mdp=?");
		$q->execute(array($login, $mdp));
		$d = $q->fetch();
		if ($d) {
			$_SESSION['nomuser'] = $d['nomuser'];
			$_SESSION['iduser'] = $d['iduser'];
			header("location:galeriecategorie.php");
		} else {
			echo '<script>alert("login ou Mot de passe incorrect")</script>';
			echo '<div class="alert alert-danger">login ou Mot de passe incorrect</div>';
		}
	}
}
?>

<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="../bootstrap-5.3.0/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
	<title>identification</title>
</head>

<body>
	<div class="container">
		<div class="row">
			<div class="col-md-4"></div>
			<div class="col-md-4">
				<img src="img/images.png" width="150" height="150" />
				<fieldset>
					<legend>IDENTIFICATION</legend>
					<form method="POST" action="">
						<label>login</label>
						<input type="text" name="login" class="form-control" /><br>
						<label>Mot de passe</label>
						<input type="password" name="mdp" class="form-control" /><br>
						<input type="submit" name="connexion" value="se connecter" class="btn btn-success" /><br>

					</form>

				</fieldset>
			</div>
			<div class="col-md-4"></div>
		</div>
	</div>
</body>

</html>