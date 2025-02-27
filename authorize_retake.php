<?php
session_start();
require_once 'includes/db.php';

// Vérifier si l'utilisateur est un professeur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professeur') {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['result_id']) && isset($_GET['user_id']) && isset($_GET['quiz_id'])) {
    $result_id = (int)$_GET['result_id'];
    $user_id = (int)$_GET['user_id'];
    $quiz_id = (int)$_GET['quiz_id'];

    // Vérifier que le quiz appartient au professeur
    $stmt = $conn->prepare("SELECT 1 FROM quizzes WHERE id = ? AND professor_id = ?");
    $stmt->bind_param("ii", $quiz_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Insérer la permission de reprise
        $stmt = $conn->prepare("
            INSERT INTO quiz_retake_permissions (quiz_id, user_id, authorized_by, authorized_at, used)
            VALUES (?, ?, ?, NOW(), 0)
        ");
        $stmt->bind_param("iii", $quiz_id, $user_id, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "La reprise a été autorisée avec succès.";
        } else {
            $_SESSION['error_message'] = "Une erreur est survenue lors de l'autorisation de la reprise.";
        }
    } else {
        $_SESSION['error_message'] = "Vous n'avez pas l'autorisation de gérer ce quiz.";
    }
}

// Rediriger vers le tableau de bord
header('Location: professor_dashboard.php');
exit();
