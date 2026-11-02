-- FICHIER : create table articles(idarticle int auto.sql
-- RÔLE    : ancienne définition minimale de la table articles.
-- La migration actuelle ajoute les colonnes photo, quantité, prix et description.
create table articles(idarticle int auto_increment primary key, idcategorie int, article varchar(200), 
constraint fkidcategorie foreign key(idcategorie) references categories(idcategorie)
)
