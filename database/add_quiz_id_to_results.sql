-- Ajouter la colonne quiz_id à la table results
ALTER TABLE results ADD COLUMN quiz_id INT NOT NULL AFTER user_id;
ALTER TABLE results ADD CONSTRAINT results_ibfk_2 FOREIGN KEY (quiz_id) REFERENCES quizzes(id);
