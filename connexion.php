<?php
session_start();
try
{
$bd=new PDO('mysql:host=localhost; dbname=bdtpgstock','root','');

}
catch(Exception $e)
{
die('Erreur connexion à la base de données');
}

?>