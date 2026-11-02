<?php
/*
 * FICHIER : detailscategorie.php
 * RÔLE    : afficher une catégorie et les articles qui lui appartiennent.
 */
require __DIR__ . '/connexion.php';
require_auth();

// Récupère l'identifiant de catégorie transmis dans l'URL.
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Charge la catégorie demandée avec une requête préparée.
$query = $bd->prepare('SELECT idcategorie, categorie FROM categories WHERE idcategorie = ?');
$query->execute([$id ?: 0]);
$category = $query->fetch();

// Une catégorie inexistante renvoie vers la galerie avec un message d'erreur.
if (!$category) {
    http_response_code(404);
    flash('Catégorie introuvable.', 'error');
    header('Location: galeriecategorie.php');
    exit;
}

// Charge tous les articles de la catégorie, avec leurs informations commerciales.
$query = $bd->prepare(
    'SELECT idarticle, article, photo, quantite, prix, description
     FROM articles
     WHERE idcategorie = ?
     ORDER BY article'
);
$query->execute([$category['idcategorie']]);
$articles = $query->fetchAll();

page_start($category['categorie'], 'home');
?>

<style>
    .article-gallery {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 18px;
        padding: 18px;
    }
    .article-card {
        background: var(--panel);
        border-radius: 14px;
        overflow: hidden;
        text-decoration: none;
        color: inherit;
        box-shadow: var(--shadow);
    }
    .article-card img,
    .article-placeholder {
        width: 100%;
        height: 180px;
        object-fit: cover;
        background: #eef0eb;
    }
    .article-placeholder {
        display: grid;
        place-items: center;
        color: var(--muted);
    }
    .article-card-body { padding: 14px; }
    .article-card h3 { margin: 0 0 7px; }
    .article-card p { color: var(--muted); min-height: 34px; }
    .article-meta {
        display: flex;
        justify-content: space-between;
        color: #9b7a43;
        font-size: 12px;
    }
</style>

<!-- Fil d'Ariane et résumé de la catégorie. -->
<div class="eyebrow">
    <a href="galeriecategorie.php">Catégories</a> / Fiche
</div>
<div class="heading-row">
    <div>
        <h1><?= e($category['categorie']) ?></h1>
        <p><?= count($articles) ?> article(s) dans cette catégorie.</p>
    </div>
    <a class="btn btn-primary" href="Ajoutearticle.php">＋ Ajouter un article</a>
</div>

<!-- Cartes des articles rattachés à cette catégorie. -->
<section class="panel">
    <div class="panel-head">
        <h2>Articles de la catégorie</h2>
        <small>Photos et informations disponibles</small>
    </div>

    <?php if (!$articles): ?>
        <div class="empty">Aucun article enregistré ici.</div>
    <?php else: ?>
        <div class="article-gallery">
            <?php foreach ($articles as $article): ?>
                <a
                    class="article-card"
                    href="detailsarticle.php?id=<?= (int) $article['idarticle'] ?>"
                >
                    <?php if ($article['photo']): ?>
                        <img
                            src="photos/<?= rawurlencode((string) $article['photo']) ?>"
                            alt="<?= e($article['article']) ?>"
                        >
                    <?php else: ?>
                        <div class="article-placeholder">Aucune photo</div>
                    <?php endif; ?>

                    <div class="article-card-body">
                        <h3><?= e($article['article']) ?></h3>
                        <p><?= e($article['description'] ?: 'Aucune description renseignée.') ?></p>
                        <div class="article-meta">
                            <span>
                                Stock :
                                <b><?= number_format((int) $article['quantite'], 0, ',', ' ') ?></b>
                            </span>
                            <span><?= number_format((float) $article['prix'], 2, ',', ' ') ?></span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php page_end(); ?>
