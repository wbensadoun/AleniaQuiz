<?php
session_start();
include 'includes/header.php';

// Vérifier si l'utilisateur est connecté et est admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// Connexion à la base de données
$conn = new mysqli("localhost", "root", "", "quizzapp");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Traitement de la modification du rôle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['new_role'])) {
    $user_id = (int)$_POST['user_id'];
    $new_role = $_POST['new_role'];
    
    // Vérifier que le nouveau rôle est valide
    if (in_array($new_role, ['eleve', 'professeur'])) {
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->bind_param("si", $new_role, $user_id);
        $stmt->execute();
    }
}

// Récupérer tous les utilisateurs sauf l'admin
$sql = "SELECT id, username, role FROM users WHERE role != 'admin' ORDER BY username";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration - Alenia Quiz</title>
    <style>
        .admin-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
        }
        .users-table {
            width: 100%;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-collapse: collapse;
            margin-top: 20px;
        }
        .users-table th,
        .users-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .users-table th {
            background: #1a237e;
            color: white;
            font-weight: 500;
        }
        .users-table th:first-child {
            border-top-left-radius: 10px;
        }
        .users-table th:last-child {
            border-top-right-radius: 10px;
        }
        .users-table tr:last-child td:first-child {
            border-bottom-left-radius: 10px;
        }
        .users-table tr:last-child td:last-child {
            border-bottom-right-radius: 10px;
        }
        .users-table tr:hover {
            background: #f5f5f5;
        }
        .role-select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-right: 10px;
            width: 120px;
        }
        .update-btn {
            padding: 8px 15px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .update-btn:hover {
            background: #45a049;
        }
        .role-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9em;
            font-weight: 500;
        }
        .role-eleve {
            background: #e3f2fd;
            color: #1976d2;
        }
        .role-professeur {
            background: #e8f5e9;
            color: #2e7d32;
        }
        .page-title {
            color: #1a237e;
            margin-bottom: 30px;
            font-size: 2em;
            text-align: center;
        }
        .success-message {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            display: none;
        }
    </style>
</head>
<body>
    <main class="admin-container">
        <h1 class="page-title">Gestion des Utilisateurs</h1>
        
        <div id="successMessage" class="success-message">
            Rôle mis à jour avec succès
        </div>
        
        <table class="users-table">
            <thead>
                <tr>
                    <th>Utilisateur</th>
                    <th>Rôle Actuel</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td>
                            <span class="role-badge role-<?php echo $user['role']; ?>">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" action="" class="role-form" onsubmit="showSuccess()">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <select name="new_role" class="role-select">
                                    <option value="eleve" <?php echo $user['role'] === 'eleve' ? 'selected' : ''; ?>>Élève</option>
                                    <option value="professeur" <?php echo $user['role'] === 'professeur' ? 'selected' : ''; ?>>Professeur</option>
                                </select>
                                <button type="submit" class="update-btn">Modifier</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>

    <script>
    function showSuccess() {
        const message = document.getElementById('successMessage');
        message.style.display = 'block';
        setTimeout(() => {
            message.style.display = 'none';
        }, 3000);
    }
    </script>
</body>
</html>

<?php $conn->close(); ?>
