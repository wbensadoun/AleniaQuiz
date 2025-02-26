<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'includes/header.php';

// Vérification de l'authentification et du rôle
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professeur') {
    header("Location: login.php");
    exit();
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
        $csvFile = $_FILES['csv_file']['tmp_name'];
        $quizTitle = $_POST['quiz_title'];
        $quizDescription = $_POST['quiz_description'];
        $category = $_POST['category'];
        
        // Connexion à la base de données
        $conn = new mysqli("localhost", "root", "", "quizzapp");
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Commencer une transaction
        $conn->begin_transaction();

        try {
            // Créer d'abord le quiz
            $stmt = $conn->prepare("INSERT INTO quizzes (title, description, category, professor_id) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $quizTitle, $quizDescription, $category, $_SESSION['user_id']);
            
            if (!$stmt->execute()) {
                throw new Exception("Erreur lors de la création du quiz");
            }
            
            $quizId = $conn->insert_id;
            
            // Préparer la requête d'insertion des questions
            $stmt = $conn->prepare("INSERT INTO questions (quiz_id, question, option_a, option_b, option_c, option_d, correct_answer, scenario, timer) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            if (($handle = fopen($csvFile, "r")) !== FALSE) {
                // Ignorer la première ligne (en-têtes)
                fgetcsv($handle, 0, ";");
                
                $successCount = 0;
                $errorCount = 0;
                
                while (($data = fgetcsv($handle, 0, ";")) !== FALSE) {
                    if (count($data) >= 8) {
                        $question = trim($data[0]);
                        
                        // Traiter les propositions (format A:réponse)
                        $propositions = array();
                        for ($i = 1; $i <= 4; $i++) {
                            $prop = trim($data[$i]);
                            if (preg_match('/^([A-D]):(.+)$/', $prop, $matches)) {
                                $propositions[$matches[1]] = trim($matches[2]);
                            }
                        }
                        
                        // Vérifier que nous avons toutes les propositions A, B, C, D
                        if (count($propositions) === 4 && 
                            isset($propositions['A']) && isset($propositions['B']) && 
                            isset($propositions['C']) && isset($propositions['D'])) {
                            
                            $correct_answer = strtoupper(trim($data[5]));
                            $scenario = trim($data[6]);
                            // Gestion du timer avec valeur par défaut de 30 secondes
                            $timer = !empty(trim($data[7])) ? (int)trim($data[7]) : 30;
                            
                            // Vérifier que le timer est un nombre positif
                            if ($timer <= 0) {
                                $timer = 30; // Valeur par défaut si le timer est invalide
                            }
                            
                            // Vérifier que la réponse est valide
                            if (in_array($correct_answer, ['A', 'B', 'C', 'D'])) {
                                $stmt->bind_param("isssssssi", 
                                    $quizId,
                                    $question,
                                    $propositions['A'],
                                    $propositions['B'],
                                    $propositions['C'],
                                    $propositions['D'],
                                    $correct_answer,
                                    $scenario,
                                    $timer
                                );
                                
                                if ($stmt->execute()) {
                                    $successCount++;
                                } else {
                                    $errorCount++;
                                }
                            } else {
                                $errorCount++;
                            }
                        } else {
                            $errorCount++;
                        }
                    } else {
                        $errorCount++;
                    }
                }
                fclose($handle);
                
                if ($successCount > 0) {
                    $conn->commit();
                    $message = "Quiz '$quizTitle' créé avec succès avec $successCount questions.";
                    if ($errorCount > 0) {
                        $message .= " $errorCount questions n'ont pas pu être ajoutées.";
                    }
                    $messageType = "success";
                } else {
                    throw new Exception("Aucune question n'a pu être ajoutée. Vérifiez le format du fichier CSV.");
                }
            } else {
                throw new Exception("Erreur lors de la lecture du fichier CSV.");
            }
        } catch (Exception $e) {
            $conn->rollback();
            $message = $e->getMessage();
            $messageType = "error";
        }
        
        $conn->close();
    } else {
        $message = "Erreur lors du téléchargement du fichier.";
        $messageType = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un Quiz - Alenia Quiz</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <main class="container fade-in">
        <div class="create-quiz-container">
            <h1 class="page-title">Créer un Nouveau Quiz</h1>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="csv-instructions">
                <h2>Instructions pour le fichier CSV</h2>
                <p>Le fichier CSV doit être au format suivant (séparé par des points-virgules) :</p>
                <ol>
                    <li>Question</li>
                    <li>Proposition A (format "A:réponse")</li>
                    <li>Proposition B (format "B:réponse")</li>
                    <li>Proposition C (format "C:réponse")</li>
                    <li>Proposition D (format "D:réponse")</li>
                    <li>Réponse (A, B, C ou D)</li>
                    <li>Scénario</li>
                    <li>Timer (en secondes, par défaut 30 si non spécifié)</li>
                </ol>
                <p>Exemple : Question;A:Réponse1;B:Réponse2;C:Réponse3;D:Réponse4;A;Description du scénario;30</p>
                <a href="templates/quiz_template.csv" class="btn btn-secondary" download>
                    <i class="fas fa-download"></i> Télécharger le modèle CSV
                </a>
            </div>

            <form method="POST" enctype="multipart/form-data" class="create-quiz-form">
                <div class="form-group">
                    <label for="quiz_title">Titre du Quiz</label>
                    <input type="text" id="quiz_title" name="quiz_title" required 
                           class="form-input" placeholder="Entrez le titre du quiz">
                </div>

                <div class="form-group">
                    <label for="quiz_description">Description</label>
                    <textarea id="quiz_description" name="quiz_description" required 
                              class="form-input" placeholder="Décrivez votre quiz"></textarea>
                </div>

                <div class="form-group">
                    <label for="category">Catégorie</label>
                    <select id="category" name="category" required class="form-input">
                        <option value="">Sélectionnez une catégorie</option>
                        <option value="mathematiques">Mathématiques</option>
                        <option value="sciences">Sciences</option>
                        <option value="histoire">Histoire</option>
                        <option value="geographie">Géographie</option>
                        <option value="langues">Langues</option>
                        <option value="informatique">Informatique</option>
                        <option value="autre">Autre</option>
                    </select>
                </div>

                <div class="file-upload-container">
                    <div class="file-upload-area" id="drop-zone">
                        <input type="file" name="csv_file" id="csv_file" accept=".csv" 
                               class="file-input" required>
                        <label for="csv_file" class="file-label">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Glissez votre fichier CSV ici ou cliquez pour sélectionner</span>
                        </label>
                        <div class="file-info" id="file-info"></div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Créer le Quiz
                    </button>
                    <a href="professor_dashboard.php" class="btn btn-outline">Annuler</a>
                </div>
            </form>
        </div>
    </main>

    <script>
        // Gestion du drag & drop et de l'affichage du nom de fichier
        const dropZone = document.getElementById('drop-zone');
        const fileInput = document.getElementById('csv_file');
        const fileInfo = document.getElementById('file-info');

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('dragover');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('dragover');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            fileInput.files = e.dataTransfer.files;
            updateFileInfo();
        });

        fileInput.addEventListener('change', updateFileInfo);

        function updateFileInfo() {
            if (fileInput.files.length > 0) {
                const fileName = fileInput.files[0].name;
                fileInfo.textContent = `Fichier sélectionné : ${fileName}`;
                fileInfo.style.display = 'block';
            } else {
                fileInfo.style.display = 'none';
            }
        }
    </script>
</body>
</html>
