<?php
require "connexion.php";
if(isset($_POST['ajouter']))
{
	$id=0;
	$categorie=addslashes($_POST['categorie']);
	$bd->exec("insert into categories (categorie) values ('$categorie')");
	$id=$bd->lastInsertId();
	if(move_uploaded_file($_FILES['photo']['tmp_name'],'photos/'.basename($_FILES['photo']['name'])))
	{	
		$bd->exec("update categories set photo='".basename($_FILES['photo']['name'])."' where idcategorie='$id' ");	
		echo '<script>alert("Enregistrement effectué")</script>';
		echo '<div class="alert alert-success">Enregistrement effectué</div>';
	}
	
}
else if (isset($_POST['appliquermodifier'])){
	$idcategorie=$_POST['idcategorie'];
	$categorie=addslashes($_POST['categorie']);
	$bd->exec("update categories set categorie='$categorie' where idcategorie='$idcategorie'");
	echo'<script>alert("Modification effectuée")</script>';
	echo '<div class="alert alert-success">Modification effectuée</div>';
}
else if( !empty($_POST['supprimer']))
 {
	$idcategorie=$_POST['idcategorie'];
	$bd->exec("delete from categories where idcategorie='$idcategorie'");
	echo'<script>alert("suppression effectuée")</script>';
	echo '<div class="alert alert-success">suppression effectuée</div>';
 }
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
	<title>deconnexion</title>
</head>
<body>
<div class="container">
	<div class="row">
		<div class="col-md-3">
			<img src="img/images.png" width="150" height="150" />
			<?php
			if(isset($_SESSION['nomuser'])){
				echo $_SESSION['nomuser'];
			}	 
			?>
			<h4>MENU</h4>
			<?php include "les menus.php"?>
		</div>
		<div class="col-md-9">
 				<div style="float:right;"><a href="deconnexion.php" class="btn btn-danger">Se deconneter</a></div>
 				<h2 style="text-align: center; margin-top: 50px;">SYSTEME DE GESTION DE STOCK</h2>
				<?php
				if (isset($_POST['modifier']))
				{
					$idcategorie=$_POST['idcategorie'];
					$categorie=$_POST['categorie'];
					?>
				<fieldset><legend>Modifier catégorie</legend>
				<form action="" method="POST" onsubmit="return confirmermodifier()">
				<label for="">Catégorie</label>
				<input type="hidden" name="idcategorie" value="<?php echo $idcategorie?>">
				<input type="text" class="form-control" name="categorie" value="<?php echo $categorie?>"><br>
				<input type="submit" value="MODIFIER" name="appliquermodifier" class="btn btn-warning">
				</form>
				</fieldset>
				<?php
				}
				else
				{
					?>
					<fieldset>
					<legend>Ajouter Catégorie</legend>
 					<form action="" method="POST" enctype="multipart/form-data">
 						<label>Catégorie</label>
 						<input type="text" name="categorie" class="form-control" required>
 						<label>Photo</label>
 						<input type="file" name="photo" class="form-control" required><br>
 						<input type="submit" name="ajouter" value="AJOUTER" class="btn btn-success">
 					</form>
					</fieldset>
				
				<?php
				}
				?>
 				

 					<div class="table" style="margin-top: 50px;">

 						<table class="table table-bordered" style="border:1px solid black; width: 100%; ">
 							<tr>
 								<th>Catégorie</th>
 								<th>Photo</th>
 								<th>Modifier</th>
 								<th>Supprimer</th>
 							</tr>
 							<tbody>
							 <?php
                        $q = $bd->query("SELECT idcategorie, categorie, photo FROM categories order by categorie");
                        while ($d = $q->fetch()) {
                            ?>
                            <tr>
                                <td><?php echo $d['categorie']; ?></td>
                                <td><img src="photos/<?php echo $d['photo']; ?>" alt="" width="200" height="200"> </td>
                                <td>
                                    <form method="POST" action="" onsubmit="return confirmermodifier()">
                                        <input type="hidden" value="<?php echo $d['idcategorie'] ?>" name="idcategorie">
                                        <input type="hidden" value="<?php echo $d['categorie'] ?>" name="categorie" required>
                                        <input type="submit" name="modifier" value="MODIFIER" class="btn btn-warning">
                                    </form>
                                </td>
                                <td>
                                    <form method="POST" action="" onsubmit="return confirmersupprimer()">
                                        <input type="hidden" value="<?php echo $d['idcategorie'] ?>" name="idcategorie">
                                        <input type="submit" name="supprimer" value="SUPPRIMER" class="btn btn-danger">
                                    </form>
                                </td>
                            </tr>
                        <?php
									}
									?>


 							</tbody>
 					</div>
			</fieldset>

			<script>
				function confirmermodifier()
				{
					return confirm("Voulez vous effectuer cette modification");
				}

				function confirmersupprimer()
				{
					return confirm("Voulez vous effectuer cette suppression");
				}
			</script>
 </body>

 </html>