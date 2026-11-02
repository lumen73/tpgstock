<?php
/*
 * FICHIER : index.php
 * RÔLE    : page de connexion.
 * Vérifie les identifiants, ouvre la session et redirige l'utilisateur.
 */
// Charge la connexion PDO et les fonctions communes.
require __DIR__.'/connexion.php';
if (!empty($_SESSION['iduser'])) { header('Location: galeriecategorie.php'); exit; }
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') { require_csrf(); $q=$bd->prepare('SELECT iduser,nomuser,mdp FROM utilisateur WHERE login=? LIMIT 1'); $q->execute([trim((string)($_POST['login']??''))]); $u=$q->fetch(); $password=(string)($_POST['mdp']??'');
if($u&&(password_verify($password,$u['mdp'])||hash_equals((string)$u['mdp'],$password))){session_regenerate_id(true);$_SESSION['iduser']=$u['iduser'];$_SESSION['nomuser']=$u['nomuser'];if(!password_get_info($u['mdp'])['algo']){$bd->prepare('UPDATE utilisateur SET mdp=? WHERE iduser=?')->execute([password_hash($password,PASSWORD_DEFAULT),$u['iduser']]);}header('Location: galeriecategorie.php');exit;} $error='Identifiants incorrects.'; }
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Connexion · TPG Stock</title><link rel="stylesheet" href="css/app.css"><style>body{min-height:100vh;display:grid;place-items:center;background:radial-gradient(ellipse at 80% 15%,#e7a94420,transparent 35%),var(--paper)}.login{width:min(430px,calc(100% - 32px));background:var(--panel);padding:38px;border-radius:18px;box-shadow:var(--shadow)}.login .brand{color:var(--ink);padding:0}.login h1{font-size:26px;margin:35px 0 5px}.login p{color:var(--muted);margin:0 0 25px}.login .field{margin:15px 0}.login .btn{width:100%;margin-top:9px}.error{background:#fce8e5;color:#963c31;padding:11px;border-radius:8px;margin:12px 0}</style></head><body><main class="login"><a class="brand" href="index.php"><span class="brand-mark">T</span><span>TPG <small>STOCK & MARCHÉ</small></span></a><h1>Ravi de vous revoir</h1><p>Connectez-vous à votre espace de gestion.</p><?php if($error):?><div class="error"><?=e($error)?></div><?php endif?><form method="post" autocomplete="on"><?=csrf_field()?><div class="field"><label for="login">Identifiant</label><input id="login" name="login" required autocomplete="username"></div><div class="field"><label for="mdp">Mot de passe</label><input id="mdp" name="mdp" type="password" required autocomplete="current-password"></div><button class="btn btn-primary" type="submit">Se connecter →</button></form></main></body></html>
