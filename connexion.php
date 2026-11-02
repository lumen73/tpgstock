<?php
/*
 * FICHIER : connexion.php
 * RÔLE    : bootstrap commun de l'application.
 *
 * Toutes les pages l'incluent pour démarrer la session, ouvrir PDO,
 * vérifier la structure de la base et utiliser les fonctions communes
 * de sécurité, de messages et de mise en page.
 */
declare(strict_types=1);
// Session utilisateur : mémorise la connexion, le token CSRF et les messages flash.
if (session_status() !== PHP_SESSION_ACTIVE) {
    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params(['httponly'=>true, 'secure'=>$secure, 'samesite'=>'Lax']);
    session_start();
}
// Connexion centralisée à la base MySQL du projet.
try {
    $bd = new PDO('mysql:host=localhost;dbname=bdtpgstock;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES=>false,
    ]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit('Connexion à la base de données indisponible.');
}
// Migration automatique : les photos, le stock et les informations
// complémentaires sont stockés sur les articles, pas sur les catégories.
try {
    $columns = $bd->query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='articles'")->fetchAll(PDO::FETCH_COLUMN);
    $definitions = [
        'photo' => "VARCHAR(250) DEFAULT NULL",
        'quantite' => "INT NOT NULL DEFAULT 0",
        'prix' => "DECIMAL(12,2) NOT NULL DEFAULT 0",
        'description' => "TEXT DEFAULT NULL",
    ];
    foreach ($definitions as $column => $definition) {
        if (!in_array($column, $columns, true)) {
            $bd->exec("ALTER TABLE articles ADD COLUMN ".$column." ".$definition);
        }
    }
    $categoryColumns = $bd->query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='categories'")->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('photo', $categoryColumns, true)) {
        $bd->exec('ALTER TABLE categories DROP COLUMN photo');
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
}
function e($value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function csrf_token(): string { return $_SESSION['_csrf'] ??= bin2hex(random_bytes(32)); }
function csrf_field(): string { return '<input type="hidden" name="_csrf" value="'.e(csrf_token()).'">'; }
function require_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !hash_equals($_SESSION['_csrf'] ?? '', (string)($_POST['_csrf'] ?? ''))) {
        http_response_code(400); exit('Jeton de sécurité invalide. Rechargez la page.');
    }
}
function require_auth(): void { if (empty($_SESSION['iduser'])) { header('Location: index.php'); exit; } }
function flash(string $message, string $type='success'): void { $_SESSION['_flash']=['message'=>$message,'type'=>$type]; }
function take_flash(): ?array { $f=$_SESSION['_flash']??null; unset($_SESSION['_flash']); return $f; }
function page_start(string $title, string $active=''): void {
    $user=e($_SESSION['nomuser']??''); $flash=take_flash();
    echo '<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#171b19"><title>'.e($title).' · TPG Stock</title><link rel="stylesheet" href="css/app.css"></head><body>';
    echo '<aside class="sidebar"><a class="brand" href="galeriecategorie.php"><span class="brand-mark">T</span><span>TPG <small>STOCK & MARCHÉ</small></span></a><div class="profile"><span class="avatar">'.e(mb_strtoupper(mb_substr($user,0,1))).'</span><span><b>'.$user.'</b><small>Gestionnaire</small></span></div><nav><small class="nav-caption">ESPACE DE TRAVAIL</small><a class="nav-link '.($active==='home'?'active':'').'" href="galeriecategorie.php"><span>◫</span> Vue d’ensemble</a><a class="nav-link '.($active==='categories'?'active':'').'" href="Ajoutecategorie.php"><span>▦</span> Catégories</a><a class="nav-link '.($active==='articles'?'active':'').'" href="Ajoutearticle.php"><span>◇</span> Articles</a></nav><div class="sidebar-bottom"><span class="location"><i></i> Cotonou, Bénin</span><a class="nav-link logout" href="deconnexion.php">↗ &nbsp;Se déconnecter</a></div></aside><main class="main"><header class="topbar"><button class="menu-toggle" aria-label="Ouvrir le menu">☰</button><div class="crumb">Gestion du stock <span>/</span> '.e($title).'</div><span class="today">'.date('d.m.Y').'</span></header><section class="content">';
    if ($flash) echo '<div class="toast toast-'.e($flash['type']).'" role="status">'.e($flash['message']).'<button aria-label="Fermer">×</button></div>';
}
function page_end(): void { echo '</section></main><script src="css/app.js" defer></script></body></html>'; }
