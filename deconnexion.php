<?php
/*
 * FICHIER : deconnexion.php
 * RÔLE    : fermer proprement la session de l'utilisateur.
 */
require __DIR__ . '/connexion.php';

// Efface toutes les données conservées dans la session.
$_SESSION = [];

// Supprime également le cookie de session du navigateur.
if (ini_get('session.use_cookies')) {
    $parameters = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $parameters['path'],
        $parameters['domain'],
        $parameters['secure'],
        $parameters['httponly']
    );
}

// Détruit la session côté serveur puis revient à la page de connexion.
session_destroy();
header('Location: index.php');
exit;
