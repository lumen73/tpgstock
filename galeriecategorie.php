<?php
require "connexion.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="stylesheet" href="../bootstrap-5.3.0/css/bootstrap.min.css">
   <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
   <title>GALERIE</title>
</head>

<body>
   <div class="container">
      <div class="row">
         <div class="col-md-3">
            <img src="img/images.png" width="150" height="150" />
            <?php
            if (isset($_SESSION['nomuser'])) {
               echo ' Bienvenu ' . $_SESSION['nomuser'];
            }
            ?>
            <h4>MENU</h4>
            <?php include "les menus.php" ?>
         </div>
         <div class="col-md-9">
            <div style="float: right"><a href="deconnexion.php" class="btn btn-danger">Déconnexion</a></div> <br>
            <h4>GALERIE CATEGORIE</h4>

            <?php
            $i = 0;
            $q = $bd->query("SELECT photo,categorie, idcategorie FROM categories");
            while ($d = $q->fetch()) {

               $i++;
            ?>
               <a href="detailscategorie.php? id=<?php echo $d['idcategorie'] ?>& categorie=<?php echo $d['categorie'] ?>" title="Voir les détails">
                  <img src="photos/<?php echo $d['photo'] ?>" alt="" width="200" height="200" style="border-radius: 10px">
               </a>


            <?php
               if ($i % 3 == 0)
                  echo '<br>';
            }
            ?>

         </div>
      </div>
   </div>
</body>

</html>