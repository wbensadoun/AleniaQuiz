<?php
// Paramètres de connexion
$host = 'localhost';
$user = 'root';
$password = '';

try {
    // Connexion au serveur MySQL sans sélectionner de base de données
    $conn = new mysqli($host, $user, $password);
    
    if ($conn->connect_error) {
        throw new Exception("Erreur de connexion : " . $conn->connect_error);
    }
    
    echo "<h2>🔄 Mise à jour de la base de données</h2>";
    
    // Création de la base de données
    $sql = "CREATE DATABASE IF NOT EXISTS quizzapp";
    if ($conn->query($sql)) {
        echo "✅ Base de données 'quizzapp' créée ou déjà existante<br>";
    } else {
        throw new Exception("Erreur lors de la création de la base de données : " . $conn->error);
    }
    
    // Sélection de la base de données
    $conn->select_db('quizzapp');
    
    // Création de la table users
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT(11) NOT NULL AUTO_INCREMENT,
        username VARCHAR(255) NOT NULL,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(20) NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY username (username)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if ($conn->query($sql)) {
        echo "✅ Table 'users' créée ou déjà existante<br>";
    } else {
        throw new Exception("Erreur lors de la création de la table users : " . $conn->error);
    }
    
    // Création de la table quizzes
    $sql = "CREATE TABLE IF NOT EXISTS quizzes (
        id INT(11) NOT NULL AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        description TEXT DEFAULT NULL,
        category VARCHAR(50) NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        professor_id INT(11) NOT NULL,
        PRIMARY KEY (id),
        KEY professor_id (professor_id),
        CONSTRAINT quizzes_ibfk_1 FOREIGN KEY (professor_id) REFERENCES users (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if ($conn->query($sql)) {
        echo "✅ Table 'quizzes' créée ou déjà existante<br>";
    } else {
        throw new Exception("Erreur lors de la création de la table quizzes : " . $conn->error);
    }
    
    // Création de la table questions
    $sql = "CREATE TABLE IF NOT EXISTS questions (
        id INT(11) NOT NULL AUTO_INCREMENT,
        question TEXT NOT NULL,
        option_a TEXT NOT NULL,
        option_b TEXT NOT NULL,
        option_c TEXT NOT NULL,
        option_d TEXT NOT NULL,
        correct_answer CHAR(1) NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        quiz_id INT(11) DEFAULT NULL,
        timer INT(11) NOT NULL DEFAULT 30,
        scenario TEXT DEFAULT NULL,
        PRIMARY KEY (id),
        KEY quiz_id (quiz_id),
        CONSTRAINT questions_ibfk_1 FOREIGN KEY (quiz_id) REFERENCES quizzes (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if ($conn->query($sql)) {
        echo "✅ Table 'questions' créée ou déjà existante<br>";
    } else {
        throw new Exception("Erreur lors de la création de la table questions : " . $conn->error);
    }
    
    // Création de la table results
    $sql = "CREATE TABLE IF NOT EXISTS results (
        id INT(11) NOT NULL AUTO_INCREMENT,
        user_id INT(11) NOT NULL,
        quiz_id INT(11) NOT NULL,
        score INT(11) NOT NULL,
        total_questions INT(11) NOT NULL,
        completed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY quiz_id (quiz_id),
        CONSTRAINT results_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id),
        CONSTRAINT results_ibfk_2 FOREIGN KEY (quiz_id) REFERENCES quizzes (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if ($conn->query($sql)) {
        echo "✅ Table 'results' créée ou déjà existante<br>";
    } else {
        throw new Exception("Erreur lors de la création de la table results : " . $conn->error);
    }
    
    // Vérification de l'existence des utilisateurs de test
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE username = ?");
    $username = 'admin@quiz.com';
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    
    // Création des utilisateurs de test s'ils n'existent pas
    if ($count == 0) {
        $sql = "INSERT INTO users (username, password, role) VALUES
            ('admin@quiz.com', 'admin123', 'admin'),
            ('professeur@quiz.com', 'prof123', 'professeur'),
            ('eleve1@quiz.com', 'eleve123', 'eleve'),
            ('eleve2@quiz.com', 'eleve123', 'eleve'),
            ('eleve3@quiz.com', 'eleve123', 'eleve')";
        
        if ($conn->query($sql)) {
            echo "✅ Utilisateurs de test créés avec succès<br>";
            
            // Insertion des quiz de test
            $sql = "INSERT INTO quizzes (title, description, category, professor_id) VALUES
                ('Test de Culture Générale', 'Un quiz pour tester vos connaissances générales', 'culture', 2),
                ('Quiz de Géographie', 'Testez vos connaissances en géographie', 'geographie', 2)";
                
            if ($conn->query($sql)) {
                echo "✅ Quiz de test créés avec succès<br>";
                
                // Récupérer les IDs des quiz créés
                $quiz1_id = $conn->insert_id;
                $quiz2_id = $quiz1_id + 1;
                
                // Insertion des questions de test
                $sql = "INSERT INTO questions (question, option_a, option_b, option_c, option_d, correct_answer, quiz_id, timer, scenario) VALUES
                    ('Quelle est la capitale de la France ?', 'Paris', 'Londres', 'Berlin', 'Madrid', 'A', $quiz1_id, 30, 'La France est un pays d''Europe occidentale.'),
                    ('Combien font 2 + 2 ?', '3', '4', '5', '6', 'B', $quiz1_id, 20, 'Dans un calcul simple d''addition.'),
                    ('Quelle est la plus grande planète du système solaire ?', 'Mars', 'Vénus', 'Jupiter', 'Saturne', 'C', $quiz1_id, 25, 'Dans notre système solaire, il existe 8 planètes principales.'),
                    ('Quelle est la capitale de la France ?', 'Londres', 'Paris', 'Berlin', 'Marseille', 'B', $quiz2_id, 30, 'Un touriste vous demande la capitale de la France'),
                    ('Quels sont les pays frontaliers de la France ?', 'Belgique', 'Allemagne', 'Suisse', 'Colombie', 'A', $quiz2_id, 30, 'Un géographe étudie les frontières de la France'),
                    ('Quelle est la couleur du ciel ?', 'Jaune', 'Bleu', 'Vert', 'Rouge', 'B', $quiz2_id, 30, 'Un enfant vous pose la question en regardant par la fenêtre'),
                    ('Quels sont les langages de programmation web ?', 'Cobra', 'Python', 'JavaScript', 'Java', 'C', $quiz2_id, 30, 'Un développeur web prépare un projet'),
                    ('Quel est le plus grand océan du monde ?', 'Pacifique', 'Atlantique', 'Indien', 'Arctique', 'A', $quiz2_id, 30, 'Un élève prépare un exposé sur les océans'),
                    ('Qui a peint la Joconde ?', 'Léonard de Vinci', 'Michel-Ange', 'Picasso', 'Van Gogh', 'A', $quiz2_id, 30, 'Un historien d''art étudie la Renaissance'),
                    ('Quelles sont les couleurs du drapeau français ?', 'Bleu', 'Blanc', 'Rouge', 'Vert', 'C', $quiz2_id, 30, 'Un enfant apprend les couleurs du drapeau'),
                    ('Quels sont les océans qui bordent la France ?', 'Atlantique', 'Pacifique', 'Indien', 'Méditerranée', 'A', $quiz2_id, 30, 'Un marin étudie les côtes françaises')";
                    
                if ($conn->query($sql)) {
                    echo "✅ Questions de test créées avec succès<br>";
                } else {
                    throw new Exception("Erreur lors de la création des questions de test : " . $conn->error);
                }
            } else {
                throw new Exception("Erreur lors de la création des quiz de test : " . $conn->error);
            }
        } else {
            throw new Exception("Erreur lors de la création des utilisateurs de test : " . $conn->error);
        }
    } else {
        echo "ℹ️ Les données de test existent déjà<br>";
    }
    
    echo "<br>✨ Installation terminée avec succès !<br>";
    echo "<br><a href='index.php' style='display: inline-block; padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px;'>Aller à l'accueil</a>";
    
} catch (Exception $e) {
    echo "<h2>❌ Erreur</h2>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
}
?>
