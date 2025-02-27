<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db.php';

// Vérifier si l'utilisateur est connecté et est un professeur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professeur') {
    header('Location: login.php');
    exit();
}

// Traitement de l'autorisation de reprise
if (isset($_POST['grant_retake'])) {
    $user_id = $_POST['user_id'];
    $quiz_id = $_POST['quiz_id'];
    
    $stmt = $conn->prepare("INSERT INTO quiz_retake_permissions (user_id, quiz_id, granted_by, granted_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iii", $user_id, $quiz_id, $_SESSION['user_id']);
    $stmt->execute();
}

// Récupérer les statistiques
$stats = $conn->query("
    SELECT 
        COUNT(DISTINCT q.id) as total_quizzes,
        COUNT(DISTINCT qu.id) as total_questions,
        COUNT(DISTINCT CASE WHEN u.role = 'etudiant' THEN u.id END) as total_students
    FROM quizzes q
    LEFT JOIN questions qu ON q.id = qu.quiz_id
    LEFT JOIN users u ON 1=1
")->fetch_assoc();

// Récupérer les résultats des étudiants avec leur statut de reprise
$results = $conn->query("
    SELECT 
        r.id as result_id,
        r.user_id,
        r.quiz_id,
        r.score,
        r.total_questions,
        r.completed_at,
        u.username as student_name,
        q.title as quiz_title,
        CASE 
            WHEN qrp.id IS NOT NULL AND qrp.used = 0 THEN 'authorized'
            WHEN qrp.used = 1 THEN 'used'
            ELSE 'none'
        END as retake_status
    FROM results r
    JOIN users u ON r.user_id = u.id
    JOIN quizzes q ON r.quiz_id = q.id
    LEFT JOIN quiz_retake_permissions qrp ON r.user_id = qrp.user_id 
        AND r.quiz_id = qrp.quiz_id
    WHERE u.role = 'etudiant'
    ORDER BY r.completed_at DESC
");

// Connexion à la base de données
$conn = new mysqli("localhost", "root", "", "quizzapp");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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

// Récupérer tous les résultats des étudiants
$stmt = $conn->prepare("
    SELECT 
        r.*,
        u.username as student_name,
        q.title as quiz_title,
        q.id as quiz_id,
        CASE 
            WHEN qrp.id IS NOT NULL AND qrp.used = 0 THEN 'authorized'
            WHEN qrp.used = 1 THEN 'used'
            ELSE 'none'
        END as retake_status
    FROM results r
    JOIN users u ON r.user_id = u.id
    JOIN quizzes q ON r.quiz_id = q.id
    LEFT JOIN quiz_retake_permissions qrp ON r.quiz_id = qrp.quiz_id 
        AND r.user_id = qrp.user_id
    WHERE q.professor_id = ?
    ORDER BY r.completed_at DESC
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$all_results = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord Professeur - Alenia Quiz</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .dashboard-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .section-title {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .data-table th, .data-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .data-table th {
            background: #f8f9fa;
            font-weight: 600;
        }
        .action-button {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            margin-right: 5px;
            font-size: 0.9em;
        }
        .view-button {
            background: #007bff;
            color: white;
        }
        .edit-button {
            background: #28a745;
            color: white;
        }
        .authorize-button {
            background: #17a2b8;
            color: white;
        }
        .authorized-status {
            color: #28a745;
        }
        .used-status {
            color: #6c757d;
        }
        .none-status {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="dashboard-container">
        <div class="dashboard-section">
            <h2 class="section-title">Derniers Quiz Créés</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Catégorie</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($quiz = $recent_quizzes->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($quiz['title']); ?></td>
                            <td><?php echo htmlspecialchars($quiz['category']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($quiz['created_at'])); ?></td>
                            <td>
                                <a href="edit_quiz.php?id=<?php echo $quiz['id']; ?>" class="action-button edit-button">Modifier</a>
                                <a href="view_quiz.php?id=<?php echo $quiz['id']; ?>" class="action-button view-button">Voir</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="dashboard-section">
            <h2 class="section-title">Derniers Résultats</h2>
            <table class="data-table">
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
                            <td><?php echo $result['score'] . '/' . $result['total_questions']; ?> (<?php echo round(($result['score'] / $result['total_questions']) * 100); ?>%)</td>
                            <td><?php echo date('d/m/Y', strtotime($result['completed_at'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="dashboard-section">
            <h2 class="section-title">Résultats des étudiants</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Étudiant</th>
                        <th>Quiz</th>
                        <th>Score</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $all_results->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['quiz_title']); ?></td>
                            <td><?php echo $row['score'] . '/' . $row['total_questions']; ?> (<?php echo round(($row['score'] / $row['total_questions']) * 100); ?>%)</td>
                            <td><?php echo date('d/m/Y', strtotime($row['completed_at'])); ?></td>
                            <td>
                                <a href="view_result.php?id=<?php echo $row['id']; ?>" class="action-button view-button">Voir</a>
                                <?php if ($row['retake_status'] === 'none'): ?>
                                    <form method="post" style="display: inline;">
                                        <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                                        <input type="hidden" name="quiz_id" value="<?php echo $row['quiz_id']; ?>">
                                        <button type="submit" name="grant_retake" class="action-button authorize-button">
                                            Autoriser la reprise
                                        </button>
                                    </form>
                                <?php elseif ($row['retake_status'] === 'authorized'): ?>
                                    <span class="authorized-status">Reprise autorisée</span>
                                <?php elseif ($row['retake_status'] === 'used'): ?>
                                    <span class="used-status">Reprise utilisée</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?>