# TPG Stock

Application web de gestion de stock pour organiser un catalogue de produits : catégories, articles, quantités, prix et photos. L’interface est en français et s’adapte aux écrans mobiles.

## Fonctionnalités

- Tableau de bord avec le nombre de catégories, d’articles et la quantité totale en stock.
- Affichage des articles sous forme de galerie ou navigation par catégorie.
- Création, modification et suppression de catégories et d’articles.
- Fiches détaillées pour consulter les informations d’un article ou d’une catégorie.
- Gestion du prix, de la quantité et de la description des articles.
- Téléversement de photos JPG, PNG ou WebP de 5 Mo maximum.
- Connexion par compte utilisateur et fermeture de session.
- Protection des formulaires contre les requêtes CSRF, requêtes SQL préparées et échappement des données affichées.

## Technologies

- PHP 8.x
- MySQL 8.0 ou plus récent
- Apache (par exemple avec WampServer)
- HTML, CSS et JavaScript

Aucun paquet Composer ou npm n’est nécessaire. PHP doit avoir les extensions **PDO MySQL**, **fileinfo** et **mbstring** activées.

## Installation locale avec WampServer

### 1. Placer le projet

Copiez ou clonez le projet dans le répertoire web de WampServer, par exemple **C:/wamp64/www/tpgstock**, puis démarrez les services Apache et MySQL.

### 2. Créer la base et les tables

Dans phpMyAdmin, ouvrez l’onglet **SQL** et exécutez ce script pour une nouvelle installation :

~~~sql
CREATE DATABASE IF NOT EXISTS bdtpgstock
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_0900_ai_ci;

USE bdtpgstock;

CREATE TABLE IF NOT EXISTS categories (
  idcategorie INT NOT NULL AUTO_INCREMENT,
  categorie VARCHAR(250) NOT NULL,
  PRIMARY KEY (idcategorie)
) ENGINE=InnoDB
  DEFAULT CHARACTER SET=utf8mb4
  COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS articles (
  idarticle INT NOT NULL AUTO_INCREMENT,
  idcategorie INT DEFAULT NULL,
  article VARCHAR(250) NOT NULL,
  photo VARCHAR(250) DEFAULT NULL,
  quantite INT NOT NULL DEFAULT 0,
  prix DECIMAL(12,2) NOT NULL DEFAULT 0,
  description TEXT DEFAULT NULL,
  PRIMARY KEY (idarticle),
  KEY idx_articles_idcategorie (idcategorie),
  CONSTRAINT fk_articles_categories
    FOREIGN KEY (idcategorie) REFERENCES categories (idcategorie)
    ON DELETE SET NULL
) ENGINE=InnoDB
  DEFAULT CHARACTER SET=utf8mb4
  COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS utilisateur (
  iduser INT NOT NULL AUTO_INCREMENT,
  nomuser VARCHAR(250) NOT NULL,
  login VARCHAR(250) NOT NULL,
  mdp VARCHAR(255) NOT NULL,
  PRIMARY KEY (iduser),
  UNIQUE KEY uq_utilisateur_login (login)
) ENGINE=InnoDB
  DEFAULT CHARACTER SET=utf8mb4
  COLLATE=utf8mb4_0900_ai_ci;
~~~

### 3. Configurer l’accès MySQL

Le fichier [connexion.php](./connexion.php) utilise actuellement les paramètres suivants :

- Hôte : **localhost**
- Base : **bdtpgstock**
- Utilisateur : **root**
- Mot de passe : vide

Modifiez la création de la connexion PDO dans [connexion.php](./connexion.php) si votre installation MySQL utilise d’autres identifiants. Pour un serveur partagé ou en production, utilisez un compte MySQL dédié avec un mot de passe, et ne publiez pas ses identifiants.

### 4. Créer le premier compte gestionnaire

Générez un hash localement avec PHP en remplaçant le texte d’exemple par le mot de passe souhaité :

~~~sh
php -r "echo password_hash('VOTRE_MOT_DE_PASSE', PASSWORD_DEFAULT), PHP_EOL;"
~~~

Dans phpMyAdmin, ajoutez ensuite l’utilisateur en collant le hash obtenu à la place du texte ci-dessous :

~~~sql
INSERT INTO utilisateur (nomuser, login, mdp)
VALUES ('Administrateur', 'admin', 'COLLER_ICI_LE_HASH');
~~~

Choisissez votre propre nom et identifiant. Le dépôt ne fournit pas de compte par défaut.

### 5. Vérifier les photos et ouvrir l’application

Le dossier [photos/](./photos/) doit être accessible en écriture par Apache pour enregistrer les images téléversées. La limite applicative est de 5 Mo par image ; si nécessaire, vérifiez aussi les réglages PHP **upload_max_filesize** et **post_max_size**.

Ouvrez ensuite **http://localhost/tpgstock/index.php** dans votre navigateur. Si le dossier du projet porte un autre nom ou se trouve dans un sous-répertoire de **www**, adaptez l’URL.

## Utilisation

1. Connectez-vous avec le compte gestionnaire créé à l’installation.
2. Créez d’abord une ou plusieurs catégories.
3. Ajoutez les articles et renseignez leur catégorie, stock, prix, description et photo.
4. Consultez le tableau de bord pour parcourir les articles ou afficher le stock par catégorie.

Lors de la suppression d’une catégorie, l’application propose de conserver les articles associés ou de supprimer également ces articles.

## Fichiers SQL fournis

- [migration_articles.sql](./migration_articles.sql) ajoute les champs photo, quantité, prix et description à une table **articles** existante et retire l’ancienne colonne photo de **categories**. Sauvegardez la base avant de l’utiliser sur des données existantes.
- [categories.sql](./categories.sql) est un export historique qui commence par supprimer la table **categories**. Ne l’exécutez pas sur une base contenant des données à conserver.
- [create table articles(idarticle int auto.sql](<./create table articles(idarticle int auto.sql>) contient une ancienne définition minimale de la table **articles**. Pour une nouvelle installation, utilisez plutôt le schéma complet de ce README.

## Pages principales

| Fichier | Rôle |
| --- | --- |
| [index.php](./index.php) | Connexion |
| [galeriecategorie.php](./galeriecategorie.php) | Tableau de bord et galeries |
| [Ajoutecategorie.php](./Ajoutecategorie.php) | Gestion des catégories |
| [Ajoutearticle.php](./Ajoutearticle.php) | Gestion des articles |
| [detailscategorie.php](./detailscategorie.php) | Fiche d’une catégorie |
| [detailsarticle.php](./detailsarticle.php) | Fiche d’un article |
| [deconnexion.php](./deconnexion.php) | Fermeture de session |

## Structure du projet

~~~text
.
├── css/                 # Feuilles de style et interactions JavaScript
├── img/                 # Images de l’interface
├── photos/              # Photos d’articles téléversées
├── connexion.php        # Connexion PDO, session et fonctions communes
├── galeriecategorie.php # Tableau de bord
├── Ajoutearticle.php    # Catalogue et formulaire des articles
└── Ajoutecategorie.php  # Gestion des catégories
~~~
