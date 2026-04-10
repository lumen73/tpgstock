<?php
require "connexion.php";
if (isset($_POST['ajouter'])) {
	$idcategorie = $_POST['idcategorie'];
	$article = addslashes($_POST['article']);
	$q = $bd->exec("insert into articles (idcategorie,article) values ('$idcategorie', '$article')");
	if ($q) {
		echo '<script>alert("Article ajouté")</script>';
		echo '<div class="alert alert-success">Article ajouté</div>';
	} else
		echo '<script>alert("Insertion non effectué")</script>';
} else if (isset($_POST['appliquermodifier'])) {
	$idcategorie = $_POST['idcategorie'];
	$idarticle = addslashes($_POST['idarticle']);
	$article = addslashes($_POST['article']);
	$bd->exec("update articles set article='$article', idcategorie='$idcategorie' where idarticle='$idarticle'");
	echo '<script>alert("Modification effectuée")</script>';
	echo '<div class="alert alert-success">Modification effectuée</div>';
} else if (!empty($_POST['supprimer'])) {
	$idarticle = $_POST['idarticle'];
	$bd->exec("delete from articles where idarticle='$idarticle'");
	echo '<script>alert("suppression effectuée")</script>';
	echo '<div class="alert alert-success">suppression effectuée</div>';
}
?>
<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="../bootstrap-5.3.0/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
	<title>Article</title>
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
				<div style="float:right"><a href="deconnexion.php" class="btn btn-danger">se deconnecter</a></div>
				<br>

				<?php
				if (isset($_POST['modifier'])) {
					$idcategorie = $_POST['idcategorie'];
					$idarticle = $_POST['idarticle'];
					$article = $_POST['article'];


				?>
					<fieldset>
						<legend>MODIFIER ARTICLE</legend>
					</fieldset>
					<form action="" method="POST">
						<label for="">Categorie</label>
						<input type="hidden" name="idarticle" value="<?php echo $idarticle ?>">
						<select name="idcategorie" id="" required class="form-control">
							<?php
							$q = $bd->query("select idcategorie,categorie from categories");
							while ($d = $q->fetch()) {
								if ($idcategorie == $d['idcategorie']) {
							?>
									<option value="<?php echo $d['idcategorie'] ?>" selected="selected"><?php echo $d['categorie'] ?></option>
								<?php
								} else {
								?>
									<option value="<?php echo $d['idcategorie'] ?>"><?php echo $d['categorie'] ?></option>
							<?php
								}
							}
							?>
						</select>
						<label for="">Article</label>
						<input type="text" name="article" value="<?php echo $article ?>" class="form-control" required>
						<input type="submit" name="appliquermodifier" value="Modifier" class="btn btn-warning">
					</form>
				<?php
				} else {
				?>
					<fieldset>
						<legend>AJOUTER ARTICLE</legend>
						<form method="POST" action="">
							<label>Article</label>
							<input type="text" name="article" class="form-control" required><br>
							<label>Categorie</label>
							<select name="idcategorie" class="form-control" required>
								<option value="">-Choisir-</option>
								<?php
								$q = $bd->query("select idcategorie, categorie from categories order by categorie");
								while ($d = $q->fetch()) {
								?>
									<option value="<?php echo $d['idcategorie'] ?>">
										<?php echo $d['categorie'] ?> </option>
								<?php
								}
								?>
							</select>
							<br><input type="submit" value="AJOUTER" name="ajouter" class="btn btn-warning">

						</form>

					</fieldset>
				<?php
				}
				?>



				<table class="table">
					<thead>
						<tr>
							<th>Article</th>
							<th>Categorie</th>
							<th>Modifier</th>
							<th>Supprimer</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$q = $bd->query("select articles.idarticle as idarticle,article, categorie,categories.idcategorie as idcategorie from articles inner join categories on articles.idcategorie=categories.idcategorie");
						while ($d = $q->fetch()) {
						?>
							<tr>
								<td><?php echo $d['article'] ?></td>
								<td><?php echo $d['categorie'] ?></td>
								<td>
									<form action="" method="POST">
										<input type="hidden" value="<?php echo $d['idarticle'] ?>" name="idarticle">
										<input type="hidden" value="<?php echo $d['article'] ?>" name="article">
										<input type="hidden" value="<?php echo $d['idcategorie'] ?>" name="idcategorie">
										<input type="submit" value="MODIFIER" name="modifier" class="btn btn-warning">
									</form>
								</td>
								<td>
									<form action="" method="POST">
										<input type="hidden" value="<?php echo $d['idarticle'] ?>" name="idarticle">
										<input type="submit" value="SUPPRIMER" name="supprimer" class="btn btn-danger">
									</form>
								</td>
							</tr>
						<?php
						}
						?>
					</tbody>
				</table>

			</div>
		</div>
	</div>
</body>

</html>