<?php
session_start();

// Vérifier si l'utilisateur est connecté et est un professeur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    header('Location: ../login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload des Questions - Alenia</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .instructions {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #3498db;
        }

        .instructions h2 {
            color: #007bff;
            margin-top: 0;
        }

        .instructions pre {
            background: #fff;
            padding: 15px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
            overflow-x: auto;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: #495057;
            font-weight: bold;
        }

        input[type="file"] {
            display: block;
            width: 100%;
            padding: 10px;
            border: 2px dashed #007bff;
            border-radius: 4px;
            background: #f8f9fa;
            cursor: pointer;
            margin-bottom: 20px;
        }

        input[type="file"]:hover {
            background: #e9ecef;
            border-color: #0056b3;
        }

        .btn {
            display: inline-block;
            padding: 8px 16px;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            background-color: #007bff;
            border: none;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .success {
            color: #28a745;
            padding: 10px;
            margin: 10px 0;
            border-left: 3px solid #28a745;
            background: #f8f9fa;
        }
        
        .buttons {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }

        .btn-primary {
            background: #007bff;
        }

        .btn-primary:hover {
            background: #0056b3;
        }

        .btn-secondary {
            background: #6c757d;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .btn-success {
            background: #28a745;
        }

        .btn-success:hover {
            background: #218838;
        }

        #quizButton {
            display: none;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="container">
        <h1>Upload des Questions</h1>
        
        <div class="instructions">
            <h2>Format du fichier CSV attendu :</h2>
            <p>Le fichier doit être un CSV avec 7 colonnes séparées par des points-virgules (;) :</p>
            <ol>
                <li>Question : Le texte de la question</li>
                <li>Option A : Au format A:texte</li>
                <li>Option B : Au format B:texte</li>
                <li>Option C : Au format C:texte</li>
                <li>Option D : Au format D:texte</li>
                <li>Réponse : La lettre de la bonne réponse (A, B, C ou D)</li>
                <li>Scénario : Le contexte de la question</li>
            </ol>
            <h3>Exemple :</h3>
            <pre>Question;Propositions;;;;Reponse;Scenario
Quelle est la capitale de la France ?;A:Londres;B:Paris;C:Berlin;D:Marseille;B;Un touriste vous demande la capitale</pre>
        </div>

        <form id="uploadForm" enctype="multipart/form-data">
            <div class="form-group">
                <label for="csvFile">Sélectionner le fichier CSV :</label>
                <input type="file" id="csvFile" name="csvFile" accept=".csv" required>
            </div>
            <button type="submit" class="btn btn-primary">Uploader les questions</button>
        </form>

        <div id="message"></div>
        
        <div class="buttons">
            <a href="../quiz.php" class="btn btn-success" id="quizButton">Voir le Quiz</a>
            <a href="../index.php" class="btn btn-secondary">Retour à l'accueil</a>
        </div>
    </div>

    <script>
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            var formData = new FormData(this);
            
            fetch('upload_api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('message').innerHTML = 
                        `<div class="success">
                            ${data.message}<br>
                            <div class="buttons">
                                <a href="../quiz.php?id=${data.quiz_id}" class="btn">Voir le quiz</a>
                                <a href="list_quizzes.php" class="btn">Voir la liste des quiz</a>
                            </div>
                        </div>`;
                } else {
                    document.getElementById('message').innerHTML = 
                        `<div class="error">${data.error}</div>`;
                }
            })
            .catch(error => {
                document.getElementById('message').innerHTML = 
                    `<div class="error">Erreur lors de l'envoi du fichier</div>`;
            });
        });
    </script>
</body>
</html>
