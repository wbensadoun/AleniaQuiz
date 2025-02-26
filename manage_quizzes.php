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

// Connexion à la base de données
$conn = new mysqli("localhost", "root", "", "quizzapp");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Récupérer tous les quiz du professeur connecté
$stmt = $conn->prepare("SELECT id, title FROM quizzes WHERE professor_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

// Traitement de la suppression si demandée
if (isset($_POST['delete_quiz']) && isset($_POST['quiz_id'])) {
    $quiz_id = $_POST['quiz_id'];
    
    // Commencer une transaction
    $conn->begin_transaction();
    
    try {
        // Supprimer d'abord les questions associées
        $delete_questions = $conn->prepare("DELETE FROM questions WHERE quiz_id = ?");
        $delete_questions->bind_param("i", $quiz_id);
        $delete_questions->execute();
        
        // Ensuite supprimer le quiz
        $delete_quiz = $conn->prepare("DELETE FROM quizzes WHERE id = ? AND professor_id = ?");
        $delete_quiz->bind_param("ii", $quiz_id, $_SESSION['user_id']);
        $delete_quiz->execute();
        
        $conn->commit();
        header("Location: manage_quizzes.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Erreur lors de la suppression du quiz.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gérer les Quiz - Alenia Quiz</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <main class="container fade-in">
        <div class="quiz-list-container">
            <h1 class="page-title">Gérer les Quiz</h1>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="quiz-list">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($quiz = $result->fetch_assoc()): ?>
                        <div class="quiz-item">
                            <span class="quiz-title"><?php echo htmlspecialchars($quiz['title']); ?></span>
                            <div class="quiz-actions">
                                <a href="edit_quiz.php?id=<?php echo $quiz['id']; ?>" class="btn btn-secondary">
                                    <i class="fas fa-edit"></i> Modifier
                                </a>
                                <form method="POST" class="delete-form" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce quiz ?');">
                                    <input type="hidden" name="quiz_id" value="<?php echo $quiz['id']; ?>">
                                    <button type="submit" name="delete_quiz" class="btn btn-danger">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="no-quiz">Aucun quiz n'a été créé pour le moment.</p>
                <?php endif; ?>
            </div>

            <div class="actions">
                <a href="create_quiz.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Créer un nouveau Quiz
                </a>
                <a href="professor_dashboard.php" class="btn btn-outline">Retour au tableau de bord</a>
            </div>
        </div>
    </main>
</body>
</html>
