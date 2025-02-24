<?php
session_start();

// Activer l'affichage des erreurs
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Définir l'encodage
mb_internal_encoding('UTF-8');
setlocale(LC_ALL, 'fr_FR.UTF-8');

// Vérifier si l'utilisateur est connecté et est un professeur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'error' => 'Accès non autorisé']);
    exit();
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csvFile'])) {
    try {
        $uploadDir = __DIR__ . '/uploads/';
        
        // Vérifier si le dossier existe, sinon le créer
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                throw new Exception("Impossible de créer le dossier uploads");
            }
        }
        
        // Vérifier les permissions du dossier
        if (!is_writable($uploadDir)) {
            throw new Exception("Le dossier uploads n'est pas accessible en écriture");
        }
        
        $uploadFile = $uploadDir . basename($_FILES['csvFile']['name']);
        
        // Vérifier si le fichier a été uploadé via HTTP POST
        if (!is_uploaded_file($_FILES['csvFile']['tmp_name'])) {
            throw new Exception("Le fichier n'a pas été uploadé correctement");
        }
        
        // Déplacer le fichier
        if (!move_uploaded_file($_FILES['csvFile']['tmp_name'], $uploadFile)) {
            throw new Exception("Erreur lors du déplacement du fichier uploadé");
        }
        
        // Renommer le fichier
        $finalFile = $uploadDir . 'questions.csv';
        if (!rename($uploadFile, $finalFile)) {
            throw new Exception("Erreur lors du renommage du fichier");
        }
        
        // Connexion à la base de données
        require_once '../db_connect.php';
        
        // S'assurer que la connexion est en UTF-8
        $conn->set_charset("utf8mb4");
        
        // Lire le fichier CSV avec détection de l'UTF-8
        if (($handle = fopen($finalFile, "r")) === FALSE) {
            throw new Exception("Impossible d'ouvrir le fichier CSV");
        }
        
        // Détecter et supprimer le BOM UTF-8 si présent
        $bom = fgets($handle, 4);
        if ($bom !== false && substr($bom, 0, 3) !== "\xEF\xBB\xBF") {
            rewind($handle);
        }
        
        // Ignorer la première ligne (en-têtes)
        fgetcsv($handle, 1000, ";");
        
        $questions = [];
        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
            if (count($data) < 7) {
                throw new Exception("Format CSV invalide. Chaque ligne doit avoir 7 colonnes");
            }
            
            $question = trim($data[0]);
            $options = [];
            
            // Les colonnes 1 à 4 contiennent les options A:texte, B:texte, etc.
            for ($i = 1; $i <= 4; $i++) {
                if (empty($data[$i])) {
                    throw new Exception("Option manquante pour la question: " . $question);
                }
                list($letter, $text) = explode(':', $data[$i]);
                $options[trim($letter)] = trim($text);
            }
            
            $correct_letter = trim($data[5]);
            $scenario = trim($data[6]);
            
            $questions[] = [
                'question' => $question,
                'options' => $options,
                'correct_letter' => $correct_letter,
                'scenario' => $scenario
            ];
        }
        
        fclose($handle);

        // Créer une nouvelle instance de quiz dans la base de données
        $questions_json = json_encode($questions, JSON_UNESCAPED_UNICODE);
        $total_questions = count($questions);
        $original_filename = basename($_FILES['csvFile']['name']);
        $file_path = str_replace('.csv', '', strtolower($original_filename));
        $file_path = preg_replace('/[^a-z0-9-_]/', '-', $file_path);
        
        // Vérifier si le chemin existe déjà
        $check_sql = "SELECT id FROM quizzes WHERE file_path = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $file_path);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Ajouter un timestamp si le chemin existe déjà
            $file_path .= '-' . time();
        }
        
        $sql = "INSERT INTO quizzes (file_name, file_path, questions_json, total_questions) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $original_filename, $file_path, $questions_json, $total_questions);
        
        if (!$stmt->execute()) {
            throw new Exception("Erreur lors de la création du quiz: " . $stmt->error);
        }
        
        $quiz_id = $conn->insert_id;
        
        echo json_encode([
            'success' => true,
            'message' => 'Quiz créé avec succès',
            'quiz_id' => $quiz_id,
            'file_path' => $file_path,
            'total_questions' => $total_questions
        ]);
        
    } catch (Exception $e) {
        error_log("Erreur dans upload_api.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée ou fichier manquant'], JSON_UNESCAPED_UNICODE);
}
