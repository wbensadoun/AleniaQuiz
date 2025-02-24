<?php
session_start();

if (!isset($_SESSION['user_id']) || !$_SESSION['is_professor']) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "quiz_app");

// Récupérer tous les résultats des élèves
$sql = "SELECT r.*, u.username, COUNT(*) as total_quizzes, AVG(score) as average_score 
        FROM results r 
        JOIN users u ON r.user_id = u.id 
        WHERE u.is_professor = 0 
        GROUP BY u.id";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tableau de Bord Professeur</title>
    <link rel="stylesheet" href="quiz.css">
</head>
<body>
    <div class="container">
        <h2>Tableau de Bord Professeur</h2>
        <div class="welcome">
            Bienvenue, <?php echo htmlspecialchars($_SESSION['username']); ?>
        </div>
        
        <div class="actions">
            <a href="secure_admin/professeur.html" class="button">Gérer les Questions</a>
            <a href="#" class="button" onclick="exportToPDF()">Exporter en PDF</a>
            <a href="logout.php" class="button">Déconnexion</a>
        </div>

        <h3>Résultats des Élèves</h3>
        <table>
            <thead>
                <tr>
                    <th>Élève</th>
                    <th>Nombre de Quiz</th>
                    <th>Score Moyen</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo $row['total_quizzes']; ?></td>
                    <td><?php echo round($row['average_score'], 2); ?>%</td>
                    <td>
                        <button onclick="viewDetails(<?php echo $row['user_id']; ?>)">Voir Détails</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script>
    function exportToPDF() {
        // À implémenter avec une bibliothèque PDF
        alert("Fonctionnalité d'export PDF à venir");
    }

    function viewDetails(userId) {
        // Afficher les détails des résultats d'un élève
        window.location.href = 'student_details.php?id=' + userId;
    }
    </script>
</body>
</html>
