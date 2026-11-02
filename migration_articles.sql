-- FICHIER : migration_articles.sql
-- RÔLE    : mettre à niveau une ancienne base vers le modèle actuel.
-- Les champs photo, quantité, prix et description sont attachés aux articles.
-- La colonne photo historique des catégories est retirée.
ALTER TABLE articles
  ADD COLUMN IF NOT EXISTS photo VARCHAR(250) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS quantite INT NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS prix DECIMAL(12,2) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS description TEXT DEFAULT NULL;

ALTER TABLE categories DROP COLUMN IF EXISTS photo;
