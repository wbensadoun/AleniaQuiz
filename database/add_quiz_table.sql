-- Création de la table quiz
CREATE TABLE IF NOT EXISTS quiz (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    professor_id INT NOT NULL,
    FOREIGN KEY (professor_id) REFERENCES users(id)
);

-- Ajout de la colonne quiz_id à la table questions
ALTER TABLE questions
ADD COLUMN quiz_id INT,
ADD FOREIGN KEY (quiz_id) REFERENCES quiz(id);
