<?php
/*
 * FICHIER : Ajoutearticle.php
 * RÔLE    : gérer les articles du catalogue.
 *
 * Ce fichier réalise trois tâches :
 * 1. afficher le formulaire de création ou de modification ;
 * 2. traiter les actions POST (ajout, modification, suppression) ;
 * 3. afficher la liste des articles enregistrés.
 */

// Charge la connexion PDO, les fonctions communes et la session utilisateur.
require __DIR__ . '/connexion.php';

// Toutes les opérations d'administration sont réservées aux utilisateurs connectés.
require_auth();

// Contient l'article sélectionné lorsque l'utilisateur demande une modification.
$editing = null;

/**
 * Valide et enregistre la photo d'un article.
 *
 * @param array|null $upload Données du fichier reçues dans $_FILES.
 * @return string|null Nom sécurisé du fichier créé, ou null si aucune photo n'a été envoyée.
 */
function article_photo(?array $upload): ?string
{
    // Une photo n'est pas obligatoire : absence de fichier = aucune action.
    if (!$upload || $upload['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    // La liste blanche associe chaque type MIME à une extension autorisée.
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    // Vérifie le vrai type du fichier, et non pas seulement son extension.
    $mime = (
        $upload['error'] === UPLOAD_ERR_OK
        && is_uploaded_file($upload['tmp_name'])
    )
        ? (new finfo(FILEINFO_MIME_TYPE))->file($upload['tmp_name'])
        : false;

    // Refuse les erreurs d'upload, les fichiers trop lourds et les formats inconnus.
    if (
        $upload['error'] !== UPLOAD_ERR_OK
        || $upload['size'] > 5 * 1024 * 1024
        || !isset($allowed[$mime])
    ) {
        throw new RuntimeException(
            'Image invalide. Formats autorisés : JPG, PNG, WebP (5 Mo maximum).'
        );
    }

    // Génère un nom aléatoire afin d'éviter les doublons et les noms dangereux.
    $name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    $directory = __DIR__ . '/photos';

    // Crée le dossier de stockage si nécessaire.
    if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
    }

    // Déplace le fichier temporaire vers le dossier public des photos.
    if (!move_uploaded_file($upload['tmp_name'], $directory . '/' . $name)) {
        throw new RuntimeException('Impossible d’enregistrer la photo.');
    }

    return $name;
}

/*
 * TRAITEMENT DES FORMULAIRES
 * Cette partie ne s'exécute que lorsqu'une requête POST est envoyée.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Protège toutes les actions contre les requêtes CSRF.
    require_csrf();

    // action indique le bouton utilisé : save, edit ou delete.
    $action = (string) ($_POST['action'] ?? '');

    // Convertit l'identifiant reçu en entier validé.
    $id = filter_var($_POST['idarticle'] ?? null, FILTER_VALIDATE_INT);

    /*
     * SUPPRESSION D'UN ARTICLE
     * La photo liée est récupérée avant la suppression SQL, puis supprimée du disque.
     */
    if ($action === 'delete' && $id) {
        $query = $bd->prepare('SELECT photo FROM articles WHERE idarticle = ?');
        $query->execute([$id]);
        $oldPhoto = $query->fetchColumn();

        $bd->prepare('DELETE FROM articles WHERE idarticle = ?')->execute([$id]);

        if ($oldPhoto && is_file(__DIR__ . '/photos/' . $oldPhoto)) {
            @unlink(__DIR__ . '/photos/' . $oldPhoto);
        }

        flash('Article supprimé.');
        header('Location: Ajoutearticle.php');
        exit;
    }

    /*
     * AJOUT OU MODIFICATION D'UN ARTICLE
     */
    if ($action === 'save') {
        // Récupère et nettoie les champs saisis dans le formulaire.
        $name = trim((string) ($_POST['article'] ?? ''));
        $category = filter_var($_POST['idcategorie'] ?? null, FILTER_VALIDATE_INT);
        $quantity = max(0, (int) ($_POST['quantite'] ?? 0));
        $price = max(0, (float) ($_POST['prix'] ?? 0));
        $description = trim((string) ($_POST['description'] ?? ''));

        // Le nom et la catégorie sont indispensables pour créer un article.
        if ($name === '' || !$category) {
            flash('Vérifiez le nom et la catégorie.', 'error');
        } else {
            try {
                // Traite éventuellement la nouvelle photo envoyée.
                $photo = article_photo($_FILES['photo'] ?? null);

                if ($id) {
                    // MODIFICATION : remplace la photo seulement si une nouvelle est envoyée.
                    if ($photo) {
                        $query = $bd->prepare('SELECT photo FROM articles WHERE idarticle = ?');
                        $query->execute([$id]);
                        $oldPhoto = $query->fetchColumn();

                        $query = $bd->prepare(
                            'UPDATE articles
                             SET article = ?, idcategorie = ?, photo = ?, quantite = ?,
                                 prix = ?, description = ?
                             WHERE idarticle = ?'
                        );
                        $query->execute([
                            $name, $category, $photo, $quantity,
                            $price, $description, $id
                        ]);

                        // Évite de conserver l'ancienne photo devenue inutile.
                        if ($oldPhoto && is_file(__DIR__ . '/photos/' . $oldPhoto)) {
                            @unlink(__DIR__ . '/photos/' . $oldPhoto);
                        }
                    } else {
                        // Modification sans nouvelle photo : conserve l'image actuelle.
                        $query = $bd->prepare(
                            'UPDATE articles
                             SET article = ?, idcategorie = ?, quantite = ?,
                                 prix = ?, description = ?
                             WHERE idarticle = ?'
                        );
                        $query->execute([
                            $name, $category, $quantity,
                            $price, $description, $id
                        ]);
                    }
                } else {
                    // AJOUT : crée une nouvelle ligne dans la table articles.
                    $query = $bd->prepare(
                        'INSERT INTO articles
                         (article, idcategorie, photo, quantite, prix, description)
                         VALUES (?, ?, ?, ?, ?, ?)'
                    );
                    $query->execute([
                        $name, $category, $photo, $quantity, $price, $description
                    ]);
                }

                // Message temporaire affiché après la redirection.
                flash($id ? 'Article modifié.' : 'Article ajouté.');
                header('Location: Ajoutearticle.php');
                exit;
            } catch (RuntimeException $exception) {
                // Affiche l'erreur d'upload sans interrompre brutalement la page.
                flash($exception->getMessage(), 'error');
            }
        }
    }

    /*
     * CHARGEMENT D'UN ARTICLE À MODIFIER
     * Le formulaire est prérempli avec les données existantes.
     */
    if ($action === 'edit' && $id) {
        $query = $bd->prepare(
            'SELECT idarticle, article, idcategorie, photo, quantite, prix, description
             FROM articles
             WHERE idarticle = ?'
        );
        $query->execute([$id]);
        $editing = $query->fetch();
    }
}

/*
 * DONNÉES NÉCESSAIRES À L'AFFICHAGE
 */

// Liste des catégories proposées dans la liste déroulante du formulaire.
$categories = $bd
    ->query('SELECT idcategorie, categorie FROM categories ORDER BY categorie')
    ->fetchAll();

// Liste des articles avec leur catégorie.
// LEFT JOIN conserve aussi les articles dont la catégorie a été supprimée.
$rows = $bd
    ->query(
        'SELECT a.*, c.categorie
         FROM articles a
         LEFT JOIN categories c ON c.idcategorie = a.idcategorie
         ORDER BY a.article'
    )
    ->fetchAll();

// Initialise l'en-tête commun, le menu, les messages flash et la feuille CSS.
page_start('Articles', 'articles');
?>

<!-- En-tête de la page de gestion des articles. -->
<div class="eyebrow">Catalogue / Inventaire</div>
<div class="heading-row">
    <div>
        <h1>Articles</h1>
        <p>Gérez les produits, leurs photos et leur stock.</p>
    </div>
    <span class="btn btn-quiet"><?= count($rows) ?> articles</span>
</div>

<!-- Formulaire de création ou de modification. -->
<section class="panel form-panel">
    <div class="panel-head" style="padding:0 0 17px;margin-bottom:18px">
        <h2><?= $editing ? 'Modifier l’article' : 'Nouvel article' ?></h2>
    </div>

    <?php if (!$categories): ?>
        <!-- Impossible de créer un article sans catégorie disponible. -->
        <div class="empty">Créez une catégorie avant d’ajouter des articles.</div>
    <?php else: ?>
        <form method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="save">
            <input
                type="hidden"
                name="idarticle"
                value="<?= (int) ($editing['idarticle'] ?? 0) ?>"
            >

            <div class="form-grid">
                <div class="field">
                    <label for="article">Nom de l’article</label>
                    <input
                        id="article"
                        name="article"
                        maxlength="250"
                        required
                        value="<?= e($editing['article'] ?? '') ?>"
                        placeholder="Ex. Huile de palme"
                    >
                </div>

                <div class="field">
                    <label for="idcategorie">Catégorie</label>
                    <select id="idcategorie" name="idcategorie" required>
                        <option value="">Choisir une catégorie</option>
                        <?php foreach ($categories as $category): ?>
                            <option
                                value="<?= (int) $category['idcategorie'] ?>"
                                <?= ($editing && $editing['idcategorie'] == $category['idcategorie'])
                                    ? 'selected'
                                    : '' ?>
                            >
                                <?= e($category['categorie']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field">
                    <label for="quantite">Quantité disponible</label>
                    <input
                        id="quantite"
                        name="quantite"
                        type="number"
                        min="0"
                        required
                        value="<?= e($editing['quantite'] ?? 0) ?>"
                    >
                </div>

                <div class="field">
                    <label for="prix">Prix</label>
                    <input
                        id="prix"
                        name="prix"
                        type="number"
                        min="0"
                        step="0.01"
                        value="<?= e($editing['prix'] ?? 0) ?>"
                    >
                </div>

                <div class="field">
                    <label for="photo">Photo de l’article (JPG, PNG ou WebP)</label>
                    <input
                        id="photo"
                        name="photo"
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                    >
                    <?php if (!empty($editing['photo'])): ?>
                        <small>Une photo est déjà enregistrée.</small>
                    <?php endif; ?>
                </div>

                <div class="field">
                    <label for="description">Description / informations</label>
                    <textarea id="description" name="description" rows="3"><?= e(
                        $editing['description'] ?? ''
                    ) ?></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button class="btn btn-primary">
                    <?= $editing ? 'Enregistrer les changements' : '＋ Ajouter l’article' ?>
                </button>
                <?php if ($editing): ?>
                    <a class="btn btn-quiet" href="Ajoutearticle.php">Annuler</a>
                <?php endif; ?>
            </div>
        </form>
    <?php endif; ?>
</section>

<!-- Tableau récapitulatif de tous les articles du catalogue. -->
<section class="panel">
    <div class="panel-head">
        <h2>Catalogue des articles</h2>
        <small>Photos, stock et informations</small>
    </div>

    <?php if (!$rows): ?>
        <div class="empty">Aucun article enregistré.</div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Article</th>
                        <th>Catégorie</th>
                        <th>Stock</th>
                        <th>Prix</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td>
                                <?php if ($row['photo']): ?>
                                    <img
                                        class="thumb"
                                        src="photos/<?= rawurlencode((string) $row['photo']) ?>"
                                        alt="<?= e($row['article']) ?>"
                                    >
                                <?php endif; ?>
                            </td>
                            <td>
                                <b><?= e($row['article']) ?></b>
                                <?php if ($row['description']): ?>
                                    <small class="table-note">
                                        <?= e($row['description']) ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td><?= e($row['categorie'] ?? 'Catégorie supprimée') ?></td>
                            <td><?= number_format((int) $row['quantite'], 0, ) ?></td>
                            <td><?= number_format((float) $row['prix'], 0, ) ?></td>
                            <td>
                                <div class="actions">
                                    <!-- Formulaire POST pour charger l'article dans le formulaire. -->
                                    <form method="post">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="edit">
                                        <input
                                            type="hidden"
                                            name="idarticle"
                                            value="<?= (int) $row['idarticle'] ?>"
                                        >
                                        <button class="btn btn-quiet">Modifier</button>
                                    </form>

                                    <!-- Formulaire POST protégé avec confirmation JavaScript. -->
                                    <form
                                        method="post"
                                        onsubmit="return confirm('Supprimer cet article et sa photo ?')"
                                    >
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input
                                            type="hidden"
                                            name="idarticle"
                                            value="<?= (int) $row['idarticle'] ?>"
                                        >
                                        <button class="btn btn-danger">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php
// Ferme le contenu, le menu HTML et charge les scripts communs.
page_end();
