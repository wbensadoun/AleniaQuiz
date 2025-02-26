-- Ajout de la colonne timer à la table questions
ALTER TABLE questions
ADD COLUMN timer INT NOT NULL DEFAULT 30;
