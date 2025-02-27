<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professeur') {
    header('Location: login.php');
    exit();
}

$conn = new mysqli("localhost", "root", "", "quizzapp");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    
    if (empty($title) || empty($description) || empty($category)) {
        $error = "Tous les champs sont obligatoires";
    } else if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        $error = "Veuillez sélectionner un fichier CSV valide";
    } else {
        $file = $_FILES['csv_file']['tmp_name'];
        $questions = array();
        
        if (($handle = fopen($file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if (count($data) >= 7) { // Au moins 7 colonnes requises
                    $questions[] = array(
                        'question' => $data[0],
                        'reponse_a' => $data[1],
                        'reponse_b' => $data[2],
                        'reponse_c' => $data[3],
                        'reponse_d' => $data[4],
                        'bonne_reponse' => $data[5],
                        'scenario' => $data[6],
                        'timer' => isset($data[7]) ? intval($data[7]) : 30
                    );
                }
            }
            fclose($handle);
            
            if (count($questions) > 0) {
                $stmt = $conn->prepare("INSERT INTO quizzes (title, description, category, professor_id) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("sssi", $title, $description, $category, $_SESSION['user_id']);
                
                if ($stmt->execute()) {
                    $quiz_id = $stmt->insert_id;
                    $success = true;
                    
                    $stmt = $conn->prepare("INSERT INTO questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_answer, scenario, timer) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    
                    foreach ($questions as $q) {
                        $stmt->bind_param("isssssssi", 
                            $quiz_id,
                            $q['question'],
                            $q['reponse_a'],
                            $q['reponse_b'],
                            $q['reponse_c'],
                            $q['reponse_d'],
                            $q['bonne_reponse'],
                            $q['scenario'],
                            $q['timer']
                        );
                        $stmt->execute();
                    }
                    
                    $success = "Quiz créé avec succès !";
                    header("Location: manage_quizzes.php");
                    exit();
                } else {
                    $error = "Erreur lors de la création du quiz";
                }
            } else {
                $error = "Le fichier CSV ne contient pas de questions valides";
            }
        } else {
            $error = "Impossible de lire le fichier CSV";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un Quiz - Alenia Quiz</title>
    <style>
        .quiz-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .quiz-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .quiz-header h1 {
            color: #1a237e;
            margin: 0;
            font-size: 2em;
        }
        .instructions {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .instructions h2 {
            color: #1a237e;
            margin-top: 0;
        }
        .instructions ul {
            list-style-type: none;
            padding: 0;
        }
        .instructions li {
            margin-bottom: 10px;
            padding-left: 20px;
            position: relative;
        }
        .instructions li:before {
            content: "•";
            color: #1a237e;
            position: absolute;
            left: 0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: bold;
        }
        .form-group input[type="text"],
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        .file-upload {
            border: 2px dashed #1a237e;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            margin: 20px 0;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .file-upload:hover {
            background: #f5f5f5;
        }
        .file-upload input[type="file"] {
            display: none;
        }
        .template-link {
            display: inline-block;
            color: #1a237e;
            text-decoration: none;
            margin-top: 10px;
        }
        .template-link:hover {
            text-decoration: underline;
        }
        .submit-btn {
            background: #1a237e;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .submit-btn:hover {
            background: #0d47a1;
        }
        .cancel-btn {
            background: #f44336;
            margin-left: 10px;
        }
        .success-message {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .button-group {
            text-align: center;
            margin-top: 20px;
        }
        .example {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
            color: #1565c0;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="quiz-container">
        <div class="quiz-header">
            <h1>Créer un Nouveau Quiz</h1>
        </div>

        <?php if ($success): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="instructions">
            <h2>Instructions pour le fichier CSV</h2>
            <p>Le fichier CSV doit être au format suivant (séparé par des virgules) :</p>
            <ul>
                <li>Question</li>
                <li>Proposition A (format "A:réponse")</li>
                <li>Proposition B (format "B:réponse")</li>
                <li>Proposition C (format "C:réponse")</li>
                <li>Proposition D (format "D:réponse")</li>
                <li>Réponse (A, B, C ou D)</li>
                <li>Scénario</li>
                <li>Timer (en secondes, par défaut 30 si non spécifié)</li>
            </ul>
            <div class="example">
                Exemple : Question,A:Réponse1,B:Réponse2,C:Réponse3,D:Réponse4,A,Description du scénario,30
            </div>
            <a href="templates/quiz_template.csv" download class="template-link">
                📥 Télécharger le modèle CSV
            </a>
        </div>

        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Titre du Quiz</label>
                <input type="text" id="title" name="title" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" required></textarea>
            </div>

            <div class="form-group">
                <label for="category">Catégorie</label>
                <select id="category" name="category" required>
                    <option value="">Sélectionnez une catégorie</option>
                    <option value="mathematiques">Mathématiques</option>
                    <option value="sciences">Sciences</option>
                    <option value="histoire">Histoire</option>
                    <option value="geographie">Géographie</option>
                    <option value="langues">Langues</option>
                    <option value="culture">Culture Générale</option>
                </select>
            </div>

            <div class="file-upload" onclick="document.getElementById('csv_file').click()">
                <input type="file" id="csv_file" name="csv_file" accept=".csv" onchange="updateFileName(this)">
                <p id="file-name">Glissez votre fichier CSV ici ou cliquez pour sélectionner</p>
            </div>

            <div class="button-group">
                <button type="submit" class="submit-btn">Créer le Quiz</button>
                <a href="manage_quizzes.php" class="submit-btn cancel-btn">Annuler</a>
            </div>
        </form>
    </div>

    <script>
    function updateFileName(input) {
        const fileName = input.files[0] ? input.files[0].name : 'Glissez votre fichier CSV ici ou cliquez pour sélectionner';
        document.getElementById('file-name').textContent = fileName;
    }
    </script>
</body>
</html>
