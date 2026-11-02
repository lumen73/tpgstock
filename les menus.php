<?php
/*
 * FICHIER : les menus.php
 * RÔLE    : ancien fragment de navigation conservé pour compatibilité.
 * Vérifie la session avant d'afficher les liens principaux.
 */
require_once __DIR__.'/connexion.php';if(empty($_SESSION['iduser']))return;
?><nav><a href="galeriecategorie.php">Vue d’ensemble</a><a href="Ajoutecategorie.php">Catégories</a><a href="Ajoutearticle.php">Articles</a><a href="deconnexion.php">Déconnexion</a></nav>
