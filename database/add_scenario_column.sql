-- Ajout de la colonne scenario à la table questions
ALTER TABLE questions
ADD COLUMN scenario TEXT DEFAULT NULL;
