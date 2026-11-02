<?php
/*
 * FICHIER : menu.php
 * RÔLE    : ancienne URL conservée pour compatibilité.
 *
 * Redirige les utilisateurs non connectés vers la connexion et les
 * utilisateurs connectés vers le tableau de bord.
 */
require __DIR__ . '/connexion.php';

$destination = empty($_SESSION['iduser'])
    ? 'index.php'
    : 'galeriecategorie.php';

header('Location: ' . $destination);
exit;
