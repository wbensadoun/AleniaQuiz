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

// Récupérer les statistiques
$stats = array();

// Nombre total de quiz créés
$sql = "SELECT COUNT(*) as total_quizzes FROM quizzes WHERE professor_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$stats['total_quizzes'] = $result->fetch_assoc()['total_quizzes'];

// Nombre total de questions créées
$sql = "SELECT COUNT(q.id) as total_questions 
        FROM questions q 
        JOIN quizzes qz ON q.quiz_id = qz.id 
        WHERE qz.professor_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$stats['total_questions'] = $result->fetch_assoc()['total_questions'];

// Nombre total d'élèves ayant participé aux quiz
$sql = "SELECT COUNT(DISTINCT r.user_id) as total_students 
        FROM results r 
        JOIN quizzes q ON r.quiz_id = q.id 
        WHERE q.professor_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$stats['total_students'] = $result->fetch_assoc()['total_students'];

// Score moyen global pour les quiz de ce professeur
$sql = "SELECT AVG(r.score * 100.0 / r.total_questions) as avg_score 
        FROM results r 
        JOIN quizzes q ON r.quiz_id = q.id 
        WHERE q.professor_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$stats['avg_score'] = $result->fetch_assoc()['avg_score'] ?? 0;

// Récupérer les derniers quiz créés
$sql = "SELECT id, title, description, category, created_at 
        FROM quizzes 
        WHERE professor_id = ? 
        ORDER BY created_at DESC 
        LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$recent_quizzes = $stmt->get_result();

// Récupérer les derniers résultats des élèves pour les quiz de ce professeur
$sql = "SELECT r.*, u.username, q.title as quiz_title
        FROM results r 
        JOIN users u ON r.user_id = u.id 
        JOIN quizzes q ON r.quiz_id = q.id
        WHERE q.professor_id = ? AND u.role = 'eleve'
        ORDER BY r.completed_at DESC 
        LIMIT 10";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$recent_results = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord Professeur - Alenia Quiz</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <main class="container fade-in">
        <h1 class="page-title">Tableau de bord Professeur</h1>

        <div class="dashboard-stats">
            <div class="stat-card">
                <h3>Quiz Créés</h3>
                <p class="stat-number"><?php echo $stats['total_quizzes']; ?></p>
            </div>
            <div class="stat-card">
                <h3>Questions Créées</h3>
                <p class="stat-number"><?php echo $stats['total_questions']; ?></p>
            </div>
            <div class="stat-card">
                <h3>Élèves Participants</h3>
                <p class="stat-number"><?php echo $stats['total_students']; ?></p>
            </div>
            <div class="stat-card">
                <h3>Score Moyen</h3>
                <p class="stat-number"><?php echo number_format($stats['avg_score'], 1); ?>%</p>
            </div>
        </div>

        <div class="dashboard-sections">
            <section class="recent-quizzes">
                <h2>Derniers Quiz Créés</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Titre</th>
                                <th>Catégorie</th>
                                <th>Date de création</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($quiz = $recent_quizzes->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($quiz['title']); ?></td>
                                <td><?php echo htmlspecialchars($quiz['category']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($quiz['created_at'])); ?></td>
                                <td>
                                    <a href="edit_quiz.php?id=<?php echo $quiz['id']; ?>" class="btn btn-small">Modifier</a>
                                    <a href="view_quiz.php?id=<?php echo $quiz['id']; ?>" class="btn btn-small">Voir</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="recent-results">
                <h2>Derniers Résultats</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Élève</th>
                                <th>Quiz</th>
                                <th>Score</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($result = $recent_results->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($result['username']); ?></td>
                                <td><?php echo htmlspecialchars($result['quiz_title']); ?></td>
                                <td><?php echo number_format(($result['score'] / $result['total_questions']) * 100, 1); ?>%</td>
                                <td><?php echo date('d/m/Y H:i', strtotime($result['completed_at'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div class="dashboard-actions">
            <a href="create_quiz.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Créer un nouveau Quiz
            </a>
            <a href="manage_quizzes.php" class="btn btn-secondary">
                <i class="fas fa-list"></i> Gérer mes Quiz
            </a>
        </div>
    </main>
</body>
</html>
<?php
$conn->close();
?>